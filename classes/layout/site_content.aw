<?php

/*

@classinfo syslog_type=ST_SITE_CONTENT

@groupinfo general caption=Üldine

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

*/

class site_content extends class_base
{
	function site_content()
	{
		$this->init(array(
			'clid' => CL_SITE_CONTENT
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
	// !this shows the object. 
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$pd = get_instance("layout/active_page_data");
		$mned = get_instance("contentmgmt/site_content");

 		if (($txt = $pd->get_text_content()) != "")
		{
			return $txt;
		}
		else
		{
			return $mned->show_documents($pd->get_active_section(), 0);
		}
	}
}
?>
