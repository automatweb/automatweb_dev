<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_case.aw,v 1.37 2005/03/21 21:48:59 voldemar Exp $
// mrp_case.aw - Juhtum/Projekt
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_MRP_CASE, on_save_case)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_DELETE, CL_MRP_CASE, on_delete_case)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_NEW, CL_MRP_CASE, on_new_case)
EMIT_MESSAGE(MSG_MRP_RESCHEDULING_NEEDED)

@classinfo syslog_type=ST_MRP_CASE relationmgr=yes no_status=1

@tableinfo mrp_case index=oid master_table=objects master_index=oid

@groupinfo grp_general caption="&Uuml;ldine" parent=general
@groupinfo grp_case_data caption="Projekti andmed" parent=general
@groupinfo grp_case_details caption="Projekti kirjeldus"
groupinfo grp_case_material caption="Kasutatav materjal"
@groupinfo grp_case_workflow caption="Ressursid ja töövoog"
@groupinfo grp_case_schedule caption="Kalender" submit=no
@groupinfo grp_case_comments caption="Kommentaarid"
@groupinfo grp_case_log caption="Ajalugu" submit=no


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

	@property project_priority type=textbox
	@caption Projekti prioriteet

	@property state type=text
	@caption Staatus

	@property customer type=relpicker reltype=RELTYPE_MRP_CUSTOMER
	@caption Klient

	@property progress type=hidden
	@property extern_id type=hidden

//!!! abordij22nus
	// @property do_abort type=checkbox ch_value=1 table=objects field=meta method=serialize
	// @caption L&otilde;peta

@default table=objects
@default field=meta
@default method=serialize
	@property planned_date type=text store=no editonly=1
	@caption Planeeritud valmimisaeg

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

	@property vahendustasu type=textbox
	@caption Vahendustasu

	@property myygi_hind type=textbox
	@caption M&uuml;&uuml;gi hind


@default group=grp_case_details
	@property case_description type=textarea
	@caption Juhtumi kirjeldus?

	@property case_id type=text
	@caption Töö/tellimuse number

	@property started_date type=date field=created table=objects store=no
	@caption Sisestatud

	@property finished_date type=text default=0
	@caption Valminud

	@property locked_date type=checkbox
	@caption Lukustatud

	@property aborted_date type=date
	@caption Katkestatud

	@property abort_comment type=textarea
	@caption Katkestamise põhjus


default group=grp_case_material
	property used_materials type=table store=no
	caption Kasutatav materjal


@default group=grp_case_comments

	@property user_comments type=comments
	@caption Kommentaarid juhtumi ja tööde kohta


@default group=grp_case_workflow
	@property workflow_toolbar type=toolbar store=no no_caption=1
	@property manager type=text no_caption=1 store=no wrapchildren=1
	@property resource_tree type=text store=no no_caption=1 parent=manager
	@property workflow_table type=table store=no no_caption=1 parent=manager

	// @property set_plannable type=checkbox store=no ch_value=1
	// @caption Planeeri projekti


@default group=grp_case_schedule
	@property chart_navigation type=text store=no no_caption=1
	@property schedule_chart type=text store=no no_caption=1


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

