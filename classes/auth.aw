<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/auth.aw,v 2.3 2002/12/20 11:39:43 kristo Exp $
// auth.aw - authentication functions
class auth extends aw_template 
{
	function auth($args = array())
	{
		$this->init("automatweb/auth");
	}

	////
	// !Generates the login form
	function show_login($args = array())
	{
		$this->read_adm_template("login.tpl");
		// remember the uri used before login so that we can 
		// redirect the user back there after (and if) he/she has finally
		// logged in
		global $request_uri_before_auth;
		$request_uri_before_auth = aw_global_get("REQUEST_URI");
		session_register("request_uri_before_auth");
		$this->vars(array(
			"reforb" => $this->mk_reforb("login",array()),
		));
		return $this->parse();
	}

	////
	// !Performs the actual login
	// uhuh. yeah. ok, and why texactly is this duplicated here? 
	// the users_user::login version seems to be much more secured to me - terryf
	function login($args = array())
	{
		global $uid;
		extract($args);
		$success = is_valid("uid",$uid);

		if ($success)
		{
			$q = "SELECT * FROM users WHERE uid = '$uid' AND blocked = 0";
			$this->db_query($q);
			$udata = $this->db_next();
			$success = is_array($udata);
		}

		if ($success)
		{
			$success = ($this->cfg["md5_passwords"]) ? 
				md5($password) == $udata["password"] : 
				$password == $udata["password"];
		}

		if ($success)
		{
			$this->_log(ST_USERS, SA_LOGIN,"$uid logis sisse");
			session_register("uid");
		}
		else
		{
			$this->_log(ST_USERS, SA_LOGIN_FAILED,"Tundmatu kasutaja või vale parool - $uid");
		};
		return $this->cfg["baseurl"] . aw_global_get("request_uri_before_auth");
	}
}
?>
