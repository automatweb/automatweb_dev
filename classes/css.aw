<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/css.aw,v 2.21 2003/04/23 17:00:23 kristo Exp $
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

/*

@classinfo syslog_type=ST_CSS relationmgr=yes
@groupinfo general caption=Üldine
@groupinfo preview caption=Eelvaade

@default group=general 
@default table=objects

@property ffamily type=select field=meta method=serialize group=general
@caption Font

@property italic type=checkbox ch_value=1 field=meta method=serialize group=general
@caption <i>Italic</i>

@property bold type=checkbox ch_value=1 field=meta method=serialize group=general
@caption <b>Bold</b>

@property underline type=checkbox ch_value=1 field=meta method=serialize group=general
@caption <u>Underline</u>

@property size type=textbox field=meta method=serialize group=general size=5
@caption Suurus

@property units type=select field=meta method=serialize group=general
@caption Suuruse &uuml;hikud

@property fgcolor type=colorpicker field=meta method=serialize group=general
@caption Teksti v&auml;rv

@property bgcolor type=colorpicker field=meta method=serialize group=general
@caption Tausta v&auml;rv

@property lineheight type=textbox field=meta method=serialize group=general size=5
@caption Joone k&otilde;rgus

@property lhunits type=select field=meta method=serialize group=general
@caption Joone k&otilde;rguse &uuml;hikud

@property border type=textbox field=meta method=serialize group=general size=5
@caption Border width

@property bordercolor type=colorpicker field=meta method=serialize group=general
@caption Border color

@property align type=select field=meta method=serialize group=general
@caption Align

@property valign type=select field=meta method=serialize group=general
@caption Valign

@property a_style type=relpicker field=meta method=serialize group=general reltype=RELTYPE_CSS
@caption Lingi stiil

@property a_hover_style type=relpicker field=meta method=serialize group=general reltype=RELTYPE_CSS
@caption Lingi stiil (hover)

@property a_visited_style type=relpicker field=meta method=serialize group=general reltype=RELTYPE_CSS
@caption Lingi stiil (visited)

@property a_active_style type=relpicker field=meta method=serialize group=general reltype=RELTYPE_CSS
@caption Lingi stiil (active)

@property pre type=text field=meta method=serialize group=preview no_caption=1

*/

define("RELTYPE_CSS", 1);

class css extends class_base
{
	function css ($args = array())
	{
		// kuidas ma seda edimisvormi alati automawebi juurest lugeda saan?
		$this->init(array(
			"tpldir" => "css",
			"clid" => CL_CSS
		));

		$this->lc_load("css","lc_css");

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

		$this->aligns = array(
			"" => "",
			"left" => "Vasak",
			"center" => "Keskel",
			"right" => "Paremal"
		);

		$this->valigns = array(
			"" => "",
			"top" => "&Uuml;leval",
			"middle" => "Keskel",
			"bottom" => "All"
		);

		// siin peaks diferentseerima selle, kas tegu on süsteemste voi kasutaja
		// stiilidega.
		$udata = $this->get_user();
		$this->rootmenu = $udata["home_folder"];
	}

