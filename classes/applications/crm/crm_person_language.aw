<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_language.aw,v 1.1 2006/04/05 13:06:22 ahti Exp $
// crm_person_language.aw - Keeleoskus
/*

@classinfo syslog_type=ST_CRM_PERSON_LANGUAGE no_name=1 no_comment=1 no_status=1

@default table=objects
@default group=general

@property school type=textbox field=name
@caption Kool

@default field=meta
@default method=serialize

@property field type=classificator reltype=RELTYPE_FIELD store=connect
@caption Valdkond

@property speciality type=textbox
@caption Eriala

@layout time type=hbox

@property start type=date_select format=month,year parent=time
@caption Algus

@property end type=date_select format=month,year parent=time
@caption Lõpp

@reltype FIELD value=1 clid=CL_META
@caption Valdkond

*/

class crm_person_language extends class_base
{
	function crm_person_language()
	{
		$this->init(array(
			"clid" => CL_CRM_PERSON_LANGUAGE
		));
	}
};
?>
