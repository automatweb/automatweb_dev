<?php

class languages extends aw_template
{
	function languages()
	{
		$this->init("languages");
		lc_load("definition");
		$this->lc_load("languages","lc_languages");
		$this->file_cache = get_instance("cache");
		// the name of the cache file
		$this->cf_name = "languages-cache-site_id-".$this->cfg["site_id"];
		$this->init_cache();
	}

	function fetch($id, $no_cache = false)
	{
		if (!$id)
		{
			return false;
		}
		if ($no_cache)
		{
			$ret =  $this->db_fetch_row("SELECT * FROM languages WHERE id = '$id'");
			$ret["meta"] = aw_unserialize($ret["meta"]);
			return $ret;
		}
		else
		{
			return aw_cache_get("languages",$id);
		}
	}

	////
	// !trying to unify names here. 
	// all_data - returns all data otherwise just the stuff to stick in a listbox
	// ignore_status - if true, returns also inactive languages
	// addempty - if true, empty element is added in the beginning
	// key - use that field as keys of the return array
	function get_list($arr = array())
	{
		extract($arr);
		$dat = $this->listall(isset($ignore_status) ? $ignore_status : false);

		if (isset($addempty))
		{
			$ret = array("0" => "");
		}
		else
		{
			$ret = array();
		}
		$use_key = isset($key) ? $key : "id";
		foreach($dat as $ldat)
		{
			if (isset($all_data))
			{
				$ret[$ldat[$use_key]] = $ldat;
			}
			else
			{
				$ret[$ldat[$use_key]] = $ldat["name"];
			}
		}

		return $ret;
	}

	function listall($ignore_status = false)
	{
		$lar = new aw_array(aw_cache_get_array("languages"));
		if (!$ignore_status)
		{
			$ret = array();
			foreach($lar->get() as $row)
			{
				if ($row["status"] == 2)
				{
					$ret[$row["id"]] = $row;
				}
			}
			return $ret;
		}
		else
		{
			return $lar->get();
		};
	}

	function _get_sl()
	{
		$ret = array();
		$this->db_query("SELECT DISTINCT(site_id) AS site_id FROM objects");
		while ($row = $this->db_next())
		{
			if ($row["site_id"] != 0)
			{
				// get site name from site server
				/*
				$sd = $this->do_orb_method_call(array(
					"class" => "site_list", 
					"action" => "get_site_data", 
					"params" => array(
						"site_id" => $row["site_id"]
					),
					"method" => "xmlrpc",
					"server" => "register.automatweb.com"
				));
				*/
				$ret[$row["site_id"]] = $sd["name"]."( ".$row["site_id"]." )";
			}
		}
		return $ret;
	}

	////
	// !sets the active language to $id
	function set_active($id,$force_act = false)
	{
		$id = (int)$id;
		$l = $this->fetch($id);
		if (($l["status"] != 2 && aw_global_get("uid") == "") && !$force_act)
		{
			return false;
		}
		$this->quote(&$id);
		$id = (int)$id;
		$q = "SELECT acceptlang FROM languages WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		if ($row)
		{
			aw_session_set("LC",$row["acceptlang"]);
		}
		$uid = aw_global_get("uid");
		if (!empty($uid))
		{
			$this->db_query("UPDATE users SET lang_id = '$id' WHERE uid = '$uid'");
		};
		aw_session_set("lang_id", $id);

		// milleks see cookie vajalik oli?
		// sest et keele eelistus v6ix ju j22da meelde ka p2rastr seda kui browseri kinni paned
		if (!headers_sent())
		{
			setcookie("lang_id",$id,time()+aw_ini_get("languages.cookie_lifetime"),"/");
		};
		aw_global_set("lang_id", $id);
		return $id;
	}
	
	////
	// !this tries to figure out the balance between the user's language preferences and the 
	// languages that are available. this will only return active languages.
	function find_best()
	{
		$la = aw_cache_get_array("languages");
		$langs = array();
		$def = 0;
		if (is_array($la))
		{
			foreach($la as $row)
			{
				if ($row["status"] == 2)
				{
					$langs[$row["acceptlang"]] = $row["id"];
					if (!$def)
					{
						// pick the first active one from the list in case no matches exist for browser settings
						$def = $row["id"];
					}
				}
			}
		}

		// get all the user's preferences from the browser
		$larr = explode(",",aw_global_get("HTTP_ACCEPT_LANGUAGE"));
		reset($larr);
		while (list(,$v) = each($larr))
		{
			$la = substr($v,0,strcspn($v,"-; "));
			if ($langs[$la])
			{
				// and accept the first match, nobody uses the really fancy features anyway :P
				return $langs[$la];
			}
		}
		// if there were no matches then just pick the first one
		if ($def)
		{
			return $def;
		}
		// if no languages are active, then get the first one. 
		if (is_array($la))
		{
			reset($la);
			list($_i,$row) = each($la);
			if ($row["id"])
			{
				return $row["id"];
			}
		};
		// if there are no languages defined in the site, we are fucked anyway, so just return a reasonable number
		return 1;
	}

