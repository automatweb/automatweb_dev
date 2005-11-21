<?php
// $Header: /home/cvs/automatweb_dev/classes/common/address/address_street.aw,v 1.3 2005/11/21 09:14:56 voldemar Exp $
// address_street.aw - Tänav
/*

@classinfo syslog_type=ST_ADDRESS_STREET relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default field=meta
@default method=serialize
@default group=general
	@property administrative_structure_oid type=hidden

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
