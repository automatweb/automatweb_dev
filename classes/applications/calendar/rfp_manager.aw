<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/rfp_manager.aw,v 1.1 2006/12/21 13:31:52 tarvo Exp $
// rfp_manager.aw - RFP Haldus 
/*

@classinfo syslog_type=ST_RFP_MANAGER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class rfp_manager extends class_base
{
	function rfp_manager()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/rfp_manager",
			"clid" => CL_RFP_MANAGER
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
