<?php
// $Header: /home/cvs/automatweb_dev/classes/aw_template.aw,v 2.75 2006/02/20 08:56:30 kristo Exp $
// aw_template.aw - Templatemootor


classload("core");

class aw_template extends core
{
	function init($args = array())
	{
		parent::init($args);
		if (is_array($args))
		{
			if (method_exists($this, "tpl_init"))
			{
				$this->tpl_init(isset($args["tpldir"]) ? $args["tpldir"] : "");
			}
		}
		else
		{
			$this->tpl_init($args);
		}
		$this->debug_mode = aw_ini_get("debug_mode");
	}

	function tpl_init($basedir = "", $has_top_level_folder = false)
	{
		if (!isset($this->cfg) || !is_array($this->cfg))
		{
			aw_config_init_class(&$this);
		}

		$this->REQUEST_URI = aw_global_get("REQUEST_URI");

		if (substr($basedir,0,1) != "/" && !preg_match("/^[a-z]:/i", substr($basedir,0,2)))
		{
			if ($has_top_level_folder)
			{
				$this->template_dir = $this->cfg["site_basedir"] . "/$basedir";
				$this->adm_template_dir = $this->cfg["basedir"] . "/templates/$basedir";
				$this->site_template_dir = $this->cfg["site_basedir"]."/".$basedir;
			}
			else
			{
				$this->template_dir = $this->cfg["tpldir"] . "/$basedir";
				$this->adm_template_dir = $this->cfg["basedir"] . "/templates/$basedir";
				$this->site_template_dir = $this->cfg["site_tpldir"]."/".$basedir;
			}
		}
		else
		{
			$this->template_dir = $basedir;
			$this->adm_template_dir = $basedir;
			$this->site_template_dir = $basedir;
		}
		
		$this->vars = array();
		$this->sub_merge = 0;

		$this->_init_vars();

		$this->use_eval = false;
	}

	function _init_vars()
	{
		// this comes from session.
		$this->vars = array(
			"self" => aw_global_get("PHP_SELF"),
			"ext"  => $this->cfg["ext"],
			// not very random really
			"rand" => time(),
			"current_time" => time(),
			"status_msg" => aw_global_get("status_msg"),
			"baseurl" => $this->cfg["baseurl"],
			"cur_lang_id" => aw_global_get("lang_id")
		);
	}

	/** sets the parse method for templates - "" or "eval"

		@attrib api=1
	**/
	function set_parse_method($method = "")
	{
		if ($method == "eval")
		{
			$this->use_eval = true;
		};
	}

	/** resets all templates and variables
		@attrib api=1
	**/
	function reset()
	{
		return $this->tpl_reset();
	}

	////
	// !resets all templates and variables
	function tpl_reset()
	{
		unset($this->templates);
		$this->v2_templates = array();
		$this->v2_name_map = array();
		$this->v2_parent_map = array();
		//$this->_init_vars();
	}

	////
	// !Deprecated - use html::select instead
	function option_list($active,$array)
	{
		$res = "";
		if (is_array($array))
		{
			while(list($k,$v) = each($array))
			{
				$selected = ($active == $k) ? " selected " : "";
				$res .= sprintf("<option %s value='%s'>%s</option>\n",$selected,$k,$v);
			};
		};
		return $res;
//		return html::select(array("selected" => $active,"options" => $array));
	}

	////
	// !Deprecated - use html::select instead
	function multiple_option_list($active,$array)
	{
		$res = "";
		if (not(is_array($array)))
		{
			return false;
		};

		if (is_array($active))
		{
			$active = array_flip($active);
		};

		while(list($k,$v) = each($array))
		{
			$selected = isset($active[$k]) ? " selected " : "";
			$res .= sprintf("<option %s value='%s'>%s</option>\n",$selected,$k,$v);
		};
		return $res;
//		return html::select(array("selected" => $active,"options" => $array,"multiple" => 1));
	}
        
	////
	// !Deprecated - use html::select instead
	function mpicker($active, $array)
	{
		return $this->multiple_option_list($active, $array);
	}

	////
	// !Deprecated - use html::select instead
	function picker($active,$array)
	{
		return $this->option_list($active,$array);
	}

	/** reads a template from a file
		@attrib api=1

	**/
	function read_template($name,$silent = 0)
	{
		$this->template_filename = $this->template_dir."/".$name;
		if (!file_exists($this->template_filename))
		{
			$this->template_filename = $this->adm_template_dir . "/" . $name;			
		};

		// try to load a template from aw directory then
		if (file_exists($this->template_filename))
		{
			$retval = $this->read_tpl(file($this->template_filename));
		}
		else
		{
			if ($silent)
			{
				$retval = false;
			}
			else
			{
				// raise_error drops out, therefore $retval has no meaning here
				$this->raise_error(ERR_TPL_NOTPL,sprintf(t("Template '%s/%s' not found"), $this->template_dir, $name),true);
			};
		}
		return $retval;
	}

