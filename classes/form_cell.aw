<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_cell.aw,v 2.6 2001/06/13 03:35:24 kristo Exp $

// ysnaga. asi peab olema nii lahendatud, et formi juures on elemendi properitd kirjas
// st forms.contents sees on ka selle elemendi propertid selle fomi sees kirjas
// et saax igale formile eraldi elemendi properteid panna
// JA elemendi juures on kirjas, et mis formide sees selle element on. 
// see on sellex, et siis kui on vaja teha nimekirja t2idetud formidest, kus see element sees on, siis
// saab sealt selle kohe teada. 

// no public interface here. everything goes through form.aw
// why? iizi. cause the element properies are saved in the form and therefore the form must always be loaded
// so we have to go through form->load to get the cells' elements.
class form_cell extends aw_template
{
	function form_cell()
	{
		$this->tpl_init("forms");
		$this->db_init();
		$this->sub_merge = 1;
	}

	////
	// !ElementFactory for all you fancy people ;)
	// creates the correct element based on form type
	function mk_element($type, &$r)
	{
		switch($type)
		{
			case FORM_ENTRY:
				$tmp = new form_entry_element();
				break;
			case FORM_SEARCH:
				$tmp = new form_search_element();
				break;
			case FORM_RATING:
				$tmp = new form_rating_element();
				break;
			default:
				$this->raise_error("form_cell->mk_element($type) , error in type!",true);
		}
		$tmp->load(&$r,$this->id,$this->col, $this->row);
		$this->arr[$this->cnt] = $tmp;
		$this->cnt++;
	}

	function load($id, $type, $row, $col, $arr)
	{
		$this->type = $type;
		$this->col = $col;
		$this->row = $row;
		$this->id = $id;
		$this->cnt = 0;

		$obj = $this->get_object($this->id);
		$this->parent = $obj[parent];

		if (is_array($arr[$row][$col]))
		{
			reset($arr[$row][$col]);
			while (list($k,$v) = each($arr[$row][$col]))
			{
				if (is_number($k))
				{
					$this->mk_element($this->type, &$v);
				}
			}
		}

		$this->style = $arr[$row][$col][style];
	}

	////
	// !Displays cell administration form for previously loaded cell
	function admin_cell()
	{
		$this->read_template("admin_cell.tpl");
		$this->mk_path($this->parent, "<a href='".$this->mk_orb("change", array("id" => $this->id),"form")."'>Muuda formi</a> / Muuda celli");

		classload("objects");
		$o = new db_objects;
		$this->vars(array("footer" =>"",
											"add_element" => $this->mk_orb("add_element", array("id" => $this->id, "col" => $this->col, "row" => $this->row, "after" => -1)),
											"cell_style"	=> $this->mk_orb("sel_cell_style", array("id" => $this->id, "col" => $this->col, "row" => $this->row),"form"),
											"sections" => $this->picker($this->parent, $o->get_list())));

		$this->vars(array("form_id" => $this->id, "form_col" => $this->col, "form_row" => $this->row, "parent" => $this->id));

		for ($i=0; $i < $this->cnt; $i++)
		{
			$this->vars(array("after" => $this->arr[$i]->get_id(),"element" => $this->arr[$i]->gen_admin_html(&$this)));
			$this->vars(array("EL_NLAST" => ($i == ($this->cnt-1) ? "" : $this->parse("EL_NLAST"))));
			$this->vars(array("EL_ADD" => (1 ? $this->parse("EL_ADD") : ""),
												"EL_ACL" => (1 ? $this->parse("EL_ACL") : "")));

			$this->parse("ELEMENT_LINE");
		}

		$this->vars(array("add_el" => $this->mk_orb("add_element", array("id" => $this->id, "row" => $this->row, "col" => $this->col),"form")));

		$ca = $this->parse("CAN_ADD");

		$caa = $this->parse("CAN_ACTION");

		$this->vars(array("CAN_ADD" => $ca,"CAN_ACTION"=>$caa,
											"reforb"	=> $this->mk_reforb("submit_cell", array("id" => $this->id, "row" => $this->row, "col" => $this->col),"form")));
		return $this->parse();
	}

	function get_elements()
	{
		$ret = array();
		for ($i=0; $i < $this->cnt; $i++)
		{
			$ret[$i][text] = $this->arr[$i]->get_text();
			$ret[$i][name] = $this->arr[$i]->get_el_name();
			$ret[$i][type] = $this->arr[$i]->get_type();
			$ret[$i][id] = $this->arr[$i]->get_id();
			$ret[$i][order] = $this->arr[$i]->get_order();
		}
		return $ret;
	}

	////
	// !this is called when the form grid is saved, and we must only save the name and order of elements
	function save_short(&$form)
	{
		for ($i=0; $i < $this->cnt; $i++)
		{
			$this->arr[$i]->save_short();
			$form->arr[elements][$this->row][$this->col][$this->arr[$i]->get_id()] = $this->arr[$i]->get_props();
		}
	}

