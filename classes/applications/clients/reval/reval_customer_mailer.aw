<?php
// reval_customer_mailer.aw - Revali klientide meilisaatjs
/*

@classinfo syslog_type=ST_REVAL_CUSTOMER_MAILER relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class reval_customer_mailer extends class_base
{
	function reval_customer_mailer()
	{
		$this->init(array(
			"tpldir" => "applications/clients/reval/reval_customer_mailer",
			"clid" => CL_REVAL_CUSTOMER_MAILER
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

	function _format_date($tm)
	{
		return date("Y-m-d", $tm)."T00:00:00";
	}

	private function do_call($action, $params, $ns = "Booking", $full_res = false)
	{
		if ($ns == "Booking")
		{
			$fn = "BookingService";
		}
		else
		if ($ns == "Customers")
		{
			$fn = "CustomerService";
		}
aw_global_set("soap_debug", 1);
		$return = $this->do_orb_method_call(array(
			"action" => $action,
			"class" => "http://revalhotels.com/ORS/webservices/",
			"params" => $params,
			"method" => "soap",
			"server" => "https://195.250.171.36/RevalORSService/RRCServices.asmx"
		));
		return $return;
	}

	/**
		@attrib name=daily_check nologin="1"
	**/
	function daily_check($arr)
	{
		die(dbg::dump($this->do_call("GetGuestsWithEmailByCODate", array(
			"CODate" => date("Y-m-d", time() - 24*3600)
		))));
	}
}

?>
