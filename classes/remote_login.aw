<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/remote_login.aw,v 2.1 2002/02/15 18:02:26 duke Exp $
// remote_login.aw - AW remote login
global $orb_defs;
$orb_defs["remote_login"] = "xml";
classload("socket");

class remote extends aw_template {
	function remote($args = array())
	{
		extract($args);
		$this->db_init();
		$this->tpl_init("automatweb/remote_login");
	}

	function handshake($args = array())
	{
		extract($args);
		$socket = new socket(array(
			"host" => $host,
			"port" => 80,
		));
		
		$op = "HEAD / HTTP/1.1\r\n";
		$op .= "Host: $host\r\n\r\n";

		print "<pre>";
		print "Acquiring session\n";
		flush();

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

		print "Got session, ID is $cookie\n";
		print "</pre>";
		flush();
	}

	function login($args = array())
	{
		extract($args);
		$cookie = $this->cookie;
		$socket = new socket(array(
			"host" => $host,
			"port" => 80,
		));

		
		$request = "uid=$uid&password=$password&Submit=Login&action=login";

		$op = "POST http://$host/refcheck.aw HTTP/1.1\r\n";
		$op .= "Host: $host\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n";
		$op .= "Keep-Alive: 5\r\n";
		$op .= "Referer: http://$host/login.aw\r\n";
		$op .= "Content-type: application/x-www-form-urlencoded\r\n";
		$op .= "Content-Length: " . strlen($request) . "\r\n\r\n";
		print "<pre>";
		print "Logging in\n";
		$socket->write($op);
		$socket->write($request);
	
		$ipd = "";
		while($data = $socket->read())
		{
			$ipd .= $data;
		};
		$this->socket = $socket;
		list($headers,$data) = explode("\r\n\r\n",$ipd);
		print "Succeeded? Server returned $data\n";
		print "</pre>";
		flush();
	}

	function logout($args = array())
	{
		extract($args);
		$cookie = $this->cookie;
		$socket = new socket(array(
			"host" => $host,
			"port" => 80,
		));
		$op = "GET http://aw.struktuur.ee/index.aw?action=logout HTTP/1.1\r\n";
		$op .= "Host: $host\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n\r\n";

		print "<pre>";
		print "Logging out:<bR>";
		$socket->write($op);
		
		while($data = $socket->read())
		{
			$ipd .= $data;
		};

		list($headers,$data) = explode("\r\n\r\n",$ipd);
		print "Succeeded? Server returned $data\n";
		print "</pre>";
		flush();
	}

};
?>
