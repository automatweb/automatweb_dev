<?php
// $Header: /home/cvs/automatweb_dev/classes/core/users/auth/auth_server_ldap.aw,v 1.8 2004/11/25 07:32:38 kristo Exp $
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

@property ad_base_dn type=textbox
@caption Active Directory baas-DN kasutajate otsimiseks

@property ad_uid type=textbox
@caption AD kasutaja (gruppide lugemiseks)

@property ad_pwd type=password
@caption AD parool

@property ad_grp type=select
@caption AD Grupp, kus kasutajad peavad olema

@property ad_grp_txt type=textbox
@caption AD Grupp, kus kasutajad peavad olema (tekst)



*/

class auth_server_ldap extends class_base
{
	function auth_server_ldap()
	{
		$this->init(array(
			"clid" => CL_AUTH_SERVER_LDAP
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "ad_grp":
				if ($arr["obj_inst"]->prop("ad_grp_txt") != "")
				{
					return PROP_IGNORE;
				}
				$grps = $this->_get_ad_grps($arr["obj_inst"]);
				if ($grps === PROP_ERROR)
				{
					$prop["error"] = $this->last_error;
					return PROP_ERROR;
				}
				if (count($grps) == 0)
				{
					return PROP_IGNORE;
				}

				$prop["options"] = $grps;
				break;

			case "ad_pwd":
			case "ad_uid":
				if ($arr["obj_inst"]->prop("ad_grp_txt") != "")
				{
					return PROP_IGNORE;
				}
				break;
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
		if (!extension_loaded("ldap"))
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
		//ldap_set_option($res, LDAP_OPT_PROTOCOL_VERSION, 3);

		$uid = $credentials["uid"];
		if ($server->prop("ad_domain"))
		{
			$uid = $uid."@".$server->prop("ad_domain");
		}

		$break = false;
		$bind = @ldap_bind($res, $uid, $credentials["password"]);
		if ($bind)
		{
			$grp = $server->prop("ad_grp_text");
			if ($server->prop("ad_grp") != "")
			{
				$grp = $server->prop("ad_grp");
			}

			if ($grp == "" || ($grp != "" && $this->_is_member_of($res, $server, $grp, $credentials)))
			{
				return array(true, "");
			}
			else
			{
				$break = true;
			}
		}
		return array(false, "Sellist kasutajat pole v&otilde;i parool on vale!", $break);
	}

	function _get_ad_grps($o)
	{
		if (!($o->prop("ad_domain") && $o->prop("ad_uid") && $o->prop("ad_pwd")))
		{
			return array();
		}
		$res = ldap_connect($o->prop("server"));
		if (!$res)
		{
			$this->last_error = "Ei saanud serveriga &uuml;hendust!";
			return PROP_ERROR;
		}
		ldap_set_option($res, LDAP_OPT_PROTOCOL_VERSION, 3);

		$uid = $o->prop("ad_uid");
		$uid = $uid."@".$o->prop("ad_domain");

		if (!@ldap_bind($res, $uid, $o->prop("ad_pwd")))
		{
			return array();
		}

		$dna = array("CN=Users");
		foreach(explode(".", $o->prop("ad_domain")) as $part)
		{
			$dna[] = "dc=".$part;
		}

		$dn = join(", ",$dna);
		$sr=ldap_search($res, $dn, "cn=*",array("memberof")); 
		$info = ldap_get_entries($res, $sr);

		$ret = array("" => "");
		for ($i=0; $i<$info["count"]; $i++) 
		{
			for ($a = 0; $a < $info[$i]["memberof"]["count"]; $a++)
			{
				list($grpn) = explode(",", $info[$i]["memberof"][$a]);
				list(, $grpn) = explode("=", $grpn);
				$ret[$grpn] = $grpn;
			}
		}

		return $ret;
	}

	function _is_member_of($res, $o, $grp, $cred)
	{
		$dna = array("OU=".$grp);
		foreach(explode(".", $o->prop("ad_domain")) as $part)
		{
			$dna[] = "dc=".$part;
		}

		$dn = join(", ",$dna);
		$sr=ldap_search($res, $dn, "samaccountname=".$cred["uid"], array("samaccountname")); 
		$info = ldap_get_entries($res, $sr);

		$ret = false;
		for ($i=0; $i<$info["count"]; $i++) 
		{
			for($a = 0; $a < $info[$i]["samaccountname"]["count"]; $a++)
			{
				if (strtolower($info[$i]["samaccountname"][$a]) == strtolower($cred["uid"]))
				{
					$ret = true;
				}
			}
		}

		return $ret;
	}
}
?>
