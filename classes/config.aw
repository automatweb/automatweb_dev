<?php
// $Header: /home/cvs/automatweb_dev/classes/config.aw,v 2.61 2004/10/28 15:07:23 duke Exp $

class db_config extends aw_template 
{
	function db_config() 
	{
		$this->init("automatweb/config");
		$this->sub_merge = 1;
		lc_load("definition");
	}

	function set_simple_config($ckey,$value)
	{
		// 1st, check if the necessary key exists
		$ret = $this->db_fetch_field("SELECT COUNT(*) AS cnt FROM config WHERE ckey = '$ckey'","cnt");
		if ($ret == false)
		{
			// no such key, so create it
			$this->create_config($ckey,$value);
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

	function create_config($ckey,$value)
	{
		$this->quote($value);
		$this->db_query("INSERT INTO config VALUES('$ckey','$value',".time().",'".aw_global_get("uid")."')");
	}

	////
	// Votab argumentidena gidlisti, ning üritab tagastada oige login menüü
	// aadressi.
	function get_login_menus($args = array())
	{
		$_data = $this->_get_login_menus();
		$data = $_data[aw_global_get("lang_id")];
		if (!is_array($data))
		{
			if (is_array($_data))
			{
				foreach($_data as $k => $v)
				{
					if (is_array($v))
					{
						$data = $v;
					}
				}
			}
		};

		if (!is_array($data))
		{
			return;
		}

		$gids = aw_global_get("gidlist");
		$cur_pri = -1;
		$cur_menu = -1;

		if (!is_array($gids))
		{
			return;
		};

		foreach($gids as $gid)
		{
			if (($data[$gid]["pri"] > $cur_pri) && ($data[$gid]["menu"]))
			{
				$cur_pri = $data[$gid]["pri"];
				$cur_menu = $data[$gid]["menu"];
			}
		};

		return $cur_menu;
	}

	function _get_login_menus($args = array())
	{
		$sid = aw_ini_get("site_id");
		$res = $this->get_simple_config("login_menus_".$sid);
		if (!$res)
		{
			$res = $this->get_simple_config("login_menus");
		}
		return aw_unserialize($res);
	}
};

// urk. orb requires class name to be == file name
class config extends db_config
{
	function config()
	{
		$this->db_config();
	}

	/**  
		
		@attrib name=join_mail params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function join_mail($arr)
	{
		$this->read_template("join_mail.tpl");

		$la = get_instance("languages");
		$ll = $la->listall();

		foreach($ll as $lid => $ldata)
		{
			$this->vars(array(
				"join_mail" => $this->get_simple_config("join_mail".$ldata["acceptlang"]),
				"pwd_mail" => $this->get_simple_config("remind_pwd_mail".$ldata["acceptlang"]),
				"join_mail_subj" => $this->get_simple_config("join_mail_subj".$ldata["acceptlang"]),
				"pwd_mail_subj" => $this->get_simple_config("remind_pwd_mail_subj".$ldata["acceptlang"]),
				"acceptlang" => $ldata["acceptlang"],
				"name" => $ldata["name"]
			));
			$lb.=$this->parse("LANG");
		}

		$this->vars(array(
			"LANG" => $lb,
			"reforb" => $this->mk_reforb("submit_join_mail", array()),
			"join_send_also" => $this->get_simple_config("join_send_also"),
		));

		return $this->parse();
	}

	/**  
		
		@attrib name=submit_join_mail params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_join_mail($arr)
	{
		extract($arr);

		$la = get_instance("languages");
		$ll = $la->listall();

		foreach($ll as $lid => $ldata)
		{
			$this->set_simple_config("join_mail".$ldata["acceptlang"],$join_mail[$ldata["acceptlang"]]);
			$this->set_simple_config("remind_pwd_mail".$ldata["acceptlang"],$pwd_mail[$ldata["acceptlang"]]);
			$this->set_simple_config("join_mail_subj".$ldata["acceptlang"],$join_mail_subj[$ldata["acceptlang"]]);
			$this->set_simple_config("remind_pwd_mail_subj".$ldata["acceptlang"],$pwd_mail_subj[$ldata["acceptlang"]]);
		}
		$this->set_simple_config("join_send_also",$join_send_also);

		return $this->mk_orb("join_mail", array());
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
		$x = get_instance("xml");
		$ea = $x->xml_unserialize(array("source" => $es));

		$u = get_instance("users");
		$u->listgroups(-1,-1,GRP_REGULAR,GRP_DYNAMIC);
		while($row = $u->db_next())
		{
			$this->vars(array(
				"grp_id" => $row["gid"],
				"grp_name" => $row["name"],
				"url" => $ea[$row["gid"]]["url"],
				"priority" => $ea[$row["gid"]]["pri"]
			));
			$li.=$this->parse("LINE");
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
		$x = get_instance("xml");
		$ea = $x->xml_unserialize(array("source" => $es));

		if (is_array($grps))
		{
			foreach($grps as $grp => $gar)
			{
				$ea[$grp]["url"] = $gar["url"];
				$ea[$grp]["pri"] = $gar["pri"];
			}
		}

		$ss = $x->xml_serialize($ea);
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

		$u = get_instance("users");
		$u->listgroups(-1,-1,GRP_REGULAR,GRP_DYNAMIC);
		while($row = $u->db_next())
		{
			$this->vars(array(
				"grp_id" => $row["gid"],
				"grp_name" => $row["name"],
				"url" => $ea[$row["gid"]]["url"],
				"priority" => $ea[$row["gid"]]["pri"]
			));
			$li.=$this->parse("LINE");
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
		$bt = $this->get_simple_config("bugtrack_developergid");
		$btadm = $this->get_simple_config("bugtrack_admgid");
		$al = $this->get_simple_config("useradd::autologin");
		$ipp = $this->get_cval("ipaddresses::default_folder");

		$fb = get_instance("formgen/form_base");
		$ops = $fb->get_op_list($if);

		$us = get_instance("users");

		$ob_i = get_instance("objects");
		
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

		$this->vars(array(
			"AFTER_LOGIN" => $r_al,
			"favicon" => "<img src='".$this->mk_my_orb("favicon", array(),"config", false,true)."'>",
			"forms" => $this->picker($if,$fb->get_list(FTYPE_ENTRY,true)),
			"ops" => $this->picker($op,$ops[$if]),
			"search_doc" => $this->mk_orb("search_doc", array(),"links"),
			"bt_gid" => $this->picker($bt,$us->get_group_picker(array("type" => array(GRP_REGULAR, GRP_DYNAMIC)))),
			"bt_adm" => $this->picker($btadm,$us->get_group_picker(array("type" => array(GRP_REGULAR, GRP_DYNAMIC)))),
			"MUSTLOGIN" => $r_ml,
			"ERROR_REDIRECT" => $r_el,
			"reforb" => $this->mk_reforb("submit_loginaddr"),
			"autologin" => checked($al),
			"cfgform_link" => $this->mk_my_orb("class_cfgforms",array()),
			"ipp" => $this->picker($ipp, $ob_i->get_list())
		));
		return $this->parse();
	}
	
	/** Kuvab dünaamiliste gruppide nimekirja ja lubab igale login menüü valida 
		
		@attrib name=login_menus params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function login_menus($args = array())
	{
		$this->read_template("login_menu.tpl");

		$_data = $this->_get_login_menus();

		$data = $_data[aw_global_get("lang_id")];

		$this->mk_path(0,"Action menüüd");

		$ob = get_instance("objects");
		$menus =  $ob->get_list(false,true);

		if (is_array($data["pri"]))
		{
			asort($data["pri"]);
		};

		$u = get_instance("users_user");
		$u->list_groups(array("type" => array(GRP_DYNAMIC,GRP_REGULAR)));
		$c = "";
		while($row = $u->db_next())
		{

			$return_url = urlencode($this->mk_my_orb("submit_login_menus",array("parent" => $row["gid"]),"config"));

			$midx = $data[$row["gid"]]["menu"];
			if ($midx)
			{
				$name = $menus[$midx];
			}
			else
			{
				$name = "(määramata)";
			};

			$this->vars(array(
				"gid" => $row["gid"],
				"group" => $row["name"],
				"name" => $name,
				"priority" =>  ($data[$row["gid"]]["pri"]) ? $data[$row["gid"]]["pri"] : 0,
				"search" => $this->mk_my_orb("search",array("otype" => CL_MENU,"one" => 1,"return_url" => $return_url),"objects"),
				"login_menu" => $this->picker($midx, $menus)
			));

			$c .= $this->parse("LINE");
		};

		$this->vars(array(
			"LINE" => $c,
			"reforb" => $this->mk_reforb("submit_login_menus",array()),
		));
		return $this->parse();
	}

	/** Submitib login_menus funktsioonis tehtud valikud 
		
		@attrib name=submit_login_menus params=name all_args="1" default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_login_menus($args = array())
	{
		extract($args);
		$xml = get_instance("xml");
		//$old = aw_unserialize($this->get_simple_config("login_menus"));
		$old = $this->_get_login_menus();

		if (is_array($login_menu))
		{
			foreach($login_menu as $gid => $mn)
			{
				$old[aw_global_get("lang_id")][$gid]["menu"] = $mn;
			}
		}

		if ($pick && $parent)
		{
			$old[aw_global_get("lang_id")][$parent]["menu"] = $pick;
		}
		else
		{
			// overwrite priorities
			foreach($pri as $key => $val)
			{
				$old[aw_global_get("lang_id")][$key]["pri"] = $val;
			}
		};

		$data = aw_serialize($old);

		$this->quote($data);

		$this->set_simple_config("login_menus_".aw_ini_get("site_id"),$data);

		return $this->mk_my_orb("login_menus",array());
	}


	/**  see laseb saidile liitumisvormi valida
		
		@attrib name=sel_join_form params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function sel_join_form($arr)
	{
		extract($arr);
		$this->mk_path(0,sprintf(LC_CONFIG_CHOOSE_USER,$this->mk_my_orb("config")));

		$this->read_template("sel_form.tpl");
		
		$this->db_query("SELECT objects.name AS name,
					objects.comment AS comment,
					objects.oid AS oid,
					forms.type AS type,
					forms.subtype AS subtype,
					forms.grp AS grp,
					forms.j_order as j_order,
					forms.j_name AS j_name,
					forms.j_op AS j_op,
					forms.j_op2 AS j_op2,
					forms.j_mustfill AS j_mustfill
				FROM forms
				LEFT JOIN objects ON objects.oid = forms.id
				WHERE objects.status != 0 AND forms.type = ".FTYPE_ENTRY);
		
		$forms = array();

		$f = get_instance("formgen/form");
		$ops = $f->get_op_list();
		while ($row = $this->db_next())
		{
			if ($row["subtype"] == FSUBTYPE_JOIN)
			{
				$forms[$row["oid"]] = $row["oid"];
			}
			$tops = new aw_array($ops[$row["oid"]]);
			$rops = array(0 => '') + $tops->get();

			$this->vars(array(
				"form_id"	=> $row["oid"],
				"form_name" 	=> $row["name"],
				"form_comment"	=> $row["comment"],
				"checked" 	=> ($row["subtype"] == FSUBTYPE_JOIN) ? "checked" : "",
				"group"		=> $row["grp"],
				"change"	=> $this->mk_orb("change", array("id" => $row["oid"]), "form"),
				"name"		=> $row["j_name"],
				"order"		=> $row["j_order"],
				"jops"			=> $this->picker($row["j_op"], $rops),
				"jops2"			=> $this->picker($row["j_op2"],$rops),
				"mf" => checked($row["j_mustfill"] == 1)
			));
			if ($row["subtype"] == FSUBTYPE_JOIN)
			{
				$this->vars(array(
					"GROUP" => $this->parse("GROUP"),
					"ORDER" => $this->parse("ORDER"),
					"NAME"	=> $this->parse("NAME"),
					"OPS"	=> $this->parse("OPS"),
					"OPS2"	=> $this->parse("OPS2"),
					"MUSTFILL"	=> $this->parse("MUSTFILL"),
				));
			}
			else
			{
				$this->vars(array("GROUP" => "","NAME" => "", "ORDER" => "","OPS" => "","OPS2" => "","MUSTFILL" => ""));
			}
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l, 
			"SEL_LINE" => "",
			"reforb" => $this->mk_reforb("save_jf")
		));

		// now select elements part
		$email_el = $this->get_cval("users::email_element");
		$name_els = aw_unserialize($this->get_cval("users::name_elements"));
		$name_els_sep = $this->get_cval("users::name_elements_sep");

		$finst = get_instance("formgen/form");
		$els = $finst->get_elements_for_forms($forms, false, true);

		$this->vars(array(
			"email_el" => $this->picker($email_el, $els),
			"name_els" => $this->mpicker($name_els, $els),
			"name_els_sep" => $name_els_sep,
			"n_reforb" => $this->mk_reforb("submit_email_els")
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_email_els params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_email_els($arr)
	{
		extract($arr);
		$this->set_cval("users::email_element",$email_el);
		$ser = aw_serialize($this->make_keys($name_els));
		$this->quote(&$ser);
		$this->set_cval("users::name_elements", $ser);
		$this->set_cval("users::name_elements_sep",$name_els_sep);
		return $this->mk_my_orb("sel_join_form");
	}

	/**  
		
		@attrib name=save_jf params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function save_jf($arr)
	{
		extract($arr);

		// k6igepealt v6tame k6ik maha
		$this->db_query("UPDATE forms SET subtype = 0, grp = '' WHERE subtype = ".FSUBTYPE_JOIN);

		if (is_array($sf))
		{
			reset($sf);
			// fid-id on vormide id-d
			// fg on vormide grupid
			// fp on vormide outputid
			$this->quote($fg);
			while(list($fid,$v) = each($sf))
			{
				$q = sprintf("UPDATE forms SET subtype = '%s', grp = '%s',j_name = '%s', j_order='%s', j_op = '%s',j_op2 = '%s',j_mustfill='%s' WHERE id = $fid",
					FSUBTYPE_JOIN,$fg[$fid],$fn[$fid],$fo[$fid],$fp[$fid],$fp2[$fid],$mf[$fid]);
				$this->db_query($q);
			}
		}
		return $this->mk_my_orb("sel_join_form");
	}

	/**  
		
		@attrib name=class_cfgforms params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function class_cfgforms()
	{
		$this->mk_path(0,"Klasside konfiguratsioonivormid");

		load_vcl("table");
		$t = new aw_table(array(
			"prefix" => "class_cfgforms",
			"tbgcolor" => "#C3D0DC",
		));

		$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
		$t->tableattribs["width"] = "300";
		$t->define_field(array(
			"name" => "name",
			"caption" => "Klass",
			"talign" => "center",
			"nowrap" => "1",
			"sortable" => 1,
			"width" => "20%",
		));
		$t->define_field(array(
			"name" => "cfgform",
			"caption" => "Konfivorm",
			"talign" => "center",
			"nowrap" => "1",
			"width" => "20%",
		));
		
		$this->read_template("class_cfgforms.tpl");
				
		$ol = new object_list(array(
			"class_id" => CL_CFGFORM,
			"site_id" => array(),
			"lang_id" => array()
		));
		$options = array("" => "") + $ol->names();
		classload("html");

		$cfgu = get_instance("cfg/cfgutils");
		
		$cs = aw_unserialize($this->get_simple_config("class_cfgforms"));

		while (list($clid,$desc) = each(aw_ini_get("classes")))
		{
			if ($cfgu->has_properties(array("clid" => $clid)))
			{
				$t->define_data(array(
					"name" => $desc["name"],
					"cfgform" => html::select(array("name" => "cfgform[$clid]","value" => $cs[$clid],"options" => $options)),
				));
			};
		}
		$this->vars(array(
			"table" => $t->draw(),
			"reforb" => $this->mk_reforb("submit_class_cfgforms",array()),
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_class_cfgforms params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_class_cfgforms($args = array())
	{
		extract($args);
		$cs = aw_serialize($cfgform);
		$this->quote($cs);
		$this->set_simple_config("class_cfgforms",$cs);
		return $this->mk_my_orb("class_cfgforms",array());
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
		$this->set_simple_config("bugtrack_developergid",$bt_gid);
		$this->set_simple_config("bugtrack_admgid",$bt_adm);
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
