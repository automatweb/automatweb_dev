<?php

class site_cache extends aw_template
{
	function site_cache()
	{
		$this->init("automatweb/menuedit");
	}

	function show($arr = array())
	{
		if (!isset($arr["template"]) || $arr["template"] == "")
		{
			$arr["template"] = "main.tpl";
		}

		$log = get_instance("contentmgmt/site_logger");
		$log->add($arr);

		if (($content = $this->get_cached_content($arr)))
		{
			return $content;
		}
		
		if (!($compiled_filename = $this->get_cached_compiled_filename($arr)))
		{
			$compiled_filename = $this->cache_compile_template($arr["template"]);
		}

		$arr["compiled_filename"] = $compiled_filename;

		$inst = get_instance("contentmgmt/site_show");
		return $inst->show($arr);
	}

	////
	// !returns the cached content for the requested page
	// if no user is logged in and the page exist in the cache
	function get_cached_content($arr)
	{
		if (aw_global_get("uid") != "")
		{
			return false;
		}

		// don't cache pages with generated content, they usually change for each request
		if ($arr["text"] != "")
		{
			if ($arr["force_cache"] != true)
			{
				return false;
			}
		}

		// check cache
		$cp = $this->get_cache_params($arr);
		
		$cache = get_instance("cache");
		return $cache->get(aw_global_get("section"), $cp);
	}

	////
	// !returns array of cache parameters with what you can check if the current page is in the cache
	// params:
	//	format
	//
	function get_cache_params($arr)
	{
		$cp = array();
		if (isset($arr["format"]) && $arr["format"] != "")
		{
			$cp[] = $arr["format"];
		}

		$cp[] = aw_global_get("act_per_id");
		$cp[] = aw_global_get("lang_id");

		// here we sould add all the variables that are in the url to the cache parameter list
		foreach($GLOBALS["HTTP_GET_VARS"] as $var => $val)
		{
			// just to make sure that each user does not get it's own copy
			if ($var != "automatweb" && $var != "set_lang_id")
			{
				if (is_array($val))
				{
					$ov = $val;
					$val = "";
					foreach($ov as $vv)
					{
						$val.=$vv;
					}
				}
				$cp[] = $var."-".$val;
			}
		}
		
		return $cp;
	}

	////
	// !returns the file where the generated code for the template is, if it is in the cache
	function get_cached_compiled_filename($arr)
	{
		$fn = aw_ini_get("cache.page_cache")."/compiled_menu_template::".str_replace(".","_",$arr["template"]);
		if (file_exists($fn) && is_readable($fn))
		{
			return $fn;
		}
		return false;
	}

	////
	// !compiles the template and saves the code in a cache file, returns the cache file
	function cache_compile_template($tpl)
	{
		$co = get_instance("contentmgmt/site_template_compiler");
		$code = $co->compile($tpl);

		$fn = "compiled_menu_template::".str_replace(".","_",$tpl);		

		$ca = get_instance("cache");
		$ca->file_set($fn, $code);

		return aw_ini_get("cache.page_cache")."/".$fn;
	}
}
?>
