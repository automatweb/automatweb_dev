<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_table.aw,v 2.49 2002/08/24 12:31:17 duke Exp $
class form_table extends form_base
{
	function form_table()
	{
		$this->form_base();
		$this->sub_merge = 1;
		lc_load("definition");
		$this->lc_load("form","lc_form");
		$this->fakels = array(
			"change" => "Change", 
			"view" => "View", 
			"special" => "Special", 
			"delete" => "Delete", 
			"created" => "Created", 
			"modified" => "Modified", 
			"uid" => "UID", 
			"active" => "Acive", 
			"chpos" => "Move",
			"order" => "Order", 
			"select" => "Select",
			"jrk" => "Jrk",
			"cnt" => "Count"
		);

		$this->lang_id = aw_global_get("lang_id");
		$this->buttons = array("save" => "Salvesta", "add" => "Lisa", "delete" => "Kustuta", "move" => "Liiguta");
		$this->ru = aw_global_get("REQUEST_URI");
		$this->image = get_instance("image");
		$this->uid = aw_global_get("uid");
	}

	////
	// !returns an array of forms that this table gets elements from
	function get_forms_for_table($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM form_table2form WHERE table_id = $id");
		while ($row = $this->db_next())
		{
			$ret[$row["form_id"]] = $row["form_id"];
		}
		return $ret;
	}

	////
	//  !returns an array of tables that belong to this form
	function get_form_tables_for_form($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM form_table2form LEFT JOIN objects ON (form_table2form.table_id = objects.oid) WHERE form_id = '$id'");
                while ($row = $this->db_next())
                {
                        $ret[$row["table_id"]] = $row["name"];
                }
		return $ret;
	}

	////
	// !starts the table data definition for table $id
	function start_table($id)
	{
		load_vcl("table");
		$this->t = new aw_table(array("prefix" => "fg_".$id));
		$this->t->parse_xml_def_string($this->get_xml($id));
		$this->set_numeric_fields_in_table();

		if ($GLOBALS["tbl_dbg"])
		{
			echo "table id = $id <br>";
		}
		enter_function("form_table::groupsettings");
		$this->in_show_all_entries_groups = false;
		if (is_array($this->table["user_entries_except_grps"]) && count($this->table["user_entries_except_grps"]) > 0)
		{
			if (count(array_intersect($this->table["user_entries_except_grps"],aw_global_get("gidlist"))) > 0)
			{
				$this->in_show_all_entries_groups = true;
			}
		}

		$this->show_modifiedby = array();

		// find in which group settings group the current user is
		if (is_array($this->table["grpsettings"]))
		{
			$pri = -100000000;
			$show_grps = array();
			$found = false;
			foreach($this->table["grpsettings"] as $gst)
			{
				$diff = array_intersect(aw_global_get("gidlist"), $gst["from_grps"]);
				if ($gst["pri"] >= $pri && count($diff) > 0)
				{
					$show_grps = $gst["to_grps"];
					$found = true;
				}
			}
			if ($found)
			{
				// now figure out the list of users in the selected groups, so we can do the row_data check quickly
				$us = get_instance("users");
				foreach($show_grps as $gid)
				{
					$this->show_modifiedby += $us->getgroupmembers2($gid);
				}
			}
		}

		exit_function("form_table::groupsettings");

		// this is for no_show_oneliners 
		$this->num_lines = 0;

		// mark down the path
		$old_sk = $GLOBALS["old_sk"];
		$tbl_sk = $GLOBALS["tbl_sk"];
		$fg_table_sessions = aw_global_get("fg_table_sessions");

		// copy the first part of the path from the previous search
		$fg_table_sessions[$tbl_sk] = $fg_table_sessions[$old_sk];

		// check that everyhting is normal
		if (!is_array($fg_table_sessions[$tbl_sk]))
		{
			$fg_table_sessions[$tbl_sk] = Array();
		}

		if ($tbl_sk != "")
		{
			// and finally, add the current search to the path
			$num = count($fg_table_sessions[$tbl_sk]);
			$req = aw_global_get("REQUEST_URI");
			$req = str_replace("&print=1", "", $req);
			$req = str_replace("?print=1", "", $req);
			if ($fg_table_sessions[$tbl_sk][$num-1] != $req)
			{
				$fg_table_sessions[$tbl_sk][] = $req;
			}
		}
		aw_session_set("fg_table_sessions", $fg_table_sessions);
	}

	////
	// !adds another row of data to the table
	function row_data(&$dat,$form_id = 0,$section = 0 ,$op_id = 0,$chain_id = 0, $chain_entry_id = 0)
	{
		enter_function("form_table::row_data", array());
		
		// check if we should perhaps not show the damn entry
		if (isset($this->table["has_grpsettings"]) && $this->table["has_grpsettings"])
		{
			if ($this->show_modifiedby[$dat["createdby"]] != $dat["createdby"])
			{
				return;
			}
		}
		else
		if ($this->table["user_entries"])
		{
			if ($dat["createdby"] != $this->uid && !$this->in_show_all_entries_groups)
			{
				return;
			}
		}

		$this->num_lines++;

		if ($form_id != 0)
		{
			// here also make the view and other links
			$change_link = $this->get_link("change", $form_id,$section,$op_id,$chain_id,$chain_entry_id, $dat["entry_id"]);
			$this->table["change_cols"]["change"] = "change";
			$dat["ev_change"] = $this->table["texts"]["change"][$this->lang_id];
			foreach($this->table["change_cols"] as $chel)
			{
				$dat["ev_".$chel] = "<a href='".$change_link."'>".$dat["ev_".$chel]."</a>";
			}

			$show_link = $this->get_link("show", $form_id,$section,$op_id,$chain_id,$chain_entry_id, $dat["entry_id"]);
			$show_link_popup = $this->get_link("show_popup", $form_id,$section,$op_id,$chain_id,$chain_entry_id, $dat["entry_id"]);
			$this->table["view_cols"]["view"] = "view";
			$dat["ev_view"] = $this->table["texts"]["view"][$this->lang_id];
			foreach($this->table["view_cols"] as $v_el)
			{
				$cl = $this->get_col_for_el($v_el);
				$_caption = $dat["ev_".$v_el];

				$popdat = $this->table["defs"][$cl];
				if (isset($popdat["link_popup"]) && $popdat["link_popup"])
				{
					$show_link = sprintf("javascript:ft_popup('%s&type=popup','popup',%d,%d,%d,%d)",
						$show_link_popup,
						$popdat["link_popup_scrollbars"],
						!$popdat["link_popup_fixed"],
						$popdat["link_popup_width"],
						$popdat["link_popup_height"]
					);
				};

				// I don't see _targetwin being defined anywhere and this causes a warning
				//$dat["ev_".$v_el] = sprintf("<a href=\"%s\" %s>%s</a>",$show_link,$_targetwin,$_caption);
				$dat["ev_".$v_el] = sprintf("<a href=\"%s\">%s</a>",$show_link,$_caption);

			}

			$del_link = $this->get_link("delete", $form_id,$section,$op_id,$chain_id,$chain_entry_id, $dat["entry_id"]);
			$dat["ev_delete"] = "<a href='".$del_link."'>".$this->table["texts"]["delete"][$this->lang_id]."</a>";

			if (isset($dat["created"]))
			{
				$dat["ev_created"] = $this->time2date($dat["created"], 2);
			};

			if (isset($dat["modifiedby"]))
			{
				$dat["ev_uid"] = $dat["modifiedby"];
			};

			if (isset($dat["modified"]))
			{
				$dat["ev_modified"] = $this->time2date($dat["modified"], 2);
			};
			$dat["ev_select"] = "<input type='checkbox' name='sel[".$dat["entry_id"]."]' ".checked(isset($this->table["sel_def"]) && ($this->table["sel_def"]))." VALUE='1'>";
			$dat["ev_jrk"] = "[__jrk_replace__]";
		}

		// here we must preprocess the data, cause then the column names will be el_col_[col_number] not element names
		for ($col = 0; $col < $this->table["cols"]; $col++)
		{
			$cc = $this->table["defs"][$col];
			if (is_array($cc["els"]))
			{
				// do this better. 
				// first we compile the elements in the column together into one string and add their 
				// separators
				$str = "";
				// NB! This crashes PHP on Sparc/Solaris8 -- duke
				// foreach($cc["els"] as $elid => $elid)
				
				foreach($cc["els"] as $elid)
				{
					if ($dat["ev_".$elid] != "")
					{
						if ($cc["el_show"][$elid] == 1)
						{
							$str .= $dat["ev_".$elid];
							$str .= $this->table["defs"][$col]["el_sep"][$elid];
						}
					}
					else
					{
						// order element will never have a value in the data
						if ($elid == "order" && $cc["el_show"][$elid] == 1)
						{
							$str .= $this->get_order_url($col,$dat);
							$str .= $this->table["defs"][$col]["el_sep"][$elid];
						}
					}
				}
				$textvalue = $str;

				// then we add the aliases to the column
				$str = $this->process_row_aliases($str, $cc, $dat, $col, $section, $form_id, $textvalue);

				// and then, finally some misc settings
				if (isset($cc["link_el"]))
				{
					$str = "<a href='".$dat["ev_".$cc["link_el"]]."'>".$str."</a>";
				}
				else
				if (isset($this->table["defs"][$col]["is_email"]))
				{
					$str = "<a href='mailto:".$str."'>".$str."</a>";
				}
				$dat["ev_col_".$col] = $str;
			}
		}

		$this->t->define_data($dat);
		exit_function("form_table::row_data", array());
	}

