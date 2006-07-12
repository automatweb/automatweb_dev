<?php

class project_files_impl extends class_base
{
	function project_files_impl()
	{
		$this->init();
	}

	function _get_files_tb($arr)
	{
		$pt = $this->_get_files_pt($arr);

		$tb =& $arr["prop"]["vcl_inst"];

		$types = array(
			CL_MENU => t("Kataloog"),
			CL_FILE => t("Fail"),
			CL_CRM_MEMO => t("Memo"),
			CL_CRM_DOCUMENT => t("CRM Dokument"),
			CL_CRM_DEAL => t("Leping"),
			CL_CRM_OFFER => t("Pakkumine")
		);

		$tb->add_menu_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Uus"),
		));
		foreach($types as $type => $desc)
		{
			$tb->add_menu_item(array(
				"parent" => "new",
				"text" => $desc,
				"link" => html::get_new_url($type, $pt, array("return_url" => get_ru())),
			));
		}
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "del_goals",
			"tooltip" => t("Kustuta"),
		));
	}

	function _get_files_pt($arr)
	{
		if ($arr["request"]["tf"])
		{
			return $arr["request"]["tf"];
		}
		$ff = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_FILES_FLD");
		if (!$ff)
		{
			$ff = obj();
			$ff->set_class_id(CL_MENU);
			$ff->set_parent($arr["obj_inst"]->id());
			$ff->set_name(sprintf(t("%s failid"), $arr["obj_inst"]->name()));
			$ff->save();
			$arr["obj_inst"]->connect(array(
				"to" => $ff->id(),
				"type" => "RELTYPE_FILES_FLD"
			));
		}
		return $ff->id();
	}

	function _get_files_tree($arr)
	{
		unset($arr["request"]["tf"]);
		$pt = $this->_get_files_pt($arr);
		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "crm_proj_t",
			),
			"root_item" => obj($pt),
			"target_url" => aw_url_change_var("tf", null),
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU),
				"parent" => $pt,
				"sort_by" => "objects.jrk"
			)),
			"var" => "tf",
			"icon" => icons::get_icon_url(CL_MENU)
		));
	}

	function _init_files_tbl(&$t)
	{	
		$t->define_field(array(
			"caption" => t(""),
			"name" => "icon",
			"align" => "center",
			"sortable" => 0,
			"width" => 1
		));

		$t->define_field(array(
			"caption" => t("Nimi"),
			"name" => "name",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Looja"),
			"name" => "createdby",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"caption" => t("Loodud"),
			"name" => "created",
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i"
		));

		$t->define_field(array(
			"caption" => t("Muudetud"),
			"name" => "modified",
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i"
		));

		$t->define_field(array(
			"caption" => t(""),
			"name" => "pop",
			"align" => "center"
		));

		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _get_files_table($arr)
	{
		$pt = $this->_get_files_pt($arr);
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_files_tbl($t);

		$ol = new object_list(array(
			"parent" => $pt,
			"class_id" => array(CL_FILE,CL_CRM_DOCUMENT, CL_CRM_DEAL, CL_CRM_MEMO, CL_CRM_OFFER),
		));

		classload("core/icons");
		$clss = aw_ini_get("classes");
		get_instance(CL_FILE);
		foreach($ol->arr() as $o)
		{
			$pm = get_instance("vcl/popup_menu");
			$pm->begin_menu("sf".$o->id());
			
			if ($o->class_id() == CL_FILE)
			{
				$pm->add_item(array(
					"text" => $o->name(),
					"link" => file::get_url($o->id(), $o->name())
				));
			}
			else
			{
				foreach($o->connections_from(array("type" => "RELTYPE_FILE")) as $c)
				{
					$pm->add_item(array(
						"text" => $c->prop("to.name"),
						"link" => file::get_url($c->prop("to"), $c->prop("to.name"))
					));
				}
			}
			
			$t->define_data(array(
				"icon" => $pm->get_menu(array(
					"icon" => icons::get_icon_url($o)
				)),
				"name" => html::obj_change_url($o),
				"class_id" => $clss[$o->class_id()]["name"],
				"createdby" => $o->createdby(),
				"created" => $o->created(),
				"modifiedby" => $o->modifiedby(),
				"modified" => $o->modified(),
				"oid" => $o->id()
			));
		}

		$t->set_default_sortby("created");
		$t->set_default_sorder("desc");
	}
}

?>