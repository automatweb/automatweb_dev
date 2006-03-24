<?php

/** aw code analyzer

	@author terryf <kristo@struktuur.ee>
	@cvs $Id: aw_code_analyzer.aw,v 1.2 2006/03/24 13:33:08 dragut Exp $

	@comment
	analyses aw code
**/

class aw_code_analyzer extends class_base
{
	function aw_code_analyzer()
	{
		$this->init("core/aw_code_analyzer");
	}

	/**
		@attrib api=1 params=pos 

		@param file required type=string
			Path to aw classfile.
		@param is_fp optional type=bool default=false
			If file parameter contains full path to classfile or not.
		@errors none

		@returns 
			Associative array with classinfo.
		@comment
			If 'file' parameter doesn't contain full path to file, the root is classes folder
			and 'file' parameter must begin with '/' (slash).
			If 'file' parameter contains full path to file, then second parameter has to be set true.
		@examples
			$analyzer = get_instance("core/aw_code_analyzer/aw_code_analyzer");

			$filename_fp = '/www/automatweb_new/classes/db.aw';
			$filename = '/vcl/calendar.aw';

			$db_class_info = $analyzer->analyze_file($filename_fp, true);
			$cal_class_info = $analyzer->analyze_file($filename);

	**/
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

		$this->classinfo = aw_ini_get("classes");

/* dbg siin ja edaspidi selles failis parandus veale. kui esines stringides muutuja: "asdfas{$sadf}dflkljlsd" siis kuna '{' ei t88delda siin T_CURLY_OPEN-ina, kutsuti '}' esinemisel handle_brace_end ilma vastava handle_brace_begin-ita. dbg on ajutine lahendus! */ $variable_token_occurred = false;

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

					// this handles ${variable}'s in code
					case T_DOLLAR_OPEN_CURLY_BRACES:
						$this->handle_brace_begin();
						break;

					case T_STRING:
						$this->handle_t_string($token);
						break;

					case T_VARIABLE:
/* dbg */ $variable_token_occurred = true;
						/*$this->handle_variable_ref($token);*/
						break;

					/*case T_NEW:
						$this->assert_fail($token);
						break;*/

