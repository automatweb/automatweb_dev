<?php
// $Version$
// xmlrpc_server.aw - XML-RPC server class
include("const.aw");
include("site_header.$ext");
classload("xml_support");

class xmlrpc_server {
	function xmlrpc_server($args = array())
	{
		global $HTTP_RAW_POST_DATA;
		$this->struct = $HTTP_RAW_POST_DATA;
		if (strlen($HTTP_RAW_POST_DATA) == 0)
		{
			exit;
		}
		
		// see on true, kui me oleme parsimisega methodCall bloki sees
		$this->inMethod = false; 

		// see on true, kui eelmine blokk sisaldas methodName algustagi
		$this->inMethodName = false;

		// see on true, kui me oleme parsimisega params bloki sees
		$this->inParams = false;

	}

	////
	// !Sends an RPC query to a server and returns the results
	// arguments:
	// server(string)
	// port(int)
	// handler(string) - millise skriptile andmed POST-ida
	// request(text) - response
	function send_request($args = array())
	{
		extract($args);
		$fp=fsockopen($server,$port,&$this->errno, &$this->errstr);
		$op = "POST $handler HTTP/1.1\r\n";
		$op .= "User-Agent: Autom@tWeb\r\n";
		$op .= "Host: $server\r\n";
		$op .= "Content-Type: text/xml\r\n";
		$op .= "Content-Length: " . strlen($request) . "\r\n\r\n";
		$op .= $request;
		if (!fputs($fp, $op, strlen($op))) {
			$this->errstr="Write error";
			return 0;
		}

		$ipd="";
		 
		while($data=fread($fp, 32768)) {
			$ipd.=$data;
		}
		
		fclose($fp);

		return $ipd;
	}

	////
	// !Sends a response to RPC query
	// arguments:
	// data(text)
	function send_response($args = array())
	{
		extract($args);
		header("Content-Type: text/xml");
		header("Content-Length: " . strlen($data));
		print $data;
	}

	////
	// Parsib xml-rpc requesti andmeblokiks
	function parse_rpc_struct($args = array())
	{
		global $lang_id;
		if (!$lang_id)
		{
			$lang_id = 1;
		};

		$raw = rpc_extract_struct($this->struct);
		$datablock = _rpc_extract_struct($raw);

		list($this->class,$this->method) = explode(".",$datablock["methodName"]);

		if ($this->class)
		{
			classload($this->class);
			$t = new $this->class;
			$method = $this->method;
			$answerblock = $t->$method($datablock);
		};

		$resblock = rpc_create_struct($answerblock);
		
		$res = xml_gen_header();
		$res .= xml_open_tag("methodResponse",0);
		$res .= xml_open_tag("params",1);
		$res .= xml_open_tag("param",2);
		$res .= $resblock;
		$res .= xml_close_tag("param",2);
		$res .= xml_close_tag("params",1);
		$res .= xml_close_Tag("methodResponse",0);
		$this->send_response(array("data" => $res));
		// sucks
		exit;

	}

};
?>
