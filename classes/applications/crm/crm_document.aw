<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_document.aw,v 1.10 2006/01/19 22:41:01 kristo Exp $
// crm_document.aw - CRM Dokument 
/*

@classinfo syslog_type=ST_CRM_DOCUMENT relationmgr=yes no_status=1 prop_cb=1

@default table=objects
@tableinfo aw_crm_document index=aw_oid master_index=brother_of master_table=objects


@default group=general

	@property project type=popup_search clid=CL_PROJECT table=aw_crm_document field=aw_project
	@caption Projekt

	@property task type=popup_search clid=CL_TASK table=aw_crm_document field=aw_task
	@caption &Uuml;lesanne

	@property customer type=popup_search clid=CL_CRM_COMPANY table=aw_crm_document field=aw_customer
	@caption Klient

	@property creator type=relpicker reltype=RELTYPE_CREATOR table=aw_crm_document field=aw_creator
	@caption Koostaja

	@property reader type=relpicker reltype=RELTYPE_READER table=aw_crm_document field=aw_reader
	@caption Lugeja

	@property state type=select table=aw_crm_document field=aw_state
	@caption Staatus

	@property reg_date type=date_select table=aw_crm_document field=aw_reg_date
	@caption Reg kuup&auml;ev

	@property make_date type=date_select table=aw_crm_document field=aw_make_date
	@caption Koostamise kuup&auml;ev

	@property reg_nr type=date_select table=aw_crm_document field=aw_reg_nr
	@caption Registreerimisnumber

	@property comment type=textarea rows=5 cols=50 table=objects field=comment
	@caption Kirjeldus

@default group=files

	@property files type=releditor reltype=RELTYPE_FILE field=meta method=serialize mode=manager props=name,file,type,comment,file_url,newwindow table_fields=name 
	@caption Failid

@default group=parts

	@property parts_tb type=toolbar no_caption=1

	@property acts type=table store=no no_caption=1
	@caption Tegevused

@groupinfo files caption="Failid"
@groupinfo parts caption="Osalejad" 

@reltype FILE value=1 clid=CL_FILE
@caption fail

@reltype CREATOR value=2 clid=CL_CRM_PERSON
@caption looja

@reltype READER value=3 clid=CL_CRM_PERSON
@caption lugeja

@reltype ACTION value=8 clid=CL_CRM_DOCUMENT_ACTION
@caption Tegevus
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
		$b = get_instance("applications/crm/crm_document_base");
		$retval = $b->get_property($arr);

		$prop = &$arr["prop"];
		switch($prop["name"])
		{
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$b = get_instance("applications/crm/crm_document_base");
		$retval = $b->set_property($arr);

		$prop = &$arr["prop"];
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
