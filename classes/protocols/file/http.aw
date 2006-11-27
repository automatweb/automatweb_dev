<?php

class http 
{
	function http()
	{
		aw_config_init_class(&$this);
	}
	
	/**
	@attrib api=1 params=pos
	@param url required type=string
		url, with all the data needed for HTTP get request
	@return array
		data returned after request
	@example
		$http = get_instance("protocols/file/http");
		$this->content = $http->get($this->url);
		$this->headers = $http->get_headers();
	@comment
		gets requested data from url
	**/
	function get($url, $sess = null)
	{
		enter_function("http::get");
		$data = parse_url($url);

		$host = !empty($data["host"]) ? $data["host"] : aw_ini_get("baseurl");
		$host = str_replace("http://", "", $host);
		$host = str_replace("https://", "", $host);

		$port = (!empty($data["port"]) ? $data["port"] : 80);

		$y_url = $data["path"].($data["query"] != "" ? "?".$data["query"] : "").($data["fragment"] != "" ? "#".$data["fragment"] : "");
		if ($y_url == "")
		{
			$y_url = "/";
		}

		$req  = "GET $y_url HTTP/1.0\r\n";
		$req .= "Host: ".$host.($port != 80 ? ":".$port : "")."\r\n";
		$req .= "User-agent: AW-http-fetch\r\n";
		if ($sess !== null)
		{
			$req .= "Cookie: automatweb=".$sess."\r\n";
		}
		$req .= "\r\n\r\n";
		classload("protocols/socket");
		$socket = new socket(array(
			"host" => $host,
			"port" => $port,
		));
		$socket->write($req);
		$ipd = "";
		while($data = $socket->read(10000))
		{
			//echo "data = $data <br>";
			$ipd .= $data;
		};
		list($headers,$data) = explode("\r\n\r\n",$ipd,2);
		$this->last_request_headers = $headers;
		//echo htmlentities($headers)."<br>".htmlentities($data);

		exit_function("http::get");
		return $data;
	}

	/**
	@attrib api=1
	@return $this->last_request_headers
	@example ${get}
	@comment
		returns last requested headers
	**/
	function get_headers()
	{
		return $this->last_request_headers;
	}
	
	/**
	@attrib api=1
	@return string/content type
	@comment
		returns content type, if not in headers, returns "text/html"
	**/
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
	
	/**
	@attrib api=1 params=name
	@param host required type=string
		host URL
	@param sessid optional type=string
		sets cookie value to sessid
	@param silent optional type=bool
		object id
	@example
		$awt = get_instance("protocols/file/http");
		$awt->handshake(array(
			"host" => $url,
			"sessid" => $evnt["sessid"]
		));
		if ($evnt["uid"] && $evnt["password"])
			$awt->login(array(
				"host" => $url, 
				"uid" => $evnt["uid"], 
				"password" => $evnt["password"]
			));
		else	$awt->login_hash(array(
				"host" => $url, 
				"uid" => $evnt["uid"], 
				"hash" => $evnt["auth_hash"],
			));
		$req = $awt->do_send_request(array(
			"host" => $url,
			"req" => substr($ev_url,strlen("http://")+strlen($url)))
		);
		$awt->logout(array("host" => $url));
	
	@return cookie, if sessid is not set
	**/
	function handshake($args = array())
	{
		extract($args);
		if (!isset($args["silent"]))
		{
			$silent = true;
		}
		$socket = get_instance("protocols/socket");  
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
	
	/**
	@attrib api=1 params=pos
	@param id required type=oid
		object id
	@return array(prop("server") , $this->cookie)
	@comment 
		object's props(server, login_uid, login_password, server) must be set
	**/
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
	
	/**
	@attrib api=1 params=name
	@param host required type=string
		Server URL
	@param uid required type=uid
		User id
	@param password required type=string
		password
	@param silent optional type=bool
		if not set, prints out the request
	@example ${handshake}
	@comment 
		Sends login request
	**/
	function login($args = array())
	{
		extract($args);
		$cookie = $this->cookie;
		$socket = get_instance("protocols/socket");
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
	
	/**
	@attrib api=1 params=name
	@param host required type=string
		Server URL
	@param uid required type=uid
		User id
	@param hash required type=oid
		hash value
	@example ${handshake}
	@comment 
		Sends login request
	**/
	function login_hash($args = array())
	{
		extract($args);
		$cookie = $this->cookie;
		$socket = get_instance("protocols/socket");
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

	/**
	@attrib api=1 params=name
	@param host required type=string
		Server URL
	@param req required type=string
		Request url
	@return string
		data returned after request
	@example ${handshake}
	@comment 
		Sends HTTP GET request
	**/
	function do_send_request($arr)
	{
		extract($arr);

		$cookie = $this->cookie;
		$socket = get_instance("protocols/socket");
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
	
	/**
	@attrib api=1 params=name
	@param host optional type=string
		Server URL
	@param req required type=string
		Request url
	@example ${handshake}
	@comment 
		Sends HTTP GET request
	**/
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
	
	/**
	@attrib api=1 params=name
	@param host required type=string
		Server URL
	@return array(headers,data)
	@example ${handshake}
	@comment 
		Log's out
	**/
	function logout($args = array())
	{
		extract($args);
		$cookie = $this->cookie;
		$socket = get_instance("protocols/socket");
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
	
	/**
	@attrib api=1 params=pos
	@param c required type=string
		cookie
	@comment 
		Sets session cookie
	**/
	function set_session_cookie($c)
	{
		$this->cookie = $c;
	}

	/**
	@attrib api=1 params=pos
	@param server required type=string
		Server URL
	@param handler required type=string
		POST handler
	@param params requires type=array
		Request parameters Array("parameter"=>"value")
	@param port optional type=int default=80
		Connection port
	@example 
		$http = get_instance("protocols/file/http");
		return $http->post_request(
			"https://unet.eyp.ee/cgi-bin/unet3.sh/un3min.r",
			"https://unet.eyp.ee/cgi-bin/unet3.sh/un3min",
			array(
				"VK_SERVICE"	=> "1002",
				"VK_VERSION"	=> "008",
				"VK_SND_ID"	=> $sender_id,
			),
			$port = 80,
		);
	@return string
	@comment 
		Initiates a socket connection to the server and generates HTTP POST request
	**/
	function post_request($server, $handler, $params, $port = 80, $sessid = NULL)
	{
		$fp = fsockopen($server,$port,&$errno, &$errstr, 5);
		$op = "POST $handler HTTP/1.0\r\n";
		$op .= "User-Agent: AutomatWeb\r\n";
		$op .= "Host: $server\r\n";
		if ($sessid)
		{
			$op .= "Cookie: automatweb=$sessid\r\n";
		}
		$op .= "Content-Type: application/x-www-form-urlencoded\r\n";

		foreach($params as $key => $val)
		{
			$request .= urlencode($key) . "=" . urlencode($val) . "&";
		} 

		$op .= "Content-Length: " . strlen($request) . "\r\n\r\n";
		$op .= $request;
		fputs($fp, $op, strlen($op));

		while($dat = fread($fp, 100000))
		{
			$str .= $dat;
		}
		fclose($fp);
		return $str;
	}
}
?>
