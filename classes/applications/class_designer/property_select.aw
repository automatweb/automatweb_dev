<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_select.aw,v 1.1 2005/02/28 10:45:37 duke Exp $
// property_select.aw - Loend 
/*

@classinfo syslog_type=ST_PROPERTY_SELECT relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property multiple type=checkbox ch_value=1
@caption Saab teha mitu valikut

@property size type=textbox size=2 datatype=int
@caption Pikkus (kui on mitu valikut)

@property options type=textarea
@caption Valikud

*/

class property_select extends class_base
{
	function property_select()
	{
		$this->init(array(
			"clid" => CL_PROPERTY_SELECT
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
