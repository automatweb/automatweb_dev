<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_output.aw,v 2.11 2001/07/17 03:18:53 duke Exp $

global $orb_defs;
$orb_defs["form_output"] = "xml";

class form_output extends form_base 
{
	function form_output()
	{
		$this->tpl_init("forms");
		$this->db_init();
		$this->sub_merge = 1;
		lc_load("definition");
	}

	////
	// !Kuvab vormi, kust saab valida väljundi tüüpide vahel
	// regrettably I had to call this add, or ORB will break
	function add($args = array())
	{
		extract($args);
		$this->read_template("output_category.tpl");
		$this->mk_path($parent,"Vali väljundi tüüp");
	
		$this->vars(array(
			"reforb" => $this->mk_reforb("choose_output_type",array("parent" => $parent)),
		));
		return $this->parse();
	}

	////
	// Soltuvalt eelnevast vormist valitud tüübile teeb redirecti oigesse kohta
	function choose_output_type($args = array())
	{
		extract($args);
		if ($type == "html")
		{
			$url = $this->mk_my_orb("add_html",array("parent" => $parent));
		}
		elseif ($type == "xml")
		{
			$url = $this->mk_my_orb("add_xml",array("parent" => $parent));
		};
		return $url;
	}

	////
	//
	function edit_xml($args = array())
	{
		extract($args);
		$this->read_template("add_xml_output.tpl");
		$pid = ($parent) ? $parent : $id;
		$this->mk_path($pid,"Koosta XML väljund");
		if ($id)
		{
			$odata = $this->get_object($id);
			$xdata = $this->get_object_metadata(array(
						"metadata" => $odata["metadata"],
			));
			$this->vars(array(
					"adminurl" => $this->mk_my_orb("xml_op",array("id" => $id)),
			));
		}
		$sel = ($xdata["forms"]) ? array_flip($xdata["forms"]) : array();
		$this->vars(array(
			"name" => $odata["name"],
			"comment" => $odata["comment"],
			"admin" => (isset($id)) ? $this->parse("admin") : "",
			"forms" => $this->multiple_option_list($sel, $this->get_list(FTYPE_ENTRY,true,true)),
			"reforb" => $this->mk_reforb("submit_xml",array("parent" => $parent,"id" => $id)),
		));
		return $this->parse();
	}

	////
	// 
	function submit_xml($args = array())
	{
		extract($args);
		
		if ($id)
		{
			$this->upd_object(array(
					"oid" => $id,
					"name" => $name,
					"comment" => $comment,
			));
		}
		else
		{
			$id = $this->new_object(array(
					"parent" => $parent,
					"name" => $name,
					"comment" => $comment,
					"class_id" => CL_FORM_XML_OUTPUT));
		};
		
		$xmlblock = $this->set_object_metadata(array(
						"oid" => $id,
						"key" => "forms",
						"value" => $forms,
		));
	
		$url = $this->mk_my_orb("edit_xml",array("id" => $id));
		return $url;
	}

	////
	// 
	function xml_op($args = array())
	{
		$this->mk_path($id,"Koosta XML väljund");
		$this->read_template("xml_output.tpl");
		extract($args);
		$odata = $this->get_object($id);
		$xdata = $this->get_object_metadata(array(
					"metadata" => $odata["metadata"],
		));

		if (is_array($xdata["forms"]))
		{
			$forms = "";
			foreach($xdata["forms"] as $key => $val)
			{
				$el = "";
				$this->load($val);
				$name = $this->name;
				$this->vars(array("fname" => "$name ($val)"));
				for ($i=0; $i < $this->arr["rows"]; $i++)
				{
					$cols="";
					for ($a=0; $a < $this->arr["cols"]; $a++)
					{
						if (!($arr = $this->get_spans($i, $a)))
						{
							continue;
						}
 
						$cell = &$this->arr["contents"][$arr["r_row"]][$arr["r_col"]];
						$els = $cell->get_elements();
						if (is_array($els))
						{
							foreach($els as $key => $val)
							{
								if ( ($val["type"] == "textbox") || ($val["type"] == "textarea") )
								{
									$jrk = ($xdata["data"]["jrk"][$val["id"]]) ? $xdata["data"]["jrk"][$val["id"]] : 0;
									if ($xdata["data"]["tag"][$val["id"]])
									{
										$tag = $xdata["data"]["tag"][$val["id"]];
									}
									else
									{
										// tagi nime leidmiseks stripime koigepealt 
										// nimest tühikud
										$tag = strtolower(str_replace(" ","",$val["name"]));
										if (preg_match("/(^\w*)/",$tag,$matches))
										{
											$tag = $matches[1];
										};
									};

									if ( isset($xdata["data"]["active"][$val["id"]]) )
									{
										$checked = ($xdata["data"]["active"][$val["id"]]) ? "checked" : "";
									}
									else
									{
										$checked = "checked";
									};										
									$this->vars(array(
										"id" => $val["id"],
										"jrk" => $jrk,
										"checked" => $checked,
										"tag" => $tag,
										"name" => $val["name"],
										"type" => $val["type"],
									));
									$el .= $this->parse("element");
								};
							};
						};
					};
				}
				$this->vars(array(
					"element" => $el,
				));
				$forms .= $this->parse("form");
			};
		};
		$this->vars(array(
			"form" => $forms,
			"edurl" => $this->mk_my_orb("edit_xml",array("id" => $id)),
			"reforb" => $this->mk_reforb("submit_xml_output",array("id" => $id)),
		));
		return $this->parse();
	}

