<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_field_other.aw,v 1.1 2006/07/12 13:29:37 kristo Exp $
// crm_field_other.aw - Muu (valdkond) 
/*

@classinfo syslog_type=ST_CRM_FIELD_OTHER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property location type=chooser
@caption Asukoht

@property languages type=chooser multiple=1
@caption Teeninduskeeled

@property price_level type=chooser multiple=1
@caption Hinnaklass

*/

class crm_field_other extends class_base
{
	function crm_field_other()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_field_other",
			"clid" => CL_CRM_FIELD_OTHER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'location':
				$prop["options"] = array(
					'loc_city' => t("Kesklinnas"),
					'loc_outside' => t("V&auml;ljaspool kesklinna"),
					'loc_country' => t("V&auml;ljaspool linna"),
				);
			break;
			case 'price_level':
				$prop["options"] = array(
					'price_A' => t("A"),
					'price_B' => t("B"),
					'price_C' => t("C"),
					'price_D' => t("D"),
					'price_E' => t("E"),
				);
			break;
			case 'languages':
				$langs = aw_ini_get('languages.list');
				$prop["options"] = array();
				foreach ($langs as $lang)
				{
					$prop["options"][$lang['acceptlang']] = t($lang['name']);
				}
			break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
}
?>
