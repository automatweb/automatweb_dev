<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_toolbar_button.aw,v 1.1 2005/03/03 15:20:55 kristo Exp $
// property_toolbar_button.aw - Taoolbari nupp 
/*

@classinfo syslog_type=ST_PROPERTY_TOOLBAR_BUTTON relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property b_type type=select 
@caption Nupu t&uuml;&uuml;p
*/

class property_toolbar_button extends class_base
{
	function property_toolbar_button()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer/property_toolbar_button",
			"clid" => CL_PROPERTY_TOOLBAR_BUTTON
		));

		$this->button_types = array(
			"sep" => "Eraldaja",
			"but" => "Nupp", 
			"men" => "Menu&uuml;&uuml;"
		);
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "b_type":
				$prop["options"] = $this->button_types;
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
}
?>
