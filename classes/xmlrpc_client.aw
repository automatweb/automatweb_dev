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
			case "methodResponse":
				$this->inMethod = true;
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
			case "methodResponse":
				$this->inMethod = false;
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

		if ($this->inParams)
		{
			print $data;
		};
	}

	function _xml_rpc_def($parser,$data)
	{

	}

	////
	// Parsib xml-rpc requesti andmeblokiks
	function parse_rpc_struct($args = array())
	{
		$this->_init_parser();
		if (!xml_parse($this->parser, $this->struct,  1))
		{
			sprintf("XML error: %s at line %d",	xml_error_string(xml_get_error_code($this->parser)),
								xml_get_current_line_number($parser));
		};
		xml_parser_free($this->parser);
	}

};

?>
