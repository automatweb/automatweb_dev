<?php
// $Header: /home/cvs/automatweb_dev/classes/menuedit.aw,v 2.168 2002/10/22 15:56:54 kristo Exp $
// menuedit.aw - menuedit. heh.

// number mille kaudu tuntakse 2ra kui tyyp klikib kodukataloog/SHARED_FOLDERS peale
define("SHARED_FOLDER_ID",2147483647);

classload("cache","defs","php","file","image");

class menuedit extends aw_template
{
	// this will be set to document id if only one document is shown, a document which can be edited
	var $active_doc = false;

	function menuedit()
	{
		$this->init("automatweb/menuedit");

		// FIXME: damn this is a mess
		$this->lc_load("menuedit","lc_menuedit");
		lc_site_load("menuedit",$this);
		lc_load("definition");
	}

	////
	// !simpel menyy lisamise funktsioon. laienda kui soovid. Mina kasutan seda saidi seest
	// uue folderi lisamiseks kodukataloogi alla
	function add_new_menu($args = array())
	{
		// ja eeldame, et meil on vähemalt parent ja name olemas.
		$this->quote($args["name"]);
		$newoid = $this->new_object(array(
			"name" => $args["name"],
			"parent" => $args["parent"],
			"status" => 2,
			"class_id" => CL_PSEUDO,
			"jrk" => $args["jrk"]
		));
		$q = sprintf("INSERT INTO menu (id,type) VALUES (%d,%d)",$newoid,MN_HOME_FOLDER_SUB);
		$this->db_query($q);
		$this->_log("menuedit",sprintf(LC_MENUEDIT_ADDED_HOMECAT_FOLDER,$args[name]));

		$this->invalidate_menu_cache(array($newoid));

		return $newoid;
	}

	// parameetrid:
	// section - millist naidata?
	// text - kui see != "" , siis n2idatakse dokude asemel seda
	// docid - millist dokumenti selle sektsiooni alt naidata?
	// s.t. kui on dokumentide nimekiri ntx.
	// strip_img - kas imaged maha strippida dokudest
	// template - mis template ga menyysid n2idataxe
	// vars - array kuhu saab sisu kirjutada, et seal
	//	olevad muutujad pannaxe menyyediti template sisse
	// $sub_callbacks - array template_name => funxiooninimi
	//  neid kutsutakse siis v2lja kui vastav sub on template sees olemas
	function gen_site_html($params)
	{
		// handle favicon
		if (($params["section"]."") == "favicon.ico")
		{
			classload("config");
			$c = new config;
			$c->show_favicon(array());
		}

		// kontrollib sektsiooni ID-d, tagastab oige numbri kui tegemist oli
		// aliasega, voi lopetab töö, kui miskit oli valesti
		// $section = $this->check_section($params["section"]);

		// at this point $section is already numeric,
		// we checked it in $this->request_startup()
		$section = aw_global_get("section");

		$obj = $this->get_object($section);
		$meta = $obj["meta"];
		$params["section"] = $section;

		global $format;
		$act_per_id = aw_global_get("act_per_id");
		if ($format == "rss")
		{
			classload("document");
			$d = new document();
			$d->gen_rss_feed(array("period" => $act_per_id,"parent" => $section));
		};

		// koostame array vajalikest parameetritest, mis identifitseerivad cachetava objekti
		$cp = array();

		// page, type and lcb are in the HTTP_GET_VARS too .. so why do they get
		// separate handling?

		$cp[] = $act_per_id;

		$cp[] = aw_global_get("lang_id");

		// here we sould add all the variables that are in the url to the cache parameter list
		global $HTTP_GET_VARS;

		foreach($HTTP_GET_VARS as $var => $val)
		{
			if ($var != "automatweb" && $var != "set_lang_id")	// just to make sure that each user does not get it's own copy
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

		$not_cached = false;

		$use_cache = true;

		if ($params["print"] || ($params["text"] && !$params["force_cache"]))
		{
			$not_cached = true;
			$use_cache = false;
		};

		$cache = new cache;
		$cache->set_opt("metaref",$meta["metaref"]);
		$cache->set_opt("referer",aw_global_get("referer"));
		if (!($res = $cache->get($section,$cp)) || $params["format"] || $not_cached)
		{
			// seda objekti pold caches
			$res = $this->_gen_site_html($params);
			if ($use_cache && !aw_global_get("no_cache_content"))
			{
				$cache->set($section,$cp,$res);
			};
			echo "<!-- no cache $section <pre>",join("-",$cp),"</pre>\n-->";
		}
		else
		{
			// kui asi on caches, siis paneme kirja et mis lehte vaadatati.
			$ch = $this->get_object_chain($section);
			reset($ch);
			while (list($k,$v) = each($ch))
			{
				$str=$v["name"]." / ".$str;
			}
			$this->do_syslog_core($str,$section);
		}

		// make sure that the banner random id's are different each time around, even when the site is cached.
		$banner_defs = $this->cfg["banners"];
		if (is_array($banner_defs))
		{
			reset($banner_defs);
			while (list($name,$gid) = each($banner_defs))
			{
				$res = str_replace("[ss".$gid."]",$this->gen_uniq_id(),$res);
			}
		}
		return $res;
	}
	
	////
	// !da thing. draws the site 
	// params: section, text, docid, strip_img, template, format, vars, no_left_pane, no_right_pane
	// niisiis. vars array peaks sisaldama mingeid pre-parsed html tükke,
	// mis võivad tulla ntx kusagilt orbi klassi seest vtm.
	// array keydeks peaksid olema variabled template sees, mis siis asendatakse
	// oma väärtustega
	function _gen_site_html($params)
	{
		extract($params);	
		$template = isset($template) && $template != "" ? $template : "main.tpl";
		global $DBG;
		if ($DBG)
		{
			print "tpl = $template<br>";
		}
		$docid = isset($docid) ? $docid : 0;

		// impordime taimeriklassi

		$this->vars(array(
			"lang_code" => aw_global_get("LC"),
		));
		
		$obj = $this->get_object($section);

		$this->check_object($obj);

		if (not($text))
		{
			$text = $this->replacement;
		};

		// this should be replaced with calls to php_serialize, since it's faster
		$meta = $this->get_object_metadata(array(
			"metadata" => $obj["metadata"],
		));

		////
		// Kui küsiti infot RDF-is, siis tagastame vastava väljundi
		// hm. Ja tegelikult peaks selle üleüldse kuhugi mujale viima.
		if (isset($format) && $format == "rss")
		{
			die($this->do_rdf($section,$obj,$format,$docid));
		}
 
		/// Vend?
		if ($obj["class_id"] == CL_BROTHER_DOCUMENT)
		{
			$section=$obj["parent"];
			$docid=$obj["brother_of"];
		}

		$_t = aw_global_get("act_period");
		$this->vars(array(
			"per_string" => $_t["description"],
			"act_per_id" => $_t["id"],
			"per_img_url" => image::check_url($_t["data"]["image"]["url"]),
			"per_img_tag" => image::make_img_tag(image::check_url($_t["data"]["image"]["url"])),
			"per_img_link" => ($_t["data"]["image_link"] != "" ? $_t["data"]["image_link"] : aw_ini_get("baseurl"))
		));

		// check whether access to that menu is denied by ACL and if so
		// redirect the user 
		if (not($this->can("view", $section)))
		{
			$this->no_access_redir();
		}

		// by default show both panes.
		$this->left_pane = (isset($no_left_pane) && $no_left_pane == true) ? false : true;
		$this->right_pane = (isset($no_right_pane) && $no_right_pane == true) ? false : true;

		// read all the menus and other necessary info into arrays from the database
		dbg("active language for menus is ".aw_global_get("lang_id")."<br>");

		$this->make_menu_caches();

		// leiame, kas on tegemist perioodilise rubriigiga
		$periodic = $this->is_periodic($section);

		if ($obj["class_id"] == CL_DOCUMENT || $obj["class_id"] == CL_PERIODIC_SECTION)
		{
			$this->sel_section = $obj["parent"];
		}
		else
		if ($obj["class_id"] == CL_BROTHER_DOCUMENT)
		{
			$bo = $this->get_object($obj["brother_of"]);
			$bo_meta = $this->get_object_metadata(array(
				"metadata" => $bo["metadata"]
			));
			if ($bo_meta["show_real_pos"])
			{
				$section = $bo["parent"];
				$this->sel_section = $bo["parent"];
			}
			else
			{
				$this->sel_section = $obj["parent"];
			}
		}
		else
		if ($obj["class_id"]  == CL_BROTHER)
		{
			$this->sel_section = $obj["brother_of"];
		}
		else
		{
			$this->sel_section = $section;
		}

		$sel_menu_id = $this->sel_section;

		$this->vars(array(
			"sel_menu_id" => $sel_menu_id,
			"se_lang_id" => aw_global_get("lang_id")
		));

		// build the menu chain for the requested section, this simplifies at least 
		// users_only check and finding the correct template_set, probably also
		// a few other functions
		$this->build_menu_chain($this->sel_section);

		// if the remote user is not logged in and the users_only property is set,
		// redirect the him to the correspondending error page
		if ( (aw_global_get("uid") == "") && ($this->properties["users_only"]) )
		{
			$this->users_only_redir();
		};
		// if the tpl_dir property is set, reinitialize the template class
		if (!isset($tpldir))
		{
			$tpldir = $this->properties["tpl_dir"];
		}
		if ($tpldir)
		{
			$this->tpl_init(sprintf("../%s/automatweb/menuedit",$tpldir));
		};
		
		$this->read_template($template);

		classload("periods","document");
		$d = new document();
		$this->doc = new document();
		
		// so, if the current object is not a menu,
		// just pretend that the parent is. Hm, I think that's wrong
		if (!is_array($this->mar[$sel_menu_id]))
		{
			$seobj = $this->get_object($sel_menu_id);
			$sel_menu_id = $seobj["parent"];
		}
		
		// here we must find the menu image, if it is not specified for this menu,
		//then use the parent's and so on.
		$this->do_menu_images($sel_menu_id);


		// nii nyt leiame aktiivse kommentaari - kui aktiivsel menyyl on tyhi, siis parenti oma jne
		$this->vars(array(
			"sel_menu_comment" => $this->properties["comment"],
		));

		if (!$this->mar[$sel_menu_id]["left_pane"])
		{
			$this->left_pane = false;
		}

		if (!$this->mar[$sel_menu_id]["right_pane"])
		{
			$this->right_pane = false;
		}

		if ($obj["class_id"] == CL_PSEUDO && (($sh_id = $this->is_shop($section)) > 0) && $text == "")
		{
			// tshekime et kas 2kki on selle menyy all pood. kui on, siis joonistame selle.
			$doc_c = $this->show_documents($section,$docid);
			$shp_c = $this->do_shop($section,$sh_id);
			$this->vars(array("doc_content" => ($doc_c.$shp_c)));
			$this->read_template($template);
		}
		else
		if ($periodic && $text == "") 
		{
			$docc = $this->show_periodic_documents($section,$obj);
			if ($this->mar[$sel_menu_id]["no_menus"] == 1 || ($params["print"]) )
			{
				// this tells site index.aw not to show the index.tpl
				// shrug, erki wants it that way
				//$this->no_index_template = true;
				return $docc;
			}

			$this->vars(array("doc_content" => $docc));
		} 
		else 
		if ($text == "")
		{
			// sektsioon pole perioodiline
			//$docc = $this->show_documents($section,$docid,$template);
			$docc = $this->show_documents($section,$docid,$xtemplate);
			if ( ($this->mar[$sel_menu_id]["no_menus"] == 1) || ($params["print"]) )
			{
				$this->no_index_template = true;
				return $docc;
			}
			$this->vars(array("doc_content" => $docc));
			if ( (is_array($this->blocks)) && (sizeof($this->blocks) > 0) )
			{
				while(list(,$blockdata) = each($this->blocks))
				{
					$this->vars(array(
						"title" => $blockdata["title"],
						"content" => $blockdata["content"],
					));
					$vars[$blockdata["template"]] .= $this->parse($blockdata["template"]);
				};
			};
		}
		else
		{
			// text on ette antud
			$this->vars(array("doc_content" => $text));
		}

//		die();
		// import language constants
		lc_site_load("menuedit",$this);

		// get array with path of objects in it
		$path = $this->get_path($section,$obj);
		$this->path = $path;

		// you are here links		
		$yah = $this->make_yah($this->path);
		if ($this->site_title == "")
		{
			$this->site_title = $this->title_yah;
		}
		$this->vars(array("YAH_LINK" => $yah));

		// language selecta
		if ($this->is_template("LANG"))
		{
			$this->make_langs();
		}

		// write info about viewing to the syslog
		$this->do_syslog($section);

		// right, now build the menus

		// this will contain all the menus parsed from templates
		$outputs = array();	

		$ce = false;

		$section_subitems = sizeof($this->mpr[$section]);

		$this->section = $section;
		
		// eek.

		$cd = "";
		$menu_defs_v2 = $this->cfg["menu_defs"];
		$this->menu_defaults = $this->cfg["menu_defaults"];
		if ($this->cfg["lang_defs"])
		{
			$menu_defs_v2 = $this->cfg["menu_defs"][aw_global_get("lang_id")];
			$this->menu_defaults = $this->cfg["menu_defaults"][aw_global_get("lang_id")];
		}
		$frontpage = $this->cfg["frontpage"];

		global $DBUG;
		if ($DBUG)
		{
			print "<pre>";
			var_dump($this->cfg["lang_defs"]);
			var_dump($menu_defs_v2);
			print "</pre>";
		};
	

		if (isset($menu_defs_v2) && is_array($menu_defs_v2))
		{
			$nx = "";
			$this->level = 0;
			reset($menu_defs_v2);
			while (list($id,$name) = each($menu_defs_v2))
			{
				$nx = $name;
				dbg("drawing $id,$name<br>");

				// SIC! check whether login menus are defined and
				// if so, overwrite the one defined in aw.ini
				if ($name == "LOGGED")
				{
					$cfg = get_instance("config");
					$_id = $cfg->get_login_menus();
					if ($_id)
					{
						$id = $_id;
					};
				};

				// so we can get the root menu of the menu area from the menu area's name quickly
				$this->menu_defs_name_map[$name] = $id;

				global $DBUG;
				if ($DBUG)
				{
					print "drawing $id $name<br>";
				};
				$this->req_draw_menu($id,$name,&$path,false);
				if ($this->sel_section == $frontpage)
				{
					$this->do_seealso_items($this->mar[$id],$name);
				}
				$blockname = sprintf("%s_L%s",$name,1);
				$blocktemplate_subs = sprintf("MENU_%s_L%s_HAS_SUBITEMS",$name,1);
				$blocktemplate_nosubs = sprintf("MENU_%s_L%s_NO_SUBITEMS",$name,1);

				if ($this->templates[$blocktemplate_subs])
				{
					if ( $this->subitems[$blockname] > 0) 
					{
						$this->vars(array($blocktemplate_subs => $this->parse($blocktemplate_subs)));
					}
					else
					{
						$this->vars(array($blocktemplate_nosubs = $this->parse($blocktemplate_nosubs)));
					};
				};
			}
		}
		
		$this->make_promo_boxes($obj["class_id"] == CL_BROTHER ? $obj["brother_of"] : $this->sel_section);


		if ($this->is_template("POLL"))
		{
			$this->make_poll();
		};

		$this->make_search();
		
		$this->make_banners();
	
		$this->do_sub_callbacks($sub_callbacks);

		$this->vars(array(
			"ss" => $this->gen_uniq_id(),		// bannerite jaox
			"ss2" => $this->gen_uniq_id(),
			"ss3" => $this->gen_uniq_id(),
			"link" => "",
			"section"	=> $section,
		  "uid" => aw_global_get("uid"),
		  "date" => $this->time2date(time(), 2),
		  "date2" => $this->time2date(time(), 8),
		  "date3" => date("d").". ".get_lc_month(date("n")).". ".date("Y"),
			"IS_FRONTPAGE" => ($section == $frontpage ? $this->parse("IS_FRONTPAGE") : ""),
			"IS_FRONTPAGE2" => ($section == $frontpage ? $this->parse("IS_FRONTPAGE2") : ""),
			"IS_NOT_FRONTPAGE" => ($section != $frontpage ? $this->parse("IS_NOT_FRONTPAGE") : ""),
			"IS_NOT_FRONTPAGE2" => ($section != $frontpage ? $this->parse("IS_NOT_FRONTPAGE2") : ""),
		));

		if ($_t["data"]["image"]["url"] != "")
		{
			$this->vars(array(
				"HAS_PERIOD_IMAGE" => $this->parse("HAS_PERIOD_IMAGE")
			));
		}
		// what's that for?
		// well, you can pass along values for variables from index.aw and places like that using $vars array 
		// it's pretty neat actually. - terryf
		if (is_array($vars))
		{
			$this->vars($vars);
		}

	
		// eek.
		// whaat?
		// damned, if I knew
		
		$cd = "";
		if (aw_global_get("uid") == "")
		{
			$login = $this->parse("login");
			$this->vars(array(
				"login" => $login, 
				"login2" => $this->parse("login2"), 
				"login3" => $this->parse("login3"), 
				"logged" => "",
				"logged2" => "",
				"logged3" => "",
				"site_title" => $this->site_title
			));
		}
		else
		{
			classload("users");
			$t = new users;
			$udata = $this->get_user();
			$jfar = $t->get_jf_list(isset($udata["join_grp"]) ? $udata["join_grp"] : "");
			$jfs = "";
			reset($jfar);
			while (list($fid,$name) = each($jfar))
			{
				$this->vars(array(
					"form_id" => $fid, 
					"form_name" => $name
				));
				$jfs.=$this->parse("JOIN_FORM");
			}
			$this->vars(array("JOIN_FORM" => $jfs));

			if ($this->can("edit",$section) && $this->active_doc)
			{
				$cd = $this->parse("CHANGEDOCUMENT");
			};
			$this->vars(array(
				"CHANGEDOCUMENT" => $cd,
			));

			$cd = "";
			if ($this->can("add",$section))
			{
				$cd = $this->parse("ADDDOCUMENT");
			};
			$this->vars(array(
				"ADDDOCUMENT" => $cd,
			));

			// check menuedit access
			if ($this->prog_acl("view", PRG_MENUEDIT))
			{
				// so if this is the only document shown and the user has edit right
				// to it, parse and show the CHANGEDOCUMENT sub
				$this->vars(array("MENUEDIT_ACCESS" => $this->parse("MENUEDIT_ACCESS")));
			}
			else
			{
				$this->vars(array("MENUEDIT_ACCESS" => ""));
			}


			// god dammit, this sucks. aga ma ei oska seda kuidagi teisiti lahendada
			// konkreetselt sonnenjetis on logged LEFT_PANE sees,
			// www.kirjastus.ee-s on LEFT_PANE logged-i sees.
			$lp = "";
			$rp = "";
			if ($this->is_template("logged.asdasdas"))
			{
				print "logged is inside LEFT_PANE<br>";

			}
		
			$this->vars(array(
				"logged" => $this->parse("logged"), 
				"logged1" => $this->parse("logged1"),
				"logged2" => $this->parse("logged2"),
				"logged3" => $this->parse("logged3"),
				"login" => "",
				"site_title" => $this->site_title
			));
		};
		
		if ($this->left_pane)
		{
			$lp = $this->parse("LEFT_PANE");
		}
		if ($this->right_pane)
		{
			$rp = $this->parse("RIGHT_PANE");
		}
		
		$this->vars(array("LEFT_PANE" => $lp, "RIGHT_PANE" => $rp));
		
		if (is_array($vars))
		{
			$vars["LEFT_PROMO"] .= $this->vars["LEFT_PROMO"];
			$this->vars($vars);
		}

		// sucks.
		if ($this->mar[$section]["parent"] == 34506 || $this->mar[$this->mar[$section]["parent"]]["parent"] == 34506 || $section == $frontpage)
		{
			$this->vars(array(
				"IS_AWCOM_FRONTPAGE" => $this->parse("IS_AWCOM_FRONTPAGE"),
				"MOSTIMP" => '<span class="pealkiri2">'.$this->mar["34506"]["name"].'</span>'
			));
		}

		if ($this->mar[$section]["parent"] == 39790 || $this->mar[$this->mar[$section]["parent"]]["parent"] == 39790 || ($section == $GLOBALS["frontpage"] && $GLOBALS["lang_id"] == 4))
		{
			$this->vars(array(
				"IS_AWCOM_FRONTPAGE_ENG" => $this->parse("IS_AWCOM_FRONTPAGE_ENG"),
				"MOSTIMP" => '<span class="pealkiri2">'.$this->mar["39790"]["name"].'</span>'
			));
		}

		$eng = "";
		$neng = "";
		if ($GLOBALS["lang_id"] == 4)
		{
			$eng = $this->parse("ENG");
		}
		else
		{
			$neng = $this->parse("NOT_ENG");
		}
		$this->vars(array(
			"ENG" => $eng,
			"NOT_ENG" => $neng
		));


		if ($section == $frontpage)
		{
			$this->vars(array("IS_FRONTPAGE" => $this->parse("IS_FRONTPAGE")));
		}

		$popups = $this->build_popups();

		$this->set_object_metadata(array(
			"oid" => $section,
			"key" => "metaref",
			"value" => $this->metaref,
		));

		$retval = $this->parse();
		return $this->parse() . $popups;
	}

	function is_periodic($section,$checkobj = 1) 
	{
		//$mn = $this->get_menu($section);
		$mn = $this->mar[$section];
		$periodic = $mn["periodic"];
		// menyysektsioon ei ole perioodiline. Well, vaatame 
		// siis, kas ehk dokument ise on?
		if (!$mn && !$periodic && $checkobj == 1)
		{
			$q = "SELECT period FROM objects WHERE oid = '$section'";
			$periodic = $this->db_fetch_field($q,"period");
		};
		return $periodic;
	}

	function has_sub_dox($oid)
	{
		$has_dox = $this->subs[$oid] > 0 ? 1 : 0;
		
		if (is_array($this->mpr[$oid]))
		{
			reset($this->mpr[$oid]);
			while (list(,$row) = each($this->mpr[$oid]))
			{
				$has_dox |= $this->has_sub_dox($row["oid"]);
				if ($this->subs[$row["oid"]] > 0)
				{
					$has_dox = 1;
				}
			}
		}

		return $has_dox;
	}

	////
	// !Listib koik objektid
	function db_listall($where = " objects.status != 0",$ignore = false,$ignore_lang = false)
	{
		$aa = "";
		if (!$ignore)
		{
			// loeme sisse koik objektid
			$aa = "AND ((objects.site_id = '".$this->cfg["site_id"]."') OR (objects.site_id IS NULL))";
		};
		if ($this->cfg["lang_menus"] == 1 && $ignore_lang == false)
		{
			$aa.="AND (objects.lang_id=".aw_global_get("lang_id")." OR menu.type = ".MN_CLIENT.")";
		}
		$q = "SELECT objects.oid as oid, 
				objects.parent as parent,
				objects.comment as comment,
				objects.name as name,
				objects.created as created,
				objects.createdby as createdby,
				objects.modified as modified,
				objects.modifiedby as modifiedby,
				objects.last as last,
				objects.status as status,
				objects.jrk as jrk,
				objects.alias as alias,
				objects.class_id as class_id,
				objects.brother_of as brother_of,
				objects.metadata as metadata,
				menu.type as mtype,
				menu.link as link,
				menu.clickable as clickable,
				menu.target as target,
				menu.ndocs as ndocs,
				menu.img_id as img_id,
				menu.img_url as img_url,
				menu.hide_noact as hide_noact,
				menu.mid as mid,
				menu.sss as sss,
				menu.links as links,
				menu.icon_id as icon_id,
				menu.admin_feature as admin_feature,
				menu.periodic as mperiodic,
				menu.is_shop as is_shop,
				menu.shop_id as shop_id
			FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")  AND $where $aa
				ORDER BY objects.parent, jrk,objects.created";
		global $XXX;
		if ($XXX)
		{
			print $q;
		}
		$this->db_query($q);
	}

	function get_default_document($section,$ignore_global = false)
	{
		// the following line looks so wrong
		// /me vaatab syytult lakke ja teeb n2gu nagu ei saax midagi aru - terryf
		if (isset($GLOBALS["docid"]) && $GLOBALS["docid"] && $ignore_global == false)
		{
			return $GLOBALS["docid"];
		}
		if (!$section)
		{
			return 0;
		}

		$obj = $this->get_object($section);
	
		// if it is a document, use this one. 
		if ($obj["class_id"] == CL_DOCUMENT)
		{
			return $section;
		}

		if ($obj["class_id"] == CL_BROTHER)
		{
			$obj = $this->get_object($obj["brother_of"]);
		}

		// if any keywords for the menu are set, we must show all the documents that match those keywords under the menu
		if ($obj["meta"]["has_kwd_rels"])
		{
			$docid = array();

			$q = "
				SELECT distinct(keywordrelations.id) as id FROM keyword2menu
				LEFT JOIN keywordrelations ON keywordrelations.keyword_id = keyword2menu.keyword_id
				LEFT JOIN objects ON keywordrelations.id = objects.oid
				WHERE keyword2menu.menu_id = '$obj[oid]' AND objects.status = 2";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$docid[] = $row["id"];
			}
			return $docid;
		}

		$docid = $obj["last"];
		$ar = unserialize($docid);

		// kuna on vaja mitme keele jaox default dokke seivida, siis uues versioonis pannaxe
		// siia array aga backward compatibility jaox tshekime, et 2kki see on integer ikkagi
		if (is_array($ar))
		{
			$docid = $ar[aw_global_get("lang_id")];
		}
		if ($docid > 0)
		{
			if ($this->cfg["lang_menus"] == 1)
			{
				$ss = "AND objects.lang_id=".aw_global_get("lang_id");
			}
			if ($this->db_fetch_field("SELECT status FROM objects WHERE oid = $docid AND class_id = ".CL_DOCUMENT." $ss ","status") != 2)	
			{
				// make sure that the default is not deleted
				$docid = 0;
			}
		}
		// ei olnud defaulti, peaks vist .. näitama nimekirja? 
		if ($docid < 1)	
		{
			// avoid unneccessary query if the menu is already in the cache
			if ($this->mar[$section])
			{
				$me_row = $this->mar[$section];
			}
			else
			{
				$me_row = $this->get_menu($section);
			};

			$sections = unserialize($me_row["sss"]);
			$periods = unserialize($me_row["pers"]);

			if (is_array($sections))
			{
				$pstr = join(",",$sections);
				if ($pstr != "")
				{
					$pstr = "objects.parent IN ($pstr)";
					$ordby = "objects.modified DESC";
					$lsas = " AND (documents.no_last != 1 OR documents.no_last is null ) ";
				}
				else
				{
					$pstr = "objects.parent = '$obj[oid]'";
				};
			}
			else
			{
				$pstr = "objects.parent = '$obj[oid]'";
			};
			if ($me_row["ndocs"] > 0)
			{
				$lm = "LIMIT ".$me_row["ndocs"];
			};

			$docid = array();
			$cnt = 0;
			if ($ordby == "")
			{
				$ordby = $this->cfg["document_list_order_by"];
			}

			if ($ordby == "")
			{
				$ordby = "objects.jrk";
			}
			$q = "SELECT objects.oid as oid,objects.class_id AS class_id, objects.brother_of AS brother_of, documents.esilehel as esilehel FROM objects LEFT JOIN documents ON documents.docid = objects.brother_of WHERE (($pstr AND status = 2 AND class_id = 7 AND objects.lang_id=".aw_global_get("lang_id").") OR (class_id = ".CL_BROTHER_DOCUMENT." AND status = 2 AND $pstr)) $lsas ORDER BY $ordby $lm";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				if (!($this->cfg["no_fp_document"] && $row["esilehel"] == 1))
				{
					$docid[$cnt++] = ($row["class_id"] == CL_DOCUMENT) ? $row["oid"] : $row["brother_of"];
				}
			}
			if ($cnt > 1)
			{
				// a list of documents
				return $docid;
			}
			else
			if ($cnt == 1)
			{
				// the correct id
				return $docid[0];
			}
			else
			{
				return false;
			}
		}

