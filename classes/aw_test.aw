<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/aw_test.aw,v 2.2 2002/11/07 10:52:16 kristo Exp $
// aw_test.aw - AW remote control
class aw_test extends aw_template 
{
	function aw_test($args = array())
	{
		extract($args);
		$this->init("automatweb/aw_test");
	}
	
	function change($args = array())
	{
		extract($args);
		if ($parent)
		{
			$act = "Lisa AW testobjekt";
		}
		else
		{
			$act = "Muuda AW testobjekti";	
			$obj = $this->get_obj_meta($id);
			$meta = $obj["meta"];
			$parent = $obj["parent"];
		};
		$this->read_template("change.tpl");
		$this->mk_path($parent,$act);
		$this->get_objects_by_class(array("class" => CL_AW_LOGIN));
		$logins = array();
		while($row = $this->db_next())
		{
			$logins[$row["oid"]] = $row["name"];
		};

		$this->vars(array(
			"name" => $obj["name"],
			"login" => $this->picker(-1,$logins),
			"reforb" => $this->mk_reforb("submit",array("id" => $id,"parent" => $parent)),
		));
		return $this->parse();
	}


	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_AW_TEST,
			));
		};

		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	// !This is the place where the user can choose a host, username, password and requests to test
	function config($args = array())
	{
		extract($args);
		$this->read_adm_template("config.tpl");
		$num = 15;
		$q = "";
		$awf = get_instance("file");
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
		$awf = get_instance("file");
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
		$socket = get_instance("socket");
		$socket->open(array(
			"host" => $host,
			"port" => 80,
		));
		
		$op = "HEAD / HTTP/1.0\r\n";
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
		$socket = get_instance("socket");
		$socket->open(array(
			"host" => $host,
			"port" => 80,
		));
		$ext = $this->cfg["ext"];
		
		$request = "uid=$uid&password=$password&class=users&action=login";

		$op = "POST http://$host/orb.".$ext." HTTP/1.0\r\n";
		$op .= "Host: $host\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n";
		$op .= "Keep-Alive: 5\r\n";
		$op .= "Referer: http://$host/login.".$ext."\r\n";
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

	function do_send_request($arr)
	{
		extract($arr);

		$cookie = $this->cookie;
		$socket = get_instance("socket");
		$socket->open(array(
			"host" => $host,
			"port" => 80,
		));
		$op = "GET $req HTTP/1.0\r\n";
		$op .= "Host: $host\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n\r\n";
		print "sending request $req <br>\n";
		$socket->write($op);
		$ipd = "";
		while($data = $socket->read())
		{
			$ipd .= $data;
		};
		$socket->close();

		return $ipd;
	}

	function send_request($args = array())
	{
		extract($args);

		$ipd = $this->do_send_request($args);

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
		$op = "GET /index.".$this->cfg["ext"]."?class=users&action=logout HTTP/1.0\r\n";
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
