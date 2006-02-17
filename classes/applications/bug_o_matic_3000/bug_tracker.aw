<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug_tracker.aw,v 1.19 2006/02/17 13:23:38 sander Exp $
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug_tracker.aw,v 1.19 2006/02/17 13:23:38 sander Exp $

// bug_tracker.aw - BugTrack 
/*

@classinfo syslog_type=ST_BUG_TRACKER relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

@property object_type type=relpicker reltype=RELTYPE_OBJECT_TYPE table=objects field=meta method=serialize
@caption Bugi objekti t&uuml;&uuml;p

@groupinfo bugs caption="Bugid" submit=no
@default group=bugs

@property bug_tb type=toolbar no_caption=1 group=bugs,archive

@property cat type=hidden store=no

@layout bug type=hbox width=15%:85%
	@property bug_tree type=treeview parent=bug no_caption=1
	@property bug_list type=table parent=bug no_caption=1 group=bugs,archive

@groupinfo archive caption="Arhiiv" submit=no
@default group=archive

@reltype MONITOR value=1 clid=CL_CRM_PERSON
@caption Jälgija

@reltype OBJECT_TYPE value=2 clid=CL_OBJECT_TYPE
@caption Objekti t&uuml;&uuml;p

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
				
			case "cat":
				if($this->can("view", $arr["request"]["cat"]))
				{
					$prop["value"] = $arr["request"]["cat"];
				}
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
			case "bug_list":
				foreach($arr["request"]["bug_priority"] as $bug_id => $bug_val)
				{
					if($this->can("edit",$bug_id))
					{
						$bug = obj($bug_id);
						$bug->set_prop("bug_priority",$bug_val);
						$bug->save();
					}
				}
				foreach($arr["request"]["bug_severity"] as $bug_id => $bug_val)
				{
					if($this->can("edit",$bug_id))
					{
						$bug = obj($bug_id);
						$bug->set_prop("bug_severity",$bug_val);
						$bug->save();
					}
				}
				break;
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
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"action" => "",
			"img" => "save.gif",
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
		classload("core/icons");
		$this->tree = get_instance("vcl/treeview");

		$this->tree->start_tree(array(
			"type" => TREE_DHTML,
			"root_icon" => "/path/to/some/image",
			"tree_id" => "ad_folders",
			"persist_state" => true,
		));

		$this->self_id = $arr["obj_inst"]->id();

		$self_ol = new object_list(array(
			"parent" => $this->self_id,
			"class_id" => array(CL_BUG, CL_MENU),
			"bug_status" => new obj_predicate_not(4),
		));
		$nimi = "Bug-Tracker";
		$nimi .= ($self_ol->count() > 0)?" (".$self_ol->count().")":"";

		$this->tree->add_item(0,array(
			"id" => $this->self_id,
			"name" => $nimi,
			"url" => html::get_change_url($this->self_id, array("group" => "bugs")),
		));
		$this->generate_bug_tree(array(
			"oid" => $this->self_id,
		));

		$arr["prop"]["value"] = $this->tree->finalize_tree();
		$arr["prop"]["type"] = "text";

	}

	function generate_bug_tree($arr)
	{
		$ol = new object_list(array("parent" => $arr["oid"],"class_id" => array(CL_BUG, CL_MENU),"bug_status" => new obj_predicate_not(4),));
		$objects = $ol->arr();
		foreach($objects as $obj_id => $object)
		{
			$sub_ol = new object_list(array(
				"parent" => $obj_id,
				"class_id" => array(CL_BUG, CL_MENU),
				"bug_status" => new obj_predicate_not(4),
			));
			
			$nimi = substr($object->name(),0,20);
			$nimi .= (strlen($object->name()) > 20)?"...":"";
			$nimi .= ($sub_ol->count() > 0)?" (".$sub_ol->count().")":"";
			$this->tree->add_item($arr["oid"],array(
				"id" => $obj_id,
				"name" => $nimi,
				"iconurl" => icons::get_icon_url($object->class_id()),
				"url" => html::get_change_url($this->self_id, array("group" => "bugs" ,"cat" => $obj_id)),
			));
			
			$this->generate_bug_tree(array("oid" => $obj_id));
		}
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
	
	function show_priority($_param)
	{
		$return = html::textbox(array(
			"name" => "bug_priority[".$_param["oid"]."]",
			"size" => 2,
			"value" => $_param["bug_priority"],
		));
		return $return;
	}

	function show_severity($_param)
	{
		$return = html::textbox(array(
			"name" => "bug_severity[".$_param["oid"]."]",
			"size" => 2,
			"value" => $_param["bug_severity"],
		));
		return $return;
	}

	function show_status($_val)
	{
		$values = array(
			1 => t("Lahtine"),
			2 => t("Tegemisel"),
			3 => t("Valmis"),
			4 => t("Suletud"),
			5 => t("Vale teade"),
			6 => t("Kordamatu"),
			7 => t("Parandamatu"),
		);
		return $values[$_val];
	}
	
	function comment_callback($arr)
	{
		$ol = new object_list(array(
			"parent" => $arr["oid"],
			"class_id" => CL_COMMENT,
		));
		return html::get_change_url($arr["oid"] , array("group" => "comments" , "return_url" => get_ru()), $ol->count());
	}

	function _init_bug_list_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "bug_status",
			"caption" => t("Staatus"),
			"sortable" => 1,
			"callback" => array(&$this, "show_status"),
			"filter" => array(
				t("1"),
				t("2"),
				t("3"),
				t("4"),
				t("5"),
				t("6"),
				t("7"),
			),
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
			"callback" => array(&$this, "show_priority"),
			"callb_pass_row" => 1,
			"filter" => array(
				t("1"),
				t("2"),
				t("3"),
				t("4"),
				t("5"),
			),
		));
		$t->define_field(array(
			"name" => "bug_severity",
			"caption" => t("T&ouml;sidus"),
			"sortable" => 1,
			"numeric" => 1,
			"callback" => array(&$this, "show_severity"),
			"callb_pass_row" => 1,
			"filter" => array(
				t("1"),
				t("2"),
				t("3"),
				t("4"),
				t("5"),
			),
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
			"numeric" => 1,
			"format" => "d.m.Y / H:i"
		));

		$t->define_field(array(
			"name" => "comment",
			"caption" => t("Kommentaare"),
			"sortable" => 1,
			"numeric" => 1,
			"callback" => array(&$this,"comment_callback"),
			"callb_pass_row" => 1,
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
			if($arr["request"]["group"] == "archive")
			{
				$ot = new object_tree(array(
					"parent" => $pt,
					"class_id" => array(
						CL_BUG,CL_MENU,
					),
				));

				$ol = new object_list(array(
					"oid" => $ot->ids(),
					"class_id" => CL_BUG,
					"bug_status" => 4,
				));
			}
			else
			{
				$ol = new object_list(array(
					"parent" => $pt,
					"class_id" => CL_BUG,
					"bug_status" => new obj_predicate_not(4),
				));
			}
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
		return $this->mk_my_orb("change", array("id" => $arr["id"], "cat" => $arr["cat"], "group" => $arr["group"]), $arr["class"]);
	}
}
?>
