<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_element.aw,v 2.31 2001/10/26 15:20:57 duke Exp $
// form_element.aw - vormi element.
lc_load("form");
global $orb_defs;
$orb_defs["form_element"] = 
array("change"	=> array("function"	=> "change",	"params"	=> array("id")));

class form_element extends aw_template
{
	function form_element()
	{
		// this is an abstract class and this constructor will never be called
	}

	////
	// !Loads the element from the array from inside the form
	function load(&$arr,&$form,$col,$row)
	{
		$this->form = &$form;
		$this->arr = $arr;
		$this->id = $arr["id"];
		$this->fid = $form->get_id();
		$this->col = $col;
		$this->row = $row;
	}


	function do_core_admin()
	{
			$this->vars(array("cell_id"									=> "element_".$this->id, 
												"cell_text"								=> htmlentities($this->arr["text"]),
												"cell_name"								=> htmlentities($this->arr["name"]),
												"cell_type_name"					=> htmlentities($this->arr["type_name"]),
												"cell_dist"								=> htmlentities($this->arr["text_distance"]),
												"type_active_textbox" 		=> ($this->arr["type"] == "textbox" ? " SELECTED " : ""),
												"type_active_textarea" 		=> ($this->arr["type"] == "textarea" ? " SELECTED " : ""),
												"type_active_checkbox" 		=> ($this->arr["type"] == "checkbox" ? " SELECTED " : ""),
												"type_active_radiobutton" => ($this->arr["type"] == "radiobutton" ? " SELECTED " : ""),
												"type_active_listbox" 		=> ($this->arr["type"] == "listbox" ? " SELECTED " : ""),
												"type_active_multiple"		=> ($this->arr["type"] == "multiple" ? " SELECTED " : ""),
												"type_active_file"				=> ($this->arr["type"] == "file" ? " SELECTED " : ""),
												"type_active_link"				=> ($this->arr["type"] == "link" ? " SELECTED " : ""),
												"type_active_button"			=> ($this->arr["type"] == "button" ? " SELECTED " : ""),
												"type_active_price"				=> ($this->arr["type"] == "price" ? " SELECTED " : ""),
												"type_active_date"				=> ($this->arr["type"] == "date" ? " SELECTED " : ""),
												"default_name"						=> "element_".$this->id."_def",
												"default"									=> htmlentities($this->arr["default"]),
												"cell_info"								=> htmlentities($this->arr["info"]),
												"front_checked"						=> checked($this->arr["front"] == 1),
												"cell_order"							=> $this->arr["ord"],
												"type"										=> $this->arr["type"],
												"sep_enter_checked"				=> ($this->arr["sep_type"] == 1 ? " CHECKED " : "" ),
												"sep_space_checked"				=> ($this->arr["sep_type"] != 1 ? " CHECKED " : "" ),
												"cell_sep_pixels"					=> $this->arr["sep_pixels"],
												"element_id"							=> $this->id,
												"text_pos_up"							=> ($this->arr["text_pos"] == "up" ? "CHECKED" : ""),
												"text_pos_down"						=> ($this->arr["text_pos"] == "down" ? "CHECKED" : ""),
												"text_pos_left"						=> ($this->arr["text_pos"] == "left" ? "CHECKED" : ""),
												"text_pos_right"					=> ($this->arr["text_pos"] == "right" ? "CHECKED" : ""),
												"length"									=> $this->arr["length"],
												"srow_grp"								=> $this->arr["srow_grp"],
												"changepos"								=> $this->mk_orb("change_el_pos",array("id" => $this->fid, "col" => $this->col, "row" => $this->row, "el_id" => $this->id), "form"),
												"ignore_text" => checked($this->arr["ignore_text"])
			));

			$cd = "";
			$cd = $this->parse("CAN_DELETE");

			$li = ""; $hl = ""; $hl2 = "";
			if ($this->arr["type"] == "link")
			{
				$this->vars(array(
					"link_text"			=> $this->arr["link_text"],
					"link_address"	=> $this->arr["link_address"],
					"subtypes" => $this->picker($this->arr["subtype"], array("" => "","show_op" => "N&auml;ita pikemalt"))
				));
				$li = $this->parse("HLINK_ITEMS");
				$this->vars(array("HAS_SUBTYPE" => $this->parse("HAS_SUBTYPE")));
			}
			else
			{
				$hl2 = $this->parse("EL_NOHLINK");
			}

			if ($this->arr["type"] == "link" && $this->arr["subtype"] == "show_op")
			{
				$ops = $this->form->get_op_list($this->form->id);
				$this->vars(array(
					"ops" => $this->picker($this->arr["link_op"], $ops[$this->form->id])
				));
				$hl = $this->parse("EL_HLINK");
			}

			$this->vars(array("EL_HLINK" => $hl, "EL_NOHLINK" => $hl2));
			$fi = "";
			if ($this->arr["type"] == "file")
			{
				$this->vars(array("ftype_image_selected"	=> ($this->arr["ftype"] == 1 ? "CHECKED" : ""),
													"ftype_file_selected"		=> ($this->arr["ftype"] == 2 ? "CHECKED" : ""),
													"file_link_text"				=> $this->arr["flink_text"],
													"file_show"							=> ($this->arr["fshow"] == 1 ? "CHECKED" : ""),
													"file_alias"						=> ($this->arr["fshow"] != 1 ? "CHECKED" : "")));
				$fi = $this->parse("FILE_ITEMS");
			}

			$lb = "";
			if ($this->arr["type"] == "listbox")		
			{	
				$this->vars(array(
					"must_fill_checked" => checked($this->arr["must_fill"] == 1),
					"must_error" => $this->arr["must_error"],
					"lb_size" => $this->arr["lb_size"],
					"subtypes" => $this->picker($this->arr["subtype"], array("" => "","relation" => "Seoseelement"))
				));
				$this->vars(array("HAS_SIMPLE_CONTROLLER" => $this->parse("HAS_SIMPLE_CONTROLLER")));
				for ($b=0; $b < ($this->arr["listbox_count"]+1); $b++)
				{
					$this->vars(array(
						"listbox_item_id" 			=> "element_".$this->id."_lb_".$b,
						"listbox_item_value"		=> $this->arr["listbox_items"][$b],
						"listbox_radio_name"		=> "element_".$this->id."_lradio",
						"listbox_radio_value"		=> $b,
						"listbox_radio_checked"	=> checked($this->arr["listbox_default"] == $b),
						"listbox_order_name" => "element_".$this->id."_lb_order_".$b,
						"listbox_order_value" => $this->arr["listbox_order"][$b],
						"num" => $b
					));
					$lb.=$this->parse("LISTBOX_ITEMS");
				}	
				$this->vars(array("HAS_SUBTYPE" => $this->parse("HAS_SUBTYPE")));
				$relation_lb = "";
				$relation_uniq = "";
				if ($this->arr["subtype"] == "relation" && !$this->form->is_form_output)
				{
					$this->do_search_script(true);
					$this->vars(array(
						"rel_forms" => $this->picker($this->arr["rel_form"], $this->form->get_relation_targets()),
						"rel_el" => $this->arr["rel_element"],
						"unique"	=> checked($this->arr["rel_unique"] == 1)
					));
					$relation_lb = $this->parse("RELATION_LB");
					if ($this->form->type == FTYPE_SEARCH)
					{
						$relation_uniq = $this->parse("SEARCH_RELATION");
					}
				}
				$this->vars(array(
					"RELATION_LB" => $relation_lb,
					"SEARCH_RELATION" => $relation_uniq
				));
			}

			$mu = "";
			if ($this->arr["type"] == "multiple")		
			{	
				for ($b=0; $b < ($this->arr["multiple_count"]+1); $b++)
				{
					$this->vars(array(
						"multiple_item_id" 				=> "element_".$this->id."_mul_".$b,
						"multiple_item_value"			=> $this->arr["multiple_items"][$b],
						"multiple_check_name"			=> "element_".$this->id."_m_".$b,
						"multiple_check_value"		=> "1",
						"multiple_check_checked"	=> checked($this->arr["multiple_defaults"][$b] == 1),
						"multiple_order_name" => "element_".$this->id."_m_order_".$b,
						"multiple_order_value" => $this->arr["multiple_order"][$b],
						"num" => $b
					));
					$mu.=$this->parse("MULTIPLE_ITEMS");
				}	
				$this->vars(array(
					"lb_size" => $this->arr["mb_size"]
				));
			}

			if ($this->arr["type"] == "listbox" || $this->arr["type"] == "multiple")
			{
				$this->vars(array(
					"sort_by_order" => checked($this->arr["sort_by_order"]),
					"sort_by_alpha" => checked($this->arr["sort_by_alpha"])
				));
				$this->vars(array(
					"LISTBOX_SORT" => $this->parse("LISTBOX_SORT")
				));
			}

			$ta = "";
			if ($this->arr["type"] == "textarea")
			{
				$this->vars(array("textarea_cols_name"	=> "element_".$this->id."_ta_cols",
													"textarea_rows_name"	=> "element_".$this->id."_ta_rows",
													"textarea_cols"	=> $this->arr["ta_cols"],
													"textarea_rows"	=> $this->arr["ta_rows"]));
				$ta = $this->parse("TEXTAREA_ITEMS");
			}

			$gp="";
			if ($this->arr["type"] == "radiobutton")
			{
				$this->vars(array(
					"default_checked"		=> checked($this->arr["default"] == 1),
					"cell_group"				=> $this->arr["group"],
					"ch_value" => $this->arr["ch_value"]
				));
				$gp = $this->parse("RADIO_ITEMS");
			}
			
			$dt="";
			if ($this->arr["type"] == "textbox")
			{
				$this->vars(array(
					"must_fill_checked" => checked($this->arr["must_fill"] == 1),
					"must_error" => $this->arr["must_error"],
					"subtypes" => $this->picker($this->arr["subtype"], array("" => "","count" => "Mitu"))
				));
				$this->vars(array("HAS_SIMPLE_CONTROLLER" => $this->parse("HAS_SIMPLE_CONTROLLER")));
				$dt = $this->parse("DEFAULT_TEXT");
				$this->vars(array("HAS_SUBTYPE" => $this->parse("HAS_SUBTYPE")));
			}

			$dc="";
			if ($this->arr["type"] == "checkbox")
			{
				$this->vars(array(
					"default_checked"	=> checked($this->arr["default"] == 1),
					"ch_value" => $this->arr["ch_value"],
					"ch_grp" => $this->arr["ch_grp"]
				));
				$dc = $this->parse("CHECKBOX_ITEMS");
			}

			$pc="";
			if ($this->arr["type"] == "price")
			{
				classload("currency");
				$cur = new currency;
				$gl = $cur->get_list();
				$this->vars(array(
					"price"	=> $this->arr["price"],
					"price_cur" => $this->picker($this->arr["price_cur"], $gl),
					"price_sep" => $this->arr["price_sep"],
					"price_show" => $this->multiple_option_list($this->arr["price_show"], $gl)
				));
				$pc = $this->parse("PRICE_ITEMS");
			}

			$bt = "";
			if ($this->arr["type"] == "submit" || $this->arr["type"] == "reset")
			{
				$this->vars(array(
					"button_text" => $this->arr["button_text"],
					"chain_forward" => checked($this->arr["chain_forward"]==1)
				));
				$bt = $this->parse("BUTTON_ITEMS");
			}

			$bt = "";
			if ($this->arr["type"] == "button")
			{
				if ($this->arr["subtype"] == "preview")
				{
					$formb = new form_base;
					$opl = $formb->get_op_list();

					$this->vars(array(
						"bops" => $this->picker($this->arr["button_op"],$opl[$this->fid])
					));
				}
				classload("objects");
				$ob = new db_objects;
				$this->vars(array(
					"button_text" => $this->arr["button_text"],
					"subtypes" => $this->picker($this->arr["subtype"], array("" => "","submit" => "Submit", "reset" => "Reset","delete" => "Kustuta","url" => "URL","preview" => "Eelvaade","confirm" => "Kinnita")),
					"button_url" => $this->arr["button_url"],
					"chain_forward" => checked($this->arr["chain_forward"]==1),
					"chain_backward" => checked($this->arr["chain_backward"]==1),
					"folders" => $this->picker($this->arr["confirm_moveto"],$ob->get_list()),
					"redirect" => $this->arr["confirm_redirect"]
				));
				$bt = $this->parse("BUTTON_ITEMS");
				$this->vars(array(
					"HAS_SUBTYPE" => $this->parse("HAS_SUBTYPE"),
					"BUTTON_CONFIRM_TYPE" => ($this->arr["subtype"] == "confirm" ? $this->parse("BUTTON_CONFIRM_TYPE") : ""),
					"BUTTON_SUB_URL" => ($this->arr["subtype"] == "url" ? $this->parse("BUTTON_SUB_URL") : ""),
					"BUTTON_SUB_OP" => ($this->arr["subtype"] == "preview" ? $this->parse("BUTTON_SUB_OP") : "")
				));
			}

			$di = "";
			if ($this->arr["type"] == "date")
			{
				$add_types = array("60" => "Minutit", "3600" => "Tundi", "86400" => "P&auml;eva", "604800" => "N&auml;dalat", "2592000" => "Kuud");
				$d_el_os = $this->form->get_element_by_type("date","",true);
				$d_els = array();
				foreach($d_el_os as $d_el)
				{
					if ($d_el->get_id() != $this->get_id())	// do not let the user select the current element
					{
						$d_els[$d_el->get_id()] = $d_el->get_el_name();
					}
				}
				$this->vars(array(
					"from_year" => $this->arr["from_year"],
					"to_year" => $this->arr["to_year"],
					"subtypes" => $this->picker($this->arr["subtype"], array("" => "","from" => "Algus", "to" => "L&otilde;pp","expires" => "Aegumine","created" => "Loomine")),
					"def_date_num" => $this->arr["def_date_num"],
					"add_types" => $this->picker($this->arr["def_date_add"],$add_types),
					"date_now_checked" => checked($this->arr["def_date_type"] == "now"),
					"date_rel_checked" => checked($this->arr["def_date_type"] == "rel"),
					"date_rel_els" => $this->picker($this->arr["def_date_rel_el"], $d_els)
				));
				$di = $this->parse("DATE_ITEMS");
				$this->vars(array("HAS_SUBTYPE" => $this->parse("HAS_SUBTYPE")));
			}

			if ($this->form->arr["save_table"] == 1)
			{
				$tar = array("" => "");
				if (is_array($this->form->arr["save_tables"]))
				{
					foreach($this->form->arr["save_tables"] as $tbl => $tblcolel)
					{
						$tar[$tbl] = $tbl;
					}
				}
				$this->vars(array(
					"tables" => $this->picker($this->arr["table"], $tar),
					"table_col" => $this->arr["table_col"]
				));
				$this->vars(array("TABLE_LB" => $this->parse("TABLE_LB")));
			}
			$this->vars(array(
				"LISTBOX_ITEMS"		=> $lb, 
				"MULTIPLE_ITEMS"	=> $mu, 
				"TEXTAREA_ITEMS"	=> $ta,
				"RADIO_ITEMS"			=> $gp,
				"DEFAULT_TEXT"		=> $dt,
				"CHECKBOX_ITEMS"	=> $dc,
				"CAN_DELETE"			=> $cd,
				"FILE_ITEMS"			=> $fi,
				"HLINK_ITEMS"			=> $li,
				"BUTTON_ITEMS"		=> $bt,
				"PRICE_ITEMS"			=> $pc,
				"DATE_ITEMS"			=> $di
			));
	}	

