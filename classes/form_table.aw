<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_table.aw,v 2.12 2001/09/05 13:13:06 duke Exp $
global $orb_defs;
$orb_defs["form_table"] = "xml";
lc_load("form");
class form_table extends form_base
{
	function form_table()
	{
		$this->form_base();
		$this->sub_merge = 1;
		lc_load("definition");
		global $lc_form;
		if (is_array($lc_form))
		{
			$this->vars($lc_form);}
	}

	////
	// !shows the adding form
	function add($arr)
	{
		extract($arr);
		$this->read_template("add_table.tpl");
		$this->mk_path($parent,LC_FORM_TABLE_ADD_FORM_TABLE);

		classload("style");
		$s = new style;

		$css = $s->get_select(0,ST_CELL);

		classload("objects");
		$ob = new db_objects;
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"forms" => $this->multiple_option_list(array(),$this->get_list(FTYPE_ENTRY,false,true)),
			"tablestyles" => $this->picker(0,$s->get_select(0,ST_TABLE)),
			"header_normal" => $this->picker(0,$css),
			"header_sortable" => $this->picker(0,$css),
			"header_sorted" => $this->picker(0,$css),
			"content_style1" => $this->picker(0,$css),
			"content_style2" => $this->picker(0,$css),
			"link_style" => $this->picker(0,$css),
			"content_sorted_style1" => $this->picker(0,$css),
			"content_sorted_style2" => $this->picker(0,$css),
			"moveto" => $this->multiple_option_list($this->table["moveto"], $ob->get_list())
		));
		return $this->parse();
	}

	////
	// !saves or adds the form table
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
			$forms = $this->get_forms_for_table($id);
			$els = $this->get_elements_for_forms($forms,true);

			$this->load_table($id);
			$this->table["defs"] = array();
			if (is_array($columns))
			{
				foreach($columns as $col => $ar)
				{
					if (is_array($ar))
					{
						foreach($ar as $elid)
						{
							$this->table["defs"][$col]["el"][$elid] = $elid;
							if (is_number($elid) && isset($els[$elid]))
							{
								$this->table["defs"][$col]["el_forms"][$elid] = $els[$elid];
							}
						}
					}
					$this->table["defs"][$col]["lang_title"] = $names[$col];
					$this->table["defs"][$col]["sortable"] = $sortable[$col];
				}
			}

			if (is_array($del))
			{
//				echo "del <br>";
				$td = array();
				$nc = 0;
				for ($i=0; $i < $num_cols; $i++)
				{
					if ($del[$i] != 1)
					{
						$td[$nc] = $this->table["defs"][$i];
						$nc++;
					}
				}
				$num_cols = $nc;
				$this->table["defs"] = $td;
			}

			if (is_array($addaf))
			{
//				echo "addaf <br>";
				$td = array();
				$nc = 0;
				for ($i=0; $i < $num_cols; $i++)
				{
					if ($addaf[$i-1] == 1)
					{
						$nc++;
					}
					
					$td[$nc] = $this->table["defs"][$i];
					$nc++;
				}
				$num_cols = $nc;
				$this->table["defs"] = $td;
			}

			$this->table["moveto"] = array();
			if (is_array($moveto))
			{
				foreach($moveto as $mfid)
				{
					$this->table["moveto"][$mfid] = $mfid;
				}
			}
			$this->table["table_style"] = $tablestyle;
			$this->table["header_normal"] = $header_normal;
			$this->table["header_sortable"] = $header_sortable;
			$this->table["header_sorted"] = $header_sorted;
			$this->table["content_style1"] = $content_style1;
			$this->table["content_style2"] = $content_style2;
			$this->table["link_style"] = $link_style;
			$this->table["content_sorted_style1"] = $content_sorted_style1;
			$this->table["content_sorted_style2"] = $content_sorted_style2;
			$this->table["submit_text"] = $submit_text;
			$this->table["submit_top"] = $submit_top;
			$this->table["submit_bottom"] = $submit_bottom;
			$this->table["user_button_top"] = $user_button_top;
			$this->table["user_button_bottom"] = $user_button_bottom;
			$this->table["user_button_text"] = $user_button_text;
			$this->table["user_button_url"] = $user_button_url;
			$this->table["view_col"] = $viewcol;
			$this->table["change_col"] = $changecol;
			$this->table["view_new_win"] = $view_new_win;
			$this->table["new_win_x"] = $new_win_x;
			$this->table["new_win_y"] = $new_win_y;
			classload("xml");
			$x = new xml;
			$co = $x->xml_serialize($this->table);
			$this->quote(&$co);
