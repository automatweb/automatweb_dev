<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_textbox.aw,v 1.1 2005/02/28 10:45:37 duke Exp $
// property_textbox.aw - Element - tekstikast 
/*

@classinfo syslog_type=ST_PROPERTY_TEXTBOX no_comment=1

@default table=objects
@default group=general

@property ord type=textbox size=2 field=jrk
@caption Jrk

@default field=meta
@default method=serialize

@property size type=textbox size=2 datatype=int
@caption Pikkus

@property maxlength type=textbox size=2 datatype=int
@caption Max. pikkus

*/

class property_textbox extends class_base
{
	function property_textbox()
	{
		$this->init(array(
			"clid" => CL_PROPERTY_TEXTBOX
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
