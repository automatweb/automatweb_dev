<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_case.aw,v 1.1 2004/11/15 16:03:39 voldemar Exp $
// mrp_case.aw - Juhtum/Projekt
/*

@classinfo syslog_type=ST_MRP_CASE relationmgr=yes

@tableinfo mrp_case index=oid master_table=objects master_index=oid

@groupinfo grp_case_details caption="Projekti kirjeldus"
@groupinfo grp_case_material caption="Kasutatav materjal"
@groupinfo grp_case_workflow caption="Ressursid ja töövoog"
@groupinfo grp_case_schedule caption="Kalender"
@groupinfo grp_case_comments caption="Juhtumi kommentaarid"


@default group=general
@default table=mrp_case
	@property starttime type=datetime_select
	@caption Alustamisaeg

	@property due_date type=date_select
	@caption Valmimistähtaeg

	@property project_priority type=textbox
	@caption Projekti prioriteet

	@property customer_priority type=textbox
	@caption Kliendi prioriteet

@default table=objects
@default field=meta
@default method=serialize
	@property number_of_jobs type=textbox store=no newonly=1 size=3
	@caption Tööde arv

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

	PRIMARY KEY  (`oid`),
	UNIQUE KEY `oid` (`oid`)
) TYPE=MyISAM;

*/

define ("MRP_JOB_STATUS_NEW", 1);
define ("MRP_JOB_STATUS_PLANNED", 2);
define ("MRP_JOB_STATUS_INPROGRESS", 3);
define ("MRP_JOB_STATUS_ABORTED", 4);
define ("MRP_JOB_STATUS_DONE", 5);

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
			case "workflow_toolbar":
				$this->create_workflow_toolbar ($arr);
				break;

			case "resource_tree":
				$this->create_resource_tree ($arr);
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
			### connect newly created obj. to workspace from which the req. was made
			$workspace = obj ($arr["request"]["mrp_workspace"]);
			$projects_folder = $workspace->prop ("projects_folder");
			$this_object->connect (array (
				"to" => $workspace,
				"reltype" => RELTYPE_MRP_OWNER,
			));
			$this_object->set_parent ($projects_folder);
			$this_object->save ();

			### create n new jobs. connect, name & ... them
			if ($arr["request"]["number_of_jobs"])
			{
				$jobs_folder = $workspace->prop ("jobs_folder");
				$job_number = $arr["request"]["number_of_jobs"];

				while ($job_number)
				{
					$job =& new object (array (
					   "name" => "Töö " . $job_number,
					   "parent" => $workspace->prop ("jobs_folder"),
					   "class_id" => CL_MRP_JOB,
					));
					$job->save ();
					$job->connect (array (
						"to" => $this_object,
						"reltype" => RELTYPE_MRP_PROJECT,
					));
					$job->set_prop ("exec_order", $job_number);
					$job->set_prop ("project", $this_object->id ());
					$job->save ();
					$this_object->connect (array (
						"to" => $job,
						"reltype" => RELTYPE_MRP_PROJECT_JOB,
					));
					$this_object->save ();
					$job_number--;
				}
			}
		}
		else
		{
			// $workspace =& $this->get_current_workspace ($arr);
		}

		### make changes to used resources
		if (is_string ($arr["request"]["mrp_resourcetree_data"]))
		{
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
			"caption" => "Töö jrk. nr.",
		));
		$table->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
		));
		$table->define_field(array(
			"name" => "length",
			"caption" => "Pikkus",
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
			$exec_order = $job->prop ("exec_order");
			$selected_resource = NULL;
			$status = $job->prop ("job_status");
			$disabled = false;
			$etag = '</span>';
			$stag = '<span>';

			switch ($status)
			{
				case MRP_JOB_STATUS_NEW:
					$stag = '<span style="color: green;">';
					$status = 'Uus';
					break;

				case MRP_JOB_STATUS_PLANNED:
					$stag = '<span style="color: yellow;">';
					$status = 'Planeeritud';
					break;

				case MRP_JOB_STATUS_ABORTED:
					$stag = '<span style="color: red;">';
					$status = 'Katkestatud';
					break;

				case MRP_JOB_STATUS_INPROGRESS:
					$stag = '<span style="color: blue;">';
					$status = 'Töös';
					$disabled = true;
					break;

				case MRP_JOB_STATUS_DONE:
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
					"size" => "10",
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
					"value" => $job->prop ("length") . " h",
					"disabled" => $disabled,
					)
				),
				"exec_order" => html::textbox(array(
					"name" => "mrp_workflow_job-" . $job_id . "-exec_order",
					"size" => "3",
					"value" => $exec_order,
					"disabled" => $disabled,
					)
				),
				"exec_order_sort" => $exec_order,
				"starttime" => $job->prop ("starttime"),
				"status" => $status,
				"job_id" => $job_id,
			));
		}

		$table->set_numeric_field ("exec_order_sort");
		$table->set_default_sortby ("exec_order_sort");
		$table->set_default_sorder ("asc");
	}

	function save_workflow_data ($arr)
	{
		$this_object = $arr["obj_inst"];
		$jobs = array ();
		$orders = array ();
		$order_changed = false;

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

					if ( ($status == MRP_JOB_STATUS_INPROGRESS) or ($status == MRP_JOB_STATUS_DONE) )
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

						case "exec_order":
							$job->set_prop ("exec_order", $value);
							break;

						case "length":
							$decimal = strstr ($value, ",") ? strstr ($value, ",") : strstr ($value, ".");
							settype ($decimal, "int");
							settype ($value, "int");
							$length = $value . "." . $decimal;
							settype ($length, "float");
							$job->set_prop ("length", ($length * 3600)); ///!!!teha puhvrite lisamine ja salv. scheduleri jaoks
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
								// !!!teha veateade?
							}
							break;
					}

					$job->save ();
				}
				else
				{
					// !!!teha veateade?
				}
			}
		}

		if ($order_changed)
		{
			$this->correct_job_order ($arr);
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
		$jobs = array ();
		$orders = array ();
		$states = array ();

		foreach ($connections as $connection)
		{
			$job = $connection->to();
			$jobs[] = $job;
			$orders[] = $job->prop ("exec_order");
			$states[] = $job->prop ("job_status");
		}

		asort ($orders, SORT_NUMERIC);
		$i = 0;

		foreach ($orders as $key => $order)
		{
			if ( ($states[$key] == MRP_JOB_STATUS_INPROGRESS) or ($states[$key] == MRP_JOB_STATUS_DONE) )
			{
				//!!! mida siin 6ieti teha?
			}

			$i++;
			$jobs[$key]->set_prop ("exec_order", $i);
			$jobs[$key]->save ();
		}
	}

/**
    @attrib name=add_job
	@param id required type=int
**/
	function add_job ($arr)
	{
		$this_object = obj ($arr["id"]);
		$workspace =& $this->get_current_workspace ($arr);

		if (!($jobs_folder = $workspace->prop ("jobs_folder")))
		{
			//!!! veateade teha?
		}

		$job =& new object (array (
		   "name" => "Uus töö",
		   "parent" => $jobs_folder,
		   "class_id" => CL_MRP_JOB,
		));
		$job->save ();
		$job->set_prop ("exec_order", 1000000);
		$job->connect (array (
			"to" => $this_object,
			"reltype" => RELTYPE_MRP_PROJECT,
		));
		$this_object->connect (array (
			"to" => $job,
			"reltype" => RELTYPE_MRP_PROJECT_JOB,
		));
		$this->correct_job_order ($arr);

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_case");
		return $return_url;
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
