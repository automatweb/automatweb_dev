<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_cell.aw,v 2.31 2002/07/23 12:58:13 kristo Exp $

// ysnaga. asi peab olema nii lahendatud, et formi juures on elemendi properitd kirjas
// st forms.contents sees on ka selle elemendi propertid selle fomi sees kirjas
// et saax igale formile eraldi elemendi properteid panna
// JA elemendi juures on kirjas, et mis formide sees selle element on. 
// see on sellex, et siis kui on vaja teha nimekirja t2idetud formidest, kus see element sees on, siis
// saab sealt selle kohe teada. 

// no public interface here. everything goes through form.aw
// why? iizi. cause the element properies are saved in the form and therefore the form must always be loaded
// so we have to go through form->load to get the cells' elements.
lc_load("definition");
class form_cell extends form_base
{
	function form_cell()
	{
		$this->form_base();
		$this->sub_merge = 1;
	}

	////
	// !ElementFactory for all you fancy people ;)
	// creates the correct element based on form type
	function mk_element($type, &$r, &$form)
	{
//		echo "type = $type <br>";
		switch($type)
		{
			case FTYPE_ENTRY:
				$t = "form_entry_element";
				break;
			case FTYPE_SEARCH:
				$t = "form_search_element";
				break;
			case FTYPE_FILTER_SEARCH:
				$t = "form_filter_search_element";
				break;
			case FTYPE_CONFIG:
				$t = "form_entry_element";
				break;
			default:
				$this->raise_error(ERR_FG_ETYPE,"form_cell->mk_element($type) , error in type!",true);
		}
		$tmp = new $t;
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

		if (is_array($this->form->arr["elements"][$row][$col]))
		{
			reset($this->form->arr["elements"][$row][$col]);
			while (list($k,$v) = each($this->form->arr["elements"][$row][$col]))
			{
				if (is_number($k))
				{
					$this->mk_element($this->type, &$v, &$form);
				}
			}
		}

		$this->style = isset($this->form->arr["elements"][$row][$col]["style"]) ? $this->form->arr["elements"][$row][$col]["style"] : 0;
		$this->style_class = isset($this->form->arr["elements"][$row][$col]["style_class"]) ? $this->form->arr["elements"][$row][$col]["style_class"] : 0;
	}