	function get_charset()
	{
		$a = $this->fetch(aw_global_get("lang_id"));
		return $a["charset"];
	}

	function get_langid($id = -1)
	{
		if ($id == -1)
		{
			$id = aw_global_get("lang_id");
		}
		$a = $this->fetch($id);
		return $a["acceptlang"];
	}

	function get_langid_for_code($code)
	{
		$list = $this->get_list(array("all_data" => true));
		foreach($list as $id => $dat)
		{
			if ($dat["acceptlang"] == $code)
			{
				return $id;
			}
		}
		return NULL;
	}

	////
	// !this reads all the languages in the site to aw language cache, all the functions in this file use that
	function init_cache($force_read = false)
	{
		if ($force_read || !($_it = aw_global_get("lang_cache_init")))
		{
			// now try the file cache thingie - maybe it's faster :) I mean, yeah, ok, 
			// this doesn't exactly take much time anyway, but still, can't be bad, can it?

			// if the file cache exists and this is not an update, then read from that
			if (!$force_read && ($cc = $this->file_cache->file_get($this->cf_name))) 	
			{
				aw_cache_set_array("languages", aw_unserialize($cc));
			}
			else
			{
				// we must re-read from the db and write the cache
				aw_cache_flush("languages");
				$this->db_query("SELECT * FROM languages WHERE status != 0");
				while ($row = $this->db_next())
				{
					$row["meta"] = aw_unserialize($row["meta"]);
					//the following if was in the form of if(true || .....), so i guess there was
					//a reason for that, i checked on eures the values of the variables, and the
					//if without the true seems to work too, if anything goes wrong, i can always
					//write it back in, this why i'm writing this comment :)
					if (trim($row["site_id"]) == "" || in_array($this->cfg["site_id"], explode(",", trim($row["site_id"]))))
					{
						aw_cache_set("languages", $row["id"],$row);
					}
				}
				$this->file_cache->file_set($this->cf_name,aw_serialize(aw_cache_get_array("languages")));
			}
			aw_global_set("lang_cache_init",1);
		}
	}

	////
	// !this will get called once in the beginning of the page, so that the class can initialize itself nicely
	function request_startup()
	{
		classload("vcl/date_edit");
		$lang_id = aw_global_get("lang_id");

		// if we explicitly request language change, we get that, except if the language is not active
		// and we are not logged in
		if (($sl = aw_global_get("set_lang_id")))
		{
			// if language has not changed, don't waste time re-setting it
			if ($sl != $lang_id)	
			{
				if (($_l = $this->set_active($sl)))
				{
					$lang_id = $_l;
				}
				// if request to change language is denied
				// then we sould remain with the old one, methinks
			}
		}

		if (!$lang_id && aw_ini_get("languages.default"))
		{
			$lang_id = aw_ini_get("languages.default");
			$this->set_active($lang_id,true);
			$la = $this->fetch($lang_id);
		}
		else
		{
			// if at this point no language is active, then we must select one
			if (!$lang_id)
			{
				// try to find one by looking at the preferences the user has set in his/her browser
				$lang_id = $this->find_best();
				// since find_best() pulls just about every trick in the book to try and find a
				// suitable lang_id, we will just force it to be set active, since we can't do better anyway
				$this->set_active($lang_id,true);
				$la = $this->fetch($lang_id);
			}
			else
			{
				// if a language is active, we must check if perhaps someone kas de-activated it in the mean time
				$la = $this->fetch($lang_id);
				if (!($la["status"] == 2 || ($la["status"] == 1 && aw_global_get("uid") != "")))
				{
					// if so, try to come up with a better one.
					$lang_id = $this->find_best();
					$this->set_active($lang_id,true);
					$la = $this->fetch($lang_id);
				}
			}
		}

		// assign the correct language so we can find translations
		$LC=$la["acceptlang"];
		if ($LC == "")
		{
			$LC = "et";
		}
		aw_global_set("LC", $LC);
		aw_global_set("charset",$la["charset"]);
		// oh yeah, we should only overwrite admin_lang_lc if it is not set already!
		if (aw_global_get("admin_lang_lc") == "")
		{
			aw_global_set("admin_lang_lc",$LC);
		}

		aw_global_set("lang_oid", $la["oid"]);
		// and we should be all done. if after this, lang_id will still be not set I won't be able to write the
		// code that fixes it anyway. 
	}

	function on_site_init($dbi, $site, &$ini_opts, &$log)
	{
		// no need to add languages if we are to use an existing database
		if (!$site['site_obj']['use_existing_database'])
		{
			foreach($this->cfg["list"] as $lid => $ldat)
			{
				$status = 1;
				if ($lid == 1)
				{
					$status = 2;
				}
				$dbi->db_query("INSERT INTO languages(id, name, charset, status, acceptlang, modified, modifiedby) values('$lid','$ldat[name]','$ldat[charset]',$status,'$ldat[acceptlang]','".time()."','".$site['site_obj']['default_user']."')");
				$log->add_line(array(
					"uid" => aw_global_get("uid"),
					"msg" => t("Lisas keele"),
					"comment" => $ldat["name"],
					"result" => "OK"
				));
			}
		}
	}
};
?>