	function do_core_save(&$arr)
	{
		extract($arr);

		$base = "element_".$this->id;

		$var = $base."_sort_order";
		$this->arr["sort_by_order"] = $$var;

		$var = $base."_sort_alpha";
		$this->arr["sort_by_alpha"] = $$var;

		$var = $base."_ignore_text";
		$this->arr["ignore_text"] = $$var;

		$var = $base."_table";
		$this->arr["table"] = $$var;

		$var = $base."_tbl_col";
		$this->arr["table_col"] = $$var;

		$var=$base."_text";
		$this->arr["text"] = $$var;
		$var=$base."_name";
		// check if the name has changed and if it has, then update the real object also
		if ($$var != $this->arr["name"])
		{
			$this->arr["name"] = $$var;
			$this->do_change_name($$var);
		}

		$var=$base."_type_name";
		if ($$var != $this->arr["type_name"])
		{
			$this->arr["type_name"] = $$var;
			$this->do_change_type_name($$var);
		}

		$var=$base."_list";
		$this->arr["join_list"] = $$var;
		$var=$base."_email_el";
		$this->arr["join_email"] = $$var;

		$var=$base."_type";
		if ($$var == "delete")
		{
			return false;
		}

		$this->arr["type"] = $$var;
		$var = $base."_info";
		$this->arr["info"]=$$var;
		$var=$base."_front";
		$this->arr["front"] = $$var;
		$var=$base."_dist";
		$this->arr["text_distance"] = $$var;
		$var=$base."_text_pos";
		$this->arr["text_pos"] = $$var;

		if ($this->arr["type"] == "listbox")
		{
			$arvar = $base."_sel";
			$ar = $$arvar;

			$dwtvar = $base."_lbitems_dowhat";
			$dwat = $$dwtvar;

			$var = $base."_lb_size";
			$this->arr["lb_size"] = $$var;

			$this->arr["listbox_items"] = array();
			$cnt=$this->arr["listbox_count"]+1;
			for ($b=0,$num=0; $b < $cnt; $b++)
			{
				if (!($dwat == "del" && $ar[$b] == 1))
				{
					$var=$base."_lb_".$b;
					$this->arr["listbox_items"][$num] = $$var;

					$var=$base."_lb_order_".$b;
					$this->arr["listbox_order"][$num] = $$var;
					$num++;
					if ($dwat=="add" && $ar[$b] == 1)
					{
						$this->arr["listbox_items"][$num] = " ";
						$num++;
					}
				}
			}
			while (isset($this->arr["listbox_items"][$num-1]) && ($this->arr["listbox_items"][$num-1] == ""))
			{
				$num--;
			}

			$this->arr["listbox_count"]=$num;
			$var = $base."_lradio";
			$this->arr["listbox_default"] = $$var;

			$this->import_lb_data();

			// sort listbox
			$this->sort_listbox();

			// save relation elements
			if ($this->arr["subtype"] == "relation")
			{
				$var = $base."_unique";
				$this->arr["rel_unique"] = $$var;

				// if we get here a relation has already been created, at least we can hope so :p
				$rel_changed = false;
				$var = $base."_rel_form";
				if ($$var != $this->arr["rel_form"])
				{
					$this->arr["rel_form"] = $$var;
					$rel_changed = true;
				}
				$var = $base."_rel_element";
				if ($$var != $this->arr["rel_element"])
				{
					$this->arr["rel_element"] = $$var;
					$rel_changed = true;
				}

				if ($rel_changed || !$this->arr["rel_table_id"])
				{
					// update the relation in the table
					if ($this->arr["rel_table_id"])
					{
						$this->db_query("UPDATE form_relations SET form_from = '".$this->arr["rel_form"]."' , form_to = '".$this->form->id."' , el_from = '".$this->arr["rel_element"]."' , el_to = ".$this->id." WHERE id = ".$this->arr["rel_table_id"]);
					}
					else
					{
						// make sure we got it right.
						$this->db_query("DELETE FROM form_relations WHERE form_from = ".$this->arr["rel_form"]." AND form_to = ".$this->form->id." AND el_from = ".$this->arr["rel_element"]." AND el_to = ".$this->id);

						$this->db_query("INSERT INTO form_relations (form_from,form_to,el_from,el_to) VALUES('".$this->arr["rel_form"]."','".$this->form->id."','".$this->arr["rel_element"]."','".$this->id."')");
						$this->arr["rel_table_id"] = $this->db_last_insert_id();
					}
				}
			}
		}

		if ($this->arr["type"] == "multiple")
		{
			$arvar = $base."_sel";
			$ar = $$arvar;

			$dwtvar = $base."_lbitems_dowhat";
			$dwat = $$dwtvar;

			$var = $base."_lb_size";
			$this->arr["mb_size"] = $$var;

			$this->arr["multiple_items"] = array();
			$cnt=$this->arr["multiple_count"]+1;	
			for ($b=0,$num=0; $b < $cnt; $b++)
			{
				if (!($dwat == "del" && $ar[$b] == 1))
				{
					$var=$base."_mul_".$b;
					$this->arr["multiple_items"][$num] = $$var;

					$var = $base."_m_".$b;
					$this->arr["multiple_defaults"][$num] = $$var;

					$var=$base."_m_order_".$b;
					$this->arr["multiple_order"][$num] = $$var;
					$num++;

					if ($dwat=="add" && $ar[$b] == 1)
					{
						$this->arr["multiple_items"][$num] = " ";
						$num++;
					}
				}
			}
			if ($this->arr["multiple_items"][$num-1] == "")
			{
				$num--;
			}
			$this->arr["multiple_count"]=$num;
			$this->sort_multiple();

			$this->import_m_data();
		}

		if ($this->arr["type"] == "textarea")
		{
			$var = $base."_ta_rows";
			$this->arr["ta_rows"]= $$var;
			$var = $base."_ta_cols";
			$this->arr["ta_cols"]=$$var;
		}

		if ($this->arr["type"] == "radiobutton")
		{
			$var=$base."_group";
			$this->arr["group"] = $$var;
		}

		if ($this->arr["type"] == "price")
		{
			$var=$base."_price";
			$this->arr["price"] = $$var;
			$var=$base."_length";
			$this->arr["length"] = $$var;
			$var=$base."_price_cur";
			$this->arr["price_cur"] = $$var;
			$var=$base."_price_sep";
			$this->arr["price_sep"] = $$var;
			$var=$base."_price_show";
			$this->arr["price_show"] = array();
			if (is_array($$var))
			{
				foreach($$var as $curid)
				{
					$this->arr["price_show"][$curid] = $curid;
				}
			}
		}

		if ($this->arr["type"] == "textbox" || $this->arr["type"] == "textarea" || $this->arr["type"] == "checkbox" || $this->arr["type"] == "radiobutton")
		{
			$var=$base."_def";
			$this->arr["default"] = $$var;
			$var=$base."_length";
			$this->arr["length"] = $$var;
			$var = $base."_ch_value";
			$this->arr["ch_value"] = $$var;
		}

		if ($this->arr["type"] == "checkbox")
		{
			$var = $base."_ch_grp";
			$this->arr["ch_grp"] = $$var;
		}

		if ($this->arr["type"] == "textbox" || $this->arr["type"] == "listbox")
		{
			$var=$base."_must_fill";
			$this->arr["must_fill"] = $$var;
			$var=$base."_must_error";
			$this->arr["must_error"] = $$var;
		}

		if ($this->arr["type"] == 'file')
		{
			$var=$base."_filetype";
			$this->arr["ftype"] = $$var;
			$var=$base."_file_link_text";
			$this->arr["flink_text"] = $$var;
			$var=$base."_file_show";
			$this->arr["fshow"] = $$var;
		}

		if ($this->arr["type"] == 'link')
		{
			$var=$base."_link_text";
			$this->arr["link_text"] = $$var;
			$var=$base."_link_address";
			$this->arr["link_address"] = $$var;
			$var=$base."_link_op";
			$this->arr["link_op"] = $$var;
		}

		if ($this->arr["type"] == 'date')
		{
			$var=$base."_from_year";
			$this->arr["from_year"] = $$var;
			$var=$base."_to_year";
			$this->arr["to_year"] = $$var;
			$var=$base."_def_date_type";
			$this->arr["def_date_type"] = $$var;
			$var=$base."_def_date_num";
			$this->arr["def_date_num"] = $$var;
			$var=$base."_def_date_add_type";
			$this->arr["def_date_add"] = $$var;
			$var=$base."_def_date_rel";
			$this->arr["def_date_rel_el"] = $$var;
		}

		if ($this->arr["type"] == "submit" || $this->arr["type"] == "reset" || $this->arr["type"] == "button")
		{
			$var = $base."_btext";
			$this->arr["button_text"] = $$var;

			$var = $base."_burl";
			$this->arr["button_url"] = $$var;

			$var = $base."_bop";
			$this->arr["button_op"] = $$var;

			$var = $base."_chain_forward";
			$this->arr["chain_forward"] = $$var;

			$var = $base."_chain_backward";
			$this->arr["chain_backward"] = $$var;

			$var = $base."_confirm_moveto";
			$this->arr["confirm_moveto"] = $$var;

			$var = $base."_confirm_redirect";
			$this->arr["confirm_redirect"] = $$var;
		}

		$var = $base."_separator_type";
		$this->arr["sep_type"] = $$var;

		$var = $base."_sep_pixels";
		$this->arr["sep_pixels"] = $$var;

		$var = $base."_order";
		$$var+=0;
		if ($this->arr["ord"] != $$var)
		{
			$this->arr["ord"] = $$var;
			$this->upd_object(array("oid" => $this->id, "jrk" => $$var));
		}

		$var = $base."_subtype";
		$this->arr["subtype"] = $$var;

		$var = $base."_srow_grp";
		$this->arr["srow_grp"] = $$var;
		
		return true;
	}

