<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_base.aw,v 2.16 2001/07/25 02:08:43 duke Exp $
// form_base.aw - this class loads and saves forms, all form classes should derive from this.
lc_load("automatweb");
lc_load("form");

class form_base extends aw_template
{
	function form_base()
	{
		$this->tpl_init("forms");
		$this->db_init();
		lc_load("definition");
		lc_load("form");
		global $lc_form;
		if (is_array($lc_form))
		{
			$this->vars($lc_form);
		}
		global $lc_automatweb;
		if (is_array($lc_automatweb))
		{
			$this->vars($lc_automatweb);
		}
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
		global $awt;
		$awt->start("form:load");
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

		$this->name = $row["name"];
		$this->id = $row["oid"];
		$this->parent = $row["parent"];
		$this->type = $row["type"];
		$this->comment = $row["comment"];
		$this->lang_id = $row["lang_id"];
		$this->entry_id = 0;

		if (substr($row["content"],0,14) == "<?xml version=")
		{
			classload("xml");
			$x = new xml;
			$this->arr = $x->xml_unserialize(array("source" => $row["content"]));
		}
		else
		{
			$this->arr = unserialize($row["content"]);
		}

		$this->normalize();
		$this->load_elements();
		$awt->stop("form:load");
	}

	////
	// !Loads form elements from database
	// loads elements as specified in $this->arr[elements]
	// form must be loaded previously
	// puts elements in $this->arr[contents]
	function load_elements()
	{
		for ($row = 0; $row < $this->arr["rows"]; $row++)
			for ($col = 0; $col < $this->arr["cols"]; $col++)
			{
				$this->arr["contents"][$row][$col] = new form_cell();		
				$this->arr["contents"][$row][$col] -> load(&$this,$row,$col);
			}
	}

	////
	// !Makes sure the form conforms to a specified standard
	// the form mus at least have one element and row count and column count must be at least 1
	// if things are not ok, the form is recreated and saved
	function normalize()
	{
		// this makes sure that the map gets initialized properly
		if (!$this->arr["map"][0][0]["row"])
		{
			$this->arr["map"][0][0]["row"] = 0;
		}
		if (!$this->arr["map"][0][0]["col"])
		{
			$this->arr["map"][0][0]["col"] = 0;
		}

		if ($this->arr["cols"] < 1)
		{
			$this->arr["cols"] = 1;
		}
		if ($this->arr["rows"] < 1)
		{
			$this->arr["rows"] = 1;
		}

		if (!$this->arr["ff_folder"])
		{
			$this->arr["ff_folder"] = $this->parent;
		}
	}