	////
	// !reads the loaded entries from array of forms $forms and adds another row of data to the table
	function row_data_from_form($forms,$special = "")
	{
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
	}

	////
	// !returns an array of forms used in the table. each entry in the array is an array of elements in that form that are used
	// assumes the table is loaded already.
	function get_used_elements()
	{
		$ret = array();
		for ($i=0; $i < $this->table["cols"]; $i++)
		{
			if (is_array($this->table["defs"][$i]["el_forms"]))
			{
				foreach($this->table["defs"][$i]["el_forms"] as $elid => $fid)
				{
					if ($fid)
					{
						$ret[$fid][$elid] = $elid;
					}
				}
			}
		}
		
		if (is_array($this->table["defsort"]))
		{
			foreach($this->table["defsort"] as $el)
			{
				$ret[$this->table["defsort_forms"][$el["el"]]][$el["el"]] = $el["el"];
			}
		}

		if (is_array($this->table["rgrps"]))
		{
			foreach($this->table["rgrps"] as $nr => $dat)
			{
				$ret[$this->table["rgrps_forms"][$dat["el"]]][$dat["el"]] = $dat["el"];
				if ($dat["sort_el"])
				{
					$ret[$this->table["rgrps_forms"][$dat["sort_el"]]][$dat["sort_el"]] = $dat["sort_el"];
				}
				if ($dat["search_val_el"])
				{
					$ret[$this->table["rgrps_forms"][$dat["search_val_el"]]][$dat["search_val_el"]] = $dat["search_val_el"];
				}
				if (is_array($dat["data_els"]))
				{
					foreach($dat["data_els"] as $_del)
					{
						$ret[$this->table["rgrps_forms"][$_del]][$_del] = $_del;
					}
				}
			}
		}
		return $ret;
	}

	////
	// !finalizes table and generates html
	// $no_form_tags - if true, no <form> </form> tags are put on table
	function finalize_table($arr = array())
	{
		extract($arr);

		// check for that damn skip_one_liners thingie
		if ($this->table["skip_one_liners"] && $this->num_lines < 2 && $this->last_table_alias_url != "")
		{
			// now we need to figure out the damn url. well. ok, since it is always the only one in the table
			// we just remember the last one we made
			header("Location: ".$this->last_table_alias_url);
			die();
		}

		if (is_array($this->table["defsort"]))
		{
			$_sby = array();
			$_sord = array();
			foreach($this->table["defsort"] as $nr => $dat)
			{
				$cl = $this->get_col_for_el($dat["el"]);
				$_sby["ev_col_".$cl] = "ev_".$dat["el"];
				$_sord["ev_col_".$cl] = $dat["type"];
				$_sord["ev_".$dat["el"]] = $dat["type"];
			}
			$this->t->set_default_sortby($_sby);
			$this->t->set_default_sorder($_sord);
		}

		$r_g = false;
		$v_g = false;
		$rgroupdat = false;
		$vgroupdat = false;
		if (is_array($this->table["rgrps"]))
		{
			foreach($this->table["rgrps"] as $nr => $dat)
			{
				$cl = $this->get_col_for_el($dat["el"]);
				if ($dat["vertical"])
				{
					$v_g["ev_".$dat["el"]] = "ev_".$dat["el"];
					$vgroupdat["ev_".$dat["el"]]["sort_el"] = "ev_".$dat["sort_el"];
					$vgroupdat["ev_".$dat["el"]]["sort_order"] = $dat["sort_order"];
				}
				else
				{
					$r_g["ev_col_".$cl] = "ev_".$dat["el"];
					if ($dat["sort_el"])
					{
						$rgroupsortdat["ev_col_".$cl]["sort_el"] = "ev_".$dat["sort_el"];
					}
					else
					{
						$rgroupsortdat["ev_col_".$cl]["sort_el"] = "ev_".$dat["el"];
					}

					$rgroupsortdat["ev_col_".$cl]["sort_order"] = $dat["sort_order"];
					if (is_array($dat["data_els"]))
					{
						foreach($dat["data_els"] as $datel)
						{
							$rgroupdat["ev_col_".$cl][$datel]["el"] = "ev_".$datel;
							$rgroupdat["ev_col_".$cl][$datel]["sep"] = $dat["data_els_seps"][$datel];
							$rgroupdat["ev_".$dat["el"]][$datel]["el"] = "ev_".$datel;
							$rgroupdat["ev_".$dat["el"]][$datel]["sep"] = $dat["data_els_seps"][$datel];
						}
					}
					$rgroupby_sep["ev_col_".$cl]["pre"] = $dat["pre_sep"];
					$rgroupby_sep["ev_col_".$cl]["after"] = $dat["after_sep"];
					$rgroupby_sep["ev_".$dat["el"]]["pre"] = $dat["pre_sep"];
					$rgroupby_sep["ev_".$dat["el"]]["after"] = $dat["after_sep"];
				}
			}
		}
		$this->t->sort_by(array(
			"rgroupby" => $r_g,
			"rgroupsortdat" => $rgroupsortdat,
			"vgroupby" => $v_g,
			"vgroupdat" => $vgroupdat
		));

		$tbl = $this->get_css();
		$tbl.= $this->get_js();

		// here. add the table header aliases to the table string
		$tbl .= $this->render_aliases($this->table["table_header_aliases"]);

		$tbl .= $this->do_render_text_aliases($this->do_pageselector(nl2br($this->table["header"])));

		if (!$no_form_tags)
		{
			$tbl.="<form action='reforb.".$this->cfg["ext"]."' method='POST' name='tb_".$this->table_id."'>\n";
		}

		$tbl.=$this->t->draw(array(
			"rgroupby" => $r_g,
			"rgroupdat" => $rgroupdat,
			"rgroupby_sep" => $rgroupby_sep,
			"titlebar_under_groups" => $this->table["has_grpnames"],
			"has_pages" => $this->table["has_pages"],
			"records_per_page" => $this->table["records_per_page"],
			"act_page" => $GLOBALS["ft_page"]
		));

		if (!$no_form_tags)
		{
			$tbl.= $this->mk_reforb("submit_table", 
				array(
					"return" => $this->binhex(
						$this->mk_my_orb("show_entry", array("id" => $this->id, "entry_id" => $entry_id, "op_id" => $output_id))
					)
				)
			);
			$tbl.="</form>";
		}

		$tbl .= $this->do_render_text_aliases($this->do_pageselector(nl2br($this->table["footer"])));

		if ($this->table["no_show_empty"] && $this->num_lines < 1)
		{
			if ($this->table["empty_table_alias"])
			{
				$tbl = $this->render_aliases(array($this->table["empty_table_alias"]));
			}
			else
			{
				$tbl = $this->table["empty_table_text"];
			}
		}

		// now, if we need to show another table as well, do the new search for it
		if ($this->table["show_second_table"] && !aw_global_get("form_table_already_drew_2nd_table"))
		{
			aw_global_set("form_table_already_drew_2nd_table",1);

			// draw the table separator thingiesh
			$tbl .= $this->render_aliases($this->table["show_second_table_tables_sep"]);

			global $entry_id, $section, $match_form, $match_entry;
			// now figure out the use table and search_form arguments from the selected aliases
			$use_table = $this->table_id;
			$search_form = $this->get_opt("current_search_form");
			if (is_array($this->table["show_second_table_aliases"]))
			{
				foreach($this->table["show_second_table_aliases"] as $aid)
				{
					$alias_data = $this->get_data_for_alias($aid);
					if ($alias_data["class_id"] == CL_FORM_TABLE)
					{
						$use_table = $alias_data["target"];
					}
					else
					if ($alias_data["class_id"] == CL_FORM)
					{
						$search_form = $alias_data["target"];
					}
				}
			}
		enter_function("form_table::finalize::fin",array());
			$form = new form;
			// here we must find the value for the element($this->table["show_second_table_search_val_el"]) that was on the row
			// we clicked on
			// we do that like this - we add the entry_id and form_id of the row to the search url
			// so here we can load the entry and get the element value
			$form->load($match_form);
			$form->load_entry($match_entry);
			$sve = $form->entry[$this->table["show_second_table_search_val_el"]];
		enter_function("form_table::finalize::nds",array());
			$tbl.=$form->new_do_search(array(
				"entry_id" => $entry_id,
				"restrict_search_el" => array($this->table["show_second_table_search_el"]), 
				"restrict_search_val" => array($sve),
				"use_table" => $use_table,
				"section" => $section,
				"no_form_tags" => false,
				"search_form" => $search_form
			));
		exit_function("form_table::finalize::nds",array());
		exit_function("form_table::finalize::fin",array());
		}

		// change the current document title if necessary
		// this must be here, after we are done showing any aliases, cause they might be documents
		// and then their titles would get changed as well. bad karma.
		if ($this->table["doc_title_is_search"])
		{
			global $restrict_search_yah;
			if (is_array($restrict_search_yah))
			{
				$str = $restrict_search_yah[count($restrict_search_yah)-1];
				if ($this->table["doc_title_is_search_upper"])
				{
					// switch to estonian locale
					$old_loc = setlocale(LC_CTYPE,0);	
					setlocale(LC_CTYPE, 'et_EE');

					$str = strtoupper($str);

					// switch back to estonian
					setlocale(LC_CTYPE, $old_loc);
				}
				aw_global_set("set_doc_title", $str);
			}
		}

		if ($this->table["doc_title_is_yah"])
		{
			global $restrict_search_yah;
			if (is_array($restrict_search_yah))
			{
				$tmp = $restrict_search_yah;
				if ($this->table["doc_title_is_yah_nolast"])
				{
					unset($tmp[count($tmp)-1]);
				}
				$str = join($this->table["doc_title_is_yah_sep"], $tmp);
				if ($this->table["doc_title_is_yah_upper"])
				{
					$old_loc = setlocale(LC_CTYPE,0);	
					setlocale(LC_CTYPE, 'et_EE');

					$str = strtoupper($str);

					// switch back to estonian
					setlocale(LC_CTYPE, $old_loc);
				}
				aw_global_set("set_doc_title", $str);
			}
		}

		return $tbl;
	}

