<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form.aw,v 2.34 2001/07/12 04:23:45 kristo Exp $
// form.aw - Class for creating forms
lc_load("form");
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
		lc_load("definition");
	}

	////
	// !Alias parser
	function parse_alias($args = array())
	{
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
		return $replacement;
	}
			
	////
	// !Generates form admin interface
	// $arr[id] - form id, required
	function gen_grid($arr)
	{
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
					if (!$this->can("delete",$v["id"]))
						$fl = false;
			}
			$this->vars(array("form_col" => $a,
												"del_col"		=> $this->mk_orb("del_col",array("id" => $this->id, "col" => $a))));
			$cd = "";
			if ($fl == true)
				$cd = $this->parse("DELETE_COL");

			$this->vars(array("FIRST_C" => $fi, "DELETE_COL" => $cd,
												"add_col"	=> $this->mk_orb("add_col", array("id" => $this->id, "count" => 1, "after" => $a))));
			$this->parse("DC");
		}

		for ($i=0; $i < $this->arr["rows"]; $i++)
		{
			$cols="";
			$fl = true;
			for ($a=0; $a < $this->arr["cols"]; $a++)
			{
				if (!($arr = $this->get_spans($i, $a)))
					continue;
				
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

				$this->vars(array("SPLIT_HORIZONTAL" => $sh, "SPLIT_VERTICAL" => $sv, "EXP_UP" => $eu, "EXP_LEFT" => $el, "EXP_RIGHT" => $er,"EXP_DOWN" => $ed));
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
		return $this->do_menu_return();
	}

	////
	// !Shows all form elements and lets user pick their style
	function gen_all_elements($arr)
	{
		extract($arr);
		$this->init($id, "all_elements.tpl", LC_FORM_ALL_ELEMENTS);

		classload("style");
		$style = new style;
		$stylesel = $style->get_select(0,ST_CELL);

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
					"row"						=> $arr["r_row"]
				));	

				if ($el == "")
				{
					$se = "<img src='/images/transa.gif' height=1 width=1 border=0>";
				}
				else
				{
					$se = $this->parse("SOME_ELEMENTS");
				}

				$this->vars(array("SOME_ELEMENTS" => $se));

				$cols.=$this->parse("COL");
			}
			$this->vars(array("COL" => $cols));
			$this->parse("LINE");
		}
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_all_els", array("id" => $id)),
			"styles" => $this->picker(0,$stylesel)
		));
		return $this->do_menu_return();
	}

	////
	// !saves the selected styles from viewing all element layout
	function submit_all_els($arr)
	{
		extract($arr);

		$this->load($id);
		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				if ($chk[$row][$col] == 1)
				{
					$this->arr["contents"][$row][$col]->set_style($setstyle,&$this);
				}
			}
		}
		$this->save();
		return $this->mk_my_orb("all_elements", array("id" => $id));
	}

	////
	// !saves the table properties of the form
	function save_settings($arr)
	{
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
		$this->arr["name_el"] = $entry_name_el;
		$this->arr["try_fill"] = $try_fill;
		$this->arr["show_table"] = $show_table;
		$this->arr["table"] = $table;
		$this->save();
		return $this->mk_orb("table_settings", array("id" => $id));
	}

	//// 
	// !saves the changes the user has made in the form generated by gen_grid
	function save_grid($arr)
	{
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

		return $this->mk_orb("change",array("id" => $this->id));
	}

	////
	// !Adds $count columns after column $after in form $id
	function add_col($arr)
	{
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
		header("Location: $orb");
		return $orb;
	}
	
	////
	// !Deletes column $col of form $id
	function delete_column($arr)
	{
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
		header("Location: $orb");
		return $orb;
	}
	
	////
	// !Deletes row $row from form $id
	function delete_row($arr)
	{
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
		header("Location: $orb");
		return $orb;
	}

	// returns array id => name of all elements in the loaded form
	function get_all_elements()
	{
		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col = 0; $col < $this->arr["cols"]; $col++)
			{
				$elar = $this->arr["contents"][$row][$col]->get_elements();
				reset($elar);
				while (list(,$el) = each($elar))
				{
					$ret[$el["id"]] = $el["name"];
				}
			}
		}
		return $ret;
	}

	////
	// !Generates the form used in modifying the table settings
	function gen_settings($arr)
	{
		extract($arr);
		$this->init($id,"settings.tpl", LC_FORM_CHANGE_SETTINGS);

		classload("style");
		$t = new style;
		$o = new db_objects;
		$menulist = $o->get_list();
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
			"els"									=> $this->picker($this->arr["name_el"],$this->get_all_elements()),
			"try_fill"						=> checked($this->arr["try_fill"]),
			"show_table_checked" => checked($this->arr["show_table"]),
			"tables" => $this->picker($this->arr["table"],$this->get_list_tables()),
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
	function gen_preview($arr)
	{
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
		$form_action = $baseurl.$form_action;

		if (!isset($reforb) || $reforb == "")
		{
			$reforb = $this->mk_reforb("process_entry", array("id" => $this->id));
		}
		if (isset($entry_id) && $entry_id)
		{
			$this->load_entry($entry_id);
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
			$html=$this->mk_row_html($i,$prefix,$elvalues);
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
			"form_border"				=> (isset($this->arr["border"]) && $this->arr["border"] != "" ? " BORDER='".$this->arr["border"]."'" : ""),
			"form_bgcolor"			=> (isset($this->arr["bgcolor"]) && $this->arr["bgcolor"] !="" ? " BGCOLOR='".$this->arr["bgcolor"]."'" : ""),
			"form_cellpadding"	=> (isset($this->arr["cellpadding"]) && $this->arr["cellpadding"] != "" ? " CELLPADDING='".$this->arr["cellpadding"]."'" : ""),
			"form_cellspacing"	=> (isset($this->arr["cellspacing"]) && $this->arr["cellspacing"] != "" ? " CELLSPACING='".$this->arr["cellspacing"]."'" : ""),
			"form_height"				=> (isset($this->arr["height"]) && $this->arr["height"] != "" ? " HEIGHT='".$this->arr["height"]."'" : ""),
			"form_width"				=> (isset($this->arr["width"]) && $this->arr["width"] != "" ? " WIDTH='".$this->arr["width"]."'" : "" ),
			"form_height"				=> (isset($this->arr["height"]) && $this->arr["height"] != "" ? " HEIGHT='".$this->arr["height"]."'" : "" ),
			"form_vspace"				=> (isset($this->arr["vspace"]) && $this->arr["vspace"] != "" ? " VSPACE='".$this->arr["vspace"]."'" : ""),
			"form_hspace"				=> (isset($this->arr["hspace"]) && $this->arr["hspace"] != "" ? " HSPACE='".$this->arr["hspace"]."'" : ""),
			"form_action"				=> $form_action,
			"submit_text"				=> isset($this->arr["submit_text"]) ? $this->arr["submit_text"] : "",
			"reforb"						=> $reforb,
			"checks"						=> $chk_js
		));
		$st = $this->parse();				
		return $st;
	}

	////
	// !generates one row of form elements
	function mk_row_html($row,$prefix = "",$elvalues = array(),$no_submit = false)
	{
		$html = "";
		for ($a=0; $a < $this->arr["cols"]; $a++)
		{
			if (($arr = $this->get_spans($row, $a)))
			{
				$ds = isset($this->arr["def_style"]) ? $this->arr["def_style"] : 0;
				$html.=$this->arr["contents"][$arr["r_row"]][$arr["r_col"]]->gen_user_html_not($ds,$arr["colspan"], $arr["rowspan"],$prefix,$elvalues,$no_submit);
			}
		}
		return $html;
	}

	////
	// !saves the entry for the form $id, if $entry_id specified, updates it instead of creating a new one
	// elements are assumed to be prefixed by $prefix
	// optional argument $chain_entry_id - if creating a new entry and it is specified, the entry is created with that chain entry id
	function process_entry($arr)
	{
		extract($arr);
		$this->load($id);

		if (!$entry_id)
		{
			$parent = isset($parent) ? $parent: $this->arr["ff_folder"];
			$entry_id = $this->new_object(array("parent" => $parent, "form_entry", "class_id" => CL_FORM_ENTRY));
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
				$this->arr["contents"][$i][$a] -> process_entry(&$this->entry, $entry_id,$prefix);
			}
		}

		if ($this->arr["name_el"])
		{
			$this->upd_object(array("oid" => $entry_id, "name" => $this->get_element_value($this->arr["name_el"]),"comment" => ""));
		}
		$en = serialize($this->entry);
		if ($new)
		{
			$this->db_query("insert into form_entries values($entry_id, $this->id, ".time().", '$en')");

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
				$ids.=",el_$k";
				// see on pildi uploadimise elementide jaoks
				if (is_array($v))
				{
					$v = serialize($v);
				}
				$vals.=",'$v'";
			}
			$sql = "INSERT INTO form_".$this->id."_entries($ids) VALUES($vals)";
			$this->db_query($sql);

			$this->_log("form","T&auml;itis formi $this->name");
			$this->do_actions($entry_id);
		}
		else
		{
			$this->db_query("update form_entries set form_entries.tm = ".time().", contents = '$en' where id = $entry_id");
			// create sql 
			reset($this->entry);
			$ids = "id = $entry_id";
			$first = true;
			while (list($k, $v) = each($this->entry))
			{
				if (is_array($v))
				{
					$v = serialize($v);
				}
				$ids.=",el_$k = '$v'";
			}
			$sql = "update form_".$this->id."_entries set $ids where id = $entry_id";
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
				$l = $this->mk_my_orb("show_entry", array("id" => $id, "entry_id" => $entry_id, "op_id" => 1));
				break;
			default:
				if ($this->type == FTYPE_SEARCH)
				{
					// n2itame ocingu tulemusi
					$l = $this->mk_my_orb("show_entry", array("id" => $id, "entry_id" => $entry_id,"op_id" => 1));
				}
				else
				{
					$l = $this->mk_my_orb("show", array("id" => $id, "entry_id" => $entry_id));
				}
				break;
		}
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

	// laeb entry parajasti aktiivse vormi jaoks
	function load_entry($entry_id)
	{
		$this->entry_id = $entry_id;

		$id = $this->id;

		$q = "SELECT * FROM form_".$id."_entries WHERE id = $entry_id";
		$this->db_query($q);

		if (!($row = $this->db_next()))
		{
			$this->raise_error(sprintf(E_FORM_NO_SUCH_ENTRY,$entry_id,$id),true);
		};

		$row["form_".$id."_entry"] = $crow["id"];

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
	}

	function load_entry_from_data($row,$entry_id,$eids = array())
	{
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
	}

	////
	// !shows entry $entry_id of form $id using output $op_id
	// if $no_load_entry == true, the loaded entry is used
	function show($arr)
	{
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
			return $this->do_search($entry_id, $op_id);
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

		if (isset($admin) && $admin)
		{
			$this->read_template("show_user_admin.tpl");

			$menunames = array();
			$this->db_query("SELECT objects.oid as oid, 
															objects.name as name
												FROM objects 
												WHERE objects.class_id = 13 AND objects.status != 0 AND objects.last = $this->id");
			while ($row = $this->db_next())
				$menunames[$row["oid"]] = $row["name"];


			$actioncache = array(); $ac = ""; $acc = "";
			$this->db_query("SELECT form_actions.*,objects.name as name FROM form_actions 
											 LEFT JOIN objects ON objects.oid = form_actions.id
											 WHERE form_id = $this->id AND type='move_filled'");
			while ($row = $this->db_next())
			{
				$row["data"] = unserialize($row["data"]);
				if (is_array($row["data"]))
				{
					$this->vars(array("colspan" => sizeof($row["data"]),"action_name" => $row["name"]));
					$ac.=$this->parse("ACTIONS");

					reset($row["data"]);
					while (list($k,$v) = each($row["data"]))
					{
						$this->vars(array("action_target" => $k,"action_target_name" => $menunames[$k],"parent" => $k,"op_id" => $output_id));
						$acc.=$this->parse("ACTION_LINE");
					}
				}
			}
			$this->vars(array("ACTION_LINE" => $acc, "ACTIONS" => $ac,"op_id" => $output_id,"parent" => $this->id));
		}
		else
		{
			if (isset($no_html) && $no_html)
			{
				$this->read_template("show_user_nohtml.tpl");
			}
			else
			{
				$this->read_template("show_user.tpl");
			}
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
					$el = $this->get_element_by_id($op_cell["els"][$i]);
					if (!$el)
					{
						// j2relikult on see element m6nes teises selle outputi formis. et siis tuleb loadida k6ik selle 
						// outputiga seotud formid ja nende seest elementi otsida
						if (!$op_other_forms_loaded)
						{
							$op_far = $this->get_op_forms($op_id);
							$op_forms = array();
							foreach($op_far as $op_fid)
							{
								$op_forms[$op_fid] = new form;
								$op_forms[$op_fid]->load($op_fid);
								// setime entry data ka
								for ($orow=0; $orow < $op_forms[$op_fid]->arr["rows"]; $orow++)
								{
									for ($ocol=0; $ocol < $op_forms[$op_fid]->arr["cols"]; $ocol++)
									{
										$op_forms[$op_fid]->arr["contents"][$orow][$ocol] -> set_entry(&$this->entry, $entry_id,&$op_forms[$op_fid]);
									};
								};
							}

							$op_other_forms_loaded = true;
						}

						foreach($op_forms as $op_fid => $op_form)
						{
							if (!$el)
							{
								$el = $op_form->get_element_by_id($op_cell["els"][$i]);
							}
						}

					}

					if ($el)
					{
						$chtml.= $el->gen_show_html();
					}
				}

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
			}
			$this->vars(array("COL" => $html));
			$this->parse("LINE");
		}

		// uurime v2lja outputi tabeli stiili ja kasutame seda
		//$this->output["table_style"] = 12220;
		//print $t_style->get_table_string($this->output["table_style"]);
		if ($this->output["table_style"])
		{
			$this->vars(array(
				"tablestring" => $t_style->get_table_string($this->output["table_style"])
			));
		}
		return $this->parse();
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
			$this->vars(array(
				"form_name"	=> $row["name"], 
				"form_comment" => $row["comment"], 
				"form_location" => $row["parent"], 
				"form_id" => $row["oid"],
				"row"	=> $cnt,
				"checked" => checked($this->arr["search_from"][$row["oid"]] == 1),
				"prev" => $this->arr["search_from"][$row["oid"]],
				"ops" => $this->picker($this->arr["search_outputs"][$row["oid"]],$ops[$row["oid"]])
			));
			$this->parse("LINE");
			$cnt+=2;
		}

		$this->vars(array(
			"reforb"	=> $this->mk_reforb("save_search_sel", array("id" => $this->id,"page" => $page))
		));

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
		
		$this->save();
		return $this->mk_orb("sel_search", array("id" => $id,"page" => $page));
	}

	// does the actual searching part and returns
	// an array, that has one entry for each form selected as a search target
	// and that entry is an array of matching entries for that form
	// parent(int) - millise parenti alt entrysid otsida
	function search($entry_id,$parent = 0)
	{
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

		// loop through all the forms that are selected as search targets
		while (list($id,$v) = each($this->arr["search_from"]))
		{
			if ($v != 1)		// search only selected forms
			{
				continue;
			}

			// create the sql that searches from this form's entries
			$query="SELECT * FROM form_".$id."_entries LEFT JOIN objects ON objects.oid = form_".$id."_entries.id WHERE objects.status !=0 ";
			if (is_array($parent))
			{
				$query .= sprintf(" AND objects.parent IN (%s)",join(",",$parent));
			}

			// loop through all the elements of this form 
			reset($els);
			while( list(,$el) = each($els))
			{
				if ($el->arr["linked_form"] == $id)	// and use only the elements that are members of the current form in the query
				{
					// oh la la
					if ($this->entry[$el->get_id()] != "")	
					{
						$query.= "AND el_".$el->arr["linked_element"]." like '%".$this->entry[$el->get_id()]."%' ";
					}
				}
			}

			if ($query == "")
			{
				$query = "SELECT * FROM form_".$id."_entries";
			}

			$matches = array();
			$this->db_query($query);
			while ($row = $this->db_next())
			{
				$matches[] = $row["id"];
				$this->cached_results[$id][$row["oid"]] = $row;
			}

			$ret[$id] = $matches;
		}
	
		return $ret;
	}

	function do_search($entry_id, $output_id)
	{
		$matches = $this->search($entry_id);
		if ($this->arr["show_table"])
		{
			if (!$this->arr["table"])
			{
				$this->raise_error("No table selected for showing data!",true);
			}

			classload("form_table");
			$ft = new form_table;
			$xml = $ft->get_xml($this->arr["table"]);

			// siin peame k6igepealt genereerima tabelit defineeriva xmli
			// siis selle tabeligenekale ette s88tma
			// ja siis rea kaupa data talle ette s88tma
			load_vcl("table");
			$t = new aw_table(array(
				"prefix" => "fg_".$entry_id,
				"self" => $PHP_SELF,
				"imgurl" => $baseurl . "/automatweb/images"
			));
			$t->parse_xml_def_string($xml);
			$t->set_header_attribs(array("class" => "form", "action" => "show_entry", "id" => $this->id, "entry_id" => $entry_id, "op_id" => $output_id));

			classload("objects");
			$ob = new db_objects;
			$li = $ob->get_list();
			$movetoar = array();
			if (is_array($ft->table["moveto"]))
			{
				foreach($ft->table["moveto"] as $mid)
				{
					$movetoar[$mid] = $li[$mid];
				}
			}
			$movetoar["0"] = "  ";
			$form = new form;
			reset($matches);
			while(list($fid,$v) = each($matches))
			{
				$form->load($fid);
				foreach($v as $eid)
				{
					$form->load_entry($eid);
					$eobj = $this->get_object($eid);

					$rds = array();
					if ($form->chain_entry_id)
					{
						$chain_id = $this->db_fetch_field("SELECT chain_id FROM form_chain_entries WHERE id = ".$this->chain_entry_id,"chain_id");
						$rds["el_change"] = "<a href='".$this->mk_my_orb("show", array("id" => $chain_id,"entry_id" => $this->chain_entry_id), "form_chain")."'>Muuda</a>";
					}
					else
					{
						$rds["el_change"] = "<a href='".$this->mk_my_orb("change", array("id" => $eid), "form_entry")."'>Muuda</a>";
					}
					
					$rds["el_view"] = "<a href='".$this->mk_my_orb("show_entry", array("id" => $fid,"entry_id" => $eid, "op_id" => $this->arr["search_outputs"][$fid]))."'>Vaata</a>";
					$rds["el_delete"] = "<a href='".$this->mk_my_orb(
						"delete_entry", 
							array(
								"id" => $fid,
								"entry_id" => $eid, 
								"after" => $this->binhex($this->mk_my_orb("show_entry", array("id" => $this->id, "entry_id" => $entry_id, "op_id" => $output_id)))
							)
						)."'>Kustuta</a>";
					$rds["el_created"] = $this->time2date($eobj["created"],2);
					$rds["el_modified"] = $this->time2date($eobj["modified"],2);
					$rds["el_createdby"] = $this->time2date($eobj["uid"],2);
					$rds["el_active"] = "<input type='checkbox' name='active[".$fid."][".$eid."]' value='1' ".(checked($eobj["status"]==2))."><input type='hidden' name='old_active[".$fid."][".$eid."]' value='".$eobj["status"]."'>";
					if (is_array($ft->table["moveto"]))
					{
						$rds["el_chpos"] = "<select name='chpos[".$fid."][".$eid."]>".$this->picker((in_array($eobj["parent"],$ft->table["moveto"]) ? $eobj["parent"] : 0),$movetoar)."</select><input type='hidden' name='old_pos[".$fid."][".$eid."]' value='".$eobj["parent"]."'>";
					}
					for ($row = 0; $row < $form->arr["rows"]; $row++)
					{
						for ($col = 0; $col < $form->arr["cols"]; $col++)
						{
							$form->arr["contents"][$row][$col]->get_els(&$elar);
							reset($elar);
							while (list(,$el) = each($elar))
							{
								$rds["el_".$el->get_id()] = htmlspecialchars($el->get_value());
							}
						}
					}
					$t->define_data($rds);
				}
			}
			$t->sort_by(array("field" => $GLOBALS["sortby"],"sorder" => $GLOBALS["sort_order"]));
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
			$tbl.=$t->draw();

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
			return $tbl;
		}
		else
		{
			// n2itame sisestusi lihtsalt yxteise j2rel 
			reset($matches);
			while(list($fid,$v) = each($matches))
			{
				$t = new form();
				reset($v);
				while (list(,$eid) = each($v))
				{
					$t->reset();
					$html.=$t->show(array("id" => $fid, "entry_id" => $eid, "op_id" => $this->arr["search_outputs"][$fid]));
				}
			}
		}
	
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
		$this->vars(array("reforb"	=> $this->mk_reforb("submit_add",array("parent" => $parent, "alias_doc" => $alias_doc))));
		return $this->parse();
	}

	function submit_add($arr)
	{
		extract($arr);

		$id = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_FORM, "comment" => $comment));
		
		if ($type == "entry") 
			$type = FORM_ENTRY;
		if ($type == "search")
			$type = FORM_SEARCH;
		if ($type == "rating")
			$type = FORM_RATING;

		$this->db_query("INSERT INTO forms(id, type,content,cols,rows) VALUES($id, $type,'',1,1)");

		$this->db_query("CREATE TABLE form_".$id."_entries (id int primary key,chain_id int)");

		$this->load($id);

		$this->_log("form",LC_FORM_ADDED_FORM.$name);

		if ($alias_doc)
		{
			$this->add_alias($alias_doc, $id);
		}

		return $this->mk_orb("change", array("id" => $id));
	}

	// here we must make a list of all the filled forms, that include element with id $arr[id]
	function list_el_forms($arr)
	{
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
			if ($ar["can_add"])	// only object types that can be added anywhere
				$arr[$id] = $ar["name"];
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
		for ($row = 0; $row < $this->arr["rows"]; $row++)
		{
			for ($col = 0; $col < $this->arr["cols"]; $col++)
			{
				$this->arr["contents"][$row][$col]->get_els(&$elar);
				while (list(,$el) = each($elar))
				{
					if ($el->get_id() == $id)
					{
						return $el;
					}
				}
			}
		}
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
		return $retval;
	}
	////
	// !Teeb paringu entryte saamiseks laaditud vormi juures
	function get_entries($args = array())
	{
		extract($args);
		// kui parent on antud, siis moodustame sellest IN klausli
		$pstr = ($parent) ? " WHERE objects.parent IN (" . join(",",map("'%s'",$parent)) . ")" : "";
		$table = sprintf("form_%d_entries",$this->id);
		$q = "SELECT * FROM $table LEFT JOIN objects ON ($table.id = objects.oid) $pstr";
		$this->db_query($q);
	}

	////
	// !finds the first element with type $type (and subtype $subtype) in the loaded form and returns a reference to it
	function get_element_by_type($type,$subtype = "")
	{
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
						return $el;
					}
				}
			}
		}
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
					$new_el = $this->new_object(array("parent" => $oldel_ob["parent"], "name" => $name, "class_id" => CL_FORM_ELEMENT));
