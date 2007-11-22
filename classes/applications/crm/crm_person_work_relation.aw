<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_work_relation.aw,v 1.4 2007/11/22 08:44:23 kaarel Exp $
// crm_person_work_relation.aw - Töösuhe 
/*

@classinfo syslog_type=ST_CRM_PERSON_WORK_RELATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1 no_name=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property org type=relpicker reltype=RELTYPE_ORG
@caption Organisatsioon

@property profession type=relpicker reltype=RELTYPE_PROFESSION
@caption Amet

@property start type=date_select year_from=1990
@caption Suhte algus

@property end type=date_select
@caption Suhte lõpp

@property tasks type=textarea
@caption Ülesanded

@property directive_link type=textbox field=meta method=serialize 
@caption Viit ametijuhendile

@property directive type=relpicker reltype=RELTYPE_DESC_FILE field=meta method=serialize 
@caption Ametijuhend

@reltype ORG value=1 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype PERSON value=2 clid=CL_CRM_PERSON
@caption Isik

@reltype PROFESSION value=3 clid=CL_CRM_PROFESSION
@caption Amet

@reltype SUBSITUTE value=4 clid=CL_CRM_PROFESSION
@caption Asendaja

@reltype DESC_FILE value=5 clid=CL_FILE
@caption Ametijuhend

@reltype CONTRACT_STOP value=6 clid=CL_CONTRACT_STOP
@caption Töölepingu peatamine

*/

class crm_person_work_relation extends class_base
{
	function crm_person_work_relation()
	{
		$this->init(array(
			"clid" => CL_CRM_PERSON_WORK_RELATION
		));
	}
}
?>