	////
	// !returns the xml definition for table $id to be passed to the table generator. if no id specified, 
	// presumes table is loaded already
	function get_xml($id = 0)
	{
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
				<content_style2_selected value=\"style_".$this->table["content_sorted_style2"]."\"/>
				<group_style value=\"style_".$this->table["group_style"]."\"/>\n";
		$xml.="<tableattribs ";

		if ($this->table["table_style"])
		{
			$s = get_instance("style");
			$xml.=$s->get_table_string($this->table["table_style"]);
		}
		
		$xml.=" />\n</definitions>\n<data>\n";
		
		// add the vertical group by columns
		if (is_array($this->table["rgrps"]))
		{
			foreach($this->table["rgrps"] as $nr => $dat)
			{
				$cl = $this->get_col_for_el($dat["el"]);
				if ($dat["vertical"])
				{
					$xml.="<field name=\"ev_".$dat["el"]."\" caption=\"".$dat["row_title"]."\" talign=\"center\" align=\"center\" />\n";
				}
			}
		}

		$gidlist = aw_global_get("gidlist");
		for ($col = 0; $col < $this->table["cols"]; $col++)
		{
			$cc = $this->table["defs"][$col];

			// don't show column to the users that are not part of the correct groups
			if (is_array($cc["grps"]) && count($cc["grps"]) > 0)
			{
				if (count(array_intersect($gidlist,$cc["grps"])) < 1)
				{
					continue;
				}
			}
			$eln = "col_".$col;
			
			$numericattr = "";
			// we need to check if the first element in the column is numeric - if it is, then we must sort that col numerically
			if (is_array($cc["els"]))
			{
				reset($cc["els"]);
				list(,$elid) = each($cc["els"]);
				if ($cc["el_types"][$elid] == "int")
				{
					$numericattr = " numeric=\"1\" thousands_sep=\"".$cc["thousands_sep"]."\"";
				};
			}
			
			$title = $cc["lang_title"][aw_global_get("lang_id")];
			if (is_array($cc["els"]) && in_array("select", $cc["els"]))
			{
				$title = "&lt;a href='javascript:void(0)' onClick='tb_selall()'&gt;".$title."&lt;/a&gt;";
			}
			
			$xml.="<field name=\"ev_".$eln."\" caption=\"".$title."\" talign=\"center\" align=\"center\" ";
			if ($cc["sortable"])
			{
				$xml.=" sortable=\"1\" ";
			}
			$xml.=" $numericattr />\n";
		}
		if ($GLOBALS["dbg_num"]) {echo("<textarea cols=80 rows=40>$xml</textarea>");};
		return $xml.="\n</data></tabledef>";
	}

	function get_css($id = 0)
	{
		if ($id)
		{
			$this->load_table($id);
		}
		$s = get_instance("style");
		$op = "<style type=\"text/css\">\n";

		if ($this->table["header_normal"])
		{
			$op.= $s->get_css($this->table["header_normal"],$this->table["header_link"]);
		}
		if ($this->table["group_style"])
		{
			$op.= $s->get_css($this->table["group_style"],$this->table["link_style"]);
		}
		if ($this->table["header_sortable"])
		{
			$op.= $s->get_css($this->table["header_sortable"],$this->table["header_sortable_link"]);
		}
		if ($this->table["header_sorted"])
		{
			$op.= $s->get_css($this->table["header_sorted"],$this->table["header_sortable_link"]);
		}
		if ($this->table["content_style1"])
		{
			$op.= $s->get_css($this->table["content_style1"],$this->table["link_style1"]);
		}
		if ($this->table["content_style2"])
		{
			$op.= $s->get_css($this->table["content_style2"],$this->table["link_style2"]);
		}
		if ($this->table["content_sorted_style1"])
		{
			$op.= $s->get_css($this->table["content_sorted_style1"],$this->table["link_style1"]);
		}
		if ($this->table["content_sorted_style2"])
		{
			$op.= $s->get_css($this->table["content_sorted_style2"],$this->table["link_style2"]);
		}
		if ($this->table["pg_lb_style"])
		{
			$op.= $s->get_css($this->table["pg_lb_style"],0);
		}
		if ($this->table["pg_text_style"])
		{
			$op.= $s->get_css($this->table["pg_text_style"],$this->table["pg_text_style_link"]);
		}
		$op.="</style>\n";
		return $op;
	}

	function get_order_url($col,$dat)
	{
		if (strpos($this->table["defs"][$col]["order_form"],"?") === false)
		{
			$sep = "?";
		}
		else
		{
			$sep = "&";
		}
		if ($dat["chain_entry_id"])
		{
			$link = $this->table["defs"][$col]["order_form"].$sep."load_chain_data=".$dat["chain_entry_id"];
		}
		else
		{
			$link = $this->table["defs"][$col]["order_form"].$sep."load_entry_data=".$dat["entry_id"];
		}
		return "<a href='".$link."'>".$this->table["texts"]["order"][$this->lang_id]."</a>";
	}

	function get_aliases_for_table()
	{
		if (!($ret = aw_cache_get("form_table::aliases",$this->table_id)))
		{
			classload("aliasmgr");
			$am = new aliasmgr;
			$am->_init_aliases();
			$defs = $am->get_defs();
			
			$defs2 = array();
			foreach($defs as $dd)
			{
				$defs2[$dd["class_id"]] = $dd["alias"];
			}

			$cnts = array();
			$aliases = $this->get_aliases_for($this->table_id);
			$ret = array();
			foreach($aliases as $ad)
			{
				$ret[$ad["id"]] = "#".$defs2[$ad["class_id"]].(++$cnts[$ad["class_id"]])."#";
			}
			aw_cache_set("form_table::aliases", $this->table_id, $ret);
		}

		return $ret;
	}

	function get_data_for_alias($aid)
	{
		if (!($all_aliases = aw_cache_get("form_table::galiases", $this->table_id)))
		{
			$all_aliases = $this->get_aliases_for($this->table_id);
			aw_cache_set("form_table::galiases", $this->table_id, $all_aliases);
		}

		foreach($all_aliases as $ad)
		{
			if ($ad["target"] == $aid)
			{
				return $ad; 
			}
		}
		return false;
	}

	function get_js()
	{
		return "<script language='javascript'>
			var chk_status = ".($this->table["sel_def"] == 1 ? "false" : "true").";

				function tb_selall()
				{
					len = document.tb_".$this->table_id.".elements.length;
					for (i=0; i < len; i++)
					{
						if (document.tb_".$this->table_id.".elements[i].name.indexOf('sel') != -1)
						{
							document.tb_".$this->table_id.".elements[i].checked=chk_status;
						}
					}
					chk_status = !chk_status;
					return false;
				}
		</script>";
	}

	function new_add($arr)
	{
		extract($arr);
		$this->read_template("add_table_settings.tpl");
		$this->mk_path($parent, "Lisa formi tabel");

		$lang = get_instance("languages");
		$obj = get_instance("objects");

		$this->vars(array(
			"languages" => $this->mpicker(array(), $lang->get_list()),
			"forms" => $this->mpicker(array(), $this->get_flist(array("type" => FTYPE_ENTRY, "addfolders" => true, "sort" => true))),
			"folders" => $this->mpicker(array(), $obj->get_list()),
			"reforb" => $this->mk_reforb("new_submit_settings", array("parent" => $parent))
		));

		return $this->parse();
	}

