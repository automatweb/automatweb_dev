<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_workspace.aw,v 1.1 2004/11/15 16:03:39 voldemar Exp $
// mrp_workspace.aw - Ressursihalduskeskkond
/*

@classinfo syslog_type=ST_MRP_WORKSPACE relationmgr=yes

@groupinfo grp_projects caption="Projektid"
@groupinfo grp_resources caption="Ressursid"
@groupinfo grp_schedule caption="Kalender"
@groupinfo grp_users caption="Kasutajad"
@groupinfo grp_settings caption="Seaded"

@default table=objects
@default field=meta
@default method=serialize



@default group=general
	@property configuration type=relpicker reltype=RELTYPE_MRP_CONFIGURATION
	@caption Ressursihalduse seaded

	@property configuration type=relpicker reltype=RELTYPE_MRP_WORKSPACE_CFGMGR
	@caption Keskkonna seaded


@default group=grp_projects
	@property project_list_toolbar type=toolbar store=no no_caption=1
	@property manager type=text no_caption=1 store=no wrapchildren=1 group=grp_projects,grp_resources
	@property project_list_tree type=text store=no no_caption=1 parent=manager
	@property project_list type=table store=no no_caption=1 parent=manager


@default group=grp_resources
	@property resource_list_toolbar type=toolbar store=no no_caption=1
	@property resource_tree type=text store=no no_caption=1 parent=manager
	@property resource_list type=table store=no no_caption=1 parent=manager


@default group=grp_schedule
	@property replan type=text
	@caption Planeeri


@default group=grp_users


@default group=grp_settings
	@property resources_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU
	@caption Ressursside kaust

	@property customers_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU
	@caption Klientide kaust

	@property projects_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU
	@caption Projektide kaust

	@property jobs_folder type=relpicker reltype=RELTYPE_MRP_FOLDER clid=CL_MENU
	@caption Tööde kaust

	@property workspace_configmanager type=relpicker reltype=RELTYPE_MRP_WORKSPACE_CFGMGR clid=CL_CFGMANAGER
	@caption Keskkonna seadetehaldur



// --------------- RELATION TYPES ---------------------

@reltype MRP_FOLDER value=1 clid=CL_MENU
@caption Kaust

@reltype MRP_CONFIGURATION clid=CL_MRP_CONFIGURATION value=3
@caption Ressursihalduse seaded

@reltype MRP_WORKSPACE_CFGMGR clid=CL_CFGMANAGER value=2
@caption Keskkonna seaded


*/

