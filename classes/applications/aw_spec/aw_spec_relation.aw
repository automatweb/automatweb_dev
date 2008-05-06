<?php
// aw_spec_relation.aw - AW Spetsifikatsiooni seos
/*

@classinfo syslog_type=ST_AW_SPEC_RELATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class aw_spec_relation extends class_base
{
	function aw_spec_relation()
	{
		$this->init(array(
			"tpldir" => "applications/aw_spec/aw_spec_relation",
			"clid" => CL_AW_SPEC_RELATION
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