	////
	// !Displays cell administration form for previously loaded cell
	function admin_cell()
	{
		$this->read_template("admin_cell.tpl");
		$this->mk_path($this->parent, "<a href='".$this->mk_orb("change", array("id" => $this->id),"form").LC_FORM_CELL_CHANGE_FORM_CHANGE_CELL);

		$this->vars(array(
			"add_element" => $this->mk_orb("add_element", array("id" => $this->id, "col" => $this->col, "row" => $this->row, "after" => -1)),
			"cell_style"	=> $this->mk_orb("sel_cell_style", array("id" => $this->id, "col" => $this->col, "row" => $this->row),"form"),
			"form_id" => $this->id,
			"form_col" => $this->col,
			"form_row" => $this->row,
			"parent" => $this->id,
		));

		for ($i=0; $i < $this->cnt; $i++)
		{
			$this->vars(array("after" => $this->arr[$i]->get_id(),"element" => $this->arr[$i]->gen_admin_html()));
			$this->vars(array("EL_NLAST" => ($i == ($this->cnt-1) ? "" : $this->parse("EL_NLAST"))));
			$this->vars(array("EL_ADD" => (1 ? $this->parse("EL_ADD") : ""),
												"EL_ACL" => (1 ? $this->parse("EL_ACL") : "")));

			$this->parse("ELEMENT_LINE");
		}

		$this->vars(array(
			"add_el" => $this->mk_orb("add_element", array("id" => $this->id, "row" => $this->row, "col" => $this->col),"form"),
		));

		$ca = $this->parse("CAN_ADD");

		$caa = $this->parse("CAN_ACTION");

		$this->vars(array(
			"CAN_ADD" 	=> $ca,
			"CAN_ACTION"	=>$caa,
			"reforb"	=> $this->mk_reforb("submit_cell", array("id" => $this->id, "row" => $this->row, "col" => $this->col),"form"),
		));
			
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
			// subtype vaja teada int sortimise jaoks form_tables
			$ret[$i]["subtype"] = $this->arr[$i]->get_subtype();
			$ret[$i]["id"] = $this->arr[$i]->get_id();
			$ret[$i]["order"] = $this->arr[$i]->get_order();
			$ret[$i]["group"] = $this->arr[$i]->get_el_group();
			$ret[$i]["lb_items"] = $this->arr[$i]->get_el_lb_items();
			$ret[$i]["thousands_sep"] = $this->arr[$i]->get_thousands_sep();

			// if that element is a relation element, perhaps we should try
			// and load it's contents too?
			$ret[$i]["rel_form"] = $this->arr[$i]->get_prop("rel_form");
			$ret[$i]["rel_element"] = $this->arr[$i]->get_prop("rel_element");

			// I need those to figure out the oid of the selected item
			// in a relation listbox in form->process_entry
			$ret[$i]["sort_by_alpha"] = $this->arr[$i]->get_prop("sort_by_alpha");
			$ret[$i]["rel_unique"] = $this->arr[$i]->get_prop("rel_unique");

			if ($this->arr[$i]->get_type() == "checkbox")
			{
				$ret[$i]["group"] = $this->arr[$i]->arr["ch_grp"];
			}

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
	// if wizard_step is not set then we are coming from the "add new element" link and have to let the
	// user make a choice what element she wants to add

	// if wizard_step is set, then she already made her choice and we can probably just add the element
	function add_element()
	{
		$this->mk_path($this->parent, "<a href='".$this->mk_orb("change", array("id" => $this->id),"form").LC_FORM_CELL_CHANGE_FROM_ADD_ELEMENT);
		$this->read_template("add_el_wiz1.tpl");

		classload("objects");
		$o = new db_objects;
		if (!(is_array($this->form->arr["el_menus"]) && count($this->form->arr["el_menus"]) > 0))
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
		
		$this->vars(array(
			"reforb"		=> $this->mk_reforb("submit_element", array("id" => $this->id, "row" => $this->row, "col" => $this->col),"form"),
			"folders"		=> $this->picker($this->parent, $mlist),
			"elements"	=> $this->picker(0,$this->listall_elements(&$this->form))
		));
		return $this->parse();
	}

	////
	// !add_element submit handler
	function submit_element($args = array())
	{
		extract($args);
		// add new element
		if ($type == "add")
		{
			// form elements are weird things.
			// namely. they are at the same time menus AND form elements. 
			// so each element is written in three places
			// objects table, class_id = CL_PSEUDO 
			// menu table with type MN_FORM_ELEMENT
			// form_elements table that is used to remember the elements proiperties when the element is
			// inserted into another form
			// the actual info about how the element is to be shown is written into the form's array. whee. 
			// and also element2form table contains all element -> table relationships
			$el = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_FORM_ELEMENT));
//			$this->db_query("INSERT INTO menu (id,type) values($el,".MN_FORM_ELEMENT.")");
			$this->db_query("INSERT INTO form_elements (id) values($el)");
			$arr = array(); // new elements do not have any props, so set that to 0
		}
		// the other choice is most likely "select" which ment that the user selected an already existing element
		else
		{
			if ($el)
			{
				$oo = $this->get_object($el);
				$name = $oo["name"];
				$ord = $oo["jrk"];
				$props = $this->db_fetch_field("SELECT props FROM form_elements WHERE id = ".$el,"props");
				$arr = aw_unserialize($props);
			}
		}
		
		if ($el)
		{
			// register the new element into this form
			$this->_do_add_element($this->id,$el);

			// add the element into the form.
			// but! use the props saved in the form_elements table to create them with the right config right away!
			$arr["id"] = $el;
			$arr["name"] = $name;
			$arr["ord"] = $ord;

			// so we lose the relations if adding an existing element. Is there a good reason for that? -- duke
			$arr["linked_element"] = 0;
			$arr["linked_form"] = 0;
			$arr["linked_element"] = 0;
			$arr["rel_table_id"] = 0;

			$this->form->arr["elements"][$this->row][$this->col][$el] = $arr;
			$this->form->save();
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
		if (!is_array($props))
		{
			$this->save_handle();
			$props = $this->db_fetch_field("SELECT props FROM form_elements WHERE id = ".$based_on,"props");
			$this->restore_handle();
			$arr = aw_unserialize($props);
		}
		else
		{
			$arr = $props;
		}
		$arr["id"] = $el;
		$arr["name"] = $name;
		$arr["ord"] = $ord;
		$arr["linked_element"] = 0;
		$arr["linked_form"] = 0;
		$arr["type_name"] = "";
		$arr["rel_table_id"] = 0;
		$this->form->arr["elements"][$this->row][$this->col][$el] = $arr;
		return $el;
	}

