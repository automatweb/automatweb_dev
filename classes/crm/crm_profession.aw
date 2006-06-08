<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_profession.aw,v 1.12 2006/06/08 14:25:52 markop Exp $
// crm_profession.aw - Ametinimetus
/*
@classinfo syslog_type=ST_CRM_PROFESSION relationmgr=yes
@tableinfo kliendibaas_amet index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@classinfo no_status=1

@property ext_id field=subclass type=textbox
@caption Siduss�steemi ID

@property hr_price field=meta method=serialize type=textbox
@caption Tunnihind

@property jrk type=textbox size=4
@caption J�rk

@property name_in_plural type=textbox table=kliendibaas_amet
@caption Nimi mitmuses
@comment Ametinimetus mitmuses

@property trans type=translator store=no group=trans props=name,name_in_plural
@caption T�lkimine

@groupinfo trans caption="T�lkimine"

@reltype SIMILARPROFESSION value=1 clid=CL_CRM_PROFESSION
@caption Sarnane amet

@reltype GROUP value=2 clid=CL_GROUP
@caption grupp

@reltype DESC_FILE value=3 clid=CL_FILE
@caption Ametijuhend

*/

class crm_profession extends class_base
{
	function crm_profession()
	{
		$this->init(array(
			"clid" => CL_CRM_PROFESSION
		));
	}
};
?>
