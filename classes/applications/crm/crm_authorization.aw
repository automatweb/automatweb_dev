<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_authorization.aw,v 1.1 2007/11/21 15:52:36 markop Exp $
// crm_authorization.aw - Volitus 
/*

@classinfo syslog_type=ST_CRM_AUTHORIZATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class crm_authorization extends class_base
{
	function crm_authorization()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_authorization",
			"clid" => CL_CRM_AUTHORIZATION
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
