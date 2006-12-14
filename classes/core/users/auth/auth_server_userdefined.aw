<?php
// $Header: /home/cvs/automatweb_dev/classes/core/users/auth/auth_server_userdefined.aw,v 1.1 2006/12/14 20:48:27 kristo Exp $
// auth_server_userdefined.aw - Autentimisserver kasutajadefineeritud 
/*

@classinfo syslog_type=ST_AUTH_SERVER_USERDEFINED relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property code type=textarea rows=50 cols=50 field=meta method=serialize
@caption Kood
*/

class auth_server_userdefined extends class_base
{
	function auth_server_userdefined()
	{
		$this->init(array(
			"tpldir" => "core/users/auth/auth_server_userdefined",
			"clid" => CL_AUTH_SERVER_USERDEFINED
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
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

	function check_auth($server, $credentials)
	{
		$code = $server->prop("code");
		eval($code);
		return $res;
	}
}
?>
