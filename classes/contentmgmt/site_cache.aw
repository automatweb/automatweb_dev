<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_cache.aw,v 1.12 2004/04/27 10:43:05 duke Exp $

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

		if (false && ($content = $this->get_cached_content($arr)))
		{
			return $this->do_final_content_checks($content);
		}

		if (aw_ini_get("menuedit.content_from_class_base") == 1)
		{
			$arr["content_only"] = 1;
		}
		// okey, now

		$inst = get_instance("contentmgmt/site_show");
		$content = $inst->show($arr);

		if (aw_ini_get("menuedit.content_from_class_base") == 1)
		{
			// now I'm assuming that frontpage is set to some kind of AW object
			$obj = new object(aw_ini_get("frontpage"));
			$t = get_instance($obj->class_id());
			$content = $t->change(array(
				"id" => aw_ini_get("frontpage"),
				"content" => $content,
			));
		};

		$this->set_cached_content($arr, $content);
		return $this->do_final_content_checks($content);
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

	function set_cached_content($arr, $content)
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
		$cache->set(aw_global_get("section"), $cp, $content);
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

	function do_final_content_checks($res)
	{
		$res = preg_replace("/\[ss(\d+)\]/e","md5(time().\$_SERVER[\"REMOTE_ADDR\"].\"\\1\")",$res);
		$ds = get_instance('contentmgmt/document_statistics');
		$res = preg_replace("/\[document_statistics(\d+)\]/e", "\$ds->show(array('id' => \\1))", $res);
		return $res;
	}
}
?>
