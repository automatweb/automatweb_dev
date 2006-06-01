<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/procurement_center.aw,v 1.3 2006/06/01 15:10:01 kristo Exp $
// procurement_center.aw - Hankekeskkond 
/*

@classinfo syslog_type=ST_PROCUREMENT_CENTER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property owner type=text store=no 
	@caption Omanik

@default group=p

	@property p_tb type=toolbar no_caption=1 store=no
	
	@layout p_l type=hbox width=30%:70%
		
		@property p_tr type=treeview no_caption=1 store=no parent=p_l

		@property p_tbl type=table no_caption=1 store=no parent=p_l

@default group=team

	@property team_tb type=toolbar store=no no_caption=1

	@property team_table type=table store=no no_caption=1

@groupinfo p caption="Hanked" submit=no
@groupinfo team caption="Meeskond" submit=no

@reltype MANAGER_CO value=1 clid=CL_CRM_COMPANY
@caption Haldaja firma

@reltype TEAM_MEMBER value=2 clid=CL_CRM_PERSON
@caption Meeskonna liige
*/

class procurement_center extends class_base
{
	function procurement_center()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/procurement_center",
			"clid" => CL_PROCUREMENT_CENTER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "owner":
				$o = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MANAGER_CO");
				if (!$o)
				{
					return PROP_IGNORE;
				}
				$prop["value"] = html::obj_change_url($o);
				break;

			case "p_tb":
				$this->_p_tb($arr);
				break;

			case "p_tr":
				$this->_p_tr($arr);
				break;

			case "p_tbl":
				$this->_p_tbl($arr);
				break;

			case "team_tb":
				$i = get_instance(CL_PROCUREMENT_IMPLEMENTOR_CENTER);
				$i->_team_tb($arr);
				break;

			case "team_table":
				$i = get_instance(CL_PROCUREMENT_IMPLEMENTOR_CENTER);
				$i->_team_table($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "team_tb":
				if ($this->can("view", $arr["request"]["add_member"]))
				{
					$arr["obj_inst"]->connect(array("to" => $arr["request"]["add_member"], "type" => "RELTYPE_TEAM_MEMBER"));
				}
				$arr["obj_inst"]->set_meta("team_prices", $arr["request"]["prices"]);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["add_member"] = "0";
	}

	function _p_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$tb->add_menu_button(array(
			'name'=>'add_item',
			'tooltip'=> t('Uus')
		));

		$parent = $arr["request"]["p_id"] ? $arr["request"]["p_id"] : $arr["obj_inst"]->id();

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Kataloog'),
			'link'=> html::get_new_url(CL_MENU, $parent, array("return_url" => get_ru()))
		));

		$tb->add_menu_item(array(
			'parent'=>'add_item',
			'text'=> t('Hange'),
			'link'=> html::get_new_url(CL_PROCUREMENT, $parent, array("return_url" => get_ru()))
		));

		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud hanked'),
			'action' => 'delete_procurements',
			'confirm' => t("Kas oled kindel et soovid valitud hanked kustudada?")
		));
	}

	/**
		@attrib name=delete_procurements
	**/
	function delete_procurements($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	function _p_tr($arr)
	{
		classload("core/icons");
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML, 
				"persist_state" => true,
				"tree_id" => "procurement_center",
			),
			"root_item" => $arr["obj_inst"],
			"ot" => new object_tree(array(
				"class_id" => array(CL_MENU),
				"parent" => $arr["obj_inst"]->id(),
				"lang_id" => array(),
				"site_id" => array()
			)),
			"var" => "p_id",
			"icon" => icons::get_icon_url(CL_MENU)
		));
	}

	function _init_p_tbl(&$t)
	{
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
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"align" => "center",
			"sortable" => 1,
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));
		$t->define_chooser(array(
			"field" => "oid",
			"name" => "sel"
		));
	}

	function _p_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_p_tbl($t);

		$parent = $arr["request"]["p_id"] ? $arr["request"]["p_id"] : $arr["obj_inst"]->id();

		$ol = new object_list(array(
			"class_id" => CL_PROCUREMENT,
			"parent" => $parent,
			"lang_id" => array(),
			"site_id" => array()
		));
		$t->data_from_ol($ol, array("change_col" => "name"));
	}
}
?>
