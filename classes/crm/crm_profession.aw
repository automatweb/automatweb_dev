<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_profession.aw,v 1.3 2004/06/25 13:05:53 rtoomas Exp $
// crm_profession.aw - Ameti nimetus 
/*
@classinfo syslog_type=ST_CRM_PROFESSION
@tableinfo kliendibaas_amet index=oid master_table=objects master_index=oid
	
relationmgr=yes

@default table=objects
@default group=general

@classinfo no_status=1

@property name_in_plural type=textbox table=kliendibaas_amet
@caption Nimi mitmuses
@comment Ametinimetus mitmuses

@reltype SIMILAR_PROFESSION value=1 clid=CL_CRM_PROFESSION
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
