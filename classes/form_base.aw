<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_base.aw,v 2.2 2001/05/21 04:01:06 kristo Exp $
// form_base.aw - this class loads and saves forms, all form classes should derive from this.

class form_base extends aw_template
{
	function form_base()
	{
		$this->tpl_init("forms");
		$this->db_init();
	}

	////
	// !Loads the specified form
	// Forms are saved as serialized arrays in forms.content
	// the array is structured like this:
	// $arr[rows] - number of rows in form
	// $arr[cols] - number of columns in form
	// $arr[map]	- array that contains the map used in merging and splitting form cells
	//							the array is 2 dimensional, $arr[rows] wide and $arr[cols] deep
	//							each element represents the corresponding cell and shows which cell
	//							should really be displayed instead of the cell in taht position
	//							example:
	//							if a form has 2 rows and 3 columns and the rightmost 4 cells are merged into one, then the map looks like this:
	//							$arr[map][0][0] = array("row" => 0, "col" => 0);
	//							$arr[map][0][1] = array("row" => 0, "col" => 1);
	//							$arr[map][0][2] = array("row" => 0, "col" => 1);
	//							$arr[map][1][0] = array("row" => 1, "col" => 0);
	//							$arr[map][1][1] = array("row" => 0, "col" => 1);
	//							$arr[map][1][2] = array("row" => 0, "col" => 1);
	//							so the form looks like this:
	//							---------------
	//							| 0,0 |				|
	//							-------  0,1  -
	//							| 1,0 |       |
	//							---------------
	//
	// $arr[contents]	- array of form_cell's, one for each cell, this is not saved to the database,
	//									instead it contains the actual objects that are created from $arr[elements] upon loading
	// $arr[style]		- form's table style id
	// $arr[elements] - array of elements in the form, indexed by row and column
	function load($id = 0)
	{
		if ($id == 0)
		{
			// see tuleb form klassi konstruktorist
			$id = $this->fid;
		};
		$q = "SELECT forms.*,objects.* FROM forms LEFT JOIN objects ON objects.oid = forms.id WHERE forms.id = $id";
		$this->db_query($q);
		if (!($row = $this->db_next(false)))
		{
			$this->raise_error("form->load($id): no such form!",true);
		}

		$this->name = $row[name];
		$this->id = $row[oid];
		$this->parent = $row[parent];
		$this->type = $row[type];
		$this->comment = $row[comment];

		$this->arr = unserialize($row[content]);

		$this->normalize();

		$this->load_elements();
	}

	////
	// !Loads form elements from database
	// loads elements as specified in $this->arr[elements]
	// form must be loaded previously
	// puts elements in $this->arr[contents]
	function load_elements()
	{
		for ($row = 0; $row < $this->arr[rows]; $row++)
			for ($col = 0; $col < $this->arr[cols]; $col++)
			{
				$this->arr[contents][$row][$col] = new form_cell();		
				$this->arr[contents][$row][$col] -> load($this->id, $this->type, $row, $col, &$this->arr[elements]);
			}
	}

	////
	// !Makes sure the form conforms to a specified standard
	// the form mus at least have one element and row count and column count must be at least 1
	// if things are not ok, the form is recreated and saved
	function normalize()
	{
		// this makes sure that the map gets initialized properly
		if (!$this->arr[map][0][0]["row"])
		{
			$this->arr[map][0][0]["row"] = 0;
		}
		if (!$this->arr[map][0][0]["col"])
		{
			$this->arr[map][0][0]["col"] = 0;
		}

		if ($this->arr[cols] < 1)
		{
			$this->arr[cols] = 1;
		}
		if ($this->arr[rows] < 1)
		{
			$this->arr[rows] = 1;
		}

		if (!$this->arr[ff_folder])
		{
			$this->arr[ff_folder] = $this->parent;
		}
	}

	////
	// !Saves the form
	// saves the form settings, nothing else
	function save()
	{
		// we must do this, otherwise we also serialize all the cells and stuff, which isn't necessary
		for ($col = 0; $col < $this->arr[cols]; $col++)		
		{
			for ($row = 0; $row < $this->arr[rows]; $row++)
			{
				$this->arr[contents][$row][$col] = "";
			}
		}

		// we must also do this, because the column/row count may have changed
		$contents = serialize($this->arr);
		$this->quote(&$contents);

		$this->upd_object(array("oid" => $this->id,"name" => $this->name, "comment" => $this->comment));
		if (!$this->arr[cols])
		{
			$this->arr[cols] = 0;
		}
		if (!$this->arr[rows])
		{
			$this->arr[rows] = 0;
		}
		$this->db_query("UPDATE forms SET content = '$contents' , rows = ".$this->arr[rows]." , cols = ".$this->arr[cols]." WHERE id = ".$this->id);
		$this->_log("form","Muutis formi $this->name");
	}

