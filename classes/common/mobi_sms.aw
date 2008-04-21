<?php
// mobi_sms.aw - Mobi SMS
/*

@classinfo syslog_type=ST_MOBI_SMS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property name type=textbox
	@caption Number

	@property comment type=textbox field=comment
	@caption S&otilde;num

@groupinfo sent caption="Saatmised"
@default group=sent

	@property sent_tbl type=table store=no no_caption=1

*/

class mobi_sms extends class_base
{
	function mobi_sms()
	{
		$this->init(array(
			"tpldir" => "common/mobi_sms",
			"clid" => CL_MOBI_SMS
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

	function _get_sent_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "mobi_answer",
			"caption" => t("Mobi vastus"),
			"align" => "center",
		));
		foreach($arr["obj_inst"]->meta("log") as $d)
		{
			$t->define_data(array(
				"time" => date("Y-m-d H:i:s", $d["t"]),
				"mobi_answer" => $d["m"],
			));
		}
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
