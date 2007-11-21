<?php
// address_street.aw - Tänav
/*

@classinfo syslog_type=ST_ADDRESS_STREET relationmgr=yes no_comment=1 no_status=1 maintainer=voldemar

@default table=objects
@default field=meta
@default method=serialize
@default group=general
	@property administrative_structure type=hidden

*/

require_once(aw_ini_get("basedir") . "/classes/common/address/as_header.aw");

class address_street extends class_base
{
	function address_street()
	{
		$this->init(array(
			"tpldir" => "common/address",
			"clid" => CL_ADDRESS_STREET
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
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
