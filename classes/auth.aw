<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/auth.aw,v 2.7 2004/08/23 10:51:29 kristo Exp $
// auth.aw - authentication functions
class auth extends aw_template 
{
	function auth($args = array())
	{
		$this->init("automatweb/auth");
	}

	/** Generates the login form 
		
		@attrib name=show_login params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
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
			"reforb" => $this->mk_reforb("login",array(),'users'),
		));
		return $this->parse();
	}
}
?>