	////
	// !Saves the form
	// saves the form settings, nothing else
	function save()
	{
		// we must do this, otherwise we also serialize all the cells and stuff, which isn't necessary
		for ($col = 0; $col < $this->arr["cols"]; $col++)		
		{
			for ($row = 0; $row < $this->arr["rows"]; $row++)
			{
				$this->arr["contents"][$row][$col] = "";
			}
		}

		// we must also do this, because the column/row count may have changed
		classload("xml");
		$x = new xml;
		$contents = $x->xml_serialize($this->arr);

		$this->quote(&$contents);

		$this->upd_object(array("oid" => $this->id,"name" => $this->name, "comment" => $this->comment));
		if (!$this->arr["cols"])
		{
			$this->arr["cols"] = 0;
		}
		if (!$this->arr["rows"])
		{
			$this->arr["rows"] = 0;
		}
		$this->db_query("UPDATE forms SET content = '$contents' , rows = ".$this->arr["rows"]." , cols = ".$this->arr["cols"]." WHERE id = ".$this->id);
		$this->_log("form",sprintf(LC_FORM_BASE_CHANGED_FORM,$this->name));
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
			$map = $this->arr["map"];
		if ($rows == -1)
			$rows = $this->arr["rows"];
		if ($cols == -1)
			$cols = $this->arr["cols"];

		// find if this cell is the top left one of the area
		$topleft = true;
		if ($i > 0)
		{
			if ($map[$i-1][$a]["row"] == $map[$i][$a]["row"])
				$topleft = false;
		}
		if ($a > 0)
		{
			if ($map[$i][$a-1]["col"] == $map[$i][$a]["col"])
				$topleft = false;
		}

		if ($topleft)
		{
			// if it is, then show the correct cell and set the col/rowspan to correct values
			for ($t_row=$i; $t_row < $rows && $map[$t_row][$a]["row"] == $map[$i][$a]["row"]; $t_row++)
				;

			for ($t_col=$a; $t_col < $cols && $map[$i][$t_col]["col"] == $map[$i][$a]["col"]; $t_col++)
				;

			$rowspan = $t_row - $i;
			$colspan = $t_col - $a;
				
			$this->vars(array("colspan" => $colspan, "rowspan" => $rowspan));
			if ($colspan > 1)
				$r_col = $map[$i][$a]["col"];
			else
				$r_col = $a;

			if ($rowspan > 1)
				$r_row = $map[$i][$a]["row"];
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
		global $lc_form;
		if (is_array($lc_form))
		{
			$this->vars($lc_form);
		}
		global $lc_automatweb;
		if (is_array($lc_automatweb))
		{
			$this->vars($lc_automatweb);
		}
		$this->read_template("menu.tpl");
		$this->do_menu();
		return $this->parse().$st;
	}

	////
	// !draws the formgen menu and makes the correct tab active. 
	function do_menu()
	{
		global $action,$op_id, $ext;

		$this->vars(array(
			"form_id"					=> $this->id, 
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
			"metainfo"				=> $this->mk_orb("metainfo", array("id" => $this->id), "form"),
			"import_entries" => $this->mk_my_orb("import_form_entries", array("id" => $this->id),"form_import"),
			"set_folders" => $this->mk_my_orb("set_folders", array("id" => $this->id),"form"),
			"translate" => $this->mk_my_orb("translate", array("id" => $this->id),"form")
		));

		if ($action == "change" || $action == "show" || $action == "all_elements")
		{
			$this->parse("GRID_SEL");
		}

		if ($action == "settings" || $action == "list_actions" || $action == "acl" || $action == "import_styles" || $action == "export_styles" || $action == "metainfo" || $action == "table_settings" || $action == "set_folders" || $action=="translate")
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
			switch($row["type"])
			{
				case "join_list":
					$data = unserialize($row["data"]);
					classload("list");
					$li = new mlist($data["list"]);

					if ($this->entry[$data["checkbox"]] == 1 || $data["checkbox"] < 1)
					{
						$li->db_add_user(array("name" => $this->entry[$data["name_tb"]], "email" => $this->entry[$data["textbox"]]));
					}
					else
					{
						$li->db_remove_user($this->entry[$data["textbox"]]);
					};
					break;

				case "email":
					if ($GLOBALS["uid"] != "")
					{
						if (!is_array($jfes))
						{
							classload("users");
							$us = new users;
							$uif = $us->fetch($GLOBALS["uid"]);
							$jfes = unserialize($uif["join_form_entry"]);
						}

						if (is_array($jfes))
						{
							$app = LC_FORM_BASE_USER.$GLOBALS["uid"].LC_FORM_BASE_INFO;
							foreach($jfes as $fid => $eid)
							{
								$app.=$this->mk_my_orb("show", array("id" => $fid, "entry_id" => $eid),"form")."\n";
							}
						}
					}
					$this->load_entry($entry_id);
					$msg = $this->show_text();
					mail($row["data"],LC_FORM_BASE_ORDER_FROM_AW, $msg.$app,"From: automatweb@automatweb.com\n");
					break;
			}
			$this->restore_handle();
		}
	}

	////
	// !generates a plain-text representation of the loaded entry for the loaded form, suitable for e-mailing
	function show_text()
	{
		$msg = "";
		for ($r = 0; $r < $this->arr["rows"]; $r++)
		{
			$msg.=$this->mk_show_text_row($r)."\n";
		}
		return $msg;
	}

	////
	// !generates row $r of the plain-text representation of the loaded entry for the loaded form 
	function mk_show_text_row($r)
	{
		$msg = "";
		for ($c = 0; $c < $this->arr["cols"]; $c++)
		{
			$elr = array();
			$this->arr["contents"][$r][$c]->get_els(&$elr);
			reset($elr);
			while (list(,$v) = each($elr))
				$msg.=$v->gen_show_text();
		}
		return $msg;
	}

	function get_op($id)
	{
		$this->db_query("SELECT form_output.*,objects.* FROM objects LEFT JOIN form_output ON form_output.id = objects.oid WHERE objects.oid = $id");
		return $this->db_next();
	}

