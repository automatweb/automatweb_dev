<?php

/*

@classinfo syslog_type=ST_LAYOUT relationmgr=yes

@groupinfo settings caption=M&auml;&auml;rangud
@groupinfo layout caption=Tabel
@groupinfo styles caption=Stiilid
@groupinfo aliases caption="Tabeli sisu"
@groupinfo hfoot caption="P&auml;is ja jalus"
@groupinfo import caption=Import
@groupinfo preview caption=Eelvaade

@default table=objects
@default field=meta
@default method=serialize

@property rows type=textbox group=general size=3 store=no
@caption Ridu

@property columns type=textbox group=general size=3 store=no
@caption Tulpi

@property cell_style_folders type=relpicker reltype=RELTYPE_CELLSTYLE_FOLDER group=settings multiple=1
@caption Stiilide kataloogid

@property grid type=callback group=layout 
@caption Tabel

@property grid_styles type=callback group=styles 
@caption Stiilid

@property table_style type=select group=settings 
@caption Tabeli stiil

@property row_widths type=callback callback=get_row_widths group=settings store=no
@caption Ridade laiused

@property row_heights type=callback callback=get_row_heights group=settings store=no
@caption Ridade k&otilde;rgused

@property grid_aliases type=callback group=aliases 
@caption Aliased

@property grid_aliases_list type=aliasmgr group=aliases store=no
@caption Aliaste manager

@property grid_preview type=callback group=preview 
@caption Eelvaade

@property import_file type=fileupload group=import 
@caption Uploadi .csv fail

@property import_remove_empty type=checkbox ch_value=1 group=import 
@caption Kas eemaldame tühjad read lõpust

@property import_sep type=textbox size=1 group=import 
@caption Mis märgiga on tulbad eraldatud?

@property show_in_folders type=relpicker reltype=RELTYPE_SHOW_FOLDER multiple=1 rel=1 group=general
@caption Millistes kataloogides n&auml;idatakse

@property header type=textarea rows=10 cols=50 field=meta method=serialize group=hfoot
@caption P&auml;is

@property footer type=textarea rows=10 cols=50 field=meta method=serialize group=hfoot
@caption Jalus

@reltype CELLSTYLE_FOLDER value=1 clid=CL_MENU
@caption celli stiilide kataloog

@reltype SHOW_FOLDER value=2 clid=CL_MENU
@caption näita selles kataloogis

@classinfo no_status=1
			

*/

class layout extends class_base
{
	function layout()
	{
		$this->init(array(
			'tpldir' => 'grid_editor',
			'clid' => CL_LAYOUT
		));
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);

		// if oid is in the arguments check whether that object is attached to
		// this document and display it instead of document
		$oid = aw_global_get("oid");
		if ($oid)
		{
			$q = "SELECT * FROM aliases WHERE source = '$alias[target]' AND target = '$oid' AND type =" . CL_FILE;
			$this->db_query($q);
			$row = $this->db_next();
			if ($row)
			{
				$fi = get_instance("file");
				$fl = $fi->get_file_by_id($oid);
				return $fl["content"];
			};
		}



		$ob = obj($alias["target"]);
		$ge = get_instance("vcl/grid_editor");
		$grid = $ob->meta('grid');
		$grid['table_style'] = $ob->meta('table_style');

		$tmp = $ge->show($grid, $alias["target"], &$tpls);
		//$tmp = str_replace("\n", "<br/>", $tmp);

		$al = get_instance("aliasmgr");
		if ($ob->prop("header") != "")
		{
			$h_tmp = create_email_links(nl2br($ob->prop("header")));
			$al->parse_oo_aliases($ob->id(), &$h_tmp);
			if ($grid["table_style"])
			{
				$st = get_instance("style");
				$h_tmp = $st->apply_style_to_text($grid["table_style"], $h_tmp, array("is_header" => true));
			}
			$tmp = $h_tmp.$tmp;
		}

		if ($ob->prop("footer") != "")
		{
			$h_tmp = create_email_links(nl2br($ob->prop("footer")));
			$al->parse_oo_aliases($ob->id(), &$h_tmp);
			if ($grid["table_style"])
			{
				$st = get_instance("style");
				$h_tmp = $st->apply_style_to_text($grid["table_style"], $h_tmp, array("is_footer" => true));
			}
			$tmp .= $h_tmp;
		}

