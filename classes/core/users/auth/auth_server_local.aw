<?php
// $Header: /home/cvs/automatweb_dev/classes/core/users/auth/auth_server_local.aw,v 1.7 2006/01/20 11:39:59 kristo Exp $
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

		$udata = NULL;
		$_uid = $credentials["uid"];
		$this->quote(&$_uid);
		$q = "SELECT * FROM users WHERE uid = '$_uid' AND blocked = 0";
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			if ($row["uid"] == $_uid)
			{
				$udata = $row;
			}
		}

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

		// check ip address
		if (is_oid($udata["oid"]) && $this->can("view", $udata["oid"]))
		{
			$u_o = obj($udata["oid"]);
			$conns = $u_o->connections_from(array("type" => "RELTYPE_ACCESS_FROM_IP"));
			if (count($conns))
			{
				$allow = false;
				$ipi = get_instance(CL_IPADDRESS);
				$cur_ip = inet::is_ip($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
				foreach($conns as $c)
				{
					$ipa = $c->to();
					if ($ipa->prop("range") != "" && $ipi->match_range($ipa->prop("range"), $cur_ip))
					{
						$allow = true;	
					}
					if ($ipi->match($ipa->prop("addr"), $cur_ip))
					{
						$allow = true;
					}
				}

				if (!$allow)
				{
					return array(false, sprintf(t("Sellelt aadressilt (%s) pole ligip&auml;&auml;s lubatud!"), $cur_ip));
				}
			}
		}

		if($success && users_user::require_password_change($udata["uid"]) && users_user::is_first_login($udata["uid"]) && !$credentials["pwdchange"])
		{ 
			Header("Location: ".$this->mk_my_orb("change_password_not_logged", array("uid" => $udata["uid"]), "users"));
			exit;		
		}
		
		return array($success, $msg);
	}
}
?>