	function submit_xml_output($args = array())
	{
		extract($args);
		$real_act = array();
		foreach($exists as $key => $val)
		{
			$real_act[$key] = $active[$key];
		};
		$data = array(
			"jrk" => $jrk,
			"tag" => $tag,
			"active" => $real_act,
		);
		$this->set_object_metadata(array(
					"oid" => $id,
					"key" => "data",
					"value" => $data,
		));
		return $this->mk_my_orb("xml_op",array("id" => $id));
	}

	////
	// !Kuvab vormi, kust saab valida HTML väljundi jaoks vajalikud atribuudid.
	function add_html($arr)
	{
		extract($arr);
		$this->read_template("add_output.tpl");
		$this->mk_path($parent,LC_FORM_OUTPUT_ADD_OUT_STYLE);

		classload("style");
		$st = new style;

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent)),
			"forms" => $this->multiple_option_list(array(), $this->get_list(FTYPE_ENTRY,true,true)),
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
			if (is_array($baseform))
			{
				// if the user selected a form to base this op on, make it look like the form.
				classload("form");
				$f = new form;
				$this->output= array();
				foreach($baseform as $bfid)
				{
					$f->load($bfid);

					$base_row = $this->output["rows"];
//					$base_col = $this->output["cols"]; // the op expands down so we don't need to add to the column
	
					$this->output["rows"] += $f->arr["rows"];
					$this->output["cols"] = max($f->arr["cols"],$this->output["cols"]);

					for ($row =0; $row < $f->arr["rows"]; $row++)
					{
						for ($col =0; $col < $f->arr["cols"]; $col++)
						{
							$elarr=array();
							$f->arr["contents"][$row][$col]->get_els(&$elarr);
							$this->output[$base_row+$row][$base_col+$col]["style"] = $f->arr["contents"][$row][$col]->get_style();

							$num=0;
							foreach($elarr as $el)
							{
								$this->output[$base_row+$row][$base_col+$col]["els"][$num] = $el->get_id();
								$num++;
							}
							$this->output[$base_row+$row][$base_col+$col]["el_count"] = $num;
							$this->output["map"][$base_row+$row][$base_col+$col]["col"] = $f->arr["map"][$row][$col]["col"]+$base_col;
							$this->output["map"][$base_row+$row][$base_col+$col]["row"] = $f->arr["map"][$row][$col]["row"]+$base_row;
						}
					}
				}
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
		$this->mk_path($this->parent, LC_FORM_OUTPUT_CHANGE_OUT_STYLE);

		classload("style");
		$st = new style;
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"name" => $this->name,
			"comment" => $this->comment,
			"admin" => $this->mk_orb("admin_op", array("id" => $id)),
			"forms" => $this->multiple_option_list($this->get_op_forms($id), $this->get_list(FTYPE_ENTRY,false,true)),
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

	////
	// !generates the grid used in changing the output $id
	function admin($arr)
	{
		extract($arr);
		$this->read_template("output_grid.tpl");
		$this->load_output($id);
		$this->mk_path($this->parent,sprintf(LC_FORM_OUTPUT_OUTPUT_ADMIN,$this->mk_orb("change", array("id" => $id))));
		$op_id = $id;

		// vaja on arrayd el_id => el_name k6ikide elementide kohta, mis on selle v2ljundi juurde valitud formides
		$elarr = $this->mk_elarr($id);

//		 $this->debug_map_print();

		// put all styles in this form in an array so they will be faster to use
		$style = new style;
		$style_select = $style->get_select(0,ST_CELL);
		$this->vars(array(
			"styles" => $this->picker(0,$style_select)
		));
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
					"stylesel" => $this->picker($cell["style"],$style_select),
					"ch_cell" => $this->mk_my_orb("ch_cell", array("id" => $id, "col" => $col, "row" => $row)),
					"style_name" => $style_select[$cell["style"]]
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
			"reforb"	=> $this->mk_reforb("submit_admin", array("id" => $id, "op_id" => $op_id)),
			"addr_reforb" => $this->mk_reforb("add_n_rows", array("id" => $id,"after" => $this->output["rows"]-1)),
			"addc_reforb" => $this->mk_reforb("add_n_cols", array("id" => $id,"after" => $this->output["cols"]-1)),
		));
		return $this->parse();
	}

	function add_n_rows($arr)
	{
		extract($arr);
		for ($i=0; $i < $nrows; $i++)
		{
			$this->add_row(array("id" => $id, "after" => $after));
		}
		return $this->mk_my_orb("admin_op", array("id" => $id));
	}

	function add_n_cols($arr)
	{
		extract($arr);
		for ($i=0; $i < $ncols; $i++)
		{
			$this->add_col(array("id" => $id, "after" => $after));
		}
		return $this->mk_my_orb("admin_op", array("id" => $id));
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

/*				$var = "stylesel_".$row."_".$col;
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
				}*/
				if ($sel[$row][$col] == 1)
				{
					$cell["style"] = $selstyle;
				}
			}
		}
		
		$this->save_output($id);
		return $this->mk_orb("admin_op", array("id" => $id));
	}

	function submit_admin_cell($arr)
	{
		extract($arr);
		$this->load_output($id);

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
		
		$this->save_output($id);
		return $this->mk_orb("ch_cell", array("id" => $id,"row" => $row, "col" => $col));
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
		$this->_log("form",sprintf(LC_FORM_OUTPUT_CHANGED_STYLE,$name));
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

	function ch_cell($arr)
	{
		extract($arr);
		$this->read_template("ch_op_cell.tpl");

		$this->load_output($id);

		$this->mk_path($this->parent,sprintf(LC_FORM_OUTPUT_CHANGE_OUTPUT_ADMIN,$this->mk_orb("change", array("id" => $id)),$this->mk_my_orb("admin_op",array("id" => $id))));
		$op_id = $id;

		$elarr = $this->mk_elarr($id);

		$cell = $this->output[$row][$col];

		$element="";
		for ($i=0; $i < $cell["el_count"]+1; $i++)
		{
			$this->vars(array(
				"elsel" => $this->picker($cell["els"][$i],$elarr),
				"element_id" => $row."_".$col."_".$i
			));
			$element.=$this->parse("ELEMENT");
		}

		$style = new style;
		$style_select = $style->get_select(0,ST_CELL);

		$this->vars(array(
			"cell_id" => ($row."_".$col), 
			"ELEMENT" => $element,
			"stylesel" => $this->picker($cell["style"],$style_select),
			"reforb"	=> $this->mk_reforb("submit_admin_cell", array("id" => $id, "row" => $row,"col" => $col))
		));
		return $this->parse();
	}

	function mk_elarr($id)
	{
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
					"preview" => $this->mk_orb("show_entry", array("id" => $row["form_id"], "op_id" => $id, "entry_id" => $row["oid"]),"form")
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
		return $elarr;
	}

	function check_environment(&$sys, $fix = false)
	{
		$op_table = array(
			"name" => "form_output", 
			"fields" => array(
				"id" => array("name" => "id", "length" => 11, "type" => "int", "flags" => ""),
				"op" => array("name" => "op", "length" => 65535, "type" => "blob", "flags" => "")
			)
		);

		$op2_table = array(
			"name" => "output2form", 
			"fields" => array(
				"op_id" => array("name" => "op_id", "length" => 11, "type" => "int", "flags" => ""),
				"form_id" => array("name" => "form_id", "length" => 11, "type" => "int", "flags" => "")
			)
		);

		$ret = $sys->check_admin_templates("forms", array("add_output.tpl","output_grid.tpl","ch_op_cell.tpl"));
		$ret.= $sys->check_site_templates("forms", array());
		$ret.= $sys->check_db_tables(array($op_table,$op2_table),$fix);

		return $ret;
	}
}
?>
