<?php

/*

@classinfo syslog_type=ST_FTP_LOGIN

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property server type=textbox field=meta method=serialize
@caption Server

@property username type=textbox field=meta method=serialize
@caption Kasutajanimi

@property password type=password field=meta method=serialize
@caption Parool

*/

class ftp extends class_base
{
	function ftp()
	{
		$this->init(array(
			'tpldir' => 'protocols/file/ftp',
			'clid' => CL_FTP_LOGIN
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

}
?>
