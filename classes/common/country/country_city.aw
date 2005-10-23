<?php
// $Header: /home/cvs/automatweb_dev/classes/common/country/country_city.aw,v 1.1 2005/10/23 17:17:15 voldemar Exp $
// country_city.aw - Linn
/*

@classinfo syslog_type=ST_COUNTRY_CITY relationmgr=yes no_comment=1 no_status=1
@extends common/country/country_administrative_unit

@default table=objects
@default group=general
	@property subclass type=text
	@caption Tüüp

*/

### address system settings
define ("ADMINISTRATIVE_CFG_TYPE_UNIT", 1);


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