	////
	// !Adds a new element in the folder $parent and associates it with the currently loaded form also. 
	function add_element($wizard_step,&$form)
	{
		$this->mk_path($this->parent, "<a href='".$this->mk_orb("change", array("id" => $this->id),"form")."'>Muuda formi</a> / Lisa element");
		if (!$wizard_step)
		{
			$this->read_template("add_el_wiz1.tpl");

			classload("objects");
			$o = new db_objects;
			$this->vars(array("reforb"		=> $this->mk_reforb("add_element", array("id" => $this->id, "row" => $this->row, "col" => $this->col,"wizard_step" => 1),"form"),
												"folders"		=> $this->picker($this->parent, $o->get_list()),
												"elements"	=> $this->picker(0,$this->listall_elements(&$form))));
			return $this->parse();
		}
		else
		{
			global $HTTP_POST_VARS;
			extract($HTTP_POST_VARS);

			if ($type == "add")
			{
				// add new element
				// form elements are weird things.
				// namely. they are at the same time menus AND form elements. 
				// so each element is written in three places
				// objects table, class_id = CL_PSEUDO 
				// menu table with type MN_FORM_ELEMENT
				// form_elements table. this just contains info in which forms the element is inserted into
				// and the actual info about how the element is to be shown is written into the form's array. whee. 

				$el = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_PSEUDO));
				$this->db_query("INSERT INTO menu (id,type) values($el,".MN_FORM_ELEMENT.")");
				$this->db_query("INSERT INTO form_elements (id) VALUES($el)");
			}
			else
			{
				if ($el)
				{
					$oo = $this->get_object($el);
					$name = $oo[name];
					$ord = $oo[jrk];
				}
			}
		