	function gen_check_html()
	{
		global $lang_id,$awt;
		$awt->start("form_element::gen_check_html");
		$awt->count("form_element::gen_check_html");

		if ($this->form->lang_id == $lang_id)
		{
			$mue = $this->arr["must_error"];
		}
		else
		{
			$mue = $this->arr["lang_must_error"][$lang_id];
		}
		if ($this->arr["type"] == "textbox" && isset($this->arr["must_fill"]) && $this->arr["must_fill"] == 1)
		{
			$str = "for (i=0; i < document.fm_".$this->fid.".elements.length; i++) ";
			$str .= "{ if (document.fm_".$this->fid.".elements[i].name == \"";
			$str .=$this->id;
			$str .= "\" && document.fm_".$this->fid.".elements[i].value == \"\")";
			$awt->stop("form_element::gen_check_html");
			return  $str."{ alert(\"".$mue."\");return false; }}\n";
		}
		else
		if ($this->arr["type"] == "listbox" && isset($this->arr["must_fill"]) && $this->arr["must_fill"] == 1)
		{
			$str = "for (i=0; i < document.fm_".$this->fid.".elements.length; i++) ";
			$str .= "{ if (document.fm_".$this->fid.".elements[i].name == \"";
			$str .=$this->id;
			$str .= "\" && document.fm_".$this->fid.".elements[i].selectedIndex == 0)";
			$awt->stop("form_element::gen_check_html");
			return  $str."{ alert(\"".$mue."\");return false; }}\n";
		}
		$awt->stop("form_element::gen_check_html");
		return "";
	}