	////
	// !loads the specified output for the currently loaded form
	function load_output($id)
	{
		if (!($row = $this->get_op($id)))
		{
			$this->raise_error(sprintf(LC_FORM_BASE_NO_SUCH_FORM,$id),true);
		}

		classload("xml");
		$x = new xml;
		$this->output = $x->xml_unserialize(array("source" => $row["op"]));
		$this->vars(array("output_id" => $id));
		if (!isset($this->output["cols"]) || $this->output["cols"] < 1 || !isset($this->output["rows"]) || $this->output["rows"] < 1)
		{
			$this->output["cols"] = 1;
			$this->output["rows"] = 1;
			$this->output["map"][0][0]=array("row" => 0, "col" => 0);
		}
		$this->output_id = $id;
		$this->name = $row["name"];
		$this->comment = $row["comment"];
		$this->parent = $row["parent"];
	}

	////
	// !returns a list of forms, filtered by type, wrapper for get_flist
	// arguments:
	// type(int) - listitavate vormide tüüp
	// addempty(bool) - kas lisada tagastatava array algusse tühi element?
	// onlyactive(bool) - whether to list only active forms?
	function get_flist($args = array())
	{
		global $awt;
		$awt->start("get_flist");
		extract($args);

		$ret = ($addempty) ? array("0" => "") : array();
		$st = ($onlyactive) ? " = 2" : "!= 0";

		$q = sprintf("	SELECT
					objects.name AS name,
					objects.oid AS oid
				FROM forms
				LEFT JOIN objects ON objects.oid = forms.id
				WHERE objects.status %s AND forms.type = %d",
				$st,$type);
		
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		$awt->stop("get_flist");
		return $ret;
	}

	////
	// !returns a list of forms, filtered by type, wrapper for get_flist
	function get_list($type,$addempty = false,$onlyactive = false)
	{
		return $this->get_flist(array(
				"type" => $type,
				"addempty" => $addempty,
				"onlyactive" => $onlyactive,
		));
	}

	////
	// !returns a list of all form_outputs
	function get_op_list()
	{
		global $SITE_ID;
		$ret = array();
		$this->db_query("SELECT op_id,objects.name as name,form_id FROM output2form LEFT JOIN objects ON objects.oid = output2form.op_id WHERE class_id = ".CL_FORM_OUTPUT." AND status !=0 AND site_id = $SITE_ID");
		while ($row = $this->db_next())
		{
			$ret[$row["form_id"]][$row["op_id"]] = $row["name"];
		}
		return $ret;
	}

	////
	// !returns an array of all forms for output $op_id
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
	// !adds a column to map $map with dimensions $rows / $cols , after col $after
	function map_add_col($rows,$cols,&$map,$after)
	{
		$nm = array();
		for ($row =0; $row < $rows; $row++)
		{
			for ($col=0; $col <= $after; $col++)
			{
				$nm[$row][$col] = $map[$row][$col];		// copy the left part of the map
			}
		}

		$change = array();
		for ($row = 0; $row < $rows; $row++)
		{
			for ($col=$after+1; $col < ($cols-1); $col++)
			{
				if ($map[$row][$col]["col"] > $after)	
				{
					$nm[$row][$col+1]["col"] = $map[$row][$col]["col"]+1;
					$nm[$row][$col+1]["row"] = $map[$row][$col]["row"];
					$change[] = array("from" => $map[$row][$col], "to" => $nm[$row][$col+1]);
				}
				else
				{
					$nm[$row][$col+1] = $map[$row][$col];
				}
			}
		}

		reset($change);
		while (list(,$v) = each($change))
		{
			for ($row=0; $row < $rows; $row++)
			{
				for ($col=0; $col <= $after; $col++)
				{
					if ($map[$row][$col] == $v["from"])
					{
						$nm[$row][$col] = $v["to"];
					}
				}
			}
		}

		for ($row = 0; $row < $rows; $row++)
		{
			if ($map[$row][$after] == $map[$row][$after+1])
			{
				$nm[$row][$after+1] = $nm[$row][$after];
			}
			else
			{
				$nm[$row][$after+1] = array("row" => $row, "col" => $after+1);
			}
		}

		$map = $nm;
	}

