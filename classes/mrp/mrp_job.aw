<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_job.aw,v 1.15 2005/03/11 09:09:38 voldemar Exp $
// mrp_job.aw - Tegevus
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_MRP_JOB, on_delete_job)
EMIT_MESSAGE(MSG_MRP_RESCHEDULING_NEEDED)

@classinfo syslog_type=ST_MRP_JOB relationmgr=yes no_status=1

@tableinfo mrp_job index=oid master_table=objects master_index=oid


@default group=general
@default table=objects
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
	@caption Eeldustööd

	@property starttime type=datetime_select
	@caption Plaanitud töösseminekuaeg

	@property planned_length type=text
	@caption Planeeritud kestus (h)

	@property state type=text
	@caption Staatus

	@property sub_state type=hidden

@default field=meta
@default method=serialize
	property started type=text
	caption Alustatud

	property finished type=text
	caption Lõpetatud

	property aborted type=checkbox
	caption Katkestatud

	property abort_comment type=textarea
	caption Katkestamise põhjus


//// old
	property job_toolbar type=toolbar no_caption=1 store=no

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
define ("MRP_STATUS_OVERDUE", 7);
define ("MRP_STATUS_DELETED", 8);
define ("MRP_STATUS_ONHOLD", 9);

### sub states
define ("MRP_SUBSTATUS_PAUSED", 9);

### misc
define ("MRP_DATE_FORMAT", "j/m/Y H.i");

class mrp_job extends class_base
{
	var $states = array (
		MRP_STATUS_NEW => "Uus",
		MRP_STATUS_PLANNED => "Töösse planeeritud",
		MRP_STATUS_INPROGRESS => "Töös",
		MRP_STATUS_ABORTED => "Katkestatud",
		MRP_STATUS_DONE => "Valmis",
	);

