<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form.aw,v 2.62 2001/09/05 15:36:04 kristo Exp $
// form.aw - Class for creating forms

// This class should be split in 2, one that handles editing of forms, and another that allows
// filling them and processing the results. It's needed to complete our plan to take over the world.
lc_load("form");
lc_load("automatweb");
global $orb_defs;
$orb_defs["form"] = "xml";

classload("form_base","form_element","form_entry_element","form_search_element","form_cell","images","style","acl");

// see on kasutajate registreerimiseks. et pannaxe kirja et mis formid tyyp on t2itnud.
session_register("session_filled_forms");

define("FORM_ENTRY",1);
define("FORM_SEARCH",2);
define("FORM_RATING",3);

// constants for get_elements_for_row - specify wheter the return array is 
// element_name => element_value
define("ARR_ELNAME", 1);
// or element_id => element_value
define("ARR_ELID",2);

// constants for get_element_by_name
// it returns just the first element with the name
define("RET_FIRST", 1);
// it returns all elements with the name, return type is array
define("RET_ALL", 2);

class form extends form_base
{
	function form()
	{
		$this->tpl_init("forms");
		global $lc_form;
		if (is_array($lc_form))
		{
			$this->vars($lc_form);
		}
		$this->db_init();
		$this->sub_merge = 1;

		$this->typearr = array(FORM_ENTRY => FG_ENTRY_FORM, FORM_SEARCH => FG_SEARCH_FORM, FORM_RATING => FG_RATING_FORM);
		$this->formaliases = "";
		$this->entry_id = 0;

		$this->active_currency = 0;

		lc_load("definition");

		global $lc_automatweb;

		// those should only be loaded, if we are inside an interactive session
		// and not let's say process_entry.
		if (is_array($lc_automatweb))
		{
			$this->vars($lc_automatweb);
		};
	}

	////
	// !Alias parser
	function parse_alias($args = array())
	{
		global $awt;
		$awt->start("form::parse_alias");
		$awt->count("form::parse_alias");

		extract($args);
		if (!is_array($this->formaliases))
		{
			$this->formaliases = $this->get_aliases(array(
								"oid" => $oid,
								"type" => array(CL_FORM,CL_FORM_ENTRY),
							));
		};
		$f = $this->formaliases[$matches[3] - 1];
		$replacement = $this->gen_preview(array("id" => $f["target"], "form_action" => "/reforb.".$GLOBALS["ext"]));
		$awt->stop("form::parse_alias");
		return $replacement;
	}

	////
	// !Generates form admin interface
	// $arr[id] - form id, required
	function gen_grid($arr)
	{
		global $awt;
		$awt->start("form::gen_grid");
		$awt->count("form::gen_grid");

		extract($arr);
		$this->init($id,"grid.tpl",LC_FORM_CHANGE_FORM);

		for ($a=0; $a < $this->arr["cols"]; $a++)
		{
			$fi = "";
			if ($a == 0)
			{
				$this->vars(array("add_col" => $this->mk_orb("add_col", array("id" => $this->id, "after" => -1, "count" => 1))));
				$fi = $this->parse("FIRST_C");
			}

			$fl = true;
			for ($row = 0; $row < $this->arr["rows"]; $row++)
			{
				$els = $this->arr["contents"][$row][$a]->get_elements();
				reset($els);
				while(list(,$v) = each($els))
				{
					if (!$this->can("delete",$v["id"]))
					{
						$fl = false;
					}
				}
			}
			$this->vars(array(
				"form_col" => $a,
				"del_col"		=> $this->mk_orb("del_col",array("id" => $this->id, "col" => $a))
			));
			$cd = "";
			if ($fl == true)
			{
				$cd = $this->parse("DELETE_COL");
			}

			$this->vars(array(
				"FIRST_C" => $fi, 
				"DELETE_COL" => $cd,
				"add_col"	=> $this->mk_orb("add_col", array("id" => $this->id, "count" => 1, "after" => $a))
			));
			$this->parse("DC");
		}

		for ($i=0; $i < $this->arr["rows"]; $i++)
		{
			$cols="";
			$fl = true;
			for ($a=0; $a < $this->arr["cols"]; $a++)
			{
				if (!($arr = $this->get_spans($i, $a)))
				{
					continue;
				}
				
				$els = $this->arr["contents"][$arr["r_row"]][$arr["r_col"]]->get_elements();

				reset($els);
				$el = "";
				$el_cnt=0;
				while (list(, $v) = each($els))
				{
					// the element's can_view property is ignored here
					$this->vars(array(
						"form_cell_text"	=> $v["text"], 
						"form_cell_order"	=> $v["order"],
						"element_id"			=> $v["id"],
						"el_name"					=> ($v["name"] == "" ? "&nbsp;" : $v["name"]),
						"el_type"					=> ($v["type"] == "" ? "&nbsp;" : $v["type"]),
						"chpos" => $this->mk_my_orb("change_el_pos", array("id" => $this->id, "col" => $arr["r_col"], "row" => $arr["r_row"],"el_id" => $v["id"]))
					));
					$el.=$this->parse("ELEMENT");
					$el_cnt++;
				}

				$this->vars(array(
					"ELEMENT" => $el, "cell_col" => $a, "cell_row" => $i, "ELEMENT_NOEDIT" => "","num_els_plus3"=>($el_cnt+5),
					"exp_left"	=> $this->mk_orb("exp_cell_left", array("id" => $this->id, "col" => $a, "row" => $i)),
					"exp_up"		=> $this->mk_orb("exp_cell_up", array("id" => $this->id, "col" => $a, "row" => $i)),
					"exp_down"	=> $this->mk_orb("exp_cell_down", array("id" => $this->id, "col" => $a, "row" => $i)),
					"exp_right"	=> $this->mk_orb("exp_cell_right", array("id" => $this->id, "col" => $a, "row" => $i)),
					"split_ver"	=> $this->mk_orb("split_cell_ver", array("id" => $this->id, "col" => $a, "row" => $i)),
					"split_hor"	=> $this->mk_orb("split_cell_hor", array("id" => $this->id, "col" => $a, "row" => $i)),
					"admin_cell"	=> $this->mk_orb("admin_cell", array("id" => $this->id, "col" => $arr["r_col"], "row" => $arr["r_row"])),
					"add_element" => $this->mk_orb("add_element", array("id" => $this->id, "col" => $arr["r_col"], "row" => $arr["r_row"])),
				));
				$sh = ""; $sv = "";
				if ($arr["rowspan"] > 1)
				{
					$sh = $this->parse("SPLIT_HORIZONTAL");
				}
				if ($arr["colspan"] > 1)
				{
					$sv = $this->parse("SPLIT_VERTICAL");
				}

				$eu = "";
				if ($i != 0)
				{
					$eu = $this->parse("EXP_UP");
				}
				$el = "";
				if ($a != 0)
				{
					$el = $this->parse("EXP_LEFT");
				}
				$er = "";
				if (($a+$arr["colspan"]) != $this->arr["cols"])
				{
					$er = $this->parse("EXP_RIGHT");
				}
				$ed = "";
				if (($i+$arr["rowspan"]) != $this->arr["rows"])
				{
					$ed = $this->parse("EXP_DOWN");
				}

				$this->vars(array(
					"SPLIT_HORIZONTAL" => $sh, 
					"SPLIT_VERTICAL" => $sv, 
					"EXP_UP" => $eu, 
					"EXP_LEFT" => $el, 
					"EXP_RIGHT" => $er,
					"EXP_DOWN" => $ed
				));
				$cols.=$this->parse("COL");
			}
			$fi = "";
			if ($i==0)
			{
				$this->vars(array("add_row" => $this->mk_orb("add_row", array("id" => $this->id, "after" => -1, "count" => 1))));
				$fi = $this->parse("FIRST_R");
			}
			$this->vars(array("del_row" => $this->mk_orb("del_row", array("id" => $this->id, "row" => $i))));
			$cd = $this->parse("DELETE_ROW");

			$this->vars(array(
				"COL" => $cols, 
				"FIRST_R" => $fi, 
				"DELETE_ROW" => $cd,
				"add_row" => $this->mk_orb("add_row", array("id" => $this->id, "after" => $i, "count" => 1))
			));
			$l.=$this->parse("LINE");
		}

		$this->vars(array(
			"LINE"				=> $l,
			"addr_reforb"	=> $this->mk_reforb("add_row", array("id" => $this->id, "after" => $this->arr["rows"]-1)),
			"addc_reforb"	=> $this->mk_reforb("add_col", array("id" => $this->id, "after" => $this->arr["cols"]-1)),
			"reforb"			=> $this->mk_reforb("submit_grid", array("id" => $this->id))
		));
		$awt->stop("form::gen_grid");
		return $this->do_menu_return();
	}