	////
	// !deletes col $d_col from map $map with dimensions [$rows x $cols]
	function map_del_col($rows,$cols,&$map,$d_col)
	{
		$nm = array();
		for ($row =0; $row < $rows; $row++)
		{
			for ($col=0; $col < $d_col; $col++)
			{
				$nm[$row][$col] = $map[$row][$col];	// copy the left part of the map
			}
		}

		// shit. I remember doing this gave me a really bad headache. 
		// .. and now, 6 months later I can understand why :p

		$changes = array();
		for ($row =0 ; $row < $rows; $row++)
		{
			for ($col = $d_col+1; $col < $cols; $col++)
			{
				if ($map[$row][$col]["col"] > $d_col)
				{
					$nm[$row][$col-1] = array("row" => $map[$row][$col]["row"], "col" => $map[$row][$col]["col"]-1);
					$changes[] = array("from" => $map[$row][$col], 
														 "to" => array("row" => $map[$row][$col]["row"], "col" => $map[$row][$col]["col"]-1));
				}
				else
				{
					$nm[$row][$col-1] = $map[$row][$col];
				}
			}
		}
		$map = $nm;
		
		reset($changes);
		while (list(,$v) = each($changes))
		{
			for ($row=0; $row < $rows; $row++)
			{
				for ($col=0; $col < $d_col; $col++)
				{
					if ($map[$row][$col] == $v["from"])
					{
						$map[$row][$col] = $v["to"];
					}
				}
			}
		}
	}

	////
	// !adds a row to the map $map [$rows x $cols] , after row $after
	function map_add_row($rows,$cols,&$map,$after)
	{
		$nm = array();
		for ($row =0; $row <= $after; $row++)
		{
			for ($col=0; $col < $cols; $col++)
			{
				$nm[$row][$col] = $map[$row][$col];		// copy the upper part of the map
			}
		}

		$change = array();
		for ($row = $after+1; $row < ($rows-1); $row++)
		{
			for ($col=0; $col < $cols; $col++)
			{
				if ($map[$row][$col]["row"] > $after)	
				{
					$nm[$row+1][$col]["col"] = $map[$row][$col]["col"];
					$nm[$row+1][$col]["row"] = $map[$row][$col]["row"]+1;
					$change[] = array("from" => $map[$row][$col], "to" => $nm[$row+1][$col]);
				}
				else
				{
					$nm[$row+1][$col] = $map[$row][$col];
				}
			}
		}

		reset($change);
		while (list(,$v) = each($change))
		{
			for ($row=0; $row <= $after; $row++)
			{
				for ($col=0; $col < $cols; $col++)
				{
					if ($map[$row][$col] == $v["from"])
					{
						$nm[$row][$col] = $v["to"];
					}
				}
			}
		}

		for ($col = 0; $col < $cols; $col++)
		{
			if ($map[$after][$col] == $map[$after+1][$col])
			{
				$nm[$after+1][$col] = $nm[$after][$col];
			}
			else
			{
				$nm[$after+1][$col] = array("row" => $after+1, "col" => $col);
			}
		}

		$map = $nm;
	}

	////
	// !deletes row $d_row of map $map [$rows x $cols]
	function map_del_row($rows,$cols,&$map,$d_row)
	{
		$nm = array();
		for ($row =0; $row < $d_row; $row++)
		{
			for ($col=0; $col < $cols; $col++)
			{
				$nm[$row][$col] = $map[$row][$col];	// copy the upper part of the map
			}
		}

		$changes = array();
		for ($row =$d_row+1 ; $row < $rows; $row++)
		{
			for ($col = 0; $col < $cols; $col++)
			{
				if ($map[$row][$col]["row"] > $d_row)
				{
					$nm[$row-1][$col] = array("row" => $map[$row][$col]["row"]-1, "col" => $map[$row][$col]["col"]);
					$changes[] = array("from" => $map[$row][$col], 
														 "to" => array("row" => $map[$row][$col]["row"]-1, "col" => $map[$row][$col]["col"]));
				}
				else
				{
					$nm[$row-1][$col] = $map[$row][$col];
				}
			}
		}
		$map = $nm;
		
		reset($changes);
		while (list(,$v) = each($changes))
		{
			for ($row=0; $row < $d_row; $row++)
			{
				for ($col=0; $col < $cols; $col++)
				{
					if ($map[$row][$col] == $v["from"])
					{
						$map[$row][$col] = $v["to"];
					}
				}
			}
		}
	}

