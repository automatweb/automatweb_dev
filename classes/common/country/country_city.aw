<?php
/*
@classinfo syslog_type=ST_COUNTRY_CITY relationmgr=yes no_comment=1 no_status=1 maintainer=voldemar
@extends common/country/country_administrative_unit

@default table=objects
@default group=general
	@property administrative_structure type=hidden

	@property subclass type=text
	@caption T&uuml;&uuml;p

	@property complete_name type=textbox field=meta method=serialize
	@caption T&auml;isnimi

	@property alt_name type=textbox field=meta method=serialize
	@caption Paralleelnimi

	@property ext_id_1 type=textbox field=meta method=serialize
	@caption Identifikaator v&auml;lises s&uuml;steemis 1

@groupinfo transl caption="T&otilde;lgi"
@default group=transl

@property transl type=callback callback=callback_get_transl store=no
@caption T&otilde;lgi

*/

require_once(aw_ini_get("basedir") . "/classes/common/address/as_header.aw");
classload("common/country/country_administrative_unit");

class country_city extends country_administrative_unit
{
	function country_city()
	{
		$this->init(array(
			"tpldir" => "common/country",
			"clid" => CL_COUNTRY_CITY
		));
		$this->trans_props = array(
			"name"
		);
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
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;
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

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}
}

?>