	/** reads a template from the given string
		@attrib api=1
	**/
	function use_template($source)
	{
		$slines = explode("\n",$source);
		return $this->read_tpl($slines);
	}
	
	/** reads a template from a file from the admin template folder
		@attrib api=1
	**/
	function read_adm_template($name,$silent = 0)
	{
		$retval = true;
		$this->template_filename = $this->adm_template_dir."/".$name;
		if (file_exists($this->template_filename))
		{
			$retval = $this->read_tpl(file($this->template_filename));
		}
		else
		{
			if ($silent)
			{
				$retval = false;
			}
			else
			{
				// raise_error drops out, therefore $retval has no meaning here
				$this->raise_error(ERR_TPL_NOTPL,sprintf(t("Template '%s' not found"), $this->template_filename),true);
			};
		}
		return $retval;
	}

	/**  reads the template from the site folder, even if we are in the admin interface
		@attrib api=1
	**/
	function read_site_template($name,$silent = 0)
	{
		$retval = true;
		$this->template_filename = $this->site_template_dir."/".$name;
		if (file_exists($this->template_filename))
		{
			$retval = $this->read_tpl(file($this->template_filename));
		}
		else
		{
			if ($silent)
			{
				$retval = false;
			}
			else
			{
				// raise_error drops out, therefore $retval has no meaning here
				$this->raise_error(ERR_TPL_NOTPL,sprintf(t("Template '%s' not found"), $this->template_filename),true);
			};
		}
		return $retval;
	}

	/** reads template from site folder and if not found there, admin folder
		@attrib api=1
	**/
	function read_any_template($name, $silent = false)
	{
		$this->template_filename = $this->site_template_dir."/".$name;
		$this->template_filename = trim($this->template_filename);
		if (file_exists($this->template_filename))
		{
			$retval = $this->read_tpl(file($this->template_filename));
		}
		else
		{
			$this->template_filename = $this->adm_template_dir."/".$name;
			if (file_exists($this->template_filename))
			{
				$retval = $this->read_tpl(file($this->template_filename));
			}
			else
			{
				if ($silent)
				{
					$retval = false;
				}
				else
				{
					// raise_error drops out, therefore $retval has no meaning here
					$this->raise_error(ERR_TPL_NOTPL,sprintf(t("Template '%s' not found in admin or site folder"), $this->template_filename),true);
				};
			}
		}
		return $retval;
	}

	/** checks if a SUB with the name given exits in the currently loaded template
		@attrib api=1
	**/
	function is_template($name)
	{
		$retval = isset($this->v2_name_map[$name]);
		return $retval;
	}

	/** Checks whether a template contains a variable placeholder or not
		@attrib api=1
	**/
	function template_has_var($varname,$tplname = "MAIN")
	{
		return strpos($this->v2_templates[$tplname],"{VAR:" . $varname . "}") !== false; 
	}

	/** checks if the template contains the given variable. checks the complete template. slow
		@attrib api=1
	**/
	function template_has_var_full($varname)
	{
		static $tmp = "";
		if (empty($tmp))
		{
			$tmp = join("\n", $this->v2_arr);
		};
		return strpos($tmp,"{VAR:" . $varname . "}") !== false; 
	}

	/** checks if the SUB $parent is a parent of the SUB $tpl 
		@attrib api=1
	**/
	function is_parent_tpl($tpl,$parent)
	{
		if (!isset($this->v2_parent_map[$tpl]))
		{
			return "" == $parent;
		}
		else
		{
			return $this->v2_parent_map[$tpl] == $parent;
		}
	}

	/** returns the name if the immediate parent SUB of the given SUB
		@attrib api=1
	**/
	function get_parent_template($tpl)
	{
		return $this->v2_parent_map[$tpl];
	}

	/** returns an array of parent SUB names for the given SUB
		@attrib api=1
	**/
	function get_parent_templates($tpl)
	{
		$ret = array();
		foreach($this->v2_templates as $fqname => $tt)
		{
			// if fqname contains the needed template,
			// get the parent
			$parts = explode(".", $fqname);
			foreach($parts as $idx => $part)
			{
				if ($part == $tpl)
				{
					$ret[] = $parts[$idx-1];
					break;
				}
			}
		}
		return $ret;
	}

	/** checks if the sub TPL is a child SUB of the $parent SUB. checks the full chain pf SUB's
		@attrib api=1
	**/
	function is_in_parents_tpl($tpl, $parent)
	{
		$fp = $this->v2_name_map[$tpl];
		if (strpos($fp, $parent) === false)
		{
			return false;
		}
		return true;
	}

	/** imports variables into the current template, overwriting the previous variables of the same name
		@attrib api=1
	**/
	function vars($params)
	{
		$this->vars = array_merge($this->vars,$params);
	}

	/** imports variables into the current template, appending to the previous variables of the same name
		@attrib api=1
	**/
	function vars_merge($params)
	{
		while(list($k,$v) = each($params))
		{
			$this->vars[$k] .= $v;
		}
	}

