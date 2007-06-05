<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_service_type.aw,v 1.1 2007/06/05 09:41:23 kristo Exp $
// crm_service_type.aw - CRM Teenuse liik 
/*

@classinfo syslog_type=ST_CRM_SERVICE_TYPE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property hr_price type=textbox size=5 table=objects field=meta method=serialize
@caption Tunnihind

*/

class crm_service_type extends class_base
{
	function crm_service_type()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_service_type",
			"clid" => CL_CRM_SERVICE_TYPE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
}
?>
