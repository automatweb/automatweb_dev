<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/scheduler.aw,v 2.28 2004/12/10 12:25:33 ahti Exp $
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

		if ($uid == "")
		{
			$event = str_replace("automatweb/", "", $event);
		}

		if ($time)
		{
			$event_id = md5($event);
			$this->evnt_add($time, $event, $uid, $password, 0, $event_id, $sessid);
		}

		if ($rep_id)
		{
			$now = time();
			// XXX: convert to storage as soon as possible
			$ltime = 0;
			$q = "SELECT * FROM recurrence WHERE recur_id = '${rep_id}' AND recur_start >= '${now}' ORDER BY recur_start LIMIT 20";
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$this->evnt_add($row["recur_start"],$event,$uid,$password,$rep_id,$event_id);
				$ltime = $row["recur_start"];
				//arr($row);
			};
			// if we use a repeater for scheduling events we get a bunch of times and add the events for those times
			//$pl = get_instance(CL_PLANNER);
			// siin tuleb lugeda sisse otse repeaters tabelist asju
			/*
			$reps = $pl->get_events(array( 
				"start" => time(), 
				"limit" => 20,
				"index_time" => true,
				"event" => $rep_id, 
				"end" => time()+24*3600*300
			));
			*/
			/*
			if (is_array($reps))
			{
				foreach($reps as $time => $_e)
				{
					$this->evnt_add($time, $event, $uid, $password, $rep_id, $event_id);
					$ltime = $time;
				}
			};
			*/
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

	/** updates the scheduled events that use repeater $id 
		
		@attrib name=update_repeaters params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
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
		$pl = get_instance(CL_PLANNER);
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

	function evnt_add($time, $event, $uid = "", $password = "", $rep_id = 0, $event_id = "", $sessid ="")
	{
		$this->open_session();
		$found = false;
		if (substr($event,0,4) != "http")
		{
			$event = aw_ini_get("baseurl") . $event;
		};
		// try and remove all existing scheduling information for this event
		if (is_array($this->repdata))
		{
			// modifying an array while looping over it can lead to unexcected results
			$tmp = $this->repdata;
			foreach($tmp as $key => $evnt)
			{
				if ($evnt["event"] == $event && $evnt["uid"] == $uid)
				{
					//unset($this->repdata[$key]);
				}
			}
		}

		if (empty($event_id))
		{
			// that should be enough to make sure that 2 requests to one url
			// do not overlap
			$event_id = md5($event);
		};

		// (re)add the event to the queue
		$this->repdata[] = array(
			"time" => $time,
			"event" => $event,
			"event_id" => $event_id,
			"uid" => $uid,
			"password" => $password,
			"rep_id" => $rep_id,
			"sessid" => $sessid
		);

		$this->close_session(true);
	}

	function open_session()
	{
		$this->session_fp = fopen($this->cfg["sched_file"], "a+");
		if (!$this->session_fp)
		{
			printf("cannot open %s for writing, please check permission",$this->cfg["sched_file"]);
			die();
		};
		flock($this->session_fp,LOCK_EX);

		fseek($this->session_fp,0,SEEK_SET);
		clearstatcache();
		$fc = fread($this->session_fp, filesize($this->cfg["sched_file"]));
		$this->repdata = aw_unserialize($fc);
		if (!is_array($this->repdata))
		{
			$this->repdata = array();
		}
		
		// also remove events that only have time set, but no url
		$nrd = array();
		foreach($this->repdata as $idx => $evnt)
		{
			if ($evnt["event"] != "")
			{
				$nrd[] = $evnt;
			}
		}
		$this->repdata = $nrd;
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

	/** this is where the event processing will take place 
		
		@attrib name=do_events params=name nologin="1" default="1"
		
		
		@returns
		
		
		@comment

	**/
	function do_events($arr)
	{
		extract($arr);
		set_time_limit(0);


		// read in all events
		$this->open_session();
		$this->close_session(true);

		// now do all events for which the time has expired
		$cp = $this->repdata;

		$now = time();

		foreach($cp as $evnt)
		{
			if (isset($evnt["time"]) && ($now > $evnt["time"]))
			{
				echo "exec event $evnt[event] <br />";
				$this->do_and_log_event($evnt);
			}

		}
	}

	function do_and_log_event($evnt)
	{
		ob_start();
		$file = $this->cfg["error_file"];
		touch($file);
		$fl = fopen($file, "wb");
		if ($evnt["event"] == "")
		{
			echo "no event specified, exiting<br />\n";
			fwrite($fl, ob_get_contents());
			fclose($fl);
			ob_end_flush();
			return;
		}
	
		// ok, here check if this event is already being processed
		$lockfilename = $this->cfg["lock_file"] . "." . $evnt["event_id"];
		if (file_exists($lockfilename) && (filemtime($lockfilename) > (time()-300)))
		//if (file_exists($lockfilename))
		{
			$pid = $this->get_file(array(
				"file" => $lockfilename,
			));
			if ($pid == getmypid())
			{
				// they are so just bail out
				echo "bailing for lock file ",$lockfilename,"<br />\n";
				fwrite($fl, ob_get_contents());
				fclose($fl);
				ob_end_flush();
				return;
			}
			else
			{
				echo "shouldn't but bailing for lock file ",$lockfilename,"<br />\n";
				fwrite($fl, ob_get_contents());
				fclose($fl);
				ob_end_flush();
				return;
			};
		}

		$this->put_file(array(
			"file" => $lockfilename,
			"content" => getmypid(),
		));

		touch($lockfilename);

		$evnt["event"];
		$ev_url = str_replace("/automatweb","",$evnt["event"]);

		preg_match("/^http:\/\/(.*)\//U",$ev_url, $mt);
		$url = $mt[1];

		echo "url = $url <br />";
		$awt = get_instance("protocols/file/http");
		$awt->handshake(array(
			"host" => $url,
			"sessid" => $evnt["sessid"]
		));

		if ($evnt["uid"] && $evnt["password"])
		{
			// we must log in 
			echo "logging in as $url $evnt[uid] $evnt[password] <br />";
			$awt->login(array("host" => $url, "uid" => $evnt["uid"], "password" => $evnt["password"]));
		}

		echo "do send req $url ",substr($ev_url,strlen("http://")+strlen($url))," <br />";
		$req = $awt->do_send_request(array("host" => $url, "req" => substr($ev_url,strlen("http://")+strlen($url))));
		print $req;
		echo "unlinking ",$lockfilename," <br />";
		unlink($lockfilename);

		if ($evnt["uid"] && $evnt["password"])
		{
			// be nice and logout
			$awt->logout(array("host" => $url));
		}

		// consider the event done, so log it and remove
		$this->log_event($evnt, $req);

		$this->remove($evnt);
		fwrite($fl, ob_get_contents());
		fclose($fl);
		ob_end_flush();
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
		$this->log = array();
		return;

		$this->log_fp = @fopen($this->cfg["log_file"], "a+");
		if (!$this->log_fp)
		{
			return false;
		};
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
		$this->log = array();
		return;
		if ($this->log_fp && $write)
		{
			ftruncate($this->log_fp,0);
			fseek($this->log_fp,0,SEEK_SET);

			fwrite($this->log_fp, aw_serialize($this->log,SERIALIZE_XML));
			fflush($this->log_fp);
		}

		if ($this->log_fp)
		{
			flock($this->log_fp,LOCK_UN);
			fclose($this->log_fp);
		};
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

	/** ui for scheduler 
		
		@attrib name=show params=name default="0"
		
		@param sortby optional
		@param sort_order optional
		
		@returns
		
		
		@comment

	**/
	function show($arr)
	{
		extract($arr);
		load_vcl("table");
		$t = new aw_table(array("prefix" => "schedshow"));
		$t->parse_xml_def($this->cfg["basedir"]."/xml/scheduler/show.xml");
		$this->read_template("list.tpl");
		$this->open_session();
		$this->close_session();

		print "<pre>";
		print_r($this->repdata);
		print "</pre>";

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

	/** shows log entries 
		
		@attrib name=show_log params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=show_log_entry params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
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

				
	// ah yes. Each time I'm saving the schedulering object, I need to create a new
	// record in the scheduler table. And that pretty much is it too
	
	//$this->add(array("event" => $link,"rep_id" => $id, "uid" => $obj["meta"]["login_uid"],"password" => $obj["meta"]["login_password"]));
}
?>
