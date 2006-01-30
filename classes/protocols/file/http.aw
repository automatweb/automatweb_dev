<?php

class http 
{
	function http()
	{
		aw_config_init_class(&$this);
	}

	function get($url)
	{
		enter_function("http::get");
		$data = parse_url($url);

		$host = !empty($data["host"]) ? $data["host"] : aw_ini_get("baseurl");

		$port = (!empty($data["port"]) ? $data["port"] : 80);

		$y_url = $data["path"].($data["query"] != "" ? "?".$data["query"] : "").($data["fragment"] != "" ? "#".$data["fragment"] : "");
		if ($y_url == "")
		{
			$y_url = "/";
		}

		$req  = "GET $y_url HTTP/1.0\r\n";
		$req .= "Host: ".$host.($port != 80 ? ":".$port : "")."\r\n";
		$req .= "User-agent: AW-http-fetch\r\n";
		$req .= "\r\n";
		classload("socket");
		$socket = new socket(array(
			"host" => $host,
			"port" => $port,
		));
		//echo "req = ".dbg::dump($req)." <br>";
		$socket->write($req);
		$ipd = "";
		while($data = $socket->read(10000000))
		{
			$ipd .= $data;
		};
		list($headers,$data) = explode("\r\n\r\n",$ipd,2);
		$this->last_request_headers = $headers;
		//echo htmlentities($headers)."<br>".htmlentities($data);

		exit_function("http::get");
		return $data;
	}

	function get_headers()
	{
		return $this->last_request_headers;
	}

	function get_type()
	{
		$headers = explode("\n", $this->last_request_headers);

		$ct = "text/html";
		foreach($headers as $hd)
		{
			if (preg_match("/Content\-Type\: (.*)/", $hd, $mt))
			{
				$ct = $mt[1];
			}
		}

		return $ct;
	}

	function handshake($args = array())
	{
		extract($args);
		if (!isset($args["silent"]))
		{
			$silent = true;
		}
		$socket = get_instance("socket");  
		if (substr($host,0,7) == "http://")
		{
			$host = substr($host,7);
		};
		$socket->open(array(
			"host" => $host,
			"port" => 80,
		));
	
		if ($args["sessid"] != "")
		{
			$this->cookie = $args["sessid"];
			return;
		}

		// what if remote host uses a different "ext"?
		$op = "HEAD /orb.".$this->cfg["ext"]."?class=remote_login&action=getcookie HTTP/1.0\r\n";
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

		return array($ob->prop("server"),$this->cookie);
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

	function login_hash($args = array())
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
		
		$request = "uid=$uid&hash=$hash&class=users&action=login";

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
		$op .= "Cache-control: no-cache\r\n";
		$op .= "Pragma: no-cache\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n\r\n";
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

		if ($host == "")
		{
			if (preg_match("/http:\/\/(.*)(\/*)(.*)/",$req, $mt))
			{
				$args["host"] = $mt[1];
				$args["req"] = $mt[3];
				extract($args);
			}
		}

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
		$op = "GET /index.".aw_ini_get("ext")."?class=users&action=logout HTTP/1.0\r\n";
		$op .= "Host: $host\r\n";
		$op .= "Cache-control: no-cache\r\n";
		$op .= "Pragma: no-cache\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n\r\n";

		$socket->write($op);
		
		while($data = $socket->read())
		{
			$ipd .= $data;
		};

		list($headers,$data) = explode("\r\n\r\n",$ipd);
		return array($headers, $data);
	}

	function set_session_cookie($c)
	{
		$this->cookie = $c;
	}

	function post_request($server, $handler, $params, $port = 80)
	{
		$fp = fsockopen($server,$port,&$errno, &$errstr, 5);
		$op = "POST $handler HTTP/1.0\r\n";
		$op .= "User-Agent: AutomatWeb\r\n";
		$op .= "Host: $server\r\n";
		$op .= "Content-Type: application/x-www-form-urlencoded\r\n";

		foreach($params as $key => $val)
		{
			$request .= urlencode($key) . "=" . urlencode($val) . "&";
		} 

		$op .= "Content-Length: " . strlen($request) . "\r\n\r\n";
		$op .= $request;

		fputs($fp, $op, strlen($op));

		$str = fread($fp, 10000);
		fclose($fp);
		return $str;
	}
}
?>
