<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/css.aw,v 2.11 2002/02/01 11:15:29 duke Exp $
// css.aw - CSS (Cascaded Style Sheets) haldus
// I decided to make it a separate class, because I think the style.aw 
// class is too cluttered.

// General idea on selline:
// Saidi juures / Autom@tWeb-is on defineeritud mingit kindlaksmääratud nimedega CSS stiilid,
// css_list kuvab nende nimekirja, ning lubab sul igale stiilile vastavusse mõne enda defineeritud
// stiili.

// Ja üldiselt peaks see jagunema kaheks, peaksid olema süsteemi stiilid, ning "Minu stiilid"...
// systeemi stiile kasutatakse by default, kuid, kui kasutajal on need endal defineeritud, siis
// kasutame neid.

// Stiilifaile peab saama ka importida/exportida

// Tööle hakkab see nii, et tehakse cachesse fail, mis siis html-i alguses sisse loetakse

// default stiilid loetakse koigepealt sisse, seejärel overraiditakse need custom stylesheediga.

// font-family: serif|sans-serif|monospace|cursive
// font-style: normal|italic
// font-weight: normal|bold|bolder
// font-size: XX px|pt|in|em|cm|mm
// color: #RRGGBB
// background: #RRGGBB

// stiilid jaotuvad gruppidesse, igas grupis voib olla iga systeemse stiili jaoks
// oma custom stiili defineeritud.

// lisaks on kasutajatel olemas oma grupid, mille elemente saab jagada teiste kasutajatega, 
// ja nii edasi ja nii tagasi
lc_load("css");
global $orb_defs;
$orb_defs["css"] = "xml";

class css extends aw_template {
	function css ($args = array())
	{
		$this->db_init();
		// kuidas ma seda edimisvormi alati automawebi juurest lugeda saan?
		$this->tpl_init("css");
		global $lc_css;
		if (is_array($lc_css))
		{
			$this->vars($lc_css);}
		// fondifamilyd, do not change the order
		$this->font_families = array(
				"0" => "Verdana,Helvetica,sans-serif",
				"1" => "Arial,Helvetica,sans-serif",
				"2" => "Tahoma,sans-serif",
				"3" => "serif",
				"4" => "sans-serif",
				"5" => "monospace",
				"6" => "cursive",
				"7" => "Trebuchet MS,Tahoma,sans-serif"
		);

		$this->units = array(
				"px" => "pikslit",
				"pt" => "punkti (1pt=1/72in)",
				"in" => "tolli",
				"em" => "ems (suhteline)",
				"cm" => "sentimeetrit",
				"mm" => "millimeetrit",
		);

		// siin on süsteemsed stiilid defineeritud.
		$this->styles = array(
			"0" => "ftitle",
			"1" => "fcaption",
			"2" => "title",
			"3" => "plain",
			"4" => "header1",
			"5" => "fgtitle",
			"6" => "fgtext",
		);

		// siin peaks diferentseerima selle, kas tegu on süsteemste voi kasutaja
		// stiilidega.
		global $udata;
		$this->rootmenu = $udata["home_folder"];


	}

	////
	// !Joonistab Editori menüü
	function css_draw_menu($args = array())
	{
		extract($args);
		global $basedir,$ext;
		load_vcl("xmlmenu");
		$xm = new xmlmenu();
		$retval = $xm->build_menu(array(
				"vars" => array_merge($vars,array("ext" => $ext)),
				"xml" => $basedir . "/xml/css_editor.xml",
				"tpl" => $this->template_dir . "/menus.tpl",
				"activelist" => $activelist,
			));
		return $retval;
	}

