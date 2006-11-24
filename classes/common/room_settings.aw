<?php
// $Header: /home/cvs/automatweb_dev/classes/common/room_settings.aw,v 1.1 2006/11/24 15:36:26 markop Exp $
// room_settings.aw - Ruumi seaded 
/*

@classinfo syslog_type=ST_ROOM_SETTINGS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class room_settings extends class_base
{
	function room_settings()
	{
		$this->init(array(
			"tpldir" => "common/room_settings",
			"clid" => CL_ROOM_SETTINGS
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
