<?php

/** aw code analyzer

	@author terryf <kristo@struktuur.ee>
	@cvs $Id: docgen_analyzer.aw,v 1.6 2004/01/13 16:24:24 kristo Exp $

	@comment 
	analyses aw code
	generates documentation and orb defs
**/

class docgen_analyzer extends class_base
{
	function docgen_analyzer()
	{
		$this->init("core/docgen");
	}

	/**  
		
		@attrib name=class_list params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function class_list()
	{
		$p = get_instance("parser");
		$files = array();
		$p->_get_class_list(&$files, $this->cfg["classdir"]);
		
		sort($files);
		foreach($files as $file)
		{
			$file = str_replace($this->cfg["basedir"]."/classes", "", $file);
			$f .= html::href(array(
				"url" => $this->mk_my_orb("class_info", array("file" => $file)),
				"caption" => $file,
				"target" => "classinfo"
			))."<Br>";
		}

		die($f);
	}

	/**  
		
		@attrib name=frames params=name default="1"
		
		@param aa define value="100"
		
		@returns
		
		
		@comment

	**/
	function frameset()
	{
		$this->read_template("frameset.tpl");

		$this->vars(array(
			"left" => $this->mk_my_orb("class_list"),
			"right" => "about:blank"
		));
		die($this->parse());
	}

	function display_class($data)
	{
		$this->read_template("class_info.tpl");

		$f = "";
		foreach($data["functions"] as $func => $f_data)
		{
			$arg = "";

			$_ar = new aw_array($f_data["arguments"]);
			foreach($_ar->get() as $a_var => $a_data)
			{
				$this->vars(array(
					"arg_name" => $a_data["name"],
					"def_val" => $a_data["default_val"],
					"is_ref" => ($a_data["is_ref"] ? "X" : "")
				));

				$arg .= $this->parse("ARG");
			}

			$this->vars(array(
				"proto" => "function $func()",
				"name" => $func,
				"start_line" => $f_data["start_line"],
				"end_line" => $f_data["end_line"],
				"returns_ref" => ($f_data["returns_ref"] ? "X" : "&nbsp;"),
				"ARG" => $arg,
			));

			$f .= $this->parse("FUNCTION");
		}

		$this->vars(array(
			"name" => $data["name"],
			"extends" => $data["extends"],
			"end_line" => $data["end_line"],
			"start_line" => $data["start_line"],
			"orb_defs" => nl2br(htmlentities($this->_get_orb_defs($data))),
			"FUNCTION" => $f
		));

		return $this->parse();
	}

	/** displays information to the user about a class

		@attrib params=name nologin=0 is_public=0 all_args=0 caption="N&auml;ita klassi infot" default=0 name=class_info

		@param file required type=int acl=view;edit 
		@param file_2 optional type=int acl=view;edit default=19
		@param file_3 define value=100

		@returns 
		html with class info

		@comment
		shows detailed info about a class
	**/
	function class_info($arr)
	{
		extract($arr);

		$data = $this->analyze_file($file);
		//echo dbg::dump($data);

		foreach($data["classes"] as $class => $c_data)
		{
			$op .= $this->display_class($c_data);
		}

		return $op;
	}

	function analyze_file($file, $is_fp = false)
	{
		if (!$is_fp)
		{
			$fp = aw_ini_get("basedir")."/classes".$file;
		}
		else
		{
			$fp = $file;
		}
		$this->tokens = token_get_all(file_get_contents($fp));

		$this->data = array();
		$this->brace_level = 0;
		$this->in_class = false;
		$this->in_function = false;
		$this->cur_line = 1;
		$this->cur_file = $file;
		
		reset($this->tokens);
		while ($token = $this->get())
		{
			if (is_array($token))
			{
				list($id, $str) = $token;
				switch($id)
				{
					case T_CLASS:
						$this->handle_class_begin();
						break;

					case T_FUNCTION:
						$this->handle_function_begin();
						break;

					case T_COMMENT:
						$this->last_comment = $str;
						$this->last_comment_line = $this->get_line();
						break;
				}
			}
			else
			{
				switch($token)
				{
					case "{":
						$this->handle_brace_begin();
						break;
					
					case "}":
						$this->handle_brace_end();
						break;
				}
			}
		}

		return $this->data;
	}

