<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/object_treeview/object_treeview_v2.aw,v 1.36 2005/01/04 10:02:13 kristo Exp $
// object_treeview_v2.aw - Objektide nimekiri v2
/*

@classinfo syslog_type=ST_OBJECT_TREEVIEW_V2 relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize


@property ds type=relpicker reltype=RELTYPE_DATASOURCE
@caption Andmed


@groupinfo showing caption="N&auml;itamine"
@default group=showing
@property show_folders type=checkbox ch_value=1
@caption N&auml;ita katalooge

@property show_add type=checkbox ch_value=1
@caption N&auml;ita toolbari

@property show_link_new_win type=checkbox ch_value=1
@caption Vaata link uues aknas

@property hide_content_table_by_default type=checkbox ch_value=1
@caption Vaikimisi &auml;ra n&auml;ita sisu tabelit

@property tree_type type=chooser default=1
@caption Puu n&auml;itamise meetod

@property per_page type=textbox size=5
@caption Mitu rida lehel

@property show_hidden_cols type=checkbox ch_value=1  default=1
@caption N&auml;ita peidetud tulpasid?

@property sortbl type=table store=no
@caption Andmete sorteerimine

@property filter_table type=table store=no
@caption Andmete filtreerimine

@property group_by_folder type=select
@caption Kaustade j&auml;rgi grupeeritav v&auml;li

@property group_in_table type=select
@caption Millise v&auml;lja j&auml;rgi tabel grupeerida

@property filter_by_char_field type=select 
@caption Millise v&auml;lja v&auml;&auml;rtuse esit&auml;he j&auml;rgi filtreeritakse

@property alphabet_in_lower_case type=checkbox ch_value=1 
@caption T&auml;hestiku kuvamisel kasutada v&auml;iket&auml;hti 

@groupinfo styles caption="Stiilid"
@default group=styles
@property title_bgcolor type=colorpicker
@caption Pealkirja taustav&auml;rv

@property even_bgcolor type=colorpicker
@caption Paaris rea taustav&auml;rv

@property odd_bgcolor type=colorpicker
@caption Paaritu rea taustav&auml;rv

@property group_header_bgcolor type=colorpicker
@caption Grupeeriva rea taustav&auml;rv

@property table_css type=relpicker reltype=RELTYPE_CSS
@caption Tabeli stiil

@property header_css type=relpicker reltype=RELTYPE_CSS
@caption Pealkirja stiil

@property group_css type=relpicker reltype=RELTYPE_CSS
@caption Grupeeriva rea stiil

@property line_css type=relpicker reltype=RELTYPE_CSS
@caption Rea stiil

@groupinfo columns caption=Tulbad
@default group=columns
@property columns type=table no_caption=1
@caption Tulbad


@reltype DATASOURCE value=1 clid=CL_OTV_DS_OBJ,CL_OTV_DS_POSTIPOISS,CL_OTV_DS_ROADINFO,CL_DB_TABLE_CONTENTS
@caption andmed

@reltype CSS value=2 clid=CL_CSS
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

	var $alphabet = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "&Otilde;", "&Auml;", "&Ouml;", "&Uuml;");

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


		$col_list = $this->_get_col_list(array(
			"o" => $arr['obj_inst'],
			"hidden_cols" => ($arr['obj_inst']->prop("show_hidden_cols") == 1) ? true : false,
		));

		$col_list = array_merge(array("" => ""), $col_list);

		switch($prop["name"])
		{
			case "tree_type":
				$prop["options"] = array(
					TREE_DHTML => "DHTML"
				);
				$prop["value"] = TREE_DHTML;
				break;

			case "sortbl":
				$this->do_sortbl($arr);
				break;

			case "filter_table":
				$this->do_filter_table($arr);
				break;
			case "group_by_folder":
////
// here i should check, if AW object list uses meta objects to draw
// folders or not. If it doesn't, i think theres nothing to do with that
// property so just hide it.
				$ds_obj = $arr['obj_inst']->get_first_obj_by_reltype("RELTYPE_DATASOURCE");
				if(!empty($ds_obj) && ($ds_obj->class_id() == CL_OTV_DS_OBJ) && ($ds_obj->prop("use_meta_as_folders") == 1))
				{
					$prop['options'] = $col_list;
				}
				else
				{
					$retval = PROP_IGNORE;
				}
				break;
			case "group_in_table":
				$prop['options'] = $col_list;
				break;
			case "filter_by_char_field":
				$prop['options'] = $col_list;
				break;
			case "group_table":
				$this->do_group_table($arr);
				break;
			case "access":
				$this->do_access_tbl($arr);
				break;

			case "columns":
				$this->_do_columns($arr);
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
				$arr["obj_inst"]->set_meta("sel_columns_ord", $arr["request"]["column_ord"]);
				$arr["obj_inst"]->set_meta("sel_columns_text", $arr["request"]["column_text"]);
				$arr["obj_inst"]->set_meta("sel_columns_editable", $arr["request"]["column_edit"]);
////
// don't save empty fields
				$valid_column_fields = array();

				foreach(safe_array($arr["request"]["column_fields"]) as $key => $value)
				{
					foreach($value as $k => $v)
					{
						if(empty($v['field']))
						{
							unset($value[$k]);
						}
					}
					if(!empty($value))
					{
						$valid_column_fields[$key] = $value;
					}
				}
				$arr["obj_inst"]->set_meta("sel_columns_fields", $valid_column_fields);
				break;

			case "sortbl":
				$this->do_save_sortbl($arr);
				break;

			case "filter_table":
				$this->do_save_filter_table($arr);
				break;

			case "group_table":
				$arr['obj_inst']->set_meta("saved_groups", $arr['request']['group_field']);
				break;
		}
		return $retval;
	}

	function parse_alias($arr)
	{
		return $this->show(array(
			"id" => $arr["alias"]["target"],
			"oid" => $arr['oid'],
		));
	}

	/**

		@attrib name=show nologin=1

		@param id required type=int acl=view
		@param tv_sel optional type=int
		@param char optional
	**/
	function show($arr)
	{
		extract($arr);
		if (!is_oid($id))
		{
			return "";
		}
		enter_function("otv2::show");
		$ob = obj($id);

		$this->read_template('show.tpl');
		// init driver
		$d_o = obj($ob->prop("ds"));
		$d_inst = $d_o->instance();

		$this->_insert_styles($ob);

		// returns an array of object id's that are folders that are in the object
		$fld = $d_inst->get_folders($d_o);

		// get all objects to show
		// if is checked, that objects won't be shown by default, then don't show them, unless
		// there are set some url params (tv_sel, char)
		if (($ob->meta("hide_content_table_by_default") == 1) && empty($_GET['tv_sel']) && empty($_GET['char']))
		{
			$ol = array();
		}
		else
		{
			$ol = $d_inst->get_objects($d_o, $fld, $_GET["tv_sel"]);
		}

		// make folders
		$this->vars(array(
			"FOLDERS" => $this->_draw_folders($ob, $ol, $fld, $oid)
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



// do some filtering in $ol
		$filters = $ob->meta("saved_filters");
// filtering is taking place if one of the following conditions are present:
// -> $filter variable is array and its element count is bigger than zero
// -> $_GET['tv_sel'] is not empty
// -> $_GET['char'] is not empty
		if ((is_array($filters) && count($filters) > 0) || !empty($_GET['tv_sel']) || !empty($_GET['char']))
		{

//			$ol_result = array();
			foreach($ol as $ol_key => $ol_value)
			{
				foreach(safe_array($filters) as $filter)
				{
					if($filter['is_strict'] == 1)
					{
						if($ol_value[$filter['field']] != $filter['value'])
						{
//							array_push($ol_result, $ol_item);

							unset($ol[$ol_key]);

							break;
						}
					}
					else
					{
						if(strpos(strtolower($ol_value[$filter['field']]), strtolower($filter['value'])) === false)
						{
//							array_push($ol_result, $ol_item);
							unset($ol[$ol_key]);
							break;
						}
					}
				}

// if meta data fields are used as folders, then i need to do
// some filtering according to $_GET['tv_sel']

// damn - it doesn't work the way i'd like to :S
				if(($d_o->prop("use_meta_as_folders") == 1) && empty($_GET['char']))
				{

					if(!empty($_GET['tv_sel']) && ($fld[$_GET['tv_sel']]['name'] != $ol_value[$ob->meta("group_by_folder")]))
					{
						unset($ol[$ol_key]);
					}
				}
// if there is char param set in the url, then filter objects by this fields value which is set by
// filter_by_char_field property
				if(!empty($_GET['char']))
				{
					$f = strtolower($ol_value[$ob->meta("filter_by_char_field")]);
					if((strlen($_GET['char']) == 1) && ($f{0} != strtolower($_GET['char'])))
					{
						unset($ol[$ol_key]);
					}
				}
			}

		}

// some filtering according to url parameter. Only needed if folders are
// meta data groups



//		arr($ol);
//		arr($ob->meta("sel_columns_fields"));
//		arr($ob->meta("saved_filters"));


// if there are set some datasource fields to be displayed in one table field

		$sel_columns_fields = new aw_array($ob->meta("sel_columns_fields"));

		if($sel_columns_fields->count() != 0)
		{
			$ol_result = array();
			foreach($ol as $ol_item)
			{
				foreach($sel_columns_fields->get() as $sel_columns_fields_key => $sel_columns_fields_value)
				{
	//				arr($sel_columns_fields_value);

					foreach($sel_columns_fields_value as $key => $value)
					{
	//					arr($value);
						if(empty($ol_item[$value['field']]))
						{
							$ol_item[$sel_columns_fields_key] .= "";
						}
						else
						{
							$ol_item[$sel_columns_fields_key] .= $value['sep'];
						}
						$ol_item[$sel_columns_fields_key] .= $value['left_encloser'];
						$ol_item[$sel_columns_fields_key] .= $ol_item[$value['field']];
						$ol_item[$sel_columns_fields_key] .= $value['right_encloser'];
					}
				}
	//			echo "algus-----------------------------<br>";
	//			arr($ol_item);
				array_push($ol_result, $ol_item);
			}
			$ol = $ol_result;
		}



		$this->cnt = 0;
		$c = "";
		$sel_cols = $ob->meta("sel_columns");

		$col_list = $this->_get_col_list(array(
			"o" => $ob,
			"hidden_cols" => true,
		));

		$tmp = new aw_array($ob->meta("itemsorts"));
		$this->__is = $tmp->get();
		usort($ol, array(&$this, "__is_sorter"));

		// now do pages
		if ($ob->prop("per_page"))
		{
			$this->do_pageselector($ol, $ob->prop("per_page"));
		}

		$has_access_to = false;
		$has_add_access = false;
		foreach($ol as $odata)
		{
			if ($d_inst->check_acl("edit", $d_o, $odata["id"]))
			{
				$has_access_to = true;
			}
			$last_o = $odata;
		}

		$edit_columns = safe_array($ob->meta("sel_columns_editable"));
		if (!$has_access_to)
		{
			unset($col_list["change"]);
			unset($col_list["select"]);

			// also unset all edit columns
			foreach($edit_columns as $coln => $_tmp)
			{
				unset($col_list[$coln]);
			}
			$edit_columns = array();
		}

		if ($last_o)
		{
			if (!$d_inst->check_acl("add", $d_o, $last_o["id"]))
			{
				$ob->set_prop("show_add", false);
			}
		}
		else
		if ($_GET["tv_sel"])
		{
			if (!$d_inst->check_acl("add", $d_o, $_GET["tv_sel"]))
			{
				$ob->set_prop("show_add", false);
			}
		}

		$group_field = $ob->prop("group_in_table");
		$group_name = "";
		$sel_cols_count = count($sel_cols);
// parsing table rows - if the field value, which is used to make table groups
// changes, i'll create group header line and put it in the table
// groups are not made, if char param is present in url
		foreach($ol as $odata)
		{
			if(($group_name != $odata[$group_field]) && empty($_GET['char']))
			{
				$this->vars(array(
					"content" => $odata[$ob->prop("group_in_table")],
					"cols_count" => $sel_cols_count,
//					"group_bgcolor" => $group_header_color_code,
				));
				$c .= $this->parse("FILE_GROUP");
			}

			$c .= $this->_do_parse_file_line($odata, $d_inst, $d_o, array(
				"tree_obj" => $ob,
				"sel_cols" => $sel_cols,
				"col_list" => $col_list,
				"edit_columns" => $edit_columns
			));
			$group_name = $odata[$group_field];
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
// checking, if there is set a field, which values should be use to filter by first character
// and according to this i'm showing or not showing the alphabet list
		$filter_by_char_field = $ob->meta("filter_by_char_field");
		if(!empty($filter_by_char_field))
		{
			$alphabet_parsed = "";
			foreach($this->alphabet as $character)
			{
				$character_value = $character;
				if(!empty($_GET['char']) && $character == $_GET['char'])
				{
					$character_value = "<strong>".$character."</strong>";
				}
				if($ob->prop("alphabet_in_lower_case"))
				{
					$character_value = strtolower($character_value);
				}
				$this->vars(array(
					"char" => $character_value,
					"char_url" => aw_ini_get("baseurl")."/".$oid."?char=".$character,
				));
				$alphabet_parsed .= $this->parse("ALPHABET");
			}
// lets put a link at the end of the alphabet to make all fields to show
			$this->vars(array(
				"char" => t("K&otilde;ik"),
				"char_url" => aw_ini_get("baseurl")."/".$oid."?char=all",
			));
			$alphabet_parsed .= $this->parse("ALPHABET");
		}

		$this->vars(array(
			"ALPHABET" => $alphabet_parsed,
			"FILE" => $c,
			"HEADER_HAS_TOOLBAR" => $tb,
			"HEADER_NO_TOOLBAR" => $no_tb,
			"reforb" => $this->mk_reforb("submit_show", array(
				"return_url" => aw_global_get("REQUEST_URI"),
				"subact" => "0",
				"id" => $ob->id(),
				"edit_mode" => count($edit_columns),
				"tv_sel" => $arr["tv_sel"]
			))
		));

		$udef_cols = $ob->meta("sel_columns_text");
		if (!is_array($udef_cols))
		{
			$udef_cols = $col_list;
		}
		if (($ob->meta("hide_content_table_by_default") == 1) && empty($_GET['tv_sel']) && empty($_GET['char']))
		{

		}
		else
		{
			// columns
			$h_str = "";
			foreach($col_list as $colid => $coln)
			{
				$str = "";
				if ($sel_cols[$colid] == 1)
				{
					$this->vars(array(
						"h_text" => ($colid == "icon" ? "" : $udef_cols[$colid])
					));
					$str = $this->parse("HEADER");
					$this->vars(array(
						"HEADER" => $str
					));
					$h_str .= $this->parse("HEADER");
				}
			}

			$this->vars(array(
				"HEADER" => $h_str
			));
		}

		$res = $this->parse();
		if ($ob->prop("show_add"))
		{
			$res = $this->_get_add_toolbar($ob).$res;
		}
		exit_function("otv2::show");
		return $res;
	}

	function _init_cols_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "show",
			"caption" => "Kas n&auml;idata",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "jrk",
			"caption" => "Jrk",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "text",
			"caption" => "Tekst",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "editable",
			"caption" => "Muudetav",
			"sortable" => 1,
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "fields",
			"caption" => "Milliste v&auml;ljade sisu n&auml;idata",
			"sortable" => 1,

		));
	}

	function _do_columns($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_cols_tbl($t);

		$cols = $arr["obj_inst"]->meta("sel_columns");
		$cols_ord = $arr["obj_inst"]->meta("sel_columns_ord");
		$cols_text = $arr["obj_inst"]->meta("sel_columns_text");
		$cols_edit = $arr["obj_inst"]->meta("sel_columns_editable");
		$cols_fields = $arr["obj_inst"]->meta("sel_columns_fields");

		$cold = $this->_get_col_list(array(
			"o" => $arr["obj_inst"],
			"hidden_cols" => true,
		));

		if (!is_array($cols_text))
		{
			$cols_text = $cold;
		}

		foreach($cold as $colid => $coln)
		{
			$text = $editable = $fields = "";


			if ($cols[$colid])
			{
				$text = html::textbox(array(
					"name" => "column_text[".$colid."]",
					"value" => $cols_text[$colid],
					"size" => 40
				));

				$editable = html::checkbox(array(
					"name" => "column_edit[".$colid."]",
					"value" => 1,
					"size" => 40,
					"checked" => $cols_edit[$colid]
				));

				$max_id = 0;
				$fields = "";

				if(is_array($cols_fields[$colid])){
					foreach($cols_fields[$colid] as $f_key => $f_val)
					{

						$fields .= html::textbox(array(
							"name" => "column_fields[".$colid."][".$f_key."][sep]",
							"value" => $cols_fields[$colid][$f_key]['sep'],
							"size" => 2,
						));

						$fields .= html::textbox(array(
							"name" => "column_fields[".$colid."][".$f_key."][left_encloser]",
							"value" => $cols_fields[$colid][$f_key]['left_encloser'],
							"size" => 2,
						));

						$fields .= html::select(array(
							"name" => "column_fields[".$colid."][".$f_key."][field]",
							"options" => array_merge(array(""=>""), $cold),
							"selected" => ($cols_fields) ? $cols_fields[$colid][$f_key]['field'] : $colid,
						));

						$fields .= html::textbox(array(
							"name" => "column_fields[".$colid."][".$f_key."][right_encloser]",
							"value" => $cols_fields[$colid][$f_key]['right_encloser'],
							"size" => 2,
						));

						$fields .= "<br />";
					}
				}
				$max_id = max($max_id, $f_key);
				$max_id++;

				$fields .= html::textbox(array(
					"name" => "column_fields[".$colid."][".$max_id."][sep]",
					"value" => "",
					"size" => 2,
				));

				$fields .= html::textbox(array(
					"name" => "column_fields[".$colid."][".$max_id."][left_encloser]",
					"value" => "",
					"size" => 2,
				));

				$fields .= html::select(array(
					"name" => "column_fields[".$colid."][".$max_id."][field]",
					"options" => array_merge(array(""=>""), $cold),
					"selected" => "",
				));

				$fields .= html::textbox(array(
					"name" => "column_fields[".$colid."][".$max_id."][right_encloser]",
					"value" => "",
					"size" => 2,
				));
			}

			$t->define_data(array(
				"name" => $coln,
				"show" => html::checkbox(array(
					"name" => "column[".$colid."]",
					"value" => 1,
					"checked" => ($cols[$colid])
				)),
				"jrk" => html::textbox(array(
					"name" => "column_ord[".$colid."]",
					"size" => 5,
					"value" => $cols_ord[$colid],
				)),
				"text" => $text,
				"editable" => $editable,
				"fields" => $fields,
			));
		}

		$t->set_default_sortby("name");
		$t->sort_by();
	}

	function _insert_styles($o)
	{
		$style = "textmiddle";
		classload("layout/active_page_data");
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

		$group_css = "textmiddle";
		if ($o->prop("group_css"))
		{
			$group_css = "st".$o->prop("group_css");
			active_page_data::add_site_css_style($o->prop("group_css"));
		}

// lets put a css style for table too
		$table_css = "textmiddle";
		if ($o->prop("table_css"))
		{
			$table_css = "st".$o->prop("table_css");
			active_page_data::add_site_css_style($o->prop("table_css"));
		}

		$header_bg = "";
		if ($o->prop("title_bgcolor"))
		{
			$header_bg = $o->prop("title_bgcolor");
			if($header_bg{0} != "#")
			{
				$header_bg = "#".$o->prop("title_bgcolor");
			}
/*			else
			{
				$header_bg = $o->prop("title_bgcolor");
			}
*/
		}

		$group_header_bg = "";
		if($o->prop("group_header_bgcolor"))
		{
			$group_header_bg = $o->prop("group_header_bgcolor");
			if(!empty($group_header_bg) && $group_header_bg{0} != "#")
			{
				$group_header_bg = "#".$group_header_bg;
			}
		}
		$this->vars(array(
			"css_class" => $style,
			"table_css_class" => $table_css,
			"header_css_class" => $header_css,
			"group_css_class" => $group_css,
			"header_bgcolor" => $header_bg,
			"group_header_bgcolor" => $group_header_bg,
		));
	}

	function _get_bgcolor($ob, $line)
	{
		$ret = "";
		if (($line % 2) == 1)
		{
			$ret = $ob->prop("odd_bgcolor");
		}
		else
		{
			$ret = $ob->prop("even_bgcolor");
		}
		if ($ret != "" && $ret{0} != "#")
		{
			$ret = "#".$ret;
		}
		return $ret;
	}

	function _draw_folders($ob, $ol, $folders, $oid)
	{
		if (!$ob->meta('show_folders'))
		{
		//	return;
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
//				"url" => aw_url_change_var("page", NULL, aw_url_change_var("tv_sel", $fld["id"], aw_url_change_var("char", ""))),
				"url" => aw_ini_get("baseurl")."/".$oid."?tv_sel=".$fld['id'],
				"icon" => $fld["icon"],
				"comment" => $fld["comment"],
				"data" => array(
					"changed" => $this->time2date($fld["modified"], 2)
				)
			));
		}
		$tv->set_selected_item($_GET["tv_sel"]);

		$pms = array();
		/*if (isset($GLOBALS["class"]))
		{
			$pms["rootnode"] = aw_global_get("section");
		}*/

		return $tv->finalize_tree($pms);
	}

	function _get_add_toolbar($ob, $drv = NULL)
	{
		// must read these from the datasource
		$ds_o = obj($ob->prop("ds"));
		$ds_i = $ds_o->instance();
		list($parent, $types) = $ds_i->get_add_types($ds_o);

		$tb = get_instance("vcl/toolbar");

		$has_b = false;

		if ($parent && count($types))
		{
			$menu = "";
			$classes = aw_ini_get("classes");

			$tb->add_menu_button(array(
				"name" => "add",
				"tooltip" => "Uus",
				"img" => "new.gif",
			));


			$ot = get_instance("admin/object_type");
			foreach($types as $c_o)
			{
				$tb->add_menu_item(array(
					"parent" => "add",
					"url" => $ot->get_add_url(array("id" => $c_o->id(), "parent" => $parent, "section" => $parent)),
					"text" => $c_o->prop("name"),
				));
			}

			$has_b = true;
		}

		$cols = $ob->meta("sel_columns");
		if ($cols["select"])
		{
			$tb->add_button(array(
				"name" => "del",
				"tooltip" => t("Kustuta"),
				"url" => "#",
				"onClick" => "document.objlist.subact.value='delete';document.objlist.submit()",
				"img" => "delete.gif",
				"class" => "menuButton",
				"confirm" => t("Oled kindel et tahad objekte kustutada?")
			));
			$has_b = true;
		}

		$edc = safe_array($ob->meta("sel_columns_editable"));
		if (count($edc))
		{
			$tb->add_button(array(
				"name" => "save",
				"tooltip" => "Salvesta",
				"url" => "#",
				"onClick" => "document.objlist.submit();return true;",
				"img" => "save.gif"
			));
		}

		if ($has_b)
		{
			return $tb->get_toolbar();
		}
		return "";
	}

	function _do_parse_file_line($arr, $drv, $d_o, $parms)
	{
		extract($parms);
		extract($arr);

		$ld = array(
			"url" => $url,
			"caption" => $name,
		);
		if ($d_o->prop("show_link_new_win"))
		{
			$ld["target"] = "_blank";
		}

		$_name = html::href($ld);
		if ($url == "")
		{
			$_name = $name;
		}
		$formatv = array(
			"show" => $url,
			"name" => $_name,
			"oid" => $oid,
			"target" => $target,
			"sizeBytes" => $fileSizeBytes,
			"sizeKBytes" => $fileSizeKBytes,
			"sizeMBytes" => $fileSizeMBytes,
			"comment" => $comment,
			"class_id" => $type,
			"created" => date("d.m.Y H:i", $add_date),
			"modified" => date("d.m.Y H:i", $mod_date),
			"createdby" => $adder,
			"modifiedby" => $modder,
			"icon" => $icon,
			"act" => $act,
			"delete" => $delete,
			"bgcolor" => $bgcolor,
			"size" => ($fileSizeMBytes > 1 ? $fileSizeMBytes."MB" : ($fileSizeKBytes > 1 ? $fileSizeKBytes."kb" : $fileSizeBytes."b")),
			"change" => $change,
			"select" => html::checkbox(array(
				"name" => "sel[]",
				"value" => $id,
			))
		);

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
		$str = "";
		foreach($col_list as $colid => $coln)
		{
			if ($sel_cols[$colid] == 1)
			{
				$content = (isset($formatv[$colid]) ? $formatv[$colid] : $arr[$colid]);
				if ($edit_columns[$colid] == 1)
				{
					$content = html::textbox(array(
						"name" => "objs[".$arr["id"]."][$colid]",
						"value" => $content,
						"size" => 5
					));
				}
				$this->vars(array(
					"content" => $content
				));
				$str .= $this->parse("COLUMN");
			}
		}

		$this->cnt++;

		$this->vars(array(
			"COLUMN" => $str
		));

// get row background color

		$this->vars(array(
			"bgcolor" => $this->_get_bgcolor($tree_obj, $this->cnt),
		));

		return $this->parse("FILE");
	}

	///
	// !Get columns list
	//  o(Object)
	//  hidden_cols(bool) - true, if hidden cols should be returned
	//
	function _get_col_list($params = array())
	{
		extract($params);

		$tmp = $o->meta("sel_columns");

		$cold = $this->all_cols;
		if ($o->prop("ds"))
		{
			$dso = obj($o->prop("ds"));
			$ds_i = $dso->instance();
			if (method_exists($ds_i, "get_fields"))
			{
				foreach($ds_i->get_fields($dso) as $fn => $fs)
				{
					$cold[$fn] = $fs;
				}
			}
		}

		foreach($cold as $col_key => $col_val)
		{
			if(!$hidden_cols)
			{
				if($tmp[$col_key] != 1)
				{
					unset($cold[$col_key]);
				}
			}
		}

		// sort
		$this->__sby = $o->meta("sel_columns_ord");
		uksort($cold, array(&$this, "__sby"));
		return $cold;
	}

	function __sby($a, $b)
	{
		if ($this->__sby[$a] == $this->__sby[$b])
		{
			return 0;
		}
		return $this->__sby[$a] >  $this->__sby[$b] ? 1 : 0;
	}

	function get_folders_as_object_list($object, $level, $parent_o)
	{
		$this->tree_ob = $object;

		$ol = new object_list();

		$d_o = obj($this->tree_ob->prop("ds"));
		$d_inst = $d_o->instance();
	
		$folders = $d_inst->get_folders($d_o);
		foreach($folders as $fld)
		{
			$i_o = obj($fld["id"]);
			
			if ($level == 0)
			{
				$parent = 0;
				$found = false;
				foreach($folders as $fp)
				{
					if ($fp["id"] == $i_o->parent())
					{
						$found = true;
					}
				}
				if ($found)
				{
					$parent = $i_o->parent();
				}

				if ($parent == 0)
				{
					$ol->add($fld["id"]);
				}
			}
			else
			{
				if ($parent_o->id() == $i_o->parent())
				{
					$ol->add($fld["id"]);
				}
			}
		}

		return $ol;
	}

	function make_menu_link($sect_obj, $ref = NULL)
	{
		if ($ref)
		{
			$link = $this->mk_my_orb("show", array("id" => $ref->id(), "tv_sel" => $sect_obj->id(), "section" => $sect_obj->id()));;
		}
		else
		{
			$link = $this->mk_my_orb("show", array("id" => $this->tree_ob->id(), "tv_sel" => $sect_obj->id(), "section" => $sect_obj->id()));;
		}
		return $link;
	}

	function get_yah_link($tree, $cur_menu)
	{
		return $this->mk_my_orb("show", array("id" => $tree, "tv_sel" => $cur_menu->id(), "section" => $cur_menu->id()));
	}

	function do_sortbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sortbl($t);

		$cols = $this->_get_col_list(array(
			"o" => $arr["obj_inst"],
			"hidden_cols" => ($arr['obj_inst']->prop("show_hidden_cols") == 1) ? true : false,
		));
