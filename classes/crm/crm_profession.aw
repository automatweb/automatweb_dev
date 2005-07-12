<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_profession.aw,v 1.10 2005/07/12 14:30:05 voldemar Exp $
// crm_profession.aw - Ametinimetus
/*
@classinfo syslog_type=ST_CRM_PROFESSION relationmgr=yes
@tableinfo kliendibaas_amet index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@classinfo no_status=1

@property ext_id field=subclass type=textbox
@caption Sidussüsteemi ID

@property name_in_plural type=textbox table=kliendibaas_amet
@caption Nimi mitmuses
@comment Ametinimetus mitmuses

@property trans type=translator store=no group=trans props=name,name_in_plural
@caption Tõlkimine

@groupinfo trans caption="Tõlkimine"

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