	function get($ws = false)
	{
		list(, $ret) = each($this->tokens);
		if (is_array($ret))
		{
			list($id, $str) = $ret;
			//echo "token: ".token_name($id)." str = ".htmlentities($str)." , line = ".$this->cur_line."<br>";
			$this->cur_line += substr_count($str, "\n");
			if ($id == T_WHITESPACE && $ws == false)
			{
				$ret = $this->get();
			}
		}
		else
		{
			//echo "str: ".htmlentities($ret)." , line = ".$this->cur_line."<Br>";
			$this->cur_line += substr_count($ret, "\n");
		}
		return $ret;
	}

	function back()
	{
		prev($this->tokens);
	}

	function get_line()
	{
		return $this->cur_line;
	}

	function assert($tok, $id)
	{
		if (!is_array($tok))
		{
			die("assert failed on line ".$this->get_line()." token = ".dbg::dump($tok)." expect = complex token with type ".token_name($id));
		}

		if ($tok[0] != $id)
		{
			die("assert failed on line ".$this->get_line()." token = {id => ".token_name($tok[0]).", str = ".$tok[1]." } expected ".token_name($id));
		}
		//$this->dump_context();
	}

	function assert_fail($tok)
	{
		echo "assert_fail on token: ";
		$this->dump_tok($tok);
		die();
	}

	function assert_str($tok, $str)
	{
		if ($tok != $str)
		{
			die("assert_str failed on line ".$this->get_line()." token = ".dbg::dump($tok)." expect = $str ");
		}
	}

	function dump_tok($tok, $ctx = true)
	{
		if (is_array($tok))
		{
			echo "tok at line ".$this->get_line()." = { id => ".token_name($tok[0]).", str = ".htmlentities($tok[1])." } <br>";
			if ($ctx)
			{
				$this->dump_context();
			}
		}
		else
		{
			echo "tok at line ".$this->get_line()." = string '".htmlentities($tok)."' <br>";
			if ($ctx)
			{
				$this->dump_context();
			}
		}
	}

	function dump_context()
	{
		echo "dumping token context , 5 tokens before, 5 after: <br>";
		$this->back();
		$this->back();
		$this->back();
		$this->back();
		$this->back();
		for ($i = 0; $i < 11; $i++)
		{
			if ($i == 5)
			{
				echo "current follows: <br>";
			}
			$this->dump_tok($this->get(true), false);
		}
	}

	function handle_class_begin()
	{
		$this->in_class = true;
		$this->class_start_level = $this->brace_level;

		$name_t = $this->get();
		$this->assert($name_t, T_STRING);
		list($name_id, $name) = $name_t;

		$this->current_class = $name;
		$this->data["classes"][$name] = array();
		$this->data["classes"][$name]["name"] = $name;
		$this->data["classes"][$this->current_class]["start_line"] = $this->get_line();
		$this->data["classes"][$name]["file"] = $this->cur_file;

		$tok = $this->get();
		if (is_array($tok))
		{
			list($id, $str) = $tok;
			if ($id == T_EXTENDS)
			{
				$extends_t = $this->get();
				$this->assert($extends_t, T_STRING);
				list($extends_id, $extends) = $extends_t;

				$this->data["classes"][$name]["extends"] = $extends;
			}
		}
	}

	function handle_class_end()
	{
		$this->data["classes"][$this->current_class]["end_line"] = $this->get_line();
		$this->in_class = false;
		$this->current_class = "";
	}

	function handle_brace_begin()
	{
		$this->brace_level++;
	}

	function handle_brace_end()
	{
		$this->brace_level--;
		if ($this->in_class && $this->brace_level == $this->class_start_level)
		{
			$this->handle_class_end();
		}

		if ($this->in_function && $this->brace_level == $this->function_start_level)
		{
			$this->handle_function_end();
		}
	}

	function handle_function_begin()
	{
		// func name
		$tok = $this->get();
		$return_ref = false;
		if (!is_array($tok))
		{
			$this->assert_str($tok, "&");

			// func-returns-ref
			$return_ref = true;
			$tok = $this->get();
		}

		$this->assert($tok, T_STRING);
		list($id, $f_name) = $tok;
		$this->data["classes"][$this->current_class]["functions"][$f_name]["name"] = $f_name;
		$this->data["classes"][$this->current_class]["functions"][$f_name]["start_line"] = $this->get_line();
		$this->data["classes"][$this->current_class]["functions"][$f_name]["returns_ref"] = $return_ref;

		$this->data["classes"][$this->current_class]["functions"][$f_name]["doc_comment"] = $this->parse_doc_comment($this->last_comment);

		$this->current_function = $f_name;
		$this->in_function = true;
		$this->function_start_level = $this->brace_level;

		// start paren
		$tok = $this->get();
		$this->assert_str($tok, "(");

		// params
		$tok = $this->get();
		$this->back();

		$this->handle_function_arguments();

		// end paren
		$tok = $this->get();
		$this->assert_str($tok, ")");
	}

