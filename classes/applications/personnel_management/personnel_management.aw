<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management.aw,v 1.13 2006/03/28 11:52:05 ahti Exp $
// personnel_management.aw - Personalikeskkond 
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT relationmgr=yes r2=yes no_status=1 no_comment=1

@default group=general
@default table=objects
@default field=meta
@default method=serialize

@property persons_fld type=relpicker reltype=RELTYPE_MENU
@caption Isikute kaust

@property offers_fld type=relpicker reltype=RELTYPE_MENU
@caption Tööpakkumiste kaust

@property fields type=relpicker reltype=RELTYPE_SECTORS
@caption Tegevusvaldkonnad

@property person_ot type=relpicker reltype=RELTYPE_PERSON_OT
@caption Isikute objektitüüp

@property crmdb type=relpicker reltype=RELTYPE_CRM_DB
@caption Kliendibaas

@property owner_org type=relpicker reltype=RELTYPE_OWNER_ORG
@caption Omanikorganisatsioon

-------------------TÖÖOTSIJAD-----------------------
@groupinfo employee caption="Tööotsijad" submit=no

@groupinfo employee_search caption="Otsing" parent=employee
@default group=employee_search

@property search_save type=relpicker reltype=RELTYPE_SEARCH_SAVE
@caption Varasem otsing

@property search_cv type=form sclass=applications/personnel_management/personnel_management_cv_search sform=cv_search store=no
@caption Otsi CV-sid

----------------------------------------

@groupinfo employee_list caption="Nimekiri" parent=employee submit=no
@default group=employee_list

@property employee_list_toolbar type=toolbar no_caption=1

@layout employee_list type=hbox width=15%:85%

@property employee_list_tree type=treeview no_caption=1 parent=employee_list

@property employee_list_table type=table no_caption=1 parent=employee_list

----------------------------------------

@groupinfo candidate caption="Kandideerijad" submit=no
@default group=candidate


@property candidate_toolbar type=toolbar no_caption=1

@layout candidate type=hbox width=15%:85%

@property candidate_tree type=treeview no_caption=1 parent=candidate

@property candidate_table type=table no_caption=1 parent=candidate

----------------------------------------
@groupinfo offers caption="Tööpakkumised" submit=no
@default group=offers

@property offers_toolbar type=toolbar no_caption=1

@layout offers type=hbox width=15%:85%

@property offers_tree type=treeview no_caption=1 parent=offers

@property offers_table type=table no_caption=1 parent=offers

----------------------------------------

@groupinfo actions caption="Tegevused" submit=no
@default group=actions

@property treeview3 type=text no_caption=1 default=asd

----------------------------------------

@groupinfo clients caption="Kliendid" submit=no
@default group=clients

@property treeview4 type=text no_caption=1 default=asd

---------------RELATION DEFINTIONS-----------------

@reltype MENU value=1 clid=CL_MENU
@caption Kaust

@reltype CRM_DB value=2 clid=CL_CRM_DB
@caption Kliendibaas

@reltype SECTORS value=3 clid=CL_METAMGR
@caption Tegevusvaldkonnad

@reltype PERSON_OT value=4 clid=CL_OBJECT_TYPE
@caption Objektitüüp

@reltype OWNER_ORG value=5 clid=CL_CRM_COMPANY
@caption Omanikorganisatsioon

@reltype SEARCH_SAVE value=6 clid=CL_BLAH
@caption Otsingu salvestus

*/

class personnel_management extends class_base
{
	function personnel_management()
	{
		$this->init(array(
			"clid" => CL_PERSONNEL_MANAGEMENT,
			"tpldir" => "applications/personnel_management/personnel_management",
		));
	}

	function callback_on_load($arr)
	{
		if(!$arr["new"])
		{
			if($this->can("view", $arr["request"]["id"]))
			{
				$obj = obj($arr["request"]["id"]);
				if($this->can("view", $obj->prop("owner_org")))
				{
					$this->owner_org = $obj->prop("owner_org");
				}
				if($this->can("view", $obj->prop("persons_fld")))
				{
					$this->persons_fld = $obj->prop("persons_fld");
				}
				if($this->can("view", $obj->prop("offers_fld")))
				{
					$this->offers_fld = $obj->prop("offers_fld");
				}
			}
		}
	}

