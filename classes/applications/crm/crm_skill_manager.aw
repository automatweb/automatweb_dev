<?php
// crm_skill_manager.aw - Oskuste haldur

// Copied from metamgr.aw and modified.
/*

@classinfo syslog_type=ST_CRM_SKILL_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property default_lvls type=relpicker reltype=RELTYPE_LEVELS field=meta method=serialize
	@caption Default tasemed

@groupinfo skills caption="Oskused" submit=no
@default group=skills

	@property skills_tlb type=toolbar no_caption=1 store=no 

	@layout skills_layout type=hbox width=20%:80%

		@property skills_tree type=treeview store=no parent=skills_layout no_caption=1
		
		@property skills_tbl type=table store=no parent=skills_layout no_caption=1
		
	@property skill type=hidden store=no 

@reltype LEVELS value=1 clid=CL_META
@caption Tasemed

*/

class crm_skill_manager extends class_base
{
	function crm_skill_manager()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_skill_manager",
			"clid" => CL_CRM_SKILL_MANAGER
		));
	}

	function callback_pre_edit($arr)
	{
		$meta_tree = new object_tree(array(
			"parent" => $arr["obj_inst"]->id(),
			"class_id" => CL_CRM_SKILL,
			"lang_id" => array(),
		));
		$olist = $meta_tree->to_list();
		$rw_tree = array();
		for ($o = $olist->begin(); !$olist->end(); $o = $olist->next())
		{
			$rw_tree[$o->parent()][$o->id()] = (int)$o->ord();
		};

		foreach($rw_tree as $parent => $items)
		{
			asort($rw_tree[$parent]);
		};

		$this->rw_tree = $rw_tree;
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "skill":
				$prop["value"] = $arr["request"]["skill"];
				break;
		}

		return $retval;
	}

	function _get_skills_tlb($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_save_button();
		$t->add_delete_button();
	}

	function _get_skills_tree($arr)
	{	
		$tree = &$arr["prop"]["vcl_inst"];
		$obj = $arr["obj_inst"];
		$object_name = $obj->name();
		$tree->add_item(0, array(
			"name" => $object_name,
			"id" => $obj->id(),
			"url" => $this->mk_my_orb("change", array(
				"id" => $obj->id(),
				"group" => $arr["prop"]["group"],
			)),
		));
		
		foreach($this->rw_tree as $parent => $items)
		{
			foreach($items as $obj_id => $ord)
			{
				$o = new object($obj_id);
				$tree->add_item($o->parent(),array(
					"name" => $o->name(),
					"id" => $o->id(),
					"url" => aw_url_change_var(array("skill" => $o->id())),
				));
			};
		};

		$tree->set_selected_item($arr["request"]["skill"]);
	}

	
	function _get_skills_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"sortable" => 1,
			"align" => "right",
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"callback" => array(&$this, "callb_name"),
			"callb_pass_row" => true,
			"align" => "center",
		));
		if(!empty($arr["request"]["skill"]))
		{
			$t->define_field(array(
				"name" => "subheading",
				"caption" => t("Vahepealkiri (ei saa siduda isikuga)"),
				"align" => "center",
			));
		}
		else
		{
			$t->define_field(array(
				"name" => "lvl",
				"caption" => t("Saab m&auml;&auml;rata taset"),
				"align" => "center",
			));
			$t->define_field(array(
				"name" => "lvl_meta",
				"caption" => t("Tasemed"),
				"align" => "center",
			));
		}
		$t->define_field(array(
			"name" => "ord",
			"caption" => t("Jrk"),
			"sortable" => 1,
			"callback" => array(&$this, "callb_ord"),
			"callb_pass_row" => true,
			"align" => "center",
		));
		$t->define_chooser(array(
			"field" => "id",
			"name"  => "sel",
		));

		$this->mt_parent = false;

		if (!empty($arr["request"]["skill"]))
		{
			$root_obj = new object($arr["request"]["skill"]);
			$this->mt_parent = $root_obj->id();
		}
		else
		{
			$root_obj = $arr["obj_inst"];
		};

		$olist = new object_list(array(
			"parent" => $root_obj->id(),
			"class_id" => CL_CRM_SKILL,
			"lang_id" => array(),
			"sort_by" => "objects.jrk",
		));
		
		$options[0] = t("--vali--");
		foreach($arr["obj_inst"]->connections_from(array("reltype" => "RELTYPE_LEVELS")) as $conn)
		{
			$to = $conn->to();
			$options[$to->id()] = $to->name();
		}

		$new_data = array(
			"id" => "new",
			"is_new" => 1,
			"name" => "",
			"subheading" => html::checkbox(array(
				"name" => "submeta[new][subheading]",
				"value" => 1,
			)),
			"lvl" => html::checkbox(array(
				"name" => "submeta[new][lvl]",
				"value" => 1,
			)),
			"ord" => "",
			"lvl_meta" => html::select(array(
				"name" => "submeta[new][lvl_meta]",
				"options" => $options,
				"value" => $arr["obj_inst"]->prop("default_lvls"),
			)),
		);

		$t->define_data($new_data);

		foreach($olist->arr() as $o)
		{
			$id = $o->id();
			$var_name = $o->name();

			$trans = array(
				"is_new" => 0,
				"id" => $id,
				"name" => $var_name,
				"subheading" => html::checkbox(array(
					"name" => "submeta[".$id."][subheading]",
					"checked" => $o->prop("subheading"),
				)),
				"lvl" => html::checkbox(array(
					"name" => "submeta[" . $id . "][lvl]",
					"checked" => $o->prop("lvl"),
				)),
				"lvl_meta" => html::select(array(
					"name" => "submeta[" . $id . "][lvl_meta]",
					"options" => $options,
					"value" => $o->prop("lvl_meta"),
				)),
				"ord" => $o->ord(),
			);
			$t->define_data($trans);
		};

		// now add the textbox thingies to allow adding of new data

		$obj_name = $arr["obj_inst"]->name();
		$pathstr[] = html::href(array(
			"url" => aw_url_change_var(array(
				"skill" => "",
			)),
			"caption" => $obj_name,
		));

		// I need to calculate the path
		if ($arr["request"]["skill"])
		{
			$ox = new object($arr["request"]["skill"]);
			$stop = $arr["obj_inst"]->id();
			$path = $ox->path(array("to" => $stop));
			foreach($path as $po)
			{
				if ($po->id() != $stop)
				{
					$po_name = $po->name();
					$pathstr[] = html::href(array(
						"url" => aw_url_change_var(array(
							"skill" => $po->id(),
						)),
						"caption" => $po_name,
					));
				};
			};
		};
		$t->set_sortable(false);
	}

	function callb_name($arr)
	{
		return html::textbox(array(
			"name" => "submeta[" . $arr["id"] . "][name]",
			"size" => 40,
			"value" => $arr["name"],
		));
	}

	function callb_ord($arr)
	{
		return html::textbox(array(
			"name" => "submeta[" . $arr["id"] . "][ord]",
			"size" => 4,
			"value" => $arr["ord"],
		));
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "skills_tbl":
				$this->submit_meta($arr);
				break;
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function submit_meta($arr = array())
	{		
		$obj = $arr["obj_inst"];
		$new = $arr["request"]["submeta"]["new"];
		if ($new["name"])
		{
			// now I need to create a new object under this object
			$parent = $obj->id();
			if ($arr["request"]["skill"])
			{
				$parent = $arr["request"]["skill"];
			};
			$no = new object;
			$no->set_class_id(CL_CRM_SKILL);
			$no->set_status(STAT_ACTIVE);
			$no->set_parent($parent);
			$no->set_name($new["name"]);
			$no->set_prop("subheading", $new["subheading"]);
			$no->set_prop("lvl", $new["lvl"]);
			$no->set_prop("lvl_meta", $new["lvl_meta"]);
			$no->set_ord((int)$new["ord"]);
			$no->save();
		};	
		$submeta = $arr["request"]["submeta"];
		unset($submeta["new"]);
		if (is_array($submeta))
		{
			foreach($submeta as $skey => $sval)
			{
				$so = new object($skey);
				$so->set_name($sval["name"]);
				$so->set_prop("subheading", $sval["subheading"]);
				$so->set_prop("lvl", $sval["lvl"]);
				$so->set_prop("lvl_meta", $sval["lvl_meta"]);
				$so->set_ord($sval["ord"]);
				$so->save();
			};
		};
	}

	function callback_mod_retval($arr)
	{
		if ($arr["request"]["skill"])
		{
			$arr["args"]["skill"] = $arr["request"]["skill"];
		};
	}

	/**
		@attrib name=get_skills

		@param id required type=oid
			The oid of the skill_manager object.
	**/
	function get_skills($arr)
	{
		$o = obj($arr["id"]);

		
		$meta_tree = new object_tree(array(
			"parent" => $o->id(),
			"class_id" => CL_CRM_SKILL,
			"lang_id" => array(),
		));
		$olist = $meta_tree->to_list();
		$rw_tree = array();
		for ($o = $olist->begin(); !$olist->end(); $o = $olist->next())
		{
			$rw_tree[$o->parent()][$o->id()]["name"] = $o->name();
			$rw_tree[$o->parent()][$o->id()]["subheading"] = $o->prop("subheading");
			$rw_tree[$o->parent()][$o->id()]["lvl"] = $o->prop("lvl");
			$rw_tree[$o->parent()][$o->id()]["lvl_meta"] = $o->prop("lvl_meta");
		};

		foreach($rw_tree as $parent => $items)
		{
			asort($rw_tree[$parent]);
		};
		return $rw_tree;
	}
}

?>