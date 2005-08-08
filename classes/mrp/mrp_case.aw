<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_case.aw,v 1.86 2005/08/08 16:18:20 voldemar Exp $
// mrp_case.aw - Juhtum/Projekt
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_MRP_CASE, on_save_case)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_MRP_CASE, on_delete_case)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_NEW, CL_MRP_CASE, on_new_case)
HANDLE_MESSAGE_WITH_PARAM(MSG_POPUP_SEARCH_CHANGE, CL_MRP_CASE, on_popup_search_change)

@classinfo syslog_type=ST_MRP_CASE relationmgr=yes no_status=1 confirm_save_data=1

@tableinfo mrp_case index=oid master_table=objects master_index=oid

@groupinfo grp_general caption="&Uuml;ldine" parent=general
@groupinfo grp_case_data caption="Projekti andmed" parent=general
groupinfo grp_case_material caption="Kasutatav materjal"
@groupinfo grp_case_workflow caption="Ressursid ja töövoog"
@groupinfo grp_case_schedule caption="Kalender" submit=no
@groupinfo grp_case_comments caption="Kommentaarid"
@groupinfo grp_case_log caption="Ajalugu" submit=no


	@property workflow_toolbar type=toolbar store=no no_caption=1 group=grp_case_schedule,grp_general,grp_case_workflow editonly=1
	@property workflow_errors type=text store=no no_caption=1 group=grp_case_schedule,grp_general,grp_case_workflow


@default group=grp_general
	@property header type=text store=no no_caption=1 group=grp_general,grp_case_data

	@property name type=textbox table=objects field=name
	@caption Projekti nr.

	@property comment type=textbox table=objects field=comment
	@caption Projekti nimetus

@default table=mrp_case
	@property starttime type=datetime_select
	@caption Alustamisaeg (materjalide saabumine)

	@property due_date type=datetime_select
	@caption Valmimistähtaeg

	@property planned_date type=text editonly=1
	@caption Planeeritud valmimisaeg

	@property project_priority type=textbox
	@caption Projekti prioriteet

	@property state type=text group=grp_case_schedule,grp_general,grp_case_workflow editonly=1
	@caption Staatus

	@property customer type=popup_search reltype=RELTYPE_MRP_CUSTOMER clid=CL_CRM_COMPANY
	@caption Klient

	@property progress type=hidden
	@property extern_id type=hidden

@default table=objects
@default field=meta
@default method=serialize
	@property sales_priority type=textbox size=5
	@caption Prioriteedihinnang müügimehelt



@default group=grp_case_data
	@property format type=textbox
	@caption Formaat

	@property sisu_lk_arv type=textbox
	@caption Sisu lk arv

	@property kaane_lk_arv type=textbox
	@caption Kaane lk arv

	@property sisu_varvid type=textbox
	@caption Sisu v&auml;rvid

	@property sisu_varvid_notes type=textbox
	@caption Sisu v&auml;rvid Notes

	@property sisu_lakk_muu type=textbox
	@caption Sisu lakk/muu

	@property kaane_varvid type=textbox
	@caption Kaane v&auml;rvid

	@property kaane_varvid_notes type=textbox
	@caption Kaane v&auml;rvid Notes

	@property kaane_lakk_muu type=textbox
	@caption Kaane lakk/muu

	@property sisu_paber type=textbox
	@caption Sisu paber

	@property kaane_paber type=textbox
	@caption Kaane paber

	@property trykiarv type=textbox
	@caption Tr&uuml;kiarv

	@property trykise_ehitus type=textbox
	@caption Tr&uuml;kise ehitus

	@property kromaliin type=textbox
	@caption Kromalin

	@property makett type=textbox
	@caption Makett

	@property naidis type=textbox
	@caption N&auml;idis

	@property plaate type=textbox
	@caption Plaate

	@property transport type=textbox
	@caption Transport

	@property soodustus type=textbox
	@caption Soodustus

	@property markused type=textbox
	@caption M&auml;rkused

	@property allahindlus type=textbox
	@caption Allahindlus

	//@property vahendustasu type=textbox
	//@caption Vahendustasu

	//@property myygi_hind type=textbox
	//@caption M&uuml;&uuml;gi hind


default group=grp_case_material
	property used_materials type=table store=no
	caption Kasutatav materjal


@default group=grp_case_comments

	@property user_comments type=comments
	@caption Kommentaarid juhtumi ja tööde kohta


@default group=grp_case_workflow
	@layout manager type=hbox width="20%:80%"
	@property resource_tree type=text store=no no_caption=1 parent=manager
	@property workflow_table type=table store=no no_caption=1 parent=manager


@default group=grp_case_schedule
	@property chart_navigation type=text store=no no_caption=1
	@property schedule_chart type=text store=no no_caption=1

	@property chart_legend type=text store=no
	@caption Legend


@default group=grp_case_log
	@property log type=table store=no no_caption=1



// --------------- RELATION TYPES ---------------------

@reltype MRP_MANAGER value=1 clid=CL_USER
@caption Müügimees/Projektijuht

@reltype MRP_CUSTOMER value=2 clid=CL_CRM_COMPANY
@caption Klient

@reltype MRP_PROJECT_JOB value=3 clid=CL_MRP_JOB
@caption Töö

@reltype MRP_USED_RESOURCE value=4 clid=CL_MRP_RESOURCE
@caption Kasutatav ressurss

@reltype MRP_OWNER value=5 clid=CL_MRP_WORKSPACE
@caption Projekti omanik

*/


