<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug_tracker.aw,v 1.3 2005/03/22 15:32:37 kristo Exp $
// bug_tracker.aw - BugTrack 
/*

@classinfo syslog_type=ST_BUG_TRACKER relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

@layout bugl type=vbox 
@layout bugl_tb type=hbox parent=bugl
@layout bugl_lower type=hbox parent=bugl

@property bug_tb type=toolbar parent=bugl no_caption=1
@property bug_tree type=treeview parent=bugl_lower no_caption=1
@property bug_list type=table parent=bugl_lower no_caption=1

*/

class bug_tracker extends class_base
{
	function bug_tracker()
	{
		$this->init(array(
			"tpldir" => "applications/bug_o_matic_3000/bug_tracker",
			"clid" => CL_BUG_TRACKER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "bug_tb":
				$this->_bug_toolbar($arr);
				break;

			case "bug_tree":
				$this->_bug_tree($arr);
				break;

			case "bug_list":
				$this->_bug_list($arr);
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

		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
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

	////////////////// property handlers
	
	function _bug_toolbar($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];

		$pt = !empty($arr["request"]["cat"]) ? $arr["request"]["cat"] : $arr["obj_inst"]->id();

		$tb->add_menu_button(array(
			"name" => "add_bug",
			"tooltip" => t("Uus"),
		));

		$tb->add_menu_item(array(
			"parent" => "add_bug",
			"text" => t("Lisa bugi"),
			"link" => html::get_new_url(CL_BUG, $pt, array(
				"return_url" => aw_global_get("REQUEST_URI")
			))
		));

		$tb->add_menu_item(array(
			"parent" => "add_bug",
			"text" => t("Lisa kategooria"),
			"link" => html::get_new_url(CL_MENU, $pt, array(
				"return_url" => aw_global_get("REQUEST_URI")
			))
		));
	}

	function _bug_tree($arr)
	{
		$ot = new object_tree(array(
			"parent" => $arr["obj_inst"]->id(),
			"class_id" => array(CL_MENU,CL_BUG)
		));

		$arr["prop"]["vcl_inst"]->tree_from_objects(array(
			"root_item" => $arr["obj_inst"],
			"ot" => $ot,
			"var" => "cat",
			"tree_opts" => array(
				PERSIST_STATE
			)
		));
	}

	function _init_bug_list_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "createdby",
			"caption" => t("Looja"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"sortable" => 1,
			"type" => "time",
			"numberic" => 1,
			"format" => "d.m.Y / H:i"
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"sortable" => 1,
			"type" => "time",
			"numberic" => 1,
			"format" => "d.m.Y / H:i"
		));
	}

	function _bug_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bug_list_tbl($t);

		$pt = !empty($arr["request"]["cat"]) ? $arr["request"]["cat"] : $arr["obj_inst"]->id();

		$ol = new object_list(array(
			"parent" => $pt,
			"class_id" => array(CL_BUG,CL_MENU)
		));
		$t->data_from_ol($ol, array(
			"change_col" => "name"
		));
	}
}
?>
