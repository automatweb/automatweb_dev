<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_phone.aw,v 1.4 2005/04/21 08:54:56 kristo Exp $
// phone.aw - Telefon 
/*

@classinfo syslog_type=ST_CRM_PHONE relationmgr=yes

@default table=objects
@default group=general

@property name type=textbox
@caption Number

@property comment type=textbox
@caption Kommentaar

@property type type=chooser orient=vertical field=meta method=serialize 
@caption Numbri tüüp

@classinfo no_status=1
*/

/*
@reltype BELONGTO value=1 clid=CL_CRM_ADDRESS,CL_CRM_COMPANY,CL_CRM_PERSON
@caption Numbriga seotud objekt
*/

class crm_phone extends class_base
{
	function crm_phone()
	{
		$this->init(array(
			"clid" => CL_CRM_PHONE
		));
	}

	function get_property($arr)
	{
		$retval = PROP_OK;
		$prop = &$arr["prop"];
		switch($prop["name"])
		{
			case "type":
				$prop["options"] = array(
					"work" => t("tööl"),
					"home" => t("kodus"),
					"short" => t("lühinumber"),
					"mobile" => t("mobiil"),
					"fax" => t("faks"),
					"skype" => t("skype"),
				);
				break;
		};
		return $retval;

	}
	
};
?>
