<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_case.aw,v 1.5 2005/01/25 12:30:28 voldemar Exp $
// mrp_case.aw - Juhtum/Projekt
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_MRP_CASE, on_save_case)

@classinfo syslog_type=ST_MRP_CASE relationmgr=yes

@tableinfo mrp_case index=oid master_table=objects master_index=oid

@groupinfo grp_case_data caption="Projekti andmed"
@groupinfo grp_case_details caption="Projekti kirjeldus"
@groupinfo grp_case_material caption="Kasutatav materjal"
@groupinfo grp_case_workflow caption="Ressursid ja töövoog"
@groupinfo grp_case_schedule caption="Kalender" submit=no
@groupinfo grp_case_comments caption="Kommentaarid"


@default group=general
	@property name type=textbox table=objects field=name
	@caption Projekti nr.

@default table=mrp_case
	@property starttime type=datetime_select
	@caption Alustamisaeg (materjalide saabumine)

	@property due_date type=datetime_select
	@caption Valmimistähtaeg

	@property project_priority type=textbox
	@caption Projekti prioriteet

	@property sales_priority type=select
	@caption Prioriteedihinnang müügimehelt

	@property state type=text
	@caption Staatus

	@property customer type=relpicker reltype=RELTYPE_MRP_CUSTOMER
	@caption Klient

	@property extern_id type=hidden

@default table=objects
@default field=meta
@default method=serialize
	@property planned_date type=text store=no editonly=1
	@caption Planeeritud valmimisaeg


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


@default group=grp_case_material
	@property used_materials type=table store=no
	@caption Kasutatav materjal


@default group=grp_case_comments
	@property user_comments type=table store=no
	@caption Kommentaarid juhtumi ja tööde kohta


@default group=grp_case_workflow
	@property workflow_toolbar type=toolbar store=no no_caption=1
	@property manager type=text no_caption=1 store=no wrapchildren=1
	@property resource_tree type=text store=no no_caption=1 parent=manager
	@property workflow_table type=table store=no no_caption=1 parent=manager


@default group=grp_case_schedule
	@property schedule_chart type=text store=no no_caption=1



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
  `due_date` int(10) unsigned default NULL,
  `customer_priority` int(10) unsigned default NULL,
  `project_priority` int(10) unsigned default NULL,
  `state` tinyint(2) unsigned default '1',
  `extern_id` int(11) unsigned default NULL,
  `customer` int(11) unsigned default NULL,

	PRIMARY KEY  (`oid`),
	UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

### resource types
define ("MRP_RESOURCE_MACHINE", 1);
define ("MRP_RESOURCE_OUTSOURCE", 2);

### states
define ("MRP_STATUS_NEW", 1);
define ("MRP_STATUS_PLANNED", 2);
define ("MRP_STATUS_INPROGRESS", 3);
define ("MRP_STATUS_ABORTED", 4);
define ("MRP_STATUS_DONE", 5);
define ("MRP_STATUS_LOCKED", 6);
define ("MRP_STATUS_OVERDUE", 7);
define ("MRP_STATUS_DELETED", 8);

### misc
define ("MRP_DATE_FORMAT", "j/m/Y H.i");

class mrp_case extends class_base
{
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

		if ($arr["new"])
		{
			$this->mrp_workspace = $arr["request"]["mrp_workspace"];
		}

		switch($prop["name"])
		{
			case "state":
				$states = array (
					MRP_STATUS_NEW => "Uus",
					MRP_STATUS_PLANNED => "Töösse planeeritud",
					MRP_STATUS_INPROGRESS => "Töös",
					MRP_STATUS_ABORTED => "Katkestatud",
					MRP_STATUS_DONE => "Valmis",
					MRP_STATUS_OVERDUE => "Üle tähtaja",
				);
				$prop["value"] = $states[$prop["value"]] ? $states[$prop["value"]] : "Määramata";
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
		$prop =& $arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "workflow_table":
				$this->save_workflow_data ($arr);
				break;
		}