/*

CREATE TABLE `mrp_case` (
  `oid` int(11) NOT NULL default '0',
  `starttime` int(10) unsigned default NULL,
  `progress` int(10) unsigned default NULL,
  `planned_date` int(10) unsigned default NULL,
  `due_date` int(10) unsigned default NULL,
  `project_priority` int(10) unsigned default NULL,
  `state` tinyint(2) unsigned default '1',
  `extern_id` int(11) unsigned default NULL,
  `customer` int(11) unsigned default NULL,

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

### colours (CSS colour definition)
define ("MRP_COLOUR_NEW", "#05F123");
define ("MRP_COLOUR_PLANNED", "#5B9F44");
define ("MRP_COLOUR_INPROGRESS", "#FF9900");
define ("MRP_COLOUR_ABORTED", "#FF13F3");
define ("MRP_COLOUR_DONE", "#996600");
define ("MRP_COLOUR_PAUSED", "#999999");
define ("MRP_COLOUR_UNAVAILABLE", "#D0D0D0");
define ("MRP_COLOUR_ONHOLD", "#9900CC");
define ("MRP_COLOUR_ARCHIVED", "#0066CC");
define ("MRP_COLOUR_HILIGHTED", "#FFE706");
define ("MRP_COLOUR_PLANNED_OVERDUE", "#FBCEC1");
define ("MRP_COLOUR_OVERDUE", "#DF0D12");
define ("MRP_COLOUR_AVAILABLE", "#FCFCF4");
define ("MRP_COLOUR_PRJHILITE", "#FFE706");


class mrp_case extends class_base
{
	function mrp_case()
	{
		$this->states = array (
			MRP_STATUS_NEW => t("Uus"),
			MRP_STATUS_PLANNED => t("Planeeritud"),
			MRP_STATUS_INPROGRESS => t("Töös"),
			MRP_STATUS_ABORTED => t("Katkestatud"),
			MRP_STATUS_DONE => t("Valmis"),
			MRP_STATUS_LOCKED => t("Lukustatud"),
			MRP_STATUS_PAUSED => t("Paus"),
			MRP_STATUS_DELETED => t("Kustutatud"),
			MRP_STATUS_ONHOLD => t("Plaanist väljas"),
			MRP_STATUS_ARCHIVED => t("Arhiveeritud"),
		);

		$this->state_colours = array (
			MRP_STATUS_NEW => MRP_COLOUR_NEW,
			MRP_STATUS_PLANNED => MRP_COLOUR_PLANNED,
			MRP_STATUS_INPROGRESS => MRP_COLOUR_INPROGRESS,
			MRP_STATUS_ABORTED => MRP_COLOUR_ABORTED,
			MRP_STATUS_DONE => MRP_COLOUR_DONE,
			MRP_STATUS_PAUSED => MRP_COLOUR_PAUSED,
			MRP_STATUS_ONHOLD => MRP_COLOUR_ONHOLD,
			MRP_STATUS_ARCHIVED => MRP_COLOUR_ARCHIVED,
		);

		$this->init(array(
			"tpldir" => "mrp/mrp_case",
			"clid" => CL_MRP_CASE
		));
	}

	function callback_on_load ($arr)
	{
		if (!is_oid ($arr["request"]["id"]))
		{
			if (is_oid ($arr["request"]["mrp_workspace"]))
			{
				$this->workspace = obj ($arr["request"]["mrp_workspace"]);
			}
			else
			{
				$this->mrp_error .= t("Uut projekti saab luua vaid ressursihalduskeskkonnast. ");
			}
		}
		else
		{
			$this_object = obj ($arr["request"]["id"]);
			$this->workspace = $this_object->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if (!$this->workspace)
			{
				$this->mrp_error .= t("Projektil puudub ressursihalduskeskkond. ");
			}
		}

		if ($this->mrp_error)
		{
			echo t("Viga! ") . $this->mrp_error;
		}
	}

	function callback_mod_reforb ($arr)
	{
		if ($this->workspace)
		{
			$arr["mrp_workspace"] = $this->workspace->id ();
		}
	}

	function get_property($arr)
	{
		if ($this->mrp_error)
		{
			return PROP_IGNORE;
		}

		$prop =& $arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		switch($prop["name"])
		{
			case "header":
			case "customer":
				if ($arr["new"])
				{
					return PROP_IGNORE;
				}

				$prop["value"] = $this->get_header($arr);
				break;

			case "workflow_errors":
				if (!empty ($arr["request"]["errors"]))
				{
					$errors = $arr["request"]["errors"];
					$this->dequote ($errors);
					$errors = unserialize ($errors);

					if (!empty ($errors))
					{
						$prop["value"] = ' <div style="color: #DF0D12; margin: 5px;">' . t('Esinenud tõrked: ') . implode (". ", $errors) . '.</div>';
						unset ($arr["request"]["errors"]);
					}
				}
				break;

			case "state":
				$prop["value"] = $this->states[$prop["value"]] ? $this->states[$prop["value"]] : "Määramata";
				break;

			case "planned_date":
				$date = $prop["value"] ? date (MRP_DATE_FORMAT, $prop["value"]) : "Planeerimata";
				$prop["value"] = $date;
				break;

			case "due_date":
				if ($arr["new"])
				{
					$prop["value"] = mktime (18, 00, 00);
				}
				break;

			case "starttime":
				if ($arr["new"])
				{
					$prop["value"] = mktime (18, 00, 00);
				}
				break;

			case "schedule_chart":
				### project states for showing its schedule chart
				$applicable_states = array (
					MRP_STATUS_PLANNED,
					MRP_STATUS_INPROGRESS,
				);

				if (in_array ($this_object->prop ("state"), $applicable_states))
				{
					### update schedule
					$schedule = get_instance (CL_MRP_SCHEDULE);
					$schedule->create (array("mrp_workspace" => $this->workspace->id()));
				}

				### project states for showing its schedule chart
				$applicable_states = array (
					MRP_STATUS_PLANNED,
					MRP_STATUS_INPROGRESS,
					MRP_STATUS_DONE,
					MRP_STATUS_ARCHIVED,
				);

				if (in_array ($this_object->prop ("state"), $applicable_states))
				{
					$prop["value"] = $this->create_schedule_chart ($arr);
				}
				else
				{
					$prop["value"] = t("Projekt pole plaanis");
				}
				break;

			case "chart_legend":
				$ws = get_instance(CL_MRP_WORKSPACE);
				$prop["value"] = $ws->draw_colour_legend ();
				break;

			case "resource_tree":
				$this->create_resource_tree ($arr);
				break;

			case "workflow_toolbar":
				$this->create_workflow_toolbar ($arr);
				break;

			case "workflow_table":
				### project states for updating schedule
				$applicable_states = array (
					MRP_STATUS_PLANNED,
					MRP_STATUS_INPROGRESS,
				);

				if (in_array ($this_object->prop ("state"), $applicable_states))
				{
					### update schedule
					$schedule = get_instance (CL_MRP_SCHEDULE);
					$schedule->create (array("mrp_workspace" => $this->workspace->id()));
				}

				$this->create_workflow_table ($arr);
				break;

			case "log":
				$this->_do_log($arr);
				break;

			case "chart_navigation":
				$prop["value"] = $this->create_chart_navigation ($arr);
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		if ($this->mrp_error)
		{
			return PROP_FATAL_ERROR;
		}

		$this_object =& $arr["obj_inst"];
		$prop =& $arr["prop"];
		$retval = PROP_OK;

		### post rescheduling msg where necessary
		$applicable_planning_states = array(
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PLANNED,
		);

		switch ($prop["name"])
		{
			case "due_date":
			case "project_priority":
			case "starttime":
				if ( in_array ($this_object->prop ("state"), $applicable_planning_states) and ($this_object->prop ($prop["name"]) != $prop["value"]) )
				{
					$this->workspace->set_prop("rescheduling_needed", 1);
				}
				break;

			case "user_comments":
				$ws = get_instance(CL_MRP_WORKSPACE);
				$ws->mrp_log($arr["obj_inst"]->id(), NULL, "", $prop["value"]["comment"]);
				break;
		}


		switch($prop["name"])
		{
			case "name":
				// see if any other projects have the same name
				$ol = new object_list(array(
					"class_id" => CL_MRP_CASE,
					"lang_id" => array(),
					"site_id" => array(),
					"name" => $prop["value"]
				));
				if (is_oid($arr["obj_inst"]->id()))
				{
					$ol->remove($arr["obj_inst"]->id());
				}
				if ($ol->count() > 0)
				{
					$prop["error"] = t("Ei tohi olla rohkem kui &uuml;ks sama nimega projekt!");
					return PROP_FATAL_ERROR;
				}
				break;

			case "workflow_table":
				$save = $this->save_workflow_data ($arr);

				if ($save !== PROP_OK)
				{echo $save;
					$prop["error"] = $save;
					return PROP_FATAL_ERROR;
				}
				break;
		}

		if ($arr["obj_inst"]->prop($prop["name"]) != $prop["value"] && in_array($prop["type"], array("textbox", "datetime_select")))
		{
			if ($prop["type"] == "textbox")
			{
				$v1 = $arr["obj_inst"]->prop($prop["name"]);
				$v2 = $prop["value"];
			}
			else
			if ($prop["type"] == "datetime_select")
			{
				$v1 = date("d.m.Y H:i", $arr["obj_inst"]->prop($prop["name"]));
				$v2 = date("d.m.Y H:i", date_edit::get_timestamp($prop["value"]));
				if ($v1 == $v2)
				{
					return $retval;
				}
			}

			$this->mrp_log(
				$arr["obj_inst"]->id(),
				NULL,
				"Projekti omaduse ".
					$prop["caption"]." v&auml;&auml;rtust muudeti ".
					$v1." => ".$v2
			);
		}

		return $retval;
	}

	function callback_post_save ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$this->workspace->save ();

		if ($arr["new"])
		{
			if (is_oid ($arr["request"]["mrp_workspace"]))
			{
				### set status
				$this_object->set_prop ("state", MRP_STATUS_NEW);

				### connect newly created obj. to workspace from which the req. was made
				$workspace = obj ($arr["request"]["mrp_workspace"]);
				$projects_folder = $workspace->prop ("projects_folder");
				$this_object->connect (array (
					"to" => $workspace,
					"reltype" => "RELTYPE_MRP_OWNER",
				));
				$this_object->set_parent ($projects_folder);
				$this_object->save ();
			}
			else
			{
				echo t("Ressursihalduskeskkond defineerimata või katkine");
			}
		}

		if (is_string ($arr["request"]["mrp_resourcetree_data"]))
		{
			### create new jobs based on resources chosen from tree
			$added_resources = explode (",", $arr["request"]["mrp_resourcetree_data"]);

			foreach ($added_resources as $resource_id)
			{
				if (is_oid ($resource_id))
				{
					$arr["mrp_new_job_resource"] = $resource_id;
					$this->add_job ($arr);
				}
			}
		}
	}

	function &get_current_workspace ($arr)
	{
		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}

		if (is_object ($arr["obj_inst"]))
		{
			$this_object =& $arr["obj_inst"];
		}

		$connections = $this_object->connections_from(array ("type" => "RELTYPE_MRP_OWNER", "class_id" => CL_MRP_WORKSPACE));

		foreach ($connections as $connection)
		{
			$workspace = $connection->to ();
			break;
		}

		if (!$workspace)
		{
			$workspace = obj(aw_ini_get("prisma.ws"));
		}
		return $workspace;
	}

	function create_schedule_chart ($arr)
	{
		$time =  time();
		$this_object =& $arr["obj_inst"];
		$chart = get_instance ("vcl/gantt_chart");
		$columns = (int) (($arr["request"]["mrp_chart_length"] == 1) ? 1 : 7);

		### get range start according to project state
		switch ($this_object->prop ("state"))
		{
			case MRP_STATUS_PLANNED:
				$project_start = $this_object->prop ("starttime");
				break;
			case MRP_STATUS_INPROGRESS:
			case MRP_STATUS_DONE:
			case MRP_STATUS_ARCHIVED:
				$project_start = $this_object->prop ("started");
				break;
		}

		$range_start = mktime (0, 0, 0, date ("m", $project_start), date ("d", $project_start), date("Y", $project_start));
		$range_start = (int) ($arr["request"]["mrp_chart_start"] ? $arr["request"]["mrp_chart_start"] : $range_start);
		$range_end = (int) ($range_start + $columns * 86400);
		$hilighted_project = $this_object->id ();
		$hilighted_jobs = array();

		### subdivisions
		switch ($columns)
		{
			case 1:
				$subdivisions = 24;
				break;

			default:
				$subdivisions = 3;
		}

		### add row dfn-s, resource names
		$connections = $this_object->connections_from(array("type" => "RELTYPE_MRP_PROJECT_JOB", "class_id" => CL_MRP_JOB));
		$project_resources = array ();
		$project_start = "NA";

		foreach ($connections as $connection)
		{
			$job = $connection->to();
			$project_resources[] = $job->prop("resource");
			$starttime = $job->prop("starttime");
			$project_start = ($project_start === "NA") ? $starttime : min($starttime, $project_start);
		}

		### add rows
		$project_resources = array_unique ($project_resources);
		$mrp_schedule = get_instance(CL_MRP_SCHEDULE);

		foreach ($project_resources as $resource_id)
		{
			if ($this->can("view", $resource_id))
			{
				$resource = obj ($resource_id);
				$chart->add_row (array (
					"name" => $resource_id,
					"title" => $resource->name (),
					"uri" => html::get_change_url ($resource_id),
				));

				### add reserved times for resources, cut off past
				$reserved_times = $mrp_schedule->get_unavailable_periods_for_range(array(
					"mrp_resource" => $resource->id(),
					"mrp_start" => $range_start,
					"mrp_length" => $range_end - $range_start
				));

				foreach($reserved_times as $rt_start => $rt_end)
				{
					if ($rt_end > $time)
					{
						$rt_start = ($rt_start < $time) ? $time : $rt_start;
						$chart->add_bar(array(
							"row" => $resource->id(),
							"start" => $rt_start,
							"length" => $rt_end - $rt_start,
							"nostartmark" => true,
							"colour" => MRP_COLOUR_UNAVAILABLE,
							"url" => "#",
							"layer" => 2,
							"title" => sprintf(t("Kinnine aeg %s - %s"), date(MRP_DATE_FORMAT, $rt_start), date(MRP_DATE_FORMAT, $rt_end))
						));
					}
				}
			}
		}

		### get jobs in requested range & add bars
		$res = $this->db_fetch_array (
			"SELECT MAX(job.planned_length), MAX(job.finished-job.started) FROM mrp_job as job ".
			"WHERE job.state !=" . MRP_STATUS_DELETED . " AND ".
			"job.length > 0 AND ".
			"job.resource > 0 ".
		"");
		rsort ($res[0]);
		$max_length = reset ($res[0]);

		### job states that are shown in chart past
		$applicable_states = array (
			MRP_STATUS_DONE,
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PAUSED,
		);

		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"state" => $applicable_states,
			"parent" => $this_object->prop ("jobs_folder"),
			"started" => new obj_predicate_compare (OBJ_COMP_BETWEEN, ($range_start - $max_length), $range_end),
			"resource" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
			"length" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
			"project" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
		));
		$jobs = $list->arr ();

		### job states that are shown in chart future
		$applicable_states = array (
			MRP_STATUS_PLANNED,
		);

		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"parent" => $this_object->prop ("jobs_folder"),
			"state" => $applicable_states,
			"starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, ($range_start - $max_length), $range_end),
			"starttime" => new obj_predicate_compare (OBJ_COMP_GREATER, time ()),
			"resource" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
			"length" => new obj_predicate_compare (OBJ_COMP_GREATER, 0),
		));
		$jobs = array_merge ($list->arr (), $jobs);

		foreach ($jobs as $job)
		{
			if (!is_oid($job->prop("project")) || !$this->can("view", $job->prop("project")))
			{
				continue;
			}

			$project = obj ($job->prop ("project"));

			if (!is_oid($job->prop("resource")) || !$this->can("view", $job->prop("resource")))
			{
				continue;
			}

			$resource = obj ($job->prop ("resource"));

			if (!in_array ($resource->id (), $project_resources))
			{
				continue;
			}

			### project states that are shown in chart
			$applicable_states = array (
				MRP_STATUS_PLANNED,
				MRP_STATUS_INPROGRESS,
				MRP_STATUS_DONE,
				MRP_STATUS_ARCHIVED,
			);

			if (!in_array ($project->prop ("state"), $applicable_states))
			{
				continue;
			}

			### get start&length according to job state
			switch ($job->prop ("state"))
			{
				case MRP_STATUS_DONE:
					$start = $job->prop ("started");
					$length = $job->prop ("finished") - $job->prop ("started");
// /* dbg */ echo date(MRP_DATE_FORMAT, $start) . "-" . date(MRP_DATE_FORMAT, $start + $length) . "<br>";
					break;

				case MRP_STATUS_PLANNED:
					$start = $job->prop ("starttime");
					$length = $job->prop ("planned_length");
					break;

				case MRP_STATUS_PAUSED:
				case MRP_STATUS_INPROGRESS:
					$start = $job->prop ("started");
					$length = (($start + $job->prop ("planned_length")) < $time) ? ($time - $start) : $job->prop ("planned_length");
					break;
			}

			$job_name = $project->name () . "-" . $job->prop ("exec_order") . " - " . $resource->name ();

			### set bar colour
			$colour = $this->state_colours[$job->prop ("state")];
			$colour = ($job->prop ("project") == $this_object->id ()) ? MRP_COLOUR_HILIGHTED : $this->state_colours[$job->prop ("state")];

			$bar = array (
				"id" => $job->id (),
				"row" => $resource->id (),
				"start" => $start,
				"colour" => $colour,
				"length" => $length,
				"layer" => 0,
				"uri" => html::get_change_url ($job->id ()),
				"title" => $job_name . " (" . date (MRP_DATE_FORMAT, $start) . " - " . date (MRP_DATE_FORMAT, $start + $length) . ")"
// /* dbg */ . " [res:" . $resource->id () . " töö:" . $job->id () . " proj:" . $project->id () . "]"
			);

			$chart->add_bar ($bar);

			### add paused bars
			foreach(safe_array($job->meta("paused_times")) as $pd)
			{
				if ($pd["start"] && $pd["end"])
				{
					$bar = array (
						"row" => $resource->id (),
						"start" => $pd["start"],
						"nostartmark" => true,
						"colour" => $this->state_colours[MRP_STATUS_PAUSED],
						"layer" => 1,
						"length" => ($pd["end"] - $pd["start"]),
						"uri" => aw_url_change_var ("mrp_hilight", $project->id ()),
						"title" => $job_name . ", paus (" . date (MRP_DATE_FORMAT, $pd["start"]) . " - " . date (MRP_DATE_FORMAT, $pd["end"]) . ")"
					);

					$chart->add_bar ($bar);
				}
			}
		}

		### config
		$chart->configure_chart (array (
			"chart_id" => "master_schedule_chart",
			"style" => "aw",
			"start" => $range_start,
			"end" => $range_end,
			"columns" => $columns,
			"subdivisions" => $subdivisions,
			"timespans" => $subdivisions,
			"width" => 950,
			"row_height" => 10,
		));

		### define columns
		$i = 0;
		$days = array ("P", "E", "T", "K", "N", "R", "L");

		while ($i < $columns)
		{
			$day_start = ($range_start + ($i * 86400));
			$day = date ("w", $day_start);
			$date = date ("j/m/Y", $day_start);
			$uri = ($columns == 1) ? aw_url_change_var ("mrp_chart_length", 7) : aw_url_change_var ("mrp_chart_length", 1);
			$uri = aw_url_change_var ("mrp_chart_start", $day_start, $uri);
			$chart->define_column (array (
				"col" => ($i + 1),
				"title" => $days[$day] . " - " . $date,
				"uri" => $uri,
			));
			$i++;
		}

		return $chart->draw_chart ();
	}

	function create_chart_navigation ($arr)
	{
		$start = (int) ($arr["request"]["mrp_chart_start"] ? $arr["request"]["mrp_chart_start"] : time ());
		$start_nav = array ();
		$start_uri = aw_url_change_var ("mrp_chart_length", "");
		$start_uri = aw_url_change_var ("mrp_chart_start", "", $uri);
		$period_length = (isset ($arr["request"]["mrp_chart_length"]) ? $arr["request"]["mrp_chart_length"] : 7) * 86400;

		$start_nav[] = html::href (array (
			"caption" => t("<< Tagasi"),
			"url" => aw_url_change_var ("mrp_chart_start", ($start - $period_length)),
		));
		$start_nav[] = html::href (array (
			"caption" => t("Algusesse"),
			"url" => $start_uri,
		));
		$start_nav[] = html::href (array (
			"caption" => t("Edasi >>"),
			"url" => aw_url_change_var ("mrp_chart_start", ($start + $period_length + 1)),
		));

		$navigation = '&nbsp;&nbsp;' . implode (" &nbsp;&nbsp; ", $start_nav);
		return $navigation;
	}

	function create_resource_tree ($arr = array ())
	{
		$this_object =& $arr["obj_inst"];
		$workspace =& $this->get_current_workspace ($arr);
		$resources_folder = $workspace->prop ("resources_folder");
		$resource_tree = new object_tree (array (
			"parent" => $resources_folder,
			"class_id" => array (CL_MENU, CL_MRP_RESOURCE),
			"sort_by" => "objects.jrk",
		));

		classload ("vcl/treeview");
		$tree = treeview::tree_from_objects (array (
			"tree_opts" => array (
				"type" => TREE_DHTML_WITH_BUTTONS,
				"tree_id" => "resourcetree",
				"persist_state" => true,
				"checkbox_data_var" => "mrp_resourcetree_data",
			),
			"root_item" => obj ($resources_folder),
			"ot" => $resource_tree,
			"var" => "mrp_resource_tree_active_item",
			"checkbox_class_filter" => array (CL_MRP_RESOURCE),
			"no_root_item" => true
		));
		$tree->set_only_one_level_opened(1);

		$arr["prop"]["value"] = $tree->finalize_tree ();
	}

	function create_workflow_toolbar ($arr = array ())
	{
		$this_object =& $arr["obj_inst"];
		$toolbar =& $arr["prop"]["toolbar"];

		### delete button
		if ($arr["request"]["group"] == "grp_case_workflow")
		{
			$disabled = false;
		}
		else
		{
			$disabled = true;
		}

		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud töö(d)"),
			"confirm" => t("Kustutada kõik valitud tööd?"),
			"action" => "delete",
			"disabled" => $disabled,
		));

		$toolbar->add_separator();

		// $toolbar->add_button(array(
			// "name" => "test",
			// "img" => "preview.gif",
			// "tooltip" => t("Testi/hinda valmimisaega"),
			// "action" => "test",
		// ));

		### states for scheduling a project
		$applicable_states = array(
			MRP_STATUS_NEW,
			MRP_STATUS_ABORTED,
			MRP_STATUS_ONHOLD,
		);

		if (in_array($this_object->prop("state"), $applicable_states))
		{
			$disabled = false;
		}
		else
		{
			$disabled = true;
		}

		$toolbar->add_button(array(
			"name" => "plan_btn",
			// "img" => "plan.gif",
			"tooltip" => t("Planeeri"),
			"action" => "plan",
			"disabled" => $disabled,
		));


		### states for taking a project out of scheduling
		$applicable_states = array(
			MRP_STATUS_PLANNED,
		);

		if (in_array($this_object->prop("state"), $applicable_states))
		{
			$disabled = false;
		}
		else
		{
			$disabled = true;
		}

		$toolbar->add_button(array(
			"name" => "onhold_btn",
			// "img" => "set_on_hold.gif",
			"tooltip" => t("Plaanist v&auml;lja"),
			"action" => "set_on_hold",
			"disabled" => $disabled,
		));

		### states for aborting a project
		$applicable_states = array(
			MRP_STATUS_INPROGRESS,
		);

		if (in_array($this_object->prop("state"), $applicable_states))
		{
			$disabled = false;
		}
		else
		{
			$disabled = true;
		}

		$toolbar->add_button(array(
			"name" => "abort_btn",
			// "img" => "abort.gif",
			"tooltip" => t("Katkesta"),
			"confirm" => t("Katkesta projekt?"),
			"action" => "abort",
			"disabled" => $disabled,
		));

		### states for finishing a project
		$applicable_states = array(
			MRP_STATUS_INPROGRESS,
		);

		if (in_array($this_object->prop("state"), $applicable_states))
		{
			$disabled = false;
		}
		else
		{
			$disabled = true;
		}

		$toolbar->add_button(array(
			"name" => "finish_btn",
			// "img" => "finish.gif",
			"tooltip" => t("Valmis"),
			"action" => "finish",
			"confirm" => t("Projekt on töös. Olete kindel, et soovite määrata projekti staatuseks \'valmis\' ?"),
			"disabled" => $disabled,
		));

		### states for archiving a project
		$applicable_states = array(
			MRP_STATUS_DONE,
		);

		if (in_array($this_object->prop("state"), $applicable_states))
		{
			$disabled = false;
		}
		else
		{
			$disabled = true;
		}

		$toolbar->add_button(array(
			"name" => "archive_btn",
			// "img" => "archive.gif",
			"tooltip" => t("Arhiveeri"),
			"action" => "archive",
			"disabled" => $disabled,
		));
	}

	function create_workflow_table ($arr)
	{
		$this_object =& $arr["obj_inst"];

		### init. table
		$table =& $arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "exec_order",
			"caption" => t("Nr."),
		));
		$table->define_field(array(
			"name" => "prerequisites",
			"caption" => t("Eel&shy;dus&shy;tööd"),
		));
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$table->define_field(array(
			"name" => "length",
			"caption" => t("Pikkus (h)"),
		));
		$table->define_field(array(
			"name" => "pre_buffer",
			"caption" => t("Eel&shy;puh&shy;ver (h)"),
		));
		$table->define_field(array(
			"name" => "post_buffer",
			"caption" => t("Järel&shy;puh&shy;ver (h)"),
		));
		$table->define_field(array(
			"name" => "comment",
			"caption" => t("Kommen&shy;taar"),
			"align" => "center"
		));
		$table->define_field(array(
			"name" => "minstart",
			"caption" => t("Min. algusaeg"),
			"align" => "center"
		));
		$table->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
		));
		$table->define_field(array(
			"name" => "starttime",
			"caption" => t("Töösse"),
		));

		if (!$arr["no_edit"])
		{
			$table->define_chooser(array(
				"name" => "selection",
				"field" => "job_id",
			));
		}

		$table->set_numeric_field ("exec_order");
		$table->set_default_sortby ("exec_order");
		$table->set_default_sorder ("asc");

		### define data for each connected job
		$connections = $this_object->connections_from(array ("type" => "RELTYPE_MRP_PROJECT_JOB", "class_id" => CL_MRP_JOB));

		foreach ($connections as $connection)
		{
			$job = $connection->to ();
			$job_id = $job->id ();
			$resource_id = $job->prop ("resource");
			$resource = obj ($resource_id);
			$disabled = false;

			switch ($job->prop ("state"))
			{
				case MRP_STATUS_INPROGRESS:
				case MRP_STATUS_PAUSED:
				case MRP_STATUS_DONE:
					$disabled = true;
					break;
			}

			$state = '<span style="color: ' . $this->state_colours[$job->prop ("state")] . ';">' . $this->states[$job->prop ("state")] . '</span>';

			### translate prerequisites from object id-s to execution orders
			$prerequisites = $job->prop ("prerequisites");
			$prerequisites = explode (",", $prerequisites);
			$prerequisites_translated = array ();

			foreach ($prerequisites as $oid)
			{
				if (is_oid ($oid) and $this->can("view", $oid))
				{
					$prerequisite_job = obj ($oid);
					$prerequisites_translated[] = $prerequisite_job->prop ("exec_order");
				}
				else
				{
					error::raise(array(
						"msg" => sprintf (t("Eeldustöö pole objekti id või puudub sellele objektile vaatamisõigus, mis siin peaks kindlasti olemas olema (oid: %s)."), $oid),
						"fatal" => false,
						"show" => false,
					));
				}
			}

			$prerequisites = implode (",", $prerequisites_translated);

			### get & process field values
			$resource_name = $resource->name () ? $resource->name () : "...";
			$starttime = $job->prop ("starttime");
			$planned_start = $starttime ? date (MRP_DATE_FORMAT, $starttime) : "Planeerimata";
			if ($arr["no_edit"] == 1)
			{
				$comment = htmlspecialchars($job->prop("comment"));
			}
			else
			{
				$comment = html::textbox(array(
					"name" => "comments[".$job->id()."]",
					"value" => htmlspecialchars($job->prop("comment")),
					"size" => 10,
					"textsize" => "11px"
				));
			}

			$t_length = round ((($job->prop ("length"))/3600), 2);
			$t_pre_buffer = round ((($job->prop ("pre_buffer"))/3600), 2);
			$t_post_buffer = round ((($job->prop ("post_buffer"))/3600), 2);
			$t_minstart = (($job->prop ("minstart")) ? $job->prop ("minstart") : time());

			$table->define_data(array(
				"name" => html::get_change_url(
					$job->id(),
					array("return_url" => urlencode(aw_global_get("REQUEST_URI"))),
					$this_object->name () . " - " . $resource_name
				),
				"length" => $arr["no_edit"] == 1 ?  $t_length : html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-length",
					"size" => "1",
					"textsize" => "11px",
					"value" => $t_length,
					"disabled" => $disabled,
					)
				),
				"pre_buffer" => $arr["no_edit"] == 1 ? $t_pre_buffer : html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-pre_buffer",
					"size" => "1",
					"textsize" => "11px",
					"value" => $t_pre_buffer,
					"disabled" => $disabled,
					)
				),
				"post_buffer" => $arr["no_edit"] == 1 ? $t_post_buffer : html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-post_buffer",
					"size" => "1",
					"textsize" => "11px",
					"value" => $t_post_buffer,
					"disabled" => $disabled,
					)
				),
				"prerequisites" => $arr["no_edit"] == 1 ? $prerequisites : html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-prerequisites",
					"size" => "4",
					"textsize" => "11px",
					"value" => $prerequisites,
					"disabled" => $disabled,
					)
				),
				"minstart" => $arr["no_edit"] == 1 ? date("d.m.Y H:i", $t_minstart) : '<span style="white-space: nowrap;">' . html::datetime_select(array(
					"name" => "mrp_workflow_job-" . $job_id . "-minstart",
					"value" => $t_minstart,
					"disabled" => $disabled,
					"day" => "text",
					"month" => "text",
					"textsize" => "11px",
					)
				) . '</span>',
				"exec_order" => $job->prop ("exec_order"),
				"starttime" => $planned_start,
				"status" => $state,
				"job_id" => $job_id,
				"comment" => $comment,
			));
		}
	}

	function save_workflow_data ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$orders = array ();
		$errors = false;
		$connections = $this_object->connections_from(array ("type" => "RELTYPE_MRP_PROJECT_JOB", "class_id" => CL_MRP_JOB));
		$jobs = array ();
		$workflow = array ();

		### non-changeable job states
		$applicable_states = array(
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PAUSED,
			MRP_STATUS_DONE,
		);

		foreach ($connections as $connection)
		{
			$job = $connection->to ();
			$jobs[$job->prop ("exec_order")] = $job->id ();

			### add non-changeable jobs to workflow
			if (in_array ($job->prop ("state"), $applicable_states))
			{
				$prerequisites = explode (",", $job->prop ("prerequisites"));
				$workflow[$job->id ()] = $prerequisites;
			}
		}

		foreach ($arr["request"] as $name => $value)
		{
			$property = explode ("-", $name);

			if ($property[0] == "mrp_workflow_job")
			{
				if (is_oid ($property[1]))
				{
					$job = obj ($property[1]);
					$property = $property[2];

					if (!in_array ($job->prop ("state"), $applicable_states))
					{
						switch ($property)
						{
							case "prerequisites":
								### translate prerequisites from execution orders to object id-s
								$prerequisites_userdata = explode (",", $value);
								$prerequisites = array ();

								foreach ($prerequisites_userdata as $prerequisite)
								{
									settype ($prerequisite, "integer");
									$job_id = $jobs[$prerequisite];
									$prerequisites[] = $job_id;
								}

								$workflow[$job->id ()] = $prerequisites;
								break;

							case "length":
							case "pre_buffer":
							case "post_buffer":
								$value = $this->safe_settype_float ($value);
								$job->set_prop ($property, (round ($value * 3600)));
								break;

							case "minstart":
								$minstart = mktime ($value["hour"], $value["minute"], 0, $value["month"], $value["day"], $value["year"]);
								$job->set_prop ("minstart", $minstart);
								break;
						}

						if ($job->comment() != $arr["request"]["comments"][$job->id()])
						{
						$job->set_comment($arr["request"]["comments"][$job->id()]);
							$workspace_i = get_instance(CL_MRP_WORKSPACE);
							$workspace_i->mrp_log($job->prop("project"), $job->id(), t("Lisas kommentaari"), $arr["request"]["comments"][$job->id()]);
						}

						aw_disable_acl();
						$job->save ();
						aw_restore_acl();
					}
				}
				else
				{
					$errors .= t("Töö objekti-id katkine");
				}
			}
		}

		if ($errors)
		{
			return $errors;
		}
		else
		{
			$applicable_planning_states = array(
				MRP_STATUS_INPROGRESS,
				MRP_STATUS_PLANNED,
			);

			if (in_array ($this_object->prop ("state"), $applicable_planning_states))
			{
				### post rescheduling msg
				$workspace = $this_object->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

				if ($workspace)
				{
					$workspace->set_prop("rescheduling_needed", 1);
					$workspace->save();
				}
				else
				{
					return t("Ressursihalduskeskkond defineerimata");
				}
			}

			### check & save workflow
			if (!empty ($workflow))
			{
				$error = $this->order_jobs ($arr, $workflow);

				if ($error)
				{
					return $error;
				}
				else
				{
					foreach ($workflow as $job_id => $prerequisites)
					{
						$job = obj ($job_id);
						$job->set_prop ("prerequisites", implode (",", $prerequisites));
						aw_disable_acl();
						$job->save ();
						aw_restore_acl();
					}
				}
			}

			return PROP_OK;
		}
	}

	/**
		@attrib name=order_jobs
		@param oid required type=int
	**/
	function order_jobs ($arr = array (), $workflow = false)
	{
		### get project object
		if (is_oid ($arr["oid"]))
		{
			$project = obj ($arr["oid"]);
		}

		if (is_object ($arr["obj_inst"]))
		{
			$project =& $arr["obj_inst"];
		}

		$connections = $project->connections_from (array ("type" => "RELTYPE_MRP_PROJECT_JOB", "class_id" => CL_MRP_JOB));

		if (!is_array ($workflow))
		{
			### read project jobs
			$workflow = array ();

			foreach ($connections as $connection)
			{
				$job = $connection->to ();

				### exclude jobs just about to be deleted
				if ($job->prop ("state") != MRP_STATUS_DELETED)
				{
					$prerequisites = explode (",", $job->prop ("prerequisites"));
					$workflow[$job->id ()] = $prerequisites;
				}
			}
		}
		elseif (count ($workflow) != count ($connections))
		{
			return t("Töövoog ei sisalda kõiki projekti töid");
		}

		foreach ($workflow as $job_id => $prerequisites)
		{
			### throw away erroneous definitions
			foreach ($prerequisites as $key => $prerequisite)
			{
				if (!is_oid ((int) $prerequisite))
				{
					unset ($prerequisites[$key]);
				}
			}

			### explicitly indicate absence of prerequisites
			if (empty ($prerequisites))
			{
				$prerequisites[] = "none";
			}

			$workflow[$job_id] = $prerequisites;
		}

		### sort workflow topologically, halt on cycle
		$jobs = array ();

		foreach ($workflow as $job_id => $prerequisites)
		{
			$degree = 0;
			$nodes = array ($job_id);

			### recursively go through all current job's prerequisites
			do
			{
				if ($degree > count ($workflow))
				{
					return t("Töövoog sisaldab tsüklit");
				}

				$current_nodes = $nodes;

				foreach ($current_nodes as $current_node)
				{
					if ($workflow[$current_node][0] != "none")
					{ ### prerequisites exist for current node
						### add new prerequisites
						$nodes = array_merge ($nodes, $workflow[$current_node]);
					}

					### remove current node from nodes to visit
					$checked_node = array_keys ($nodes, $current_node);
					$checked_node = $checked_node[0];
					unset ($nodes[$checked_node]);
				}

				### increment arc count
				$degree++;
			}
			while (!empty ($nodes));

			$jobs[$degree][] = $job_id;
		}

		### sort by degree
		ksort ($jobs);

		### convert topology to sequence
		$order = array ();

		foreach ($jobs as $degree => $degree_jobs)
		{
			$order = array_merge ($order, $degree_jobs);
		}

		### save job orders
		foreach ($order as $key => $job_id)
		{
			$job = obj ($job_id);
			$job->set_prop ("exec_order", ($key + 1));
			aw_disable_acl();
			$job->save ();
			aw_restore_acl();
		}
	}

	function order_jobs_old ($arr = array ())// unused function
	{
		if (is_oid ($arr["oid"]))
		{
			$this_object = obj ($arr["oid"]);
		}

		if (is_object ($arr["obj_inst"]))
		{
			$this_object =& $arr["obj_inst"];
		}

		$connections = $this_object->connections_from (array ("type" => "RELTYPE_MRP_PROJECT_JOB", "class_id" => CL_MRP_JOB));
		$workflow = array ();

		foreach ($connections as $connection)
		{
			$job = $connection->to ();

			if ($job->prop ("state") != MRP_STATUS_DELETED)
			{
				$job_id = $job->id ();
				$prerequisites = explode (",", $job->prop ("prerequisites"));
				$workflow[$job_id] = $prerequisites;
			}
		}

		$jobs = $this->workflow2sequence ($workflow);

		foreach ($jobs as $key => $job_id)
		{
			if (is_oid ($job_id))
			{
				$job = obj ($job_id);
				$job->set_prop ("exec_order", ($key + 1));
				aw_disable_acl();
				$job->save ();
				aw_restore_acl();
			}
			else
			{
				veryseriouserror;
			}
		}
	}

	// converts workflow to sequence of jobs. concurrent jobs are ordered as given in Input
	// Input: $workflow = array (job_id_1 => array (prerequisite_job_id_m, ...), ...)
	// if job n has no prerequisites then: job_id_n => array (0)
	// Output: ordered non-associative array of job id-s: array (0 => job_id_1, ...)
	function workflow2sequence ($workflow_input = array ())// unused function
	{
		$workflow = array ();

		foreach ($workflow_input as $job_id => $prerequisites)
		{
			if ($prerequisites[0])
			{
				foreach ($prerequisites as $value)
				{
					if ($value)
					{
						$workflow[$job_id][] = trim ($value);
					}
				}
			}
			else
			{
					$workflow[$job_id][] = 0;
			}
		}

		$sequence = array ();
		$current_prerequisites = array (0);

		while ($current_prerequisites !== false)
		{
			$new_prerequisites = array ();

			foreach ($current_prerequisites as $current_prerequisite)
			{
				foreach ($workflow as $job_id => $prerequisites)
				{
					foreach ($workflow[$job_id] as $key => $prerequisite)
					{
						if ($prerequisite == $current_prerequisite)
						{
							if ( (count ($prerequisites) == 1) or (count ($workflow) == 1) )
							{
								if (!in_array ($job_id, $sequence))
								{
									$sequence[] = $job_id;

									if (!in_array ($job_id, $new_prerequisites))
									{
										$new_prerequisites[] = $job_id;
									}
								}
							}

							unset ($workflow[$job_id][$key]);
						}
					}
				}
			}

			if (empty ($new_prerequisites))
			{
				$current_prerequisites = false;
			}
			else
			{
				$current_prerequisites = $new_prerequisites;
			}
		}

		return $sequence;
	}