	/** replaces variables with their values and returns the content of the given sub as parsed text
		@attrib api=1
	**/
	function parse($object = "MAIN") 
	{
		$tmp = isset($this->v2_name_map[$object]) ? $this->v2_name_map[$object] : "";
		$val = isset($this->v2_templates[$tmp]) ? $this->v2_templates[$tmp] : ""; 
		if ($this->use_eval)
		{
			$cval = $this->c_templates[$tmp];
			$vars = $this->vars;
			eval("\$src=\"" . $cval . "\";");
		}
		else
		{
			$src = localparse($val, $this->vars);
		};
		// võtame selle maha ka .. this is NOT a good place for that
		//aw_session_del("status_msg", true);

		if ($this->sub_merge == 1)
		{
			if (!isset($this->vars[$object]))
			{
				$this->vars[$object] = "";
			}
	   		$this->vars[$object] .= $src;
		}
		if ($this->debug_mode == 1 && isset($_GET["TPL"]) && $_GET["TPL"] == 2 && $object == "MAIN")
		{
			print "Available variables for: " . $this->template_filename;
			print "<pre>";
			print_r($this->vars);
			print "</pre>";
		};
		return $src;
	}

	////
	// !$arr - template content, array of lines of text
	function read_tpl($arr)
	{
		if ($this->debug_mode != 0 && isset($_GET["TPL"]) && $_GET["TPL"] == 1)
		{
			print "using " . $this->template_filename . "<br />";
		};
		$this->tpl_reset();
		if (is_array($arr))
		{
			reset($arr);
			$this->v2_arr = $arr;
			$this->req_read_tpl("MAIN","MAIN","");
		}
		return true;
	}

	function req_read_tpl($fq_name,$cur_name,$parent_name)
	{
		$cur_src = "";
		$this->v2_parent_map[$cur_name] = $parent_name;
		while (list(,$line) = each($this->v2_arr))
		{
			// this check allows us to avoid a LOT of preg_match calls,
			// those are probably expensive. I don't care what the profiler
			// says, just think about how a regexp engine works. Simple
			// string comparing is ALWAYS faster. --duke
			if (strpos($line,"<!--") === false)
			{
				$cur_src.=$line;
			}
			else
			if (preg_match("/<!-- SUB: (.*) -->/",$line, $mt))
			{
				// start new subtemplate
				$this->req_read_tpl($fq_name.".".$mt[1],$mt[1],$cur_name);
				// add the var def for this sub to this template
				$cur_src.="{VAR:".$mt[1]."}";
			}
			else
			if (preg_match("/^(.*)<!-- END SUB: (.*) -->/",$line, $mt))
			{
				/* This avoid obligatory newline after each block, making templates more flexible.. 
				/use eg 
				|<!-- SUB: file-->
				|{VAR:filecontents}
				|EOF-with-no-linebreak<!-- END SUB: file -->
				*/
				$cur_src .= $mt[1];
				// found an end of this subtemplate, 
				// finish and exit
				$this->v2_templates[$fq_name] = $cur_src;
				if ($this->use_eval)
				{
					$xsrc = str_replace("\"","\\\"",$cur_src);
					$this->c_templates[$fq_name] = preg_replace("/{VAR:(.+?)}/","\".\$vars[\$1].\"",$xsrc);
				};

				$this->templates[$cur_name] = $cur_src;	// ugh, this line for aliasmanager and image_inplace compatibility :(
				$this->v2_name_map[$cur_name] = $fq_name;
				$this->v2_name_map[$parent_name.".".$cur_name] = $fq_name;
				$this->v2_name_map[$fq_name] = $fq_name;
				return;
			}
			else
			{
				// just add this line
				$cur_src.=$line;
			}
		}
		$this->v2_templates[$fq_name] = $cur_src;
		if ($this->use_eval)
		{
			$xsrc = str_replace("\"","\\\"",$cur_src);
			$this->c_templates[$fq_name] = preg_replace("/{VAR:(.+?)}/","\".\$vars[\$1].\"",$xsrc);
		};

		$this->templates[$cur_name] = $cur_src;	// ugh, this line for aliasmanager and image_inplace compatibility :(
		$this->v2_name_map[$cur_name] = $fq_name;
		$this->v2_name_map[$fq_name] = $fq_name;
		return;
	}

	/** Retrieves a list of SUB's matching a regexp
		@attrib api=1
	**/
	function get_subtemplates_regex($regex)
	{
		$tpls = array_keys($this->v2_name_map);
		$res = array();
		foreach($tpls as $key)
		{
			if (preg_match("/^$regex/",$key,$matches))
			{
				$res[] = $matches[1];
			};
		};
		return array_unique($res);
	}

	/** returns the un-parsed content of the given SUB 
		@attrib api=1
	**/
	function get_template_string($name)
	{
		$tmp = isset($this->v2_name_map[$name]) ? $this->v2_name_map[$name] : "";
		return isset($this->v2_templates[$tmp]) ? $this->v2_templates[$tmp] : ""; 
	}
};
classload("class_base","html");

?>
