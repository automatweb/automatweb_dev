<?php

/*

@classinfo syslog_type=ST_AIP_CONF relationmgr=yes

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property change_folder type=relpicker type=RELATION_CHANGE_FOLDER field=meta method=serialize
@caption Muudatuste kataloog

*/

define("RELATION_CHANGE_FOLDER", 1);

class aip_conf extends class_base
{
	function aip_conf()
	{
		$this->init(array(
			'clid' => CL_AIP_CONF
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

	function callback_get_rel_types()
	{
		return array(
			RELATION_CHANGE_FOLDER => "muudatuste kataloog",
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELATION_CHANGE_FOLDER)
		{
			return array(CL_PSEUDO);
		}
	}
}
?>
