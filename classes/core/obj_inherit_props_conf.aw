<?php
// $Header: /home/cvs/automatweb_dev/classes/core/obj_inherit_props_conf.aw,v 1.2 2004/09/21 14:04:33 kristo Exp $
// obj_inherit_props_conf.aw - Objekti omaduste p&auml;rimine 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_SAVE, CL_OBJ_INHERIT_PROPS_CONF, on_save_conf)


@classinfo syslog_type=ST_OBJ_INHERIT_PROPS_CONF relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property inherit_from type=relpicker reltype=RELTYPE_INHERIT_FROM
@caption Vali objekt, millelt p&auml;rida

@property inherit_to_class type=select
@caption Vali klass, kuhu omadusi kirjutatakse

@property inherit_to_objs type=relpicker reltype=RELTYPE_INHERIT_TO multiple=1
@caption Vali objektid kuhu p&auml;rida

@groupinfo ihf caption="P&auml;ritavad omadused"


@layout ihf_main type=hbox group=ihf width=20%:60%:20%
@layout ihf_tree type=vbox parent=ihf_main group=ihf
@layout ihf_table type=vbox parent=ihf_main group=ihf
@layout ihf_tree_right type=vbox parent=ihf_main group=ihf

@property ihf_tree type=treeview store=no group=ihf no_caption=1 parent=ihf_tree
@property ihf_tbl type=table store=no group=ihf no_caption=1 parent=ihf_table
@property ihf_tree_right type=treeview store=no group=ihf no_caption=1 parent=ihf_tree_right


@groupinfo if_ov caption="Valitud p&auml;rimised" submit=no

@property if_ov_toolbar type=toolbar store=no no_caption=1 group=if_ov
@property if_ov_table type=table store=no no_caption=1 group=if_ov


@reltype INHERIT_FROM value=1  clid=CL_MENU
@caption p&auml;ritav objekt

@reltype INHERIT_TO value=2  clid=CL_MENU,CL_SWOT_THREAT
@caption kirjutatav objekt

*/

class obj_inherit_props_conf extends class_base
{
	function obj_inherit_props_conf()
	{
		$this->init(array(
			"tpldir" => "core/obj_inherit_props_conf",
			"clid" => CL_OBJ_INHERIT_PROPS_CONF
		));
	}

