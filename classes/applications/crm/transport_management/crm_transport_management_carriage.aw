<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/transport_management/crm_transport_management_carriage.aw,v 1.1 2006/06/15 12:35:26 dragut Exp $
// carriage.aw - Vedu 
/*

@classinfo syslog_type=ST_CARRIAGE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class carriage extends class_base
{
	function carriage()
	{
		$this->init(array(
			"tpldir" => "transport_management/carriage",
			"clid" => CL_CARRIAGE
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
