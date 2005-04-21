<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_group.aw,v 1.2 2005/04/21 08:39:15 kristo Exp $
// property_group.aw - Vormi grupp 
/*

@classinfo syslog_type=ST_PROPERTY_GROUP relationmgr=yes

@default table=objects
@default group=general

@property ord type=textbox size=2 field=jrk
@caption Jrk

@default field=meta
@default method=serialize

@property no_submit type=checkbox ch_value=1 
@caption Salvesta nuppu pole tarvis

*/

class property_group extends class_base
{
	function property_group()
	{
		$this->init(array(
			"clid" => CL_PROPERTY_GROUP
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