	////
	// !merges the cell ($row,$col) of map $map with the cell above it
	function map_exp_up($rows,$cols,&$map,$row,$col)
	{
		// here we don't need to find the upper bound, because this always is the upper bound
		if ($row > 0)
		{
			// first we must find out the colspan of the current cell and set all the cell above that one to the correct values in the map
			for ($a=0; $a < $cols; $a++)
			{
				if ($map[$row][$a] == $map[$row][$col])
				{
					$map[$row-1][$a] = $map[$row][$col];		// expand the area
				}
			}
		}
	}

	////
	// !merges the cell ($row,$col) in map $map, with the cell below it
	function map_exp_down($rows,$cols,&$map,$row,$col)
	{
		// here we must first find the lower bound for the area being expanded and use that instead the $row, because
		// that is an arbitrary position in the area really.
		for ($i=$row; $i < $rows; $i++)
		{
			if ($map[$i][$col] == $map[$row][$col])
			{
				$r=$i;
			}
			else
			{
				break;
			}
		}

		if (($r+1) < $rows)
		{
			for ($a=0; $a < $cols; $a++)
			{
				if ($map[$row][$a] == $map[$row][$col])
				{
					$map[$r+1][$a] = $map[$row][$col];		// expand the area
				}
			}
		}
	}

	////
	// !merges the cell ($row,$col) in map $map with the cell to the left of it
	function map_exp_left($rows,$cols,&$map,$row,$col)
	{
		// again, this is the left bound, so we don't need to find it
		if ($col > 0)
		{
			for ($a =0; $a < $rows; $a++)
			{
				if ($map[$a][$col] == $map[$row][$col])
				{
					$map[$a][$col-1] = $map[$row][$col];		// expand the area
				}
			}
		}
	}

	////
	// !merges the cell ($row,$col) of map $map with the cell to the right of it
	function map_exp_right($rows,$cols,&$map,$row,$col)
	{
		// here we must first find the right bound for the area being expanded and use that instead the $row, because
		// that is an arbitrary position in the area really.
		for ($i=$col; $i < $cols; $i++)
		{
			if ($map[$row][$i] == $map[$row][$col])
			{
				$r=$i;
			}
			else
			{
				break;
			}
		}

		if (($r+1) < $cols)
		{
			for ($a =0; $a < $rows; $a++)
			{
				if ($map[$a][$r] == $map[$row][$r])
				{
					$map[$a][$r+1] = $map[$row][$r];		// expand the area
				}
			}
		}
	}

	////
	// !splits the cell at $row,$col on map $map vertically
	function map_split_ver($rows,$cols,&$map,$row,$col)
	{
		$lbound = -1;
		for ($i=0; $i < $cols && $lbound==-1; $i++)
		{
			if ($map[$row][$i] == $map[$row][$col])
			{
				$lbound = $i;
			}
		}

		$rbound = -1;
		for ($i=$lbound; $i < $cols && $rbound==-1; $i++)
		{
			if ($map[$row][$i] != $map[$row][$col])
			{
				$rbound = $i-1;
			}
		}

		if ($rbound == -1)
		{
			$rbound = $cols-1;
		}

		$nm = array();
		$center = ($rbound+$lbound)/2;

		for ($i=0; $i < $rows; $i++)
		{
			for ($a=0; $a < $cols; $a++)
			{
				if ($map[$i][$a] == $map[$row][$col])
				{
					if ($map[$i][$a]["col"] < $center)	
					{
						// the hotspot of the cell is on the left of the splitter
						if ($a <= $center)	
						{
							// and we currently are also on the left side then leave it be
							$nm[$i][$a] = $map[$i][$a];
						}
						else
						{
							// and we are on the right side choose a new one
							$nm[$i][$a] = array("row" => $map[$i][$a]["row"], "col" => floor($center)+1);
						}
					}
					else
					{
						// the hotspot of the cell is on the right of the splitter
						if ($a <= $center)
						{
							// and we are on the left side choose a new one
							$nm[$i][$a] = array("row" => $map[$i][$a]["row"], "col" => $lbound);
						}
						else
						{
							// if we are on the same side, use the current value
							$nm[$i][$a] = $map[$i][$a];
						}
					}	
				}
				else
				{
					$nm[$i][$a] = $map[$i][$a];
				}
			}
		}

		$map = $nm;
	}

