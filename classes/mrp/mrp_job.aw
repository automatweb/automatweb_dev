<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_job.aw,v 1.24 2005/03/22 11:13:10 kristo Exp $
// mrp_job.aw - Tegevus
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_MRP_JOB, on_delete_job)
EMIT_MESSAGE(MSG_MRP_RESCHEDULING_NEEDED)

@classinfo syslog_type=ST_MRP_JOB relationmgr=yes no_status=1

@tableinfo mrp_job index=oid master_table=objects master_index=oid


@default group=general
@default table=objects
	@property name type=text store=no
	@caption Nimi

	@property comment type=textarea
	@caption Kommentaar


@groupinfo data caption="Andmed"
@default group=data
@default table=mrp_job
	@property length type=textbox
	@caption Töö pikkus (h)

	@property pre_buffer type=textbox
	@caption Eelpuhveraeg (h)

	@property post_buffer type=textbox
	@caption Järelpuhveraeg (h)

	@property resource type=text
	@caption Ressurss

	@property project type=hidden
	@caption Projekt

	@property exec_order type=hidden
	@caption Töö jrk. nr.

	@property prerequisites type=textbox
	@comment Komaga eraldatud
	@caption Eeldustööd

	@property starttime type=datetime_select
	@caption Plaanitud töösseminekuaeg

	@property planned_length type=text
	@caption Planeeritud kestus (h)

	@property state type=text
	@caption Staatus

	@property job_toolbar type=toolbar no_caption=1 store=no

// --------------- RELATION TYPES ---------------------

@reltype MRP_RESOURCE value=1 clid=CL_MRP_RESOURCE
@caption Tööks kasutatav ressurss

@reltype MRP_PROJECT value=2 clid=CL_MRP_CASE
@caption Projekt

//@reltype MRP_PRIORITY value=3 clid=CL_PRIORITY
//@caption Töö prioriteet


*/

