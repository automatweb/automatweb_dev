<?php
/*
@classinfo syslog_type=ST_UNIT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop

@default table=objects
@default group=general
@default field=meta
@default method=seralize

@property unit_code type=textbox
@caption &Uuml;hiku kood

@property unit_sort type=select
@caption &Uuml;hiku liik
*/

class unit extends class_base
{
	function unit()
	{
		$this->init(array(
			"tpldir" => "common//unit",
			"clid" => CL_UNIT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "unit_sort":
				$prop["options"] = array(0 => "" , 1 => t("pikkus&uuml;hik"), 2 => t("massi&uuml;hik"), 3 => t("koguse&uuml;hik"), 4 => t("mahu&uuml;hik"), 5 => t("aja&uuml;hik"));
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
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function get_unit_list($choose = null)
	{
		$ol = new object_list(array(
			"class_id" => CL_UNIT,
			"lang_id" => array(),
			"site_id" => array(),
		));
		if($choose)
		{
			return array(0=>t("--vali--")) + $ol->names();
		}
		return $ol->names();
	}
}
?>
