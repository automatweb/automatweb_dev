<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/remote.aw,v 2.2 2002/02/05 04:58:27 duke Exp $
// remote.aw - AW remote control
global $orb_defs;
$orb_defs["remote"] = "xml";
classload("socket");

class remote extends aw_template {
	function remote($args = array())
	{
		extract($args);
		$this->db_init();
		$this->tpl_init("automatweb/remote");
	}

	////
	// !This is the place where the user can choose a host, username, password and requests to test
	function config($args = array())
	{
		extract($args);
		$this->read_adm_template("config.tpl");
		$num = 15;
		$q = "";
		classload("file");
		$awf = new file();
		$dat = $awf->get_special_file(array(
			"name" => "testsuite.ser",
		));
		$data = aw_unserialize($dat);
		for ($i = 0; $i < $num; $i++)
		{
			$this->vars(array(
				"id" => $id,
				"query" => $data["qs"][$i],
			));
			$q .= $this->parse("QUERY");
		};

		$this->vars(array(
			"QUERY" => $q,
			"server" => ($data["server"]) ? $data["server"] : "aw.struktuur.ee",
			"reforb" => $this->mk_reforb("submit_config",array()),
		));
		return $this->parse();
	}

	////
	// !Submits the config
	function submit_config($args = array())
	{
		extract($args);
		$block = array("server" => $server,"qs" => $query);
		$contents = aw_serialize($block,SERIALIZE_PHP);
		classload("file");
		$awf = new file();
		$awf->put_special_file(array(
			"name" => "testsuite.ser",
			"content" => $contents,
		));

		// that's the name of the query button and if it's set then it means we have to do the test run
		if ($do_query)
		{
			print "starting test run, please be patient, it _will_ take some time<br>";
			$this->handshake(array(
				"host" => $server,
			));

			$this->login(array(
				"host" => $server,
				"uid" => $uid,
				"password" => $password,
			));

			print "<pre>";
			print "Sending requests now\n";

			if (is_array($query))
			{
				foreach($query as $key => $val)
				{
					if (strlen($val) > 0)
					{
						print "Sending $val";
						flush();
						$this->send_request(array(
							"host" => $server,
							"req" => $val,
						));
						print "-------------------------------------------------------\n";
						flush();
					};
				};
			};

			print "All requests sent\n";
			print "</pre>";
			
			$this->logout(array(
				"host" => $server,
			));
			exit;
		}
		return $this->mk_my_orb("config",array());
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

	function send_request($args = array())
	{
		extract($args);
		$cookie = $this->cookie;
		$socket = new socket(array(
			"host" => $host,
			"port" => 80,
		));
		$op = "GET $req HTTP/1.1\r\n";
		$op .= "Host: $host\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n\r\n";
		//print "sending request $req\n";
		$socket->write($op);
		$ipd = "";
		//print "sending $op\n";
		while($data = $socket->read())
		{
			$ipd .= $data;
		};

		$fail = false;

		if (preg_match("/AW_ERROR: (.*)\n/",$ipd,$matches))
		{
			print " - <b><font color=red>FAIL</font></b>\n";
			print trim($matches[1]);
			print "\n";
			$fail = true;
			if (preg_match("/X-AW-Error: (\d)/",$ipd,$matches))
			{
				print "Additonaly, error code $matches[1] was detected\n";
			};
		};

		if (not($fail))
		{
			print " - <b><font color=green>SUCCESS</font></b>\n";
		};
			
		//preg_match("/HTTP\/1.1 (\d+?) (w+?)/",$ipd,$matches);
		//print $matches[1] . $matches[2];
		//print "\n";
		//print "server returned: $ipd\n";
		flush();
		$socket->close();
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