	////
	// !seab vormielemendi sisu
	function set_content($args = array())
	{
		switch($this->arr["type"])
		{
			case "textbox":
				$this->arr["text"] = $args["content"];
				break;

			case "listbox":
				$this->arr["listbox_items"] = $args["content"];
				break;
		};
	}

	function get_lang_text($lid = -1)		
	{	
		if ($lid == -1)
		{
			$lid = $GLOBALS["lang_id"];
		}
		if ($this->form->lang_id == $lid)
		{
			return $this->arr["text"];
		}
		else
		{
			return $this->arr["lang_text"][$lid];
		}
	}

	function set_lang_text($lid,$txt)
	{
		$this->arr["lang_text"][$lid] = $txt;
	}

	function get_text()		
	{	
		return $this->arr["text"]; 
	}

	function get_ch_grp() { return $this->arr["ch_grp"]; }
	function get_el_name()		{	return $this->arr["name"]; }
	function get_style()	{	return $this->arr["style"]; }
	function get_type()		{	return $this->arr["type"]; }
	function get_subtype()		{	return isset($this->arr["subtype"]) ? $this->arr["subtype"] : ""; }
	function get_srow_grp()		{	return isset($this->arr["srow_grp"]) ? $this->arr["srow_grp"] : ""; }
	function get_id()			{ return $this->id;	}
	function get_order()	{ return $this->arr["ord"]; }
	function get_props()  { return $this->arr; }
	function get_type()		{	return $this->arr["type"]; }
	function get_row()		{ return $this->row; }
	function get_col()		{ return $this->col; }
	function get_el_group()		{ return $this->arr["group"]; }
	function get_related_form() { return $this->arr["rel_form"]; }
	function get_related_element() { return $this->arr["rel_elelement"]; }
	function get_el_lb_items()	
	{
		return $this->arr["listbox_items"];
	} 

	////
	// !returns the name of table that the data from this element should be written to
	function get_save_table()
	{
		if ($this->form->arr["save_table"] == 1)
		{
			return $this->arr["table"];
		}
		else
		{
			return "form_".$this->form->id."_entries";
		}
	}

	////
	// !returns the name of column that the data from this element should be written to
	function get_save_col()
	{
		if ($this->form->arr["save_table"] == 1)
		{
			return $this->arr["table_col"];
		}
		else
		{
			return "ev_".$this->id;
		}
	}

	function save_short()
	{
		$var = "element_".$this->id."_text";
		global $$var;
		if (isset($$var))
		{
			$this->arr["text"] = $$var;
			$this->dequote($this->arr["text"]);

			$var = "element_".$this->id."_order";
			global $$var;
			$$var+=0;
			if ($this->arr["ord"] != $$var)
			{
				$this->arr["ord"] = $$var;
				$this->upd_object(array("oid" => $this->id, "jrk" => $$var));
			}
		};

		$var = "element_".$this->id."_name";
		global $$var;
		if ($$var != $this->arr["name"])
		{
			$this->arr["name"] = $$var;
			$this->do_change_name($$var);
		}
	}