	////
	// !Küsib nime uue stiili jaoks, mis menueditist sisestati
	function css_new($args = array())
	{
		extract($args);
		$this->read_template("add.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_new",array("parent" => $parent)),
		));
		return $this->parse();
	}

	function css_submit_new($args = array())
	{
		$this->quote($args);
		extract($args);
		$id = $this->new_object(array("parent" => $parent,"name" => $name,"class_id" => CL_CSS));
		return $this->mk_orb("change",array("id" => $id));
	}

	////
	// !Kuvab süsteemsete ja kasutaja stiiligruppde nimekirja
	// lubab sealt valida ühe, ning selle kasutusele võtta
	function css_active_list($args = array())
	{
		$menu = $this->css_draw_menu(array(
				"activelist" => array("list")
				));

		classload("users");
		$u = new users();
		$group_in_user = $u->get_user_config(array(
						"uid" => UID,
						"key" => "automatweb_css_group",
				));


		if (!$group_in_use)
		{
			$group_in_use = "default";
		};

		$this->read_template("grouplist.tpl");
		$lx = array(
			"0" => "default",
		);

		// koigepealt kuvame stiiligrupid, mis on süsteemi all
		global $rootmenu;
		// ja seejärel need, mis on kasutaja all
		$groups = $this->get_objects_below(array(
				//"parent" => $this->rootmenu,
				"parent" => $rootmenu,
				"class" => CL_CSS_GROUP,
				"active" => $active,
		));

		foreach($groups as $key => $val)
		{
			$lx[$key] = $val["name"];
		}

		$c = "";
		foreach($lx as $key => $nam)
		{
			$cnt++;
			$this->vars(array(
				"cnt" => $cnt,
				"gname" => $nam,
				"gid" => $key,
				"checked" => ($group_in_use == $key) ? "checked" : "",
				"link_edgroup" => $this->mk_my_orb("group_content_list",array("gid" => $key)),
			));

			$c .= $this->parse("line");
		};

		$this->vars(array(
			"line" => $c,
			"link_add" => $this->mk_my_orb("add_group",array()),
			"reforb" => $this->mk_reforb("submit_list",array()),
			"menu" => $menu,
		));
		return $this->parse();
	}

	////
	// !Salvestab gruppde nimekirja
	function css_submit_list_groups($args = array())
	{
		extract($args);
		classload("users");
		$u = new users();

		$u->set_user_config(array(
				"uid" => UID,
				"key" => "automatweb_css_group",
				"value" => $use,
		));
		
		$this->css_sync(array("gid" => $use));
		return $this->mk_my_orb("list",array());
	}
	
	////
	// !Kuvab kasutaja oma stiilide nimekirja, igayhe juures on link
	// nende muutmise peale.
	function css_mylist($args = array())
	{
		$menu = $this->css_draw_menu(array(
				"activelist" => array("my","mylist"),
				
				));

		extract($args);
		$this->read_template("global_list.tpl");
		$c = "";
		$cnt = 0;
		foreach($this->styles as $key => $val)
		{
			$this->vars(array(
				"cnt" => ++$cnt,
				"sys_style" => $val,
				"description" => "puudub",
			));

			$c .= $this->parse("line");
		}

		$this->vars(array(
			"line" => $c,
			"menu" => $menu,
		));
		return $this->parse();
	}

	////
	// !Joonistab menüü süsteemsete stiilidega. phuk it
	function css_syslist($args = array())
	{
		extract($args);
		
		$menu = $this->css_draw_menu(array(
				"activelist" => array("list"),
				"vars" => array("glist" => "syslist","stylist" => "syslist_styles"),
			));


		$this->read_template("syslist.tpl");

		$u = new config();
		$sys_css = $u->get_simple_config("sys_css");

		if (!$group_in_use)
		{
			$group_in_use = "default";
		};

		$lx = array(
			"0" => array(
				"name" => "default",
				"comment" => "Default. süsteemne stiil",
				"status" => 2,
				"oid" => 0,
				"modifiedby" => "system"),
				
		);
	
		global $rootmenu;
		$styles = $this->get_objects_below(array(
				"parent" => $rootmenu,
				"class" => CL_CSS_GROUP,
		));

		$lx = array_merge($lx,$styles);

		$l = "";
		$cnt = 0;
		
		foreach($lx as $key => $val)
		{
			$this->vars(array(
				"name" => $val["name"],
				"comment" => $val["comment"],
				"modifiedby" => $val["modifiedby"],
				"active" => checked($val["status"] == 2),
				"use" => checked($val["oid"] == $sys_css),
				"cnt" => ++$cnt,
				"link_edgroup" => $this->mk_orb("group_contents",array("gid" => $val["oid"])),
				"oid" => $val["oid"],
			));
			$l .= $this->parse("line");
		}

		$this->vars(array(
			"line" => $l,
			"link_addgroup" => $this->mk_orb("add_group",array("type" => "system")),
			"reforb" => $this->mk_reforb("syslist_submit",array()),
			"menu" => $menu,
		));

		return $this->parse();
	}

	function css_syslist_submit($args = array())
	{
		extract($args);
		global $rootmenu;
		$q = "UPDATE objects SET status = 1 WHERE parent = '$rootmenu' AND class_id = " . CL_CSS_GROUP;
		$this->db_query($q);
		if (is_array($active))
		{
			$actlist = join(",",$active);
			$q = "UPDATE objects SET status = 2 WHERE parent = '$rootmenu' AND class_id = " . CL_CSS_GROUP . " AND oid IN ($actlist)";
			$this->db_query($q);
		};
		
		$u = new config();
		$sys_css = $u->set_simple_config("sys_css",$use);
		return $this->mk_orb("syslist",array());
	}

	////
	// !Kuvab uue grupi lisamise vormi.
	function css_add_group($args = array())
	{
		extract($args);
		$this->read_template("addgroup.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_add_group",array("type" => $type)),
		));
		return $this->parse();
	}

	function css_submit_add_group($args = array())
	{
		extract($args);

		if ($type == "system")
		{
			$redir_action = "syslist";
			global $rootmenu;
		}
		elseif ($type == "user")
		{
			$redir_action = "list";
			$rootmenu = $this->rootmenu;
		}
		else
		{
			$this->raise_error("Wrong type for css_submit_add_group",true);
		};
		
		$this->quote($name);
		$gid = $this->new_object(array(
			"name" => $name,
			"parent" => $rootmenu,
			"class_id" => CL_CSS_GROUP,
		));

		return $this->mk_my_orb($redir_action,array());
	}

	////
	// !Kuvab stiiligrupi sisu, ehk siis nimekirja, kus ühel pool on süsteemi 
	// poolt defineeritud stiilid ja teisel pool on sinu omad.
	function css_group_contents($args = array())
	{

		extract($args);
		// loeme grupi info sisse
		global $rootmenu;
		$css_ginfo = $this->get_object($gid);

		if ( ($css_ginfo["parent"] == $rootmenu) || ($gid == 0) )
		{
		}
		else
		{
		};

		$menu = $this->css_draw_menu(array(
				"activelist" => "globals",
			));


		extract($args);
		classload("users","xml");
		$u = new config();
		$xml = new xml();

		$custom_css = $u->get_simple_config(array(
					"uid" => UID,
					"key" => "custom_css",
		));

		$this->read_template("list.tpl");
		$styles = $this->_get_my_styles(array("active" => true,"parent" => $gid));
		$my_styles = array("0" => "default");
		if (is_array($my_styles))
		{
			foreach($styles as $key => $val)
			{
				$my_styles[$key] = $val["name"];
			};
		};

		// kaherealine tabel, vasakul kuvatakse koik predefined stiilide nimed,
		// paremal on dropdownid, kust saab valida enda defineeritud nimedega stiilid
		// ja need enda stilid on siis objektid, mis asuvad kasutaja kodukataloogi all
		$c = "";
		$cnt = 0;
		foreach($this->styles as $key => $val)
		{
			$cnt++;

			$this->vars(array(
				"cnt" => $cnt,
				"sys_style" => $val,
				"my_styles" => $this->picker($custom_css[$val],$my_styles),
			));

			$c .= $this->parse("line");
		};
		
		$this->vars(array(
			"line" => $c,
			"link_groups" => $this->mk_my_orb("list",array()),
			"link_sys_styles" => $this->mk_my_orb("group_content_list",array("gid" => $gid)),
			"link_my_styles" => $this->mk_my_orb("my_list",array("gid" => $gid)),
			"reforb" => $this->mk_reforb("submit_group_content_list",array("gid" => $gid)),
			"menu" => $menu,
		));

		return $this->parse();
	}

	function css_sync($args = array())
	{
		// loeme koigepealt koik css stiilid sisse
		classload("users","xml","file");
		$u = new users();
		$xml = new xml();

		$use_group = $u->get_user_config(array(
					"uid" => UID,
					"key" => "automatweb_css_group",
				));
		if (not($use_group == $args["gid"]))
		{
			// don't do anything since this is not the css currently in use
			return;
		};

		$meta = $this->get_object_metadata(array(
						"oid" => $args["gid"],
						"key" => "custom_css",
		));
		$custom_css_xml = $meta;
		$this->dequote($custom_css_xml);
		$custom_css = $xml->xml_unserialize(array("source" => $custom_css_xml));
		
		$css_file = "";

		foreach($custom_css as $key => $val)
		{
			if ($val)
			{
				$css_info = $this->get_obj_meta($val);
				$css_file .= $this->_gen_css_style($key,$css_info["meta"]["css"]);
			};
		}

		$awf = new file();
		$uid = UID;
		$success =$awf->put_special_file(array(
			"name" => "$uid.css",
			"path" => "css",
			"content" => $css_file,
			"sys" => true,
		));
	}
		
	
	////
	// !Submitib CSS nimekirja
	function submit_css_list($args = array())
	{
		extract($args);
		classload("xml","users","file");
		$xml = new xml();
		
		$custom_css = $xml->xml_serialize($style);
		$this->quote($custom_css);
		
		$this->set_object_metadata(array(
			"oid" => $gid,
			"key" => "custom_css",
			"value" => $custom_css,
		));


		$this->css_sync(array("gid" => $gid));

		return $this->mk_my_orb("group_content_list",array("gid" => $gid));
	}

	////
	// !gets "my styles"
	function _get_my_styles($args = array())
	{
		extract($args);

		if (not($parent))
		{
			$parent = $this->rootmenu;
		};
		
		$styles = $this->get_objects_below(array(
				"parent" => $parent,
				"class" => CL_CSS,
				"active" => $active,
		));

		return $styles;
	}

	////
	// !genereerib arrayst tegeliku stiili
	// name - stiili nimi, data, array css atribuutidest
	function _gen_css_style($name,$data = array())
	{
		$retval = ".$name {\n";
		if (!(is_array($data)))
		{
			return false;
		};
		foreach($data as $key => $val)
		{
			if ($val === "")			
			{
				continue;
			}
			$ign = false;
			switch($key)
			{
				case "ffamily":
					$mask = "font-family: %s;\n";
					break;

				case "fstyle":
					$mask = "font-style: %s;\n";
					break;

				case "fweight":
					$mask = "font-weight: %s;\n";
					break;
				
				case "fgcolor":
					$mask = "color: %s;\n";
					break;

				case "bgcolor":
					$mask = "background: %s;\n";
					break;

				case "textdecoration":
					$mask = "text-decoration: %s;\n";
					break;

				case "lineheight":
					$mask = "line-height: %s ".$data["lhunits"].";\n";
					break;
				
				default:
					$ign = true;
					break;
			};

			if ($key == "size")
			{
				$retval .= "\tfont-size: $val" . $data["units"] . ";\n";
			}
			else
			if ($key != "units" && !$ign)
			{
				$retval .= sprintf("\t" . $mask,$val);
			};

		}

		$retval .= "}\n";

		if (!$this->in_gen)
		{
			$this->in_gen = true;
			if ($data["a_style"])
			{
				$retval.=$this->_gen_css_style($name." a:link",$this->get_cached_style_data($data["a_style"]));
			}
			if ($data["a_hover_style"])
			{
				$retval.=$this->_gen_css_style($name." a:hover",$this->get_cached_style_data($data["a_hover_style"]));
			}
			if ($data["a_visited_style"])
			{
				$retval.=$this->_gen_css_style($name." a:visited",$this->get_cached_style_data($data["a_visited_style"]));
			}
			if ($data["a_active_style"])
			{
				$retval.=$this->_gen_css_style($name." a:active",$this->get_cached_style_data($data["a_active_style"]));
			}
			$this->in_gen = false;
		}
		return $retval;
	}

	function get_cached_style_data($id)
	{
		global $AW_CSS_STYLE_CACHE;
		if (!$AW_CSS_STYLE_CACHE[$id])
		{
			$css_info = $this->get_obj_meta($id);
			$AW_CSS_STYLE_CACHE[$id] = $css_info["meta"]["css"];
		}
		return $AW_CSS_STYLE_CACHE[$id];
	}

	////
	// !Kuvab "Minu stiilide" nimekirja, ehk selle, kust sa saad neid endale juurde teha,
	// ja olemasolevaid muuta
	function css_my_list($args = array())
	{
		extract($args);
		$this->read_template("my_list.tpl");

		$styles = $this->_get_my_styles(array("parent" => $gid));

		if (is_array($styles))
		{
			$c = "";
			$cnt = 0;
			foreach($styles as $key => $val)
			{
				$cnt++;
				$this->vars(array(
					"cnt" => $cnt,
					"oid" => $key,
					"name" => ($val["name"]) ? $val["name"] : "(nimetu)",
					"modified" => $this->time2date($val["modified"],4),
					"modifiedby" => $val["modifiedby"],
					"checked" => ($val["status"] == 2) ? "checked" : "",
					"link_edit" => $this->mk_my_orb("edit",array("oid" => $key)),
					"link_delete" => $this->mk_my_orb("delete",array("oid" => $key)),
				));
				$c .= $this->parse("line");
			};
		};

		$this->vars(array(
			"line" => $c,
			"link_sys_styles" => $this->mk_my_orb("group_content_list",array("gid" => $gid)),
			"link_add_style" => $this->mk_my_orb("add",array("gid" => $gid)),
			"link_groups" => $this->mk_my_orb("list",array()),
			"reforb" => $this->mk_reforb("submit_my_list",array("gid" => $gid)),
		));
		return $this->parse();
	}

	////
	// !submitib "minu stiilid"
	function css_submit_my_list($args = array())
	{
		extract($args);
		$oidlist = join(",",$check);
		// koigepealt setime koik stiiliobjektid mitteaktiivseks
		$q = "UPDATE objects SET status = 1 WHERE oid IN ($oidlist)";
		$this->db_query($q);
		if (is_array($act))
		{
			$oidlist = join(",",$act);
			$q = "UPDATE objects SET status = 2 WHERE oid IN ($oidlist)";
			$this->db_query($q);
		};
		return $this->mk_my_orb("my_list",array("gid" => $gid));
	}

	////
	// !Kuvab CSS objekti lisamis/muutmisvormi
	// argumendid:
	// oid(int) - muudetava stiiliobjekti ID
	function css_edit($args = array())
	{
		$this->read_template("edit.tpl");
		extract($args);	
		if ($id)
		{
			$obj = $this->get_obj_meta($id);
			$gid = $obj["parent"];
			$css_data = $obj["meta"]["css"];
			$this->mk_path($obj["parent"]);
		}
		else
		{
			$css_data = array();
		};
	
		$c = "";
		foreach($this->font_families as $key => $val)
		{
			$this->vars(array(
				"family" => $val,
				"ffamily" => $key,
				"fchecked" => ($val == $css_data["ffamily"]) ? "checked" : "",
			));

			$c .= $this->parse("family");
		};
			
		$styl = $this->_gen_css_style("demo",$css_data);

		$stlist = $this->get_select(true);

		$this->vars(array(
			"styl" => $styl,
			"name" => $obj["name"],
			"family" => $c,
			"italic" => ($css_data["fstyle"] == "italic") ? "checked" : "",
			"bold" => ($css_data["fweight"] == "bold") ? "checked" : "",
			"underline" => ($css_data["textdecoration"] == "underline") ? "checked" : "",
			"fgcolor" => $css_data["fgcolor"],
			"bgcolor" => $css_data["bgcolor"],
			"lineheight" => $css_data["lineheight"],
			"size" => $css_data["size"],
			"units" => $this->picker($css_data["units"],$this->units),
			"lhunits" => $this->picker($css_data["lhunits"],$this->units),
			"link_sys_styles" => $this->mk_my_orb("group_content_list",array("gid" => $gid)),
			"link_my_styles" => $this->mk_my_orb("my_list",array("gid" => $gid)),
			"link_groups" => $this->mk_my_orb("list",array()),
			"reforb" => $this->mk_reforb("submit",array("oid" => $id,"gid" => $gid)),
			"a_style" => $this->picker($css_data["a_style"], $stlist),
			"a_hover_style" => $this->picker($css_data["a_hover_style"], $stlist),
			"a_visited_style" => $this->picker($css_data["a_visited_style"], $stlist),
			"a_active_style" => $this->picker($css_data["a_active_style"], $stlist),
		));
		return $this->parse();
	}


	////
	// !Salvestab CSS stiili
	function css_submit($args = array())
	{
		// by default lisame uue stiili saidi root objecti alla
		$rootmenu = $this->rootmenu;
		
		$block = array();

		$block["ffamily"] = $this->font_families[$args["ffamily"]];
		$block["fstyle"] = isset($args["italic"]) ? "italic" : "normal";
		$block["fweight"] = isset($args["bold"]) ? "bold" : "normal";
		$block["textdecoration"] = isset($args["underline"]) ? "underline" : "none";
		$block["size"] = $args["size"];
		$block["units"] = $args["units"];
		$block["lhunits"] = $args["lhunits"];
		$block["fgcolor"] = $args["fgcolor"];
		$block["bgcolor"] = $args["bgcolor"];
		$block["lineheight"] = $args["lineheight"];
		$block["a_style"] = $args["a_style"];
		$block["a_hover_style"] = $args["a_hover_style"];
		$block["a_visited_style"] = $args["a_visited_style"];
		$block["a_active_style"] = $args["a_active_style"];

		$oid = $args["oid"];
		$gid = $args["gid"];

		if (not($oid))
		{
			$oid = $this->new_object(array(
				"parent" => $gid,
				"name" => $args["name"],
				"class_id" => CL_CSS,
				),false);
		}
		else
		{
			$this->upd_object(array(
				"oid" => $oid,
				"name" => $args["name"],
			));
		};

		$this->set_object_metadata(array(
				"oid" => $oid,
				"key" => "css",
				"value" => $block,
		));

		$this->css_sync(array("gid" => $gid));

		return $this->mk_my_orb("change",array("id" => $oid));
	}

	////
	// !Kustutab mingi eelnevalt defineeritud stiili
	function css_delete($args = array())
	{
		extract($args);
		$q = "DELETE FROM objects WHERE oid = '$oid' AND class_id = " . CL_CSS;
		$this->db_query($q);
		
		return $this->mk_my_orb("my_list",array());
	}
	
	function get_select($addempty = false)
	{
		$ret = array();
		if ($addempty)
		{
			$ret[0] = "";
		}
		$this->db_query("SELECT oid,name FROM objects WHERE class_id = ".CL_CSS." AND status != 0");
		while ($row = $this->db_next())
		{
			$ret[$row["oid"]] = $row["name"];
		}
		return $ret;
	}
};