//			echo "num_cols = $num_cols <br>";
			$q = "UPDATE form_tables SET num_cols = '$num_cols' , content = '$co' WHERE id = $id";
			$this->db_query($q);
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "class_id" => CL_FORM_TABLE, "name" => $name, "comment" => $comment));
			$this->db_query("INSERT INTO form_tables(id,num_cols) VALUES($id,'$num_cols')");
		}

		$this->db_query("DELETE FROM form_table2form WHERE table_id = $id");
		if (is_array($forms))
		{
			foreach($forms as $fid)
			{
				$this->db_query("INSERT INTO form_table2form(form_id,table_id) VALUES($fid,$id)");
			}
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	////
	// !shows the change form
	function change($arr)
	{
		extract($arr);
		$this->read_template("add_table.tpl");
		$tb = $this->load_table($id);
		$tbo = $this->get_object($id);
		$this->mk_path($this->table_parent, LC_FORM_TABLE_CHANGE_FORM_TABLE);

		$forms = $this->get_forms_for_table($id);

		$els = $this->get_elements_for_forms($forms);

		// teeme esimese rea elementide nimega
		foreach($els as $elid => $elname)
		{
			$this->vars(array(
				"el_name" => $elname
			));
			$this->parse("TITLE");
		}

		classload("languages");
		$lang = new languages;
		$lar = $lang->listall();
		foreach($lar as $la)
		{
			$this->vars(array(
				"lang_name" => $la["name"]
			));
			$this->parse("LANG_H");
		}

		for ($col=0; $col < $this->table["cols"]; $col++)
		{
			$this->vars(array(
				"column" => $col,
				"sortable" => checked($this->table["defs"][$col]["sortable"])
			));
		
			$lit = "";
			foreach($lar as $la)
			{
				if ($tbo["lang_id"] == $la["id"] && $this->table["defs"][$col]["lang_title"][$la["id"]] == "")
				{
					$lt = $this->table["defs"][$col]["title"];
				}
				else
				{
					$lt = $this->table["defs"][$col]["lang_title"][$la["id"]];
				}
				$this->vars(array(
					"lang_id" => $la["id"],
					"c_name" => $lt
				));
				$lit.=$this->parse("LANG");
			}
			$this->vars(array("LANG" => $lit));

			$c = "";
			foreach($els as $elid => $elname)
			{
				if (is_array($this->table["defs"][$col]["el"]))	// backward compatibility sucks
				{
					$chk = checked(in_array($elid,$this->table["defs"][$col]["el"]));
				}
				else
				{
					$chk = checked($this->table["defs"][$col]["el"] == $elid);
				}
				$this->vars(array(
					"el_id" => $elid,
					"checked" => $chk
				));
				$c.=$this->parse("COL");
			}
			if (is_array($this->table["defs"][$col]["el"]))	// backward compatibility sucks
			{
				$this->vars(array(
					"change_checked" => checked(in_array("change",$this->table["defs"][$col]["el"])),
					"view_checked" => checked(in_array("view",$this->table["defs"][$col]["el"])),
					"special_checked" => checked(in_array("special",$this->table["defs"][$col]["el"])),
					"delete_checked" => checked(in_array("delete",$this->table["defs"][$col]["el"])),
					"uid_checked" => checked(in_array("uid",$this->table["defs"][$col]["el"])),
					"created_checked" => checked(in_array("created",$this->table["defs"][$col]["el"])),
					"modified_checked" => checked(in_array("modified",$this->table["defs"][$col]["el"])),
					"active_checked" => checked(in_array("active",$this->table["defs"][$col]["el"])),
					"chpos_checked" => checked(in_array("chpos",$this->table["defs"][$col]["el"]))
				));
			}
			else
			{
				$this->vars(array(
					"change_checked" => checked($this->table["defs"][$col]["el"] == "change"),
					"view_checked" => checked($this->table["defs"][$col]["el"] == "view"),
					"special_checked" => checked($this->table["defs"][$col]["el"] == "special"),
					"delete_checked" => checked($this->table["defs"][$col]["el"] == "delete"),
					"uid_checked" => checked($this->table["defs"][$col]["el"] == "uid"),
					"created_checked" => checked($this->table["defs"][$col]["el"] == "created"),
					"modified_checked" => checked($this->table["defs"][$col]["el"] == "modified"),
					"active_checked" => checked($this->table["defs"][$col]["el"] == "active"),
					"chpos_checked" => checked($this->table["defs"][$col]["el"] == "chpos")
				));
			}
			$this->vars(array(
				"COL" => $c,
			));
			$this->parse("ROW");
		}

		$vc = "";
		foreach($els as $elid => $elname)
		{
			$this->vars(array(
				"el_id" => $elid,
				"checked" => checked($this->table["view_col"] == $elid)
			));
			$vc.=$this->parse("VCOL");
		}
		$this->vars(array(
			"VCOL" => $vc,
			"v_view_checked" => checked($this->table["view_col"] == "view")
		));

		$cc = "";
		foreach($els as $elid => $elname)
		{
			$this->vars(array(
				"el_id" => $elid,
				"checked" => checked($this->table["change_col"] == $elid)
			));
			$cc.=$this->parse("CCOL");
		}
		$this->vars(array(
			"CCOL" => $cc,
			"v_change_checked" => checked($this->table["view_col"] == "change")
		));

		classload("style");
		$s = new style;
		classload("objects");
		$ob = new db_objects;
		$css = $s->get_select(0,ST_CELL);
		$this->vars(array(
			"name" => $this->table_name,
			"comment" => $this->table_comment,
			"num_cols" => $this->table["cols"],
			"forms" => $this->multiple_option_list($forms, $this->get_list(FTYPE_ENTRY,false,true)),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"CHANGE" => $this->parse("CHANGE"),
			"tablestyles" => $this->picker($this->table["table_style"],$s->get_select(0,ST_TABLE)),
			"new_win_x" => ($this->table["new_win_x"]) ? $this->table["new_win_x"] : 100,
			"new_win_y" => ($this->table["new_win_y"]) ? $this->table["new_win_y"] : 100,
			"view_new_win" => checked($this->table["view_new_win"]),
			"header_normal" => $this->picker($this->table["header_normal"],$css),
			"header_sortable" => $this->picker($this->table["header_sortable"],$css),
			"header_sorted" => $this->picker($this->table["header_sorted"],$css),
			"content_style1" => $this->picker($this->table["content_style1"],$css),
			"content_style2" => $this->picker($this->table["content_style2"],$css),
			"link_style" => $this->picker($this->table["link_style"],$css),
			"content_sorted_style1" => $this->picker($this->table["content_sorted_style1"],$css),
			"content_sorted_style2" => $this->picker($this->table["content_sorted_style2"],$css),
			"moveto" => $this->multiple_option_list($this->table["moveto"], $ob->get_list()),
			"top_checked" => checked($this->table["submit_top"]),
			"bottom_checked" => checked($this->table["submit_bottom"]),
			"submit_text" => $this->table["submit_text"],
			"user_button_top" => checked($this->table["user_button_top"]),
			"user_button_bottom" => checked($this->table["user_button_bottom"]),
			"user_button_text" => $this->table["user_button_text"],
			"user_button_url" => $this->table["user_button_url"]
		));
		return $this->parse();
	}

	////
	// !returns an array of forms that this table gets elements from
	function get_forms_for_table($id)
	{
		global $awt;
		$awt->start("form_table::get_forms_for_table");
		$awt->count("form_table::get_forms_for_table");

		$ret = array();
		$this->db_query("SELECT * FROM form_table2form WHERE table_id = $id");
		while ($row = $this->db_next())
		{
			$ret[$row["form_id"]] = $row["form_id"];
		}
		$awt->stop("form_table::get_forms_for_table");
		return $ret;
	}

	////
	// !starts the table data definition for table $id
	// $header_attribs = an array of get items in the url, used to sort the table
	function start_table($id,$header_attribs)
	{
		global $awt;
		$awt->start("form_table::start_table");
		$awt->count("form_table::start_table");

		load_vcl("table");
		$this->t = new aw_table(array(
			"prefix" => "fg_".$id,
			"self" => $PHP_SELF,
			"imgurl" => $GLOBALS["baseurl"] . "/automatweb/images"
		));
		$this->t->parse_xml_def_string($this->get_xml($id));
		$this->t->set_header_attribs($header_attribs);
		$awt->stop("form_table::start_table");
	}

	////
	// !adds another row of data to the table
	function row_data($dat)
	{
		global $awt;
		$awt->start("form_table::row_data");
		$awt->count("form_table::row_data");

		// hmph. here we must preprocess the data if any columns have more than 1 elements assigned to them, cause then the column names will be el_col_[col_number] not element names
		for ($col = 0; $col < $this->arr["cols"]; $col++)
		{
			$cc = $this->table["defs"][$col];
			if (is_array($cc["el"]) && count($cc["el"]) > 1)
			{
				$str = array();
				foreach($cc["el"] as $elid)
				{
					$str[]=$dat["ev_".$elid];
				}
				$dat["ev_col_".$col] = join(",",$str);
			}
		}

		$this->t->define_data($dat);
		$awt->stop("form_table::row_data");
	}

	////
	// !reads the loaded entries from array of forms $forms and adds another row of data to the table
	function row_data_from_form($forms,$special = "")
	{
		global $awt;
		$awt->start("form_table::row_data_from_form");
		$awt->count("form_table::row_data_from_form");

		$rds = array();
		foreach($forms as $form)
		{
			$rds["ev_change"] = "<a href='".$this->mk_my_orb("show", array("id" => $form->id, "entry_id" => $form->entry_id), "form")."'>Muuda</a>";
			$rds["ev_view"] = "<a href='".$this->mk_my_orb("show_entry", array("id" => $form->id,"entry_id" => $form->entry_id, "op_id" => $form->arr["search_outputs"][$form->id]),"form")."'>Vaata</a>";
			$rds["ev_special"] = $special;
			for ($row = 0; $row < $form->arr["rows"]; $row++)
			{
				for ($col = 0; $col < $form->arr["cols"]; $col++)
				{
					$form->arr["contents"][$row][$col]->get_els(&$elar);
					reset($elar);
					while (list(,$el) = each($elar))
					{
						$rds["ev_".$el->get_id()] = $el->get_value();
					}
				}
			}
		}
		$this->t->define_data($rds);
		$awt->stop("form_table::row_data_from_form");
	}

	////
	// !draws the table and returns the html for the current table
	function finish_table()
	{
		global $awt;
		$awt->start("form_table::finish_table");
		$awt->count("form_table::finish_table");

		if (is_object($this->t))
		{
			$this->t->sort_by(array("field" => $GLOBALS["sortby"],"sorder" => $GLOBALS["sort_order"]));
			$css = $this->get_css();
			$contents = $this->t->draw();
			return $this->get_css().$contents;
		}
		$awt->stop("form_table::finish_table");
		return "";
	}

	////
	// !shows all the entries for the logged in user of form ($form_id) or chain ($chain_id) with table $table_id
	function show_user_entries($arr)
	{
		extract($arr);
		if ($form_id)
		{
			return $this->show_user_form_entries($arr);
		}


		global $awt;
		$awt->start("form_table::show_user_entries");
		$awt->count("form_table::show_user_entries");


		global $section;
		$this->start_table($table_id,array("class" => "form_table", "action" => "show_user_entries", "form_id" => $form_id, "chain_id" => $chain_id, "table_id" => $table_id,"section" => $section,"op_id" => $op_id));

		// leiame k6ik sisestused mis on tehtud $uid poolt $chain_id jaox.
		$this->load_chain($chain_id);

		$eids = array();
		$this->db_query("SELECT id FROM form_chain_entries WHERE uid = '".$GLOBALS["uid"]."'");
		while ($row = $this->db_next())
		{
			$eids[] = $row["id"];
		}

		$tbls = "";
		$joins = "";
		reset($this->chain["forms"]);
		list($fid,) = each($this->chain["forms"]);
		while(list($ch_fid,) = each($this->chain["forms"]))
		{
			if ($ch_fid != $fid)
			{
				$tbls.=",form_".$ch_fid."_entries.*";
				$joins.=" LEFT JOIN form_".$ch_fid."_entries ON form_".$ch_fid."_entries.chain_id = form_".$fid."_entries.chain_id ";
			}
		}
		
		$eids = join(",", $eids);
		if ($eids != "")
		{
			$this->db_query("SELECT form_".$fid."_entries.id as entry_id, form_".$fid."_entries.chain_id as chain_entry_id, form_".$fid."_entries.* $tbls FROM form_".$fid."_entries LEFT JOIN objects ON objects.oid = form_".$fid."_entries.id $joins WHERE objects.status != 0 AND form_".$fid."_entries.chain_id in ($eids)");
			while ($row = $this->db_next())
			{
				$row["ev_change"] = "<a href='".$this->mk_my_orb("show", array("id" => $chain_id,"entry_id" => $row["chain_entry_id"]), "form_chain")."'>Muuda</a>";
				$row["ev_view"] = "<a href='".$this->mk_my_orb("show_entry", array("id" => $fid,"entry_id" => $row["entry_id"], "op_id" => $op_id,"section" => $section),"form")."'>Vaata</a>";		
				$row["ev_delete"] = "<a href='".$this->mk_my_orb(
					"delete_entry", 
						array(
							"id" => $fid,
							"entry_id" => $row["entry_id"], 
							"after" => $this->binhex($this->mk_my_orb("show_user_entries", array("chain_id" => $chain_id, "table_id" => $table_id, "op_id" => $op_id,"section" => $section)))
						),
					"form")."'>Kustuta</a>";
				$this->row_data($row);
			}
		}

		$this->t->sort_by(array("field" => $GLOBALS["sortby"],"sorder" => $GLOBALS["sort_order"]));
		$tbl = $this->get_css();
		$tbl.="<form action='reforb.aw' method='POST'>\n";
		if ($this->table["submit_top"])
		{
			$tbl.="<input type='submit' value='".$this->table["submit_text"]."'>";
		}
		if ($this->table["user_button_top"])
		{
			$tbl.="&nbsp;<input type='submit' value='".$this->table["user_button_text"]."' onClick=\"window.location='".$this->table["user_button_url"]."';return false;\">";
		}
		$tbl.=$this->t->draw();

		if ($this->table["submit_bottom"])
		{
			$tbl.="<input type='submit' value='".$this->table["submit_text"]."'>";
		}
		if ($this->table["user_button_bottom"])
		{
			$tbl.="&nbsp;<input type='submit' value='".$this->table["user_button_text"]."' onClick=\"window.location='".$this->table["user_button_url"]."';return false;\">";
		}
		$tbl.= $this->mk_reforb("submit_table", array("return" => $this->binhex($this->mk_my_orb("show_entry", array("id" => $this->id, "entry_id" => $entry_id, "op_id" => $output_id)))));
		$tbl.="</form>";
		$awt->stop("form_table::show_user_entries");
		return $tbl;
	}

	////
	// !shows all the entries for the logged in user of form ($form_id) with table $table_id
	function show_user_form_entries($arr)
	{
		global $awt;
		$awt->start("form_table::show_user_form_entries");
		$awt->count("form_table::show_user_form_entries");

		extract($arr);

		global $section;
		$this->start_table($table_id,array("class" => "form_table", "action" => "show_user_entries", "form_id" => $form_id, "chain_id" => $chain_id, "table_id" => $table_id,"section" => $section,"op_id" => $op_id));

		// leiame k6ik sisestused mis on tehtud $uid poolt $form_id jaox.
		$this->load($form_id);

		$this->db_query("SELECT form_".$fid."_entries.* FROM form_".$fid."_entries LEFT JOIN objects ON objects.oid = form_".$fid."_entries.id WHERE objects.status != 0 AND objects.createdby = '".$GLOBALS["uid"]."'");
		while ($row = $this->db_next())
		{
			$row["ev_change"] = "<a href='".$this->mk_my_orb("show", array("id" => $form_id,"entry_id" => $row["id"]), "form")."'>Muuda</a>";
			$row["ev_view"] = "<a href='".$this->mk_my_orb("show_entry", array("id" => $form_id,"entry_id" => $row["id"], "op_id" => $op_id,"section" => $section),"form")."'>Vaata</a>";		
			$row["ev_delete"] = "<a href='".$this->mk_my_orb(
				"delete_entry", 
					array(
						"id" => $fid,
						"entry_id" => $row["entry_id"], 
						"after" => $this->binhex($this->mk_my_orb("show_user_entries", array("form_id" => $form_id, "table_id" => $table_id, "op_id" => $op_id,"section" => $section)))
					),
				"form")."'>Kustuta</a>";
			$this->row_data($row);
		}

		$this->t->sort_by(array("field" => $GLOBALS["sortby"],"sorder" => $GLOBALS["sort_order"]));
		$tbl = $this->get_css();
		$tbl.="<form action='reforb.aw' method='POST'>\n";
		if ($this->table["submit_top"])
		{
			$tbl.="<input type='submit' value='".$this->table["submit_text"]."'>";
		}
		if ($this->table["user_button_top"])
		{
			$tbl.="&nbsp;<input type='submit' value='".$this->table["user_button_text"]."' onClick=\"window.location='".$this->table["user_button_url"]."';return false;\">";
		}
		$tbl.=$this->t->draw();

		if ($this->table["submit_bottom"])
		{
			$tbl.="<input type='submit' value='".$this->table["submit_text"]."'>";
		}
		if ($this->table["user_button_bottom"])
		{
			$tbl.="&nbsp;<input type='submit' value='".$this->table["user_button_text"]."' onClick=\"window.location='".$this->table["user_button_url"]."';return false;\">";
		}
		$tbl.= $this->mk_reforb("submit_table", array("return" => $this->binhex($this->mk_my_orb("show_entry", array("id" => $this->id, "entry_id" => $entry_id, "op_id" => $output_id)))));
		$tbl.="</form>";
		$awt->stop("form_table::show_user_form_entries");
		return $tbl;
	}

	////
	// !returns an array of forms used in the table. each entry in the array is an array of elements in that form that are used
	// assumes the table is loaded already.
	function get_used_elements()
	{
		global $awt;
		$awt->start("form_table::get_used_elements");
		$ret = array();
		for ($i=0; $i < $this->table["cols"]; $i++)
		{
			if (is_array($this->table["defs"][$i]["el_forms"]))
			{
				foreach($this->table["defs"][$i]["el_forms"] as $elid => $fid)
				{
					$ret[$fid][$elid] = $elid;
				}
			}
		}
		$awt->stop("form_table::get_used_elements");
		return $ret;
	}

	////
	// !returns the xml definition for table $id to be passed to the table generator. if no id specified, presumes table is loaded already
	function get_xml($id = 0)
	{
		global $awt;
		$awt->start("form_table::get_xml");
		$awt->count("form_table::get_xml");

		if ($id)
		{
			$this->load_table($id);
		}

		$xml = "<?xml version='1.0'?>
			<tabledef>
			<definitions>
				<header_normal value=\"style_".$this->table["header_normal"]."\"/>
				<header_sortable value=\"style_".$this->table["header_sortable"]."\"/>
				<header_sorted value=\"style_".$this->table["header_sorted"]."\"/>
				<content_style1 value=\"style_".$this->table["content_style1"]."\"/>
				<content_style2 value=\"style_".$this->table["content_style2"]."\"/>
				<content_style1_selected value=\"style_".$this->table["content_sorted_style1"]."\"/>
				<content_style2_selected value=\"style_".$this->table["content_sorted_style2"]."\"/>\n";

		classload("style");
		$s = new style;
		if ($this->table["table_style"])
		{
				$xml.="<tableattribs ".$s->get_table_string($this->table["table_style"])."/>\n";
		}
		else
		{
				$xml.="<tableattribs />\n";
		}
		
		$xml.="</definitions>
			<data>\n";
		
		for ($col = 0; $col < $this->table["cols"]; $col++)
		{
			$cc = $this->table["defs"][$col];
			if (is_array($cc["el"]))
			{
				if (count($cc["el"]) == 1)
				{
					reset($cc["el"]);
					list($eln,) = each($cc["el"]);
				}
				else
				{
					$eln = "col_".$col;
				}
			}
			else
			{
				$eln = $cc["el"];
			}
			global $lang_id;
			if (isset($cc["lang_title"]))
			{
				$title = $cc["lang_title"][$lang_id];
			}
			else
			{
				$title = $cc["title"];
			}
			$xml.="<field name=\"ev_".$eln."\" caption=\"".$title."\" talign=\"center\" align=\"center\"";
			if ($cc["sortable"])
			{
				$xml.=" sortable=\"1\" ";
			}
			$xml.="/>\n";
		}
		$awt->stop("form_table::get_xml");
		return $xml.="\n</data></tabledef>";
	}

	function get_css($id = 0)
	{
		global $awt;
		$awt->start("form_table::get_css");
		$awt->count("form_table::get_css");

		if ($id)
		{
			$this->load_table($id);
		}
		classload("style");
		$s = new style;
		$op = "<style type=\"text/css\">\n";

		if ($this->table["header_normal"])
		{
			$op.= $s->get_css($this->table["header_normal"],$this->table["link_style"]);
		}
		if ($this->table["header_sortable"])
		{
			$op.= $s->get_css($this->table["header_sortable"],$this->table["link_style"]);
		}
		if ($this->table["header_sorted"])
		{
			$op.= $s->get_css($this->table["header_sorted"],$this->table["link_style"]);
		}
		if ($this->table["content_style1"])
		{
			$op.= $s->get_css($this->table["content_style1"],$this->table["link_style"]);
		}
		if ($this->table["content_style2"])
		{
			$op.= $s->get_css($this->table["content_style2"],$this->table["link_style"]);
		}
		if ($this->table["content_sorted_style1"])
		{
			$op.= $s->get_css($this->table["content_sorted_style1"],$this->table["link_style"]);
		}
		if ($this->table["content_sorted_style2"])
		{
			$op.= $s->get_css($this->table["content_sorted_style2"],$this->table["link_style"]);
		}
		$op.="</style>\n";
		$awt->stop("form_table::get_css");
		return $op;
	}
}

?>