		$d = get_instance("document");
		$d->create_relative_links($tmp);
		if (strpos($tmp, "<a") !== false || strpos($tmp, "< a") !== false || strpos($tmp, "<A") !== false)
		{
			return $tmp;
		}
		return create_email_links($tmp);
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = obj($id);
		$ge = get_instance("vcl/grid_editor");
		return create_email_links($ge->show($ob->meta('grid'), $id));
	}

	function get_property(&$arr)
	{
		$prop = &$arr['prop'];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "grid":
				$ge = get_instance("vcl/grid_editor");
				$prop['value'] = $ge->on_edit($arr['obj_inst']->meta('grid'), $arr['obj_inst']->id());
				break;

			case "grid_aliases":
				$ge = get_instance("vcl/grid_editor");
				$prop['value'] = $ge->on_aliases_edit($arr['obj_inst']->meta('grid'), $arr['obj_inst']->id());
				break;

			case "grid_styles":
				$ge = get_instance("vcl/grid_editor");
				$prop['value'] = $ge->on_styles_edit($arr['obj_inst']->meta('grid'), $arr['obj_inst']->id());
				break;

			case "grid_preview":
				$ge = get_instance("vcl/grid_editor");
				$grid = $arr['obj_inst']->meta('grid');
				$grid["table_style"] = $arr["obj_inst"]->meta("table_style");
				$tmp = $ge->show($grid, $arr['obj_inst']->id());
				if ($arr['obj_inst']->prop("header") != "")
				{
					$tmp = nl2br($arr['obj_inst']->prop("header")).$tmp;
				}
				if ($arr['obj_inst']->prop("footer") != "")
				{
					$tmp .= nl2br($arr['obj_inst']->prop("footer"));
				}
				$d = get_instance("document");
				$d->create_relative_links($tmp);
				if (strpos($tmp, "<a") !== false)
				{
					$prop["value"] = $tmp;
				}
				else
				{
					$prop["value"] = create_email_links($tmp);
				}

				break;

			case "table_style":
				$st = get_instance("style");
				$prop['options'] = $st->get_table_style_picker();
				break;

			case "rows":
				$ge = get_instance("vcl/grid_editor");
				$ge->_init_table($arr['obj_inst']->meta('grid'));
				$prop['value'] = $ge->get_num_rows();
				break;

			case "columns":
				$ge = get_instance("vcl/grid_editor");
				$ge->_init_table($arr['obj_inst']->meta('grid'));
				$prop['value'] = $ge->get_num_cols();
				break;
		}
		return $retval;
	}

	function set_property(&$arr)
	{
		$prop = &$arr['prop'];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "grid":
				$ge = get_instance("vcl/grid_editor");
				$prop['value'] = $ge->on_edit_submit($arr['obj_inst']->meta('grid'), $arr['request']);
				break;

			case "grid_aliases":
				$ge = get_instance("vcl/grid_editor");
				$arr['obj_inst']->set_meta("grid",$ge->on_aliases_edit_submit($arr['obj_inst']->meta('grid'), $arr['request']));
				break;

			case "grid_styles":
				$ge = get_instance("vcl/grid_editor");
				$arr["obj_inst"]->set_meta("grid",$ge->on_styles_edit_submit($arr['obj_inst']->meta('grid'), $arr['request']));
				break;

			case "rows":
				$ge = get_instance("vcl/grid_editor");
				$ge->_init_table($arr['obj_inst']->meta('grid'));
				$ge->set_num_rows($arr["request"]["rows"]);
				$ge->set_num_cols($arr["request"]["columns"]);
				$arr["obj_inst"]->set_meta("grid",$ge->_get_table());
				break;

			case "columns":
				$ge = get_instance("vcl/grid_editor");
				$ge->_init_table($arr['obj_inst']->meta('grid'));
				$ge->set_num_cols($arr["request"]["columns"]);
				$ge->set_num_rows($arr["request"]["rows"]);
				$arr["obj_inst"]->set_meta("grid",$ge->_get_table());
				break;

			case "import_file":
				global $import_file;
				if (is_uploaded_file($import_file))
				{
					$ge = get_instance("vcl/grid_editor");
					$arr["obj_inst"]->set_meta("grid",$ge->do_import(array(
						"sep" => $arr["request"]["import_sep"],
						"remove_empty" => $arr["request"]["import_remove_empty"],
						"file" => $import_file
					)));
				}
				break;

			case "row_widths":
				$ge = get_instance("vcl/grid_editor");
				$ge->_init_table($arr["obj_inst"]->meta("grid"));
				for($i = 0; $i < $ge->get_num_cols(); $i++)
				{
					$ge->set_col_width($i, $arr["request"]["colw"][$i]);
				}
				$arr['obj_inst']->set_meta('grid',$ge->_get_table());
				break;

			case "row_heights":
				$ge = get_instance("vcl/grid_editor");
				$ge->_init_table($arr['obj_inst']->meta('grid'));
				for($i = 0; $i < $ge->get_num_cols(); $i++)
				{
					$ge->set_col_height($i, $arr["request"]["colh"][$i]);
				}
				$arr['obj_inst']->set_meta('grid',$ge->_get_table());
				break;
		}
		return $retval;
	}

	function _do_import($arr)
	{
		extract($arr);

	}

	/**  
		
		@attrib name=sel_style params=name all_args="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function sel_style($arr)
	{
		extract($arr);
		$this->read_template("pickstyle.tpl");
		$ob = obj($oid);

		// make style pick list
		// folders:
		$styles = array();
		$folders = new aw_array($ob->meta('cell_style_folders'));

		$ol = new object_list(array(
			"parent" => $folders,
			"class_id" => CL_CSS,
			"lang_id" => array(),
			"site_id" => array()
		));
		$styles = $ol->names(array(
			"add_folders" => true
		));

		$this->vars(array(
			"stylessel" => $this->option_list("", $styles),
			"reforb"	=> $this->mk_reforb("submit_styles", array(
				"cols" => $cols,
				"rows" => $rows,
				"cells" => $cells,
				"oid" => $oid
			))
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_styles params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_styles($arr)
	{
		extract($arr);
		
		$obj = obj($oid);
		$ge = get_instance("vcl/grid_editor");
		$ge->_init_table($obj->meta('grid'));

		// now we need to figure out where to apply the style
		if ($rows != "")
		{
			$rowarr = explode("dr_", $rows);
			foreach($rowarr as $row)
			{
				if ($row !== "")
				{
					// set style for the row
					$ge->set_row_style($row, $style);
				}
			}
		}

		if ($cols != "")
		{
			$colarr = explode("dc_", $cols);
			foreach($colarr as $col)
			{
				if ($col !== "")
				{
					// set style for the col
					$ge->set_col_style($col, $style);
				}
			}
		}

		if ($cells != "")
		{
			$celarr = explode("sel_", $cells);
			foreach($celarr as $cell)
			{
				if ($cell !== "")
				{
					list($rd, $cd) = explode(";", $cell);
					list(, $row) = explode("=", $rd);
					list(, $col) = explode("=", $cd);

					// set style for the cell
					$ge->set_cell_style($row, $col, $style);
				}
			}
		}

		// now save object
		$o = obj($oid);
		$o->set_meta("grid", $ge->_get_table());
		$o->save();

		return $this->mk_my_orb("sel_style", array(
			"cols" => $cols,
			"rows" => $rows,
			"cells" => $cells,
			"oid" => $oid
		));
	}

	////
	// !returns the layout data that can be fed to grid editor. useful when you can select a default layout
	function get_layout($oid)
	{
		if (!is_oid($oid))
		{
			return array();
		}
		$ob = new object($oid);
		return $ob->meta("grid");
	}

	function get_row_widths($arr)
	{
		$ret = array();
		$ge = get_instance("vcl/grid_editor");
		$ge->_init_table($arr['obj_inst']->meta('grid'));
		for($i = 0; $i < $ge->get_num_cols(); $i++)
		{
			$ret["colw[$i]"] = array(
				"name" => "colw[$i]",
				"type" => "textbox",
				"size" => 6,
				"group" => "settings",
				"table" => "objects",
				"field" => "meta",
				"method" => "serialize",
				"caption" => (1+$i)." tulba laius",
				"value" => $ge->get_col_width($i)
			);
		}
		return $ret;
	}
	
	function get_row_heights($arr)
	{
		$ret = array();
		$ge = get_instance("vcl/grid_editor");
		$ge->_init_table($arr['obj_inst']->meta('grid'));
		for($i = 0; $i < $ge->get_num_cols(); $i++)
		{
			$ret["colh[$i]"] = array(
				"name" => "colh[$i]",
				"type" => "textbox",
				"size" => 6,
				"group" => "settings",
				"table" => "objects",
				"field" => "meta",
				"method" => "serialize",
				"caption" => ($i+1)." tulba k&otilde;rgus",
				"value" => $ge->get_col_height($i)
			);
		}
		return $ret;
	}
}
?>
