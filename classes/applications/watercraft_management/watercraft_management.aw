<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/watercraft_management/watercraft_management.aw,v 1.1 2006/08/08 12:58:05 dragut Exp $
// watercraft_management.aw - Vees�idukite haldus 
/*

@classinfo syslog_type=ST_WATERCRAFT_MANAGEMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class watercraft_management extends class_base
{
	function watercraft_management()
	{
		$this->init(array(
			"tpldir" => "applications/watercraft_management/watercraft_management",
			"clid" => CL_WATERCRAFT_MANAGEMENT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
