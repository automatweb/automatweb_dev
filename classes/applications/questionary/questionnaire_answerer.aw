<?php
// questionnaire_answerer.aw - Dünaamilise küsimustiku vastaja
/*

@classinfo syslog_type=ST_QUESTIONNAIRE_ANSWERER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class questionnaire_answerer extends class_base
{
	function questionnaire_answerer()
	{
		$this->init(array(
			"tpldir" => "applications/questionary/questionnaire_answerer",
			"clid" => CL_QUESTIONNAIRE_ANSWERER
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