	function handle_function_arguments()
	{
		$tok = $this->get();
		$args = array();
		while (1)
		{
			$arg_is_ref = false;

			// possibilities here are: T_VARIABLE or string "&", then T_VARIABLE
			if (is_array($tok))
			{
				$this->assert($tok, T_VARIABLE);
			}
			else
			{
				// if it's a ")", then we got to the end of arguments
				if ($tok == ")")
				{
					break;
				}

				// must be reference argument
				$this->assert_str($tok, "&");
				$arg_is_ref = true;

				// read variable name
				$tok = $this->get();
			}

			$has_default_val = false;
			$defval_type = "";
			$defval = "";

			// possible sequence var [= type]|[,]|[)]
			$next = $this->get();
//			echo "next = ";
//			$this->dump_tok($next);
			if (!is_array($next) && $next == "=")
			{
				// default value
				$defval = $this->get();
				$df_mul = 1;

				if (!is_array($defval))
				{
					// string, it gots to be "-" as in "-1"
					$this->assert_str($defval, "-");
					$defval_mul = -1;
					$defval = $this->get();
				}

				$defval_type = $defval[0];

				switch($defval[0])
				{
					case T_STRING:
					case T_CONSTANT_ENCAPSED_STRING:
						$defval = $defval[1];
						break;

					case T_LNUMBER:
						$defval = (int)$defval[1] * $defval_mul;
						break;

					case T_ARRAY:
						$defval = $this->read_const_array_def();
						break;

					default:
						$this->assert_fail($defval);
						break;
				}

				$has_default_val = true;
				$next = $this->get();
			}

			if (!is_array($next))
			{
				if ($next == ",")
				{
					// no problem
					;
				}
				else
				if ($next == ")")
				{
					// put it back
					$this->back();
				}
				else
				{
					$this->assert_str($next, ",)");
				}
			}
			else
			{
				echo "got weird_ass token in func args on line ".$this->get_line()." tok = ";
				$this->dump_tok($next);
				die();
			}

			$arg = $tok[1];
			$args[$arg] = array(
				"name" => substr($arg, 1),
				"has_default_val" => $has_default_val,
				"default_val" => $defval,
				"default_value_type" => $defval_type,
				"is_ref" => $arg_is_ref
			);
			//$this->dump_tok($tok);
			$tok = $this->get();
		}

		$this->data["classes"][$this->current_class]["functions"][$this->current_function]["arguments"] = $args;
		$this->back();
	}

	function handle_function_end()
	{
		$this->data["classes"][$this->current_class]["functions"][$this->current_function]["end_line"] = $this->get_line();
		$this->in_function = false;
		$this->current_function = "";
	}

	// reads constant array definition and returns the corresponding array
	function read_const_array_def()
	{
		// right now, loop over tokens, count ( and ) 's  to skip the array
		$tok = $this->get();
		$this->assert_str($tok, "(");

		$level = 1;
		while ($level > 0)
		{
			$tok = $this->get();
			if (!is_array($tok) && $tok == "(")
			{
				$level++;
			}

			if (!is_array($tok) && $tok == ")")
			{
				$level--;
			}
		}

		return array();
	}

