<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_treeview/object_treeview_v2.aw,v 1.1 2004/04/29 12:21:06 kristo Exp $
// object_treeview_v2.aw - Objektide nimekiri v2 
/*

@classinfo syslog_type=ST_OBJECT_TREEVIEW_V2 relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general


@property ds type=relpicker reltype=RELTYPE_DATASOURCE field=meta method=serialize
@caption Andmed


@groupinfo showing caption="N&auml;itamine"
@property show_folders type=checkbox ch_value=1 field=meta method=serialize group=showing
@caption N&auml;ita katalooge

@property show_add type=checkbox ch_value=1 field=meta method=serialize group=showing
@caption N&auml;ita toolbari

@property tree_type type=chooser  field=meta method=serialize default=1 group=showing
@caption Puu n&auml;itamise meetod

@groupinfo styles caption="Stiilid"
@property title_bgcolor type=colorpicker field=meta method=serialize group=styles
@caption Pealkirja taustav&auml;rv

@property even_bgcolor type=colorpicker field=meta method=serialize group=styles
@caption Paaris rea taustav&auml;rv

@property odd_bgcolor type=colorpicker field=meta method=serialize group=styles
@caption Paaritu rea taustav&auml;rv

@property header_css type=relpicker reltype=RELTYPE_CSS field=meta method=serialize  group=styles
@caption Pealkirja stiil

@property line_css type=relpicker reltype=RELTYPE_CSS field=meta method=serialize  group=styles
@caption Rea stiil

@groupinfo columns caption=Tulbad
@property columns type=callback callback=callback_get_columns field=meta method=serialize group=columns
@caption Tulbad


@reltype DATASOURCE value=1 clid=CL_OTV_DS_OBJ,CL_OTV_DS_POSTIPOISS
@caption andmed

@reltype CSS value=2 clid=CL_GROUP
@caption css stiil

*/

class object_treeview_v2 extends class_base
{
	var $all_cols = array(
		"icon" => "Ikoon",
		"name" => "Nimi",
		"size" => "Suurus",
		"class_id" => "T&uuml;&uuml;p",
		"modified" => "Muutmise kuup&auml;ev",
		"modifiedby" => "Muutja",
		"change" => "Muuda",
		"select" => "Vali"
	);

	function object_treeview_v2()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/object_treeview/object_treeview_v2",
			"clid" => CL_OBJECT_TREEVIEW_V2
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "tree_type":
				$prop["options"] = array(
					TREE_HTML => "HTML",
					TREE_JS => "Javascript",
					TREE_DHTML => "DHTML"
				);
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
			case "columns":
				$arr["obj_inst"]->set_meta("sel_columns", $arr["request"]["column"]);
				break;
		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		extract($arr);
		if (!is_oid($id))
		{
			return "";
		}
		$ob = obj($id);

		$this->read_template('show.tpl');

		// init driver
		$d_o = obj($ob->prop("ds"));
		$d_inst = $d_o->instance();

		$this->_insert_row_styles($ob);

		// returns an array of object id's that are folders that are in the object
		$fld = $d_inst->get_folders($d_o);

		// get all objects to show
		$ol = $d_inst->get_objects($d_o, $fld, $GLOBALS["tv_sel"]); 

		// make folders
		$this->vars(array(
			"FOLDERS" => $this->_draw_folders($ob, $ol, $fld)
		));

		// get all related object types
		// and their cfgforms
		// and make a nice little lut from them.
		$class2cfgform = array();
		foreach($ob->connections_from(array("type" => RELTYPE_ADD_TYPE)) as $c)
		{
			$addtype = $c->to();
			if ($addtype->prop("use_cfgform"))
			{
				$class2cfgform[$addtype->prop("type")] = $addtype->prop("use_cfgform");
			}
		}

		$this->cnt = 0;
		$c = "";
		$sel_cols = $ob->meta("sel_columns");