/**
    @attrib name=add_job
	@param oid required type=int
**/
	function _add_job ($arr)
	{
		$arr["obj_inst"] = obj ($arr["oid"]);
		$this->add_job ($arr);

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["oid"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_case");
		return $return_url;
	}

	function add_job ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$workspace =& $this->get_current_workspace ($arr);
		$connections = $this_object->connections_from (array ("type" => "RELTYPE_MRP_PROJECT_JOB", "class_id" => CL_MRP_JOB));
		$resource_id = $arr["mrp_new_job_resource"];
		$job_number = (count ($connections) + 1);

		if (is_oid ($resource_id))
		{
			$resource = obj ($resource_id);
			$pre_buffer = $resource->prop ("default_pre_buffer");
			$post_buffer = $resource->prop ("default_post_buffer");
		}
		else
		{
			$resource = false;
			$pre_buffer = 0;
			$post_buffer = 0;
		}

		if (!($jobs_folder = $workspace->prop ("jobs_folder")))
		{
			return false;//!!! veateade teha? siin ei tasu mingit suurt kella l88ma hakata.
		}

		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"exec_order" => ($job_number - 1),
			"parent" => $jobs_folder,
			"project" => $this_object->id (),
		));
		$prerequisite_job = $list->begin ();
		$prerequisite = is_object ($prerequisite_job) ? $prerequisite_job->id () : "";

		$job =& new object (array (
		   "parent" => $jobs_folder,
		   // "class_id" => CL_MRP_JOB,
		));
		$job->set_class_id (CL_MRP_JOB);
		$job->set_prop ("state", MRP_STATUS_NEW);
		$job->set_prop ("exec_order", $job_number);
		$job->set_prop ("prerequisites", $prerequisite);
		$job->set_prop ("project", $this_object->id ());
		$job->set_prop ("pre_buffer", $pre_buffer);
		$job->set_prop ("post_buffer", $post_buffer);
		$job->set_prop ("resource", $resource_id);
		$job->set_name ($this_object->name () . "-" . $resource->name ());
		aw_disable_acl();
		$job->save ();
		aw_restore_acl();

		if ($resource)
		{
			$job->connect (array (
				"to" => $resource,
				"reltype" => "RELTYPE_MRP_RESOURCE",
			));
		}

		$job->connect (array (
			"to" => $this_object,
			"reltype" => "RELTYPE_MRP_PROJECT",
		));
		$this_object->connect (array (
			"to" => $job,
			"reltype" => "RELTYPE_MRP_PROJECT_JOB",
		));

		return true;
	}

	/**
		@attrib name=delete
	**/
	function delete ($arr)
	{
		$sel = $arr["selection"];

		if (is_array($sel))
		{
			$ol = new object_list(array(
				"oid" => array_keys($sel),
			));

			for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				if ($this->can("delete", $o->id()))
				{
					$class = $o->class_id ();
					$o->delete ();
				}
			}

			$arr["oid"] = $arr["id"];
		}

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_case"); //echo $return_url;

		return $return_url;
	}

	function on_save_case($arr)
	{
		// save data to prisma server
		$i = get_instance(CL_MRP_PRISMA_IMPORT);
		$i->write_proj($arr["oid"]);
	}

	function mrp_log($proj, $job, $msg, $comment = '')
	{
		$this->db_query("
			INSERT INTO
				mrp_log(
					project_id,job_id,uid,tm,message,comment
				)
				values(
					".((int)$proj).",".((int)$job).",'".aw_global_get("uid")."',".time().",'$msg','$comment'
				)
		");
	}

	function _init_log_t(&$t)
	{
		$t->define_field(array(
			"name" => "tm",
			"caption" => t("Millal"),
			"type" => "time",
			"align" => "center",
			"format" => "d.m.Y H:i",
			"numeric" => 1,
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "job_id",
			"caption" => t("T&ouml;&ouml"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "uid",
			"caption" => t("Kasutaja"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "message",
			"caption" => t("sisu"),
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "comment",
			"caption" => t("Kommentaar"),
			"align" => "center",
			"sortable" => 1
		));
	}

	function _do_log($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_log_t($t);

		$this->db_query("SELECT tm,objects.name as job_id, uid,message,mrp_log.comment as comment FROM mrp_log left join objects on objects.oid = mrp_log.job_id  WHERE project_id = ".$arr["obj_inst"]->id()." ORDER BY tm DESC");
		while ($row = $this->db_next())
		{
			$row["message"] = nl2br($row["message"]);
			$row["comment"] = nl2br($row["comment"]);
			$t->define_data($row);
		}
		$t->set_default_sortby("tm");
		$t->set_default_sorder("desc");
		$t->sort_by();
	}

	function on_new_case ($arr)
	{
		$case_id = $arr["oid"];
		$this->db_query("
			INSERT INTO
				mrp_log(
					project_id,job_id,uid,tm,message
				)
				values(
					$case_id,NULL,'".aw_global_get("uid")."',".time().",'Projekt lisati'
				)
		");
	}

	function on_delete_case ($arr)
	{
		$project = obj ($arr["oid"]);
		$project->set_prop ("state", MRP_STATUS_DELETED);
		aw_disable_acl();
		$project->save ();
		aw_restore_acl();

		### delete project's jobs
		$connections = $project->connections_from (array ("type" => "RELTYPE_MRP_PROJECT_JOB"));

		foreach ($connections as $connection)
		{
			$job = $connection->to ();
			$job->delete ();
		}

		$applicable_planning_states = array(
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PLANNED,
		);

		if (in_array ($project->prop ("state"), $applicable_planning_states))
		{
			### post rescheduling msg
			$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if ($workspace)
			{
				$workspace->set_prop("rescheduling_needed", 1);
				aw_disable_acl();
				$workspace->save();
				aw_restore_acl();
			}
			else
			{
				return t("Ressursihalduskeskkond defineerimata");
			}
		}
	}

	function safe_settype_float ($value)
	{
		$parts1 = explode (",", $value, 2);
		$parts2 = explode (".", $value, 2);
		$parts = (count ($parts2) == 1) ? $parts1 : $parts2;
		$value = (float) ((isset ($parts[0]) ? ((int) $parts[0]) : 0) . "." . (isset ($parts[1]) ? ((int) $parts[1]) : 0));
		return $value;
	}

	function get_header($arr)
	{
		$ws = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

		if ($ws)
		{
			if (is_oid($ws->prop("case_header_controller")) && $this->can("view", $ws->prop("case_header_controller")))
			{
				$ctr = obj($ws->prop("case_header_controller"));
				$i = $ctr->instance();
				$res = $i->eval_controller($ctr->id(), $arr["obj_inst"]);
				return $res;
			}
		}
	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "grp_case_details")
		{
			return false;
		}

		return true;
	}

/**
    @attrib name=start
	@param id required type=int
**/
	function start ($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_case");

		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			$errors[] = t("Projekti id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		### states for starting a project
		$applicable_states = array (
			MRP_STATUS_PLANNED,
		);

		if (!in_array ($project->prop ("state"), $applicable_states))
		{
			$errors[] = t("Projekt pole planeeritud");
		}

		### ...
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### start project
			$project->set_prop ("state", MRP_STATUS_INPROGRESS);
			$project->set_prop ("progress", time ());
			aw_disable_acl();
			$project->save ();
			aw_restore_acl();

			### log change
			$ws = get_instance (CL_MRP_WORKSPACE);
			$ws->mrp_log ($project->id (), NULL, "Projekt l&auml;ks t&ouml;&ouml;sse");


			return $return_url;
		}
	}

/**
    @attrib name=finish
	@param id required type=int
**/
	function finish ($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_case");

		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			$errors[] = t("Projekti id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		### check if all jobs are done
		$job_list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"project" => $project->id (),
		));
		$all_jobs = (int) $job_list->count ();

		### states for jobs that allow finishing a project
		$applicable_states = array (
			MRP_STATUS_DONE,
			MRP_STATUS_ABORTED,
		);
		$job_list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"state" => $applicable_states,
			"project" => $project->id()
		));
		$done_jobs = (int) $job_list->count ();

		if ($done_jobs != $all_jobs)
		{
			$errors[] = t("Projekti ei saa lõpetada. Kõik projekti tööd pole valmis");
		}

		### states for finishing a project
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (!in_array ($project->prop ("state"), $applicable_states))
		{
			$errors[] = t("Projekt pole töös");
		}

		### ...
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### finish project
			$project->set_prop ("state", MRP_STATUS_DONE);
			aw_disable_acl();
			$project->save ();
			aw_restore_acl();

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log(
				$project->id(),
				NULL,
				"Projekt l&otilde;petati"
			);

			return $return_url;
		}
	}

