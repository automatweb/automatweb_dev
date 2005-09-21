<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_document.aw,v 1.1 2005/09/21 12:47:05 kristo Exp $
// crm_document.aw - CRM Dokument 
/*

@classinfo syslog_type=ST_CRM_DOCUMENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@tableinfo aw_crm_document index=aw_oid master_index=brother_of master_table=objects


@default group=general

	@property task type=select table=aw_crm_document field=aw_task
	@caption Juhtum

	@property customer type=select table=aw_crm_document field=aw_customer
	@caption Klient

	@property state type=select table=aw_crm_document field=aw_state
	@caption Staatus

	@property reg_date type=date_select table=aw_crm_document field=aw_reg_date
	@caption Reg kuup&auml;ev

	@property make_date type=date_select table=aw_crm_document field=aw_make_date
	@caption Koostamise kuup&auml;ev

	@property reg_nr type=date_select table=aw_crm_document field=aw_reg_nr
	@caption Registreerimisnumber

@default group=files

	@property files type=releditor reltype=RELTYPE_FILE field=meta method=serialize mode=manager props=name,file,type,comment,file_url,newwindow table_fields=name 
	@caption Failid

@groupinfo files caption="Failid"

@reltype FILE value=1 clid=CL_FILE
@caption fail

*/

class crm_document extends class_base
{
	function crm_document()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_document",
			"clid" => CL_CRM_DOCUMENT
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
