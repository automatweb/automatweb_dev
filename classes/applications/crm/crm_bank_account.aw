<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_bank_account.aw,v 1.1 2005/10/19 06:44:06 kristo Exp $
// crm_bank_account.aw - CRM Pangakonto 
/*

@classinfo syslog_type=ST_CRM_BANK_ACCOUNT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_crm_bank_account index=aw_oid master_index=brother_of master_table=objects

@default table=objects
@default group=general

	@property acct_no type=textbox table=aw_crm_bank_account field=aw_acct_no
	@caption Arve number

	@property bank type=relpicker reltype=RELTYPE_BANK automatic=1 table=aw_crm_bank_account field=aw_bank
	@caption Pank

@reltype BANK value=1 clid=CL_CRM_BANK
@caption pank

*/

class crm_bank_account extends class_base
{
	function crm_bank_account()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_bank_account",
			"clid" => CL_CRM_BANK_ACCOUNT
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
