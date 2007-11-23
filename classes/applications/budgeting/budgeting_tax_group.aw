<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/budgeting/budgeting_tax_group.aw,v 1.2 2007/11/23 14:28:43 kristo Exp $
// budgeting_tax_group.aw - Eelarvestamise maksu grupp 
/*

@classinfo syslog_type=ST_BUDGETING_TAX_GROUP relationmgr=yes no_status=1 prop_cb=1 mantainer=kristo

@default table=objects
@default group=general

*/

class budgeting_tax_group extends class_base
{
	function budgeting_tax_group()
	{
		$this->init(array(
			"tpldir" => "applications/budgeting/budgeting_tax_group",
			"clid" => CL_BUDGETING_TAX_GROUP
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
