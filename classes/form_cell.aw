<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_cell.aw,v 2.14 2001/07/18 16:22:30 kristo Exp $

// ysnaga. asi peab olema nii lahendatud, et formi juures on elemendi properitd kirjas
// st forms.contents sees on ka selle elemendi propertid selle fomi sees kirjas
// et saax igale formile eraldi elemendi properteid panna
// JA elemendi juures on kirjas, et mis formide sees selle element on. 
// see on sellex, et siis kui on vaja teha nimekirja t2idetud formidest, kus see element sees on, siis
// saab sealt selle kohe teada. 

// no public interface here. everything goes through form.aw
// why? iizi. cause the element properies are saved in the form and therefore the form must always be loaded
// so we have to go through form->load to get the cells' elements.
class form_cell extends form_base
{
	function form_cell()
	{
		$this->tpl_init("forms");
		$this->db_init();
		$this->sub_merge = 1;
		lc_load("definition");
	}

	////
	// !ElementFactory for all you fancy people ;)
	// creates the correct element based on form type
	function mk_element($type, &$r, &$form)
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
		$tmp->load(&$r,&$form,$this->col, $this->row);
		$this->arr[$this->cnt] = $tmp;
		$this->cnt++;
	}

	function load(&$form, $row, $col)
	{
		$this->type = $form->get_type();
		$this->col = $col;
		$this->row = $row;
		$this->id = $form->get_id();
		$this->cnt = 0;
		$this->parent = $form->get_parent();
		$this->form = &$form;

		$arr = &$form->get_el_arr();

		if (is_array($arr[$row][$col]))
		{
			reset($arr[$row][$col]);
			while (list($k,$v) = each($arr[$row][$col]))
			{
				if (is_number($k))
				{
					$this->mk_element($this->type, &$v, &$form);
				}
			}
		}

		$this->style = isset($arr[$row][$col]["style"]) ? $arr[$row][$col]["style"] : 0;
	}

	////
	// !Displays cell administration form for previously loaded cell
	function admin_cell()
	{
		$this->read_template("admin_cell.tpl");
		$this->mk_path($this->parent, "<a href='".$this->mk_orb("change", array("id" => $this->id),"form").LC_FORM_CELL_CHANGE_FORM_CHANGE_CELL);

		$this->vars(array(
			"add_element" => $this->mk_orb("add_element", array("id" => $this->id, "col" => $this->col, "row" => $this->row, "after" => -1)),
			"cell_style"	=> $this->mk_orb("sel_cell_style", array("id" => $this->id, "col" => $this->col, "row" => $this->row),"form")
		));

		$this->vars(array("form_id" => $this->id, "form_col" => $this->col, "form_row" => $this->row, "parent" => $this->id));

		for ($i=0; $i < $this->cnt; $i++)
		{
			$this->vars(array("after" => $this->arr[$i]->get_id(),"element" => $this->arr[$i]->gen_admin_html()));
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
			$ret[$i]["text"] = $this->arr[$i]->get_text();
			$ret[$i]["name"] = $this->arr[$i]->get_el_name();
			$ret[$i]["type"] = $this->arr[$i]->get_type();
			$ret[$i]["id"] = $this->arr[$i]->get_id();
			$ret[$i]["order"] = $this->arr[$i]->get_order();
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
			$form->arr["elements"][$this->row][$this->col][$this->arr[$i]->get_id()] = $this->arr[$i]->get_props();
		}
	}

	////
	// !Adds a new element in the folder $parent and associates it with the currently loaded form also. 
	function add_element($wizard_step)
	{
		$this->mk_path($this->parent, "<a href='".$this->mk_orb("change", array("id" => $this->id),"form").LC_FORM_CELL_CHANGE_FROM_ADD_ELEMENT);
		if (!$wizard_step)
		{
			$this->read_template("add_el_wiz1.tpl");

			classload("objects");
			$o = new db_objects;
			if (!is_array($this->form->arr["el_menus"]))
			{
				$mlist = $o->get_list();
			}
			else
			{
				$tlist = $o->get_list();
				foreach($this->form->arr["el_menus"] as $menuid)
				{
					$mlist[$menuid] = $tlist[$menuid];
				}
			}
			
			$this->vars(array("reforb"		=> $this->mk_reforb("add_element", array("id" => $this->id, "row" => $this->row, "col" => $this->col,"wizard_step" => 1),"form"),
												"folders"		=> $this->picker($this->parent, $mlist),
												"elements"	=> $this->picker(0,$this->listall_elements(&$this->form))));
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
				// form_elements table that is used to remember the elements proiperties when the element is insertet into another form
				// the actual info about how the element is to be shown is written into the form's array. whee. 
				// and also element2form table contains all element -> table relationships
				$el = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_FORM_ELEMENT));