class mrp_case extends class_base
{
	var $states = array (
		MRP_STATUS_NEW => t("Uus"),
		MRP_STATUS_PLANNED => t("Töösse planeeritud"),
		MRP_STATUS_INPROGRESS => t("Töös"),
		MRP_STATUS_ABORTED => t("Katkestatud"),
		MRP_STATUS_DONE => t("Valmis"),
		MRP_STATUS_LOCKED => t("Lukustatud"),
		MRP_STATUS_PAUSED => t("Paus",
		MRP_STATUS_DELETED => t("Kustutatud"),
		MRP_STATUS_ONHOLD => t("Plaanist väljas"),
		MRP_STATUS_ARCHIVED => t("Arhiveeritud"),
	);

	function mrp_case()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_case",
			"clid" => CL_MRP_CASE
		));
	}

	function get_property($arr)
	{
		$prop =& $arr["prop"];
		$retval = PROP_OK;
		$this_object =& $arr["obj_inst"];

		if ($arr["new"])
		{
			$this->mrp_workspace = $arr["request"]["mrp_workspace"];
		}

		switch($prop["name"])
		{
			case "header":
				if ($arr["new"])
				{
					return PROP_IGNORE;
				}
				$prop["value"] = $this->get_header($arr);
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
				$prop["value"] = $this->create_schedule_chart ($arr);
				break;

			case "resource_tree":
				$this->create_resource_tree ($arr);
				break;

			case "workflow_toolbar":
				$this->create_workflow_toolbar ($arr);
				break;

			case "workflow_table":
				$this->create_workflow_table ($arr);
				break;

			case "log":
				$this->_do_log($arr);
				break;

//!!! abordij22nus
				// case "do_abort":
				// if ($arr["obj_inst"]->prop("state") == MRP_STATUS_DONE || $arr["obj_inst"]->prop("state") == MRP_STATUS_NEW)
				// {
					// return PROP_IGNORE;
				// }
				// break;

			// case "set_plannable":
				// $state = $this_object->prop ("state");
				// $applicable_states = array (
					// MRP_STATUS_DELETED,
					// MRP_STATUS_DONE,
				// );

				// if (in_array ($state, $applicable_states))
				// {
					// $prop["value"] = 0;
					// $prop["disabled"] = true;
				// }
				// else
				// {
					// $applicable_states = array (
						// MRP_STATUS_PLANNED,
						// MRP_STATUS_INPROGRESS,
					// );
					// $prop["value"] = (in_array ($state, $applicable_states)) ? 1 : 0;
				// }
				// break;

			case "chart_navigation":
				$prop["value"] = $this->create_chart_navigation ($arr);
				break;
		}

		return $retval;
	}

	function callback_mod_reforb ($arr)
	{
		if ($this->mrp_workspace)
		{
			$arr["mrp_workspace"] = $this->mrp_workspace;
		}
	}

	function set_property($arr = array())
	{
		$this_object =& $arr["obj_inst"];
		$prop =& $arr["prop"];
		$retval = PROP_OK;

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
				$this->save_workflow_data ($arr);
				break;

//!!! set_plannable j22nus
			// case "set_plannable":
				// $state = $this_object->prop ("state");

				// if ($prop["value"] == 1)
				// {
					// $applicable_states = array (
						// MRP_STATUS_ABORTED,
						// MRP_STATUS_NEW,
						// MRP_STATUS_INPROGRESS,
						// MRP_STATUS_ONHOLD,
					// );

					// if (in_array ($state, $applicable_states))
					// {
						// $this_object->set_prop ("state", MRP_STATUS_PLANNED);
					// }
				// }
				// else
				// {
					// $applicable_states = array (
						// MRP_STATUS_INPROGRESS,
						// MRP_STATUS_PLANNED,
					// );

					// if (in_array ($state, $applicable_states))
					// {
						// $this_object->set_prop ("state", MRP_STATUS_ONHOLD);
					// }
				// }
				// break;
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

		if ( ($arr["new"]) and (is_oid ($arr["request"]["mrp_workspace"])) )
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
			// $workspace =& $this->get_current_workspace ($arr);
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

			$this->order_jobs ($arr);
		}

