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
		$this->children = array();
		$fname = $args["fname"];
		$cf = get_instance("cache");

                $cf->get_cached_file(array(
                        "fname" => $fname,
                        "unserializer" => array(&$this,"parse_data"),
			"loader" => array(&$this,"load_data"),
                ));

		//$this->parse_data(array("content" => join("",file($basedir . $args["fname"]))));
	}
	
	function parse_data($args = array())
	{
		$this->content = $args["content"];
		$this->children = array();
		$this->fname = $args["fname"];
		$this->_setup_parser();
		$retval = &$this->children;
		return $retval;
	}

	function load_data($args = array())
	{
		$this->children = $args["data"];
	}

	function get_data($path)
	{
		$els = explode("/",$path);
		$data = &$this->children;
		foreach($els as $prnt)
		{
			if ($prnt)
			{
				$data = &$data[$prnt];
			};
		};
		return $data;
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
                	$b_idx = xml_get_current_byte_index($parser);
                	$frag = substr($this->content,$b_idx - 100, 200);
			$pref = htmlspecialchars(substr($frag,0,100));
			$suf = htmlspecialchars(substr($frag,101));
			$offender = htmlspecialchars(substr($frag,100,1));
			echo(sprintf("XML %s in file %s:%s <pre>%s--|<font color='red'>%s</font>|--%s</pre>",
			xml_error_string(xml_get_error_code($parser)),
			$this->fname,
			xml_get_current_line_number($parser),
			$pref,$offender,$suf));
			// if we do continue then the results are undefined
			die("<br>Cannot continue like this");
			
		};
		xml_parser_free($parser);

	}

	function xml_start_element($parser,$name,$attr)
	{
		array_push($this->context,$name);
		$ctx = "/" . join("/",$this->context);
//                print "$name starts, ctx = $ctx<br />";

		array_walk($this->paths,array(&$this,'depthwalker'),1);

		if (empty($this->paths[$ctx]) || ($this->paths[$ctx]["depth"] <= 0))
		{
			$this->paths[$ctx] = array("depth" => 0,"children" => 0);
		};

		$this->paths[$ctx]["content"] = "";
	}

	function xml_end_element($parser,$name)
	{
//                print "$name ends<br />";

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
			if (isset($this->children[$this->pname]))
			{
				if (empty($this->children[$this->directparent][$this->pname]))
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
