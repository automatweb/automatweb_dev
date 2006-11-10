<?php
// $Header: /home/cvs/automatweb_dev/classes/core/util/ip_locator/ip_locator.aw,v 1.1 2006/11/10 10:43:53 dragut Exp $
// ip_locator.aw - IP lokaator 
/*

@classinfo syslog_type=ST_IP_LOCATOR relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class ip_locator extends class_base
{
	function ip_locator()
	{
		$this->init(array(
			"tpldir" => "core/util/ip_locator/ip_locator",
			"clid" => CL_IP_LOCATOR
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
