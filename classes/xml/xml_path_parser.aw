<?php
class xml_path_parser 
{
	function xml_path_parser()
	{

	}

	function set_handler($args = array())
	{
		extract($args);
		$this->handlers[$path] = array(
			"name" => "caller",
			"started" => 0,
		);
	}

	function parse_file($args = array())
	{
		$basedir = aw_ini_get("basedir");
		if ($args["fname"])
		{
			$this->content = join("",file($basedir . $args["fname"]));
		}
		else
		{
			$this->content = $args["content"];	
		};
		$this->_setup_parser();
		print "<big>-------------------------------</big>";
		print "<pre>";
		print_r($this->children);
		print "</pre>";
		print "<big>-------------------------------</big>";
	}

	function _setup_parser()
	{
		$this->context = array();
		$this->paths = array();
		
		$parser = xml_parser_create();
		xml_set_object($parser, &$this);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
		xml_set_element_handler($parser,"xml_start_element","xml_end_element");
		xml_set_character_data_handler($parser,"xml_cdata");
		if (!xml_parse($parser,$this->content))
		{
			echo(sprintf("XML error: %s at line %d",
			xml_error_string(xml_get_error_code($parser)),
			xml_get_current_line_number($parser)));
		};
		xml_parser_free($parser);

	}

	function xml_start_element($parser,$name,$attr)
	{
		array_push($this->context,$name);
		$ctx = "/" . join("/",$this->context);
		print "$name starts, ctx = $ctx<br>";

		array_walk($this->paths,array(&$this,'depthwalker'),1);

		if ($this->paths[$ctx]["depth"] <= 0)
		{
			$this->paths[$ctx] = array("depth" => 0,"children" => 0);
		};

		$this->paths[$ctx]["content"] = "";
	}

	function xml_end_element($parser,$name)
	{
		print "$name ends<br>";

		array_walk($this->paths,array(&$this,'depthwalker'),-1);
		$ctx = "/" . join("/",$this->context);
		array_pop($this->context);

		$this->directparent = $this->context[sizeof($this->context)-1];

		$this->pname = substr(strrchr($ctx,"/"),1);
		if ($this->directparent)
		{
			array_walk($this->paths,array(&$this,'collector'),$ctx);
		};
	}

	function xml_cdata($parser,$data)
	{
		if (strlen(trim($data)) == 0)
		{
			return false;
		};
		$ctx = "/" . join("/",$this->context);
		array_walk($this->paths,array(&$this,'add_data'),$data);
	}

	function depthwalker(&$value,$key,$step)
	{
		if ($value["depth"] >= 0)
		{
			$value["depth"] += $step;
		};
	}

	// fast scan over shit to figure out where to add the data
	function add_data(&$value,$key,$data)
	{
		if ($value["depth"] == 0)
		{
			$value["content"] .= $data;
		};
	}

	function collector(&$value,$key,$ctx)
	{
		// collect all data in subkeys of this element
		// but only in the subkeys.

		if ((strpos($key,$ctx) === 0))
		{
			// now check whether the ending tag has any children defined - if so, push 'em
			if ($this->children[$this->pname])
			{
				if (!is_array($this->children[$this->directparent][$this->pname]))
				{
					$this->children[$this->directparent][$this->pname] = array();
				};

				array_push($this->children[$this->directparent][$this->pname],$this->children[$this->pname]);
				// it moved t a better place, get rid of it
				unset($this->children[$this->pname]);
			}
			elseif ($value["content"])
			{
				$this->children[$this->directparent][$this->pname]["text"] = $value["content"];
				$value["content"] = "";
			};
		}
	}
};
?>
