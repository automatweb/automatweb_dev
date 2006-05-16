<?php
// $Header: /home/cvs/automatweb_dev/classes/common/country/country_city.aw,v 1.4 2006/05/16 14:57:41 markop Exp $
// country_city.aw - Linn
/*

@classinfo syslog_type=ST_COUNTRY_CITY relationmgr=yes no_comment=1 no_status=1
@extends common/country/country_administrative_unit

@default table=objects
@default group=general
	@property administrative_structure_oid type=hidden

	@property subclass type=text
	@caption Tüüp

*/

### address system settings
if (!defined ("ADDRESS_SYSTEM"))
{
	define ("ADDRESS_SYSTEM", 1);
	define ("NEWLINE", "<br />");
	define ("ADDRESS_STREET_TYPE", "street"); # used in many places. also in autocomplete javascript -- caution when changing.
	define ("ADDRESS_COUNTRY_TYPE", "country"); # used in many places. also in autocomplete javascript -- caution when changing.
	define ("ADDRESS_DBG_FLAG", "address_dbg");
}
classload("common/country/country_administrative_unit");
class country_city extends country_administrative_unit
{
	function country_city()
	{
		$this->init(array(
			"tpldir" => "common/country",
			"clid" => CL_COUNTRY_CITY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "subclass":
				if (is_oid ($prop["value"]))
				{
					$administrative_unit = obj ($prop["value"]);
					$prop["value"] = $administrative_unit->name ();
				}
				else
				{
					return PROP_IGNORE;
				}
				break;
		}

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

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

/**
	@attrib name=show
**/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}

?>
