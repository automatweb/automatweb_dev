<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_education.aw,v 1.3 2006/03/28 11:56:27 ahti Exp $
// crm_person_education.aw - Haridus 
/*

@classinfo syslog_type=ST_CRM_PERSON_EDUCATION no_name=1 no_comment=1 no_status=1

@default table=objects
@default group=general

@property school type=textbox field=name
@caption Kool

@default field=meta
@default method=serialize

@property field type=classificator
@caption Valdkond

@property speciality type=textbox
@caption Eriala

@layout time type=hbox

@property start type=date_select format=month,year parent=time
@caption Algus

@property end type=date_select format=month,year parent=time
@caption Lõpp

*/

class crm_person_education extends class_base
{
	function crm_person_education()
	{
		$this->init(array(
			"clid" => CL_CRM_PERSON_EDUCATION
		));
	}
};
?>
