<?php
// $Header: /home/cvs/automatweb_dev/classes/aw_template.aw,v 2.42 2003/03/13 14:13:56 duke Exp $
// aw_template.aw - Templatemootor

classload("acl_base");
class aw_template extends acl_base
{
	function init($args = array())
	{
		parent::init($args);
		if (is_array($args))
		{
			$this->tpl_init(isset($args["tpldir"]) ? $args["tpldir"] : "");
		}
		else
		{
			$this->tpl_init($args);
		}
	}

	function tpl_init($basedir = "")
	{
		if (!isset($this->cfg) || !is_array($this->cfg))
		{
			aw_config_init_class(&$this);
		}


		$this->REQUEST_URI = aw_global_get("REQUEST_URI");
		$this->PHP_SELF = aw_global_get("PHP_SELF");

		$this->template_dir = $this->cfg["tpldir"] . "/$basedir";
		$this->adm_template_dir = $this->cfg["basedir"] . "/templates/$basedir";
		$this->site_template_dir = $this->cfg["site_tpldir"]."/".$basedir;
		
		// I'm trying to fix the breakage of links class
		// it does $this->extlinks() first, which loads the localizations
		// and calls tpl_init as well, and then does $this->init,
		// which in turn calls tpl_init again and makes us lose
		// all the data that came from extlinks
		if (isset($this->init_done) && $this->init_done == 1)
		{
			return false;
		}

		$this->vars = array();
		$this->sub_merge = 0;

		$this->_init_vars();

		$this->init_done = 1;
	}

	function _init_vars()
	{
		// this comes from session.
		$this->vars = array(
			"self" => $this->PHP_SELF,
			"ext"  => $this->cfg["ext"],
			// not very random really
			"rand" => time(),
			"status_msg" => aw_global_get("status_msg"),
			"baseurl" => $this->cfg["baseurl"]
		);
	}

	////
	// !resets all templates and variables
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

	////
	// !Loeb template failist
	function read_template($name,$silent = 0)
	{
		$this->template_filename = $this->template_dir."/".$name;
		if (file_exists($this->template_filename))
		{
			$retval = $this->read_tpl(file($this->template_filename));
		}
		// try to load a template from aw directory then
		elseif (file_exists($this->adm_template_dir . "/" . $name))
		{
			$retval = $this->read_tpl(file($this->adm_template_dir . "/" . $name));
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
				$this->raise_error(ERR_TPL_NOTPL,"Template '".$this->template_filename."' not found",true);
			};
		}
		return $retval;
	}

	function use_template($source)
	{
		$slines = explode("\n",$source);
		return $this->read_tpl($slines);
	}
	
	////
	// !Loeb template failist
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
				$this->raise_error(ERR_TPL_NOTPL,"Template '".$this->template_filename."' not found",true);
			};
		}
		return $retval;
	}

	////
	// !reads the template from the site folder, even if we are in the admin interface
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
				$this->raise_error(ERR_TPL_NOTPL,"Template '".$this->template_filename."' not found",true);
			};
		}
		return $retval;
	}

	function read_any_template($name, $silent = false)
	{
		$this->template_filename = $this->site_template_dir."/".$name;
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
					$this->raise_error(ERR_TPL_NOTPL,"Template '".$this->template_filename."' not found in admin or site folder",true);
				};
			}
		}
		return $retval;
	}

	////
	// !Saab kysida, kas sellise nimega template on registreeritud
	function is_template($name)
	{
		$retval = isset($this->v2_name_map[$name]);
		return $retval;
	}

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
       
	////
	// !Impordib muutujad templatesse, seejuures kirjutatakse juba eksisteerivad
	// muutujad yle
	function vars($params)
	{
		$this->vars = array_merge($this->vars,$params);
	}

	function vars_merge($params)
	{
		while(list($k,$v) = each($params))
		{
			$this->vars[$k] .= $v;
		}
	}

	////
	// !This is where all the magic takes place
	function parse($object = "MAIN") 
	{
		$tmp = isset($this->v2_name_map[$object]) ? $this->v2_name_map[$object] : "";
		$val = isset($this->v2_templates[$tmp]) ? $this->v2_templates[$tmp] : ""; 
		$src = localparse($val, $this->vars);

		// võtame selle maha ka
		aw_session_del("status_msg", true);

		if ($this->sub_merge == 1)
		{
			if (!isset($this->vars[$object]))
			{
				$this->vars[$object] = "";
			}
	   		$this->vars[$object] .= $src;
		}
		return $src;
	}

	////
	// !$arr - template content, array of lines of text
	function read_tpl($arr)
	{
		global $TPL;
		if ( (aw_ini_get("debug_mode") !=0 ) && $TPL)
		{
			print "using " . $this->template_filename . "<br>";
		};
		$this->tpl_reset();
		if (is_array($arr))
		{
			reset($arr);
			$this->v2_arr = $arr;
			$this->req_read_tpl("MAIN","MAIN","");
		}
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
			if (preg_match("/<!-- END SUB: (.*) -->/",$line, $mt))
			{
				// found an end of this subtemplate, 
				// finish and exit
				$this->v2_templates[$fq_name] = $cur_src;
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
		$this->templates[$cur_name] = $cur_src;	// ugh, this line for aliasmanager and image_inplace compatibility :(
		$this->v2_name_map[$cur_name] = $fq_name;
		$this->v2_name_map[$fq_name] = $fq_name;
		return;
	}
};

classload('class_base','html');
?>
