<?php

global $orb_defs;
$orb_defs["form_table"] = "xml";

class form_table extends form_base
{
	function form_table()
	{
		$this->form_base();
		$this->sub_merge = 1;
	}

	////
	// !shows the adding form
	function add($arr)
	{
		extract($arr);
		$this->read_template("add_table.tpl");
		$this->mk_path($parent,"Lisa formi tabel");

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

			$this->load_table($id);
			if (is_array($columns))
			{
				foreach($columns as $col => $val)
				{
					$this->table["defs"][$col]["el"] = $val;
					$this->table["defs"][$col]["title"] = $names[$col];
					$this->table["defs"][$col]["sortable"] = $sortable[$col];
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
			$this->table["table_style"] = $tablestyle;
			$this->table["header_normal"] = $header_normal;
			$this->table["header_sortable"] = $header_sortable;
			$this->table["header_sorted"] = $header_sorted;
			$this->table["content_style1"] = $content_style1;
			$this->table["content_style2"] = $content_style2;
			$this->table["content_sorted_style1"] = $content_sorted_style1;
			$this->table["content_sorted_style2"] = $content_sorted_style2;
			$this->table["submit_text"] = $submit_text;
			$this->table["submit_top"] = $submit_top;
			$this->table["submit_bottom"] = $submit_bottom;
			$this->table["user_button_top"] = $user_button_top;
			$this->table["user_button_bottom"] = $user_button_bottom;
			$this->table["user_button_text"] = $user_button_text;
			$this->table["user_button_url"] = $user_button_url;
			classload("xml");
			$x = new xml;
			$co = $x->xml_serialize($this->table);
			$this->quote(&$co);
			$this->db_query("UPDATE form_tables SET num_cols = '$num_cols' , content = '$co' WHERE id = $id");
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
		$this->mk_path($this->table_parent, "Muuda formi tabelit");

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

		for ($col=0; $col < $this->table["cols"]; $col++)
		{
			$this->vars(array(
				"column" => $col,
				"c_name" => $this->table["defs"][$col]["title"],
				"sortable" => checked($this->table["defs"][$col]["sortable"])
			));
			
			$c = "";
			foreach($els as $elid => $elname)
			{
				$this->vars(array(
					"el_id" => $elid,
					"checked" => checked($this->table["defs"][$col]["el"] == $elid)
				));
				$c.=$this->parse("COL");
			}
			$this->vars(array(
				"COL" => $c,
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
			$this->parse("ROW");
		}

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
			"header_normal" => $this->picker($this->table["header_normal"],$css),
			"header_sortable" => $this->picker($this->table["header_sortable"],$css),
			"header_sorted" => $this->picker($this->table["header_sorted"],$css),
			"content_style1" => $this->picker($this->table["content_style1"],$css),
			"content_style2" => $this->picker($this->table["content_style2"],$css),
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
		$ret = array();
		$this->db_query("SELECT * FROM form_table2form WHERE table_id = $id");
		while ($row = $this->db_next())
		{
			$ret[$row["form_id"]] = $row["form_id"];
		}
		return $ret;
	}

	////
	// !starts the table data definition for table $id
	// $header_attribs = an array of get items in the url, used to sort the table
	function start_table($id,$header_attribs)
	{
		load_vcl("table");
		$this->t = new aw_table(array(
			"prefix" => "fg_".$id,
			"self" => $PHP_SELF,
			"imgurl" => $GLOBALS["baseurl"] . "/automatweb/images"
		));
		$this->t->parse_xml_def_string($this->get_xml($id));
		$this->t->set_header_attribs($header_attribs);
	}

	////
	// !adds another row of data to the table
	function row_data($dat)
	{
		$this->t->define_data($dat);
	}

	////
	// !reads the loaded entries from array of forms $forms and adds another row of data to the table
	function row_data_from_form($forms,$special = "")
	{
		$rds = array();
		foreach($forms as $form)
		{
			$rds["el_change"] = "<a href='".$this->mk_my_orb("show", array("id" => $form->id, "entry_id" => $form->entry_id), "form")."'>Muuda</a>";
			$rds["el_view"] = "<a href='".$this->mk_my_orb("show_entry", array("id" => $form->id,"entry_id" => $form->entry_id, "op_id" => $form->arr["search_outputs"][$form->id]),"form")."'>Vaata</a>";
			$rds["el_special"] = $special;
			for ($row = 0; $row < $form->arr["rows"]; $row++)
			{
				for ($col = 0; $col < $form->arr["cols"]; $col++)
				{
					$form->arr["contents"][$row][$col]->get_els(&$elar);
					reset($elar);
					while (list(,$el) = each($elar))
					{
						$rds["el_".$el->get_id()] = $el->get_value();
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
			$this->t->sort_by(array("field" => $GLOBALS["sortby"],"sorder" => $GLOBALS["sort_order"]));
			return $this->get_css().$this->t->draw();
		}
		return "";
	}

	////
	// !shows all the entries for the logged in user of form ($form_id) or chain ($chain_id) with table $table_id
	function show_user_entries($arr)
	{
		extract($arr);
		$this->start_table($table_id,array("class" => "form_table", "action" => "show_user_entries", "form_id" => $form_id, "chain_id" => $chain_id, "table_id" => $table_id));

		classload("form");
		$far = array();
		if ($form_id)
		{
			// teeme nimekirja yhe formi sisestustest
			$far[0] = new form;
			$far[0]->load($form_id);
			$cnt=1;
			$this->db_query("SELECT objects.*,form_".$form_id."_entries.* FROM objects LEFT JOIN form_".$form_id."_entries ON objects.oid = form_".$form_id."_entries.id WHERE objects.status = 2 AND createdby = '".$GLOBALS["uid"]."'");
		}
		else
		if ($chain_id)
		{
			$this->load_chain($chain_id);
			$ftables = array();
			$fjoins = array();
			$ftables[] = "form_chain_entries.*";
			$cnt=0;
			foreach($this->chain["forms"] as $fid)
			{
				$ftables[] = "form_".$fid."_entries.*";
				$fjoins[] = "LEFT JOIN form_".$fid."_entries ON form_".$fid."_entries.chain_id = form_chain_entries.id";
				$far[$cnt] = new form;
				$far[$cnt]->load($fid);
				$cnt++;
			}
			$fts = join(",",$ftables);
			$fjs = join(" ",$fjoins);
			$this->db_query("SELECT $fts FROM form_chain_entries $fjs WHERE form_chain_entries.uid = '".$GLOBALS["uid"]."' GROUP BY form_chain_entries.id");
		}
		classload("xml");
		$x = new xml;
		while ($row = $this->db_next())
		{
			if ($chain_id && $row["chain_id"])
			{
				// get chain entry ids
				$char = $x->xml_unserialize(array("source" => $row["ids"]));
				$rds = array();
				for ($i=0; $i < $cnt; $i++)
				{
					$form = &$far[$i];
					if (!is_array($row))
					{
						continue;
					}
					$form->load_entry_from_data($row,$char[$form->id]);

					$rds["el_change"] = "<a href='".$this->mk_my_orb("show", array("id" => $chain_id, "section" => "0","entry_id" => $row["chain_id"]), "form_chain")."'>Muuda</a>";

					$rds["el_view"] = "<a href='".$this->mk_my_orb("show_entry", array("id" => $form->id,"entry_id" => $form->entry_id, "op_id" => $op_id),"form")."'>Vaata</a>";

					for ($row = 0; $row < $form->arr["rows"]; $row++)
					{
						for ($col = 0; $col < $form->arr["cols"]; $col++)
						{
							$form->arr["contents"][$row][$col]->get_els(&$elar);
							reset($elar);
							while (list(,$el) = each($elar))
							{
								$rds["el_".$el->get_id()] = $el->get_value();
							}
						}
					}
				}
				$this->t->define_data($rds);
			}
			else
			if ($form_id)
			{
				$far[0]->load_entry_from_data($row,$row["id"]);
				$this->row_data_from_form($far);
			}
		}
		return $this->finish_table();
	}

	////
	// !returns the xml definition for table $id to be passed to the table generator. if no id specified, presumes table is loaded already
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
			$xml.="<field name=\"el_".$cc["el"]."\" caption=\"".$cc["title"]."\" talign=\"center\" align=\"center\"";
			if ($cc["sortable"])
			{
				$xml.=" sortable=\"1\" ";
			}
			$xml.="/>\n";
		}
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
			$op.= $s->get_css($this->table["header_normal"]);
		}
		if ($this->table["header_sortable"])
		{
			$op.= $s->get_css($this->table["header_sortable"]);
		}
		if ($this->table["header_sorted"])
		{
			$op.= $s->get_css($this->table["header_sorted"]);
		}
		if ($this->table["content_style1"])
		{
			$op.= $s->get_css($this->table["content_style1"]);
		}
		if ($this->table["content_style2"])
		{
			$op.= $s->get_css($this->table["content_style2"]);
		}
		if ($this->table["content_sorted_style1"])
		{
			$op.= $s->get_css($this->table["content_sorted_style1"]);
		}
		if ($this->table["content_sorted_style2"])
		{
			$op.= $s->get_css($this->table["content_sorted_style2"]);
		}
		$op.="</style>\n";
		return $op;
	}
}

?>