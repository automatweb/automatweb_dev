<?php

class object_type extends aw_template
{
	function object_type()
	{
		$this->init("object_type");
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
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa object_type");
		}
		else
		{
			$this->mk_path($parent,"Lisa object_type");
		}
		$this->read_template("change.tpl");

		$this->vars(array(
			"types" => $this->picker(0, $this->get_type_picker()),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent, "alias_to" => $alias_to, "return_url" => $return_url))
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
		if ($type && $name == "")
		{
			$dat = $this->get_type_picker();
			$name = $dat[$type];
		}

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
				"class_id" => CL_OBJECT_TYPE
			));
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		$this->set_object_metadata(array(
			"oid" => $id,
			"key" => "type",
			"value" => $type
		));

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
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda object_type");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda object_type");
		}
		$this->read_template("change.tpl");
	
		$this->vars(array(
			"name" => $ob["name"],
			"types" => $this->picker($ob["meta"]["type"], $this->get_type_picker()),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => urlencode($return_url)))
		));

		return $this->parse();
	}

	function get_type_picker()
	{
		$ret = array();
		foreach($this->cfg["classes"] as $clid => $cldat)
		{
			if ($cldat["can_add"] == 1)
			{
				$ret[$clid] = $cldat["name"];
			}
		}
		asort($ret);
		$ret = array("__all_objs" => "K&otilde;ik") + $ret;
		return $ret;
	}

	////////////////////////////////////
	// object persistance functions - used when copying/pasting object
	// if the object does not support copy/paste, don't define these functions
	////////////////////////////////////

	////
	// !this should create a string representation of the object
	// parameters
	//    oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		return serialize($ob);
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = unserialize($str);
		$row["parent"] = $parent;
		$this->quote(&$row);
		$id = $this->new_object($row);
		return true;
	}
}
?>