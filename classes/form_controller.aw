<?php

classload("form_base","languages");

// controller types in element - each controller can be used for every one of these, 
// they are just here to specify for which controller in the element the controller is selected
define("CTRL_USE_TYPE_ENTRY", 1);			// entry controller - checks on form submit
define("CTRL_USE_TYPE_SHOW", 2);			// show controller - checks on form showing
define("CTRL_USE_TYPE_LB", 3);				// listbox controller - checks every listbox element on showing
define("CTRL_USE_TYPE_DEFVALUE", 4);	// default value controller - if element value is not set the return value of this will be used
define("CTRL_USE_TYPE_VALUE", 5);			// value controller - evals on show and submit - the element value will be the return of this

class form_controller extends form_base
{
	function form_controller()
	{
		$this->form_base();
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add_controller.tpl");
		$this->mk_path($parent, "Lisa kontroller");
	
		$l = new languages;
		$lar = $l->listall();
		foreach($lar as $ld)
		{
			$this->vars(array(
				"lang_name" => $ld["name"],
				"lang_id" => $ld["id"]
			));
			$la.=$this->parse("LANG");
		}
		$this->vars(array(
			"LANG" => $la,
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			// update
			$co = $this->load_controller($id);
			$co["name"] = $name;
			$co["meta"]["eq"] = $eq;
			$co["meta"]["errmsg"] = $errmsg;
			$co["meta"]["show_errors_showctl"] = $show_errors_showctl;
			$this->save_controller($co);
		}
		else
		{
			// add
			$id = $this->new_object(array(
				"name" => $name,
				"class_id" => CL_FORM_CONTROLLER,
				"parent" => $parent,
				"status" => 2,
				"metadata" => array(
					"eq" => $eq,
					"errmsg" => $errmsg,
					"vars" => array(),
					"show_errors_showctl" => $show_errors_showctl
				)
			));
		}
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$co = $this->load_controller($id);
		$this->read_template("add_controller.tpl");
		$this->mk_path($co["parent"], "Muuda kontrollerit");

		$l = new languages;
		$lar = $l->listall();
		foreach($lar as $ld)
		{
			$this->vars(array(
				"lang_name" => $ld["name"],
				"lang_id" => $ld["id"],
				"errmsg" => $co["meta"]["errmsg"][$ld["id"]]
			));
			$la.=$this->parse("LANG");
		}

		if (is_array($co["meta"]["vars"]))
		{
			foreach($co["meta"]["vars"] as $var => $vd)
			{
				if ($vd["form_id"])
				{
					$fd = $this->get_object($vd["form_id"]);
					$ref = $fd["name"];
					if ($vd["el_id"])
					{
						$ed = $this->get_object($vd["el_id"]);
						$ref.=".".$ed["name"];
					}
				}
				$this->vars(array(
					"var_name" => $var,
					"var_value" => $this->get_var_value($co,$var),
					"ref" => $ref,
					"del_var" => $this->mk_my_orb("del_var", array("id" => $id, "var_name" => $var)),
					"ch_var" => $this->mk_my_orb("change_var", array("id" => $id, "var_name" => $var))
				));
				$vl.=$this->parse("VAR_LINE");
			}
		}

		$this->vars(array(
			"VAR_LINE" => $vl,
			"add_var" => $this->mk_my_orb("add_var", array("id" => $id)),
			"form_list" => $this->mk_my_orb("form_list", array("id" => $id)),
			"LANG" => $la,
			"name" => $co["name"],
			"eq" => $co["meta"]["eq"],
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"show_errors" => checked($co["meta"]["show_errors_showctl"])
		));

		$this->vars(array(
			"CHANGE" => $this->parse("CHANGE"),
			"CHANGE2" => $this->parse("CHANGE2"),
		));
		return $this->parse();
	}

	function load_controller($id)
	{
		$ret = $this->get_object($id);
		$this->loaded_controller = $ret;
		return $ret;
	}

	////
	// !get a list of form controllers
	// if $parents is set, only controllers in those folders are returned
	// returns array ($controller_id => $controller_path_and_name)
	function listall($arr)
	{
		extract($arr);
		$ret = array();
		if (is_array($parents) && count($parents) > 0)
		{
			$wh = " AND objects.parent IN(".join(",",$parents).") ";
		}

		classload("objects");
		$obj = new objects;
		$ol = $obj->get_list();

		$this->db_query("SELECT oid,name,parent FROM objects WHERE class_id = ".CL_FORM_CONTROLLER." AND status != 0 $wh");
		while($row = $this->db_next())
		{
			$ret[$row["oid"]] = $ol[$row["parent"]]."/".$row["name"];
		}
		if ($add_empty)
		{
			$ret[""] = "";
		}
		return $ret;
	}

	////
	// !this validates entered data $entry via controller $id 
	// $form_ref is a reference to the form that the controller is connected to
	// it is used to access the current entry element values
	// $el_ref is a reference to the current element - it is used to import metadata values
	// returns true, if the data matches the controller and an error message if not
	function do_check($id,$entry,&$form_ref,&$el_ref)
	{
		if (!$id)
		{
			return true;
		}
		$res = $this->eval_controller($id, $entry, $form_ref, $el_ref);
		if (!$res)
		{
			$co = $this->loaded_controller;
			return $this->replace_vars($co,$co["meta"]["errmsg"][aw_global_get("lang_id")],false,&$form_ref, $el_ref, $entry);
		}
		return true;
	}

	////
	// !loads controller $id , replaces variables and evals the equasion
	// $entry is the current element's value
	// form_ref - reference to the form that the current element is a part of
	// $el_ref is a reference to the current element - it is used to import metadata values - optional
	function eval_controller($id, $entry, &$form_ref,$el_ref = false)
	{
		if (!$id)
		{
			return true;	// don't remove this, otherwise all controller checks will fail withut a controller
		}
		$co = $this->load_controller($id);
		$eq = $this->replace_vars($co,$co["meta"]["eq"],true,$form_ref, $el_ref, $entry);

		$eq = "\$res = ".$eq.";";
//		echo "evaling $eq <br>";
		eval($eq);
		return $res;
	}

	////
	// !this imports all the variable values to equasion $eq
	function replace_vars($co,$eq,$add_quotes,&$form_ref, $el_ref, $el_value = "")
	{
		$this->cur_form_instance = &$form_ref;
		if (is_array($co["meta"]["vars"]))
		{
			foreach($co["meta"]["vars"] as $var => $vd)
			{
//				echo "var = '$var' <br>";
				if (strpos($eq,"[".$var."]") !== false)
				{
//					echo "included <br>";
					$val = $this->get_var_value($co, $var);
//					echo "val = $val <br>";
					if ($add_quotes)
					{
						$val = "\"".$val."\"";
					}
					$eq = str_replace("[".$var."]",$val,$eq);
				}
			}
		}

		// now import all current form element values as well
		$els = $form_ref->get_all_els();
		foreach($els as $el)
		{
			$var = $el->get_el_name();
//			echo "var = '$var' eq = $eq <br>";
			if (strpos($eq,"[".$var."]") !== false)
			{
				$val = $el->get_controller_value();
				if ($add_quotes)
				{
					$val = "\"".$val."\"";
				}
//				echo "replace '$var' with '$val' <Br>";
				$eq = str_replace("[".$var."]",$val,$eq);
			}
		}

		$eq = str_replace("[el]","\"".$el_value."\"",$eq);

		// now do element metadata as well
		if (is_object($el_ref))
		{
			foreach($el_ref->get_metadata() as $mtk => $mtv)
			{
				$eq = str_replace("[el.".$mtk."]",$mtv,$eq);
			}
		}

		// and finally init all non-initialized vars to zero to avoid parse errors
		$eq = preg_replace("/(\[.*\])/","0",$eq);

		return $eq;
	}

	////
	// !presents the interface for adding variables to controller
	function add_var($arr)
	{
		extract($arr);
		$co = $this->load_controller($id);
		$this->mk_path($co["parent"], "<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Muuda kontrollerit</a> / Lisa muutuja");
		$this->read_template("ctr_add_var.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_var", array("id" => $id))
		));
		return $this->parse();
	}

	////
	// !saves the variable prefs
	function submit_var($arr)
	{
		extract($arr);
		$co = $this->load_controller($id);

		$var_name = str_replace("'","",$var_name);
		$var_name = str_replace("\"","",$var_name);
		$var_name = str_replace("\\","",$var_name);
		$var_name = strip_tags($var_name);

		if ($change)
		{
			$co["meta"]["vars"][$var_name]["form_id"] = $sel_form;
			$co["meta"]["vars"][$var_name]["el_id"] = $sel_el;
			$co["meta"]["vars"][$var_name]["et_type"] = $entry_type;
			$co["meta"]["vars"][$var_name]["et_entry_id"] = $sel_entry_id;
		}
		else
		{
			if ($var_name != "")
			{
				$co["meta"]["vars"][$var_name] = array("name" => $var_name);
			}
		}

		$this->save_controller($co);

		if ($var_name == "")
		{
			return $this->mk_my_orb("add_var", array("id" => $id));
		}
		else
		{
			return $this->mk_my_orb("change_var", array("id" => $id, "var_name" => $var_name));
		}
	}

	function change_var($arr)
	{
		extract($arr);
		$co = $this->load_controller($id);

		$this->mk_path($co["parent"], "<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Muuda kontrollerit</a> / Lisa muutuja");
		$this->read_template("ctr_add_var.tpl");

		$v_form_id = $co["meta"]["vars"][$var_name]["form_id"];
		$v_el_id = $co["meta"]["vars"][$var_name]["el_id"];

		if ($v_form_id)
		{
			$this->vars(array(
				"elements" => $this->picker($v_el_id,array("" => "") + $this->get_elements_for_forms(array($v_form_id)))
			));

			if ($v_el_id)
			{
				$et_type = $co["meta"]["vars"][$var_name]["et_type"];

				$this->vars(array(
					"et_entry_id" => checked($et_type == "entry_id"),
					"et_user_data" => checked($et_type == "user_data"),
					"et_user_entry" => checked($et_type == "user_entry"),
					"et_same_chain" => checked($et_type == "same_chain"),
					"et_writer_entry" => checked($et_type == "writer_entry"),
					"et_session" => checked($et_type == "session"),
					"et_element_sum" => checked($et_type == "element_sum")
				));

				if ($et_type == "entry_id")
				{
					$et_entry_id = $co["meta"]["vars"][$var_name]["et_entry_id"];

					$this->vars(array(
						"entries" => $this->picker($et_entry_id, $this->get_entries(array("id" => $v_form_id,"addempty" => true))),
						"change_entry" => $this->mk_my_orb("show", array("id" => $v_form_id, "entry_id" => $et_entry_id),"form")
					));
					if ($et_entry_id)
					{
						$this->vars(array(
							"CHANGE_ENTRY" => $this->parse("CHANGE_ENTRY")
						));
					}
					$this->vars(array(
						"ET_ENTRY_ID" => $this->parse("ET_ENTRY_ID")
					));
				}
				$this->vars(array(
					"EL_SEL" => $this->parse("EL_SEL")
				));
			}
			$this->vars(array(
				"FORM_SEL" => $this->parse("FORM_SEL")
			));
		}

		$forms = $this->get_flist(array("addfolders" => true, "lang_id" => aw_global_get("lang_id")));
		asort($forms);

		$this->vars(array(
			"forms" => $this->picker($v_form_id,$forms),
			"reforb" => $this->mk_reforb("submit_var", array("id" => $id, "var_name" => $var_name, "change" => 1)),
			"var_name" => $var_name
		));

		$this->vars(array(
			"CHANGE" => $this->parse("CHANGE"),
		));
		return $this->parse();
	}

	function save_controller($co)
	{
		$this->upd_object(array(
			"oid" => $co["oid"],
			"name" => $co["name"],
			"metadata" => $co["meta"]
		));
	}

	function get_var_value($co,$var_name)
	{
		$fid = $co["meta"]["vars"][$var_name]["form_id"];
		$elid = $co["meta"]["vars"][$var_name]["el_id"];
		$et_type = $co["meta"]["vars"][$var_name]["et_type"];
		$et_entry_id = $co["meta"]["vars"][$var_name]["et_entry_id"];
		$cache_key = $fid."::".$elid."::".$et_type."::".$et_entry_id;
		if ($fid && $elid && $et_type)
		{
/*			if (($val = aw_cache_get("controller::var_value_cache", $cache_key)))
			{
				return $val;
			}*/

			$form =& $this->cache_get_form_instance($fid);

			if ($et_type == "entry_id")
			{
				$entry_id = $et_entry_id;
			}
			else
			if ($et_type == "user_data")
			{
				// we must read the data from the user info, which means that this form
				// must have an entry in the user data for the current user
				// so find that to get the entry id
				classload("users");
				$us = new users;
				$jfe = $us->get_join_entries();
				$entry_id = $jfe[$fid];
			}
			else
			if ($et_type == "user_entry")
			{
				// the first entry made for this form by the current user
				$dat = $form->get_entries(array("user" => aw_global_get("uid"),"max_lines" => 1));
				reset($dat);
				list($entry_id,$entry_name) = each($dat);
			}
			else
			if ($et_type == "same_chain")
			{
				// figure out the current chain entry and load it
				// cur_form_instance wasn't defined
				//$chent = $this->cur_form_instance->get_current_chain_entry();
				// i hope that does the right thing
//				$chent = $form->get_current_chain_entry();
				$chent = aw_global_get("current_chain_entry");
				if ($chent)
				{
					$chd = $this->get_chain_entry($chent);
					$entry_id = $chd[$fid];
				}
			}
			else
			if ($et_type == "session")
			{
				$sff = aw_global_get("session_filled_forms");
//				echo "sff = <pre>", var_dump($sff),"</pre> fid = $fid <br>";
				$entry_id = $sff[$fid];
//				echo "entry id for form $fid = $entry_id <br>";
			}
			else
			if ($et_type == "writer_entry")
			{
				$entry_id = aw_global_get("current_writer_entry");
//				echo "got eid $entry_id <br>";
			}
			else
			if ($et_type == "element_sum")
			{
				$cursums = aw_global_get("fg_element_sums");
				return $cursums[$elid];
			}

			if ($entry_id)
			{
				if ($form->entry_id != $entry_id)
				{
					$form->load_entry($entry_id);
				}
				// and now read the damn value
				$el =& $form->get_element_by_id($elid);
				if (is_object($el))
				{
					$val = $el->get_controller_value();
//					echo "val = $val entry = $el->entry , elid = $elid <br>";
					aw_cache_set("controller::var_value_cache", $cache_key,$val);
					return $val;
				}
			}
		}
	}

	function del_var($arr)
	{
		extract($arr);
		$co = $this->load_controller($id);
		unset($co["meta"]["vars"][$var_name]);
		$this->save_controller($co);
		return $this->mk_my_orb("change", array("id" => $id));
	}

	////
	// !returns, for the current loaded controller whether 
	// error messages should be shown instead of elements in show controllers
	function get_show_errors()
	{
		return $this->loaded_controller["meta"]["show_errors_showctl"];
	}

	////
	// !shows the user in what forms what elements use this controller and lets the user remove it
	function form_list($arr)
	{
		extract($arr);
		$co = $this->load_controller($id);
		$this->read_template("ctrl_form_list.tpl");
		$this->mk_path($co["parent"],"<a href='".$this->mk_my_orb("change", array("id" => $id))."'>Muuda kontrollerit</a> / Formide nimekiri");

		// hoo-kay, now we gots to figure out how to get the forms this is used in
		// since we bloody well can't load every form in the database we must have the relations
		// somewhere written down
		// so we will get table called form_controller2element(ctrl_id, form_id, el_id, type)
	
		$this->vars(array(
			"ENTRY_ELEMENT" => $this->_do_type($id,CTRL_USE_TYPE_ENTRY,"ENTRY_ELEMENT"),
			"SHOW_ELEMENT" => $this->_do_type($id,CTRL_USE_TYPE_SHOW,"SHOW_ELEMENT"),
			"LB_ELEMENT" => $this->_do_type($id,CTRL_USE_TYPE_LB,"LB_ELEMENT"),
			"DEFVL_ELEMENT" => $this->_do_type($id,CTRL_USE_TYPE_DEFVALUE,"DEFVL_ELEMENT"),
			"VL_ELEMENT" => $this->_do_type($id,CTRL_USE_TYPE_VALUE,"VL_ELEMENT"),
			"reforb" => $this->mk_reforb("submit_form_list", array("id" => $id))
		));
		return $this->parse();
	}

	function _do_type($id, $type, $tpl)
	{
		$ret = "";
		$this->db_query("SELECT form_id, el_id, form_o.name as form_name, el_o.name as el_name 
										 FROM form_controller2element AS fc2e
										 LEFT JOIN objects AS form_o ON form_o.oid = fc2e.form_id
										 LEFT JOIN objects AS el_o ON el_o.oid = fc2e.el_id
										 WHERE ctrl_id = '$id' AND type = '$type' AND form_o.status != 0");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"form_name" => $row["form_name"],
				"element_name" => $row["el_name"],
				"form_id" => $row["form_id"],
				"el_id" => $row["el_id"]
			));
			$ret.=$this->parse($tpl);
		}
		return $ret;
	}

	////
	// !saves the changes the user has made to the element list
	function submit_form_list($arr)
	{
		extract($arr);
		$this->_proc_arr($id, $entryels, $entryels_n, CTRL_USE_TYPE_ENTRY);
		$this->_proc_arr($id, $showels, $showels_n, CTRL_USE_TYPE_SHOW);
		$this->_proc_arr($id, $lbels, $lbels_n, CTRL_USE_TYPE_LB);
		$this->_proc_arr($id, $defvlels, $defvlels_n, CTRL_USE_TYPE_DEFVALUE);
		$this->_proc_arr($id, $vlels, $vlels_n, CTRL_USE_TYPE_VALUE);

		return $this->mk_my_orb("form_list", array("id" => $id));
	}

	function _proc_arr($id, $ar, $ar_n, $typ)
	{
		if (is_array($ar))
		{
			foreach($ar as $fid => $forms)
			{
				if (is_array($forms))
				{
					foreach($forms as $elid => $one)
					{
						if ($ar_n[$fid][$elid] != 1)
						{
							// this was removed, load the form and remove controller
							$f = new form;
							$f->load($fid);
							$f->remove_controller_from_element(array(
								"controller" => $id,
								"element" => $elid,
								"type" => $typ
							));
//							echo "tried to remove from form $fid controller $id , element $elid , type = $typ <br>";
							$f->save();
						}
					}
				}
			}
		}
	}
}

?>