					case T_RETURN:
						$this->handle_return();
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
/* dbg */ if ($variable_token_occurred)
/* dbg */ {
/* dbg */ 	$variable_token_occurred = false;
/* dbg */ }
/* dbg */ else
/* dbg */ {
						$this->handle_brace_end();
/* dbg */ }
						break;
				}
			}
		}

		return $this->data;
	}

	function get($ws = false)
	{
		$this->bm_cnt++;
		$this->bm2_cnt++;
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
		// short dbg bt
		//.echo $this->_dbg_backtrace()." <br>";;
		return $ret;
	}

	function back()
	{
		$this->bm_cnt--;
		$this->bm2_cnt--;
		prev($this->tokens);
		list(, $tmp) = each($this->tokens);
		if (is_array($tmp))
		{
			$this->cur_line -= substr_count($tmp[1], "\n");
		}
		else
		{
			$this->cur_line -= substr_count($tmp, "\n");
		}
		prev($this->tokens);
	}

	function save_bm()
	{
		$this->bm_cnt = 0;
	}

	function restore_bm()
	{
		while ($this->bm_cnt > 0)
		{
			$this->back();
		}
	}

	function save_bm2()
	{
		$this->bm2_cnt = 0;
	}

	function restore_bm2()
	{
		while ($this->bm2_cnt > 0)
		{
			$this->back();
		}
	}

	function get_line()
	{
		return $this->cur_line;
	}

	function assert($tok, $id)
	{
		if (!is_array($tok))
		{
			die("assert failed in file ".$this->cur_file." on line ".$this->get_line()." token = ".dbg::dump($tok)." expect = complex token with type ".token_name($id).dbg::process_backtrace(debug_backtrace(),-1));
		}

		if ($tok[0] != $id)
		{
			die("assert failed in file ".$this->cur_file."on line ".$this->get_line()." token = {id => ".token_name($tok[0]).", str = ".$tok[1]." } expected ".token_name($id).dbg::process_backtrace(debug_backtrace(),-1));
		}
		//$this->dump_context();
	}

	function assert_fail($tok)
	{
		echo "assert_fail in file ".$this->cur_file." on token: ";
		$this->dump_tok($tok);
		echo dbg::process_backtrace(debug_backtrace(), -1);
		die();
	}

	function assert_str($tok, $str)
	{
		if ($tok != $str)
		{
			echo("assert_str failed in file ".$this->cur_file." on line ".$this->get_line()." token = ".$this->dump_tok($tok)." expect = $str ");
			echo dbg::process_backtrace(debug_backtrace(), -1);
			die();
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
		$this->data["classes"][$name]["functions"] = array();

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
		else
		{
			if ($tok == "{") // class doesn't extend anything
			{
				$this->handle_brace_begin();
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
		//echo "brace begin, level = ".$this->brace_level." <br>";
	}

	function handle_brace_end()
	{
		//echo "brace end, level = ".$this->brace_level." <br>";
		$this->brace_level--;
		if ($this->in_class && $this->brace_level == $this->class_start_level)
		{
			//echo "cur level == class start level (".$this->class_start_level.") so end class <br>";
			$this->handle_class_end();
		}

		if ($this->in_function && $this->brace_level == $this->function_start_level)
		{
			//echo "cur level == func start level (".$this->function_start_level.") so end func<br>";
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

		$this->var_track_func_scope = array();

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
		$this->data["classes"][$this->current_class]["functions"][$this->current_function]["tracked_vars"] = $this->var_track_glob_scope;
		$this->data["classes"][$this->current_class]["functions"][$this->current_function]["end_line"] = $this->get_line();
		$this->in_function = false;
		$this->var_track_func_scope = array();
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
					die(sprintf(t("error: do_parse_parameters failed for string %s <br>\n"), $_pm));
				}
				$data["params"][$pdat["name"]] = $pdat;
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
					$data['params'][$pdat['name']]['comment'] .= "\n".$line;
				}
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
			else
			if (substr($line, 0, strlen('@errors')) == '@errors')
			{
				$data['errors'] = trim(substr($line, strlen('@errors')));
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
					$data["errors"] .= "\n".$line;
				}
				$data["errors"] = trim($data["errors"]);
	
			}
			else
			if (substr($line, 0, strlen('@examples')) == '@examples')
			{
				$data['examples'] = trim(substr($line, strlen('@examples')));
				// now loop, until we find the end or next parameter
				while (list(, $line) = each($lines))
				{
				// it removes the indenting too :(
				//	$line = trim($line);
					// trim() lines only when there are certain substrings present, 
					// can't trim every line here, cause i lose indentation then too :(
					if ( (strpos($line, '@') !== false) || (strpos($line, '**/') !== false) )
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
					}	

		
					$data["examples"] .= "\n".$line;
				}
			
				$data["examples"] = trim($data["examples"]);
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
		$depd = false;
		if ($tok[1] == "get_instance" || $tok[1] == "classload")
		{
			// follows:
			// (
			// encapsed_string class name || defined constant || variable
			// )
			// ;
			$o = $this->get();
			$this->assert_str($o, "(");

			do {
				// variable dependency?
				$is_var = false;

				$cln = $this->get();
				//echo "cln: <br>";
				//$this->dump_tok($cln, false);
				switch($cln[0])
				{
					case T_CONSTANT_ENCAPSED_STRING:
						//echo "constexp <br>";
						// the string is the class name, remove quotes and voila
						$class = str_replace("\"","", str_replace("'","",$cln[1]));
						$_tok = $this->get();
						//$this->dump_tok($_tok, false);
						if ($_tok == ",")
						{
							$this->back();
						}
						else
						if ($_tok != ")")
						{
							// something else, mark as vraiable dependency right now
							$is_var = true;
							$class = $cln[1].$this->do_read_funcall(false);
						}
						else
						{
							$this->back();
						}
						//echo "got class as $class <br>";
						break;

					case T_STRING:
						// the string is the CL_ define
						$class = $this->classinfo[constant($cln[1])]["file"];
						//echo "from tstring got class $class <br>";
						break;

					case T_VARIABLE:
						// the string is in the variable, mark as depend on all
						$class = "variable dependency!";
						$is_var = true;
						$class = $cln[1].$this->do_read_funcall(false);
						//echo "from t_var got class $class <br>";
						break;

					case T_ARRAY:
						$class = "";
						$this->read_const_array_def();
						break;

					case "\"":
						// string containing variables, read it
						$class = "variable dependency! ";
						$is_var = true;
						$class .= $this->do_read_const_string();
						//echo "from quote got class $class <br>";
						break;

					default:
						$this->assert_fail($cln);
						break;
				}

				if ($this->current_class && $class != "")
				{
					$depd = array(
						"dep_via" => $tok[1],
						"function" => $this->current_function,
						"line" => $this->get_line(),
						"dep" => $class,
						"is_var" => $is_var
					);
					$this->data["classes"][$this->current_class]["dependencies"][] = $depd;
				}

				$o = $this->get();
				//echo "outlooper tok: <br>";
				//$this->dump_tok($o, false);
			} while($o == ",");

			$o = $this->get();
			//$this->assert_str($o, ";");
		}
		return $depd;
	}

	/** skips function call arguments, returns content
	**/
	function do_read_funcall($restore = true)
	{
		// check if the first tok is (, then skip that
		$tmp = $this->get();
		if (!(!is_array($tmp) && $tmp == "("))
		{
			$this->back();
		}

		/*$tmp = $this->get();
		$this->back();
		echo "in do_read fcall start token is: ";
		$this->dump_tok($tmp, false);*/

		// save the position on the stack, read the string then rewind wo that we get to process
		// the arguments as well, cause those might be funcalls or whatever as well.
		if ($restore)
		{
			$this->save_bm();
		}

		$cnt = 1;
		$tcnt = 0;
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
			$tcnt ++;
		} while ($cnt > 0 && $tcnt < 100000);
		if ($tcnt >= 100000)
		{
			$this->assert_fail();
		}
		$this->back();

		if ($restore)
		{
			$this->restore_bm();
		}
		/*$tmp = $this->get();
		echo "after do_read fcall end token is: ";
		$this->dump_tok($tmp, false);
		$this->back();*/

		return $class;
	}

	function handle_variable_ref($v_tok)
	{
		$nxt = $this->get();
		/*echo "handle var ref on line ".$this->get_line()." =<br>";
		$this->dump_tok($v_tok, false);
		$this->dump_tok($nxt, false);*/
		if (!is_array($nxt) && $nxt == "=")
		{
			$this->handle_variable_assign($v_tok, $nxt);
		}
		else
		if (is_array($nxt) && $nxt[0] == T_OBJECT_OPERATOR)
		{
			// check if it is a funcall or object variable ref
			$osnm = $this->get();
			$ttt = $this->get();
			/*if (!is_array($ttt) && $ttt == "=")
			{
				$this->back();
				$this->handle_variable_ref(array($v_tok[0], $v_tok[1]."->".$osnm[1]));
				return;
			}*/

			if (!is_array($ttt) && $ttt == "(")
			{
				if ($v_tok[1] == "\$this")
				{
					// funcall via $this->bla, register local call in func table
					$calld = array(
						"func" => $osnm[1],
						"line" => $this->get_line(),
						"arguments" => $this->do_read_funcall()
					);
					$this->data["classes"][$this->current_class]["functions"][$this->current_function]["local_calls"][] = $calld;
				}
				else
				{
					// it's a funcall, get the variable type from var track and mark the func as called
					$var = $this->get_track_var($v_tok[1]);
					if ($var)
					{
						$calld = array(
							"class" => $var["class"],
							"func" => $osnm[1],
							"line" => $this->get_line(),
							"arguments" => $this->do_read_funcall()
						);
						$this->data["classes"][$this->current_class]["functions"][$this->current_function]["foreign_calls"][] = $calld;
					}
					else
					{
						// object call via untracked variable
						// try to resolve with text match
						//echo "no var track for $v_tok[1] on line ".$this->get_line().", trying to resolve<br>";
						if (!aw_global_get("no_db_connection"))
						{
							$resolv = array();
							$this->db_query("SELECT * FROM aw_da_funcs WHERE func = '$osnm[1]'");
							while ($row = $this->db_next())
							{
								if ($row["class"] == "_int_object")
								{
									$row["class"] = "object";
								}
								$resolv[$row["class"]] = $row["class"];
								//echo ".. found ".$row["class"]."::".$row["func"]." <br>";
							}
							if (count($resolv) == 1)
							{
								// found unique, use this
								reset($resolv);
								$calld = array(
									"class" => reset($resolv),
									"func" => $osnm[1],
									"line" => $this->get_line(),
									"arguments" => $this->do_read_funcall()
								);
								$this->data["classes"][$this->current_class]["functions"][$this->current_function]["foreign_calls"][] = $calld;
								// also, mark the object as a tracked var!
								$this->add_track_var($v_tok[1],array(
									"type" => "class",
									"class" => reset($resolv),
									"assigned_at" => $this->get_line(),
								));
								//echo "added to var track, var $v_tok[1] as value ".reset($resolv)." <br>";
							}
							else
							{
								//echo "unresolved $v_tok[1] -> $osnm[1] on line ".$this->get_line().". baad wookie!! <br>";
							}
						}
					}
				}
			}
		}
	}

	function get_track_var($varname)
	{
		// if in func, check local scope
		if ($this->in_function)
		{
			/*if ($this->current_function == "_req_add_itypes")
			{
				echo "read track var $varname => ".dbg::dump($this->var_track_func_scope[$varname])." <br>";
			}*/
			return $this->var_track_func_scope[$varname];
		}
		return $this->var_track_glob_scope[$varname];
	}

	function add_track_var($varn, $vard)
	{
		if ($this->in_function)
		{
			/*if ($this->current_function == "_req_add_itypes")
			{
				echo "added track var $varn => ".dbg::dump($vard)." <br>";
			}*/
			$this->var_track_func_scope[$varn] = $vard;
		}
		$this->var_track_glob_scope[$varn] = $vard;
	}

	function _try_parse_complex_expr()
	{
		// we are insode a complex expression. try to figure out if it is constant
		// by type (since currently we only get here from finding the isset token
		// on variable assign, it probably is an array.
		// TODO: right. currently, just return unknown type
		return array(
			"type" => "other",
			"last_assign" => $this->get_line(),
			"last_value" => "unknown",
		);
	}

	function do_read_const_string()
	{
		// skip fist " if not yet skipped
		$tmp = $this->get();
		if (!(!is_array($tmp) && $tmp == "\""))
		{
			$this->back();
		}

		// a simple count of "'s from here
		$cnt = 1;
		$tmp = 0;
		while ($cnt > 0)
		{
			$tok = $this->get();
			if ($tok == "\"")
			{
				$cnt --;
			}

			$tmp++;
			if ($tmp > 1000)
			{
				die(t("horrible parse error!"));
			}
		}
	}

	function _dbg_backtrace()
	{
		$msg = "";
		if (function_exists("debug_backtrace"))
		{
			$bt = debug_backtrace();
			for ($i = count($bt)-1; $i > 0; $i--)
			{
				if ($bt[$i+1]["class"] != "")
				{
					$fnm = $bt[$i+1]["class"]."::".$bt[$i+1]["function"];
				}
				else
				if ($bt[$i+1]["function"] != "")
				{
					if ($bt[$i+1]["function"] != "include")
					{
						$fnm = $bt[$i+1]["function"];
					}
					else
					{
						$fnm = "";
					}
				}
				else
				{
					$fnm = "";
				}

				$msg .= $fnm.":".$bt[$i]["line"]."->";

				/*if ($bt[$i]["class"] != "")
				{
					$fnm2 = $bt[$i]["class"]."::".$bt[$i]["function"];
				}
				else
				if ($bt[$i]["function"] != "")
				{
					$fnm2 = $bt[$i]["function"];
				}
				else
				{
					$fnm2 = "";
				}

				$msg .= $fnm2;*/
			}
		}

		return $msg;
	}

	function handle_variable_assign($var_tok, $eq_tok)
	{
		// get the expression until end of statement (;)
		// and give it to a generic expression parser that tries to deduct the return type

		// right now just check for get_instance as the next token
		$tok = $this->get();
		if ($tok[0] == T_STRING && $tok[1] == "get_instance")
		{
			$opb = $this->get();
			$this->assert_str($opb, "(");

			$git = $this->get();
			if ($git[0] == T_CONSTANT_ENCAPSED_STRING)
			{
				$this->assert($git, T_CONSTANT_ENCAPSED_STRING);

				// this is the class name
				$this->add_track_var($var_tok[1],array(
					"type" => "class",
					"class" => str_replace("\"", "", str_replace("'", "", $git[1])),
					"assigned_at" => $this->get_line()
				));
				//echo "on line ".$this->get_line()." added var $var_tok[1] as type class, class = $git[1] <br>";
			}
		}
		else
		if ($tok[0] == T_STRING && $tok[1] == "obj")
		{
			$this->add_track_var($var_tok[1],array(
				"type" => "class",
				"class" => "object",
				"assigned_at" => $this->get_line(),
				"parameters" => $this->do_read_funcall()
			));
		}
		else
		if ($tok[0] == T_NEW)
		{
			$clsnm = $this->get();
			if ($clsnm[0] == T_STRING)	// could be T_VARIABLE
			{
				$obr = $this->get();
				$this->back();
				$parm = "";
				if ($obr == "(")
				{
					$parm = $this->do_read_funcall();
				}
				$this->add_track_var($var_tok[1],array(
					"type" => "class",
					"class" => $clsnm[1],
					"assigned_at" => $this->get_line(),
					"parameters" => $parm
				));
			}
		}
		else
		if ($tok[0] == T_VARIABLE)
		{
			$this->save_bm2();
			//echo "dmn, in var assign got asigned val from as variable, $tok[1] <br>";
			//$this->dump_tok($tok, false);
			$this->handle_variable_ref($tok);
			$this->restore_bm2();

			$this->back();
			$this->save_bm();

			$this->get();
			$nxt = $this->get();
			//$this->dump_tok($nxt, false);
			if ($nxt[0] == T_OBJECT_OPERATOR)
			{
				$vn = $this->get();
				$obr = $this->get();
				if ($obr == "(")
				{
					// we got assign to variable with value from object. this could ba a funcall that we know the return type of.

					// get from track
					$vard = $this->get_track_var($tok[1]);
					if ($vard)
					{
						// now we know the class::function , check if we know the return type
						$q = "SELECT ret_class FROM aw_da_funcs WHERE class = '".$vard["class"]."' AND func = '".$vn[1]."'";
						//echo "q = $q <br>";
						if (!aw_global_get("no_db_connection"))
						{
							$ret_class = $this->db_fetch_field($q, "ret_class");
						}

						if ($ret_class != "")
						{
							/*echo "got string tok $tok[1] on assign in line ".$this->get_line()." <br>";
							$this->dump_tok($tok,false);
							$this->dump_tok($nxt,false);
							$this->dump_tok($vn,false);
							$this->dump_tok($obr,false);
							echo dbg::dump($vard);
							echo "and finally, ret class = $ret_class <br>";
							echo "------ <Br>";*/
							$this->add_track_var($var_tok[1],array(
								"type" => "class",
								"class" => $ret_class,
								"assigned_at" => $this->get_line(),
							));
						}
					}
				}
			}

			$this->restore_bm();
		}
	}

	function handle_return()
	{
		// check if a variable is returned
		// if so, check if it is tracked
		// if also true, store the type of the returned variable
		// so that it can be used later for variable type guesses
		$return = $this->get();
		if (is_array($return) && $return[0] == T_VARIABLE)
		{
			$vard = $this->get_track_var($return[1]);
			if ($vard)
			{
				//echo "for var $return[1] on line ".$this->get_line()." got track var ".dbg::dump($vard)." <br>";
				$this->data["classes"][$this->current_class]["functions"][$this->current_function]["return_var"] = $vard;
			}
		}
		else
		{
			if (is_array($return) && $return[0] == T_STRING && $return[1] == "obj")
			{
				$this->data["classes"][$this->current_class]["functions"][$this->current_function]["return_var"] = array(
					"class" => "object"
				);
				//echo "on line ".$this->get_line()." set return type as object! <br>";
			}
			else
			if (is_array($return) && $return[0] == T_STRING && $return[1] == "get_instance")
			{
				$cln = $this->get();
				$this->assert_str($cln,"(");
				$cln = $this->get();
				if ($cln[0] == T_CONSTANT_ENCAPSED_STRING)
				{
					$this->data["classes"][$this->current_class]["functions"][$this->current_function]["return_var"] = array(
						"class" => str_replace("\"","", str_replace("'","",basename($cln[1])))
					);
					//echo "on line ".$this->get_line()." set return type as $cln[1]! <br>";
				}
			}
			else
			{
				$this->back();
				//$this->dump_tok($return, false);
			}
		}
	}
	
}
?>
