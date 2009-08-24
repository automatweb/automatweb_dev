<?php
/*
@classinfo syslog_type=ST_COUNTRY_CITYDISTRICT relationmgr=yes no_comment=1 no_status=1 maintainer=voldemar
@extends common/country/country_administrative_unit

@default table=objects
@default group=general
	@property administrative_structure type=hidden

	@property subclass type=text
	@caption Tüüp

	@property complete_name type=textbox field=meta method=serialize
	@caption T&auml;isnimi

	@property alt_name type=textbox field=meta method=serialize
	@caption Paralleelnimi

	@property ext_id_1 type=textbox field=meta method=serialize
	@caption Identifikaator v&auml;lises s&uuml;steemis 1


*/

require_once(aw_ini_get("basedir") . "/classes/common/address/as_header.aw");

class country_citydistrict extends country_administrative_unit
{
	function country_citydistrict ()
	{
		$this->init(array(
			"tpldir" => "common/country",
			"clid" => CL_COUNTRY_CITYDISTRICT
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