	function get_property($arr)
	{
		$this->tree_sel = $arr["request"]["tree_sel"];
		$this->tree_sel_right = $arr["request"]["tree_sel_right"];
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "if_ov_toolbar":
				$prop["toolbar"]->add_button(array(
					"name" => "del",
					"img" => "delete.gif",
					"tooltip" => "Kustuta valitud",
					"url" => "javascript:document.changeform.submit()"
				));
				break;

			case "ihf_tree":
				$this->do_ihf_tree($arr);
				break;

			case "ihf_tree_right":
				$this->do_ihf_tree_right($arr);
				break;

			case "ihf_tbl":
				$this->do_ihf_table($arr);
				break;

			case "if_ov_table":
				$this->do_if_ov_table($arr);
				break;

			case "inherit_to_class":
				$prop["options"] = get_class_picker();
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
			case "ihf_tbl":
				$cur_wd = $arr["obj_inst"]->meta("wd");
				foreach(safe_array($arr["request"]["wd"]) as $k => $v)
				{
					if ($k != "" && $v != "")
					{
						$cur_wd[$k] = $v;
					}
				}
				$arr["obj_inst"]->set_meta("wd", $cur_wd);
				break;

			case "if_ov_table":
				$cur_wd = $arr["obj_inst"]->meta("wd");
				$del = safe_array($arr["request"]["del_ifs"]);
				foreach($del as $from_prop)
				{
					unset($cur_wd[$from_prop]);
				}
				$arr["obj_inst"]->set_meta("wd", $cur_wd);
				break;
		}
		return $retval;
	}	

	function on_save_conf($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_OBJ_INHERIT_PROPS_CONF,
			"lang_id" => array(),
			"site_id" => array()
		));

		$data = array();
		foreach($ol->arr() as $o)
		{
			if ($o->prop("inherit_from") && $o->prop("inherit_to_class"))
			{
				$wd = safe_array($o->meta("wd"));
				$tmp = array();

				foreach($wd as $from_prop => $to_prop)
				{
					if ($from_prop != "" && $to_prop != "")
					{
						
						$tmp[] = array(
							"from_prop" => $from_prop,
							"to_class" => $o->prop("inherit_to_class"),
							"to_prop" => $to_prop,
							"only_to_objs" => $this->make_keys($o->prop("inherit_to_objs"))
						);
					}
				}

				$data[$o->prop("inherit_from")] = $tmp;
			}
		}

		$this->put_file(array(
			"file" => aw_ini_get("site_basedir")."/files/obj_inherit_props.conf",
			"content" => aw_serialize($data)
		));
	}

	function do_ihf_tree_right($arr)
	{
		$tree =& $arr["prop"]["vcl_inst"];

		// read properties		
		$cu = get_instance("cfg/cfgutils");
		$props = $cu->load_properties(array(
			"clid" => $arr["obj_inst"]->prop("inherit_to_class")
		));
		$grps = $cu->groupinfo;
		foreach($grps as $gn => $gd)
		{
			$cpat = $gd["caption"];
			if ($arr["request"]["tree_sel_right"] == $gn)
			{
				$cpat = "<b>".$cpat."</b>";
			}
			$tree->add_item(($gd["parent"] != "" ? "grp_".$gd["parent"] : 0), array(
				"id" => "grp_".$gn,
				"name" => $cpat,
				"url" => aw_url_change_var("tree_sel_right", $gn),
			));
		}
	}

	function do_ihf_tree($arr)
	{
		$tree =& $arr["prop"]["vcl_inst"];

		// read properties		
		$ifo = obj($arr["obj_inst"]->prop("inherit_from"));
		$cu = get_instance("cfg/cfgutils");
		$props = $cu->load_properties(array(
			"clid" => $ifo->class_id()
		));
		$grps = $cu->groupinfo;
		foreach($grps as $gn => $gd)
		{
			$cpat = $gd["caption"];
			if ($arr["request"]["tree_sel"] == $gn)
			{
				$cpat = "<b>".$cpat."</b>";
			}
			$tree->add_item(($gd["parent"] != "" ? "grp_".$gd["parent"] : 0), array(
				"id" => "grp_".$gn,
				"name" => $cpat,
				"url" => aw_url_change_var("tree_sel", $gn),
			));
		}
	}

	function _init_ihf_table(&$t)
	{
		$t->define_field(array(
			"name" => "prop",
			"caption" => "Omadus"
		));
		$t->define_field(array(
			"name" => "type",
			"caption" => "Omaduse t&uuml;&uuml;p",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "write_to",
			"caption" => "Vali omadus, kuhu see omadus kirjutatakse",
			"align" => "center"
		));
	}

	function do_ihf_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_ihf_table($t);

		if (!$arr["request"]["tree_sel"])
		{
			return;
		}

		$wd = $arr["obj_inst"]->meta("wd");		

		$selectable_props = array("" => "---");
		// read properties		
		$cu = get_instance("cfg/cfgutils");
		$props = $cu->load_properties(array(
			"clid" => $arr["obj_inst"]->prop("inherit_to_class")
		));
		foreach($props as $pn => $pd)
		{
			if ($args["request"]["tree_sel_right"] == "" || $args["request"]["tree_sel_right"] == $pd["group"])
			{
				$selectable_props[$pn] = $pd["caption"];
			}
		}

		// read properties		
		$ifo = obj($arr["obj_inst"]->prop("inherit_from"));
		$cu = get_instance("cfg/cfgutils");
		$props = $cu->load_properties(array(
			"clid" => $ifo->class_id()
		));

		foreach($props as $pn => $pd)
		{
			if ($arr["request"]["tree_sel"] == $pd["group"])
			{
				$t->define_data(array(
					"prop" => $pd["caption"],
					"type" => $pd["type"],
					"write_to" => html::select(array(
						"selected" => $wd[$pn],
						"options" => $selectable_props,
						"name" => "wd[$pn]"
					))
				));
			}
		}
		$t->set_sortable(false);
	}

	function callback_mod_reforb(&$arr)
	{
		$arr["tree_sel"] = $this->tree_sel;
		$arr["tree_sel_right"] = $this->tree_sel_right;
	}

	function callback_mod_retval($arr)
	{
		$arr["args"]["tree_sel"] = $arr["request"]["tree_sel"];
		$arr["args"]["tree_sel_right"] = $arr["request"]["tree_sel_right"];
	}

	function _init_if_ov_table(&$t)
	{
		$t->define_field(array(
			"name" => "from_class",
			"caption" => "Mis klassist",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "from_prop",
			"caption" => "Mis omadusest",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "to_class",
			"caption" => "Mis klassi",
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "to_prop",
			"caption" => "Mis omadusse",
			"align" => "center"
		));

		$t->define_chooser(array(
			"field" => "from_prop",
			"name" => "del_ifs"
		));
	}

	function do_if_ov_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_if_ov_table($t);

		$wd = safe_array($arr["obj_inst"]->meta("wd"));

		$cld = aw_ini_get("classes");
		$if = obj($arr["obj_inst"]->prop("inherit_from"));
		$from_nm = $cld[$if->class_id()]["name"];
		$to_nm = $cld[$arr["obj_inst"]->prop("inherit_to_class")]["name"];

		foreach($wd as $from_prop => $to_prop)
		{
			if ($from_prop != "" && $to_prop != "")
			{
				$t->define_data(array(
					"from_class" => $from_nm,
					"from_prop" => $from_prop,
					"to_prop" => $to_prop,
					"to_class" => $to_nm
				));
			}
		}
		$t->set_sortable(false);
	}
}
?>
