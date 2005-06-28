<?php

// tokens for the script interpreter
define("OSI_TOK_VAR_ASSIGN", 1);	// params { $name }
define("OSI_TOK_CREATE_INI", 2);	// params { }
define("OSI_TOK_CREATE_OBJ", 3);	// params { }
define("OSI_TOK_SETTING", 4);	// params { $name,$value }
define("OSI_TOK_CMD_END", 5);		// params { }  - ends line exec
define("OSI_TOK_CREATE_REL", 6); 

class object_script_interpreter extends class_base
{

	function object_script_interpreter()
	{
		$this->init();
	}

	/** executes the script given in the file $file

		@attrib name=exec_file
	
		@param file required

	**/
	function exec_file($arr)
	{
		extract($arr);
		$sc = $this->get_file(array("file" => $file));
		error::raise_if($sc === false, array(
			"id" => ERR_NO_FILE,
			"msg" => sprintf(t("object_script_interpreter::exec_file(%s): file does not exist!"), $file)
		));

		return $this->exec(array(
			"script" => $sc,
			"vars" => $arr["vars"]
		));
	}

	/** executes the script given in $script

		@param script required
		@param vars optional

	**/
	function exec($arr)
	{
		$this->_init_sym_table($arr["vars"]);

		$this->lineno = 0;

		$lines = explode("\n", $arr["script"]);
		foreach($lines as $line)
		{
			$nl = trim($line);
			if ($nl{0} == "#")
			{
				continue;
			}

			if ($nl != "")
			{
				$this->_syntax_check_line($nl);
			}
		}

		foreach($lines as $line)
		{
			$nl = trim($line);
			if ($nl{0} == "#")
			{
				continue;
			}
			if ($nl != "")
			{
				$this->_exec_line($nl);
			}
		}

		return array(
			"created_objs" => $this->_get_created_objs(),
			"ini_settings" => $this->_get_ini_settings(),
			"vars" => $this->_get_sym_table()
		);
	}

	function _init_sym_table($vars)
	{
		$this->created_objs = array();
		$this->ini_settings = array();
		$this->sym_table =array();

		$awa = new aw_array($vars);
		foreach($awa->get() as $k => $v)
		{
			$this->sym_table[$k] = $v;
		}
	}

	function _get_sym_table()
	{
		return $this->sym_table;
	}

	function _get_created_objs()
	{
		return $this->created_objs;
	}

	function _get_ini_settings()
	{
		if (!is_array($this->ini_settings))
		{
			return array();
		}
		return $this->ini_settings;
	}

	function _get_sym($n)
	{
		error::raise_if(!isset($this->sym_table[$n]), array(
			"id" => ERR_OSI_NO_VAR,
			"msg" => sprintf(t("object_script_interpreter::_get_sym(%s): no variable by the name %s defined on line %s!"), $n, $n, $this->lineno)
		));
		return $this->sym_table[$n];
	}

	function _replace_syms($line)
	{
		$this->lineno++;
		return preg_replace('/\$\{(.*)\}/Ue',"\"\\\"\".\$this->_get_sym(\"\\1\").\"\\\"\"",$line);
	}

	function _tokenize_line($line)
	{
		$toks = array();

		// now, check for var assign
		if ($line{0} == "$")
		{
			$sppos = min(strpos($line, "="), strpos($line, " "));

			$toks[] = array(
				"tok" => OSI_TOK_VAR_ASSIGN,
				"params" => array(
					"name" => trim(substr($line, 1, $sppos-1))
				)
			);

			$line = trim(substr($line, $sppos));
			if ($line{0} == "=")
			{
				$line = trim(substr($line, 1));
			}
		}

		// now, check for obj/ini
		if (substr($line, 0, 3) == "obj")
		{
			$toks[] = array(
				"tok" => OSI_TOK_CREATE_OBJ,
				"params" => array()
			);
		}
		else
		if (substr($line, 0, 3) == "ini")
		{
			$toks[] = array(
				"tok" => OSI_TOK_CREATE_INI,
				"params" => array()
			);
		}
		else
		if (substr($line, 0, 3) == "rel")
		{
			$toks[] = array(
				"tok" => OSI_TOK_CREATE_REL,
				"params" => array()
			);
		}
		else
		{
			error::raise(array(
				"id" => ERR_OSI_PARSE,
				"msg" => sprintf(t("object_script_interpreter::_tokenize_line(%s): parse error - unrecognized command on line %s!"), $line, $this->lineno)
			));
		}

		$line = trim(substr($line, 3));

		error::raise_if($line{0} != "{", array(
			"id" => ERR_OSI_PARSE,
			"msg" => sprintf(t("object_script_interpreter::_tokenize_line(%s): parse error no opening brace after command on line %s!"), $line, $this->lineno)
		));

		error::raise_if($line{strlen($line)-1} != "}", array(
			"id" => ERR_OSI_PARSE,
			"msg" => sprintf(t("object_script_interpreter::_tokenize_line(%s): parse error no closing brace on line %s!"), $line, $this->lineno)
		));

		$line = trim(substr(substr($line,0,-1), 1));

		// now we gots to parse the opts
		$len = strlen($line);

		// read name=value 
		$cnt = 0;

		while ($cnt < $len)
		{
			while ($line{$cnt} == " " || $line{$cnt} == "=" || $line{$cnt} == "\t")
			{
				$cnt++;
			}

			$o_n = "";
			while($line{$cnt} != "=" && $cnt < $len)
			{
				$o_n .= $line{$cnt};
				$cnt++;
			}

			while ($line{$cnt} == " " || $line{$cnt} == "=" || $line{$cnt} == "\t")
			{
				$cnt++;
			}

			$o_v = "";
			if ($line{$cnt} == "\"")
			{
				$cnt++;
				// read quoted value
				while ($cnt < $len && $line{$cnt} != "\"")
				{
					$o_v .= $line{$cnt};
					$cnt++;
				}

				// and skip the final "
				$cnt++;
			}
			else
			{
				// read un quoted value
				while ($cnt < $len && $line{$cnt} != " " && $line{$cnt} != ",")
				{
					$o_v .= $line{$cnt};
					$cnt++;
				}
			}

			// skip final spaces && ,
			while ($line{$cnt} == " " || $line{$cnt} == "," || $line{$cnt} == "\t")
			{
				$cnt++;
			}

			if ($o_n{0} == "\"")
			{
				$o_n = substr($o_n, 1);
			}

			$toks[] = array(
				"tok" => OSI_TOK_SETTING,
				"params" => array(
					"name" => trim($o_n),
					"value" => trim($o_v)
				)
			);
		}

		$toks[] = array(
			"tok" => OSI_TOK_CMD_END,
			"params" => array()
		);

		return $toks;
	}


