<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_output.aw,v 2.4 2001/06/14 08:47:39 kristo Exp $

global $orb_defs;
$orb_defs["form_output"] = "xml";

/*										array("list_op"					=> array("function" => "gen_output_list", "params" => array("id")),
													"add_op"					=> array("function" => "add_output",			"params" => array("id")),
													"submit_op"				=> array("function" => "admin_output",		"params" => array()),
													"change_op"				=> array("function" => "gen_output_grid", "params" => array("id", "op_id")),
													"op_add_col"			=> array("function" => "op_add_col",			"params" => array("id", "op_id", "after")),
													"op_add_row"			=> array("function" => "op_add_row",			"params" => array("id", "op_id", "after")),
													"op_del_col"			=> array("function" => "op_del_col",			"params" => array("id", "op_id", "col")),
													"op_del_row"			=> array("function" => "op_del_row",			"params" => array("id", "op_id", "row")),
													"op_exp_left"			=> array("function" => "op_exp_left",			"params" => array("id", "op_id", "row", "col")),
													"op_exp_up"				=> array("function"	=> "op_exp_up",				"params" => array("id", "op_id", "row", "col")),
													"op_exp_down"			=> array("function"	=> "op_exp_down",			"params" => array("id", "op_id", "row", "col")),
													"op_exp_right"		=> array("function" => "op_exp_right",		"params" => array("id", "op_id", "row", "col")),
													"submit_op_grid"	=> array("function" => "save_output_grid","params" => array()),
													"op_style"				=> array("function" => "gen_output_settings", "params" => array("id", "op_id")),
													"submit_op_settings" => array("function" => "save_output_settings", "params" => array()),
													"op_meta"					=> array("function" => "gen_output_meta", "params" => array("id" , "op_id")),
													"save_op_meta"		=> array("function" => "save_output_meta", "params" => array())
												);*/

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

						$this->output[$row][$col]["el_count"] = count($elarr);
						for ($i=0; $i < $this->output[$row][$col]["el_count"]; $i++)
						{
							$this->output[$row][$col]["els"][$i] = $elarr[$i]->get_id();
						}
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
			$this->db_query(
				"SELECT distinct(el_id) as el_id, objects.name as name
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

// ---------------------------------------------------------------------------------
	////
	// !lists the outputs for this form
/*	function gen_output_list($arr)
	{
		extract($arr);
		$this->init($id, "output_list.tpl","V&auml;ljundite nimekiri");

		$this->db_query("SELECT form_output.*, objects.name as name, objects.oid as oid, objects.comment as comment
										 FROM form_output
										 LEFT JOIN objects ON objects.oid = form_output.id
										 WHERE objects.status !=0 AND objects.parent = $this->id
										 GROUP BY objects.oid");
		$this->vars(array("LINE" => ""));
		while ($row = $this->db_next())
		{
			$this->vars(array("name" => $row[name], "comment" => $row[comment], "id" => $row[oid],
												"change"	=> $this->mk_orb("change_op", array("id" => $this->id, "op_id" => $row[oid])),
												"delete"	=> $this->mk_orb("delete_op", array("id" => $this->id, "op_id" => $row[oid]))));

			$co = "";
			$co = $this->parse("CHANGE_OP");
			$do = "";
			$do = $this->parse("DELETE_OP");
			
			$this->vars(array("CHANGE_OP"=>$co, "DELETE_OP"=>$do));
			$this->parse("LINE");
		}
		$this->vars(array("add" => $this->mk_orb("add_op", array("id" => $this->id))));
		$ca = "";
		$ca = $this->parse("ADD_OP");
		$this->vars(array("ADD_OP" => $ca));
		return $this->do_menu_return();
	}

	////
	// !generates output adding UI for form
	function add_output($arr)
	{
		extract($arr);
		$this->init($id, "add_output.tpl", "<a href='".$this->mk_orb("change", array("id" => $this->id))."'>Muuda formi</a> / Lisa v&auml;ljund");
		$this->vars(array("name" => "", "comment" => "", "id" => "",
											"reforb" => $this->mk_reforb("submit_op", array("id" => $this->id))));
		return $this->parse();
	}

	////
	// !saves the properties of the output
	function admin_output($arr)
	{
		$this->dequote(&$arr);
		extract($arr);
		$this->load($id);

		if ($op_id)
		{
			$this->upd_object(array("oid" => $op_id, "name" => $name, "comment" => $comment));
			$this->_log("form","Muutis formi $this->name outputi stiili $name");
		}
		else
		{
			$this->output= array();
			// when adding a new output, make it look like the form by default.
			$this->output[rows] = $this->arr[rows];
			$this->output[cols] = $this->arr[cols];
			for ($row =0; $row < $this->arr[rows]; $row++)
			{
				for ($col =0; $col < $this->arr[cols]; $col++)
				{
					$elarr=array();
					$this->arr[contents][$row][$col]->get_els(&$elarr);
					$this->output[$row][$col][style] = $this->arr[contents][$row][$col]->get_style();

					$this->output[$row][$col][el_count] = count($elarr);
					for ($i=0; $i < $this->output[$row][$col][el_count]; $i++)
					{
						$this->output[$row][$col][els][$i] = $elarr[$i]->get_id();
					}
				}
			}
			$this->output[map] = $this->arr[map];
			$tp = serialize($this->output);
			$id = $this->new_object(array("parent" => $this->id, "name" => $name, "class_id" => CL_FORM_OUTPUT, "comment" => $comment));
			$this->db_query("insert into form_output values($id, '$tp')");
			$this->_log("form","Lisas formile $this->name outputi stiili $name");
		}
		return $this->mk_orb("list_op", array("id" => $this->id));
	}

	////
	// !generates the form to change the table properties of output $opid of form $id
	function gen_output_settings($arr)
	{
		extract($arr);
		$this->init($id, "output_settings.tpl", "Muuda v&auml;ljundit");
		$this->load_output($op_id);

		$st = new style;

		$this->vars(array("form_bgcolor"				=> $this->output[bgcolor],
											"form_border"					=> $this->output[border],
											"form_cellpadding"		=> $this->output[cellpadding],
											"form_cellspacing"		=> $this->output[cellspacing],
											"form_height"					=> $this->output[height],
											"form_width"					=> $this->output[width],
											"form_hspace"					=> $this->output[hspace],
											"form_vspace"					=> $this->output[vspace],
											"id"									=> $id,
											"reforb"							=> $this->mk_reforb("submit_op_settings", array("id" => $this->id, "op_id" => $op_id)),
											"def_style"						=> $this->picker($this->output[def_style],$st->get_select(0,ST_CELL))));
		return $this->do_menu_return();
	}

	////
	// !generates the form for changing output metainfo
	function gen_output_meta($arr)
	{
		extract($arr);
		$this->init($id, "output_metainfo.tpl", "V&auml;ljundi metainfo");
		
		$this->db_query("SELECT form_output.*, objects.name as name, objects.comment as comment, objects.created as created, 
														objects.createdby as createdby, objects.modified as modified, objects.modifiedby as modifiedby,
														objects.hits as hits
										 FROM form_output
										 LEFT JOIN objects ON objects.oid = form_output.id
										 LEFT JOIN acl ON acl.oid = objects.oid
										 WHERE form_output.id = $op_id
										 GROUP BY objects.oid");
		if (!($row = $this->db_next()))
		{
			$this->raise_error("form->change_output($id): No such output!", true);
		}

		$this->vars(array("op_name" => $row[name], "op_comment" => $row[comment], "id" => $op_id,
											"modified" => $this->time2date($row[modified],2),
											"modified_by" => $row[modifiedby], "created" => $this->time2date($row[created],2), 
											"created_by" => $row[createdby], "views"=>$row[hits],
											"reforb"		=> $this->mk_reforb("save_op_meta", array("id" => $this->id, "op_id" => $op_id))));

		return $this->do_menu_return();
	}

	function delete_output($id)
	{
		$this->delete_object($id);
		$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $id","name");
		$this->_log("form","Kustutas formi $this->name outputi stiili $name");
	}

	////
	// !saves the metadata of output $op_id on form $id
	function save_output_meta($arr)
	{
		$this->dequote(&$arr);
		extract($arr);

		$this->upd_object(array("oid" => $op_id, "name" => $name, "comment" => $comment));
		$this->_log("form","Muutis formi $this->name outputi stiili $name");
		return $this->mk_orb("op_meta", array("id" => $id, "op_id" => $op_id));
	}

	////
	// !saves table settings for output $op_id for form $id
	function save_output_settings($arr)
	{
		$this->dequote(&$arr);
		extract($arr);
		$this->load($id);
		$this->load_output($op_id);

		$this->output[bgcolor] = $bgcolor;
		$this->output[border] = $border;
		$this->output[cellpadding]	= $cellpadding;
		$this->output[cellspacing] = $cellspacing;
		$this->output[height] = $height;
		$this->output[width] = ($width > 316 ? 316 : $width);
		$this->output[height] = $height;
		$this->output[hspace] = $hspace;
		$this->output[vspace] = $vspace;
		$this->output[def_style] = $def_style;

		$this->save_output($op_id);
		return $this->mk_orb("op_style", array("id" => $id, "op_id" => $op_id));
	}

	////
	// !returns an array op_id => op_name for all outputs of form $id
	function get_op_list($arr)
	{
		extract($arr);
		$this->load($id);

		$ret = array();
		$this->db_query("SELECT form_output.*, objects.name as name, objects.oid as oid, objects.comment as comment
										 FROM form_output
										 LEFT JOIN objects ON objects.oid = form_output.id
										 WHERE objects.status !=0 AND objects.parent = $this->id
										 GROUP BY objects.oid");
		while ($row = $this->db_next())
		{
			$ret[$row[oid]] = $row[name];
		}
		return $ret;
	}*/
}
?>
