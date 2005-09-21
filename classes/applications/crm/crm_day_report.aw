<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_day_report.aw,v 1.1 2005/09/21 12:47:05 kristo Exp $
// crm_day_report.aw - P&auml;eva raport 
/*

@classinfo syslog_type=ST_CRM_DAY_REPORT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@tableinfo aw_crm_day_report index=aw_oid master_index=brother_of master_table=objects

@property content type=textarea rows=20 cols=50 table=aw_crm_day_report field=aw_content
@caption Tegevused

@property num_hrs type=textbox size=5 table=aw_crm_day_report field=aw_num_hrs
@caption T&ouml;&ouml;tundide arv

*/

class crm_day_report extends class_base
{
	function crm_day_report()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_day_report",
			"clid" => CL_CRM_DAY_REPORT
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
