<?php
// $Version$
// Don't pay attention, this is work in progress
// Info loeme sisse $HTTP_RAW_POST_DATA seest
include("const.aw");
include("site_header.$ext");
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

		#$this->struct = join("",file("/www/automatweb_dev/classes/request.xml"));
	}

	function _init_parser($args = array())
	{
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,false);
		xml_set_object($this->parser,&$this);
		xml_set_element_handler($this->parser,"_xml_rpc_tag_start","_xml_rpc_tag_end");
		xml_set_character_data_handler($this->parser, "_xml_rpc_cdata");
		xml_set_default_handler($this->parser, "_xml_rpc_def");
	}

	function _xml_rpc_tag_start($parser,$name,$attribs)
	{
		switch($name)
		{
			case "methodCall":
				$this->inMethod = true;
				break;

			case "methodName":
				$this->inMethodName = true;
				break;

			case "params":
				$this->inParams = true;
				break;

			case "param":
				$this->inParam = true;
				break;
			
			case "value":
				$this->inValue = true;
				break;
			
			default:
				if ($this->inValue)
				{
					switch($name)
					{
						case "i4":
							$this->type = "int";
							break;

						case "string":
							$this->type = "string";
							break;
					};
				};
		};

		//print "start: name = $name<br>";
		if (sizeof($attribs) > 0)
		{
		//	print "<pre>";
		//	print_r($attribs);
		//	print "</pre>";
		};

	}

	function _xml_rpc_tag_end($parser,$name)
	{
		switch($name)
		{
			case "methodCall":
				$this->inMethod = false;
				break;

			case "methodName":
				$this->inMethodName = false;
				break;

			case "params":
				$this->inParams = false;
				break;
			
			case "param":
				$this->inParam = true;
				break;

			case "value":
				$this->inValue = false;
				$this->type = false;
				break;

			default:					
		};
		//print "end: name= $name<br>"; 

	}

	function _xml_rpc_cdata($parser,$data)
	{
		if ($this->inMethodName)
		{
			list($class,$method) = explode(".",$data);
			$this->class = $class;
			$this->method = $method;
			//print "class = $class, method = $method<br>";
		};

		if ($this->inValue)
		{
			if ($this->type == "int")
			{
				$this->args[] = $data;
				//print "got integer with value $data<br>";
			}
			elseif ($this->type == "string")
			{
				$this->args[] = $data;
				//print "got string with value $data<br>";
			};
		};
	}

	function _xml_rpc_def($parser,$data)
	{

	}


	function send_request($args = array())
	{
		$request = join("",file("/www/automatweb_dev/classes/request.xml"));
		$fp=fsockopen("xmlrpc.usefulinc.com","80",&$this->errno, &$this->errstr);
		$op = "POST /demo/server.php HTTP/1.1\r\n";
		$op .= "User-Agent: Autom@tWeb\r\n";
		$op .= "Host: xmlrpc.usefulinc.com\r\n";
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

		print "<h1>Resources available @ xmlrpc.usefulinc.com:80/demo/server.php</h1>";
		print "<pre>";
		print htmlspecialchars($ipd);
		print "</pre>";
		fclose($fp);
	}

	////
	// Parsib xml-rpc requesti andmeblokiks
	function parse_rpc_struct($args = array())
	{
		$this->_init_parser();
		classload("xml_support");

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
		header("Content-Type: text/xml");
		header("Content-Length: " . strlen($res));
		print $res;
		exit;

	}

	function gen_response($args = array())
	{
		classload("xml_support");
		$res = xml_gen_header();
		$res .= xml_open_tag("methodResponse",0);
		$res .= xml_open_tag("params",1);
		$res .= xml_open_tag("param",2);
		$res .= xml_open_tag("array",3);
		$res .= xml_open_tag("data",3);
		$res .= "<value><string>test.method</string></value>\n";
		$res .= "<value><string>test.method2</string></value>\n";
		$res .= "<value><string>test.method3</string></value>\n";
		$res .= xml_close_tag("data",3);
		$res .= xml_close_tag("array",3);
		$res .= xml_close_tag("param",2);
		$res .= xml_close_tag("params",1);
		$res .= xml_close_tag("methodResponse",0);
		header("Content-Type: text/xml");
		header("Content-Length: " . strlen($res));
		print $res;
		exit;
	}
		
};

?>
