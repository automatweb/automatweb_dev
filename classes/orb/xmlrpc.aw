<?php

class xmlrpc extends aw_template
{
	function xmlrpc()
	{
		$this->init("");
	}

	////
	// !sends the request to the remote server, retrieves the response and decodes it
	function do_request($arr)
	{
		$xml = $this->make_request_xml($arr);

		$resp = $this->send_request(array(
			"server" => $arr["remote_host"],
			"port" => 80,
			"handler" => "/xmlrpc.aw",
			"request" => $xml,
			"session" => $arr["remote_session"]
		));
		return $this->decode_response($resp);
	}

	////
	// !creates the xml for the request. 
	function make_request_xml($arr)
	{
		extract($arr);
		$xml  = "<?xml version=\"1.0\"?>\n";
		$xml .= "<methodCall>\n";
		$xml .= "\t<methodName>".$class."::".$action."</methodName>\n";

		if (is_array($params) && count($params) > 0)
		{
			$xml .= "\t<params>\n";
			$xml .= "\t\t<struct>\n";

			foreach($params as $name => $value)
			{
				$xml .= "\t\t\t<member>\n";
				$xml .= "\t\t\t\t<name>$name</name>\n";
				if (is_array($value))
				{
					$value = aw_serialize($value, SERIALIZE_NATIVE);
				}
				$xml .= "\t\t\t\t<value>$value</value>\n";
				$xml .= "\t\t\t</member>\n";
			}

			$xml .= "\t\t</struct>\n";
			$xml .= "\t</params>\n";
		}

		$xml .= "</methodCall>\n";
		return $xml;
	}

	function decode_response($xml)
	{
		$xml = str_replace("&", "__faking_bitchass_barbara_streisand__",$xml);
		$result = array();

		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parse_into_struct($parser,$xml,&$values,&$tags); 
		xml_parser_free($parser); 

		foreach($values as $val)
		{
			if ($val["tag"] == "value")
			{
				$val["value"] = str_replace("__faking_bitchass_barbara_streisand__","&", $val["value"]);
//				$this->dequote(&$val["value"]);
				$try = aw_unserialize(urldecode($val["value"]));
				if (is_array($try))
				{
					return $try;
				}
				return $val["value"];
			}
		}
		return false;
	}

	////
	// !Sends an RPC query to a server and returns the results
	// arguments:
	// server(string)
	// port(int)
	// handler(string) - millise skriptile andmed POST-ida
	// request(text) - xml request to send
	// session - the session id to send
	function send_request($args = array())
	{
		extract($args);
		$fp = fsockopen($server,$port,&$this->errno, &$this->errstr);
		$op = "POST $handler HTTP/1.0\r\n";
		$op .= "User-Agent: AutomatWeb\r\n";
		$op .= "Host: $server\r\n";
		$op .= "Content-Type: text/xml\r\n";
		if ($session != "")
		{
			$op.="Cookie: automatweb=$session\r\n";
		}
		$op .= "Content-Length: " . strlen($request) . "\r\n\r\n";
		$op .= $request;
		if (!fputs($fp, $op, strlen($op))) 
		{
			$this->errstr="Write error";
			return 0;
		}
		$ipd = "";
		while($data=fread($fp, 32768)) 
		{
			$ipd.=$data;
		}
		fclose($fp);
		list($headers,$data) = explode("\r\n\r\n",$ipd);
		return $data;
	}

	////
	// !decodes an xmlrpc request - request data comes from global scope
	// returns array with members:
	// class - the class of the request
	// action - the action of the request
	// params - array of parameters for request
	function decode_request()
	{
		$xml = $GLOBALS["HTTP_RAW_POST_DATA"];
		$result = array();

		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parse_into_struct($parser,$xml,&$values,&$tags); 
		xml_parser_free($parser); 

		$res = $this->req_decode_xml($values);
		return $res;	
	}

	function req_decode_xml($values)
	{
		$result = array();
		$name = "";
		$in_value = false;
		$this->i = 0;

		$continue = $i < sizeof($values);
		while ($continue)
		{
			$token = $values[$this->i++];

			if (($token["tag"] == "methodName") && ($token["type"] == "complete"))
			{
				list($result["class"], $result["action"]) = explode("::", $token["value"]);
			};

			if ($in_value && ($token["type"] == "complete") )
			{
				$result["params"][$name] = $token["value"];
				$in_value = false;
			};

			if ($in_value && ($token["tag"] == "struct") )
			{
				$in_value = false;
				$tmp = $this->req_decode_xml($values);
				$result["params"][$name] = $tmp["params"];
			};

			if (($token["tag"] == "struct") && ($token["type"] == "close"))
			{
				return $result;
			};

			if ($token["tag"] == "member")
			{
				if ($token["type"] == "open")
				{
					//print "w00p!";
				};
			};

			if (($token["tag"] == "name") && ($token["type"] == "complete"))
			{
				$name = $token["value"];
			};

			if (($token["tag"] == "value") && ($token["type"] == "complete"))
			{
				$result["params"][$name] = $token["value"];
			};

			if (($token["tag"] == "value") && ($token["type"] == "open"))
			{
				$in_value = true;
			};

			$continue = $this->i < sizeof($values);
		};
		
		return $result;
	}

	function encode_return_data($dat)
	{
		$xml  = "<?xml version=\"1.0\"?>\n";
		$xml .= "<methodResponse>\n";
		$xml .= "\t<params>\n";
		$xml .= "\t\t<param>\n";
		if (is_array($dat))
		{
			$dat = urlencode(aw_serialize($dat, SERIALIZE_NATIVE));
		}
		$xml .= "\t\t\t<value>".$dat."</value>\n";
		$xml .= "\t\t</param>\n";
		$xml .= "\t</params>\n";
		$xml .= "</methodResponse>\n";
		return $xml;
	}
}
?>