	function map_split_hor($rows,$cols,&$map,$row,$col)
	{
		$ubound = -1;
		for ($i=0; $i < $rows && $ubound==-1; $i++)
		{
			if ($map[$i][$col] == $map[$row][$col])
			{
				$ubound = $i;
			}
		}

		$lbound = -1;
		for ($i=$ubound; $i < $rows && $lbound==-1; $i++)
		{
			if ($map[$i][$col] != $map[$row][$col])
			{
				$lbound = $i-1;
			}
		}

		if ($lbound == -1)
		{
			$lbound = $rows-1;
		}

		$nm = array();
		$center = ($ubound+$lbound)/2;

		for ($i=0; $i < $rows; $i++)
		{
			for ($a=0; $a < $cols; $a++)
			{
				if ($map[$i][$a] == $map[$row][$col])
				{
					if ($map[$i][$a]["row"] < $center)	
					{
						// the hotspot of the cell is above the splitter
						if ($i <= $center)	
						{
							// and we currently are also above then leave it be
							$nm[$i][$a] = $map[$i][$a];
						}
						else
						{
							// and we are below choose a new one
							$nm[$i][$a] = array("row" => floor($center)+1, "col" => $map[$i][$a]["col"]);
						}
					}
					else
					{
						// the hotspot of the cell is below the splitter
						if ($i <= $center)
						{
							// but we are above, so make new
							$nm[$i][$a] = array("row" => $ubound, "col" => $map[$i][$a]["col"]);
						}
						else
						{
							// if we are on the same side, use the current value
							$nm[$i][$a] = $map[$i][$a];
						}
					}	
				}
				else
				{
					$nm[$i][$a] = $map[$i][$a];
				}
			}
		}

		$map = $nm;
	}

	function load_table($id)
	{
		$this->db_query("SELECT objects.*,form_tables.* FROM objects LEFT JOIN form_tables ON form_tables.id = objects.oid WHERE oid = $id");
		$row = $this->db_next();
		$this->table_name = $row["name"];
		$this->table_comment = $row["comment"];
		$this->table_id = $id;
		$this->table_parent = $row["parent"];

		classload("xml");
		$x = new xml;

		$this->table = $x->xml_unserialize(array("source" => $row["content"]));
		$this->table["cols"] = $row["num_cols"];

		if ($this->table["cols"] < 1)
		{
			$this->table["cols"] = 1;
		}
	}

	////
	// !returns an array of id => name of all elements in the forms whose id's are in $arr
	function get_elements_for_forms($arr)
	{
		$ret = array();
		$sss = join(",",$arr);
		if ($sss != "")
		{
			$this->db_query("SELECT el_id,objects.name as name FROM element2form LEFT JOIN objects ON objects.oid = element2form.el_id WHERE element2form.form_id IN (".$sss.")");
			while ($row = $this->db_next())
			{
				$ret[$row["el_id"]] = $row["name"];
			}
		}
		return $ret;
	}

	////
	// !returns an array of elements for a form, (including id-s, types, 'n stuff)
	// I realize that this is slow, but you're welcome to improve this
	// arguments:
	// id(int) - id of the form, which we are to load
	function get_form_elements($args = array())
	{
		extract($args);
		$this->load($id);
		$retval = array();
		for ($i = 0; $i < $this->arr["rows"]; $i++)
		{
			$cols = "";
			for ($j = 0; $j < $this->arr["cols"]; $j++) 
			{
				// kui see cell on mone teise "all", siis jätame
				// ta lihtsalt vahele
				if (!($arr = $this->get_spans($i, $j)))
				{
					continue;
				}

				$cell = &$this->arr["contents"][$arr["r_row"]][$arr["r_col"]];
				$els = $cell->get_elements();
				foreach($els as $key => $val)
				{
					if ($val["name"])
					{
						// we only want elements with names, the rest
						// is probably just captions 'n stuff
						$retval[$val["name"]] = $val;
					};
				};
			}
		}
		return $retval;
	}

	////
	// !returns an array of all form tables
	function get_list_tables()
	{
		$ret = array();
		$this->db_query("SELECT oid,name FROM objects WHERE class_id = ".CL_FORM_TABLE." AND status != 0");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		return $ret;
	}

