<?php

/** aw code analyzer

	@author terryf <kristo@struktuur.ee>
	@cvs $Id: docgen_analyzer.aw,v 1.10 2004/02/19 23:23:22 duke Exp $

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

		$ex = "";

		if ($data["extends"] != "")
		{
			$dat = $data;
			$orb = get_instance("orb");
			$that = get_instance("core/docgen/docgen_analyzer");

			// now, do extended classes. we do that by parsing all the extends classes
			do {
				$level++;

				if ($dat["extends"] == "db_connector")
				{
					$_extends = "db";
				}
				else
				{
					$_extends = $dat["extends"];
				}

				// get the file the class is in.
				// for that we have to load it's orb defs to get the folder below the classes folder
				$orb_defs = $orb->load_xml_orb_def($_extends);
				$ex_fname = $this->cfg["basedir"]."/classes/".$orb_defs[$dat["extends"]]["___folder"]."/".$_extends.".".$this->cfg["ext"];

				$this->vars(array(
					"spacer" => str_repeat("&nbsp;", $level * 3),
					"inh_link" => $this->mk_my_orb("class_info", array("file" => "/".$_extends.".".$this->cfg["ext"])),
					"inh_name" => $dat["extends"]
				));
				$ex .= $this->parse("EXTENDER");

				$_dat = $that->analyze_file($ex_fname, true);
				$dat = $_dat["classes"][$dat["extends"]];
			} while ($dat["extends"] != "");
		}

		$this->vars(array(
			"name" => $data["name"],
			"extends" => $data["extends"],
			"end_line" => $data["end_line"],
			"start_line" => $data["start_line"],
			"FUNCTION" => $f,
			"EXTENDER" => $ex
		));

		return $this->parse();
	}

	/** displays information to the user about a class

		@attrib params=name nologin=0 is_public=0 all_args=0 caption="N&auml;ita klassi infot" default=0 name=class_info

		@param file required 

		@returns 
		html with class info

		@comment
		shows detailed info about a class
	**/
	function class_info($arr)
	{
		extract($arr);

		$data = $this->analyze_file($file);

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

					case T_DOLLAR_OPEN_CURLY_BRACES:
						$this->handle_brace_begin();
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
					$x_a[] = "caption=\"".str_replace("&", "&amp;", $attr["caption"])."\"";
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

						$od = str_replace(substr($this->cfg["basedir"]."/classes/",1), "", $this->_get_orb_defs($cldat));
						$od = str_replace(substr($this->cfg["basedir"]."/classes",1), "", $od);

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

	/**
		@attrib name=search_method
		@param method required 
	**/
	function search_method($arr)
	{
		set_time_limit(0);
		$method = $arr["method"];
		$p = get_instance("parser");
		$files = array();
		$p->_get_class_list(&$files, $this->cfg["classdir"]);
		
		sort($files);
		$found = 0;
		foreach($files as $file)
		{
			$fdat = $this->analyze_file($file,true);
			$bn = basename($file,".aw");
			$check = $fdat["classes"][$bn]["functions"][$method];
			if ($check)
			{
				print "fl = $file<br>";
				$start = $check["start_line"];
				$offset = $check["end_line"] - $start;
				$fc = join("",array_slice(file($file),$start-1,$offset+1));
				$fc = "<" . "?\n" . $fc . "\n" . "?" . ">"; 
				print "<pre>";
				print highlight_string($fc,true);
				//print_r($fdat["classes"][$bn]["functions"][$method]);
				print "</pre>";
				$found++;
			};



		}
		print "Found $found instances<br>";
	}


}
?>