/*

CREATE TABLE `mrp_job` (
  `oid` int(11) NOT NULL default '0',
  `length` int(10) unsigned NOT NULL default '0',
  `planned_length` int(10) unsigned NOT NULL default '0',
  `resource` int(11) unsigned default NULL,
  `exec_order` smallint(5) unsigned NOT NULL default '1',
  `project` int(11) unsigned default NULL,
  `starttime` int(10) unsigned default NULL,
  `prerequisites` char(255) default NULL,
  `state` tinyint(2) unsigned default '1',
  `pre_buffer` int(10) unsigned default NULL,
  `post_buffer` int(10) unsigned default NULL,

	PRIMARY KEY  (`oid`),
	UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

### resource types
define ("MRP_RESOURCE_SCHEDULABLE", 1);
define ("MRP_RESOURCE_NOT_SCHEDULABLE", 2);
define ("MRP_RESOURCE_SUBCONTRACTOR", 3);

### states
define ("MRP_STATUS_NEW", 1);
define ("MRP_STATUS_PLANNED", 2);
define ("MRP_STATUS_INPROGRESS", 3);
define ("MRP_STATUS_ABORTED", 4);
define ("MRP_STATUS_DONE", 5);
define ("MRP_STATUS_LOCKED", 6);
define ("MRP_STATUS_PAUSED", 7);
define ("MRP_STATUS_DELETED", 8);
define ("MRP_STATUS_ONHOLD", 9);
define ("MRP_STATUS_ARCHIVED", 10);

define ("MRP_STATUS_RESOURCE_AVAILABLE", 10);
define ("MRP_STATUS_RESOURCE_INUSE", 11);
define ("MRP_STATUS_RESOURCE_OUTOFSERVICE", 12);

### misc
define ("MRP_DATE_FORMAT", "j/m/Y H.i");

class mrp_job extends class_base
{
	function mrp_job ()
	{
		$this->states = array(
			MRP_STATUS_NEW => t("Uus"),
			MRP_STATUS_PLANNED => t("Töösse planeeritud"),
			MRP_STATUS_INPROGRESS => t("Töös"),
			MRP_STATUS_ABORTED => t("Katkestatud"),
			MRP_STATUS_DONE => t("Valmis"),
			MRP_STATUS_LOCKED => t("Lukustatud"),
			MRP_STATUS_PAUSED => t("Paus"),
			MRP_STATUS_DELETED => t("Kustutatud"),
			MRP_STATUS_ONHOLD => t("Plaanist väljas"),
			MRP_STATUS_ARCHIVED => t("Arhiveeritud"),
		);

		$this->init(array(
			"tpldir" => "mrp/mrp_job",
			"clid" => CL_MRP_JOB,
		));
	}

	function get_property ($arr)
	{
		$prop =& $arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		switch($prop["name"])
		{
			case "name":
				$project_id = $this_object->prop ("project");
				$resource_id = $this_object->prop ("resource");

				if (is_oid ($project_id) and is_oid ($resource_id))
				{
					$project = obj ($project_id);
					$resource = obj ($resource_id);
					$project_name = $project->name () ? $project->name () : "...";
					$resource_name = $resource->name () ? $resource->name () : "...";
				}
				else
				{
					$project_name = $resource_name = "...";
				}

				$prop["value"] = $project_name . " - " . $resource_name;
				break;

			case "resource":
				$resource = is_oid ($prop["value"]) ? obj ($prop["value"]) : false;
				$prop["value"] = $resource ? $resource->name () : t("Ressurss määramata");
				break;

			case "length":
			case "planned_length":
			case "pre_buffer":
			case "post_buffer":
				$prop["value"] = round (($prop["value"] / 3600), 2);
				break;

			case "state":
				$prop["value"] = $this->states[$prop["value"]] ? $this->states[$prop["value"]] : t("Määramata");
				break;

			case "job_toolbar":
				if ($this_object->prop ("state") != MRP_STATUS_DONE)
				{
					$this->create_job_toolbar ($arr);
				}
				break;
		}

		return $retval;
	}

	function set_property ($arr = array())
	{
		$prop =& $arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "length":
			case "pre_buffer":
			case "post_buffer":
				$prop["value"] = round ($prop["value"] * 3600);
				break;

		}
		return $retval;
	}

	function &get_current_workspace ($arr)
	{
		if ($arr["new"])
		{
			$workspace = obj ($arr["request"]["mrp_workspace"]);
		}
		else
		{
			$this_object =& $arr["obj_inst"];
			$connections = $this_object->connections_from(array ("type" => "RELTYPE_MRP_PROJECT", "class_id" => CL_MRP_CASE));

			foreach ($connections as $connection)
			{
				$project = $connection->to();
				$project_connections = $project->connections_from(array ("type" => "RELTYPE_MRP_OWNER", "class_id" => CL_MRP_WORKSPACE));

				foreach ($project_connections as $project_connection)
				{
					$workspace = $project_connection->to();
					break;
				}

				break;
			}
		}

		return $workspace;
	}

	function &get_project ($arr)
	{
		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}

		if (is_object ($arr["obj_inst"]))
		{
			$this_object =& $arr["obj_inst"];
		}

		$connections = $this_object->connections_from(array ("type" => "RELTYPE_MRP_PROJECT", "class_id" => CL_MRP_CASE));

		foreach ($connections as $connection)
		{
			$project = $connection->to();
			break;
		}

		return $project;
	}

	function create_job_toolbar ($arr = array())
	{
		$toolbar =& $arr["prop"]["toolbar"];
		$this_object =& $arr["obj_inst"];

		if ($this_object->prop ("state") != MRP_STATUS_INPROGRESS)
		{
			if ($this->can_start(array("job" => $this_object->id())))
			{
				$toolbar->add_button(array(
					"name" => "start",
					//"img" => "new.gif",
					"tooltip" => t("Alusta"),
					"action" => "start",
				));
			}
		}

		if ($this_object->prop ("state")  == MRP_STATUS_INPROGRESS)
		{
			$toolbar->add_button(array(
				"name" => "done",
				//"img" => "done.gif",
				"tooltip" => t("Valmis"),
				"action" => "done",
			));

			$toolbar->add_button(array(
				"name" => "abort",
				//"img" => "abort.gif",
				"tooltip" => t("Katkesta"),
				//"action" => "abort",
				"url" => "#",
				"onClick" => "if (document.changeform.pj_change_comment.value.replace(/\\s+/, '') != '') { submit_changeform('abort') } else { alert('" . t("Kommentaar peab olema t&auml;idetud!") . "'); }"
			));

			if ($this_object->prop("state") != MRP_STATUS_PAUSED)
			{
				$toolbar->add_button(array(
					"name" => "pause",
					//"img" => "pause.gif",
					"tooltip" => t("Paus"),
					"action" => "pause",
				));

				$toolbar->add_button(array(
					"name" => "end_shift",
					//"img" => "end_shift.gif",
					"tooltip" => t("Vahetuse l&otilde;pp"),
					"action" => "end_shift",
				));
			}
			else
			{
				$toolbar->add_button(array(
					"name" => "scontinue",
					//"img" => "continue.gif",
					"tooltip" => t("J&auml;tka"),
					"action" => "scontinue",
				));
			}
		}
	}

/**
	@attrib name=start
	@param id required type=int
**/
	function start ($arr)
	{
		$errors = false;

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			return false;
		}

		$project =& $this->get_project ($arr);
		$applicable_project_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_INPROGRESS,
		);
		$applicable_job_states = array (
			MRP_STATUS_PLANNED,
		);

		### check if prerequisites are done
		$prerequisites = explode (",", $this_object->prop ("prerequisites"));
		$prerequisites_done = true;

		foreach ($prerequisites as $prerequisite_oid)
		{
			if (is_oid ($prerequisite_oid))
			{
				$prerequisite = obj ($prerequisite_oid);

				if (((int) $prerequisite->prop ("state")) !== MRP_STATUS_DONE)
				{
					$prerequisites_done = false;
				}
			}
			else
			{
				return false;
			}
		}

		$mrp_resource = get_instance(CL_MRP_RESOURCE);
		$resource_is_reserved = $mrp_resource->start_job(array("resource" => $this_object->prop("resource")));

		if ( (in_array ($project->prop ("state"), $applicable_project_states)) and (in_array ($this_object->prop ("state"), $applicable_job_states)) and $prerequisites_done and $resource_is_reserved )
		{
			if (((int) $this_object->prop ("exec_order")) === 1)
			{
				### start project
				$mrp_case = get_instance(CL_MRP_RESOURCE);
				$project_start = $mrp_case->start(array("id" => $project->id ()));

				if (!$project_start)
				{
					$errors = true;
				}
			}

			### set project state & progress
			$progress = time () + $this_object->prop ("planned_length");
			$project->set_prop ("state", MRP_STATUS_INPROGRESS);
			$project->set_prop ("progress", $progress);

			### start job
			$this_object->set_prop ("state", MRP_STATUS_INPROGRESS);
		}
		else
		{
			$errors = true;
		}

		if ($errors)
		{
			### free resource and exit
			$mrp_resource->stop_job(array("resource" => $this_object->prop("resource")));
			return false;
		}
		else
		{
			### log
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);

			### all went well, save and say OK
			$this_object->save ();
			$project->save ();
			return true;
		}
	}

