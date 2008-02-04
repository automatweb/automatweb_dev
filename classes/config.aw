<?php
/*
@classinfo  maintainer=kristo
*/
class config extends aw_template
{
	function config() 
	{
		$this->init("automatweb/config");
		$this->sub_merge = 1;
		lc_load("definition");
	}

	function set_simple_config($ckey,$value)
	{
		// 1st, check if the necessary key exists
		$this->quote(&$value);
		$ret = $this->db_fetch_field("SELECT COUNT(*) AS cnt FROM config WHERE ckey = '$ckey'","cnt");
		if ($ret == false)
		{
			// no such key, so create it
			$this->quote($value);
			$this->db_query("INSERT INTO config VALUES('$ckey','$value',".time().",'".aw_global_get("uid")."')");
		}
		else
		{
			$this->quote($content);
			$q = "UPDATE config
				SET content = '$value'
				WHERE ckey = '$ckey'";
			$this->db_query($q);
		}
	}

	function get_simple_config($ckey)
	{
		$q = "SELECT content FROM config WHERE ckey = '$ckey'";
		return $this->db_fetch_field($q,"content");
	}

	/**  
		
		@attrib name=errors params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function errors($arr)
	{
		$this->read_template("errors.tpl");

		$es = $this->get_simple_config("errors");
		$ea = aw_unserialize($es);

		$ol = new object_list(array(
			"class_id" => CL_GROUP,
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($ol->arr() as $o)
		{
			$this->vars(array(
				"grp_id" => $o->prop("gid"),
				"grp_name" => $o->prop("name"),
				"url" => $ea[$o->prop("gid")]["url"],
				"priority" => $ea[$o->prop("gid")]["pri"]
			));
			$li .= $this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $li,
			"reforb" => $this->mk_reforb("submit_errors", array())
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_errors params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_errors($arr)
	{
		extract($arr);

		$es = $this->get_simple_config("errors");
		$ea = aw_unserialize($es);

		if (is_array($grps))
		{
			foreach($grps as $grp => $gar)
			{
				$ea[$grp]["url"] = $gar["url"];
				$ea[$grp]["pri"] = $gar["pri"];
			}
		}

		$ss = aw_serialize($ea, SERIALIZE_XML);
		$this->quote(&$ss);
		$this->set_simple_config("errors", $ss);

		return $this->mk_my_orb("errors", array());
	}

	function get_grp_redir()
	{
		$es = $this->get_simple_config("login_grp_redirect_".aw_ini_get("site_id")."_".aw_global_get("LC"));
		if ($es == false)
		{
			$es = $this->get_simple_config("login_grp_redirect_".aw_global_get("LC"));
			if ($es == false)
			{
				$es = $this->get_simple_config("login_grp_redirect");
			}
		}
		$this->dequote(&$es);
		return aw_unserialize($es);
	}

	/** lets the user set it so that different groups get redirected to diferent pages when logging in 
		
		@attrib name=grp_redirect params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function grp_redirect($arr)
	{
		$this->read_template("login_grp_redirect.tpl");
		$ea = $this->get_grp_redir();

		$ol = new object_list(array(
			"class_id" => CL_GROUP,
			"lang_id" => array(),
			"site_id" => array(),
			"type" => new obj_predicate_not(1) 
		));
		foreach($ol->arr() as $o)
		{
			$this->vars(array(
				"grp_id" => $o->prop("gid"),
				"grp_name" => $o->prop("name"),
				"url" => $ea[$o->prop("gid")]["url"],
				"priority" => $ea[$o->prop("gid")]["pri"]
			));
			$li .= $this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $li,
			"reforb" => $this->mk_reforb("submit_grp_redirect", array())
		));

		return $this->parse();
	}

	/**  
		
		@attrib name=submit_grp_redirect params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_grp_redirect($arr)
	{
		extract($arr);
		$ea = $this->get_grp_redir();

		if (is_array($grps))
		{
			foreach($grps as $grp => $gar)
			{
				$ea[$grp]["url"] = $gar["url"];
				$ea[$grp]["pri"] = $gar["pri"];
			}
		}

		$ss = aw_serialize($ea, SERIALIZE_XML);
		$this->quote(&$ss);
		$this->set_simple_config("login_grp_redirect_".aw_ini_get("site_id")."_".aw_global_get("LC"), $ss);
		return $this->mk_my_orb("grp_redirect", array());
	}

	/**  
		
		@attrib name=favicon params=name nologin="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function show_favicon($arr)
	{
		header("Content-type: image/x-icon");
		die($this->get_simple_config("favicon"));
	}
	
	/**  
		
		@attrib name=config params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function gen_config()
	{
		$this->mk_path(0,LC_CONFIG_SITE);
		$this->read_template("config.tpl");
	
		$al = $this->get_simple_config("after_login");
		$doc = $this->get_simple_config("orb_err_mustlogin");
		$err = $this->get_simple_config("error_redirect");
		$if = $this->get_simple_config("user_info_form");
		$op = $this->get_simple_config("user_info_op");
		$al = $this->get_simple_config("useradd::autologin");
		$ipp = $this->get_cval("ipaddresses::default_folder");

		$fb = get_instance("formgen/form_base");
		$ops = $fb->get_op_list($if);

		$us = get_instance("users");

		$la = get_instance("languages");
		$li = $la->get_list(array("all_data" => true));
		$r_al = "";
		foreach($li as $lid => $ld)
		{
			$tal = $this->get_simple_config("after_login_".$ld["acceptlang"]);
			if (!$tal)
			{
				$tal = $al;
			}
			$this->vars(array(
				"lang_id" => $ld["acceptlang"],
				"lang" => $ld["name"],
				"after_login" => $tal
			));
			$r_al .= $this->parse("AFTER_LOGIN");
		}

		$r_ml = "";
		foreach($li as $lid => $ld)
		{
			$tml = $this->get_simple_config("orb_err_mustlogin_".$ld["acceptlang"]);
			if (!$tml)
			{
				$tml = $doc;
			}
			$this->vars(array(
				"lang_id" => $ld["acceptlang"],
				"lang" => $ld["name"],
				"mustlogin" => $tml
			));
			$r_ml .= $this->parse("MUSTLOGIN");
		}

		$r_el = "";
		foreach($li as $lid => $ld)
		{
			$tml = $this->get_simple_config("error_redirect_".$ld["acceptlang"]);
			if (!$tml)
			{
				$tml = $err;
			}
			$this->vars(array(
				"lang_id" => $ld["acceptlang"],
				"lang" => $ld["name"],
				"error_redirect" => $tml
			));
			$r_el .= $this->parse("ERROR_REDIRECT");
		}

		$fvi = "";
		if ($this->get_simple_config("favicon") != "")
		{
			$fvi = "<img src='".$this->mk_my_orb("favicon", array(),"config", false,true)."'>";
		}

		$this->vars(array(
			"AFTER_LOGIN" => $r_al,
			"favicon" => $fvi,
			"forms" => $this->picker($if,$fb->get_list(FTYPE_ENTRY,true)),
			"ops" => $this->picker($op,$ops[$if]),
			"search_doc" => $this->mk_orb("search_doc", array(),"links"),
			"MUSTLOGIN" => $r_ml,
			"ERROR_REDIRECT" => $r_el,
			"reforb" => $this->mk_reforb("submit_loginaddr"),
			"autologin" => checked($al),
			"ipp" => $this->picker($ipp, $this->get_menu_list())
		));
		return $this->parse();
	}
	
	/**  
		
		@attrib name=submit_loginaddr params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_loaginaddr($arr)
	{
		extract($arr);
		$la = get_instance("languages");
		$li = $la->get_list(array("all_data" => true));
		foreach($li as $lid => $ld)
		{
			$var = "after_login_".$ld["acceptlang"];
			$this->set_simple_config($var, $$var);
		}
		$this->set_simple_config("user_info_form",$user_info_form);
		$this->set_simple_config("user_info_op",$user_info_op);

		foreach($li as $lid => $ld)
		{
			$var = "mustlogin_".$ld["acceptlang"];
			$this->set_simple_config("orb_err_mustlogin_".$ld["acceptlang"], $$var);
		}

		$this->set_simple_config("error_redirect",$error_redirect);
		foreach($li as $lid => $ld)
		{
			$var = "error_redirect_".$ld["acceptlang"];
			$this->set_simple_config($var, $$var);
		}
		$this->set_simple_config("useradd::autologin",$autologin);
		$this->set_simple_config("ipaddresses::default_folder",$ipp);

		// if favicon was uploaded, handle it. 
		global $favicon;
		if (is_uploaded_file($favicon))
		{
			$f = fopen($favicon,"r");
			$fc = fread($f,filesize($favicon));
			$this->quote(&$fc);
			fclose($f);
			$this->set_simple_config("favicon", $fc);
		}
		return $this->mk_my_orb("config");
	}
}
?>