		return $docid;
	}

	function do_syslog_core($log,$section)
	{
		global $artid,$sid,$mlxuid;
		if ($artid)	// tyyp tuli meilist, vaja kirja panna
		{
			if (is_number($artid))
			{
				$sid = (int)$sid;
				$this->db_query("SELECT * FROM ml_mails WHERE id = $sid");
				$ml_msg = $this->db_next();

				$this->db_query("SELECT ml_users.*,objects.name as name FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE id = $artid");
				if (($ml_user = $this->db_next()))
				{
					$this->_log("pageview",$ml_user["name"]." (".$ml_user["mail"].") tuli lehele $log meilist ".$ml_msg["subj"],$section);

					// and also remember the guy
					// set a cookie, that expires in 3 years
					setcookie("mlxuid",$artid,time()+3600*24*1000,"/");
				}
			}
		}
		else
		if ($mlxuid)
		{
			$this->db_query("SELECT ml_users.*,objects.name as name FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE id = $mlxuid");
			if (($ml_user = $this->db_next()))
			{
				$this->_log("pageview",$ml_user["name"]." (".$ml_user["mail"].") vaatas lehte $log",$section);
			}
		}
		else
		{
			$this->_log("pageview",sprintf(LC_MENUEDIT_LOOKED_SITE,$log),$section);
		}
	}

	function do_syslog($section = 0)
	{
		// now build the string to put in syslog
		$log = "";
		$names = array();
		foreach($this->path as $val)
		{
			$names[] = $this->menu_chain[$val]["name"];
		};

		if ($GLOBALS["tbl_sk"] != "")
		{
			$tbld = aw_global_get("fg_table_sessions");
			foreach($tbld[$GLOBALS["tbl_sk"]] as $url)
			{
				preg_match("/restrict_search_val=([^&$]*)/",$url,$mt);
				$names[] = urldecode($mt[1]);
			}
		}

		$log = join(" / ",$names);
		$this->do_syslog_core($log,$section);
	}

	function request_startup()
	{
		$section = aw_global_get("section");
		$realsect = $this->check_section($section);
		$_mn = $this->get_menu($realsect);
		$set_lang_id = false;
		if ($_mn)
		{
			if ($_mn["type"] != MN_CLIENT)
			{
				$set_lang_id = $_mn["lang_id"];
			};
		}
		else
		{
			$_obj = $this->get_object($realsect);
			$set_lang_id = $_obj["lang_id"];
		};

		$q = "SELECT name FROM languages WHERE id = '$set_lang_id' AND status = 2";
		$this->db_query($q);
		$row = $this->db_next();

		if ($row && $set_lang_id)
		{
			aw_global_set("lang_id",$set_lang_id);
		}
		else
		{
			$realsect = $this->cfg["frontpage"];
		};

		aw_global_set("section",$realsect);
	}

	function check_section($section, $show_errors = true)
	{
		$frontpage = $this->cfg["frontpage"];

		// kui sektsiooni viimane märk on "-", paneme selle objekti sees püsti
		// raw flagi

		if (substr($section,-1) == "-")
		{
			$this->raw = 1;
			// cut the minus sign
			$section = substr($section,0,-1);
		};
		
		// cut the / from the end
		// so that http://site/alias and http://site/alias/ both work
		if (substr($section,-1) == "/")
		{

			$section = substr($section,0,-1);
		};

		if ($section == "")
		{
			return $frontpage < 1 ? 1 : $frontpage;
		}

		// sektsioon ei olnud numbriline
		if (!is_number($section)) 
		{

			// first I have to check whether the alias contains /-s and if so, split
			// the url into pieces
			$sections = explode("/",$section);

			// if it contains a single $section, it is now located in $sections[0]
			
			// oh boy, that hurt. we accepted random crap as section name and then
			// fed it directly to the SQL server (get_object_by_alias)
			// so now we check whether the name contains anything besides alphanumeric and _
			// and if so, log it as crack attempt

			// and if that check is not good enough, we need something like "is_valid_section"
			$prnt = 0;
			foreach($sections as $skey => $sval)
			{
				global $DBUG;
				if ($DBUG)
				{
					print "checking $sval<br>";
				}
				if (preg_match("/\W/",$sval))
				{
					$obj = false;
				}
				else
				if ($obj !== false)
				{
					// vaatame, kas selle nimega aliast on?
					$obj = $this->_get_object_by_alias($sval/*,$prnt*/);
					//$obj = $this->_get_object_by_alias2($sval,$prnt);

					// need to check one more thing, IF prnt = 0 then fetch the parent
					// of this object and see whether it has an alias. if so, do not
					// let him access this menu directly

					// and why the hell not? 
					// this broke aw.struktuur.ee and why is it good anyway? - terryf

/*					if ($prnt == 0)
					{
						$pobj = $this->get_object($obj["parent"]);
						if (strlen($pobj["alias"]) > 0)
						{
							$obj = false;
						}
					};*/

/*					if ( ($prnt != 0) && ($obj["parent"] != $prnt) )
					{
						$obj = false;
					}
					else
					{*/
						$prnt = $obj["oid"];
//					};

				};
			};

			// nope. mingi skriptitatikas? voi cal6
			// inside joked ruulivad exole duke ;)
			// nendele kes aru ei saanud - cal6 ehk siis kalle volkov - ehk siis okia tyyp 
			// oli esimene kes aw seest kala leidis - kui urli panna miski oid, mida polnud, siis asi hangus - see oli siis kui 
			// www.struktuur.ee esimest korda v2lja tuli. 
			// niiet nyyd te siis teate ;)
			// - terryf
			if (!$obj) 
			{
				if ($show_errors)
				{
					$this->_log("menuedit",sprintf(LC_MENUEDIT_TRIED_ACCESS,$section));
					// neat :), kui objekti ei leita, siis saadame 404 koodi
					header ("HTTP/1.1 404 Not Found");
					printf(E_ME_NOT_FOUND);
					exit;
				}
				else
				{
					return false;
				}
			} 
			else 
			{
				$section = $obj["oid"];
			};
		} 
		else 
		{
			// mingi kontroll, et kui sektsioon ei eksisteeri, siis näitame esilehte
			if (!(($section > 0) && ($this->get_object($section)))) 
			{
				$this->_log("menuedit",sprintf(LC_MENUEDIT_TRIED_ACCESS2,$section));
				$section = $frontpage;
			}
		};

		return $section;
	}

	// well, mul on vaja kuvada see asi popupi sees, niisiis tegin ma miniversiooni folders.tpl-ist
	// ja lisasin siia uue parameetri
	// no, that $popup is really not needed anymore anylonger
	function gen_folders($period,$popup = 0)
	{
		if ($this->cfg["tree_type"] == "java")
		{
			return $this->gen_java_tree($period);
		};

		if ($this->cfg["site_id"] == 88)
		{
			$this->read_template("folders_no_periods.tpl");
		}
		else
		{
			$this->read_template("folders.tpl");
		};

		$arr = array();
		$mpr = array();
		$this->listacl("objects.status != 0 AND objects.class_id = ".CL_PSEUDO);
		// listib koik menyyd ja paigutab need arraysse
		$this->db_listall("objects.status != 0 AND menu.type != ".MN_FORM_ELEMENT,true);
		while ($row = $this->db_next())
		{
			if ($this->can("view",$row["oid"]))
			{
				$row["name"] = str_replace("\"","&quot;", $row["name"]);
				$arr[$row["parent"]][] = $row;
				$mpr[] = $row["parent"];
			}
		}
		// objektipuu
		$tr = $this->rec_tree(&$arr, $this->cfg["admin_rootmenu2"],$period);

		// kodukataloom
		$tr.=$this->mk_homefolder(&$arr);

		// the whole she-bang
		$arr = array();
		$this->db_listall("objects.status = 2 AND menu.type = ".MN_ADMIN1,true,true);
		while ($row = $this->db_next())
		{
			$arr[$row["parent"]][] = $row;
		}
		$tr.= $this->rec_admin_tree(&$arr, $this->cfg["amenustart"]);

		$this->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => $this->cfg["admin_rootmenu2"],
			"uid" => aw_global_get("uid"),
			"date" => $this->time2date(time(),2)
		));

		// perioodide tropp.
		if ($this->cfg["per_oid"])
		{
			classload("periods");
			$dbp = new db_periods($this->cfg["per_oid"]);
			$act_per_id = $dbp->get_active_period();
			$dbp->clist();
			$pl = array();
			$actrec = 0;
			// loeme k6ik perioodid sisse
			while ($row = $dbp->db_next())
			{
				if ($row["id"] == $act_per_id)
				{
					$actrec = $row["rec"];
				};
				$pl[$row["rec"]] = $row;
			}
			// leiame praegune +-3
			$ar = array();
			for ($i=$actrec-6; $i <= ($actrec+6); $i++)
			{
				if (isset($pl[$i]))
				{
					if ($pl[$i]["id"] == $act_per_id)
					{
						$ar[$pl[$i]["id"]] = $pl[$i]["description"].MN_ACTIVE;
					}
					else
					{
						$ar[$pl[$i]["id"]] = $pl[$i]["description"];
					}
				}
			}
			$ar[0] = MN_PERIODIC;
			$this->vars(array(
				"periods" => str_replace("\n","",$this->picker($period,$ar))
			));
		}
		$this->vars(array(
			"rooturl" => $this->mk_my_orb("right_frame", array("parent" => $this->cfg["admin_rootmenu2"]))
		));
		return $this->parse();
	}

	function gen_java_tree($period)
	{
		$this->read_template("javatree.tpl");
		$this->vars(array(
			"session" => $GLOBALS["automatweb"],
			"uid" => aw_global_get("uid"),
			"site_id" => aw_ini_get("site_id"),
			"date" => $this->time2date(time(),2),
			"demon_server" => $this->cfg["java_tree_update_server"],
			"demon_port" => $this->cfg["java_tree_update_port"],
			"rootmenu" => $this->cfg["admin_rootmenu2"],
		));
		return $this->parse();
	}

	function rec_homefolder(&$arr,$parent)
	{
		if (!is_array($arr[$parent]))
		{
			return "";
		}

		$baseurl = $this->cfg["baseurl"];
		$ret = "";
		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			$sub = $this->rec_tree(&$arr,$row["oid"],0);
			$this->vars(array(
				"name" => $row["name"],
				"id" => $row["oid"],
				"parent" => $row["parent"],
				"iconurl" => $row["icon_id"] != "" ? $baseurl."/automatweb/icon.".$this->cfg["ext"]."?id=".$row["icon_id"] : $baseurl."/automatweb/images/ftv2doc.gif",
				"url" => $this->mk_my_orb("right_frame",array("parent" => $row["oid"]))
			));
			$this->homefolders[$row["oid"]] = $row["oid"];
			if ($sub == "")
			{
				$ret.=$this->parse("DOC");
			}
			else
			{
				$ret.=$this->parse("TREE").$sub;
			};
		}
		return $ret;
	}

	////
	// !Loob kasutaja kodukataloogi
	function mk_homefolder(&$arr)
	{
		$udata = $this->get_user();
		$uid = aw_global_get("uid");
		$admin_rootmenu2 = $this->cfg["admin_rootmenu2"];
		$ext = $this->cfg["ext"];
		$baseurl = $this->cfg["baseurl"];

		// k6igepealt loeme k6ik kodukatalooma all olevad menyyd
		$this->db_query("SELECT menu.*,objects.* FROM menu
					LEFT JOIN objects ON objects.oid = menu.id
					WHERE oid = ".$udata["home_folder"]);
		if (!($hf = $this->db_next()))
		{
			$this->raise_error(ERR_MNEDIT_NOFOLDER,sprintf(MN_E_NO_HOME_FOLDER,$uid),true);
		};
		
		// when we create the home folders we write down which ones are shown
		// so we won't show them again under shared folders
		$this->homefolders = array();

		$ret = $this->rec_homefolder($arr, $hf["oid"]);

		$this->vars(array(
			"name" => $hf["name"],
			"id" => $hf["oid"], 
			"parent" => $admin_rootmenu2,
			"iconurl" => $this->get_icon_url("homefolder",""),
			"url" => $this->mk_my_orb("right_frame",array("parent" => $hf["oid"]))
		));
		$hft = $this->parse("TREE");

		// now we need to make a list of all the shared folders of all the users.
		// we do that by simply scanning the array of all folders for visible menus with type MN_HOME_FOLDER_SUB
		// that should work, because if acl is checked, then only folders that are shared to this user will be visible
		// and we exclude the users own home folder menus cause they would be duplicated there otherwise
		$shared_arr = $this->get_shared_arr(&$arr,$this->homefolders);
		$shares = "";
		reset($shared_arr);
		while (list(,$v) = each($shared_arr))
		{
			$this->vars(array(
				"name"	=> $v["name"],
				"id"	=> $v["oid"],		
				"parent"=> SHARED_FOLDER_ID,
				"iconurl" => $row["icon_id"] ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : $baseurl."/automatweb/images/ftv2doc.gif",
				"url"	=> $this->mk_my_orb("right_frame", array("parent" => $v["oid"]))
			));
			$shares.=$this->parse("DOC");
		}

		$this->vars(array(
			"name"=> "SHARED FOLDERS",
			"id" => SHARED_FOLDER_ID,		
			"parent" => $hf["oid"],
			"iconurl" => $this->get_icon_url("shared_folders",""),
			"url" => $this->mk_my_orb("right_frame",array("parent" => SHARED_FOLDER_ID))
		));
		if ($shares != "")
		{
			$shfs = $this->parse("TREE");
		}
		else
		{
			$shfs = $this->parse("DOC");
		};

		// now we need to make a list of all the groups created by this user
		classload("users_user");
		$dbu = new users_user;
		$dbu->listgroups(-1,-1,4);
		$grps_arr = array();
		while ($row = $dbu->db_next())
		{
			$row["oid"] = $row["gid"];
			$grps_arr[$row["parent"]][] = $row;
		}
		$dgid = $dbu->get_gid_by_uid($uid);
		$grptree = $this->rec_tree_grps(&$grps_arr, $dgid);

		$this->vars(array(
			"name"		=> "GROUPS",
			"id"			=> "gr_".$dgid,
			"parent"	=> $hf["oid"],
			"iconurl" => $this->get_icon_url("hf_groups",""),
			"url"			=> $this->mk_orb("mk_grpframe",array("parent" => $dgid),"groups")
		));
		if ($grptree != "")
		{
			$grps = $this->parse("TREE");
		}
		else
		{
			$grps = $this->parse("DOC");
		}

		$ret = $hft.$shfs.$shares.$grps.$grptree.$ret;

		return $ret;
	}

	function get_add_menu($arr)
	{
		extract($arr);
		$ret = "";

		$sep = "\n";
		if ($sharp)
		{
			$sep = "#";
		}

		// check if any parent menus have config objects attached
		$atc_id = 0;
		$ch = $this->get_object_chain($id);
		foreach($ch as $oid => $od)
		{
			if ($od["meta"]["add_tree_conf"])
			{
				$atc_id = $od["meta"]["add_tree_conf"];
			}
		}

		if ($atc_id)
		{
			$atc_inst = get_instance("add_tree_conf");
			$atc_root = $atc_inst->get_root_for_user($atc_id);
			if ($atc_root)
			{
				$menu_cache = get_instance("menu_cache");
				$menu_cache->make_caches();
				$menus = $menu_cache->get_menus_below($atc_root);

				$mn = array($atc_root);
				foreach($menus as $_oid => $_dat)
				{
					$mn[] = $_oid;
				}

				$objs = array();
				$mns = join(",",$mn);
				if ($mns != "")
				{
					$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_OBJECT_TYPE." AND status = 2 AND lang_id = ".aw_global_get("lang_id")." AND parent IN (".$mns.") ORDER BY jrk");
					while ($row = $this->db_next())
					{
						$objs[$row["parent"]][] = $row;
					}
				}

				$cnt = 0;
				$counts = array($atc_root => 0);
				foreach($objs as $prnt => $arr)
				{
					$cnt++;
					if (!isset($counts[$prnt]))
					{
						$counts[$prnt] = $cnt;
						$ret .= $cnt."|".((int)$counts[$arr[$prnt]["parent"]])."|".$menus[$prnt]["name"]."||".$sep;
					}
					if (is_array($arr))
					{
						foreach($arr as $row)
						{
							$meta = $this->get_object_metadata(array("metadata" => $row["metadata"]));

							if ($meta["type"] == "__all_objs")
							{
								$cldata = array();
								foreach($this->cfg["classes"] as $clid => $_cldata)
								{
									if ($_cldata["name"] != "")
									{
										$cldata[$_cldata["name"][0]][$_cldata["file"]] = $_cldata["name"];
									}
								}

								ksort($cldata);
								foreach($cldata as $letter => $clns)
								{
									$cnt++;
									$ret .= $cnt."|".((int)$counts[$prnt])."|".$letter."||".$sep;
									$cp = $cnt;
									asort($clns);
									foreach($clns as $cl_file => $cl_name)
									{
										$addlink = $this->mk_my_orb("new", array("parent" => $id, "period" => $period), $cl_file, true, true);

										$cnt++;
										$ret .= $cnt."|".((int)$cp)."|".$cl_name."|$addlink|list".$sep;
									}
								}
							}
							else
							{
								$addlink = $this->mk_my_orb("new", array("parent" => $id, "period" => $period), $this->cfg["classes"][$meta["type"]]["file"], true, true);

								$cnt++;
								$ret .= $cnt."|".((int)$counts[$row["parent"]])."|".$row["name"]."|$addlink|list".$sep;
							}
						}
					}
				}
				if ($ret_data)
				{
					return $ret;
				}
				else
				{
					die($ret);
				}
			}
		}
		if ($ret_data)
		{
			return ($this->req_get_default_add_menu(0, $id, $period, $sep));
		}
		else
		{
			die($this->req_get_default_add_menu(0, $id, $period, $sep));
		}
	}

	function get_az_def_menu($pt, $parent, $period, $sep)
	{
		$counts = array();
		$cldata = array();
		foreach($this->cfg["classes"] as $clid => $_cldata)
		{
			if ($_cldata["name"] != "")
			{
				$cldata[$_cldata["name"][0]][$_cldata["file"]] = $_cldata["name"];
			}
		}

		$cnt = 2000;
		ksort($cldata);
		foreach($cldata as $letter => $clns)
		{
			$cnt++;
			$ret .= $cnt."|".((int)($pt+4))."|".$letter."||".$sep;
			$cp = $cnt;
			asort($clns);
			foreach($clns as $cl_file => $cl_name)
			{
				$addlink = $this->mk_my_orb("new", array("parent" => $parent, "period" => $period), $cl_file, true, true);

				$cnt++;
				$ret .= $cnt."|".((int)$cp)."|".$cl_name."|$addlink|list".$sep;
			}
		}
		return $ret;
	}

	function req_get_default_add_menu($prnt, $parent, $period, $sep)
	{
		$ret = "";
		if (is_array($this->cfg["classes"]) && $prnt == 0)
		{
			$tcnt = 0;
			foreach($this->cfg["classes"] as $clid => $cldata)
			{
				if ($cldata["parents"] != "")
				{
					$parens = explode(",", $cldata["parents"]);
					if (in_array($prnt, $parens))
					{
						$addlink = $this->mk_my_orb("new", array("parent" => $parent, "period" => $period), $cldata["file"], true, true);
						$tcnt++;
						$ret .= ($tcnt)."|0|".$cldata["name"]."|$addlink|list".$sep;
					}
				}
			}
			$ret.="separator".$sep;
		}

		if (is_array($this->cfg["classfolders"]))
		{
			foreach($this->cfg["classfolders"] as $fid => $fdata)
			{
				if ($fdata["parent"] == $prnt)
				{
					$_fid = $fid+4;
					$_fprnt = ($fdata["parent"] == 0 ? 0 : $fdata["parent"] + 4);
					$ret .= $_fid."|".((int)$_fprnt)."|".$fdata["name"]."||list".$sep;
					$ret .= $this->req_get_default_add_menu($fid, $parent, $period, $sep);
					if ($fdata["all_objs"])
					{
						$ret .= $this->get_az_def_menu($fid, $parent, $period, $sep);
					}
				}
			}
		}

		if (is_array($this->cfg["classes"]) && $prnt != 0)
		{
			foreach($this->cfg["classes"] as $clid => $cldata)
			{
				if ($cldata["parents"] != "")
				{
					$parens = explode(",", $cldata["parents"]);
					if (in_array($prnt, $parens))
					{
						$addlink = $this->mk_my_orb("new", array("parent" => $parent, "period" => $period), $cldata["file"], true, true);
						$ret .= ($clid+1000)."|".((int)($prnt+4))."|".$cldata["name"]."|$addlink|list".$sep;
					}
				}
			}
		}
		return $ret;
	}

	function get_popup_data($args = array())
	{
		extract($args);
		if ($addmenu == 1)
		{
			$this->get_add_menu($args);
		}
		$obj = $this->get_object($id);


		$sep = "\n";
		if ($sharp)
		{
			$sep = "#";
		}

		$baseurl = $this->cfg["baseurl"];

		if ($obj["class_id"] == CL_PSEUDO)
		{
			$ourl = $this->mk_my_orb("right_frame", array("id" => $id, "parent" => $obj["oid"],"period" => $period), "menuedit",true,true);
			if ($type == "js")
			{
				$this->vars(array(
					"link" => $ourl,
					"text" => "Open"
				));
				$retval = $this->parse("MENU_ITEM");
			}
			else
			{
				$retval = "0|0|Open|".$ourl."|list".$sep;
			}
		}

		if ($this->can("edit", $id))
		{
			$churl = $this->mk_my_orb("change", array("id" => $id, "parent" => $obj["parent"],"period" => $period), $this->cfg["classes"][$obj["class_id"]]["file"],true,true);
			if ($type == "js")
			{
				$this->vars(array(
					"link" => $churl,
					"text" => "Change"
				));
				$retval .= $this->parse("MENU_ITEM");
			}
			else
			{
				$retval .= "1|0|Change|".$churl."|list".$sep;
			}

			$cuturl = $this->mk_my_orb("cut", array("reforb" => 1, "id" => $id, "parent" => $obj["parent"],"sel[$id]" => "1"), "menuedit",true,true);
			if ($type == "js")
			{
				$this->vars(array(
					"link" => $cuturl,
					"text" => "Cut"
				));
				$retval .= $this->parse("MENU_ITEM");
			}
			else
			{
				$retval .= "2|0|Cut|".$cuturl."|list".$sep;
			}
		}

		$copyurl = $this->mk_my_orb("copy", array("reforb" => 1, "id" => $id, "parent" => $obj["parent"],"sel[$id]" => "1","period" => $period), "menuedit",true,true);
		if ($type == "js")
		{
			$this->vars(array(
				"link" => $copyurl,
				"text" => "Copy"
			));
			$retval .= $this->parse("MENU_ITEM");
		}
		else
		{
			$retval .= "3|0|Copy|".$copyurl."|list".$sep;
		}

		if ($this->can("delete", $id))
		{
			$delurl = $this->mk_my_orb("delete", array("reforb" => 1, "id" => $id, "parent" => $obj["parent"],"sel[$id]" => "1","period" => $period), "menuedit",true,true);
			if ($type == "js")
			{
				$this->vars(array(
					"link" => $delurl,
					"text" => "Delete"
				));
				$retval .= $this->parse("MENU_ITEM");
			}
			else
			{
				$retval .= "4|0|Delete|".$delurl."|list".$sep;
			}
		}

		if ($this->can("admin", $id))
		{
			$delurl = $baseurl."/automatweb/editacl.".$this->cfg["ext"]."?file=menu.xml&oid=".$id;
			if ($type == "js")
			{
				$this->vars(array(
					"link" => $delurl,
					"text" => "ACL"
				));
				$retval .= $this->parse("MENU_ITEM");
			}
			else
			{
				$retval .= "5|0|ACL|".$delurl."|list".$sep;
			}
		}

		if ($ret_data)
		{
			return $retval;
		}

		print $retval;
		exit;
	}

	function get_branch($args = array())
	{
		extract($args);
		// Header
		$baseurl = $this->cfg["baseurl"];
		$this->format = $format;
		$this->java_branches = array();
		if (not($parent))
		{
			$parent = $this->cfg["amenustart"];
			// kui parentit antud pole, siis esimese elemendina tagastame selle AW ikooni
			$rooturl = $this->mk_my_orb("right_frame", array("parent" => $this->cfg["admin_rootmenu2"]));
			$this->_send_branch_line(0,0,"AutomatWeb",$rooturl,$baseurl . "/automatweb/images/aw_ikoon.gif");
		};

		// grupid

		// sisumenüüd.
		$this->_gen_menu_branch($args);

		// kodukataloog, kui oleme esimesel tasemel
		if (not($args["parent"]))
		{
			$udata = $this->get_user();
			$hf_id = (int)$udata["home_folder"];
			// there is a bunch of objects with parent=0, and we dont want to show
			// those unter the home folder
			if ($hf_id == 0)
			{
				$hf_id = -1;
			};
			$this->db_query("SELECT menu.*,objects.* FROM menu
						LEFT JOIN objects ON objects.oid = menu.id
						WHERE oid = '$hf_id'");
			if ($hf = $this->db_next())
			{

				$url = $this->mk_my_orb("right_frame",array("parent" => $hf["oid"]));
				$iconurl = $this->get_icon_url("homefolder","");
				// allpool peab ka acli kontrollima :(
				$q = "SELECT oid FROM objects LEFT JOIN menu ON (objects.oid = menu.id) WHERE objects.status != 0 AND ( (objects.class_id = 1) OR (objects.class_id = 39) ) AND (menu.type != " . MN_FORM_ELEMENT . " AND menu.type != " . MN_ADMIN1 . ") AND objects.parent = '$hf[oid]' AND (objects.lang_id = ".aw_global_get("lang_id") . " OR menu.type = 69)";
				$this->db_query($q);
				$subcnt = 0;
				while ($drow = $this->db_next())
				{
					if ($this->can("view", $drow["oid"]))
					{
						$subcnt++;
					}
				}
				$this->_send_branch_line($hf["oid"],$subcnt,$hf["name"],$url,$iconurl);
			};
		}
			
		
		// programmid
		$this->_gen_prog_branch(array("parent" => $parent));

		if ($format == "xmlrpc")
		{
			header("Content-Type: text/xml");
			print "<?xml version=\"1.0\"?>\n";
			print "<methodResponse>\n";
			print "<params>\n";
			print "<param>\n<value>\n<struct><member><name>result</name><value>\n";
			$mask .= "<member><name>object</name><value><array><data>";
			$mask .= "<i4>%d</i4>\n";
			$mask .= "<i4>%d</i4>\n";
			$mask .= "<string>%s</string>\n";
			$mask .= "<string>%s</string>\n";
			$mask .= "<string>%s</string></data></array></value></member>\n";
		}
		else
		{
			$mask = "%d\t%d\t%s\t%s\t%s\n";
		};

		foreach($this->java_branches as $dt)
		{
			printf($mask,$dt["oid"],$dt["subcnt"],$dt["name"],$dt["url"],$dt["iconurl"]);
		}

		if ($format == "xmlrpc")
		{
			print "</value>\n";
			print "</member>\n";
			print "</struct>\n";
			print "</value>\n";
			print "</param>\n";
			print "</params>\n";
			print "</methodResponse>\n";
		};
		exit;
	}

	function get_hf_branch($args = array())
	{
		$uid = aw_global_get("uid");
		$admin_rootmenu2 = $this->cfg["admin_rootmenu2"];
		$ext = $this->cfg["ext"];
		$baseurl = $this->cfg["baseurl"];

		$ret = $this->rec_homefolder($arr, $hf["oid"]);

	}

	function _gen_menu_branch($args = array())
	{
		extract($args);
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		$arr = array();
		$mpr = array();
		if (!$parent)
		{
			$parent = $this->cfg["admin_rootmenu2"];
		};

		$this->listacl("objects.status != 0 AND objects.parent = '$parent' AND objects.class_id = ".CL_PSEUDO);
		// listib koik menyyd ja paigutab need arraysse
		$this->db_listall("objects.status != 0 AND objects.parent = '$parent' AND (menu.type != ".MN_FORM_ELEMENT." AND menu.type != ".MN_ADMIN1.") AND objects.lang_id = ".aw_global_get("lang_id"),true);
		while ($row = $this->db_next())
		{
			$this->save_handle();
			// allpool peab ka acli kontrollima :(
			$q = "SELECT oid FROM objects LEFT JOIN menu ON (objects.oid = menu.id) WHERE objects.status != 0 AND ( (objects.class_id = 1) OR (objects.class_id = 39) ) AND (menu.type != " . MN_FORM_ELEMENT . " AND menu.type != ".MN_ADMIN1.") AND objects.parent = '$row[oid]' AND (objects.lang_id = ".aw_global_get("lang_id") . " OR menu.type = 69)";
			$this->db_query($q);
			$subcnt = 0;
			while ($drow = $this->db_next())
			{
				if ($this->can("view", $drow["oid"]))
				{
					$subcnt++;
				}
			}
			$this->restore_handle();

			if ($this->can("view",$row["oid"]))
			{
				if (!isset($row["mtype"]) || $row["mtype"] != MN_HOME_FOLDER)
				{
					if ($row["class_id"] == CL_PROMO)
					{
						$iconurl = $this->get_icon_url("promo_box","");
					}
					else
					if ($row["class_id"] == CL_BROTHER)
					{
						$iconurl = $this->get_icon_url("brother","");
					}
					else
					{
						$iconurl = $row["icon_id"] > 0 ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : $baseurl."/automatweb/images/ftv2doc.gif";
					}

					$url = $this->mk_my_orb("right_frame",array("parent" => $row["oid"], "period" => $period));
					$this->_send_branch_line($row["oid"],$subcnt,$row["name"],$url,$iconurl);
				}
			}
			else
			{
				$subcnt--;
			}
		}
	}
		
		
	function _gen_prog_branch($args = array())
	{
		extract($args);
		$baseurl = $this->cfg["baseurl"];

		//$this->db_listall("objects.status = 2 AND objects.parent = '$parent' AND menu.type = ".MN_ADMIN1." AND objects.lang_id = ".aw_global_get("lang_id"),true,true);
		$this->db_listall("objects.status = 2 AND objects.parent = '$parent' AND menu.type = ".MN_ADMIN1,true,true);
		$ext = $this->cfg["ext"];
		$blank = $this->mk_my_orb("blank");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			//$q = "SELECT count(*) AS cnt FROM objects LEFT JOIN menu ON (objects.oid = menu.id) WHERE objects.status = 2 AND menu.type = " . MN_ADMIN1 . " AND objects.parent = '$row[oid]' AND objects.lang_id = ".aw_global_get("lang_id");
			$q = "SELECT count(*) AS cnt FROM objects LEFT JOIN menu ON (objects.oid = menu.id) WHERE objects.status = 2 AND menu.type = " . MN_ADMIN1 . " AND objects.parent = '$row[oid]'";
			$subcnt = $this->db_fetch_field($q,"cnt");


			$this->restore_handle();
			$iconurl = $baseurl . isset($row["icon_id"]) && $row["icon_id"] != "" ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : ($row["admin_feature"] ? $this->get_feature_icon_url($row["admin_feature"]) : $baseurl."/automatweb/images/ftv2doc.gif");
			$name = $row["name"];
			$url = $row["link"] != "" ? $row["link"] : ($row["admin_feature"] ? $this->cfg["programs"][$row["admin_feature"]]["url"]: $blank);
			if (substr($url,0,7) != "http://")
			{
				$url = $baseurl . "/automatweb/" . $url;
			};
			$this->_send_branch_line($row["oid"],$subcnt,$name,$url,$iconurl);
		};
	}
	
	function _send_branch_line($oid,$subcnt,$name,$url,$iconurl)
	{
		if (strlen($name) == 0)
		{
			$name = "(nimetu)";
		};
		if ($this->format == "xmlrpc")
		{
			$name = str_replace("&","&amp;",$name);
			$url = str_replace("&","&amp;",$url);
			$iconurl = str_replace("&","&amp;",$iconurl);
		};

//		printf("%d\t%d\t%s\t%s\t%s\n",$oid,$subcnt,$name,$url,$iconurl);
		$this->java_branches[$oid] = array("oid" => $oid, "subcnt" => $subcnt, "name" => $name, "url" => $url, "iconurl" => $iconurl);
	}		

	// see oli siin java puu sees perioodide testimise jaoks
	function get_periods()
	{
		$per = get_instance("periods");
		$active = $per->rec_get_active_period();
                $per->clist();
                while($row = $per->db_next())
                {
			if ($row["id"] == $active)
			{
				$act = 1;
			}
			else
			{
				$act = 0;
			};
			//printf("%d\t%s\t%d\n",$row["id"],$row["description"],$act);
			printf("%d\t%s\t%d\n",$row["id"],"xxx",$act);
		};
		exit;
	}


	function get_shared_arr(&$arr,$exclude)
	{
		$ret = array();

		reset($arr);
		while (list($parent, $v) = each($arr))
		{
			reset($v);
			while (list(,$row) = each($v))
			{
				if (isset($row["mtype"]) && $row["mtype"] == MN_HOME_FOLDER_SUB && !$exclude[$row["oid"]])
				{
					$ret[] = $row;
				}
			}
		}
		return $ret;
	}

	function rec_admin_tree(&$arr,$parent)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}

		$admin_rootmenu2 = $this->cfg["admin_rootmenu2"];
		$ext = $this->cfg["ext"];
		$baseurl = $this->cfg["baseurl"];

		reset($arr[$parent]);
		$ret = "";
		while (list(,$row) = each($arr[$parent]))
		{
			if ($row["status"] != 2)
			{
				continue;
			}
			if ($row["admin_feature"] && !$this->prog_acl("view", $row["admin_feature"]) && ($this->cfg["acl"]["check_prog"]))
			{
				continue;
			}

			$sub = $this->rec_admin_tree(&$arr,$row["oid"]);

			if ($row["admin_feature"])
			{
				$sub.=$this->get_feature_tree($row["admin_feature"],$row["oid"]);
			}

			$iconurl = isset($row["icon_id"]) && $row["icon_id"] != "" ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : ($row["admin_feature"] ? $this->get_feature_icon_url($row["admin_feature"]) : $baseurl."/automatweb/images/ftv2doc.gif");
			$blank = $this->mk_my_orb("blank");
			$this->vars(array(
				"name"		=> $row["name"],
				"id"			=> ($row["admin_feature"] == 4 ? "gp_" : "").$row["oid"], 
				"parent"	=> ($parent == $this->cfg["amenustart"] ? $admin_rootmenu2 : $row["parent"]),
				"iconurl" =>  $iconurl,
				"url"			=> $row["link"] != "" ? $row["link"] : ($row["admin_feature"] ? $this->cfg["programs"][$row["admin_feature"]]["url"]: $blank)));

			if ($sub == "")
			{
				$ret.=$this->parse("DOC");
			}
			else
			{
				$ret.=$this->parse("TREE").$sub;
			}
		}
		return $ret;
	}

	function get_feature_tree($feat,$parent)
	{
		switch($feat)
		{
			// grupid
			case 4:
				return $this->mk_grp_tree($parent);
		}
	}

	function mk_grp_tree($parent)
	{
		classload("groups");
		$t = new groups;
		$t->listacl("objects.class_id = ".CL_GROUP." AND objects.status = 2");
		$t->listgroups("parent","asc",0,2);
		$grar = array();
		while ($row = $t->db_next())
		{
			$grar[$row["gid"]] = $row;
		}

		reset($grar);
		while (list($gid,$row) = each($grar))
		{
			// we must convert the parent member so that it actually points to
			// the parent OBJECT not the parent group
			$puta = isset($row["parent"]) ? $row["parent"] : 0;
			$row["parent"] = isset($grar[$puta]["oid"]) ? $grar[$puta]["oid"] : 0;

			if ($row["parent"] == 0)
			{
				$row["parent"] = $parent;
			}
			$grpcache[$row["parent"]][] = $row;
		}
		$ret = $this->rec_grp_tree(&$grpcache,$parent);
		return $ret;
	}

	function rec_grp_tree(&$arr,$parent)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}

		$ret = "";
		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			if (!$this->can("view",$row["oid"]) || $row["gid"] == aw_ini_get("groups.all_users_grp"))
			{
				continue;
			}

			$sub = $this->rec_grp_tree(&$arr,$row["oid"]);
			$this->vars(array(
				"name" => $row["name"],"id" => "gp_".$row["oid"], "parent" => "gp_".$row["parent"],
				"iconurl" => $this->cfg["baseurl"]."/automatweb/images/ftv2doc.gif",
				"url"			=> $this->mk_orb("mk_grpframe",array("parent" => $row["gid"]),"groups")
			));
			if ($sub == "")
			{
				$ret.=$this->parse("DOC");
			}
			else
			{
				$ret.=$this->parse("TREE").$sub;
			}
		}
		return $ret;
	}

	function rec_tree(&$arr,$parent,$period)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}

		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];

		$ret = "";
		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			if (!isset($row["mtype"]) || $row["mtype"] != MN_HOME_FOLDER)
			{
				// tshekime et kas menyyl on submenyysid
				// kui on, siis n2itame alati
				// kui pole, siis tshekime et kas n2idatakse perioodilisi dokke
				// kui n2idatakse ja menyy on perioodiline, siis n2itame menyyd
				// kui pole perioodiline siis ei n2ita
				$sub = $this->rec_tree(&$arr,$row["oid"],$period);
				$show = true;
				if ($sub == "" && $period > 0 && $row["mperiodic"] != 1) 
				{
					$show = false;
				}

				if ($show)
				{
					if ($row["class_id"] == CL_PROMO)
					{
						$url = $this->get_icon_url("promo_box","");
					}
					else
					if ($row["class_id"] == CL_BROTHER)
					{
						$url = $this->get_icon_url("brother","");
					}
					else
					{
						$url = $row["icon_id"] > 0 ? $baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"] : $baseurl."/automatweb/images/ftv2doc.gif";
					}
					$this->vars(array(
						"name" => $row["name"],
						"id" => $row["oid"],
						"parent" => $row["parent"],
						"iconurl" => $url,
						"url" => $this->mk_my_orb("right_frame",array("parent" => $row["oid"], "period" => $period))
					));
					if ($sub == "")
					{
						$ret.=$this->parse("DOC");
					}
					else
					{
						$ret.=$this->parse("TREE").$sub;
					}
				}
			}
		}
		return $ret;
	}

	function rec_tree_grps(&$arr,$parent)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
		{
			return "";
		}

		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			$sub = $this->rec_tree_grps(&$arr,$row["oid"]);
			$this->vars(array(
				"name" => $row["name"],
				"id" => "gr_".$row["oid"],
				"parent" => "gr_".$row["parent"],
				"iconurl" => $this->cfg["baseurl"]."/automatweb/images/ftv2doc.gif",
				"url" => $this->mk_orb("mk_grpframe",array("parent" => $row["gid"]),"groups")
			));
			$ret .= ($sub == "") ? $this->parse("DOC") : $this->parse("TREE");
		}
		return $ret;
	}

	function get_feature_icon_url($fid)
	{
		if (!is_array($this->pr_icons))
		{
			$c = new db_config;
			$this->pr_icons = aw_unserialize($c->get_simple_config("program_icons"));
		}
		$i = $this->pr_icons[$fid]["url"];
		return icons::check_url($i == "" ? "/automatweb/images/icon_aw.gif" : $i);
	}

	function get_icon_url($clid,$name)
	{
		classload("defs");
		return get_icon_url($clid,$name);
	}

	function add($arr)
	{
		extract($arr);

		if (!$this->can("add",$parent))
		{
			$this->raise_error(ERR_MNEDIT_ACL_NOADD,LC_MENUEDIT_NOT_ALLOW, true);
		}

		// just add the damn thing and be don withit
		$id = $this->new_object(array(
			"parent" => $parent, 
			"name" => "", 
			"class_id" => CL_PSEUDO, 
			"comment" => "","status" => 1
		));
		$this->db_query("INSERT INTO menu (id,link,type,is_l3,left_pane,right_pane,tpl_edit) VALUES($id,'$link',".MN_CONTENT.",0,1,1,0)");
		header("Location: ".$this->mk_my_orb("change", array("id" => $id, "parent" => $parent,"period" => $period)));
		die();
	}

	function nsubmit(&$arr)
	{
		$this->quote(&$arr);
		extract($arr);

		$updmenus = array();

		// stripime aliasest tyhikud v2lja
		str_replace(" ","",$alias);
		// kui muudame olemasolevat menyyd, siis ........
		if ($id) 
		{
			if (!$this->can("edit",$id))
			{
				$this->raise_error(ERR_MNEDIT_ACL_NOCHANGE,LC_MENUEDIT_NOT_ALLOW, true);
			}

			// küsime olemasoleva info menüü kohta
			$q = "SELECT objects.*,menu.* FROM objects
				LEFT JOIN menu ON menu.id = objects.oid
				WHERE oid = '$id'";
			$this->db_query($q);
			$menu = $this->db_next();

			$meta = $this->get_object_metadata(array(
				"metadata" => $menu["metadata"],
			));

			if ($arr["users_only"])
			{
				$meta["users_only"] = $arr["users_only"];
			};

			if ($arr["show_lead"])
			{
				$meta["show_lead"] = $arr["show_lead"];
			};

			if ($arr["tpl_dir"])
			{
				$meta["tpl_dir"] = $arr["tpl_dir"];
			};

			if ($arr["keywords"])
			{
				$meta["keywords"] = $arr["keywords"];
			}
			
			if ($arr["description"])
			{
				$meta["description"] = $arr["description"];
			}

			if ($arr["aip_filename"])
			{
				$meta["aip_filename"] = $arr["aip_filename"];
			}

			$this->set_object_metadata(array(
				"oid" => $id,
				"data" => array(
					"users_only" => $arr["users_only"],
					"img_timing" => $arr["img_timing"],
					"show_lead" => $arr["show_lead"],
					"tpl_dir" => $arr["tpl_dir"],
					"keywords" => $arr["keywords"],
					"description" => $arr["description"],
					"color" => $arr["color"],
					"template_type" => $template_type,
					"ftpl_edit" => $ftpl_edit,
					"ftpl_lead" => $ftpl_lead,
					"ftpl_view" => $ftpl_view,
					"aip_filename" => $arr["aip_filename"],
					"pclass" => $pclass,
					"pm_url_admin" => $pm_url_admin,
					"pm_url_menus" => $pm_url_menus,
					"objtbl_conf" => $objtbl_conf,
					"add_tree_conf" => $add_tree_conf,
					"cfgmanager" => $cfgmanager,
					"sort_by_name" => $sort_by_name
				),
			));
			
			if ($arr["keywords"] || $arr["description"])
			{
				classload("file","php");
				$awf = new file();

				$old = $awf->get_special_file(array(
					"name" => "meta.tags",
				));

				$meta = aw_unserialize($old);

				$meta[$id] = array(
					"keywords" => $arr["keywords"],
					"description" => $arr["description"],
				);
				$ser_meta = aw_serialize($meta,SERIALIZE_PHP);

				$awf->put_special_file(array(
					"name" => "meta.tags",
					"content" => $ser_meta,
				));
			}

			$sar = array(); $oidar = array();
			// leiame koik selle menüü vennad
			$q = "SELECT * FROM objects
				WHERE brother_of = $id AND status != 0 AND class_id = " . CL_BROTHER;
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$sar[$row["parent"]] = $row["parent"];
				$oidar[$row["parent"]] = $row["oid"];
			}

			$not_changed = array();
			$added = array();
			if (is_array($sections))
			{
				reset($sections);
				$a = array();
				while (list(,$v) = each($sections))
				{
					if ($sar[$v])
					{
						$not_changed[$v] = $v;
					}
					else
					{
						$added[$v] = $v;
					}
					$a[$v]=$v;
				}
			}
			$deleted = array();
			reset($sar);
			while (list($oid,) = each($sar))
			{
				if (!$a[$oid])
				{
					$deleted[$oid] = $oid;
				}
			}

			reset($deleted);
			while (list($oid,) = each($deleted))
			{
				$updmenus[] = $oid;
				$this->delete_object($oidar[$oid]);
			}
			reset($added);
			while(list($oid,) = each($added))
			{
				if ($oid != $id)	// no recursing , please
				{
					$noid = $this->new_object(array("parent" => $oid,"class_id" => CL_BROTHER,"status" => 2,"brother_of" => $id,"name" => $menu["name"],"comment" => $menu["comment"],"jrk" => 50));
					$this->db_query("INSERT INTO menu(id,link,type,is_l3,is_copied,periodic,tpl_edit,tpl_view,tpl_lead,active_period,clickable,target,mid,data,hide_noact)	
values($noid,'$menu[link]','$menu[type]','$menu[is_l3]','$menu[is_copied]','$menu[periodic]','$menu[tpl_edit]','$menu[tpl_view]','$menu[tpl_lead]','$menu[active_period]','$menu[clickable]','$menu[target]','$menu[mid]','$menu[data]','$menu[hide_noact]')");
					$updmenus[] = $noid;
				}
			}

			$status = ($active == "on") ? 2 : 1;
			$act_stamp = 0;
			$deact_stamp = 0;
			if ($autoactivate == "on") 
			{
				$act_stamp = mktime($activate_at["hour"],$activate_at["minute"],0,$activate_at["month"],
															$activate_at["day"],$activate_at["year"]);
				$autoactivate = 1;
			};
			if ($autodeactivate == "on") 
			{
				$deact_stamp = mktime($deactivate_at["hour"],$deactivate_at["minute"],0,$deactivate_at["month"],$deactivate_at["day"],$deactivate_at["year"]);
				$autodeactivate = 1;
			};

			$charr = array(
				"oid"      => $id,
				"name"     => $name,
				"status"   => $status,
				"autoactivate" => $autoactivate,
				"autodeactivate" => $autodeactivate,
				"activate_at" => $act_stamp,
				"deactivate_at" => $deact_stamp,
				"comment"  => $comment,
				"alias"    => $alias
			);
			if ($menu["class_id"] == CL_PSEUDO)	// if this is a real menu then change it's name and all its brothers names
			{
				$this->db_query("UPDATE objects set name = '$name' WHERE status != 0 AND class_id = ".CL_BROTHER." AND brother_of = $id");
			}
			else
			if ($menu["class_id"] == CL_BROTHER)
			{
				// don't change its' name
				unset($charr["name"]);
			}

			$sar = array();
			if (is_array($sss))
			{
				reset($sss);
				while (list(,$v) = each($sss))
				{
					$sar[$v] = $v;
				}
			}
			$sss = serialize($sar);
			$par = array();
			if (is_array($pers))
			{
				reset($pers);
				while (list(,$v) = each($pers))
				{
					$par[$v] = $v;
				}
			}
			$pers = serialize($par);
			// pildi uploadimine
			$t = get_instance("image");
			$ar = $t->add_upload_image("img_act", $id, $meta["img_act_id"]);
			$this->set_object_metadata(array(
				"oid" => $id,
				"data" => array(
					"img_act_id" => $ar["id"],
					"img_act_url" => image::check_url($ar["url"]),
				),
			));

			$num_menu_images = $this->cfg["num_menu_images"]; 

			$imgar = $meta["menu_images"];
			for ($i=0; $i < $num_menu_images; $i++)
			{
				if ($img_del[$i] == 1)
				{
					unset($imgar[$i]);
				}
				else
				{
					$ar = $t->add_upload_image("img".$i, $id, $imgar[$i]["id"]);
					$imgar[$i]["id"] = $ar["id"];
					$imgar[$i]["url"] = $ar["url"];
					$imgar[$i]["ord"] = $img_ord[$i];
				}
			}

			$timgar = array();
			$cnt = 0;
			for ($i=0; $i < $num_menu_images; $i++)
			{
				if ($imgar[$i]["id"])
				{
					$timgar[$cnt++] = $imgar[$i];
				}
			}

			// now sort the image array
			usort($timgar,array($this,"_menu_img_cmp"));

			$this->set_object_metadata(array(
				"oid" => $id,
				"key" => "menu_images",
				"value" => $timgar,
			));

			if ($timgar[0]["id"])
			{
				$tt = "img_id = ".$timgar[0]["id"].",img_url = '".$timgar[0]["url"]."',";
			}

			if ($number > 0)
			{
				$nn = "number = '$number',";
			}

			$this->save_menu_keywords($arr["grkeywords"],$id);

			$lang_id = aw_global_get("lang_id");
			// teeme seealso korda.

			// see k2ib siis nii et objekti metadata juures on kirjas et mis menyyde all see menyy on seealso
			// ja siis menu.seealso on serializetud array nendest, mis selle menyy all on seealso

			// niisis. k2ime k6ik praegused menyyd l2bi kus see menyy seealso on ja kui see on 2ra v6etud, siis kustutame ja kui jrk muutunud
			// siis muudame seda ja viskame nad arrayst v2lja
			if (!is_array($seealso))
			{
				$seealso = array();
			}

			if (is_array($meta["seealso_refs"][$lang_id]))
			{
				foreach ($meta["seealso_refs"][$lang_id] as $mid)
				{
					if (!in_array($mid,$seealso))
					{
						// remove this one from the menus seealso list
						$m_sa = $this->db_fetch_field("SELECT seealso FROM menu WHERE id = $mid", "seealso");
						$m_sa_a = unserialize($m_sa);	
						unset($m_sa_a[$lang_id][$id]);
						$m_sa = serialize($m_sa_a);
						$this->db_query("UPDATE menu SET seealso = '$m_sa' WHERE id = $mid");
					}
					else
					{
						if ($seealso_order != $meta["seealso_order"])
						{
							// kui jrk on muutunud siis tuleb see 2ra muuta
							$m_sa = $this->db_fetch_field("SELECT seealso FROM menu WHERE id = $mid", "seealso");
							$m_sa_a = unserialize($m_sa);	
							$m_sa_a[$lang_id][$id] = $seealso_order;
							$m_sa = serialize($m_sa_a);
							$this->db_query("UPDATE menu SET seealso = '$m_sa' WHERE id = $mid");
						}
					}
				}
			}
			// nyt k2ime l2bi sisestet seealso array ja lisame need mis pole metadatas olemas juba
			$sas = $meta["seealso_refs"];
			unset($sas[$lang_id]);
			foreach($seealso as $m_said)
			{
				if (!isset($meta["seealso_refs"][$lang_id][$m_said]))
				{
					// tuleb lisada selle menyy juurde kirje
					$m_sa = $this->db_fetch_field("SELECT seealso FROM menu WHERE id = $m_said", "seealso");
					$m_sa_a = unserialize($m_sa);	
					$m_sa_a[$lang_id][$id] = $seealso_order;
					$m_sa = serialize($m_sa_a);
					$this->db_query("UPDATE menu SET seealso = '$m_sa' WHERE id = $m_said");
				}
				$sas[$lang_id][$m_said] = $m_said;
			}
			$this->set_object_metadata(array(
				"oid" => $id,
				"data" => array(
					"seealso_refs" => $sas,
					"seealso_order" => $seealso_order,
				),
			));

			$this->upd_object($charr);
			$this->_log("menuedit",sprintf(LC_MENUEDIT_CJANGED_MENU,$name));
			$q = "UPDATE menu SET 
							tpl_edit = '$tpl_edit',
							tpl_lead = '$tpl_lead',
							tpl_view = '$tpl_view',
							hide_noact = '$hide_noact',
							link = '$link',
							clickable = '$clickable',
							target = '$target',
							ndocs = '$ndocs',
							mid = '$arr[mid]',
							is_shop = '$is_shop',
							shop_id = '$shop',
							links = '$links',
							width = '$width',
							$tt
							$nn
							sss = '$sss',
							seealso = '$seealso',
							pers = '$pers',
							admin_feature = '$admin_feature',
							left_pane = '$left_pane',
							shop_parallel = '$shop_parallel',
							shop_ignoregoto = '$shop_ignoregoto',
							no_menus = '$no_menus',
							right_pane = '$right_pane',
							type = '$type'
							WHERE id = '$id'";
			$this->db_query($q);
		} 
		else 
		{
			if (!$this->can("add",$parent))
			{
				$this->raise_error(ERR_MNEDIT_ACL_NOADD,LC_MENUEDIT_NOT_ALLOW, true);
			}
			// teeme uue menyy
			$id = $this->new_object(array("parent" => $parent, "name" => $name, "class_id" => CL_PSEUDO, "comment" => $comment,"status" => 1));
			$this->db_query("INSERT INTO menu (id,link,type,is_l3,left_pane,right_pane) VALUES($id,'$link',$class_id,0,1,1)");
			if ($class_id == MN_HOME_FOLDER_SUB)
			{
				// keelame teistel selle n2gemise sharetud folderis
				$this->deny_obj_access($id);
			}
			$this->_log("menuedit",sprintf(LC_MENUEDIT_ADDED_SECTION,$name));
		}

		$updmenus[] = $id;
		$this->invalidate_menu_cache($updmenus);
		return $this->mk_orb("change", array("parent" => $arr["parent"],"id" => $id,"period" => $arr["period"]));
	}

	function change($arr)
	{
		extract($arr);
		global $period;
		$this->mk_path($id, "Muuda",$period);
		if (!$this->can("edit",$id))
		{
			$this->raise_error(ERR_MNEDIT_ACL_NOCHANGE,LC_MENUEDIT_NOT_ALLOW, true);
		}

		$basedir = $this->cfg["basedir"];
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		load_vcl("date_edit");
		$d_edit = new date_edit("x");
		$d_edit->configure(array("day" => 1,"month" => 2,"year" => 3,"hour" => 4,"minute" => 5));
		$this->db_query("SELECT objects.*,objects.alias AS alias,
										 menu.type as type, menu.link as link,
										 menu.tpl_edit as tpl_edit,
										 menu.tpl_view as tpl_view,
										 menu.tpl_lead as tpl_lead,
										 menu.hide_noact as hide_noact,
										 menu.clickable as clickable,
										 menu.target as target,
										 menu.ndocs as ndocs,
										 menu.mid as mid,
										 menu.img_id as img_id,
										 menu.links as links,
										 menu.sss as sss,
										 menu.number as number,
										 menu.icon_id as icon_id,
										 menu.admin_feature as admin_feature,
										 menu.is_shop as is_shop,
										 menu.shop_id as shop_id,
										 menu.seealso as seealso,
										 menu.width as width,
										 menu.left_pane as left_pane,
										 menu.right_pane as right_pane,
										 menu.pers as pers,
										 menu.shop_parallel as shop_parallel,
										 menu.shop_ignoregoto as shop_ignoregoto,
										 menu.no_menus as no_menus
										 FROM objects 
										 LEFT JOIN menu ON menu.id = objects.oid
										 WHERE oid = $id");
		if (!($row = $this->db_next()))
		{
			$this->raise_error(ERR_MNEDIT_NOMENU,"menuedit->gen_change_html($id): No such menu!", true);
		}

		$meta = $this->get_object_metadata(array(
			"metadata" => $row["metadata"],
		));

		if ($row["class_id"] == CL_PROMO)
		{
			classload("promo");
			$p = new promo;
			return $p->change(array("id" => $id));
		}

		if (strpos(aw_global_get("HTTP_USER_AGENT"),"MSIE") === false)
		{
			$this->read_template("nchange_plain.tpl");
		}
		else
		{
			$this->read_template("nchange.tpl");
		};

		// kysime infot adminnitemplatede kohta
		$q = "SELECT * FROM template WHERE type = 0 ORDER BY id";
		$this->db_query($q);
		$edit_templates = array("0" => "default");
		while($tpl = $this->db_fetch_row()) 
		{
			$edit_templates[$tpl["id"]] = $tpl["name"];
		};
		// kysime infot lyhikeste templatede kohta
		$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
		$this->db_query($q);
		$short_templates = array();
		while($tpl = $this->db_fetch_row()) 
		{
			$short_templates[$tpl["id"]] = $tpl["name"];
		};
		// kysime infot pikkade templatede kohta
		$q = "SELECT * FROM template WHERE type = 2 ORDER BY id";
		$this->db_query($q);
		$long_templates = array();
		while($tpl = $this->db_fetch_row()) 
		{
			$long_templates[$tpl["id"]] = $tpl["name"];
		};

		$bsar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER);
		while ($arow = $this->db_next())
		{
			$bsar[$arow["parent"]] = $arow["parent"];
		}

		classload("objects");
		$ob = new db_objects;
	  $activate_at = ($row["activate_at"]) ? $row["activate_at"] : "+24h";
		$deactivate_at = ($row["deactivate_at"]) ? $row["deactivate_at"] : "+48h";
		$this->dequote(&$row["name"]);

		if ($row["ndocs"] > 0)
		{
			$il = $this->parse("IS_LAST");
		}

		// kui see on adminni menyy, siis kuvame kasutajale featuuride listi, 
		// mille hulgast ta siis valida saab, et mis selle menyy alt avaneb. 
		if ($row["type"] == MN_ADMIN1)
		{
			$this->vars(array("admin_feature" => $this->picker($row["admin_feature"],$this->get_feature_sel())));
			$af = $this->parse("ADMIN_FEATURE");
		}

		$pm = "";
		if ($row["type"]  == MN_PMETHOD)
		{
			$aw_orb = get_instance("aw_orb");
			$pclasses = array("0" => "--choose--") + $aw_orb->get_public_classes();
			$this->vars(array(
				"pclasses" => $this->picker($meta["pclass"],$pclasses),
				"pmethods" => "",
				"pm_url_admin" => checked($meta["pm_url_admin"]),
				"pm_url_menus" => checked($meta["pm_url_menus"])
			));

			$pm = $this->parse("PMETHOD");
		};

		$num_menu_images = $this->cfg["num_menu_images"];
		$imgar = $meta["menu_images"];

		for ($i=0; $i < $num_menu_images; $i++)
		{
			$image = "";
			if ($imgar[$i]["id"])
			{
				$image = "<img src='".$imgar[$i]["url"]."'>";
			}
			$this->vars(array(
				"nr" => $i,
				"image" => $image,
				"img_ord" => $imgar[$i]["ord"]
			));
			$ims.=$this->parse("M_IMG");
		}
		$this->vars(array("M_IMG" => $ims));

		// keyword list
		classload("keywords");
		$kwds = new keywords;
		$all_kwds = $kwds->get_keyword_picker();
		$kwd_list = $this->get_menu_keywords($id);

		classload("shop");
		$sh = new shop;
		$shs = $sh->get_list();

		$icon = $row["icon_id"] ? "<img src=\"".$baseurl."/automatweb/icon.".$ext."?id=".$row["icon_id"]."\">" : ($row["admin_feature"] ? "<img src=\"".$this->get_feature_icon_url($row["admin_feature"])."\">" : "");

		classload("periods");
		$dbp = new db_periods($this->cfg["per_oid"]);

		$oblist = $ob->get_list();

		// seealso asi on nyt nii. et esiteks on metadata[seealso_refs] - seal on kirjas, mis menyyde all see menyy seealso item on
		// ja siis menu.seealso on nagu enne serializetud array menyydest mis selle menyy all seealso on et n2itamisel kiirelt teada saax
		$sa = $meta["seealso_refs"];
		$rsar = $sa[aw_global_get("lang_id")];

		classload("form_base");
		$fb = new form_base;
		$flist = $fb->get_flist(array("type" => FTYPE_ENTRY));

		$img2 = $meta["img_act_url"] != "" ? "<img src='".$meta["img_act_url"]."'>" : "";
		$template_sets = $this->cfg["template_sets"];
		$template_sets = array_merge(array("" => "kasuta parenti valikut"),$template_sets);

		$types = array(
			"69" => LC_MENUEDIT_CLIENT,
			"70" => LC_MENUEDIT_SECTION,
			"71" => LC_MENUEDIT_ADMINN_MENU,
			"72" => LC_MENUEDIT_DOCUMENT,
			"75" => LC_MENUEDIT_CATALOG,
			"77" => LC_MENUEDIT_PMETHOD,
		);

		$this->vars(array(
			"types" => $this->option_list($row["type"],$types),
			"grkeywords" => $this->multiple_option_list($kwd_list, $all_kwds),
			"parent"			=> $row["parent"], 
			"SA_ITEM"			=> $sal,
			"image_act"		=> $img2,
			"seealso"			=> $this->multiple_option_list($rsar,$oblist),
			"seealso_order" => $meta["seealso_order"],
			"color" => $meta["color"],
			"ADMIN_FEATURE"	=> $af,
			"PMETHOD" => $pm,
			"name"				=> htmlentities($row["name"]), 
			"number"			=> $row["number"],
			"comment"			=> $row["comment"], 
			"links"				=> checked($row["links"]), 
			"users_only"  => checked($meta["users_only"] == 1),
			"show_lead" => checked($meta["show_lead"] == 1),
			"id"					=> $id,
			"active"	    => checked($row["status"] == 2),
			"clickable"	    => checked($row["clickable"] == 1),
			"hide_noact"   => checked($row["hide_noact"] == 1),
			"alias"				=> $row["alias"],
			"created"			=> $this->time2date($row["created"],2),
			"target"		=> checked($row["target"]),
			"autoactivate" => checked($row["autoactivate"]),
			"autodeactivate" => checked($row["autodeactivate"]),
			"activate_at" => $d_edit->gen_edit_form("activate_at",$activate_at),
			"deactivate_at" => $d_edit->gen_edit_form("deactivate_at",$deactivate_at),
			"createdby"		=> $row["createdby"],
			"modified"		=> $this->time2date($row["modified"],2),
			"modifiedby"	=> $row["modifiedby"],
			"tpl_edit" => $this->option_list($row["tpl_edit"],$edit_templates),
			"tpl_view" => $this->option_list($row["tpl_view"],$long_templates),
			"tpl_lead" => $this->option_list($row["tpl_lead"],$short_templates),
			"tpl_dir" => $this->picker($meta["tpl_dir"],$template_sets),
			"section"			=> $this->multiple_option_list($sets["section"],$oblist),
			"sss"					=> $this->multiple_option_list(unserialize($row["sss"]),$oblist),
			"link"				=> $row["link"],
			"sep_checked"	=> checked($row["type"] == 4),
			"mid"	=> checked($row["mid"] == 1),
			"doc_checked"	=> checked($row["type"] == 6),
			"sections"		=> $this->multiple_option_list($bsar,$ob->get_list(false,true)),
			"real_id"			=> $row["brother_of"],
			"reforb"			=> $this->mk_reforb("submit",array("id" => $id, "parent" => $parent,"period" => $period)),
			"ndocs"				=> $row["ndocs"],
			"ex_menus"		=> $this->multiple_option_list($ob->get_list(false,false,$id),$ob->get_list(false,false,$id)),
			"icon"				=> $icon,
			"IS_LAST"			=> $il,
			"shop"				=> $this->picker($row["shop_id"],$shs),
			"is_shop"			=> checked($row["is_shop"]),
			"left_pane"		=> checked($row["left_pane"]),
			"right_pane"	=> checked($row["right_pane"]),
			"shop_parallel" => checked($row["shop_parallel"]),
			"shop_ignoregoto" => checked($row["shop_ignoregoto"]),
			"no_menus" => checked($row["no_menus"]),
			"width" => $row["width"],
			"img_timing" => $meta["img_timing"],
			"keywords" => $meta["keywords"],
			"description" => $meta["description"],
			"pers" => $dbp->period_mlist(unserialize($row["pers"])),
			"ftpl_edit" => $this->picker($meta["ftpl_edit"],$flist),
			"ftpl_view" => $this->picker($meta["ftpl_view"],$this->list_objects(array("class" => CL_FORM_OUTPUT))),
			"ftpl_lead" => $this->picker($meta["ftpl_lead"],$this->list_objects(array("class" => CL_FORM_OUTPUT))),
			"tpltype_form" => checked((int)$meta["template_type"] == TPLTYPE_FORM),
			"tpltype_tpl" => checked((int)$meta["template_type"] == TPLTYPE_TPL),
			"ftpl_edit_id" => (int)$meta["ftpl_edit"],
			"ftpl_lead_id" => (int)$meta["ftpl_lead"],
			"ftpl_view_id" => (int)$meta["ftpl_view"],
			"aip_filename" => $meta["aip_filename"],
			"objtbl_conf" => $this->picker($meta["objtbl_conf"], $this->list_objects(array("class" => CL_OBJ_TABLE_CONF, "addempty" => true))),
			"add_tree_conf" => $this->picker($meta["add_tree_conf"], $this->list_objects(array("class" => CL_ADD_TREE_CONF, "addempty" => true))),
			"cfgmanager" => $this->picker($meta["cfgmanager"], $this->list_objects(array("class" => CL_CFGMANAGER, "addempty" => true))),
			"sort_by_name" => checked($meta["sort_by_name"])
		));

		$op_list = $fb->get_op_list();
		reset($flist);
		while (list($id,) = each($flist))
		{
			if (!$form_id)
			{
				$form_id = $id;
			}
			$this->vars(array("form_id" => $id));
			if (is_array($op_list[$id]))
			{
				reset($op_list[$id]);
				$cnt = 0;
				$fop = "";
				while (list($op_id,$op_name) = each($op_list[$id]))
				{
					$this->vars(array("cnt" => $cnt, "op_id" => $op_id, "op_name" => $op_name));
					$fop.=$this->parse("FORM_OP");
					$cnt++;
				}
				$this->vars(array("FORM_OP" => $fop));
				$fo.=$this->parse("FORM");
			}
		}

		$this->vars(array(
			"FORM" => $fo,
			"CAN_BROTHER" => $row["class_id"] == CL_PSEUDO ? $this->parse("CAN_BROTHER") : "",
			"IS_BROTHER" => $row["class_id"] == CL_PSEUDO ? "" : $this->parse("IS_BROTHER"),
			"IS_SHOP"	=> ($row["is_shop"] ? $this->parse("IS_SHOP") : "")
		));

		return $this->parse();
	}

	////
	// !tagastab array adminni featuuridest, mida sobib ette s88ta aw_template->picker funxioonile
	function get_feature_sel()
	{
		$ret = array();
		reset($this->cfg["programs"]);
		while (list($id,$v) = each($this->cfg["programs"]))
		{
			$ret[$id] = $v["name"];
		}

		return $ret;
	}

	function create_homes()
	{
		$this->db_query("SELECT * FROM users");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$id = $this->new_object(array("parent" => 1, "name" => $row["uid"], "class_id" => 1, "comment" => $row["uid"]." kodukataloog"));
			$this->db_query("INSERT INTO menu (id,type) VALUES($id,".MN_HOME_FOLDER.")");
			$this->db_query("UPDATE users SET home_folder = $id WHERE uid = '$row[uid]'");
			echo "created for $row[uid] , id = $id<br>";
			flush();
			$this->restore_handle();
		}

		$this->invalidate_menu_cache();
	}

	////
	// !sets the icon ($icon_id) for menu $id
	function set_menu_icon($id,$icon_id)
	{
		$af = $this->db_fetch_field("SELECT admin_feature FROM menu WHERE id = $id","admin_feature");
		if ($af)
		{
			classload("config");
			$c = new db_config;
			$c->set_program_icon(array("id" => $af,"icon_id" => $icon_id));
		}
		$this->db_query("UPDATE menu SET icon_id = $icon_id WHERE id = $id");
	}

	function req_draw_menu($parent,$name,&$path,$ignore_path)
	{
		// FIXME: don't really need to do that every time
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		$menu_check_acl = $this->cfg["menu_check_acl"];
		$this->sub_merge = 1;

		global $DBUG;
		if ($DBUG)
		{
			print "parent = $parent<br>";
		};

		$this->level++;

		// needed to make creating links containing hiearchical aliases work
		if (not(is_array($this->menu_aliases)))
		{
			$this->menu_aliases = array();
		}

		// I don't care about the first level menus
		if ($this->level > 0)
		{
			if ($this->mar[$parent]["alias"])
			{
				array_push($this->menu_aliases,$this->mar[$parent]["alias"]);
			}
			else
			{
				array_push($this->menu_aliases,"n/a");
			};
		};

		$cnt = 0;

		if (!isset($this->mpr[$parent]) || !is_array($this->mpr[$parent]))
		{
			$this->level--;
			array_pop($this->menu_aliases);
			global $DBUG;
			if ($DBUG)
			{
				print "skip";
			};
			return 0;
		}
		
		$check_acl = false;

		if (is_array($menu_check_acl))
		{
			if (in_array($parent,$menu_check_acl))
			{
				$check_acl = true;
			};
		}
		
		// make the subtemplate names for this and the next level
		$mn = "MENU_".$name."_L".$this->level."_ITEM";
		$mn2 = "MENU_".$name."_L".($this->level+1)."_ITEM";

		$this->vars(array(
			"sel_menu_".$name."_L".$this->level."_cnt" => 0,
		));

		$in_path = in_array($parent,$path);
		if (is_array($this->menu_defaults[$name]))
		{
			if ($this->menu_defaults[$name][$parent] == $parent)
			{
				if (!in_array($this->menu_defs_name_map[$name], $path))
				{
					$in_path = true;
				}
			}
		}
		else
		{
			if ($this->menu_defaults[$name] == $parent)
			{
				if (!in_array($this->menu_defs_name_map[$name], $path))
				{
					$in_path = true;
				}
			}
		}

		$parent_tpl = $this->is_parent_tpl($mn2, $mn);
		if (!( ($in_path||$this->level == 1) || ($parent_tpl && $in_path) || $ignore_path))
		{
			// don't show unless the menu is selected (in the path)
			// or the next level subtemplates are nested in this one
			// which signifies that we sould show them anyway
			// ignore all these if the meny is a 1st level menu 
			$this->level--;
			array_pop($this->menu_aliases);
			global $DBUG;
			if ($DBUG)
			{
				print "skip";
			};
			return 0;
		}
		$this->vars(array(
			$mn => ""
		));

		$no_mid = false;
		// go over the menus on this level
		$l = "";
		$l_mid = "";
		reset($this->mpr[$parent]);

		global $DBX;
		if ($DBX && ($parent == 489))
		{
			print "<pre>";
			print_r($this->mpr[$parent]);
			print "</pre>";
		}

		// find out how many menus do we have so we know when to use
		// the _END moficator

		$tmp = $this->mpr[$parent];
		if ($this->mar[$parent]["meta"]["sort_by_name"])
		{
			uasort($tmp, create_function('$a,$b','if ($a["name"] > $b["name"]) { return 1;} else if ($a["name"] < $b["name"]) { return -1;} else {return 0;}'));
		}

		$total = sizeof($tmp) - 1;
		while (list(,$row) = each($tmp))
		{
			$bro = false;
			$row["mtype"] = $row["type"];
			// here we fake the brother menus
			if ($row["class_id"] == CL_BROTHER)
			{
				$trow = $this->mar[$row["brother_of"]];
				$trow["parent"] = $row["parent"];
				$trow["oid"] = $row["oid"];
				$row = $trow;
				$bro = true;
			}
			
			if ($row["oid"] == $this->section)
			{
				$this->subitems[$name . "_L" . $this->level]  = sizeof($this->mpr[$row["oid"]]);
			}
			// je, I know, this will kind of slow down things
			// hmhm. taimisin seda vibe esilehel - 0.05 sek. niiet mitte oluliselt. - terryf
			// kuigi, seda siin funxioonis kasutatakse aint n2dala vasaku paani tegemisex exole. ja see v6ix ikka n2dala koodis olla. 
			// njah, praegu ainult nädalas. Aga idee on selles, et metainfo välja kasutad ka muu info salvestamiseks,
			// mitte teha jarjest uusi valju juurde - duke
			// 
			// ok, point taken. nyt kasutatakse seda objekti metadatat ka ntx sellex et selektitud menyy pildi urli salvestada. - terryf
			// it's already uncompressed, use it
			$meta = $row["meta"];

			// see on siis nädala parema paani leadide näitamine
			// nõme häkk. FIX ME.
			if ($meta["show_lead"])
			{
				$activeperiod = aw_global_get("act_per_id");
				$this->save_handle();
				$q = "SELECT objects.oid,documents.* AS lead FROM objects LEFT JOIN documents ON (objects.oid = documents.docid) WHERE parent = $row[oid] AND status = 2 AND objects.period = '$activeperiod' AND class_id = " . CL_PERIODIC_SECTION;
				$this->db_query($q);
				$xdat = $this->db_next();

				if (!$xdat)
				{
					continue;
				};

				$done = $this->doc->gen_preview(array("docid" => $xdat["oid"], "tpl" => "nadal_film_side_lead.tpl","leadonly" => 1, "section" => $row["oid"],    "strip_img" => 0));

				$this->vars(array("lugu" => $done));
				
				$this->restore_handle();
			};

			if ($check_acl)
			{
				// sellele menüüle pole oigusi, me ei näita seda
				if (not($this->can("view",$row["oid"])))
				{
					continue;
				};
			};
			
			// only show content menus
			if ($row["mtype"] != MN_CONTENT && $row["mtype"] != MN_CLIENT && $row["mtype"] != MN_HOME_FOLDER_SUB && $row["mtype"] != MN_PMETHOD)
			{
				continue;
			}

			if ($row["hide_noact"] || $this->cfg["all_menus_makdp"] == true)
			{
				// also go through the menus below this one to find out if there are any documents beneath those
				// since then we must show the menu
				if (!$this->has_sub_dox($row["oid"]))
				{
					continue;
				}
			}

			// check if this menu is THE selected menu
			if ($this->sel_section == $row["oid"] && $this->is_template("MENU_".$name."_SEEALSO_ITEM"))
			{
				$this->do_seealso_items($row,$name);
				// center menus as used in www.stat.ee 
				if (!$this->active_doc)
				{
					$this->do_center_menu($row["oid"]);
				}
			}

			$is_sel = false;
			if (is_array($this->menu_defaults[$name]))
			{
				if ($this->menu_defaults[$name][$row["oid"]] == $row["oid"])
				{
					if (!in_array($this->menu_defs_name_map[$name], $path))
					{
						$is_sel = true;
					}
				}
			}
			else
			{
				if ($this->menu_defaults[$name] == $row["oid"])
				{
					if (!in_array($this->menu_defs_name_map[$name], $path))
					{
						$is_sel = true;
					}
				}
			}

			if (in_array($row["oid"], $path) || $is_sel)
			{
				$this->vars(array(
					"sel_menu_".$name."_L".$this->level."_cnt" => $cnt,
					"sel_menu_".$name."_L".$this->level."_name" => $row["name"],
					"sel_menu_".$name."_L".$this->level."_id" => $row["oid"],
					"sel_menu_".$name."_L".$this->level."_color" => $meta["color"],
					"sel_menu_".$name."_L".$this->level."_comment" => $row["comment"]
				));
				$this->sel_menus[$name][$this->level] = $row["oid"];
			}

			$this->vars(array($mn2."_N" => ""));
			$ap = "";

			if ($this->is_template($mn."_N"))
			{
				$cont = true;
				if (is_array($this->menu_defaults[$name]))
				{
					if ($this->menu_defaults[$name][$row["parent"]] == $row["parent"])
					{
						if (!in_array($this->menu_defs_name_map[$name], $path))
						{
							$cont = false;
						}
					}
				}
				else
				{
					if ($this->menu_defaults[$name] == $row["parent"])
					{
						if (!in_array($this->menu_defs_name_map[$name], $path))
						{
							$cont = false;
						}
					}
				}
				if (!in_array($row["parent"],$path) && $cont)
				{
					continue;
				}
				else
				{
					$mn=$mn."_N";
				}
			}

			if ($this->is_parent_tpl("MENU_".$name."_L".($this->level+1)."_ITEM2","MENU_".$name."_L".($this->level)."_ITEM_N2"))
			{
				$this->vars(array(
					"MENU_".$name."_L".($this->level+1)."_ITEM2" => "",
				));
			}

			$n = $this->req_draw_menu($row["oid"], $name, &$path,$parent_tpl);
			
			if ($cnt == $total && $this->is_template($mn."_END"))
			{
				$ap.="_END";	// last one of this level menus
			}
			elseif ($cnt == 0 && $this->is_template($mn."_BEGIN"))
			{
				$ap.="_BEGIN";	// first one of this level menus
			};
			
			$this_selected = false;
			if (in_array($row["oid"],$path) && $row["clickable"] == 1)
			{
				$ap.="_SEL";		// a selected menu
				$this_selected = true;
			};

			// if the current menu is set as the default active menu for this menu area
			// and the selected path does not go through the root of this menu area, then 
			// we should mark this menu as active
			if (is_array($this->menu_defaults[$name]))
			{
				if ($this->menu_defaults[$name][$row["oid"]] == $row["oid"])
				{
					if (!in_array($this->menu_defs_name_map[$name], $path))
					{
						$ap.="_SEL";		// a selected menu
						$this_selected = true;
					}
				}
			}
			else
			{
				if ($this->menu_defaults[$name] == $row["oid"])
				{
					if (!in_array($this->menu_defs_name_map[$name], $path))
					{
						$ap.="_SEL";		// a selected menu
						$this_selected = true;
					}
				}
			}

			if ($row["clickable"] != 1)
			{
				$ap.="_SEP";		// non-clickable menu
			};

			$is_mid = false;
/*			if ($row["mid"] == 1 && !in_array($row["parent"],$path))
			{
				// keskel olevad menyyd peavad ignoreerima seda et neid igaljuhul n2idatakse
				$no_mid = true;
				continue;
			}*/
			if ($row["mid"] == 1)
			{
				$ap.="_MID";		// menu in center
				$is_mid = true;
			};

			if ($this->is_template($mn.$ap."_NOSUB") && $n == 0)
			{
				$ap.="_NOSUB";	// menu without subitems
			};
			// if no correct combination exists, use the default
			if (!$this->is_template($mn.$ap))
			{
				$ap = "";	
			};

			if ($row["brother_of"])
			{
				$row = $this->mar[$row["brother_of"]];
			}

			$link = $this->make_menu_link(&$row);

			$target = ($row["target"] == 1) ? sprintf("target='%s'","_blank") : "";

			$imgurl2 = "";
			if ($this_selected)
			{
				if ($meta["img_act_url"] != "")
				{
					$imgurl = $meta["img_act_url"];
				}
				else
				{
					$imgurl = $row["img_url"];
				}
				$imgurl2 = image::check_url($imgurl);
			}
			else
			{
				$imgurl = $row["img_url"];
			}

			if ($imgurl != "")
			{
				$imgurl = sprintf("<img src='%s' border='0'>",image::check_url($imgurl));
			}
			else
			{
				$imgurl = "";
			};

			$num_menu_images = $this->cfg["num_menu_images"];
			$has_image = false;
			if (is_array($meta["menu_images"]))
			{
				$imgar = $meta["menu_images"];
			}
			else
			{
				$imgar = array();
			}
			for ($_i=0; $_i < $num_menu_images; $_i++)
			{
				if ($imgar[$_i]["url"] != "")
				{
					$imgurl = image::check_url($imgar[$_i]["url"]);
					$this->vars(array(
						"menu_image_".$_i => "<img src='".$imgurl."'>",
						"menu_image_".$_i."_url" => $imgurl
					));
					if (in_array($row["oid"], $path))
					{
						$this->vars(array(
							"sel_menu_".$name."_L".$this->level."_image_".$_i."_url" => $imgurl,
							"sel_menu_".$name."_L".$this->level."_image_".$_i => "<img src='".$imgurl."'>",
						));
					}
					$has_image = true;
				}
			}

			$this->vars(array(
				"text" 		=> $row["name"],
				"link" 		=> $link,
				"comment" 	=> $row["comment"],
				"section"	=> $row["oid"],
				"target" 	=> $target,
				"image"		=> $imgurl,
				"cnt" => $cnt,
				"sel_image_url" => $imgurl2,
				"color" => $meta["color"]
			));

			if ($has_image)
			{
				$_hi = "";
				if ($this->is_template("HAS_IMAGE"))
				{
					if ($this->is_template($mn.$ap))
					{
						$_hi =  $this->parse($mn.$ap.".HAS_IMAGE");
					}
					else
					{
						$_hi =  $this->parse($mn.".HAS_IMAGE");
					}
				}
				$this->vars(array(
					"HAS_IMAGE" => $_hi,
					"NO_IMAGE" => ""
				));
			}
			else
			{
				$_hi = "";
				if ($this->is_template("NO_IMAGE"))
				{
					$_hi = $this->parse($mn.$ap.".NO_IMAGE");
				}
				$this->vars(array(
					"HAS_IMAGE" => "",
					"NO_IMAGE" => $_hi 
				));
			}

			if (isset($this->mpr[$row["oid"]]) && is_array($this->mpr[$row["oid"]]))
			{
				$hs = "";
				if ($this->is_template($mn.$ap.".HAS_SUBITEMS_".$name))
				{
					$this->parse($mn.$ap.".HAS_SUBITEMS_".$name);
				}
				$hsl = "";
				if ($this->is_template($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level))
				{
					$hsl = $this->parse($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level);
				}
				if (in_array($row["oid"],$path))	// this menu is selected
				{
					$_tmp = "";
					//if ($this->is_template($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL"))
					list(,$_basename) = explode(".",$mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL");
					if ($this->is_template($_basename))
					{
						$_tmp = $this->parse($_basename);
					}
					else
					if ($this->is_template($ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL"))
					{
						//$_tmp = $this->parse($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL");
						$_tmp = $this->parse($ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL");
					}
					$this->vars(array(
							"HAS_SUBITEMS_".$name."_L".$this->level."_SEL" => $_tmp
					));
					if ($this->is_template($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL_MID"))
					{
						$_hm = false;
						foreach($this->mpr[$row["oid"]] as $_row)
						{
							if ($row["mid"] == 1)
							{
								$_hm = true;
							}
						}
						if ($_hm)
						{
							$hslm = $this->parse($mn.$ap.".HAS_SUBITEMS_".$name."_L".$this->level."_SEL_MID");
						}
					}
					$this->vars(array(
						"HAS_SUBITEMS_".$name."_L".$this->level."_SEL_MID" => $hslm
					));
				}
			}
			else
			{
				$hs = "";
				if ($this->is_template($mn.$ap.".NO_SUBITEMS_".$name))
				{
					$hs = $this->parse($mn.$ap.".NO_SUBITEMS_".$name);
				}
				$hsl = "";
				if ($this->is_template($mn.$ap.".NO_SUBITEMS_".$name."_L".$this->level))
				{
					$hsl = $this->parse($mn.$ap.".NO_SUBITEMS_".$name."_L".$this->level);
				}
			}
			$this->vars(array(
				"HAS_SUBITEMS_".$name => $hs,
				"NO_SUBITEMS_".$name => "",
				"HAS_SUBITEMS_".$name."_L".$this->level => $hsl,
				"NO_SUBITEMS_".$name."_L".$this->level => "",
			));


			// ok, menyyd ei n2idata juhul, kui ta pole selektitud ja template MENU_BLAH_L5666_ITEM_SELONLY on defineeritud
			$istplso = $this->is_template($mn."_SELONLY");
			$issel = $this->sel_section != $row["parent"];
			$selonly = !($istplso && $issel);
			// - va juhul kui sel tasemel, mis aktiivne on pol yhtegi menyyd, siis n2idatakse eelmise taseme omi
			if ($this->mar[$this->sel_section]["parent"] == $row["parent"])
			{
				// see on aktiivne tase - 1
				if (!is_array($this->mpr[$this->sel_section]))
				{
					$selonly = true;
				}
			}

			$noshowu = aw_global_get("uid") == "" && $meta["users_only"] && $this->cfg["no_show_users_only"] == true;
			// v6i menyy nimi on tyhi, v6i menyyle on 8eldud et users only ja kasutraja pole sisse loginud const.aw sees 
			// on defineeritud $no_show_users_only
			if ($selonly && $row["name"] != "" && !$noshowu && !$this->skip)
			{
				if ($this->is_template($mn.$ap))
				{
					if ($is_mid)
					{
						$l_mid.=$this->parse($mn.$ap);
						if ($GLOBALS["DBUG"] == 1)
						{
							echo "parse is_mid $mn $ap <br>";
						}
					}
					else
					{
						$l.=$this->parse($mn.$ap);
						if ($GLOBALS["DBUG"] == 1)
						{
							echo "parse $mn $ap <br>";
						}
					}
				}
			}
			$this->vars(array(
				$mn.$ap => "",
			));

			if (!$no_mid)
			{
				$this->vars(array($mn."_MID" => $l_mid));
			};

			if ($this->is_template($mn."2"))
			{
				if ($this->is_template($mn."2".$ap))
				{
					$l2.=$this->parse($mn."2".$ap);
				}
				$this->vars(array($mn."2".$ap => ""));
				$second = true;
			}

			if ($this->is_template($mn."_N2") && $this->sel_section == $row["parent"])
			{
				if ($this->is_template($mn."_N2".$ap))
				{
					$l2.=$this->parse($mn."_N2".$ap);
				}
				$this->vars(array($mn."_N2".$ap => ""));
				$second_n = true;
			}

			// ok, here's the tricky bit
			// if the next level subtemplate is nested in this levels subtemplate, then we must clear
			// the variable in the template parser for the next level 
			// cause if we don't and the next item on this level has no subitems
			// it will get this item's parsed submenus below it. 
			if ($parent_tpl)
			{
				$this->vars(array(
					"MENU_".$name."_L".($this->level+1)."_ITEM" => "",
				));
			}
			$cnt++;
		}

		$this->vars(array(
				$mn."_SEL" => "",
		));
		$this->vars(array($mn => $l));

		if (!$no_mid)
		{
			$this->vars(array($mn."_MID" => $l_mid));
		}
		if ($second)
		{
			$this->vars(array($mn."2" => $l2));
		}
		if ($second_n)
		{
			$this->vars(array($mn."_N2" => $l2));
		}
		$this->level--;
		array_pop($this->menu_aliases);
		return $cnt;
	}
			
	////
	// !Creates a link for the menu
	function make_menu_link(&$row)
	{
		$this->skip = false;
		if ($row["mtype"] == MN_PMETHOD)
		{
				global $DBX;
				if ($DBX)
				{
					print "<pre>";
					print_r($row);
					print "</pre>";
				};
			// I should retrieve orb definitions for the requested class
			// to figure out which arguments it needs and then provide
			// those
			$pclass = $row["meta"]["pclass"];
			if ($pclass)
			{
				list($_cl,$_act) = explode("/",$pclass);
				$aw_orb = get_instance("aw_orb");
				$meth = $aw_orb->get_public_method(array(
					"id" => $_cl,
					"action" => $_act,
				));
				$values = array();
				$err = false;
				foreach($meth["required"] as $key => $val)
				{
					if (in_array($key,array_keys($meth["values"])))
					{
						$values[$key] = $meth["values"][$key];
					}
					else
					{
						$err = true;
					};
				};

				foreach($meth["optional"] as $key => $val)
				{
					if (in_array($key,array_keys($meth["values"])))
					{
						$values[$key] = $meth["values"][$key];
					}
				};
				if (not($err))
				{
					$_sec = aw_global_get("section");
					if ($_sec)
					{
						$values["section"] = $_sec;
					};
					$link = $this->mk_my_orb($_act,$values,$_cl,$row["meta"]["pm_url_admin"],!$row["meta"]["pm_url_menus"]);
				}
				else
				{
					$this->skip = true;
				};
			}
			else
			{
				$link = "";
			};
		}
		else if ($row["link"] != "")
		{
			$link = $row["link"];
		}
		else
		{
			$link = $this->cfg["baseurl"] ."/";
			if ($this->cfg["long_section_url"])
			{
				if ($row["alias"] != "")
				{
					$link .= $row["alias"];
				}
				else
				{
					$link .= "index.".$this->cfg["ext"]."?section=".$row["oid"].$this->add_url;
				}
			}
			else
			{
				if ($row["alias"] != "")
				{
					$tmp = array();
					foreach($this->menu_aliases as $_al)
					{
						if ($_al != "n/a")
						{
							$tmp[] = $_al;
						}
					}
					$link .= join("/",$tmp);
					if (sizeof($tmp) > 0)
					{
						$link .= "/";
					};
					$link .= $row["alias"];
				}
				else
				{
					$link .= $row["oid"];
				};
			};
		}

		// this here bullshit is so that the static ut site will work
		// basically, rewrite_links is set if we are doing searching or some other non-static action
		// it is set in site_header.aw
		// and we need to make all links go to the static site
		// and this is where the magic happens
		// also in document::do_search and search_conf::search
		if (aw_global_get("rewrite_links"))
		{
			$exp = get_instance("export");
			if (!$exp->is_external($link))
			{
				$_link = $link;
				if (strpos($link, $this->cfg["baseurl"]) === false)
				{
					$link = $this->cfg["baseurl"].$link;
				}
				$exp->fn_type = aw_ini_get("search.rewrite_url_type");
				$link = $exp->rewrite_link($link);
				if (strpos($link, "class=search_conf") === false || strpos($link, "action=search") === false)
				{
					$link = $exp->add_session_stuff($link, aw_global_get("lang_id"));
					$_tl = $link;
					$link = $this->cfg["baseurl"]."/".$exp->get_hash_for_url(str_replace($this->cfg["baseurl"],"",$link),aw_global_get("lang_id"));
//					echo "made hash for link $_tl = $link <br>";
				}
				else
				{
					$link = str_replace($this->cfg["baseurl"],aw_ini_get("search.baseurl"),$link);
				};
				
				if ($link != $this->cfg["baseurl"]."/index.".$this->cfg["ext"] && 
						$link != $this->cfg["baseurl"]."/" &&
						$link != $this->cfg["baseurl"])
				{
					$exp->fn_type = aw_ini_get("search.rewrite_url_type");
					$link = $exp->rewrite_link($link);
					if (strpos($link, "class=search_conf") === false || strpos($link, "action=search") === false)
					{
						$link = $exp->add_session_stuff($link, aw_global_get("lang_id"));
						$_tl = $link;
						$link = $this->cfg["baseurl"]."/".$exp->get_hash_for_url(str_replace($this->cfg["baseurl"],"",$link),aw_global_get("lang_id"));
	//					echo "made hash for link $_tl = $link <br>";
					}
					else
					{
						$link = str_replace($this->cfg["baseurl"],aw_ini_get("search.baseurl"),$link);
					}
				}
			}
		}
		return $link;
	}

	////
	// !draws MENU_$name_SEEALSO_ITEM 's for the menu given in $row
	function do_seealso_items($row,$name)
	{
		$ext = $this->cfg["ext"];
		$baseurl = $this->cfg["baseurl"];
		$lang_id = aw_global_get("lang_id");

		$sa = unserialize($row["seealso"]);
		if (is_array($sa[$lang_id]))
		{
			reset($sa[$lang_id]);
			while (list($said,) = each($sa[$lang_id]))
			{
				$samenu = $this->mar[$said];
				if (!is_array($samenu))
				{
					// the menu was not loaded. load it.
					$this->save_handle();
					//$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE objects.oid = $said");
					//$samenu = $this->db_next();

					$samenu = $this->get_menu($said);
					$this->mar[$said] = $samenu;
					$this->restore_handle();
				}

				if ($samenu["link"] != "")
				{
					$link = $samenu["link"];
				}
				else
				{
					$link = $baseurl."/";
					$link .= ($samenu["alias"] != "") ? $samenu["alias"] :  $samenu["oid"];
				}

				// use uncompressed version
				$meta = $samenu["meta"];

				if (!($meta["users_only"] == 1 && aw_global_get("uid") == ""))
				{
					$this->vars(array(
						"target" => $samenu["target"] ? "target=\"_blank\"" : "",
						"link" => $link,
						"text" => str_replace("&nbsp;","",strip_tags($samenu["name"]))
					));
					$this->parse("MENU_".$name."_SEEALSO_ITEM");
				}
			}
		}
	}

	////
	// !exports menu $id and all below it
	function export_menus($arr)
	{
		extract($arr);

		classload("icons");
		$i = new icons();
		$this->get_feature_icon_url(0);	// warm up the cache

		$menus = array("0" => $id);

		// ok. now we gotta figure out which menus the user wants to export. 
		// he can select just the lower menus and assume that the upper onec come along with them.
		// biyaatch 

		// kay. so we cache the menus
		$this->db_listall();
		while ($row = $this->db_next())
		{
			$this->mar[$row["oid"]] = $row;
		}

		if (!is_array($ex_menus))
		{
			return;
		}
		// this keeps all the menus that will be selected
		$sels = array();	
		// now we start going through the selected menus
		reset($ex_menus);
		while (list(,$eid) = each($ex_menus))
		{
			// and for each we run to the top of the hierarchy and also select all menus 
			// so we will gather a list of all the menus we need. groovy.
			
			$sels[$eid] = $eid;
			while ($eid != $id && $eid > 0)
			{
				$sels[$eid] = $eid;
				$eid = $this->mar[$eid]["parent"];
			}
		}

		// so now we have a complete list of menus to fetch. 
		// so fetchemall
		reset($sels);
		while (list(,$eid) = each($sels))
		{
			$row = $this->mar[$eid];
			if ($allactive)
			{
				$row["status"] = 2;
			}
			$this->append_exp_arr($row,&$menus,$ex_icons,$i);
		}

		/// now all menus are in the array with all the other stuff, 
		// so now export it.
		header("Content-type: x-automatweb/menu-export");
		echo serialize($menus);
		die();
	}

	function append_exp_arr($db, $menus,$ex_icons,&$i)
	{
		$ret = array();
		$ret["db"] = $db;
		if ($ex_icons)
		{
			$icon = -1;
			// admin_feature icon takes precedence over menu's icon. so include just that.
			if ($db["admin_feature"] > 0)
			{
				$icon = $this->pr_icons[$db["admin_feature"]]["id"];
				if ($icon)
				{
					$icon = $i->get($icon);
				}
			}
			else
			if ($db["icon_id"] > 0)
			{
				$icon = $i->get($db["icon_id"]);
			}
			$ret["icon"] = $icon;
		}
		$menus[$db["parent"]][] = $ret;
	}

	////
	// !shows menus importing form
	function import($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_MENUEDIT_IMPORT_MENU);
		$this->read_template("import.tpl");
		$this->vars(array("reforb" => $this->mk_reforb("submit_import", array("parent" => $parent))));
		return $this->parse();
	}

	////
	// !does the actual menu importing bit
	function submit_import($arr)
	{
		extract($arr);

		$updmenus = array();
		if ($file_type == "text")
		{
			$this->do_text_import($arr);
		}
		else
		{
			global $fail;

			$f = fopen($fail, "r");
			$d = fread($f,filesize($fail));
			fclose($f);

			$menus = unserialize($d);
			$i_p = $menus[0];

			$this->req_import_menus($i_p, &$menus, $parent);
		}

		$this->invalidate_menu_cache($this->updmenus);

		return $this->mk_my_orb("right_frame", array("parent" => $parent));
	}

	function req_import_menus($i_p, &$menus, $parent)
	{
		if (!is_array($menus[$i_p]))
		{
			return;
		}
		$mt = $this->db_fetch_field("SELECT type FROM menu WHERE id= $parent","type");
		classload("icons");
		$i = new icons;
		reset($menus[$i_p]);
		while (list(,$v) = each($menus[$i_p]))
		{
			$db = $v["db"];
	
			$icon_id = 0;
			if (is_array($v["icon"]))
			{
				$icon_id = $i->get_icon_by_file($v["icon"]["file"]);
				if (!$icon_id)
				{
					// not in db, must add
					$icon_id = $i->add_array($v["icon"]);
				}
			}
			if ($mt == MN_HOME_FOLDER || $mt == MN_HOME_FOLDER_SUB)
			{
				$db["mtype"] = MN_HOME_FOLDER_SUB;	// so you can share them later on.
			}
			$id = $this->new_object(array("parent" => $parent,"name" => $db["name"], "class_id" => $db["class_id"], "status" => $db["status"], "comment" => $db["comment"], "jrk" => $db["jrk"], "visible" => $db["visible"], "alias" => $db["alias"], "periodic" => $db["periodic"]));
			$this->db_query("INSERT INTO menu 
						 (id,link,type,is_l3,periodic,clickable,target,mid,hide_noact,ndocs,admin_feature,number,icon_id,links) 
			VALUES ($id,'".$db["link"]."','".$db["mtype"]."','".$db["is_l3"]."','".$db["periodic"]."','".$db["clickable"]."','".$db["target"]."','".$db["mid"]."','".$db["hide_noact"]."','".$db["ndocs"]."','".$db["admin_feature"]."','".$db["number"]."',$icon_id,'".$db["links"]."')");

			$this->updmenus[] = $id;
			// tegime vanema menyy 2ra, teeme lapsed ka.
			$this->req_import_menus($db["oid"],$menus,$id);
		}
	}

	////
	// !cuts the selected objects
	function cut($arr)
	{
		extract($arr);

		$cut_objects = array();
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$cut_objects[$oid] = $oid;
			}
		}

		aw_session_set("cut_objects",$cut_objects);
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
	}

	////
	// !copies the selected objects
	function copy($arr)
	{
		extract($arr);

		// check if any objects that are to be copied need special handling
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$ob = $this->get_object($oid);
				if ($ob["class_id"] == CL_PSEUDO)
				{
					return $this->mk_my_orb("copy_feedback", array("parent" => $parent, "period" => $period, "sel" => $sel));
				}
			}
		}

		// if not, just copy the damn things
		$copied_objects = array();
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$r = $this->serialize(array("oid" => $oid));
				if ($r != false)
				{
					$copied_objects[$oid] = $r;
				}
			}
		}
		aw_session_set("copied_objects", $copied_objects);
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
	}

	function copy_feedback($arr)
	{
		extract($arr);
		$this->read_template("copy_feedback.tpl");
		$this->mk_path($parent, "Vali kuidaws objekte kopeerida");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_copy_feedback", array("parent" => $parent, "period" => $period,"sel" => $sel))
		));

		return $this->parse();
	}

	function submit_copy_feedback($arr)
	{
		extract($arr);
		aw_register_default_class_member("menuedit", "serialize_submenus", $ser_submenus);
		aw_register_default_class_member("menuedit", "serialize_subobjs",$ser_subobjs);

		$copied_objects = array();
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$r = $this->serialize(array("oid" => $oid));
				if ($r != false)
				{
					$copied_objects[$oid] = $r;
				}
			}
		}
		aw_session_set("copied_objects", $copied_objects);
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
	}

	////
	// !pastes the cut objects 
	function paste($arr)
	{
		extract($arr);

		$cut_objects = aw_global_get("cut_objects");
		$copied_objects = aw_global_get("copied_objects");

		$updmenus = array();

		if (is_array($cut_objects))
		{
			reset($cut_objects);
			while (list(,$oid) = each($cut_objects))
			{
				if ($oid != $parent)
				{
					$this->upd_object(array("oid" => $oid, "parent" => $parent,"period" => $period,"lang_id" => aw_global_get("lang_id")));
					$updmenus[] = $oid;
				}
			}
		}
		aw_session_set("cut_objects",array());

		if (is_array($copied_objects))
		{
			reset($copied_objects);
			while (list($oid,$str) = each($copied_objects))
			{
				if ($oid != $parent)
				{
					$this->unserialize(array("str" => $str, "parent" => $parent, "period" => $period));
				}
			}
		}

		$this->invalidate_menu_cache($updmenus);

		$GLOBALS["copied_objects"] = array();
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
	}

	function o_delete($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			reset($sel);
			while (list($ooid,) = each($sel))
			{
				$this->delete_object($ooid);
				$this->delete_aliases_of($ooid);
			}
		}
		if ($oid)
		{
			$this->delete_object($oid);
		}
		return $this->mk_orb("obj_list", array("parent" => $parent, "period" => $period));
		aw_session_set("copied_objects",array());
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period));
	}

	function make_menu_caches($where = "objects.status = 2")
	{
		$mc = get_instance("menu_cache");
		$mc->make_caches();
		upd_instance("menu_cache",$mc);
		$this->subs =  $mc->get_ref("subs");
		$this->mar =  $mc->get_ref("mar");
		$this->mpr =  $mc->get_ref("mpr");
	}

	function is_link_collection($section)
	{
		$p = $section; 
		$links = false;
		$cnt = 0;
		while ($p && ($cnt < 20))
		{
			$cnt++;
			if (isset($this->mar[$p]["links"]) && $this->mar[$p]["links"])
			{
				$p = 0;
				$links = true;
			}	
			$p = $this->mar[$p]["parent"];
		}
		return $links;
	}

	function is_shop($section)
	{
		$p = $section; 
		$links = false;
		$cnt = 0;
		while ($p && ($cnt < 20))
		{
			$cnt++;
			if (!is_array($this->mar[$p]))
			{
				//$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE oid = $p");
				// no point in overwriting it
				if (not($this->mar[$p]))
				{
					$this->mar[$p] = $this->get_menu($p);
				};
			}

			if (isset($this->mar[$p]["is_shop"]) && $this->mar[$p]["is_shop"] == 1)
			{
				$sh_id = $this->mar[$p]["shop_id"];
				$p = 0;
				$links = true;
			}	
			isset($this->mar[$p]["parent"]) ? $p = $this->mar[$p]["parent"] : $p = 0;
		}
		if (!$links)
		{
			return false;
		}
		else
		{
			// so, if that's a shop, we turn off the right pane. right.	
			$this->right_pane = false;
			return $sh_id;
		}
	}

	function show_periodic_documents($section,$obj)
	{
		$d = get_instance("document");
		$cont = "";
		// if $section is a periodic document then emulate the current period for it and show the document right away
		$d->set_opt("parent",$section);
		if ($obj["class_id"] == CL_PERIODIC_SECTION)
		{
			$template = $this->get_long_template($section);
			$activeperiod = $obj["period"];
			$cont = $d->gen_preview(array(
				"docid" => $section,
				"boldlead" => 1,
				"keywords" => 1,
				"tpl" => $template
			));
			$this->vars(array("docid" => $section));
			$this->active_doc = $section;
			$d->set_opt("shown_document",$section);
			$PRINTANDSEND = $this->parse("PRINTANDSEND");
		}
		else
		{
			$activeperiod = aw_global_get("act_per_id");
			$d->set_period($activeperiod);
			$d->list_docs($section, $activeperiod,2);
	
			// I need to  know that for the public method menus
			$d->set_opt("cnt_documents",$d->num_rows());

			if ($d->num_rows() > 1)		// the database driver sets this
			{
				$template = $this->get_lead_template($section);
				while($row = $d->db_next()) 
				{
					$d->save_handle();
					$d->set_period($row["period"]);
					$cont .= $d->gen_preview(array(
						"docid" => $row["docid"],
						"tpl" => $template,
						"leadonly" => 1,
						"section" => $section,
						"keywords" => 1,
						"doc"	=> $row));
					$d->restore_handle();
					$d->_init_vars();
				}; // while
			} // if
			// on 1 doku
			else 
			{
				$row = $d->db_next();
				$template = $this->get_long_template($section);
				$d->set_opt("shown_document",$row["docid"]);
				$cont = $d->gen_preview(array(
					"docid" => $row["docid"],
					"boldlead" => 1,
					"keywords" => 1,
					"tpl" => $template
				));
				$this->vars(array("docid" => $row["docid"]));
				$this->active_doc = $row["docid"];
				$PRINTANDSEND = $this->parse("PRINTANDSEND");
			}
		}
		upd_instance("document",$d);
		return $cont;
	}

	function show_documents($section,$docid,$template = "")
	{
		//classload("document");
		//$d = new document();
		$d = get_instance("document");
		// Vaatame, kas selle sektsiooni jaoks on "default" dokument
		if ($docid < 1) 
		{
			$docid = $this->get_default_document($section);
		};
		$ct = "";

		// oleks vaja teha voimalus feedbacki tegemiseks. S.t. doku voib 
		// lisaks enda sisule tekitada veel mingeid datat, mida siis menuedit
		// voiks paigutada saidi raami sisse. Related links .. voi nimekiri
		// mingitest artiklis esinevatest asjadest. You name it.
		$this->blocks = array();

		$template = $this->get_long_template($section);

		$metaref = array();
		
		$d->set_opt("parent",$section);
		
		if (is_array($docid)) 
		{
			$template = $this->get_lead_template($section);
			
			// I need to  know that for the public method menus
			$d->set_opt("cnt_documents",sizeof($docid));
		
			$template = $template == "" ? "plain.tpl" : $template;
			$template2 = file_exists($this->cfg["tpldir"]."/automatweb/documents/".$template."2") ? $template."2" : $template;
			$ct = ""; 
			$dk=1;
			// I hate this. docid on dokumendi id,
			// ja seda ei peaks arrayna kasutama
			reset($docid);
			while (list(,$did) = each($docid)) 
			{
				$d->_init_vars();
				$ct.=$d->gen_preview(array(
					"docid" => $did,
					"tpl" => ($dk & 1 ? $template : $template2),
					"leadonly" => 1,
					"section" => $section,
					"strip_img" => $strip_img,
					"tpls" => $tpls,
					"keywords" => 1,
					"no_strip_lead" => aw_global_get("document.no_strip_lead")
				));

				if ($d->referer)
				{
					$metaref[] = $d->referer;
				}

				$dk++;
			} // while
		} 
		else 
		{
			// kui docid on 0, siis leiame default doku
			if ($docid == 0)
			{
				$pobj = $this->get_object($section);
				$lastar = unserialize($pobj["last"]);
				// this is wrong, lang_id should be used
				if (is_array($lastar))
				{
					list(,$default_doc) = each($lastar);
					$docid = $default_doc;
				}
			};

			if ($docid)
			{
				$d->set_opt("cnt_documents",1);
				$d->set_opt("shown_document",$docid);

				$ct = $d->gen_preview(array(
					"docid" => $docid,
					"section" => $section,
					"no_strip_lead" => aw_ini_get("document.no_strip_lead"),
					"notitleimg" => 0,
					"tpl" => $template,
					"keywords" => 1,
					"boldlead" => aw_ini_get("document.boldlead")
				));

				if ($d->referer)
				{
					$metaref[] = $d->referer;
				};

				if ($d->no_left_pane)
				{
					$this->left_pane = false;
				}

				if ($d->no_right_pane)
				{
					$this->right_pane = false;
				}

				$this->vars(array(
					"docid" => $docid,
					"section" => $section,
				));
				
				$PRINTANDSEND = $this->parse("PRINTANDSEND");

				$this->vars(array(
					"section" => $section,
					"docid" => $docid,
					"PRINTANDSEND" => $PRINTANDSEND
				));

				$this->active_doc = $docid;

				if ($d->title != "")
				{
					$this->site_title = " / ".$d->title;
				}
				
				if (is_array($d->blocks))
				{
					$this->blocks = $this->blocks + $d->blocks;
				};
			}
		}
		$this->metaref = $metaref;
		upd_instance("document",$d);
		return $ct;
	}

	function get_path($section,$obj)
	{
		// now find the path through the menu
		$path = array();
		if ($obj["class_id"] == CL_PERIODIC_SECTION || $obj["class_id"] == CL_DOCUMENT)
		{
			$sec = $obj["parent"];
			$section = $obj["parent"];
		}
		else
		{
			$sec = $section; 
		}
		$cnt = 0;
		// kontrollime seda ka, et kas see "sec" yldse olemas on,
		// vastasel korral satume loputusse tsyklisse
		while ($sec && ($sec != 1)) 
		{
			$this->_push($sec);
			$sec = $this->mar[$sec]["parent"];
			$cnt++;
			if ($cnt > 1000)
			{
				$this->raise_error(ERR_MNED_HIER, "Error in object hierarchy, $sec is it's own parent!", true);
			}
		}
		// now the path is in the correct order on the "root" stack

		for ($i=0; $i < $cnt; $i++) 
		{
			$path[$i+1] = $this->_pop();
		};
		// and now in the $path array
		return $path;
	}

	function make_yah($path)
	{
		// now build "you are here" links from the path
		$ya = "";  
		$show = false;
		$cnt = count($path);

		$this->title_yah = "";
		$alias_path = array();

		for ($i=0; $i < $cnt; $i++)	
		{
			if ($show)
			{
				$ref = $this->mar[$path[$i+1]];
				if ($ref["alias"])
				{
					if (sizeof($alias_path) == 0)
					{
						$use_aliases = true;
					};

					if ($use_aliases)
					{
						array_push($alias_path,$ref["alias"]);
					};

					if ($use_aliases)
					{
						$linktext = join("/",$alias_path);
					};

					$link = $this->cfg["baseurl"]."/".$linktext;
				}
				else
				{
					$use_aliases = false;
					$link = $this->cfg["baseurl"]."/".$this->mar[$path[$i+1]]["oid"];
				};

				if ($this->mar[$path[$i+1]]["link"] != "")
				{
					$link = $this->mar[$path[$i+1]]["link"];
				}

				$this->vars(array(
					"link" => $link,
					"text" => str_replace("&nbsp;","",strip_tags($this->mar[$path[$i+1]]["name"])), 
					"ysection" => $this->mar[$path[$i+1]]["oid"]
				));

				$check_subs = ($this->subs[$this->mar[$path[$i+1]]["oid"]] > 0) || $this->cfg["yah_no_subs"];

				if ($this->mar[$path[$i+1]]["clickable"] == 1 && $check_subs)
				{
					$ya.=$this->parse("YAH_LINK");
					$this->title_yah.=" / ".$this->mar[$path[$i+1]]["name"];
				}
			}
			// don't show things that are before $frontpage
			if (isset($path[$i]) && isset($this->mar[$path[$i]]) && $this->mar[$path[$i]]["oid"] == $this->cfg["rootmenu"])
			{
				$show = true;
			}
		}

		// form table yah links get made here. 
		// basically the session contains a vriable fg_table_sessions that has all the possible yah links for 
		// all shown tables (and yeah, I know it is gonna be friggin huge. 
		// and no, I can't remove the old ones, cause the user might have other windows open
		// and if I remove all the other ones from the array, he will lose the yah link in other windows
		if ($GLOBALS["tbl_sk"] != "")
		{
			$tbld = aw_global_get("fg_table_sessions");
			foreach($tbld[$GLOBALS["tbl_sk"]] as $url)
			{
				preg_match_all("/restrict_search_yah\[\]=([^&$]*)/",$url,$mt);
				$this->vars(array(
					"link" => $url,
					"text" => urldecode($mt[1][count($mt[1])-1])
				));
				if (urldecode($mt[1][count($mt[1])-1]) != "")
				{
					$ya.=$this->parse("YAH_LINK");
				}
			}
		}
		return $ya;
	}

	////
	// !See jupp siin teeb promokasti
	function make_promo_boxes($section)
	{
		$doc = new document;
		$right_promo = "";
		$left_promo = "";
		$scroll_promo = "";

		$template = $this->get_lead_template($section);
		if ($this->cfg["lang_menus"])
		{
			$lai = "AND objects.lang_id = ".aw_global_get("lang_id");
		}
		if ($this->cfg["promo_lead_only"])
		{
			$leadonly = 1;
		}
		else
		{
			$leadonly = -1;
		};
		$q = "SELECT objects.*, template.filename as filename,menu.link as link,objects.metadata as metadata
				FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				LEFT JOIN template ON template.id = menu.tpl_lead
				WHERE objects.status = 2 AND objects.class_id = 22 AND (objects.site_id = ".$this->cfg["site_id"]." OR objects.site_id is null) $lai
				ORDER by jrk";
		$this->db_query($q);
		$gidlist = aw_global_get("gidlist");
		while ($row = $this->db_next())
		{
			if (not($row["filename"]))
			{
				continue;
			};

			$meta = $this->get_object_metadata(array("metadata" => $row["metadata"]));

			$found = false;

			if (!is_array($meta["groups"]) || count($meta["groups"]) < 1)
			{
				$found = true;
			}
			else
			{
				foreach($meta["groups"] as $gid)
				{
					if ($gidlist[$gid] == $gid)
					{
						$found = true;
					}
				}
			}

			$doc->doc_count = 0;
			// FIXME: promo boxes shouldn't use the comment field for holding that data
			$ar = unserialize($row["comment"]);
			if (((isset($ar["section"][$section]) && $ar["section"][$section]) || ($row["comment"] == "all_menus" && $row["site_id"] == $this->cfg["site_id"])) && $found)
			{
				// visible. so show it
				$this->save_handle();
				// get list of documents in this promo box
				$pr_c = "";
				$docid = $this->get_default_document($row["oid"],true);
				global $XXX;
				if ($XXX)
				{
					print "<pre>";
					print_r($docid);
					print "</pre>";
				};
				if (is_array($docid))
				{
					reset($docid);
					while (list(,$d) = each($docid))
					{
						if ($row["filename"])
						{
							$cont = $doc->gen_preview(array(
								"docid" => $d,
								"tpl" => $row["filename"],
								"leadonly" => $leadonly,
								"section" => $section,
								"strip_img" => false,
								"showlead" => 1,
								"boldlead" => 1,
								"no_strip_lead" => 1,
							));
							$pr_c .= str_replace("\r","",str_replace("\n","",$cont));
						}
					}
				}
				else
				{
					$pr_c.=$doc->gen_preview(array(
						"docid" => $docid, 
						"tpl" => $row["filename"],
						"leadonly" => $leadonly, 
						"section" => $section, 	
						"strip_img" => false,
						"showlead" => 1, 
						"boldlead" => 0,
						"no_strip_lead" => 1,
					));
				}

				dbg($pr_c);

				$this->vars(array(
					"comment" => $ar["comment"],
					"title" => $row["name"], 
					"content" => $pr_c,
					"url" => $row["link"],
					"link_caption" => $meta["link_caption"]
				));

				// which promo to use? we need to know this to use
				// the correct SHOW_TITLE subtemplate
				if ($ar["scroll"] == 1)
				{
					$use_tpl = "SCROLL_PROMO";
				}
				else
				if ($ar["down"] == 1)
				{
					$use_tpl = "DOWN_PROMO";
				}
				else
				if ($ar["up"] == 1)
				{
					$use_tpl = "UP_PROMO";
				}
				else
				if ($ar["right"] == 1)
				{
					$use_tpl = "RIGHT_PROMO";
				}
				else
				{
					$use_tpl = "LEFT_PROMO";
				};

				if ($meta["no_title"] != 1)
				{
					$this->vars(array("SHOW_TITLE" => $this->parse($use_tpl . ".SHOW_TITLE")));
				}
				else
				{
					$this->vars(array("SHOW_TITLE" => ""));
				}
				$ap = "";
				if ($row["link"] != "")
				{
					$ap = "_LINKED";
				}
				if ($this->used_promo_tpls[$use_tpl] != 1)
				{
					$ap.="_BEGIN";
					$this->used_promo_tpls[$use_tpl] = 1;
				}
				if ($ar["scroll"] == 1)
				{
					if ($this->is_template("SCROLL_PROMO".$ap))
					{
						$scroll_promo .= $this->parse("SCROLL_PROMO".$ap);
						$this->vars(array("SCROLL_PROMO".$ap => ""));
					}
					else
					{
						$scroll_promo .= $this->parse("SCROLL_PROMO");
						$this->vars(array("SCROLL_PROMO" => ""));
					}
				}
				else
				if ($ar["right"] == 1)
				{
					if ($this->is_template("RIGHT_PROMO".$ap))
					{
						$right_promo .= $this->parse("RIGHT_PROMO".$ap);
						$this->vars(array("RIGHT_PROMO".$ap => ""));
					}
					else
					{
						$right_promo .= $this->parse("RIGHT_PROMO");
						$this->vars(array("RIGHT_PROMO" => ""));
					}
				}
				else
				if ($ar["up"] == 1)
				{
					if ($this->is_template("UP_PROMO".$ap))
					{
						$up_promo .= $this->parse("UP_PROMO".$ap);
						$this->vars(array("UP_PROMO".$ap => ""));
					}
					else
					{
						$up_promo .= $this->parse("UP_PROMO");
						$this->vars(array("UP_PROMO" => ""));
					}
				}
				else
				if ($ar["down"] == 1)
				{
					if ($this->is_template("DOWN_PROMO".$ap))
					{
						$down_promo .= $this->parse("DOWN_PROMO".$ap);
						$this->vars(array("DOWN_PROMO".$ap => ""));
					}
					else
					{
						$down_promo .= $this->parse("DOWN_PROMO");
						$this->vars(array("DOWN_PROMO" => ""));
					}
				}
				else
				{
					if ($this->is_template("LEFT_PROMO".$ap))
					{
						$left_promo .= $this->parse("LEFT_PROMO".$ap);
						$this->vars(array("LEFT_PROMO".$ap => ""));
					}
					else
					{
						$left_promo .= $this->parse("LEFT_PROMO");
						$this->vars(array("LEFT_PROMO" => ""));
					}
				}
				// nil the variables that were imported for promo boxes
				// if we dont do that we can get unwanted copys of promo boxes
				// in places we dont want them
				$this->vars(array("title" => "", "content" => "","url" => ""));
				$this->restore_handle();
			}
		};

		$this->vars(array(
			"LEFT_PROMO" => $left_promo,
			"RIGHT_PROMO" => $right_promo,
			"UP_PROMO" => $up_promo,
			"DOWN_PROMO" => $down_promo,
			"SCROLL_PROMO" => $scroll_promo,
		));

	}

	function make_poll()
	{
		classload("poll");
		$t = new poll;
		$this->vars(array("POLL" => $t->gen_user_html()));
	}

	function make_search()
	{
		if ($this->is_template("SEARCH_SEL"))
		{
			global $section;
			$id = (int)$section;
			if (!$id)
			{
				$id=$this->cfg["frontpage"];
			}
			classload("search_conf");
			$t = new search_conf;
			$def = $GLOBALS["HTTP_GET_VARS"]["parent"] ? $GLOBALS["HTTP_GET_VARS"]["parent"] : $section;
			$sl = $t->get_search_list(&$def);
			$this->vars(array(
				"search_sel" => $this->option_list($def,$sl),
				"section" => $id,
				"str" => htmlentities($GLOBALS["HTTP_GET_VARS"]["str"])
			));
			$this->vars(array("SEARCH_SEL" => $this->parse("SEARCH_SEL")));
		}
	}

	function do_rdf($section,$obj,$format,$docid)
	{
		classload("rdf");
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		$rdf = new rdf(array(
			"about" => "$baseurl/index.$ext/section=$section/format=rss",
			"title" => $obj["name"],
			"description" => $obj["description"],
			"link" => "$baseurl/index.$ext/section=$section",
		));

		// read all the menus and other necessary info into arrays from the database
		$this->make_menu_caches();

		// laeme dokumentide klassi
		classload("periods","document");
		
		// leiame, kas on tegemist perioodilise rubriigiga
		$periodic = $this->is_periodic($section);

		// loome sisu
		$d = new document();
		if ($periodic) 
		{
			// if $section is a periodic document then emulate the current period for it
			if ($obj["class_id"] == CL_PERIODIC_SECTION)
			{
				$activeperiod = $obj["period"];
			}
			else
			{
				$activeperiod = aw_global_get("act_per_id");
			}
			$d->set_period($activeperiod);
			$d->list_docs($section, $activeperiod,2);
			$cont = "";
			if ($d->num_rows() > 1) 
			{
				while($row = $d->db_next()) 
				{
					$rdf->add_item($row);
				};
			} 
			// on 1 doku
			else 
			{
				$q = "SELECT docid,title,lead,author FROM documents WHERE docid = '$section'";
				$this->db_query($q);
				$row = $this->db_next();
				$rdf->add_item($row);
			} 
		}
		else 
		{
			// sektsioon pole perioodiline
			if ($docid < 1) 
			{
				$docid = $this->get_default_document($section);
			};

			if (is_array($docid)) 
			{
				// I hate this. docid on dokumendi id,
				// ja seda ei peaks arrayna kasutama
				reset($docid);
				while (list(,$did) = each($docid)) 
				{
					$q = "SELECT * FROM documents WHERE docid = '$did'";
					$this->db_query($q);
					$row = $this->db_next();
					$rdf->add_item($row);
				} 
			} 
			else 
			{
				$q = "SELECT * FROM documents WHERE docid = '$docid'";
				$this->db_query($q);
				$row = $this->db_next();
				$rdf->add_item($row);
			}
		}

		header("Content-Type: text/xml");
		print $rdf->gen_output();
		// I know, I know, it's damn ugly
		die();
	}

	function make_banners()
	{
		$banner_defs = $this->cfg["banners"];
		$banner_server = $this->cfg["banner_server"];
		$ext = $this->cfg["ext"];
		$uid = aw_global_get("uid");

		if (!is_array($banner_defs))
		{
			return;
		}

		reset($banner_defs);
		while (list($name,$gid) = each($banner_defs))
		{
			$htmlf = $banner_server."/banner.$ext?gid=$gid&html=1";
			if ($uid != "")
			{
				$htmlf.="&aw_uid=".$uid;
			}
			$f = fopen($htmlf,"r");
			$fc = fread($f,100000);
			fclose($f);

			$fc = str_replace("[ss]","[ss".$gid."]",$fc);

			$this->vars(array("banner_".$name => $fc));
		}
	}

	////
	// !generates the ui for the shop
	function do_shop($section,$shop_id)
	{
		classload("shop");
		$sh = new shop;
		$ret = $sh->show(array("section" => $section,"id" => $shop_id));
		$this->vars(array("shop_menus" => $sh->shop_menus));
		return $ret;
	}

	function make_langs()
	{
		$lang_id = aw_global_get("lang_id");
		classload("languages");
		$langs = new languages;
		$lar = $langs->listall();
		$l = "";
		foreach($lar as $row)
		{
			$this->vars(array(
				"name" => $row["name"],
				"lang_id" => $row["id"]
			));
			if ($row["id"] == $lang_id)
			{
				$l.=$this->parse("SEL_LANG");
			}
			else
			{
				$l.=$this->parse("LANG");
			}
		}
		$this->vars(array(
			"LANG" => $l,
			"SEL_LANG" => ""
		));
	}

	function convimages()
	{
		$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu on menu.id = objects.oid WHERE class_id = ".CL_PSEUDO." AND status != 0");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			
			$meta = $this->get_object_metadata(array(
				"metadata" => $row["metadata"]
			));

			$cnt = 0;
			$imgar = array();

			$t = get_instance("image");
			if ($row["img_id"])
			{
				$img = $t->get_img_by_id($row["img_id"]);
				$this->vars(array(
					"image" => "<img src='".$img["url"]."'>",
					"img_ord1" => $meta["img1_ord"]
				));
				$imgar[$cnt]["id"] = $row["img_id"];
				$imgar[$cnt]["url"] = $img["url"];
				$imgar[$cnt]["ord"] = $meta["img1_ord"];
				$cnt++;
			}

			if ($meta["img2_id"])
			{
				$img2 = $t->get_img_by_id($meta["img2_id"]);
				$this->vars(array(
					"image2" => "<img src='".$img2["url"]."'>",
					"img_ord2" => $meta["img2_ord"]
				));
				$imgar[$cnt]["id"] = $meta["img2_id"];
				$imgar[$cnt]["url"] = $img2["url"];
				$imgar[$cnt]["ord"] = $meta["img2_ord"];
				$cnt++;
			}
			if ($meta["img3_id"])
			{
				$img3 = $t->get_img_by_id($meta["img3_id"]);
				$this->vars(array(
					"image3" => "<img src='".$img3["url"]."'>",
					"img_ord3" => $meta["img3_ord"]
				));
				$imgar[$cnt]["id"] = $meta["img3_id"];
				$imgar[$cnt]["url"] = $img3["url"];
				$imgar[$cnt]["ord"] = $meta["img3_ord"];
				$cnt++;
			}
			if ($meta["img4_id"])
			{
				$img4 = $t->get_img_by_id($meta["img4_id"]);
				$this->vars(array(
					"image4" => "<img src='".$img4["url"]."'>",
					"img_ord4" => $meta["img4_ord"]
				));
				$imgar[$cnt]["id"] = $meta["img4_id"];
				$imgar[$cnt]["url"] = $img4["url"];
				$imgar[$cnt]["ord"] = $meta["img4_ord"];
				$cnt++;
			}
			if ($meta["img5_id"])
			{
				$img5 = $t->get_img_by_id($meta["img5_id"]);
				$this->vars(array(
					"image5" => "<img src='".$img5["url"]."'>",
					"img_ord5" => $meta["img5_ord"]
				));
				$imgar[$cnt]["id"] = $meta["img5_id"];
				$imgar[$cnt]["url"] = $img5["url"];
				$imgar[$cnt]["ord"] = $meta["img5_ord"];
				$cnt++;
			}

			usort($imgar,array($this,"_menu_img_cmp"));

			$this->set_object_metadata(array(
				"oid" => $row["oid"],
				"key" => "menu_images",
				"value" => $imgar
			));

			echo "menu $row[oid] <br>\n";
			flush();
			$this->restore_handle();
		}
	}

	function do_menu_images($sel_menu_id)
	{
		$si_parent = $sel_menu_id;
		$imgs = false;
		while ($sel_image == "" && $si_parent)
		{
			$sel_menu_meta = $this->mar[$si_parent]["meta"];
//			echo "si_parent = $si_parent , meta = <pre>", var_dump($sel_menu_meta),"</pre> <br>";
			if (is_array($sel_menu_meta["menu_images"]) && count($sel_menu_meta["menu_images"]) > 0)
			{
				$imgs = true;
				break;
			}

			if ($sel_menu_meta["img_act_url"] != "")
			{
				$sel_image = "<img name='sel_menu_image' src='".image::check_url($sel_menu_meta["img_act_url"])."' border='0'>";
				$sel_image_url = $sel_menu_meta["img_act_url"];
			}
			$si_parent = $this->mar[$si_parent]["parent"];
		}

		if ($imgs)
		{
			$imgar = $sel_menu_meta["menu_images"];
			$smi = "";
			foreach($imgar as $nr => $dat)
			{
				if ($smi == "")
				{
					$sel_image = "<img name='sel_menu_image' src='".image::check_url($dat["url"])."' border='0'>";
					$sel_image_url = $dat["url"];
				}
				$this->vars(array(
					"url" => image::check_url($dat["url"])
				));
				$smi .= $this->parse("SEL_MENU_IMAGE");
			}
		}
		$this->vars(array(
			"SEL_MENU_IMAGE" => $smi,
			"sel_menu_name" => $this->mar[$sel_menu_id]["name"],
			"sel_menu_image" => $sel_image,
			"sel_menu_image_url" => $sel_image_url,
			"sel_menu_id" => $sel_menu_id,
			"sel_menu_timing" => $sel_menu_meta["img_timing"] ? $sel_menu_meta["img_timing"] : 6 
		));
	}

	function invalidate_menu_cache($ar)
	{
		$cache = new cache;

		// here we gots to invalidate the objects::get_list cache as well, cause it also contains menus
		// but that's gonna be a bit harder, cause it might be in a zillion different files and we must unlink
		// all of them. so scan the damn folder for all those files
		if ($dir = @opendir(aw_ini_get("cache.page_cache"))) 
		{
			$lang_id = aw_global_get("lang_id");
		  while (($file = readdir($dir)) !== false) 
			{
				if (substr($file,0,strlen("objects::get_list::")) == "objects::get_list::")
				{
					$cache->file_invalidate($file);
				}
				else
				if (substr($file,0,strlen("menuedit::menu_cache::lang::".$lang_id."::site_id::".aw_ini_get("site_id"))) == "menuedit::menu_cache::lang::".$lang_id."::site_id::".aw_ini_get("site_id"))
				{
					$cache->file_invalidate($file);
				}
			}
	  }  
	  closedir($dir);

		if (is_array($ar) && (sizeof($ar) > 0))
		{
			array_unique($ar);
			$this->save_handle();
			$this->db_query("SELECT parent FROM objects WHERE oid IN(".join(",",$this->map("%s", $ar)).")");
			$str = "";
//			foreach($ar as $oid)
			while ($row = $this->db_next())
			{
				$str.="1 ".$this->cfg["site_id"]." ".$row["parent"]."\n";
			}
			if ($this->cfg["tree_type"] == "java" && $this->cfg["java_tree_update"])
			{
				$server_socket = fsockopen($this->cfg["java_tree_update_server"], $this->cfg["java_tree_update_port"],$errno,$errstr,10);
				if ($server_socket)
				{
					fputs($server_socket,$str);
					fclose($server_socket);
				}
			}
			$this->restore_handle();
		}
	}

	function get_menu_keywords($id)
	{
		$ret = array();
		$this->db_query("SELECT * FROM keyword2menu WHERE menu_id = $id");
		while ($row = $this->db_next())
		{
			$ret[$row["keyword_id"]] = $row["keyword_id"];
		}
		return $ret;
	}

	function save_menu_keywords($keywords,$id)
	{
		$old_kwds = $this->get_menu_keywords($id);
		if (is_array($keywords))
		{
			// check if the kwywords have actually changed - if not, we souldn't do this, as this can be quite time-consuming
			$update = false;
			foreach($keywords as $koid)
			{
				if ($old_kwds[$koid] != $koid)
				{
					$update = true;
				}
			}

			if (count($old_kwds) != count($keywords))
			{
				$update = true;
			}

			if (!$update)
			{
				return;
			}
		}
		else
		{
			if (count($old_kwds) < 1)
			{
				return;
			}
		}

		$this->db_query("DELETE FROM keyword2menu WHERE menu_id = $id");

		if (is_array($keywords))
		{
			foreach($keywords as $koid)
			{
				$this->db_query("INSERT INTO keyword2menu (menu_id,keyword_id) VALUES('$id','$koid')");
			}
			$this->set_object_metadata(array(
				"oid" => $id,
				"key" => "has_kwd_rels",
				"value" => 1
			));
		}
		else
		{
			$this->set_object_metadata(array(
				"oid" => $id,
				"key" => "has_kwd_rels",
				"value" => 0
			));
		}
	}

	function do_center_menu($oid)
	{
		$mpar = $this->mpr[$oid];
		if (is_array($mpar))
		{
			foreach($mpar as $mprow)
			{
				if ($mprow["mid"])
				{
					if ($this->has_sub_dox($mprow["oid"]))
					{
						if ($mprow["link"] != "")
						{
							$link = $mprow["link"];
						}
						else
						if ($mprow["alias"] != "")
						{
							$link = $mprow["alias"];
						}
						else
						{
							$link = $this->cfg["baseurl"]."/".$mprow["oid"];
						}
						$this->vars(array(
							"link" => $link,
							"target" => ($mprow["target"] ? "target=\"_blank\"" : ""),
							"text" => str_replace("&nbsp;"," ",strip_tags($mprow["name"]))
						));
						$mmd.=$this->parse("CENTER_MENU");
					}
				}
			}
			$this->vars(array("CENTER_MENU" => $mmd));
		}
	}

	function reset_template_sets()
	{
		$q = "SELECT id FROM menu";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$this->save_handle();
			$oldmeta = $this->get_object_metadata(array("oid" => $row["id"]));
			if ($oldmeta)
			{
				$oldmeta["tpl_dir"] = "";
				$this->set_object_metadata(array(
					"oid" => $row["id"],
					"data" => $oldmeta,
				));	
			}
			$this->restore_handle();
		}
	}

	function do_sub_callbacks($sub_callbacks)
	{
		if (is_array($sub_callbacks))
		{
			// ok, check if the new and better OO (TM) way exists
			if (function_exists("__get_site_instance"))
			{
				$si =&__get_site_instance();
				if (is_object($si))
				{
					foreach($sub_callbacks as $sub => $fun)
					{
						if ($this->is_template($sub))
						{
							$si->$fun(&$this);
						}
					}
				};
			}
			else
			{
				foreach($sub_callbacks as $sub => $fun)
				{
					if ($this->is_template($sub))
					{
						$fun(&$this);
					}
				}
			}
		}
	}

	////
	// !Fetches the menu chain for the current object from the menu cache for further use
	function build_menu_chain($section)
	{
		$parent = $section;
		// this is used to store the whole menu chain
		$this->menu_chain = array();
		
		// this is where we store the full path to the object
		$this->path = array();
	
		// we will this with properties from the first element in chain who
		// has thoses
		$this->properties = array(
			"tpl_dir"  => "",
			"users_only" => 0,
			"comment" => "",
			"tpl_view" => "",
			"ftpl_view" => "",
			"tpl_lead" => "",
			"ftpl_lead" => "",
			"tpl_edit" => "",
			"ftpl_edit" => "",
		);
	
		while($parent)
		{
			$obj = $this->mar[$parent];
			// if the object was not in the cache (which probably means it was not a menu)
			// fetch it directly from the database
			if (not($obj))
			{
				$obj = $this->get_obj_meta($parent);
			};

			// only use metadata from menus
			$is_menu = ($obj["class_id"] == CL_PSEUDO);

			if (is_array($obj))
			{
				array_unshift($this->path,$obj["oid"]);
				$this->menu_chain[$obj["oid"]] = $obj;
				// check whether this object has any properties that 
				// none of the previous ones had
				$_dat = (is_array($obj["meta"])) ? array_merge($obj,$obj["meta"]) : $obj;
				$intersect = array_intersect(array_keys($_dat),array_keys($this->properties));
				foreach($intersect as $val)
				{
					if ($is_menu && not($this->properties[$val]))
					{
						//print "<!-- found at $obj[oid] -->\n";
						$this->properties[$val] = $_dat[$val];
					};
				};
			};
			// also, check whether the parent of the current object is alreay handled
			// and if so, just drop out of the cycle
			$parent = ($this->menu_chain[$obj["parent"]]) ? false : $obj["parent"];
		};
		/*
		  print "<!--";
		  print_r($this->properties);
		  print "-->";
		*/

	}

	////
	// !Checks whether the section or one of it's parents is marked as "users_only
	function users_only_redir()
	{
		classload("config");
		$dbc = new db_config();
		$url = $dbc->get_simple_config("orb_err_mustlogin");
		header("Location: ".$this->cfg["baseurl"]."/$url");
		// exit from inside the class, yuck.
		exit;
	}


	////
	// !Redirect the user if he/she didn't have the right to view that section
	function no_access_redir()
	{
		classload("config");
		$c = new db_config;
		$ec = $c->get_simple_config("errors");
		$ra = aw_unserialize($ec);
			
		$gidlist = aw_global_get("gidlist");
		if (is_array($gidlist))
		{
			$d_gid = 0;
			$d_pri = 0;
			$d_url = "";
			foreach($gidlist as $gid)
			{
				if ($ra[$gid]["pri"] >= $d_pri && $ra[$gid]["url"] != "")
				{
					$d_gid = $gid;
					$d_pri = $ra[$gid]["pri"];
					$d_url = $ra[$gid]["url"];
				}
			}
			
			if ($d_url != "")
			{
				if ($d_url != aw_global_get("REQUEST_URI"))
				{
					header("Location: $d_url");
					die();
				}
				else
				{
					$this->raise_error(ERR_ACL_ERR,"Access denied and error redirects are defined.incorrectly. Please report this to the site administrator",1);
				};
					
			}
		}
		$this->raise_error(ERR_MNEDIT_NOACL,"No ACL error messages defined!",true);
	}

	function _get_template_filename($id)
	{
		$q = "SELECT filename FROM template WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		return $row["filename"];
	}

	////	
	// !imports menus from text file. file format description is at http://aw.struktuur.ee/index.aw?section=38624
	function do_text_import($arr)
	{
		global $fail;
		if (is_uploaded_file($fail))
		{
			$c = file($fail);
			$cnt = 0;
			$levels = array("" => $parent); // here we keep the info about the numbering of the levels => menu id's
			foreach($c as $row)
			{
				$cnt++;
				// parse row and create menu.
				if (!preg_match("/([0-9\.]+)(.*)\[(.*)\]/",$row,$mt))
				{
					if (!preg_match("/([0-9\.]+)(.*)/",$row,$mt))
					{
						$this->raise_error(ERR_MNEDIT_TXTIMP,"Menyyde importimisel tekkis viga real $cnt ",true);
					}
				}
				// now parse the position in the structure from the numbers.
				$pos = strrpos($mt[1],".");
				$_pt = substr($mt[1],0,$pos);
				if ($_pt == "")
				{
					$_parent = $arr["parent"];
				}
				else
				{
					$_parent = $levels[$_pt];
				}

				if ($_pt != "" && !$_parent)
				{
					$this->raise_error(ERR_MNEDIT_TXTIMP_PARENT,"Menyyde importimisel ei leidnud parent menyyd real $cnt ",true);
				}
				else
				{
					// parse the menu options
					$opts = trim($mt[3]);
					$mopts = array("click" => 1);
					if ($opts != "")
					{
						// whee. do a preg_match for every option. 
						$mopts["act"] = preg_match("/\+act/",$opts);
						if (preg_match("/\+comment=\"(.*)\"/",$opts,$mmt))
						{
							$mopts["comment"] = $mmt[1];
						}
						if (preg_match("/\+alias=\"(.*)\"/",$opts,$mmt))
						{
							$mopts["alias"] = $mmt[1];
						}
						$mopts["per"] = preg_match("/\+per/",$opts);
						if (preg_match("/\+link=\"(.*)\"/",$opts,$mmt))
						{
							$mopts["link"] = $mmt[1];
						}
						$mopts["click"] = preg_match("/\+click/",$opts);
						$mopts["target"] = preg_match("/\+target/",$opts);
						$mopts["mid"] = preg_match("/\+mid/",$opts);
						$mopts["makdp"] = preg_match("/\+makdp/",$opts);
						if (preg_match("/\+width=\"(.*)\"/",$opts,$mmt))
						{
							$mopts["width"] = $mmt[1];
						}
						$mopts["rp"] = preg_match("/\+rp/",$opts);
						$mopts["lp"] = preg_match("/\+lp/",$opts);
						if (preg_match("/\+fn=\"(.*)\"/",$opts,$mmt))
						{
							$mopts["fn"] = $mmt[1];
						}
					}

					// now create the damn thing.
					$id = $this->new_object(array(
						"parent" => $_parent,
						"class_id" => CL_PSEUDO,
						"name" => trim($mt[2]),
						"comment" => $mopts["comment"],
						"status" => ($mopts["act"] ? 2 : 1),
						"alias" => $mopts["alias"],
						"jrk" => substr($mt[1],($pos > 0 ? $pos+1 : 0))
					));

					if ($mopts["fn"] != "")
					{
						$this->set_object_metadata(array(
							"oid" => $id,
							"key" => "aip_filename",
							"value" => $mopts["fn"]
						));
					}
					$this->db_query("INSERT INTO menu (id,type,link,clickable,target,mid,hide_noact,width,right_pane,left_pane)
						VALUES($id,".MN_CONTENT.",'".$mopts["link"]."','".$mopts["click"]."','".$mopts["target"]."','".$mopts["mid"]."','".$mopts["makdp"]."','".$mopts["width"]."','".(!$mopts["rp"])."','".(!$mopts["lp"])."')");
					$levels[$mt[1]] = $id;
				}
			}
		}
	}

	function check_object($obj)
	{
		switch($obj["class_id"])
		{
			case CL_EXTLINK:
				classload("extlinks");
				$t = new extlinks();
				$link = $t->get_link($obj["oid"]);
				//list($url,$target,$caption) = $t->draw_link($obj["oid"]);
				header("Location: $link[url]");
				//$replacement = sprintf("<a href='%s' %s>%s</a>",$url,$target,$caption);
				exit;
				break;

			case CL_IMAGE:
				classload("image");
				$t = new image();
				$idata = $t->get_image_by_id($obj["oid"]);
				$this->replacement = sprintf("<img src='%s'><br>%s",$idata["url"],$idata["comment"]);
				if ($this->raw)
				{
					print $this->replacement;
					exit;
				};
				break;

			case CL_FILE:
				classload("file");
				$t = new file();
				die($t->show($obj["oid"]));
				break;

			case CL_TABLE:
				classload("table");
				$t = new table();
				$this->replacement = $t->show(array("id" => $obj["oid"],"align" => $align));
				if ($this->raw)
				{	
					print $this->replacement;
					exit;
				};
				break;

			default:
				$this->replacement = "";

		};
	}

	// builds HTML popups
	function build_popups()
	{
		// that sucks. We really need to rewrite that
		// I mean we always read information about _all_ the popups
		$q = "SELECT * FROM objects WHERE status = 2 AND class_id = " . CL_HTML_POPUP;
		$this->db_query($q);
		$popups = "";
		while($row = $this->db_next())
		{
			$meta = aw_unserialize($row["meta"]);
			if (is_array($meta["menus"]))
			{
				foreach($meta["menus"] as $key => $val)
				{
					if ($val == $this->sel_section)
					{
						$popups .= "window.open('$meta[url]','popup','top=0,left=0,toolbar=0,location=0,menubar=0,scrollbars=0,width=$meta[width],height=$meta[height]');";
					};
				};
			};
		};
		$retval = (strlen($popups) > 0) ? "<script language='Javascript'>$popups</script>" : "";
		dbg("l = " . strlen($retval));
		return $retval;
	}

	function show_folders($arr)
	{
		extract($arr);
		classload("languages");
		$t = new languages;
		$sf = new aw_template;
		$sf->tpl_init("automatweb");
		$sf->read_template("index_folders.tpl");
		$sf->vars(array(
			"charset" => $t->get_charset(),
			"content" => $this->gen_folders($period)
		));
		die($sf->parse());
	}

	function setup_rf_table($parent)
	{
		load_vcl("table");
		$this->t = new aw_table(array("prefix" => "me_rf"));

		$this->co_id = 0;

		// check if any parent menus have config objects attached 
		$ch = $this->get_object_chain($parent);
		foreach($ch as $oid => $od)
		{
			if ($od["meta"]["objtbl_conf"])
			{
				$this->co_id = $od["meta"]["objtbl_conf"];
			}
		}

		if (!$this->co_id)
		{
			$this->t->parse_xml_def($this->cfg["basedir"]."/xml/menuedit/right_frame_default.xml");
		}
		else
		{
			$this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
			$this->otc_inst = get_instance("obj_table_conf");
			$this->otc_inst->init_table($this->co_id, $this->t);
		}
	}

	function new_right_frame($arr)
	{
		extract($arr);
		$lang_id = aw_global_get("lang_id");
		$site_id = $this->cfg["site_id"];
		$parent = $parent ? $parent : $this->cfg["rootmenu"];
		$sel_objs = aw_global_get("cut_objects");
		if (!is_array($sel_objs))
		{
			$sel_objs = array();
		}
		$t = aw_global_get("copied_objects");
		if (!is_array($t))
		{
			$t = array();
		}
		$sel_objs+=$t;

		$this->mk_path($parent,"",$period);

		$la = get_instance("languages");
		$lar = $la->get_list();

		$this->setup_rf_table($parent);

		$host = str_replace("http://","",$this->cfg["baseurl"]);
		preg_match("/.*:(.+?)/U",$host, $mt);
		if ($mt[1])
		{
			$host = str_replace(":".$mt[1], "", $host);
		}

		if (isset($period))
		{
			$ps = " AND ((period = '$period') OR (periodic = 1)) ";
		}

		$this->read_template("js_popup_menu.tpl");
		$this->db_query("SELECT * FROM objects WHERE parent = '$parent' AND lang_id = '$lang_id' AND site_id = '$site_id' AND status != 0 $ps ");
		while ($row = $this->db_next())
		{
			if (!$this->can("view", $row["oid"]))
			{
				continue;
			}
			$can_change = $this->can("edit", $row["oid"]);
			$can_delete = $this->can("delete", $row["oid"]);
			$can_admin = $this->can("admin", $row["oid"]);

			if ($row["class_id"] == CL_PSEUDO || $row["class_id"] == CL_BROTHER || $row["class_id"] == CL_PROMO)
			{
				$chlink = $this->mk_my_orb("right_frame", array("parent" => $row["oid"], "period" => $period));
				$row["is_menu"] = 1;
			}
			else
			{
				$chlink = $this->mk_my_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]),$this->cfg["classes"][$row["class_id"]]["file"]);
				$row["is_menu"] = 2;
			}
			$dellink = $this->mk_my_orb("delete", array("reforb" => 1, "id" => $id, "parent" => $row["parent"],"sel[".$row["oid"]."]" => "1"), "menuedit",true,true);

			if ($sel_objs[$row["oid"]])
			{
				$row["cutcopied"] = "#E2E2DB";
			}
			else
			{
				$row["cutcopied"] = "#FCFCF4";
			}
			$iu = $this->get_icon_url($row["class_id"],$row["name"]);

			$row["lang_id"] = $lar[$row["lang_id"]];

			$this->save_handle();
