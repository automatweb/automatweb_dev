<?php
// $Header: /home/cvs/automatweb_dev/classes/menuedit.aw,v 2.226 2003/02/06 09:46:45 duke Exp $
// menuedit.aw - menuedit. heh.

// meeza thinks we should split this class. One part should handle showing stuff
// and the other the admin side -- duke

// number mille kaudu tuntakse 2ra kui tyyp klikib kodukataloog/SHARED_FOLDERS peale
define("SHARED_FOLDER_ID",2147483647);

classload('icons');
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
	// skip_invalidate   
	function add_new_menu($args = array())   
	{   
		// ja eeldame, et meil on vähemalt parent ja name olemas.   
		$this->quote($args["name"]);   
		$newoid = $this->new_object(array(   
			"name" => $args["name"],   
			"parent" => $args["parent"],   
			"status" => (isset($args["status"]) ? $args["status"] : 2),   
			"class_id" => CL_PSEUDO,   
			"jrk" => $args["jrk"],   
			'no_flush' => $args['no_flush'],   
			"metadata" => array(
				"pclass" => $args["pclass"],
				"pm_url_admin" => $args["pm_url_admin"]
			)
		));   
		$type = $args["type"] ? $args["type"] : MN_HOME_FOLDER_SUB;   
		$q = sprintf("INSERT INTO menu (id,type,link) VALUES (%d,%d,'%s')",$newoid,$type,$args["link"]);   
		$this->db_query($q);   
		$this->_log(ST_MENUEDIT, SA_ADD, $args["name"], $newoid);   

		if (!$args['no_flush'])   
		{   
			$this->invalidate_menu_cache(array($newoid));   
		}   

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
	// @arg section type=int
	// @arg print type=int
	function gen_site_html($params)
	{
		// handle favicon
		if (($params["section"]."") == "favicon.ico")
		{
			$c = get_instance("config");
			$c->show_favicon(array());
		}

		if (aw_global_get("no_menus") == 1)
		{
			return $params["text"];
		}

		// kontrollib sektsiooni ID-d, tagastab oige numbri kui tegemist oli
		// aliasega, voi lopetab töö, kui miskit oli valesti
		$section = $this->check_section($params["section"]);


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
			$d = get_instance("document");
			$d->gen_rss_feed(array("period" => $act_per_id,"parent" => $section));
		};

		// koostame array vajalikest parameetritest, mis identifitseerivad cachetava objekti
		$cp = array();

		$cp[] = $act_per_id;

		$cp[] = aw_global_get("lang_id");

		// here we sould add all the variables that are in the url to the cache parameter list
		global $HTTP_GET_VARS;

		foreach($HTTP_GET_VARS as $var => $val)
		{
			// just to make sure that each user does not get it's own copy
			if ($var != "automatweb" && $var != "set_lang_id")
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

		$cache = get_instance("cache");
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
				$res = str_replace("[ss".$gid."]",gen_uniq_id(),$res);
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
		$docid = isset($docid) ? $docid : 0;

		// impordime taimeriklassi

		$this->vars(array(
			"lang_code" => aw_global_get("LC"),
		));

		$obj = $this->get_object($section);

		classload("image");
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
		
		classload("image");
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
		dbg::p("active language for menus is ".aw_global_get("lang_id")."<br>");

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

		$d = get_instance("document");
		$this->doc = get_instance("document");
		
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
		$this->dequote($this->properties["comment"]);
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
			$this->site_title = strip_tags($this->title_yah);
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
		
		$cd = "";
		$menu_defs_v2 = $this->cfg["menu_defs"];
		$this->menu_defaults = $this->cfg["menu_defaults"];
		if ($this->cfg["lang_defs"])
		{
			$menu_defs_v2 = $this->cfg["menu_defs"][aw_global_get("lang_id")];
			$this->menu_defaults = $this->cfg["menu_defaults"][aw_global_get("lang_id")];
		}
		$frontpage = $this->cfg["frontpage"];

		if (isset($menu_defs_v2) && is_array($menu_defs_v2))
		{
			$nx = "";
			$this->level = 0;
			reset($menu_defs_v2);
			while (list($id,$name) = each($menu_defs_v2))
			{
				$nx = $name;
				dbg::p("drawing $id,$name<br>");

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
			"ss" => gen_uniq_id(),		// bannerite jaox
			"ss2" => gen_uniq_id(),
			"ss3" => gen_uniq_id(),
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
		
		// you can pass along values for variables from index.aw and places like that using
		// $vars array - it's pretty neat actually. - terryf
		if (is_array($vars))
		{
			$this->vars($vars);
		}

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
				"site_title" => strip_tags($this->site_title),
			));
		}
		else
		{
			$t = get_instance("users");
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
				"site_title" => strip_tags($this->site_title),
			));
		};
		
		if ($this->left_pane)
		{
			$lp = $this->parse("LEFT_PANE");
		}
		else
		{
			$lp = $this->parse("NO_LEFT_PANE");
		}
		if ($this->right_pane)
		{
			$rp = $this->parse("RIGHT_PANE");
		}
		else
		{
			$rp = $this->parse("NO_RIGHT_PANE");
		}
		
		$this->vars(array(
			"LEFT_PANE" => $lp, 
			"RIGHT_PANE" => $rp,
			"NO_LEFT_PANE" => "",
			"NO_RIGHT_PANE" => ""
		));
		
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

		// end of suckage


		if ($section == $frontpage)
		{
			$this->vars(array("IS_FRONTPAGE" => $this->parse("IS_FRONTPAGE")));
		}

		$popups = $this->build_popups();

		if (is_array($this->metaref) && (sizeof($this->metaref) > 0))
		{
			$this->set_object_metadata(array(
				"oid" => $section,
				"key" => "metaref",
				"value" => $this->metaref,
			));
		};

		$retval = $this->parse();
		return $this->parse() . $popups;
	}

	function is_periodic($section,$checkobj = 1) 
	{
		$mn = $this->get_object($section);
		if ($mn["class_id"] != CL_PSEUDO)
		{
			$mn = $this->get_object($mn["parent"]);
		}
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
			$aa = "AND ((objects.site_id = '".aw_ini_get("site_id")."') OR (objects.site_id IS NULL))";
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
				objects.periodic as periodic,
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

			if ($me_row["class_id"] == CL_PROMO)
			{
				if (is_array($me_row["meta"]["last_menus"]) && ($me_row["meta"]["last_menus"][0] !== 0))
				{
					$sections = $me_row["meta"]["last_menus"];
				}
				else
				{
					$sections = array($section);
				};
			}
			else
			{
				$sections = $me_row["meta"]["sss"];
			};

			$periods = $me_row["meta"]["pers"];


			if (is_array($sections) && ($sections[0] !== 0))
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
			$q = "SELECT objects.oid as oid,objects.class_id AS class_id, objects.brother_of AS brother_of, documents.esilehel as esilehel FROM objects LEFT JOIN documents ON documents.docid = objects.brother_of WHERE (($pstr AND status = 2 AND class_id in (7,29) AND objects.lang_id=".aw_global_get("lang_id").") OR (class_id = ".CL_BROTHER_DOCUMENT." AND status = 2 AND $pstr)) $lsas ORDER BY $ordby $lm";
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
					$this->_log(ST_MENUEDIT, SA_PAGEVIEW ,$ml_user["name"]." (".$ml_user["mail"].") tuli lehele $log meilist ".$ml_msg["subj"],$section);

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
				$this->_log(ST_MENUEDIT, SA_PAGEVIEW,$ml_user["name"]." (".$ml_user["mail"].") vaatas lehte $log",$section);
			}
		}
		else
		{
			$this->_log(ST_MENUEDIT, SA_PAGEVIEW, $log, $section);
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
			$ar = new aw_array($tbld[$GLOBALS["tbl_sk"]]);
			foreach($ar->get() as $url)
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

		if ($set_lang_id)
		{
			if ($row)
			{
				aw_global_set("lang_id",$set_lang_id);
			}
			else
			{
				$realsect = $this->cfg["frontpage"];
			};
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

		if ($section == 'favicon.ico')
		{
			// if user requested favicon, then just show the thing here and be done with it
			$c = get_instance("config");
			$c->show_favicon(array());
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

			// yeah, well, /\W/ match is way too strict - I can't use blah.html as the alias for instance
			// $this->quote(&$alias) will prevent doing any bad things in the sql - terryf
			$prnt = 0;
			$obj = true;
			foreach($sections as $skey => $sval)
			{
				global $DBUG;
				if ($DBUG)
				{
					print "checking $sval<br>";
				}
				if ($obj !== false)
				{
					// ok, ini option: menuedit.recursive_aliases  - if true, aliases are checked by parents
					if ($this->cfg['recursive_aliases'])
					{
						// vaatame, kas selle nimega aliast on?
						$obj = $this->_get_object_by_alias($sval,$prnt);

						// need to check one more thing, IF prnt = 0 then fetch the parent
						// of this object and see whether it has an alias. if so, do not
						// let him access this menu directly
						if ($prnt == 0)
						{
							$pobj = $this->get_object($obj["parent"]);
							if (strlen($pobj["alias"]) > 0)
							{
								$obj = false;
							}
						};

						if ( ($prnt != 0) && ($obj["parent"] != $prnt) )
						{
							$obj = false;
						}
						else
						{
							$prnt = $obj["oid"];
						};
					}
					else
					{
						// vaatame, kas selle nimega aliast on?
						$obj = $this->_get_object_by_alias($sval);
						$prnt = $obj["oid"];
					}
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
					$this->_log(ST_MENUEDIT, SA_ACL_ERROR,sprintf(LC_MENUEDIT_TRIED_ACCESS,$section), $section);
					// neat :), kui objekti ei leita, siis saadame 404 koodi
					if ($this->cfg["404redir"])
					{
						header("Location: " . $this->cfg["404redir"]);
					}
					else
					{
						header ("HTTP/1.1 404 Not Found");
						printf(E_ME_NOT_FOUND);
					};
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
				$this->_log(ST_MENUEDIT, SA_NOTEXIST,sprintf(LC_MENUEDIT_TRIED_ACCESS2,$section), $section);
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
		$u = get_instance("users");
		$treetype = $u->get_user_config(array(
			"uid" => aw_global_get("uid"),
			"key" => "treetype",
		));

		if (!$treetype)
		{
			$treetype = $this->cfg["tree_type"];
		};

		if ($treetype == "java")
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
			$dbp = get_instance("periods",$this->cfg["per_oid"]);
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
			"iconurl" => icons::get_icon_url("homefolder",""),
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
			"iconurl" => icons::get_icon_url("shared_folders",""),
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
		$dbu = get_instance("users_user");
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
			"iconurl" => icons::get_icon_url("hf_groups",""),
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
				$mn = $this->get_objects_below(array(
					'parent' => $atc_root,
					'class' => CL_PSEUDO,
					'full' => true,
					'ignore_lang' => true,
					'ret' => ARR_NAME
				)) + array($atc_root => $atc_root);

				$objs = array();
				$mns = join(",",$mn);
				if ($mns != "")
				{
					$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_OBJECT_TYPE." AND status = 2 AND parent IN (".$mns.") ORDER BY jrk");
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
			if ($_cldata["name"] != "" && $_cldata["can_add"])
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

		if (is_array($this->cfg["classes"]) && $prnt != 0)
		{
			foreach($this->cfg["classes"] as $clid => $cldata)
			{
				if ($cldata["parents"] != "" && $cldata["can_add"])
				{
					$parens = explode(",", $cldata["parents"]);
					if (in_array($prnt, $parens))
					{
						$addlink = $this->mk_my_orb("new", array("parent" => $parent, "period" => $period), $cldata["file"], true, true);
						$ret .= ($clid+1000)."|".((int)($prnt+4))."|".$cldata["name"]."|$addlink|list".$sep;
					}
				}
			}
//			$ret.="separator".$sep;
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
				$iconurl = icons::get_icon_url("homefolder","");
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
		$this->db_listall("objects.status != 0 AND objects.parent = '$parent' AND (menu.type != ".MN_FORM_ELEMENT." AND menu.type != ".MN_ADMIN1.") AND objects.lang_id = ".aw_global_get("lang_id") . " OR (menu.type = 69 AND objects.parent ='$parent')",true);

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
						$iconurl = icons::get_icon_url("promo_box","");
					}
					else
					if ($row["class_id"] == CL_BROTHER)
					{
						$iconurl = icons::get_icon_url("brother","");
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
		$act_per = $per->get_active_period();
		$plist = $per->period_list($act_per, true);
		foreach($plist as $id => $desc)
		{
			$act = $id == $act_per ? 1 : 0;
			printf("%d\t%s\t%d\n",$id,$desc,$act);
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
		$t = get_instance("groups");
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

		global $XYZ;
		if ($XYZ)
		{
			print "rec_tree with $parent<br>";
		};

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
				if ($sub == "" && $period > 0 && $row["periodic"] != 1) 
				{
					$show = false;
				}

				if ($show)
				{
					if ($row["class_id"] == CL_PROMO)
					{
						$url = icons::get_icon_url("promo_box","");
					}
					else
					if ($row["class_id"] == CL_BROTHER)
					{
						$url = icons::get_icon_url("brother","");
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
			$c = get_instance("config");
			$this->pr_icons = aw_unserialize($c->get_simple_config("program_icons"));
		}
		$i = $this->pr_icons[$fid]["url"];
		classload("icons");
		return icons::check_url($i == "" ? "/automatweb/images/icon_aw.gif" : $i);
	}

	////
	// !Tagastab nimekirja erinevatest menüütüüpidest
	function get_type_sel()
	{
		return array(
			"70" => LC_MENUEDIT_SECTION,
			"69" => LC_MENUEDIT_CLIENT,
			"71" => LC_MENUEDIT_ADMINN_MENU,
			"75" => LC_MENUEDIT_CATALOG,
			"77" => LC_MENUEDIT_PMETHOD,
		);
	}

	// shouldn't this be somewhere else? --duke
	function create_homes()
	{
		$upd = array();
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
			$upd[] = $id;
		}

		$this->invalidate_menu_cache($upd);
	}

	////
	// !sets the icon ($icon_id) for menu $id
	function set_menu_icon($id,$icon_id)
	{
		$af = $this->db_fetch_field("SELECT admin_feature FROM menu WHERE id = $id","admin_feature");
		if ($af)
		{
			$c = get_instance("config");
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

		if ($this->mar[$parent]["meta"]["show_periods"] && $this->mar[$parent]["meta"]["show_period_count"])
		{
			// now we replace the list of menus with list of periods
			// oh, god, I hate myself
			$this->mpr[$parent] = array();
			$per = get_instance("periods");
			$perlist = new aw_array($per->list_periods(0));
			foreach($perlist->get() as $key => $val)
			{
				$this->mpr[$parent][] = array(
					"name" => $val["description"],
					"comment" => $val["data"]["comment"],
					"type" => MN_CONTENT,
					"link" => $this->mk_my_orb("show",array("id" => $val["id"]),"contents"),
				);
			};
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
							"sel_menu_".$name."_L".$this->level."_image_".$_i => "<img src='".$imgurl."' border='0'>",
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
				$mv = new aw_array($meth["values"]);
				$mr = new aw_array($meth["required"]);
				foreach($mr->get() as $key => $val)
				{
					if (in_array($key,array_keys($mv->get())))
					{
						$values[$key] = $meth["values"][$key];
					}
					else
					{
						$err = true;
					};
				};

				$mo = new aw_array($meth["optional"]);
				foreach($mo->get() as $key => $val)
				{
					if (in_array($key,array_keys($mv->get())))
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
					if (!is_array($this->menu_aliases))
					{
						$this->menu_aliases = array();
					};
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

			$f = @fopen($fail, "r");
			if ($f)
			{
				$d = fread($f,filesize($fail));
				fclose($f);
			}
			else
			{
				return $this->mk_my_orb("import",array("parent" => $parent));
			};

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
		$i = get_instance("icons");
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
				$this->unserialize(array("str" => $str, "parent" => $parent, "period" => $period));
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
				$this->vars(array("DOCUMENT_LIST" => $this->parse("DOCUMENT_LIST")));
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
		$d = get_instance("document");
		// Vaatame, kas selle sektsiooni jaoks on "default" dokument
		if ($docid < 1) 
		{
			$docid = $this->get_default_document($section);
		};
		$ct = "";

		$jumpbox = false;
		if ($this->is_template("JUMPBOX") && ($this->mar[$section]["meta"]["multi_doc_style"]))
		{
			$jumpbox = true;
		};


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
				
				if ($jumpbox)
				{
					$oid = aw_global_get("oid");
					if ($oid)
					{
						$tpl = ($oid == $did) ? "ACTIVE_LINK" : "LINK";
					}
					else
					{
						if ($done)
						{
							$tpl = "LINK";
						}
						else
						{
							$tpl = "ACTIVE_LINK";
							$done = true;
						};
                                        }


					$dobj = $this->get_object($did);
					$this->vars(array(
						"text" => $dobj["name"],
						"link" => aw_global_get("baseurl") . "/section=$section" . "/oi
						d=$did",
					));
					$JMP .= $this->parse($tpl);
				}
				else
				{
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
				};

				if ($tpl == "ACTIVE_LINK")
				{
					$ct =$d->gen_preview(array(
						"docid" => $did,
						"section" => $section,
						"strip_img" => $strip_img,
						"tpl" => $longtpl,
						"tpls" => $tpls,
						"keywords" => 1,
						"no_strip_lead" => aw_global_get("document.no_strip_lead")
					));
				};

				if ($d->referer)
				{
					$metaref[] = $d->referer;
				}
				$dk++;
			} // while
			if ($jumpbox)
			{
				$this->vars(array(
					"LINK" => $JMP,
				));

				$this->vars(array(
					"JUMPBOX" => $this->parse("JUMPBOX"),
				));
			};

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
			$ar = new aw_array($tbld[$GLOBALS["tbl_sk"]]);
			foreach($ar->get() as $url)
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
		$doc = get_instance("document");
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
		$promos = array();
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

			$show_promo = false;

			if ($meta["all_menus"])
			{
				$show_promo = true;
			}
			else
			if (isset($meta["section"][$section]) && $meta["section"][$section])
			{
				$show_promo = true;
			}
			else
			if ( is_array($meta["section_include_submenus"]) && in_array($section,$this->path) )
			{
				// here we need to check, whether any of the parent menus for
				// this menu has been assigned a promo box and has been told
				// that it should be shown in all submenus as well
				$intersect = array_intersect($this->path,$meta["section_include_submenus"]);
				if (sizeof($intersect) > 0)
				{
					$show_promo = true;
				};
			};

			if ($found == false)
			{
				$show_promo = false;
			};
			
			// this line decides, whether we should show this promo box here or not.
			// now, how do I figure out whether the promo box is actually in my path?
			if ($show_promo)
			{
				// visible. so show it
				$this->save_handle();
				// get list of documents in this promo box
				$pr_c = "";
				$docid = $this->get_default_document($row["oid"],true);
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

				dbg::p($pr_c);

				$this->vars(array(
					"comment" => $row["comment"],
					"title" => $row["name"], 
					"content" => $pr_c,
					"url" => $row["link"],
					"link_caption" => $meta["link_caption"]
				));

				// which promo to use? we need to know this to use
				// the correct SHOW_TITLE subtemplate
				$templates = array(
					"scroll" => "SCROLL_PROMO",
					"0" => "LEFT_PROMO",
					"1" => "RIGHT_PROMO",
					"2" => "UP_PROMO",
					"3" => "DOWN_PROMO",
				);
	
				$use_tpl = $templates[$meta["type"]];
				if (!$use_tpl)
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

				if ($this->is_template($use_tpl . $ap))
				{
					$promos[$use_tpl] .= $this->parse($use_tpl . $ap);
					$this->vars(array($use_tpl . $ap => ""));
				}
				else
				{
					$promos[$use_tpl] .= $this->parse($use_tpl);
					$this->vars(array($use_tpl => ""));
				};
				// nil the variables that were imported for promo boxes
				// if we dont do that we can get unwanted copys of promo boxes
				// in places we dont want them
				$this->vars(array("title" => "", "content" => "","url" => ""));
				$this->restore_handle();
			}
		};

		$this->vars($promos);
	}

	function make_poll()
	{
		$t = get_instance("poll");
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
			$t = get_instance("search_conf");
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
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		$rdf = get_instance("rdf",array(
			"about" => "$baseurl/index.$ext/section=$section/format=rss",
			"title" => $obj["name"],
			"description" => $obj["description"],
			"link" => "$baseurl/index.$ext/section=$section",
		));

		// read all the menus and other necessary info into arrays from the database
		$this->make_menu_caches();

		// leiame, kas on tegemist perioodilise rubriigiga
		$periodic = $this->is_periodic($section);

		// loome sisu
		$d = get_instance("document");
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
		$sh = get_instance("shop/shop");
		$ret = $sh->show(array("section" => $section,"id" => $shop_id));
		$this->vars(array("shop_menus" => $sh->shop_menus));
		return $ret;
	}

	function make_langs()
	{
		$lang_id = aw_global_get("lang_id");
		$langs = get_instance("languages");
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
		$smi = "";
		// why the fuck did I put this in?!?!

		// I don't know, but we need SEL_MENU_IMAGE for director, so I'm
		// making this work again. -- duke
		//return false;
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
				$this->vars(array(
                                        "url" => image::check_url($sel_menu_meta["img_act_url"])
                                ));
                                $smi .= $this->parse("SEL_MENU_IMAGE");
                                break;

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
		$cache = get_instance("cache");

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
			$this->db_query("SELECT parent FROM objects WHERE oid IN(".join(",",map("%s", $ar)).")");
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
		$dbc = get_instance("config");
		$url = $dbc->get_simple_config("orb_err_mustlogin");
		header("Location: ".$this->cfg["baseurl"]."/$url");
		// exit from inside the class, yuck.
		exit;
	}


	////
	// !Redirect the user if he/she didn't have the right to view that section
	function no_access_redir()
	{
		$c = get_instance("config");
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
				$t = get_instance("extlinks");
				$link = $t->get_link($obj["oid"]);
				//list($url,$target,$caption) = $t->draw_link($obj["oid"]);
				header("Location: $link[url]");
				//$replacement = sprintf("<a href='%s' %s>%s</a>",$url,$target,$caption);
				exit;
				break;

			case CL_IMAGE:
				$t = get_instance("image");
				$idata = $t->get_image_by_id($obj["oid"]);
				$this->replacement = sprintf("<img src='%s'><br>%s",$idata["url"],$idata["comment"]);

				if ($this->raw)
				{
					print $this->replacement;
					exit;
				};
				break;

			case CL_FILE:
				$t = get_instance("file");
				die($t->show($obj["oid"]));
				break;

			case CL_TABLE:
				$t = get_instance("table");
				$this->replacement = $t->show(array("id" => $obj["oid"],"align" => $align));
				if ($this->raw)
				{	
					print $this->replacement;
					exit;
				};
				break;

			case CL_SITE_THREEPANE:
				$t = get_instance("site/site_threepane");
				print $t->show(array("id" => $obj["oid"]));
				exit;
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
		dbg::p("l = " . strlen($retval));
		return $retval;
	}

	function show_folders($arr)
	{
		extract($arr);
		$t = get_instance("languages");
		$sf = get_instance("aw_template");
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
		if (!$this->prog_acl("view", PRG_MENUEDIT))
		{
			$this->acl_error("view", PRG_MENUEDIT);
		}

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

		if ($period)
		{
			$ps = " AND ((period = '$period') OR (periodic = 1)) ";
		}

		$u = get_instance("users");
		$addobject_type = $u->get_user_config(array(
			"uid" => aw_global_get("uid"),
			"key" => "addobject_type",
		));

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
			if ($row["class_id"] == CL_PLANNER)
			{
				$chlink = $this->mk_my_orb("change",array("id" => $row["oid"],"cb_view" => "show"),"planner");
			}
			else
			{
				$chlink = $this->mk_my_orb("view", array("id" => $row["oid"], "parent" => $row["parent"]),$this->cfg["classes"][$row["class_id"]]["file"]);
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
			$iu = icons::get_icon_url($row["class_id"],$row["name"]);

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


		$content = $this->get_add_menu(array(
			"id" => $parent,
			"ret_data" => true,
			"sharp" => true,
			"addmenu" => 1,
			"period" => $period,
		));

		if ($addobject_type == "dhtml")
		{

			$items = explode("#",$content);

			// id|parent|caption|link

			$byparent = array();
			foreach($items as $item)
			{
				list($m_id,$m_parent,$m_caption,$m_link) = explode("|",$item);
				$real_parent = (int)$m_parent;
				if ($m_id)
				{
						  $by_parent[$real_parent][$m_id] = array(
							  "link" => $m_link,
							  "caption" => $m_caption,
						  );
				};
			};

			$this->read_template("js_add_menu.tpl");

			$whole_menu = "";


			// each parent starts a new menu
			foreach($by_parent as $item_id => $item_collection)
			{
				$menu_data = "";
				foreach($item_collection as $el_id => $el_data)
				{
					// if this el_id has children, make it a submenu
					$children = sizeof($by_parent[$el_id]);
					if ($el_id == "separator")
					{
						$tpl = "MENU_SEPARATOR";
					}
					elseif ($children > 0)
					{
						$tpl = "MENU_ITEM_SUB";
					}
					else
					{
						$tpl = "MENU_ITEM";
					};
					$this->vars(array(
						"caption" => $el_data["caption"],
						"url" => $el_data["link"],
						"sub_menu_id" => "aw_menu_" . $el_id,
					));
					$menu_data .= $this->parse($tpl);
				};
				$this->vars(array(
					"MENU_ITEM" => $menu_data,
					"menu_id" => "aw_menu_" . $item_id,
				));
				$whole_menu .= $this->parse("MENU");
			};
		};


		// make applet for adding objects
		$this->read_template("java_popup_menu.tpl");
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
			"content" => $content,
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

		if ($addobject_type == "dhtml")
		{
			$applet_data = $whole_menu;
		}
		else
		{
			$applet_data = $this->parse();
		};

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

		$this->add_object_type = $addobject_type;

		$toolbar = $this->rf_toolbar(array(
			"parent" => $parent,
			"add_applet" => $applet_data,
			"sel_count" => count($sel_objs),
		));

		$toolbar_data = $toolbar->get_toolbar();
		if ($addobject_type == "dhtml")
		{
			$toolbar_data .= $whole_menu;
		};

		$this->vars(array(
			"table" => $this->t->draw(),
			"reforb" => $this->mk_reforb("submit_rf", array("parent" => $parent, "period" => $period, "sortby" => $sortby, "sort_order" => $sort_order)),
			"types" => $this->picker(" ", $types),
			"parent" => $parent,
			"period" => $period,
			"lang_name" => $la->get_langid(),
			"toolbar" => $toolbar_data,
		));

		return $this->parse();
	}

	function rf_toolbar($args = array())
	{
		extract($args);
		$toolbar = get_instance("toolbar");
		
		if ($this->can("add", $parent))
		{
			if ($this->add_object_type == "dhtml")
			{
				$toolbar->add_button(array(
					"name" => "add",
					"tooltop" => "Uus",
					"url" => "#",
					"onClick" => "return buttonClick(event, 'aw_menu_0');",
					"img" => "new.gif",
					"imgover" => "new_over.gif",
					"class" => "menuButton",
				));
			}
			else
			{
				$toolbar->add_cdata($add_applet);
			};
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
		$upd = array();

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
						$upd[] = $oid;
					}
				}
			}
		}
		$this->invalidate_menu_cache($upd);
		return $this->mk_my_orb("right_frame", array("parent" => $parent, "period" => $period, "sortby" => $sortby, "sort_order" => $sort_order));
	}

	function new_delete($arr)
	{
		extract($arr);
		$upd = array();
		if (is_array($sel))
		{
			$oids = join(",",array_keys($sel));
			$this->db_query("SELECT oid,class_id FROM objects WHERE oid IN($oids)");
			while ($row = $this->db_next())
			{
				$this->save_handle();
				if ($this->cfg["classes"][$row["class_id"]]["file"] != "")
				{
					$inst = get_instance($this->cfg["classes"][$row["class_id"]]["alias_class"] != "" ? $this->cfg["classes"][$row["class_id"]]["alias_class"] : $this->cfg["classes"][$row["class_id"]]["file"]);
					if (method_exists($inst, "delete_hook"))
					{
						$inst->delete_hook(array("oid" => $row["oid"]));
					}
				}
				$this->delete_object($row["oid"]);
				$upd[] = $row["oid"];
				$this->restore_handle();
			}
		}
		$this->invalidate_menu_cache($upd);
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
		return "<html><body>&nbsp;</body></html>";
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

				$hash = gen_uniq_id();
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

				$this->save_handle();
				$this->req_serialize_obj_tree($cur_id);
				$this->restore_handle();
			}
		}
		if ($this->serialize_subobjs || aw_global_get("__is_rpc_call"))
		{
			$this->db_query("SELECT oid FROM objects WHERE parent = $oid AND status != 0 AND class_id != ".CL_PSEUDO." AND lang_id = '".aw_global_get("lang_id")."' AND site_id = '".$this->cfg["site_id"]."'");
			while ($row = $this->db_next())
			{
				$dat = $this->serialize(array("oid" => $row["oid"]));
				if ($dat !== false)
				{
					$hash = gen_uniq_id();
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
		$hash = gen_uniq_id();
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

		if ($this->serialize_submenus || aw_global_get("__is_rpc_call"))
		{
			$this->req_serialize_obj_tree($oid);
		}
		else
		{
			if ($this->serialize_subobjs || aw_global_get("__is_rpc_call"))
			{
				$this->db_query("SELECT oid FROM objects WHERE parent = $oid AND status != 0 AND class_id != ".CL_PSEUDO." AND lang_id = '".aw_global_get("lang_id")."' AND site_id = '".$this->cfg["site_id"]."'");
				while ($row = $this->db_next())
				{
					$dat = $this->serialize(array("oid" => $row["oid"]));
					if ($dat !== false)
					{
						$hash = gen_uniq_id();
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
