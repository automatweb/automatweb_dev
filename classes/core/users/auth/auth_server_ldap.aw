<?php
// $Header: /home/cvs/automatweb_dev/classes/core/users/auth/auth_server_ldap.aw,v 1.1 2004/10/18 15:37:34 kristo Exp $
// auth_server_ldap.aw - Autentimisserver LDAP 
/*

@classinfo syslog_type=ST_AUTH_SERVER_LDAP relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property server type=textbox
@caption LDAP server

@property ad_domain type=textbox
@caption Active Directory domeen

*/

class auth_server_ldap extends class_base
{
	function auth_server_ldap()
	{
		$this->init(array(
			"tpldir" => "core/users/auth/auth_server_ldap",
			"clid" => CL_AUTH_SERVER_LDAP
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

	function check_auth($server, $credentials)
	{
		if (!function_exists("ldap_connect"))
		{
			error::throw(array(
				"id" => "ERR_NO_LDAP",
				"msg" => "auth_server_ldap::check_auth(): The LDAP module for PHP is not installed, but the auth configuration specifies a LDAP server to authenticate against!",
				"fatal" => false,
				"show" => false
			));
			return array(false, "LDAP Moodul pole installeeritud!");
		}
		$res = ldap_connect($server->prop("server"));
		if (!$res)
		{
			return array(false, "Ei saanud &uuml;hendust LDAP serveriga ".$server->prop("server"));
		}
		ldap_set_option($res, LDAP_OPT_PROTOCOL_VERSION, 3);

		$uid = $credentials["uid"];
		if ($server->prop("ad_domain"))
		{
			$uid = $uid."@".$server->prop("ad_domain");
		}

		$bind = @ldap_bind($res, $uid, $credentials["password"]);
		if ($bind)
		{
			return array(true, "");
		}
		return array(false, "Sellist kasutajat pole v&otilde;i parool on vale!");
	}
}
?>
