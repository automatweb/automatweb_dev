<?php

/*

HANDLE_MESSAGE(MSG_USER_LOGIN, on_login)

*/

class site_list_updater extends aw_template
{
	function site_list_updater()
	{
		$this->init();
	}

	/**

		@attrib name=on_login

		@param uid required
	**/
	function on_login($arr)
	{
		if (!function_exists('mcrypt_module_open'))
		{
			return;
		}
		// check if there has been an update in the last hour
		// if so, do nothing
		if ($this->_get_last_update_time() > (time() - (3600*24)))
		{
			return;
		}
		// else
		// update this site's info in the site list
		// check if we have a session key for this site
		if (!is_array($key = $this->_get_session_key()))
		{
			// if not, request a session key from the site list server
			$key = $this->_init_session_key();
		}

		if ($key[0] == "")
		{
			// bad key, something is wroing
			return;
		}

		// the idea behind the session key is that the first time 
		// any communication between the site and the register happens
		// the session key is created in both databases
		// after that it is used to encrypt all communications, but it iself
		// is of course never passed between servers, so that if the attacker
		// misses the session key he can not make any modifications to the register
		// it still is vulnerable during the session key creation, but... 
		
		// get the new info about the site
		$data = $this->_collect();

		// encrypt it
		$data = $this->_encrypt(aw_serialize($data, SERIALIZE_XML), $key);

		// send it to the register
		$this->_do_update($data);
	}

	function _get_last_update_time()
	{
		return $this->get_cval("site_list_last_update".aw_ini_get("site_id"));
	}

	function _set_last_update_time()
	{
		$this->set_cval("site_list_last_update".aw_ini_get("site_id"), time());
	}

	function _get_session_key()
	{
		return aw_unserialize($this->get_cval("site_list_session_key".aw_ini_get("site_id")));
	}

	function _init_session_key()
	{
		$key = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "create_session_key",
			"method" => "xmlrpc",
			"server" => "http://register.automatweb.com",
			"params" => array(
				"site_id" => aw_ini_get("site_id")
			)
		));
		$ds = aw_serialize($key, SERIALIZE_XML);
		$this->quote(&$ds);
		$this->set_cval("site_list_session_key".aw_ini_get("site_id"), $ds);
		return $key;
	}

	function _collect()
	{
		return array(
			"id" => aw_ini_get("site_id"),
			"baseurl" => aw_ini_get("baseurl"),
			"site_basedir" => aw_ini_get("site_basedir"),
			"code" => aw_ini_get("basedir"),
			"uid" => aw_global_get("uid"),
		);
	}

	function _encrypt($data, $key)
	{
		$td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
		mcrypt_generic_init($td, $key[0], base64_decode($key[1]));
		$encrypted = mcrypt_generic($td, $data);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return $encrypted;
	}

	function _do_update($data)
	{
		$res = $this->do_orb_method_call(array(
			"class" => "site_list",
			"action" => "do_auto_update",
			"method" => "xmlrpc",
			"server" => "http://register.automatweb.com",
			"params" => array(
				"site_id" => aw_ini_get("site_id"),
				"data" => base64_encode($data)
			),
			"no_errors" => 1
		));
		if ($res)
		{
			$this->_set_last_update_time();
		}
	}
}
?>
