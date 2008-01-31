<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_profession.aw,v 1.17 2008/01/31 13:54:16 kristo Exp $
// crm_profession.aw - Ametinimetus
/*
@classinfo syslog_type=ST_CRM_PROFESSION relationmgr=yes maintainer=markop
@tableinfo kliendibaas_amet index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@classinfo no_status=1

@property ext_id field=subclass type=textbox
@caption Sidussüsteemi ID

@property hr_price field=meta method=serialize type=textbox
@caption Tunnihind

@property jrk type=textbox size=4
@caption Järk

@property directive_link type=textbox field=meta method=serialize 
@caption Viit ametijuhendile

@property directive type=relpicker reltype=RELTYPE_DESC_FILE field=meta method=serialize 
@caption Ametijuhend

@property name_in_plural type=textbox table=kliendibaas_amet
@caption Nimi mitmuses
@comment Ametinimetus mitmuses

@property skills type=relpicker reltype=RELTYPE_SKILL store=connect multiple=1 automatic=1
@caption P&auml;devused

@property trans type=translator store=no group=trans props=name,name_in_plural
@caption Tõlkimine

@groupinfo trans caption="Tõlkimine"

@reltype SIMILARPROFESSION value=1 clid=CL_CRM_PROFESSION
@caption Sarnane amet

@reltype GROUP value=2 clid=CL_GROUP
@caption grupp

@reltype DESC_FILE value=3 clid=CL_FILE
@caption Ametijuhend

@reltype SKILL value=4 clid=CL_PERSON_SKILL
@caption Oskus

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
