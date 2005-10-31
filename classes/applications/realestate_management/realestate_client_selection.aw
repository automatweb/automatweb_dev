<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/realestate_management/realestate_client_selection.aw,v 1.1 2005/10/31 17:13:35 voldemar Exp $
// realestate_client_selection.aw - Klientide valim
/*

@classinfo syslog_type=ST_REALESTATE_CLIENT_SELECTION relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize
@default group=general

*/

class realestate_client_selection extends class_base
{
	function realestate_client_selection()
	{
		$this->init(array(
			"tpldir" => "applications/realestate_management/realestate_client_selection",
			"clid" => CL_REALESTATE_CLIENT_SELECTION
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
}
?>
