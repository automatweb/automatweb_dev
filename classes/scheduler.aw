<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/scheduler.aw,v 2.4 2002/11/07 10:52:24 kristo Exp $
// scheduler.aw - Scheduler

class scheduler extends aw_template
{
	function scheduler()
	{
		$this->init("scheduler");
		$this->file = get_instance("file");
	}

	////
	// !adds an event to the schedule - complete description @ http://aw.struktuur.ee/41441
	function add($arr)
	{
		extract($arr);
		if (!$time && !$rep_id)
		{
			$this->raise_error(ERR_SCHED_NOTIMEREP, "No time or repeater id specified in adding an event", true);
		}

		if (!$event)
		{
			// no url? fuck off.
			return false;
		};

		if ($time)
		{
			$this->evnt_add($time, $event, $uid, $password);
		}

		if ($rep_id)
		{
			// if we use a repeater for scheduling events we get a bunch of times and add the events for those times
			$pl = get_instance("planner");
			$reps = $pl->get_events(array( 
				"start" => time(), 
				"limit" => 20,
				"index_time" => true,
				"event" => $rep_id, 
				"end" => time()+24*3600*300
			));
			$ltime = 0;
			if (is_array($reps))
			{
				foreach($reps as $time => $_e)
				{
					$this->evnt_add($time, $event, $uid, $password, $rep_id);
					$ltime = $time;
				}
			};
			// and the clever bit here - schedule an event right after the last repeater to reschedule 
			// events for that repeater
			if ($ltime)
			{
				$this->evnt_add(
					$ltime+1, 
					$this->mk_my_orb("update_repeater", array("id" => $rep_id))
				);
			}
		}
	}

	////
	// !updates the scheduled events that use repeater $id
	function update_repeaters($arr)
	{
		extract($arr);

		$this->open_session();

		$newdat = array();
		$evdat = array();
		
		// delete the events for that repeater
		foreach($this->repdata as $evnt)
		{
			if ($evnt["rep_id"] != $id)
			{
				$newdat[] = $evnt;
			}
			else
			{
				$evdat = $evnt;
			}
		}
		$this->repdata = $newdat;

		// and now add new events for that repeater
		$pl = get_instance("planner");
		$reps = $pl->get_events(array( 
			"start" => time(), 
			"limit" => 20,
			"index_time" => true,
			"event" => $id, 
			"end" => time()+24*3600*300
		));
		if (is_array($reps))
		{
			foreach($reps as $time => $_e)
			{
				$evdat["time"] = $time;
				$this->repdata[] = $evdat;
			}
		};
		$this->close_session(true);
	}

	function evnt_add($time, $event, $uid = "", $password = "", $rep_id = 0)
	{
		$this->open_session();
		$found = false;
		if (is_array($this->repdata))
		{
			foreach($this->repdata as $evnt)
			{
				if ($evnt["time"] == $time && $evnt["event"] == $event && $evnt["uid"] == $uid)
				{
					$found = true;
				}
			}
		}

		if (!$found)
		{
			$this->repdata[] = array("time" => $time, "event" => $event, "uid" => $uid, "password" => $password, "rep_id" => $rep_id);
		}
		$this->close_session(true);
	}

	function open_session()
	{
		$this->session_fp = fopen($this->cfg["sched_file"], "a+");
		flock($this->session_fp,LOCK_EX);

		fseek($this->session_fp,0,SEEK_SET);
		clearstatcache();
		$fc = fread($this->session_fp, filesize($this->cfg["sched_file"]));
		$this->repdata = aw_unserialize($fc);
		if (!is_array($this->repdata))
		{
			$this->repdata = array();
		}
	}

	function close_session($write = false)
	{
		if ($write)
		{
			ftruncate($this->session_fp,0);
			fseek($this->session_fp,0,SEEK_SET);

			fwrite($this->session_fp, aw_serialize($this->repdata,SERIALIZE_XML));
			fflush($this->session_fp);
		}

		flock($this->session_fp,LOCK_UN);
		fclose($this->session_fp);
		$this->session_fp = false;
	}

