<?php

class swot_type extends class_base
{
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
		$row['parent'] = $parent;
		unset($row['brother_of']);
		$this->quote(&$row);
		$id = $this->new_object($row);
		if ($id)
		{
			return true;
		}
		return false;
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		if ($prop["name"] == "clf")
		{
			$cf = get_instance("classificator");
			$prop['options'] = $cf->get_clfs(array(
				"parent" => $arr['obj']['parent'],
				"clid" => $this->clid,
				"add_empty" => true
			));
		}
		return PROP_OK;
	}
}
?>
