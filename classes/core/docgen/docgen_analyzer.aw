<?php

/** aw code analyzer

	@author terryf <kristo@struktuur.ee>
	@cvs $Id: docgen_analyzer.aw,v 1.11 2004/02/27 11:16:27 kristo Exp $

	@comment 
	analyses aw code
**/

class docgen_analyzer extends class_base
{
	function docgen_analyzer()
	{
		$this->init("core/docgen");
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

					case T_STRING:
						$this->handle_t_string($token);
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

		if ($this->data["classes"][$this->current_class]["functions"][$f_name]["doc_comment"] != false)
		{
			$this->data["classes"][$this->current_class]["functions"][$f_name]["doc_comment_str"] = $this->last_comment;
		}

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

	function handle_t_string($tok)
	{
		if ($tok[1] == "get_instance" || $tok[1] == "classload")
		{
			// follows:
			// (
			// encapsed_string class name || defined constant || variable
			// )
			// ;
			$o = $this->get();
			$this->assert_str($o, "(");

			// variable dependency?
			$is_var = false;

			$cln = $this->get();
			switch($cln[0])
			{
				case T_CONSTANT_ENCAPSED_STRING:
					// the string is the class name, remove quotes and voila
					$class = str_replace("\"","", str_replace("'","",$cln[1]));
					$_tok = $this->get();
					if ($_tok != ")")
					{
						// something else, mark as vraiable dependency right now
						$is_var = true;
						$class = $cln[1].$this->do_read_funcall();
					}
					else
					{
						$this->back();
					}
					break;

				case T_STRING:
					// the string is the CL_ define
					$class = $this->cfg["classes"][constant($cln[1])]["file"];
					break;

				case T_VARIABLE:
					// the string is in the variable, mark as depend on all
					$class = "variable dependency!";
					$is_var = true;
					$class = $cln[1].$this->do_read_funcall();
					break;

				default:
					$this->assert_fail($cln);
					break;
			}

			$o = $this->get();
			$this->assert_str($o, ")");

			$o = $this->get();
			$this->assert_str($o, ";");

			if ($this->current_class)
			{
				$this->data["classes"][$this->current_class]["dependencies"][] = array(
					"dep_via" => $tok[1],
					"function" => $this->current_function,
					"line" => $this->get_line(),
					"dep" => $class,
					"is_var" => $is_var
				);
			}
		}
	}

	/** skips function call arguments, returns content
	**/
	function do_read_funcall()
	{
		$cnt = 1;
		$class = "";
		// skip until we get to the closing )
		do {
			$tok = $this->get();
			if (!is_array($tok) && $tok == "(")
			{
				$cnt++;
			}
			if (!is_array($tok) && $tok == ")")
			{
				$cnt --;
			}

			if ($cnt > 0)
			{
				if (is_array($tok))
				{
					$class .= $tok[1];
				}
				else
				{
					$class .= $tok;
				}
			}
		} while ($cnt > 0);
		$this->back();

		return $class;
	}
}
?>