	function mrp_job ()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_job",
			"clid" => CL_MRP_JOB,
		));
	}

	function get_property ($arr)
	{
		$prop =& $arr["prop"];
		$retval = PROP_OK;
		$this_object = $arr["obj_inst"];

		switch($prop["name"])
		{
			case "resource":
				$resource = is_oid ($prop["value"]) ? obj ($prop["value"]) : false;
				$prop["value"] = $resource ? $resource->name () : "Ressurss määramata";
				break;

			case "length":
			case "planned_length":
			case "pre_buffer":
			case "post_buffer":
				$prop["value"] = round (($prop["value"] / 3600), 2);
				break;

			case "state":
				$prop["value"] = $this->states[$prop["value"]] ? $this->states[$prop["value"]] : "Määramata";
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
			$this_object = $arr["obj_inst"];
			$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_PROJECT, "class_id" => CL_MRP_CASE));

			foreach ($connections as $connection)
			{
				$project = $connection->to();
				$project_connections = $project->connections_from(array ("type" => RELTYPE_MRP_OWNER, "class_id" => CL_MRP_WORKSPACE));

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

		$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_PROJECT, "class_id" => CL_MRP_CASE));

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
		$this_object = $arr["obj_inst"];

		if ($this_object->prop ("state") != MRP_STATUS_INPROGRESS)
		{
			$toolbar->add_button(array(
				"name" => "start",
				//"img" => "new.gif",
				"tooltip" => "Alusta",
				"action" => "start",
			));
		}

		if ($this_object->prop ("state")  == MRP_STATUS_INPROGRESS)
		{
			$toolbar->add_button(array(
				"name" => "done",
				//"img" => "done.gif",
				"tooltip" => "Valmis",
				"action" => "done",
			));

			$toolbar->add_button(array(
				"name" => "abort",
				//"img" => "abort.gif",
				"tooltip" => "Katkesta",
				//"action" => "abort",
				"url" => "#",
				"onClick" => "if (document.changeform.pj_change_comment.value.replace(/\\s+/, '') != '') { submit_changeform('abort') } else { alert('Kommentaar peab olema t&auml;idetud!'); }"
			));

			if ($this_object->prop("sub_state") != MRP_SUBSTATUS_PAUSED)
			{
				$toolbar->add_button(array(
					"name" => "pause",
					//"img" => "pause.gif",
					"tooltip" => "Paus",
					"action" => "pause",
				));

				$toolbar->add_button(array(
					"name" => "end_shift",
					//"img" => "end_shift.gif",
					"tooltip" => "Vahetuse l&otilde;pp",
					"action" => "end_shift",
				));
			}
			else
			{
				$toolbar->add_button(array(
					"name" => "scontinue",
					//"img" => "continue.gif",
					"tooltip" => "J&auml;tka",
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
		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}
		else
		{
			return ERROR;///!!! ...
		}

		$project =& $this->get_project ($arr);
		$log = $project->prop("state") != MRP_STATUS_INPROGRESS;
		$project->set_prop ("state", MRP_STATUS_INPROGRESS);
		$project->save ();


		$ws = get_instance(CL_MRP_WORKSPACE);
		if ($log)
		{
			$ws->mrp_log($this_object->prop("project"), NULL, "Projekt l&auml;ks t&ouml;&ouml;sse");
		}

		$this_object->set_prop ("state", MRP_STATUS_INPROGRESS);
		$this_object->save ();

		$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);

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
			return ERROR;///!!! ...
		}

		$this_object->set_prop ("state", MRP_STATUS_DONE);
		$this_object->save ();

		$ws = get_instance(CL_MRP_WORKSPACE);
		$ws->mrp_log(
			$this_object->prop("project"),
			$this_object->id(),
			"T&ouml;&ouml; ".$this_object->name().
				" staatus muudeti ".$this->states[$this_object->prop("state")],
			$arr["pj_change_comment"]
		);

		### set status DONE for whole project if this was the last job
		$project =& $this->get_project ($arr);
		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"project" => $project->id (),
			"exec_order" => new obj_predicate_compare (OBJ_COMP_GREATER, $this_object->prop ("exec_order")),
		));
		$next_jobs = $list->count ();

		if (!$next_jobs)
		{
			$log = $project->prop("state") != MRP_STATUS_DONE;

			$project->set_prop ("state", MRP_STATUS_DONE);
			$project->save ();

			if ($log)
			{
				$ws->mrp_log(
					$project->id(),
					NULL,
					"Projekt l&otilde;petati"
				);
			}
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
			return ERROR;///!!! ...
		}

		// $project =& $this->get_project ($arr);
		// $project->set_prop ("state", MRP_STATUS_ABORTED);
		// $project->save ();
//!!! siin vist tuleks panna projektile ka abort aga ainult siis kui leidub t8id mis eeldavad antud t88 valmimist
//!!! siin tuleb ka abordikommentaare n6uda kuidagi

		$this_object->set_prop ("state", MRP_STATUS_ABORTED);
		$this_object->save ();

		$ws = get_instance(CL_MRP_WORKSPACE);
		$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("state")], $arr["pj_change_comment"]);
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
			return ERROR;///!!! ...
		}

		$this_object->set_prop ("sub_state", MRP_SUBSTATUS_PAUSED);
		$this_object->save ();

		$ws = get_instance(CL_MRP_WORKSPACE);
		$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("sub_state")], $arr["pj_change_comment"]);
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
			return ERROR;///!!! ...
		}

		$this_object->set_prop ("sub_state", 0);
		$this_object->save ();

		$ws = get_instance(CL_MRP_WORKSPACE);
		$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti ".$this->states[$this_object->prop("sub_state")], $arr["pj_change_comment"]);
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
			return ERROR;///!!! ...
		}

		$this_object->set_prop ("sub_state", MRP_SUBSTATUS_PAUSED);
		$this_object->save ();

		$ws = get_instance(CL_MRP_WORKSPACE);
		$ws->mrp_log($this_object->prop("project"), $this_object->id(), "T&ouml;&ouml; ".$this_object->name()." staatus muudeti Vahetuse l&otilde;pp", $arr["pj_change_comment"]);

		// log out user
		$u = get_instance("users");
		$u->orb_logout();
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
