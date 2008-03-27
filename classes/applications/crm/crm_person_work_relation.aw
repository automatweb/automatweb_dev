<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_work_relation.aw,v 1.12 2008/03/27 22:13:05 instrumental Exp $
// crm_person_work_relation.aw - T&ouml;&ouml;suhe 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_PHONE, on_connect_phone_to_work_relation)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_ML_MEMBER, on_connect_email_to_work_relation)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_PHONE, on_disconnect_phone_from_work_relation)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_ML_MEMBER, on_disconnect_email_from_work_relation)

@classinfo syslog_type=ST_CRM_PERSON_WORK_RELATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1 no_name=1 maintainer=markop

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property org type=relpicker reltype=RELTYPE_ORG
@caption Organisatsioon

@property section type=hidden reltype=RELTYPE_SECTION
#@property section type=relpicker reltype=RELTYPE_SECTION
#@caption &Uuml;ksus (non-functioning)

@property section2 type=relpicker reltype=RELTYPE_SECTION
@caption &Uuml;ksus

@property profession type=relpicker reltype=RELTYPE_PROFESSION
@caption Amet

@property room type=textbox
@caption T&ouml;&ouml;ruum

@property start type=date_select year_from=1990
@caption Suhte algus

@property end type=date_select
@caption Suhte l&otilde;pp

@property tasks type=textarea
@caption &Uuml;lesanded

@property load type=select
@caption Koormus

@property salary type=textbox field=meta method=serialize
@caption Kuutasu
@comment Bruto

@property salary_currency type=relpicker reltype=RELTYPE_CURRENCY store=connect
@caption Valuuta

@property directive_link type=textbox field=meta method=serialize 
@caption Viit ametijuhendile

@property directive type=relpicker reltype=RELTYPE_DESC_FILE field=meta method=serialize 
@caption Ametijuhend

@property contract_stop type=relpicker reltype=RELTYPE_CONTRACT_STOP field=meta method=serialize 
@caption T&ouml;&ouml;lepingu peatumine

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

@reltype CONTRACT_STOP value=6 clid=CL_CRM_CONTRACT_STOP
@caption T&ouml;&ouml;lepingu peatamine

@reltype SECTION value=7 clid=CL_CRM_SECTION
@caption &Uuml;ksus

@reltype PHONE value=8 clid=CL_CRM_PHONE
@caption Telefon

@reltype EMAIL value=9 clid=CL_ML_MEMBER
@caption E-post

@reltype FAX value=10 clid=CL_CRM_PHONE
@caption Faks

@reltype CURRENCY value=11 clid=CL_CURRENCY
@caption Valuuta

*/

class crm_person_work_relation extends class_base
{
	function crm_person_work_relation()
	{
		$this->init(array(
			"clid" => CL_CRM_PERSON_WORK_RELATION
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "load":
				$data["options"] = object_type::get_classificator_options(array(
					"clid" => CL_PERSONNEL_MANAGEMENT,
					"classificator" => "cv_load",
				));
				break;

			case "section2":
				$data["value"] = $arr["obj_inst"]->prop("section");
				break;
		}
		return $retval;
	}

	function on_connect_phone_to_work_relation($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_PERSON_WORK_RELATION && $target_obj->prop("type") == "fax")
		{
			$target_obj->connect(array(
				"to" => $conn->prop("from"),
				"reltype" => 10,		// RELTYPE_FAX
			));
		}
		else
		{
			$target_obj->connect(array(
				"to" => $conn->prop("from"),
				"reltype" => 8,		// RELTYPE_PHONE
			));
		}
	}

	function on_connect_email_to_work_relation($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_PERSON_WORK_RELATION)
		{
			$target_obj->connect(array(
				"to" => $conn->prop("from"),
				"reltype" => 9,		// RELTYPE_EMAIL
			));
		}
	}

	function on_disconnect_phone_from_work_relation($arr)
	{
		obj_set_opt("no_cache", 1);
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_PERSON_WORK_RELATION)
		{
			if($target_obj->is_connected_to(array('from' => $conn->prop('from'))))
			{
				$target_obj->disconnect(array(
					"from" => $conn->prop("from"),
					"errors" => false
				));
			}
		}
	}

	function on_disconnect_email_from_work_relation($arr)
	{
		obj_set_opt("no_cache", 1);
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_CRM_PERSON_WORK_RELATION)
		{
			if($target_obj->is_connected_to(array('from' => $conn->prop('from'))))
			{
				$target_obj->disconnect(array(
					"from" => $conn->prop("from"),
					"errors" => false
				));
			}
		}
	}
}
?>