//!!! abordij22nus
		// if ($arr["obj_inst"]->prop("do_abort") == 1)
		// {
			// ### kill project - mark as done && all jobs as well
			// $arr["obj_inst"]->set_prop("state", MRP_STATUS_DONE);
			// $arr["obj_inst"]->save();

			// $jl = new object_list(array(
				// "class_id" => CL_MRP_JOB,
				// "project" => $arr["obj_inst"]->id()
			// ));
			// foreach($jl->arr() as $jo)
			// {
				// $jo->set_prop("state", MRP_STATUS_DONE);
				// $jo->save();
			// }
		// }
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
		$this_object =& $arr["obj_inst"];
		$chart = get_instance ("vcl/gantt_chart");
		$columns = 7;
		$hilighted_project = $this_object->id ();
		$hilighted_jobs = array ();

		### add row dfn-s, resource names
		$connections = $this_object->connections_from(array ("type" => "RELTYPE_MRP_PROJECT_JOB", "class_id" => CL_MRP_JOB));
		$project_resources = array ();
		$project_start = "NA";

		foreach ($connections as $connection)
		{
			$job = $connection->to ();
			$project_resources[] = $job->prop ("resource");
			$starttime = $job->prop ("starttime");
			$project_start = ($project_start === "NA") ? $starttime : min ($starttime, $project_start);
		}

		### add rows
		$project_resources = array_unique ($project_resources);

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
			}
		}

		### ...
		$range_start = mktime (0, 0, 0, date ("m", $project_start), date ("d", $project_start), date("Y", $project_start));
		$range_start = (int) ($arr["request"]["mrp_chart_start"] ? $arr["request"]["mrp_chart_start"] : $range_start);
		$range_end = (int) ($range_start + $columns * 86400);

		### get jobs in requested range & add bars
		// $list = new object_list (array (
			// "class_id" => CL_MRP_JOB,
			// "parent" => $this_object->prop ("jobs_folder"),
			// "state" => new obj_predicate_not (MRP_STATUS_DELETED),
			// "starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, $range_start, $range_end),
		// ));
		$res = $this->db_fetch_array (
			"SELECT `planned_length` FROM `mrp_job` ".
			"WHERE `state`!=" . MRP_STATUS_DELETED . " AND ".
			"`length` > 0 ".
			"ORDER BY `planned_length` DESC ".
			"LIMIT  1".
		"");
		$max_length = isset ($res[0]["planned_length"]) ? $res[0]["planned_length"] : 0;

		$jobs = $this->db_fetch_array (
			"SELECT `oid` FROM `mrp_job` ".
			"WHERE `state`!=" . MRP_STATUS_DELETED . " AND ".
			"`length` > 0 AND ".
			"`starttime` > " . ($range_start - $max_length) . " AND ".
			"`starttime` < " . $range_end . " AND ".
			"`resource` != 0 AND ".
			"`resource` IS NOT NULL".
		"");

		foreach ($jobs as $job)
		{
			if ($this->can ("view", $job["oid"]))
			{
				$job = obj ($job["oid"]);
				$resource_id = $job->prop ("resource");

				if (!in_array ($resource_id, $project_resources))
				{
					continue;
				}

				$length = $job->prop ("planned_length");
				$resource = obj ($resource_id);
				$start = $job->prop ("starttime");
				$hilight = ($job->prop ("project") == $hilighted_project) ? "hilighted" : "";
				$job_name = $this_object->name () . " - " . $resource->name ();

				$bar = array (
					"row" => $resource_id,
					"start" => $start,
					"type" => $hilight,
					"length" => $length,
					"uri" => html::get_change_url ($job["oid"]),
					"title" => $job_name . " (" . date (MRP_DATE_FORMAT, $start) . " - " . date (MRP_DATE_FORMAT, $start + $length) . ")"
// /* dbg */ . " [res: " . $resource_id . " job: " . $job->id () . " proj: " . $project_id . "]"
				);

				$chart->add_bar ($bar);
			}
		}

		### config
		$chart->configure_chart (array (
			"chart_id" => "master_schedule_chart",
			"style" => "aw",
			"start" => $range_start,
			"end" => $range_end,
			"columns" => $columns,
			"width" => 950,
		));

		### define columns
		$i = 0;
		$days = array ("P", "E", "T", "K", "N", "R", "L");

		while ($i < $columns)
		{
			$day_start = ($range_start + ($i * 86400));
			$day = date ("w", $day_start);
			$date = date ("j/m/Y", $day_start);
			$chart->define_column (array (
				"col" => ($i + 1),
				"title" => $days[$day] . " - " . $date,
			));
			$i++;
		}

		return $chart->draw_chart ();
	}

	function create_chart_navigation ($arr)
	{
		$start = (int) ($arr["request"]["mrp_chart_start"] ? $arr["request"]["mrp_chart_start"] : time ());
		$start_nav = array ();
		$period_length = 7 * 86400;

		$start_nav[] = html::href (array (
			"caption" => t("<< N&auml;dal tagasi"),
			"url" => aw_url_change_var ("mrp_chart_start", ($start - $period_length)),
		));
		$start_nav[] = html::href (array (
			"caption" => t("N&auml;dal edasi >>"),
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

		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta valitud töö(d)"),
			"confirm" => t("Kustutada kõik valitud tööd?"),
			"action" => "delete",
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
			$toolbar->add_button(array(
				"name" => "plan_btn",
				// "img" => "save.gif",
				"tooltip" => t("Planeeri"),
				"action" => "plan",
			));
		}

		### states for taking a project out of scheduling
		$applicable_states = array(
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PLANNED,
		);

		if (in_array($this_object->prop("state"), $applicable_states))
		{
			$toolbar->add_button(array(
				"name" => "onhold_btn",
				// "img" => "save.gif",
				"tooltip" => t("Võta planeerimisest v&auml;lja"),
				"action" => "set_on_hold",
			));
		}

		### states for aborting a project
		$applicable_states = array(
			MRP_STATUS_INPROGRESS,
		);

		if (in_array($this_object->prop("state"), $applicable_states))
		{
			$toolbar->add_button(array(
				"name" => "abort_btn",
				// "img" => "save.gif",
				"tooltip" => t("Katkesta projekt"),
				"confirm" => t("Katkesta projekt?"),
				"action" => "abort",
			));
		}

		### states for archiving a project
		$applicable_states = array(
			MRP_STATUS_DONE,
		);

		if (in_array($this_object->prop("state"), $applicable_states))
		{
			$toolbar->add_button(array(
				"name" => "archive_btn",
				// "img" => "save.gif",
				"tooltip" => t("Arhiveeri projekt"),
				"action" => "archive",
			));
		}
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
			"caption" => t("Eeldustööd"),
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
			"caption" => t("Eelpuhver (h)"),
		));
		$table->define_field(array(
			"name" => "post_buffer",
			"caption" => t("Järelpuhver (h)"),
		));
		$table->define_field(array(
			"name" => "comment",
			"caption" => t("Kommentaar"),
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
		$table->define_field(array(
			"name" => "open",
			"caption" => t("Ava"),
		));
		$table->define_chooser(array(
			"name" => "selection",
			"field" => "job_id",
		));

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
			$stag = '<span>';
			$etag = '</span>';
			$status = $this->states[$job->prop ("state")];

			switch ($job->prop ("state"))
			{
				case MRP_STATUS_NEW:
					$stag = '<span style="color: green;">';
					break;

				case MRP_STATUS_PLANNED:
					$stag = '<span style="color: blue;">';
					break;

				case MRP_STATUS_ABORTED:
					$stag = '<span style="color: red;">';
					break;

				case MRP_STATUS_INPROGRESS:
					$stag = '<span style="color: yellow;">';
					$disabled = true;
					break;

				case MRP_STATUS_DONE:
					$stag = '<span style="color: gray;">';
					$disabled = true;
					break;
			}

			$status = $stag . $status . $etag;

			### translate prerequisites from object id-s to execution orders
			$prerequisites = $job->prop ("prerequisites");
			$prerequisites = explode (",", $prerequisites);
			$prerequisites_translated = array ();

			foreach ($prerequisites as $oid)
			{
				if (is_oid ($oid)) //!!! selle can view v6tsin 2ra, sest kui siin on viga siis edasiminekul andmete riknemine laieneb. samas ei saa korrektse systeemi puhul siin viga esineda. nimelt ei ole siinne kasutajainputiga otseselt seotud. voldemar 2/18/2005.
				{
					$prerequisite_job = obj ($oid);
					$prerequisites_translated[] = $prerequisite_job->prop ("exec_order");
				}
			}

			$prerequisites = implode (",", $prerequisites_translated);

			### get & process field values
			$resource_name = $resource->name () ? $resource->name () : "...";
			$starttime = $job->prop ("starttime");
			$planned_start = $starttime ? date (MRP_DATE_FORMAT, $starttime) : "Planeerimata";

			$change_url = $this->mk_my_orb ("change", array (
				"id" => $job_id,
				"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			), "mrp_job");

			$comment = "";
			if ($job->prop("comment") != "")
			{
				$comment = html::href(array(
					"url" => "#",
					"caption" => t("JAH"),
					"title" => $job->prop("comment")
				));
			}

			$table->define_data(array(
				"open" => html::href(array(
					"caption" => t("Ava"),
					"url" => $change_url,
					)
				),
				"name" => $this_object->name () . " - " . $resource_name,
				"length" => html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-length",
					"size" => "3",
					"value" => round ((($job->prop ("length"))/3600), 2),
					"disabled" => $disabled,
					)
				),
				"pre_buffer" => html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-pre_buffer",
					"size" => "3",
					"value" => round ((($job->prop ("pre_buffer"))/3600), 2),
					"disabled" => $disabled,
					)
				),
				"post_buffer" => html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-post_buffer",
					"size" => "3",
					"value" => round ((($job->prop ("post_buffer"))/3600), 2),
					"disabled" => $disabled,
					)
				),
				"prerequisites" => html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-prerequisites",
					"size" => "4",
					"value" => $prerequisites,
					"disabled" => $disabled,
					)
				),
				"exec_order" => $job->prop ("exec_order"),
				"starttime" => $planned_start,
				"status" => $status,
				"job_id" => $job_id,
				"comment" => $comment
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

		foreach ($connections as $connection)
		{
			$job = $connection->to ();
			$jobs[$job->prop ("exec_order")] = $job->id ();
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
					$status = $job->prop ("job_status");

					if ( ($status == MRP_STATUS_INPROGRESS) or ($status == MRP_STATUS_DONE) )
					{
						break;
					}

					switch ($property)
					{
						case "prerequisites":
							### translate prerequisites from execution orders to object id-s
							$prerequisites = $job->prop ("prerequisites");
							$prerequisites = explode (",", $prerequisites);
							$prerequisites_translated = array ();

							foreach ($prerequisites as $oid)
							{
								if (is_oid ($oid))
								{
									$prerequisite_job = obj ($oid);
									$prerequisites_translated[] = $prerequisite_job->prop ("exec_order");
								}
								else
								{
									$errors = "Viga "; ///!!!mingi veateade teha? mida siin edasi teha?
								}
							}

							$prerequisites = implode (",", $prerequisites_translated);

							if ($value != $prerequisites)
							{
								$prerequisites_userdata = explode (",", $value);
								$prerequisites = array ();

								foreach ($prerequisites_userdata as $prerequisite)
								{
									settype ($prerequisite, "integer");
									$job_id = $jobs[$prerequisite];

									if (is_oid ($job_id))
									{
										$prerequisites[] = $job_id;
									}
									else
									{
										$errors .= "Viga. "; ///!!!
									}
								}

								$prerequisites = implode (",", $prerequisites);
								$job->set_prop ("prerequisites", $prerequisites);
							}
							break;

						case "length":
						case "pre_buffer":
						case "post_buffer":
							$value = $this->safe_settype_float ($value);

							switch ($property)
							{
								case "length":
									$job->set_prop ("length", (round ($value * 3600)));
									break;

								case "pre_buffer":
									$job->set_prop ("pre_buffer", (round ($value * 3600)));
									break;

								case "post_buffer":
									$job->set_prop ("post_buffer", (round ($value * 3600)));
									break;
							}
							break;

						case "resource":
							if (is_oid ($value))
							{
								### delete currently used/connected resource
								$connections = $job->connections_from(array ("type" => "RELTYPE_MRP_RESOURCE", "class_id" => CL_MRP_RESOURCE));

								foreach ($connections as $connection)
								{
									$connection->delete ();
								}

								$job->connect (array (
									"to" => obj ($value),
									"reltype" => "RELTYPE_MRP_RESOURCE",
								));
								$job->set_prop ("resource", $value);
								$job->save ();
							}
							else
							{
								///!!! spetsif veateade
								$errors .= "Viga";
							}
							break;
					}

					$job->save ();
				}
				else
				{
					$errors .= "Viga";///!!!
				}
			}
		}

		if ($errors)
		{
			return PROP_FATAL_ERROR;
		}
		else
		{
			return PROP_OK;
		}
	}

	/**
		@attrib name=order_jobs
		@param oid required type=int
	**/
	function order_jobs ($arr = array ())
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
				$job->save ();
			}
			else
			{
				veryseriouserror;///!!!
			}
		}
	}

	// converts workflow to sequence of jobs. concurrent jobs are ordered as given in Input
	// Input: $workflow = array (job_id_1 => array (prerequisite_job_id_m, ...), ...)
	// if job n has no prerequisites then: job_id_n => array (0)
	// Output: ordered non-associative array of job id-s: array (0 => job_id_1, ...)
	function workflow2sequence ($workflow_input = array ())
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
		$job->save ();

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
			$this->order_jobs ($arr);
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
		$project->save ();

		### delete project's jobs
		$connections = $project->connections_from (array ("type" => "RELTYPE_MRP_PROJECT_JOB"));

		foreach ($connections as $connection)
		{
			$job = $connection->to ();
			$job->delete ();
		}

		$applicable_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_INPROGRESS,
		);

		if (in_array ($project->prop ("state"), $applicable_states))
		{
			$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if ($workspace)
			{
				post_message (MSG_MRP_RESCHEDULING_NEEDED, array ("mrp_workspace" => $workspace->id ()));
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
		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			return false;
		}

		### states for starting a project
		$applicable_states = array (
			MRP_STATUS_PLANNED,
		);

		if (in_array ($project->prop ("state"), $applicable_states))
		{
			### start project
			$project->set_prop ("state", MRP_STATUS_INPROGRESS);
			$project->set_prop ("progress", time ());
			$project->save ();

			### log change
			$ws = get_instance (CL_MRP_WORKSPACE);
			$ws->mrp_log ($project->id (), NULL, "Projekt l&auml;ks t&ouml;&ouml;sse");
			return true;
		}
		else
		{
			return false;
		}
	}