	function _do_add_element($fid,$el)
	{
		if (is_number($el))
		{
			// we must also update the form_$id_entries table
			// sigh. would be really nice if we could have element of another type - integer for example
			// that would make some searches really faster
			$this->db_query("ALTER TABLE form_".$fid."_entries ADD el_$el TEXT");
			$this->db_query("ALTER TABLE form_".$fid."_entries ADD ev_$el TEXT");

			// add indexes to form tables aswell
			// can't add these - mysql has a limit of 10 indexes per table :((
			// so we can't have more than 5 elements per form when we do this :((
//			$this->db_query("ALTER TABLE form_".$fid."_entries ADD INDEX el_$el(el_$el(10))");
//			$this->db_query("ALTER TABLE form_".$fid."_entries ADD INDEX ev_$el(ev_$el(10))");

			// and add this form to the list of forms in which the element is
			$this->db_query("INSERT INTO element2form(el_id,form_id) VALUES ($el,$fid)");
		}
	}

	//// 
	// !deletes all the elements in this cell
	function del()
	{
		for ($i=0; $i < $this->cnt; $i++)
		{
			$this->arr[$i]->del();
		}
	}

	function gen_user_html_not($def_style, $colspan, $rowspan,$prefix = "",$elvalues,$no_submit=false)
	{
		$c = "";
		$cs = "";
		for ($i=0; $i < $this->cnt; $i++)
		{
			// here we must check the show element controllers
			$errs = array();
			$shcs = $this->arr[$i]->get_show_controllers();
			$controllers_ok = true;
			foreach($shcs as $ctlid)
			{
				$res = $this->form->controller_instance->do_check($ctlid, $this->arr[$i]->get_controller_value(), &$this->form, $this->arr[$i]);
				if ($res !== true)
				{
					$controllers_ok = false;
					if ($this->form->controller_instance->get_show_errors())
					{
						$errs[] = $res;
					}
				}
			}

			if ($controllers_ok)
			{
				$c.=$this->arr[$i]->gen_user_html_not($prefix,$elvalues,$no_submit);
			}
			else
			{
				$erstr = join("<br>", $errs);
				if ($erstr != "")
				{
					$c .= "<font color='red' size='2'>".$erstr."</font>";
				}
			}
		}
		if ($c == "")
		{
			$c = "<img src='".$this->cfg["baseurl"]."/images/transa.gif' height=1 width=1 border=0>";
		}

		$style_id=$this->style;
		if (!$style_id)
		{
			$style_id = $def_style;
		}

		if ($this->style_class == CL_CSS)
		{
			$styl = "";
			if ($style_id)
			{
				if (!isset($this->form->styles[$style_id]))
				{
					$styl = "formstyle".aw_global_get("form_style_count");
					$this->form->styles[$style_id] = $styl;
					aw_global_set("form_style_count",aw_global_get("form_style_count")+1);
				}
				else
				{
					$styl = $this->form->styles[$style_id];
				}
			}
			$cs.="<td colspan=\"".$colspan."\" rowspan=\"".$rowspan."\" class=\"$styl\">".$c."</td>";
		}
		else
		{
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
			{
				$cs.= $stc->get_cell_end_str($style_id);
			}

			$cs.= "</td>";
		}			
		return $cs;
	}
	
	function process_entry(&$entry, $id,$prefix = "")
	{
		$controllers_ok = true;

		// iterate over all the elements in the cell
		for ($i=0; $i < $this->cnt; $i++)
		{
			$shctrlok = true;
			$errs = array();
			$shcs = $this->arr[$i]->get_show_controllers();
			$controllers_ok = true;
			foreach($shcs as $ctlid)
			{
				$res = $this->form->controller_instance->do_check($ctlid, $this->arr[$i]->get_controller_value(), &$this->form, $this->arr[$i]);
				if ($res !== true)
				{
					$shctrlok = false;
				}
			}
			if ($shctrlok)
			{
				// call process_entry for each
				$controllers_ok &= $this->arr[$i] -> process_entry(&$entry, $id,$prefix);
			}
		};
		return $controllers_ok;
	}

