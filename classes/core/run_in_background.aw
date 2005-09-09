<?php

define("BG_OK", 0);
define("BG_DONE", 1);
define("BG_FORCE_CHECKPOINT", 2);

class run_in_background extends class_base
{
	function init($arr)
	{
		parent::init($arr);
		$this->lock_file = aw_ini_get("server.tmpdir")."/bg_run_".get_class($this).".run";
		$this->stop_file = aw_ini_get("server.tmpdir")."/bg_run_".get_class($this).".stop";
		$this->bg_checkpoint_steps = 100;
		$this->bg_log_steps = 5;
	}

	function bg_run_get_property_control($arr)
	{
		$prop =& $arr["prop"];
		$fn = $this->lock_file.".".$arr["obj_inst"]->id();
		if (file_exists($fn))
		{
			$prop["value"] = html::href(array(
				"caption" => t("Peata"),
				"url" => $this->mk_my_orb("bg_control", array(
					"id" => $arr["obj_inst"]->id(),
					"do" => "stop",
					"ru" => get_ru()
				))
			));
		}
		else
		{
			$prop["value"] = html::href(array(
				"caption" => t("K&auml;ivita"),
				"url" => $this->mk_my_orb("bg_control", array(
					"id" => $arr["obj_inst"]->id(),
					"do" => "start",
					"ru" => get_ru()
				))
			));
		}
		return PROP_OK;
	}

	function bg_run_get_property_status($arr)
	{
		$prop =& $arr["prop"];
		$fn = $this->lock_file.".".$arr["obj_inst"]->id();
		if (file_exists($fn))
		{
			$prop["value"] = nl2br($this->get_file(array("file" => $fn)));
		}
		else
		{
			$v = $arr["obj_inst"]->meta("bg_run_log");
			if ($v != "")
			{
				$prop["value"] = nl2br($v);
				return PROP_OK;
			}
			return PROP_IGNORE;
		}
	}

	/**

		@attrib name=bg_control
	
		@param id required type=int acl=view
		@param do required

		@param ru optional
	**/
	function bg_control($arr)
	{
		$o = obj($arr["id"]);

		$fn = $this->lock_file.".".$o->id();
		$s = get_instance("scheduler");
		switch($arr["do"])
		{
			case "start":
				$url = $this->mk_my_orb("bg_run", array("id" => $o->id()));
				$s->add(array(
					"event" => $url,
					"time" => time()-1
				));
				$o->set_meta("bg_run_log",t("Protsess k&auml;ivitub hiljemalt kahe minuti p&auml;rast"));
				$o->save();
				break;

			case "stop":
				touch($this->stop_file.".".$o->id());
				$this->put_file(array(
					"file" => $fn,
					"content" => t("Protsess l&otilde;petab t&ouml;&ouml;d")
				));
				break;
		}
		return $arr["ru"];
	}

	/**

		@attrib name=bg_check_scheduler nologin=1
		
	**/
	function bg_check_scheduler($arr)
	{
		$s = get_instance("scheduler");

		// make a list of all interested parties
		// check if they are in scheduler 
		$ol = new object_list(array(
			"class_id" => $this->clid,
			"site_id" => array(),
			"lang_id" => array()
		));
		foreach($ol->arr() as $o)
		{
			$url = $this->mk_my_orb("bg_run", array("id" => $o->id()));
			$s->remove(array(
				"event" => $url
			));

			// get the time it should run a
			if ($o->prop("bg_run_always"))
			{
				$s->add(array(
					"event" => $url,
					"time" => time()
				));
				$o->set_meta("bg_run_log",t("Protsess k&auml;ivitub hiljemalt kahe minuti p&auml;rast"));
				aw_disable_acl();
				$o->save();
				aw_restore_acl();
			}
			else
			{
				$recur = $o->get_first_obj_by_reltype("RELTYPE_RECURRENCE");
				if ($recur)
				{
					$s->add(array(
						"event" => $url,
						"rep_id" => $recur->id()
					));
					$o->set_meta("bg_run_log",t("Protsess k&auml;ivitub j&auml;rgmisel kordusel"));
					aw_disable_acl();
					$o->save();
					aw_restore_acl();
				}
			}
		}

		// add scheduler check every 20 min
		$s->add(array(
			"event" => $this->mk_my_orb("bg_check_scheduler", array()),
			"time" => time()+40*60
		));
	}

	/**

		@attrib name=bg_run nologin=1

		@param id required type=int 
	**/
	function bg_run($arr)
	{
		set_time_limit(9999);
		$o = obj($arr["id"]);
		if ($this->bg_is_running($o))
		{
			echo "process is already running, will not start another thread!<br>";
			return;
		}

		// run init
		if (method_exists($this, "bg_run_init"))
		{
			$this->bg_run_init($o);
		}

		// figure out if this is start or restart
		if ($o->meta("bg_run_state") == "started")
		{
			// and if it is restart, then run restore step
			if (method_exists($this, "bg_run_continue"))
			{
				$this->bg_run_continue($o);
			}
		}
		else
		{
			// mark state as started
			$o->set_meta("bg_run_state", "started");
			$o->set_meta("bg_run_start", time());
			aw_disable_acl();
			$o->save();
			aw_restore_acl();

		}

		// get first log entry
		if (method_exists($this, "bg_run_get_log_entry"))
		{
			$this->bg_write_log_entry($this->bg_run_get_log_entry($o), $o);
		}

		// run steps until done
		$iter = 0;
		while(true)
		{
			if (file_exists($this->stop_file.".".$o->id()))
			{
				$this->bg_do_halt($o);
			}

			$res = $this->bg_run_step($o);
			if ($res == BG_DONE)
			{
				break;
			}

			if (++$iter > $this->bg_checkpoint_steps || $res == BG_FORCE_CHECKPOINT)
			{
				if (method_exists($this, "bg_checkpoint"))
				{
					$this->bg_checkpoint($o);
					aw_disable_acl();
					$o->save();
					aw_restore_acl();
				}
			}

			if ($iter > $this->bg_log_steps)
			{
				if (method_exists($this, "bg_run_get_log_entry"))
				{
					$this->bg_write_log_entry($this->bg_run_get_log_entry($o), $o);
				}
			}
		}

		// call finalizer
		if (method_exists($this, "bg_run_finish"))
		{
			$this->bg_run_finish($o);
		}

		// mark run as donw
		$o->set_meta("bg_run_state", "done");
		aw_disable_acl();
		$o->save();
		aw_restore_acl();
		@unlink($this->lock_file.".".$o->id());
		die(t("all done"));
	}

	function bg_is_running($o)
	{
		$fn = $this->lock_file.".".$o->id();
		if (file_exists($fn))
		{
			if (filemtime($fn) > (time()-39*60))
			{
				return true;
			}
			unlink($fn);
		}
		return false;
	}

	function bg_write_log_entry($entry, $o)
	{
		// write status info to lock file
		$f = fopen($this->lock_file.".".$o->id(), "w");
		if ($f)
		{
			fwrite($f, $entry);
			fclose($f);
		}
	}

	function bg_do_halt($o)
	{
		echo "found stop flag, stopping scheduler <br>";
		unlink($this->stop_file.".".$o->id());
		unlink($this->lock_file.".".$o->id());

		$o->set_meta("bg_run_state", "done");

		if (method_exists($this, "bg_halt"))
		{
			$this->bg_halt($o);
		}

		aw_disable_acl();
		$o->save();
		aw_restore_acl();

		die(t("Halt"));
	}
}
?>