			if ($el)
			{
				$this->_do_add_element($this->id,$el);

				// add the element into the form.
				$form->arr[elements][$this->row][$this->col][$el] = array("id" => $el,"name" => $name,"ord" => $ord);
				$form->save();
			}
			return false;
		}
	}

	function _do_add_element($fid,$el)
	{
		// we must also update the form_$id_entries table
		$this->db_query("ALTER TABLE form_".$fid."_entries add el_$el text");

		// and add this form to the list of forms in which the element is
		$this->db_query("SELECT * FROM form_elements WHERE id = ".$el);
		$row = $this->db_next();
		$ra = unserialize($row["forms"]);
		$ra[$fid] = $fid;
		$rs = serialize($ra);
		$this->db_query("UPDATE form_elements SET forms = '$rs' WHERE id = ".$el);
	}

	function admin_cell_actions()
	{
		if (!$this->facl->get(can_action))
			$this->raise_error("ACL ERROR: You do not have access to do this! (admin_cell_actions)",true);

		$this->read_template("admin_cell_actions.tpl");
		$c = "";
		for ($i=0; $i < $this->cnt; $i++)
			$c.=$this->arr[$i]->gen_action_html();
		$this->vars(array("elements" => $c, "form_id" => $this->id, "form_col" => $this->col, "form_row" => $this->row, "parent" => $this->id));
		return $this->parse();
	}

	//// 
	// !deletes all the elements in this cell
	function del()
	{
		for ($i=0; $i < $this->cnt; $i++)
			$this->arr[$i]->del();
	}

	function gen_user_html_not($def_style, &$images, $colspan, $rowspan,$prefix = "",$elvalues)
	{
		$el = 0;
		$c = "";
		for ($i=0; $i < $this->cnt; $i++)
		{
			$c.=$this->arr[$i]->gen_user_html_not(&$images,$prefix,$elvalues);
			$el = &$this->arr[$i];
		}
		if ($c == "")
			$c = "<img src='/images/transa.gif' height=1 width=1 border=0>";

		$style_id=$this->style;
		if (!$style_id)
		{
			$style_id = $def_style;
		}

		$stc = new style;
		if ($style_id)
			$cs.= $stc->get_cell_begin_str($style_id,$colspan,$rowspan);
		else
			$cs .= "<td colspan=\"".$colspan."\" rowspan=\"".$rowspan."\">";

		$cs.= $c;

		if ($style_id)
			$cs.= $stc->get_cell_end_str($style_id);

		$cs.= "</td>";
		
		return $cs;
	}

	function process_entry(&$entry, $id,$prefix = "")
	{
		for ($i=0; $i < $this->cnt; $i++)
			$this->arr[$i] -> process_entry(&$entry, $id,$prefix);
	}

	function set_entry(&$arr, $e_id)
	{
		for ($i=0; $i < $this->cnt; $i++)
			$this->arr[$i] -> set_entry(&$arr, $e_id);
	}

	function get_els(&$arr)
	{
		if (!is_array($arr))
		{
			$arr = array();
		}
		for ($i=0; $i < $this->cnt; $i++)
		{
			$arr[] = &$this->arr[$i];
		}
	}

	function get_style()
	{
		return $this->style;
	}

	function set_style($id,&$form)
	{
		$form->arr[elements][$this->row][$this->col][style] = $id;
	}

	function set_mark($mk)
	{
		for ($i=0; $i < $this->cnt; $i++)
			$this->arr[$i] -> set_mark($mk);
	}

	function admin_cell_controllers()
	{
		if (!$this->facl->get(can_edit))
			$this->raise_error("ACL ERROR: You do not have access to do this! (can_edit)",true);

		$this->read_template("admin_cell_controllers.tpl");

		$caa = "";
		if ($this->facl->get(can_action))
			$caa = $this->parse("CAN_ACTION");

		$c = "";
		for ($i=0; $i < $this->cnt; $i++)
			$c.=$this->arr[$i]->gen_controller_html();
		$this->vars(array("elements" => $c, "form_id" => $this->id, "form_col" => $this->col, "form_row" => $this->row, "parent" => $this->id,"CAN_ACTION" => $caa));
		return $this->parse();
	}

	function gen_parent_arr()
	{
		reset($this->parents);
		$first = true;
		while(list(, $v) = each($this->parents))
		{
			if($first)
				$sql = " ( parent = ".$v[id]." ";
			else
				$sql .= " OR parent = ".$v[id]." ";
			$first = false;
		}
		return $sql." ) ";
	}

	////
	// !generates the form for selecting cell style
	function pickstyle()
	{
		$this->mk_path($this->parent,"<a href='".$this->mk_orb("change",array("id" => $this->id),"form")."'>Muuda formi</a> / <a href='".$this->mk_orb("admin_cell",array("id" => $this->id, "row" => $this->row, "col" => $this->col),"form")."'>Muuda celli</a> / Vali stiil");
		$this->read_template("pickstyle.tpl");

		classload("style");
		$t = new style;

		$this->vars(array("reforb" => $this->mk_reforb("save_cell_style", array("id" => $this->id, "col" => $this->col, "row" => $this->row),"form"),
											"stylessel"	=> $this->option_list($this->get_style(),$t->get_select(0,ST_CELL))));
		return $this->parse();
	}

	////
	// !creats the full path of an element from an array of all menus ($arr) and the record of the element ($el)
	function mk_element_path(&$arr, &$el)
	{
		$parent = $el[parent];
		$ret = "";
		while ($parent > 1)
		{
			$ret =$arr[$parent][name]."/".$ret;
			$parent = $arr[$parent][parent];
		}
		return $ret;
	}

	////
	// !creates a list of all elements to be put in a listbox for the suer to select which one he wants to add
	function listall_elements(&$form)
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
				$ret[$row[oid]] = $row;
		
		// teeme olemasolevatest elementidest array
		$elarr = array();
		for ($row = 0; $row < $form->arr[rows]; $row++)
		{
			for ($col = 0; $col < $form->arr[cols]; $col++)
			{
				if (is_array($form->arr[elements][$row][$col]))
				{
					reset($form->arr[elements][$row][$col]);
					while (list($eid,) = each($form->arr[elements][$row][$col]))
					{
						$elarr[$eid] = $eid;
					}
				}
			}
		}
		$ar = array(0 => "");
		$this->db_query("SELECT objects.*, form_elements.* FROM form_elements LEFT JOIN objects ON objects.oid = form_elements.id WHERE objects.status != 0");
		while ($row = $this->db_next())
		{
			if (!$elarr[$row["oid"]])
			{
				// if this element does not exist in this form yet
				// add it to the select list.
				$ar[$row[oid]] = $this->mk_element_path(&$ret,&$row).$row[name];
			}
		}

		return $ar;
	}

	////
	// !saves the elements in this cell
	function submit_cell(&$arr,&$form)
	{
		$this->dequote(&$arr);
		// gather all properties of the elements in the cell in their arrays from the submitted form
		// and put them in the form's array of element properties
		for ($i=0; $i < $this->cnt; $i++)
		{
			if ($this->arr[$i]->save(&$arr) == false)
			{
				$elid = $this->arr[$i]->get_id();
				// we must delete the element from this form.
				unset($form->arr["elements"][$this->row][$this->col][$elid]);

				// remove this form from the list of forms in which the element is
				$row = $this->db_query("SELECT * FROM form_elements WHERE id = ".$elid);
				$ra = unserialize($row["forms"]);
				unset($ra[$this->id]);
				$rs = serialize($ra);
				$this->db_query("UPDATE form_elements SET forms = '$rs' WHERE id = ".$elid);

				// also remove the column for this element from the form
				$this->db_query("ALTER TABLE form_".$this->id."_entries DROP el_".$elid);
			}
			else
			{
				$form->arr["elements"][$this->row][$this->col][$this->arr[$i]->get_id()] = $this->arr[$i]->get_props();
			}
		}
	}

	function gen_check_html()
	{
		$ret = "";
		for ($i=0; $i < $this->cnt; $i++)
			$ret.=$this->arr[$i] -> gen_check_html();
		return $ret;
	}
};
?>
