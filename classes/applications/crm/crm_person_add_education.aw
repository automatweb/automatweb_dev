<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_add_education.aw,v 1.1 2006/03/28 11:54:59 ahti Exp $
// crm_person_education.aw - Haridus 
/*

@classinfo syslog_type=ST_CRM_PERSON_ADD_EDUCATION no_name=1 no_comment=1 no_status=1

@default table=objects
@default group=general

@property org type=textbox field=name
@caption Ettevõte

@property field type=textbox field=comment
@caption Teema

@default field=meta
@default method=serialize

@property time type=date_select format=month,year
@caption Aeg

@property length type=textbox
@caption Kestvus päevades
@comment Üle kuuajalise koolituse puhul kestvus kuudes

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