	function do_change_name($name,$id = -1)
	{
		if ($id == -1)
		{
			$id = $this->id;
		}
		$this->upd_object(array("oid" => $id, "name" => $name));
		// ok now here we must fuckin load all the forms that contain this element and fuckin change all elements names in those. 
		// shit I hate this but I suppose it's gotta be done
		$this->save_handle();
		$this->db_query("SELECT * FROM element2form WHERE el_id = ".$id);
		while ($drow = $this->db_next())
		{
			$fup = new form;
			$fup->load($drow["form_id"]);
			for ($row = 0;$row < $fup->arr["rows"]; $row++)
			{
				for ($col = 0; $col < $fup->arr["cols"]; $col++)
				{
					if (is_array($fup->arr["elements"][$row][$col]))
					{
						foreach($fup->arr["elements"][$row][$col] as $k => $v)
						{
							if ($k == $id)
							{
								$fup->arr["elements"][$row][$col][$k]["name"] = $name;
							}
						}
					}
				}
			}
			$fup->save();
		}
		$this->restore_handle();
	}

	function do_change_type_name($name)
	{
		$this->db_query("UPDATE form_elements SET type_name = '$name' WHERE id = ".$this->id);

		// ok now here we must fuckin load all the forms that contain this element and fuckin change all elements typenames in those. 
		// shit I hate this but I suppose it's gotta be done
		$this->save_handle();
		$this->db_query("SELECT * FROM element2form WHERE el_id = ".$this->id);
		while ($drow = $this->db_next())
		{
			$fup = new form;
			$fup->load($drow["form_id"]);
			for ($row = 0;$row < $fup->arr["rows"]; $row++)
			{
				for ($col = 0; $col < $fup->arr["cols"]; $col++)
				{
					if (is_array($fup->arr["elements"][$row][$col]))
					{
						foreach($fup->arr["elements"][$row][$col] as $k => $v)
						{
							if ($k == $this->id)
							{
								$fup->arr["elements"][$row][$col][$k]["type_name"] = $name;
							}
						}
					}
				}
			}
			$fup->save();
		}
		$this->restore_handle();
	}

	////
	// this function deletes the element from this form only
	function del()
	{
		// if this is a relation element, remove it from the list of relations
		if ($this->arr["rel_table_id"])
		{
			$this->db_query("DELETE FROM form_relations WHERE id = '".$this->arr["rel_table_id"]."'");
		}

		// remove this form from the list of forms in which the element is
		$this->db_query("DELETE FROM element2form WHERE el_id = ".$this->id." AND form_id = ".$this->fid);

		// also remove the column for this element from the form
		$this->db_query("ALTER TABLE form_".$this->fid."_entries DROP el_".$this->id);
		$this->db_query("ALTER TABLE form_".$this->fid."_entries DROP ev_".$this->id);
	}

	function gen_action_html()
	{
		$this->read_template("admin_element_actions.tpl");
		$this->vars(array("element_id" => "element_".$this->id, "email" => $this->arr["email"], "element_text" => $this->arr["text"]));
		return $this->parse();
	}

	function set_style($id)
	{
		$this->arr["style"] = $id;
	}

	function set_entry(&$arr, $e_id)
	{
		$this->entry = $arr[$this->id];
		$this->entry_id = $e_id;
	}

	function gen_controller_html()
	{
		$this->read_template("admin_element_controllers.tpl");
		$tt = "";
		if ($this->arr["type"] == "textbox")
		{
			$this->vars(array("el_maxlen"			=> $this->arr["c_maxlen"],
												"el_minlen"			=> $this->arr["c_minlen"],
												"t_email_sel"		=> ($this->arr["c_type"] == "email" ? "SELECTED" : ""),
												"t_url_sel"			=> ($this->arr["c_type"] == "url" ? "SELECTED" : ""),
												"t_number_sel"	=> ($this->arr["c_type"] == "number" ? "SELECTED" : ""),
												"t_letter_sel"	=> ($this->arr["c_type"] == "letter" ? "SELECTED" : ""),
												"el_req_sel"		=> ($this->arr["c_req"] == 1 ? "CHECKED" : "" )));
			$tt = $this->parse("T_TEXTBOX");
		}
		$this->vars(array("element_text"	=> $this->arr["text"], 
											"element_type"	=> $this->arr["type"],
											"T_TEXTBOX"			=> $tt));
		return $this->parse();
	}

	function change_pos($arr,&$f)
	{
		$this->read_template("change_pos.tpl");
		$o = new db_objects;
		$obj = $this->get_object($this->id);
		if (!is_array($f->arr["el_menus"]))
		{
			$mlist = $o->get_list();
		}
		else
		{
			$tlist = $o->get_list();
			foreach($f->arr["el_menus"] as $menuid)
			{
				$mlist[$menuid] = $tlist[$menuid];
			}
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_chpos", array("id" => $this->fid, "col" => $this->col, "row" => $this->row, "el_id" => $this->id), "form"),
			"folders"	=> $this->picker($obj["parent"], $mlist),
			"name"		=> $this->arr["name"]
		));