	function callback_mod_tab($arr)
	{
		if(!$arr["new"] && $this->owner_org)
		{
			if($arr["id"] == "actions")
			{
				$arr["link"] = $this->mk_my_orb("change", array("id" => $this->owner_org, "group" => "overview"), CL_CRM_COMPANY);
			}
			elseif($arr["id"] == "clients")
			{
				$arr["link"] = $this->mk_my_orb("change", array("id" => $this->owner_org, "group" => "relorg"), CL_CRM_COMPANY);
			}
		}
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "employee_list_toolbar":
				$this->employee_list_toolbar($arr);
				break;

			case "candidate_toolbar":
				$this->candidate_toolbar($arr);
				break;

			case "offers_toolbar":
				$this->offers_toolbar($arr);
				break;

			case "employee_list_table":
				$this->employee_list_table($arr);
				break;

			case "candidate_table":
				$this->candidate_table($arr);
				break;

			case "offers_table":
				$this->offers_table($arr);
				break;

			case "employee_list_tree":
				$this->employee_list_tree($arr);
				break;

			case "candidate_tree":
				$this->candidate_tree($arr);
				break;

			case "offers_tree":
				$this->offers_tree($arr);
				break;
		}
		return $retval;
	}

	function employee_list_tree($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_item(0, array(
			"id" => 3,
			"name" => t("Element"),
		));
	}

	function candidate_tree($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_item(0, array(
			"id" => 3,
			"name" => t("Element"),
		));
	}

	function offers_tree($arr)
	{
		$fld = $this->offers_fld;
		if($this->can("view", $arr["request"]["offer_fld"]))
		{
			$fld = $arr["request"]["offer_fld"];
		}
		classload("core/icons");
		$this->tree = get_instance("vcl/treeview");
		$this->active_group = $arr["request"]["group"];
		$this->tree_root_name = "Bug-Tracker";

		$this->tree->start_tree(array(
			"type" => TREE_DHTML,
			//"has_root" => 1,
			"tree_id" => "offers_tree",
			"persist_state" => 1,
			//"root_name" => t("Tööpakkumised"),
			"root_url" => aw_url_change_var("b_id", null),
			"get_branch_func" => $this->mk_my_orb("get_offers_node", array(
				"inst_id" => $this->self_id,
				"active_group" => $this->active_group,
				"b_id" => $arr["request"]["b_id"],
				"p_fld_id" => $arr["request"]["p_fld_id"],
				"p_cls_id" => $arr["request"]["p_cls_id"],
				"p_cust_id" => $arr["request"]["p_cust_id"],
				"parent" => " ",
			)),
		));
		$this->generate_offers_tree(array(
			"parent" => $fld,
		));
		$arr["prop"]["value"] = $this->tree->finalize_tree();
		$arr["prop"]["type"] = "text";
	}

	function generate_offers_tree($arr)
	{
		$ol = new object_list(array(
			"parent" => $arr["parent"],
			"class_id" => CL_MENU,
		));
		$objects = $ol->arr();

		$nm = t("Tööpakkumised")."(".$ol->count().")";
		if (!$_GET["b_id"])
		{
			$nm = "<b>".$nm."</b>";
		}
		$this->tree->add_item(0,array(
				"id" => $this->offers_fld,
				"name" => $nm,
				"url" => aw_url_change_var("b_id", null)
		));
		
		foreach($objects as $obj_id => $object)
		{
			$nm = $object->name();
			if ($_GET["b_id"] == $obj_id)
			{
				$nm = "<b>".$nm."</b>";
			}
			$this->tree->add_item($arr["parent"] , array(
				"id" => $obj_id,
				"name" => $nm,
			));
		}
	}

	/**
		@attrib name=get_offers_node all_args=1
	**/
	function get_offers_node($arr)
	{
		classload("core/icons");
		$node_tree = get_instance("vcl/treeview");
		$node_tree->start_tree (array (
			"type" => TREE_DHTML,
			"tree_id" => "offers_tree",
			"branch" => 1,
		));

		$ol = new object_list(array(
			"parent" => $arr["parent"],
			"class_id" => CL_MENU,
		));

		$objects = $ol->arr();
		foreach($objects as $obj_id => $object)
		{
			$ol = new object_list(array(
				"parent" => $obj_id,
				"class_id" => CL_MENU,
			));
			$ol_list = $ol->arr();
			$subtree_count = (count($ol_list) > 0)?" (".count($ol_list).")":"";

			$nm = $object->name();
			if (false && $_GET["b_id"] == $obj_id)
			{
				$nm = "<b>".$nm."</b>";
			}

			$node_tree->add_item(0 ,array(
				"id" => $obj_id,
				"name" => $nm,
				"iconurl" => icons::get_icon_url($object->class_id()),
				"url" => html::get_change_url($arr["inst_id"], array(
					"group" => $arr["active_group"],
					"b_id" => $obj_id,
				)),
			));

			foreach($ol_list as $sub_id => $sub_obj)
			{
				$node_tree->add_item( $obj_id, array(
					"id" => $sub_id,
					"name" => $sub_obj->name()." (".html::get_change_url($sub_id, array("return_url" => get_ru()), t("<span style='font-size: 8px;'>Muuda</span>")).")",
				));
			}
		}

		die($node_tree->finalize_tree());
	}

	function employee_list_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "add",
			"caption" => t("Lisa"),
			"img" => "new.gif",
		));
	}

	function candidate_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "add",
			"caption" => t("Lisa"),
			"img" => "new.gif",
		));
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta kandidaadid"),
			"action" => "delete_rels",
		));
	}
	
	function offers_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_menu_button(array(
			"name" => "add",
			"tooltip" => t("Uus"),
		));

		$tb->add_menu_item(array(
			"parent" => "add",
			"text" => t("Tööpakkumine"),
			"link" => html::get_new_url(CL_PERSONNEL_MANAGEMENT_JOB_OFFER, $this->offers_fld, array("return_url" => get_ru())),
			"href_id" => "add_bug_href"
		));
		$tb->add_menu_item(array(
			"parent" => "add",
			"text" => t("Kategooria"),
			"link" => html::get_new_url(CL_MENU, $this->offers_fld, array("return_url" => get_ru())),
		));
		$tb->add_button(array(
			"name" => "copy",
			"caption" => t("Alusta olemasoleva põhjal"),
			"img" => "copy.gif",
			"action" => "",
		));
		$tb->add_button(array(
			"name" => "delete",
			"caption" => t("Kustuta tööpakkumised"),
			"img" => "delete.gif",
			"action" => "",
		));

	}

	function employee_list_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_data(array(
			"name" => "test",
		));
	}

	function candidate_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
		));
		$t->define_data(array(
			"name" => "test",
		));
	}

	function _init_offers_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "profession",
			"caption" => t("Ametikoht"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "org",
			"caption" => t("Organisatsioon"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "location",
			"caption" => t("Asukoht"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "end",
			"caption" => t("Tähtaeg"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "status",
			"caption" => t("Staatus"),
			"sortable" => 1,
		));
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
	}

	function offers_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_offers_table(&$t);

		$toopakkujad_ids = array();
		$toopakkujad = array();

		$fld = $this->offers_fld;
		if($this->can("view", $arr["request"]["offer_fld"]))
		{
			$fld = $arr["request"]["offer_fld"];
		}

		$objs = new object_list(array(
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			"parent" => $fld,
		));
        foreach ($objs->arr() as $obj)
		{
			if($obj->prop("end"))
			{
        		$end = get_lc_date($obj->prop("end"));
        	}
        	else
        	{
        		$end = t("Määramata");
        	}
			$t->define_data(array(
				"id" => $obj->id(),
				"name" => html::get_change_url($obj->id(), array("return_url" => get_ru()), $obj->name()),
				"profession" => $obj->prop("profession.name"),
				"org" => html::get_change_url($obj->prop("company"), array("return_url" => get_ru()), $obj->prop("company.name")),
				"location" => $obj->prop("location.name"),
				"end" => $end,
				"created" => $obj->created(),
				"status" => $obj->prop_str("status") ? t("aktiivne") : t("mitteaktiivne"),
			));
		}
		$t->set_default_sortby("created");
		$t->set_default_sorder("desc");
		$t->sort_by();
	}
	
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{			
		}
		
		return $retval;
	}	

	function parse_alias($arr)
	{
		$obj = obj($arr["id"]);
		$this->read_template("show.tpl");
		$objs = new object_list(array(
			"parent" => $obj->prop("offers_fld"),
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
		));
		$offers = "";
		foreach($objs->arr() as $ob)
		{
			$this->vars(array(
				"profession" => html::href(array(
					"url" => obj_link($ob->prop("profession")),
					"caption" => $ob->prop("profession.name"),
				)),
				"company" => $ob->prop("company.name"),
				"location" => $ob->prop("location.name"),
				"field" => $ob->prop("field.name"),
				"end" => get_lc_date($ob->prop("end")),
			));
			$offers .= $this->parse("OFFER");
		}
		$this->vars(array(
			"count" => $objs->count(),
			"OFFER" => $offers,
		));
		return $this->parse();
	}
}
?>
