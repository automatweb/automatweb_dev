<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/remote_login.aw,v 2.17 2004/06/08 19:05:47 duke Exp $
// remote_login.aw - AW remote login

/*
	@default table=objects
	@default field=meta
	@default method=serialize
	@default group=general

	@property login_uid type=textbox
	@caption Kasutajanimi

	@property login_password type=password
	@caption Parool

	@property server type=textbox
	@caption Server

*/


class remote_login extends class_base
{
	function remote_login($args = array())
	{
		extract($args);
		$this->init(array(
			"tpldir" => "automatweb/remote_login",
			"clid" => CL_AW_LOGIN,
		));
	}

	function handshake($args = array())
	{
		extract($args);
		$socket = get_instance("socket");  
		if (substr($host,0,7) == "http://")
		{
			$host = substr($host,7);
		};
		$socket->open(array(
			"host" => $host,
			"port" => 80,
		));
	
		// what if remote host uses a different "ext"?
		$op = "HEAD /orb.".$this->cfg["ext"]."?class=remote_login&action=getcookie&fastcall=1 HTTP/1.0\r\n";
		$op .= "Host: $host\r\n\r\n";

		if (!$silent)
		{
			print "<pre>";
			print "Acquiring session  \nop = ".htmlentities($op)."\n";
			flush();
		}

		$socket->write($op);

		$ipd="";
		
		while($data = $socket->read())
		{
			$ipd .= $data;
		};

		if (preg_match("/automatweb=(\w+?);/",$ipd,$matches))
		{
			$cookie = $matches[1];
		};

		$this->cookie = $cookie;

		if (!$silent)
		{
			print "Got session, ID is $cookie\n";
			print "ipd = ".htmlentities($ipd)."</pre>";
			flush();
		}
		return $cookie;
	}

	function login($args = array())
	{
		extract($args);
		$cookie = $this->cookie;
		$socket = get_instance("socket");
		if (substr($host,0,7) == "http://")
		{
			$host = substr($host,7);
		};
		$socket->open(array(
			"host" => $host,
			"port" => 80,
		));

		
		$request = "uid=$uid&password=$password&class=users&action=login";

		$op = "GET /orb.".$this->cfg["ext"]."?$request HTTP/1.0\r\n";
		$op .= "Host: $host\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n";
		$op .= "Referer: http://$host/login.".$this->cfg["ext"]."\r\n\r\n";
		if (!$silent)
		{
			print "<pre>";
			echo "Logging in $host op = ",htmlentities($op),"\n";
		}

		$socket->write($op);
		$ipd = "";
		while($data = $socket->read())
		{
			$ipd .= $data;
		};
		$this->socket = $socket;

		if (!$silent)
		{
			print "Succeeded? Server returned ".htmlentities($ipd)."\n";
			print "</pre>";
			flush();
		}
	}

	function logout($args = array())
	{
		extract($args);
		$cookie = $this->cookie;
		$socket = get_instance("socket");
		$socket->open(array(
			"host" => $host,
			"port" => 80,
		));
		$op = "GET http://aw.struktuur.ee/index.".$this->cfg["ext"]."?class=users&action=logout HTTP/1.1\r\n";
		$op .= "Host: $host\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n\r\n";

		if (!$silent)
		{
			print "<pre>";
			print "Logging out:<br />";
		}

		$socket->write($op);
		
		while($data = $socket->read())
		{
			$ipd .= $data;
		};

		if (!$silent)
		{
			list($headers,$data) = explode("\r\n\r\n",$ipd);
			print "Succeeded? Server returned $data\n";
			print "</pre>";
			flush();
		}
	}

	function set_session_cookie($c)
	{
		$this->cookie = $c;
	}

	function login_from_obj($id)
	{
		$ob = new object($id);
		$this->handshake(array(
			"silent" => true,
			"host" => $ob->prop("server"),
		));

		$this->login(array(
			"host" => $ob->prop("server"),
			"uid" => $ob->prop("login_uid"),
			"password" => $ob->prop("login_password"),
			"silent" => true
		));

		return array($ob["meta"]["server"],$this->cookie);
	}

	function get_server($id)
	{
		$ob = new object($id);
		return $ob->prop("server");
	}

	/**  
		
		@attrib name=getcookie params=name nologin="1" is_public="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function getcookie($arr)
	{
		die("Relax, take a cookie.");
	}
};
?>
