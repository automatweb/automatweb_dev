<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/object_type.aw,v 2.5 2002/12/19 18:03:11 duke Exp $
// object_type.aw - objekti klass (lisamise puu jaoks)
/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property type type=select
	@caption Objektitüüp

*/

class object_type extends class_base
{
	function object_type()
	{
		$this->init(array(
			"tpldir" => "object_type",
			"clid" => CL_OBJECT_TYPE,
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = true;
		switch($data["name"])
		{
			case "type":
				$data["options"] = $this->get_type_picker();
				break;
		}
		return PROP_OK;
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
