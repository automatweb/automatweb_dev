<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_output.aw,v 2.6 2001/06/21 03:51:30 kristo Exp $

global $orb_defs;
$orb_defs["form_output"] = "xml";

class form_output extends form_base 
{
	function form_output()
	{
		$this->tpl_init("forms");
		$this->db_init();
		$this->sub_merge = 1;
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add_output.tpl");
		$this->mk_path($parent,"Lisa v&auml;ljundi stiil");

		classload("style");
		$st = new style;

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"forms" => $this->multiple_option_list(array(), $this->get_list(FTYPE_ENTRY,true)),
			"styles" => $this->picker(0,$st->get_select(0,ST_TABLE))
		));
		$this->parse("ADD");
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array("oid" => $id, "name" => $name, "comment" => $comment));
		}
		else
		{
			$id = $this->new_object(array("parent" => $parent, "name" => $name, "comment" => $comment, "class_id" => CL_FORM_OUTPUT));
			$this->db_query("INSERT INTO form_output(id) VALUES($id)");
			$this->load_output($id);
			if ($baseform)
			{
				// if the user selected a form to base this op on, make it look like the form.
				classload("form");
				$f = new form;
				$f->load($baseform);

				$this->output= array();
				$this->output["rows"] = $f->arr["rows"];
				$this->output["cols"] = $f->arr["cols"];

				for ($row =0; $row < $f->arr["rows"]; $row++)
				{
					for ($col =0; $col < $f->arr["cols"]; $col++)
					{
						$elarr=array();
						$f->arr["contents"][$row][$col]->get_els(&$elarr);
						$this->output[$row][$col]["style"] = $f->arr["contents"][$row][$col]->get_style();

						$num=0;
						foreach($elarr as $el)
						{
							$this->output[$row][$col]["els"][$num] = $el->get_id();
							$num++;
						}
						$this->output[$row][$col]["el_count"] = $num;
					}
				}
				$this->output["map"] = $f->arr["map"];
				$this->save_output($id);
			}
		}

		$this->db_query("DELETE FROM output2form WHERE op_id = $id");
		if (is_array($forms))
		{
			foreach($forms as $fid)
			{
				$this->db_query("INSERT INTO output2form (op_id, form_id) VALUES($id,'$fid')");
			}
		}
		
		$this->load_output($id);
		$this->output["table_style"] = $table_style;
		$this->save_output($id);
		return $this->mk_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$this->load_output($id);
		$this->read_template("add_output.tpl");
		$this->mk_path($this->parent, "Muuda v&auml;ljundi stiili");

		classload("style");
		$st = new style;
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"name" => $this->name,
			"comment" => $this->comment,
			"admin" => $this->mk_orb("admin_op", array("id" => $id)),
			"forms" => $this->multiple_option_list($this->get_op_forms($id), $this->get_list(FTYPE_ENTRY)),
			"styles" => $this->picker($this->output["table_style"],$st->get_select(0,ST_TABLE))
		));
		$this->parse("CHANGE");
		return $this->parse();
	}

	function debug_map_print()
	{
		echo "<table border=1>";
		for ($r=0; $r < $this->output["rows"]; $r++)
		{
			echo "<tr>";
			for ($c=0; $c < $this->output["cols"]; $c++)
				echo "<td>(", $this->output["map"][$r][$c]["row"], ",",$this->output["map"][$r][$c]["col"],")</td>";
			echo "</tr>";
		}
		echo "</table>";
	}

	function get_op_forms($op_id)
	{
		$ret = array();
		$this->db_query("SELECT form_id FROM output2form WHERE op_id = $op_id");
		while ($row = $this->db_next())
		{
			$ret[$row["form_id"]] = $row["form_id"];
		}
		return $ret;
	}

	////
	// !generates the grid used in changing the output $id
	function admin($arr)
	{
		extract($arr);
		$this->read_template("output_grid.tpl");
		$this->load_output($id);
		$this->mk_path($this->parent,"<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda v&auml;jundit</a> / Adminni");
		$op_id = $id;

		// vaja on arrayd el_id => el_name k6ikide elementide kohta, mis on selle v2ljundi juurde valitud formides
		$elarr = array("0" => "");
		$op_forms = $this->get_op_forms($id);
		$fidstring = join(",",$this->map2("%s",$op_forms));
		if ($fidstring != "")
		{
			// make preview
			$this->db_query("SELECT oid,form_id FROM objects LEFT JOIN form_entries ON form_entries.id = objects.oid WHERE class_id = ".CL_FORM_ENTRY." AND status != 0 AND form_entries.form_id IN ($fidstring)");
			$row = $this->db_next();
			if ($row)
			{
				$this->vars(array(
					"preview" => $this->mk_orb("show_entry", array("id" => $row["form_id"], "op_id" => $op_id, "entry_id" => $row["oid"]),"form")
				));
				$this->vars(array("PREVIEW" => $this->parse("PREVIEW")));
			}

			$this->db_query(
				"SELECT el_id, objects.name as name
				 FROM element2form 
					 LEFT JOIN objects ON objects.oid = element2form.el_id
					WHERE form_id IN ($fidstring)"
			);
			while ($row = $this->db_next())
			{
				$elarr[$row["el_id"]] = $row["name"];
			}
		}

//		 $this->debug_map_print();

		// put all styles in this form in an array so they will be faster to use
		$style = new style;
		$style_select = $style->get_select(0,ST_CELL);

		// tabeli ylemine rida delete column nuppudega
		for ($a=0; $a < $this->output["cols"]; $a++)
		{
			$fc = "";
			if ($a == 0)
			{
				$this->vars(array("add_col" => $this->mk_orb("add_col", array("id" => $op_id, "after" => -1))));
				$fc = $this->parse("FIRST_C");
			}

			$this->vars(array(
				"add_col" => $this->mk_orb("add_col", array("id" => $op_id, "after" => $a)),
				"del_col" => $this->mk_orb("del_col", array("id" => $op_id, "col" => $a)),
				"FIRST_C" => $fc
			));
			$this->parse("DC");
		}

		for ($row = 0; $row < $this->output["rows"]; $row++)
		{
			$this->vars(array("COL" => ""));
			for ($col = 0; $col < $this->output["cols"]; $col++)
			{
				if (!($arr = $this->get_spans($row, $col, $this->output["map"], $this->output["rows"], $this->output["cols"])))
				{
					// kui see cell on peidus m6ne teise all, siis 2rme seda joonista
					continue;
				}
				
				$rcol = $arr["r_col"];
				$rrow = $arr["r_row"];
				$cell = $this->output[$rrow][$rcol];

				$element="";
				for ($i=0; $i < $cell["el_count"]+1; $i++)
				{
					$this->vars(array(
						"elsel" => $this->picker($cell["els"][$i],$elarr),
						"element_id" => $rrow."_".$rcol."_".$i
					));
					$element.=$this->parse("ELEMENT");
				}

				$this->vars(array(
					"colspan" => $arr["colspan"], 
					"rowspan" => $arr["rowspan"],
					"num_els_plus3" => $cell["el_count"]+5,
					"col" => $rcol, 
					"row" => $rrow,
					"cell_id" => ($rrow."_".$rcol), 
					"ELEMENT" => $element, 
					"exp_left"	=> $this->mk_orb("exp_left", array("id" => $op_id, "col" => $col, "row" => $row)),
					"exp_right"	=> $this->mk_orb("exp_right", array("id" => $op_id, "col" => $col, "row" => $row)),
					"exp_up"	=> $this->mk_orb("exp_up", array("id" => $op_id, "col" => $col, "row" => $row)),
					"exp_down"	=> $this->mk_orb("exp_down", array("id" => $op_id, "col" => $col, "row" => $row)),
					"split_ver"	=> $this->mk_orb("split_cell_ver", array("id" => $id, "col" => $col, "row" => $row)),
					"split_hor"	=> $this->mk_orb("split_cell_hor", array("id" => $id, "col" => $col, "row" => $row)),
					"stylesel" => $this->picker($cell["style"],$style_select)
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
				if ($row != 0)
				{
					$eu = $this->parse("EXP_UP");
				}
				$el = "";
				if ($col != 0)
				{
					$el = $this->parse("EXP_LEFT");
				}
				$er = "";
				if (($col+$arr["colspan"]) != $this->output["cols"])
				{
					$er = $this->parse("EXP_RIGHT");
				}
				$ed = "";
				if (($row+$arr["rowspan"]) != $this->output["rows"])
				{
					$ed = $this->parse("EXP_DOWN");
				}
				$this->vars(array(
					"SPLIT_HORIZONTAL" => $sh, 
					"SPLIT_VERTICAL" => $sv, 
					"EXP_UP" => $eu, 
					"EXP_LEFT" => $el, 
					"EXP_RIGHT" => $er,
					"EXP_DOWN" => $ed
				));
				$spls = "";
				if ($sh != "" || $sv != "")
				{
					$spls = $this->parse("SPLITS");
				}
				$this->vars(array("SPLITS" => $spls));
				$this->parse("COL");
			}
			$fi = "";
			if ($row==0)
			{
				$this->vars(array("add_row" => $this->mk_orb("add_row", array("id" => $op_id, "after" => -1))));
				$fi = $this->parse("FIRST_R");
			}
			$this->vars(array(
				"add_row" => $this->mk_orb("add_row", array("id" => $op_id, "after" => $row)),
				"del_row" => $this->mk_orb("del_row", array("id" => $op_id, "row" => $row)),
				"FIRST_R" => $fi
			));
			$this->parse("LINE");
		}

		$this->vars(array(
			"reforb"	=> $this->mk_reforb("submit_admin", array("id" => $id, "op_id" => $op_id))
		));
		return $this->parse();
	}

	////
	// !saves the output grid ($id)
	function submit_admin($arr)
	{
		extract($arr);
		$this->load_output($id);

		for ($row=0; $row < $this->output["rows"]; $row++)
		{
			for ($col=0; $col < $this->output["cols"]; $col++)
			{
				$cell = &$this->output[$row][$col];

				$var = "stylesel_".$row."_".$col;
				$cell["style"] = $$var;

				for ($i=0; $i < $cell["el_count"]+1; $i++)
				{
					$var = "elsel_".$row."_".$col."_".$i;
					$cell["els"][$i] = $$var;
					$ls = $$var;
				}
				if ($ls != 0)
				{
					$cell["el_count"]++;
				}
				else
				if ($cell["els"][$cell["el_count"]-1] == 0)
				{
					$cell["el_count"]--;
				}

				if ($cell["el_count"] < 0)
				{
					$cell["el_count"] = 0;
				}
			}
		}
		
		$this->save_output($id);
		return $this->mk_orb("admin_op", array("id" => $id));
	}

	////
	// !saves the current state of the loaded form output
	function save_output($id)
	{
		// saveme xml
		classload("xml");
		$x = new xml;
		$tp = $x->xml_serialize($this->output);
		$this->quote(&$tp);
		$this->db_query("UPDATE form_output SET op = '$tp' WHERE id = $id");
		$this->upd_object(array("oid" => $id));
		$this->_log("form","Muutis outputi stiili $name");
	}

	////
	// !adds a column to output $id after $after
	function add_col($arr)
	{
		extract($arr);
		$this->load_output($id);

		for ($row = 0; $row < $this->output["rows"]; $row++)
		{
			for ($i=$this->output["cols"]; $i > $after; $i--)
			{
				$this->output[$row][$i+1] = $this->output[$row][$i];
			}
		}

		for ($row = 0; $row < $this->output["rows"]; $row++)
		{
			$this->output[$row][$after+1] = "";
		}

		$this->output["cols"]++;
		$this->map_add_col($this->output["rows"], $this->output["cols"], &$this->output["map"],$after);

		$this->save_output($id);
		$orb = $this->mk_orb("admin_op", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !deletes the column $col of output $id
	function del_col($arr)
	{
		extract($arr);
		$this->load_output($id);

		for ($row = 0; $row < $this->output["rows"]; $row++)
		{
			for ($i=$col; $i < ($this->output["cols"]-1); $i++)
			{
				$this->output[$row][$i] = $this->output[$row][$i+1];
			}
		}

		for ($row = 0; $row < $this->output["rows"]; $row++)
		{
			$this->output[$row][$this->output["cols"]-1] = "";
		}

		$this->map_del_col($this->output["rows"], $this->output["cols"], &$this->output["map"],$col);
		$this->output["cols"]--;

		$this->save_output($id);
		$orb = $this->mk_orb("admin_op", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !adds a row to output $id after row $after
	function add_row($arr)
	{
		extract($arr);
		$this->load_output($id);

		for ($row=$this->output["rows"]; $row > $after; $row--)
		{
			for ($col=0; $col < $this->output["cols"]; $col++)
			{
				$this->output[$row+1][$col] = $this->output[$row][$col];
			}
		}

		for ($col = 0; $col < $this->output["cols"]; $col++)
		{
			$this->output[$after+1][$col] = "";
		}

		$this->output["rows"]++;
		$this->map_add_row($this->output["rows"], $this->output["cols"], &$this->output["map"], $after);

		$this->save_output($id);
		$orb = $this->mk_orb("admin_op", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !deletes row $row of output $id 
	function del_row($arr)
	{
		extract($arr);
		$this->load_output($id);

		$row_d = $row;
		for ($row=$row_d; $row < ($this->output["rows"]-1); $row++)
		{
			for ($col=0; $col < $this->output["cols"]; $col++)
			{
				$this->output[$row][$col] = $this->output[$row+1][$col];
			}
		}

		for ($col = 0; $col < $this->output["cols"]; $col++)
		{
			$this->output[$this->output["rows"]-1][$col] = "";
		}

		$this->map_del_row($this->output["rows"], $this->output["cols"], &$this->output["map"], $row_d);
		$this->output["rows"]--;
		$this->save_output($id);
		$orb = $this->mk_orb("admin_op", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !merges the cell ($row, $col) in output $id with the cell immediately above it
	function exp_up($arr)
	{
		extract($arr);
		$this->load_output($id);
		$this->map_exp_up($this->output["rows"], $this->output["cols"], &$this->output["map"],$row,$col);
		$this->save_output($id);
		$orb = $this->mk_orb("admin_op", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !merges the cell ($row,$col) in output $id with the cell below it
	function exp_down($arr)
	{
		extract($arr);
		$this->load_output($id);
		$this->map_exp_down($this->output["rows"], $this->output["cols"], &$this->output["map"],$row,$col);
		$this->save_output($id);
		$orb = $this->mk_orb("admin_op", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !merges the cell ($row,$col) in output $id with the cell to the left of it
	function exp_left($arr)
	{
		extract($arr);
		$this->load_output($id);
		$this->map_exp_left($this->output["rows"], $this->output["cols"], &$this->output["map"],$row,$col);
		$this->save_output($id);
		$orb = $this->mk_orb("admin_op", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !merges the cell ($row,$col) in output $id with the cell to the right of it
	function exp_right($arr)
	{
		extract($arr);
		$this->load_output($id);
		$this->map_exp_right($this->output["rows"], $this->output["cols"], &$this->output["map"],$row,$col);
		$this->save_output($id);
		$orb = $this->mk_orb("admin_op", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !splits the cell ($row,$col) in output $id vertically
	function split_cell_ver($arr)
	{
		extract($arr);
		$this->load_output($id);
		$this->map_split_ver($this->output["rows"], $this->output["cols"], &$this->output["map"],$row,$col);
		$this->save_output($id);
		$orb = $this->mk_orb("admin_op", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !splits the cell ($row,$col) in output $id horizontally
	function split_cell_hor($arr)
	{
		extract($arr);
		$this->load_output($id);
		$this->map_split_hor($this->output["rows"], $this->output["cols"], &$this->output["map"],$row,$col);
		$this->save_output($id);
		$orb = $this->mk_orb("admin_op", array("id" => $id));
		header("Location: $orb");
		return $orb;
	}
}
?>