	////
	// !this is where the event processing will take place
	function do_events($arr)
	{
		extract($arr);
		set_time_limit(0);
		
		// ok, here check if events are already being processed
		if (file_exists($this->cfg["lock_file"]) && (filectime($this->cfg["lock_file"]) > (time()-180)))
		{
			// they are so just bail out
			echo "bailing for lock file ",$this->cfg["lock_file"],"<br>\n";
			return;
		}

		touch($this->cfg["lock_file"]);

		// read in all events
		$this->open_session();
		$this->close_session();

		// now do all events for which the time has expired
		$cp = $this->repdata;
		foreach($cp as $evnt)
		{
			if (time() > $evnt["time"])
			{
				echo "exec event $evnt[event] <br>";
				$this->do_and_log_event($evnt);
			}
		}
		echo "unlinking ",$this->cfg["lock_file"]," <Br>";
		unlink($this->cfg["lock_file"]);
	}

	function do_and_log_event($evnt)
	{
		if ($evnt["event"] == "")
		{
			return;
		}

		preg_match("/^http:\/\/(.*)\//U",$evnt["event"], $mt);
		$url = $mt[1];

		echo "url = $url <br>";
		$awt = get_instance("aw_test");
		$awt->handshake(array("host" => $url));

		if ($evnt["uid"] && $evnt["password"])
		{
			// we must log in 
			echo "logging in as $url $evnt[uid] $evnt[password] <br>";
			$awt->login(array("host" => $url, "uid" => $evnt["uid"], "password" => $evnt["password"]));
		}

		echo "do send req $url ",substr($evnt["event"],strlen("http://")+strlen($url))," <br>";
		$req = $awt->do_send_request(array("host" => $url, "req" => substr($evnt["event"],strlen("http://")+strlen($url))));

		if ($evnt["uid"] && $evnt["password"])
		{
			// be nice and logout
			$awt->logout(array("host" => $url));
		}

		// consider the event done, so log it and remove
		$this->log_event($evnt, $req);

		$this->remove($evnt);
	}

	////
	// !removes all the events that match the filter in $evnt
	function remove($evnt)
	{
		$this->open_session();
		$newdat = array();
		foreach($this->repdata as $e)
		{
			if (!$this->match($evnt, $e))
			{
				$newdat[] = $e;
			}
			else
			{
				echo "removing evnt $evnt[event] <br>";
			}
		}

		$this->repdata = $newdat;
		$this->close_session(true);
	}

	function match($mask, $event)
	{
		$match = true;
		foreach($mask as $k => $v)
		{
			if ($event[$k] != $v)
			{
				$match = false;
			}
		}
		return $match;
	}

	function open_log_session()
	{
		$this->log_fp = fopen($this->cfg["log_file"], "a+");
		flock($this->log_fp,LOCK_EX);

		fseek($this->log_fp,0,SEEK_SET);
		clearstatcache();
		$fc = fread($this->log_fp, filesize($this->cfg["log_file"]));
		$this->log = aw_unserialize($fc);
		if (!is_array($this->log))
		{
			$this->log = array();
		}
	}

	function close_log_session($write = false)
	{
		if ($write)
		{
			ftruncate($this->log_fp,0);
			fseek($this->log_fp,0,SEEK_SET);

			fwrite($this->log_fp, aw_serialize($this->log,SERIALIZE_XML));
			fflush($this->log_fp);
		}

		flock($this->log_fp,LOCK_UN);
		fclose($this->log_fp);
	}

	function log_event($event, $pg)
	{
		$this->open_log_session();
		$this->log[] = array("time" => time(), "event" => $event, "response" => $pg);
		$this->close_log_session(true);
	}

	////
	// !returns the log entries for the events that match mask
	function get_log_for_events($mask)
	{
		// read log
		$this->open_log_session();
		$this->close_log_session();

		$ret = array();
		foreach($this->log as $ldat)
		{
			if ($this->match($mask,$ldat["event"]))
			{
				$ret[] = $ldat;
			}
		}

		return $ret;
	}

	function remove_log_events($mask)
	{
		$this->open_log_session();
		$ret = array();
		foreach($this->log as $ldat)
		{
			if (!$this->match($mask,$ldat["event"]))
			{
				$ret[] = $ldat;
			}
		}
		$this->log = $ret;
		$this->close_log_session(true);
	}

