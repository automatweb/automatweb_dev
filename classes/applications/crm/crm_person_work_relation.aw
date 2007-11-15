<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_work_relation.aw,v 1.3 2007/11/15 13:31:21 hannes Exp $
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

@reltype ORG value=1 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype PERSON value=2 clid=CL_CRM_PERSON
@caption Isik

@reltype PROFESSION value=3 clid=CL_CRM_PROFESSION
@caption Amet

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
