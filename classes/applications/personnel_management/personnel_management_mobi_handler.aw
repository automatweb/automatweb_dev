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

	@property send type=submit action=send_sms
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
		$this->send_sms(array(
			"id" => $arr["obj_inst"]->id(),
			"number" => "37255547369",
			"message" => "Tere",
		));
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}
		
	/** Sends SMS via Mobi.
		@attrib name=send_sms api=1 params=name

		@param id required type=oid
			The OID of the Mobi handler object, that describes the service ID, password and Mobi URL.

		@param number required type=int
			The phone number to send the SMS to. Must contain aera code, only digits. (Example: 3725123456)

		@param message required type=string
			The content of the SMS. No more than 160 symbols.

		@example

		@comment

		@returns The feedback from Mobi.

		@errors Return error if number contains other symbols beside digits. Returns error if message contains more than 160 symbols.
	**/
	function send_sms($arr)
	{
		if(!preg_match("/\d*/", $arr["number"]))
		{
			error::raise(array(
				"id" => "ERR_PARAM",
				"msg" => t("personnel_management_mobi_handler::send_sms(number => ".$arr['number']."): number must only contain digits!")
			));
		}

		if(strlen($arr["message"]) > 160)
		{
			error::raise(array(
				"id" => "ERR_PARAM",
				"msg" => t("personnel_management_mobi_handler::send_sms(message => ".$arr['message']."): message must be no more than 160 symbols!")
			));
		}
		$o = obj($arr["id"]);
		$service_id = $o->prop("service_id");
		$password = $o->prop("mpassword");
		$url = $o->prop("url");

		$params = array(
			"password" => "e1DsA8fJr4w",
			"phone" => "37255547369",
			"text" => "Text. Yeah!",
			"requestid" => 1,
		);
		arr(http_build_query($params));
		$args = array(
			"http" => array(
				"method" => "POST",
				"header" => "Content-type: application/x-www-form-urlencoded",
				"content" => http_build_query($params),
			)
		);
		$context = stream_context_create($args);

		arr(file_get_contents($url, false, $context));
		exit;
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
