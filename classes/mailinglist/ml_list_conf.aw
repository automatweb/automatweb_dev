<?php

class ml_list_conf extends aw_template
{
	function ml_list_conf()
	{
		$this->init("mailinglist/ml_list_conf");
	}

	////
	// !called, when adding a new object 
	// parameters:
	//    parent - the folder under which to add the object
	//    return_url - optional, if set, the "back" link should point to it
	//    alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created
	function add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa ml_list_conf");
		}
		else
		{
			$this->mk_path($parent,"Lisa ml_list_conf");
		}
		$this->read_template("change.tpl");

		$finst = get_instance("formgen/form");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to, "return_url" => $return_url)),
			"forms" => $this->mpicker(array(), $finst->get_flist(array("type" => FTYPE_ENTRY, "addempty" => true, "addfolders" => true, "sort" => true))),
			"folders" => $this->mpicker(array(), $this->get_menu_list())
		));
		return $this->parse();
	}

	////
	// !this gets called when the user submits the object's form
	// parameters:
	//    id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_ML_LIST_CONF
			));
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"user_form" => $this->make_keys($user_form),
				"folders" => $this->make_keys($folder),
				"name_els" => $this->make_keys($name_els),
				"form_jrk" => $jrk,
				"mailto_el" => $mailto_el
			)
		));

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	//    id - the id of the object to change
	//    return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda ml_list_conf");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda ml_list_conf");
		}
		$this->read_template("change.tpl");
	
		$finst = get_instance("formgen/form");
		$flist = $finst->get_flist(array("type" => FTYPE_ENTRY, "addempty" => true, "addfolders" => true, "sort" => true));

		$elements = array();

		$f = "";
		$ar = new aw_array($ob["meta"]["user_form"]);
		foreach($ar->get() as $fid)
		{
			$ml = $finst->get_form_elements(array("id" => $fid, "key" => "id", "all_data" => false));
			foreach($ml as $k => $v)
			{
				$elements[$k] = $v;
			}

			$this->vars(array(
				"form" => $flist[$fid],
				"form_id" => $fid, 
				"jrk" => $ob["meta"]["form_jrk"][$fid]
			));
			$f.=$this->parse("FORM");
		}

		$e = "";
		foreach($elements as $elid => $eln)
		{
			$this->vars(array(
				"elname" => $eln,
				"elid" => $elid,
				"is_name_el" => checked($ob["meta"]["name_els"][$elid])
			));
			$e.= $this->parse("ELEMENT");
		}
		$this->vars(array(
			"FORM" => $f,
			"ELEMENT" => $e,
			"mailto_el" => $this->picker($ob["meta"]["mailto_el"], $elements)
		));

		$this->vars(array(
			"CHANGE" => $this->parse("CHANGE"),
			"name" => $ob["name"],
			"forms" => $this->mpicker($ob["meta"]["user_form"], $flist),
			"folders" => $this->mpicker($ob["meta"]["folders"], $this->get_menu_list()),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		if (is_array($ob))
		{
			return aw_serialize($ob, SERIALIZE_NATIVE);
		}
		return false;
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = aw_unserialize($str);
		$row["parent"] = $parent;
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	////
	// !returns a list of form id's that the user selected as forms for list config object $id
	function get_forms_by_id($id)
	{
		$this->ob = $this->get_object($id);
		// here we gots to sort the forms as well
		$ar = new aw_array($this->ob["meta"]["user_form"]);
		uasort($ar->get(), array($this, "__uf_cmp"));
		return $this->ob["meta"]["user_form"];
	}

	function __uf_cmp($a, $b)
	{
		if ($this->ob["meta"]["form_jrk"][$a] > $this->ob["meta"]["form_jrk"][$b])
		{
			return -1;
		}
		else
		if ($this->ob["meta"]["form_jrk"][$a] < $this->ob["meta"]["form_jrk"][$b])
		{
			return 1;
		}
		return 0;
	}

	function get_folders_by_id($id)
	{
		$ret = array();
		$ob = $this->get_object($id);

		$ol = $this->get_menu_list();

		$ar = new aw_array($ob["meta"]["folders"]);
		foreach($ar->get() as $fid)
		{
			$ret+= array($fid => $ol[$fid]);
			$ml = $this->get_menu_list(false, false, $fid);
			foreach($ml as $mk => $mv)
			{
				$ret+= array($mk => $ol[$fid]."/".$mv);
			}
		}
		asort($ret);
		return $ret;
	}

	////
	// !returns an array of element id's whose values make up the name for the list member
	function get_name_els_by_id($id)
	{
		$ob = $this->get_object($id);
		$ar = new aw_array($ob["meta"]["name_els"]);
		return $ar->get();
	}

	function get_mailto_element($id)
	{
		$ob = $this->get_object($id);
		return $ob["meta"]["mailto_el"];
	}
}
?>