/**
	@attrib name=done
	@param id required type=int
**/
	function done ($arr)
	{
		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			return false;
		}

		$project = $job->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (in_array ($this_object->prop ("state"), $applicable_states))
		{
			### finish job
			$this_object->set_prop ("state", MRP_STATUS_DONE);
			$this_object->save ();

			### set resource as free
			$resource = get_instance(CL_MRP_RESOURCE);
			$resource->stop_job(array("resource" => $this_object->prop("resource")));

			### log job change
			$ws = get_instance (CL_MRP_WORKSPACE);
			$ws->mrp_log(
				$this_object->prop ("project"),
				$this_object->id (),
				"T&ouml;&ouml; ".$this_object->name() . " staatus muudeti ".$this->states[$this_object->prop("state")],
				$arr["pj_change_comment"]
			);

			### finish project if this was the last job
			$list = new object_list (array (
				"class_id" => CL_MRP_JOB,
				"project" => $project->id (),
				"state" => MRP_STATUS_DONE,
			));
			$done_jobs = $list->count ();

			$list = new object_list (array (
				"class_id" => CL_MRP_JOB,
				"project" => $project->id (),
			));
			$all_jobs = $list->count ();

			if ($done_jobs === $all_jobs)
			{
				### finish project
				$mrp_case = get_instance(CL_MRP_RESOURCE);
				$mrp_case->finish(array("id" => $project->id ()));
			}
			else
			{
				### update progress
				$project->set_prop ("progress", time ());
				$project->save ();
			}

			return true;
		}
		else
		{
			return false;
		}
	}

/**
	@attrib name=abort
	@param id required type=int
**/
	function abort ($arr)
	{
		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			return false;
		}

		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PAUSED,
		);

		if (in_array ($this_object->prop ("state"), $applicable_states))
		{
			### abort job
			$this_object->set_prop ("state", MRP_STATUS_ABORTED);
			$this_object->save ();

			### set resource as free
			$resource = get_instance(CL_MRP_RESOURCE);
			$resource->stop_job(array("resource" => $this_object->prop("resource")));

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);

			return true;
		}
		else
		{
			return false;
		}
	}

/**
	@attrib name=pause
	@param id required type=int
**/
	function pause($arr)
	{
		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			return false;
		}

		$project = $job->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (in_array ($this_object->prop ("state"), $applicable_states))
		{
			### pause job
			$this_object->set_prop ("state", MRP_STATUS_PAUSED);
			$this_object->save ();

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);
			$project->save ();

			### log event
			$ws = get_instance (CL_MRP_WORKSPACE);
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);

			return true;
		}
		else
		{
			return false;
		}
	}

