<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_phone.aw,v 1.5 2005/12/27 21:27:14 ekke Exp $
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

@property country type=relpicker reltype=RELTYPE_COUNTRY field=meta method=serialize automatic=1
@caption Riik

@classinfo no_status=1
*/

/*
@reltype BELONGTO value=1 clid=CL_CRM_ADDRESS,CL_CRM_COMPANY,CL_CRM_PERSON
@caption Numbriga seotud objekt

@reltype COUNTRY value=2 clid=CL_CRM_COUNTRY
@caption Riik 
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

	// Returns nicer view (formatted, with or without country code)
	//  oid - id of phone object
	//  show_area_code - boolean, default true
	
	function show($arr)
	{
		$return = "";
		$oid = $arr['oid'];
		if (!is_oid($oid) || !$this->can('view', $oid))
		{
			return;
		}
		$o = obj($oid);
		if ($o->class_id() != CL_CRM_PHONE)
		{
			return;
		}
		
		$ccode = true;
		if (!empty($arr['show_area_code']) && !$arr['show_area_code'])
		{
			$ccode = false;
		}
		if ($ccode)
		{
			$country = $o->get_first_obj_by_reltype(array(
				'reltype' => 'RELTYPE_COUNTRY',
			));
			if ($country)
			{
				$code = $country->prop('area_code');
				if (strlen($code))
				{
					$return = '+'.$code.' ';
				}
			}
		}
		$return .= $o->name();
		return $return;
	}
	
};
?>