	function _exec_line($line)
	{
		$line = $this->_replace_syms($line);
		$toks = $this->_tokenize_line($line);
		echo "exec line $line <br>\n";
		flush();
		$this->_exec_toks($toks);
	}

	function _syntax_check_line($line)
	{
		$line = $this->_replace_syms($line);
		$toks = $this->_tokenize_line($line);

		$start = 0;
		if ($toks[$start]["tok"] == OSI_TOK_VAR_ASSIGN)
		{
			$start = 1;
		}

		if ($toks[$start]["tok"] == OSI_TOK_CREATE_OBJ)
		{
			$cnt = $start+1;

			if ($start == 1)
			{
				$this->sym_table[$toks[0]["params"]["name"]] = "test_value";
			}
		}
	}

	function _dbg_tok_dump($toks)
	{
		foreach($toks as $n => $t)
		{
			echo "tok $n = { $t[tok], params = ".join(",", map2("%s => %s", $t["params"]))." }<br>";
		}
	}

	function _exec_toks($toks)
	{
		$start = 0;
		if ($toks[$start]["tok"] == OSI_TOK_VAR_ASSIGN)
		{
			$start = 1;
		}

		if ($toks[$start]["tok"] == OSI_TOK_CREATE_OBJ)
		{
			$cnt = $start+1;
			$o = new object();

			// go over opts to set class id first
			while ($toks[$cnt]["tok"] != OSI_TOK_CMD_END)
			{
				if ($toks[$cnt]["params"]["name"] == "class_id")
				{
					$o->set_class_id($this->_get_value($toks[$cnt]["params"]["value"]));
				}
				$cnt++;
			}

			// now set all opts
			$cnt = $start+1;
			while ($toks[$cnt]["tok"] != OSI_TOK_CMD_END)
			{
				if (substr($toks[$cnt]["params"]["name"], 0, 5) == "meta.")
				{
					$mn = substr($toks[$cnt]["params"]["name"], 5);
					$o->set_meta($mn, $this->_get_value($toks[$cnt]["params"]["value"]));
				}
				else
				{
					switch($toks[$cnt]["params"]["name"])
					{
						case "class_id":
							break;
			
						case "parent":
							$o->set_parent($this->_get_value($toks[$cnt]["params"]["value"]));
							break;
					
						case "flags":
							$o->set_flags($this->_get_value($toks[$cnt]["params"]["value"]));
							break;
					
						default:
							$o->set_prop($toks[$cnt]["params"]["name"], $this->_get_value($toks[$cnt]["params"]["value"]));
							break;
					}
				}

				$cnt++;
			}

			$o->save();

			if ($start == 1)
			{
				$this->sym_table[$toks[0]["params"]["name"]] = $o->id();
			}
		}
		else
		if ($toks[$start]["tok"] == OSI_TOK_CREATE_INI)
		{
			$this->ini_settings[$toks[$start+1]["params"]["name"]] = $toks[$start+1]["params"]["value"];
		}
		else
		if ($toks[$start]["tok"] == OSI_TOK_CREATE_REL)
		{
			$c = new connection();
			$parm = array();
			// now set all opts
			$cnt = $start+1;
			while ($toks[$cnt]["tok"] != OSI_TOK_CMD_END)
			{
				$parm[$toks[$cnt]["params"]["name"]] = $this->_get_value($toks[$cnt]["params"]["value"]);
				$cnt++;
			}

			if (!count($parm) || !is_oid($parm["from"]) || !is_oid($parm["to"]))
			{
				error::raise(array(
					"id" => ERR_OSI_REL,
					"msg" => t("object_script_interpreter::_exec_toks(): relation must have both ends defined!")
				));
			}

			$c->change($parm);

			if ($start == 1)
			{
				$this->sym_table[$toks[0]["params"]["name"]] = $c->id();
			}

		}
	}

	function _get_value($v)
	{
		if (defined($v))
		{
			return constant($v);
		}
		return $v;
	}
}
?>