	////
	// !ui for scheduler
	function show($arr)
	{
		extract($arr);
		load_vcl("table");
		$t = new aw_table(array("prefix" => "schedshow"));
		$t->parse_xml_def($this->cfg["basedir"]."/xml/scheduler/show.xml");
		$this->read_template("list.tpl");
		$this->open_session();
		$this->close_session();

		foreach($this->repdata as $evnt)
		{
			$t->define_data(array(
				"time" => $evnt["time"],
				"event" => $evnt["event"],
			));
		}

		$t->sort_by(array(
                        "field" => ($sortby) ? $sortby : "time",
                        "sorder" => ($sort_order) ? $sort_order : "asc",
                ));

		$this->vars(array(
			"table" => $t->draw(),
			"log_url" => $this->mk_my_orb("show_log")
		));
		return $this->parse();
	}

	////
	// !shows log entries
	function show_log($arr)
	{
		extract($arr);
		$this->read_template("log.tpl");
		$this->open_log_session();
		$this->close_log_session();

		foreach($this->log as $lid => $lit)
		{
			$this->vars(array(
				"time" => $this->time2date($lit["time"], 2),
				"event" => $lit["event"]["event"],
				"view" => $this->mk_my_orb("show_log_entry", array("id" => $lid))
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"sched_url" => $this->mk_my_orb("show")
		));
		return $this->parse();
	}

	function show_log_entry($arr)
	{
		extract($arr);
		$this->read_template("log_entry.tpl");
		$this->open_log_session();
		$this->close_log_session();
		
		$this->vars(array(
			"time" => $this->time2date($this->log[$id]["time"],2),
			"event" => $this->log[$id]["event"]["event"],
			"response" => htmlentities($this->log[$id]["response"]),
			"sched_url" => $this->mk_my_orb("show"),
			"log_url" => $this->mk_my_orb("show_log"),
		));

		return $this->parse();
	}

	////
	// !Displays UI for adding or editing a scheduler object
	function add_sched($args = array())
	{
		$caption = "Lisa uus scheduler";
		$this->mk_path($args["parent"],"Lisa uus scheduler");
		
		$this->read_template("add.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("parent" => $args["parent"])),
		));

		return $this->parse();

	}

