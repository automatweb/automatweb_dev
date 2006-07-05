<?php

class project_req_impl extends class_base
{
	function project_req_impl($arr)
	{	
		$this->init();
	}

	function _get_req_tb($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$t->add_menu_button(array(
			"name" => "new",
			"img" => "new.gif",
			"tooltip" => t("Lisa")
		));

		$t->add_menu_item(array(
			"name" => "new_task",
			"parent" => "new",
			"link" => html::get_new_url(
				CL_TASK, 
				is_oid($arr["request"]["tf"]) ? $arr["request"]["tf"] : $arr["obj_inst"]->id(), 
				array(
					"return_url" => get_ru(),
					"alias_to_org" => $arr["obj_inst"]->prop("orderer"),
					"set_proj" => $arr["obj_inst"]->id()
				)
			),
			"text" => t("Toimetus"),
		));

		$t->add_menu_item(array(
			"name" => "new_call",
			"parent" => "new",
			"link" => html::get_new_url(
				CL_CRM_CALL, 
				is_oid($arr["request"]["tf"]) ? $arr["request"]["tf"] : $arr["obj_inst"]->id(), 
				array(
					"return_url" => get_ru(),
					"alias_to_org" => $arr["obj_inst"]->prop("orderer"),
					"set_proj" => $arr["obj_inst"]->id()
				)
			),
			"text" => t("K&otilde;ne"),
		));

		$t->add_menu_item(array(
			"name" => "new_call",
			"parent" => "new",
			"link" => html::get_new_url(
				CL_CRM_MEETING, 
				is_oid($arr["request"]["tf"]) ? $arr["request"]["tf"] : $arr["obj_inst"]->id(), 
				array(
					"return_url" => get_ru(),
					"alias_to_org" => $arr["obj_inst"]->prop("orderer"),
					"set_proj" => $arr["obj_inst"]->id()
				)
			),
			"text" => t("Kohtumine"),
		));

		$t->add_menu_item(array(
			"name" => "new_bug",
			"parent" => "new",
			"link" => html::get_new_url(
				CL_BUG, 
				is_oid($arr["request"]["tf"]) ? $arr["request"]["tf"] : $arr["obj_inst"]->id(), 
				array(
					"return_url" => get_ru(),
					"alias_to_org" => $arr["obj_inst"]->prop("orderer"),
					"set_proj" => $arr["obj_inst"]->id()
				)
			),
			"text" => t("Arendus&uuml;lesanne"),
		));

		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"action" => "del_goals",
			"tooltip" => t("Kustuta"),
		));
		
	}

	function _get_req_tree($arr)
	{
		$proc = $this->get_proc($arr["obj_inst"]);
		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "procurement_center",
			),
			"root_item" => obj($proc),
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU, CL_PROCUREMENT_REQUIREMENT),
				"parent" => $proc,
				"lang_id" => array(),
				"site_id" => array()
			)),
			"var" => "tf"
		));
	}

	function _init_req_tbl(&$t)
	{	
		$t->define_field(array(
			"name" => "icon",
			"width" => 1,
			"caption" => t(""),
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "parts",
			"caption" => t("Osalejad"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _get_req_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_req_tbl($t);

		if (!$arr["request"]["tf"])
		{	
			return;
		}
		$ol = new object_list(array(
			"parent" => $arr["request"]["tf"],
			"class_id" => array(CL_BUG,CL_TASK,CL_CRM_CALL,CL_CRM_MEETING)
		));
		classload("core/icons");
		$u = get_instance(CL_USER);
		foreach($ol->arr() as $o)
		{
			$o = $o->get_original();
			$p = $u->get_person_for_uid($o->createdby());
			$parts = array();
			switch($o->class_id())
			{
				case CL_TASK:
					$conns = $o->connections_to(array(
						'type' => array(10, 8),//CRM_PERSON.RELTYPE_PERSON_TASK==10
					));
					foreach($conns as $conn)
					{
						$parts[] = html::obj_change_url($conn->from());
					}
					break;

				case CL_CRM_CALL:
					$conns = $o->connections_to(array(
						'type' => "RELTYPE_PERSON_CALL",
						"from.class_id" => CL_CRM_PERSON
					));
					foreach($conns as $conn)
					{
						$parts[] = html::obj_change_url($conn->from());
					}
					break;

				case CL_CRM_MEETING:
					$conns = $o->connections_to(array(
						'type' => "RELTYPE_PERSON_MEETING",
						"from.class_id" => CL_CRM_PERSON
					));
					foreach($conns as $conn)
					{
						$parts[] = html::obj_change_url($conn->from());
					}
					break;

				case CL_BUG:
					$parts[] = html::obj_change_url($o->prop("who"));
					foreach(safe_array($o->prop("monitors")) as $mon)
					{
						$parts[] = html::obj_change_url($mon);
					}
					break;
			}
			$t->define_data(array(
				"icon" => icons::get_icon($o),
				"name" => html::obj_change_url($o),
				"createdby" => $p->name(),
				"created" => $o->created(),
				"parts" => join(", ", $parts),
				"oid" => $o->id()
			));
		}
	}

	function get_proc($o)
	{
		static $lut;
		if (!is_array($lut))
		{
			$lut = array();
		}
		if (!isset($lut[$o->id()]))
		{
			$ol = new object_list(array(
				"class_id" => CL_PROCUREMENT,
				"proj" => $o->id()
			));
			if ($ol->count())
			{
				$p = $ol->begin();
			}
			else
			{
				$p = obj();
			}
			$lut[$o->id()] = $p->id();
		}
		return $lut[$o->id()];
	}
}
?>