<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_table.aw,v 2.36 2002/07/18 10:51:05 kristo Exp $
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
			"jrk" => "Jrk"
		);

		$this->lang_id = aw_global_get("lang_id");
		$this->buttons = array("save" => "Salvesta", "add" => "Lisa", "delete" => "Kustuta", "move" => "Liiguta");
		$this->ru = aw_global_get("REQUEST_URI");
		$this->image = get_instance("image");
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
			"group_style" => $this->picker(0,$css),
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
			$t_forms = $this->get_forms_for_table($id);
			
			// noojah lae aga kõik formid sisse
			if (is_array($t_forms))
			foreach($t_forms as $formid)
			{
				$formels=$this->get_form_elements(array("id"=> $formid,"key"=> "id"));
				if (is_array($formels))
				foreach($formels as $k_elid => $v_eldata)
				{
					$els[$k_elid]=$formid;
					$elsubtypes[$formid][$k_elid]["subtype"]=$v_eldata["subtype"];
					$elsubtypes[$formid][$k_elid]["thousands_sep"]=$v_eldata["thousands_sep"];
					if ($k_elid == $defaultsort)
					{
						$elsubtypes[$els[$defaultsort]][$defaultsort]["subtype"]=$v_eldata["subtype"];
					}
				};
			};
			
			$this->load_table($id);
			$this->table["defs"] = array();
			$doelsearchcols = array();
			if (is_array($columns))
			{
				$r_c = 0;
				foreach($columns as $col => $ar)
				{
					if ($todelete[$col] != 1)
					{
						if (is_array($ar))
						{
							foreach($ar as $elid)
							{
								$this->table["defs"][$r_c]["el"][$elid] = $elid;
								$this->table["defs"][$r_c]["link_el"] = $link_columns[$col];
								if (is_number($elid) && isset($els[$elid]))
								{
									$this->table["defs"][$r_c]["el_forms"][$elid] = $els[$elid];
									if ($doelsearch[$r_c] == 1)
									{
										$doelsearchcols[] = array("elid" => $elid, "elform" => $els[$elid]);
									}
								}
							}
						}
			
						//siin vaata kas on int tyyi column
						if (count($ar)==1 && $elsubtypes[$els[$elid]][$elid]["subtype"]=="int")
						{
							//elid tuleb eelmisest loobist
							//echo("column $r_c [id:$col ] has 1 el (id:$elid) of type".$elsubtypes[$els[$elid]][$elid]["subtype"]."<br>");
							$this->table["defs"][$r_c]["show_int"]=1;
							$this->table["defs"][$r_c]["thousands_sep"]= $elsubtypes[$els[$elid]][$elid]["thousands_sep"];
						};
						
						$this->table["defs"][$r_c]["lang_title"] = $names[$r_c];
						$this->table["defs"][$r_c]["sortable"] = $sortable[$r_c];
						$this->table["defs"][$r_c]["is_email"] = $is_email[$r_c];
						$this->table["defs"][$r_c]["doelsearch"] = $doelsearch[$r_c];
						$this->table["defs"][$r_c]["show_grps"] = $this->make_keys($show_grps[$r_c]);
						$this->table["defs"][$r_c]["order_form"] = $order_forms[$r_c];
						$this->table["defs"][$r_c]["linkel"] = $linkels[$r_c];
						$this->table["defs"][$r_c]["alias"] = $aliases[$r_c];
						$this->table["defs"][$r_c]["alias_data"] = $this->get_data_for_alias($aliases[$r_c]);
						$r_c++;
					}
					else
					{
						$num_cols--;
					}
				}
			}
			

			$this->table["moveto"] = array();
			if (is_array($moveto))
			{
				foreach($moveto as $mfid)
				{
					$this->table["moveto"][$mfid] = $mfid;
				}
			}
			$this->table["doelsearchcols"] = $doelsearchcols;
			$this->table["table_style"] = $tablestyle;
			$this->table["header_normal"] = $header_normal;
			$this->table["header_sortable"] = $header_sortable;
			$this->table["header_sorted"] = $header_sorted;
			$this->table["content_style1"] = $content_style1;
			$this->table["content_style2"] = $content_style2;
			$this->table["link_style"] = $link_style;
			$this->table["group_style"] = $group_style;
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
			$this->table["defaultsort"] = $defaultsort;
			$this->table["defaultsort_form"] = $els[$defaultsort];
			$this->table["defaultsort_type"] = $elsubtypes[$els[$defaultsort]][$defaultsort]["subtype"];
			$this->table["group"] = $group_el;
			$this->table["rgroup"] = $rgroup_el;
			$this->table["group_form"] = $els[$group_el];
			$this->table["rgroup_form"] = $els[$rgroup_el];
			$this->table["view_new_win"] = $view_new_win;
			$this->table["new_win_x"] = $new_win_x;
			$this->table["new_win_y"] = $new_win_y;
			$this->table["new_win_scroll"] = $new_win_scroll;
			$this->table["new_win_fixedsize"] = $new_win_fixedsize;
			$this->table["print_button"] = $print_button;
			$this->table["texts"] = $texts;
			$this->table["sel_def"] = $sel_def;
			$this->save_table(array("id" => $id, "num_cols" => $num_cols));
		}
		else
		{
			$this->id = $id = $this->new_object(array("parent" => $parent, "class_id" => CL_FORM_TABLE, "name" => $name, "comment" => $comment));
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

	function save_table($arr)
	{
		extract($arr);
		$co = aw_serialize($this->table,SERIALIZE_XML);
		$this->quote(&$co);
		$noc = "";
		if (isset($num_cols))
		{
			$noc = "num_cols = '$num_cols' , ";
		}
		$q = "UPDATE form_tables SET $noc content = '$co' WHERE id = $id";
		$this->db_query($q);
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

		// nini. siin vaja teha listboxi sobiv nimekiri elementidest
		$forms = $this->get_forms_for_table($id);
		$els = $this->get_elements_for_forms($forms);
		// lisame veel siia fake-elemendid:
		$els["change"] = "Change";
		$els["view"] = "View";
		$els["special"] = "Special";
		$els["delete"] = "Delete";
		$els["created"] = "Created";
		$els["modified"] = "modified";
		$els["uid"] = "UID";
		$els["active"] = "Active";
		$els["chpos"] = "Move";
		$els["order"] = "Order";
		$els["select"] = "Select";

		classload("languages");
		$lang = new languages;
		$lar = $lang->listall();

		classload("users");
		$us = new users;
		$grplist = $us->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)));

		for ($col=0; $col < $this->table["cols"]; $col++)
		{
			$this->vars(array(
				"column" => $col,
				"sortable" => checked($this->table["defs"][$col]["sortable"]),
				"is_email" => checked($this->table["defs"][$col]["is_email"]),
				"elements" => $this->multiple_option_list($this->table["defs"][$col]["el"],$els),
				"link_elements" => $this->picker($this->table["defs"][$col]["link_el"],$els),
				"add_col" => $this->mk_my_orb("add_col", array("id" => $id,"after" => $col)),
				"del_col" => $this->mk_my_orb("del_col", array("id" => $id,"col" => $col)),
				"doelsearch" => checked($this->table["defs"][$col]["doelsearch"]),
				"linkel" => checked($this->table["defs"][$col]["linkel"]),
				"show_grps" => $this->multiple_option_list($this->table["defs"][$col]["show_grps"],$grplist),
				"order_forms" => $this->table["defs"][$col]["order_form"],
				"aliases" => $this->picker($this->table["defs"][$col]["alias"], $this->get_aliases_for_table())
			));

			if (is_array($this->table["defs"][$col]["el"]) && in_array("order", $this->table["defs"][$col]["el"]))
			{
				$of = $this->parse("ORDER");
				$nof = "";
			}
			else
			{
				$of = "";
				if ($this->table["defs"][$col]["linkel"])
				{
					$nof = $this->parse("NOT_ORDER");
				}
				else
				{
					$nof = "";
				}
			}
			$this->vars(array(
				"ORDER" => $of,
				"NOT_ORDER" => $nof
			));

			$lh = "";
			$lit = "";
			$lh2 = "";
			$lit2 = "";
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
					"lang_name" => $la["name"],
					"lang_id" => $la["id"],
					"c_name" => $lt,
				));
				$lh.=$this->parse("LANG_H");
				$lit.=$this->parse("LANG");
			}
			$this->vars(array(
				"LANG_H" => $lh,
				"LANG" => $lit,
				"CLANG" => $lit2
			));
			$co.=$this->parse("COL");
		}

		$clh = "";
		foreach($lar as $la)
		{
			$this->vars(array(
				"lang_name" => $la["name"]
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
			foreach($lar as $la)
			{
				$this->vars(array(
					"lang_id" => $la["id"],
					"t_name" => $this->table["texts"][$fakelname][$la["id"]]
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
			"COL" => $co,
			"v_elements" => $this->picker($this->table["view_col"], array(0 => "") + $els),
			"c_elements" => $this->picker($this->table["change_col"], array(0 => "") + $els),
			"ds_elements" => $this->picker($this->table["defaultsort"], array(0 => "") + $els),
			"g_elements" => $this->picker($this->table["group"], array(0 => "") + $els),
			"rg_elements" => $this->picker($this->table["rgroup"], array(0 => "") + $els),
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
			"new_win_scroll" => checked($this->table["new_win_scroll"]),
			"new_win_fixedsize" => checked($this->table["new_win_fixedsize"]),
			"print_button" => checked($this->table["print_button"]),
			"view_new_win" => checked($this->table["view_new_win"]),
			"header_normal" => $this->picker($this->table["header_normal"],$css),
			"header_sortable" => $this->picker($this->table["header_sortable"],$css),
			"header_sorted" => $this->picker($this->table["header_sorted"],$css),
			"content_style1" => $this->picker($this->table["content_style1"],$css),
			"content_style2" => $this->picker($this->table["content_style2"],$css),
			"link_style" => $this->picker($this->table["link_style"],$css),
			"group_style" => $this->picker($this->table["group_style"],$css),
			"content_sorted_style1" => $this->picker($this->table["content_sorted_style1"],$css),
			"content_sorted_style2" => $this->picker($this->table["content_sorted_style2"],$css),
			"moveto" => $this->multiple_option_list($this->table["moveto"], $ob->get_list()),
			"top_checked" => checked($this->table["submit_top"]),
			"bottom_checked" => checked($this->table["submit_bottom"]),
			"submit_text" => $this->table["submit_text"],
			"user_button_top" => checked($this->table["user_button_top"]),
			"user_button_bottom" => checked($this->table["user_button_bottom"]),
			"user_button_text" => $this->table["user_button_text"],
			"user_button_url" => $this->table["user_button_url"],
			"aliasmgr_link" => $this->mk_my_orb("aliasmgr",array("id" => $id)),
			"sel_def" => checked($this->table["sel_def"]),
		));
		return $this->parse();
	}
	
	function aliasmgr($args = array())
	{
		extract($args);
		$this->read_template("table_aliasmgr.tpl");
		$this->vars(array(
			"table_link" => $this->mk_my_orb("change",array("id" => $id)),
			"aliasmgr_link" => $this->mk_my_orb("list_aliases",array("id" => $id),"aliasmgr"),
		));
    		return $this->parse();
	}

	function add_col($arr)
	{
		extract($arr);
		$this->load_table($id);

		for ($i=$this->table["cols"]-1; $i > $after ; $i--)
		{
			$this->table["defs"][$i+1] = $this->table["defs"][$i];
		}
		$this->table["defs"][$after+1] = array();
		$this->save_table(array("id" => $id, "num_cols" => $this->table["cols"]+1));
		header("Location: ".$this->mk_my_orb("change", array("id" => $id)));
	}

	function del_col($arr)
	{
		extract($arr);
		$this->load_table($id);

		for ($i=$col; $i < $this->table["cols"]; $i++)
		{
			$this->table["defs"][$i] = $this->table["defs"][$i+1];
		}
		$this->save_table(array("id" => $id, "num_cols" => $this->table["cols"]-1));
		header("Location: ".$this->mk_my_orb("change", array("id" => $id)));
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
	//  !returns an array of defined tables for a form
	function get_tables_for_form($id)
	{
		$ret = array();
		$this->db_query("SELECT *,objects.name FROM form_table2form LEFT JOIN objects ON (form_table2form.table_id = objects.oid) WHERE form_id = $id");
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

		// this is for no_show_oneliners 
		$this->num_lines = 0;

		// mark down the path
		$old_sk = $GLOBALS["old_sk"];
		$tbl_sk = $GLOBALS["tbl_sk"];
		$fg_table_sessions = aw_global_get("fg_table_sessions");
		if ($old_sk != "")
		{
			$fg_table_sessions[$tbl_sk] = $fg_table_sessions[$old_sk];
		}
		if (!is_array($fg_table_sessions[$tbl_sk]))
		{
			$fg_table_sessions[$tbl_sk] = Array();
		}
		if ($tbl_sk != "")
		{
			$num = count($fg_table_sessions[$tbl_sk]);
			if ($fg_table_sessions[$tbl_sk][$num-1] != aw_global_get("REQUEST_URI"))
			{
				$fg_table_sessions[$tbl_sk][] = aw_global_get("REQUEST_URI");
			}
		}
		aw_session_set("fg_table_sessions", $fg_table_sessions);
	}

	////
	// !adds another row of data to the table
	function row_data($dat,$form_id = 0,$section = 0 ,$op_id = 0,$chain_id = 0, $chain_entry_id = 0)
	{
		enter_function("form_table::row_data", array());
		if ($form_id != 0)
		{
			// here also make the view and other links
			if ($chain_id)
			{
				// if we are in a chain, leave the chain also shown
				$change_link = $this->mk_my_orb("show",
					array(
						"id" => $chain_id,
						"form_id" => $form_id,
						"entry_id" => $chain_entry_id,
						"form_entry_id" => $dat["entry_id"],
						"section" => $section
					),
				"form_chain");
			}
			else
			{
				$change_link = $this->mk_my_orb("show", 
					array(					
						"id" => $form_id,
						"entry_id" => $dat["entry_id"],
						"section" => $section
					), 
				"form");
			}

			$show_link = $this->mk_my_orb("show_entry",array(
				"id" => $form_id,
				"entry_id" => $dat["entry_id"], 
				"op_id" => $op_id,				
				"section" => $section
			),
			"form");

			$show_link_popup = $this->mk_my_orb("show_entry",array(
				"id" => $form_id,
				"entry_id" => $dat["entry_id"], 
				"op_id" => $op_id,				
				"section" => $section
			),
			"form", false,true);


			// FIXME: kõigepealt peaks kontrollima, kas tabelis üldse ev_change välja ongi, vastasel
			// korral on see acl kontroll mõttetu ajakulu -- duke
/*			if ($this->can("edit",$dat["entry_id"])  || $this->cfg["site_id"] == 11)
			{
			}*/
			$dat["ev_change"] = "<a href='".$change_link."'>".$this->table["texts"]["change"][$this->lang_id]."</a>";
			$dat["ev_created"] = $this->time2date($dat["created"], 2);
			$dat["ev_uid"] = $dat["modifiedby"];
			$dat["ev_modified"] = $this->time2date($dat["modified"], 2);
			$dat["ev_view"] = "<a href='".$show_link."'>".$this->table["texts"]["view"][$this->lang_id]."</a>";		
			$dat["ev_select"] = "<input type='checkbox' name='sel[".$dat["entry_id"]."]' ".checked($this->table["sel_def"])." VALUE='1'>";
			$dat["ev_jrk"] = "[__jrk_replace__]";

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

			$dat["ev_delete"] = "<a href='".$this->mk_my_orb(
				"delete_entry", 
				array(
					"id" => $form_id,
					"entry_id" => $dat["entry_id"], 
					"after" => $this->binhex(
						$this->mk_my_orb(
							"show_entry", 
							array(
								"id" => $this->id, 
								"entry_id" => $entry_id, 
								"op_id" => $output_id,
								"section" => $section
							)
						)
					)
				),
				"form"
			)."'>".$this->table["texts"]["delete"][$this->lang_id]."</a>";

			if (is_array($this->table["view_cols"]))
			{
				foreach($this->table["view_cols"] as $v_el)
				{
					$cl = $this->get_col_for_el($v_el);
					$_caption = $dat["ev_".$v_el];

					$popdat = $this->table["defs"][$cl];
					if ($popdat["link_popup"])
					{
						$show_link = sprintf(
							"javascript:ft_popup('%s&type=popup','popup',%d,%d,%d,%d)",
							$show_link_popup,
							$popdat["link_popup_scrollbars"],
							!$popdat["link_popup_fixed"],
							$popdat["link_popup_width"],
							$popdat["link_popup_height"]
						);
					};

					$dat["ev_".$v_el] = sprintf("<a href=\"%s\" %s>%s</a>",$show_link,$_targetwin,$_caption);
				}
			}

			if ($this->table["change_col"] && $this->table["change_col"] != "change" && ($this->can("edit",$dat["entry_id"]) || $this->cfg["site_id"] == 11))
			{
				$dat["ev_".$this->table["change_col"]] = "<a href='".$change_link."'>".$dat["ev_".$this->table["change_col"]]."</a>";
			}
		}

		// here we must preprocess the data, cause then the column names will be el_col_[col_number] not element names
		for ($col = 0; $col < $this->table["cols"]; $col++)
		{
			$cc = $this->table["defs"][$col];
			if (is_array($cc["els"]))
			{
				$str = "";
				foreach($cc["els"] as $elid => $elid)
				{
					if ($dat["ev_".$elid] != "")
					{
						// check if the column has an alias set, if it does then figure out what the fuck are we supposed to do with it
						if (is_array($cc["alias"]))
						{
							$link_started = false;
							foreach($cc["alias"] as $aid)
							{
								// check if there are any form tables as aliases
								// if we find any, let it start the link
								$alias_data = $this->table["defs"][$col]["alias_data"][$aid];
								if ($alias_data["class_id"] == CL_FORM_TABLE)
								{
									$str .= $this->get_alias_url($col, $dat["ev_".$elid], $elid,$dat,$section,$aid);
									$link_started = true;
								}
							}

							foreach($cc["alias"] as $aid)
							{
								// now find the image aliases and have them replace element content as they wish
								$alias_data = $this->table["defs"][$col]["alias_data"][$aid];
								if ($alias_data["class_id"] == CL_IMAGE)
								{
									$dat["ev_".$elid] = $this->get_alias_url($col, $dat["ev_".$elid], $elid,$dat,$section,$aid);
								}
							}

							// now let the other aliases do their thing. whatever that will be. 
							foreach($cc["alias"] as $aid)
							{
								$alias_data = $this->table["defs"][$col]["alias_data"][$aid];
								if ($alias_data["class_id"] != CL_FORM_TABLE && $alias_data["class_id"] != CL_IMAGE)
								{
									$str.=$this->get_alias_url($col, $dat["ev_".$elid], $elid,$dat,$section,$aid);
								}
							}

							// and if we started a table alias link, we end it here.
							if ($link_started)
							{
								$str.=$dat["ev_".$elid]."</a>";
							}
							$str.=$this->table["defs"][$col]["el_sep"][$elid];
						}
						else
						if ($cc["alias"])
						{
							$str.=$this->get_alias_url($col, $dat["ev_".$elid], $elid,$dat,$section).$this->table["defs"][$col]["el_sep"][$elid];
						}
						else
						if ($elid == "order")
						{
							$str .= $this->get_order_url($col,$dat["ev_".$elid],$dat).$this->table["defs"][$col]["el_sep"][$elid];
						}
						else
						{
							$str .= $dat["ev_".$elid].$this->table["defs"][$col]["el_sep"][$elid];
						}
					}
				}
				$dat["ev_col_".$col] = $str;

				if ($cc["link_el"])
				{
					$dat["ev_col_".$col] = "<a href='".$dat["ev_".$cc["link_el"]]."'>".$dat["ev_col_".$col]."</a>";
				}
				else
				if ($this->table["defs"][$col]["is_email"])
				{
					$dat["ev_col_".$col] = "<a href='mailto:".$dat["ev_col_".$col]."'>".$dat["ev_col_".$col]."</a>";
				}
			}
		}

		$this->t->define_data($dat);
		$this->num_lines++;
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
	// !draws the table and returns the html for the current table
	function finish_table()
	{
		if (is_object($this->t))
		{
			$_sby = $GLOBALS["sortby"];
			$_so = $GLOBALS["sort_order"];
			if ($_sby == "")
			{
				$_sby = "ev_".$this->table["defaultsort"];
				$_so = "asc";
			}
			if ($this->table["group"])
			{
				$_grpby = "ev_".$this->table["group"];
			}
			$this->t->sort_by(array("field" => $_sby,"sorder" => $_so,"group_by" => $_grpby));
			$css = $this->get_css();
			$contents = $this->t->draw(array(
				"rgroupby" => "ev_".$this->table["rgroup"]
			));
			return $this->get_css().$contents;
		}
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

		global $section;
		$this->start_table($table_id);

		$this->load_chain($chain_id);

		$eids = array();
		// leiame k6ik sisestused mis on tehtud $uid poolt $chain_id jaox.
		$this->db_query("SELECT id FROM form_chain_entries WHERE uid = '".aw_global_get("uid")."'");
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

		$this->t->sort_by();
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
		$tbl.=$this->t->draw(array(
			"rgroupby" => "ev_".$this->table["rgroup"]
		));

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
		return $tbl;
	}

	////
	// !shows all the entries for the logged in user of form ($form_id) with table $table_id
	function show_user_form_entries($arr)
	{
		extract($arr);

		global $section;
		$this->start_table($table_id);

		// leiame k6ik sisestused mis on tehtud $uid poolt $form_id jaox.
		$this->load($form_id);

		$this->db_query("SELECT form_".$fid."_entries.* FROM form_".$fid."_entries LEFT JOIN objects ON objects.oid = form_".$fid."_entries.id WHERE objects.status != 0 AND objects.createdby = '".aw_global_get("uid")."'");
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

		$this->t->sort_by();
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
		$tbl.=$this->t->draw(array(
			"rgroupby" => "ev_".$this->table["rgroup"]
		));

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
		return $tbl;
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
			}
		}
		return $ret;
	}

	////
	// !returns an array of menus for the loaded table where entries can be moved
	function get_menu_picker()
	{
		if (is_array($this->menu_picker))
		{
			return $this->menu_picker;
		}

		$ret = array(0 => "");
		if (is_array($this->table["moveto"]))
		{
			foreach($this->table["moveto"] as $mfid)
			{
				$mar = $this->get_object_chain($mfid,false,$this->cfg["rootmenu"]);
				$str = "";
				foreach($mar as $oid => $row)
				{
					$str=$row["name"]."/".$str;
				}
				$ret[$mfid]=$str;
			}
		}
		$this->menu_picker = $ret;
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
		if (is_array($this->table["rgrps"]))
		{
			foreach($this->table["rgrps"] as $nr => $dat)
			{
				$cl = $this->get_col_for_el($dat["el"]);
				$r_g["ev_col_".$cl] = "ev_".$dat["el"];
			}
		}
		$this->t->sort_by(array("rgroupby" => $r_g));

		$tbl = $this->get_css();
		$tbl.=$this->get_js();

		if (!$no_form_tags)
		{
			$tbl.="<form action='reforb.".$this->cfg["ext"]."' method='POST' name='tb_".$this->table_id."'>\n";
		}

		if ($this->table["submit_top"])
		{
			$tbl.="<input type='submit' value='".$this->table["submit_text"]."'>";
		}

		if ($this->table["closewin"])
		{
			global $ft_closewin;
			$ft_closewin[$output_id] = $this->table["closewin_value"];
			session_register("ft_closewin");
		};

		if ($this->table["user_button_top"])
		{
			$tbl.="&nbsp;<input type='submit' value='".$this->table["user_button_text"]."' onClick=\"window.location='".$this->table["user_button_url"]."';return false;\">";
		}

		$tbl.=$this->t->draw(array(
			"rgroupby" => $r_g
		));

		if ($this->table["submit_bottom"])
		{
			$tbl.="<input type='submit' value='".$this->table["submit_text"]."'>";
		}
		if ($this->table["user_button_bottom"])
		{
			$tbl.="&nbsp;<input type='submit' value='".$this->table["user_button_text"]."' onClick=\"window.location='".$this->table["user_button_url"]."';return false;\">";
		}

		if (!$no_form_tags)
		{
			$tbl.= $this->mk_reforb("submit_table", array("return" => $this->binhex($this->mk_my_orb("show_entry", array("id" => $this->id, "entry_id" => $entry_id, "op_id" => $output_id)))));
			$tbl.="</form>";
		}

//		echo "fgtbls = <pre>", var_dump(aw_global_get("fg_table_sessions")),"</pre> <br>";
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
		classload("style");
		$s = new style;
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
		$op.="</style>\n";
		return $op;
	}

	function get_order_url($col,$elval,$dat)
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

	function get_alias_url($col, $elval, $elid,$dat,$section ,$aid = -1)
	{
		if ($aid != -1)
		{
			$alias_data = $this->table["defs"][$col]["alias_data"][$aid];
		}
		else
		{
			$alias_data = $this->table["defs"][$col]["alias_data"];
		}
		if ($alias_data["class_id"] == CL_FORM_TABLE)
		{
			$ru = $this->ru;
			// new approach - restrict_* are now arrays - so that they remember previous levels as well
			$ru = preg_replace("/use_table=[^&$]*/","",$ru);
			$ru = preg_replace("/tbl_sk=[^&$]*/","",$ru);
			$ru = preg_replace("/old_sk=[^&$]*/","",$ru);
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
			$url = $ru.$sep."use_table=".$alias_data["target"];
			// if we are doing grouping in the table then we must include the group elements value(s) as a restrict search element
			// as well, because it would make a whole lotta sense that way
			if (is_array($this->table["rgrps"]))
			{
				foreach($this->table["rgrps"] as $nr => $_dat)
				{
					$url.= "&restrict_search_el[]=".$_dat["el"]."&restrict_search_val[]=".urlencode($dat["ev_".$_dat["el"]]);
				}
			}
			$url.="&restrict_search_el[]=".$elid."&restrict_search_val[]=".urlencode($dat["ev_".$elid])."&tbl_sk=".$new_sk."&old_sk=".$GLOBALS["tbl_sk"];
			$this->last_table_alias_url = $url;
			$ret = "<a href='".$url."'>";
			if ($aid == -1)
			{
				$ret.=$elval."</a>";
			}
			return $ret;
		}
		else
		if ($alias_data["class_id"] == CL_FORM_OUTPUT)
		{
			$url = $this->mk_my_orb("show_entry", array(
				"id" => $this->get_form_for_entry($dat["entry_id"]),
				"entry_id" => $dat["entry_id"],
				"op_id" => $alias_data["target"],
				"section" => $section
			),"form");
			return "<a href='".$url."'>".$elval."</a>";
		}
		else
		if ($alias_data["class_id"] == CL_IMAGE)
		{
			$elval = $dat["ev_".$elid];
			$imgdat = $this->image->get_image_by_id($alias_data["target"]);

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
		}
		return $elval;
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
		$this->table["has_pages_up"] = $settings["has_pages_up"];
		$this->table["has_pages_down"] = $settings["has_pages_down"];
		$this->table["page_sep_pixels"] = $settings["page_sep_pixels"];
		$this->table["skip_one_liners"] = $settings["skip_one_liners"];
		$this->table["user_entries"] = $settings["user_entries"];
		$this->table["forms"] = $this->make_keys($settings["forms"]);
		$this->table["languages"] = $this->make_keys($settings["languages"]);
		$this->table["moveto"] = $this->make_keys($settings["folders"]);
		$this->table["view_cols"] = $this->make_keys($settings["view_cols"]);
		$this->table["change_cols"] = $this->make_keys($settings["change_cols"]);
		$this->table["user_entries_except_grps"] = $this->make_keys($user_entries_except_grps);

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
				$this->table["rgrps"][] = $dat;
				$this->table["rgrps_forms"][$dat["el"]] = $els[$dat["el"]];
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

		$this->do_menu(2);

		$lang = get_instance("languages");
		$obj = get_instance("objects");
		$us = get_instance("users");

		$els = $this->get_tbl_elements();

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
			"page_sep_pixels" => $this->table["page_sep_pixels"],
			"has_pages_up" => checked($this->table["has_pages_up"]),
			"has_pages_down" => checked($this->table["has_pages_down"]),
			"uee_grps" => $this->mpicker($this->table["user_entries_except_grps"], $us->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC)))),
			"skip_one_liners" => checked($this->table["skip_one_liners"]),
			"view_cols" => $this->mpicker($this->table["view_cols"], $els),
			"change_cols" => $this->mpicker($this->table["change_cols"], $els),
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
					"els" => $this->picker($dat["el"], $els),
					"gp_count" => checked($dat["count"] == 1)
				));
				$l.= $this->parse("GRP2LINE");
				$mnr = $nr;
				$mord = max($dat["ord"], $mord);
			}
		}
		$this->vars(array(
			"grp_nr" => $mnr+1,
			"gp_ord" => $mord+1,
			"els" => $this->picker('', $els),
			"gp_count" => checked(false),
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
		$co = aw_serialize($this->table,SERIALIZE_XML);
		$this->quote(&$co);
		$q = "UPDATE form_tables SET num_cols = '".$this->table["cols"]."' , content = '$co' WHERE id = ".$this->table_id;
		$this->db_query($q);
	}

	function do_menu()
	{
		$tpl = new aw_template;
		$tpl->tpl_init("forms");
		$tpl->read_template("fg_table_menu.tpl");

		$items["new_change_cols"] = array("name" => "Tulbad", "url" => $this->mk_my_orb("new_change_cols", array("id" => $this->table_id), "",false,true));

		$items["new_change_settings"] = array("name" => "M&auml;&auml;rangud", "url" => $this->mk_my_orb("new_change_settings", array("id" => $this->table_id), "",false,true));

		$items["new_change_styles"] = array("name" => "Stiilid", "url" => $this->mk_my_orb("new_change_styles", array("id" => $this->table_id), "",false,true));

		$items["new_change_styles"] = array("name" => "Stiilid", "url" => $this->mk_my_orb("new_change_styles", array("id" => $this->table_id), "",false,true));

		if ($this->table["has_aliasmgr"])
		{
			$items["new_change_aliasmgr"] = array("name" => "Aliastehaldur", "url" => $this->mk_my_orb("new_change_aliasmgr", array("id" => $this->table_id), "",false,true));
		}

		$items["new_change_translate"] = array("name" => "T&otilde;lgi", "url" => $this->mk_my_orb("new_change_translate", array("id" => $this->table_id), "",false,true));

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

			if ($this->table["has_aliasmgr"])
			{
				$this->vars(array(
					"aliases" => $this->mpicker($this->table["defs"][$col]["alias"], $this->get_aliases_for_table())
				));
				$coldata[$col][3] = $this->parse("SEL_ALIAS");
			}

			if ($this->table["has_groupacl"])
			{
				$us = get_instance("users");
				$this->vars(array(
					"grps" => $this->mpicker($this->table["defs"][$col]["grps"], $us->get_group_picker(array("type" => array(GRP_REGULAR,GRP_DYNAMIC))))
				));
				$coldata[$col][4] = $this->parse("SEL_GRPS");
			}

			if ($this->table["defs"][$col]["link"] == 1)
			{
				$this->vars(array(
					"link" => $this->picker($this->table["defs"][$col]["link_el"], $els)
				));
				$coldata[$col][5] = $this->parse("SEL_LINK");
			}

			
			$l = "";
			if (is_array($this->table["defs"][$col]["els"]))
			{
				foreach($this->table["defs"][$col]["els"] as $el)
				{
					$this->vars(array(
						"el_name" => $els[$el],
						"el_ord" => $this->table["defs"][$col]["el_ord"][$el],
						"el_id" => $el,
						"el_sep" => $this->table["defs"][$col]["el_sep"][$el]
					));
					$l.= $this->parse("SEL_EL");
				}
			}
			$this->vars(array(
				"SEL_EL" => $l
			));
			$coldata[$col][6] = $this->parse("SEL_SETTINGS");

			$this->vars(array(
				"col_sortable" => checked($this->table["defs"][$col]["sortable"]),
				"col_email" => checked($this->table["defs"][$col]["is_email"]),
				"col_clicksearch" => checked($this->table["defs"][$col]["clicksearch"]),
				"col_link" => checked($this->table["defs"][$col]["link"]),
				"col_link_popup" => checked($this->table["defs"][$col]["link_popup"])
			));
			$coldata[$col][7] = $this->parse("SEL_SETINGS2");

			$this->vars(array(
				"popup_width" => $this->table["defs"][$col]["link_popup_width"],
				"popup_height" => $this->table["defs"][$col]["link_popup_height"],
				"scrollbars" => checked($this->table["defs"][$col]["link_popup_scrollbars"]),
				"fixed" => checked($this->table["defs"][$col]["link_popup_fixed"]),
				"toolbar" => checked($this->table["defs"][$col]["link_popup_toolbar"]),
				"addressbar" => checked($this->table["defs"][$col]["link_popup_addressbar"]),
			));
			$coldata[$col][8] = $this->parse("SEL_POPUP");

			$this->vars(array(
				"img_type_img" => checked($this->table["defs"][$col]["image_type"] == "img"),
				"img_type_tximg" => checked($this->table["defs"][$col]["image_type"] == "tximg"),
				"img_type_imgtx" => checked($this->table["defs"][$col]["image_type"] == "imgtx"),
			));
			$coldata[$col][9] = $this->parse("SEL_IMAGE");
		}

		$l = "";
		for ($idx = 1; $idx < 10; $idx++)
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
		));
		return $this->parse();
	}

	function new_submit_cols($arr)
	{
		extract($arr);
		$this->load_table($id);

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
		return $this->mk_my_orb("new_change_cols", array("id" => $id));
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
			if ($this->table["defs"][$_i]["els"][$el])
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
				$ret[$this->table["grps_forms"][$dat["gp_el"]]] = $dat["gp_el"];
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
	}
}

?>