//		$tmp = $arr["obj_inst"]->meta("sel_columns");
		$elements = array_merge(array("" => "") + $cols);
//		foreach($cols as $colid => $coln)
//		{
//			$elements[$colid] = $coln;
//		}
//		$elements = array_merge($elements + $cols)
//		arr($arr['obj_inst']->prop("only_visible_cols"));
/*
		foreach($cols as $colid => $coln)
		{
			if($arr['obj_inst']->prop("show_hidden_cols") == 1)
			{
				$elements[$colid] = $coln;
			}
			else
			{
				if (1 == $tmp[$colid])
				{
					$elements[$colid] = $coln;
				}
			}
		}
		
*/		
		$maxi = 0;
		$is = new aw_array($arr["obj_inst"]->meta("itemsorts"));
		foreach($is->get() as $idx => $sd)
		{
			$t->define_data(array(
				"sby" => html::select(array(
					"options" => $elements,
					"selected" => $sd["element"],
					"name" => "itemsorts[$idx][element]"
				)),
				"sby_ord" => html::select(array(
					"options" => array("asc" => "Kasvav", "desc" => "Kahanev"),
					"selected" => $sd["ord"],
					"name" => "itemsorts[$idx][ord]"
				)),
				"is_date" => html::checkbox(array(
					"name" => "itemsorts[$idx][is_date]",
					"value" => 1,
					"checked" => ($sd["is_date"] == 1)
				))
			));
			$maxi = max($maxi, $idx);
		}
		$maxi++;

		$t->define_data(array(
			"sby" => html::select(array(
				"options" => $elements,
				"selected" => "",
				"name" => "itemsorts[$maxi][element]"
			)),
			"sby_ord" => html::select(array(
				"options" => array("asc" => "Kasvav", "desc" => "Kahanev"),
				"selected" => "",
				"name" => "itemsorts[$maxi][ord]"
			))
		));

		$t->set_sortable(false);
	}

	function do_save_sortbl(&$arr)
	{
		$awa = new aw_array($arr["request"]["itemsorts"]);
		$res = array();
		foreach($awa->get() as $idx => $dat)
		{
			if ($dat["element"])
			{
				$res[] = $dat;
			}
		}

		$arr["obj_inst"]->set_meta("itemsorts", $res);
	}

	function _init_sortbl(&$t)
	{
		$t->define_field(array(
			"name" => "sby",
			"caption" => "Sorditav v&auml;li",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "sby_ord",
			"caption" => "Kasvav / kahanev",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "is_date",
			"caption" => "Kuup&auml;ev tekstina?",
			"align" => "center"
		));
	}

	function __is_sorter($a, $b)
	{
		$comp_a = NULL;
		$comp_b = NULL;
		// find the first non-matching element
		foreach($this->__is as $isd)
		{
			if ($isd["element"] == "modified")
			{
				$isd["element"] = "mod_date";
			}
			if ($isd["element"] == "created")
			{
				$isd["element"] = "add_date";
			}
			$comp_a = $a[$isd["element"]];
			$comp_b = $b[$isd["element"]];

			if (1 == $isd["is_date"])
			{
				list($d, $m,$y) = explode(".", $comp_a);
				$comp_a = mktime(0,0,0, $m,$d, $y);

				list($d, $m,$y) = explode(".", $comp_b);
				$comp_b = mktime(0,0,0, $m,$d, $y);
			}
			$ord = $isd["ord"];
			if ($comp_a != $comp_b)
			{
				break;
			}
		}

		// sort by that element
		if ($comp_a  == $comp_b)
		{
			return 0;
		}

		if ($ord == "asc")
		{
			return $comp_a > $comp_b ? 1 : -1;
		}
		else
		{
			return $comp_a < $comp_b ? -1 : 1;
		}
	}

	function _init_filter_table(&$t)
	{
		$t->define_field(array(
			"name" => "filter_field",
			"caption" => "Filtreeritav v&auml;li",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "filter_value",
			"caption" => "Filtreeritav v&auml;&auml;rtus",
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "filter_strict",
			"caption" => "Kas t&auml;pne?",
			"align" => "center",
		));

	}

	function do_filter_table(&$arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$this->_init_filter_table($t);

		$all_cols = $this->_get_col_list(array(
			"o" => $arr["obj_inst"],
			"hidden_cols" => ($arr['obj_inst']->prop("show_hidden_cols") == 1) ? true : false,
		));
//		$tmp = $arr["obj_inst"]->meta("sel_columns");
		$cols = array_merge(array("" => "") + $all_cols);
		$saved_filters = new aw_array($arr['obj_inst']->meta("saved_filters"));

		$max_id = 0;
		foreach($saved_filters->get() as $id => $filter_data)
		{
			$t->define_data(array(
				"filter_field" => html::select(array(
						"name" => "filters[".$id."][field]",
						"options" => $cols,
						"selected" => $filter_data['field'],
					)),
				"filter_value" => html::textbox(array(
						"name" => "filters[".$id."][value]",
						"value" => $filter_data['value'],
					)),
				"filter_strict" => html::checkbox(array(
						"name" => "filters[".$id."][is_strict]",
						"value" => 1,
						"checked" => ($filter_data['is_strict'] == 1) ? true : false,
					)),
			));

			$max_id = max($max_id, $id);
		}
		$max_id++;

		$t->define_data(array(
			"filter_field" => html::select(array(
					"name" => "filters[".$max_id."][field]",
					"options" => $cols,
					"selected" => "",
				)),
			"filter_value" => html::textbox(array(
					"name" => "filters[".$max_id."][value]",
					"value" => "",
				)),
		));

		$t->set_sortable(false);

	}

	function do_save_filter_table(&$arr)
	{
		$saved_filters = new aw_array($arr['request']['filters']);
		$valid_filters = array();
		foreach($saved_filters->get() as $filter_key => $filter_value)
		{
			if($filter_value['field'])
			{
				array_push($valid_filters, $filter_value);
			}
		}
		$arr['obj_inst']->set_meta("saved_filters", $valid_filters);
	}

	function do_pageselector(&$list, $per_page)
	{
		$page = $GLOBALS["page"];
		$start = $page * $per_page;
		$end = ($page + 1) * $per_page;
		$cnt = 0;

		$num = count($list);
		$num_p = $num / $per_page;

		$tmp = array();
		foreach($list as $k => $v)
		{
			if (($cnt >= $start && $cnt < $end))
			{
				//unset($list[$k]);
				$tmp[$k] = $v;
			}
			$cnt++;
		}
		$list = $tmp;

		$ps = "";
		for ($i = 0; $i <  $num_p; $i++)
		{
			$this->vars(array(
				"url" => aw_url_change_var("page", $i),
				"page" => ($i * $per_page)." - ".min($num, ((($i+1) * $per_page)-1))
			));
			if ($i == $page)
			{
				$ps .= $this->parse("PAGE_SEL");
			}
			else
			{
				$ps .= $this->parse("PAGE");
			}
		}

		$this->vars(array(
			"PAGE_SEL" => "",
			"PAGE" => $ps
		));
	}

	/**

		@attrib name=submit_show params=name default="0"

		@param id required type=int acl=view
		@param subact required
		@param sel optional
		@param return_url required		
		
		@returns
		
		
		@comment

	**/
	function submit_show($arr)
	{
		extract($arr);

		$ob = obj($id);
		$d_o = obj($ob->prop("ds"));
		$d_inst = $d_o->instance();

		if ($subact == "delete")
		{
			$tt = array();
			$awa = new aw_array($sel);
			$farr = $awa->get();

			// get datasource
			$d_inst->do_delete_objects($d_o, $farr);
		}

		// if has editable columns, save them
		if ($arr["edit_mode"] > 0)
		{
			$objs = safe_array($arr["objs"]);
			$ef = safe_array($ob->meta("sel_columns_editable"));

			$fld = $d_inst->get_folders($d_o);
			$ol = $d_inst->get_objects($d_o, $fld, $arr["tv_sel"]); 
			foreach($ol as $oid => $o)
			{
				if ($d_inst->check_acl("edit", $d_o, $oid))
				{
					$d_inst->update_object($ef, $oid, $objs[$oid]);
				}
			}
		}

		return $return_url;
	}
}
?>