/**
	@attrib name=scontinue
	@param id required type=int
**/
	function scontinue($arr)
	{
		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			return false;
		}

		$project = $job->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_project_states = array (
			MRP_STATUS_INPROGRESS,
			// MRP_STATUS_ONHOLD,
		);
		$applicable_job_states = array (
			MRP_STATUS_PAUSED,
		);

		if ( (in_array ($project->prop ("state"), $applicable_project_states)) and (in_array ($this_object->prop ("state"), $applicable_job_states)) )
		{
			### continue job
			$this_object->set_prop ("state", MRP_STATUS_INPROGRESS);
			$this_object->save ();

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);
			$project->save ();

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);

			return true;
		}
		else
		{
			return false;
		}
	}

/**
	@attrib name=end_shift
	@param id required type=int
**/
	function end_shift($arr)
	{
		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			return false;
		}

		$project = $job->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (in_array ($this_object->prop ("state"), $applicable_states))
		{
			### pause job
			$this_object->set_prop ("state", MRP_STATUS_PAUSED);
			$this_object->save ();

			### update progress
			$progress = max ($project->prop ("progress"), time ());
			$project->set_prop ("progress", $progress);
			$project->save ();

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti Vahetuse l&otilde;pp", $arr["pj_change_comment"]);

			### log out user
			$u = get_instance("users");
			$u->orb_logout();

			return true;
		}
		else
		{
			return false;
		}
	}

/**
    @attrib name=can_start
	@param job required type=int
**/
	function can_start ($arr)
	{
		if (is_oid ($arr["job"]))
		{
			$job = obj ($arr["job"]);
		}
		else
		{
			return false;
		}

		### check if project is ready to go on
		$project = $job->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PLANNED,
		);

		if (!in_array ($project->prop ("state"), $applicable_states))
		{
			return false;
		}

		### check if job can start
		$applicable_states = array (
			MRP_STATUS_PLANNED,
		);

		if (!in_array ($job->prop ("state"), $applicable_states))
		{
			return false;
		}

		### check if resource is available
		$resource = obj($job->prop("resource"));
		$applicable_states = array (
			MRP_STATUS_RESOURCE_AVAILABLE,
		);

		if (!in_array ($resource->prop ("state"), $applicable_states))
		{
			return false;
		}

		### check if all prerequisite jobs are done
		if (trim ($job->prop ("prerequisites")))
		{
			$prerequisites = explode (",", $job->prop ("prerequisites"));
			$applicable_states = array (
				MRP_STATUS_DONE,
			);

			foreach ($prerequisites as $prerequisite_oid)
			{
				$prerequisite = obj ($prerequisite_oid);

				if (!in_array ($prerequisite->prop ("state"), $applicable_states))
				{
					return false;
				}
			}
		}

		return true;
	}

	function on_delete_job ($arr)
	{
		$job_id = (int) $arr["oid"];
		$job = obj ($job_id);
		$job->set_prop ("state", MRP_STATUS_DELETED);
		$job->save ();

		### get project for deleted job
		$project = $job->get_first_obj_by_reltype ("RELTYPE_MRP_PROJECT");

		if (!$project)
		{
			return;
		}

		### set successive jobs' prerequisites equal to deleted job's prerequisites
		$prerequisites = explode (",", $job->prop ("prerequisites"));
		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"project" => $project->id (),
			"state" => new obj_predicate_not (MRP_STATUS_DELETED),
		));

		for ($successive_job =& $list->begin (); !$list->end (); $successive_job =& $list->next ())
		{
			$successor_prerequisites = explode (",", $successive_job->prop ("prerequisites"));

			if (in_array ($job_id, $successor_prerequisites))
			{
				$successor_prerequisites = array_merge ($successor_prerequisites, $prerequisites);
				$successor_prerequisites = array_unique ($successor_prerequisites);
				$keys = array_keys ($successor_prerequisites, $job_id);
				unset ($successor_prerequisites[$keys[0]]);
				$successor_prerequisites = implode (",", $successor_prerequisites);
				$successive_job->set_prop ("prerequisites", $successor_prerequisites);
			}
		}

		### correct project's job order if project wasn't deleted
		$this->do_orb_method_call (array (
			"action" => "order_jobs",
			"class" => "mrp_case",
			"params" => array (
				"oid" => $project->id ()
			)
		));
	}
}

?>
