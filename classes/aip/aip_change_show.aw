<?php

/*

@classinfo syslog_type=ST_AIP_CHANGE_SHOW

@groupinfo general caption=�ldine

@default table=objects
@default group=general

@property type type=select field=meta method=serialize
@caption Muudatuse t&uuml;&uuml;p

*/

class aip_change_show extends class_base
{
	function aip_change_show()
	{
		$this->init(array(
			'tpldir' => 'aip/aip_change_show',
			'clid' => CL_AIP_CHANGE_SHOW
		));
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

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$ac = get_instance("aip_change");
		return $ac->show_files(array(
			"type" => $ob['meta']['type']
		));
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		if ($prop["name"] == "type")
		{
			$prop['options'] = array("1" => "AIP AMDT", "2" => "AIRAC AIP AMDT","3" => "SUP");
		}
		return PROP_OK;
	}
}
?>
