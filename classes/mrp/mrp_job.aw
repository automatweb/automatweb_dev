<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_job.aw,v 1.2 2004/12/08 12:23:32 voldemar Exp $
// mrp_job.aw - Tegevus
/*

@classinfo syslog_type=ST_MRP_JOB relationmgr=yes

@tableinfo mrp_job index=oid master_table=objects master_index=oid

@default group=general
	@property job_toolbar type=toolbar no_caption=1 store=no

@default table=mrp_job
	@property length type=textbox
	@caption Plaanitud kestus (h)

	@property buffer type=textbox
	@caption Puhveraeg (h)

	@property resource type=text
	@caption Ressurss

	@property project type=hidden
	@caption Projekt

	@property exec_order type=hidden
	@caption Töö jrk. nr.

	@property prerequisites type=textbox
	@caption Eeldustööd

	@property starttime type=datetime_select
	@caption Plaanitud töösseminekuaeg (timestamp)

	@property state type=radio
	@caption Staatus


@default table=objects
	@property comment type=textarea
	@caption Kommentaarid

@default field=meta
@default method=serialize
	@property started type=text
	@caption Alustatud

	@property finished type=text
	@caption Lõpetatud

	@property aborted type=checkbox
	@caption Katkestatud

	@property abort_comment type=textarea
	@caption Katkestamise põhjus


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
  `resource` int(10) unsigned default NULL,
  `exec_order` smallint(5) unsigned NOT NULL default '1',
  `project` int(10) unsigned NOT NULL default '0',
  `starttime` int(10) unsigned default NULL,
  `prerequisites` char(14) default NULL,
  `state` tinyint(2) unsigned default '1',
  `buffer` int(10) unsigned NOT NULL default '0',

	PRIMARY KEY  (`oid`),
	UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

### resource types
define ("MRP_RESOURCE_PHYSICAL", 1);
define ("MRP_RESOURCE_OUTSOURCE", 2);
define ("MRP_RESOURCE_GLOBAL_BUFFER", 3);

### states
define ("MRP_STATUS_NEW", 1);
define ("MRP_STATUS_PLANNED", 2);
define ("MRP_STATUS_INPROGRESS", 3);
define ("MRP_STATUS_ABORTED", 4);
define ("MRP_STATUS_DONE", 5);
define ("MRP_STATUS_LOCKED", 6);
define ("MRP_STATUS_OVERDUE", 7);

class mrp_job extends class_base
{
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
			case "buffer":
				$prop["value"] = $prop["value"] * 3600;
				break;

			case "state":
				$states = array (
					MRP_STATUS_NEW => "Uus",
					MRP_STATUS_PLANNED => "Töösse planeeritud",
					MRP_STATUS_INPROGRESS => "Töös",
					MRP_STATUS_ABORTED => "Katkestatud",
					MRP_STATUS_DONE => "Valmis",
				);
				$prop["value"] = $states[$prop["value"]] ? $states[$prop["value"]] : "Määramata";
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
			case "buffer":
				$prop["value"] = $prop["value"] / 3600;
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
				"img" => "start.gif",
				"tooltip" => "Alusta",
				"action" => "start",
			));
		}

		if ($this_object->prop ("state")  == MRP_STATUS_INPROGRESS)
		{
			$toolbar->add_button(array(
				"name" => "done",
				"img" => "done.gif",
				"tooltip" => "Valmis",
				"action" => "done",
			));

			$toolbar->add_button(array(
				"name" => "abort",
				"img" => "abort.gif",
				"tooltip" => "Katkesta",
				"action" => "abort",
			));
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
		$project->set_prop ("state", MRP_STATUS_INPROGRESS);
		$project->save ();

		$this_object->set_prop ("state", MRP_STATUS_INPROGRESS);
		$this_object->save ();
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
			$project->set_prop ("state", MRP_STATUS_DONE);
			$project->save ();
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

///!!! siin tuleb ka abordikommentaare n6uda kuidagi

		$this_object->set_prop ("state", MRP_STATUS_ABORTED);
		$this_object->save ();
	}
}
?>
