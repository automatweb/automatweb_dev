<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_entry_element.aw,v 2.49 2002/03/11 16:15:32 duke Exp $
// form_entry_element.aw - 
session_register("clipboard");
classload("currency");
lc_load("form");
load_vcl("date_edit");
lc_load("definition");

	class form_entry_element extends form_element
	{
		function form_entry_element()
		{
			$this->form_element();
			global $awt;
			$awt->start("form_entry_element::new()");
			$this->tpl_init("forms");
			$this->db_init();

			$this->parent = 0;
			$this->entry_id = 0;
			$this->id = 0;
			$this->currency = new currency;
			$awt->stop("form_entry_element::new()");
		}

		function gen_admin_html()
		{
			global $lc_form;
			if (is_array($lc_form))
			{
				$this->vars($lc_form);
			}
			$this->read_template("admin_element.tpl");

			// here we create the listboxes for selecting tables
			if (is_array($this->form->arr["save_tables"]))
			{
				$tbl_num = 0;
				foreach($this->form->arr["save_tables"] as $tbl => $tbcol)
				{
					$ta = $this->db_get_table($tbl);
					foreach($ta["fields"] as $fn => $fdata)
					{
						$this->vars(array(
							"tbl_num" => $tbl_num++,
							"table_name" => $tbl,
							"col_name" => $fn
						));
						$t_tb.=$this->parse("TBL");
					}
				}
				$this->vars(array("TBL" => $t_tb));
			}

			if ($this->form->arr["save_table"] == 1)
			{
				$this->vars(array("TABLE_DEFS" => $this->parse("TABLE_DEFS")));

				global $search_script;
				if (!$search_script)
				{
					$GLOBALS["search_script"] = true;
					$this->vars(array("SEARCH_SCRIPT" => $this->parse("SEARCH_SCRIPT")));
				}
			}

			$this->do_core_admin();

			return $this->parse();
		}

		////
		// !this function takes the changed properties of this element from the form and joins them together in the array of element properties 
		function save(&$arr)
		{
			return $this->do_core_save(&$arr);
		}


		function gen_user_html_not($prefix = "",$elvalues = array(),$no_submit = false)		// function that doesn't use templates
		{
			return $this->do_core_userhtml($prefix,$elvalues,$no_submit);
		}

		function process_entry(&$entry, $id,$prefix = "")
		{
			return $this->core_process_entry(&$entry,$id,$prefix);
		}

		function gen_show_html()
		{
			global $awt;
			$awt->start("form_entry_element::gen_show_html");
			$awt->count("form_entry_element::gen_show_html");

			$t = new db_images;

			global $lang_id;
			$html = "";
			if ($this->arr["type"] == "textarea")
			{
				$this->entry .= " ";
				$src = ($this->form->allow_html) ? $this->entry : htmlspecialchars($this->entry);
				$src = preg_replace("/((http(s?):\/\/)|(www\.))(.+?)\s/i", "<a href=\"http$3://$4$5\" target=\"_blank\">$2$4$5</a>", $src);
				$src = preg_replace("/(\S*?\@.*\.\w*)/i","<a href=\"mailto:$1\">$1</a>",$src);
				$html = str_replace("\n","<br>",$src);
			}
					
			if ($this->arr["type"] == "radiobutton")
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
					
			if ($this->arr["type"] == "listbox")
			{
				if ($this->arr["subtype"] == "relation" && $this->arr["rel_element"] && $this->arr["rel_form"])
				{
					$this->make_relation_listbox_content();
				}
				$sp = split("_", $this->entry, 10);
				$sp[3] = (int)$sp[3];
				if ($this->form->lang_id != $lang_id)
				{
					$html=$this->arr["listbox_lang_items"][$lang_id][$sp[3]];
				}
				else
				{
					$html=$this->arr["listbox_items"][$sp[3]];
				}
			}
					
			if ($this->arr["type"] == "multiple")
			{
				$ec=explode(",",$this->entry);
				reset($ec);
				foreach($ec as $v)
				{
					$vx = (int)$v;
					if ($this->form->lang_id != $lang_id)
					{
						$html.=($this->arr["multiple_lang_items"][$lang_id][$vx]." ");
					}
					else
					{
						$html.=($this->arr["multiple_items"][$vx]." ");
					}
				}
			}

			if ($this->arr["type"] == "checkbox")
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
					
			if ($this->arr["type"] == "textbox")
			{
				$this->entry .= " ";
				$src = ($this->form->allow_html) ? $this->entry : htmlspecialchars($this->entry);
				$src = preg_replace("/((http(s?):\/\/)|(www\.))(.+?)\s/i", "<a href=\"http$3://$4$5\" target=\"_blank\">$2$4$5</a>", $src);
				$src = preg_replace("/(\S*?\@.*)\s/i","<a href=\"mailto:$1\">$1</a>",$src);
				$html = $src;
			}

			if ($this->arr["type"] == "price")
			{
				$html.=$this->entry;
				// currencies are cached the first time we ask for one
				if ($this->arr["price_cur"])
				{
					if ($this->form->active_currency)
					{
						// if the currency in which to show price is set, then show that currency
						$cur = $this->currency->get($this->form->active_currency);
						$in_dem = (double)$cur["rate"]*(double)$this->entry;
						$html.=$cur["name"];
					}
					else
					{
						$cur = $this->currency->get($this->arr["price_cur"]);
						$in_dem = (double)$cur["rate"]*(double)$this->entry;
						$html.=$cur["name"];
					}

					if (is_array($this->arr["price_show"]))
					{
						foreach($this->arr["price_show"] as $prid)
						{
							$cur = $this->currency->get($prid);
							$val = round((double)$cur["rate"]*$in_dem,2);
							$html.=$this->arr["price_sep"].$val.$cur["name"];
						}
					}
				}
			}

			if ($this->arr["type"] == "date")
			{
				$html.=$this->get_date_value();
			}

			if ($this->arr["type"] == "file")
			{
				$im = new db_images;
				if (!is_array($this->entry) && $this->entry != "")
				{
					$this->entry = unserialize($this->entry);
				}
				if (is_array($this->entry))	// if this is an array, then there is a file that must be shown in place
				{
					$row = $im->get_img_by_id($this->entry["id"]);
					$row["url"] = str_replace("?","/",$row["url"]);
					if ($this->arr["ftype"] == 1)
					{
						$html.="<img src='".$row["url"]."'>";
					}
					else
					{
						$linktext = ($this->entry["name"]) ? $this->entry["name"] : $this->arr["flink_text"];
						$html.="<a target='_new' href='".$row["url"]. "/" . $linktext . "'>".$linktext."</a>";
					}
				}

			}

			if ($this->arr["type"] == "link")
			{
				if ($this->arr["subtype"] == "show_op")
				{
					$html.="<a href='".$this->mk_my_orb("show_entry", array("id" => $this->form->id, "entry_id" => $this->entry_id, "op_id" => $this->arr["link_op"], "section" => $GLOBALS["section"]),"form")."'>".$this->arr["link_text"]."</a>";
				}
				else
				{
					$html.="<a href='".$this->entry["address"]."'>".$this->entry["text"]."</a>";
				}
			}

			if ($this->arr["type"] == "button")
			{
				if ($lang_id == $this->form->lang_id)
				{
					$butt = $this->arr["button_text"];
				}
				else
				{
					$butt = $this->arr["lang_button_text"][$lang_id];
				}
				if ($this->arr["subtype"] == "order")
				{
					$loc = $this->mk_my_orb("show", array("id" => $this->arr["order_form"], "load_entry_data" => $this->form->entry_id,"section" => $GLOBALS["section"]),"form");
					$html = "<input type='submit' VALUE='".$butt."' onClick=\"window.location='".$loc."';return false;\">";
				}
				else
				if ($this->arr["subtype"] == "close")
				{
					$html = "<input type='submit' VALUE='".$butt."' onClick=\"window.close();return false;\">";
				}
			}

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
			global $baseurl;

			if (!$this->arr["ignore_text"])
			{
				if ($this->arr["type"] != "")
				{
					$sep_ver = ($this->arr["text_distance"] > 0 ? "<br><img src='$baseurl/images/transa.gif' width='1' height='".$this->arr["text_distance"]."' border='0'><br>" : "<br>");
					$sep_hor = ($this->arr["text_distance"] > 0 ? "<img src='$baseurl/images/transa.gif' height='1' width='".$this->arr["text_distance"]."' border='0'>" : "");
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
			}

			if ($info != "")
			{
				$html .= "<br><font face='arial, geneva, helvetica' size='1'>&nbsp;&nbsp;$info</font>";
			}

			if (!$this->arr["ignore_text"])
			{
				if ($this->arr["sep_type"] == 1)	// reavahetus
				{
					$html.="<br>";
				}
				else
				if ($this->arr["sep_pixels"] > 0)
				{
					$html.="<img src='$baseurl/images/transa.gif' width=".$this->arr["sep_pixels"]." height=1 border=0>";
				}

				if ($this->arr["sep_type"] == 1)	// reavahetus
				{
					$html.="<br>";
				}
				else
				// this is bad too. We need an image called transa.gif for each site.
				// so? of course we need an image like that? what the fuck is wrong with that? - terryf
				if ($this->arr["sep_pixels"] > 0)
				{
					$html.="<img src='$baseurl/images/transa.gif' width=".$this->arr["sep_pixels"]." height=1 border=0>";
				}
			}

			$awt->stop("form_entry_element::gen_show_html");
			return $html;
		}

		function gen_show_text()
		{
			if (!$this->entry_id)
				return "";

			global $lang_id;
			if (!$this->arr["ignore_text"])
			{
				if ($this->form->lang_id == $lang_id)
				{
					$text = $this->arr["text"];
				}
				else
				{
					$text = $this->arr["lang_text"][$lang_id];
				}
			}

			$html = $text;
			if ($html != "")
			{
				$html = $text." ";
			}

			$xval = $this->get_value();
			if ($xval != "")
			{
				$html.=$xval." ";
			}

			return strip_tags($html);
		}
	}
?>
