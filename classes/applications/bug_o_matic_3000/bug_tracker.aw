<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug_tracker.aw,v 1.22 2006/03/01 08:51:26 tarvo Exp $
// $Header: /home/cvs/automatweb_dev/classes/applications/bug_o_matic_3000/bug_tracker.aw,v 1.22 2006/03/01 08:51:26 tarvo Exp $

// bug_tracker.aw - BugTrack 
define("MENU_ITEM_LENGTH", 20);
define("BUG_STATUS_CLOSED", 4);
/*

@classinfo syslog_type=ST_BUG_TRACKER relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

@property object_type type=relpicker reltype=RELTYPE_OBJECT_TYPE table=objects field=meta method=serialize
@caption Bugi objekti t&uuml;&uuml;p

@groupinfo bugs caption="Bugid" submit=no
	@groupinfo by_default caption="default" parent=bugs submit=no
	@groupinfo by_project caption="Projektid" parent=bugs submit=no
	@groupinfo by_who caption="Kellele" parent=bugs submit=no

@default group=by_default,by_project,by_who

@property bug_tb type=toolbar no_caption=1 group=bugs,by_default,by_project,by_who

@property cat type=hidden store=no

@layout bug type=hbox width=15%:85%
	@property bug_tree type=treeview parent=bug no_caption=1
	@property bug_list type=table parent=bug no_caption=1 group=bugs,archive,by_default,by_project,by_who

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
		if($arr["request"]["group"] == "bugs")
		{
			$arr["request"]["group"] = "by_default";
		}
		switch($arr["request"]["group"])
		{
			case "by_default":
				$this->sort_type = "parent";
				aw_session_set("bug_tree_sort",array("name" => "parent"));
				break;
			case "by_project":
				aw_session_set("bug_tree_sort",array("name" => "project", "class" => CL_PROJECT, "reltype" => RELTYPE_PROJECT));
				break;
			case "by_who":
				aw_session_set("bug_tree_sort",array("name" => "who", "class" => CL_CRM_PERSON, "reltype" => RELTYPE_MONITOR));
				break;
			case "by_classes":
				aw_session_set("bug_tree_sort",array("name" => "classes"));
				break;
		}

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

	/** to get subtree for who & projects view
	    @attrib name=get_node_other all_args=1
	**/
	function get_node_other($arr)
	{
	    classload("core/icons");
		$node_tree = get_instance("vcl/treeview");
		$node_tree->start_tree (array (
		"type" => TREE_DHTML,
		));
	    
		$obj = new object($arr["parent"]);
	
		if($obj->class_id() == CL_BUG_TRACKER)
		{
			$ol = new object_list(array("class_id" => CL_BUG, "bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED)));
			$c = new connection();
			$bug2proj = $c->find(array("from.class_id" => CL_BUG, "to.class_id" => $arr["clid"], "type" => $arr["reltype"], "from" => $ol->ids()));

			foreach($bug2proj as $conn)
			{
				$to[] = $conn["to"];
				$bugs[] = $conn["from"];
				$bug_count[$conn["to"]]++;
			}
			$to_unique = array_unique($to);
			
			foreach($to_unique as $project)
			{
				$obj = new object($project);
				$node_tree->add_item(0, array(
					"id" => $obj->id(),
					"name" => $this->name_cut($obj->name())." (".$bug_count[$obj->id()].")",
					"iconurl" => icons::get_icon_url($obj->class_id()),
					"url" => html::get_change_url( $arr["inst_id"], array(
						"id" => $this->self_id,
						"group" => $arr["active_group"],
						"p_id" => $obj->id(),
					)),
				));

			}
			foreach($bugs as $key => $bug)
			{
				$sub_obj =  new object($bug);
				$node_tree->add_item($to[$key] , array(
					"id" => $sub_obj->id(),
					"name" => $sub_obj->name(),
				));
			}
		}
		else
		{
			if($obj->class_id() == CL_PROJECT)
			{
				$filter = "project";
			}
			elseif($obj->class_id() == CL_CRM_PERSON)
			{
				$filter = "who";
			}
			else
			{
				$filter = "parent";
			}

			$ol = new object_list(array(
				$filter  => $arr["parent"],
				"class_id" => array(CL_BUG, CL_MENU),
				"bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED)
			));

			$objects = $ol->arr();
			foreach($objects as $obj_id => $object)
			{
				$ol = new object_list(array("parent" => $obj_id, "class_id" => array(CL_BUG, CL_MENU), "bug_status" => new obj_predicate_not(4)));
				$ol_list = $ol->arr();

				$node_tree->add_item(0 ,array(
					"id" => $obj_id,
					"name" => $this->name_cut($object->name()).(count($ol_list)?" (".count($ol_list).")":""),
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
						"name" => $sub_obj->name(),
					));
				}
			}
		}
		die($node_tree->finalize_tree());
	}
	
	/**  to get subtree for default view
		@attrib name=get_node all_args=1

	**/
	function get_node($arr)
	{
		classload("core/icons");
		$node_tree = get_instance("vcl/treeview");
		$node_tree->start_tree (array (
			"type" => TREE_DHTML,
		));

		$ol = new object_list(array("parent" => $arr["parent"], "class_id" => array(CL_BUG, CL_MENU), "bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),));

		$objects = $ol->arr();
		foreach($objects as $obj_id => $object)
		{
			$ol = new object_list(array("parent" => $obj_id, "class_id" => array(CL_BUG, CL_MENU), "bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),));
			$ol_list = $ol->arr();
			$subtree_count = (count($ol_list) > 0)?" (".count($ol_list).")":"";

			$node_tree->add_item(0 ,array(
				"id" => $obj_id,
				"name" => $this->name_cut($object->name()).$subtree_count,
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
					"name" => $sub_obj->name(),
				));
			}
		}

		die($node_tree->finalize_tree());
	}

	function _bug_tree($arr)
	{
		classload("core/icons");
		$this->tree = get_instance("vcl/treeview");
		$this->active_group = aw_global_get("request");
		$this->active_group = $this->active_group["group"];
		$this->sort_type = aw_global_get("bug_tree_sort");	
		$this->self_id = $arr["obj_inst"]->id();
		$this->tree_root_name = "Bug-Tracker";
		($this->sort_type["name"] == "project" || $this->sort_type["name"] == "who")?$orb_function = "get_node_other":$orb_function = "get_node";

		$root_name = array("by_default" => "Tavaline", "by_project"=> "Projektid", "by_who" => "Teostajad");

		$this->tree->start_tree(array(
			"type" => TREE_DHTML,
			"has_root" => 1,
			"root_name" => $root_name[($this->active_group == "bugs")?"by_default":$this->active_group],
			"root_icon" => icons::get_icon_url(CL_BUG_TRACKER),
			"get_branch_func" => $this->mk_my_orb($orb_function, array(
				"type" => $this->sort_type["name"], 
				"reltype" => $this->sort_type["reltype"], 
				"clid"=> $this->sort_type["class"], 
				"inst_id" => $this->self_id,
				"active_group" => $this->active_group,
				"parent" => " ",
			)),
		));
 
		if($this->sort_type["name"] == "parent")
		{
			$this->generate_bug_tree(array(
				"parent" => $this->self_id,
			));
		}
	
		if($this->sort_type["name"] == "who" || $this->sort_type["name"] == "project")
		{
			$this->gen_tree_other(array(
				"parent" => $this->self_id,
			));
		}

		$arr["prop"]["value"] = $this->tree->finalize_tree();
		$arr["prop"]["type"] = "text";

	}

	function gen_tree_other($arr)
	{
		$c = new connection();
		$bug2proj = $c->find(array("from.class_id" => CL_BUG, "to.class_id" => $this->sort_type["class"], "type" => $this->sort_type["reltype"]));

		foreach($bug2proj as $conn)
		{
			$projects[] = $conn["to"];
		}
		$projects = array_unique($projects);

		$this->tree->add_item(0,array(
			"id" => $this->self_id,
			"name" => $this->tree_root_name." (".count($projects).")",
		));		
		
		foreach($projects as $project)
		{
			$obj = new object($project);
			$this->tree->add_item($arr["parent"],array(
				"id" => $obj->id(),
				"name" => $obj->name(),
			));
		}
	}

	function generate_bug_tree($arr)
	{
		$ol = new object_list(array("parent" => $arr["parent"], "class_id" => array(CL_BUG, CL_MENU), "bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),));
		$objects = $ol->arr();

		$this->tree->add_item(0,array(
				"id" => $this->self_id,
				"name" => $this->tree_root_name." (".$ol->count().")",
		));
		
		foreach($objects as $obj_id => $object)
		{
			$this->tree->add_item($arr["parent"] , array(
				"id" => $obj_id,
				"name" => $object->name(),
			));
		}
	}
	
	function name_cut($name)
	{
		$pre = substr($name, 0, MENU_ITEM_LENGTH);
		$suf = (strlen($name) > MENU_ITEM_LENGTH)?"...":"";
		return $pre.$suf;
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
			// arhiivi tab
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
					"bug_status" => BUG_STATUS_CLOSED,
				));
			}
			// bugid tab
			else
			{
				$ol = new object_list(array(
					"parent" => $pt,
					"class_id" => CL_BUG,
					"bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),
				));

				if(strlen($arr["request"]["p_id"]))
				{
					$ol->filter(array(
						$this->sort_type["name"] => $arr["request"]["p_id"],
						"class_id" => CL_BUG,
						"bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),
					));
				}
				elseif(strlen($arr["request"]["b_id"]))
				{
					$ol->filter(array(
						"parent" => $arr["request"]["b_id"],
						"class_id" => CL_BUG,
						"bug_status" => new obj_predicate_not(BUG_STATUS_CLOSED),
					));
				}
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
