<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_output.aw,v 2.2 2001/05/21 04:01:06 kristo Exp $

global $orb_defs;
$orb_defs["form_output"] = 
										array("list_op"					=> array("function" => "gen_output_list", "params" => array("id")),
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
												);

class form_output extends form_base 
{
	function form_output()
	{
		$this->tpl_init("forms");
		$this->db_init();
		$this->sub_merge = 1;
	}

	////
	// !lists the outputs for this form
	function gen_output_list($arr)
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

		$this->update_object($op_id, $name, -1, $comment);
		$this->_log("form","Muutis formi $this->name outputi stiili $name");
		return $this->mk_orb("op_meta", array("id" => $id, "op_id" => $op_id));
	}

	////
	// !saves the current state of the loaded form output
	function save_output($id)
	{
		$tp = serialize($this->output);
		$this->db_query("UPDATE form_output SET op = '$tp' WHERE id = $id");
		$this->update_object($id);
		$this->_log("form","Muutis formi $this->name outputi stiili $name");
	}

	////
	// !generates the grid used in changig the output
	function gen_output_grid($arr)
	{
		extract($arr);
		$this->init($id, "output_grid.tpl", "<a href='".$this->mk_orb("change", array("id" => $id))."'>Muuda formi</a> / <a href='".$this->mk_orb("op_list", array("id" => $id))."'>V&auml;ljundite nimekiri</a> / Muuda v&auml;ljundit");

		$this->output = array();
		$this->load_output($op_id);

		// put all the elements in this form in an array, so it will be faster to use
		$elarr = array();
		for ($row = 0; $row < $this->arr[rows]; $row++)
			for ($col = 0; $col < $this->arr[cols]; $col++)
				$this->arr[contents][$row][$col] ->get_els(&$elarr);		// this function returns only the elements, for which can_view == 1

		// put all styles in this form in an array so they will be faster to use
		$styles = array();

/*			echo "<table border=1>";
		for ($r=0; $r < $this->output[rows]; $r++)
		{
			echo "<tr>";
			for ($c=0; $c < $this->output[cols]; $c++)
				echo "<td>(", $this->output[map][$r][$c][row], ",",$this->output[map][$r][$c][col],")</td>";
			echo "</tr>";
		}
		echo "</table>";*/

		$style = new style;
		$select = $style->get_select(0,ST_CELL);

		$this->vars(array("DC" => "", "FIRST_C" => ""));
		for ($a=0; $a < $this->output[cols]; $a++)
		{
			$fc = "";
			if ($a == 0)
			{
				$this->vars(array("add_col" => $this->mk_orb("op_add_col", array("id" => $this->id, "op_id" => $op_id, "after" => -1))));
				$fc = $this->parse("FIRST_C");
			}

			$this->vars(array("add_col" => $this->mk_orb("op_add_col", array("id" => $this->id, "op_id" => $op_id, "after" => $a)),
												"del_col" => $this->mk_orb("op_del_col", array("id" => $this->id, "op_id" => $op_id, "col" => $a))));
			$this->vars(array("output_col" => $a,"FIRST_C" => $fc));
			$this->parse("DC");
		}

		for ($row = 0; $row < $this->output[rows]; $row++)
		{
			$this->vars(array("COL" => ""));
			for ($col = 0; $col < $this->output[cols]; $col++)
			{
				if (!($arr = $this->get_spans($row, $col, $this->output[map], $this->output[rows], $this->output[cols])))
					continue;
				
				$this->vars(array("colspan" => $arr[colspan], "rowspan" => $arr[rowspan],"num_els_plus3" => $this->output[$arr[r_row]][$arr[r_col]][el_count]+4,"col" => $arr[r_col], "row" => $arr[r_row]));

				$elmnt="";
				for ($i=0; $i < $this->output[$arr[r_row]][$arr[r_col]][el_count]+1; $i++)
				{
					$elsel = "<option VALUE='0'>";
					reset($elarr);
					while (list($k, $v) = each($elarr))
					{
						$elsel.="<option VALUE='".$v->get_id()."' ".($this->output[$arr[r_row]][$arr[r_col]][els][$i] == $v->get_id() ? " SELECTED " : "").">".($v->get_el_name());
					}
					$elmnt .= "<tr><td align=right class=plain>Element:</td><td><select class='small_button'	name='elsel_".$arr[r_row]."_".$arr[r_col]."_".$i."'>$elsel</select></td></tr>";		// ok, we shouldn't probably do this, but what the heck
				}
				$this->vars(array("cell_id" => ($arr[r_row]."_".$arr[r_col]), "ELEMENT" => $elmnt, 
													"exp_left"	=> $this->mk_orb("op_exp_left", array("id" => $this->id, "op_id" => $op_id, "col" => $arr[r_col], "row" => $arr[r_row])),
													"exp_right"	=> $this->mk_orb("op_exp_right", array("id" => $this->id, "op_id" => $op_id, "col" => $arr[r_col], "row" => $arr[r_row])),
													"exp_up"	=> $this->mk_orb("op_exp_up", array("id" => $this->id, "op_id" => $op_id, "col" => $arr[r_col], "row" => $arr[r_row])),
													"exp_down"	=> $this->mk_orb("op_exp_down", array("id" => $this->id, "op_id" => $op_id, "col" => $arr[r_col], "row" => $arr[r_row])),
													"stylesel" => $this->picker($this->output[$arr[r_row]][$arr[r_col]][style],$select)));
				$this->parse("COL");
			}
			$fi = "";
			if ($row==0)
			{
				$this->vars(array("add_row" => $this->mk_orb("op_add_row", array("id" => $this->id, "op_id" => $op_id, "after" => -1))));
				$fi = $this->parse("FIRST_R");
			}
			$this->vars(array("add_row" => $this->mk_orb("op_add_row", array("id" => $this->id, "op_id" => $op_id, "after" => $row)),
												"del_row" => $this->mk_orb("op_del_row", array("id" => $this->id, "op_id" => $op_id, "row" => $row))));
			$this->vars(array("FIRST_R" => $fi, "output_row" => $row));
			$this->parse("LINE");
		}

		$this->vars(array("def_style_value"			=> "", 
											"def_style_selected"	=> "",
											"def_style_text"			=> ""));
		$this->parse("STYLE_LINE");
		for ($i=0; $i < $style_count; $i++)
		{
			$this->vars(array("def_style_value"			=>	$styles[$i][id], 
												"def_style_selected"	=> ($this->output[def_style] == $styles[$i][id] ? " SELECTED " : "" ),
												"def_style_text"			=> $styles[$i][name]));
			$this->parse("STYLE_LINE");
		}

		$this->vars(array("form_bgcolor"				=> $this->output[bgcolor],
											"form_border"					=> $this->output[border],
											"form_cellpadding"		=> $this->output[cellpadding],
											"form_cellspacing"		=> $this->output[cellspacing],
											"form_height"					=> $this->output[height],
											"form_width"					=> $this->output[width],
											"form_hspace"					=> $this->output[hspace],
											"form_vspace"					=> $this->output[vspace],
											"reforb"							=> $this->mk_reforb("submit_op_grid", array("id" => $id, "op_id" => $op_id)),
											"id"									=> $op_id));
		return $this->do_menu_return();
	}

	////
	// !adds a $count columns to output $op_id of form $id after $after
	function op_add_col($arr)
	{
		extract($arr);
		$this->load($id);
		$this->load_output($op_id);

		for ($row = 0; $row < $this->output[rows]; $row++)
			for ($i=$this->output[cols]; $i > $after; $i--)
				$this->output[$row][$i+1] = $this->output[$row][$i];

		for ($row = 0; $row < $this->output[rows]; $row++)
			$this->output[$row][$after+1] = "";

		$this->output[cols]++;

		$nm = array();
		for ($row =0; $row < $this->output[rows]; $row++)
			for ($col=0; $col <= $after; $col++)
				$nm[$row][$col] = $this->output[map][$row][$col];		// copy the left part of the map

		$change = array();
		for ($row = 0; $row < $this->output[rows]; $row++)
			for ($col=$after+1; $col < ($this->output[cols]-1); $col++)
			{
				if ($this->output[map][$row][$col][col] > $after)	
				{
					$nm[$row][$col+1][col] = $this->output[map][$row][$col][col]+1;
					$nm[$row][$col+1][row] = $this->output[map][$row][$col][row];
					$change[] = array("from" => $this->output[map][$row][$col], "to" => $nm[$row][$col+1]);
				}
				else
					$nm[$row][$col+1] = $this->output[map][$row][$col];
			}

		reset($change);
		while (list(,$v) = each($change))
			for ($row=0; $row < $this->output[rows]; $row++)
				for ($col=0; $col <= $after; $col++)
					if ($this->output[map][$row][$col] == $v[from])
						$nm[$row][$col] = $v[to];

		for ($row = 0; $row < $this->output[rows]; $row++)
		{
			if ($this->output[map][$row][$after] == $this->output[map][$row][$after+1])
				$nm[$row][$after+1] = $nm[$row][$after];
			else
				$nm[$row][$after+1] = array("row" => $row, "col" => $after+1);
		}

		$this->output[map] = $nm;

		$this->save_output($op_id);
		$orb = $this->mk_orb("change_op", array("id" => $this->id, "op_id" => $op_id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !adds a row to output $op_id of form $id
	function op_add_row($arr)
	{
		extract($arr);
		$this->load($id);
		$this->load_output($op_id);

		for ($row=$this->output[rows]; $row > $after; $row--)
			for ($col=0; $col < $this->output[cols]; $col++)
				$this->output[$row+1][$col] = $this->output[$row][$col];

		for ($col = 0; $col < $this->output[cols]; $col++)
			$this->output[$after+1][$col] = "";

		$this->output[rows]++;

		$nm = array();
		for ($row =0; $row <= $after; $row++)
			for ($col=0; $col < $this->output[cols]; $col++)
				$nm[$row][$col] = $this->output[map][$row][$col];		// copy the upper part of the map

		$change = array();
		for ($row = $after+1; $row < ($this->output[rows]-1); $row++)
			for ($col=0; $col < $this->output[cols]; $col++)
			{
				if ($this->output[map][$row][$col][row] > $after)	
				{
					$nm[$row+1][$col][col] = $this->output[map][$row][$col][col];
					$nm[$row+1][$col][row] = $this->output[map][$row][$col][row]+1;
					$change[] = array("from" => $this->output[map][$row][$col], "to" => $nm[$row+1][$col]);
				}
				else
					$nm[$row+1][$col] = $this->output[map][$row][$col];
			}

		reset($change);
		while (list(,$v) = each($change))
			for ($row=0; $row <= $after; $row++)
				for ($col=0; $col < $this->output[cols]; $col++)
					if ($this->output[map][$row][$col] == $v[from])
						$nm[$row][$col] = $v[to];

		for ($col = 0; $col < $this->output[cols]; $col++)
		{
			if ( $this->output[map][$after][$col] == $this->output[map][$after+1][$col])
				$nm[$after+1][$col] = $nm[$after][$col];
			else
				$nm[$after+1][$col] = array("row" => $after+1, "col" => $col);
		}

		$this->output[map] = $nm;
		$this->save_output($this->output_id);
		$orb = $this->mk_orb("change_op", array("id" => $this->id, "op_id" => $op_id));
		header("Location: $orb");
		return $orb;
	}
	
	////
	// !deletes the column $col of form $id 's output $op_id
	function op_del_col($arr)
	{
		extract($arr);
		$this->load($id);
		$this->load_output($op_id);

		for ($row = 0; $row < $this->output[rows]; $row++)
			for ($i=$col; $i < ($this->output[cols]-1); $i++)
				$this->output[$row][$i] = $this->output[$row][$i+1];

		for ($row = 0; $row < $this->output[rows]; $row++)
			$this->output[$row][$this->output[cols]-1] = "";

		$d_col = $col;

		$nm = array();
		for ($row =0; $row < $this->output[rows]; $row++)
			for ($col=0; $col < $d_col; $col++)
				$nm[$row][$col] = $this->output[map][$row][$col];	// copy the left part of the map

		$changes = array();
		for ($row =0 ; $row < $this->output[rows]; $row++)
			for ($col = $d_col+1; $col < $this->output[cols]; $col++)
			{
				if ($this->output[map][$row][$col][col] > $d_col)
				{
					$nm[$row][$col-1] = array("row" => $this->output[map][$row][$col][row], "col" => $this->output[map][$row][$col][col]-1);
					$changes[] = array("from" => $this->output[map][$row][$col], 
														 "to" => array("row" => $this->output[map][$row][$col][row], "col" => $this->output[map][$row][$col][col]-1));
				}
				else
					$nm[$row][$col-1] = $this->output[map][$row][$col];
				
			}
		$this->output[map] = $nm;
		
		reset($changes);
		while (list(,$v) = each($changes))
			for ($row=0; $row < $this->output[rows]; $row++)
				for ($col=0; $col < $d_col; $col++)
					if ($this->output[map][$row][$col] == $v[from])
						$this->output[map][$row][$col] = $v[to];

		$this->output[cols]--;
		$this->save_output($this->output_id);
		$orb = $this->mk_orb("change_op", array("id" => $this->id, "op_id" => $op_id));
		header("Location: $orb");
		return $orb;
	}
	
	////
	// !deletes row $row of outpit $op_id of form $id
	function op_del_row($arr)
	{
		extract($arr);
		$this->load($id);
		$this->load_output($op_id);

		$row_d = $row;
		for ($row=$row_d; $row < ($this->output[rows]-1); $row++)
			for ($col=0; $col < $this->output[cols]; $col++)
				$this->output[$row][$col] = $this->output[$row+1][$col];

		for ($col = 0; $col < $this->output[cols]; $col++)
			$this->output[$this->output[rows]-1][$col] = "";

		$d_row = $row;

		$nm = array();
		for ($row =0; $row < $d_row; $row++)
			for ($col=0; $col < $this->output[cols]; $col++)
				$nm[$row][$col] = $this->output[map][$row][$col];	// copy the upper part of the map

		$changes = array();
		for ($row =$d_row+1 ; $row < $this->output[rows]; $row++)
			for ($col = 0; $col < $this->output[cols]; $col++)
			{
				if ($this->output[map][$row][$col][row] > $d_row)
				{
					$nm[$row-1][$col] = array("row" => $this->output[map][$row][$col][row]-1, "col" => $this->output[map][$row][$col][col]);
					$changes[] = array("from" => $this->output[map][$row][$col], 
														 "to" => array("row" => $this->output[map][$row][$col][row]-1, "col" => $this->output[map][$row][$col][col]));
				}
				else
					$nm[$row-1][$col] = $this->output[map][$row][$col];
				
			}
		$this->output[map] = $nm;
		
		reset($changes);
		while (list(,$v) = each($changes))
			for ($row=0; $row < $d_row; $row++)
				for ($col=0; $col < $this->output[cols]; $col++)
					if ($this->output[map][$row][$col] == $v[from])
						$this->output[map][$row][$col] = $v[to];

		$this->output[rows]--;
		$this->save_output($this->output_id);
		$orb = $this->mk_orb("change_op", array("id" => $this->id, "op_id" => $op_id));
		header("Location: $orb");
		return $orb;
	}

	////
	// !saves the form's ($id) output grid ($op_id)
	function save_output_grid($arr)
	{
		$this->dequote(&$arr);
		extract($arr);

		$this->load_output($op_id);
		for ($row=0; $row < $this->output[rows]; $row++)
		{
			for ($col=0; $col < $this->output[cols]; $col++)
			{
				$var = "stylesel_".$row."_".$col;
				$this->output[$row][$col][style] = $$var;
				for ($i=0; $i < $this->output[$row][$col][el_count]+1; $i++)
				{
					$var = "elsel_".$row."_".$col."_".$i;
					$this->output[$row][$col][els][$i] = $$var;
					$ls = $$var;
				}
				if ($ls != 0)
					$this->output[$row][$col][el_count]++;
				else
				if ($this->output[$row][$col][els][$this->output[$row][$col][el_count]-1] == 0)
					$this->output[$row][$col][el_count]--;

				if ($this->output[$row][$col][el_count] < 0)
					$this->output[$row][$col][el_count] = 0;
			}
		}
		
		$this->save_output($op_id);
		return $this->mk_orb("change_op", array("id" => $id, "op_id" => $op_id));
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
	}
}
?>