		foreach($ol as $odata)
		{
			$c .= $this->_do_parse_file_line($odata, $d_inst, $d_o, array(
				"tree_obj" => $ob, 
				"sel_cols" => $sel_cols
			));
		}

		$tb = "";
		$no_tb = "";
		if ($ob->prop("show_add"))
		{
			$tb = $this->parse("HEADER_HAS_TOOLBAR");
		}
		else
		{
			$no_tb = $this->parse("HEADER_NO_TOOLBAR");
		}
		$this->vars(array(
			"FILE" => $c,
			"HEADER_HAS_TOOLBAR" => $tb,
			"HEADER_NO_TOOLBAR" => $no_tb,
			"reforb" => $this->mk_reforb("submit_show", array(
				"return_url" => aw_global_get("REQUEST_URI"),
				"subact" => "0"
			))
		));

		// columns
		foreach($this->all_cols as $colid => $coln)
		{
			$str = "";
			if ($sel_cols[$colid] == 1)
			{
				$str = $this->parse("HEADER_".$colid);
			}
			$this->vars(array(
				"HEADER_".$colid => $str
			));
		}

		$res = $this->parse();
		if ($ob->prop("show_add"))
		{
			$res = $this->_get_add_toolbar($ob).$res;
		}
		return $res;
	}

	function callback_get_columns($arr)
	{
		$cols = $arr["obj_inst"]->meta("sel_columns");

		$ret = array();

		foreach($this->all_cols as $colid => $coln)
		{

			$rt = "column[".$colid."]";
			$ret[$rt] = array(
				'name' => $rt,
				'caption' => $coln,
				'type' => 'checkbox',
				'ch_value' => 1,
				'store' => 'no',
				'group' => 'columns',
				'value' => $cols[$colid]
			);
		}
		return $ret;
	}

	function _insert_row_styles($o)
	{
		$style = "textmiddle";
		if ($o->prop("line_css"))
		{
			$style = "st".$o->prop("line_css");
			active_page_data::add_site_css_style($o->prop("line_css"));
		}

		$header_css = "textmiddle";
		if ($o->prop("header_css"))
		{
			$header_css = "st".$o->prop("header_css");
			active_page_data::add_site_css_style($o->prop("header_css"));
		}

		$header_bg = "#E0EFEF";
		if ($o->prop("title_bgcolor"))
		{
			$header_bg = "#".$o->prop("title_bgcolor");
		}

		$this->vars(array(
			"css_class" => $style,
			"header_css_class" => $header_css,
			"header_bgcolor" => $header_bg
		));
	}

	function _get_bgcolor($ob, $line)
	{
		$ret = "";
		if (($line % 2) == 1)
		{
			$ret = $ob->prop("odd_bgcolor");
			if ($ret == "")
			{
				$ret = "#EFF7F7";
			}
		}
		else
		{
			$ret = $ob->prop("even_bgcolor");
			if ($ret == "")
			{
				$ret = "#FFFFFF";
			}
		}
		return $ret;
	}

	function _draw_folders($ob, $ol, $folders)
	{
		if (!$ob->meta('show_folders'))
		{
			return;
		}

		classload("icons");
		// use treeview widget
		$tv = get_instance("vcl/treeview");
		$tv->start_tree(array(
			"root_name" => "",
			"root_url" => "",
			"root_icon" => "",
			"type" => TREE_DHTML, //$ob->meta('tree_type'),
			"persist_state" => true
		));

		// now, insert all folders defined
		foreach($folders as $fld)
		{
			$tv->add_item($fld["parent"], array(
				"id" => $fld["id"],
				"name" => $fld["name"],
				"url" => aw_url_change_var("tv_sel", $fld["id"]),
				"icon" => $fld["icon"],
				"comment" => $fld["comment"],
				"data" => array(
					"changed" => $this->time2date($fld["modified"], 2)
				)
			));
		}
		$tv->set_selected_item($GLOBALS["tv_sel"]);

		$pms = array();
		/*if (isset($GLOBALS["class"]))
		{
			$pms["rootnode"] = aw_global_get("section");
		}*/
		
		return $tv->finalize_tree($pms);
	}

	function _get_add_toolbar($ob, $drv = NULL)
	{
		$this->tpl_init("automatweb/menuedit");
		$this->read_template("js_add_menu.tpl");

		$types_c = $ob->connections_from(array(
			"type" => RELTYPE_ADD_TYPE
		));

		$menu = "";
		$classes = aw_ini_get("classes");

		$parent = $GLOBALS["tv_sel"] ? $GLOBALS["tv_sel"] : $this->first_folder;

/*		$p_o = obj($parent);
		if ($p_o->class_id() == CL_SERVER_FOLDER)
		{
			$this->vars(array(
				"url" => $this->mk_my_orb("add_file", array("id" => $p_o->id(), "section" => aw_global_get("section")), "server_folder"),
				"caption" => $classes[CL_FILE]["name"]
			));
			$menu .= $this->parse("MENU_ITEM");
		}
		else
		{*/
			$ot = get_instance("admin/object_type");
			foreach($types_c as $c)
			{
				$c_o = $c->to();

				$this->vars(array(
					"url" => $ot->get_add_url(array("id" => $c_o, "parent" => $parent, "section" => $parent)),
					"caption" => $c_o->prop("name")
				));
				$menu .= $this->parse("MENU_ITEM");
			}
//		}
		$this->vars(array(
			"menu_id" => "aw_menu_0",
			"MENU_ITEM" => $menu
		));
		$this->vars(array("MENU" => $this->parse("MENU")));
		
		

		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "add",
			"tooltip" => "Uus",
			"url" => "#",
			"onClick" => "return buttonClick(event, 'aw_menu_0');",
			"img" => "new.gif",
			"class" => "menuButton",
		));

		$tb->add_button(array(
			"name" => "del",
			"tooltip" => "Kustuta",
			"url" => "#",
			"onClick" => "document.objlist.subact.value='delete';document.objlist.submit()",
			"img" => "delete.gif",
			"class" => "menuButton",
		));
		return $this->parse().$tb->get_toolbar();
	}

	function _do_parse_file_line($arr, $drv, $d_o, $parms)
	{
		extract($parms);
		extract($arr);
		$this->vars(array(
			"show" => $url,
			"name" => $name,
			"oid" => $oid,
			"target" => $target,
			"sizeBytes" => $fileSizeBytes,
			"sizeKBytes" => $fileSizeKBytes,
			"sizeMBytes" => $fileSizeMBytes,
			"comment" => $comment,
			"type" => $type,
			"add_date" => date("d.m.Y H:i", $add_date),
			"mod_date" => date("d.m.Y H:i", $mod_date),
			"adder" => $adder,
			"modder" => $modder,
			"icon" => $icon,
			"act" => $act,
			"delete" => $delete,
			"bgcolor" => $bgcolor,
			"size" => ($fileSizeMBytes > 1 ? $fileSizeMBytes."MB" : ($fileSizeKBytes > 1 ? $fileSizeKBytes."kb" : $fileSizeBytes."b"))
		));

		$del = "";
		if ($drv->check_acl("delete", $d_o, $arr["id"]))
		{
			$del = $this->parse("DELETE");
		}
		$this->vars(array(
			"DELETE" => $del
		));

		$tb = "";
		$no_tb = "";
		if ($tree_obj->prop("show_add"))
		{
			$tb = $this->parse("HAS_TOOLBAR");
		}
		else
		{
			$no_tb = $this->parse("NO_TOOLBAR");
		}
		$this->vars(array(
			"HAS_TOOLBAR" => $tb,
			"NO_TOOLBAR" => $no_tb
		));

		// columns
		foreach($this->all_cols as $colid => $coln)
		{
			$str = "";
			if ($sel_cols[$colid] == 1)
			{
				$str = $this->parse("FILE_".$colid);
			}
			$this->vars(array(
				"FILE_".$colid => $str
			));
		}
		
		$this->cnt++;

		return $this->parse("FILE");
	}
}
?>