		for ($col=0; $col < $f->arr["cols"]; $col++)
		{
			$this->vars(array(
				"col" => $col+1
			));
			$cc.=$this->parse("COLNUMC");
			$cc2.=$this->parse("COLNUM");
		}
		$this->vars(array(
			"COLNUMC" => $cc,
			"COLNUM" => $cc2
		));
		for ($row = 0; $row < $f->arr["rows"]; $row++)
		{
			$c = "";
			$cc="";
			for ($col = 0; $col < $f->arr["cols"]; $col++)
			{
				$this->vars(array(
					"row" => $row, 
					"col" => $col, 
					"checked" => checked($this->col == $col && $this->row == $row),
					"cnt" => $cnt++,
					"drow" => $row+1
				));
				$c.=$this->parse("COL");
				$cc.=$this->parse("COLC");
			}
			$this->vars(array("COL" => $c,"COLC" => $cc));
			$l.=$this->parse("ROW");
			$cl.=$this->parse("ROWC");
		}
		$this->vars(array("ROW" => $l,"ROWC" => $cl));
		return $this->parse();
	}

	function do_core_userhtml($prefix,$elvalues,$no_submit)
	{
		global $awt;
		$awt->start("form_element::do_core_userhtml");
		$awt->count("form_element::do_core_userhtml");

		$html="";
		global $lang_id;
		if ($this->form->lang_id == $lang_id)
		{
			$text = $this->arr["text"];
			$info = $this->arr["info"]; 
		}
		else
		{
			$text = $this->arr["lang_text"][$lang_id];
			$info = $this->arr["lang_info"][$lang_id]; 
		}

		$elid = $this->id;
		$ext = false;

		global $fg_check_status;

		$stat_check = "";

		if ($fg_check_status)
		{
			$stat_check = " onChange='set_changed()' ";
		};

		switch($this->arr["type"])
		{
			case "textarea":
				$html="<textarea $stat_check NAME='".$prefix.$elid."' COLS='".$this->arr["ta_cols"]."' ROWS='".$this->arr["ta_rows"]. "'>";
				$html .= htmlspecialchars($this->get_val($elvalues));
				$html .= "</textarea>";
				break;

			case "radiobutton":
				$ch = ($this->entry_id ? checked($this->entry == $this->id) : checked($this->arr["default"] == 1));
				$html="<input type='radio' $stat_check NAME='".$prefix."radio_group_".$this->arr["group"]."' VALUE='".$this->id."' $ch>";
				break;

			case "listbox":
				// kui seoseelement siis feigime sisu
				if ($this->arr["subtype"] == "relation" && $this->arr["rel_element"] && $this->arr["rel_form"])
				{
					$this->make_relation_listbox_content();
				}
				$html="<select $stat_check name='".$prefix.$elid."'";
				if ($this->arr["lb_size"] > 1)
				{
					$html.=" size=\"".$this->arr["lb_size"]."\"";
				}
				$html.=">";
				if (is_array($elvalues[$this->arr["name"]]))
				{
					$val = $this->get_val($elvalues);
					$cnt = sizeof($val);
					$this->arr["listbox_items"] = $val;
					$ext = true;
				}
				else
				{
					$cnt = $this->arr["listbox_count"];
				};

				if ($lang_id != $this->form->lang_id)
				{
					$larr = $this->arr["listbox_lang_items"][$lang_id];
				}
				else
				{
					$larr = $this->arr["listbox_items"];
				}

				for ($b=0; $b < $cnt; $b++)
				{	
					if ($this->entry_id)
						$lbsel = ($this->entry == "element_".$this->id."_lbopt_".$b ? " SELECTED " : "");
					else
						$lbsel = ($this->arr["listbox_default"] == $b ? " SELECTED " : "");
		
					if (is_array($larr))
					{
						list($key,$value) = each($larr);
						if ($ext)
						{
							$html .= "<option $lbsel value='$key'>$value</option>\n";
						}
						else
						{
							$html.="<option $lbsel VALUE='element_".$this->id."_lbopt_".$b."'>".$value;
						};
					}
				}
				$html.="</select>";
				break;

			case "multiple":
				$html="<select $stat_check NAME='".$prefix.$elid."[]' MULTIPLE";
				if ($this->arr["mb_size"] > 1)
				{
					$html.=" size=\"".$this->arr["mb_size"]."\"";
				}
				$html.=">";

				if ($this->entry_id)
					$ear = explode(",",$this->entry);

				if ($lang_id != $this->form->lang_id)
				{
					$larr = $this->arr["multiple_lang_items"][$lang_id];
				}
				else
				{
					$larr = $this->arr["multiple_items"];
				}

				for ($b=0; $b < $this->arr["multiple_count"]; $b++)
				{
					$sel = false;
					if ($this->entry_id)
					{
						reset($ear);
						while (list(,$v) = each($ear))
							if ($v == $b)
								$sel = true;
					}
					else
						$sel = ($this->arr["multiple_defaults"][$b] == 1 ? true : false);

					$html.="<option ".($sel == true ? " SELECTED " : "")." VALUE='$b'>".$larr[$b];
				}
				$html.="</select>";
				break;

			case "checkbox":
				$sel = ($this->entry_id ? ($this->entry == 1 ? " CHECKED " : " " ) : ($this->arr["default"] == 1 ? " CHECKED " : ""));
				$html = "<input $stat_check type='checkbox' NAME='".$prefix.$elid."' VALUE='1' $sel>";
				break;

			case "textbox":
				$l = $this->arr["length"] ? "SIZE='".$this->arr["length"]."'" : "";
				$html = "<input $stat_check type='text' NAME='".$prefix.$elid."' $l VALUE='".($this->get_val($elvalues))."'>";
				break;


			case "price":
				$l = $this->arr["length"] ? "SIZE='".$this->arr["length"]."'" : "";
				$html = "<input $stat_check type='text' NAME='".$prefix.$elid."' $l VALUE='".($this->get_val($elvalues))."'>";
				break;

			case "button":
			case "submit":
			case "reset":
				if (!$no_submit)
				{
					if ($lang_id == $this->form->lang_id)
					{
						$butt = $this->arr["button_text"];
					}
					else
					{
						$butt = $this->arr["lang_button_text"][$lang_id];
					}
					if ($this->arr["subtype"] == "submit" || $this->arr["type"] == "submit" || $this->arr["subtype"] == "confirm")
					{
						if ($this->arr["chain_forward"] == 1)
						{
							$bname="name=\"no_chain_forward\"";
						}
						else
						if ($this->arr["chain_backward"] == 1)
						{
							$bname="name=\"chain_backward\"";
						}
						else
						if ($this->arr["subtype"] == "confirm")
						{
							$bname = "name=\"confirm\"";
						}
						$html = "<input $bname type='submit' VALUE='".$butt."' onClick=\"return check_submit();\">";
					}
					else
					if ($this->arr["subtype"] == "reset" || $this->arr["type"] == "reset")
					{
						$html = "<input type='reset' VALUE='".$butt."'>";
					}
					else
					if ($this->arr["subtype"] == "url")
					{
						$html = "<input type='submit' VALUE='".$butt."' onClick=\"window.location='".$this->arr["button_url"]."';return false;\">";
					}
				}
				break;

			case "file":
				$html = "<input type='file' $stat_check NAME='".$prefix.$elid."' value=''>";
				break;

			// yuck
			case "link":
				if ($this->arr["subtype"] != "show_op")
				{
					$html="<table border=0><tr><td align=right>".$this->arr["link_text"]."</td><td><input type='text' NAME='".$prefix.$elid."_text' VALUE='".($this->entry_id ? $this->entry["text"] : "")."'></td></tr>";
					$html.="<tr><td align=right>".$this->arr["link_address"]."</td><td><input type='text' NAME='".$prefix.$elid."_address' VALUE='".($this->entry_id ? $this->entry["address"] : "")."'></td></tr></table>";
					$html.="<a onClick=\"e_".$this->fid."_elname='".$prefix.$elid."_text';e_".$this->fid."_elname2='".$prefix.$elid."_address';\" href=\"javascript:remote('no',500,400,'".$this->mk_orb("search_doc", array(),"links")."')\">Vali dokument</a>";
				}
				break;

			case "date":
				$de = new date_edit(time());
				$de->configure(array(
					"year" => "",
					"month" => "",
					"day" => ""
				));
				$fy = $this->arr["from_year"];
				$ty = $this->arr["to_year"];
				if ($this->arr["def_date_type"] == "now")
				{
					$def = time() + ($this->arr["def_date_num"] * $this->arr["def_date_add"]);
				}
				else
				{
					$def = time();
				}
				$html = $de->gen_edit_form($prefix.$elid, ($this->entry_id ? $this->entry : $def),($fy ? $fy : 2000),($ty ? $ty : 2005),true);
				break;
		};
		
		if ($this->arr["type"] != "")
		{
			$sep_ver = ($this->arr["text_distance"] > 0 ? "<br><img src='/images/transa.gif' width='1' height='".$this->arr["text_distance"]."' border='0'><br>" : "<br>");
			$sep_hor = ($this->arr["text_distance"] > 0 ? "<img src='/images/transa.gif' height='1' width='".$this->arr["text_distance"]."' border='0'>" : "");
		}
		if ($this->arr["text_pos"] == "up")
		{
			$html = $text.$sep_ver.$html;
		}
		else
		if ($this->arr["text_pos"] == "down")
		{	
			$html = $html.$sep_ver.$text;
		}
		else
		if ($this->arr["text_pos"] == "right")
		{
			$html = $html.$sep_hor.$text;
		}
		else
		{
			$html = $text.$sep_hor.$html;		// default is on left of element
		}
		if ($this->arr["info"] != "")
		{
			$html .= "<br><font face='arial, geneva, helvetica' size='1'>&nbsp;&nbsp;$info</font>";
		}

		if ($this->arr["sep_type"] == 1)	// reavahetus
		{
			$html.="<br>";
		}
		else
		if ($this->arr["sep_pixels"] > 0)
		{
			$html.="<img src='/images/transa.gif' width=".$this->arr["sep_pixels"]." height=1 border=0>";
		}
		$awt->stop("form_element::do_core_userhtml");
		return $html;
	}

	////
	// tagastab mingi elemendi väärtuse
	function get_val($elvalues = array())
	{
		global $lang_id,$awt;
		$awt->start("form_element::get_val");
		$awt->count("form_element::get_val");


		// kui entry on laetud, siis voetakse see sealt.
		if ($this->entry_id)
		{
			$val = $this->entry;
		}
		else
		// vastasel korral, kui $elvalues midagi sisaldab, siis saame info sealt
		if (isset($elvalues[$this->arr["name"]]) && $elvalues[$this->arr["name"]] != "")
		{
			$val = $elvalues[$this->arr["name"]];
		}
		// finally, if nothing else succeeded, we will just use the default.
		else
		{
			if ($lang_id != $this->form->lang_id)
			{
				$val = $this->arr["lang_default"][$lang_id];
			}
			else
			{
				$val = $this->arr["default"];
			}
		}
		$awt->stop("form_element::get_val");
		return $val;
	}

	function core_process_entry(&$entry, $id,$prefix = "")
	{
		global $awt;
		$awt->start("form_element::core_process_entry");
		$awt->count("form_element::core_process_entry");


		//// This is called for every single element in the form.
		// $this->form->post_vars sisaldab $HTTP_POST_VARS väärtusi.
		// the following code should be fixed to use only that and
		// not import the variables from the local scope.
		if ($this->arr["type"] == 'link')
		{
			$var = $prefix.$this->id."_text";
			$var2= $prefix.$this->id."_address";
			// fuck you. fuck YOU. 
			// ok, is it really THAT hard to change this into $this->form->post_vars[$var] or what are you whining about? :p - terryf
			global $$var, $$var2;
			$entry[$this->id] = array("text" => $$var, "address" => $$var2);
			$this->entry_id = $id;
			$this->entry = $entry[$this->id];
			$awt->stop("form_element::core_process_entry");
			return;
		}
		else
		if ($this->arr["type"] == 'file')
		{
			// gotcha, siis handletakse piltide uploadi
			$var = $prefix.$this->id;
			global $$var;

			if ($$var != "none")
			{
				$ft = $var."_type";
				global $$ft;
				$fn = $var."_name";
				global $$fn;

				// nyah. this should be rewritten to use file class... 
				// and actually db_images class should be deprecated and all file uploads handled by file class
				// cause it's interface and everything is lots better
				$im = new db_images;
				if ($this->arr["fshow"] == 1)
				{
					if (is_array($entry[$this->id]))	// this holds array("id" => $image_id, "idx" => $image_idx);
					{
						$entry[$this->id] = $im->replace($$var,$$ft,$id,$entry[$this->id]["idx"],"",$entry[$this->id]["id"],true,$$fn);
					}
					else
					{
						$entry[$this->id] = $im->upload($$var, $$ft, $id, "",true,$$fn);
					};
				}
				else
				{
					$entry[$this->id] = $im->upload($$var, $$ft, $id, "",true,$$fn);
				}
				$this->entry = $entry[$this->id];
				$this->entry_id = $id;
			}
			$awt->stop("form_element::core_process_entry");
			return;
		}
		else
		if ($this->arr["type"] == "radiobutton")
		{
			// this is not good, I hade to hack around this naming scheme in
			// XML-RPC.
			// um, could you explain what's wrong with it? - terryf
			$var = $this->form->post_vars[$prefix."radio_group_".$this->arr["group"]];
		}
		else
		if ($this->arr["type"] == "button" && $this->arr["subtype"] == "confirm")
		{
			if (isset($GLOBALS[$prefix."confirm"]))
			{
				$this->form->update_entry_object(array("oid" => $id, "parent" => $this->arr["confirm_moveto"]));
			}
		}
		else
		// I think the listboxes are handled as well here.
		if ($this->arr["type"] == "multiple")
		{
			$var = $prefix.$this->id;
			global $$var;
			if (is_array($$var))
			{
				$entry[$this->id] = join(",",$$var);
			}
			else
			{
				$entry[$this->id] = "";
			}
			$this->entry = $entry[$this->id];
			$this->entry_id = $id;
			$awt->stop("form_element::core_process_entry");
			return;
		}
		else
		if ($this->arr["type"] == "date")
		{
			// subtle trickery here. since if this is a related date element it must get its value from the other element
			// we just use that elements variable for that
			$d_id = $this->id;
			if ($this->arr["def_date_type"] == "rel")
			{
				$d_id = $this->arr["def_date_rel_el"];
			}
			$var = $prefix.$d_id;
			global $$var;
			$v = $$var;
			$tm = mktime($v["hour"],$v["minute"],0,$v["month"],$v["day"],$v["year"]);
			if ($this->arr["def_date_type"] == "rel")
			{
				$tm+=($this->arr["def_date_num"] * $this->arr["def_date_add"]);
			}
			$entry[$this->id] = $tm;
			$this->entry = $entry[$this->id];
			$this->entry_id = $id;
			$awt->stop("form_element::core_process_entry");
			return;
		}
		else
		{
			$var = $this->form->post_vars[$prefix.$this->id];
		}

		$entry[$this->id] = $var;
		$this->entry = $var;
		$this->entry_id = $id;
		$awt->stop("form_element::core_process_entry");
	}

	////
	// !returns the elements value in the currently loaded entry in a form that can be presented to the user
	function get_value($numeric = false)
	{
		global $awt;
		$awt->start("form_element::get_value");
		$awt->count("form_element::get_value");

		switch($this->arr["type"])
		{
			case "textarea":
				$html = trim($this->entry);
				break;

			case "radiobutton":
				if (!$numeric)
				{
					if ($this->arr["ch_value"] != "")
					{
						$html=$this->entry == $this->id ? $this->arr["ch_value"] : "";
					}
					else
					{
						$html=($this->entry == $this->id ? " (X) " : " (-) ");
					}
				}
				else
				{
					$html = ($this->entry == $this->id ? 1 : 0);
				}
				break;

			case "listbox":
				if ($this->arr["subtype"] == "relation" && $this->arr["rel_element"] && $this->arr["rel_form"])
				{
					$this->make_relation_listbox_content();
				}

				$sp = split("_", $this->entry, 10);
				$html = $this->arr["listbox_items"][$sp[3]];
				break;

			case "multiple":
				$ec=explode(",",$this->entry);
				reset($ec);
				while (list(, $v) = each($ec))
				{
					$html .= ($this->arr["multiple_items"][$v]." ");
				}
				break;

			case "checkbox":
				if (!$numeric)
				{
					if ($this->arr["ch_value"] != "")
					{
						$html=$this->entry == 1 ? $this->arr["ch_value"] : "";
					}
					else
					{
						$html=$this->entry == 1 ? "(X) " : " (-) ";
					}
				}
				else
				{
					$html = $this->entry;
				}
				break;

			case "textbox":
				$html = trim($this->entry);
				break;

			case "date":
				// FIXME: kui mingil hetkel kasutaja kuupäeva formaat muutub
				// konfitavaks, siis muuda ka siit ära
				$html = $this->time2date($this->entry,5);
				break;

			case "price":
				$html = trim($this->entry);
				break;

			case "link":
				$html = $this->entry["address"];
				break;
		};
		$awt->stop("form_element::get_value");
		return $html;
	}

	function sort_listbox()
	{
		if (is_array($this->arr["listbox_items"]))
		{
			$cnt=0;
			foreach($this->arr["listbox_items"] as $k => $v)
			{
				if ($v != "")
				{
					$ar[$cnt++] = $v;
				}
			}

			if (is_array($ar))
			{
				uksort($ar,array($this,"__lb_sort"));
				$cnt=0;
				$ordar = $this->arr["listbox_order"];
				foreach( $ar as $k => $v)
				{
					$this->arr["listbox_items"][$cnt] = $v;
					$this->arr["listbox_order"][$cnt] = $ordar[$k];
					$cnt++;
				}
			}
		}
	}

	function __lb_sort($a,$b)
	{
		if ($this->arr["sort_by_order"])
		{
			if ($this->arr["listbox_order"][$a] == $this->arr["listbox_order"][$b])
			{
				if ($this->arr["sort_by_alpha"])
				{
					$res =  strcmp($this->arr["listbox_items"][$a],$this->arr["listbox_items"][$b]);
					return $res;
				}
				else
				{
					return 0;
				}
			}
			else
			if ($this->arr["listbox_order"][$a] < $this->arr["listbox_order"][$b])
			{
				return -1;
			}
			else
			{
				return 1;
			}
		}
		else
		if ($this->arr["sort_by_alpha"])
		{
			return strcmp($this->arr["listbox_items"][$a],$this->arr["listbox_items"][$b]);
		}
		else
		{
			return 0;
		}
	}

	function sort_multiple()
	{
		if (is_array($this->arr["multiple_items"]))
		{
			$cnt=0;
			foreach($this->arr["multiple_items"] as $k => $v)
			{
				if ($v != "")
				{
					$ar[$cnt++] = $v;
				}
			}

			if (is_array($ar))
			{
				uksort($ar,array($this,"__mb_sort"));
				$cnt=0;
				$ordar = $this->arr["multiple_order"];
				foreach( $ar as $k => $v)
				{
					$this->arr["multiple_items"][$cnt] = $v;
					$this->arr["multiple_order"][$cnt] = $ordar[$k];
					$cnt++;
				}
			}
		}
	}

	function __mb_sort($a,$b)
	{
		if ($this->arr["sort_by_order"])
		{
			if ($this->arr["multiple_order"][$a] == $this->arr["multiple_order"][$b])
			{
				if ($this->arr["sort_by_alpha"])
				{
					$res =  strcmp($this->arr["multiple_items"][$a],$this->arr["multiple_items"][$b]);
					return $res;
				}
				else
				{
					return 0;
				}
			}
			else
			if ($this->arr["multiple_order"][$a] < $this->arr["multiple_order"][$b])
			{
				return -1;
			}
			else
			{
				return 1;
			}
		}
		else
		if ($this->arr["sort_by_alpha"])
		{
			return strcmp($this->arr["multiple_items"][$a],$this->arr["multiple_items"][$b]);
		}
		else
		{
			return 0;
		}
	}

	////
	// !imports the data from a text file if the user uploaded it
	function import_lb_data()
	{
		$base = "element_".$this->id;
		$var = $base."_import";
		global $$var;
		if (is_uploaded_file($$var))
		{
			// imprtime siis phaili sisu
			$this->arr["listbox_items"] = array();
			$fc = file($$var);
			$cnt=0;
			foreach($fc as $line)
			{
				$line = str_replace("\r","",$line);
				$line = str_replace("\n","",$line);
				if ($line == "")
				{
					$line = " ";
				}
				$this->arr["listbox_items"][$cnt++] = $line;
			}
			$this->arr["listbox_count"] = $cnt;
		}
	}

	////
	// !imports the data from a text file if the user uploaded it
	function import_m_data()
	{
		$base = "element_".$this->id;
		$var = $base."_import";
		global $$var;
		if (is_uploaded_file($$var))
		{
			// imprtime siis phaili sisu
			$this->arr["multiple_items"] = array();
			$fc = file($$var);
			$cnt=0;
			foreach($fc as $line)
			{
				$line = str_replace("\r","",$line);
				$line = str_replace("\n","",$line);
				if ($line == "")
				{
					$line = " ";
				}
				$this->arr["multiple_items"][$cnt++] = $line;
			}
			$this->arr["multiple_count"] = $cnt;
		}
	}

	function do_search_script($rel = false)
	{
		global $elements_created,$search_script;

		if (!$search_script)
		{
			$GLOBALS["search_script"] = true;
			$this->vars(array("SEARCH_SCRIPT" => $this->parse("SEARCH_SCRIPT")));
		}

		if (!$elements_created)
		{
			// make javascript arrays for form elements
			$formcache = array(0 => "");
			if (!$rel)
			{
				$tarr = $this->form->get_search_targets();
			}
			else
			{
				$tarr = $this->form->get_relation_targets();
			}
			$tarstr = join(",",$this->map2("%s",$tarr));
			if ($tarstr != "")
			{
				$this->db_query("SELECT name, oid FROM objects WHERE status != 0 AND oid IN ($tarstr)");
				while ($row = $this->db_next())
				{
					$formcache[$row["oid"]] = $row["name"];
				}
			
				$el_num = 0;
				$this->db_query("SELECT objects.name as el_name, element2form.el_id as el_id,element2form.form_id as form_id FROM element2form LEFT JOIN objects ON objects.oid = element2form.el_id WHERE element2form.form_id IN ($tarstr)");
				while ($row = $this->db_next())
				{
					$this->vars(array(
						"el_num" => $el_num++,
						"el_id" => $row["el_id"],
						"el_text" => $row["el_name"],
						"form_id" => $row["form_id"]
					));
					$eds.=$this->parse("ELDEFS");
				}
			}
			$this->vars(array("ELDEFS" => $eds));
			$this->vars(array("SEARCH_DEFS" => $this->parse("SEARCH_DEFS")));
			$GLOBALS["elements_created"] = true;
			$GLOBALS["formcache"] = $formcache;
		}
	}

	////
	// !reads the elements for the relation listbox (this element) from the database
	function make_relation_listbox_content()
	{
		$this->save_handle();
		$rel_el = "form_".$this->arr["rel_form"]."_entries.ev_".$this->arr["rel_element"];

		$order_by = "";
		if ($this->arr["sort_by_alpha"])
		{
			$order_by = "ORDER BY $rel_el ";
		}

		if ($this->arr["rel_unique"] == 1)
		{
			$rel_el = "distinct(".$rel_el.")";
		}

		$this->db_query("SELECT $rel_el as ev_".$this->arr["rel_element"]." FROM form_".$this->arr["rel_form"]."_entries LEFT JOIN objects ON objects.oid = form_".$this->arr["rel_form"]."_entries.id  WHERE objects.status != 0 $order_by");
		$cnt=0; 
		while($row = $this->db_next())
		{
			$this->arr["listbox_items"][$cnt] = $row["ev_".$this->arr["rel_element"]];
			$cnt++;
		}
		$this->arr["listbox_count"] = $cnt;
		if ($this->form->type == FTYPE_SEARCH)
		{
			$this->arr["listbox_count"] = $cnt+1;
			$this->arr["listbox_items"][$cnt] = "";
			$this->arr["listbox_default"] = $cnt;
		}
		$this->restore_handle();
	}
}
?>
