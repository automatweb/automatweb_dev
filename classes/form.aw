<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form.aw,v 2.4 2001/05/21 05:24:55 kristo Exp $
// form.aw - Class for creating forms
lc_load("form");
global $orb_defs;
$orb_defs["form"] = "xml";

	classload("form_base","form_element","form_entry_element","form_search_element","form_cell","images","style","acl");

	// see on kasutajate registreerimiseks. et pannaxe kirja et mis formid tyyp on t2itnud.
	session_register("session_filled_forms");

	define(FORM_ENTRY,1);
	define(FORM_SEARCH,2);
	define(FORM_RATING,3);


	class form extends form_base
	{
		function form()
		{
			$this->tpl_init("forms");
			$this->db_init();
			if (func_num_args() == 1)
			{
				$this->use_form(func_get_arg(0));
			}
			else
			{
				$this->use_form(-1);
			};
			$this->sub_merge = 1;

			$this->typearr = array(FORM_ENTRY => FG_ENTRY_FORM, FORM_SEARCH => FG_SEARCH_FORM, FORM_RATING => FG_RATING_FORM);
		}

		// määrab ära vormi, mida kasutatakse
		function use_form()
		{
			$arg = func_get_arg(0);
			// kui etteantud parameeter on intege 
			if (gettype($arg) == "array")
			{
				$this->flist = $arg;
				$this->id = $arg[0]; // Set it to first element. Just in case
			}
			else
			{
				$this->flist = array();
				$this->id = $arg;
			};
		}
			

		////
		// !Generates form admin interface
		// $arr[id] - form id, required
		function gen_grid($arr)
		{
			extract($arr);
			$this->init($id,"grid.tpl","Muuda formi");

/*			echo "<table border=1>";
			for ($r=0; $r < $this->arr[rows]; $r++)
			{
				echo "<tr>";
				for ($c=0; $c < $this->arr[cols]; $c++)
					echo "<td>(", $this->arr[map][$r][$c][row], ",",$this->arr[map][$r][$c][col],")</td>";
				echo "</tr>";
			}
			echo "</table>";
			flush();*/

			for ($a=0; $a < $this->arr[cols]; $a++)
			{
				$fi = "";
				if ($a == 0)
				{
					$this->vars(array("add_col" => $this->mk_orb("add_col", array("id" => $this->id, "after" => -1, "count" => 1))));
					$fi = $this->parse("FIRST_C");
				}

				$fl = true;
				for ($row = 0; $row < $this->arr[rows]; $row++)
				{
					$els = $this->arr[contents][$row][$a]->get_elements();
					reset($els);
					while(list(,$v) = each($els))
						if (!$this->can("delete",$v[id]))
							$fl = false;
				}
				$this->vars(array("form_col" => $a,
													"del_col"		=> $this->mk_orb("del_col",array("id" => $this->id, "col" => $a))));
				$cd = "";
				if ($fl == true)
					$cd = $this->parse("DELETE_COL");

				$this->vars(array("FIRST_C" => $fi, "DELETE_COL" => $cd,
													"add_col"	=> $this->mk_orb("add_col", array("id" => $this->id, "count" => 1, "after" => $a))));
				$this->parse("DC");
			}

			for ($i=0; $i < $this->arr[rows]; $i++)
			{
				$cols="";
				$fl = true;
				for ($a=0; $a < $this->arr[cols]; $a++)
				{
					if (!($arr = $this->get_spans($i, $a)))
						continue;
					
					$els = $this->arr[contents][$arr[r_row]][$arr[r_col]]->get_elements();

					reset($els);
					$el = "";
					$el_cnt=0;
					while (list(, $v) = each($els))
					{
						// the element's can_view property is ignored here
						$this->vars(array("form_cell_text"	=> $v[text], 
															"form_cell_order"	=> $v[order],
															"element_id"			=> $v[id],
															"el_name"					=> ($v[name] == "" ? "&nbsp;" : $v[name]),
															"el_type"					=> ($v[type] == "" ? "&nbsp;" : $v[type])));
						$el.=$this->parse("ELEMENT");
						if (!$this->can("delete",$v[id]))
							$fl = false;
						$el_cnt++;
					}

					$this->vars(array("ELEMENT" => $el, "cell_col" => $a, "cell_row" => $i, "ELEMENT_NOEDIT" => "","num_els_plus3"=>($el_cnt+5),
														"exp_left"	=> $this->mk_orb("exp_cell_left", array("id" => $this->id, "col" => $a, "row" => $i)),
														"exp_up"		=> $this->mk_orb("exp_cell_up", array("id" => $this->id, "col" => $a, "row" => $i)),
														"exp_down"	=> $this->mk_orb("exp_cell_down", array("id" => $this->id, "col" => $a, "row" => $i)),
														"exp_right"	=> $this->mk_orb("exp_cell_right", array("id" => $this->id, "col" => $a, "row" => $i)),
														"split_ver"	=> $this->mk_orb("split_cell_ver", array("id" => $this->id, "col" => $a, "row" => $i)),
														"split_hor"	=> $this->mk_orb("split_cell_hor", array("id" => $this->id, "col" => $a, "row" => $i)),
														"admin_cell"	=> $this->mk_orb("admin_cell", array("id" => $this->id, "col" => $arr[r_col], "row" => $arr[r_row])),
														"add_element" => $this->mk_orb("add_element", array("id" => $this->id, "col" => $arr[r_col], "row" => $arr[r_row]))));
					$sh = ""; $sv = "";
					if ($arr[rowspan] > 1)
						$sh = $this->parse("SPLIT_HORIZONTAL");
					if ($arr[colspan] > 1)
						$sv = $this->parse("SPLIT_VERTICAL");

					$eu = "";
					if ($i != 0)
						$eu = $this->parse("EXP_UP");
					$el = "";
					if ($a != 0)
						$el = $this->parse("EXP_LEFT");
					$er = "";
					if (($a+$arr[colspan]) != $this->arr[cols])
						$er = $this->parse("EXP_RIGHT");
					$ed = "";
					if (($i+$arr[rowspan]) != $this->arr[rows])
						$ed = $this->parse("EXP_DOWN");

					$this->vars(array("SPLIT_HORIZONTAL" => $sh, "SPLIT_VERTICAL" => $sv, "EXP_UP" => $eu, "EXP_LEFT" => $el, "EXP_RIGHT" => $er,"EXP_DOWN" => $ed));
					$cols.=$this->parse("COL");
				}
				$fi = "";
				if ($i==0)
				{
					$this->vars(array("add_row" => $this->mk_orb("add_row", array("id" => $this->id, "after" => -1, "count" => 1))));
					$fi = $this->parse("FIRST_R");
				}
				$cd = "";
				if ($fl)
				{
					$this->vars(array("del_row" => $this->mk_orb("del_row", array("id" => $this->id, "row" => $i))));
					$cd = $this->parse("DELETE_ROW");
				}
				$this->vars(array("COL" => $cols, "FIRST_R" => $fi, "DELETE_ROW" => $cd,
													"add_row" => $this->mk_orb("add_row", array("id" => $this->id, "after" => $i, "count" => 1))));
				$l.=$this->parse("LINE");
			}

			$this->vars(array("LINE"				=> $l,
												"addr_reforb"	=> $this->mk_reforb("add_row", array("id" => $this->id, "after" => $this->arr[rows]-1)),
												"addc_reforb"	=> $this->mk_reforb("add_col", array("id" => $this->id, "after" => $this->arr[cols]-1)),
												"reforb"			=> $this->mk_reforb("submit_grid", array("id" => $this->id))));
			return $this->do_menu_return();
		}

		////
		// !Shows all form elements and lets user pick their style
		function gen_all_elements($arr)
		{
			extract($arr);
			$this->init($id, "all_elements.tpl", "K&otilde;ik elemendid");

			$this->vars(array("form_id" => $this->id));
			for ($i=0; $i < $this->arr[rows]; $i++)
			{
				$cols="";
				for ($a=0; $a < $this->arr[cols]; $a++)
				{
					$this->vars(array("ELEMENT"	=> "", "STYLEITEMS" => "", "SOME_ELEMENTS" => ""));	
					if (!($arr = $this->get_spans($i, $a)))
						continue;
						
					$els = $this->arr[contents][$arr[r_row]][$arr[r_col]]->get_elements();
					reset($els);
					$el = "";
					while (list(, $v) = each($els))
					{
						// the element's can_view property is ignored here
						$this->vars(array("el_text"	=> ($v[text] == "" ? "&nbsp;" : $v[text]),
															"el_name"	=> ($v[name] == "" ? "&nbsp;" : $v[name]),
															"el_type"	=> ($v[type] == "" ? "&nbsp;" : $v[type]),
															"form_cell_order"	=> $v[order],
															"element_id"			=> $v[id]));
						$el.=$this->parse("ELEMENT");
					}

					$sti = ""; $flag = false;
					for ($st=0; $st < $style_count; $st++)
					{
						$sel = ($styles[$st][id] == $this->arr[contents][$i][$a]->get_style());
						if ($sel)
							$flag=true;
						$this->vars(array("style_id"				=> $styles[$st][id], 
															"style_selected"	=> ($sel == true ? " SELECTED " : ""),
															"style_name"			=> $styles[$st][name]));
						$sti.=$this->parse("STYLEITEMS");
					}
					if (!$flag && $this->arr[contents][$i][$a]->get_style() != 0)	
					{
						// if we didn't find the style but one was selected, then it's a temp style!
						$this->vars(array("style_id"				=> $this->arr[contents][$i][$a]->get_style(), 
															"style_selected"	=> " SELECTED ", 
															"style_name"			=> ""));
					}
					else
						$this->vars(array("style_id" => "", "style_selected" => "", "style_name" => ""));

					$sti = $this->parse("STYLEITEMS").$sti;

					$this->vars(array("ELEMENT"				=> $el, 
														"STYLEITEMS"		=> $sti,
														"col"						=> $arr[r_col], 
														"row"						=> $arr[r_row]));	

					if ($el == "")
						$se = "<img src='/images/transa.gif' height=1 width=1 border=0>";
					else
						$se = $this->parse("SOME_ELEMENTS");

					$this->vars(array("SOME_ELEMENTS" => $se));

					$cols.=$this->parse("COL");
				}
				$this->vars(array("COL" => $cols));
				$this->parse("LINE");
			}
			return $this->do_menu_return();
		}

		////
		// !saves the table properties of the form
		function save_settings($arr)
		{
			$this->dequote(&$arr);
			extract($arr);
			$this->load($id);

			$this->arr[bgcolor] = $bgcolor;
			$this->arr[border] = $border;
			$this->arr[cellpadding]	= $cellpadding;
			$this->arr[cellspacing] = $cellspacing;
			$this->arr[height] = $height;
			$this->arr[width] = $width;
			$this->arr[height] = $height;
			$this->arr[hspace] = $hspace;
			$this->arr[vspace] = $vspace;
			$this->arr[def_style] = $def_style;
			$this->arr[submit_text] = $submit_text;
			$this->arr[after_submit] = $after_submit;
			$this->arr[after_submit_text] = $after_submit_text;
			$this->arr[after_submit_link] = $after_submit_link;
			$this->arr[ff_folder] = $ff_folder;
			$this->save();
			return $this->mk_orb("table_settings", array("id" => $id));
		}

		//// 
		// !saves the changes the user has made in the form generated by gen_grid
		function save_grid($arr)
		{
			$this->dequote(&$arr);
			extract($arr);
			$this->load($id);

			for ($i=0; $i < $this->arr[rows]; $i++)
			{
				for ($a=0; $a < $this->arr[cols]; $a++)
				{
					$this->arr[contents][$i][$a]->save_short(&$this);
				}
			}
			$this->save();

/*			global $HTTP_POST_VARS;
			$cdelete = array();
			$rdelete = array();
			reset($HTTP_POST_VARS);
			while (list($k,$v) = each($HTTP_POST_VARS))
			{
				if (substr($k,0,3) == 'dc_' && $v==1)
					$cdelete[substr($k,3)] = substr($k,3);
				else
				if (substr($k,0,3) == 'dr_' && $v==1)
					$rdelete[substr($k,3)] = substr($k,3);
			}

			// kustutame tagant-ettepoole, niiet numbrid ei muutuks
			krsort($cdelete,SORT_NUMERIC);
			krsort($rdelete,SORT_NUMERIC);

			reset($cdelete);
			while (list($k,$v) = each($cdelete))
			{
				$this->cells_loaded = false;
				$this->delete_column($v);
			}

			reset($rdelete);
			while (list($k,$v) = each($rdelete))
			{
				$this->cells_loaded = false;
				$this->delete_row($v);
			}*/
			return $this->mk_orb("change",array("id" => $this->id));
		}

		////
		// !Adds $count columns after column $after in form $id
		function add_col($arr)
		{
			extract($arr);
			$this->load($id);

			while ($count-- > 0)
			{
				$this->arr[cols]++;

				$nm = array();
				for ($row =0; $row < $this->arr[rows]; $row++)
					for ($col=0; $col <= $after; $col++)
						$nm[$row][$col] = $this->arr[map][$row][$col];		// copy the left part of the map

				$change = array();
				for ($row = 0; $row < $this->arr[rows]; $row++)
					for ($col=$after+1; $col < ($this->arr[cols]-1); $col++)
					{
						if ($this->arr[map][$row][$col][col] > $after)	
						{
							$nm[$row][$col+1][col] = $this->arr[map][$row][$col][col]+1;
							$nm[$row][$col+1][row] = $this->arr[map][$row][$col][row];
							$change[] = array("from" => $this->arr[map][$row][$col], "to" => $nm[$row][$col+1]);
						}
						else
							$nm[$row][$col+1] = $this->arr[map][$row][$col];
					}

				reset($change);
				while (list(,$v) = each($change))
					for ($row=0; $row < $this->arr[rows]; $row++)
						for ($col=0; $col <= $after; $col++)
							if ($this->arr[map][$row][$col] == $v[from])
								$nm[$row][$col] = $v[to];

				for ($row = 0; $row < $this->arr[rows]; $row++)
				{
					if ($this->arr[map][$row][$after] == $this->arr[map][$row][$after+1])
						$nm[$row][$after+1] = $nm[$row][$after];
					else
						$nm[$row][$after+1] = array("row" => $row, "col" => $after+1);
				}

				$this->arr[map] = $nm;

				// move necessary elements to the right
				for ($i = $this->arr[cols]; $i > ($after+1); $i--)
				{
					for ($a = 0; $a < $this->arr[rows]; $a++)
					{
						$this->arr[elements][$a][$i] = $this->arr[elements][$a][$i-1];
					}
				}
				// zero out all elemnts on the newly added column
				for ($a = 0; $a < $this->arr[rows]; $a++)
				{
					$this->arr[elements][$a][$after+1] = array();
				}
			}
			$this->save();
			$orb = $this->mk_orb("change", array("id" => $this->id));
			// since this function can be called both from reforb and orb
			// we make sure we return to the right place afterwards.
			header("Location: $orb");
			return $orb;
		}

		////
		// !Adds rows to the form
		// parameters:
		// id - form id
		// after - row number after which rows are added
		// count - number of rows to add
		function add_row($arr)
		{
			extract($arr);
			$this->load($id);

			while ($count-- > 0)
			{
				$this->arr[rows]++;

				$nm = array();
				for ($row =0; $row <= $after; $row++)
					for ($col=0; $col < $this->arr[cols]; $col++)
						$nm[$row][$col] = $this->arr[map][$row][$col];		// copy the upper part of the map

				$change = array();
				for ($row = $after+1; $row < ($this->arr[rows]-1); $row++)
					for ($col=0; $col < $this->arr[cols]; $col++)
					{
						if ($this->arr[map][$row][$col][row] > $after)	
						{
							$nm[$row+1][$col][col] = $this->arr[map][$row][$col][col];
							$nm[$row+1][$col][row] = $this->arr[map][$row][$col][row]+1;
							$change[] = array("from" => $this->arr[map][$row][$col], "to" => $nm[$row+1][$col]);
						}
						else
							$nm[$row+1][$col] = $this->arr[map][$row][$col];
					}

				reset($change);
				while (list(,$v) = each($change))
					for ($row=0; $row <= $after; $row++)
						for ($col=0; $col < $this->arr[cols]; $col++)
							if ($this->arr[map][$row][$col] == $v[from])
								$nm[$row][$col] = $v[to];

				for ($col = 0; $col < $this->arr[cols]; $col++)
				{
					if ( $this->arr[map][$after][$col] == $this->arr[map][$after+1][$col])
						$nm[$after+1][$col] = $nm[$after][$col];
					else
						$nm[$after+1][$col] = array("row" => $after+1, "col" => $col);
				}

				$this->arr[map] = $nm;

				// now we must also move all elements in $this->arr[elements]
				// so that when the form is loaded they get put in the correct
				// places.
				for ($i=$this->arr[rows]; $i > $after; $i--)
				{
					for ($a = 0; $a < $this->arr[cols]; $a++)
					{
						$this->arr[elements][$i][$a] = $this->arr[elements][$i-1][$a];
					}
				}
				// zero out all elements on the newly inserted row
				for ($a = 0; $a < $this->arr[cols]; $a++)
				{
					$this->arr[elements][$after+1][$a] = array();
				}
			}
			$this->save();
			$orb = $this->mk_orb("change", array("id" => $this->id));
			header("Location: $orb");
			return $orb;
		}
		
		////
		// !Deletes column $col of form $id
		function delete_column($arr)
		{
			extract($arr);
			$this->load($id);

			for ($i=0; $i < $this->arr[rows]; $i++)
			{
				// we don't delete the element from the database, we jsut delete it
				// from this form. 
				$this->arr[elements][$i][$col] = array();
				$this->arr[contents][$i][$this->arr[cols]-1] = array();
			}

			$d_col = $col;

			$nm = array();
			for ($row =0; $row < $this->arr[rows]; $row++)
				for ($col=0; $col < $d_col; $col++)
					$nm[$row][$col] = $this->arr[map][$row][$col];	// copy the left part of the map

			$changes = array();
			for ($row =0 ; $row < $this->arr[rows]; $row++)
				for ($col = $d_col+1; $col < $this->arr[cols]; $col++)
				{
					if ($this->arr[map][$row][$col][col] > $d_col)
					{
						$nm[$row][$col-1] = array("row" => $this->arr[map][$row][$col][row], "col" => $this->arr[map][$row][$col][col]-1);
						$changes[] = array("from" => $this->arr[map][$row][$col], 
															 "to" => array("row" => $this->arr[map][$row][$col][row], "col" => $this->arr[map][$row][$col][col]-1));
					}
					else
						$nm[$row][$col-1] = $this->arr[map][$row][$col];
					
				}
			$this->arr[map] = $nm;
			
			reset($changes);
			while (list(,$v) = each($changes))
				for ($row=0; $row < $this->arr[rows]; $row++)
					for ($col=0; $col < $d_col; $col++)
						if ($this->arr[map][$row][$col] == $v[from])
							$this->arr[map][$row][$col] = $v[to];

			// we must also shift all elements that are to the right of the deleted
			// column 1 position to the left
			for ($i=$d_col; $i < $this->arr[cols]; $i++)
			{
				for ($a=0; $a < $this->arr[rows]; $a++)
				{
					$this->arr[elements][$a][$i] = $this->arr[elements][$a][$i+1];
				}
			}

			$this->arr[cols]--;
			$this->save();
			$orb = $this->mk_orb("change" , array("id" => $this->id));
			header("Location: $orb");
			return $orb;
		}
		
		////
		// !Deletes row $row from form $id
		function delete_row($arr)
		{
			extract($arr);
			$this->load($id);

			for ($i=0; $i < $this->arr[cols]; $i++)
			{
				$this->arr[elements][$row][$i] = array();
				$this->arr[contents][$this->arr[rows]-1][$i] = "";
			}

			$d_row = $row;

			$nm = array();
			for ($row =0; $row < $d_row; $row++)
				for ($col=0; $col < $this->arr[cols]; $col++)
					$nm[$row][$col] = $this->arr[map][$row][$col];	// copy the upper part of the map

			$changes = array();
			for ($row =$d_row+1 ; $row < $this->arr[rows]; $row++)
				for ($col = 0; $col < $this->arr[cols]; $col++)
				{
					if ($this->arr[map][$row][$col][row] > $d_row)
					{
						$nm[$row-1][$col] = array("row" => $this->arr[map][$row][$col][row]-1, "col" => $this->arr[map][$row][$col][col]);
						$changes[] = array("from" => $this->arr[map][$row][$col], 
															 "to" => array("row" => $this->arr[map][$row][$col][row]-1, "col" => $this->arr[map][$row][$col][col]));
					}
					else
						$nm[$row-1][$col] = $this->arr[map][$row][$col];
					
				}
			$this->arr[map] = $nm;
			
			reset($changes);
			while (list(,$v) = each($changes))
				for ($row=0; $row < $d_row; $row++)
					for ($col=0; $col < $this->arr[cols]; $col++)
						if ($this->arr[map][$row][$col] == $v[from])
							$this->arr[map][$row][$col] = $v[to];

			// we must move all elements below the deleted row up by one
			for ($i = $d_row; $i < $this->arr[rows]; $i++)
			{
				for ($a=0; $a < $this->arr[cols]; $a++)
				{
					$this->arr[elements][$i][$a] = $this->arr[elements][$i+1][$a];
				}
			}

			$this->arr[rows]--;
			
			$this->save();
			$orb = $this->mk_orb("change", array("id" => $this->id));
			header("Location: $orb");
			return $orb;
		}

		////
		// !Generates the form used in modifying the table settings
		function gen_settings($arr)
		{
			extract($arr);
			$this->init($id,"settings.tpl", "Muuda settinguid");

			classload("style");
			$t = new style;
			$o = new db_objects;
			$this->vars(array("form_bgcolor"				=> $this->arr[bgcolor],
												"form_border"					=> $this->arr[border],
												"form_cellpadding"		=> $this->arr[cellpadding],
												"form_cellspacing"		=> $this->arr[cellspacing],
												"form_height"					=> $this->arr[height],
												"form_width"					=> $this->arr[width],
												"form_hspace"					=> $this->arr[hspace],
												"form_vspace"					=> $this->arr[vspace],
												"def_style"						=> $this->picker($this->arr[def_style],$t->get_select(0,ST_CELL)),
												"submit_text"					=> $this->arr[submit_text],
												"after_submit_text"		=> $this->arr[after_submit_text],
												"after_submit_link"		=> $this->arr[after_submit_link],
												"as_1"								=> ($this->arr[after_submit] == 1 ? "CHECKED" : ""),
												"as_2"								=> ($this->arr[after_submit] == 2 ? "CHECKED" : ""),
												"as_3"								=> ($this->arr[after_submit] == 3 ? "CHECKED" : ""),
												"ff_folder"						=> $this->picker($this->arr[ff_folder], $o->get_list())));
			$ns = "";
			if ($this->type != 2)
				$ns = $this->parse("NOSEARCH");

			$this->vars(array("NOSEARCH" => $ns,
												"reforb"	=> $this->mk_reforb("save_settings", array("id" => $this->id))));
			return $this->do_menu_return();				
		}

		////
		// !shows form $id
		// optional parameters: 
		//	$entry_id - the entry to show
		//	$reforb - replaces {VAR:reforb}
		//  $form_action = <form action='$form_action'
		//  $extraids - array of parameters to pass along with the form
		function gen_preview($arr)
		{
			extract($arr);
			$this->load($id);

			if ($form_action == "")
			{
				$form_action = "/automatweb/reforb.".$GLOBALS["ext"];
			}
			$form_action = $GLOBALS["baseurl"].$form_action;

			if ($reforb == "")
			{
				$reforb = $this->mk_reforb("process_entry", array("id" => $this->id));
			}
			if ($entry_id)
			{
				$this->load_entry($entry_id);
			}

			$this->read_template("show.tpl",1);
			$images = new db_images;

			$c="";
			for ($i=0; $i < $this->arr[rows]; $i++)
			{
				$html="";
				for ($a=0; $a < $this->arr[cols]; $a++)
				{
					if (($arr = $this->get_spans($i, $a)))
					{
						$html.=$this->arr[contents][$arr[r_row]][$arr[r_col]]->gen_user_html_not($this->arr[def_style], &$images, $arr[colspan], $arr[rowspan]);
					}
				}
				$this->vars(array("COL" => $html));
				$c.=$this->parse("LINE");
			}

			$pic = "";
			if ($this->entry_id)
			{
				$images->list_by_object($this->entry_id);
				while ($row = $images->db_next())
				{
					$this->vars(array("img_idx" => $row[idx],"img_id" => $row[oid]));
					$pic.=$this->parse("IMAGE");
				}
			}
			global $REQUEST_URI;
			$ip ="";
			if ($this->entry_id != 0)	// images can only be addes after the place is entered for the first time
			{
				$this->vars(array("IMAGE" => $pic, "url"	=> $REQUEST_URI));
				$ip = $this->parse("IMG_WRAP");
			}

			$this->vars(array("var_name" => "entry_id", "var_value" => $this->entry_id));
			$ei = $this->parse("EXTRAIDS");
			if (is_array($extraids))
			{
				reset($extraids);
				while(list($k,$v) = each($extraids))
				{
					$this->vars(array("var_name" => $k, "var_value" => $v));
					$ei.=$this->parse("EXTRAIDS");
				}
			}

			$this->add_hit($this->id);

			$this->vars(array("LINE"							=> $c,
												"EXTRAIDS"					=> $ei,
												"IMG_WRAP"					=> $ip, 
												"form_border"				=> ($this->arr[border] != "" ? " BORDER='".$this->arr[border]."'" : ""),
												"form_bgcolor"			=> ($this->arr[bgcolor] !="" ? " BGCOLOR='".$this->arr[bgcolor]."'" : ""),
												"form_cellpadding"	=> ($this->arr[cellpadding] != "" ? " CELLPADDING='".$this->arr[cellpadding]."'" : ""),
												"form_cellspacing"	=> ($this->arr[cellspacing] != "" ? " CELLSPACING='".$this->arr[cellspacing]."'" : ""),
												"form_height"				=> ($this->arr[height] != "" ? " HEIGHT='".$this->arr[height]."'" : ""),
												"form_width"				=> ($this->arr[width] != "" ? " WIDTH='".$this->arr[width]."'" : "" ),
												"form_height"				=> ($this->arr[height] != "" ? " HEIGHT='".$this->arr[height]."'" : "" ),
												"form_vspace"				=> ($this->arr[vspace] != "" ? " VSPACE='".$this->arr[vspace]."'" : ""),
												"form_hspace"				=> ($this->arr[hspace] != "" ? " HSPACE='".$this->arr[hspace]."'" : ""),
												"action"						=> $action,
												"form_action"				=> $form_action,
												"submit_text"				=> $this->arr[submit_text],
												"reforb"						=> $reforb));
			$st = $this->parse();				
			return $st;
		}

		////
		// !saves the entry for the form $id, if $entry_id specified, updates it instead of creating a new one
		function process_entry($arr)
		{
			extract($arr);
			$this->load($id);

			if (!$entry_id)
			{
				$entry_id = $this->new_object(array("parent" => $this->arr[ff_folder], "name" => "form_entry", "class_id" => CL_FORM_ENTRY));
				$new = true;
			}
			else
			{
				$new = false;
			}

			for ($i=0; $i < $this->arr[rows]; $i++)
			{
				for ($a=0; $a < $this->arr[cols]; $a++)
				{
					$this->arr[contents][$i][$a] -> process_entry(&$this->entry, $entry_id);
				}
			}

			$en = serialize($this->entry);
			if ($new)
			{
				$this->db_query("insert into form_entries values($entry_id, $this->id, ".time().", '$en')");

				// create sql 
				reset($this->entry);
				$ids = "id"; $vals = "$entry_id";
				$first = true;
				while (list($k, $v) = each($this->entry))
				{
					$ids.=",el_$k";
					if (is_array($v))
					{
						$v = serialize($v);
					}
					$vals.=",'$v'";
				}
				$sql = "INSERT INTO form_".$this->id."_entries($ids) VALUES($vals)";
				$this->db_query($sql);

				$this->_log("form","T&auml;itis formi $this->name");
				$this->do_actions($entry_id);
			}
			else
			{
				$this->db_query("update form_entries set form_entries.tm = ".time().", contents = '$en' where id = $entry_id");
				// create sql 
				reset($this->entry);
				$ids = "id = $entry_id";
				$first = true;
				while (list($k, $v) = each($this->entry))
				{
 					if (is_array($v))
					{
						$v = serialize($v);
					}
					$ids.=",el_$k = '$v'";
				}
				$sql = "update form_".$this->id."_entries set $ids where id = $entry_id";
				$this->db_query($sql);
				$this->_log("form","Muutis formi $this->name sisestust $entry_id");
			}
			// paneme kirja, et kasutaja t2itis selle formi et siis kasutajax regimisel saame seda kontrollida.
			$this->entry_id = $entry_id;
			$GLOBALS["session_filled_forms"][$this->id] = $entry_id;

			global $ext;

			if ($redirect_after)
			{
				// if this variable has been set in extraids when showing the form, redirect to it
				return $redirect_after;
			}

			switch ($this->get_location())
			{
				case "text":
					$l = "forms.$ext?type=ae_text&id=$id&entry_id=$entry_id";
					break;
				case "redirect":
					$l = $this->get_ae_location();
					break;
				case "search_results":
					$l = $this->mk_my_orb("show_entry", array("id" => $id, "entry_id" => $entry_id, "op_id" => 1));
					break;
				default:
					if ($this->type == FTYPE_SEARCH)
					{
						// n2itame ocingu tulemusi
						$l = $this->mk_my_orb("show_entry", array("id" => $id, "entry_id" => $entry_id,"op_id" => 1));
					}
					else
					{
						$l = $this->mk_my_orb("show", array("id" => $id, "entry_id" => $entry_id));
					}
					break;
			}
			return $l;
		}

		function delete_entry($entry_id)
		{
			$eacl = new acl;
			$eacl->query($entry_id);
			$this->delete_object($entry_id);
			$this->_log("form","Kustutas formi $this->name sisestuse $entry_id");
		}

		// laeb entry parajasti aktiivse (konstruktoriga määratud) vormi jaoks
		function load_entry($entry_id)
		{
			$this->entry_id = $entry_id;
			if (sizeof($this->flist) > 0)
			{
				list(,$id) = each($this->flist);
			}
			else
			{
				$id = $this->id;
			};
			$this->db_query("SELECT * FROM form_".$id."_entries WHERE id = $entry_id");
			if (!($row = $this->db_next()))
			{
				$this->raise_error(sprintf(E_FORM_NO_SUCH_ENTRY,$entry_id,$id),true);
			};

			reset($row);

			while (list($k,$v) = each($row))
			{
				$v2 = unserialize($v);
				if (is_array($v2))
				{
					$v = $v2;
				}
				if (substr($k,0,3) == "el_")
				{
					$this->entry[substr($k,3)] = $v;
				};
			};

			$this->vars(array("entry_id" => $entry_id));

			for ($row=0; $row < $this->arr[rows]; $row++)
			{
				for ($col=0; $col < $this->arr[cols]; $col++)
				{
					$this->arr[contents][$row][$col] -> set_entry(&$this->entry, $entry_id);
				};
			};
		}


		////
		// !shows entry $entry_id of form $id using output $op_id
		function show($arr)
		{
			extract($arr);
			$this->load($id);

			if ($this->type == 2)	// if this is a search form, then search, instead of showing the entered data
				return $this->do_search($entry_id, $op_id);

			$this->load_output($op_id);
			$this->load_entry($entry_id);
			if ($admin)
			{
				$this->read_template("show_user_admin.tpl");

				$menunames = array();
				$this->db_query("SELECT objects.oid as oid, 
																objects.name as name
													FROM objects 
													WHERE objects.class_id = 13 AND objects.status != 0 AND objects.last = $this->id");
				while ($row = $this->db_next())
					$menunames[$row[oid]] = $row[name];


				$actioncache = array(); $ac = ""; $acc = "";
				$this->db_query("SELECT form_actions.*,objects.name as name FROM form_actions 
												 LEFT JOIN objects ON objects.oid = form_actions.id
												 WHERE form_id = $this->id AND type='move_filled'");
				while ($row = $this->db_next())
				{
					$row[data] = unserialize($row[data]);
					if (is_array($row[data]))
					{
						$this->vars(array("colspan" => sizeof($row[data]),"action_name" => $row[name]));
						$ac.=$this->parse("ACTIONS");

						reset($row[data]);
						while (list($k,$v) = each($row[data]))
						{
							$this->vars(array("action_target" => $k,"action_target_name" => $menunames[$k],"parent" => $k,"op_id" => $output_id));
							$acc.=$this->parse("ACTION_LINE");
						}
					}
				}
				$this->vars(array("ACTION_LINE" => $acc, "ACTIONS" => $ac,"op_id" => $output_id,"parent" => $this->id));
			}
			else
			{
				$this->read_template("show_user.tpl");
			}
			$this->add_hit($entry_id);
			$this->add_hit($op_id);

//			$style_cache = array();
//			$styles_loaded = array();

			if (($def_style = $this->output[def_style]) == 0)
				$def_style = $this->arr[def_style];

			$t_style = new style();

			for ($row = 0; $row < $this->output[rows]; $row++)
			{
				$html="";
				for ($col = 0; $col < $this->output[cols]; $col++)
				{
					if (!($arr = $this->get_spans($row, $col, $this->output[map], $this->output[rows], $this->output[cols])))
						continue;

					$style_id = $this->output[$arr[r_row]][$arr[r_col]][style];
					if ($style_id == 0)
					{
						$style_id = $def_style;
					}

					$chtml= "";
					for ($i=0; $i < $this->output[$arr[r_row]][$arr[r_col]][el_count]; $i++)
					{
						$el = $this->get_element_by_id($this->output[$arr[r_row]][$arr[r_col]][els][$i]);
						if ($el)
						{
							$chtml.= $el->gen_show_html();
						}
					}

					if ($style_id != 0)
					{
						$html.= $t_style->get_cell_begin_str($style_id,$arr[colspan],$arr[rowspan]).$chtml.$t_style->get_cell_end_str($style_id)."</td>";
					}
					else
					{
						$html.= "<td colspan=\"".$arr[colspan]."\" rowspan=\"".$arr[rowspan]."\">".$chtml."</td>";
					}
				}
				$this->vars(array("COL" => $html));
				$this->parse("LINE");
			}
			$this->vars(array("form_border"				=> ($this->output[border] != "" ? " BORDER='".$this->output[border]."'" : ""),
												"form_bgcolor"			=> ($this->output[bgcolor] !="" ? " BGCOLOR='".$this->output[bgcolor]."'" : ""),
												"form_cellpadding"	=> ($this->output[cellpadding] != "" ? " CELLPADDING='".$this->output[cellpadding]."'" : ""),
												"form_cellspacing"	=> ($this->output[cellspacing] != "" ? " CELLSPACING='".$this->output[cellspacing]."'" : ""),
												"form_height"				=> ($this->output[height] != "" ? " HEIGHT='".$this->output[height]."'" : ""),
												"form_width"				=> ($this->output[width] != "" ? " WIDTH='".$this->output[width]."'" : "" ),
												"form_height"				=> ($this->output[height] != "" ? " HEIGHT='".$this->output[height]."'" : "" ),
												"form_vspace"				=> ($this->output[vspace] != "" ? " VSPACE='".$this->output[vspace]."'" : ""),
												"form_hspace"				=> ($this->output[hspace] != "" ? " HSPACE='".$this->output[hspace]."'" : "")));
			return $this->parse();
		}

		function save_cells()
		{
			for ($row = 0; $row < $this->arr[rows]; $row++)
				for ($col = 0; $col < $this->arr[cols]; $col++)
					$this->arr[ceontents][$row][$col]->save_final();
		}

		////
		// !Merge the cell above cell($row,$col) in form $id
		function exp_cell_up($arr)
		{
			extract($arr);
			$this->load($id);

			// here we don't need to find the upper bound, because this always is the upper bound

			if ($row > 0)
			{
				// first we must find out the colspan of the current cell and set all the cell above that one to the correct values in the map
				for ($a=0; $a < $this->arr[cols]; $a++)
					if ($this->arr[map][$row][$a] == $this->arr[map][$row][$col])
						$this->arr[map][$row-1][$a] = $this->arr[map][$row][$col];		// expand the area
			}

			$this->save();
			$orb = $this->mk_orb("change", array("id" => $this->id));
			header("Location: $orb");
			return $orb;
		}

		////
		// !Merges the cell ($row,$col) in form $id with the cell immediately below it
		function exp_cell_down($arr)
		{
			extract($arr);
			$this->init($id);

			// here we must first find the lower bound for the area being expanded and use that instead the $row, because
			// that is an arbitrary position in the area really.
			for ($i=$row; $i < $this->arr[rows]; $i++)
				if ($this->arr[map][$i][$col] == $this->arr[map][$row][$col])
					$r=$i;
				else
					break;

			if (($r+1) < $this->arr[rows])
			{
				for ($a=0; $a < $this->arr[cols]; $a++)
					if ($this->arr[map][$row][$a] == $this->arr[map][$row][$col])
						$this->arr[map][$r+1][$a] = $this->arr[map][$row][$col];		// expand the area
			}

			$this->save();
			$orb = $this->mk_orb("change", array("id" => $this->id));
			header("Location: $orb");
			return $orb;
		}

		////
		// !Expand cell at $row,$col with the cell to it's left, in form $id
		function exp_cell_left($arr)
		{
			extract($arr);
			$this->load($id);

			// again, this is the left bound, so we don't need to find it

			if ($col > 0)
			{
				for ($a =0; $a < $this->arr[rows]; $a++)
					if ($this->arr[map][$a][$col] == $this->arr[map][$row][$col])
						$this->arr[map][$a][$col-1] = $this->arr[map][$row][$col];		// expand the area
			}

			$this->save();
			$orb = $this->mk_orb("change", array("id" => $this->id));
			header("Location: $orb");
			return $orb;
		}

		////
		// !Merges the cell ($row, $col) in form $id with the cell right to it
		function exp_cell_right($arr)
		{
			extract($arr);
			$this->load($id);

			// here we must first find the right bound for the area being expanded and use that instead the $row, because
			// that is an arbitrary position in the area really.
			for ($i=$col; $i < $this->arr[cols]; $i++)
				if ($this->arr[map][$row][$i] == $this->arr[map][$row][$col])
					$r=$i;
				else
					break;

			if (($r+1) < $this->arr[cols])
			{
				for ($a =0; $a < $this->arr[rows]; $a++)
					if ($this->arr[map][$a][$r] == $this->arr[map][$row][$r])
						$this->arr[map][$a][$r+1] = $this->arr[map][$row][$r];		// expand the area
			}

			$this->save();
			$orb = $this->mk_orb("change", array("id" => $id));
			header("Location: $orb");
			return $orb;
		}

		function get_location()
		{
			if ($this->type == 2)
				return "search_results";

			switch($this->arr[after_submit])
			{
				case 1:
					return "edit";
				case 2:
					return "text";
				case 3:
					return "redirect";
			}
		}

		function get_ae_location()
		{
			return $this->arr[after_submit_link];
		}

		function ae_text()
		{
			$this->read_template("ae_text.tpl");
			$this->vars(array("ae_text" => $this->arr[after_submit_text]));
			return $this->parse();
		}

		////
		// !Splits the cell ($row, $col) in form $id vertically
		function split_cell_ver($arr)
		{
			extract($arr);
			$this->load($id);

			$lbound = -1;
			for ($i=0; $i < $this->arr[cols] && $lbound==-1; $i++)
				if ($this->arr[map][$row][$i] == $this->arr[map][$row][$col])
					$lbound = $i;

			$rbound = -1;
			for ($i=$lbound; $i < $this->arr[cols] && $rbound==-1; $i++)
				if ($this->arr[map][$row][$i] != $this->arr[map][$row][$col])
					$rbound = $i-1;

			if ($rbound == -1)
				$rbound = $this->arr[cols]-1;

			$nm = array();
			$center = ($rbound+$lbound)/2;

			for ($i=0; $i < $this->arr[rows]; $i++)
				for ($a=0; $a < $this->arr[cols]; $a++)
					if ($this->arr[map][$i][$a] == $this->arr[map][$row][$col])
					{
						if ($this->arr[map][$i][$a][col] < $center)	
						{
							// the hotspot of the cell is on the left of the splitter
							if ($a <= $center)	
								// and we currently are also on the left side then leave it be
								$nm[$i][$a] = $this->arr[map][$i][$a];
							else
								// and we are on the right side choose a new one
								$nm[$i][$a] = array("row" => $this->arr[map][$i][$a][row], "col" => floor($center)+1);
						}
						else
						{
							// the hotspot of the cell is on the right of the splitter
							if ($a <= $center)
								// and we are on the left side choose a new one
								$nm[$i][$a] = array("row" => $this->arr[map][$i][$a][row], "col" => $lbound);
							else
								// if we are on the same side, use the current value
								$nm[$i][$a] = $this->arr[map][$i][$a];
						}	
					}
					else
						$nm[$i][$a] = $this->arr[map][$i][$a];

			$this->arr[map] = $nm;
			$this->save();
			$orb = $this->mk_orb("change", array("id" => $id));
			header("Location: $orb");
			return $orb;
		}

		////
		// !splits the cell at ($row, $col) in form $id vertically
		function split_cell_hor($arr)
		{
			extract($arr);
			$this->load($id);

			$ubound = -1;
			for ($i=0; $i < $this->arr[rows] && $ubound==-1; $i++)
				if ($this->arr[map][$i][$col] == $this->arr[map][$row][$col])
					$ubound = $i;

			$lbound = -1;
			for ($i=$ubound; $i < $this->arr[rows] && $lbound==-1; $i++)
				if ($this->arr[map][$i][$col] != $this->arr[map][$row][$col])
					$lbound = $i-1;

			if ($lbound == -1)
				$lbound = $this->arr[rows]-1;

			$nm = array();
			$center = ($ubound+$lbound)/2;

			for ($i=0; $i < $this->arr[rows]; $i++)
				for ($a=0; $a < $this->arr[cols]; $a++)
					if ($this->arr[map][$i][$a] == $this->arr[map][$row][$col])
					{
						if ($this->arr[map][$i][$a][row] < $center)	
						{
							// the hotspot of the cell is above the splitter
							if ($i <= $center)	
								// and we currently are also above then leave it be
								$nm[$i][$a] = $this->arr[map][$i][$a];
							else
								// and we are below choose a new one
								$nm[$i][$a] = array("row" => floor($center)+1, "col" => $this->arr[map][$i][$a][col]);
						}
						else
						{
							// the hotspot of the cell is below the splitter
							if ($i <= $center)
								// but we are above, so make new
								$nm[$i][$a] = array("row" => $ubound, "col" => $this->arr[map][$i][$a][col]);
							else
								// if we are on the same side, use the current value
								$nm[$i][$a] = $this->arr[map][$i][$a];
						}	
					}
					else
						$nm[$i][$a] = $this->arr[map][$i][$a];

			$this->arr[map] = $nm;
			$this->save();
			$orb = $this->mk_orb("change", array("id" => $id));
			header("Location: $orb");
			return $orb;
		}

		////
		// !merges the cell ($row, $col) in output $op_id of form $id with the cell immediately above it
		function op_exp_up($arr)
		{
			extract($arr);
			$this->load($id);
			$this->load_output($op_id);

			// here we don't need to find the upper bound, because this always is the upper bound

			if ($row > 0)
			{
				// first we must find out the colspan of the current cell and set all the cell above that one to the correct values in the map
				for ($a=0; $a < $this->output[cols]; $a++)
					if ($this->output[map][$row][$a] == $this->output[map][$row][$col])
						$this->output[map][$row-1][$a] = $this->output[map][$row][$col];		// expand the area
			}

			$this->save_output($op_id);
			$orb = $this->mk_orb("change_op", array("id" => $id, "op_id" => $op_id));
			header("Location: $orb");
			return $orb;
		}

		////
		// !merges the cell ($row,$col) in output $op_id of form $id with the cell below it
		function op_exp_down($arr)
		{
			extract($arr);
			$this->load($id);
			$this->load_output($op_id);

			// here we must first find the lower bound for the area being expanded and use that instead the $row, because
			// that is an arbitrary position in the area really.
			for ($i=$row; $i < $this->output[rows]; $i++)
				if ($this->output[map][$i][$col] == $this->output[map][$row][$col])
					$r=$i;
				else
					break;

			if (($r+1) < $this->output[rows])
			{
				for ($a=0; $a < $this->output[cols]; $a++)
					if ($this->output[map][$row][$a] == $this->output[map][$row][$col])
						$this->output[map][$r+1][$a] = $this->output[map][$row][$col];		// expand the area
			}

			$this->save_output($op_id);
			$orb = $this->mk_orb("change_op", array("id" => $id, "op_id" => $op_id));
			header("Location: $orb");
			return $orb;
		}

		////
		// !merges the cell ($row,$col) in output $op_id of form $id with the cell to the left of it
		function op_exp_left($arr)
		{
			extract($arr);
			$this->load($id);
			$this->load_output($op_id);

			// again, this is the left bound, so we don't need to find it

			if ($col > 0)
			{
				for ($a =0; $a < $this->output[rows]; $a++)
					if ($this->output[map][$a][$col] == $this->output[map][$row][$col])
						$this->output[map][$a][$col-1] = $this->output[map][$row][$col];		// expand the area
			}

			$this->save_output($op_id);
			$orb = $this->mk_orb("change_op", array("id" => $id, "op_id" => $op_id));
			header("Location: $orb");
			return $orb;
		}

		////
		// !merges the cell ($row,$col) in output $op_id of form $id with the cell to the left of it
		function op_exp_right($arr)
		{
			extract($arr);
			$this->load($id);
			$this->load_output($op_id);

			// here we must first find the right bound for the area being expanded and use that instead the $row, because
			// that is an arbitrary position in the area really.
			for ($i=$col; $i < $this->output[cols]; $i++)
				if ($this->output[map][$row][$i] == $this->output[map][$row][$col])
					$r=$i;
				else
					break;

			if (($r+1) < $this->output[cols])
			{
				for ($a =0; $a < $this->output[rows]; $a++)
					if ($this->output[map][$a][$r] == $this->output[map][$row][$r])
						$this->output[map][$a][$r+1] = $this->output[map][$row][$r];		// expand the area
			}

			$this->save_output($op_id);
			$orb = $this->mk_orb("change_op", array("id" => $id, "op_id" => $op_id));
			header("Location: $orb");
			return $orb;
		}

		////
		// !generates the form for selecting among which forms to search for search form $id
		function gen_search_sel($arr)
		{
			extract($arr);
			$this->init($id, "search_sel.tpl", "Vali otsitavad formid");
	
			$this->vars(array("LINE" => "")); $cnt=0;

			$this->db_query("SELECT forms.*,objects.parent as parent,objects.name as name, objects.comment as comment 
											 FROM forms 
											 LEFT JOIN objects ON objects.oid = forms.id
											 WHERE objects.status != 0 AND forms.type = 1
											 GROUP BY objects.oid");
			while($row = $this->db_next())
			{
				$this->save_handle();

				// read all the form's outputs
				$this->db_query("SELECT form_output.*, objects.name as name
												 FROM form_output 
												 LEFT JOIN objects ON objects.oid = form_output.id
												 WHERE objects.status != 0 AND objects.parent = ".$row[id]."
												 GROUP BY objects.oid");
				$ol = ""; 
				while ($orow = $this->db_next())
				{
					$this->vars(array("op_id"				=> $orow[id], 
														"op_name"			=> $orow[name], 
														"op_selected" => ($this->arr[search_outputs][$row[id]] == $orow[id] ? "SELECTED" : "")));
					$ol.=$this->parse("OP_ITEM");
				}

				$this->vars(array("form_name"			=> $row[name], 
													"form_comment"	=> $row[comment], 
													"form_location" => $row[parent], 
													"form_id"				=> $row[id],
													"row"						=> $cnt,
													"checked"				=> ($this->arr[search_from][$row[id]] == 1 ? "CHECKED" : ""),
													"OP_ITEM"				=> $ol));
				$this->parse("LINE");
				$this->parse("SELLINE");
				$cnt+=2;
				$this->restore_handle();
			}

			$this->vars(array("form_id" => $this->id,
												"reforb"	=> $this->mk_reforb("save_search_sel", array("id" => $this->id))));

			return $this->do_menu_return();
		}

		////
		// !saves the forms from which to search for search form $id
		function save_search_sel(&$arr)
		{
			$this->dequote(&$arr);
			extract($arr);
			$this->load($id);

			$this->arr[search_from] = array();
			while( list($k,$v) = each(&$arr))
			{
				if (substr($k,0,3) == "ch_")
					$this->arr[search_from][substr($k,3)] = $v;
				else
				if (substr($k,0,4) == "sel_")
					$this->arr[search_outputs][substr($k,4)] = $v;
			}
			
			$this->save();
			return $this->mk_orb("sel_search", array("id" => $id));
		}

		// does the actual searching part and returns
		// an array, that has one entry for each form selected as a search target
		// and that entry is an array of matching entries for that form
		function search($entry_id)
		{
			// laeb täidetud vormi andmed sisse
			$this->load_entry($entry_id);

			// gather all the elements of this form in an array
			$els = array();
			for ($row=0; $row < $this->arr["rows"]; $row++)
			{
				for ($col=0; $col < $this->arr["cols"]; $col++)
				{
					$this->arr["contents"]["$row"]["$col"]->get_els(&$els);
				};
			};

			$ret = array();

			reset($this->arr["search_from"]);

			// loop through all the forms that are selected as search targets
			while (list($id,$v) = each($this->arr["search_from"]))
			{
				if ($v != 1)		// search only selected forms
					continue;

				// create the sql that searches from this form's entries
				$query="SELECT * FROM form_".$id."_entries LEFT JOIN objects ON objects.oid = form_".$id."_entries.id WHERE objects.status !=0 ";

				// loop through all the elements of this form 
				reset($els);
				while( list(,$el) = each($els))
					if ($el->arr[linked_form] == $id)	// and use only the elements that are members of the current form in the query

						// oh la la
						if ($this->entry[$el->get_id()] != "")	
							$query.= "AND el_".$el->arr[linked_element]." like '%".$this->entry[$el->get_id()]."%' ";

				if ($query == "")
					$query = "SELECT * FROM form_".$id."_entries";

				$matches = array();
				$this->db_query($query);
				while ($row = $this->db_next())
					$matches[] = $row[id];

				$ret[$id] = $matches;
			}
		
			return $ret;
		}

		function do_search($entry_id, $output_id)
		{
			$matches = $this->search($entry_id);
			reset($matches);
			while(list($fid,$v) = each($matches))
			{
				$t = new form();
				reset($v);
				while (list(,$eid) = each($v))
				{
					$t->reset();
					$html.=$t->show(array("id" => $fid, "entry_id" => $eid, "op_id" => $this->arr[search_outputs][$fid]));
				}
			}
		
			return $html;
		}

		function html()
		{
			$frm= $this->gen_user_html();
			$frm = htmlentities($frm);
			$this->reset();
			$this->read_template("html.tpl");
			$this->vars(array("form" => $frm));
			return $this->parse();
		}

		// new orb functions start here
		function add($arr)
		{
			extract($arr);
			$this->mk_path($parent,"Lisa form");
			$this->read_template("form_add.tpl");
			$this->vars(array("reforb"	=> $this->mk_reforb("submit_add",array("parent" => $parent, "alias_doc" => $alias_doc))));
			return $this->parse();
		}

		function submit_add($arr)
		{
			extract($arr);

			$id = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_FORM, "comment" => $comment));
			
			if ($type == "entry") 
				$type = FORM_ENTRY;
			if ($type == "search")
				$type = FORM_SEARCH;
			if ($type == "rating")
				$type = FORM_RATING;

			$this->db_query("INSERT INTO forms(id, type,content,cols,rows) VALUES($id, $type,'',1,1)");

			$this->db_query("CREATE TABLE form_".$id."_entries (id int primary key)");

			$this->load($id);

			$this->_log("form","Lisas formi $name");

			if ($alias_doc)
			{
				$this->add_alias($alias_doc, $id);
			}

			return $this->mk_orb("change", array("id" => $id));
		}

		// here we must make a list of all the filled forms, that include element with id $arr[id]
		function list_el_forms($arr)
		{
			extract($arr);
			$this->read_template("objects.tpl");
			$this->db_query("SELECT form_id FROM form_elements WHERE id = $id");
			$el_row = $this->db_next();
			$form_id = $el_row[form_id];
			
			global $sortby;
			if ($sortby == "")
				$sortby = "jrk";

			global $order,$baseurl;
			if ($order == "")
				$order = "ASC";

			global $class_defs;
			$arr = array();
			reset($class_defs);
			while (list($id,$ar) = each($class_defs))
				if ($ar[can_add])	// only object types that can be added anywhere
					$arr[$id] = $ar[name];
			$this->vars(array("parent" => $parent,"types" => $this->option_list(0,$arr)));
			$this->vars(array("ADD_CAT" => "","form_id" => $form_id));

			global $class_defs;

			classload("menuedit");
			$m = new menuedit;

			$this->listacl("objects.class_id = ".CL_FORM_ENTRY." AND objects.status != 0 AND form_entries.form_id = $form_id",array("form_entries" => "form_entries.id = objects.oid"));
			$this->db_query("SELECT objects.* FROM objects LEFT JOIN form_entries ON form_entries.id = objects.oid WHERE objects.class_id = ".CL_FORM_ENTRY." AND objects.status != 0 AND form_entries.form_id = $form_id ORDER BY $sortby $order");
			while ($row = $this->db_next())
			{
				$this->dequote(&$row[name]);
				$inf = $class_defs[$row[class_id]];
				$this->vars(array("name"				=> $row[name],
													"oid"					=> $row[oid], 
													"order"				=> $row[jrk], 
													"active"			=> ($row[status] == 2 ? "CHECKED" : ""),
													"active2"			=> $row[status],
													"modified"		=> $this->time2date($row[modified],2),
													"modifiedby"	=> $row[modifiedby],
													"icon"				=> $m->get_icon_url($row[class_id],$row[name]),
													"type"				=> $GLOBALS["class_defs"][$row[class_id]][name],
													"change"			=> $this->mk_orb("change", array("id" => $row[oid], "parent" => $row[parent]), $inf[file])));
				$this->vars(array("NFIRST" => $this->can("order",$row[oid]) ? $this->parse("NFIRST") : "",
													"CAN_ACTIVE" => $this->can("active",$row[oid]) ? $this->parse("CAN_ACTIVE") : ""));
				$l.=$this->parse("LINE");
			}

			$this->vars(array("LINE" => $l,
												"reforb" => $this->mk_reforb("submit_order_doc", array("parent" => $parent)),
												"order1"			=> $sortby == "name" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
												"sortedimg1"	=> $sortby == "name" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
												"order2"			=> $sortby == "jrk" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
												"sortedimg2"	=> $sortby == "jrk" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
												"order3"			=> $sortby == "modifiedby" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
												"sortedimg3"	=> $sortby == "modifiedby" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
												"order4"			=> $sortby == "modified" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
												"sortedimg4"	=> $sortby == "modified" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
												"order5"			=> $sortby == "class_id" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
												"sortedimg5"	=> $sortby == "class_id" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
												"order6"			=> $sortby == "status" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
												"sortedimg6"	=> $sortby == "status" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : ""
												));
			return $this->parse();
		}

		////
		// !Generates admin form for editing cell at ($row,$col) in form $id
		function admin_cell($arr)
		{
			extract($arr);
			$this->load($id);
			return $this->arr[contents][$row][$col]->admin_cell();
		}
 
		////
		// !Adds an element to the end of 
		function add_element($arr)
		{
			extract($arr);
			$this->load($id);
			$ret = $this->arr[contents][$row][$col]->add_element($wizard_step,&$this);
			if ($ret == false)
			{
				return $this->mk_orb("admin_cell", array("id" => $this->id, "row" => $row, "col" => $col));
			}
			else
			{
				return $ret;
			}
		}

		////
		// !saves the elements in the cell ($row, $col) in form $id
		function submit_cell($arr)
		{
			$this->dequote(&$arr);
			extract($arr);
			$this->load($id);
			$this->arr[contents][$row][$col]->submit_cell($arr,&$this);
			$this->save();
			return $this->mk_orb("admin_cell", array("id" => $this->id, "row" => $row, "col" => $col));
		}

		////
		// !generates the form for selecting cell style
		function sel_cell_style($arr)
		{
			extract($arr);
			$this->load($id);
			return $this->arr[contents][$row][$col]->pickstyle();
		}

		////
		// !saves the cell style
		function save_cell_style($arr)
		{
			$this->dequote(&$arr);
			extract($arr);
			$this->load($id);
			$this->arr[contents][$row][$col]->set_style($style,&$this);
			$this->save();
			return $this->mk_orb("admin_cell", array("id" => $this->id, "row" => $row, "col" => $col));
		}

		////
		// !deletes form $id
		function delete($arr)
		{
			extract($arr);
			$this->delete_object($id);
			$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $id","name");
			$this->_log("form","Kustutas formi $name");
			header("Location: ".$this->mk_orb("obj_list", array("parent" => $parent), "menuedit"));
		}

		////
		// !finds the element with id $id in the loaded form and returns a reference to it
		function get_element_by_id($id)
		{
			for ($row = 0; $row < $this->arr[rows]; $row++)
			{
				for ($col = 0; $col < $this->arr[cols]; $col++)
				{
					$this->arr[contents][$row][$col]->get_els(&$elar);
					reset($elar);
					while (list(,$el) = each($elar))
					{
						if ($el->get_id() == $id)
						{
							return $el;
						}
					}
				}
			}
			return false;
		}

		////
		// !finds the element with name $name in the loaded form and returns a reference to it
		function get_element_by_name($name)
		{
			for ($row = 0; $row < $this->arr[rows]; $row++)
			{
				for ($col = 0; $col < $this->arr[cols]; $col++)
				{
					$this->arr[contents][$row][$col]->get_els(&$elar);
					reset($elar);
					while (list(,$el) = each($elar))
					{
						if ($el->get_el_name() == $name)
						{
							return $el;
						}
					}
				}
			}
			return false;
		}

		////
		// !finds the first element with type $name in the loaded form and returns a reference to it
		function get_element_by_type($name)
		{
			for ($row = 0; $row < $this->arr[rows]; $row++)
			{
				for ($col = 0; $col < $this->arr[cols]; $col++)
				{
					$this->arr[contents][$row][$col]->get_els(&$elar);
					reset($elar);
					while (list(,$el) = each($elar))
					{
						if ($el->get_type() == $name)
						{
							return $el;
						}
					}
				}
			}
			return false;
		}

		////
		// !generates the form for changing the form element's position in the hierarchy and in the cells
		function change_el_pos($arr)
		{
			extract($arr);
			$this->init($id, "", "<a href='".$this->mk_orb("change", array("id" => $this->id))."'>Muuda formi</a> / Vali elemendi asukoht");
			$el =&$this->get_element_by_id($el_id);
			return $el->change_pos($arr,&$this);
		}

		////
		// !saves the element position changes
		function submit_chpos($arr)
		{
			$this->dequote(&$arr);
			extract($arr);
			$this->load($id);
			
			$this->upd_object(array("oid" => $el_id, "parent" => $parent));

			list($r,$c) = explode("_", $s_cell);

			if (!($r == $row && $c == $col))
			{
				$this->arr[elements][$r][$c][$el_id] = $this->arr[elements][$row][$col][$el_id];
				unset($this->arr[elements][$row][$col][$el_id]);
				if (!is_array($this->arr[elements][$row][$col]))
				{
					$this->arr[elements][$row][$col] = array();
				}
				$this->save();
			}

			return $this->mk_orb("change_el_pos", array("id" => $this->id, "col" => $c, "row" => $r, "el_id" => $el_id));
		}

		function _serialize($arr)
		{
			extract($arr);
			$this->db_query("SELECT objects.*, forms.* FROM objects LEFT JOIN forms ON forms.id = objects.oid WHERE oid = $oid");
			$row = $this->db_next();
			if (!$row)
			{
				return false;
			}
			return serialize($row);
		}

		function _unserialize($arr)
		{
			extract($arr);

			$row = unserialize($str);
			// basically, we create a new object and insert the stuff in the array right back in it. 
			$oid = $this->new_object(array("parent" => $parent, "name" => $row["name"], "class_id" => CL_FORM, "status" => $row["status"], "comment" => $row["comment"], "last" => $row["last"], "jrk" => $row["jrk"], "visible" => $row["visible"], "period" => $period, "alias" => $row["alias"], "periodic" => $row["periodic"], "doc_template" => $row["doc_template"], "activate_at" => $row["activate_at"], "deactivate_at" => $row["deactivate_at"], "autoactivate" => $row["autoactivate"], "autodeactivate" => $row["autodeactivate"], "brother_of" => $row["brother_of"]));

			// same with the form. 
			$this->db_query("INSERT INTO forms(id,content,type,cols,rows) values($oid,'".$row["content"]."','".$row["type"]."','".$row["cols"]."','".$row["rows"]."')");

			// create form entries table
			$this->db_query("CREATE TABLE form_".$oid."_entries (id int primary key)");

			// then we go through alla the elements in the form
			$this->load($oid);

			$fc = new form_cell;

			for ($row = 0; $row < $this->arr["rows"]; $row++)
			{
				for ($col = 0; $col < $this->arr["cols"]; $col++)
				{
					if (is_array($this->arr["elements"][$row][$col]))
					{
						reset($this->arr["elements"][$row][$col]);
						while (list($k,$v) = each($this->arr["elements"][$row][$col]))
						{
							// and for each alter the form_xxx_entries table and the form element to include this form. cool.
							$fc->_do_add_element($this->id,$k);
						}
					}
				}
			}
			// and we should be done? ok, except for form actions and outputs, but we do those l8r. 
			return $oid;
		}

		////
		// !generates the form for changing output metainfo
		function metainfo($arr)
		{
			extract($arr);
			$this->init($id,"metainfo.tpl","Metainfo");
			$row = $this->get_object($this->id);

			$this->db_query("SELECT count(id) as cnt from form_entries where form_id = $this->id");
			if (!($cnt = $this->db_next()))
				$this->raise_error("form->metainfo(): weird error!", true);

			$this->vars(array("created"			=> $this->time2date($row[created],2), 
												"created_by"	=> $row[createdby],
												"modified"		=> $this->time2date($row[modified],2),
												"modified_by"	=> $row[modifiedby],
												"views"				=> $row[hits],
												"num_entries"	=> $cnt[cnt],
												"position"		=> $ret,
												"reforb"			=> $this->mk_reforb("submit_metainfo", array("id" => $this->id)),
												"form_name"		=> $row["name"],
												"form_comment" => $row["comment"]));
			return $this->do_menu_return();
		}


		function submit_metainfo(&$arr)
		{
			$this->quote(&$arr);
			extract($arr);
			$this->update_object($id, $name, -1, $comment);
			$this->_log("form","Muutis formi $this->name metainfot");
			return $this->mk_orb("metainfo",  array("id" => $id));
		}

		////
		// !returns the value of the entered element. form entry must be loaded before calling this.
		function get_element_value_by_name($name)
		{
			$el = $this->get_element_by_name($name);
			if (!$el)
			{
				return false;
			}

			return $this->entry[$el->get_id()];
		}

		////
		// !returns the value of the entered element. finds the first element of that type and 
		// ignores the rest. form entry must be loaded before calling this.
		function get_element_value_by_type($name)
		{
			$el = $this->get_element_by_type($name);
			if (!$el)
			{
				return false;
			}

			return $this->entry[$el->get_id()];
		}
	};	// class ends
?>