	////
	// !returns an array of form_id => entry_id for the given chain entry id
	function get_chain_entry($entry_id)
	{
		$this->db_query("SELECT * FROM form_chain_entries WHERE id = $entry_id");
		$row = $this->db_next();
		classload("xml");
		$x = new xml;
		return $x->xml_unserialize(array("source" => $row["ids"]));
	}

	////
	// !loads form chain $id into $this->chain
	function load_chain($id)
	{
		$this->db_query("SELECT objects.*, form_chains.* FROM objects LEFT JOIN form_chains ON objects.oid = form_chains.id WHERE objects.oid = $id");
		$row = $this->db_next();
		classload("xml");
		$x = new xml;
		$this->chain = $x->xml_unserialize(array("source" => $row["content"]));
		return $row;
	}

	function get_type()
	{
		return $this->type;
	}

	function get_id()
	{
		return $this->id;
	}

	function get_parent()
	{
		return $this->parent;
	}

	function &get_el_arr()
	{
		return $this->arr["elements"];
	}

	function listall_el_types($addempty = false)
	{
		if ($addempty)
		{
			$ret = array(0 => "");
		}
		else
		{
			$ret = array();
		}
		$this->db_query("SELECT * FROM form_elements WHERE type_name != ''");
		while ($row = $this->db_next())
		{
			$ret[$row["id"]] = $row["type_name"];
		}
		return $ret;
	}

	////
	// !creates a list of all elements to be put in a listbox for the suer to select which one he wants to add
	function listall_elements()
	{
		$this->db_query("SELECT objects.oid as oid, 
														objects.parent as parent,
														objects.name as name
											FROM objects 
											LEFT JOIN menu ON menu.id = objects.oid
											WHERE objects.class_id = 1 AND objects.status != 0 
											GROUP BY objects.oid
											ORDER BY objects.parent, jrk");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row;
		}
		
		// teeme olemasolevatest elementidest array
		$elarr = array();
		for ($row = 0; $row < $this->form->arr["rows"]; $row++)
		{
			for ($col = 0; $col < $this->form->arr["cols"]; $col++)
			{
				if (is_array($this->form->arr["elements"][$row][$col]))
				{
					reset($this->form->arr["elements"][$row][$col]);
					while (list($eid,) = each($this->form->arr["elements"][$row][$col]))
					{
						$elarr[$eid] = $eid;
					}
				}
			}
		}

		$check_parent = false;
		if (is_array($this->form->arr["el_menus2"]) && count($this->form->arr["el_menus2"]) > 0)
		{
			$check_parent = true;
		}

		$ar = array(0 => "");
		$this->db_query("SELECT oid,name,parent FROM objects WHERE objects.class_id= ".CL_FORM_ELEMENT." AND status != 0 ");
		while ($row = $this->db_next())
		{
			if (!$elarr[$row["oid"]])
			{
				if ($check_parent)
				{
					// if this element does not exist in this form yet
					// add it to the select list.
					if (in_array($row["parent"],$this->form->arr["el_menus2"]))
					{
						$ar[$row["oid"]] = $this->mk_element_path(&$ret,&$row).$row["name"];
					}
				}
				else
				{
						$ar[$row["oid"]] = $this->mk_element_path(&$ret,&$row).$row["name"];
				}
			}
		}

		return $ar;
	}

	////
	// !creats the full path of an element from an array of all menus ($arr) and the record of the element ($el)
	function mk_element_path(&$arr, &$el)
	{
		$parent = $el["parent"];
		$ret = "";
		while ($parent > 1)
		{
			$ret =$arr[$parent]["name"]."/".$ret;
			$parent = $arr[$parent]["parent"];
		}
		return $ret;
	}

	function get_chains_for_form($fid)
	{
		$ret = array();
		$this->db_query("SELECT chain_id FROM form2chain LEFT JOIN objects ON objects.oid = form2chain.chain_id WHERE form_id = $fid AND objects.status != 0");
		while ($row = $this->db_next())
		{
			$ret[$row["chain_id"]] = $row["chain_id"];
		}
		return $ret;
	}

	function get_forms_for_chain($chid)
	{
		$ret = array();
		$this->db_query("SELECT form_id FROM form2chain WHERE chain_id = $chid ORDER BY ord");
		while ($row = $this->db_next())
		{
			$ret[$row["form_id"]] = $row["form_id"];
		}
		return $ret;
	}
}
?>
