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
		$this->cf_name = "languages::cache::site_id::".$this->cfg["site_id"];
		$this->init_cache();
	}

	function gen_list()
	{
		$this->mk_path(0,"Keeled");
		$this->read_template("list.tpl");
		
		$admin_lang = aw_global_get("admin_lang");
		$lang_id = aw_global_get("lang_id");

		$langs = aw_cache_get_array("languages");
		foreach($langs as $row)
		{
			$this->vars(array(
				"id" => $row["id"], 
				"name" => $row["name"], 
				"charset" => $row["charset"],
				"acceptlang" => $row["acceptlang"],
				"selected" => checked($lang_id == $row["id"]),
				"active" => checked($row["status"] == 2),
				"check" => checked($admin_lang == $row["id"]),
				"modified" => $this->time2date($row["modified"],2),
				"modifiedby" => $row["modifiedby"],
				"change" => $this->mk_my_orb("change", array("id" => $row["id"])),
				"delete" => $this->mk_my_orb("delete", array("id" => $row["id"]))
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"reforb" => $this->mk_reforb("submit_admin"),
			"add" => $this->mk_my_orb("add")
		));

		return $this->parse();
	}

	function fetch($id)
	{
		if (!$id)
		{
			return false;
		}
		return aw_cache_get("languages",$id);
	}

	////
	// !trying to unify names here. 
	// all_data - returns all data otherwise just the stuff to stick in a listbox
	// ignore_status - if true, returns also inactive languages
	// addempty - if true, empty element is added in the beginning
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
		foreach($dat as $ldat)
		{
			if (isset($all_data))
			{
				$ret[$ldat["id"]] = $ldat;
			}
			else
			{
				$ret[$ldat["id"]] = $ldat["name"];
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
					$ret[] = $row;
				}
			}
			return $ret;
		}
		else
		{
			return $lar->get();
		};
	}

	function add()
	{
		$this->mk_path(0,"<a href='".$this->mk_my_orb("admin_list")."'>Keeled</a> / Lisa");
		$this->read_template("add.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_add")
		));
		return $this->parse();
	}

	function change($arr)
	{
		extract($arr);
		$this->mk_path(0,"<a href='".$this->mk_my_orb("admin_list")."'>Keeled</a> / Muuda");
		$this->read_template("add.tpl");
		$l = $this->fetch($id);
		$this->vars(array(
			"id" => $id, 
			"name" => $l["name"], 
			"charset" => $l["charset"],
			"acceptlang" => $l["acceptlang"],
			"reforb" => $this->mk_reforb("submit_add",array("id" => $id))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->db_query("UPDATE languages SET name = '$name' , charset = '$charset' , acceptlang='$acceptlang', modified = ".time().", modifiedby = '".aw_global_get("uid")."' WHERE id = $id");
		}
		else
		{
			$id = $this->db_fetch_field("select max(id) as id from languages","id")+1;
			$this->db_query("INSERT INTO languages(id, name, charset, status, acceptlang, modified, modifiedby) values($id,'$name','$charset',1,'$acceptlang','".time()."','".aw_global_get("uid")."')");
		}

		$this->init_cache(true);

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function set_status($id,$status)
	{
		$this->db_query("UPDATE languages SET status = $status, modified = '".time()."', modifiedby = '".aw_global_get("uid")."' WHERE id = $id");
		$this->init_cache(true);
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
		$q = "SELECT acceptlang FROM languages WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		if ($row)
		{
			aw_session_set("LC",$row["acceptlang"]);
		}
		$this->db_query("UPDATE users SET lang_id = $id WHERE uid = '".aw_global_get("uid")."'");
		aw_session_set("lang_id", $id);

		// milleks see cookie vajalik oli?
		// sest et keele eelistus v6ix ju j22da meelde ka p2rastr seda kui browseri kinni paned
		setcookie("lang_id",$id,time()+$this->cfg["cookie_lifetime"],"/");
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

	function save_list($arr)
	{
		extract($arr);

		$this->set_active($selected);

		$lar = $this->listall(true);
		foreach($lar as $l)
		{
			if ($l["status"] != 0)
			{
				$this->set_status($l["id"],((int)$act[$l["id"]])+1);
			}
		}
		$this->init_cache(true);
	}

	function submit_admin($arr)
	{
		extract($arr);
		aw_global_set("admin_lang",$adminlang);
		aw_session_set("admin_lang",$adminlang);
		$admin_lang_lc = $this->get_langid($adminlang);
		aw_global_set("admin_lang_lc", $admin_lang_lc);
		aw_session_set("admin_lang_lc", $admin_lang_lc);

		setcookie("admin_lang",$adminlang,time()*24*3600*1000,"/");
		setcookie("admin_lang_lc",$admin_lang_lc,time()*24*3600*1000,"/");
		$this->save_list($arr);
		return $this->mk_my_orb("admin_list");
	}

	function orb_delete($arr)
	{
		extract($arr);
		$this->set_status($id,0);
		return $this->mk_my_orb("admin_list");
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
					aw_cache_set("languages", $row["id"],$row);
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
		$lang_id = aw_global_get("lang_id");
		global $DBX;
		if ($DBX)
		{
			var_dump($lang_id);
		};

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

		// assign the correct language so we can find translations
		$LC=$la["acceptlang"];
		if ($LC == "")
		{
			$LC = "et";
		}
		aw_global_set("LC", $LC);
		// oh yeah, we should only overwrite admin_lang_lc if it is not set already!
		if (aw_global_get("admin_lang_lc") == "")
		{
			aw_global_set("admin_lang_lc",$LC);
		}
		// and we should be all done. if after this, lang_id will still be not set I won't be able to write the
		// code that fixes it anyway. 
	}

	function on_site_init($dbi, $site, &$ini_opts)
	{
		// no need to add languages if we are to use an existing database
		if (!$site['site_obj']['use_existing_database'])
		{
			foreach($this->cfg["list"] as $lid => $ldat)
			{
				$dbi->db_query("INSERT INTO languages(id, name, charset, status, acceptlang, modified, modifiedby) values('$lid','$ldat[name]','$ldat[charset]',1,'$ldat[acceptlang]','".time()."','".$site['site_obj']['default_user']."')");
			}
		}
	}
};
?>
