<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/remote_login.aw,v 2.10 2002/11/15 18:06:40 kristo Exp $
// remote_login.aw - AW remote login
class remote_login extends aw_template 
{
	function remote_login($args = array())
	{
		extract($args);
		$this->init("automatweb/remote_login");
	}

	function change($args = array())
	{
		extract($args);
		$this->read_template("change.tpl");
		if ($parent)
		{
			$act = "Lisa AW login objekt";
			$meta = array();
		}
		else
		{
			$obj = $this->get_obj_meta($id);
			$parent = $obj["parent"];
			$act = "Muuda AW login objekti";
			$meta = $obj["meta"];
		};

		$this->mk_path($parent,$act);
		$this->vars(array(
			"name" => $obj["name"],
			"server" => $meta["server"],
			"uid" => $meta["login_uid"],
			"password" => $meta["login_password"],
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
				"class_id" => CL_AW_LOGIN,
			));
		};

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => array(
				"login_uid" => $uid,
				"login_password" => $password,
				"server" => $server
			),
		));
		return $this->mk_my_orb("change",array("id" => $id));
	}


	function handshake($args = array())
	{
		extract($args);
		$socket = get_instance("socket");
		$socket->open(array(
			"host" => $host,
			"port" => 80,
		));
		
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
			print "Logging out:<bR>";
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
		$ob = $this->get_object($id);
		$this->handshake(array(
			"silent" => true,
			"host" => $ob["meta"]["server"]
		));

		$this->login(array(
			"host" => $ob["meta"]["server"],
			"uid" => $ob["meta"]["login_uid"],
			"password" => $ob["meta"]["login_password"],
			"silent" => true
		));

		return array($ob["meta"]["server"],$this->cookie);
	}

	function get_server($id)
	{
		$ob = $this->get_object($id);
		return $ob["meta"]["server"];
	}

	function getcookie($arr)
	{
		die("Relax, take a cookie.");
	}
};
?>
