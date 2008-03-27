<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_add_education.aw,v 1.3 2008/03/27 09:18:04 instrumental Exp $
// crm_person_education.aw - Haridus 
/*

@classinfo syslog_type=ST_CRM_PERSON_ADD_EDUCATION no_name=1 no_comment=1 no_status=1 maintainer=markop

@default table=objects
@default group=general

@property org type=textbox field=name
@caption Ettev&otilde;te

@property field type=textbox field=comment
@caption Teema

@default field=meta
@default method=serialize

@property time type=date_select year_from=1980
@caption Algus

@property length type=textbox
@caption Kestvus p&auml;evades
@comment &Uuml;le kuuajalise koolituse puhul kestvus kuudes

*/

class crm_person_add_education extends class_base
{
	function crm_person_add_education()
	{
		$this->init(array(
			"clid" => CL_CRM_PERSON_ADD_EDUCATION
		));
	}
};
?>