/**
    @attrib name=finish
	@param id required type=int
**/
	function finish ($arr)
	{
		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			return false;
		}

		### check if all jobs are done
		$job_list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"project" => $project->id (),
		));
		$jobs = $job_list->count ();

		### states for jobs that allow finishing a project
		$applicable_states = array (
			MRP_STATUS_DONE,
		);
		$jobs_done = $job_list->filter (array (
			"state" => $applicable_states,
		));

		### states for finishing a project
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (in_array ($project->prop ("state"), $applicable_states) and ($jobs_done == $jobs))
		{
			### finish project
			$project->set_prop ("state", MRP_STATUS_DONE);
			$project->save ();

			### log event
			$ws->mrp_log(
				$project->id(),
				NULL,
				"Projekt l&otilde;petati"
			);

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
		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			return false;
		}

		### states for aborting a project
		$applicable_states = array (
			MRP_STATUS_INPROGRESS,
		);

		if (in_array ($this_object->prop ("state"), $applicable_states))
		{
			### abort project
			$this_object->set_prop ("state", MRP_STATUS_ABORTED);
			$this_object->save ();

			### log event
			$ws = get_instance(CL_MRP_WORKSPACE);
			$ws->mrp_log ($this_object->id (), NULL, "Projekt katkestati");

			return true;
		}
		else
		{
			return false;
		}
	}