/*			$this->vars(array(
				"content" => $this->get_popup_data(array("id" => $row["oid"], "ret_data" => true, "sharp" => true))
			));*

			$this->vars(array(
				"oid" => $row["oid"],
				"name" => "",
				"bgcolor" => $row["cutcopied"],
				"icon" => $this->cfg["baseurl"]."/automatweb/images/blue/obj_settings.gif",
				"width" => "16",
				"height" => "16",
				"icon_over" => $this->cfg["baseurl"]."/automatweb/images/blue/obj_settings.gif",
				"url" => $host,
				"URLPARAM" => "",
				"FETCHCONTENT" => ($this->cfg["fetchcontent"] ? $this->parse("FETCHCONTENT") : "")
			));*/
			$this->vars(array(
				"menu_id" => "js_pop_".$row["oid"],
				"menu_icon" => $this->cfg["baseurl"]."/automatweb/images/blue/obj_settings.gif",
				"MENU_ITEM" => $this->get_popup_data(array("period" => $period,"id" => $row["oid"], "ret_data" => true, "sharp" => true,"type" => "js"))
			));
			$row["java"] = $this->parse();

			$this->restore_handle();

			$row["name"] = "<a href=\"".$chlink."\">".($row["name"] == "" ? "(nimeta)" : $row["name"])."</a>";
			$row["icon"] = "<img src=\"".$iu."\">";
			$row["link"] = "<a href=\"".$this->cfg["baseurl"]."/".$row["oid"]."\">Link</a>";
			$row["class_id"] = $this->cfg["classes"][$row["class_id"]]["name"];
			$row["hidden_jrk"] = $row["jrk"];
			$row["jrk"] = "<input type=\"hidden\" name=\"old[jrk][".$row["oid"]."]\" value=\"".$row["jrk"]."\"><input type=\"text\" name=\"new[jrk][".$row["oid"]."]\" value=\"".$row["jrk"]."\" class=\"formtext\" size=\"3\">";
			$row["status"] = "<input type=\"hidden\" name=\"old[status][".$row["oid"]."]\" value=\"".$row["status"]."\"><input type=\"checkbox\" name=\"new[status][".$row["oid"]."]\" value=\"2\" ".checked($row["status"] == 2).">";
			$row["select"] = "<input type=\"checkbox\" name=\"sel[".$row["oid"]."]\" value=\"1\">";
			$row["change"] = $can_change ? "<a href=\"$chlink\"><img src=\"".$this->cfg["baseurl"]."/automatweb/images/blue/obj_settings.gif\" border=\"0\"></a>" : "";
			$row["delete"] = $can_delete ? "<a href=\"javascript:box2('Oled kindel, et soovid seda objekti kustutada?','$dellink')\"><img src=\"".$this->cfg["baseurl"]."/automatweb/images/blue/obj_delete.gif\" border=\"0\"></a>" : "";
			$row["acl"] = $can_admin ? "<a href=\"editacl.aw?oid=".$row["oid"]."&file=menu.xml\"><img src=\"".$this->cfg["baseurl"]."/automatweb/images/blue/obj_acl.gif\" border=\"0\"></a>" : "";
			if ($this->co_id)
			{
				$this->otc_inst->table_row($row, &$this->t);
			}
			else
			{
				$this->t->define_data($row);
			}
		}
		$types = array();
		foreach($this->cfg["classes"] as $clid => $cldat)
		{
			$types[$cldat["file"]] = $cldat["name"];
		}
		asort($types);

		$this->read_template("java_popup_menu.tpl");

		// make applet for adding objects
		$this->vars(array(
			"icon_over" => $this->cfg["baseurl"]."/automatweb/images/icons/new2_over.gif",
			"icon" => $this->cfg["baseurl"]."/automatweb/images/icons/new2.gif",
			"oid" => $parent,
			"bgcolor" => "#D4D7DA",
			"nr" => 2,
			"key" => "addmenu",
			"val" => 1,
			"name" => "",
			"height" => 22,
			"width" => 23,
			"url" => $host,
			"content" => $this->get_add_menu(array("id" => $parent, "ret_data" => true, "sharp" => true, "addmenu" => 1, "period" => $period))
		));
		$up = $this->parse("URLPARAM");
		$this->vars(array(
			"nr" => 3,
			"key" => "period",
			"val" => $period,
		));
		$up .= $this->parse("URLPARAM");
		$this->vars(array(
			"URLPARAM" => $up,
			"FETCHCONTENT" => $this->parse("FETCHCONTENT")
		));
		$add_applet = $this->parse();

		$la = get_instance("languages");

		if (!$sortby)
		{
			$sortby = "hidden_jrk";
		};

		if (!$sort_order)
		{
			$sort_order = "asc";
		};

		$this->t->set_numeric_field("hidden_jrk");

		$this->t->sort_by(array(
			"field" => array("is_menu", $sortby),
			"sorder" => array("is_menu" => "asc", $sortby => $sort_order)
		));

		$this->read_template("right_frame.tpl");

		$toolbar = $this->rf_toolbar(array(
			"parent" => $parent,
			"add_applet" => $add_applet,
			"sel_count" => count($sel_objs),
		));


		$this->vars(array(
			"table" => $this->t->draw(),
			"reforb" => $this->mk_reforb("submit_rf", array("parent" => $parent, "period" => $period, "sortby" => $sortby, "sort_order" => $sort_order)),
			"types" => $this->picker(" ", $types),
			"parent" => $parent,
			"period" => $period,
			"lang_name" => $la->get_langid(),
			"toolbar" => $toolbar->get_toolbar(),
		));

		return $this->parse();
	}

	function rf_toolbar($args = array())
	{
		extract($args);
		$toolbar = get_instance("toolbar");
		
		if ($this->can("add", $parent))
		{
			$toolbar->add_cdata($add_applet);
		};

		if (!$no_save)
		{
			$toolbar->add_button(array(
				"name" => "save",
				"tooltip" => "Salvesta",
				"url" => "javascript:document.foo.submit()",
				"imgover" => "save_over.gif",
				"img" => "save.gif",
			));
		};
		
		$toolbar->add_button(array(
			"name" => "search",
			"tooltip" => "Otsi",
			"url" => $this->mk_my_orb("search",array("parent" => $parent),"search"),
			"imgover" => "search_over.gif",
			"img" => "search.gif",
		));
		

		$toolbar->add_separator();

		$toolbar->add_button(array(
			"name" => "cut",
			"tooltip" => "Cut",
			"url" => "javascript:submit('cut')",
			"imgover" => "cut_over.gif",
			"img" => "cut.gif",
		));

		$toolbar->add_button(array(
			"name" => "copy",
			"tooltip" => "Copy",
			"url" => "javascript:submit('copy')",
			"imgover" => "copy_over.gif",
			"img" => "copy.gif",
		));

		if ($sel_count > 0)
		{
			$toolbar->add_button(array(
				"name" => "paste",
				"tooltip" => "Paste",
				"url" => "javascript:submit('paste')",
				"imgover" => "paste_over.gif",
				"img" => "paste.gif",
			));
		};

		$toolbar->add_button(array(
			"name" => "delete",
			"tooltip" => "Delete",
			"url" => "javascript:submit('delete')",
			"imgover" => "delete_over.gif",
			"img" => "delete.gif",
		));

		$toolbar->add_button(array(
			"name" => "edit",
			"tooltip" => "Edit",
			"url" => "javascript:change()",
			"imgover" => "edit_over.gif",
			"img" => "edit.gif",
		));
		
		$toolbar->add_separator();
	
		$toolbar->add_button(array(
			"name" => "refresh",
			"tooltip" => "Refresh",
			"url" => "javascript:window.location.reload()",
			"imgover" => "refresh_over.gif",
			"img" => "refresh.gif",
		));
	
		$toolbar->add_button(array(
			"name" => "import",
			"tooltip" => "Import",
			"url" => $this->mk_my_orb("import",array("parent" => $parent)),
			"imgover" => "import_over.gif",
			"img" => "import.gif",
		));
	
		$toolbar->add_button(array(
			"name" => "bugtrack",
			"tooltip" => "Bugtrack",
			"url" => $this->mk_my_orb("list",array("filt" => "all"),"bugtrack"),
			"imgover" => "bugtrack_over.gif",
			"img" => "bugtrack.gif",
		));
	
		if (is_array($callback) && sizeof($callback) == 2)
		{
			$callback[0]->$callback[1](array("toolbar" => &$toolbar));
		};

		return $toolbar;
	}

	function submit_rf($arr)
	{
		extract($arr);

		if (is_array($old))
		{
			foreach($old as $column => $coldat)
			{
				foreach($coldat as $oid => $oval)
				{
					$val = $new[$column][$oid];
					if ($column == "status" && $val == 0)
					{
						$val = 1;
					}
					if ($val != $oval)
					{
						$this->upd_object(array(
							"oid" => $oid,
							$column => $val
						));
					}
				}
			}
		}
		$this->invalidate_menu_cache(array());
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period, "sortby" => $sortby, "sort_order" => $sort_order));
	}

	function new_delete($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			$oids = join(",",array_keys($sel));
			$this->db_query("SELECT oid,class_id FROM objects WHERE oid IN($oids)");
			while ($row = $this->db_next())
			{
				$this->save_handle();
				if ($this->cfg["classes"][$row["class_id"]]["file"] != "")
				{
					$inst = get_instance($this->cfg["classes"][$row["class_id"]]["file"]);
					if (method_exists($inst, "delete_hook"))
					{
						$inst->delete_hook(array("oid" => $row["oid"]));
					}
				}
				$this->delete_object($row["oid"]);
				$this->restore_handle();
			}
		}
		$this->invalidate_menu_cache(array());
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period, "sortby" => $sortby, "sort_order" => $sort_order));
	}

	function change_redir($arr)
	{
		extract($arr);
		if (!is_array($sel))
		{
			$this->raise_error(ERR_MNED_NO_OBJS,"Valitud pole &uuml;htegi objekti!", true);
		}

		reset($sel);
		list($oid,) = each($sel);

		$obj = $this->get_object($oid);
		return $this->mk_my_orb("change", array("id" => $oid, "parent" => $parent), $this->cfg["classes"][$obj["class_id"]]["file"]);
	}

	function blank($arr)
	{
		return "<html><body>&nbsop;</body></html>";
	}

	function req_serialize_obj_tree($oid)
	{
		$objs = $this->list_objects(array("class" => CL_PSEUDO, "parent" => $oid, "return" => ARR_ALL));
		$oids = join(",", array_keys($objs));
		if ($oids != "")
		{
			$this->db_query("SELECT * FROM menu WHERE id IN ($oids)");
			while ($row = $this->db_next())
			{
				$cur_id = $row["id"];

				$hash = $this->gen_uniq_id();
				$this->menu_hash2id[$cur_id] = $hash;

				$od = $objs[$cur_id];
				$od["parent"] = $this->menu_hash2id[$od["parent"]];
				$od["oid"] = $hash;
				$row["id"] = $hash;

				$dat = array(
					"object" => $od,
					"table" => $row
				);
				$this->ser_obj[$hash] = $dat;

				$this->req_serialize_obj_tree($cur_id);
			}
		}
		if ($this->serialize_subobjs)
		{
			$this->db_query("SELECT oid FROM objects WHERE parent = $oid AND status != 0 AND class_id != ".CL_PSEUDO." AND lang_id = '".aw_global_get("lang_id")."' AND site_id = '".$this->cfg["site_id"]."'");
			while ($row = $this->db_next())
			{
				$dat = $this->serialize(array("oid" => $row["oid"]));
				if ($dat !== false)
				{
					$hash = $this->gen_uniq_id();
					$this->ser_obj[$hash] = array("is_object" => true, "objstr" => $dat, "parent" => $this->menu_hash2id[$oid]);
				}
			}
		}
	}

	////
	// !this should creates a string representation of the menu
	// parameters
	//    oid - menu id
	function _serialize($arr)
	{
		extract($arr);
		$this->ser_obj = array();
		$hash = $this->gen_uniq_id();
		$this->menu_hash2id[$oid] = $hash;
		$od = $this->get_object($oid);
		$od["parent"] = 0;
		$od["oid"] = $hash;

		$row = $this->db_fetch_row("SELECT * FROM menu WHERE id = '$oid'");
		$row["id"] = $hash;
		$dat = array(
			"object" => $od,
			"table" => $row
		);
		$this->ser_obj[$hash] = $dat;

		if ($this->serialize_submenus)
		{
			$this->req_serialize_obj_tree($oid);
		}
		else
		{
			if ($this->serialize_subobjs)
			{
				$this->db_query("SELECT oid FROM objects WHERE parent = $oid AND status != 0 AND class_id != ".CL_PSEUDO." AND lang_id = '".aw_global_get("lang_id")."' AND site_id = '".$this->cfg["site_id"]."'");
				while ($row = $this->db_next())
				{
					$dat = $this->serialize(array("oid" => $row["oid"]));
					if ($dat !== false)
					{
						$hash = $this->gen_uniq_id();
						$this->ser_obj[$hash] = array("is_object" => true, "objstr" => $dat, "parent" => $this->menu_hash2id[$oid]);
					}
				}
			}
		}

		return serialize($this->ser_obj);
	}

	////
	// !this should create a menu from a string created by the _serialize() function
	// parameters
	//    str - the string
	//    parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$dat = unserialize($str);

		$hash2id = array(0 => $parent);

		foreach($dat as $hash => $row)
		{
			if (!$row["is_object"])
			{
				$ob = $row["object"];
				$ob["parent"] = $hash2id[$ob["parent"]];
				$this->quote(&$ob);
				$id = $this->new_object($ob);
				$hash2id[$hash] = $id;

				$menu = $row["table"];
				$m_ids = array("id");
				$m_vls = array($id);
				foreach($menu as $col => $val)
				{
					if ($col != "id" && $col != "rec")
					{
						$m_ids[] = $col;
						$m_vls[] = "'".$val."'";
					}
				}
				$this->db_query("INSERT INTO menu (".join(",",$m_ids).") VALUES(".join(",",$m_vls).")");
			}
			else
			{
				$this->unserialize(array("str" => $row["objstr"], "parent" => $hash2id[$row["parent"]], "period" => $period));
			}
		}
		return true;
	}
}
?>
