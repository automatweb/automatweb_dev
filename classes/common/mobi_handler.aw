<?php
// mobi_handler.aw - Mobi SMS haldur
/*

@classinfo syslog_type=ST_MOBI_HANDLER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

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

	@property symbol_count type=textbox store=no size=3 value=0
	@caption T&auml;hem&auml;rke

	@property send type=submit action=presend_sms
	@caption Saada

@groupinfo log caption="Logi"
@default group=log

	@property log_tbl type=table store=no no_caption=1

*/

class mobi_handler extends class_base
{
	function mobi_handler()
	{
		$this->init(array(
			"tpldir" => "common/mobi_handler",
			"clid" => CL_MOBI_HANDLER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "message":
				$prop["onkeyup"] = "aw_get_el('symbol_count').value = this.value.length";
				break;
		}

		return $retval;
	}

	function _get_log_tbl($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "number",
			"caption" => t("Number"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "message",
			"caption" => t("S&otilde;num"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "mobi_ans",
			"caption" => t("Mobi vastus"),
			"align" => "center",
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"align" => "center",
			"sortable" => 1,
		));
		$ol = new object_list(array(
			"class_id" => CL_MOBI_SMS,
			"parent" => $arr["obj_inst"]->id(),
			"status" => array(),
			"lang_id" => array(),
		));
		foreach($ol->arr() as $o)
		{
			foreach($o->meta("log") as $log)
			{
				$t->define_data(array(
					"number" => $o->name,
					"message" => $o->comment,
					"mobi_ans" => $log["m"],
					"time" => date("Y-d-m H:i:s", $log["t"]),
					"timestamp" => $log["t"],
				));
			}
		}
		$t->set_default_sortby("timestamp");
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
	
	/**
	@attrib name=presend_sms
	**/
	function presend_sms($arr)
	{
		$nrs = explode(",", $arr["number"]);
		$msg = $arr["message"];
		foreach($nrs as $nr)
		{
			$nr = preg_replace("/[^0-9]/", "", $nr);
			$i = 0;
			$sms = $this->send_sms(array(
				"id" => $arr["id"],
				"number" => $nr,
				"message" => $msg,
				"sms" => &$i,
			));
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "log"), CL_MOBI_HANDLER);
		//return $arr["post_ru"];
	}
		
	/** Sends SMS via Mobi.
		@attrib name=send_sms api=1 params=name

		@param id required type=oid
			The OID of the Mobi handler object, that describes the service ID, password and Mobi URL.

		@param number required type=string
			The phone number to send the SMS to. Must contain aera code, only digits. (Example: 3725123456)

		@param message required type=string
			The content of the SMS. No more than 160 symbols.

		@param sms optional type=*oid
			Used to get the OID of the SMS object.

		@example
			$send = get_instance(CL_MOBI_HANDLER)->send_sms(array(
				"id" => 2312,
				"number" => "3725123456",
				"message" => "The quick brown fox jumps over the lazy dog!",
				"sms" => &$sms,
			));

		@comment

		@returns True on success, false on failure.

		@errors Returns error if number contains other symbols beside digits. Returns error if message contains more than 160 symbols.
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
		$request_id = $o->meta("request_id");
		// This has to be unique every time. So we'll increase it no matter what.
		$o->set_meta("request_id", $request_id + 1);
		$o->save();

		$params = array(
			"serviceid" => $service_id,
			"password" => $password,
			"phone" => $arr["number"],
			"text" => $arr["message"],
			"requestid" => $request_id,
		);
		$args = array(
			"http" => array(
				"method" => "POST",
				"header" => "Content-type: application/x-www-form-urlencoded",
				"content" => http_build_query($params),
			)
		);
		$context = stream_context_create($args);
		$mobi_answer = file_get_contents($url, false, $context);

		$log = array();
		$log[] = array("t" => time(), "m" => $mobi_answer);

		$sms = obj();
		$sms->parent = $o->id();
		$sms->class_id = CL_MOBI_SMS;
		$sms->name = $arr["number"];
		$sms->comment = $arr["message"];
		$sms->set_meta("log", $log);
		$sms->save();

		if(isset($arr["sms"]))
		{
			$arr["sms"] = $sms->id();
		}

		return substr($mobi_answer, 0, 2) == "OK";
	}
}

?>