class mrp_workspace extends class_base
{
	function mrp_workspace()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_workspace",
			"clid" => CL_MRP_WORKSPACE
		));

		### READ CONFIGURATION
		### resource types
		define ("MRP_RESOURCE_PHYSICAL", 1);
		define ("MRP_RESOURCE_OUTSOURCE", 2);
		define ("MRP_RESOURCE_GLOBAL_BUFFER", 3);

		### folders
		define ("MRP_FOLDER_RESOURCES", 1);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "project_list_toolbar":
				$this->create_project_list_toolbar ($arr);
				break;

			case "project_list_tree":
				$this->create_project_list_tree ($arr);
				break;

			case "project_list":
				$this->create_project_list ($arr);
				break;

			case "resource_list_toolbar":
				$this->create_resource_list_toolbar ($arr);
				break;

			case "resource_tree":
				$this->create_resource_tree ($arr);
				break;

			case "resource_list":
				$this->create_resource_list ($arr);
				break;

			case "replan":
				$plan_url = $this->mk_my_orb("create", array(
					"return_url" => urlencode(aw_global_get('REQUEST_URI')),
					"mrp_workspace" => $this_object->id (),
				), "mrp_schedule");
				$plan_href = html::href(array(
					"caption" => "[Planeeri]",
					"url" => $plan_url,
					)
				);
				$prop["value"] = $plan_href;
				break;
		}
		return $retval;
	}

	function create_resource_tree ($arr = array())
	{
		$this_object =& $arr["obj_inst"];

		// ### "command" tree
		// $url_resources_show_all = $this->mk_my_orb("change", array(
			// "id" => $this_object->id (),
			// "group" => "grp_projects",
			// "mrp_resources_show" => "all",
		// ), "mrp_workspace");

		// $tree = get_instance("vcl/treeview");
		// $tree->start_tree(array(
			// "type" => TREE_DHTML,
			// "tree_id" => "commandtree",
			// "persist_state" => 1,
		// ));
		// $tree->add_item (0, array(
			// "name" => "Kõik ressursid",
			// "id" => 1,
			// "url" => $url_resources_show_all,
		// ));
		// $arr["prop"]["value"] = $tree->finalize_tree ();

		### resource tree
		$resources_folder = $this_object->prop ("resources_folder");
		$resource_tree = new object_tree(array(
			"parent" => $resources_folder,
			"class_id" => array(CL_MENU, CL_MRP_RESOURCE),
		));

		classload("vcl/treeview");
		$tree = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "resourcetree",
				"persist_state" => true,
			),
			"root_item" => obj ($resources_folder),
			"ot" => $resource_tree,
			"var" => "mrp_resource_tree_active_item",
		));
		//!!! teha nii et ressursid oleks teistsuguste ikoonidega kui kaustad

		$arr["prop"]["value"] .= $tree->finalize_tree ();
	}

	function create_resource_list ($arr = array())
	{
		$table =& $arr["prop"]["vcl_inst"];
		$this_object =& $arr["obj_inst"];

		if (is_oid ($arr["request"]["mrp_resource_tree_active_item"]))
		{
			$parent = obj ($arr["request"]["mrp_resource_tree_active_item"]);

			if ($parent->class_id () != CL_MENU)
			{
				$parent = $parent->parent ();
			}
			else
			{
				$parent = $parent->id ();
			}
		}
		else
		{
			$parent = $this_object->prop ("resources_folder");
		}

		$table->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "operator",
			"caption" => "Operaator",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "status",
			"caption" => "Staatus",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "modify",
			"caption" => "Ava",
		));

		$table->define_chooser(array(
			"name" => "selection",
			"field" => "resource_id",
		));

		$table->set_default_sortby("name");
		$table->set_default_sorder("desc");

		$object_list = new object_list(array(
			"class_id" => CL_MRP_RESOURCE,
			"parent" => $parent,
		));

		$resources = $object_list->arr();

		foreach ($resources as $resource)
		{
			$change_url = $this->mk_my_orb("change", array(
				"id" => $resource->id(),
				"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			), "mrp_resource");

			$table->define_data(array(
				"modify" => html::href(array(
					"caption" => "Ava",
					"url" => $change_url,
					)
				),
				"name" => $resource->name(),
				"operator" => $resource->prop("operator"),
				"status" => $resource->prop("status"),
				"resource_id" => $resource->id(),
			));
		}
	}

	function create_resource_list_toolbar ($arr = array())
	{
		$toolbar =& $arr["prop"]["toolbar"];
		$this_object =& $arr["obj_inst"];

		if (is_oid ($arr["request"]["mrp_resource_tree_active_item"]))
		{
			$parent = obj ($arr["request"]["mrp_resource_tree_active_item"]);

			if ($parent->class_id () != CL_MENU)
			{
				$parent = $parent->parent ();
			}
			else
			{
				$parent = $parent->id ();
			}
		}
		else
		{
			$parent = $this_object->prop ("resources_folder");
		}

		$add_resource_url = $this->mk_my_orb("new", array(
			"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			"mrp_workspace" => $this_object->id (),
			"parent" => $parent,
		), "mrp_resource");
		$add_category_url = $this->mk_my_orb("new", array(
			"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			"parent" => $parent,
		), "menu");

		$toolbar->add_menu_button(array(
			"name" => "add",
			"img" => "new.gif",
			"tooltip" => "Lisa uus",
		));
		$toolbar->add_menu_item(array(
			"parent" => "add",
			"text" => "Ressurss",
			"link" => $add_resource_url,
		));
		$toolbar->add_menu_item(array(
			"parent" => "add",
			"text" => "Ressurssikategooria",
			"link" => $add_category_url,
		));

		// $toolbar->add_separator();

		// $toolbar->add_button(array(
			// "name" => "cut",
			// "tooltip" => "L&otilde;ika",
			// "action" => "cut_resources",
			// "img" => "cut.gif",
		// ));

		// $toolbar->add_button(array(
			// "name" => "copy",
			// "tooltip" => "Kopeeri",
			// "action" => "copy_resources",
			// "img" => "copy.gif",
		// ));

		// if ($sel_count > 0)
		// {
			// $toolbar->add_button(array(
				// "name" => "paste",
				// "tooltip" => "Kleebi",
				// "action" => "paste_resources",
				// "img" => "paste.gif",
			// ));
		// };

		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta",
			"action" => "delete",
			"confirm" => "Kustutada kõik valitud ressursid?",
		));
	}

	function create_project_list_toolbar ($arr = array())
	{
		$toolbar =& $arr["prop"]["toolbar"];
		$workspace = $arr["obj_inst"];
		$add_project_url = $this->mk_my_orb("new", array(
			"return_url" => urlencode(aw_global_get('REQUEST_URI')),
			"mrp_workspace" => $workspace->id (),
		), "mrp_case");
		$toolbar->add_button(array(
			"name" => "add",
			"img" => "new.gif",
			"tooltip" => "Lisa uus projekt",
			"url" => $add_project_url,
		));
		$toolbar->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => "Kustuta valitud projekt(id)",
			"confirm" => "Kustutada kõik valitud projektid?",
			"action" => "delete",
		));
	}

	function create_project_list_tree ($arr = array())
	{
		$this_object =& $arr["obj_inst"];
		$projects_folder = $this_object->prop ("projects_folder");

		$url_projects_all = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "all",
		), "mrp_workspace");
		$url_projects_in_work = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "inwork",
		), "mrp_workspace");
		$url_projects_overdue = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "overdue",
		), "mrp_workspace");
		$url_projects_new = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "new",
		), "mrp_workspace");
		$url_projects_done = $this->mk_my_orb("change", array(
			"id" => $this_object->id (),
			"group" => "grp_projects",
			"mrp_projects_show" => "done",
		), "mrp_workspace");

		$list = new object_list (array ( //!!! to finish
			"class_id" => CL_MRP_CASE,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
			//"" => "",
		));
		$count_projects_in_work = $list->count ();

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"due_date" => new obj_predicate_compare (OBJ_COMP_LESS, time()),
			"finished_date" => 0,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_overdue = $list->count ();

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"starttime" => new obj_predicate_compare (OBJ_COMP_GREATER, time()),
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_new = $list->count ();

		$list = new object_list (array (
			"class_id" => CL_MRP_CASE,
			"due_date" => new obj_predicate_compare (OBJ_COMP_LESS, time()),
			"finished_date" => new obj_predicate_not ("0"),
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$count_projects_done = $list->count ();

		$tree = get_instance("vcl/treeview");
		$tree->start_tree(array(
				"type" => TREE_DHTML,
				"tree_id" => "resourcetree",
				"persist_state" => 1,
		));

		$tree->add_item(0, array(
			"name" => "Kõik projektid",
			"id" => 1,
			"url" => $url_projects_all,
		));

		$tree->add_item(1, array(
			"name" => "Töösolevad" . "(" . $count_projects_in_work . ")",
			"id" => 2,
			"url" => $url_projects_in_work,
		));

		$tree->add_item(1, array(
			"name" => "Üle tähtaja" . "(" . $count_projects_overdue . ")",
			"id" => 3,
			"url" => $url_projects_overdue,
		));

		$tree->add_item(1, array(
			"name" => "Uued" . "(" . $count_projects_new . ")",
			"id" => 4,
			"url" => $url_projects_new,
		));

		$tree->add_item(1, array(
			"name" => "Valmis" . "(" . $count_projects_done . ")",
			"id" => 5,
			"url" => $url_projects_done,
		));

		// $tree->set_selected_item($arr["request"]["meta"]);
		$arr["prop"]["value"] = $tree->finalize_tree();
	}

	function create_project_list ($arr = array())
	{
		$table =& $arr["prop"]["vcl_inst"];
		$this_object =& $arr["obj_inst"];

		$table->define_field(array(
			"name" => "name",
			"caption" => "Projekt",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "added",
			"caption" => "Sisestatud",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "modified",
			"caption" => "Muudetud",
			"sortable" => 1
		));
		$table->define_field(array(
			"name" => "modify",
			"caption" => "Ava",
		));

		$table->define_chooser(array(
			"name" => "selection",
			"field" => "project_id",
		));

		$table->set_default_sortby("modified");
		$table->set_default_sorder("desc");
		$table->draw_text_pageselector(array(
			"records_per_page" => 50,
		));

		$list = new object_list(array(
			"class_id" => CL_MRP_CASE,
			"parent" => $this_object->prop ("projects_folder"),
			// "createdby" => aw_global_get('uid'),
		));
		$projects = $list->arr ();

		foreach ($projects as $project_id => $project)
		{
			$change_url = $this->mk_my_orb("change", array(
				"id" => $project_id,
				"return_url" => urlencode(aw_global_get('REQUEST_URI')),
				"group" => "grp_case_schedule",
			), "mrp_case");

			$table->define_data(array(
				"modify" => html::href(array(
					"caption" => "Ava",
					"url" => $change_url,
					)
				),
				"name" => $project->name (),
				"added" => get_lc_date ($project->created(), LC_DATE_FORMAT_SHORT_FULLYEAR ),
				"modified" => get_lc_date ($project->modified(), LC_DATE_FORMAT_SHORT_FULLYEAR ),
				"project_id" => $project_id,
			));
		}
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
			for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				if ($this->can("delete", $o->id()))
				{
					$o->delete();
				}
			}
		}

		$return_url = $this->mk_my_orb("change", array(
			"id" => $arr["id"],
			"return_url" => urlencode ($arr["return_url"]),
			"group" => $arr["group"],
			"subgroup" => $arr["subgroup"],
		), "mrp_workspace");
		return $return_url;
	}
}

?>