	function parse_doc_comment($str)
	{
		if (substr($str, 0, 3) != "/**" || ($this->get_line() - 1) != $this->last_comment_line)
		{
			return false;
		}

		$lines = explode("\n", $str);

		$data = array();

		$data["short_comment"] = trim(substr(array_shift($lines), 3));
		
		reset($lines);
		while (list(, $line) = each($lines))
		{
			$line = trim($line);
			if (substr($line, 0, strlen("@attrib")) == "@attrib")
			{
				// parse attributes
				$data["attribs"] = $this->_do_parse_attributes(trim(substr($line, strlen("@attrib"))));
			}
			else
			if (substr($line, 0, strlen("@param")) == "@param")
			{
				// parse parameter
				$_pm = trim(substr($line, strlen("@param")));
				$pdat = $this->_do_parse_parameter($_pm);
				if ($pdat["name"] == "")
				{
					die("error: do_parse_parameters failed for string $_pm <br>\n");
				}
				$data["params"][$pdat["name"]] = $pdat;
			}
			else
			if (substr($line, 0, strlen("@returns")) == "@returns")
			{
				$data["returns"] = trim(substr($line, strlen("@returns")));
				// now loop, until we find the end or next parameter
				while (list(, $line) = each($lines))
				{
					$line = trim($line);
					if ($line{0} == "@")
					{
						prev($lines);
						break;
					}
					else
					if (substr($line, 0, 3) == "**/")
					{
						break;
					}
					$data["returns"] .= "\n".$line;
				}
				$data["returns"] = trim($data["returns"]);
			}
			else
			if (substr($line, 0, strlen("@comment")) == "@comment")
			{
				$data["comment"] = trim(substr($line, strlen("@comment")));
				// now loop, until we find the end or next parameter
				while (list(, $line) = each($lines))
				{
					$line = trim($line);
					if ($line{0} == "@")
					{
						prev($lines);
						break;
					}
					else
					if (substr($line, 0, 3) == "**/")
					{
						break;
					}
					$data["comment"] .= "\n".$line;
				}
				$data["comment"] = trim($data["comment"]);
			}
		}
		return $data;
	}

	function _do_parse_attributes($str)
	{
		$ret = array();
		// any attribute can have quoted content
		// so we can't just explode by space
		$in_att_name = false;
		$in_att_value = false;
		$att_val_quoted = false;
		$cur_att_name = "";
		$cur_att_val = "";

		$len = strlen($str);
		for ($i = 0; $i < $len; $i++)
		{
//			echo "i = $i, char = ".$str{$i}." <br>";
			if ($in_att_name)
			{
				if ($str{$i} == "=")
				{
//					echo "in_att_name found eq <br>";
					$in_att_name = false;
					$in_att_value = true;
				}
				else
				{
//					echo "in att name add <br>";
					$cur_att_name .= $str{$i};
				}
			}
			else
			if ($in_att_value)
			{
				if ($str{$i} == "\"" && trim($cur_att_value) == "")
				{
//					echo "in att value begin quoted <br>";
					$att_val_quoted = true;
				}

				$end_value = false;
				if ($att_val_quoted && $str{$i} == "\"" && trim($cur_att_value) != "")
				{
//					echo "att val found end quoted cur att = $cur_att_value <br>";
					$end_value = true;
				}
				else
				if ($att_val_quoted == false && $str{$i} == " ")
				{
//					echo "att val found end not quoted cur att = $cur_att_value <br>";
					$end_value = true;
				}

				if ($end_value)
				{
					if ($att_val_quoted)
					{
						$ret[trim($cur_att_name)] = substr($cur_att_value, 1);
					}
					else
					{
						$ret[trim($cur_att_name)] = $cur_att_value;
					}
//					echo "att val ended att $cur_att_name => $cur_att_value <br>";
					$in_att_value = false;
					$att_val_quoted = false;
					$cur_att_name = "";
					$cur_att_value = "";
				}
				else
				{
//					echo "add to cur att value ".$str{$i}." cur val = $cur_att_value<br>";
					$cur_att_value .= $str{$i};
				}
			}
			else
			{
				if ($str{$i} != " ")
				{
//					echo "not space, start new att <Br>";
					$in_att_name = true;
					$cur_att_name = $str{$i};
				}
			}
		}
		if (trim($cur_att_name) != "")
		{
			$ret[trim($cur_att_name)] = $cur_att_value;
		}
		return $ret;
	}

	function _do_parse_parameter($str)
	{
		$ret = array();
		list($ret["name"], $ret["req"], $extra) = explode(" ", $str, 3);
		
		// now parse extra params
		$att = $this->_do_parse_attributes($extra);

		$ret = $ret+$att;
		return $ret;
	}

