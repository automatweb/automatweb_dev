<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_person_language.aw,v 1.2 2006/04/05 13:44:43 ahti Exp $
// crm_person_language.aw - Keeleoskus
/*

@classinfo syslog_type=ST_CRM_PERSON_LANGUAGE no_name=1 no_comment=1 no_status=1

@default table=objects
@default group=general

@property language type=textbox field=name
@caption Keel

@default field=meta
@default method=serialize

@property talk type=select
@caption Räägin

@property understand type=select
@caption Saan aru

@property write type=select
@caption Kirjutan

*/

class crm_person_language extends class_base
{
	function crm_person_language()
	{
		$this->init(array(
			"clid" => CL_CRM_PERSON_LANGUAGE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "talk":
			case "understand":
			case "write":
				$prop["options"] = array(
					0 => t("-- vali --"),
					1 => t("ei oska"),
					2 => t("napp"),
					3 => t("keskmine"),
					4 => t("hea"),
					5 => t("väga hea"),
				);
				break;
		}
		return $retval;
	}
};
?>