/**
	@attrib name=abort
	@param id required type=int
**/
	function abort ($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_case");

		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			$errors[] = t("Projekti id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		### states for aborting a project
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (!in_array ($project->prop ("state"), $applicable_states))
		{
			$errors[] = t("Projekt pole töös");
		}

		### if no errors, abort project
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### abort project
			$project->set_prop ("state", MRP_STATUS_ABORTED);
			aw_disable_acl();
			$project->save ();
			aw_restore_acl();

			### post rescheduling msg
			$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if ($workspace)
			{
				$workspace->set_prop("rescheduling_needed", 1);
				aw_disable_acl();
				$workspace->save();
				aw_restore_acl();
			}
			else
			{
				return aw_url_change_var ("errors", array (t("Ressursihalduskeskkond defineerimata.")), $return_url);
			}

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log ($project->id (), NULL, "Projekt katkestati");

			return $return_url;
		}
	}

/**
    @attrib name=archive
	@param id required type=int
**/
	function archive ($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_case");

		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			$errors[] = t("Projekti id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		### states for archiving a project
		$applicable_states = array(
			MRP_STATUS_DONE,
		);

		if (!in_array ($project->prop ("state"), $applicable_states))
		{
			$errors[] = t("Projekt pole valmis");
		}

		### if no errors, archive project
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### archive project
			$project->set_prop("state", MRP_STATUS_ARCHIVED);
			aw_disable_acl();
			$project->save();
			aw_restore_acl();

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log(
				$project->id(),
				NULL,
				"Projekt arhiveeriti"
			);

			return $return_url;
		}
	}

