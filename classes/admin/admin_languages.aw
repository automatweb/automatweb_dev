<?php

/*

EMIT_MESSAGE(MSG_LANGUAGE_ADD)

*/

classload("languages");
class admin_languages extends languages
{
	function languages()
	{
		parent::init();
	}

	/**  
		
		@attrib name=admin_list params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function gen_list()
	{
		$this->mk_path(0,t("Keeled"));
		$this->read_template("list.tpl");
		
		$admin_lang = aw_global_get("admin_lang");
		$lang_id = aw_global_get("lang_id");

		//$langs = aw_cache_get_array("languages");
		//foreach($langs as $row)
		$this->db_query("SELECT * FROM languages WHERE status != 0");
		while ($row = $this->db_next())
		{
			$row["meta"] = aw_unserialize($row["meta"]);
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
				"delete" => $this->mk_my_orb("delete", array("id" => $row["id"])),
				"sites" => $row["site_id"],
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

	// the thing is .. you should not be able to enter random crap into "acceptlang" field,
	// so I'm deprecating it and replacing it with a select which gets its contents
	// from the languages.list ini setting (acceptlang key). Want to define a new language?
	// first define it in the INI file. 

	/**  
		
		@attrib name=add params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function add()
	{
		$this->mk_path(0,"<a href='".$this->mk_my_orb("admin_list")."'>".t("Keeled")."</a> / ".t("Lisa"));
		$this->read_template("add.tpl");
		$tmp = aw_ini_get("languages.list");
		$lang_codes = array();
		foreach($tmp as $langdata)
		{
			$lang_codes[$langdata["acceptlang"]] = $langdata["acceptlang"] . "(" . $langdata["name"] . ")";
		};
		$this->vars(array(
			"lang_codes" => $lang_codes,
			"reforb" => $this->mk_reforb("submit_add"),
			"sites" => $this->mpicker(array(), $this->_get_sl())
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=change params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function change($arr)
	{
		extract($arr);
		$this->mk_path(0,"<a href='".$this->mk_my_orb("admin_list")."'>".t("Keeled")."</a> / ".t("Muuda"));
		$this->read_template("add.tpl");
		$l = $this->fetch($id, true);
		// ph33r my l33t copy 'n paste sk1llz
		$tmp = aw_ini_get("languages.list");
		$lang_codes = array();
		foreach($tmp as $langdata)
		{
			$lang_codes[$langdata["acceptlang"]] = $langdata["acceptlang"] . " (" . $langdata["name"] . ")";
		};

		$this->vars(array(
			"id" => $id, 
			"name" => $l["name"], 
			"charset" => $l["charset"],
			"lang_codes" => $this->picker($l["acceptlang"],$lang_codes),
			"acceptlang" => $l["acceptlang"],
			"reforb" => $this->mk_reforb("submit_add",array("id" => $id)),
			"sites" => $this->mpicker($this->make_keys(explode(",", $l["site_id"])), $this->_get_sl()),
			"trans_msg" => $l["meta"]["trans_msg"]
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_add params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit($arr)
	{
		extract($arr);

		$new = false;
		$si = join(",", is_array($site_id) ? $site_id : array());
		$acceptlang = $arr["lang_code"];

		$meta = aw_serialize($meta);
		$this->quote(&$meta);

		if ($id)
		{
			$this->db_query("UPDATE languages SET site_id = '$si' , name = '$name' , charset = '$charset' , acceptlang='$acceptlang', modified = ".time().", modifiedby = '".aw_global_get("uid")."',meta = '$meta' WHERE id = $id");
		}
		else
		{
			$id = $this->db_fetch_field("select max(id) as id from languages","id")+1;
			$this->db_query("INSERT INTO languages(id, name, charset, status, acceptlang, modified, modifiedby, site_id,meta) values($id,'$name','$charset',1,'$acceptlang','".time()."','".aw_global_get("uid")."','$si','$meta')");
			$new = true;
		}

		$this->init_cache(true);

		if ($new)
		{
			post_message("MSG_LANGUAGE_ADD", array("id" => $id));
		}

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function set_status($id,$status)
	{
		$ld = $this->fetch($id, true);
		if ($status != $ld["status"])
		{
			$this->db_query("UPDATE languages SET status = $status, modified = '".time()."', modifiedby = '".aw_global_get("uid")."' WHERE id = $id");
		}
		$this->init_cache(true);
	}


	function save_list($arr)
	{
		extract($arr);

		$this->set_active($selected, true);

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

	/**  
		
		@attrib name=submit_admin params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=delete params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function orb_delete($arr)
	{
		extract($arr);
		$this->set_status($id,0);
		return $this->mk_my_orb("admin_list");
	}
};
?>
