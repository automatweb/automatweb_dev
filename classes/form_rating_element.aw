<?php
	session_register("clipboard");
	class form_rating_element extends aw_template
	{
		function form_rating_element($id = -1)
		{
			$this->tpl_init("forms");
			$this->db_init();

			$this->entry_id = 0;
			$this->id = $id;
			if ($id != -1)
				$this->load($id);
		}

		function load_from(&$r,&$acl)
		{
			global $clipboard;

			$this->arr = unserialize($r[content]);
			$clipboard[id] = $this->arr[style];		// put the selected style in the clipboard so that when the user wants to change it, 
																						// the style selector knows the currently selected one
			$clipboard[type] = "sel_style";
			$this->order=$r[ord];
			$this->parent = $r[parent];
			$this->id = $r[id];
		}

		function load($id)
		{
			global $clipboard;
			$this->db_query("SELECT form_elements.*, objects.parent as parent, acl
												FROM form_elements
												LEFT JOIN objects ON objects.oid = form_elements.id
												LEFT JOIN acl ON acl.oid = objects.oid
												WHERE objects.oid = $id
												GROUP BY objects.oid");
			if (!($row = $this->db_next()))
				$this->raise_error("form_entry_element->load($id), no such element!", true);
			$this->arr = unserialize($row[content]);
			$clipboard[id] = $this->arr[style];		// put the selected style in the clipboard so that when the user wants to change it, 
																						// the style selector knows the currently selected one
			$clipboard[type] = "sel_style";
			$this->order=$row[ord];
			$this->parent = $row[parent];
		}

		function get_text()		{	return $this->arr[text]; }
		function get_style()	{	return $this->arr[style]; }
		function get_type()		{	return $this->arr[type]; }
		function get_id()			{ return $this->id;	}
		function get_order()	{ return $this->order; }
		function get_acl()		{ return $this->acl; }

		function gen_admin_html()
		{
			$this->read_template("admin_rating_element.tpl");

			$this->vars(array("element_group"						=> $this->arr[group],
												"element_id"							=> "element_".$this->id,
												"element_text"						=> $this->arr[text],
												"type_active_checkbox" 		=> ($this->arr[type] == "checkbox" ? " SELECTED " : " "),
												"type_active_radiobutton" => ($this->arr[type] == "radiobutton" ? " SELECTED " : " "),
												"type_active_listbox" 		=> ($this->arr[type] == "listbox" ? " SELECTED " : " "),
												"type_active_multiple" 		=> ($this->arr[type] == "multiple" ? " SELECTED " : " "),
												"element_info"						=> $this->arr[info],
												"element_order"						=> $this->arr[order]));

			$lb = "";
			if ($this->arr[type] == "listbox")		
			{	
				for ($b=0; $b < ($this->arr[listbox_count]+1); $b++)
				{
					$this->vars(array("listbox_item_id" 		=> "element_".$this->id."_lb_".$b,
														"listbox_item_value"	=> $this->arr[listbox_items][$b],
														"listbox_item_num"		=> $this->arr[listbox_values][$b]));
					$lb.=$this->parse("LISTBOX_ITEMS");
				}	
			}

			$mu = "";
			if ($this->arr[type] == "multiple")		
			{	
				for ($b=0; $b < ($this->arr[multiple_count]+1); $b++)
				{
					$this->vars(array("multiple_item_id" 		=> "element_".$this->id."_mul_".$b,
														"multiple_item_value"	=> $this->arr[multiple_items][$b],
														"multiple_item_num"		=> $this->arr[multiple_values][$b]));
					$mu.=$this->parse("MULTIPLE_ITEMS");
				}	
			}

			$grp="";
			$val = "";
			if ($this->arr[type] == "radiobutton" || $this->arr[type] == "checkbox")
			{
				$this->vars(array("element_value" => $this->arr[value]));
				$val = $this->parse("VALUE");
				$grp = $this->parse("GROUP");
			}
									
			$this->vars(array("LISTBOX_ITEMS"	=> $lb,
												"MULTIPLE_ITEMS"=> $mu,
												"GROUP"					=> $grp,
												"VALUE"					=> $val));
			return $this->parse();
		}

		function save(&$arr)
		{
			$this->quote(&$arr);
			extract($arr);

			$base = "element_".$this->id;
			
			$var=$base."_text";
			$this->arr[text] = $$var;
			$var=$base."_type";
			if ($$var == "delete")
				return $this->del();

			$this->arr[type] = $$var;
			$var = $base."_info";
			$this->arr[info]=$$var;
			$var=$base."_front";
			$this->arr[front] = $$var;

			if ($this->arr[type] == "listbox")
			{
				$cnt=$this->arr[listbox_count]+1;
				for ($b=0; $b < $cnt; $b++)
				{
					$var=$base."_lb_".$b;
					$this->arr[listbox_items][$b] = $$var;
				}
				if ($this->arr[listbox_items][$cnt-1] == "")		// if the last item is empty, remove it
					$cnt--;

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

			if ($this->arr[type] == "textbox" || $this->arr[type] == "textarea" || $this->arr[type] == "checkbox" || $this->arr[type] == "radiobutton")
			{
				$var=$base."_def";
				$this->arr["default"] = $$var;
			}

			$var = $base."_order";
			$$var+=0;

			$this->save_final($$var);
		}

		function save_short()
		{
			$var = "element_".$this->id."_text";
			global $$var;
			$this->arr[text] = $$var;

			$var = "element_".$this->id."_order";
			global $$var;
			$$var+=0;

			$this->save_final($$var);
		}

		function save_final($ord = "")
		{
			$o = "";
			if ($ord != "")
				$o = ", ord = $ord ";

			$contents = serialize($this->arr);
			$this->update_object($this->id);
			$this->db_query("UPDATE form_elements SET content = '$contents' $o WHERE id = ".$this->id);
		}

		// this function deletes the element. called from $this->save();
		function del()
		{
			$this->delete_object($this->id);
		}

		function gen_action_html()
		{
			$this->read_template("admin_element_actions.tpl");
			$this->vars(array("element_id" => "element_".$this->id, "email" => $this->arr[email], "element_text" => $this->arr[text]));
			return $this->parse();
		}

		function set_style($id)
		{
			$this->arr[style] = $id;
			$this->save_final();
		}

/*		function gen_user_html(&$images)
		{
			$this->read_template("element.tpl");

			$this->vars(array("element_id" 					=> $this->id,
												"text"								=> ($this->arr[text] == "" ? "" : $images->proc_text($this->arr[text], $this->parent)),
												"info"								=> $images->proc_text($this->arr[info], $this->parent)));
																				
			$ta = "";
			if ($this->arr[type] == "textarea")
			{
				$tekst = "";
				if ($this->arr[text] != "")
					$tekst = $this->parse("TEXT_TEXTAREA");
	
				$this->vars(array("textarea_cols"	=> $this->arr[ta_cols],
													"textarea_rows"	=> $this->arr[ta_rows],
													"element_value"	=> ($this->entry_id ? $this->entry : $this->arr["default"]),
													"TEXTAREA_TEXT"	=> $tekst));
				$ta = $this->parse("TEXTAREA");
			}
			
			$rb = "";
			if ($this->arr[type] == "radiobutton")
			{
				$this->vars(array("radio_value"		=> $this->id,
													"radio_checked" => ($this->entry_id ? ($this->entry == $this->id ? " CHECKED " : " " ) : ($this->arr["default"] == 1 ? " CHECKED " : "")),
													"element_id"		=> "radio_group_".$this->arr[group]));
				$rb = $this->parse("RADIOBUTTON");
			}
			
			$lb = "";
			if ($this->arr[type] == "listbox")
			{
				$lbc ="";
				for ($b=0; $b < $this->arr[listbox_count]; $b++)
				{	
					if ($this->entry_id)
						$lbsel = ($this->entry == "element_".$this->id."_lbopt_".$b ? " SELECTED " : "");
					else
						$lbsel = ($this->arr[listbox_default] == $b ? " SELECTED " : "");

					$this->vars(array("listbox_option_id" 				=> "element_".$this->id."_lbopt_".$b,
														"listbox_option"						=> $this->arr[listbox_items][$b],
														"listbox_option_selected" 	=> $lbsel));
					$lbc.=$this->parse("LISTBOX_ITEMS");
				}
				$tekst = "";
				if ($this->arr[text] != "")
					$tekst = $this->parse("LISTBOX_TEXT");
					
				$this->vars(array("LISTBOX_ITEMS"	=>	$lbc, "LISTBOX_TEXT" => $tekst));
				$lb = $this->parse("LISTBOX");
			}
				
			$mu = "";
			if ($this->arr[type] == "multiple")
			{
				$muc ="";
				for ($b=0; $b < $this->arr[multiple_count]; $b++)
				{
					$sel = false;
					if ($this->entry_id)
					{
						$ec=$this->entry;
						if (gettype($ec) == "array")
						{
							reset($ec);
							while (list($k, $v) = each($ec))
							{
								if ($v == $b)
									$sel = true;
							}
						}
					}
					else
						$sel = ($this->arr[multiple_defaults][$b] == 1 ? true : false);

					$this->vars(array("multiple_option_id" 				=> $b,
														"multiple_option"						=> $this->arr[multiple_items][$b],
														"multiple_option_selected" 	=> ($sel == true ? " SELECTED " : "")));
					$muc.=$this->parse("MULTIPLE_ITEMS");
				}

				$tekst = "";
				if ($this->arr[text] != "")
					$tekst = $this->parse("MULTIPLE_TEXT");
					
				$this->vars(array("MULTIPLE_ITEMS"	=>	$muc, "MULTIPLE_TEXT" => $tekst));
				$mu = $this->parse("MULTIPLE");
			}
			
			$cb = "";
			if ($this->arr[type] == "checkbox")
			{
				$sel = ($this->entry_id ? ($this->entry == 1 ? " CHECKED " : " " ) : ($this->arr["default"] == 1 ? " CHECKED " : ""));
				$this->vars(array("checkbox_checked" => $sel));
				$cb=$this->parse("CHECKBOX");	
			}
			
			$tb = "";
			if ($this->arr[type] == "textbox")
			{
				$this->vars(array("element_value"	=> ($this->entry_id ? $this->entry : $this->arr["default"])));
				$tb = $this->parse("TEXTBOX");
			}
				
			$tx="";
			if ($this->arr[type] == "")
				$tx=$this->parse("TEXT");
			
			$cm="";
			if ($this->arr[info] != "")
				$cm = $this->parse("COMMENT");
				
			$this->vars(array("TEXTAREA" 				=> $ta,
												"RADIOBUTTON"			=> $rb,
												"LISTBOX"					=> $lb,
												"CHECKBOX"				=> $cb,
												"TEXTBOX"					=> $tb,
												"TEXT"						=> $tx,
												"COMMENT"					=> $cm,
												"RATING"					=> $re,
												"MULTIPLE"				=> $mu));
			return $this->parse();
		}*/

		function gen_user_html_not(&$images)		// function that doesn't use templates
		{
			$html="";
			$info = $images->proc_text($this->arr[info], $this->parent);
			$text = ($this->arr[text] == "" ? "" : $images->proc_text($this->arr[text], $this->parent));
			$elid = $this->id;
																	
			if ($this->arr[type] == "textarea")
			{
				if ($this->arr[text] != "")
					$html = $this->arr[text]."<br>";

				$html.="<textarea NAME='$elid' COLS='".$this->arr[ta_cols]."' ROWS='".$this->arr[ta_rows]."'>";
				$html.=($this->entry_id ? $this->entry : $this->arr["default"])."</textarea>";
			}
			
			if ($this->arr[type] == "radiobutton")
			{
				$ch = ($this->entry_id ? ($this->entry == $this->id ? " CHECKED " : " " ) : ($this->arr["default"] == 1 ? " CHECKED " : ""));
				$html="<input type='radio' NAME='radio_group_".$this->arr[group]."' VALUE='".$this->id."' $ch>$text";
			}
			
			if ($this->arr[type] == "listbox")
			{
				if ($this->arr[text] != "")
					$html = $this->arr[text]."<br>";
				
				$html.="<select name='$elid'>";
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
				
			if ($this->arr[type] == "multiple")
			{
				if ($this->arr[text] != "")
					$html = $this->arr[text]."<br>";

				$html.="<select NAME='".$elid."[]' MULTIPLE>";
				for ($b=0; $b < $this->arr[multiple_count]; $b++)
				{
					$sel = false;
					if ($this->entry_id)
					{
						$ec=$this->entry;
						if (gettype($ec) == "array")
						{
							reset($ec);
							while (list($k, $v) = each($ec))
							{
								if ($v == $b)
									$sel = true;
							}
						}
					}
					else
						$sel = ($this->arr[multiple_defaults][$b] == 1 ? true : false);

					$html.="<option ".($sel == true ? " SELECTED " : "")." VALUE='$b'>".$this->arr[multiple_items][$b];
				}
				$html.="</select>";
			}
			
			if ($this->arr[type] == "checkbox")
			{
				$sel = ($this->entry_id ? ($this->entry == 1 ? " CHECKED " : " " ) : ($this->arr["default"] == 1 ? " CHECKED " : ""));
				$html = "<input type='checkbox' NAME='$elid' VALUE='1' $sel>$text";
			}
			
			if ($this->arr[type] == "textbox")
				$html = "$text<input type='text' NAME='$elid' VALUE='".($this->entry_id ? $this->entry : $this->arr["default"])."'>";
				
			if ($this->arr[type] == "")
				$html = $text;
			
			if ($this->arr[info] != "")
				$html = "<br><font face='arial, geneva, helvetica' size='1'>&nbsp;&nbsp;$info</font>";

			return $html;
		}

		function process_entry(&$entry)
		{
			if ($this->arr[type] == "radiobutton")
				$var = "radio_group_".$this->arr[group];
			else
				$var = $this->id;

			global $$var;
										
			$entry[$this->id] = $$var;
		}

		function set_entry(&$arr, $e_id)
		{
			$this->entry = $arr[$this->id];
			$this->entry_id = $e_id;
		}

		function gen_show_html()
		{

			if (!$this->entry_id)
				return "";

			$this->read_template("show_user_element.tpl");
			$t = new db_images;

			$ta = "";
			if ($this->arr[type] == "textarea")
			{
				$this->vars(array("element_value"	=> $t->proc_text($this->entry, $this->entry_id)));
				$ta = $this->parse("TEXTAREA");
			}
					
			$rb = "";
			if ($this->arr[type] == "radiobutton")
			{
				$this->vars(array("radio_checked" => $this->entry == $this->id ? " Jah " : " Ei "));
				$rb = $this->parse("RADIOBUTTON");
			}
					
			$lb = "";
			if ($this->arr[type] == "listbox")
			{
				$sp = split("_", $this->entry, 10);
				$this->vars(array("listbox_option_selected" => $this->arr[listbox_items][$sp[3]]));
				$lb = $this->parse("LISTBOX");
			}
					
			$mb = "";
			if ($this->arr[type] == "multiple")
			{
				$ec=$this->entry;
				if (gettype($ec) == "array")		// check only if something was entered
				{
					reset($ec);
					while (list($k, $v) = each($ec))
					{
						$this->vars(array("multiple_option_selected" => ($this->arr[multiple_items][$v]." ")));
						$mb .= $this->parse("MULTIPLE");
					}
				}
			}

			$ch = "";
			if ($this->arr[type] == "checkbox")
			{
				$this->vars(array("checkbox_checked" => $this->entry == 1 ? "Jah " : " Ei "));
				$ch = $this->parse("CHECKBOX");	
			}
					
			$te = "";
			if ($this->arr[type] == "textbox")
			{
				$this->vars(array("element_value" => $t->proc_text($this->entry, $this->entry_id)));
				$te = $this->eparse("TEXTBOX");
			}
						
			$this->vars(array("element_text"	=> ($this->arr[text] == "" ? "&nbsp;" : $this->arr[text]),
												"TEXTBOX"				=> $te,
												"CHECKBOX"			=> $ch,
												"MULTIPLE"			=> $mb,
												"LISTBOX"				=> $lb,
												"RADIOBUTTON"		=> $rb,
												"TEXTAREA"			=> $ta));
			return $this->parse();
		}
	}
?>