	function new_submit_settings($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"class_id" => CL_FORM_TABLE,
				"name" => $name,
				"comment" => $comment
			));
			$this->db_query("INSERT INTO form_tables(id, content, num_cols, cols) values('$id','',0,0)");
		}

		$this->load_table($id);

		$t_forms = $this->get_forms_for_table($id);
		
		if (is_array($t_forms))
		foreach($t_forms as $formid)
		{
			$formels=$this->get_form_elements(array("id"=> $formid,"key"=> "id"));
			if (is_array($formels))
			{
				foreach($formels as $k_elid => $v_eldata)
				{
					$els[$k_elid]=$formid;
					$elsubtypes[$formid][$k_elid]["subtype"]=$v_eldata["subtype"];
					$elsubtypes[$formid][$k_elid]["thousands_sep"]=$v_eldata["thousands_sep"];
				};
			}
		};

		$this->table["has_aliasmgr"] = $settings["has_aliasmgr"];
		$this->table["has_yah"] = $settings["has_yah"];
		$this->table["select_default"] = $settings["select_default"];
		$this->table["has_textels"] = $settings["has_textels"];
		$this->table["has_groupacl"] = $settings["has_groupacl"];
		$this->table["has_grpnames"] = $settings["has_grpnames"];
		$this->table["print_button"] = $settings["print_button"];
		$this->table["has_pages"] = $settings["has_pages"];
		$this->table["has_pages_type"] = $settings["has_pages_type"];
		$this->table["records_per_page"] = $settings["records_per_page"];
		$this->table["skip_one_liners"] = $settings["skip_one_liners"];
		$this->table["user_entries"] = $settings["user_entries"];
		$this->table["doc_title_is_search"] = $settings["doc_title_is_search"];
		$this->table["doc_title_is_search_upper"] = $settings["doc_title_is_search_upper"];
		$this->table["doc_title_is_yah"] = $settings["doc_title_is_yah"];
		$this->table["doc_title_is_yah_sep"] = $settings["doc_title_is_yah_sep"];
		$this->table["doc_title_is_yah_nolast"] = $settings["doc_title_is_yah_nolast"];
		$this->table["doc_title_is_yah_upper"] = $settings["doc_title_is_yah_upper"];
		$this->table["show_second_table"] = $settings["show_second_table"];
		$this->table["show_second_table_search_el"] = $settings["show_second_table_search_el"];
		$this->table["show_second_table_search_val_el"] = $settings["show_second_table_search_val_el"];
		$this->table["show_second_table_tables_sep"] = $this->make_keys($settings["show_second_table_tables_sep"]);
		$this->table["table_header_aliases"] = $this->make_keys($settings["table_header_aliases"]);
		$this->table["no_show_empty"] = $settings["no_show_empty"];
		$this->table["empty_table_text"] = $settings["empty_table_text"];
		$this->table["empty_table_alias"] = $settings["empty_table_alias"];
		$this->table["no_grpels_in_restrict"] = $settings["no_grpels_in_restrict"];
		$this->table["has_grpsettings"] = $settings["has_grpsettings"];
		$this->table["forms"] = $this->make_keys($settings["forms"]);
		$this->table["languages"] = $this->make_keys($settings["languages"]);
		$this->table["moveto"] = $this->make_keys($settings["folders"]);
		$this->table["view_cols"] = $this->make_keys($settings["view_cols"]);
		$this->table["change_cols"] = $this->make_keys($settings["change_cols"]);
		$this->table["user_entries_except_grps"] = $this->make_keys($user_entries_except_grps);
		$this->table["show_second_table_aliases"] = $this->make_keys($settings["show_second_table_aliases"]);
		$this->table["header"] = $settings["header"];
		$this->table["footer"] = $settings["footer"];

		if (!is_array($defsort))
		{
			$defsort = array();
		}
		$this->table["defsort"] = array();
		foreach($defsort as $nr => $dat)
		{
			if ($dat["el"])
			{
				$this->table["defsort"][] = $dat;
				$this->table["defsort_forms"][$dat["el"]] = $els[$dat["el"]];
				$this->table["defsort_el_types"][$dat["el"]] = $elsubtypes[$els[$dat["el"]]][$dat["el"]]["subtype"];
			}
		}
		usort($this->table["defsort"], create_function('$a,$b','if ($a["ord"] > $b["ord"]) return 1; if ($a["ord"] < $b["ord"]) return -1; return 0;'));

		if (!is_array($grps))
		{
			$grps = array();
		}
		$this->table["grps"] = array();
		foreach($grps as $nr => $dat)
		{
			if ($dat["gp_el"])
			{
				$this->table["grps"][] = $dat;
				$this->table["grps_forms"][$dat["gp_el"]] = $els[$dat["gp_el"]];
			}
		}

		if (!is_array($rgrps))
		{
			$rgrps = array();
		}
		$this->table["rgrps"] = array();
		foreach($rgrps as $nr => $dat)
		{
			if ($dat["el"])
			{
				$dat["data_els"] = $this->make_keys($dat["data_els"]);

				$this->table["rgrps"][] = $dat;
				$this->table["rgrps_forms"][$dat["el"]] = $els[$dat["el"]];
				$this->table["rgrps_el_types"][$dat["el"]] = $elsubtypes[$els[$dat["el"]]][$dat["el"]]["subtype"];
				if ($dat["sort_el"])
				{
					$this->table["rgrps_forms"][$dat["sort_el"]] = $els[$dat["sort_el"]];
					$this->table["rgrps_el_types"][$dat["sort_el"]] = $elsubtypes[$els[$dat["sort_el"]]][$dat["sort_el"]]["subtype"];
				}

				if ($dat["search_val_el"])
				{
					$this->table["rgrps_forms"][$dat["search_val_el"]] = $els[$dat["search_val_el"]];
					$this->table["rgrps_el_types"][$dat["search_val_el"]] = $elsubtypes[$els[$dat["search_val_el"]]][$dat["search_val_el"]]["subtype"];
				}

				if (is_array($dat["data_els"]))
				{
					foreach($dat["data_els"] as $_del)
					{
						$this->table["rgrps_forms"][$_del] = $els[$_del];
					}
				}
			}
		}
		usort($this->table["rgrps"], create_function('$a,$b','if ($a["ord"] > $b["ord"]) return 1; if ($a["ord"] < $b["ord"]) return -1; return 0;'));

		$this->table["buttons"] = $buttons;

		$this->save_table_settings();
		return $this->mk_my_orb("new_change_settings", array("id" => $id));
	}

	function new_change_settings($arr)
	{
		extract($arr);
		$this->load_table($id);
		$this->read_template("add_table_settings.tpl");
		$this->mk_path($this->table_parent, "Muuda formi tabelit");

		$this->do_menu();

		$lang = get_instance("languages");
		$obj = get_instance("objects");
		$us = get_instance("users");

		$els = $this->get_tbl_elements();

		$als = $this->get_aliases_for_table();
		$this->vars(array(
			"name" => $this->table_name,
			"comment" => $this->table_comment,
			"languages" => $this->mpicker($this->table["languages"], $lang->get_list()),
			"forms" => $this->mpicker($this->table["forms"], $this->get_flist(array("type" => FTYPE_ENTRY, "addfolders" => true, "sort" => true))),
			"folders" => $this->mpicker($this->table["moveto"], $obj->get_list()),
			"has_print_button" => checked($this->table["print_button"]),
			"has_grpnames" => checked($this->table["has_grpnames"]),
			"has_groupacl" => checked($this->table["has_groupacl"]),
			"has_textels" => checked($this->table["has_textels"]),
			"has_aliasmgr" => checked($this->table["has_aliasmgr"]),
			"select_default" => checked($this->table["select_default"]),
			"has_yah" => checked($this->table["has_yah"]),
			"has_pages" => checked($this->table["has_pages"]),
			"has_pages_text" => checked($this->table["has_pages_type"] == "text"),
			"has_pages_lb" => checked($this->table["has_pages_type"] == "lb"),
			"has_user_entries" => checked($this->table["user_entries"]),
			"records_per_page" => $this->table["records_per_page"],
			"uee_grps" => $this->mpicker($this->table["user_entries_except_grps"], $us->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)))),
			"skip_one_liners" => checked($this->table["skip_one_liners"]),
			"show_second_table" => checked($this->table["show_second_table"]),
			"no_grpels_in_restrict" => checked($this->table["no_grpels_in_restrict"]),
			"no_show_empty" => checked($this->table["no_show_empty"]),
			"empty_table_text" => $this->table["empty_table_text"],
			"empty_table_alias" => $this->picker($this->table["empty_table_alias"], $als),
			"view_cols" => $this->mpicker($this->table["view_cols"], $els),
			"show_second_table_aliases" => $this->mpicker($this->table["show_second_table_aliases"], $als),
			"second_table_search_el" => $this->picker($this->table["show_second_table_search_el"], $els),
			"second_table_search_val_el" => $this->picker($this->table["show_second_table_search_val_el"], $els),
			"show_second_table_tables_sep" => $this->mpicker($this->table["show_second_table_tables_sep"], $als),
			"table_header_aliases" => $this->mpicker($this->table["table_header_aliases"], $als),
			"change_cols" => $this->mpicker($this->table["change_cols"], $els),
			"doc_title_is_search" => checked($this->table["doc_title_is_search"]),
			"doc_title_is_search_upper" => checked($this->table["doc_title_is_search_upper"]),
			"doc_title_is_yah" => checked($this->table["doc_title_is_yah"]),
			"doc_title_is_yah_upper" => checked($this->table["doc_title_is_yah_upper"]),
			"doc_title_is_yah_nolast" => checked($this->table["doc_title_is_yah_nolast"]),
			"doc_title_is_yah_sep" => $this->table["doc_title_is_yah_sep"],
			"has_grpsettings" => checked($this->table["has_grpsettings"]),
			"header" => htmlentities($this->table["header"]),
			"footer" => htmlentities($this->table["footer"]),
			"reforb" => $this->mk_reforb("new_submit_settings", array("id" => $id))
		));

		$mnr = 0;
		if (is_array($this->table["defsort"]))
		{
			foreach($this->table["defsort"] as $nr => $dat)
			{
				$this->vars(array(
					"ds_nr" => $nr,
					"ds_ord" => $dat["ord"],
					"ds_els" => $this->picker($dat["el"], $els),
					"ds_asc" => checked($dat["type"] == "asc"),
					"ds_desc" => checked($dat["type"] == "desc")
				));
				$l.= $this->parse("DEFSORT_LINE");
				$mnr = $nr;
				$mord = max($dat["ord"], $mord);
			}
		}
		$this->vars(array(
			"ds_nr" => $mnr+1,
			"ds_ord" => $mord+1,
			"ds_els" => $this->picker('', $els),
			"ds_asc" => checked(true),
			"ds_desc" => checked(false)
		));
		$l.= $this->parse("DEFSORT_LINE");
		$this->vars(array("DEFSORT_LINE" => $l));

		$l = "";
		if (is_array($this->table["grps"]))
		{
			foreach($this->table["grps"] as $nr => $dat)
			{
				$this->vars(array(
					"grp_nr" => $nr,
					"gp_sep" => $dat["sep"],
					"gp_els" => $this->picker($dat["gp_el"], $els),
					"collect_els" => $this->picker($dat["collect_el"], $els),
					"ord_els" => $this->picker($dat["ord_el"], $els),
				));
				$l.= $this->parse("GRPLINE");
				$mnr = $nr;
			}
		}
		$this->vars(array(
			"grp_nr" => $mnr+1,
			"gp_sep" => ",",
			"gp_els" => $this->picker('', $els),
			"collect_els" => $this->picker('', $els),
			"ord_els" => $this->picker('', $els),
		));
		$l.= $this->parse("GRPLINE");
		$this->vars(array("GRPLINE" => $l));


		$l = "";
		$mnr = 0;
		$mord = 0;
		if (is_array($this->table["rgrps"]))
		{
			foreach($this->table["rgrps"] as $nr => $dat)
			{
				$this->vars(array(
					"grp_nr" => $nr,
					"gp_ord" => $dat["ord"],
					"gp_row_title" => $dat["row_title"],
					"els" => $this->picker($dat["el"], $els),
					"sort_els" => $this->picker($dat["sort_el"], $els),
					"sort_order" => $this->picker($dat["sort_order"], array("asc" => "Asc", "desc" => "Desc")),
					"gp_vertical" => checked($dat["vertical"] == 1),
					"data_els" => $this->mpicker($dat["data_els"], $els),
					"search_val_els" => $this->picker($dat["search_val_el"], $els),
					"search_to_els" => $this->picker($dat["search_to_el"], $els),
					"data_els" => $this->mpicker($dat["data_els"], $els),
					"pre_sep" => $dat["pre_sep"],
					"after_sep" => $dat["after_sep"],
				));
				$del = "";
				if (is_array($dat["data_els"]))
				{
					foreach($dat["data_els"] as $_del)
					{
						$this->vars(array(
							"del" => $_del,
							"del_name" => $els[$_del],
							"del_sep" => $dat["data_els_seps"][$_del]
						));
						$del.=$this->parse("DATEL");
					}
				}
				$this->vars(array(
					"DATEL" => $del
				));
				$l.= $this->parse("GRP2LINE");
				$mnr = $nr;
				$mord = max($dat["ord"], $mord);
			}
		}
		$this->vars(array(
			"grp_nr" => $mnr+1,
			"gp_ord" => $mord+1,
			"gp_row_title" => "",
			"els" => $this->picker('', $els),
			"sort_els" => $this->picker('', $els),
			"gp_vertical" => checked(false),
			"data_els" => $this->mpicker(array(), $els),
			"pre_sep" => "",
			"after_sep" => "",
			"DATEL" => ""
		));
		$l.= $this->parse("GRP2LINE");
		$this->vars(array("GRP2LINE" => $l));

		$l = "";
		foreach($this->buttons as $btn_id => $btn_name)
		{
			$this->vars(array(
				"button_check" => checked($this->table["buttons"][$btn_id]["check"] == 1),
				"button_text" => $this->table["buttons"][$btn_id]["text"],
				"button_ord" => $this->table["buttons"][$btn_id]["ord"],
				"button_up" => checked($this->table["buttons"][$btn_id]["pos"]["up"] == 1),
				"button_down" => checked($this->table["buttons"][$btn_id]["pos"]["down"] == 1),
				"bt_name" => $btn_name,
				"bt_id" => $btn_id
			));
			$l.=$this->parse("BUTTON");
		}
		$this->vars(array(
			"BUTTON" => $l,
			"CHANGE" => $this->parse("CHANGE")
		));
		return $this->parse();
	}

	function save_table_settings()
	{
		// make things just a bit more sane
		for($i=0; $i < $this->table["cols"]; $i++)
		{
			if (!is_array($this->table["defs"][$i]["els"]))
			{
				$this->table["defs"][$i]["els"] = array();
			}
		}
		if (!is_array($this->table["forms"]))
		{
			$this->table["forms"] = array();
		}

		$this->db_query("DELETE FROM form_table2form WHERE table_id = ".$this->table_id);
		if (is_array($this->table["forms"]))
		{
			foreach($this->table["forms"] as $fid)
			{
				$this->db_query("INSERT INTO form_table2form(form_id,table_id) VALUES($fid,".$this->table_id.")");
			}
		}
		$co = aw_serialize($this->table,SERIALIZE_PHP);
		$this->quote(&$co);
		$q = "UPDATE form_tables SET num_cols = '".$this->table["cols"]."' , content = '$co' WHERE id = ".$this->table_id;
		$this->db_query($q);
	}

	function do_menu()
	{
		$tpl = new aw_template;
		$tpl->tpl_init("forms");
		$tpl->read_template("fg_table_menu.tpl");

		$items["change"] = array("name" => "Tulbad", "url" => $this->mk_my_orb("change", array("id" => $this->table_id), "",false,true));

		$items["new_change_settings"] = array("name" => "M&auml;&auml;rangud", "url" => $this->mk_my_orb("new_change_settings", array("id" => $this->table_id), "",false,true));

		$items["new_change_styles"] = array("name" => "Stiilid", "url" => $this->mk_my_orb("new_change_styles", array("id" => $this->table_id), "",false,true));

		$items["new_change_styles"] = array("name" => "Stiilid", "url" => $this->mk_my_orb("new_change_styles", array("id" => $this->table_id), "",false,true));

		if ($this->table["has_aliasmgr"])
		{
			$items["new_change_aliasmgr"] = array("name" => "Aliastehaldur", "url" => $this->mk_my_orb("new_change_aliasmgr", array("id" => $this->table_id), "",false,true));
		}

		$items["new_change_translate"] = array("name" => "T&otilde;lgi", "url" => $this->mk_my_orb("new_change_translate", array("id" => $this->table_id), "",false,true));

		if ($this->table["has_grpsettings"])
		{
			$items["has_grpsettings"] = array("name" => "Gruppide m&auml;&auml;rangud", "url" => $this->mk_my_orb("change_grpsettings", array("id" => $this->table_id), "",false,true));
		}

		$this->vars(array(
			"menu" => $tpl->do_menu($items)
		));
	}

	function new_change_cols($arr)
	{
		extract($arr);
		$this->read_template("add_table_cols.tpl");
		$this->load_table($id);
		$this->mk_path($this->table_parent, "Muuda formi tabelit");
		$this->do_menu();

		$els = $this->get_tbl_elements();

		$this->vars(array(
			"num_cols" => $this->table["cols"],
			"reforb" => $this->mk_reforb("new_submit_cols", array("id" => $id))
		));

		for ($col = 0; $col < $this->table["cols"]; $col++)
		{
			$this->vars(array(
				"lang_title" => $this->table["defs"][$col]["lang_title"][aw_global_get("lang_id")],
				"ord" => $this->table["defs"][$col]["ord"],
				"col_id" => $col,
				"lang_id" => aw_global_get("lang_id")
			));
			$coldata[$col][1] = $this->parse("COL_HEADER");

			$this->vars(array(
				"els" => $this->mpicker($this->table["defs"][$col]["els"], $els)
			));
			$coldata[$col][2] = $this->parse("SEL_ELS");

			if ($this->table["defs"][$col]["els"]["order"] == "order")
			{
				$this->vars(array(
					"order_form" => $this->table["defs"][$col]["order_form"]
				));
				$coldata[$col][3] = $this->parse("SEL_ORDER_FORM");
			}

			if ($this->table["has_aliasmgr"])
			{
				$this->vars(array(
					"aliases" => $this->mpicker($this->table["defs"][$col]["alias"], $this->get_aliases_for_table())
				));
				$coldata[$col][4] = $this->parse("SEL_ALIAS");
			}

			if ($this->table["has_groupacl"])
			{
				$us = get_instance("users");
				$this->vars(array(
					"grps" => $this->mpicker($this->table["defs"][$col]["grps"], $us->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC))))
				));
				$coldata[$col][5] = $this->parse("SEL_GRPS");
			}

			if ($this->table["defs"][$col]["link"] == 1)
			{
				$this->vars(array(
					"link" => $this->picker($this->table["defs"][$col]["link_el"], $els)
				));
				$coldata[$col][6] = $this->parse("SEL_LINK");
			}

			
			$l = "";
			$elns = array();
			if (is_array($this->table["defs"][$col]["els"]))
			{
				foreach($this->table["defs"][$col]["els"] as $el)
				{
					$this->vars(array(
						"el_name" => $els[$el],
						"el_ord" => $this->table["defs"][$col]["el_ord"][$el],
						"el_id" => $el,
						"el_sep" => $this->table["defs"][$col]["el_sep"][$el],
						"el_show" => checked($this->table["defs"][$col]["el_show"][$el]),
						"el_search" => checked($this->table["defs"][$col]["el_search"][$el])
					));
					$l.= $this->parse("SEL_EL");
					$elns[$el] = $els[$el];
				}
			}
			$this->vars(array(
				"SEL_EL" => $l,
				"search_map" => $this->picker($this->table["defs"][$col]["search_map"], $elns),
				"search_el" => $this->picker($this->table["defs"][$col]["search_el"], $elns)
			));
			$has_ftable_aliases = false;
			if (is_array($this->table["defs"][$col]["alias"]))
			{
				foreach($this->table["defs"][$col]["alias"] as $aid)	
				{
					$alias_data = $this->table["defs"][$col]["alias_data"][$aid];
					if ($alias_data["class_id"] == CL_FORM_TABLE)
					{
						$has_ftable_aliases = true;
					}
				}
			}

			$this->vars(array(
				"HAS_FTABLE_ALIASES" => ($has_ftable_aliases ? $this->parse("HAS_FTABLE_ALIASES") : "")
			));
			$coldata[$col][7] = $this->parse("SEL_SETTINGS");

			$this->vars(array(
				"col_sortable" => checked($this->table["defs"][$col]["sortable"]),
				"col_email" => checked($this->table["defs"][$col]["is_email"]),
				"col_clicksearch" => checked($this->table["defs"][$col]["clicksearch"]),
				"col_link" => checked($this->table["defs"][$col]["link"]),
				"col_link_popup" => checked($this->table["defs"][$col]["link_popup"])
			));
			$coldata[$col][8] = $this->parse("SEL_SETINGS2");

			$this->vars(array(
				"popup_width" => $this->table["defs"][$col]["link_popup_width"],
				"popup_height" => $this->table["defs"][$col]["link_popup_height"],
				"scrollbars" => checked($this->table["defs"][$col]["link_popup_scrollbars"]),
				"fixed" => checked($this->table["defs"][$col]["link_popup_fixed"]),
				"toolbar" => checked($this->table["defs"][$col]["link_popup_toolbar"]),
				"addressbar" => checked($this->table["defs"][$col]["link_popup_addressbar"]),
			));
			$coldata[$col][9] = $this->parse("SEL_POPUP");

			$this->vars(array(
				"img_type_img" => checked($this->table["defs"][$col]["image_type"] == "img"),
				"img_type_tximg" => checked($this->table["defs"][$col]["image_type"] == "tximg"),
				"img_type_imgtx" => checked($this->table["defs"][$col]["image_type"] == "imgtx"),
			));
			$coldata[$col][10] = $this->parse("SEL_IMAGE");
		}

		$l = "";
		for ($idx = 1; $idx < 11; $idx++)
		{
			$td = "";
			for ($col =0 ; $col < $this->table["cols"]; $col++)
			{
				$this->vars(array(
					"content" => $coldata[$col][$idx]
				));
				$td.=$this->parse("TD");
			}
			$this->vars(array("TD" => $td));
			$l.=$this->parse("ROW");
		}
		$this->vars(array(
			"ROW" => $l,
			"COL_HEADER" => "",
			"SEL_ELS" => "",
			"SEL_ALIAS" => "",
			"SEL_GRPS" => "",
			"SEL_LINK" => "",
			"SEL_SETTINGS" => "",
			"SEL_SETINGS2" => "",
			"SEL_POPUP" => "",
			"SEL_IMAGE" => "",
			"SEL_ORDER_FORM" => "",
		));
		return $this->parse();
	}

	function new_submit_cols($arr)
	{
		extract($arr);
		$this->load_table($id);

		$old_defs = $this->table["defs"];
		$this->table["cols"] = $num_cols;
		$this->table["defs"] = $cols;

		// now create lookup tables for elements so we know in which forms they are
		$t_forms = $this->get_forms_for_table($id);
		
		if (is_array($t_forms))
		foreach($t_forms as $formid)
		{
			$formels=$this->get_form_elements(array("id"=> $formid,"key"=> "id"));
			if (is_array($formels))
			{
				foreach($formels as $k_elid => $v_eldata)
				{
					$els[$k_elid]=$formid;
					$elsubtypes[$formid][$k_elid]["subtype"]=$v_eldata["subtype"];
					$elsubtypes[$formid][$k_elid]["thousands_sep"]=$v_eldata["thousands_sep"];
				};
			}
		};

		for ($i=0; $i < $this->table["cols"]; $i++)
		{
			$this->table["defs"][$i]["els"] = $this->make_keys($cols[$i]["els"]);
			foreach($this->table["defs"][$i]["els"] as $elid)
			{
				$this->table["defs"][$i]["el_forms"][$elid] = $els[$elid];
				$this->table["defs"][$i]["el_types"][$elid] = $elsubtypes[$els[$elid]][$elid]["subtype"];
				// if the element was just added, default the damn thing to show and search
				if (!isset($old_defs[$i]["els"][$elid]))
				{
					$this->table["defs"][$i]["el_show"][$elid] = 1;
					$this->table["defs"][$i]["el_search"][$elid] = 1;
				}
			}
			$this->table["defs"][$i]["grps"] = $this->make_keys($cols[$i]["grps"]);
			$this->table["defs"][$i]["alias"] = $this->make_keys($cols[$i]["alias"]);

			foreach($this->table["defs"][$i]["alias"] as $aid)
			{
				$this->table["defs"][$i]["alias_data"][$aid] = $this->get_data_for_alias($aid);
			}

			// sort elements in col
			$this->elsort_dat = $this->table["defs"][$i]["el_ord"];
			uasort($this->table["defs"][$i]["els"], array($this, "elsort"));
		}

		usort($this->table["defs"], create_function('$a,$b','if ($a["ord"] > $b["ord"]) return 1; if ($a["ord"] < $b["ord"]) return -1; return 0;'));

		$this->save_table_settings();
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function elsort($a,$b)
	{
		if ($this->elsort_dat[$a] > $this->elsort_dat[$b])
		{
			return 1;
		}
		if ($this->elsort_dat[$a] < $this->elsort_dat[$b])
		{
			return -1;
		}
		return 0;
	}

	function new_change_styles($arr)
	{
		extract($arr);
		$this->read_template("add_table_styles.tpl");
		$this->load_table($id);
		$this->mk_path($this->table_parent, "Muuda formi tabelit");
		$this->do_menu();

		$s = get_instance("style");
		$css = $s->get_select(0,ST_CELL, true);

		$this->vars(array(
			"tablestyles" => $this->picker($this->table["table_style"],$s->get_select(0,ST_TABLE,true)),
			"header_normal" => $this->picker($this->table["header_normal"],$css),
			"header_link" => $this->picker($this->table["header_link"], $css),
			"header_sortable" => $this->picker($this->table["header_sortable"],$css),
			"header_sortable_link" => $this->picker($this->table["header_sortable_link"], $css),
			"header_sorted" => $this->picker($this->table["header_sorted"],$css),
			"content_style1" => $this->picker($this->table["content_style1"],$css),
			"content_style2" => $this->picker($this->table["content_style2"],$css),
			"content_sorted_style1" => $this->picker($this->table["content_sorted_style1"],$css),
			"content_sorted_style2" => $this->picker($this->table["content_sorted_style2"],$css),
			"group_style" => $this->picker($this->table["group_style"],$css),
			"group_link_style" => $this->picker($this->table["group_link_style"],$css),
			"link_style1" => $this->picker($this->table["link_style1"],$css),
			"link_style2" => $this->picker($this->table["link_style2"],$css),
			"pg_text_style" => $this->picker($this->table["pg_text_style"], $css),
			"pg_text_style_link" => $this->picker($this->table["pg_text_style_link"], $css),
			"pg_lb_style" => $this->picker($this->table["pg_lb_style"], $css),
			"sum_style" => $this->picker($this->table["sum_style"],$css),
			"reforb" => $this->mk_reforb("new_submit_styles", array("id" => $id))
		));

		return $this->parse();
	}

	function new_submit_styles($arr)
	{
		extract($arr);
		$this->load_table($id);

		$this->table = array_merge($this->table, $styles);

		$this->save_table_settings();
		return $this->mk_my_orb("new_change_styles", array("id" => $id));
	}

	function new_change_translate($arr)
	{
		extract($arr);
		$this->read_template("add_table_translate.tpl");
		$this->load_table($id);
		$this->mk_path($this->table_parent, "Muuda formi tabelit");
		$this->do_menu();

		$la = get_instance("languages");
		$ls = $la->get_list();
		foreach($ls as $lid => $lname)
		{
			if ($this->table["languages"][$lid] != $lid)
			{
				unset($ls[$lid]);
			}
		}

		foreach($ls as $lid => $lname)
		{
			$this->vars(array(
				"lang_name" => $lname
			));
			$lh.= $this->parse("LANG_H");
		}

		for ($col = 0; $col < $this->table["cols"]; $col++)
		{
			$this->vars(array(
				"col_id" => $col
			));
			$lg = "";
			foreach($ls as $lid => $lname)
			{
				$this->vars(array(
					"lang_id" => $lid,
					"title" => $this->table["defs"][$col]["lang_title"][$lid]
				));
				$lg.=$this->parse("LANG");
			}
			$this->vars(array(
				"LANG" => $lg
			));
			$l.=$this->parse("COL");
		}

		$clh = "";
		foreach($ls as $lid => $lname)
		{
			$this->vars(array(
				"lang_name" => $lname
			));
			$clh.=$this->parse("CLANG_H");
		}

		$ct = "";
		foreach($this->fakels as $fakelname => $__fk)
		{
			$cl="";
			$this->vars(array(
				"eltype" => $fakelname
			));
			foreach($ls as $lid => $lname)
			{
				$tx = $this->table["texts"][$fakelname][$lid];
				$this->vars(array(
					"lang_id" => $lid,
					"t_name" => ($tx != "" ? $tx : $__fk )
				));
				$cl.=$this->parse("CLANG");
			}
			$this->vars(array(
				"CLANG" => $cl
			));
			$ct.=$this->parse("COL_TEXT");
		}

		$this->vars(array(
			"COL_TEXT" => $ct,
			"CLANG_H" => $clh,
			"COL" => $l,
			"LANG_H" => $lh,
			"reforb" => $this->mk_reforb("new_submit_translate", array("id" => $id))
		));

		return $this->parse();
	}

	function new_submit_translate($arr)
	{
		extract($arr);
		$this->load_table($id);

		$la = get_instance("languages");
		$ls = $la->get_list();

		for ($col = 0; $col < $this->table["cols"]; $col++)
		{
			foreach($ls as $lid => $lname)
			{
				$this->table["defs"][$col]["lang_title"][$lid] = $langs[$col][$lid];
			}
		}
		
		$this->table["texts"] = $texts;

		$this->save_table_settings();
		return $this->mk_my_orb("new_change_translate", array("id" => $id));
	}

	function new_change_aliasmgr($arr)
	{
		extract($arr);
		$this->read_template("add_table_aliasmgr.tpl");
		$this->load_table($id);
		$this->mk_path($this->table_parent, "Muuda formi tabelit");
		$this->do_menu();

		$this->vars(array(
			"aliasmgr" => $this->mk_my_orb("list_aliases", array("id" => $id), "aliasmgr")
		));
		return $this->parse();
	}

	////
	// !returns an array of all elements that are in the forms selected for this table
	// honors the no_text_tlements setting
	function get_tbl_elements()
	{
		$ret = array();
		if (is_array($this->table["forms"]))
		{
			foreach($this->table["forms"] as $fid)
			{
				$ret += $this->get_form_elements(array("id" => $fid, "key" => "id", "all_data" => false));
			}
		}
		$ret+=$this->fakels;
		return $ret;
	}

	function get_col_for_el($el)
	{
		$cl = false;
		for ($_i = 0; $_i < $this->table["cols"]; $_i++)
		{
			if (isset($this->table["defs"][$_i]["els"][$el]) && $this->table["defs"][$_i]["els"][$el])
			{
				$cl = $_i;
			}
		}
		return $cl;
	}

	function get_group_by_elements()
	{
		$ret = array();
		if (is_array($this->table["grps"]))
		{
			foreach($this->table["grps"] as $nr => $dat)
			{
				$ret[$this->table["grps_forms"][$dat["gp_el"]]][] = $dat["gp_el"];
			}
		}
		return $ret;
	}

	function get_group_by_collect_elements()
	{
		$ret = array();
		if (is_array($this->table["grps"]))
		{
			foreach($this->table["grps"] as $nr => $dat)
			{
				if (isset($dat["collect_el"]))
				{
					$ret[$dat["collect_el"]]["sep"] = $dat["sep"];
					$ret[$dat["collect_el"]]["ord_el"] = $dat["ord_el"];
				}
			}
		}
		return $ret;
	}

	////
	// !this sets the numeric status for each element in the form_table to the vcl table
	function set_numeric_fields_in_table()
	{
		for ($i=0; $i < $this->table["cols"]; $i++)
		{
			if (is_array($this->table["defs"][$i]["els"]))
			{
				foreach($this->table["defs"][$i]["els"] as $elid)
				{
					if ($this->table["defs"][$i]["el_types"][$elid] == "int")
					{
						$this->t->set_numeric_field("ev_".$elid);
					}
				}
			}
		}
		if (is_array($this->table["defsort_el_types"]))
		{
			foreach($this->table["defsort_el_types"] as $el => $type)
			{
				if ($type == "int")
				{
					$this->t->set_numeric_field("ev_".$el);
				}
			}
		}
		if (is_array($this->table["rgrps_el_types"]))
		{
			foreach($this->table["rgrps_el_types"] as $el => $type)
			{
				if ($type == "int")
				{
					$this->t->set_numeric_field("ev_".$el);
				}
			}
		}
	}

	////
	// !returns the element contents for a column that has image alias
	// params:
	// $elval - element value befure adding image
	// $alias_target - the linked image id
	// $col - the column that we are talking about
	function get_image_alias_url($elval, $alias_target, $col)
	{
		$imgdat = $this->image->get_image_by_id($alias_target);

		switch($this->table["defs"][$col]["image_type"])
		{
			case "img":
				$elval = "<img border='0' src='".$imgdat["url"]."'>";
				break;

			case "tximg":
				$elval .= "<img border='0' src='".$imgdat["url"]."'>";
				break;

			case "imgtx":
				$elval = "<img border='0' src='".$imgdat["url"]."'>".$elval;
				break;
		}
		return $elval;
	}

	////
	// !returns the element contents for a column that has form table alias - adds a link around the column
	// params:
	// $elval - element value for the column
	// $alias_target - the id if the aliased form table
	// $row_data - the data containing all tha values for the current row
	// $col - the column we are processing
	// $cc - row settings
	// $textvalue - text value of column before images and links are applied to it
	function get_ftable_alias_url($elval, $alias_target, $row_data, $col, $cc, $form_id, $textvalue)
	{
		// request uri, set in constructor
		$ru = $this->ru;
		// new approach - restrict_* are now arrays - so that they remember previous levels as well
		$ru = preg_replace("/use_table=[^&$]*/","",$ru);
		$ru = preg_replace("/tbl_sk=[^&$]*/","",$ru);
		$ru = preg_replace("/old_sk=[^&$]*/","",$ru);
		// matching form and entry
		$ru = preg_replace("/match_form=[^&$]*/","",$ru);
		$ru = preg_replace("/match_entry=[^&$]*/","",$ru);

		// and the leftover &&'s
		$ru = preg_replace("/&{2,}/","&",$ru);
		$ru = str_replace("?&", "?",$ru);
		if (strpos($ru,"?") === false)
		{
			$sep = "?";
		}
		else
		{
			$sep = "&";
		}

		$new_sk = $this->gen_uniq_id();
		$url = $ru.$sep."use_table=".$alias_target;
		// if we are doing grouping in the table then we must include the group elements value(s) as a restrict search element
		// as well, because it would make a whole lotta sense that way
		if (is_array($this->table["rgrps"]) && !$this->table["no_grpels_in_restrict"])
		{
			foreach($this->table["rgrps"] as $nr => $_dat)
			{
				$search_el = $_dat["search_to_el"] ? $_dat["search_to_el"] : $_dat["el"];
				$search_val = $_dat["search_val_el"] ? $_dat["search_val_el"] : $_dat["el"];
				$url .= "&restrict_search_el[]=".$search_el;
				$url .= "&restrict_search_val[]=".urlencode($row_data["ev_".$search_val]);
				$url .= "&restrict_search_yah[]=".urlencode($row_data["ev_".$_dat["el"]]);
			}
		}

		$url .= "&restrict_search_el[]=".$cc["search_el"];
		$url .= "&restrict_search_val[]=".urlencode($row_data["ev_".$cc["search_map"]]);
		$url .= "&restrict_search_yah[]=".urlencode($textvalue);

		$url.="&tbl_sk=".$new_sk."&old_sk=";
		if (isset($GLOBALS["tbl_sk"]))
		{
			$url .= $GLOBALS["tbl_sk"];
		};
		$url.="&match_form=".$form_id."&match_entry=".$row_data["entry_id"];

		// AND if there is a form selected as aliase for this column, then we must specify it as the new search form
		if (is_array($this->table["defs"][$col]["alias_data"]))
		{
			foreach($this->table["defs"][$col]["alias_data"] as $_aid)
			{
				if ($_aid["class_id"] == CL_FORM)
				{
					$url = preg_replace("/search_form=[^&$]*/","",$url);
					$url.="&search_form=".$_aid["target"];
				}
			}
		}
		$this->last_table_alias_url = $url;
		$ret = "<a href='".$url."'>";
		$ret.=$elval."</a>";
		return $ret;
	}

	////
	// !returns the element contents for a column that has form output alias - adds a view link to that op for the current entry
	// params:
	// $elval - column value
	// $alias_target - linked op id
	// $entry_id - the entry_id for the current row
	// $section - the current section
	function get_fop_alias_url($elval, $alias_target, $entry_id, $section)
	{
		$url = $this->mk_my_orb("show_entry", array(
			"id" => $this->get_form_for_entry($entry_id),
			"entry_id" => $entry_id,
			"op_id" => $alias_target,
			"section" => $section
		),"form");
		return "<a href='".$url."'>".$elval."</a>";
	}

	////
	// !processes all the aliases for the current row and modifies the row content accordingly
	// params:
	// $str - the current row content
	// $cc - the curent row settings ($this->table["defs"][$col])
	// $dat - the current row data 
	// $col - the current column number
	// $section - the current section
	// $form_id - the id of the form of the current entry
	// $textvalue - the value of the column without any images or links or shit
	function process_row_aliases($str, $cc, $dat, $col, $section, $form_id, $textvalue)
	{
		if (is_array($cc["alias"]))
		{
			// first, the image aliases, because they affect the row contents
			foreach($cc["alias"] as $aid)	
			{
				$alias_data = $cc["alias_data"][$aid];
				if ($alias_data["class_id"] == CL_IMAGE)
				{
					$str = $this->get_image_alias_url($str, $alias_data["target"], $col);
				}
			}

			// then the form_table aliases, cause they can add a link around the whole thing
			// and the form_output aliases, for the same reason
			foreach($cc["alias"] as $aid)	
			{
				$alias_data = $cc["alias_data"][$aid];
				if ($alias_data["class_id"] == CL_FORM_TABLE)
				{
					$str = $this->get_ftable_alias_url($str, $alias_data["target"], $dat, $col, $cc, $form_id, $textvalue);
				}
				else
				if ($alias_data["class_id"] == CL_FORM_OUTPUT)
				{
					$str = $this->get_fop_alias_url($str, $alias_data["target"], $dat["entry_id"], $section);
				}
			}
		}
		return $str;
	}

	function get_link($type, $form_id, $section, $op_id, $chain_id, $chain_entry_id, $entry_id)
	{
		$link = "";
		switch($type)
		{
			case "change":
				if ($chain_id)
				{
					// if we are in a chain, leave the chain also shown
					$link = $this->mk_my_orb("show",
						array(
							"id" => $chain_id,
							"form_id" => $form_id,
							"entry_id" => $chain_entry_id,
							"form_entry_id" => $entry_id,
							"section" => $section
						),
					"form_chain");
				}
				else
				{
					$link = $this->mk_my_orb("show", 
						array(					
							"id" => $form_id,
							"entry_id" => $entry_id,
							"section" => $section
						), 
					"form");
				}
				break;

			case "show":
				$link = $this->mk_my_orb("show_entry",array(
					"id" => $form_id,
					"entry_id" => $entry_id, 
					"op_id" => $op_id,				
					"section" => $section
				),
				"form");
				break;

			case "show_popup":
				$link = $this->mk_my_orb("show_entry",array(
					"id" => $form_id,
					"entry_id" => $entry_id, 
					"op_id" => $op_id,				
					"section" => $section
				),
				"form", false,true);
				break;

			case "delete":
				$after_show = $this->mk_my_orb("show_entry", 
					array(
						"id" => $form_id, 
						"entry_id" => $entry_id, 
						"op_id" => $op_id,
						"section" => $section
					),"form"
				);
				if ($chain_id)
				{
					$after_show = $this->mk_my_orb("show",
						array(
							"id" => $chain_id,
							"form_id" => $form_id,
							"entry_id" => $chain_entry_id,
							"section" => $section
						),
					"form_chain");
				}

				$link = $this->mk_my_orb(
					"delete_entry", 
					array(
						"id" => $form_id,
						"entry_id" => $entry_id, 
						"after" => $this->binhex($after_show)
					),
					"form"
				);
				break;
		}
		return $link;
	}

	function do_render_text_aliases($text)
	{
		$am = get_instance("aliasmgr");
		
		$aliases = $am->get_oo_aliases(array("oid" => $this->table_id));
		
		// we must do all form table aliases ourselves unfortunately. 
		// form table alias marker is w
		while (preg_match("/#w(\d+)#/U", $text, $mt))
		{
			$text = str_replace("#w".$mt[1]."#", $this->do_parse_ftbl_alias($aliases[CL_FORM_TABLE][$mt[1]]), $text);
		}
		
		$am->parse_oo_aliases($this->table_id, $text);
		return $text;
	}
	
	////
	// !renders the aliases that are passed as an array of alias id's
	function render_aliases($arr)
	{
		$tbl = "";
		// this is here to make sure that any document aliases will not be using the print template
		$print = aw_global_get("print");
		aw_global_set("print", false);

		if (is_array($arr))
		{
			$als = $this->get_aliases_for_table();
			foreach($arr as $aid)
			{
				$alias_data = $this->get_data_for_alias($aid);
				if ($alias_data["class_id"] == CL_FORM_OUTPUT)
				{
					// if it is a form output alias then show the output with the data the user clicked on last
					$tbl .= $this->do_parse_ftbl_alias($alias_data["target"]);
				}
				else
				{
					// if it is something else, oo parse the thing
					// get the #blah555# notation
					$str = " ".$als[$aid]." ";

					$amgr = get_instance("aliasmgr");
					$amgr->parse_oo_aliases($this->table_id, $str);
					$tbl .= $str;
				}
			}
		}
		aw_global_set("print", $print);
		return $tbl;
	}

	function do_parse_ftbl_alias($id)
	{
		if (!$GLOBALS["match_form"] || !$GLOBALS["match_entry"])
		{
			$this->raise_error(ERR_FG_TBL_NOSEARCHTBL, "Can't show output alias in form table, no matching form or entry set - outputs can only be shown if doing a search from a previous table", true);
		}
		$finst = get_instance("form");
		return $finst->show(array(
			"id" => $GLOBALS["match_form"],
			"entry_id" => $GLOBALS["match_entry"],
			"op_id" => $id
		));	 
	}
	
	function change_grpsettings($arr)
	{
		extract($arr);
		$this->read_template("add_table_grpsettings.tpl");
		$this->load_table($id);
		$this->mk_path($this->table_parent, "Muuda formi tabelit");
		$this->do_menu();

		$gp = get_instance("users");
		$grplist = $gp->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)));

		$l = "";
		if (is_array($this->table["grpsettings"]))
		{
			foreach($this->table["grpsettings"] as $idx => $gsettings)
			{
				$this->vars(array(
					"from_grps" => $this->mpicker($gsettings["from_grps"],$grplist),
					"to_grps" => $this->mpicker($gsettings["to_grps"],$grplist),
					"pri" => $gsettings["pri"],
					"num" => $idx
				));
				$l.=$this->parse("GRPSETTING");
			}
		}

		$this->vars(array(
			"from_grps" => $this->mpicker(array(), $grplist),
			"to_grps" => $this->mpicker(array(), $grplist),
			"pri" => "",
			"num" => $idx+1
		));
		$l.=$this->parse("GRPSETTING");

		$this->vars(array(
			"GRPSETTING" => $l,
			"reforb" => $this->mk_reforb("submit_grpsettings", array("id" => $id))
		));
		return $this->parse();
	}

	function submit_grpsettings($arr)
	{
		extract($arr);
		$this->load_table($id);

		$this->table["grpsettings"] = array();
		if (is_array($grpsettings))
		{
			foreach($grpsettings as $gst)
			{
				$gst["from_grps"] = $this->make_keys($gst["from_grps"]);
				$gst["to_grps"] = $this->make_keys($gst["to_grps"]);

				if (count($gst["from_grps"]) > 0)
				{
					$this->table["grpsettings"][] = $gst;
				}
			}
		}

		$this->save_table_settings();
		return $this->mk_my_orb("change_grpsettings", array("id" => $id));
	}

	function do_pageselector($txt)
	{
		if (strpos($txt, "#lk#") !== false)
		{
			$pgsel = "";
			if ($this->table["has_pages"])
			{
				$num_pages = $this->num_lines / $this->table["records_per_page"];
				$ru = preg_replace("/ft_page=\d*/", "", $this->ru);
				$ru = preg_replace("/\&{2,}/","&",$ru);
				$sep = "&";
				if (strpos($ru, "?") === false)
				{
					$sep = "?";
				}
				if ($this->table["has_pages_type"] == "text")	// text pageselector
				{
					$cl = "";
					if ($this->table["pg_text_style"])
					{
						$cl = "class=\"style_".$this->table["pg_lb_style"]."\"";
					}
					for ($i = 0; $i < $num_pages; $i++)
					{
						$from = $i*$this->table["records_per_page"]+1;
						$to = min(($i+1)*$this->table["records_per_page"], $this->num_lines);
						$url = $ru.$sep."ft_page=".$i;
						if ($GLOBALS["ft_page"] == $i)
						{
							$pgsel.="<span $cl>".$from." - ".$to."</span>";
						}
						else
						{
							$pgsel.="<span $cl><a href='$url'>".$from." - ".$to."</a></span>";
						}
						if ($i < ($num_pages - 1))
						{
							$pgsel.= " | ";
						}
					}
				}
				else	// listbox pageselector
				{
					$cl = "";
					if ($this->table["pg_lb_style"])
					{
						$cl = "class=\"style_".$this->table["pg_lb_style"]."\"";
					}
					$pgsel = "<select $cl name=\"ft_page\" onChange=\"window.location='".$ru.$sep."ft_page='+this.options[this.selectedIndex].value\">";
					for ($i = 0; $i < $num_pages; $i++)
					{
						$from = $i*$this->table["records_per_page"]+1;
						$to = min(($i+1)*$this->table["records_per_page"], $this->num_lines);
						$sel = "";
						if ($GLOBALS["ft_page"] == $i)
						{
							$sel = "selected";
						}
						$pgsel.= "<option $sel value='".$i."'>".$from." - ".$to;
					}
					$pgsel.="</select>";
				}
			}
			$txt = str_replace("#lk#", $pgsel, $txt);
		}
		return $txt;
	}

	function set_num_rows($num)
	{
		$this->data_num_rows = $num;
	}
}
?>
