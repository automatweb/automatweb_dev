<?php
// $Header: /home/cvs/automatweb_dev/classes/core/users/auth/auth_server_local.aw,v 1.1 2004/10/18 15:37:34 kristo Exp $
// auth_server_local.aw - Autentimsserver Kohalik 
/*

@classinfo syslog_type=ST_AUTH_SERVER_LOCAL relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

*/

class auth_server_local extends class_base
{
	function auth_server_local()
	{
		$this->init(array(
			"tpldir" => "core/users/auth/auth_server_local",
			"clid" => CL_AUTH_SERVER_LOCAL
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
		// by default eeldame, et kasutaja on jobu ja ei saa
		// sisse logida
		$success = false;

		$q = "SELECT * FROM users WHERE uid = '$credentials[uid]' AND blocked = 0";
		$this->db_query($q);
		$udata = $this->db_next();

		if (is_array($udata))
		{
			if (aw_ini_get("auth.md5_passwords"))
			{
				if (md5($credentials["password"]) == $udata["password"])
				{
					$success = true;
				};
			}
			else
			if ($credentials["password"] == $udata["password"])
			{
				$success = true;
			}
			else
			{
				$msg = sprintf(E_USR_WRONG_PASS,$credentials["uid"],"");
			};
		}
		else
		{
			$msg = "Sellist kasutajat pole $credentials[uid]";
		};

		return array($success, $msg);
	}
}
?>