	////
	// !Shows all form elements and lets user pick their style
	function gen_all_elements($arr)
	{
		global $awt;
		$awt->start("form::gen_all_elements");
		$awt->count("form::gen_all_elements");

		extract($arr);
		$this->init($id, "all_elements.tpl", LC_FORM_ALL_ELEMENTS);

		classload("style");
		$style = new style;
		$stylesel = $style->get_select(0,ST_CELL,true);

		for ($c =0; $c < $this->arr["cols"]; $c++)
		{
			$this->vars(array("col1" => $c+1));
			$hh.=$this->parse("HE");
		}
		$this->vars(array("HE" => $hh));

		$this->vars(array("form_id" => $this->id));
		for ($i=0; $i < $this->arr["rows"]; $i++)
		{
			$cols="";
			for ($a=0; $a < $this->arr["cols"]; $a++)
			{
				$this->vars(array("ELEMENT"	=> "", "STYLEITEMS" => "", "SOME_ELEMENTS" => ""));	
				if (!($arr = $this->get_spans($i, $a)))
				{
					continue;
				}
						
				$cell = &$this->arr["contents"][$arr["r_row"]][$arr["r_col"]];
				$els = $cell->get_elements();
				reset($els);
				$el = "";
				while (list(, $v) = each($els))
				{
					// the element's can_view property is ignored here
					$this->vars(array(
						"el_text"	=> ($v["text"] == "" ? "&nbsp;" : $v["text"]),
						"el_name"	=> ($v["name"] == "" ? "&nbsp;" : $v["name"]),
						"el_type"	=> ($v["type"] == "" ? "&nbsp;" : $v["type"]),
						"form_cell_order"	=> $v["order"],
						"element_id"			=> $v["id"]
					));
					$el.=$this->parse("ELEMENT");
				}

				$this->vars(array(
					"ELEMENT"				=> $el, 
					"style_name" => $stylesel[$this->arr["contents"][$arr["r_row"]][$arr["r_col"]]->get_style()],
					"col"						=> $arr["r_col"], 
					"row"						=> $arr["r_row"],
					"row1" => $arr["r_row"]+1
				));	

				$this->vars(array("SOME_ELEMENTS" => $this->parse("SOME_ELEMENTS")));

				$cols.=$this->parse("COL");
			}
			$this->vars(array("COL" => $cols));
			$this->parse("LINE");
		}
		classload("objects");
		$ob = new db_objects;
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_all_els", array("id" => $id)),
			"styles" => $this->picker(0,$stylesel),
			"folders" => $this->picker(0,(is_array($this->arr["el_move_menus"]) ? $this->arr["el_move_menus"] : $ob->get_list(false,true))),
			"types" => $this->picker(0,$this->listall_el_types(true))
		));

		$awt->stop("form::gen_all_elements");
		return $this->do_menu_return();
	}

	////
	// !saves the selected styles from viewing all element layout
	function submit_all_els($arr)
	{
		global $awt;
		$awt->start("form::submit_all_els");
		$awt->count("form::submit_all_els");

		extract($arr);

		$this->load($id);
		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				if ($chk[$row][$col] == 1)
				{
					$this->arr["contents"][$row][$col]->set_style($setstyle,&$this);
					if ($addel)
					{
						// we must add an element of the specified type to this cell
						$this->arr["contents"][$row][$col]->do_add_element(array("parent" => $this->arr["newel_parent"], "name" => "uus_element_".(++$newelcnt), "based_on" => $addel));
					}
				}
			}
		}

		if (is_array($selel))
		{
			foreach($selel as $selid)
			{
				if ($setfolder)
				{
					$this->upd_object(array("oid" => $selid, "parent" => $setfolder));
				}
			}
		}
		$this->save();

		if (is_array($selel) && isset($diliit))
		{
			$this->load($id);
			foreach($selel as $selid)
			{
				$el = $this->get_element_by_id($selid);
				if ($el)
				{
					unset($this->arr["elements"][$el->get_row()][$el->get_col()][$selid]);
					$el->del();
				}
			}
			$this->save();
		}
		$awt->stop("form::submit_all_els");
		return $this->mk_my_orb("all_elements", array("id" => $id));
	}

	////
	// !saves the table properties of the form
	function save_settings($arr)
	{
		global $awt;
		$awt->start("form::save_settings");
		$awt->count("form::save_settings");

		$this->dequote(&$arr);
		extract($arr);
		$this->load($id);

		$this->arr["bgcolor"] = $bgcolor;
		$this->arr["border"] = $border;
		$this->arr["cellpadding"]	= $cellpadding;
		$this->arr["cellspacing"] = $cellspacing;
		$this->arr["height"] = $height;
		$this->arr["width"] = $width;
		$this->arr["height"] = $height;
		$this->arr["hspace"] = $hspace;
		$this->arr["vspace"] = $vspace;
		$this->arr["def_style"] = $def_style;
		$this->arr["submit_text"] = $submit_text;
		$this->arr["after_submit"] = $after_submit;
		$this->arr["after_submit_text"] = $after_submit_text;
		$this->arr["after_submit_link"] = $after_submit_link;
		$this->arr["name_els"] = array();
		if (is_array($entry_name_el))
		{
			foreach($entry_name_el as $elid)
			{
				$this->arr["name_els"][$elid] = $elid;
			}
		}

		$this->arr["try_fill"] = $try_fill;
		$this->arr["show_table"] = $show_table;
		$this->arr["table"] = $table;
		$this->arr["tablestyle"] = $tablestyle;
		$this->arr["after_submit_op"] = $after_submit_op;
		$this->save();
		$awt->stop("form::save_settings");
		return $this->mk_orb("table_settings", array("id" => $id));
	}

	//// 
	// !saves the changes the user has made in the form generated by gen_grid
	function save_grid($arr)
	{
		global $awt;
		$awt->start("form::save_grid");
		$awt->count("form::save_grid");

		extract($arr);
		$this->load($id);

		for ($i=0; $i < $this->arr["rows"]; $i++)
		{
			for ($a=0; $a < $this->arr["cols"]; $a++)
			{
				$this->arr["contents"][$i][$a]->save_short(&$this);
			}
		}

		$this->save();

		// ok here we must do the element separation for all the checked elements
		if (is_array($sel))
		{
			$this->load($id);
			foreach($sel as $elid)
			{
				$inothers = true;
				$this->db_query("SELECT * FROM element2form WHERE el_id = $elid AND form_id != $id");
				while ($row = $this->db_next())
				{
					$inothers = true;
				}
				if ($inothers)
				{
					// since this element is in some other forms as well, we must create a replica and remove the old one from this form
					$el = $this->get_element_by_id($elid);
					
					if ($el)
					{
						$el_parent = $this->db_fetch_field("SELECT parent FROM objects WHERE oid = ".$el->get_id(),"parent");

						$this->arr["contents"][$el->get_row()][$el->get_col()]->do_add_element(array("name" => $el->get_el_name(), "parent" => $this->arr["tear_folder"], "based_on" => $elid,"props" => $el->get_props()));

						unset($this->arr["elements"][$el->get_row()][$el->get_col()][$elid]);
						$el->del();	// delete the element from this form
					}
				}
			}
			$this->save();
		}

		global $HTTP_POST_VARS;
		$cdelete = array();
		$rdelete = array();
		reset($HTTP_POST_VARS);
		while (list($k,$v) = each($HTTP_POST_VARS))
		{
			if (substr($k,0,3) == 'dc_' && $v==1)
				$cdelete[substr($k,3)] = substr($k,3);
			else
			if (substr($k,0,3) == 'dr_' && $v==1)
				$rdelete[substr($k,3)] = substr($k,3);
		}

		// kustutame tagant-ettepoole, niiet numbrid ei muutuks
		krsort($cdelete,SORT_NUMERIC);
		krsort($rdelete,SORT_NUMERIC);

		reset($cdelete);
		while (list($k,$v) = each($cdelete))
		{
			$this->cells_loaded = false;
			$this->delete_column(array("id" => $id, "col" => $v));
		}

		reset($rdelete);
		while (list($k,$v) = each($rdelete))
		{
			$this->cells_loaded = false;
			$this->delete_row(array("id" => $id, "row" => $v));
		}

		$awt->stop("form::save_grid");
		return $this->mk_orb("change",array("id" => $this->id));
	}

	////
	// !Adds $count columns after column $after in form $id
	function add_col($arr)
	{
		global $awt;
		$awt->start("form::add_col");
		$awt->count("form::add_col");

		extract($arr);
		$this->load($id);

		while ($count-- > 0)
		{
			$this->arr["cols"]++;
			$this->map_add_col($this->arr["rows"], $this->arr["cols"], &$this->arr["map"],$after);

			// move necessary elements to the right
			for ($i = $this->arr["cols"]; $i > ($after+1); $i--)
			{
				for ($a = 0; $a < $this->arr["rows"]; $a++)
				{
					$this->arr["elements"][$a][$i] = $this->arr["elements"][$a][$i-1];
				}
			}
			// zero out all elemnts on the newly added column
			for ($a = 0; $a < $this->arr["rows"]; $a++)
			{
				$this->arr["elements"][$a][$after+1] = array();
			}
		}
		$this->save();
		$orb = $this->mk_orb("change", array("id" => $this->id));
		// since this function can be called both from reforb and orb
		// we make sure we return to the right place afterwards.
		$awt->stop("form::add_col");
		header("Location: $orb");
		return $orb;
	}

	////
	// !Adds rows to the form
	// parameters:
	// id - form id
	// after - row number after which rows are added
	// count - number of rows to add
	function add_row($arr)
	{
		global $awt;
		$awt->start("form::add_row");
		$awt->count("form::add_row");

		extract($arr);
		$this->load($id);

		while ($count-- > 0)
		{
			$this->arr["rows"]++;
			$this->map_add_row($this->arr["rows"], $this->arr["cols"], &$this->arr["map"], $after);

			// now we must also move all elements in $this->arr[elements]
			// so that when the form is loaded they get put in the correct
			// places.
			for ($i=$this->arr["rows"]; $i > $after; $i--)
			{
				for ($a = 0; $a < $this->arr["cols"]; $a++)
				{
					$this->arr["elements"][$i][$a] = $this->arr["elements"][$i-1][$a];
				}
			}
			// zero out all elements on the newly inserted row
			for ($a = 0; $a < $this->arr["cols"]; $a++)
			{
				$this->arr["elements"][$after+1][$a] = array();
			}
		}
		$this->save();
		$orb = $this->mk_orb("change", array("id" => $this->id));
		$awt->stop("form::add_row");
		header("Location: $orb");
		return $orb;
	}
	
	////
	// !Deletes column $col of form $id
	function delete_column($arr)
	{
		global $awt;
		$awt->start("form::delete_column");
		$awt->count("form::delete_column");

		extract($arr);
		$this->load($id);

		for ($i=0; $i < $this->arr["rows"]; $i++)
		{
			// we don't delete the element from the database, we jsut delete it
			// from this form. 
			$this->arr["elements"][$i][$col] = array();
			$this->arr["contents"][$i][$col]->del();
			$this->arr["contents"][$i][$this->arr["cols"]-1] = array();
		}

		$this->map_del_col($this->arr["rows"], $this->arr["cols"], &$this->arr["map"],$col);

		// we must also shift all elements that are to the right of the deleted
		// column 1 position to the left
		for ($i=$col; $i < $this->arr["cols"]; $i++)
		{
			for ($a=0; $a < $this->arr["rows"]; $a++)
			{
				$this->arr["elements"][$a][$i] = $this->arr["elements"][$a][$i+1];
			}
		}

		$this->arr["cols"]--;
		$this->save();
		$orb = $this->mk_orb("change" , array("id" => $this->id));
		$awt->stop("form::delete_column");
		header("Location: $orb");
		return $orb;
	}
	
	////
	// !Deletes row $row from form $id
	function delete_row($arr)
	{
		global $awt;
		$awt->start("form::delete_row");
		$awt->count("form::delete_row");

		extract($arr);
		$this->load($id);

		for ($i=0; $i < $this->arr["cols"]; $i++)
		{
			$this->arr["elements"][$row][$i] = array();
			$this->arr["contents"][$row][$i]->del();
			$this->arr["contents"][$this->arr["rows"]-1][$i] = "";
		}

		$this->map_del_row($this->arr["rows"], $this->arr["cols"], &$this->arr["map"], $row);

		// we must move all elements below the deleted row up by one
		for ($i = $row; $i < $this->arr["rows"]; $i++)
		{
			for ($a=0; $a < $this->arr["cols"]; $a++)
			{
				$this->arr["elements"][$i][$a] = $this->arr["elements"][$i+1][$a];
			}
		}

		$this->arr["rows"]--;
		
		$this->save();
		$orb = $this->mk_orb("change", array("id" => $this->id));
		$awt->stop("form::delete_column");
		header("Location: $orb");
		return $orb;
	}

	////
	// !returns array id => name of all elements in the loaded form
	// what if I want to know the types of the elements as well?
	// if type argument is set, then the values of the returned array are 
	// also arrays, consiting of two elements,
	// 1) type of the element
	// 2) the actual name
	function get_all_elements($args = array())
	{
		global $awt;
		$awt->start("form::get_all_elements");
		$awt->count("form::get_all_elements");

		$ret = array();
		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col = 0; $col < $this->arr["cols"]; $col++)
			{
				$elar = $this->arr["contents"][$row][$col]->get_elements();
				reset($elar);
				while (list(,$el) = each($elar))
				{
					if ($args["type"])
					{
						$block = array(
							"name" => $el["name"],
							"type" => $el["type"],
						);
						$ret[$el["id"]] = $block;
					}
					else
					{
						$ret[$el["id"]] = $el["name"];
					};
				}
			}
		}
		$awt->stop("form::get_all_elements");
		return $ret;
	}

	////
	// !Generates the form used in modifying the table settings
	function gen_settings($arr)
	{
		global $awt;
		$awt->start("form::gen_settings");
		$awt->count("form::gen_settings");

		extract($arr);
		$this->init($id,"settings.tpl", LC_FORM_CHANGE_SETTINGS);

		classload("style");
		$t = new style;
		$o = new db_objects;
		$menulist = $o->get_list();
		$ops = $this->get_op_list($id);
		$this->vars(array(
			"form_bgcolor"				=> $this->arr["bgcolor"],
			"form_border"					=> $this->arr["border"],
			"form_cellpadding"		=> $this->arr["cellpadding"],
			"form_cellspacing"		=> $this->arr["cellspacing"],
			"form_height"					=> $this->arr["height"],
			"form_width"					=> $this->arr["width"],
			"form_hspace"					=> $this->arr["hspace"],
			"form_vspace"					=> $this->arr["vspace"],
			"def_style"						=> $this->picker($this->arr["def_style"],$t->get_select(0,ST_CELL)),
			"submit_text"					=> $this->arr["submit_text"],
			"after_submit_text"		=> $this->arr["after_submit_text"],
			"after_submit_link"		=> $this->arr["after_submit_link"],
			"as_1"								=> ($this->arr["after_submit"] == 1 ? "CHECKED" : ""),
			"as_2"								=> ($this->arr["after_submit"] == 2 ? "CHECKED" : ""),
			"as_3"								=> ($this->arr["after_submit"] == 3 ? "CHECKED" : ""),
			"as_4"								=> ($this->arr["after_submit"] == 4 ? "CHECKED" : ""),
			"ops" => $this->picker($this->arr["after_submit_op"], $ops[$id]),
			"els"									=> $this->multiple_option_list(is_array($this->arr["name_els"]) ? $this->arr["name_els"] : $this->arr["name_el"] ,$this->get_all_elements()),
			"try_fill"						=> checked($this->arr["try_fill"]),
			"show_table_checked" => checked($this->arr["show_table"]),
			"tables" => $this->picker($this->arr["table"],$this->get_list_tables()),
			"tablestyles" => $this->picker($this->arr["tablestyle"], $t->get_select(0,ST_TABLE))
		));
		$ns = "";
		if ($this->type != 2)
		{
			$ns = $this->parse("NOSEARCH");
		}
		else
		{
			$ns = $this->parse("SEARCH");
		}

		$this->vars(array(
			"NOSEARCH" => $ns,
			"SEARCH" => "",
			"reforb"	=> $this->mk_reforb("save_settings", array("id" => $this->id))
		));
		$awt->stop("form::gen_settings");
		return $this->do_menu_return();				
	}

	////
	// !shows form $id
	// optional parameters: 
	//	$entry_id - the entry to show
	//	$reforb - replaces {VAR:reforb}
	//  $form_action = <form action='$form_action'
	//  $extraids - array of parameters to pass along with the form
	//  $elvalues - array of name => value pairs for elements that specify default values
	//  $prefix - value to prefix the element names with
	//  $silent_errors - if true, error messages are only written to syslog, not shown to user
	function gen_preview($arr)
	{
		global $awt;
		$awt->start("form::gen_preview");
		$awt->count("form::gen_preview");

		extract($arr);
	
		// kui id-d pole antud, siis kasutame seda vormi, mis juba eelnevalt
		// laetud on. Somewhere.
		if (isset($id))
		{
			$this->load($id);
		};
		global $baseurl;

		if ($form_action == "")
		{
			if (stristr($GLOBALS["REQUEST_URI"],"/automatweb")!=false)
			{
				$form_action = "/automatweb/reforb.".$GLOBALS["ext"];
			}
			else
			{
				$form_action = "/reforb.".$GLOBALS["ext"];
			}
		}
		//$form_action = $baseurl.$form_action;

		global $section;
		if (!isset($reforb) || $reforb == "")
		{
			$reforb = $this->mk_reforb("process_entry", array("id" => $this->id,"section" => $section));
		}
		if (isset($entry_id) && $entry_id)
		{
			$this->load_entry($entry_id,$silent_errors);
		}
		else
		{
			if ($this->arr["try_fill"])
			{
				if (!isset($elvalues))
				{
					$elvalues = array();
				}
				classload("users");
				$u = new users;
				$elvalues=$elvalues + $u->get_user_info($GLOBALS["uid"]);
			}
		}
			
		$tpl = isset($tpl) ? $tpl : "show.tpl";
		$this->read_template($tpl,1);
		$this->vars(array("form_id" => $id));
		$images = new db_images;

		$c="";
		$chk_js = "";
		for ($i=0; $i < $this->arr["rows"]; $i++)
		{
			$html=$this->mk_row_html($i,$prefix,$elvalues,$no_submit);
			$this->vars(array("COL" => $html));
			$c.=$this->parse("LINE");

			if ($this->type == FTYPE_ENTRY)
			{
				// generate all entry checking html
				for ($a = 0; $a < $this->arr["cols"]; $a++)
				{
					$chk_js .= $this->arr["contents"][$i][$a]->gen_check_html();
				}
			}
		}

		$pic = "";
		if ($this->entry_id)
		{
			$images->list_by_object($this->entry_id);
			while ($row = $images->db_next())
			{
				$this->vars(array("img_idx" => $row["idx"],"img_id" => $row["oid"]));
				$pic.=$this->parse("IMAGE");
			}
		}
		global $REQUEST_URI;
		$ip ="";
		if ($this->entry_id != 0)	// images can only be addes after the place is entered for the first time
		{
			$this->vars(array("IMAGE" => $pic, "url"	=> $REQUEST_URI));
			$ip = $this->parse("IMG_WRAP");
		}

		$this->vars(array("var_name" => "entry_id", "var_value" => $this->entry_id));
		$ei = $this->parse("EXTRAIDS");
		if (isset($extraids) && is_array($extraids))
		{
			reset($extraids);
			while(list($k,$v) = each($extraids))
			{
				$this->vars(array("var_name" => $k, "var_value" => $v));
				$ei.=$this->parse("EXTRAIDS");
			}
		}

		$this->add_hit($this->id);

		$this->vars(array(
			"LINE"							=> $c,
			"EXTRAIDS"					=> $ei,
			"IMG_WRAP"					=> $ip, 
			"form_action"				=> $form_action,
			// lauri muudetud-> formtag_name on formi tagi nimi kuhu see form parsitakse
			"formtag_name"				=> $formtag_name,
			"submit_text"				=> isset($this->arr["submit_text"]) ? $this->arr["submit_text"] : "",
			"reforb"						=> $reforb,
			"checks"						=> $chk_js
		));
		if (isset($this->arr["tablestyle"]) &&  $this->arr["tablestyle"] != 0)
		{
			classload("style");
			$st = new style;
			$s = $st->get($this->arr["tablestyle"]);
			$s = unserialize($s["style"]);
			$this->vars(array(
				"form_border"				=> (isset($s["border"]) && $s["border"] != "" ? " BORDER='".$s["border"]."'" : ""),
				"form_bgcolor"			=> (isset($s["bgcolor"]) && $s["bgcolor"] !="" ? " BGCOLOR='".$s["bgcolor"]."'" : ""),
				"form_cellpadding"	=> (isset($s["cellpadding"]) && $s["cellpadding"] != "" ? " CELLPADDING='".$s["cellpadding"]."'" : ""),
				"form_cellspacing"	=> (isset($s["cellspacing"]) && $s["cellspacing"] != "" ? " CELLSPACING='".$s["cellspacing"]."'" : ""),
				"form_height"				=> (isset($s["height"]) && $s["height"] != "" ? " HEIGHT='".$s["height"]."'" : ""),
				"form_width"				=> (isset($s["width"]) && $s["width"] != "" ? " WIDTH='".$s["width"]."'" : "" ),
				"form_height"				=> (isset($s["height"]) && $s["height"] != "" ? " HEIGHT='".$s["height"]."'" : "" ),
				"form_vspace"				=> (isset($s["vspace"]) && $s["vspace"] != "" ? " VSPACE='".$s["vspace"]."'" : ""),
				"form_hspace"				=> (isset($s["hspace"]) && $s["hspace"] != "" ? " HSPACE='".$s["hspace"]."'" : ""),
			));
		}
		else
		{
			$this->vars(array(
				"form_border"				=> (isset($this->arr["border"]) && $this->arr["border"] != "" ? " BORDER='".$this->arr["border"]."'" : ""),
				"form_bgcolor"			=> (isset($this->arr["bgcolor"]) && $this->arr["bgcolor"] !="" ? " BGCOLOR='".$this->arr["bgcolor"]."'" : ""),
				"form_cellpadding"	=> (isset($this->arr["cellpadding"]) && $this->arr["cellpadding"] != "" ? " CELLPADDING='".$this->arr["cellpadding"]."'" : ""),
				"form_cellspacing"	=> (isset($this->arr["cellspacing"]) && $this->arr["cellspacing"] != "" ? " CELLSPACING='".$this->arr["cellspacing"]."'" : ""),
				"form_height"				=> (isset($this->arr["height"]) && $this->arr["height"] != "" ? " HEIGHT='".$this->arr["height"]."'" : ""),
				"form_width"				=> (isset($this->arr["width"]) && $this->arr["width"] != "" ? " WIDTH='".$this->arr["width"]."'" : "" ),
				"form_height"				=> (isset($this->arr["height"]) && $this->arr["height"] != "" ? " HEIGHT='".$this->arr["height"]."'" : "" ),
				"form_vspace"				=> (isset($this->arr["vspace"]) && $this->arr["vspace"] != "" ? " VSPACE='".$this->arr["vspace"]."'" : ""),
				"form_hspace"				=> (isset($this->arr["hspace"]) && $this->arr["hspace"] != "" ? " HSPACE='".$this->arr["hspace"]."'" : ""),
			));
		}
		$st = $this->parse();				
		$awt->stop("form::gen_preview");
		return $st;
	}

	////
	// !generates one row of form elements
	function mk_row_html($row,$prefix = "",$elvalues = array(),$no_submit = false)
	{
		global $awt;
		$awt->start("form::mk_row_html");
		$awt->count("form::mk_row_html");

		$html = "";
		for ($a=0; $a < $this->arr["cols"]; $a++)
		{
			if (($arr = $this->get_spans($row, $a)))
			{
				$ds = isset($this->arr["def_style"]) ? $this->arr["def_style"] : 0;
				$html.=$this->arr["contents"][$arr["r_row"]][$arr["r_col"]]->gen_user_html_not($ds,$arr["colspan"], $arr["rowspan"],$prefix,$elvalues,$no_submit);
			}
		}
		$awt->stop("form::mk_row_html");
		return $html;
	}


	////
	// !saves the entry for the form $id, if $entry_id specified, updates it instead of creating a new one
	// elements are assumed to be prefixed by $prefix
	// optional argument $chain_entry_id - if creating a new entry and it is specified, the entry is created with that chain entry id
	// parent (id) - mille alla entry salvestada

	function process_entry($arr)
	{
		global $awt;
		$awt->start("form::process_entry");
		$awt->count("form::process_entry");

		extract($arr);

		// values can be bassed from the caller inside the $values argument, or..
		if (is_array($values))
		{
			$this->post_vars = $values;
		}
		else
		{
			// .. if that is not the case, then we just import all the POST variables.
			global $HTTP_POST_VARS;
			$this->post_vars = $HTTP_POST_VARS;
		};

		$this->load($id);

		if ($entry_id)	// tshekime et kas see entry on ikka loaditud formi jaox ja kui pole, siis ignoorime seda
		{
			$fid = $this->db_fetch_field("SELECT form_id FROM form_entries WHERE id = $entry_id","form_id");
			if ($fid != $id)
			{
				$entry_id = false;
			}
		}

		if (!$entry_id)
		{
			// ff_folder on vormi konfist määratud folderi id, mille alla entry peaks
			// minema. parent argument overraidib selle
			$parent = isset($parent) ? $parent: $this->arr["ff_folder"];
			
			// what the hell is that single "form_entry" doing there in the middle?
			// we override the lang_id here, because entries that have been entered over
			// XML-RPC do not know what their language_id might be, so specify one.
			$params = array(
				"parent" => $parent,
				"0"	=> "form_entry",
				"class_id" => CL_FORM_ENTRY,
				"lang_id" => $lang_id,
			);

			$entry_id = $this->new_object($params);

			$this->entry_id = $entry_id;
			$new = true;
		}
		else
		{
			$new = false;
		}

		if (!isset($prefix))
		{
			$prefix = "";
		}

		for ($i=0; $i < $this->arr["rows"]; $i++)
		{
			for ($a=0; $a < $this->arr["cols"]; $a++)
			{
				// form_cell->process_entry
				$this->arr["contents"][$i][$a] -> process_entry(&$this->entry, $entry_id,$prefix);
			}
		}

		// what exactly does this code do?
		//
		// well, you can select a bunch of elements and then the data entered in those elements will be used to neme the form_entry object.
		// and this is where it's done - terryf
		if (is_array($this->arr["name_els"]))
		{
			foreach($this->arr["name_els"] as $elid)
			{
				$el = $this->get_element_by_id($elid);
				if ($el)
				{
					if ($el->get_type() == "")
					{
						$this->entry_name.= " ".$el->get_text();
					}
					else
					{
						$this->entry_name.= " ".$el->get_value();
					}
				}
			}
			$this->upd_object(array("oid" => $entry_id, "name" => $this->entry_name,"comment" => ""));
		}
		else
		if ($this->arr["name_el"])
		{
			$el = $this->get_element_by_id($this->arr["name_el"]);
			if ($el)
			{
				if ($el->get_type() == "")
				{
					$this->entry_name = $el->get_text();
				}
				else
				{
					$this->entry_name = $el->get_value();
				}
			}
			$this->upd_object(array("oid" => $entry_id, "name" => $this->entry_name,"comment" => ""));
		}
		
		$en = serialize($this->entry);
		
		if ($new)
		{
			// lisame uue entry
			$this->db_query("INSERT INTO form_entries VALUES($entry_id, $this->id, ".time().", '$en')");

			// create sql 
			reset($this->entry);
			$ids = "id"; $vals = "$entry_id";
			if ($chain_entry_id)
			{
				$ids.=",chain_id";
				$vals.=",".$chain_entry_id;
			}

			$first = true;

			while (list($k, $v) = each($this->entry))
			{
				$el = $this->get_element_by_id($k);

				// häkk
				if (is_object($el))
				{
					$ev = $el->get_value();

					$ids.=",el_$k,ev_$k";
					// see on pildi uploadimise elementide jaoks
					if (is_array($v))
					{
						$v = serialize($v);
					}
					$vals.=",'$v','$ev'";
				};
			}

			$sql = "INSERT INTO form_".$this->id."_entries($ids) VALUES($vals)";
			$this->db_query($sql);

			// see logimine on omal kohal ainult siis, kui täitmine toimub
			// läbi veebi.
			$this->_log("form","T&auml;itis formi $this->name");
			$this->do_actions($entry_id);
		}
		else
		{
			// muudame olemasolevat entryt.
			$this->db_query("UPDATE form_entries SET form_entries.tm = ".time().", contents = '$en' WHERE id = $entry_id");
			// create sql 
			reset($this->entry);
			$ids = "id = $entry_id";
			$first = true;

			while (list($k, $v) = each($this->entry))
			{
				$el = $this->get_element_by_id($k);
				if ($el)
				{
					$ev = $el->get_value();
					if (is_array($v))
					{
						$v = serialize($v);
					}
					$ids.=",el_$k = '$v',ev_$k = '$ev'";
				}
			}

			$sql = "UPDATE form_".$this->id."_entries SET $ids WHERE id = $entry_id";
			$this->db_query($sql);
			$this->_log("form","Muutis formi $this->name sisestust $entry_id");
		}
		// paneme kirja, et kasutaja t2itis selle formi et siis kasutajax regimisel saame seda kontrollida.
		$this->entry_id = $entry_id;
		$GLOBALS["session_filled_forms"][$this->id] = $entry_id;

		global $ext;

		if (isset($redirect_after) && $redirect_after)
		{
			// if this variable has been set in extraids when showing the form, redirect to it
			$awt->stop("form::process_entry");
			return $redirect_after;
		}

		switch ($this->get_location())
		{
			case "text":
				$l = "forms.$ext?type=ae_text&id=$id&entry_id=$entry_id";
				break;
			case "redirect":
				$l = $this->get_ae_location();
				break;
			case "search_results":
				$l = $this->mk_my_orb("show_entry", array("id" => $id, "entry_id" => $entry_id, "op_id" => 1,"section" => $section));
				break;
			case "show_op":
				$l = $this->mk_my_orb("show_entry", array("id" => $id, "entry_id" => $entry_id, "op_id" => $this->arr["after_submit_op"],"section" => $section));
				break;
			default:
				if ($this->type == FTYPE_SEARCH)
				{
					// n2itame ocingu tulemusi
					$l = $this->mk_my_orb("show_entry", array("id" => $id, "entry_id" => $entry_id,"op_id" => 1,"section" => $section));
				}
				else
				{
					$l = $this->mk_my_orb("show", array("id" => $id, "entry_id" => $entry_id));
				}
				break;
		}
		$awt->stop("form::process_entry");
		return $l;
	}

	////
	// !delketes $entry_id of form $id and redirects to hexbin($after)
	function delete_entry($arr)
	{
		extract($arr);
		$this->delete_object($entry_id);
		$this->_log("form","Kustutas formi $this->name sisestuse $entry_id");
		$after = $this->hexbin($after);
		header("Location: ".$after);
		die();
	}

	function req_load_relations($id,&$row)
	{
		$this->temp_ids[$id] = $id;
		$this->save_handle();
		$this->db_query("SELECT * FROM form_relations WHERE form_to = $id OR form_from = $id");
		while ($r_row = $this->db_next())
		{
			// nii. nyt on siis see form, mille entryt loaditakse on lingitud miski teise formi kylge. 
			// j2relikult tuleb siin loadida ka see entry, mis on lingitud
			// niisis, tuleb teha p2ring teise formi tabelisse, milles where klauslis on see v22rtus, mis k2esolevast formist valiti
			$this->save_handle();
			$this->db_query("SELECT * FROM form_".$r_row["form_from"]."_entries WHERE ev_".$r_row["el_from"]." = '".$row["ev_".$r_row["el_to"]]."'");
			$er_row = $this->db_next();
			if (is_array($er_row))
			{
				$er_row["form_".$r_row["form_from"]."_entry"] = $er_row["id"];
				$row= $row+$er_row;
			}
			if (!in_array($r_row["form_from"],$this->temp_ids))
			{
				$this->req_load_relations($r_row["form_from"],&$row);
			}
			$this->db_query("SELECT * FROM form_".$r_row["form_to"]."_entries WHERE ev_".$r_row["el_to"]." = '".$row["ev_".$r_row["el_from"]]."'");
			$er_row = $this->db_next();
			if (is_array($er_row))
			{
				$er_row["form_".$r_row["form_to"]."_entry"] = $er_row["id"];
				$row= $row+$er_row;
			}
			if (!in_array($r_row["form_to"],$this->temp_ids))
			{
				$this->req_load_relations($r_row["form_to"],&$row);
			}
			$this->restore_handle();
		}
		$this->restore_handle();
	}

	// laeb entry parajasti aktiivse vormi jaoks
	function load_entry($entry_id,$silent_errors = false)
	{
		global $awt;
		$awt->start("form::load_entry");
		$awt->count("form::load_entry");

		$this->entry_id = $entry_id;

		$id = $this->id;

		$q = "SELECT * FROM form_".$id."_entries WHERE id = $entry_id";
		$this->db_query($q);

		if (!($row = $this->db_next()))
		{
			if ($silent_errors)
			{
				$this->raise_error(sprintf("No such entry %d for form %d",$entry_id,$id),false,true);
				$this->entry_id = 0;
			}
			else
			{
				$this->raise_error(sprintf("No such entry %d for form %d",$entry_id,$id),true);
			}
		};

		$row["form_".$id."_entry"] = $row["id"];

		// siin tuleb nyyd relatsioonitud formid ka sisse lugeda ja seda rekursiivselt. ugh. 
		$this->temp_ids = array();
		$this->req_load_relations($id,&$row);

		if ($row["chain_id"])
		{
			// kuna see entry on osa chaini entryst, siis tuleb laadida ka teised chaini entryd
			$char = $this->get_chain_entry($row["chain_id"]);
			foreach($char as $cfid => $ceid)
			{
				if ($ceid != $entry_id)
				{
					$this->db_query("SELECT * FROM form_".$cfid."_entries WHERE id = $ceid");
					$crow = $this->db_next();
					if (is_array($crow))
					{
						$crow["form_".$cfid."_entry"] = $crow["id"];
						$row= $row+$crow;
					}
				}
			}
			$this->chain_entry_id = $row["chain_id"];
		}
		else
		{
			$this->chain_entry_id = 0;
		}

		$this->load_entry_from_data($row,$entry_id,$eids);
		$awt->stop("form::load_entry");
	}

	function load_entry_from_data($row,$entry_id,$eids = array())
	{
		global $awt;
		$awt->start("form::load_entry_from_data");
		$awt->count("form::load_entry_from_data");

		$this->entry_id = $entry_id;
		reset($row);
		$this->values = array();
		while (list($k,$v) = each($row))
		{
			// pildi elementide inff on arrays serializetult
			// selle unserializeme hiljem, elemendi juures alles

			if (substr($k,0,3) == "el_")
			{
				//print "k = $k, v = $v<br>";
				$key = substr($k,3);
				$this->entry[substr($k,3)] = $v;
			}
			else
			if (substr($k,0,5) == "form_")
			{
				$this->entry[$k] = $v;
			}
		};

		$this->vars(array("entry_id" => $entry_id));

		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$this->arr["contents"][$row][$col] -> set_entry(&$this->entry, $entry_id,&$this);
			};
		};
		$awt->stop("form::load_entry_from_data");
	}

	////
	// !shows entry $entry_id of form $id using output $op_id
	// if $no_load_entry == true, the loaded entry is used
	function show($arr)
	{
		global $awt;
		$awt->start("form::show");
		$awt->count("form::show");

		extract($arr);
		
		// if reset argument is set, zero out all data that has been gathered inside templates
		if (isset($reset))
		{
			$this->tpl_reset();
		};
		
		if (!$no_load_entry)
		{
			$this->load($id);
		}

		// if this is a search form, then search, instead of showing the entered data
		if ($this->type == FORM_SEARCH)
		{
			$r = $this->do_search($entry_id, $op_id);
			$awt->stop("form::show");
			return $r;
		}

		$this->load_output($op_id);

		if (!$no_load_entry)
		{
			$this->load_entry($entry_id);
			if (is_array($misc))
			{
				foreach($misc as $key => $val)
				{
					$this->set_element_value($key, $val);
				};
			}
		}
		else
		{
			$entry_id = $this->entry_id;
		}

		if (isset($no_html) && $no_html)
		{
			$this->read_template("show_user_nohtml.tpl");
		}
		else
		{
			$this->read_template("show_user.tpl");
		}
	
		// I think that commenting out those 2 will give us a tiny win in the speed.
		//$this->add_hit($entry_id);
		//$this->add_hit($op_id);

		$t_style = new style();
		// kui on tabeli stiil m22ratud v2ljundile, siis kasutame seda, kui pole, siis vaatame kas sellele formile on 
		// m22ratud default stiil ja kui on, siis kasutame seda
		$fcol_style = 0;
		$fcol_cnt = 0;
		$frow_style = 0;
		$frow_cnt = 0;
		if ($this->output["table_style"])
		{
			$fcol_style = $t_style->get_fcol_style($this->output["table_style"]);
			$fcol_cnt = $t_style->get_num_fcols($this->output["table_style"]);
			$frow_style = $t_style->get_frow_style($this->output["table_style"]);
			$frow_cnt = $t_style->get_num_frows($this->output["table_style"]);
		}

		// kui tabeli stiilis ei m22ratud default stiili, siis v6etakse see formist. I guess. 
		if ($this->arr["def_style"] && $fcol_cnt < 1 && $frow_cnt < 1)
		{
			$fcol_style = $this->arr["def_style"];
			$fcol_cnt = $this->output["cols"];
			$frow_style = $this->arr["def_style"];
			$frow_cnt = $this->output["rows"];
		}
	
		$op_far = $this->get_op_forms($op_id);

		$awt->start("form::show::cycle");
		// tsykkel yle koigi outputi ridade ja cellide
		for ($row = 0; $row < $this->output["rows"]; $row++)
		{
			$html="";
			for ($col = 0; $col < $this->output["cols"]; $col++)
			{
				if (!($arr = $this->get_spans($row, $col, $this->output["map"], $this->output["rows"], $this->output["cols"])))
					continue;

				$rrow = $arr["r_row"];
				$rcol = $arr["r_col"];
				$op_cell = $this->output[$rrow][$rcol];
				$style_id = $op_cell["style"];
				if ($style_id == 0)
				{
					// now. find the defult style based on the row / col default styles. 
					// start with cols
					if ($col < $fcol_cnt && $fcol_style)
					{
						$style_id = $fcol_style;
					}
					else
					if ($row < $frow_cnt && $frow_style)
					{
						$style_id = $frow_style;
					}
				}


				$chtml= "";
				for ($i=0; $i < $op_cell["el_count"]; $i++)
				{
					// load the element from output
					$awt->start("form::show::cycle::new_element");
					$awt->count("form::show::cycle::new_element");
					$el=new form_entry_element;
					$el->load($op_cell["elements"][$i],&$this,$rcol,$rrow);
					// if the element is linked, then fake the elements entry
					if ($op_cell["elements"][$i]["linked_element"] && $op_far[$op_cell["elements"][$i]["linked_form"]] == $op_cell["elements"][$i]["linked_form"])
					{
						// now fake the correct id
						$this->entry[$el->get_id()] = $this->entry[$op_cell["elements"][$i]["linked_element"]];
						$el->set_entry($this->entry,$this->entry_id, &$this);
					}
					$awt->stop("form::show::cycle::new_element");

					$awt->start("form::show::cycle::show");
					if ($el)
					{
						$chtml.= $el->gen_show_html();
					}
					$awt->stop("form::show::cycle::show");
				}

				$awt->start("form::show::cycle::style");
				if (isset($no_html) && $no_html)
				{
					$html.=$chtml." ";
				}
				else
				{
					if ($style_id != 0)
					{
						$html.= $t_style->get_cell_begin_str($style_id,$arr["colspan"],$arr["rowspan"]).$chtml.$t_style->get_cell_end_str($style_id)."</td>";
					}
					else
					{
						$html.= "<td colspan=\"".$arr["colspan"]."\" rowspan=\"".$arr["rowspan"]."\">".$chtml."</td>";
					}
				}
				$awt->stop("form::show::cycle::style");
			}
			$this->vars(array("COL" => $html));
			$this->parse("LINE");
		}
		$awt->stop("form::show::cycle");

		// uurime v2lja outputi tabeli stiili ja kasutame seda
		//$this->output["table_style"] = 12220;
		//print $t_style->get_table_string($this->output["table_style"]);
		if ($this->output["table_style"])
		{
			$this->vars(array(
				"tablestring" => $t_style->get_table_string($this->output["table_style"])
			));
		}
		$awt->stop("form::show");
		$retval = $this->parse();
		return $retval;
		
	}

	////
	// !Merge the cell above cell($row,$col) in form $id
	function exp_cell_up($arr)
	{
		extract($arr);
		$this->load($id);
		$this->map_exp_up($this->arr["rows"], $this->arr["cols"], &$this->arr["map"],$row,$col);
		$this->save();
		$orb = $this->mk_orb("change", array("id" => $this->id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !Merges the cell ($row,$col) in form $id with the cell immediately below it
	function exp_cell_down($arr)
	{
		extract($arr);
		$this->init($id);
		$this->map_exp_down($this->arr["rows"], $this->arr["cols"], &$this->arr["map"],$row,$col);
		$this->save();
		$orb = $this->mk_orb("change", array("id" => $this->id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !Expand cell at $row,$col with the cell to it's left, in form $id
	function exp_cell_left($arr)
	{
		extract($arr);
		$this->load($id);
		$this->map_exp_left($this->arr["rows"], $this->arr["cols"], &$this->arr["map"],$row,$col);
		$this->save();
		$orb = $this->mk_orb("change", array("id" => $this->id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !Merges the cell ($row, $col) in form $id with the cell right to it
	function exp_cell_right($arr)
	{
		extract($arr);
		$this->load($id);
		$this->map_exp_right($this->arr["rows"], $this->arr["cols"], &$this->arr["map"],$row,$col);
		$this->save();
		$orb = $this->mk_orb("change", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	function get_location()
	{
		if ($this->type == 2)
		{
			return "search_results";
		}

		switch($this->arr["after_submit"])
		{
			case 1:
				return "edit";
			case 2:
				return "text";
			case 3:
				return "redirect";
			case 4:
				return "show_op";
		}
	}

	function get_ae_location()
	{
		return $this->arr["after_submit_link"];
	}

	function ae_text()
	{
		$this->read_template("ae_text.tpl");
		$this->vars(array("ae_text" => $this->arr["after_submit_text"]));
		return $this->parse();
	}

	////
	// !Splits the cell ($row, $col) in form $id vertically
	function split_cell_ver($arr)
	{
		extract($arr);
		$this->load($id);
		$this->map_split_ver($this->arr["rows"], $this->arr["cols"], &$this->arr["map"],$row,$col);
		$this->save();
		$orb = $this->mk_orb("change", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !splits the cell at ($row, $col) in form $id vertically
	function split_cell_hor($arr)
	{
		extract($arr);
		$this->load($id);
		$this->map_split_hor($this->arr["rows"], $this->arr["cols"], &$this->arr["map"],$row,$col);
		$this->save();
		$orb = $this->mk_orb("change", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !generates the form for selecting among which forms to search for search form $id
	function gen_search_sel($arr)
	{
		global $awt;
		$awt->start("form::gen_search_sel");
		$awt->count("form::gen_search_sel");

		extract($arr);
		$this->init($id, "search_sel.tpl", "Vali otsitavad formid");

		$this->vars(array("LINE" => "")); $cnt=0;

		$ops = $this->get_op_list();

		$per_page = 10;

		$total = $this->db_fetch_field("SELECT count(oid) as cnt FROM objects LEFT JOIN forms ON forms.id = objects.oid WHERE status != 0 AND class_id = ".CL_FORM." AND forms.type = ".FTYPE_ENTRY,"cnt");
		$pages = $total / $per_page;
		for ($i=0; $i < $pages; $i++)
		{
			$this->vars(array(
				"from" => ($i*$per_page),
				"to" => min(($i+1)*$per_page, $total),
				"pageurl" => $this->mk_my_orb("sel_search", array("id" => $id, "page" => $i))
			));
			if ($i == $page)
			{
				$pp.=$this->parse("SEL_PAGE");
			}
			else
			{
				$pp.=$this->parse("PAGE");
			}
		}
		$this->vars(array(
			"PAGE" => $pp,
			"SEL_PAGE" => ""
		));

		$this->db_query("SELECT oid,parent,name,comment FROM objects LEFT JOIN forms ON forms.id = objects.oid WHERE status != 0 AND class_id = ".CL_FORM." AND forms.type = ".FTYPE_ENTRY." LIMIT ".($page*$per_page).",$per_page");
		while($row = $this->db_next())
		{
			$tar = array(0 => "");
			if (is_array($ops[$row["oid"]]))
			{
				foreach($ops[$row["oid"]] as $opid => $opname)
				{
					$tar[$opid] = $opname;
				}
			}
			$sel = $this->arr["search_from"][$row["oid"]] == 1 ? $this->arr["search_outputs"][$row["oid"]] : 0;
			$this->vars(array(
				"form_name"	=> $row["name"], 
				"form_comment" => $row["comment"], 
				"form_location" => $row["parent"], 
				"form_id" => $row["oid"],
				"row"	=> $cnt,
				"checked" => checked($this->arr["search_from"][$row["oid"]] == 1),
				"prev" => $this->arr["search_from"][$row["oid"]],
				"ops" => $this->picker($sel,$tar)
			));
			$this->parse("LINE");
			$cnt+=2;
		}

		$this->vars(array(
			"reforb"	=> $this->mk_reforb("save_search_sel", array("id" => $this->id,"page" => $page)),
			"formsonly" => checked($this->arr["formsonly"] == 1),
			"chains" => $this->picker($this->arr["se_chain"], $this->get_chains(true))
		));

		$awt->stop("form::gen_search_sel");
		return $this->do_menu_return();
	}

	////
	// !saves the forms from which to search for search form $id
	function save_search_sel(&$arr)
	{
		$this->dequote(&$arr);
		extract($arr);
		$this->load($id);

		foreach($inpage as $ifid => $v)
		{
			$var = "ch_".$ifid;
			$this->arr["search_from"][$ifid] = $$var;
			$var = "sel_".$ifid;
			$this->arr["search_outputs"][$ifid] = $$var;
		}
		
		// kas ocime aint formist v6i yritame p2rga leida
		$this->arr["formsonly"] = $formsonly;

		$this->arr["se_chain"] = $se_chain;

		$this->save();
		return $this->mk_orb("sel_search", array("id" => $id,"page" => $page));
	}

	// does the actual searching part and returns
	// an array, that has one entry for each form selected as a search target
	// and that entry is an array of matching entries for that form
	// parent(int) - millise parenti alt entrysid otsida
	function search($entry_id,$parent = 0)
	{
		global $awt;
		$awt->start("form::search");
		$awt->count("form::search");

		// laeb täidetud vormi andmed sisse
		$this->load_entry($entry_id);

		// gather all the elements of this form in an array
		$els = array();
		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$this->arr["contents"]["$row"]["$col"]->get_els(&$els);
			};
		};

		$ret = array();

		if (!is_array($this->arr["search_from"]))
		{
			$this->raise_error("form->search($entry_id): no forms selected as search targets!",true);
		}

		reset($this->arr["search_from"]);
		$this->cached_results = array();

		$is_chain = false;
		if ($this->arr["formsonly"] != 1)
		{
			if ($this->arr["se_chain"])
			{
				$is_chain = true;
				$chain_id = $this->arr["se_chain"];
			}
			else
			{
				// leiame kas see otsing on p2rja kohta. 
				$fidstr = join(",", $this->map2("%s",$this->arr["search_from"]));
				if ($fidstr != "")
				{
					$this->db_query("SELECT distinct(chain_id) as chain_id FROM form2chain WHERE form_id IN ($fidstr)");
					while ($row = $this->db_next())
					{
						$is_chain = true;
						$chain_id = $row["chain_id"];
					}
				}
			}
		}

		if ($is_chain)
		{
			$this->chain_id = $chain_id;
			// loop through all the forms that are selected as search targets
			$ar = $this->get_forms_for_chain($chain_id);
			reset($ar);
			list($mid,$v) = each($ar);
			// let's create the query a bit differently - join only the tables that are actually being searched from

			$query = "";
			$forms_in_q = array(); // here we store all the ids of the forms that are actually used in the query and thus must be linked
			$ch_q = array();
			reset($els);
			// loop through all the elements of this form 
			while( list(,$el) = each($els))
			{
				if ($el->arr["linked_form"] && $el->arr["linked_element"])	
				{
					if (trim($el->get_value()) != "")	
					{
						if ($el->get_type() == "multiple")
						{
							$query.=" AND (";
							$ec=explode(",",$el->entry);
							reset($ec);
							$qpts = array();
							while (list(, $v) = each($ec))
							{
								$qpts[] =" form_".$el->arr["linked_form"]."_entries.ev_".$el->arr["linked_element"]." like '%".$el->arr["multiple_items"][$v]."%' ";
							}

							$query.= join("OR",$qpts).")";
						}
						else
						if ($el->get_type() == "checkbox")
						{	
							//checkboxidest ocime aint siis kui nad on tshekitud
							if ($el->get_value(true) == 1)
							{
								// grupeerime p2ringus nii et checkboxi gruppide vahel on AND ja grupi sees OR
								$ch_q[$el->get_ch_grp()][] = " form_".$el->arr["linked_form"]."_entries.ev_".$el->arr["linked_element"]." like '%".$el->get_value()."%' ";
							}
						}
						else
						if ($el->get_type() == "radiobutton")
						{
							if ($el->get_value(true) == 1)
							{
								$query.="AND (form_".$el->arr["linked_form"]."_entries.ev_".$el->arr["linked_element"]." LIKE '%".$el->get_value()."%')";
							}
						}
						else
						if ($el->get_type() == "date")
						{
							if ($el->get_subtype() == "from")
							{
								$query.= "AND (form_".$el->arr["linked_form"]."_entries.el_".$el->arr["linked_element"]." >= ".$this->entry[$el->get_id()].")";
							}
							else
							if ($el->get_subtype() == "to")
							{
								$query.= "AND (form_".$el->arr["linked_form"]."_entries.el_".$el->arr["linked_element"]." <= ".$this->entry[$el->get_id()].")";
							}
							else
							{
								$query.= "AND (form_".$el->arr["linked_form"]."_entries.el_".$el->arr["linked_element"]." = ".$this->entry[$el->get_id()].")";
							}
						}
						else
						{
							$query.= "AND (form_".$el->arr["linked_form"]."_entries.ev_".$el->arr["linked_element"]." like '%".$el->get_value()."%')";
						}
						if ($el->arr["linked_form"] != $mid)
						{
							$forms_in_q[$el->arr["linked_form"]] = $el->arr["linked_form"];
						}
					}
				}
			}
	
			// k2ime l2bi erinevad checkboxide grupid ja paneme gruppide vahele AND ja checkboxide vahele OR
			$chqpts = array();
			foreach($ch_q as $chgrp => $ch_ar)
			{
				$chqs = join(" OR ", $ch_ar);
				if ($chqs !="")
				{
					$query.=" AND ($chqs)";
				}
			}

			// now compose the complete query
			if ($query == "")
			{
				// return all the chain entries for the first form in the chain
				$query = "SELECT distinct(chain_id) as oid FROM form_".$mid."_entries LEFT JOIN objects ON objects.oid = form_".$mid."_entries.id WHERE objects.status != 0 AND form_".$mid."_entries.chain_id IS NOT NULL ";
			}
			else
			{
				// join all the necessary forms together
				$query = "SELECT distinct(form_".$mid."_entries.chain_id) as oid FROM form_".$mid."_entries LEFT JOIN objects ON objects.oid = form_".$mid."_entries.id ".join(" ",map2("LEFT JOIN form_%s_entries ON form_%s_entries.chain_id = form_".$mid."_entries.chain_id",$forms_in_q))." WHERE objects.status != 0 AND form_".$mid."_entries.chain_id IS NOT NULL ".$query;
			}

			$this->main_search_form = $mid;
			$matches = array();
//		echo "query = $query  <br>\n";
//		flush();
			$this->db_query($query);
//		echo "q finished \n <br>";
//		flush();

			while ($row = $this->db_next())
			{
				$matches[] = $row["oid"];
			}

			$this->form_search_only = false;
			$ret = $matches;
		}
		else
		{
			// loop through all the forms that are selected as search targets
			while (list($id,$v) = each($this->arr["search_from"]))
			{
				if ($v == 1)		// search only selected forms
				{
					break;
				}
			}

			if (!$id)
			{
				$this->raise_error("No forms selected as search targets!");
			}

			$this->search_form = $id;
			// create the sql that searches from this form's entries
			$query="SELECT * FROM form_".$id."_entries LEFT JOIN objects ON objects.oid = form_".$id."_entries.id WHERE objects.status !=0 AND objects.lang_id = ".$GLOBALS["lang_id"]." " ;
			if (is_array($parent))
			{
				$query .= sprintf(" AND objects.parent IN (%s)",join(",",$parent));
			}

			// loop through all the elements of this form 
			$ch_q = array();
			reset($els);
			while( list(,$el) = each($els))
			{
				if ($el->arr["linked_form"] == $id)	// and use only the elements that are members of the current form in the query
				{
					// oh la la
					if ($el->get_type() == "checkbox")
					{	
						//checkboxidest ocime aint siis kui nad on tshekitud
						if ($el->get_value(true) == 1)
						{
							$ch_q[$el->get_ch_grp()][] = " form_".$el->arr["linked_form"]."_entries.ev_".$el->arr["linked_element"]." like '%".$el->get_value()."%' ";
						}
					}
					else
					if ($el->get_type() == "radiobutton")
					{
						if ($el->get_value(true) == 1)
						{
							$query.="AND (form_".$el->arr["linked_form"]."_entries.ev_".$el->arr["linked_element"]." LIKE '%".$el->get_value()."%')";
						}
					}
					else
					if ($el->get_type() == "date")
					{
						if ($el->get_subtype() == "from")
						{
							$query.= "AND (form_".$el->arr["linked_form"]."_entries.el_".$el->arr["linked_element"]." >= ".$this->entry[$el->get_id()].")";
						}
						else
						if ($el->get_subtype() == "to")
						{
							$query.= "AND (form_".$el->arr["linked_form"]."_entries.el_".$el->arr["linked_element"]." <= ".$this->entry[$el->get_id()].")";
						}
						else
						{
							$query.= "AND (form_".$el->arr["linked_form"]."_entries.el_".$el->arr["linked_element"]." = ".$this->entry[$el->get_id()].")";
						}
					}
					else
					if ($el->get_type() == "radiobutton")
					{
						// blah
					}
					else
					if ($el->get_value() != "")	
					{
						$query.= "AND ev_".$el->arr["linked_element"]." like '%".$el->get_value()."%' ";
					}
				}

				// k2ime l2bi erinevad checkboxide grupid ja paneme gruppide vahele AND ja checkboxide vahele OR
				$chqpts = array();
				foreach($ch_q as $chgrp => $ch_ar)
				{
					$chqs = join(" OR ", $ch_ar);
					if ($chqs !="")
					{
						$query.=" AND ($chqs)";
					}
				}

				if ($query == "")
				{
					$query = "SELECT * FROM form_".$id."_entries";
				}

//				echo "q = $query <br>";
				$matches = array();
				$this->db_query($query);
				while ($row = $this->db_next())
				{
					$matches[] = $row["id"];
				}

				$ret = $matches;
				$this->form_search_only = true;
			}
		}
	
		$awt->stop("form::search");
		return $ret;
	}

	function do_search($entry_id, $output_id)
	{
		global $awt;
		$awt->start("form::do_search");
		$awt->count("form::do_search");

		global $section;
		$matches = $this->search($entry_id);
		if ($this->arr["show_table"])
		{
			if (!$this->arr["table"])
			{
				$this->raise_error("No table selected for showing data!",true);
			}

			$awt->start("form::do_search::setup");
			classload("form_table");
			$ft = new form_table;
			$ft->start_table($this->arr["table"], array("class" => "form", "action" => "show_entry", "id" => $this->id, "entry_id" => $entry_id, "op_id" => $output_id,"section" => $section));

			// this returns an array of roms each of which is an array of elements that are actually used in the table
			$used_els = $ft->get_used_elements();

			if ($this->form_search_only)
			{
				foreach($used_els as $form_id => $el_arr)
				{
					foreach($el_arr as $elid)
					{
						$q_els[] = "form_".$form_id."_entries.ev_".$elid." as ev_".$elid;
					}
				}
				$eids = join(",", $matches);
				$awt->stop("form::do_search::setup");
				if ($eids != "")
				{
					$awt->start("form::do_search::qandstuff");
					$jss = join(",",$q_els);
					if ($jss != "")
					{
						$jss=",".$jss;
					}
					$chenrties = array();
					$q = "SELECT objects.modifiedby as modifiedby,objects.modified as modified,objects.created as created,form_".$form_id."_entries.id as entry_id $jss FROM form_".$form_id."_entries LEFT JOIN objects ON objects.oid = form_".$form_id."_entries.id WHERE form_".$form_id."_entries.id in ($eids) AND objects.status != 0";
	//				echo "q = $q <br>";
					$this->db_query($q);
					$cnt = 0;
					while ($row = $this->db_next())
					{
						$cnt++;
						$row["ev_change"] = "<a href='".$this->mk_my_orb("show", array("id" => $form_id,"entry_id" => $row["entry_id"],"section" => $section), "form")."'>Muuda</a>";
						$row["ev_created"] = $this->time2date($row["created"], 2);
						$row["ev_uid"] = $row["modifiedby"];
						$row["ev_modified"] = $this->time2date($row["modified"], 2);
						$row["ev_view"] = "<a href='".$this->mk_my_orb("show_entry", array("id" => $form_id,"entry_id" => $row["entry_id"], "op_id" => $this->arr["search_outputs"][$form_id],"section" => $section))."'>Vaata</a>";		
						$row["ev_delete"] = "<a href='".$this->mk_my_orb(
							"delete_entry", 
								array(
									"id" => $fid,
									"entry_id" => $row["entry_id"], 
									"after" => $this->binhex($this->mk_my_orb("show_entry", array("id" => $this->id, "entry_id" => $entry_id, "op_id" => $output_id,"section" => $section)))
								),
							"form")."'>Kustuta</a>";
						if ($ft->table["view_col"] && $ft->table["view_col"] != "view")
						{
							$_link = $this->mk_my_orb("show_entry", array(
										"id" => $form_id,
										"entry_id" => $row["entry_id"],
										"op_id" => $this->arr["search_outputs"][$form_id],
										"section" => $section,
							));

							$_caption = $row["ev_".$ft->table["view_col"]];

							if ($ft->table["view_new_win"])
							{
								$_link = sprintf("javascript:aw_popup('%s&type=popup','popup',%d,%d)",$_link,$ft->table["new_win_x"],$ft->table["new_win_y"]);
							};

							$row["ev_".$ft->table["view_col"]] = sprintf("<a href=\"%s\" %s>%s</a>",$_link,$_targetwin,$_caption);
						}
						if ($ft->table["change_col"] && $ft->table["change_col"] != "change")
						{
							$row["ev_".$ft->table["change_col"]] = "<a href='".$this->mk_my_orb("show", array("id" => $form_id,"entry_id" => $row["entry_id"],"section" => $section), "form")."'>".$row["ev_".$ft->table["change_col"]]."</a>";
						}
						$ft->row_data($row);
					}
				}
			}
			else
			{
				// figure out what elements from what forms are used in the table and bring in the data from those forms and 
				// those forms elements only.
				$joins = array();
				$q_els = array();
				$has_eid = false;
				foreach($used_els as $form_id => $el_arr)
				{
					$joins[] = "LEFT JOIN form_".$form_id."_entries ON form_".$form_id."_entries.chain_id = form_chain_entries.id";
					if (!$has_eid)
					{
						$q_els[] = "form_".$form_id."_entries.id as entry_id";
						$has_eid = true;
						$real_form_id = $form_id;
					}
					foreach($el_arr as $elid)
					{
						$q_els[] = "form_".$form_id."_entries.ev_".$elid." as ev_".$elid;
					}
				}
					
				$eids = join(",", $matches);
				$awt->stop("form::do_search::setup");
				if ($eids != "")
				{
					$awt->start("form::do_search::qandstuff");
					$jss = join(",",$q_els);
					if ($jss != "")
					{
						$jss=",".$jss;
					}
					$joss = join(" ",$joins);
					$chenrties = array();
					$q = "SELECT form_chain_entries.id as chain_entry_id,uid as modifiedby, tm as created, tm as modified  $jss FROM form_chain_entries $joss WHERE form_chain_entries.id in ($eids)";
	//				echo "q = $q <br>";
					$this->db_query($q);
					$cnt = 0;
					while ($row = $this->db_next())
					{
						$cnt++;
						// kui on p2rg, siis muudame p2rga
						$row["ev_change"] = "<a href='".$this->mk_my_orb("show", array("id" => $this->chain_id,"section" => 1,"entry_id" => $row["chain_entry_id"],"section" => $section), "form_chain")."'>Muuda</a>";
						$row["ev_created"] = $this->time2date($row["created"], 2);
						$row["ev_uid"] = $row["modifiedby"];
						$row["ev_modified"] = $this->time2date($row["modified"], 2);
						$row["ev_view"] = "<a href='".$this->mk_my_orb("show_entry", array("id" => $real_form_id,"entry_id" => $row["entry_id"], "op_id" => $this->arr["search_outputs"][$real_form_id],"section" => $section))."'>Vaata</a>";		
						$row["ev_delete"] = "<a href='".$this->mk_my_orb(
							"delete_entry", 
								array(
									"id" => $fid,
									"entry_id" => $row["entry_id"], 
									"after" => $this->binhex($this->mk_my_orb("show_entry", array("id" => $this->id, "entry_id" => $entry_id, "op_id" => $output_id,"section" => $section)))
								),
							"form")."'>Kustuta</a>";
						if ($ft->table["view_col"] && $ft->table["view_col"] != "view")
						{
							$row["ev_".$ft->table["view_col"]] = "<a href='".$this->mk_my_orb("show_entry", array("id" => $real_form_id,"entry_id" => $row["entry_id"], "op_id" => $this->arr["search_outputs"][$real_form_id],"section" => $section))."'>".$row["ev_".$ft->table["view_col"]]."</a>";
						}
						if ($ft->table["change_col"] && $ft->table["change_col"] != "change")
						{
							$row["ev_".$ft->table["change_col"]] = "<a href='".$this->mk_my_orb("show", array("id" => $this->chain_id,"section" => 1,"entry_id" => $row["chain_entry_id"],"section" => $section), "form_chain")."'>".$row["ev_".$ft->table["change_col"]]."</a>";
						}
						$ft->row_data($row);
					}
				}
			}
			$awt->stop("form::do_search::qandstuff");
			$awt->start("form::do_search::finish_table");
			$ft->t->sort_by(array("field" => $GLOBALS["sortby"],"sorder" => $GLOBALS["sort_order"]));
			$tbl = $ft->get_css();
			$tbl.="<form action='reforb.aw' method='POST'>\n";
			if ($ft->table["submit_top"])
			{
				$tbl.="<input type='submit' value='".$ft->table["submit_text"]."'>";
			}
			if ($ft->table["user_button_top"])
			{
				$tbl.="&nbsp;<input type='submit' value='".$ft->table["user_button_text"]."' onClick=\"window.location='".$ft->table["user_button_url"]."';return false;\">";
			}
			$tbl.=$ft->t->draw();

			if ($ft->table["submit_bottom"])
			{
				$tbl.="<input type='submit' value='".$ft->table["submit_text"]."'>";
			}
			if ($ft->table["user_button_bottom"])
			{
				$tbl.="&nbsp;<input type='submit' value='".$ft->table["user_button_text"]."' onClick=\"window.location='".$ft->table["user_button_url"]."';return false;\">";
			}

			$tbl.= $this->mk_reforb("submit_table", array("return" => $this->binhex($this->mk_my_orb("show_entry", array("id" => $this->id, "entry_id" => $entry_id, "op_id" => $output_id)))));

			$tbl.="</form>";
			$awt->stop("form::do_search::finish_table");
			$awt->stop("form::do_search");

			if ($GLOBALS["SITE_ID"] == 14)
			{
				$tbl="Otsingu tulemusena leiti ".$cnt." kirjet. <br>".$tbl;
			}
			return $tbl;
		}
		else
		{
			// n2itame sisestusi lihtsalt yxteise j2rel 
//			reset($matches);
//			while(list($fid,$v) = each($matches))
//			{
				$fid = $this->search_form;
				$t = new form();
				reset($matches);
				while (list(,$eid) = each($matches))
				{
					$t->reset();
					$html.=$t->show(array("id" => $fid, "entry_id" => $eid, "op_id" => $this->arr["search_outputs"][$fid]));
				}
//			}
		}
	
		$awt->stop("form::search");
		return " ".$html;
	}

	////
	// !this gets called when the user views search results as a table that has a submit button. 
	// here we must change the activity / loactio of the form entries
	function submit_table($arr)
	{
		extract($arr);
		if (is_array($old_active))
		{
			foreach($old_active as $fid => $ear)
			{
				foreach($ear as $eid => $status)
				{
					if ($active[$fid][$eid] == 1 && $status == 1)	// new status active, old not active
					{
						// make obj active
						$this->upd_object(array("oid" => $eid, "status" => 2));
					}
					else
					if ($active[$fid][$eid] != 1 && $status == 2)	// new status not active, old active
					{
						// make not active
						$this->upd_object(array("oid" => $eid, "status" => 1));
					}
				}
			}
		}

		if (is_array($old_pos))
		{
			foreach($old_pos as $fid => $par)
			{
				foreach($par as $eid => $loc)
				{
					if ($chpos[$fid][$eid] != 0 && $loc != $chpos[$fid][$eid])	// location selected and changed
					{
						// change location
						$this->upd_object(array("oid" => $eid, "parent" => $chpos[$fid][$eid]));
					}
				}
			}
		}
		return $this->hexbin($return);
	}

	function html()
	{
		$frm= $this->gen_user_html();
		$frm = htmlentities($frm);
		$this->reset();
		$this->read_template("html.tpl");
		$this->vars(array("form" => $frm));
		return $this->parse();
	}

	// new orb functions start here
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_FORM_ADD_FORM);
		$this->read_template("form_add.tpl");
		$this->vars(array(
			"forms" => $this->picker(0,$this->get_list(FTYPE_ENTRY,true)),
			"reforb"	=> $this->mk_reforb("submit_add",array("parent" => $parent, "alias_doc" => $alias_doc))
		));
		return $this->parse();
	}

	function submit_add($arr)
	{
		extract($arr);

		$id = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_FORM, "comment" => $comment));
		
		if ($type == "entry") 
		{
			$type = FORM_ENTRY;
		}
		if ($type == "search")
		{
			$type = FORM_SEARCH;
		}
		if ($type == "rating")
		{
			$type = FORM_RATING;
		}

		$this->db_query("INSERT INTO forms(id, type,content,cols,rows) VALUES($id, $type,'',1,1)");

		$this->db_query("CREATE TABLE form_".$id."_entries (id int primary key,chain_id int)");
		$this->db_query("ALTER TABLE form_".$id."_entries add index chain_id(chain_id)");

		$this->load($id);

		$this->_log("form",LC_FORM_ADDED_FORM.$name);

		if ($alias_doc)
		{
			$this->add_alias($alias_doc, $id);
		}

		// uhm yeah. if the user selected a base form, then we must clone it and all the elements in it
		if ($base)
		{
			$bf = new form;
			$bf->load($base);

			$this->arr = $bf->arr;
			$this->arr["elements"] = array();
			$this->arr["contents"] = array();
			for ($row = 0; $row < $this->arr["rows"]; $row++)
			{
				for ($col=0; $col < $this->arr["cols"]; $col++)
				{
					$bf->arr["contents"][$row][$col]->form = &$this;	// this is a trick to make form cells in $bf save new elements to $this
					$bf->arr["contents"][$row][$col]->id = $id;

					if (is_array($bf->arr["elements"][$row][$col]))
					{
						foreach($bf->arr["elements"][$row][$col] as $elid => $elval)
						{
							if (is_number($elid))
							{
								// replicate this element into this form!!
								$el_parent = $this->db_fetch_field("SELECT parent FROM objects WHERE oid = $elid", "parent");

								$newel = $bf->arr["contents"][$row][$col]->do_add_element(array("parent" => $el_parent, "name" => $elval["name"], "based_on" => $elid));

								$elval["id"] = $newel;
								$elval["ver2"] = true;
								$elval["linked_form"] = $base;
								$elval["linked_element"] = $elid;
								$this->arr["elements"][$row][$col][$newel] = $elval;
							}
							else
							{
								// class style
								$this->arr["elements"][$row][$col]["style"] = $elval;
							}
						}
					}
				}
			}
			$this->arr["search_from"][$base]=1;
			$this->save();
		}
		return $this->mk_orb("change", array("id" => $id));
	}

	// here we must make a list of all the filled forms, that include element with id $arr[id]
	function list_el_forms($arr)
	{
		global $awt;
		$awt->start("form::list_el_forms");
		$awt->count("form::list_el_forms");

		extract($arr);
		$this->read_template("objects.tpl");
		$this->db_query("SELECT form_id FROM element2form WHERE el_id = $id");
		$el_row = $this->db_next();
		$form_id = $el_row["form_id"];
		
		global $sortby;
		if ($sortby == "")
			$sortby = "jrk";

		global $order,$baseurl;
		if ($order == "")
			$order = "ASC";

		global $class_defs;
		$arr = array();
		reset($class_defs);
		while (list($id,$ar) = each($class_defs))
		{
			if ($ar["can_add"])	// only object types that can be added anywhere
			{
				$arr[$id] = $ar["name"];
			}
		}
		$this->vars(array("parent" => $parent,"types" => $this->option_list(0,$arr)));
		$this->vars(array("ADD_CAT" => "","form_id" => $form_id));

		global $class_defs;

		classload("menuedit");
		$m = new menuedit;

		$this->listacl("objects.class_id = ".CL_FORM_ENTRY." AND objects.status != 0 AND form_entries.form_id = $form_id",array("form_entries" => "form_entries.id = objects.oid"));
		$this->db_query("SELECT objects.* FROM objects LEFT JOIN form_entries ON form_entries.id = objects.oid WHERE objects.class_id = ".CL_FORM_ENTRY." AND objects.status != 0 AND form_entries.form_id = $form_id ORDER BY $sortby $order");
		while ($row = $this->db_next())
		{
			$this->dequote(&$row["name"]);
			$inf = $class_defs[$row["class_id"]];
			$this->vars(array("name"				=> $row["name"],
												"oid"					=> $row["oid"], 
												"order"				=> $row["jrk"], 
												"active"			=> ($row["status"] == 2 ? "CHECKED" : ""),
												"active2"			=> $row["status"],
												"modified"		=> $this->time2date($row["modified"],2),
												"modifiedby"	=> $row["modifiedby"],
												"icon"				=> $m->get_icon_url($row["class_id"],$row["name"]),
												"type"				=> $GLOBALS["class_defs"][$row["class_id"]]["name"],
												"change"			=> $this->mk_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]), $inf["file"])));
			$this->vars(array("NFIRST" => $this->can("order",$row["oid"]) ? $this->parse("NFIRST") : "",
												"CAN_ACTIVE" => $this->can("active",$row["oid"]) ? $this->parse("CAN_ACTIVE") : ""));
			$l.=$this->parse("LINE");
		}

		$this->vars(array("LINE" => $l,
											"reforb" => $this->mk_reforb("submit_order_doc", array("parent" => $parent)),
											"order1"			=> $sortby == "name" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
											"sortedimg1"	=> $sortby == "name" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
											"order2"			=> $sortby == "jrk" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
											"sortedimg2"	=> $sortby == "jrk" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
											"order3"			=> $sortby == "modifiedby" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
											"sortedimg3"	=> $sortby == "modifiedby" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
											"order4"			=> $sortby == "modified" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
											"sortedimg4"	=> $sortby == "modified" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
											"order5"			=> $sortby == "class_id" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
											"sortedimg5"	=> $sortby == "class_id" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
											"order6"			=> $sortby == "status" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
											"sortedimg6"	=> $sortby == "status" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : ""
											));
		$awt->stop("form::list_el_forms");
		return $this->parse();
	}

	////
	// !Generates admin form for editing cell at ($row,$col) in form $id
	function admin_cell($arr)
	{
		extract($arr);
		$this->load($id);
		return $this->arr["contents"][$row][$col]->admin_cell();
	}

	////
	// !Adds an element to the end of 
	function add_element($arr)
	{
		extract($arr);
		$this->load($id);
		$ret = $this->arr["contents"][$row][$col]->add_element($wizard_step);
		if ($ret == false)
		{
			return $this->mk_orb("admin_cell", array("id" => $this->id, "row" => $row, "col" => $col));
		}
		else
		{
			return $ret;
		}
	}

	////
	// !saves the elements in the cell ($row, $col) in form $id
	function submit_cell($arr)
	{
		$this->dequote(&$arr);
		extract($arr);
		$this->load($id);
		$this->arr["contents"][$row][$col]->submit_cell($arr,&$this);
		$this->save();
		return $this->mk_orb("admin_cell", array("id" => $this->id, "row" => $row, "col" => $col));
	}

	////
	// !generates the form for selecting cell style
	function sel_cell_style($arr)
	{
		extract($arr);
		$this->load($id);
		return $this->arr["contents"][$row][$col]->pickstyle();
	}

	////
	// !saves the cell style
	function save_cell_style($arr)
	{
		$this->dequote(&$arr);
		extract($arr);
		$this->load($id);
		$this->arr["contents"][$row][$col]->set_style($style,&$this);
		$this->save();
		return $this->mk_orb("sel_cell_style", array("id" => $this->id, "row" => $row, "col" => $col));
	}

	////
	// !deletes form $id
	function delete($arr)
	{
		extract($arr);
		$this->delete_object($id);
		$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $id","name");
		$this->_log("form","Kustutas formi $name");
		header("Location: ".$this->mk_orb("obj_list", array("parent" => $parent), "menuedit"));
	}

	////
	// !finds the element with id $id in the loaded form and returns a reference to it
	function &get_element_by_id($id)
	{
		global $awt;
		$awt->start("form::get_element_by_id");
		$awt->count("form::get_element_by_id");

		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col = 0; $col < $this->arr["cols"]; $col++)
			{
				$this->arr["contents"][$row][$col]->get_els(&$elar);
				while (list(,$el) = each($elar))
				{
					if ($el->get_id() == $id)
					{
						$awt->stop("form::get_element_by_id");
						return $el;
					}
				}
			}
		}
		$awt->stop("form::get_element_by_id");
		return false;
	}

	////
	// !finds the element with name $name in the loaded form and returns a reference to it
	// Kui ma nyyd oieti aru saan, siis see eeldab muu hulgas ka seda, et on laetud mingi entry.
	//
	// nope, see ei eelda, get_element_value_by_name eeldab et miski entry on loaditud - terryf
	// $type can be either RET_FIRST - returns the forst element or RET_ALL - returns all elements with the name

	function get_element_by_name($name,$type = RET_FIRST)
	{
		global $awt;
		$awt->start("form::get_element_by_name");
		$awt->count("form::get_element_by_name");

		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col = 0; $col < $this->arr["cols"]; $col++)
			{
				$this->arr["contents"][$row][$col]->get_els(&$elar);
				reset($elar);
				while (list(,$el) = each($elar))
				{
					if ($el->get_el_name() == $name)
					{
						if ($type == RET_FIRST)
						{
							$awt->start("form::get_element_by_name");
		$awt->count("form::get_element_by_name");

							return $el;
						}
						else
						{
							$ret[] = $el;
						}
					}
				}
			}
		}
		$awt->start("form::get_element_by_name");
		$awt->count("form::get_element_by_name");

		if ($type == RET_FIRST || !is_array($ret))
		{
			return false;
		}
		else
		{
			return $ret;
		}
	}

	////
	// !Finds the id-s for for form elements passed by name to this function
	// argumendid:
	// names(array) - array nimedest, mille id-sid me teada tahame
	function get_ids_by_name($args = array())
	{
		global $awt;
		$awt->start("form::get_ids_by_name");
		$awt->count("form::get_ids_by_name");

		extract($args);
		$retval = array();
		$namelist = array_flip($names);

		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col = 0; $col < $this->arr["cols"]; $col++)
			{
				$elar = array();
				$this->arr["contents"][$row][$col]->get_els(&$elar);
				reset($elar);
				while(list(,$el) = each($elar))
				{
					$name = $el->arr["name"];
					if (isset($namelist[$name]))
					{
						$retval[$name] = sprintf("el_%d",$el->arr["id"]);
					};
				};
			};
		};
		$awt->stop("form::get_ids_by_name");
		return $retval;
	}
	////
	// !Teeb paringu entryte saamiseks laaditud vormi juures
	function get_entries($args = array())
	{
		global $awt;
		$awt->start("form::get_entries");
		$awt->count("form::get_entries");

		extract($args);
		// kui parent on antud, siis moodustame sellest IN klausli
		$pstr = ($parent) ? " WHERE objects.parent IN (" . join(",",map("'%s'",$parent)) . ")" : "";
		$fid = ($id) ? $id : $this->id;

		$table = sprintf("form_%d_entries",$fid);
		$q = "SELECT * FROM $table LEFT JOIN objects ON ($table.id = objects.oid) $pstr";
		$this->db_query($q);
		$awt->stop("form::get_entries");
	}

	////
	// !finds the first element with type $type (and subtype $subtype) in the loaded form and returns a reference to it
	function get_element_by_type($type,$subtype = "")
	{
		global $awt;
		$awt->start("form::get_element_by_type");
		$awt->count("form::get_element_by_type");

		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col = 0; $col < $this->arr["cols"]; $col++)
			{
				$this->arr["contents"][$row][$col]->get_els(&$elar);
				reset($elar);
				while (list(,$el) = each($elar))
				{
					if ($el->get_type() == $type && ($subtype == "" || $el->get_subtype() == $subtype))
					{
						$awt->stop("form::get_element_by_type");
						return $el;
					}
				}
			}
		}
		$awt->stop("form::get_element_by_type");
		return false;
	}

	////
	// !generates the form for changing the form element's position in the hierarchy and in the cells
	function change_el_pos($arr)
	{
		extract($arr);
		$this->init($id, "", "<a href='".$this->mk_orb("change", array("id" => $id)).LC_FORM_CHANGE_FORM_CHOOSE_EL_LOC);
		$el =&$this->get_element_by_id($el_id);
		return $el->change_pos($arr,&$this);
	}

	////
	// !saves the element position changes
	function submit_chpos($arr)
	{
		extract($arr);
		$this->load($id);
		
		$this->upd_object(array("oid" => $el_id, "parent" => $parent));

		if (is_array($c_cell))
		{
			$oldel = $this->arr["elements"][$row][$col][$el_id];
			$oldel_ob = $this->get_object($el_id);

			$cnt = 1;
			foreach($c_cell as $rowc)
			{
				list($r,$c) = explode("_", $rowc);
				// $r,$c = kuhu kopeerida element
				// kordame niimitu korda kui mitu elementi tyyp tahtis
				
				for ($i=0; $i < $el_count; $i++)
				{
					$name = $oldel["name"]."_".$cnt;
					$this->arr["contents"][$row][$col]->do_add_element(array("name" => $name, "parent" => $oldel_ob["parent"], "based_on" => $el_id));
					$cnt++;
				}
			}
			$this->save();	// sync
			$this->load($id);
		}

		list($r,$c) = explode("_", $s_cell);

		if (!($r == $row && $c == $col))
		{
			$this->arr[elements][$r][$c][$el_id] = $this->arr[elements][$row][$col][$el_id];
			unset($this->arr[elements][$row][$col][$el_id]);
			if (!is_array($this->arr[elements][$row][$col]))
			{
				$this->arr[elements][$row][$col] = array();
			}
			$this->save();
		}

		return $this->mk_orb("change_el_pos", array("id" => $this->id, "col" => $c, "row" => $r, "el_id" => $el_id));
	}

	function _serialize($arr)
	{
		extract($arr);
		$this->db_query("SELECT objects.*, forms.* FROM objects LEFT JOIN forms ON forms.id = objects.oid WHERE oid = $oid");
		$row = $this->db_next();
		if (!$row)
		{
			return false;
		}
		return serialize($row);
	}

	function _unserialize($arr)
	{
		extract($arr);

		$row = unserialize($str);
		// basically, we create a new object and insert the stuff in the array right back in it. 
		$oid = $this->new_object(array("parent" => $parent, "name" => $row["name"], "class_id" => CL_FORM, "status" => $row["status"], "comment" => $row["comment"], "last" => $row["last"], "jrk" => $row["jrk"], "visible" => $row["visible"], "period" => $period, "alias" => $row["alias"], "periodic" => $row["periodic"], "doc_template" => $row["doc_template"], "activate_at" => $row["activate_at"], "deactivate_at" => $row["deactivate_at"], "autoactivate" => $row["autoactivate"], "autodeactivate" => $row["autodeactivate"], "brother_of" => $row["brother_of"]));

		// same with the form. 
		$this->quote(&$row);
		$this->db_query("INSERT INTO forms(id,content,type,cols,rows) values($oid,'".$row["content"]."','".$row["type"]."','".$row["cols"]."','".$row["rows"]."')");

		// create form entries table
		$this->db_query("CREATE TABLE form_".$oid."_entries (id int primary key,chain_id int)");

		// then we go through alla the elements in the form
		$this->load($oid);

		$fc = new form_cell;

		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col = 0; $col < $this->arr["cols"]; $col++)
			{
				if (is_array($this->arr["elements"][$row][$col]))
				{
					reset($this->arr["elements"][$row][$col]);
					while (list($k,$v) = each($this->arr["elements"][$row][$col]))
					{
						// and for each alter the form_xxx_entries table and the form element to include this form. cool.
						$fc->_do_add_element($this->id,$k);
					}
				}
			}
		}
		// and we should be done? ok, except for form actions and outputs, but we do those l8r. 
		return $oid;
	}

	////
	// !generates the form for changing output metainfo
	function metainfo($arr)
	{
		extract($arr);
		$this->init($id,"metainfo.tpl","Muutis formi $this->name metainfot");
		$row = $this->get_object($this->id);

		$this->db_query("SELECT count(id) as cnt from form_entries where form_id = $this->id");
		if (!($cnt = $this->db_next()))
			$this->raise_error("form->metainfo(): weird error!", true);

		$this->vars(array("created"			=> $this->time2date($row["created"],2), 
											"created_by"	=> $row["createdby"],
											"modified"		=> $this->time2date($row["modified"],2),
											"modified_by"	=> $row["modifiedby"],
											"views"				=> $row["hits"],
											"num_entries"	=> $cnt["cnt"],
											"position"		=> $ret,
											"reforb"			=> $this->mk_reforb("submit_metainfo", array("id" => $this->id)),
											"form_name"		=> $row["name"],
											"form_comment" => $row["comment"]));
		return $this->do_menu_return();
	}


	function submit_metainfo(&$arr)
	{
		$this->quote(&$arr);
		extract($arr);
		$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		$this->_log("form","Muutis formi $this->name metainfot");
		return $this->mk_orb("metainfo",  array("id" => $id));
	}

	////
	// !returns the value of the entered element. form entry must be loaded before calling this.
	function get_element_value_by_name($name)
	{
		global $awt;
		$awt->start("form::get_element_value_by_name");
		$awt->count("form::get_element_value_by_name");

		$el = $this->get_element_by_name($name);
		if (!$el)
		{
			$awt->stop("form::get_element_value_by_name");
			return false;
		}

		$va = $el->get_value();
		$awt->stop("form::get_element_value_by_name");
		return $va;
	}

	////
	// !returns the value of the entered element. finds the first element of $type (and $subtype)  and 
	// ignores the rest. form entry must be loaded before calling this.
	function get_element_value_by_type($type,$subtype = "")
	{
		global $awt;
		$awt->start("form::get_element_value_by_type");
		$awt->count("form::get_element_value_by_type");

		$el = $this->get_element_by_type($type,$subtype);
		if (!$el)
		{
			$awt->stop("form::get_element_value_by_type");
			return false;
		}

		$va = $el->get_value();
		$awt->stop("form::get_element_value_by_type");
		return $va;
	}

	////
	// !returns the value of element with id $id
	function get_element_value($id)
	{
		global $awt;
		$awt->start("form::get_element_value");
		$awt->count("form::get_element_value");

		$el = $this->get_element_by_id($id);
		if ($el)
		{
			$ev =  $el->get_value();
			$awt->stop("form::get_element_value");
			return $ev;
		}
		$awt->stop("form::get_element_value");
		return "";
	}

	////
	// !sets the element $id's value in the loaded entry to $val
	function set_element_value($id,$val)
	{
		global $awt;
		$awt->start("form::set_element_value");
		$awt->count("form::set_element_value");

		$this->entry[$id] = $val;
		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$this->arr["contents"][$row][$col] -> set_entry(&$this->entry, $this->entry_id,&$this);
			};
		};
		$awt->stop("form::set_element_value");
	}

	////
	// !sets the element value in the loaded entry to $val fort elements of type $type
	function set_element_value_by_type($type,$val)
	{
		global $awt;
		$awt->start("form::set_element_value");
		$awt->count("form::set_element_value");

		$el = $this->get_element_by_type($type);
		if ($el)
		{
			$id = $el->get_id();
			$this->set_element_value($id,$val);
		}
		$awt->stop("form::set_element_value");
	}

	////
	// !returns the number of rows in the currently loaded form
	function get_num_rows()
	{
		return $this->arr["rows"];
	}

	////
	// !returns all the element_name => value pairs for the specified row
	// type values are defined in the beginning of this file
	function get_elements_for_row($row,$type = ARR_ELNAME)
	{
		global $awt;
		$awt->start("form::get_elements_for_row");
		$awt->count("form::get_elements_for_row");

		$ret = array();
		for ($col = 0; $col < $this->arr["cols"]; $col++)
		{
			$this->arr["contents"][$row][$col]->get_els(&$elar);
			reset($elar);
			while (list(,$el) = each($elar))
			{
				if ($type == ARR_ELNAME)
				{
					$k = $el->get_el_name();
				}
				else
				if ($type == ARR_ELID)
				{
					$k = $el->get_id();
				}
				$ret[$k] = $el->get_value();
			}
		}
		$awt->stop("form::get_elements_for_row");
		return $ret;
	}

	////
	// !returns true if the value is the value that a checkbox recieves if it is checked
	function is_checked_value($val)
	{
		if ($val == '1')
		{
			return true;
		}
		return false;
	}

	////
	// !returns array of name => value pairs for the loaded form entry if $type == ARR_ELNAME
	// if $type == ARR_ELID, then array index is element id
	function get_element_values($type = ARR_ELNAME)
	{
		global $awt;
		$awt->start("form::get_element_values");
		$awt->count("form::get_element_values");

		$ret = array();
		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col = 0; $col < $this->arr["cols"]; $col++)
			{
				$this->arr["contents"][$row][$col]->get_els(&$elar);
				reset($elar);
				while (list(,$el) = each($elar))
				{
					if ($type == ARR_ELNAME)
					{
						$k = $el->get_el_name();
					}
					else
					if ($type == ARR_ELID)
					{
						$k = $el->get_id();
					}
					$ret[$k] = $el->get_value();
				}
			}
		}
		$awt->stop("form::get_element_values");
		return $ret;
	}

	////
	// returns the entry in an array that you can feed to restore_entry to revert the saved entry to the old data
	function get_entry($form_id,$entry_id,$id_only = false)
	{
		global $awt;
		$awt->start("form::get_entry");
		$awt->count("form::get_entry");

		$ret = array();
		$this->db_query("SELECT * FROM form_".$form_id."_entries WHERE id = $entry_id");
		$row =  $this->db_next();
		if ($row)
		{
			foreach($row as $k => $v)
			{
				$key = substr($k,3);
				if (substr($k,0,3) == "el_")
				{
					if ( $id_only )
					{
						$ret[$key] = $v;
					}
					else
					{
						$ret[$k] = $v;
					};
				}
			}
		}
		$awt->stop("form::get_entry");
		return $ret;
	}

	function restore_entry($form_id,$entry_id,$arr)
	{
		if (!is_array($arr))
		{
			return;
		}
		$str = join(",",$this->map2(" %s = '%s' ",$arr));
		if ($str != "" && $entry_id)
		{
			$this->db_query("UPDATE form_".$form_id."_entries SET $str WHERE id = $entry_id");
		}
	}

	function convels()
	{
		// convert from old representation of element -> form relations to the new and better one
		$this->db_query("SELECT form_elements.*,objects.* FROM form_elements LEFT JOIN objects ON objects.oid = form_elements.id WHERE objects.status != 0");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			echo "element ".$row["name"]." id ".$row["oid"]."<br>";
			$fa = unserialize($row["forms"]);
			if (is_array($fa))
			{
				foreach($fa as $fid)
				{
					$this->db_query("INSERT INTO element2form(el_id,form_id) VALUES(".$row["oid"].",$fid)");
					echo "in form $fid <br>";
				}
			}
			flush();
			$this->restore_handle();
		}
	}

	function convtype()
	{
		$this->db_query("SELECT objects.*,menu.* FROM objects,menu WHERE objects.oid = menu.id AND objects.class_id = ".CL_PSEUDO." AND menu.type = ".MN_FORM_ELEMENT);
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$this->upd_object(array("oid" => $row["oid"], "class_id" => CL_FORM_ELEMENT));
			echo "oid = ", $row["oid"], "name = ",$row["name"], "<br>";
			$this->restore_handle();
		}
	}

	function convindexes()
	{
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_FORM);
		while ($row = $this->db_next())
		{
			$this->save_handle();
			echo "ALTER TABLE form_".$row["oid"]."_entries add index chain_id(chain_id)<br>\n";
			flush();
			$this->db_query("ALTER TABLE form_".$row["oid"]."_entries add index chain_id(chain_id)");
			$this->restore_handle();
		}
	}

	////
	// !converts form_xx_entries table and adds ev_xxx columns
	function convtables()
	{
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_FORM);
		while ($row = $this->db_next())
		{
			$this->save_handle();
			
			$this->db_query("SELECT * FROM element2form WHERE form_id = ".$row["oid"]);
			while ($erow = $this->db_next())
			{
				$this->save_handle();
				
				$this->db_query("ALTER TABLE form_".$row["oid"]."_entries ADD ev_".$erow["el_id"]." text");
				
				$this->restore_handle();
			}
			
			$this->restore_handle();
		}
	}

	function conventries()
	{
		$run = true;
//		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_FORM);
		
//		while ($frow = $this->db_next())
//		{
	$frow=array("oid" => 1735);

			if ($run)
			{
			$this->save_handle();

			$form = new form;
			$form->load($frow["oid"]);

			echo "form ",$frow["oid"],"<br>\n";
			flush();
			$cnt = 0;
			$this->db_query("SELECT * FROM form_".$frow["oid"]."_entries");
			while ($erow = $this->db_next())
			{
				$cnt++;
				if (($cnt % 100) == 0)
				{
					echo "cnt = $cnt <br>\n";
					flush();
				}
				$this->save_handle();
				
				$form->load_entry($erow["id"]);
				for ($row = 0; $row < $form->arr["rows"]; $row++)
				{
					for ($col = 0; $col < $form->arr["cols"]; $col++)
					{
						$elar = array();
						$form->arr["contents"][$row][$col]->get_els(&$elar);
						foreach($elar as $el)
						{
//							if ($erow["ev_".$el->get_id()] != $el->get_value())
//							{
								$ev = $el->get_value();
//								$ev = preg_replace("/<(.*)>(.*)<\/(.*)>/imsU","",$ev);
								$ev = strip_tags($ev);
	echo "value for element ", $el->get_id(), " set to $ev <br>\n";
	flush();
								$this->db_query("UPDATE form_".$frow["oid"]."_entries SET ev_".$el->get_id()." = '".$ev."' WHERE id = ".$erow["id"]);
//							}
						}
					}
				}
				
				$this->restore_handle();
			}

			$this->restore_handle();
			}
//		}
	}

	function convchains()
	{
		classload("xml");
		$x = new xml;
		$this->db_query("DELETE FROM form2chain");

		$this->db_query("SELECT * FROM form_chains");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$cc = $x->xml_unserialize(array("source" => $row["content"]));
			foreach($cc["forms"] as $fid => $fid)
			{
				$this->db_query("INSERT INTO form2chain(form_id,chain_id,ord) values($fid,".$row["id"].",'".$cc["form_order"][$fid]."')");
			}
			$this->restore_handle();
		}
	}

	////
	// !lets the user select all folders for the form
	function set_folders($arr)
	{
		extract($arr);
		$this->init($id,"settings_folders.tpl", LC_FORM_CHANGE_FOLDERS);
		
		$o = new db_objects;
		$menulist = $o->get_list();

		$this->vars(array(
			"relation_forms" => $this->multiple_option_list($this->arr["relation_forms"], $this->get_list(FTYPE_ENTRY,false,true)),
			"ff_folder"	=> $this->picker($this->arr["ff_folder"], $menulist),
			"ne_folder"	=> $this->picker($this->arr["newel_parent"], $menulist),
			"tear_folder"	=> $this->picker($this->arr["tear_folder"], $menulist),
			"el_menus" => $this->multiple_option_list($this->arr["el_menus"], $menulist),
			"el_menus2" => $this->multiple_option_list($this->arr["el_menus2"], $menulist),
			"el_move_menus" => $this->multiple_option_list($this->arr["el_move_menus"], $menulist),
			"reforb"	=> $this->mk_reforb("save_folders", array("id" => $id))
		));
		return $this->do_menu_return();
	}

	////
	// !Salvestab vormi settingutes määratud folderite asukohad.
	function save_folders($arr)
	{
		extract($arr);
		$this->load($id);
		
		// ff_folder - kuhu pannakse vormi sisestused?
		$this->arr["ff_folder"] = $ff_folder;

		// kataloog, kuhu lisatakse uued elemendid
		$this->arr["newel_parent"] = $newel_parent;

		// kataloog, kuhu pannakse rebitud elemendid
		$this->arr["tear_folder"] = $tear_folder;

		// kataloogid, kuhu saab uusi elemente salvestada
		$this->arr["el_menus"] = "";
		if (is_array($el_menus))
		{
			foreach($el_menus as $menuid)
			{
				$this->arr["el_menus"][$menuid] = $menuid;
			}
		}

		// formid kust saab seoseelemente valida
		if (is_array($relation_forms))
		{
			foreach($relation_forms as $r_fid)
			{
				$this->arr["relation_forms"][$r_fid] = $r_fid;
			}
		}

		classload("objects");
		$iobj = new db_objects;
		$ms = $iobj->get_list();
		// kataloogid kuhu saab elemente liigutada
		$this->arr["el_move_menus"] = "";
		if (is_array($el_move_menus))
		{
			foreach($el_move_menus as $menuid)
			{
				$this->arr["el_move_menus"][$menuid] = $ms[$menuid];
			}
		}

		$this->arr["el_menus2"] = "";
		if (is_array($el_menus2))
		{
			foreach($el_menus2 as $menuid)
			{
				$this->arr["el_menus2"][$menuid] = $menuid;
			}
		}
		$this->save();
		return $this->mk_orb("set_folders", array("id" => $id));
	}

	function get_search_targets()
	{
		global $awt;
		$awt->start("form::get_search_targets");
		$awt->count("form::get_search_targets");

		$ret = array();
		if (is_array($this->arr["search_from"]))
		{
			foreach ($this->arr["search_from"] as $fid => $one)
			{
				if ($one == 1)
				{
					$ret[$fid] = $this->arr["search_from"][$fid];
				}
			}
		}
		$awt->stop("form::get_search_targets");
		return $ret;
	}

	function get_relation_targets()
	{
		global $awt;
		$awt->start("form::get_relation_targets");
		$awt->count("form::get_relation_targets");

		$ret = array();
		if (is_array($this->arr["relation_forms"]))
		{
			foreach ($this->arr["relation_forms"] as $fid => $fid)
			{
				$o = $this->get_object($fid);
				$ret[$fid] = $o["name"];
			}
		}
		$awt->stop("form::get_relation_targets");
		return $ret;
	}

	////
	// !if this function is called, all price elements are converted to this currency
	function set_active_currency($cuid = 0)
	{
		$this->active_currency = $cuid;
	}

	////
	// !shows the form texts translation form
	function translate($arr)
	{
		extract($arr);
		$this->init($id,"translate.tpl","T&otilde;gi");

		classload("languages");
		$la = new languages;
		$langs = $la->listall();

		foreach($langs as $lar)
		{
			$this->vars(array(
				"lang_name" => $lar["name"]
			));
			$lah.=$this->parse("LANGH");
		}
		$this->vars(array("LANGH" => $lah));

		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$elar = array();
				$this->arr["contents"][$row][$col]->get_els(&$elar);

				foreach($elar as $el)
				{
					$lcol = "";
					foreach($langs as $lar)
					{
						$this->vars(array(
							"text" => $el->get_lang_text($lar["id"]),
							"col" => $col,
							"row" => $row,
							"elid" => $el->get_id(),
							"lang_id" => $lar["id"]
						));
						$lcol.=$this->parse("LCOL");
					}
					$this->vars(array("LCOL" => $lcol));
					$lrow.=$this->parse("LROW");
				}
			}
		}
		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$elar = array();
				$this->arr["contents"][$row][$col]->get_els(&$elar);

				foreach($elar as $el)
				{
					if ($el->get_type() == "listbox")
					{
						for ($i=0; $i < $el->arr["listbox_count"]; $i++)
						{
							$lcol1 = "";
							foreach($langs as $lar)
							{
								if ($lar["id"] != $this->lang_id)
								{
									$txt = $el->arr["listbox_lang_items"][$lar["id"]][$i];
								}
								else
								{
									$txt = $el->arr["listbox_items"][$i];
								}
								$this->vars(array(
									"text" => $txt,
									"col" => $col,
									"row" => $row,
									"elid" => $el->get_id(),
									"lang_id" => $lar["id"],
									"item" => $i
								));
								$lcol1.=$this->parse("LCOL1");
							}
							$this->vars(array("LCOL1" => $lcol1));
							$lrow1.=$this->parse("LROW1");
						}
					}
				}
			}
		}
		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$elar = array();
				$this->arr["contents"][$row][$col]->get_els(&$elar);

				foreach($elar as $el)
				{
					if ($el->get_type() == "multiple")
					{
						for ($i=0; $i < $el->arr["multiple_count"]; $i++)
						{
							$lcol2 = "";
							foreach($langs as $lar)
							{
								if ($lar["id"] != $this->lang_id)
								{
									$txt = $el->arr["multiple_lang_items"][$lar["id"]][$i];
								}
								else
								{
									$txt = $el->arr["multiple_items"][$i];
								}
								$this->vars(array(
									"text" => $txt,
									"col" => $col,
									"row" => $row,
									"elid" => $el->get_id(),
									"lang_id" => $lar["id"],
									"item" => $i
								));
								$lcol2.=$this->parse("LCOL2");
							}
							$this->vars(array("LCOL2" => $lcol2));
							$lrow2.=$this->parse("LROW2");
						}
					}
				}
			}
		}
		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$elar = array();
				$this->arr["contents"][$row][$col]->get_els(&$elar);

				foreach($elar as $el)
				{
					$lcol3 = "";
					foreach($langs as $lar)
					{
						if ($lar["id"] != $this->lang_id)
						{
							$txt = $el->arr["lang_info"][$lar["id"]];
						}
						else
						{
							$txt = $el->arr["info"];
						}
						$this->vars(array(
							"text" => $txt,
							"col" => $col,
							"row" => $row,
							"elid" => $el->get_id(),
							"lang_id" => $lar["id"],
						));
						$lcol3.=$this->parse("LCOL3");
					}
					$this->vars(array("LCOL3" => $lcol3));
					$lrow3.=$this->parse("LROW3");
				}
			}
		}
		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$elar = array();
				$this->arr["contents"][$row][$col]->get_els(&$elar);

				foreach($elar as $el)
				{
					if ($el->get_type() == "textbox" || $el->get_type() == "textarea")
					{
						$lcol4 = "";
						foreach($langs as $lar)
						{
							if ($lar["id"] != $this->lang_id)
							{
								$txt = $el->arr["lang_default"][$lar["id"]];
							}
							else
							{
								$txt = $el->arr["default"];
							}
							$this->vars(array(
								"text" => $txt,
								"col" => $col,
								"row" => $row,
								"elid" => $el->get_id(),
								"lang_id" => $lar["id"],
							));
							$lcol4.=$this->parse("LCOL4");
						}
						$this->vars(array("LCOL4" => $lcol4));
						$lrow4.=$this->parse("LROW4");
					}
				}
			}
		}
		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$elar = array();
				$this->arr["contents"][$row][$col]->get_els(&$elar);

				foreach($elar as $el)
				{
					$lcol5 = "";
					foreach($langs as $lar)
					{
						if ($lar["id"] != $this->lang_id)
						{
							$txt = $el->arr["lang_must_error"][$lar["id"]];
						}
						else
						{
							$txt = $el->arr["must_error"];
						}
						$this->vars(array(
							"text" => $txt,
							"col" => $col,
							"row" => $row,
							"elid" => $el->get_id(),
							"lang_id" => $lar["id"],
						));
						$lcol5.=$this->parse("LCOL5");
					}
					$this->vars(array("LCOL5" => $lcol5));
					$lrow5.=$this->parse("LROW5");
				}
			}
		}
		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$elar = array();
				$this->arr["contents"][$row][$col]->get_els(&$elar);

				foreach($elar as $el)
				{
					if ($el->get_type() == "button")
					{
						$lcol6 = "";
						foreach($langs as $lar)
						{
							if ($lar["id"] != $this->lang_id)
							{
								$txt = $el->arr["lang_button_text"][$lar["id"]];
							}
							else
							{
								$txt = $el->arr["button_text"];
							}
							$this->vars(array(
								"text" => $txt,
								"col" => $col,
								"row" => $row,
								"elid" => $el->get_id(),
								"lang_id" => $lar["id"],
							));
							$lcol6.=$this->parse("LCOL6");
						}
						$this->vars(array("LCOL6" => $lcol6));
						$lrow6.=$this->parse("LROW6");
					}
				}
			}
		}
		$this->vars(array(
			"LROW" => $lrow,
			"LROW1" => $lrow1,
			"LROW2" => $lrow2,
			"LROW3" => $lrow3,
			"LROW4" => $lrow4,
			"LROW5" => $lrow5,
			"LROW6" => $lrow6,
			"reforb" => $this->mk_reforb("submit_translate", array("id" => $id))
		));

		return $this->do_menu_return();
	}

	function submit_translate($arr)
	{
		extract($arr);
		$this->load($id);

		classload("languages");
		$la = new languages;
		$langs = $la->listall();

		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$elar = array();
				$this->arr["contents"][$row][$col]->get_els(&$elar);

				foreach($elar as $el)
				{
					foreach($langs as $lar)
					{
						$this->arr["elements"][$row][$col][$el->get_id()]["lang_text"][$lar["id"]] = $r[$row][$col][$lar["id"]][$el->get_id()];
						$this->arr["elements"][$row][$col][$el->get_id()]["lang_info"][$lar["id"]] = $s[$row][$col][$lar["id"]][$el->get_id()];
						$this->arr["elements"][$row][$col][$el->get_id()]["lang_default"][$lar["id"]] = $d[$row][$col][$lar["id"]][$el->get_id()];
						$this->arr["elements"][$row][$col][$el->get_id()]["lang_must_error"][$lar["id"]] = $e[$row][$col][$lar["id"]][$el->get_id()];
						if ($el->get_type() == "button")
						{
							$this->arr["elements"][$row][$col][$el->get_id()]["lang_button_text"][$lar["id"]] = $b[$row][$col][$lar["id"]][$el->get_id()];
						}
					}
					if ($el->get_type() == "listbox")
					{
						foreach($langs as $lar)
						{
							for ($i=0; $i < $el->arr["listbox_count"]; $i++)
							{
								$this->arr["elements"][$row][$col][$el->get_id()]["listbox_lang_items"][$lar["id"]][$i] = $l[$row][$col][$lar["id"]][$el->get_id()][$i];
							}
						}
					}
					else
					if ($el->get_type() == "multiple")
					{
						foreach($langs as $lar)
						{
							for ($i=0; $i < $el->arr["multiple_count"]; $i++)
							{
								$this->arr["elements"][$row][$col][$el->get_id()]["multiple_lang_items"][$lar["id"]][$i] = $m[$row][$col][$lar["id"]][$el->get_id()][$i];
							}
						}
					}
				}
			}
		}
		$this->save();

		return $this->mk_my_orb("translate", array("id" => $id));
	}

	function convformat()
	{
		set_time_limit(0);
		$this->db_query("SELECT oid FROM objects WHERE class_id = ".CL_FORM." AND status != 0");
		while ($row = $this->db_next())
		{
			echo "form $row[oid] \n<br>";
			flush();
			$f = new form;
			$f->load($row["oid"]);
			$f->save();
		}

		classload("form_output");
		$this->db_query("SELECT oid FROM objects WHERE class_id = ".CL_FORM_OUTPUT." AND status != 0");
		while ($row = $this->db_next())
		{
			echo "form_op $row[oid] \n<br>";
			flush();
			$f = new form_output;
			$f->load_output($row["oid"]);
			$f->save_output($row["oid"]);
		}
	}
};	// class ends
?>
