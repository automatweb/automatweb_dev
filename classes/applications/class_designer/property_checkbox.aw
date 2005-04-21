<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_checkbox.aw,v 1.2 2005/04/21 08:39:14 kristo Exp $
// property_checkbox.aw - Element - checkbox 
/*

@classinfo syslog_type=ST_PROPERTY_CHECKBOX relationmgr=yes

@default table=objects
@default group=general

*/

class property_checkbox extends class_base
{
	function property_checkbox()
	{
		$this->init(array(
			"clid" => CL_PROPERTY_CHECKBOX
		));
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
