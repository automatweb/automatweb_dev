<?php

/*

@classinfo syslog_type=ST_LAYOUT relationmgr=yes

@groupinfo general caption=Üldine
@groupinfo settings caption=M&auml;&auml;rangud
@groupinfo layout caption=Tabel
@groupinfo styles caption=Stiilid
@groupinfo aliases caption=Aliased
@groupinfo preview caption=Eelvaade

@default table=objects
@default field=meta

@property rows type=textbox group=general method=serialize size=3
@caption Ridu

@property columns type=textbox group=general method=serialize size=3
@caption Tulpi

@property cell_style_folders type=relpicker reltype=RELTYPE_CELLSTYLE_FOLDER group=settings field=meta method=serialize multiple=1
@caption Stiilide kataloogid

@property grid type=callback group=layout method=serialize field=meta table=objects
@caption Tabel

@property grid_styles type=callback group=styles method=serialize field=meta table=objects
@caption Stiilid

@property table_style type=select group=settings method=serialize field=meta table=objects
@caption Tabeli stiil

@property grid_aliases type=callback group=aliases method=serialize field=meta table=objects
@caption Aliased

@property grid_aliases_list type=aliasmgr group=aliases store=no
@caption Aliaste manager

@property grid_preview type=callback group=preview method=serialize
@caption Eelvaade

*/

define("RELTYPE_CELLSTYLE_FOLDER",1);

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
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$this->read_template('show.tpl');

		$this->vars(array(
			'name' => $ob['name']
		));

		return $this->parse();
	}

	function get_property(&$arr)
	{
		$prop = &$arr['prop'];
		if ($prop['name'] == "grid")
		{
			$ge = get_instance("vcl/grid_editor");
			$prop['value'] = $ge->on_edit($arr['obj']['meta']['grid'], $arr['obj']['oid']);
		}
		else
		if ($prop['name'] == "grid_aliases")
		{
			$ge = get_instance("vcl/grid_editor");
			$prop['value'] = $ge->on_aliases_edit($arr['obj']['meta']['grid'], $arr['obj']['oid']);
		}
		else
		if ($prop['name'] == "grid_styles")
		{
			$ge = get_instance("vcl/grid_editor");
			$prop['value'] = $ge->on_styles_edit($arr['obj']['meta']['grid'], $arr['obj']['oid']);
		}
		else
		if ($prop['name'] == "grid_preview")
		{
			$ge = get_instance("vcl/grid_editor");
			$prop['value'] = $ge->show($arr['obj']['meta']['grid'], $arr['obj']['oid']);
		}
		else
		if ($prop['name'] == "table_style")
		{
			$st = get_instance("style");
			$prop['options'] = $st->get_select(0, ST_TABLE, true);
		}
		else
		if ($prop['name'] == "rows")
		{
			$ge = get_instance("vcl/grid_editor");
			$ge->_init_table($arr['obj']['meta']['grid']);
			$prop['value'] = $ge->get_num_rows();
		}
		else
		if ($prop['name'] == "columns")
		{
			$ge = get_instance("vcl/grid_editor");
			$ge->_init_table($arr['obj']['meta']['grid']);
			$prop['value'] = $ge->get_num_cols();
		}
		return PROP_OK;
	}

	function set_property(&$arr)
	{
		$prop = &$arr['prop'];
		if ($prop['name'] == "grid")
		{
			$ge = get_instance("vcl/grid_editor");
			$prop['value'] = $ge->on_edit_submit($arr['obj']['meta']['grid'], $arr['form_data']);
		}
		else
		if ($prop['name'] == "grid_aliases")
		{
			$ge = get_instance("vcl/grid_editor");
			$arr['metadata']['grid'] = $ge->on_aliases_edit_submit($arr['obj']['meta']['grid'], $arr['form_data']);
		}
		else
		if ($prop['name'] == "grid_styles")
		{
			$ge = get_instance("vcl/grid_editor");
			$arr['metadata']['grid'] = $ge->on_styles_edit_submit($arr['obj']['meta']['grid'], $arr['form_data']);
		}
		else
		if ($prop['name'] == "rows")
		{
			$ge = get_instance("vcl/grid_editor");
			$ge->_init_table($arr['obj']['meta']['grid']);
			$ge->set_num_rows($arr["form_data"]["rows"]);
			$ge->set_num_cols($arr["form_data"]["columns"]);
			$arr['metadata']['grid'] = $ge->_get_table();
		}
		else
		if ($prop['name'] == "columns")
		{
			$ge = get_instance("vcl/grid_editor");
			$ge->_init_table($arr['obj']['meta']['grid']);
			$ge->set_num_cols($arr["form_data"]["columns"]);
			$ge->set_num_rows($arr["form_data"]["rows"]);
			$arr['metadata']['grid'] = $ge->_get_table();
		}
		return PROP_OK;
	}

	function sel_style($arr)
	{
		extract($arr);
		$this->read_template("pickstyle.tpl");
		$ob = $this->get_object($oid);

		//$css = get_instance("css");
		//$stylesel = $css->get_select();
		// make style pick list
		// folders:
		$styles = array();
		$folders = $ob['meta']['cell_style_folders'];
		foreach($folders as $fld)
		{
			$styles += $this->list_objects(array(
				"parent" => $fld,
				"class" => CL_CSS,
				"add_folders" => true
			));
		}

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

	function submit_styles($arr)
	{
		extract($arr);
		
		$obj = $this->get_object($oid);
		$ge = get_instance("vcl/grid_editor");
		$ge->_init_table($obj['meta']['grid']);

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
		$this->upd_object(array(
			"oid" => $oid,
			"metadata" => array(
				"grid" => $ge->_get_table()
			)
		));
		return $this->mk_my_orb("sel_style", array(
			"cols" => $cols,
			"rows" => $rows,
			"cells" => $cells,
			"oid" => $oid
		));
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_CELLSTYLE_FOLDER => "celli stiilide kataloog",
		);
	}
}
?>
