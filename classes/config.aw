<?php
// $Header: /home/cvs/automatweb_dev/classes/config.aw,v 2.37 2002/11/01 14:34:16 duke Exp $

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
			return;
		};

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
		return aw_unserialize($this->get_simple_config("login_menus"));
	}
};

// urk. orb requires class name to be == file name
class config extends db_config
{
	function config()
	{
		$this->db_config();
	}

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

	////
	// !lets the user set it so that different groups get redirected to diferent pages when logging in
	function grp_redirect($arr)
	{
		$this->read_template("login_grp_redirect.tpl");
		$es = $this->get_simple_config("login_grp_redirect");
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
			"reforb" => $this->mk_reforb("submit_grp_redirect", array())
		));

		return $this->parse();
	}

	function submit_grp_redirect($arr)
	{
		extract($arr);

		$es = $this->get_simple_config("login_grp_redirect");
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
		$this->set_simple_config("login_grp_redirect", $ss);
		return $this->mk_my_orb("grp_redirect", array());
	}

	////
	// !generates the UI for adding aw sites to the server
	function sites($arr)
	{
		extract($arr);
		$this->read_template("add_site.tpl");

		global $status_msg,$add_site_status;
		if (is_array($add_site_status))
		{
			$this->vars($add_site_status);
		}
		session_unregister("add_site_status");
		session_unregister("status_msg");

		$this->vars(array(
			"status_msg" => $status_msg,
			"reforb" => $this->mk_reforb("submit_add_site")
		));
		return $this->parse();
	}

	////
	// !creates the site and checks for errors
	function submit_add_site($arr)
	{
		extract($arr);

		global $status_msg,$add_site_status;

		umask(0);

		$act = "";

		$site_dir = $this->cfg["AW_SITES_basefolder"]."/".$site_folder;	
		if (is_dir($site_dir))
		{
			$status_msg = "A folder with that name ($site_dir) already exists!";
		}
		else
		if (!mkdir($site_dir,0777))
		{
			$status_msg = "Error creating folder $site_dir!";
			$act.="Created folder $site_dir <br>";
		}
		else
		if (!mkdir($site_dir."/img",0777))
		{
			$status_msg = "Error creating folder ".$site_dir."/img !";
			$act.="Created folder $site_dir/img <br>";
		}
		else
		if (!mkdir($site_dir."/pagecache",0777))
		{
			$status_msg = "Error creating folder ".$site_dir."/pagecache !";
			$act.="Created folder $site_dir/pagecache <br>";
		}
		else
		if (!mkdir($site_dir."/public",0777))
		{
			$status_msg = "Error creating folder ".$site_dir."/public !";
			$act.="Created folder $site_dir/public <br>";
		}
		else
		if (!mkdir($site_dir."/templates",0777))
		{
			$status_msg = "Error creating folder ".$site_dir."/templates !";
			$act.="Created folder $site_dir/templates <br>";
		}
		else
		{
			// now create the apache vhost file
			$fc = "<VirtualHost ".$this->cfg["AW_SITES_server_ip"].">\n";
			$fc.= "Servername $site_url\n";
			$fc.= "DocumentRoot ".$site_dir."/public\n";
			$fc.= "ErrorLog /var/log/apache/aw.struktuur.ee/error_log\n";
			$fc.= "CustomLog /var/log/apache/aw.struktuur.ee/access_log common\n";
			$fc.= "</VirtualHost>\n";

			$fp = fopen($this->cfg["AW_SITES_vhost_folder"]."/".$site_url,"w");
			if (!$fp)
			{
				$status_msg = "Could not create apache VirtualHost file (".$this->cfg["AW_SITES_vhost_folder"]."/".$site_url.") ";
			}
			else
			{
				fwrite($fp,$fc);
				fclose($fp);
				$act.="Created apache VirtualHost file <br>";
			}
		}

		if ($status_msg == "")
		{
			// creat the link to automatweb folder
			symlink($this->cfg["AW_SITES_admin_dir"],$site_dir."/public/automatweb");

			// now copy all the files in the public folder to the new site from this site
			$di = opendir($this->cfg["site_basedir"]."/public");
			while (($file = readdir($di)) && $status_msg == "")
			{
				$_file = $this->cfg["site_basedir"]."/public/".$file;
				$dest = $site_dir."/public/".$file;
				if (is_file($_file))
				{
					if (!copy($_file,$dest))
					{
						$status_msg = "Could not copy file $_file to $dest!";
					}
					else
					{
						$act.="Copied $_file to $dest <br>";
					}
				}
			}
		}

		if ($status_msg != "")
		{
			session_register("status_msg","add_site_status");
			$add_site_status = $arr;
			return $this->mk_my_orb("sites");
		}
		else
		{
			echo $act."<br><br>Successfully created site $site_name in folder $site_dir <br>\n";
			die();
		}
	}

	////
	// !lets the user pick which folders to let the users move documents to
	function docfolders($arr)
	{
		extract($arr);
		$this->read_template("docfolders.tpl");

		$langs = get_instance("languages");
		$la = $langs->fetch(aw_global_get("lang_id"));
		$LC=$la["acceptlang"];

		$df = $this->get_simple_config("docfolders".$LC);
		$xml = get_instance("xml");
		$_df = $xml->xml_unserialize(array("source" => $df));

		$ndf = array();

		foreach($_df as $dfid => $dfname)
		{
			$ndf[$dfid] = $dfid;
		}

		$ob = get_instance("objects");
		$this->vars(array(
			"folders" => $this->multiple_option_list($ndf,$ob->get_list(false,false,$this->cfg["rootmenu"])),
			"reforb" => $this->mk_reforb("submit_docfolders",array())
		));
		return $this->parse();
	}

	////
	// !saves the user picked document moving folders
	function submit_docfolders($arr)
	{
		extract($arr);

		$ob = get_instance("objects");
		$obl = $ob->get_list(false,false,$this->cfg["rootmenu"]);

		$_df = array();
		if (is_array($docfolders))
		{
			foreach($docfolders as $dfid)
			{
				$_df[$dfid] = $obl[$dfid];
			}
		}

		$xml = get_instance("xml");
		$df = $xml->xml_serialize($_df);

		$this->quote(&$df);

		$langs = get_instance("languages");
		$la = $langs->fetch(aw_global_get("lang_id"));
		$LC=$la["acceptlang"];

		$this->set_simple_config("docfolders".$LC, $df);

		return $this->mk_my_orb("docfolders");
	}

	function set_menu_icon($arr)
	{
		extract($arr);
		$t = get_instance("menuedit");
		$t->set_menu_icon($id,$icon_id);
		$obj = $t->get_object($id);
		header("Location: ".$t->mk_orb("change", array("id" => $id, "parent" => $obj["parent"])));
		die();
	}

	function show_favicon($arr)
	{
		header("Content-type: image/x-icon");
		die($this->get_simple_config("favicon"));
	}
	
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

		$fb = get_instance("formgen/form_base");
		$ops = $fb->get_op_list($if);

		$us = get_instance("users");

		$this->vars(array(
			"favicon" => "<img src='".$this->mk_my_orb("favicon", array(),"config", false,true)."'>",
			"after_login" => $al,
			"forms" => $this->picker($if,$fb->get_list(FTYPE_ENTRY,true)),
			"ops" => $this->picker($op,$ops[$if]),
			"search_doc" => $this->mk_orb("search_doc", array(),"links"),
			"bt_gid" => $this->picker($bt,$us->get_group_picker(array("type" => array(GRP_REGULAR, GRP_DYNAMIC)))),
			"bt_adm" => $this->picker($btadm,$us->get_group_picker(array("type" => array(GRP_REGULAR, GRP_DYNAMIC)))),
			"mustlogin" => $doc,
			"error_redirect" => $err,
			"reforb" => $this->mk_reforb("submit_loginaddr"),
			"autologin" => checked($al),
			"cfgform_link" => $this->mk_my_orb("class_cfgforms",array()),
		));
		return $this->parse();
	}
	
	////
	// !Kuvab dünaamiliste gruppide nimekirja ja lubab igale login menüü valida
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
				"search" => $this->mk_my_orb("search",array("otype" => CL_PSEUDO,"one" => 1,"return_url" => $return_url),"objects"),
			));

			$c .= $this->parse("LINE");
		};

		$this->vars(array(
			"LINE" => $c,
			"reforb" => $this->mk_reforb("submit_login_menus",array()),
		));
		return $this->parse();
	}

	////
	// !Submitib login_menus funktsioonis tehtud valikud
	function submit_login_menus($args = array())
	{
		extract($args);
		$xml = get_instance("xml");
		$old = aw_unserialize($this->get_simple_config("login_menus"));

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

		$this->set_simple_config("login_menus",$data);

		return $this->mk_my_orb("login_menus",array());
	}


	// see laseb saidile liitumisvormi valida
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
			
		$f = get_instance("formgen/form");
		$ops = $f->get_op_list();
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"form_id"	=> $row["oid"],
				"form_name" 	=> $row["name"],
				"form_comment"	=> $row["comment"],
				"checked" 	=> ($row["subtype"] == FSUBTYPE_JOIN) ? "checked" : "",
				"group"		=> $row["grp"],
				"change"	=> $this->mk_orb("change", array("id" => $row["oid"]), "form"),
				"name"		=> $row["j_name"],
				"order"		=> $row["j_order"],
				"jops"			=> $this->picker($row["j_op"],$ops[$row["oid"]]),
				"jops2"			=> $this->picker($row["j_op2"],$ops[$row["oid"]]),
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

		return $this->parse();
	}

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

	function class_icons()
	{
		$this->mk_path(0,sprintf(LC_CONFIG_CLASS_ICONS,$this->mk_my_orb("config")));

		$this->read_template("admin_class_icons.tpl");

		classload("icons");
		$il = unserialize($this->get_simple_config("menu_icons"));
		reset($this->cfg["classes"]);
		while (list($clid,$desc) = each($this->cfg["classes"]))
		{
			if ($desc["name"])
			{
				$this->vars(array(
					"name" => $desc["name"], 
					"url" => ($il["content"][$clid]["imgurl"] == "" ? "/automatweb/images/icon_aw.gif" : icons::check_url($il["content"][$clid]["imgurl"])),
					"id" => $clid,
					"change" => $this->mk_my_orb("sel_icon", array("rtype" => "class_icon" , "rid" => $clid),"icons")
				));
				$l.=$this->parse("LINE");
			};
		}
		$this->vars(array(
			"LINE" => $l,
			"reforb" => $this->mk_reforb("export_class_icons")
		));
		return $this->parse();
	}
	
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
				
		$options = $this->list_objects(array("class" => CL_CFGFORM, "addempty" => true));
		classload("html");
		
		$cs = aw_unserialize($this->get_simple_config("class_cfgforms"));

		while (list($clid,$desc) = each($this->cfg["classes"]))
		{
			// now I have to check whether this class has any property files
			// or not.
			if (strpos($desc["file"],"/"))
			{
				$fl = substr(strrchr($desc["file"],"/"),1);
			}
			else
			{
				$fl = $desc["file"];
			};
			if ($fl && file_exists($this->cfg["basedir"] . "/xml/properties/$fl.xml"))
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

	function submit_class_cfgforms($args = array())
	{
		extract($args);
		$cs = aw_serialize($cfgform);
		$this->quote($cs);
		$this->set_simple_config("class_cfgforms",$cs);
		return $this->mk_my_orb("class_cfgforms",array());
	}

	function export_class_icons($arr)
	{
		extract($arr);
		if (!is_array($sel))
		{
			return;
		}

		$il = unserialize($this->get_simple_config("menu_icons"));

		header("Content-type: automatweb/icon-export");

		$ic = get_instance("icons");

		reset($sel);
		while (list(,$clid) = each($sel))
		{
			$ret.="\x01classicon\x02\n";
			$icon = $il["content"][$clid]["imgid"] ? $ic->get($il["content"][$clid]["imgid"]) : -1;
			$ar = array("clid" => $clid, "icon" => $icon);
			$ret.=serialize($ar);
		}
		die($ret);
	}

	function export_all_class_icons()
	{
		$ic = get_instance("icons");

		$il = unserialize($this->get_simple_config("menu_icons"));
		reset($this->cfg["classes"]);
		while (list($clid,$desc) = each($this->cfg["classes"]))
		{
			$ret.="\x01classicon\x02\n";
			$icon = $il["content"][$clid]["imgid"] ? $ic->get($il["content"][$clid]["imgid"]) : -1;
			$ar = array("clid" => $clid, "icon" => $icon);
			$ret.=serialize($ar);
		}
		return $ret;
	}

	function import_class_icons($arr)
	{
		extract($arr);
		if (!$level)
		{
			$this->mk_path(0,sprintf(LC_CONFIG_IMPORT_CLASS_ICON,$this->mk_my_orb("config")));
			$this->read_template("import_class_icons.tpl");
			$this->vars(array(
				"reforb" => $this->mk_reforb("import_class_icons", array("level" => 1))
			));
			return $this->parse();
		}
		else
		{
			$this->do_import("classicon",set_class_icon,"class_icons","clid");
			die();
		}
	}

	function set_class_icon($arr,$p2 = false)
	{
		if (is_array($arr))
		{
			extract($arr);
		}
		else
		{
			$id = $arr;
			$icon_id = $p2;
		}

		$t = get_instance("icons");
		$ic = $t->get($icon_id);

		$icons = unserialize($this->get_simple_config("menu_icons"));
		$icons["content"][$id]["imgid"] = $icon_id;
		$icons["content"][$id]["imgurl"] = $ic["url"];

		$cs = serialize($icons);
		$this->set_simple_config("menu_icons",$cs);
		header("Location: ".$this->mk_my_orb("class_icons"));
	}

	function file_icons()
	{
		$this->mk_path(0,LC_CONFIG_FILETYPE_ICONS);

		$this->read_template("file_icons.tpl");

		classload("icons");
		$ar = unserialize($this->get_simple_config("file_icons"));
		if (is_array($ar))
		{
			reset($ar);
			while (list($extt,$v) = each($ar))
			{
				if ($v["url"] == "")
				{
					$v["url"] = $this->cfg["baseurl"]."/images/transa.gif";
				}

				$this->vars(array(
					"extt" => $extt, 
					"type" => $v["type"], 
					"url" => icons::check_url($v["url"]),
					"select" => $this->mk_my_orb("sel_icon", array("rtype" => "file_icon" ,"rid" => $extt),"icons"),
					"change" => $this->mk_my_orb("change_filetype", array("extt" => $extt)),
					"delete" => $this->mk_my_orb("delete_filetype", array("extt" => $extt))
				));
				$this->parse("LINE");
			}
		}
		$this->vars(array(
			"reforb" => $this->mk_reforb("export_file_icons"),
			"add" => $this->mk_my_orb("add_filetype")
		));
		return $this->parse();
	}

	function export_file_icons($arr)
	{
		extract($arr);
		if (!is_array($sel))
		{
			return;
		}

		$il = unserialize($this->get_simple_config("file_icons"));

		header("Content-type: automatweb/icon-export");

		$ic = get_instance("icons");

		reset($sel);
		while (list(,$extt) = each($sel))
		{
			$ret.="\x01fileicon\x02\n";
			$icon = $il[$extt]["id"] ? $ic->get($il[$extt]["id"]) : -1;
			$ar = array("ext" => $extt, "icon" => $icon);
			$ret.=serialize($ar);
		}
		die($ret);
	}

	function export_all_file_icons()
	{
		$il = unserialize($this->get_simple_config("file_icons"));

		$ic = get_instance("icons");

		reset($il);
		while (list($extt,$v) = each($il))
		{
			$ret.="\x01fileicon\x02\n";
			$icon = $il[$extt]["id"] ? $ic->get($il[$extt]["id"]) : -1;
			$ar = array("ext" => $extt, "icon" => $icon);
			$ret.=serialize($ar);
		}
		return $ret;
	}

	function do_import($sep,$callback,$goto,$arname)
	{
		global $fail;
		if (is_uploaded_file($fail))
		{
			if (!($f = fopen($fail,"r")))
			{
				$this->raise_error(ERR_CONFIG_IMPORT,LC_CONFIG_SOMETHING_IS_WRONG,true);
			}
			$fc = fread($f,filesize($fail));
			fclose($f);
		}
		$this->do_core_import($sep,$callback,$goto,$arname,$fc);
	}

	function do_core_import($sep,$callback,$goto,$arname,$fc)
	{
		$ic = get_instance("icons");

		// nyyd asume faili parsima. 
		// splitime ta lahti, eraldajaks on string \x01$sep\x02\n
		$arr = explode("\x01".$sep."\x02\n",$fc);
		reset($arr);
		$cnt = 0;
		list(,$v) = each($arr); // skipime tyhja
		while (list(,$v) = each($arr))
		{
			$v = unserialize($v);
			$iid = 0;
			if (is_array($v["icon"]))
			{
				if (!($iid = $ic->get_icon_by_file($v["icon"]["file"])))
					$iid = $ic->add_array($v["icon"]);
			}
			$this->$callback($v[$arname],$iid);
			$cnt++;
		}
		echo sprintf(LC_CONFIG_ALL_BEEN_IMPORTED,$cnt,$this->mk_my_orb($goto));
	}

	function import_file_icons($arr)
	{
		extract($arr);
		if (!$level)
		{
			$this->mk_path(0,sprintf(LC_CONFIG_IMPORT_FILE_ICONS,$this->mk_my_orb("config")));
			$this->read_template("import_file_icons.tpl");
			$this->vars(array(
				"reforb" => $this->mk_reforb("import_file_icons", array("level" => 1))
			));
			return $this->parse();
		}
		else
		{
			$this->do_import("fileicon",set_file_icon,"file_icons","ext");
			die();
		}
	}

	function add_filetype($arr)
	{
		extract($arr);
		$this->mk_path(0,sprintf(LC_CONFIG_CONFIG_FILETYPE_ICONS,$this->mk_my_orb("config"),$this->mk_my_orb("file_icons")));

		$this->read_template("add_filetype.tpl");
		$this->vars(array(
			"error" => $error,
			"reforb" => $this->mk_reforb("submit_filetype")
		));
		return $this->parse();
	}

	function change_filetype($arr)
	{
		extract($arr);
		$this->mk_path(0,sprintf(LC_CONFIG_CONFIG_FILETYPE_ICONS,$this->mk_my_orb("config"),$this->mk_my_orb("file_icons")));

		$this->read_template("add_filetype.tpl");
		$ar = unserialize($this->get_simple_config("file_icons"));
		$this->vars(array(
			"extt" => $extt,
			"type" => $ar[$extt]["type"],
			"reforb" => $this->mk_reforb("submit_filetype", array("extt" => $extt))
		));
		return $this->parse();
	}

	function submit_filetype($arr)
	{
		extract($arr);

		$ar = unserialize($this->get_simple_config("file_icons"));

		$ar[$extt] = array("type" => $type);

		$this->set_simple_config("file_icons", serialize($ar));
		return $this->mk_my_orb("change_filetype", array("extt" => $extt));
	}

	function set_file_icon($arr)
	{
		extract($arr);
		if ($id == "")
		{
			return;
		}

		$t = get_instance("icons");
		$ic = $t->get($icon_id);

		$icons = unserialize($this->get_simple_config("file_icons"));
		$icons[$id]["url"] = $ic["url"];
		$icons[$id]["id"] = $icon_id;

		$cs = serialize($icons);
		$this->set_simple_config("file_icons",$cs);
		header("Location: ".$this->mk_my_orb("file_icons"));
		die();
	}

	function delete_filetype($arr)
	{
		extract($arr);
		$icons = unserialize($this->get_simple_config("file_icons"));
		unset($icons[$extt]);
		$cs = serialize($icons);
		$this->set_simple_config("file_icons",$cs);
		header("Location: ".$this->mk_my_orb("file_icons"));
		die();
	}

	function program_icons()
	{
		$this->mk_path(0,sprintf(LC_CONFIG_PROGRAMS_ICONS,$this->mk_my_orb("config")));

		$this->read_template("admin_program_icons.tpl");
		classload("icons");

		$il = unserialize($this->get_simple_config("program_icons"));
		reset($this->cfg["programs"]);
		while (list($id,$desc) = each($this->cfg["programs"]))
		{
			$this->vars(array(
				"name" => $desc["name"], 
				"url" => ($il[$id]["url"] == "" ? "/automatweb/images/icon_aw.gif" : icons::check_url($il[$id]["url"])),
				"id" => $id,
				"select" => $this->mk_my_orb("sel_icon", array("rtype" => "program_icon","rid" => $id),"icons")
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"reforb" => $this->mk_reforb("export_program_icons")
		));
		return $this->parse();
	}

	function export_program_icons($arr)
	{
		extract($arr);
		if (!is_array($sel))
		{
			return;
		}

		$il = unserialize($this->get_simple_config("program_icons"));

		header("Content-type: automatweb/icon-export");

		$ic = get_instance("icons");

		reset($sel);
		while (list(,$id) = each($sel))
		{
			$ret.="\x01programicon\x02\n";
			$icon = $il[$id]["id"] ? $ic->get($il[$id]["id"]) : -1;
			$ar = array("id" => $id, "icon" => $icon);
			$ret.=serialize($ar);
		}
		die($ret);
	}

	function export_all_program_icons()
	{
		$il = unserialize($this->get_simple_config("program_icons"));

		header("Content-type: automatweb/icon-export");

		$ic = get_instance("icons");

		reset($this->cfg["programs"]);
		while (list($id,$desc) = each($this->cfg["programs"]))
		{
			$ret.="\x01programicon\x02\n";
			$icon = $il[$id]["id"] ? $ic->get($il[$id]["id"]) : -1;
			$ar = array("id" => $id, "icon" => $icon);
			$ret.=serialize($ar);
		}
		return $ret;
	}

	function import_program_icons($arr)
	{
		extract($arr);
		if (!$level)
		{
			$this->mk_path(0,sprintf(LC_CONFIC_CONFIG_IMPORT_FILE_ICONS,$this->mk_my_orb("config")));
			$this->read_template("import_program_icons.tpl");
			$this->vars(array(
				"reforb" => $this->mk_reforb("import_program_icons", array("level" => 1))
			));
			return $this->parse();
		}
		else
		{
			$this->do_import("programicon",set_program_icon,"program_icons","id");
			die();
		}
	}

	function set_program_icon($arr)
	{
		extract($arr);
		$t = get_instance("icons");
		$ic = $t->get($icon_id);

		$icons = unserialize($this->get_simple_config("program_icons"));
		$icons[$id]["id"] = $icon_id;
		$icons[$id]["url"] = $ic["url"];

		$this->db_query("UPDATE menu SET icon_id = '$icon_id' WHERE admin_feature = '$id'");

		$cs = serialize($icons);
		$this->set_simple_config("program_icons",$cs);
		header("Location: ".$this->mk_my_orb("program_icons"));
	}

	function other_icons()
	{
		$this->mk_path(0,sprintf(LC_CONFIG_ELSE_ICONS,$this->mk_my_orb("config")));

		$this->read_template("other_icons.tpl");

		$ar = unserialize($this->get_simple_config("other_icons"));
		$v = $ar["promo_box"];
		if ($v["url"] == "")
		{
			$v["url"] = $this->cfg["baseurl"]."/images/transa.gif";
		}

		classload("icons");
		$this->vars(array(
			"type" => LC_CONFIG_PROMO_BOX, 
			"mtype" => "promo_box", 
			"url" => icons::check_url($v["url"]),
			"select" => $this->mk_my_orb("sel_icon", array("rtype" => "other_icon","rid" => "promo_box"),"icons")
		));
		$this->parse("LINE");

		$v = $ar["brother"];
		if ($v["url"] == "")
		{
			$v["url"] = $this->cfg["baseurl"]."/images/transa.gif";
		}

		$this->vars(array(
			"type" => LC_CONFIG_BROD_MENY, 
			"mtype" => "brother", 
			"url" => icons::check_url($v["url"]),
			"select" => $this->mk_my_orb("sel_icon", array("rtype" => "other_icon","rid" => "brother"),"icons")
		));
		$this->parse("LINE");

		$v = $ar["homefolder"];
		if ($v["url"] == "")
		{
			$v["url"] = $this->cfg["baseurl"]."/images/transa.gif";
		}
		$this->vars(array(
			"type" => LC_CONFIG_HOME_CAT, 
			"mtype" => "homefolder", 
			"url" => icons::check_url($v["url"]),
			"select" => $this->mk_my_orb("sel_icon", array("rtype" => "other_icon","rid" => "homefolder"),"icons")
		));
		$this->parse("LINE");

		$v = $ar["shared_folders"];
		if ($v["url"] == "")
		{
			$v["url"] = $this->cfg["baseurl"]."/images/transa.gif";
		}

		$this->vars(array(
			"type" => LC_CONFIG_HOME_CAT_SHAR_FOL, 
			"mtype" => "shared_folders", 
			"url" => icons::check_url($v["url"]),
			"select" => $this->mk_my_orb("sel_icon", array("rtype" => "other_icon","rid" => "shared_folders"),"icons")
		));
		$this->parse("LINE");

		$v = $ar["hf_groups"];
		if ($v["url"] == "")
		{
			$v["url"] = $this->cfg["baseurl"]."/images/transa.gif";
		}

		$this->vars(array(
			"type" => LC_CONFIG_HOMECATALOG_GROUPS, 
			"mtype" => "hf_groups", 
			"url" => icons::check_url($v["url"]),
			"select" => $this->mk_my_orb("sel_icon", array("rtype" => "other_icon","rid" => "hf_groups"),"icons")
		));
		$this->parse("LINE");

		$this->vars(array(
			"reforb" => $this->mk_reforb("export_other_icons")
		));
		return $this->parse();
	}

	function set_other_icon($arr)
	{
		extract($arr);
		$t = get_instance("icons");
		$ic = $t->get($icon_id);

		$icons = unserialize($this->get_simple_config("other_icons"));
		$icons[$id]["id"] = $icon_id;
		$icons[$id]["url"] = $ic["url"];

		$cs = serialize($icons);
		$this->set_simple_config("other_icons",$cs);
		header("Location: ".$this->mk_my_orb("other_icons"));
	}

	function export_other_icons($arr)
	{
		extract($arr);
		if (!is_array($sel))
		{
			return;
		}

		$il = unserialize($this->get_simple_config("other_icons"));

		header("Content-type: automatweb/icon-export");

		$ic = get_instance("icons");

		reset($sel);
		while (list(,$id) = each($sel))
		{
			$ret.="\x01othericon\x02\n";
			$icon = $il[$id]["id"] ? $ic->get($il[$id]["id"]) : -1;
			$ar = array("id" => $id, "icon" => $icon);
			$ret.= serialize($ar);
		}
		die($ret);
	}

	function export_all_other_icons()
	{
		$il = unserialize($this->get_simple_config("other_icons"));

		header("Content-type: automatweb/icon-export");

		$ic = get_instance("icons");

		reset($il);
		while (list($id,) = each($il))
		{
			$ret.="\x01othericon\x02\n";
			$icon = $il[$id]["id"] ? $ic->get($il[$id]["id"]) : -1;
			$ar = array("id" => $id, "icon" => $icon);
			$ret.= serialize($ar);
		}
		return $ret;
	}

	function import_other_icons($arr)
	{
		extract($arr);
		if (!$level)
		{
			$this->mk_path(0,sprintf(LC_CONFIG_IMPORT_ELSE_ICONS,$this->mk_my_orb("config")));
			$this->read_template("import_other_icons.tpl");
			$this->vars(array(
				"reforb" => $this->mk_reforb("import_other_icons", array("level" => 1))
			));
			return $this->parse();
		}
		else
		{
			global $fail;
			if (is_uploaded_file($fail))
			{
				if (!($f = fopen($fail,"r")))
				{
					$this->raise_error(ERR_CONFIG_IMPORT,LC_CONFIG_SOMETHING_IS_WRONG,true);
				}
				$fc = fread($f,filesize($fail));
				fclose($f);
			}

			$this->do_core_import_other_icons($fc);
			die();
		}
	}

	function do_core_import_other_icons($fc)
	{
		$ic = get_instance("icons");

		// nyyd asume faili parsima. 
		// splitime ta lahti, eraldajaks on string \x01$sep\x02\n
		$arr = explode("\x01othericon\x02\n",$fc);
		reset($arr);
		$cnt = 0;
		list(,$v) = each($arr); // skipime tyhja
		while (list(,$v) = each($arr))
		{
			$v = unserialize($v);
			$iid = 0;
			if (is_array($v["icon"]))
			{
				if (!($iid = $ic->get_icon_by_file($v["icon"]["file"])))
				{
					$iid = $ic->add_array($v["icon"]);
				}
			}
			$this->set_other_icon($v["id"],$iid);
			$cnt++;
		}
		echo "Importisin $cnt ikooni. <a href='".$this->mk_my_orb("other_icons")."'>Tagasi</a><br>";
	}

	function import()
	{
		$this->read_template("import.tpl");
		return $this->parse();
	}

	function export()
	{
		$this->read_template("export.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("exp_icons")
		));
		return $this->parse();
	}

	function do_export($arr)
	{
		extract($arr);

		$ex = array();

		if ($exp_db)
		{
			$t = get_instance("icons");
			$ex["db"] = $t->export_all();
		}

		if ($exp_cl)
		{
			$ex["cl"] = $this->export_all_class_icons();
		}

		if ($exp_ft)
		{
			$ex["ft"] = $this->export_all_file_icons();
		}

		if ($exp_pr)
		{
			$ex["pr"] = $this->export_all_program_icons();
		}

		if ($exp_ot)
		{
			$ex["ot"] = $this->export_all_other_icons();
		}

		header("Content-type: aw/icon-export");
		die(serialize($ex));
	}

	function import_all_icons($arr)
	{
		extract($arr);
		if (!$level)
		{
			$this->mk_path(0,sprintf(LC_CONFIG_IMPORT_ALL_ICONS,$this->mk_my_orb("config")));
			$this->read_template("import_all_icons.tpl");
			$this->vars(array(
				"reforb" => $this->mk_reforb("import_all_icons",array("level" => 1))
			));
			return $this->parse();
		}
		else
		{
			global $fail;
			if (is_uploaded_file($fail))
			{
				if (!($f = fopen($fail,"r")))
				{
					$this->raise_error(ERR_CONFIG_IMPORT,LC_CONFIG_SOMETHING_IS_WRONG,true);
				}

				$fc = fread($f,filesize($fail));
				fclose($f);
			}

			$ar = unserialize($fc);
			if ($ar["db"] != "")
			{
				$t = get_instance("icons");
				$t->core_import($ar["db"]);
			}
			if ($ar["cl"] != "")
			{
				$this->do_core_import("classicon",set_class_icon,"class_icons","clid",$ar["cl"]);
			}
			if ($ar["ft"] != "")
			{
				$this->do_core_import("fileicon",set_file_icon,"file_icons","ext",$ar["ft"]);
			}
			if ($ar["pr"] != "")
			{
				$this->do_core_import("programicon",set_program_icon,"program_icons","id",$ar["pr"]);
			}
			if ($ar["ot"] != "")
			{
				$this->do_core_import_other_icons($ar["ot"]);
			}
			die();
		}
	}

	// see laseb kontakti formi valida
	function sel_contact_form()
	{
		$this->mk_path(0,sprintf(LC_CONFIG_CHOOSE_CONTACT_INPUT_FORM,"config.".$this->cfg["ext"]));

		$this->read_template("sel_form.tpl");
		
		$cf = $this->get_simple_config("contact_form");

		$fb = get_instance("formgen/form_base");
		$fa = $fb->get_list(FTYPE_ENTRY);
		reset($fa);
		while (list($fid,$name) = each($fa))
		{
			$this->vars(array(
				"form_id"	=> $row["oid"],
				"form_name" 	=> $row["name"],
				"checked" 	=> checked($cf == $row["oid"])
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l, "SEL_LINE" => ""));

		return $this->parse();
	}

	function submit_loaginaddr($arr)
	{
		extract($arr);
		$this->set_simple_config("after_login",$after_login);
		$this->set_simple_config("user_info_form",$user_info_form);
		$this->set_simple_config("user_info_op",$user_info_op);
		$this->set_simple_config("orb_err_mustlogin",$mustlogin);
		$this->set_simple_config("error_redirect",$error_redirect);
		$this->set_simple_config("bugtrack_developergid",$bt_gid);
		$this->set_simple_config("bugtrack_admgid",$bt_adm);
		$this->set_simple_config("useradd::autologin",$autologin);

		// if favicon was uploaded, handle it. 
		global $favicon;
		if (is_uploaded_file($favicon))
		{
			$f = fopen($favicon,"r");
			$fc = fread($f,filesize($favicon));
			fclose($f);
			$this->quote(&$fc);
			$this->set_simple_config("favicon", $fc);
		}
		return $this->mk_my_orb("config");
	}

}
?>