/**
    @attrib name=plan
	@param id required type=int
**/
	function plan ($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_case");

		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			$errors[] = t("Projekti id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		### states for planning a project
		$applicable_states = array(
			NULL,
			MRP_STATUS_NEW,
			MRP_STATUS_ABORTED,
			MRP_STATUS_ONHOLD,
		);

		if (!in_array ($project->prop ("state"), $applicable_states))
		{
			$errors[] = t("Projekt pole planeerimiseks valmis");
		}

		### if no errors, plan project
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### plan project
			$project->set_prop("state", MRP_STATUS_PLANNED);
			aw_disable_acl();
			$project->save();
			aw_restore_acl();

			### post rescheduling msg
			$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if ($workspace)
			{
				$workspace->set_prop("rescheduling_needed", 1);
				aw_disable_acl();
				$workspace->save();
				aw_restore_acl();
			}
			else
			{
				return aw_url_change_var ("errors", array (t("Ressursihalduskeskkond defineerimata.")), $return_url);
			}

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log(
				$project->id(),
				NULL,
				"Projekt sisestati planeerimisse"
			);

			return $return_url;
		}
	}

/**
    @attrib name=set_on_hold
	@param id required type=int
**/
	function set_on_hold ($arr)
	{
		$errors = array ();
		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_case");

		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			$errors[] = t("Projekti id vale");
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}

		### states for taking a project out of schedule
		$applicable_states = array(
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PLANNED,
		);

		if (!in_array ($project->prop ("state"), $applicable_states))
		{
			$errors[] = t("Projekti staatus sobimatu");
		}

		### if no errors, set project on hold
		if ($errors)
		{
			$errors = urlencode(serialize($errors));
			return aw_url_change_var ("errors", $errors, $return_url);
		}
		else
		{
			### set project on hold
			$project->set_prop("state", MRP_STATUS_ONHOLD);
			aw_disable_acl();
			$project->save();
			aw_restore_acl();

			### post rescheduling msg
			$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if ($workspace)
			{
				$workspace->set_prop("rescheduling_needed", 1);
				aw_disable_acl();
				$workspace->save();
				aw_restore_acl();
			}
			else
			{
				return aw_url_change_var ("errors", array (t("Ressursihalduskeskkond defineerimata.")), $return_url);
			}

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log(
				$project->id(),
				NULL,
				"Projekt võeti planeerimisest välja"
			);

			return $return_url;
		}
	}

	/** message handler for the MSG_POPUP_SEARCH_CHANGE message so we can create the correct relation
	**/
	function on_popup_search_change ($arr)
	{
		if (!is_oid($arr["oid"]))
		{
			return;
		}

		$o = obj($arr["oid"]);

		foreach($o->connections_from(array("type" => "RELTYPE_MRP_CUSTOMER")) as $c)
		{
			$c->delete();
		}

		if (is_oid($o->prop($arr["prop"])))
		{
			$customer = obj ($o->prop($arr["prop"]));
			$o->connect(array(
				"to" => $customer,
				"reltype" => "RELTYPE_MRP_CUSTOMER"
			));
		}
	}
}

?>