	function change_sched($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		
		$this->read_template("change.tpl");

		if (is_array($obj["meta"]["sched_objects"]))
		{
			$clids = $this->get_clids();
			$q = sprintf("SELECT * FROM objects WHERE oid IN (%s)",join(",",$obj["meta"]["sched_objects"]));
			$this->db_query($q);
			load_vcl("table");
			$t = new aw_table(array("prefix" => "schedsel"));
			$t->parse_xml_def($this->cfg["basedir"]."/xml/scheduler/selected_objs.xml");
			while($row = $this->db_next())
			{
				$met = $clids[$row["class_id"]]["action"];
				$row["method"] = "<select name='action'><option>$met</select>";
				$row["class_id"] = $clids[$row["class_id"]]["name"];
				$t->define_data($row);
			};

			$this->vars(array(
				"table" => $t->draw(),
			));
		};

		$this->mk_path($obj["parent"],"$obj[name] / Muuda scheduleri");

		// we have to provide fields for entering name and comment
		// for the scheduler object.

		$this->vars(array(
			"search_objs" => $this->mk_my_orb("search_objs",array("id" => $id)),
			"set_time" => $this->mk_my_orb("set_time",array("id" => $id)),
			"name" => $obj["name"],
			"comment" => $obj["comment"],
			"class" => $classes,
			"uid" => $obj["meta"]["login_uid"],
			"password" => $obj["meta"]["login_password"],
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));

		// and then allow for searching different objects, which could
		// _then_ be selected for importing.

		return $this->parse();
	}

	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($parent)
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"comment" => $comment,
				"class_id" => CL_SCHEDULER,
			));
		}
		else
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"login_uid" => $uid,
					"login_password" => $password,
				),
			));
		};


		return $this->mk_my_orb("change",array("id" => $id));
	}

	function search_objs($args = array())
	{
		extract($args);
		$this->quote($args);

		if ($save)
		{
			if (is_array($chk))
			{
				$this->upd_object(array(
					"oid" => $id,
					"metadata" => array("sched_objects" => $chk),
				));
			};

			return $this->mk_my_orb("change",array("id" => $id));
		}
		
		$obj = $this->get_object($id);

		$clids = $this->get_clids();
		
		$this->read_template("search_objs.tpl");

		// now, if name and clid are both set in the arguments, then we perform the actual
		// search
		if (isset($clid) && is_array($clid))
		{
			load_vcl("table");
			$t = new aw_table(array("prefix" => "schedobj"));
			$t->parse_xml_def($this->cfg["basedir"]."/xml/scheduler/search_objs.xml");

			$q = sprintf("SELECT oid,name,class_id,modified,modifiedby FROM objects WHERE name like '%%%s%%' AND class_id IN (%s) AND status = 2 AND lang_id = %d AND site_id = %d",$name,join(",",$clid),aw_global_get("lang_id"),$this->cfg["site_id"]);
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$row["class_id"] = $clids[$row["class_id"]]["name"];
				$row["select"] = "<input type='checkbox' name='chk[$row[oid]]' value='$row[oid]'>";
				$t->define_data($row);
			};

			$tb = $t->draw();

			$this->vars(array(
				"table" => $t->draw(),
			));
		}

		$chlink = $this->mk_my_orb("change",array("id" => $id));

		$this->mk_path($obj["parent"],"<a href='$chlink'>$obj[name]</a> / Muuda scheduleri objekte");
		
		$classes = "";
		foreach($clids as $_clid => $defs)
		{
			$this->vars(array(
				"cvalue" => $_clid,
				"cname" => $defs["name"],
				"checked" => checked(is_array($clid) ? in_array($_clid,$clid) : 0),
			));

			$classes .= $this->parse("class");
		};

		$this->vars(array(
			"name" => $name,
			"class" => $classes,
			"reforb" => $this->mk_reforb("search_objs",array("id" => $id,"no_reforb" => 1)),
		));

		return $this->parse();
	}

	function get_clids()
	{
		$iface = get_instance("interface");
		// fetch the list of all interfaces that can be scheduled
		$rv = $iface->get_if(array("name" => "scheduled"));

		// id => name pairs of all class_ids that can be scheduled
		$clids = array();


		// now figure out methods for each of those
		foreach($rv as $clid => $scd_classes)
		{
			foreach($scd_classes as $scd_method)
			{
				$clids[$scd_method["clid"]]["name"] = $scd_method["name"];
				$clids[$scd_method["clid"]]["action"] = $scd_method["action"];
				$clids[$scd_method["clid"]]["clid"] = $scd_method["id"];
			};

		};
		return $clids;
	}

	function set_time($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
			
		$clids = $this->get_clids();
		
		if (is_array($obj["meta"]["sched_objects"]) && !$cycle)
		{
			$clids = $this->get_clids();
			$q = sprintf("SELECT * FROM objects WHERE oid IN (%s)",join(",",$obj["meta"]["sched_objects"]));
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$ref = $clids[$row["class_id"]];
				$met = $ref["action"];
				$_cl = $ref["clid"];
				$link = $this->mk_my_orb($met,array("id" => $row["oid"]),$_cl);
				$this->add(array("event" => $link,"rep_id" => $id, "uid" => $obj["meta"]["login_uid"],"password" => $obj["meta"]["login_password"]));
			};
		};

		$this->read_template("set_time.tpl");
		$ce = get_instance("cal_event");
		$html = $ce->repeaters(array(
			"id" => $id,
			"cycle" => $cycle,
			"hide_menubar" => "hell_yes",
			"use_class" => "scheduler",
			"use_method" => "set_time",
    ));	
		$this->vars(array(
			"table" => $html,
		));
		$chlink = $this->mk_my_orb("change",array("id" => $id));
		$schedlink = $this->mk_my_orb("set_time",array("id" => $id));
		$this->mk_path($obj["parent"],"<a href='$chlink'>$obj[name]</a> / <a href='$schedlink'>M‰‰ra scheduleri kellaajad</a>");
		return $this->parse();
	}

}
?>