	function _get_orb_defs($data)
	{
		$xml  = "<?xml version='1.0'?>\n";
		$xml .= "<orb>\n";

		$folder = substr(dirname($data["file"]), 1);

		$xml .= "\t<class name=\"".$data["name"]."\" folder=\"".$folder."\" extends=\"".$data["extends"]."\">\n";

		foreach($data["functions"] as $f_name => $f_data)
		{
			// func is public if name attrib is set
			$attr = $f_data["doc_comment"]["attribs"];
			if (($a_name = $attr["name"]) != "")
			{
				$xml .= "\t\t<action name=\"$a_name\"";
				$x_a = array();
				if (isset($attr["default"]) && $attr["default"] == 1)
				{
					$x_a[] = "default=\"1\"";
				}

				if (isset($attr["nologin"]) && $attr["nologin"] == 1)
				{
					$x_a[] = "nologin=\"1\"";
				}

				if (isset($attr["is_public"]) && $attr["is_public"] == 1)
				{
					$x_a[] = "is_public=\"1\"";
				}

				if (isset($attr["all_args"]) && $attr["all_args"] == 1)
				{
					$x_a[] = "all_args=\"1\"";
				}

				if (isset($attr["is_content"]) && $attr["is_content"] == 1)
				{
					$x_a[] = "is_content=\"1\"";
				}

				if (isset($attr["caption"]) && $attr["caption"] != "")
				{
					$x_a[] = "caption=\"".$attr["caption"]."\"";
				}

				$xml .= " ".join(" ", $x_a).">\n";

				$xml .= "\t\t\t<function name=\"$f_name\">\n";
				$xml .= "\t\t\t\t<arguments>\n";
	
				// make parameters
				$par = new aw_array($f_data["doc_comment"]["params"]);

				foreach($par->get() as $p_name => $p_dat)
				{
					$xml .= "\t\t\t\t\t<".$p_dat["req"]." name=\"$p_name\"";
					
					$x_p = array();
					if (isset($p_dat["type"]) && $p_dat["type"] != "")
					{
						$x_p[] = "type=\"".$p_dat["type"]."\"";
					}

					if (isset($p_dat["acl"]) && $p_dat["acl"] != "")
					{
						$x_p[] = "acl=\"".$p_dat["acl"]."\"";
					}

					if (isset($p_dat["default"]) && $p_dat["default"] != "")
					{
						$x_p[] = "default=\"".$p_dat["default"]."\"";
					}

					if (isset($p_dat["value"]) && $p_dat["value"] != "")
					{
						$x_p[] = "value=\"".$p_dat["value"]."\"";
					}

					$xml .= " ".join(" ", $x_p)."/>\n";
				}
				$xml .= "\t\t\t\t</arguments>\n";
				$xml .= "\t\t\t</function>\n";
				$xml .= "\t\t</action>\n\n";
			}
		}
		$xml .= "\t</class>\n";
		$xml .= "</orb>\n";
		return $xml;
	}
	