/**
    @attrib name=archive
	@param id required type=int
**/
	function archive ($arr)
	{
		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			return false;
		}

		### states for archiving a project
		$applicable_states = array(
			MRP_STATUS_DONE,
		);

		if (in_array($project->prop("state"), $applicable_states))
		{
			### archive project
			$project->set_prop("state", MRP_STATUS_ARCHIVED);
			$project->save();

			### log event
			$ws->mrp_log(
				$project->id(),
				NULL,
				"Projekt arhiveeriti"
			);

			return true;
		}
		else
		{
			return false;
		}
	}

/**
    @attrib name=plan
	@param id required type=int
**/
	function plan ($arr)
	{
		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			return false;
		}

		### states for planning a project
		$applicable_states = array(
			MRP_STATUS_NEW,
			MRP_STATUS_ABORTED,
			MRP_STATUS_ONHOLD,
		);

		if (in_array($project->prop("state"), $applicable_states))
		{
			### plan project
			$workspace = $project->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if ($workspace)
			{
				post_message (MSG_MRP_RESCHEDULING_NEEDED, array ("mrp_workspace" => $workspace->id ()));
			}

			### log event
			$ws->mrp_log(
				$project->id(),
				NULL,
				"Projekt sisestati planeerimisse"
			);

			return true;
		}
		else
		{
			return false;
		}
	}

/**
    @attrib name=set_on_hold
	@param id required type=int
**/
	function set_on_hold ($arr)
	{
		if (is_oid($arr["id"]))
		{
			$project = obj($arr["id"]);
		}
		else
		{
			return false;
		}

		### states for taking a project out of schedule
		$applicable_states = array(
			MRP_STATUS_INPROGRESS,
			MRP_STATUS_PLANNED,
		);

		if (in_array($project->prop("state"), $applicable_states))
		{
			### archive project
			$project->set_prop("state", MRP_STATUS_ONHOLD);
			$project->save();

			### log event
			$ws->mrp_log(
				$project->id(),
				NULL,
				"Projekt võeti planeeimisest välja"
			);

			return true;
		}
		else
		{
			return false;
		}
	}
}

?>
