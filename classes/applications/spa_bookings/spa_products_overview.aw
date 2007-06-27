<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/spa_bookings/spa_products_overview.aw,v 1.1 2007/06/27 07:07:57 tarvo Exp $
// spa_products_overview.aw - Broneeringute toitlustuse haldus 
/*

@classinfo syslog_type=ST_SPA_PRODUCTS_OVERVIEW relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class spa_products_overview extends class_base
{
	function spa_products_overview()
	{
		$this->init(array(
			"tpldir" => "applications/spa_bookings/spa_products_overview",
			"clid" => CL_SPA_PRODUCTS_OVERVIEW
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