		return $retval;
	}

	function callback_post_save ($arr)
	{
		$this_object = $arr["obj_inst"];

		if ( ($arr["new"]) and (is_oid ($arr["request"]["mrp_workspace"])) )
		{
			### set status
			$this_object->set_prop ("state", MRP_STATUS_NEW);

			### connect newly created obj. to workspace from which the req. was made
			$workspace = obj ($arr["request"]["mrp_workspace"]);
			$projects_folder = $workspace->prop ("projects_folder");
			$this_object->connect (array (
				"to" => $workspace,
				"reltype" => RELTYPE_MRP_OWNER,
			));
			$this_object->set_parent ($projects_folder);
			$this_object->save ();

			// ### create n new jobs. connect, name & ... them
			// if ($arr["request"]["number_of_jobs"])
			// {
				// $jobs_folder = $workspace->prop ("jobs_folder");
				// $job_number = $arr["request"]["number_of_jobs"];

				// while ($job_number)
				// {
					// $arr["mrp_job_exec_order"] = $job_number;
					// $this->add_job ($arr);
					// $job_number--;
				// }

				// $this->correct_job_order ($arr);
			// }
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

			$this->correct_job_order ($arr);
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

		$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_OWNER, "class_id" => CL_MRP_WORKSPACE));

		foreach ($connections as $connection)
		{
			$workspace = $connection->to ();
			break;
		}

		return $workspace;
	}

	function create_schedule_chart ($arr)
	{
		$this_object = $arr["obj_inst"];
		$chart = get_instance ("vcl/gantt_chart");

		### get  project jobs
		$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_PROJECT_JOB, "class_id" => CL_MRP_JOB));
		$project_start = 100000000000000000;
		$project_length = 0;
		$jobs = array ();
		$project_resources = array ();

		foreach ($connections as $connection)
		{
			$job = $connection->to ();
			$length = $job->prop ("pre_buffer") + $job->prop ("length") + $job->prop ("post_buffer");
			$resource = $job->prop ("resource");
			$project_length += $length;
			$jobs[] = $job;
			$project_resources[] = $resource;
			$starttime = $job->prop ("starttime");
			$project_start = ($starttime < $project_start) ? $starttime : $project_start;

			$bar = array (
				"row" => $resource,
				"start" => $starttime,
				"length" => $length,
				"title" => $job->name (),
				"hilight" => true,
			);
			$chart->add_bar ($bar);
		}

		$project_start = ($project_start == 100000000000000000) ? $this_object->prop ("starttime") : $project_start;
		$project_resources = array_unique ($project_resources);

		$chart->configure_chart (array (
			"chart_id" => "project_schedule_chart",
			"start" => $project_start,
			"end" => $this_object->prop("due_date"),
			"cells" => 1,
			"cell_size" => $project_length,
		));

		foreach ($project_resources as $resource_id)
		{
			if (!$this->can("view", $resource_id))
			{
				continue;
			}
			$resource = obj ($resource_id);
			$chart->add_row (array (
				"name" => $resource_id,
				"title" => $resource->name (),
				"uri" => html::get_change_url ($resource_id)
			));
		}

		return $chart->draw_chart ();
	}

	function create_resource_tree ($arr = array ())
	{
		$this_object =& $arr["obj_inst"];
		$workspace =& $this->get_current_workspace ($arr);
		$resources_folder = $workspace->prop ("resources_folder");
		$resource_tree = new object_tree (array (
			"parent" => $resources_folder,
			"class_id" => array (CL_MENU, CL_MRP_RESOURCE),
			// "sort_by" => "objects.jrk",
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
		));

		$arr["prop"]["value"] = $tree->finalize_tree ();
	}

	function create_workflow_toolbar ($arr = array ())
	{
		$toolbar =& $arr["prop"]["toolbar"];
		// $toolbar->add_button(array(
			// "name" => "add",
			// "img" => "new.gif",
			// "tooltip" => "Lisa uus töö",
			// "action" => "add_job",
		// ));
		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta valitud töö(d)",
			"confirm" => "Kustutada kõik valitud tööd?",
			"action" => "delete",
		));
		$toolbar->add_separator();
		$toolbar->add_button(array(
			"name" => "test",
			"img" => "preview.gif",
			"tooltip" => "Testi/hinda valmimisaega",
			"action" => "test",
		));
		$toolbar->add_button(array(
			"name" => "plan",
			"img" => "save.gif",
			"tooltip" => "Planeeri",
			"action" => "plan",
		));
	}

	function create_workflow_table ($arr)
	{
		$this_object = $arr["obj_inst"];

		### init. table
		$table =& $arr["prop"]["vcl_inst"];
		$table->define_field(array(
			"name" => "exec_order",
			"caption" => "Nr.",
		));
		$table->define_field(array(
			"name" => "prerequisites",
			"caption" => "Eeldustööd",
		));
		$table->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
		));
		$table->define_field(array(
			"name" => "resource",
			"caption" => "Ressurss",
		));
		$table->define_field(array(
			"name" => "length",
			"caption" => "Pikkus (h)",
		));
		$table->define_field(array(
			"name" => "pre_buffer",
			"caption" => "Eelpuhver (h)",
		));
		$table->define_field(array(
			"name" => "post_buffer",
			"caption" => "Järelpuhver (h)",
		));
		$table->define_field(array(
			"name" => "status",
			"caption" => "Staatus",
		));
		$table->define_field(array(
			"name" => "starttime",
			"caption" => "Töösse",
		));
		$table->define_field(array(
			"name" => "open",
			"caption" => "Ava",
		));
		$table->define_chooser(array(
			"name" => "selection",
			"field" => "job_id",
		));

		$table->set_numeric_field ("exec_order");
		$table->set_default_sortby ("exec_order");
		$table->set_default_sorder ("asc");

		### define data for each connected job
		$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_PROJECT_JOB, "class_id" => CL_MRP_JOB));

		foreach ($connections as $connection)
		{
			$job = $connection->to ();
			$job_id = $job->id ();
			$resource_id = $job->prop ("resource");
			$resource = obj ($resource_id);
			$status = $job->prop ("job_status");
			$disabled = false;
			$stag = '<span>';
			$etag = '</span>';

			switch ($status)
			{
				case MRP_STATUS_NEW:
					$stag = '<span style="color: green;">';
					$status = 'Uus';
					break;

				case MRP_STATUS_PLANNED:
					$stag = '<span style="color: yellow;">';
					$status = 'Planeeritud';
					break;

				case MRP_STATUS_ABORTED:
					$stag = '<span style="color: red;">';
					$status = 'Katkestatud';
					break;

				case MRP_STATUS_INPROGRESS:
					$stag = '<span style="color: blue;">';
					$status = 'Töös';
					$disabled = true;
					break;

				case MRP_STATUS_DONE:
					$stag = '<span style="color: black;">';
					$status = 'Valmis';
					$disabled = true;
					break;
			}

			### translate prerequisites from object id-s to execution orders
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
			}

			$prerequisites = implode (",", $prerequisites_translated);

			### get & process field values
			$resource_name = $resource->name () ? $resource->name () : "-";
			$starttime = $job->prop ("starttime");
			$planned_start = $starttime ? date (MRP_DATE_FORMAT, $starttime) : "Planeerimata";

			$change_url = $this->mk_my_orb("change", array(
				"id" => $job_id,
				"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			), "mrp_job");

			$table->define_data(array(
				"open" => html::href(array(
					"caption" => "Ava",
					"url" => $change_url,
					)
				),
				"name" => html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-name",
					"size" => "4",
					"value" => $job->name (),
					"disabled" => $disabled,
					)
				),
				"resource" => $resource_name,
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
			));
		}
	}

	function save_workflow_data ($arr)
	{
		$this_object = $arr["obj_inst"];
		$orders = array ();
		$errors = false;
		$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_PROJECT_JOB, "class_id" => CL_MRP_JOB));
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
						case "name":
							$job->set_name ($value);
							break;

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
							$decimal = strstr ($value, ",") ? strstr ($value, ",") : strstr ($value, ".");
							$decimal = substr ($decimal, 1);
							settype ($decimal, "int");
							settype ($value, "int");
							$value = $value . "." . $decimal;
							settype ($value, "float");

							switch ($property)
							{
								case "length":
									$job->set_prop ("length", (ceil ($value * 3600)));
									break;

								case "pre_buffer":
									$job->set_prop ("pre_buffer", (ceil ($value * 3600)));
									break;

								case "post_buffer":
									$job->set_prop ("post_buffer", (ceil ($value * 3600)));
									break;
							}
							break;

						case "resource":
							if (is_oid ($value))
							{
								### delete currently used/connected resource
								$connections = $job->connections_from(array ("type" => RELTYPE_MRP_RESOURCE, "class_id" => CL_MRP_RESOURCE));

								foreach ($connections as $connection)
								{
									$connection->delete ();
								}

								$job->connect (array (
									"to" => obj ($value),
									"reltype" => RELTYPE_MRP_RESOURCE,
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

	function correct_job_order ($arr = array ())
	{
		$prerequisite_index = array ();
		$job_index = array ();
		$prerequisites = array ();
		$jobs = array ();

		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}

		if (is_object ($arr["obj_inst"]))
		{
			$this_object =& $arr["obj_inst"];
		}

		$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_PROJECT_JOB, "class_id" => CL_MRP_JOB));

		foreach ($connections as $connection)
		{
			$job = $connection->to ();
			$job_id = $job->id ();
			$job_index[] = $job_id;
			$job_prerequisites = explode (",", $job->prop ("prerequisites"));
			$prerequisite_index[$job_id] = $job_prerequisites;

			foreach ($job_prerequisites as $prerequisite)
			{
				$prerequisites[] = (int) $prerequisite;
			}
		}

		$prerequisites = array_unique ($prerequisites);
		$last_job = array_diff ($job_index, $prerequisites);
		$last_job_id = reset ($last_job);

		if (!$last_job_id)
		{
			veryseriouserror;///!!!
		}
		else
		{
			$jobs[] = $last_job_id;
			$pointer = 0;
		}

		while (1)
		{
			if (array_key_exists ($pointer, $jobs))
			{
				$job_id = $jobs[$pointer];
				$pointer++;
			}
			else
			{
				break;
			}

			$prerequisites = $prerequisite_index[$job_id];

			foreach ($prerequisites as $job_id)
			{
				if ((!in_array ($job_id, $jobs)) and $job_id)
				{
					$jobs[] = $job_id;
				}
			}
		}

		krsort ($jobs);
		$job_count = count ($jobs);

		foreach ($jobs as $key => $job_id)
		{
			if (is_oid($job_id))
			{
				$job = obj ($job_id);
				$job->set_prop ("exec_order", ($job_count - $key));
				$job->save ();
			}
			else
			{
				veryseriouserror;///!!!
			}
		}
	}

