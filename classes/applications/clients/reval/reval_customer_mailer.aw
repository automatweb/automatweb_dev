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
