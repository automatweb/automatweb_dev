<?php
if (defined("FORM_EELEMENT_LOADED")) {
} else {
define(FORM_EELEMENT_LOADED,1);
	session_register("clipboard");

	class form_entry_element extends form_element
	{
		function form_entry_element()
		{
			$this->tpl_init("forms");
			$this->db_init();

			$this->entry_id = 0;
			$this->id = 0;
		}

		function gen_admin_html(&$cell)
		{
			$this->read_template("admin_entry_element.tpl");
			$chk_val = " CHECKED ";

			$this->vars(array("cell_id"									=> "element_".$this->id, 
												"cell_text"								=> $this->arr[text],
												"cell_name"								=> $this->arr[name],
												"cell_dist"								=> $this->arr[text_distance],
												"type_active_textbox" 		=> ($this->arr[type] == "textbox" ? " SELECTED " : ""),
												"type_active_textarea" 		=> ($this->arr[type] == "textarea" ? " SELECTED " : ""),
												"type_active_checkbox" 		=> ($this->arr[type] == "checkbox" ? " SELECTED " : ""),
												"type_active_radiobutton" => ($this->arr[type] == "radiobutton" ? " SELECTED " : ""),
												"type_active_listbox" 		=> ($this->arr[type] == "listbox" ? " SELECTED " : ""),
												"type_active_multiple"		=> ($this->arr[type] == "multiple" ? " SELECTED " : ""),
												"type_active_file"				=> ($this->arr[type] == "file" ? " SELECTED " : ""),
												"type_active_link"				=> ($this->arr[type] == "link" ? " SELECTED " : ""),
												"type_active_submit"			=> ($this->arr[type] == "submit" ? " SELECTED " : ""),
												"type_active_reset"				=> ($this->arr[type] == "reset" ? " SELECTED " : ""),
												"type_active_price"				=> ($this->arr[type] == "price" ? " SELECTED " : ""),
												"default_name"						=> "element_".$this->id."_def",
												"default"									=> $this->arr["default"],
												"cell_info"								=> $this->arr[info],
												"front_checked"						=> ($this->arr[front] == 1 ? $chk_val : ""),
												"cell_order"							=> $this->arr[ord],
												"type"										=> $this->arr[type],
												"sep_enter_checked"				=> ($this->arr[sep_type] == 1 ? " CHECKED " : "" ),
												"sep_space_checked"				=> ($this->arr[sep_type] != 1 ? " CHECKED " : "" ),
												"cell_sep_pixels"					=> $this->arr[sep_pixels],
												"element_id"							=> $this->id,
												"text_pos_up"							=> ($this->arr[text_pos] == "up" ? "CHECKED" : ""),
												"text_pos_down"						=> ($this->arr[text_pos] == "down" ? "CHECKED" : ""),
												"text_pos_left"						=> ($this->arr[text_pos] == "left" ? "CHECKED" : ""),
												"text_pos_right"					=> ($this->arr[text_pos] == "right" ? "CHECKED" : ""),
												"length"									=> $this->arr[length],
												"changepos"								=> $this->mk_orb("change_el_pos",array("id" => $this->fid, "col" => $this->col, "row" => $this->row, "el_id" => $this->id), "form")));

			$cd = "";
			$cd = $this->parse("CAN_DELETE");

			$li = ""; $hl = ""; $hl2 = "";
			if ($this->arr[type] == "link")
			{
				$this->vars(array("link_text"			=> $this->arr[link_text],
													"link_address"	=> $this->arr[link_address]));
				$li = $this->parse("HLINK_ITEMS");
			}
			else
				$hl2 = $this->parse("EL_NOHLINK");
			$hl = $this->parse("EL_HLINK");

			$this->vars(array("EL_HLINK" => $hl, "EL_NOHLINK" => $hl2));
			$fi = "";
			if ($this->arr[type] == "file")
			{
				$this->vars(array("ftype_image_selected"	=> ($this->arr[ftype] == 1 ? "CHECKED" : ""),
													"ftype_file_selected"		=> ($this->arr[ftype] == 2 ? "CHECKED" : ""),
													"file_link_text"				=> $this->arr[flink_text],
													"file_show"							=> ($this->arr[fshow] == 1 ? "CHECKED" : ""),
													"file_alias"						=> ($this->arr[fshow] != 1 ? "CHECKED" : "")));
				$fi = $this->parse("FILE_ITEMS");
			}

			$lb = "";
			if ($this->arr[type] == "listbox")		
			{	
				for ($b=0; $b < ($this->arr[listbox_count]+1); $b++)
				{
					$this->vars(array("listbox_item_id" 			=> "element_".$this->id."_lb_".$b,
														"listbox_item_value"		=> $this->arr[listbox_items][$b],
														"listbox_radio_name"		=> "element_".$this->id."_lradio",
														"listbox_radio_value"		=> $b,
														"listbox_radio_checked"	=> ($this->arr[listbox_default] == $b ? $chk_val : "")));
					$lb.=$this->parse("LISTBOX_ITEMS");
				}	
			}

			$mu = "";
			if ($this->arr[type] == "multiple")		
			{	
				for ($b=0; $b < ($this->arr[multiple_count]+1); $b++)
				{
					$this->vars(array("multiple_item_id" 				=> "element_".$this->id."_mul_".$b,
														"multiple_item_value"			=> $this->arr[multiple_items][$b],
														"multiple_check_name"			=> "element_".$this->id."_m_".$b,
														"multiple_check_value"		=> "1",
														"multiple_check_checked"	=> ($this->arr[multiple_defaults][$b] == 1 ? $chk_val : "")));
					$mu.=$this->parse("MULTIPLE_ITEMS");
				}	
			}

			$ta = "";
			if ($this->arr[type] == "textarea")
			{
				$this->vars(array("textarea_cols_name"	=> "element_".$this->id."_ta_cols",
													"textarea_rows_name"	=> "element_".$this->id."_ta_rows",
													"textarea_cols"	=> $this->arr[ta_cols],
													"textarea_rows"	=> $this->arr[ta_rows]));
				$ta = $this->parse("TEXTAREA_ITEMS");
			}

			$gp="";
			if ($this->arr[type] == "radiobutton")
			{
				$this->vars(array("default_checked"		=> ($this->arr["default"] == 1 ? $chk_val : ""),
													"cell_group"				=> $this->arr[group]));
				$gp = $this->parse("RADIO_ITEMS");
			}
			
			$dt="";
			if ($this->arr[type] == "textbox")
				$dt = $this->parse("DEFAULT_TEXT");

			$dc="";
			if ($this->arr[type] == "checkbox")
			{
				$this->vars(array("default_checked"	=> ($this->arr["default"] == 1 ? $chk_val : "")));
				$dc = $this->parse("CHECKBOX_ITEMS");
			}

			$pc="";
			if ($this->arr[type] == "price")
			{
				$this->vars(array("price"	=> $this->arr["price"]));
				$pc = $this->parse("PRICE_ITEMS");
			}

			$bt = "";
			if ($this->arr[type] == "submit" || $this->arr[type] == "reset")
			{
				$this->vars(array("button_text" => $this->arr["button_text"]));
				$bt = $this->parse("BUTTON_ITEMS");
			}

			$this->vars(array("LISTBOX_ITEMS"		=> $lb, 
												"MULTIPLE_ITEMS"	=> $mu, 
												"TEXTAREA_ITEMS"	=> $ta,
												"RADIO_ITEMS"			=> $gp,
												"DEFAULT_TEXT"		=> $dt,
												"CHECKBOX_ITEMS"	=> $dc,
												"CAN_DELETE"			=> $cd,
												"FILE_ITEMS"			=> $fi,
												"HLINK_ITEMS"			=> $li,
												"BUTTON_ITEMS"		=> $bt,
												"PRICE_ITEMS"			=> $pc));
			return $this->parse();
		}

		////
		// !this function takes the changed properties of this element from the form and joins them together in the array of element properties 
		function save(&$arr)
		{
			extract($arr);

			$base = "element_".$this->id;
			
			$var=$base."_text";
			$this->arr[text] = $$var;
			$var=$base."_name";
			// check if the name has changed and if it has, then update the real object also
			if ($$var != $this->arr[name])
			{
				$this->arr[name] = $$var;
				$this->upd_object(array("oid" => $this->id, "name" => $$var));
			}
			$var=$base."_list";
			$this->arr[join_list] = $$var;
			$var=$base."_email_el";
			$this->arr[join_email] = $$var;

			$var=$base."_type";
			if ($$var == "delete")
				return false;

			$this->arr[type] = $$var;
			$var = $base."_info";
			$this->arr[info]=$$var;
			$var=$base."_front";
			$this->arr[front] = $$var;
			$var=$base."_dist";
			$this->arr[text_distance] = $$var;
			$var=$base."_text_pos";
			$this->arr[text_pos] = $$var;

			if ($this->arr[type] == "listbox")
			{
				$cnt=$this->arr[listbox_count]+1;
				for ($b=0; $b < $cnt; $b++)
				{
					$var=$base."_lb_".$b;
					$this->arr[listbox_items][$b] = $$var;
				}
				while (isset($this->arr[listbox_items][$cnt-1]) && ($this->arr[listbox_items][$cnt-1] == ""))
				{
					$cnt--;
				}

				$this->arr[listbox_count]=$cnt;
				$var = $base."_lradio";
				$this->arr[listbox_default] = $$var;
			}

			if ($this->arr[type] == "multiple")
			{
				$cnt=$this->arr[multiple_count]+1;	
				for ($b=0; $b < $cnt; $b++)
				{
					$var=$base."_mul_".$b;
					$this->arr[multiple_items][$b] = $$var;

					$var = $base."_m_".$b;
					$this->arr[multiple_defaults][$b] = $$var;
				}
				if ($this->arr[multiple_items][$cnt-1] == "")
					$cnt--;
				$this->arr[multiple_count]=$cnt;
			}

			if ($this->arr[type] == "textarea")
			{
				$var = $base."_ta_rows";
				$this->arr[ta_rows]= $$var;
				$var = $base."_ta_cols";
				$this->arr[ta_cols]=$$var;
			}

			if ($this->arr[type] == "radiobutton")
			{
				$var=$base."_group";
				$this->arr[group] = $$var;
			}

			if ($this->arr["type"] == "price")
			{
				$var=$base."_price";
				$this->arr["price"] = $$var;
				$var=$base."_length";
				$this->arr["length"] = $$var;
			}

			if ($this->arr[type] == "textbox" || $this->arr[type] == "textarea" || $this->arr[type] == "checkbox" || $this->arr[type] == "radiobutton")
			{
				$var=$base."_def";
				$this->arr["default"] = $$var;
				$var=$base."_length";
				$this->arr["length"] = $$var;
			}

			if ($this->arr[type] == 'file')
			{
				$var=$base."_filetype";
				$this->arr[ftype] = $$var;
				$var=$base."_file_link_text";
				$this->arr[flink_text] = $$var;
				$var=$base."_file_show";
				$this->arr[fshow] = $$var;
			}

			if ($this->arr[type] == 'link')
			{
				$var=$base."_link_text";
				$this->arr[link_text] = $$var;
				$var=$base."_link_address";
				$this->arr[link_address] = $$var;
			}

			if ($this->arr[type] == "submit" || $this->arr[type] == "reset")
			{
				$var = $base."_btext";
				$this->arr[button_text] = $$var;
			}

			$var = $base."_separator_type";
			$this->arr[sep_type] = $$var;

			$var = $base."_sep_pixels";
			$this->arr[sep_pixels] = $$var;

			$var = $base."_order";
			$$var+=0;
			if ($this->arr[ord] != $$var)
			{
				$this->arr[ord] = $$var;
				$this->upd_object(array("oid" => $this->id, "jrk" => $$var));
			}
			return true;
		}

		function get_val($elvalues = array())
		{
			if ($this->entry_id)
			{
				$val = $this->entry;
			}
			else
			if ($elvalues[$this->arr["name"]] != "")
			{
				$val = $elvalues[$this->arr["name"]];
			}
			else
			{
				$val = $this->arr["default"];
			}
			return $val;
		}

		function gen_user_html_not(&$images,$prefix = "",$elvalues = array())		// function that doesn't use templates
		{
			$html="";
			$info = $images->proc_text($this->arr[info], $this->parent);
			$text = ($this->arr[text] == "" ? "" : $images->proc_text($this->arr[text], $this->parent));

			$elid = $this->id;
																	
			if ($this->arr[type] == "textarea")
			{
				$html="<textarea NAME='".$prefix.$elid."' COLS='".$this->arr[ta_cols]."' ROWS='".$this->arr[ta_rows]."'>";
				$html.=($this->get_val($elvalues))."</textarea>";
			}
			
			if ($this->arr[type] == "radiobutton")
			{
				$ch = ($this->entry_id ? checked($this->entry == $this->id) : checked($this->arr["default"] == 1));
				$html="<input type='radio' NAME='".$prefix."radio_group_".$this->arr[group]."' VALUE='".$this->id."' $ch>";
			}
			
			if ($this->arr[type] == "listbox")
			{
				$html="<select name='".$prefix.$elid."'>";
				for ($b=0; $b < $this->arr[listbox_count]; $b++)
				{	
					if ($this->entry_id)
						$lbsel = ($this->entry == "element_".$this->id."_lbopt_".$b ? " SELECTED " : "");
					else
						$lbsel = ($this->arr[listbox_default] == $b ? " SELECTED " : "");
					$html.="<option $lbsel VALUE='element_".$this->id."_lbopt_".$b."'>".$this->arr[listbox_items][$b];
				}
				$html.="</select>";
			}
				
			if ($this->arr["type"] == "multiple")
			{
				$html="<select NAME='".$prefix.$elid."[]' MULTIPLE>";

				if ($this->entry_id)
					$ear = explode(",",$this->entry);

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

					$html.="<option ".($sel == true ? " SELECTED " : "")." VALUE='$b'>".$this->arr["multiple_items"][$b];
				}
				$html.="</select>";
			}
			
			if ($this->arr["type"] == "checkbox")
			{
				$sel = ($this->entry_id ? ($this->entry == 1 ? " CHECKED " : " " ) : ($this->arr["default"] == 1 ? " CHECKED " : ""));
				$html = "<input type='checkbox' NAME='".$prefix.$elid."' VALUE='1' $sel>";
			}
			
			if ($this->arr["type"] == "textbox")
			{
				$l = $this->arr["length"] ? "SIZE='".$this->arr["length"]."'" : "";
				$html = "<input type='text' NAME='".$prefix.$elid."' $l VALUE='".($this->get_val($elvalues))."'>";
			}

			if ($this->arr["type"] == "price")
			{
				$l = $this->arr["length"] ? "SIZE='".$this->arr["length"]."'" : "";
				$html = "<input type='text' NAME='".$prefix.$elid."' $l VALUE='".($this->get_val($elvalues))."'>";
			}

			if ($this->arr["type"] == "submit")
				$html = "<input type='submit' VALUE='".$this->arr["button_text"]."'>";

			if ($this->arr["type"] == "reset")
				$html = "<input type='reset' VALUE='".$this->arr["button_text"]."'>";
				
			if($this->arr["type"] == "file")
				$html = "<input type='file' NAME='".$prefix.$elid."'>";

			if($this->arr["type"] == "link")
			{
				$html="<table border=0><tr><td align=right>".$this->arr["link_text"]."</td><td><input type='text' NAME='".$prefix.$elid."_text' VALUE='".($this->entry_id ? $this->entry["text"] : "")."'></td></tr>";
				$html.="<tr><td align=right>".$this->arr["link_address"]."</td><td><input type='text' NAME='".$prefix.$elid."_address' VALUE='".($this->entry_id ? $this->entry["address"] : "")."'></td></tr></table>";
				$html.="<a onClick=\"e_".$this->fid."_elname='".$prefix.$elid."_text';e_".$this->fid."_elname2='".$prefix.$elid."_address';\" href=\"javascript:remote('no',500,400,'".$this->mk_orb("search_doc", array(),"links")."')\">Vali dokument</a>";
			}

			if ($this->arr["type"] == "")
				$html .= $text;
			else
			{
				$sep_ver = ($this->arr["text_distance"] > 0 ? "<br><img src='/images/transa.gif' width='1' height='".$this->arr[text_distance]."' border='0'><br>" : "<br>");
				$sep_hor = ($this->arr["text_distance"] > 0 ? "<img src='/images/transa.gif' height='1' width='".$this->arr[text_distance]."' border='0'>" : "");
				if ($this->arr["text_pos"] == "up")
					$html = $text.$sep_ver.$html;
				else
				if ($this->arr["text_pos"] == "down")
					$html = $html.$sep_ver.$text;
				else
				if ($this->arr["text_pos"] == "right")
					$html = $html.$sep_hor.$text;
				else
					$html = $text.$sep_hor.$html;		// default is on left of element
			}
			
			if ($this->arr["info"] != "")
				$html .= "<br><font face='arial, geneva, helvetica' size='1'>&nbsp;&nbsp;$info</font>";

			if ($this->arr["sep_type"] == 1)	// reavahetus
				$html.="<br>";
			else
			if ($this->arr["sep_pixels"] > 0)
				$html.="<img src='/images/transa.gif' width=".$this->arr["sep_pixels"]." height=1 border=0>";
			return $html;
		}

		function process_entry(&$entry, $id,$prefix = "")
		{
			if ($this->arr[type] == 'link')
			{
				$var = $prefix.$this->id."_text";
				$var2= $prefix.$this->id."_address";
				global $$var, $$var2;
				$entry[$this->id] = array("text" => $$var, "address" => $$var2);
				return;
			}
			else
			if ($this->arr[type] == 'file')
			{
				$var = $prefix.$this->id;
				global $$var;

				if ($$var != "none")
				{
					$ft = $var."_type";
					global $$ft;
					$fn = $var."_name";
					global $$fn;

					$im = new db_images;
					if ($this->arr[fshow] == 1)
					{
						if (is_array($entry[$this->id]))	// this holds array("id" => $image_id, "idx" => $image_idx);
							$entry[$this->id] = $im->replace($$var,$$ft,$id,$entry[$this->id][idx],"",$entry[$this->id][id],true,$$fn);
						else
							$entry[$this->id] = $im->upload($$var, $$ft, $id, "",true,$$fn);
					}
					else
						$im->upload($$var, $$ft, $id, "",true,$$fn);
				}
				return;
			}
			else
			if ($this->arr[type] == "radiobutton")
				$var = $prefix."radio_group_".$this->arr[group];
			else
				$var = $prefix.$this->id;

			global $$var;
			$entry[$this->id] = $$var;
		}

		function gen_show_html()
		{
			if (!$this->entry_id)
				return "";

			$t = new db_images;
			$html = $t->proc_text($this->arr[text],$this->parent);

			if ($this->arr[type] == "textarea")
				$html.=$t->proc_text($this->entry, $this->entry_id);
					
			if ($this->arr[type] == "radiobutton")
				$html.=($this->entry == $this->id ? " Jah " : " Ei ");
					
			if ($this->arr[type] == "listbox")
			{
				$sp = split("_", $this->entry, 10);
				$html.=$this->arr[listbox_items][$sp[3]];
			}
					
			if ($this->arr[type] == "multiple")
			{
				$ec=explode(",",$this->entry);
				reset($ec);
				while (list(, $v) = each($ec))
					$html.=($this->arr[multiple_items][$v]." ");
			}

			if ($this->arr[type] == "checkbox")
				$html.=$this->entry == 1 ? "Jah " : " Ei ";
					
			if ($this->arr[type] == "textbox")
				$html.=$t->proc_text($this->entry, $this->entry_id);

			if ($this->arr[type] == "price")
				$html.=$this->entry;

			if ($this->arr[type] == "file")
			{
				$im = new db_images;
				if (is_array($this->entry))	// if this is an array, then there is a file that must be shown in place
				{
					$row = $im->get_img_by_id($this->entry[id]);

					if ($this->arr[ftype] == 1)
						$html.="<img src='".$row[url]."'>";
					else
						$html.="<a href='".$row[url]."'>".$this->arr[flink_text]."</a>";
				}
			}

			if ($this->arr[type] == "link")
				$html.="<a href='".$this->entry[address]."'>".$this->entry[text]."</a>";

			if ($this->arr[sep_type] == 1)	// reavahetus
				$html.="<br>";
			else
			if ($this->arr[sep_pixels] > 0)
				$html.="<img src='/images/transa.gif' width=".$this->arr[sep_pixels]." height=1 border=0>";

			return $html;
		}

		function gen_show_text()
		{
			if (!$this->entry_id)
				return "";

			$html = trim($this->arr[text])." ";

			if ($this->arr[type] == "textarea")
				$html.=trim($this->entry);
					
			if ($this->arr[type] == "radiobutton")
				$html.=($this->entry == $this->id ? " Jah " : " Ei ");
					
			if ($this->arr[type] == "listbox")
			{
				$sp = split("_", $this->entry, 10);
				$html.=$this->arr[listbox_items][$sp[3]];
			}
					
			if ($this->arr[type] == "multiple")
			{
				$ec=explode(",",$this->entry);
				reset($ec);
				while (list(, $v) = each($ec))
					$html.=($this->arr[multiple_items][$v]." ");
			}

			if ($this->arr[type] == "checkbox")
				$html.=$this->entry == 1 ? "Jah " : " Ei ";
					
			if ($this->arr[type] == "textbox")
				$html.=trim($this->entry);

			if ($this->arr[type] == "price")
				$html.=trim($this->entry);

			if ($this->arr[type] == "link")
				$html.=$this->entry[address];

			return $html;
		}
	}
}
?>