	function set_entry(&$arr, $e_id)
	{
		for ($i=0; $i < $this->cnt; $i++)
		{
			$this->arr[$i] -> set_entry(&$arr, $e_id);
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

	function set_style($id,$style_class = 0)
	{
		$this->form->arr["elements"][$this->row][$this->col]["style"] = $id;
		$this->form->arr["elements"][$this->row][$this->col]["style_class"] = $style_class;
	}

	////
	// !generates the form for selecting cell style
	function pickstyle()
	{
		$this->mk_path($this->parent,"<a href='".$this->mk_orb("change",array("id" => $this->id),"form")."'>Muuda formi</a> / <a href='".$this->mk_orb("admin_cell",array("id" => $this->id, "row" => $this->row, "col" => $this->col),"form").LC_FORM_CELL_CHANDE_CELL);
		$this->read_template("pickstyle.tpl");

		classload("style");
		$t = new style;

		$this->vars(array(
			"reforb" => $this->mk_reforb("save_cell_style", array("id" => $this->id, "col" => $this->col, "row" => $this->row),"form"),
			"stylessel"	=> $this->option_list($this->get_style(),$t->get_select(0,ST_CELL))
		));
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
				$xp = aw_serialize($props,SERIALIZE_XML);
				$this->quote(&$xp);
				$this->db_query("UPDATE form_elements SET props = '".$xp."' WHERE id = ".$id);
			}
		}
	}

	function gen_check_html()
	{
		$ret = "";
		for ($i=0; $i < $this->cnt; $i++)
		{
			$ret.=$this->arr[$i] -> gen_check_html();
		}
		return $ret;
	}

	function set_lang_text($lid,$lar)
	{
		for ($i=0; $i < $this->cnt; $i++)
		{
			$this->arr[$i]->set_lang_text($lid,$lar[$this->arr[$i]->get_id()]);
		}
	}

	////
	// !removes controller $controller for type $type from element $element in this cell
	function remove_controller_from_element($arr)
	{
		extract($arr);
		for ($i=0; $i < $this->cnt; $i++)
		{
			if ($this->arr[$i] -> get_id() == $element)
			{
				switch($type)
				{
					case CTRL_USE_TYPE_ENTRY:
						$this->arr[$i]->remove_entry_controller($controller);
						break;

					case CTRL_USE_TYPE_SHOW:
						$this->arr[$i]->remove_show_controller($controller);
						break;

					case CTRL_USE_TYPE_LB:
						$this->arr[$i]->remove_lb_controller($controller);
						break;
				}
			}
		}
	}

	////
	// !call this after the cell's elements properties have changed - it makes sure that they get
	// saved along with form_base::save
	function prep_save()
	{
		for ($i=0; $i < $this->cnt; $i++)
		{
			$this->form->arr["elements"][$this->row][$this->col][$this->arr[$i]->get_id()] = $this->arr[$i]->get_props();
		}
	}

	////
	// !sets element $el 's entry to $val
	function set_element_entry($el,$val)
	{
		for ($i=0; $i < $this->cnt; $i++)
		{
			if ($this->arr[$i]->get_id() == $el)
			{
				if ($this->arr[$i]->get_type() == "listbox")
				{
					if ($this->arr[$i]->arr["subtype"] == "relation" && $this->arr[$i]->arr["rel_element"] && $this->arr[$i]->arr["rel_form"])
					{
						$this->arr[$i]->make_relation_listbox_content();
					}
					// find the right element from the lb content
					if (is_array($this->arr[$i]->arr["listbox_items"]))
					{
						foreach($this->arr[$i]->arr["listbox_items"] as $lbid => $lbval)
						{
							if ($lbval == $val)
							{
								$val = "element_".$this->arr[$i]->get_id()."_lbopt_".$lbid;
								$this->arr[$i]->entry = $val;
//								echo "set entry to $val <br>";
//								echo "get val = ",$this->arr[$i]->get_value()," <br>";
								return;
							}
						}
					}
				}
				else
				{
					$this->arr[$i]->entry = $val;
				}
			}
		}
	}
};
?>
