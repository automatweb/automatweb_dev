<?php
// questionnaire_answer.aw - Dünaamilise küsimustiku vastus
/*

@classinfo syslog_type=ST_QUESTIONNAIRE_ANSWER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property name type=textbox field=name
	@caption Vastus

	@property jrk type=textbox size=4 field=jrk
	@caption J&auml;rjekord

	@property correct type=checkbox field=meta method=serialize
	@caption &Otilde;ige vastus

	@property comm type=textbox field=comment
	@caption Kommentaar

*/

class questionnaire_answer extends class_base
{
	function questionnaire_answer()
	{
		$this->init(array(
			"tpldir" => "applications/questionary/questionnaire_answer",
			"clid" => CL_QUESTIONNAIRE_ANSWER
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
