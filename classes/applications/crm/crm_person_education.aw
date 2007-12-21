<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_education.aw,v 1.8 2007/12/21 16:29:35 kaarel Exp $
// crm_person_education.aw - Haridus 
/*

@classinfo syslog_type=ST_CRM_PERSON_EDUCATION no_name=1 no_comment=1 no_status=1

@default table=objects
@default group=general

@property school type=textbox field=name
@caption Kool

@default field=meta
@default method=serialize

@property degree type=select
@caption Akadeemiline kraad

@property field type=classificator reltype=RELTYPE_FIELD store=connect
@caption Valdkond

@property speciality type=textbox
@caption Eriala

@property main_speciality type=chooser
@caption Põhieriala

@property in_progress type=chooser
@caption Omandamisel

@property obtain_language type=textbox
@caption Omandamise keel

@layout time type=hbox

@property start type=date_select format=month,year parent=time
@caption Algus

@property end type=date_select format=month,year parent=time
@caption Lõpp

@property end_date type=date_select
@caption Lõpetamise kuupäev

@property diploma_nr type=textbox
@caption Diplomi number

@reltype FIELD value=1 clid=CL_META
@caption Valdkond

*/

class crm_person_education extends class_base
{
	function crm_person_education()
	{
		$this->init(array(
			"clid" => CL_CRM_PERSON_EDUCATION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "main_speciality":
				$arr["prop"]["options"] = array(
					1 => t("Jah"),
					0 => t("Ei"),
				);
				break;

			case "in_progress":
				$arr["prop"]["options"] = array(
					1 => t("Jah"),
					0 => t("Ei"),
				);
				break;

			case "degree":
				$arr["prop"]["options"] = array(
					"pohiharidus" => t("Põhiharidus"),
					"keskharidus" => t("Keskharidus"),
					"keskeriharidus" => t("Kesk-eriharidus"),
					"diplom" => t("Diplom"),
					"bakalaureus" => t("Bakalaureus"),
					"magister" => t("Magister"),
					"doktor" => t("Doktor"),
					"teadustekandidaat" => t("Teaduste kandidaat"),
				);
				break;
		};
		return $retval;
	}
};
?>