	function get_doc_comment_from_orb_def($class)
	{
		$o = get_instance("orb");
		$dat = $o->load_xml_orb_def($class);
		$dat = $dat[$class];

		// also, copy the file to the /www/dev/terryf/awcl/classes folder with the correct subfolders
		$from = "/www/dev/terryf/automatweb_dev/classes/".$dat["___folder"]."/".$class.".aw";
		$to = "/www/dev/terryf/awcl/classes/".$dat["___folder"]."/".$class.".aw";
		echo "copied $from to $to <br>\n";
		copy($from, $to);
		flush();

		foreach($dat as $nm => $inf)
		{
			if ($nm == "_extends" || $nm == "___folder" || $nm == "default")
			{
				continue;

			}

			//echo "Comment for function $inf[function] = <br><br>\n";

			$comm  = "\t/** short comment \n";
			$comm .= "\t\t\n";

			$x_a = array();
			$x_a["nologin"] = $inf["nologin"];
			$x_a["is_public"] = $inf["is_public"];
			$x_a["all_args"] = $inf["all_args"];
			$x_a["caption"] = $inf["caption"];
			$x_a["default"] = (int)($nm == $dat["default"]);

			$comm .= "\t\t@attrib name=".$nm." params=name ".join(" ", map2("%s=\"%s\"",$x_a))."\n\t\t\n";

			// make params
			foreach($inf["required"] as $p_n => $param)
			{
				$comm .= "\t\t@param $p_n required";
				if ($inf["types"][$p_n] != "")
				{
					$comm .= " type=".$inf["types"][$p_n];
				}

				if ($inf["acl"][$p_n] != "")
				{
					$comm .= " acl=\"".$inf["acl"][$p_n]."\"";
				}

				if ($inf["defaults"][$p_n] != "")
				{
					$comm .= " default=\"".$inf["defaults"][$p_n]."\"";
				}

				$comm .= "\n";
			}

			foreach($inf["optional"] as $p_n => $param)
			{
				$comm .= "\t\t@param $p_n optional";
				if ($inf["types"][$p_n] != "")
				{
					$comm .= " type=".$inf["types"][$p_n];
				}

				if ($inf["acl"][$p_n] != "")
				{
					$comm .= " acl=\"".$inf["types"][$p_n]."\"";
				}

				if ($inf["defaults"][$p_n] != "")
				{
					$comm .= " default=\"".$inf["defaults"][$p_n]."\"";
				}

				$comm .= "\n";
			}

			foreach($inf["define"] as $p_n => $val)
			{
				$comm .= "\t\t@param $p_n define";
				if ($inf["types"][$p_n] != "")
				{
					$comm .= " type=".$inf["types"][$p_n];
				}

				if ($inf["acl"][$p_n] != "")
				{
					$comm .= " acl=\"".$inf["types"][$p_n]."\"";
				}

				if ($inf["defaults"][$p_n] != "")
				{
					$comm .= " default=\"".$inf["defaults"][$p_n]."\"";
				}

				$comm .= " value=\"$val\"\n";
			}
			$comm .= "\t\t\n";
			$comm .= "\t\t@returns\n";
			$comm .= "\t\t\n";
			$comm .= "\t\t\n";
			$comm .= "\t\t@comment\n";
			$comm .= "long comment\n";
			$comm .= "\t**/\n";
			//echo "comm = ".nl2br(htmlentities($comm))." <br>";

			// now, write the actual comment to the file
			// first, run the file through the analyzer
			// then extract the beginning line of the function
			// and insert the comment before that. 
			$this->analyze_file($to, true);

			$start_line = $this->data["classes"][$class]["functions"][$inf["function"]]["start_line"]-1;
			$newf = "";
			$fls = file($to);

			$last_oc_line = 0;
			foreach($fls as $ln => $tline)
			{
				if (trim($tline) == "////")
				{
					$last_oc_line = $ln;
				}
				if ($ln == $start_line)
				{
					break;
				}
				if (substr(trim($tline), 0, 8) == "function")
				{
					$last_oc_line = 0;
				}
			}

			$oc = "";
			$shortc = "";
			foreach($fls as $ln => $tline)
			{
				if ($ln == $start_line)
				{
					$newf .= str_replace("short comment", $shortc, str_replace("long comment", $oc, $comm));
				}

				if ($ln >= $last_oc_line && $ln < $start_line && $last_oc_line)
				{
					if ($ln == $last_oc_line)
					{
						continue;
					}

					if (substr(trim($tline), 0, 4) == "// !")
					{
						$shortc = trim(substr(trim($tline), 4));
					}
					else
					{
						$oc .= "\t\t".trim(substr(trim($tline), 2))."\n";
					}
				}
				else
				{
					$newf .= $tline;
				}
			}
			$this->put_file(array(
				"file" => $to,
				"content" => $newf
			));
			echo "did function $inf[function] <br>\n";
			flush();
		}
	}

	function make_doc_comments_from_orb_defs()
	{
		// get all files in the xml/orb folder and do the trick for them
		$dc = $this->get_directory(array("dir" => aw_ini_get("basedir")."/xml/orb"));
		foreach($dc as $file)
		{
			$this->get_doc_comment_from_orb_def(basename($file,".xml"));
		}
		//$this->get_doc_comment_from_orb_def("docgen_analyzer");
		//die("all done<br>");
	}

	function make_orb_defs_from_doc_comments()
	{
		$p = get_instance("parser");
		$files = array();
		$p->_get_class_list(&$files, $this->cfg["basedir"]."/classes");

		foreach($files as $file)
		{
			// check if file is modified
			$clmod = @filemtime($file);
			$xmlmod = @filemtime($this->cfg["basedir"]."/xml/orb/".basename($file, ".aw").".xml");

			if ($clmod >= $xmlmod)
			{
				$this->analyze_file($file, true);
				if (!is_array($this->data["classes"]) || count($this->data["classes"]) < 1)
				{
					continue;
				}

				foreach($this->data["classes"] as $class => $cldat)
				{
					if (is_array($cldat["functions"]) && $class != "" && strtolower($class) == strtolower(basename($file, ".aw")))
					{
						echo "make orb defs for $file\n";

						$od = str_replace($this->cfg["basedir"]."/classes/", "", $this->_get_orb_defs($cldat));
						$this->put_file(array(
							"file" => $this->cfg["basedir"]."/xml/orb/".$class.".xml",
							"content" => $od
						));
					}
				}
				flush();
			}
		}
		echo ("all done\n");
	}
}
?>
