<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_profession.aw,v 1.6 2004/09/20 16:45:21 duke Exp $
// crm_profession.aw - Ameti nimetus 
/*
@classinfo syslog_type=ST_CRM_PROFESSION relationmgr=yes
@tableinfo kliendibaas_amet index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@classinfo no_status=1

@property name_in_plural type=textbox table=kliendibaas_amet
@caption Nimi mitmuses
@comment Ametinimetus mitmuses

@property trans type=translator store=no group=trans props=name,name_in_plural
@caption T�lkimine

@groupinfo trans caption="T�lkimine"

@reltype SIMILARPROFESSION value=1 clid=CL_CRM_PROFESSION
@caption Sarnane amet

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
