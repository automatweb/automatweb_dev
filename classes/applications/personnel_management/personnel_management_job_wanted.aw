<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_job_wanted.aw,v 1.9 2008/03/28 09:15:24 instrumental Exp $
// personnel_management_job_wanted.aw - T&ouml;&ouml; soov 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_PERSON, on_connect_person_to_job_wanted)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_PERSON, on_disconnect_person_from_job_wanted)

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_JOB_WANTED relationmgr=yes r2=yes no_status=1 no_comment=1 maintainer=kristo
@tableinfo personnel_management_job_wanted master_table=objects master_index=oid index=oid

@default table=personnel_management_job_wanted
@default group=general

@property field type=classificator multiple=1 reltype=RELTYPE_FIELD orient=vertical store=connect
@caption Tegevusala

@property job_type type=classificator multiple=1 reltype=RELTYPE_JOB_TYPE store=connect
@caption T&ouml;&ouml; liik

@property professions type=textarea field=ametinimetus
@caption Soovitavad ametid

@property load type=select field=koormus
@caption T&ouml;&ouml;koormus

@property pay type=textbox size=5 datatype=int field=palgasoov
@caption Palgasoov alates

@property pay2 type=textbox size=5 datatype=int field=palgasoov2
@caption Palgasoov kuni

@property location type=relpicker multiple=1 automatic=1 orient=vertical store=connect reltype=RELTYPE_LOCATION
@caption T&ouml;&ouml; asukoht

@property addinfo type=textarea field=lisainfo
@caption Lisainfo soovitava t&ouml;&ouml; kohta

@reltype PERSON value=1 clid=CL_CRM_PERSON
@caption T&ouml;&ouml;soovija

@reltype FIELD value=2 clid=CL_META
@caption Valdkond

@reltype LOAD value=3 clid=CL_META
@caption T&ouml;&ouml;koormus

@reltype LOCATION value=4 clid=CL_CRM_CITY,CL_CRM_COUNTY,CL_CRM_COUNTRY,CL_CRM_AREA
@caption Asukoht

@reltype JOB_TYPE value=5 clid=CL_META
@caption T&ouml;&ouml; liik

*/

class personnel_management_job_wanted extends class_base
{
	function personnel_management_job_wanted()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_PERSONNEL_MANAGEMENT_JOB_WANTED
		));
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch ($prop["name"])
		{
			case "load":
				$prop["options"] = object_type::get_classificator_options(array(
					"clid" => CL_PERSONNEL_MANAGEMENT,
					"classificator" => "cv_load",
				));
				break;

			case "sbutton":
				if(is_numeric($_GET["eoid"]))
				{
					$prop["caption"] = "Muuda";
				}
			break;

			case "candidate_toolbar":
				$prop["vcl_inst"]->add_button(array(
					"name" => "add",
					"caption" => t("Lisa"),
					"img" => "new.gif",
				));
				break;

			case "candidate_table":
				$prop["vcl_inst"]->define_field(array(
					"name" => "name",
					"caption" => t("Nimi"),
				));
				$prop["vcl_inst"]->define_data(array(
					"name" => "test",
				));
				break;
		}
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
		return $retval;
	}

	function do_db_upgrade($tbl, $field, $q, $err)
	{
		if ($tbl == "personnel_management_job_wanted" && $field == "")
		{
			$this->db_query("create table personnel_management_job_wanted (oid int primary key)");
			return true;
		}

		switch($field)
		{
			case "ametinimetus":
			case "lisainfo":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "text"
				));
				return true;

			case "koormus":
			case "palgasoov":
			case "palgasoov2":
				$this->db_add_col($tbl, array(
					"name" => $field,
					"type" => "int"
				));
				return true;
		}
	}

	function on_connect_person_to_job_wanted($arr)
	{
		$conn = $arr['connection'];
		$target_obj = $conn->to();
		if($target_obj->class_id() == CL_PERSONNEL_MANAGEMENT_JOB_WANTED)
		{
			$target_obj->connect(array(
				'to' => $conn->prop('from'),
				'reltype' => "RELTYPE_PERSON",
			));
		}
	}

	function on_disconnect_person_from_job_wanted($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_PERSONNEL_MANAGEMENT_JOB_WANTED)
		{
			$target_obj->disconnect(array(
				"from" => $conn->prop("from"),
			));
		};
	}
}
?>
