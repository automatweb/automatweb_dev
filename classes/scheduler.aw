<?php

classload("planner","file","aw_test");
class scheduler extends aw_template
{
	function scheduler()
	{
		$this->init("scheduler");
		$this->file = new file;
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

		if ($time)
		{
			$this->evnt_add($time, $event, $uid, $password);
		}

		if ($rep_id)
		{
			// if we use a repeater for scheduling events we get a bunch of times and add the events for those times
			$pl = new planner;
			$reps = $pl->get_events(array( 
				"start" => time(), 
				"limit" => 20,
				"index_time" => true,
				"event" => $rep_id, 
				"end" => time()+24*3600*300
			));
			$ltime = 0;
			foreach($reps as $time => $_e)
			{
				$this->evnt_add($time, $event, $uid, $password, $rep_id);
				$ltime = $time;
			}
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
		$pl = new planner;
		$reps = $pl->get_events(array( 
			"start" => time(), 
			"limit" => 20,
			"index_time" => true,
			"event" => $id, 
			"end" => time()+24*3600*300
		));
		foreach($reps as $time => $_e)
		{
			$evdat["time"] = $time;
			$this->repdata[] = $evdat;
		}
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
		if (file_exists($this->cfg["lock_file"]) && (filectime($this->cfg["lock_file"]) > (time()-3600)))
		{
			// they are so just bail out
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
		unlink($this->cfg["lock_file"]);
	}

	function do_and_log_event($evnt)
	{
		preg_match("/^http:\/\/(.*)\//U",$evnt["event"], $mt);
		$url = $mt[1];

		echo "url = $url <br>";

		$awt = new aw_test;
		$awt->handshake(array("host" => $url));

		if ($evnt["uid"] && $evnt["password"])
		{
			// we must log in 
			echo "logging in as $url $evnt[uid] $evnt[password] <br>";
			$awt->login(array("host" => $url, "uid" => $evnt["uid"], "password" => $evnt["password"]));
		}

		echo "do send req $url ",substr($evnt["event"],strlen("http://")+strlen($url))," <br>";
		$req = $awt->do_send_request(array("host" => $url, "req" => substr($evnt["event"],strlen("http://")+strlen($url))));
//		echo "result = $req <br>";

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
		$this->read_template("list.tpl");
		$this->open_session();
		$this->close_session();

		foreach($this->repdata as $evnt)
		{
			$this->vars(array(
				"time" => $this->time2date($evnt["time"], 2),
				"event" => $evnt["event"]
			));
			$l.= $this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
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
}
?>