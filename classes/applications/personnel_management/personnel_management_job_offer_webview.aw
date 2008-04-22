<?php
// personnel_management_job_offer_webview.aw - T&ouml;&ouml;pakkumised veebis
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_JOB_OFFER_WEBVIEW relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class personnel_management_job_offer_webview extends class_base
{
	function personnel_management_job_offer_webview()
	{
		$this->init(array(
			"tpldir" => "applications/personnel_management/personnel_management_job_offer_webview",
			"clid" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER_WEBVIEW
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