/**
    @attrib name=add_job
	@param id required type=int
**/
	function _add_job ($arr)
	{
		$arr["obj_inst"] = obj ($arr["id"]);
		$this->add_job ($arr);

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
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
		$connections = $this_object->connections_from (array ("type" => RELTYPE_MRP_PROJECT_JOB, "class_id" => CL_MRP_JOB));
		$resource_id = $arr["mrp_new_job_resource"];
		$job_number = (count ($connections) + 1);

		if (is_oid ($resource_id))
		{
			$resource = obj ($resource_id);
			$name = $this_object->name () . " - " . $resource->name ();
			$pre_buffer = $resource->prop ("default_pre_buffer");
			$post_buffer = $resource->prop ("default_post_buffer");
		}
		else
		{
			$resource = false;
			$name = $this_object->name () . " - ...";
			$pre_buffer = 0;
			$post_buffer = 0;
		}

		if (!($jobs_folder = $workspace->prop ("jobs_folder")))
		{
			return false;//!!! veateade teha?
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
		   "name" => $name,
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
		$job->save ();

		if ($resource)
		{
			$job->connect (array (
				"to" => $resource,
				"reltype" => RELTYPE_MRP_RESOURCE,
			));
		}

		$job->connect (array (
			"to" => $this_object,
			"reltype" => RELTYPE_MRP_PROJECT,
		));
		$this_object->connect (array (
			"to" => $job,
			"reltype" => RELTYPE_MRP_PROJECT_JOB,
		));

		return true;
	}

	/**
		@attrib name=delete
		@param id required type=int
	**/
	function delete ($arr)
	{
		$sel = $arr["selection"];
		$this_object = obj ($arr["id"]);

		if (is_array($sel))
		{
			$ol = new object_list(array(
				"oid" => array_keys($sel),
			));
			for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				if ($this->can("delete", $o->id()))
				{
					$class = $o->class_id ();

					if ($class = CL_MRP_JOB)
					{
						$o->set_prop ("state", MRP_STATUS_DELETED);
					}

					$o->delete ();
				}
			}

			if ($class = CL_MRP_JOB)
			{
				$this->correct_job_order ($arr);
			}
		}

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_case"); echo $return_url;
		return $return_url;
	}

	function on_save_case($arr)
	{
		// save data to prisma server
		$i = get_instance(CL_MRP_PRISMA_IMPORT);
		$i->write_proj($arr["oid"]);
	}
}

?>