	////
	// !Returns the colspan and rowspan of the specified cell from the specified map
	// used in showing/adminning the form
	// parameters:
	// $i - row
	// $i - column
	// $map - the map from which the spans are calculated
	// $rows - rows in the map
	// $cols - columns in the map
	// if $map or $rows or $cols are omitted, they are taken from $this
	function get_spans($i, $a, $map = -1,$rows = -1, $cols = -1)	// row, col
	{
		if ($map == -1)
			$map = $this->arr[map];
		if ($rows == -1)
			$rows = $this->arr[rows];
		if ($cols == -1)
			$cols = $this->arr[cols];

		// find if this cell is the top left one of the area
		$topleft = true;
		if ($i > 0)
		{
			if ($map[$i-1][$a][row] == $map[$i][$a][row])
				$topleft = false;
		}
		if ($a > 0)
		{
			if ($map[$i][$a-1][col] == $map[$i][$a][col])
				$topleft = false;
		}

		if ($topleft)
		{
			// if it is, then show the correct cell and set the col/rowspan to correct values
			for ($t_row=$i; $t_row < $rows && $map[$t_row][$a][row] == $map[$i][$a][row]; $t_row++)
				;

			for ($t_col=$a; $t_col < $cols && $map[$i][$t_col][col] == $map[$i][$a][col]; $t_col++)
				;

			$rowspan = $t_row - $i;
			$colspan = $t_col - $a;
				
			$this->vars(array("colspan" => $colspan, "rowspan" => $rowspan));
			if ($colspan > 1)
				$r_col = $map[$i][$a][col];
			else
				$r_col = $a;

			if ($rowspan > 1)
				$r_row = $map[$i][$a][row];
			else
				$r_row = $i;

			return array("colspan" => $colspan, "rowspan" => $rowspan, "r_row" => $r_row, "r_col" => $r_col);
		}
		else
		{
			// we return false if the cell is not the top-left cell of the area, because then we need to skip drawing it
			return false;
		}
	}

	////
	// !Loads form, template and generates description header
	function init($id, $tpl = "", $desc = "")
	{
		$this->load($id);
		if ($tpl != "")
		{
			$this->read_template($tpl);
		}
		if ($desc != "")
		{
			$this->mk_path($this->parent,$desc);
		}
	}

	////
	// !helper function. generates the formgen menu and returns the string. 
	// use instead of return $this->parse() in the end of functions
	function do_menu_return($st = "")
	{
		if ($st == "")
		{
			$st = $this->parse();
		}
		$this->reset();
		$this->read_template("menu.tpl");
		$this->do_menu();
		return $this->parse().$st;
	}

