<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_textarea.aw,v 1.1 2005/02/28 10:45:37 duke Exp $
// property_textarea.aw - Tekstikast 
/*

@classinfo syslog_type=ST_PROPERTY_TEXTAREA relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property cols type=textbox size=2 datatype=int
@caption Laius

@property rows type=textbox size=2 datatype=int
@caption Kõrgus

*/

class property_textarea extends class_base
{
	function property_textarea()
	{
		$this->init(array(
			"clid" => CL_PROPERTY_TEXTAREA
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
