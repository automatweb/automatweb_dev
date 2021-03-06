<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_work_relation.aw,v 1.31 2008/11/13 16:16:23 markop Exp $
// crm_person_work_relation.aw - T&ouml;&ouml;suhe 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CRM_PHONE, on_connect_phone_to_work_relation)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_ML_MEMBER, on_connect_email_to_work_relation)

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_CRM_PHONE, on_disconnect_phone_from_work_relation)
HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_DELETE_FROM, CL_ML_MEMBER, on_disconnect_email_from_work_relation)

@classinfo syslog_type=ST_CRM_PERSON_WORK_RELATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1 no_name=1 maintainer=markop

@default table=objects
@default group=general

@property org type=relpicker reltype=RELTYPE_ORG store=connect
property org type=relpicker reltype=RELTYPE_ORG store=connect mode=autocomplete option_is_tuple=1

@caption Organisatsioon

#kommenteerisin v2lja need autocompleted j2lle, kui huvi on , siis kommenteerige sisse tagasi, a igasugu new korral tulevad company, section ja profession v22rtused peaks ka lisaks k6igele muule toimima
#@property section type=relpicker reltype=RELTYPE_SECTION
@property section type=hidden reltype=RELTYPE_SECTION store=connect
@caption &Uuml;ksus

@property section2 type=relpicker reltype=RELTYPE_SECTION store=connect
property section2 type=relpicker reltype=RELTYPE_SECTION store=connect mode=autocomplete option_is_tuple=1
@caption &Uuml;ksus

@property profession type=relpicker reltype=RELTYPE_PROFESSION store=connect
property profession type=relpicker reltype=RELTYPE_PROFESSION store=connect mode=autocomplete option_is_tuple=1
@caption Amet

@property field type=classificator reltype=RELTYPE_FIELD store=connect sort_callback=CL_PERSONNEL_MANAGEMENT::cmp_function
@caption Valdkond

@default field=meta
@default method=serialize

@property room type=textbox
@caption T&ouml;&ouml;ruum

#@property start type=date_select year_from=1970
@property start type=select
@caption Suhte algus

#@property end type=date_select year_from=1970
@property end type=select
@caption Suhte l&otilde;pp

@property tasks type=textarea
@caption &Uuml;lesanded

@property load type=select
@caption Koormus

@property salary type=textbox
@caption Kuutasu
@comment Bruto

@property salary_currency type=relpicker reltype=RELTYPE_CURRENCY store=connect
@caption Valuuta

@property benefits type=textarea
@caption Soodustused ja eritingimused

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

@reltype FIELD value=12 clid=CL_META
@caption Valdkond

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
			case "start":		
				$ops["---"] = "---";
				for($i = date("Y") +1; $i >= 1970; $i--)
				{
					$ops[mktime(0, 0, 0, 1, 1, $i)] = $i;
				}
				$data["options"] = $ops;
				$data["onchange"] = "asd = this.name; asd = asd.replace('start', 'end'); if(aw_get_el(asd).value - this.value < 0 && aw_get_el(asd).value != 0){ alert('".t("Algus ei saa olla suurem kui l&otilde;pp!")."'); aw_get_el(asd).value = this.value; }";
				break;

			case "end":	
				$ops["---"] = "---";
				for($i = date("Y") +1; $i >= 1970; $i--)
				{
					$ops[mktime(0, 0, 0, 1, 1, $i)] = $i;
				}
				$data["options"] = $ops;
				$data["onchange"] = "asd = this.name; asd = asd.replace('end', 'start'); if(aw_get_el(asd).value - this.value > 0 && aw_get_el(asd).value != 0){ alert('".t("L&otilde;pp ei saa olla v&auml;iksem kui algus!")."'); aw_get_el(asd).value = this.value; }";
				break;

			case "load":
				$r = get_instance(CL_CLASSIFICATOR)->get_choices(array(
					"clid" => CL_PERSONNEL_MANAGEMENT,
					"name" => "cv_load",
					"sort_callback" => "CL_PERSONNEL_MANAGEMENT::cmp_function",
				));
				$data["options"] = $r[4]["list_names"];
				break;

			case "section2":
			case "org":
			case "profession":
				$data["option_is_tuple"] = true;
				if($data["option_is_tuple"] && $this->can("view", $data["value"]))
				{
					$data["content"] = obj($data["value"])->name();
				}
				else
				{
					$data["content"] = $data["value"];
				}
				break;
		}
		return $retval;
	}

	function set_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "section":
				if($arr["request"]["section"])
				{
					$data["value"] = $arr["request"]["section"];
					$data["options"] = array($data["value"] => get_name($data["value"]));
				}
				break;
	
			case "org":
				if($arr["request"]["company"])
				{
					$data["value"] = $arr["request"]["company"];
					$data["options"] = array($data["value"] => get_name($data["value"]));
				}
				break;
			case "section2":
			case "profession":

				if($arr["request"]["profession"])
				{
					$data["value"] = $arr["request"]["profession"];
					$data["options"] = array($data["value"] => get_name($data["value"]));
				}
//				$data["option_is_tuple"] = true;
/*
				$clids = array(
					"org" => CL_CRM_COMPANY,
					"section2" => CL_CRM_SECTION,
					"profession" => CL_CRM_PROFESSION,
				);
				if(empty($prop["value"]))
				{
					$retval = PROP_IGNORE;
				}
				else
				if(!is_oid($prop["value"]))
				{
					// option_is_tuple
					$name = $prop["value"];
					$ol = new object_list(array(
						"class_id" => $clids[$prop["name"]],
						"name" => $name,
						"lang_id" => array(),
						"site_id" => array(),
						"limit" => 1,
					));
					if($ol->count() > 0)
					{
						$prop["value"] = reset($ol->ids());
					}
					else
					{
						$o = obj();
						$o->set_class_id($clids[$prop["name"]]);
						$o->set_parent($arr["obj_inst"]->parent());
						$o->set_name($name);
						$o->save();
						$prop["value"] = $o->id();
					}
					$arr["obj_inst"]->set_prop($prop["name"], $prop["value"]);
					$retval = PROP_IGNORE;
				}
				else
				{
					$arr["obj_inst"]->set_prop($prop["name"], $prop["value"]);
					$retval = PROP_IGNORE;
				}
*/
				break;
		}
		return $retval;
	}

	function callback_post_save($arr)
	{

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

	function cmp_function($a, $b)
	{
		if($a->ord() == $b->ord())
		{
			return strcmp($a->trans_get_val("name"), $b->trans_get_val("name"));
		}
		else
		{
			return (int)$a->ord() > (int)$b->ord() ? 1 : -1;
		}
	}
}
?>
