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

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"forms" => $this->multiple_option_list(array(),$this->get_list(FTYPE_ENTRY)),
			"tablestyles" => $this->picker(0,$s->get_select(0,ST_TABLE)),
			"header_normal" => $this->picker(0,$css),
			"header_sortable" => $this->picker(0,$css),
			"header_sorted" => $this->picker(0,$css),
			"content_style1" => $this->picker(0,$css),
			"content_style2" => $this->picker(0,$css),
			"content_sorted_style1" => $this->picker(0,$css),
			"content_sorted_style2" => $this->picker(0,$css),
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
			$this->table["table_style"] = $tablestyle;
			$this->table["header_normal"] = $header_normal;
			$this->table["header_sortable"] = $header_sortable;
			$this->table["header_sorted"] = $header_sorted;
			$this->table["content_style1"] = $content_style1;
			$this->table["content_style2"] = $content_style2;
			$this->table["content_sorted_style1"] = $content_sorted_style1;
			$this->table["content_sorted_style2"] = $content_sorted_style2;
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
				"view_checked" => checked($this->table["defs"][$col]["el"] == "view")
			));
			$this->parse("ROW");
		}

		classload("style");
		$s = new style;
		$css = $s->get_select(0,ST_CELL);
		$this->vars(array(
			"name" => $this->table_name,
			"comment" => $this->table_comment,
			"num_cols" => $this->table["cols"],
			"forms" => $this->multiple_option_list($forms, $this->get_list(FTYPE_ENTRY)),
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