//				$this->db_query("INSERT INTO menu (id,type) values($el,".MN_FORM_ELEMENT.")");
				$this->db_query("INSERT INTO form_elements (id) values($el)");
			}
			else
			{
				if ($el)
				{
					$oo = $this->get_object($el);
					$name = $oo["name"];
					$ord = $oo["jrk"];
				}
			}
		
			if ($el)
			{
				$this->_do_add_element($this->id,$el);
				// add the element into the form.
				// but! use the props saved in the form_elements table to create them with the right config right away!
				$props = $this->db_fetch_field("SELECT props FROM form_elements WHERE id = ".$el,"props");
				classload("xml");
				$xml = new xml;
				$arr = $xml->xml_unserialize(array("source" => $props));
				$arr["id"] = $el;
				$arr["name"] = $name;
				$arr["ord"] = $ord;
				$arr["linked_element"] = 0;
				$arr["linked_form"] = 0;
				$this->form->arr["elements"][$this->row][$this->col][$el] = $arr;
				$this->form->save();
			}
			return false;
		}
	}

	////
	// !adds an element to this cell
	// $parent, $name, $ord, $based_on
	function do_add_element($arr)
	{
		extract($arr);
		$el = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_FORM_ELEMENT));
		$this->db_query("INSERT INTO form_elements (id) values($el)");
		$this->_do_add_element($this->id,$el);
		$props = $this->db_fetch_field("SELECT props FROM form_elements WHERE id = ".$based_on,"props");
		classload("xml");
		$xml = new xml;
		$arr = $xml->xml_unserialize(array("source" => $props));
		$arr["id"] = $el;
		$arr["name"] = $name;
		$arr["ord"] = $ord;
		$arr["linked_element"] = 0;
		$arr["linked_form"] = 0;
		$arr["type_name"] = "";
		$this->form->arr["elements"][$this->row][$this->col][$el] = $arr;
		return $el;
	}

	function _do_add_element($fid,$el)
	{
		if (is_number($el))
		{
			// we must also update the form_$id_entries table
			$this->db_query("ALTER TABLE form_".$fid."_entries add el_$el text");
	
			// and add this form to the list of forms in which the element is
			$this->db_query("INSERT INTO element2form(el_id,form_id) values($el,$fid)");
		}
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

	function gen_user_html_not($def_style, $colspan, $rowspan,$prefix = "",$elvalues,$no_submit=false)
	{
		$el = 0;
		$c = "";
		$cs = "";
		for ($i=0; $i < $this->cnt; $i++)
		{
			$c.=$this->arr[$i]->gen_user_html_not($prefix,$elvalues,$no_submit);
			$el = &$this->arr[$i];
		}
		if ($c == "")
		{
			$c = "<img src='/images/transa.gif' height=1 width=1 border=0>";
		}

		$style_id=$this->style;
		if (!$style_id)
		{
			$style_id = $def_style;
		}

		$stc = new style;
		if ($style_id)
		{
			$cs.= $stc->get_cell_begin_str($style_id,$colspan,$rowspan);
		}
		else
		{
			$cs .= "<td colspan=\"".$colspan."\" rowspan=\"".$rowspan."\">";
		}

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

	function set_entry(&$arr, $e_id,&$form)
	{
		for ($i=0; $i < $this->cnt; $i++)
		{
			$this->arr[$i] -> set_entry(&$arr, $e_id,&$form);
		}
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
		$form->arr["elements"][$this->row][$this->col]["style"] = $id;
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

	////
	// !generates the form for selecting cell style
	function pickstyle()
	{
		$this->mk_path($this->parent,"<a href='".$this->mk_orb("change",array("id" => $this->id),"form")."'>Muuda formi</a> / <a href='".$this->mk_orb("admin_cell",array("id" => $this->id, "row" => $this->row, "col" => $this->col),"form").LC_FORM_CELL_CHANDE_CELL);
		$this->read_template("pickstyle.tpl");

		classload("style");
		$t = new style;

		$this->vars(array("reforb" => $this->mk_reforb("save_cell_style", array("id" => $this->id, "col" => $this->col, "row" => $this->row),"form"),
											"stylessel"	=> $this->option_list($this->get_style(),$t->get_select(0,ST_CELL))));
		return $this->parse();
	}

	////
	// !saves the elements in this cell
	function submit_cell(&$arr,&$form)
	{
		// gather all properties of the elements in the cell in their arrays from the submitted form
		// and put them in the form's array of element properties
		for ($i=0; $i < $this->cnt; $i++)
		{
			if ($this->arr[$i]->save(&$arr) == false)
			{
				$this->arr[$i]->del();
				// we must delete the element from this form.
				$elid = $this->arr[$i]->get_id();
				unset($form->arr["elements"][$this->row][$this->col][$elid]);
			}
			else
			{
				$id = $this->arr[$i]->get_id();
				$props = $this->arr[$i]->get_props();
				$form->arr["elements"][$this->row][$this->col][$id] = $props;
				// also save the elements properties so that when you add the same element to a new form, 
				// all it's properties are exactly the same! yeah! baby! SWEET!
				classload("xml");
				$x = new xml;
				$xp = $x->xml_serialize($props);
				$this->quote(&$xp);
				$this->db_query("UPDATE form_elements SET props = '".$xp."' WHERE id = ".$id);
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