//						$this->db_query("INSERT INTO menu (id,type) values($new_el,".MN_FORM_ELEMENT.")");
					$this->db_query("INSERT INTO form_elements (id) VALUES($new_el)");

					// we must also update the form_$id_entries table
					$this->db_query("ALTER TABLE form_".$id."_entries add el_$new_el text");

					$this->db_query("INSERT INTO element2form(el_id,form_id) values($new_el,$id)");
					$arr = $oldel;
					$arr["id"] = $new_el;
					$arr["name"] = $name;
					$this->arr[elements][$r][$c][$new_el] = $arr;
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
		$el = $this->get_element_by_name($name);
		if (!$el)
		{
			return false;
		}

		return $el->get_value();
	}

	////
	// !returns the value of the entered element. finds the first element of $type (and $subtype)  and 
	// ignores the rest. form entry must be loaded before calling this.
	function get_element_value_by_type($type,$subtype = "")
	{
		$el = $this->get_element_by_type($type,$subtype);
		if (!$el)
		{
			return false;
		}

		return $el->get_value();
	}

	////
	// !returns the value of element with id $id
	function get_element_value($id)
	{
		$el = $this->get_element_by_id($id);
		if ($el)
		{
		return $el->get_value();
		
	}
	return "";
	}

	////
	// !sets the element $id's value in the loaded entry to $val
	function set_element_value($id,$val)
	{
		$this->entry[$id] = $val;
		for ($row=0; $row < $this->arr["rows"]; $row++)
		{
			for ($col=0; $col < $this->arr["cols"]; $col++)
			{
				$this->arr["contents"][$row][$col] -> set_entry(&$this->entry, $this->entry_id,&$this);
			};
		};
	}

	////
	// !sets the element value in the loaded entry to $val fort elements of type $type
	function set_element_value_by_type($type,$val)
	{
		$el = $this->get_element_by_type($type);
		if ($el)
		{
			$id = $el->get_id();
			$this->set_element_value($id,$val);
		}
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
		return $ret;
	}

	////
	// returns the entry in an array that you can feed to restore_entry to revert the saved entry to the old data
	function get_entry($form_id,$entry_id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM form_".$form_id."_entries WHERE id = $entry_id");
		$row =  $this->db_next();
		if ($row)
		{
			foreach($row as $k => $v)
			{
				if (substr($k,0,3) == "el_")
				{
					$ret[$k] = $v;
				}
			}
		}
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

	////
	// !lets the user select all folders for the form
	function set_folders($arr)
	{
		extract($arr);
		$this->init($id,"settings_folders.tpl", LC_FORM_CHANGE_FOLDERS);
		
		$o = new db_objects;
		$menulist = $o->get_list();
		$this->vars(array(
			"ff_folder"	=> $this->picker($this->arr["ff_folder"], $menulist),
			"el_menus" => $this->multiple_option_list($this->arr["el_menus"], $menulist),
			"el_menus2" => $this->multiple_option_list($this->arr["el_menus2"], $menulist),
			"reforb"	=> $this->mk_reforb("save_folders", array("id" => $id))
		));
		return $this->do_menu_return();
	}

	function save_folders($arr)
	{
		extract($arr);
		$this->load($id);
		$this->arr["ff_folder"] = $ff_folder;
		$this->arr["el_menus"] = "";
		if (is_array($el_menus))
		{
			foreach($el_menus as $menuid)
			{
				$this->arr["el_menus"][$menuid] = $menuid;
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
		$ret = array();
		if (is_array($this->arr["search_from"]))
		{
			foreach ($this->arr["search_from"] as $fid => $one)
			{
				if ($one == 1)
				{
					$ret[$fid] = $this->arr["search_outputs"][$fid];
				}
			}
		}
		return $ret;
	}
};	// class ends
?>
