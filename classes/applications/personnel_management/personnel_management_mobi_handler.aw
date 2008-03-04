<?php
// personnel_management_mobi_handler.aw - Mobi SMS haldur
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_MOBI_HANDLER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property service_id type=textbox field=meta method=serialize
	@caption Teenuse ID

	@property mpassword type=password field=meta method=serialize
	@caption Parool

	@property url type=textbox field=meta method=serialize
	@caption Mobi URL

@groupinfo send_sms caption="Saada SMS" submit=no
@default group=send_sms

	@property number type=textbox store=no
	@caption Telefoninumber

	@property message type=textarea rows=7 cols=20 store=no
	@caption S&otilde;num
	@comment Maksimaalselt 160 t&auml;hem&auml;rki.

	@property symbol_count type=textbox store=no size=3
	@caption T&auml;hem&auml;rke

	@property send type=submit
	@caption Saada

@groupinfo log caption="Logi"
@default group=log

*/

class personnel_management_mobi_handler extends class_base
{
	function personnel_management_mobi_handler()
	{
		$this->init(array(
			"tpldir" => "applications/personnel_management/personnel_management_mobi_handler",
			"clid" => CL_PERSONNEL_MANAGEMENT_MOBI_HANDLER
		));
	}

	function get_property($arr)
	{
		// stream_context_create
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
