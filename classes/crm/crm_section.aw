<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_section.aw,v 1.2 2004/04/13 16:05:26 duke Exp $
// crm_section.aw - &Uuml;ksus 
/*

@classinfo syslog_type=ST_CRM_SECTION relationmgr=yes

@default table=objects
@default group=general

*/

class crm_section extends class_base
{
	function crm_section()
	{
		$this->init(array(
			"clid" => CL_CRM_SECTION
		));
	}

	function get_folders_as_object_list($o, $level, $parent)
	{
		$ol = new object_list();
		//$ol->add(125818);
		return $ol;
	}


	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}
	*/

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/
}
?>