	////
	// !Joonistab Editori menüü
	function css_draw_menu($args = array())
	{
		extract($args);
		load_vcl("xmlmenu");
		$xm = new xmlmenu();
		$retval = $xm->build_menu(array(
			"vars" => array_merge($vars,array("ext" => $this->cfg["ext"])),
			"xml" => $this->cfg["basedir"] . "/xml/css_editor.xml",
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

		$u = get_instance("users");
		$group_in_user = $u->get_user_config(array(
			"uid" => aw_global_get("uid"),
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
		// ja seejärel need, mis on kasutaja all
		$groups = $this->get_objects_below(array(
				"parent" => $this->cfg["rootmenu"],
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
		$u = get_instance("users");

		$u->set_user_config(array(
			"uid" => aw_global_get("uid"),
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

		$u = get_instance("config");
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
	
		$styles = $this->get_objects_below(array(
			"parent" => $this->cfg["rootmenu"],
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
		$q = "UPDATE objects SET status = 1 WHERE parent = '".$this->cfg["rootmenu"]."' AND class_id = " . CL_CSS_GROUP;
		$this->db_query($q);
		if (is_array($active))
		{
			$actlist = join(",",$active);
			$q = "UPDATE objects SET status = 2 WHERE parent = '".$this->cfg["rootmenu"]."' AND class_id = " . CL_CSS_GROUP . " AND oid IN ($actlist)";
			$this->db_query($q);
		};
		
		$u = get_instance("config");
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
			$rootmenu = $this->cfg["rootmenu"];
		}
		elseif ($type == "user")
		{
			$redir_action = "list";
			$rootmenu = $this->rootmenu;
		}
		else
		{
			$this->raise_error(ERR_CSS_EGRP,"Wrong type for css_submit_add_group",true);
		};
		
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
		$css_ginfo = $this->get_object($gid);

		if ( ($css_ginfo["parent"] == $this->cfg["rootmenu"]) || ($gid == 0) )
		{
		}
		else
		{
		};

		$menu = $this->css_draw_menu(array(
			"activelist" => "globals",
		));

		extract($args);
		$u = get_instance("config");
		$xml = get_instance("xml");

		$custom_css = $u->get_simple_config(array(
			"uid" => aw_global_get("uid"),
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
		$u = get_instance("users");
		$xml = get_instance("xml");

		$use_group = $u->get_user_config(array(
			"uid" => aw_global_get("uid"),
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

		$awf = get_instance("file");
		$uid = aw_global_get("uid");
		$success =$awf->put_special_file(array(
			"name" => aw_global_get("uid").".css",
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
		$custom_css = aw_serialize($style, SERIALIZE_XML);
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
					$mask = "line-height: %s".$data["lhunits"].";\n";
					break;

				case "border":
					$mask = "border-width: %spx;\n";
					break;
				
				case "valign":
					$mask = "vertical-align: %s;\n";
					break;
				
				case "align":
					$mask = "text-align: %s;\n";
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
		if (!aw_cache_get("AW_CSS_STYLE_CACHE",$id))
		{
			$css_info = $this->get_obj_meta($id);
			aw_cache_set("AW_CSS_STYLE_CACHE",$id,$css_info["meta"]["css"]);
		}
		return aw_cache_get("AW_CSS_STYLE_CACHE",$id);
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

	////
	// !this reads and outputs the user's css file 
	function get_user_css($arr)
	{
		extract($arr);
		if (strpos("/",$name) === false)
		{
			$path = sprintf("%s/files/css/%s.css",$this->cfg["site_basedir"],aw_global_get("uid"));
			$arr = @file($path);
			if (is_array($arr))
			{
				$css = join("",$arr);
			}
			print $css;
		};
		die();
	}

	////
	// !I could not think of another place to stick this. oh well. whatever
	function colorpicker($arr)
	{
		if (method_exists($this,"db_init"))
		{
			$this->db_init();
		};
		if (method_exists($this,"tpl_init"))
		{
			$this->tpl_init("automatweb");
		}
		$this->read_template("colorpicker.tpl");
		die($this->parse());
	}

	function callback_get_rel_types()
	{
		return array(
			RELTYPE_CSS => "css stiil"
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELTYPE_CSS)
		{
			return array(CL_CSS);
		}
	}

	function get_property(&$arr)
	{
		$prop =& $arr["prop"];
		switch($prop['name'])
		{
			case "ffamily":
				$prop['options'] = $this->font_families;
				break;

			case "align":
				$prop['options'] = $this->aligns;
				break;

			case "valign":
				$prop['options'] = $this->valigns;
				break;

			case "units":
			case "lhunits":
				$prop['options'] = $this->units;
				break;

			case "pre":
				$this->read_template("preview.tpl");
				$prop['value'] = $this->parse();
				break;
		}
		return PROP_OK;
	}

	function callback_pre_edit($arr)
	{
		$dc =& $arr["coredata"];
		$_t = new aw_array($dc['meta']['css']);
		foreach($_t->get() as $k => $v)
		{
			$dc['meta'][$k] = $v;
		}
	}

	function callback_pre_save($arr)
	{
		$dc =& $arr["coredata"];
		$_t = new aw_array($dc['metadata']);
		foreach($_t->get() as $k => $v)
		{
			$dc['metadata']['css'][$k] = $v;
		}
	}
};
