<?php
// intellectual_property.aw - Intellektuaalne omand
/*

@classinfo syslog_type=ST_INTELLECTUAL_PROPERTY relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class intellectual_property extends class_base
{
	function intellectual_property()
	{
		$this->init(array(
			"tpldir" => "applications/clients/patent_office/intellectual_property",
			"clid" => CL_INTELLECTUAL_PROPERTY
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
