<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug_tracker.aw,v 1.7 2006/01/26 15:25:24 ahti Exp $
// bug_tracker.aw - BugTrack 
/*

@classinfo syslog_type=ST_BUG_TRACKER relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

@groupinfo bugs caption="Bugid" submit=no
@default group=bugs

@property bug_tb type=toolbar no_caption=1

@layout bug type=hbox width=15%:85%
	@property bug_tree type=treeview parent=bug no_caption=1
	@property bug_list type=table parent=bug no_caption=1

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

	/*

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
	*/
	
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
				"return_url" => get_ru(),
			))
		));

		$tb->add_menu_item(array(
			"parent" => "add_bug",
			"text" => t("Lisa kategooria"),
			"link" => html::get_new_url(CL_MENU, $pt, array(
				"return_url" => get_ru(),
			))
		));
		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta"),
			"img" => "delete.gif",
			"action" => "delete",
			"confirm" => t("Oled kindel, et soovid bugi kustutada?"),
		));
	}

	function _bug_tree($arr)
	{
		get_instance(CL_TREEVIEW);
		if($this->can("view", $arr["obj_inst"]->id()))
		{
			$ot = new object_tree(array(
				"parent" => $arr["obj_inst"]->id(),
				//"class_id" => CL_MENU,
				"class_id" => array(CL_MENU,CL_BUG)
			));
		}
		else
		{
			$ot = new object_tree();
		}
		$arr["prop"]["vcl_inst"] = treeview::tree_from_objects(array(
			"root_item" => $arr["obj_inst"],
			"ot" => $ot,
			"var" => "cat",
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "prods",
				"persist_state" => true,
			)
		));
	}
	
	function callb_who($val)
	{
		$name = "";
		if($this->can("view", $val))
		{
			$obj = obj($val);
			$name = $obj->name();
		}
		return $name;
	}

	function _init_bug_list_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "who",
			"caption" => t("Kellele"),
			"sortable" => 1,
			//"callback" => array(&$this, "callb_who"),
			//"callback_pass_row" => 1,
		));
		$t->define_field(array(
			"name" => "bug_priority",
			"caption" => t("Prioriteet"),
			"sortable" => 1,
			"numeric" => 1,
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
		/*
		$t->define_field(array(
			"name" => "comments",
			"caption" => t("Kommentaare"),
			"sortable" => 1,
			"numeric" => 1,
		));
		*/
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
	}

	function _bug_list($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_bug_list_tbl($t);

		$pt = !empty($arr["request"]["cat"]) ? $arr["request"]["cat"] : $arr["obj_inst"]->id();
		if($this->can("view", $pt))
		{
			$ol = new object_list(array(
				"parent" => $pt,
				"class_id" => CL_BUG,
			));
		}
		else
		{
			$ol = new object_list();
		}
		$t->data_from_ol($ol, array(
			"change_col" => "name"
		));
		$t->sort_by(array(
			"field" => "bug_priority",
			"sorder" => "desc",
		));
	}

	/**
		@attrib name=delete
		@param cat optional
	**/
	function delete($arr)
	{
		foreach($arr["sel"] as $id)
		{
			if($this->can("view", $id))
			{
				$obj = obj($id);
				$obj->delete();
			}
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}
}
?>
