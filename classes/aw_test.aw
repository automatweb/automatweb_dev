<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/aw_test.aw,v 2.8 2004/02/27 13:04:05 kristo Exp $
// aw_test.aw - AW remote control

/*

@classinfo syslog_type=ST_AW_TEST relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default field=meta
@default method=serialize
@default group=general

@property login type=relpicker reltype=RELTYPE_LOGIN 
@caption AW Login

@property activate type=text store=no editonly=1


@property urls type=callback callback=callback_get_urls store=no
@caption P&auml;ringud

@reltype LOGIN value=1 clid=CL_AW_LOGIN
@caption aw login

*/

class aw_test extends class_base
{
	function aw_test($args = array())
	{
		extract($args);
		$this->init(array(
			"tpldir" => "automatweb/aw_test",
			"clid" => CL_AW_TEST
		));
		$this->num = 15;
	}

	function callback_get_urls($arr)
	{
		$acts = array();
		$values = $arr["obj_inst"]->meta("urls");
		for($i = 0; $i < $this->num; $i++)
		{
			$rt = 'url_'.$i;

			$acts[$rt] = array(
				'name' => $rt,
				'caption' => "Url $i",
				'type' => 'textbox',
				'value' => $values[$i],
				'group' => $arr["prop"]["group"],
			);
		}

		return $acts;
	}	

	function get_property($arr)
	{
		$prop =& $arr["prop"];
		switch ($prop["name"])
		{
			case "activate":
				if (!$arr["obj_inst"]->prop("login"))
				{
					return PROP_IGNORE;
				}
				$prop['value'] = html::href(array(
					"url" => $this->mk_my_orb("activate", array("id" => $arr["obj_inst"]->id())),
					"caption" => "K&auml;ivita"
				));
				break;
		}
		
		return PROP_OK;
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];

		switch ($prop["name"])
		{
			case "urls":
				$urls = array();
				for($i = 0; $i < $this->num; $i++)
				{
					$urls[$i] = $arr["request"]["url_".$i];
				}
				$arr["obj_inst"]->set_meta("urls", $urls);
				break;
		}

		return PROP_OK;
	}

	/** runs the test
	
		@attrib name=activate

		@param id required type=int acl=view

	**/
	function activate($args = array())
	{
		extract($args);

		$o = obj($id);
		
		$rl = get_instance("remote_login");
		list($server, $this->cookie) = $rl->login_from_obj($o->prop("login"));

		print "<pre>";
		print "Sending requests now\n";

		$query = $o->meta("urls");
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


	function handshake($args = array())
	{
		extract($args);
		$socket = get_instance("socket");
		$socket->open(array(
			"host" => $host,
			"port" => 80,
		));
		
		$op = "HEAD / HTTP/1.0\r\n";
		$op .= "Host: $host\r\n";
		$op .= "Cache-control: no-cache\r\n";
		$op .= "Pragma: no-cache\r\n";
		$op .= "\r\n";

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

		$op = "POST /reforb.".$ext." HTTP/1.0\r\n";
		$op .= "Host: $host\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n";
		$op .= "Keep-Alive: 5\r\n";
		$op .= "Cache-control: no-cache\r\n";
		$op .= "Pragma: no-cache\r\n";
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
		$op .= "Cache-control: no-cache\r\n";
		$op .= "Pragma: no-cache\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n\r\n";
		print "sending request $req <br />\n";
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
		$op .= "Cache-control: no-cache\r\n";
		$op .= "Pragma: no-cache\r\n";
		$op .= "Cookie: automatweb=$cookie\r\n\r\n";

		print "<pre>";
		print "Logging out:<br />";
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