	////
	// !draws the formgen menu and makes the correct tab active. 
	function do_menu()
	{
		global $action,$op_id, $ext;

		$this->vars(array("form_id"					=> $this->id, 
											"change"					=> $this->mk_orb("change", array("id" => $this->id),"form"),
											"show"						=> $this->mk_orb("show", array("id" => $this->id),"form"),
											"table_settings"	=> $this->mk_orb("table_settings", array("id" => $this->id),"form"),
											"all_elements"		=> $this->mk_orb("all_elements", array("id" => $this->id),"form"),
											"list_op"					=> $this->mk_orb("list_op", array("id" => $this->id),"form_output"),
											"change_op"				=> $this->mk_orb("change_op", array("id" => $this->id, "op_id" => $op_id),"form_output"),
											"op_style"				=> $this->mk_orb("op_style", array("id" => $this->id, "op_id" => $op_id),"form_output"),
											"op_meta"					=> $this->mk_orb("op_meta", array("id" => $this->id, "op_id" => $op_id),"form_output"),
											"actions"					=> $this->mk_orb("list_actions", array("id" => $this->id),"form_actions"),
											"sel_search"			=> $this->mk_orb("sel_search", array("id" => $this->id), "form"),
											"metainfo"				=> $this->mk_orb("metainfo", array("id" => $this->id), "form")));

		if ($action == "change" || $action == "show" || $action == "all_elements")
		{
			$this->parse("GRID_SEL");
		}

		if ($action == "settings" || $action == "list_actions" || $action == "acl" || $action == "import_styles" || $action == "export_styles" || $action == "metainfo" || $action == "table_settings")
		{
			$this->parse("SETTINGS_SEL");
		}

		if ($action == "filled_forms" || $action == "import_contents" || $action == "change_entry" || $action == "show_entry")
		{
			$this->parse("FILLED_SEL");
		}

		if ($action == "op_list" || $action == "change_op" || $action == "add_pp" || $action == "output_grid" || $action == "output_settings" || $action == "output_meta")
		{
			$this->parse("OUTPUT_SEL");
			if ($action == "change_op" || $action == "output_settings" || $action == "output_meta")
			{
				$this->db_query("SELECT form_entries.id AS id
												 FROM form_entries 
												 LEFT JOIN objects ON objects.oid = form_entries.id
												 WHERE form_id = $this->id AND objects.status != 0
												 ORDER BY objects.modified");	// select all form entries under the form. well this sucks ass. we gotta select them all, cause
																							// we can't select only those that you can view in the sql. sloooooow. but what can I do?
				$entry_id = 0;
				while ($row = $this->db_next())
				{
					$entry_id = $row["id"];
					break;
				}

				$this->vars(array("op_id"				=> $op_id,
													"entry_id"		=> $entry_id,
													"op_preview"	=> $this->mk_orb("show_entry", array("id" => $this->id, "op_id" => $op_id, "entry_id" => $entry_id),"form")));
				$this->parse("OP_SEL");
			}
		}

		if ($this->type == 2)
		{
			$this->parse("SEARCH_SEL");
		}
		$this->parse("CAN_GRID");
		$this->parse("CAN_ALL");
		$this->parse("CAN_TABLE");
		$this->parse("CAN_META");

		$this->parse("CAN_PREVIEW");
		$this->parse("CAN_FILLED");

		$this->parse("CAN_ACL");

		$this->parse("CAN_IMPORT_DATA");	

		$this->parse("IMPORT_STYLES");	

		$this->parse("CAN_ACTION");

		$this->parse("M_EXPORT_STYLES");

		if ($this->type != 2)
		{
			$this->parse("OP_1");
		}

		$this->parse("FILLED_FORMS");

		$fm = $this->parse("FG_MENU");
		$this->vars(array("FG_MENU" => $fm));
	}

	function do_actions($entry_id)
	{
		$this->db_query("SELECT * FROM form_actions LEFT JOIN objects ON objects.oid = form_actions.id WHERE form_id = $this->id AND objects.status != 0");
		while($row = $this->db_next())
		{
			$this->save_handle();
			switch($row[type])
			{
				case "join_list":
					$data = unserialize($row[data]);
					classload("list");
					$li = new mlist($data["list"]);

					if ($this->entry[$data[checkbox]] == 1 || $data[checkbox] < 1)
						$li->db_add_user(array("name" => $this->entry[$data[name_tb]], "email" => $this->entry[$data[textbox]]));
					else
						$li->db_remove_user($this->entry[$data[textbox]]);
					break;

				case "email":
					$this->load_entry($entry_id);
					$msg = "";
					for ($r = 0; $r < $this->arr[rows]; $r++)
					{
						for ($c = 0; $c < $this->arr[cols]; $c++)
						{
							$elr = array();
							$this->arr[contents][$r][$c]->get_els(&$elr);
							reset($elr);
							while (list(,$v) = each($elr))
								$msg.=$v->gen_show_text();
						}
						$msg.="\n";
					}
					mail($row[data],"Tellimus AutomatWebist", $msg,"From: automatweb@automatweb.com\n");
					break;
			}
			$this->restore_handle();
		}
	}

	////
	// !loads the specified output for the currently loaded form
	function load_output($id)
	{
		$this->db_query("SELECT form_output.*, objects.name as name, objects.comment as comment
										 FROM form_output
										 LEFT JOIN objects ON objects.oid = form_output.id
										 LEFT JOIN acl ON acl.oid = objects.oid
										 WHERE form_output.id = $id
										 GROUP BY objects.oid");
		if (!($row = $this->db_next()))
		{
			$this->raise_error("form->load_output($id): no such record!",true);
		}

		$this->output = unserialize($row[op]);
		$this->vars(array("output_id" => $id));
		if ($this->output[cols] < 1 || $this->output[rows] < 1)
		{
			$this->output[cols] = 1;
			$this->output[rows] = 1;
			$this->output[map][0][0]=array("row" => 0, "col" => 0);
		}
		$this->output_id = $id;
	}

	////
	// !returns a list of forms, filtered by type
	function get_list($type)
	{
		$ret = array();
		$this->db_query("SELECT objects.name AS name,
					objects.comment AS comment,
					objects.oid AS oid,
					forms.type AS type,
					forms.subtype AS subtype,
					forms.grp AS grp,
					forms.j_order as j_order,
					forms.j_name AS j_name
				FROM forms
				LEFT JOIN objects ON objects.oid = forms.id
				WHERE objects.status != 0 AND forms.type = $type");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		return $ret;
	}

	////
	// !returns a list of all form_outputs
	function get_op_list()
	{
		global $SITE_ID;
		$ret = array();
		$this->db_query("SELECT oid,name,parent as form_id FROM objects WHERE class_id = ".CL_FORM_OUTPUT." AND status !=0 AND site_id = $SITE_ID");
		while ($row = $this->db_next())
		{
			$ret[$row["form_id"]][$row["oid"]] = $row["name"];
		}
		return $ret;
	}
}
?>
