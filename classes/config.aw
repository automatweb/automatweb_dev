<?php
// $Header: /home/cvs/automatweb_dev/classes/config.aw,v 2.15 2001/08/02 01:15:51 duke Exp $

global $orb_defs;
$orb_defs["config"] = "xml";

class db_config extends aw_template 
{
	function db_config() 
	{
		$this->db_init();
		$this->tpl_init("automatweb/config");
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
		$this->db_query("INSERT INTO config VALUES('$ckey','$value',".time().",'".$GLOBALS["uid"]."')");
	}

	function gen_config()
	{
		$this->mk_path(0,LC_CONFIG_SITE);
		$this->read_template("config.tpl");
	
		$al = $this->get_simple_config("after_login");
		$ml = $this->get_simple_config("orb_err_mustlogin");

		$this->vars(array(
			"after_login" => $al,
			"orb_err_mustlogin" => $ml,
			"search_doc" => $this->mk_orb("search_doc", array(),"links")
		));
		return $this->parse();
	}

	////
	// !Kuvab dünaamiliste gruppide nimekirja ja lubab igale login menüü valida
	function login_menus($args = array())
	{
		$this->read_template("login_menu.tpl");

		if (not(defined("LOGIN_MENUS")))
		{
			$this->raise_error("LOGIN_MENUS on defineerimata.",true);
		};

		$obj = $this->get_objects_below(array("parent" => LOGIN_MENUS,"class" => CL_PSEUDO));

		$menus = array();

		foreach($obj as $key => $val)
		{
			$menus[$key] = $val["name"];
		};

		classload("users_user");
		$u = new users_user();
		$u->listgroups(-1,-1,-1,-1,0);
		$c = "";

		while($row = $u->db_next())
		{
			$this->vars(array(
				"gid" => $row["gid"],
				"group" => $row["name"],
				"menus" => $this->picker($row["login_menu"],$menus),
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
		// $menu on array, key on gid, value on login menüü, mida see grupp kasutama peaks
		if (is_array($menu))
		{
			foreach($menu as $key => $val)
			{
				$q = "UPDATE groups SET login_menu = '$val' WHERE gid = '$key'";
				$this->db_query($q);
			};
		}
		return $this->mk_my_orb("login_menus",array());
	}


	// see laseb saidile liitumisvormi valida
	function sel_join_form()
	{
		global $ext;
		$this->mk_path(0,sprintf(LC_CONFIG_CHOOSE_USER,config.$ext));

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
					forms.j_op2 AS j_op2
				FROM forms
				LEFT JOIN objects ON objects.oid = forms.id
				WHERE objects.status != 0 AND forms.type = ".FTYPE_ENTRY);
			
		classload("form");
		$f = new form;
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
				"jops2"			=> $this->picker($row["j_op2"],$ops[$row["oid"]])
			));
			if ($row["subtype"] == FSUBTYPE_JOIN)
			{
				$this->vars(array(
					"GROUP" => $this->parse("GROUP"),
					"ORDER" => $this->parse("ORDER"),
					"NAME"	=> $this->parse("NAME"),
					"OPS"	=> $this->parse("OPS"),
					"OPS2"	=> $this->parse("OPS2"),
				));
			}
			else
			{
				$this->vars(array("GROUP" => "","NAME" => "", "ORDER" => "","OPS" => "","OPS2" => ""));
			}
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l, "SEL_LINE" => ""));

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
				$q = sprintf("UPDATE forms SET subtype = '%s', grp = '%s',j_name = '%s', j_order='%s', j_op = '%s',j_op2 = '%s' WHERE id = $fid",
					FSUBTYPE_JOIN,$fg[$fid],$fn[$fid],$fo[$fid],$fp[$fid],$fp2[$fid]);
				$this->db_query($q);
			}
		}
	}

	function sel_search_form()
	{
		global $ext;
		$this->mk_path(0,sprintf(LC_CONFIG_CHOOSE_USER_FORM,config.$ext));

		$this->read_template("sel_form.tpl");
		
		$this->gen_form_list("sel_search",$this->get_simple_config("user_search_form"),2);

		return $this->parse();
	}

	function sel_search($id)
	{
		$this->set_simple_config("user_search_form",$id);
	}

	function class_icons()
	{
		global $ext;
		$this->mk_path(0,sprintf(LC_CONFIG_CLASS_ICONS,config.$ext));

		global $class_defs;
		$this->read_template("admin_class_icons.tpl");

		$il = unserialize($this->get_simple_config("menu_icons"));
		reset($class_defs);
		while (list($clid,$desc) = each($class_defs))
		{
			$this->vars(array("name" => $desc["name"], "url" => ($il["content"][$clid]["imgurl"] == "" ? "/images/icon_aw.gif" : $il["content"][$clid]["imgurl"]),"id" => $clid));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l));
		return $this->parse();
	}

	function export_class_icons($arr)
	{
		extract($arr);
		if (!is_array($sel))
			return;

		$il = unserialize($this->get_simple_config("menu_icons"));

		header("Content-type: automatweb/icon-export");

		classload("icons");
		$ic = new icons;

		reset($sel);
		while (list(,$clid) = each($sel))
		{
			$ret.="\x01classicon\x02\n";
			$icon = $il["content"][$clid]["imgid"] ? $ic->get($il["content"][$clid]["imgid"]) : -1;
			$ar = array("clid" => $clid, "icon" => $icon);
			$ret.=serialize($ar);
		}
		return $ret;
	}

	function export_all_class_icons()
	{
		classload("icons");
		$ic = new icons;

		$il = unserialize($this->get_simple_config("menu_icons"));
		global $class_defs;
		reset($class_defs);
		while (list($clid,$desc) = each($class_defs))
		{
			$ret.="\x01classicon\x02\n";
			$icon = $il["content"][$clid]["imgid"] ? $ic->get($il["content"][$clid]["imgid"]) : -1;
			$ar = array("clid" => $clid, "icon" => $icon);
			$ret.=serialize($ar);
		}
		return $ret;
	}

	function import_class_icons($level)
	{
		if (!$level)
		{
			global $ext;
			$this->mk_path(0,sprintf(LC_CONFIG_IMPORT_CLASS_ICON,config.$ext));
			$this->read_template("import_class_icons.tpl");
			return $this->parse();
		}
		else
		{
			$this->do_import("classicon",set_class_icon,"class_icons","clid");
		}
	}

	function set_class_icon($clid, $icon_id)
	{
		$t = new icons;
		$ic = $t->get($icon_id);

		$icons = unserialize($this->get_simple_config("menu_icons"));
		$icons["content"][$clid]["imgid"] = $icon_id;
		$icons["content"][$clid]["imgurl"] = $ic["url"];

		$cs = serialize($icons);
		$this->set_simple_config("menu_icons",$cs);
	}

	function file_icons()
	{
		global $ext;
		$this->mk_path(0,LC_CONFIG_FILETYPE_ICONS);

		$this->read_template("file_icons.tpl");

		$ar = unserialize($this->get_simple_config("file_icons"));
		if (is_array($ar))
		{
			reset($ar);
			while (list($extt,$v) = each($ar))
			{
				if ($v["url"] == "")
					$v["url"] = $GLOBALS["baseurl"]."/images/transa.gif";

				$this->vars(array("extt" => $extt, "type" => $v["type"], "url" => $v["url"]));
				$this->parse("LINE");
			}
		}
		return $this->parse();
	}

	function export_file_icons($arr)
	{
		extract($arr);
		if (!is_array($sel))
			return;

		$il = unserialize($this->get_simple_config("file_icons"));

		header("Content-type: automatweb/icon-export");

		classload("icons");
		$ic = new icons;

		reset($sel);
		while (list(,$extt) = each($sel))
		{
			$ret.="\x01fileicon\x02\n";
			$icon = $il[$extt]["id"] ? $ic->get($il[$extt]["id"]) : -1;
			$ar = array("ext" => $extt, "icon" => $icon);
			$ret.=serialize($ar);
		}
		return $ret;
	}

	function export_all_file_icons()
	{
		$il = unserialize($this->get_simple_config("file_icons"));

		classload("icons");
		$ic = new icons;

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
		if (!($f = fopen($fail,"r")))
		{
			$this->raise_error(LC_CONFIG_SOMETHING_IS_WRONG,true);
		}

		$fc = fread($f,filesize($fail));
		fclose($f);

		$this->do_core_import($sep,$callback,$goto,$arname,$fc);
	}

	function do_core_import($sep,$callback,$goto,$arname,$fc)
	{
		classload("icons");
		$ic = new icons;

		// nyyd asume faili parsima. 
		// splitime ta lahti, eraldajaks on string \x01$sep\x02\n
		$arr = explode("\x01".$sep."\x02\n",$fc);
		reset($arr);
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
		global $ext;
		echo sprintf(LC_CONFIG_ALL_BEEN_IMPORTED,$cnt,"config.$ext?type=$goto");
	}

	function import_file_icons($level)
	{
		if (!$level)
		{
			global $ext;
			$this->mk_path(0,sprintf(LC_CONFIG_IMPORT_FILE_ICONS,config.$ext));
			$this->read_template("import_file_icons.tpl");
			return $this->parse();
		}
		else
		{
			$this->do_import("fileicon",set_file_icon,"file_icons","ext");
		}
	}

	function add_filetype($error)
	{
		global $ext;
		$this->mk_path(0,sprintf(LC_CONFIG_CONFIG_FILETYPE_ICONS,"config.$ext","config.$ext?type=file_icons"));

		$this->read_template("add_filetype.tpl");
		$this->vars(array("error" => $error));
		return $this->parse();
	}

	function change_filetype($extt)
	{
		global $ext;
		$this->mk_path(0,sprintf(LC_CONFIG_CONFIG_FILETYPE_ICONS,"config.$ext","config.$ext?type=file_icons"));

		$this->read_template("add_filetype.tpl");
		$ar = unserialize($this->get_simple_config("file_icons"));
		$this->vars(array("change" => 1,"extt" => $extt,"type" => $ar[$extt][type]));
		return $this->parse();
	}

	function submit_filetype($arr)
	{
		extract($arr);

		$ar = unserialize($this->get_simple_config("file_icons"));
		if (is_array($ar[$extt]) && $change != 1)
		{
			header("Location: conf.".$GLOBALS["ext"]."?type=add_filetype&error=Selline+t&uuml;&uuml;p+on+juba+andembaasis+olemas!");
			die();
		}

		$ar[$extt] = array("type" => $type);

		$this->set_simple_config("file_icons", serialize($ar));
	}

	function set_file_icon($extt, $icon_id)
	{
		if ($extt == "")
			return;

		$t = new icons;
		$ic = $t->get($icon_id);

		$icons = unserialize($this->get_simple_config("file_icons"));
		$icons[$extt]["url"] = $ic["url"];
		$icons[$extt]["id"] = $icon_id;

		$cs = serialize($icons);
		$this->set_simple_config("file_icons",$cs);
	}

	function delete_filetype($extt)
	{
		$icons = unserialize($this->get_simple_config("file_icons"));
		unset($icons[$extt]);
		$cs = serialize($icons);
		$this->set_simple_config("file_icons",$cs);
	}

	function program_icons()
	{
		global $ext;
		$this->mk_path(0,sprintf(LC_CONFIG_PROGRAMS_ICONS,"config.$ext"));

		global $programs;
		$this->read_template("admin_program_icons.tpl");

		$il = unserialize($this->get_simple_config("program_icons"));
		reset($programs);
		while (list($id,$desc) = each($programs))
		{
			$this->vars(array("name" => $desc["name"], "url" => ($il[$id]["url"] == "" ? "/images/icon_aw.gif" : $il[$id]["url"]),"id" => $id));
			$l.=$this->parse("LINE");
		}
		$this->vars(array("LINE" => $l));
		return $this->parse();
	}

	function export_program_icons($arr)
	{
		extract($arr);
		if (!is_array($sel))
			return;

		$il = unserialize($this->get_simple_config("program_icons"));

		header("Content-type: automatweb/icon-export");

		classload("icons");
		$ic = new icons;

		reset($sel);
		while (list(,$id) = each($sel))
		{
			$ret.="\x01programicon\x02\n";
			$icon = $il[$id]["id"] ? $ic->get($il[$id]["id"]) : -1;
			$ar = array("id" => $id, "icon" => $icon);
			$ret.=serialize($ar);
		}
		return $ret;
	}

	function export_all_program_icons()
	{
		$il = unserialize($this->get_simple_config("program_icons"));

		header("Content-type: automatweb/icon-export");

		classload("icons");
		$ic = new icons;

		global $programs;
		reset($programs);
		while (list($id,$desc) = each($programs))
		{
			$ret.="\x01programicon\x02\n";
			$icon = $il[$id]["id"] ? $ic->get($il[$id]["id"]) : -1;
			$ar = array("id" => $id, "icon" => $icon);
			$ret.=serialize($ar);
		}
		return $ret;
	}

	function import_program_icons($level)
	{
		if (!$level)
		{
			global $ext;
			$this->mk_path(0,sprintf(LC_CONFIC_CONFIG_IMPORT_FILE_ICONS,config.$ext));
			$this->read_template("import_program_icons.tpl");
			return $this->parse();
		}
		else
		{
			$this->do_import("programicon",set_program_icon,"program_icons","id");
		}
	}

	function set_program_icon($id, $icon_id)
	{
		$t = new icons;
		$ic = $t->get($icon_id);

		$icons = unserialize($this->get_simple_config("program_icons"));
		$icons[$id]["id"] = $icon_id;
		$icons[$id]["url"] = $ic["url"];

		$this->db_query("UPDATE menu SET icon_id = '$icon_id' WHERE admin_feature = '$id'");

		$cs = serialize($icons);
		$this->set_simple_config("program_icons",$cs);
	}

	function other_icons()
	{
		global $ext;
		$this->mk_path(0,sprintf(LC_CONFIG_ELSE_ICONS,config.$ext));

		$this->read_template("other_icons.tpl");

		$ar = unserialize($this->get_simple_config("other_icons"));
		$v = $ar["promo_box"];
		if ($v["url"] == "")
			$v["url"] = $GLOBALS["baseurl"]."/images/transa.gif";

		$this->vars(array("type" => LC_CONFIG_PROMO_BOX, "mtype" => "promo_box", "url" => $v["url"]));
		$this->parse("LINE");

		$v = $ar["brother"];
		if ($v["url"] == "")
			$v["url"] = $GLOBALS["baseurl"]."/images/transa.gif";

		$this->vars(array("type" => LC_CONFIG_BROD_MENY, "mtype" => "brother", "url" => $v["url"]));
		$this->parse("LINE");

		$v = $ar["homefolder"];
		if ($v["url"] == "")
			$v["url"] = $GLOBALS["baseurl"]."/images/transa.gif";
		$this->vars(array("type" => LC_CONFIG_HOME_CAT, "mtype" => "homefolder", "url" => $v["url"]));
		$this->parse("LINE");

		$v = $ar["shared_folders"];
		if ($v["url"] == "")
			$v["url"] = $GLOBALS["baseurl"]."/images/transa.gif";

		$this->vars(array("type" => LC_CONFIG_HOME_CAT_SHAR_FOL, "mtype" => "shared_folders", "url" => $v["url"]));
		$this->parse("LINE");

		$v = $ar["hf_groups"];
		if ($v["url"] == "")
			$v["url"] = $GLOBALS["baseurl"]."/images/transa.gif";

		$this->vars(array("type" => LC_CONFIG_HOMECATALOG_GROUPS, "mtype" => "hf_groups", "url" => $v["url"]));
		$this->parse("LINE");


		return $this->parse();
	}

	function set_other_icon($id, $icon_id)
	{
		$t = new icons;
		$ic = $t->get($icon_id);

		$icons = unserialize($this->get_simple_config("other_icons"));
		$icons[$id]["id"] = $icon_id;
		$icons[$id]["url"] = $ic["url"];

		$cs = serialize($icons);
		$this->set_simple_config("other_icons",$cs);
	}

	function export_other_icons($arr)
	{
		extract($arr);
		if (!is_array($sel))
			return;

		$il = unserialize($this->get_simple_config("other_icons"));

		header("Content-type: automatweb/icon-export");

		classload("icons");
		$ic = new icons;

		reset($sel);
		while (list(,$id) = each($sel))
		{
			$ret.="\x01othericon\x02\n";
			$icon = $il[$id]["id"] ? $ic->get($il[$id]["id"]) : -1;
			$ar = array("id" => $id, "icon" => $icon);
			$ret.= serialize($ar);
		}
		return $ret;
	}

	function export_all_other_icons()
	{
		$il = unserialize($this->get_simple_config("other_icons"));

		header("Content-type: automatweb/icon-export");

		classload("icons");
		$ic = new icons;

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

	function import_other_icons($level)
	{
		if (!$level)
		{
			global $ext;
			$this->mk_path(0,sprintf(LC_CONFIG_IMPORT_ELSE_ICONS,config.$ext));
			$this->read_template("import_other_icons.tpl");
			return $this->parse();
		}
		else
		{
			global $fail;
			if (!($f = fopen($fail,"r")))
				$this->raise_error(LC_CONFIG_SOMETHING_IS_WRONG,true);

			$fc = fread($f,filesize($fail));
			fclose($f);

			$this->do_core_import_other_icons($fc);
		}
	}

	function do_core_import_other_icons($fc)
	{
		classload("icons");
		$ic = new icons;

		// nyyd asume faili parsima. 
		// splitime ta lahti, eraldajaks on string \x01$sep\x02\n
		$arr = explode("\x01othericon\x02\n",$fc);
		reset($arr);
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
			$this->set_other_icon($v["id"],$iid);
			$cnt++;
		}
		global $ext;
		echo "Importisin $cnt ikooni. <a href='config.aw?type=other_icons'>Tagasi</a><br>";
	}

	function import()
	{
		$this->read_template("import.tpl");
		return $this->parse();
	}

	function export()
	{
		$this->read_template("export.tpl");
		return $this->parse();
	}

	function do_export($arr)
	{
		extract($arr);

		$ex = array();

		if ($exp_db)
		{
			classload("icons");
			$t = new icons;
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
		return serialize($ex);
	}

	function import_all_icons($level)
	{
		if (!$level)
		{
			global $ext;
			$this->mk_path(0,sprintf(LC_CONFIG_IMPORT_ALL_ICONS,config.$ext));
			$this->read_template("import_all_icons.tpl");
			return $this->parse();
		}
		else
		{
			global $fail;
			if (!($f = fopen($fail,"r")))
				$this->raise_error(LC_CONFIG_SOMETHING_IS_WRONG,true);

			$fc = fread($f,filesize($fail));
			fclose($f);

			$ar = unserialize($fc);
			if ($ar["db"] != "")
			{
				classload("icons");
				$t = new icons;
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
		}
	}

	// see laseb kontakti formi valida
	function sel_contact_form()
	{
		global $ext;
		$this->mk_path(0,sprintf(LC_CONFIG_CHOOSE_CONTACT_INPUT_FORM,config.$ext));

		$this->read_template("sel_form.tpl");
		
		$cf = $this->get_simple_config("contact_form");

		classload("form_base");
		$fb = new form_base;
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
		$this->set_simple_config("orb_err_mustlogin",$orb_err_mustlogin);
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

		$this->vars(array(
			"join_mail" => $this->get_simple_config("join_mail"),
			"pwd_mail" => $this->get_simple_config("remind_pwd_mail"),
			"join_mail_subj" => $this->get_simple_config("join_mail_subj"),
			"pwd_mail_subj" => $this->get_simple_config("remind_pwd_mail_subj"),
			"reforb" => $this->mk_reforb("submit_join_mail", array())
		));

		return $this->parse();
	}

	function submit_join_mail($arr)
	{
		extract($arr);

		$this->set_simple_config("join_mail",$join_mail);
		$this->set_simple_config("remind_pwd_mail",$pwd_mail);
		$this->set_simple_config("join_mail_subj",$join_mail_subj);
		$this->set_simple_config("remind_pwd_mail_subj",$pwd_mail_subj);

		return $this->mk_orb("join_mail", array());
	}
}
?>
