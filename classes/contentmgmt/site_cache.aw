<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_cache.aw,v 1.18 2004/12/22 09:38:44 kristo Exp $

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

		$si = __get_site_instance();
		if (is_object($si) && method_exists($si,"pre_start_display"))
		{
			$si->pre_start_display($arr);
		}

		//if (aw_ini_get("menuedit.content_from_class_base") == 1 && aw_global_get("section") != aw_ini_get("frontpage"))
		if (aw_ini_get("menuedit.content_from_class_base") == 1)
		{
			$arr["content_only"] = 1;
		}
		else
		if (($content = $this->get_cached_content($arr)))
		{
			$this->ip_access($arr);
			return $this->do_final_content_checks($content);
		}
		// okey, now

		$inst = get_instance("contentmgmt/site_show");
		$content = $inst->show($arr);
		if (!aw_global_get("no_cache"))
		{
			$this->set_cached_content($arr, $content);
		}

		if (aw_ini_get("menuedit.content_from_class_base") == 1)
		{
			// now I'm assuming that frontpage is set to some kind of AW object
			$obj = new object(aw_ini_get("site_container"));
			$t = get_instance($obj->class_id());

			if (aw_global_get("section") != aw_ini_get("frontpage") || empty($_REQUEST["group"]))
			{
				// see kuvab vajaliku sisu
				$content = $t->change(array(
					"id" => aw_ini_get("site_container"),
					"group" => $_REQUEST["group"],
					"content" => $content,
				));
			}
			else
			{
				// see kuvab muutmisvormi
				$content = $t->change(array(
					"id" => aw_ini_get("site_container"),
					"group" => $_REQUEST["group"],
				));


			};

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

		if (aw_global_get("no_cache"))
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
		$tmp = $cache->get(aw_global_get("section"), $cp);
		if ($GLOBALS["INTENSE_CACHE"] == 1)
		{
			echo "look for pagecache ".aw_global_get("section")." cp = ".dbg::dump($cp)." <br>";
		}
		return $tmp;
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
		if (strpos($res,"[ss") !== false)
		{
			$res = preg_replace("/\[ss(\d+)\]/e","md5(time().\$_SERVER[\"REMOTE_ADDR\"].\"\\1\")",$res);
		};
		if (strpos($res,"[document_statistics") !== false)
		{
			$ds = get_instance('contentmgmt/document_statistics');
			$res = preg_replace("/\[document_statistics(\d+)\]/e", "\$ds->show(array('id' => \\1))", $res);
		};

		// also clear the no_cache flag from session if present
		// why? well to let the session continue with cached pages. 
		// basically, to have error messages on your submit handler you set 
		// aw_session_set("no_cache", 1);
		// then redirect to some page. now, the no_cache stays as an aw_global
		// so on the next pageview it is checked and honored. 
		// now, it could be the task of the application to clear this
		// but then the content would still end up in the cache
		// because this thing sets the cache content if the page is uncached
		// and the no_cache flag is off after having displayed the page
		// therefore this is the only place where we can safely clear the flag.
		aw_session_set("no_cache", 0);

		return $res;
	}

	function ip_access($arr)
	{
		$so = obj(aw_global_get("section"));
		$p = $so->path();
		$p[] = $so;
		$p = array_reverse($p);
		foreach($p as $o)
		{
			$ipa = $o->meta("ip_allow");
			$ipd = $o->meta("ip_deny");
			if ((is_array($ipd) && count($ipd)) || (is_array($ipa) && count($ipa)))
			{
				$si = get_instance("contentmgmt/site_show");
				$si->section_obj = $so;
				$si->do_check_ip_access(array(
					"allowed" => $ipa,
					"denied" => $ipd
				));
			}
		}
	}
}
?>
