<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_case.aw,v 1.2 2004/12/08 12:23:32 voldemar Exp $
// mrp_case.aw - Juhtum/Projekt
/*

@classinfo syslog_type=ST_MRP_CASE relationmgr=yes

@tableinfo mrp_case index=oid master_table=objects master_index=oid

@groupinfo grp_case_details caption="Projekti kirjeldus"
@groupinfo grp_case_material caption="Kasutatav materjal"
@groupinfo grp_case_workflow caption="Ressursid ja töövoog"
@groupinfo grp_case_schedule caption="Kalender" submit=no
@groupinfo grp_case_comments caption="Juhtumi kommentaarid"


@default group=general
@default table=mrp_case
	@property starttime type=datetime_select
	@caption Alustamisaeg

	@property due_date type=datetime_select
	@caption Valmimistähtaeg

	@property project_priority type=textbox
	@caption Projekti prioriteet

	@property customer_priority type=textbox
	@caption Kliendi prioriteet

	@property state type=text
	@caption Staatus

@default table=objects
@default field=meta
@default method=serialize
	// @property number_of_jobs type=textbox store=no newonly=1 size=3
	// @caption Tööde arv

	@property available_resources type=hidden no_caption=1


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

@reltype MRP_OWNER value=1 clid=CL_MRP_WORKSPACE
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
			$used_resources = explode (",", $this_object->prop ("available_resources"));
			$selected_resources = explode (",", $arr["request"]["mrp_resourcetree_data"]);

			if ($used_resources != $selected_resources)
			{
				$new_resources = array_diff ($selected_resources,$used_resources);

				foreach ($new_resources as $resource_id)
				{
					$this->add_job ($arr);
				}
			}

			### make changes to used resources
			$this_object->set_prop ("available_resources", $arr["request"]["mrp_resourcetree_data"]);
			$this_object->save ();
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
			$workspace = $connection->to();
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
		$project_length = 0;
		$jobs = array ();
		$project_resources = array ();

		foreach ($connections as $connection)
		{
			$job = $connection->to();
			$length = $job->prop ("length") + $job->prop ("buffer");
			$resource = $job->prop ("resource");
			$project_length += $length;
			$jobs[] = $job;
			$project_resources[] = $resource;

			$bar = array (
				"row" => $resource,
				"start" => $job->prop ("starttime"),
				"length" => $length,
				"title" => $job->name (),
				"hilight" => true,
			);
			$chart->add_bar ($bar);
		}

		$project_resources = array_unique ($project_resources);

		$chart->configure_chart (array (
			"chart_id" => "project_schedule_chart",
			"start" => $this_object->prop ("starttime"),
			"end" => $this_object->prop("due_date"),
			"cells" => 1,
			"cell_size" => $project_length,
		));

		foreach ($project_resources as $resource_id)
		{
			$resource = obj ($resource_id);
			$chart->add_row (array (
				"name" => $resource_id,
				"title" => $resource->name (),
				"uri" => html::get_change_url($resource_id)
			));
		}

		return $chart->draw_chart ();
	}

	function create_resource_tree ($arr = array())
	{
		$this_object =& $arr["obj_inst"];
		$workspace =& $this->get_current_workspace ($arr);
		$resources_folder = $workspace->prop ("resources_folder");
		$resource_tree = new object_tree(array(
			"parent" => $resources_folder,
			"class_id" => array(CL_MENU, CL_MRP_RESOURCE),
		));
		$checked_nodes = explode (",", $this_object->prop ("available_resources"));

		classload("vcl/treeview");
		$tree = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML_WITH_CHECKBOXES,
				"tree_id" => "resourcetree",
				"checked_nodes" => $checked_nodes,
				"persist_state" => true,
				"checkbox_data_var" => "mrp_resourcetree_data",
			),
			"root_item" => obj ($resources_folder),
			"ot" => $resource_tree,
			"var" => "mrp_resource_tree_active_item",
			"checkbox_class_filter" => array (CL_MRP_RESOURCE),
		));

		// $tree->set_selected_item($arr["request"]["meta"]);
		$arr["prop"]["value"] = $tree->finalize_tree();
	}

	function create_workflow_toolbar ($arr = array())
	{
		$toolbar =& $arr["prop"]["toolbar"];
		$toolbar->add_button(array(
			"name" => "add",
			"img" => "new.gif",
			"tooltip" => "Lisa uus töö",
			"action" => "add_job",
		));
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
			"name" => "length",
			"caption" => "Pikkus (h)",
		));
		$table->define_field(array(
			"name" => "buffer",
			"caption" => "Puhver (h)",
		));
		$table->define_field(array(
			"name" => "resource",
			"caption" => "Ressurss",
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

		### get available resources for html select
		$available_resources = explode (",", $this_object->prop ("available_resources"));
		$select_resource_options = array ("0" => "");

		foreach ($available_resources as $resource_id)
		{
			if (is_oid ($resource_id))
			{
				$resource = obj ($resource_id);
				$select_resource_options[$resource->id ()] = $resource->name ();
			}
		}

		### define data for each connected job
		$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_PROJECT_JOB, "class_id" => CL_MRP_JOB));

		foreach ($connections as $connection)
		{
			$job = $connection->to();
			$job_id = $job->id ();
			$selected_resource = NULL;
			$status = $job->prop ("job_status");
			$disabled = false;
			$etag = '</span>';
			$stag = '<span>';

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

			### find currently used/connected resource
			$job_connections = $job->connections_from(array ("type" => RELTYPE_MRP_RESOURCE, "class_id" => CL_MRP_RESOURCE));

			foreach ($job_connections as $job_connection)
			{
				$resource = $job_connection->to ();
				$selected_resource = $resource->id ();
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
				else
				{
					///!!!mingi veateade teha? mida siin edasi teha?
				}
			}

			$prerequisites = implode (",", $prerequisites_translated);


			###
			$options = $select_resource_options;

			if ($selected_resource)
			{
				if (count ($select_resource_options) < 2)
				{
					$options = array ($selected_resource => $resource->name ());
				}

				unset ($options["0"]);
			}

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
				"resource" => html::select(array(
					"name" => "mrp_workflow_job-" . $job_id . "-resource",
					"options" => $options,
					"value" => $selected_resource,
					"disabled" => $disabled,
					)
				),
				"length" => html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-length",
					"size" => "3",
					"value" => round ((($job->prop ("length"))/3600), 2),
					"disabled" => $disabled,
					)
				),
				"buffer" => html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-buffer",
					"size" => "3",
					"value" => round ((($job->prop ("buffer"))/3600), 2),
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
				"starttime" => date ("j/m/Y H.i", $job->prop ("starttime")),
				"status" => $status,
				"job_id" => $job_id,
			));
		}
	}

	function save_workflow_data ($arr)
	{
		$this_object = $arr["obj_inst"];
		$jobs = array ();
		$orders = array ();
		$order_changed = false;
		$errors = false;

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
					else
					{
						$order_changed = true;
					}

					switch ($property)
					{
						case "name":
							$job->set_name ($value);
							break;

						case "prerequisites":
							$prerequisites = $job->prop ("prerequisites");

							if ($value != $prerequisites)
							{
								$prerequisites_userdata = explode (",", $value);
								$prerequisites = array ();
								$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_PROJECT_JOB, "class_id" => CL_MRP_JOB));
								$jobs = array ();

								foreach ($connections as $connection)
								{
									$job = $connection->to ();
									$jobs[$job->prop ("exec_order")] = $job->id ();
								}

								foreach ($prerequisites_userdata as $value)
								{
									///!!! otsida t88objektide oid-d siia??
									///!!! kontrollida kas sellised jrk nr-d on olemas?
									settype ($value, "integer");
									$job_id = $jobs[$value];

									if (is_oid ($job_id))
									{
										$prerequisites[] = $job_id;
									}
								}

								$prerequisites = implode (",", $prerequisites);
								$job->set_prop ("prerequisites", $prerequisites);
								$this->correct_job_order ($arr);
							}
							break;

						case "length":
						case "buffer":
							$decimal = strstr ($value, ",") ? strstr ($value, ",") : strstr ($value, ".");
							$decimal = substr ($decimal, 1);
							settype ($decimal, "int");
							settype ($value, "int");
							$value = $value . "." . $decimal;
							settype ($value, "float");

							switch ($property)
							{
								case "length":
									$job->set_prop ("length", ($value * 3600));
									break;

								case "buffer":
									$job->set_prop ("buffer", ($value * 3600));
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
					$errors .= "Viga";
				}
			}
		}

		if ($order_changed)
		{
			$this->correct_job_order ($arr);
		}

		if ($errors)
		{
			return PROP_ERROR;
		}
		else
		{
			return PROP_OK;
		}
	}

	function correct_job_order ($arr)
	{
		if (is_oid ($arr["id"]))
		{
			$this_object = obj ($arr["id"]);
		}

		if (is_object ($arr["obj_inst"]))
		{
			$this_object =& $arr["obj_inst"];
		}

		$connections = $this_object->connections_from(array ("type" => RELTYPE_MRP_PROJECT_JOB, "class_id" => CL_MRP_JOB));
		$this->prerequisite_index = array ();
		$this->job_index = array ();
		$this->jobs = array ();

		foreach ($connections as $connection)
		{
			$job = $connection->to ();
			$prerequisites = explode (",", $job->prop ("prerequisites"));

			foreach ($prerequisites as $prerequisite)
			{
				$this->prerequisite_index[] = $prerequisite ? $prerequisite : "x";
				$this->job_index[] = $job->id ();
			}
		}

		$prerequisite = "x";
		$this->order_jobs ($prerequisite);
		$i = 0;

		foreach ($this->jobs as $job_id)
		{
			if (!is_oid($job_id))
			{
				continue;
			}
			$i++;
			$job = obj ($job_id);
			$job->set_prop ("exec_order", $i);
			$job->save ();
		}
	}

	function order_jobs ($prerequisite)
	{
		$keys = array_keys ($this->prerequisite_index, $prerequisite);

		foreach ($keys as $key)
		{
			$this->jobs[] = $job_index[$key];
			$prerequisite = $job_index[$key];
			$this->order_jobs ($prerequisite);
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
		$job_number = (count ($connections) + 1);
		$name = "Töö " . $job_number;

		if (!($jobs_folder = $workspace->prop ("jobs_folder")))
		{
			return false;//!!! veateade teha?
		}

		$job =& new object (array (
		   "name" => $name,
		   "parent" => $jobs_folder,
		   // "class_id" => CL_MRP_JOB,
		));
		$job->set_class_id(CL_MRP_JOB);
		$job->set_prop ("state", MRP_STATUS_NEW);
		$job->set_prop ("exec_order", $job_number);
		$job->set_prop ("prerequisites", ($job_number - 1));
		$job->set_prop ("project", $this_object->id ());
		$job->save ();
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
}

?>
