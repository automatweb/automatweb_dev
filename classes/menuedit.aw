<?php
// $Header: /home/cvs/automatweb_dev/classes/menuedit.aw,v 2.110 2002/03/07 23:24:54 duke Exp $
// menuedit.aw - menuedit. heh.

// number mille kaudu tuntakse 2ra kui tyyp klikib kodukataloog/SHARED_FOLDERS peale
define("SHARED_FOLDER_ID",2147483647);

session_register("cut_objects","copied_objects");

lc_load("menuedit");
classload("cache","defs","php");

class menuedit extends aw_template
{
	// me peame ju kuidagi vahet tegema, kas vaatame perioodi
	// voi lihtsalt koiki staatilisi dokumente
	function menuedit($period = 0,$pname = "")
	{
		$this->tpl_init("automatweb/menuedit");
		$this->db_init();
		$this->cache = new cache;
		$this->feature_icons_loaded = false;

		// this is set if only one document is shown, a document which can be edited
		$this->active_doc = false;

		global $lc_menuedit;
		if (is_array($lc_menuedit))
		{
			$this->vars($lc_menuedit);
		}
		lc_site_load("menuedit",$this);
		if (isset($GLOBALS["lc_menuedit"]) && (is_array($GLOBALS["lc_menuedit"])))
		{
			$this->vars($GLOBALS["lc_menuedit"]);
		}
		lc_load("definition");
	}

	function mk_folders($parent,$str)
	{
		if (!isset($this->menucache[$parent]) || !is_array($this->menucache[$parent]))
			return;

		global $awt;
		$awt->start("menuedit::mk_folders");
		reset($this->menucache[$parent]);
		while(list(,$v) = each($this->menucache[$parent]))
		{
			$name = $v["data"]["name"];
			if ($v["data"]["parent"] == 1)
			{
				$words = explode(" ",$name);
				if (count($words) == 1)
					$name = $words[0][0].$words[0][1];
				else
				{
					reset($words);
					$mstr = "";
					while(list(,$v3) = each($words))
						$mstr.=$v3[0];
					$name = $mstr;
				}
			}

			$sep = ($str == "" ? "" : " / ");
			$tstr = $str.$sep.$name;

			if (isset($v["data"]["oid"]) && isset($this->extrarr[$v["data"]["oid"]]) && is_array($this->extrarr[$v["data"]["oid"]]))
			{
				reset($this->extrarr[$v["data"]["oid"]]);
				while (list(,$v2) = each($this->extrarr[$v["data"]["oid"]]))
					$this->docs[$v2["docid"]] = $tstr." / ".$v2["name"];
			}

			$this->mk_folders($v["data"]["oid"],$tstr);
		}
		$awt->stop("menuedit::mk_folders");
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
		));
		$q = sprintf("INSERT INTO menu (id,type) VALUES (%d,%d)",$newoid,MN_HOME_FOLDER_SUB);
		$this->db_query($q);
		$this->_log("menuedit",sprintf(LC_MENUEDIT_ADDED_HOMECAT_FOLDER,$args[name]));

		$this->invalidate_menu_cache();

		return $newoid;

	}

	function rd($parent)
	{
		$this->db_query("SELECT * FROM objects WHERE parent = $parent AND class_id = 1 AND status != 0 AND objects.lang_id=".$GLOBALS["lang_id"]."");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$this->rd($row["oid"]);
			$this->restore_handle();
		}

		$this->delete_object($parent);
		$this->invalidate_menu_cache();
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
		global $awt;
		$awt->start("menuedit::gen_site_html");
		// kontrollib sektsiooni ID-d, tagastab oige numbri kui tegemist oli
		// aliasega, voi lopetab töö, kui miskit oli valesti
		$section = $this->check_section($params["section"]);
		$params["section"] = $section;

		global $format,$act_per_id;
		if ($format == "rss")
		{
			classload("document");
			$d = new document();
			$d->gen_rss_feed(array("period" => $act_per_id,"parent" => $section));

                };

		// koostame array vajalikest parameetritest, mis identifitseerivad cachetava objekti
		$cp = array();
//		$periodic = $this->is_periodic($section,0);
//		if ($periodic)
//		{
			$cp[] = $GLOBALS["act_per_id"];
//		};
		if (isset($GLOBALS["page"]))
		{
			$cp[] = $GLOBALS["page"];
		}

		$cp[] = $GLOBALS["lang_id"];

		$not_cached = false;

		if ($GLOBALS["docid"])
		{
			$cp[] = $GLOBALS["docid"];
			if ($this->cache_dirty($GLOBALS["docid"]))
			{
				$not_cached = true;
				$this->clear_cache($GLOBALS["docid"]);
			}; 
		};

		$use_cache = true;

		if ($params["print"] || $params["text"])
		{
			$not_cached = true;
			$use_cache = false;
		};

		if (!($res = $this->cache->get($section,$cp)) || $params["format"] || $not_cached)
		{
			// seda objekti pold caches
			$res = $this->_gen_site_html($params);
			if ($use_cache)
			{
				$this->cache->set($section,$cp,$res);
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
		global $banner_defs;
		if (is_array($banner_defs))
		{
			reset($banner_defs);
			while (list($name,$gid) = each($banner_defs))
			{
				$res = str_replace("[ss".$gid."]",$this->gen_uniq_id(),$res);
			}
		}
		$awt->stop("menuedit::gen_site_html");
		return $res;
	}
	
	////
	// !da thing. draws the site 
	// params: section, text, docid, strip_img, template, homedir, special, format, vars, no_left_pane, no_right_pane
	// niisiis. vars array peaks sisaldama mingeid pre-parsed html tükke,
	// mis võivad tulla ntx kusagilt orbi klassi seest vtm.
	// array keydeks peaksid olema variabled template sees, mis siis asendatakse
	// oma väärtustega
	function _gen_site_html($params)
	{
		extract($params);	
		$template = isset($template) && $template != "" ? $template : "main.tpl";
		$docid = isset($docid) ? $docid : 0;


		// debuukimiseks
		global $SITE_ID;
		// impordime taimeriklassi
		global $test;
		global $baseurl;
		global $awt;
		$awt->start("sh");

		global $lang_id,$LC;
		$lang_code = $LC;
		$this->vars(array(
			"lang_code" => $lang_code,
		));
		
		$obj = $this->get_object($section);

		$this->check_object($obj);

		if (not($text))
		{
			$text = $this->replacement;
		};

		// this should be inexpensive, since it caches all the object, and if 
		// for example the document class does the same, most objecst should
		// already be cached.


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

		global $DEBUG;


		$this->vars(array(
						"per_string" => $GLOBALS["act_period"]["description"],
		));

		// check whether access to that menu is denied by ACL and if so
		// redirect the user 
		if (not($this->can("view", $section)))
		{
			$this->no_access_redir();
		}

		// main.tpl-i muutuste testimiseks ilma seda oiget main.tpl-i muutmata
		// kasutasin seda ntx skycraper bänneri sobitamiseks seltskonna sisse
		if ($test == 1)
		{
			$template = "main2.tpl";
		};

		// by default show both panes.
		$this->left_pane = (isset($no_left_pane) && $no_left_pane == true) ? false : true;
		$this->right_pane = (isset($no_right_pane) && $no_right_pane == true) ? false : true;

		// read all the menus and other necessary info into arrays from the database
		global $DEBUG;
		if ($DEBUG)
		{
			print "active language for menus is $GLOBALS[lang_id]<br>";
		}
		$this->make_menu_caches();

		// leiame, kas on tegemist perioodilise rubriigiga
		$periodic = $this->is_periodic($section);

		if ($obj["class_id"] == CL_DOCUMENT || $obj["class_id"] == CL_PERIODIC_SECTION)
		{
			$this->sel_section = $obj["parent"];
		}
		else
		{
			$this->sel_section = $section;
		}

		$sel_menu_id = $this->sel_section;

		$this->vars(array(
			"sel_menu_id" => $sel_menu_id
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
		if ($this->properties["tpl_dir"])
		{
			$this->tpl_init(sprintf("../%s/automatweb/menuedit",$this->properties["tpl_dir"]));
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
		//$path = $this->path;
		$this->path = $path;

		// you are here links		
		$this->vars(array("YAH_LINK" => $this->make_yah($this->path)));

		// language selecta
		if ($this->is_template("LANG"))
		{
			$this->make_langs();
		}


		// write info about viewing to the syslog
		$this->do_syslog($section);

		// right, now build the menus
		global $menu_defs,$rootmenu;

		// this will contain all the menus parsed from templates
		$outputs = array();	

		$ce = false;

		$section_subitems = sizeof($this->mpr[$section]);

		$this->section = $section;

		global $menu_defs_v2,$frontpage;

		if (isset($menu_defs_v2) && is_array($menu_defs_v2))
		{
				$nx = "";
				$this->level = 0;
				reset($menu_defs_v2);
				while (list($id,$name) = each($menu_defs_v2))
				{
					$nx = $name;
					global $DEBUG;
					if ($DEBUG)
					{
						print "drawing $id,$name<br>";
					};
					$awt->start("menuedit::rdrawmenu");
					$this->req_draw_menu($id,$name,&$path,false);
					$awt->stop("menuedit::rdrawmenu");
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
		
		if ($this->is_template("NADALA_NAGU"))
		{
			$this->make_nadalanagu();
		};

		$this->make_banners();
	
		$this->do_sub_callbacks($sub_callbacks);

		global $sstring;
		$this->vars(array(
			"ss" => $this->gen_uniq_id(),		// bannerite jaox
			"ss2" => $this->gen_uniq_id(),
			"ss3" => $this->gen_uniq_id(),
			"link" => "",
			"section"	=> $section,
			   "uid" => aw_global_get("uid"),
			   "date" => $this->time2date(time(), 2),
			   "date2" => $this->time2date(time(), 8),
			"sstring" => $sstring,
			"IS_FRONTPAGE" => ($section == $GLOBALS["frontpage"] ? $this->parse("IS_FRONTPAGE") : ""),
			"IS_FRONTPAGE2" => ($section == $GLOBALS["frontpage"] ? $this->parse("IS_FRONTPAGE2") : "")
		));

		// sucks.
		if ($this->mar[$section]["parent"] == 34506 || $this->mar[$this->mar[$section]["parent"]]["parent"] == 34506 || $section == $GLOBALS["frontpage"])
		{
			$this->vars(array(
				"IS_AWCOM_FRONTPAGE" => $this->parse("IS_AWCOM_FRONTPAGE"),
				"MOSTIMP" => '<span class="pealkiri2">'.$this->mar["34506"]["name"].'</span>'
			));
		}

		// what's that for?
		if (is_array($vars))
		{
			$vars["LEFT_PROMO"] .= $this->vars["LEFT_PROMO"];
			$this->vars($vars);
		}

		// eek.

		$cd = "";

		if (aw_global_get("uid") == "")
		{
			$login = $this->parse("login");
			$this->vars(array("login" => $login, "logged" => ""));
		}
		else
		{
			classload("users");
			$t = new users;
			// but why? we already should have the info about that?
			$udata = $this->get_user(array("uid" => aw_global_get("uid")));
			$jfar = $t->get_jf_list(isset($udata["join_grp"]) ? $udata["join_grp"] : "");
			$jfs = "";
			reset($jfar);
			while (list($fid,$name) = each($jfar))
			{
				$this->vars(array("form_id" => $fid, "form_name" => $name));
				$jfs.=$this->parse("JOIN_FORM");
			}
			$this->vars(array("JOIN_FORM" => $jfs));

			// check menuedit access
			if ($this->prog_acl("view", PRG_MENUEDIT))
			{
				$this->vars(array("MENUEDIT_ACCESS" => $this->parse("MENUEDIT_ACCESS")));
				// so if this is the only document shown and the user has edit right
				// to it, parse and show the CHANGEDOCUMENT sub
				if ($this->can("edit",$section) && $this->active_doc)
				{
					$cd = $this->parse("CHANGEDDOCUMENT");
				};
			}
			else
			{
				$this->vars(array("MENUEDIT_ACCESS" => ""));
			}
			$logged = $this->parse("logged");
			$this->vars(array(
				"logged" => $logged, 
				"logged1" => $this->parse("logged1"),
				"logged2" => $this->parse("logged2"),
				"logged3" => $this->parse("logged3"),
				"login" => ""
			));
		};

		$lp = "";
		$rp = "";
		if ($this->left_pane)
		{
			$lp = $this->parse("LEFT_PANE");
		}
		if ($this->right_pane)
		{
			$rp = $this->parse("RIGHT_PANE");
		}
		$this->vars(array("LEFT_PANE" => $lp, "RIGHT_PANE" => $rp));

		if ($section == $GLOBALS["frontpage"])
		{
			$this->vars(array("IS_FRONTPAGE" => $this->parse("IS_FRONTPAGE")));
		}

		$popups = $this->build_popups();

		$this->vars(array(
			   "CHANGEDOCUMENT" => $cd,
		));
		return $this->parse() . $popups;
	}

	function is_periodic($section,$checkobj = 1) 
	{
		global $awt;
		$awt->start("menuedit::is_periodic");
		//$mn = $this->get_menu($section);
		$mn = $this->mar[$section];
		$periodic = $mn["periodic"];
		// menyysektsioon ei ole perioodiline. Well, vaatame 
		// siis, kas ehk dokument ise on?
		if (!$periodic && $checkobj == 1) {
		$q = "SELECT period FROM objects WHERE oid = '$section'";
		$periodic = $this->db_fetch_field($q,"period");
		};
		$awt->stop("menuedit::is_periodic");
		return $periodic;
	}

	function has_sub_dox($oid)
	{
		global $awt;
		$awt->start("menuedit::has_sub_dox");
		$awt->count("menuedit::has_sub_dox");
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

		$awt->stop("menuedit::has_sub_dox");
		return $has_dox;
	}

	function db_prep_listall($where = " objects.status != 0") 
	{
		global $awt;
		$awt->start("menuedit::db_prep_listall");
		global $act_per_id;
		if ($act_per_id) 
		{
			$sufix = "AND period = '$act_per_id'";
		} 
		else 
		{
			$sufix = "";
		};
		$q = "SELECT objects.parent AS parent,count(*) as subs
						FROM objects WHERE $where $sufix
						GROUP BY parent";
		$this->subs = array();
		$this->db_query($q);
		while($row = $this->db_next()) 
		{
			$this->subs[$row["parent"]] = $row["subs"];
		};
		$awt->stop("menuedit::db_prep_listall");
	}

	////
	// !Listib koik objektid
	function db_listall($where = " objects.status != 0",$ignore = false,$ignore_lang = false)
	{
		global $awt;
		$awt->start("menuedit::db_listall");
		global $SITE_ID;
		$aa = "";
		if (!$ignore)
		{
			// loeme sisse koik objektid
			$aa = "AND ((objects.site_id = '".$GLOBALS["SITE_ID"]."') OR (objects.site_id IS NULL))";
		};
		if ($GLOBALS["lang_menus"] == 1 && $ignore_lang == false)
		{
			$aa.="AND (objects.lang_id=".$GLOBALS["lang_id"]." OR menu.type = ".MN_CLIENT.")";
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
		$this->db_query($q);
		$awt->stop("menuedit::db_listall");
	}

	function db_listall_lite($where = " objects.status != 0",$ignore = false,$ignore_lang = false)
	{
		global $awt;
		$awt->start("menuedit::db_listall_lite");
		global $SITE_ID;
		if (!$ignore)
		{
			// loeme sisse koik objektid
			$aa = "AND ((objects.site_id = '".$GLOBALS["SITE_ID"]."') OR (objects.site_id IS NULL))";
		};
		if ($GLOBALS["lang_menus"] == 1 && $ignore_lang == false)
		{
			$aa.="AND (objects.lang_id=".$GLOBALS["lang_id"]." OR menu.type = ".MN_CLIENT.")";
		}
		$q = "SELECT objects.oid as oid, 
				objects.parent as parent,
				objects.name as name,
				objects.last as last,
				objects.jrk as jrk,
				objects.alias as alias,
				objects.brother_of as brother_of,
				objects.metadata as metadata,
				objects.class_id as class_id,
				objects.comment as comment,
				menu.*
			FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")  AND menu.type != ".MN_FORM_ELEMENT." AND $where $aa
				ORDER BY objects.parent, jrk,objects.created";
		$this->db_query($q);
		$awt->stop("menuedit::db_listall_lite");
	}

	function get_default_document($section,$ignore_global = false)
	{
		global $awt;
		$awt->start("menuedit::get_default_document");
		// the following line looks so wrong
		// /me vaatab syytult lakke ja teeb n2gu nagu ei saax midagi aru - terryf
		if (isset($GLOBALS["docid"]) && $GLOBALS["docid"] && $ignore_global == false)
		{
			$awt->stop("menuedit::get_default_document");
			return $GLOBALS["docid"];
		}
		if (!$section)
		{
			$awt->stop("menuedit::get_default_document");
			return 0;
		}

		$obj = $this->get_object($section);	// if it is a document, use this one. 
		if ($obj["class_id"] == CL_DOCUMENT)
		{
			$awt->stop("menuedit::get_default_document");
			return $section;
		}

		if ($obj["class_id"] == CL_BROTHER)
		{
			$obj = $this->get_object($obj["brother_of"]);
		}

		$docid = $obj["last"];
		$ar = unserialize($docid);
		if (is_array($ar))	// kuna on vaja mitme keele jaox default dokke seivida, siis uues versioonis pannaxe siia array
												// aga backward compatibility jaox tshekime, et 2kki see on integer ikkagi
		{
			$docid = $ar[$GLOBALS["lang_id"]];
		}
		if ($docid > 0)
		{
			if ($GLOBALS["lang_menus"] == 1)
			{
				$ss = "AND objects.lang_id=".$GLOBALS["lang_id"];
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
			$me_row = $this->get_menu($section);
			//$me_row = $this->mar[$section];
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
					$pstr = "objects.parent = $obj[oid]";
				};
			}
			else
			{
				$pstr = "objects.parent = $obj[oid]";
			};
			if ($me_row["ndocs"] > 0)
			{
				$lm = "LIMIT ".$me_row["ndocs"];
			};

			$docid = array();
			$cnt = 0;
			if ($ordby == "")
			{
				$ordby = "objects.jrk";
			}
			$q = "SELECT objects.oid as oid,objects.class_id AS class_id, objects.brother_of AS brother_of, documents.esilehel as esilehel FROM objects LEFT JOIN documents ON documents.docid = objects.brother_of WHERE (($pstr AND status = 2 AND class_id = 7 AND objects.lang_id=".$GLOBALS["lang_id"].") OR (class_id = ".CL_BROTHER_DOCUMENT." AND status = 2 AND $pstr)) $lsas ORDER BY $ordby $lm";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				if (!($GLOBALS["no_fp_document"] && $row["esilehel"] == 1))
				{
					$docid[$cnt++] = ($row["class_id"] == CL_DOCUMENT) ? $row["oid"] : $row["brother_of"];
				}
			}
			if ($cnt > 1)
			{
				// a list of documents
				$awt->stop("menuedit::get_default_document");
				return $docid;
			}
			else
			if ($cnt == 1)
			{
				// the correct id
				$awt->stop("menuedit::get_default_document");
				return $docid[0];
			}
			else
			{
				$awt->stop("menuedit::get_default_document");
				return false;
			}
		}

		$awt->stop("menuedit::get_default_document");
		return $docid;
	}

	function do_syslog_core($log,$section)
	{
		global $uid,$artid,$sid,$mlxuid,$awt;
		$awt->start("menuedit::do_syslog_core");
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
				$this->_log("pageview",$ml_user["name"]." (".$ml_user["mail"].") vaatas lehte $log",$section);
		}
		else
			$this->_log("pageview",sprintf(LC_MENUEDIT_LOOKED_SITE,$log),$section);
		$awt->stop("menuedit::do_syslog_core");
	}

	function do_syslog($section = 0)
	{
		global $awt;
		$awt->start("menuedit::do_syslog");
		// now build the string to put in syslog
		$log = "";
		$names = array();
		foreach($this->path as $val)
		{
			$names[] = $this->menu_chain[$val]["name"];
		};

		$log = join(" / ",$names);
		$this->do_syslog_core($log,$section);
		$awt->stop("menuedit::do_syslog");
	}

	function check_section($section)
	{
		global $awt;
		$awt->start("menuedit::check_section");
		global $frontpage;

		// kui sektsiooni viimane märk on "-", paneme selle objekti sees püsti
		// raw flagi
		if (substr($section,-1) == "-")
		{
			$this->raw = 1;
			// cut the minus sign
			$section = substr($section,0,-1);
		};

		if ($section == "")
		{
			$awt->stop("menuedit::check_section");
			return $frontpage < 1 ? 1 : $frontpage;
		}

		// sektsioon ei olnud numbriline
		if (!is_number($section)) 
		{
			// vaatame, kas selle nimega aliast on?
			$obj = $this->_get_object_by_alias($section);
			// nope. mingi skriptitatikas? voi cal6
			// inside joked ruulivad exole duke ;)
			// nendele kes aru ei saanud - cal6 ehk siis kalle volkov - ehk siis okia tyyp 
			// oli esimene kes aw seest kala leidis - kui urli panna miski oid, mida polnud, siis asi hangus - see oli siis kui 
			// www.struktuur.ee esimest korda v2lja tuli. 
			// niiet nyyd te siis teate ;)
			// - terryf
			if (!$obj) 
			{
				$this->_log("menuedit",sprintf(LC_MENUEDIT_TRIED_ACCESS,$section));
				// neat :), kui objekti ei leita, siis saadame 404 koodi
				header ("HTTP/1.1 404 Not Found");
				printf(E_ME_NOT_FOUND);
				exit;
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
				$section = $globals["frontpage"];
			}
		};

		$awt->stop("menuedit::check_section");
		return $section;
	}

	// well, mul on vaja kuvada see asi popupi sees, niisiis tegin ma miniversiooni folders.tpl-ist
	// ja lisasin siia uue parameetri
	function gen_folders($period,$popup = 0)
	{
		global $awt;
		$awt->start("menuedit::gen_folders");
		if ($popup == 1)
		{
			$this->read_template("popup.tpl");
		}
		else
		{
			global $SITE_ID;
			global $DBG;
			if ($SITE_ID == 88)
			{
				$this->read_template("folders_no_periods.tpl");
			}
			elseif ($DBG)
			{
				$this->read_template("folders_new.tpl");
			}
			else
			{
				$this->read_template("folders.tpl");
			};
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
				$arr[$row["parent"]][] = $row;
				$mpr[] = $row["parent"];
			}
		}
/*		$this->listacl("objects.status != 0 AND objects.class_id = ".CL_PROMO);
		$this->db_query("SELECT objects.*, menu.* FROM objects
				LEFT JOIN menu ON menu.id = objects.oid
				WHERE objects.class_id = ".CL_PROMO." AND objects.status != 0");
		while ($row = $this->db_next())
		{
			if ($this->can("view",$row["oid"]))
			{
				$ar = unserialize($row["comment"]);
				$row["sections"] = $ar;
				if (is_array($ar["section"]))	
				{
					// put the promo box under all the places it must be shown
					reset($ar);
					while (list($v,$sid) = each($ar["section"]))
					{
						$row["parent"] = $sid;
						$arr[$sid][] = $row;
					}
				}
			}
		}*/

		// objektipuu
		$tr = $this->rec_tree(&$arr, $GLOBALS["admin_rootmenu2"],$period);

		// kodukataloom
		$tr.=$this->mk_homefolder(&$arr);

		// the whole she-bang
		$arr = array();
		$this->db_listall("objects.status = 2 AND menu.type = ".MN_ADMIN1,true,true);
		while ($row = $this->db_next())
		{
			$arr[$row["parent"]][] = $row;
		}
		$tr.= $this->rec_admin_tree(&$arr, $GLOBALS["amenustart"]);

		$this->vars(array(
			"TREE" => $tr,
			"DOC" => "",
			"root" => $GLOBALS["admin_rootmenu2"],
			"uid" => $GLOBALS["uid"],
			"date" => $this->time2date(time(),2)
		));

		// perioodide tropp.
		if ($GLOBALS["per_oid"])
		{
			classload("periods");
			$dbp = new db_periods($GLOBALS["per_oid"]);
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
		$awt->stop("menuedit::gen_folders");
		return $this->parse();
	}

	function rec_homefolder(&$arr,$parent)
	{
		if (!is_array($arr[$parent]))
			return "";

		global $PHP_SELF,$baseurl,$ext;

		$ret = "";
		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			$sub = $this->rec_tree(&$arr,$row["oid"],0);
			$this->vars(array(
				"name" => $row["name"],
				"id" => $row["oid"],
				"parent" => $row["parent"],
				"iconurl" => isset($row["icon_id"]) && $row["icon_id"] != "" ? $baseurl."/icon.".$ext."?id=".$row["icon_id"] : "images/ftv2doc.gif",
				"url" => "menuedit_right.".$GLOBALS["ext"]."?parent=".$row["oid"]));
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
		global $udata,$uid,$admin_rootmenu2,$ext,$baseurl,$awt;
		$awt->start("menuedit::mk_homefolder");

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
			"url" => "menuedit_right.".$GLOBALS["ext"]."?parent=".$hf["oid"]
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
				"iconurl" => $row["icon_id"] ? $baseurl."/icon.".$ext."?id=".$row["icon_id"] : "images/ftv2doc.gif",
				"url"	=> "menuedit_right.".$GLOBALS["ext"]."?parent=".$v["oid"]));
			$shares.=$this->parse("DOC");
		}

		$this->vars(array(
			"name"=> "SHARED FOLDERS",
			"id" => SHARED_FOLDER_ID,		
			"parent" => $hf["oid"],
			"iconurl" => $this->get_icon_url("shared_folders",""),
			"url" => "menuedit_right.".$GLOBALS["ext"]."?parent=".SHARED_FOLDER_ID
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
		// mis need on? tänased keno loto võidunumbrid?
		$dbu->listgroups(-1,-1,4);
		$grps_arr = array();
		while ($row = $dbu->db_next())
		{
			$row["oid"] = $row["gid"];
			$grps_arr[$row["parent"]][] = $row;
		}
		$dgid = $dbu->get_gid_by_uid(UID);
		$grptree = $this->rec_tree_grps(&$grps_arr, $dgid);

		$this->vars(array(
			"name"		=> "GROUPS",
			"id"			=> "gr_".$dgid,		
			"parent"	=> $hf["oid"],
			"iconurl" => $this->get_icon_url("hf_groups",""),
			"url"			=> $this->mk_orb("mk_grpframe",array("parent" => $dgid),"groups")
		));
		if ($grptree != "")
			$grps = $this->parse("TREE");
		else
			$grps = $this->parse("DOC");

		$ret = $hft.$shfs.$shares.$grps.$grptree.$ret;

		$awt->stop("menuedit::mk_homefolder");
		return $ret;
	}


	function get_shared_arr(&$arr,$exclude)
	{
		global $awt;
		$awt->start("menuedit::get_shared_arr");
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
		$awt->stop("menuedit::get_shared_arr");
		return $ret;
	}

	function rec_admin_tree(&$arr,$parent)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
			return "";

		global $admin_rootmenu2,$ext,$baseurl;

		reset($arr[$parent]);
		$ret = "";
		while (list(,$row) = each($arr[$parent]))
		{
			if ($row["status"] != 2)
				continue;
			if ($row["admin_feature"] && !$this->prog_acl("view", $row["admin_feature"]) && ($GLOBALS["check_prog_acl"]))
				continue;

			$sub = $this->rec_admin_tree(&$arr,$row["oid"]);

			if ($row["admin_feature"])
			{
				$sub.=$this->get_feature_tree($row["admin_feature"],$row["oid"]);
			}

			$iconurl = isset($row["icon_id"]) && $row["icon_id"] != "" ? $baseurl."/icon.".$ext."?id=".$row["icon_id"] : ($row["admin_feature"] ? $this->get_feature_icon_url($row["admin_feature"]) : "images/ftv2doc.gif");
			$this->vars(array(
				"name"		=> $row["name"],
				"id"			=> ($row["admin_feature"] == 4 ? "gp_" : "").$row["oid"], 
				"parent"	=> ($parent == $GLOBALS["amenustart"] ? $admin_rootmenu2 : $row["parent"]),
				"iconurl" =>  $iconurl,
				"url"			=> $row["link"] != "" ? $row["link"] : ($row["admin_feature"] ? $GLOBALS["programs"][$row["admin_feature"]]["url"] : "blank.html")));

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
		global $awt;
		$awt->start("menuedit::mk_grp_tree");
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
			// we must convert the parent member so that it actually points to the parent OBJECT not the parent group
			$puta = isset($row["parent"]) ? $row["parent"] : 0;
			$row["parent"] = isset($grar[$puta]["oid"]) ? $grar[$puta]["oid"] : 0;

			if ($row["parent"] == 0)
			{
				$row["parent"] = $parent;
			}
			$grpcache[$row["parent"]][] = $row;
		}
		$ret = $this->rec_grp_tree(&$grpcache,$parent);
		$awt->stop("menuedit::mk_grp_tree");
		return $ret;
	}

	function rec_grp_tree(&$arr,$parent)
	{
		if (!isset($arr[$parent]) || !is_array($arr[$parent]))
			return "";

		global $PHP_SELF;

		$ret = "";
		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			if (!$this->can("view",$row["oid"]) || $row["gid"] == $GLOBALS["all_users_grp"])
			{
				continue;
			}

			$sub = $this->rec_grp_tree(&$arr,$row["oid"]);
			$this->vars(array(
				"name" => $row["name"],"id" => "gp_".$row["oid"], "parent" => "gp_".$row["parent"],
				"iconurl" => "images/ftv2doc.gif",
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
			return "";

		global $PHP_SELF,$baseurl,$ext;

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
					$show = false;

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
						$url = isset($row["icon_id"]) && $row["icon_id"] > 0 ? $baseurl."/icon.".$ext."?id=".$row["icon_id"] : "images/ftv2doc.gif";
					}
					$this->vars(array(
						"name" => $row["name"],
						"id" => $row["oid"],
						"parent" => $row["parent"],
						"iconurl" => $url,
						"url" => "menuedit_right.".$GLOBALS["ext"]."?parent=".$row["oid"]."&period=".$period));
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
			return "";

		reset($arr[$parent]);
		while (list(,$row) = each($arr[$parent]))
		{
			$sub = $this->rec_tree_grps(&$arr,$row["oid"]);
			$this->vars(array(
				"name" => $row["name"],
				"id" => "gr_".$row["oid"],
				"parent" => "gr_".$row["parent"],
				"iconurl" => "images/ftv2doc.gif",
				"url" => $this->mk_orb("mk_grpframe",array("parent" => $row["gid"]),"groups")));
				//$this->mk_orb("list_grps_user",array("parent" => $row[oid]),"groups"))
			$ret .= ($sub == "") ? $this->parse("DOC") : $this->parse("TREE");
		}
		return $ret;
	}

	function gen_list($parent,$period=0)
	{
		if (is_array($parent))
		{
			extract($parent);
		}
		$period = $GLOBALS["period"];
		return $this->gen_list_menus($parent,$period);
	}

	function gen_list_menus($parent,$period)
	{
		if (is_array($parent))
		{
			extract($parent);
		};	

		if (!$this->can("view", $parent))
		{
			return $this->acl_error("view",$parent);
		}
		global $awt;
		$awt->start("menuedit::gen_list_menus");

		// selle voiks ju ka tablegenni peale ajada.
		global $sortby;

		if ($sortby == "")
		{
			$sortby = "jrk";
		};

		global $order,$baseurl;
		if ($order == "")
		{
			$order = "ASC";
		};

		$this->read_template("menus.tpl");

		$this->mk_path($parent,"");
		$this->vars(array(
			"parent" => $parent,
			"addmenu" => $this->mk_orb("new", array("parent" => $parent)),
			"period"	=> $period,
			"import"	=> $this->mk_orb("import", array("parent" => $parent))
		));

		// vaikimisi on need tühjad
		$can_add = "";
		$can_paste = "";
		$can_add_promo = "";

		global $cut_objects;
		// ja lühikesed muutujad imevad. so...
		//$ca = $cp = $ap = "";
		if ($this->can("add",$parent))
		{
			$can_add = $this->parse("ADD_CAT");
			if (count($cut_objects) > 0)
			{
				$can_paste = $this->parse("PASTE");
			}
			$can_add_promo = $this->parse("CAN_ADD_PROMO");
		}
		
		$this->vars(array(
			"ADD_CAT"=> $can_add,
			"PASTE" => $can_paste,
			"CAN_ADD_PROMO" => $can_add_promo
		));

		$this->listacl("objects.class_id = ".CL_PSEUDO." AND objects.status != 0 AND objects.parent = $parent");

		if ($GLOBALS["lang_menus"] == 1)
		{
			$ss = "AND (objects.lang_id=".$GLOBALS["lang_id"]." OR menu.type = ".MN_CLIENT.")";
		}

		if ($period > 0)
		{
			$ss.=" AND menu.periodic=1 ";
		};


		$q = "SELECT objects.*,menu.*
			FROM objects LEFT JOIN menu ON menu.id = objects.oid
			WHERE (objects.class_id IN (".CL_PSEUDO.",".CL_PROMO.",".CL_BROTHER.")) AND objects.status != 0 AND objects.parent = $parent $ss
			ORDER BY $sortby $order";
		$this->db_query($q);

		$cut = $this->parse("CUT");
		$nocut = $this->parse("NORMAL");

		global $ext;
		$l = "";
		while ($row = $this->db_next())
		{
			if (!$this->can("view",$row["oid"]))
				continue;

			$r_id = ($row["class_id"] == CL_BROTHER ? $row["brother_of"] : $row["oid"]);

			if ($row["class_id"] == CL_PROMO)
			{
				$ic_url = $this->get_icon_url("promo_box","");
			}
			else
			{
				$ic_url = isset($row["icon_id"]) && $row["icon_id"] > 0 ? $baseurl."/icon.".$ext."?id=".$row["icon_id"] : "images/ftv2folderclosed.gif";
			}
			$this->vars(array(
				"is_cut"				=> ($cut_objects[$row["oid"]] ? $cut : $nocut),
				"name"					=> $row["name"],
				"menu_id"				=> $row["oid"], 
				"menu_order"		=> $row["jrk"], 
				"menu_active"		=> ($row["status"] == 2 ? "CHECKED" : ""),
				"menu_active2"	=> $row["status"],
				"prd1"					=> ($row["periodic"] == 1 ? "CHECKED" : ""),
				"prd2"					=> $row["periodic"],
				"copied"				=> $row["is_copied"] == 1 ? "CHECKED" : "",
				"modified"			=> $this->time2date($row["modified"],2),
				"modifiedby"		=> $row["modifiedby"],
				"delete"				=> $this->mk_orb("delete", array("parent" => $parent,"id" => $row["oid"],"period" => $period)),
				"r_menu_id"			=> $r_id,
				"properties"	=> $this->mk_orb("change", array("parent" => $parent,"id" => $r_id,"period" => $period)),
				"imgref" => $ic_url
			));

			$this->vars(array(
				"NFIRST" => $this->can("order",$row["oid"]) ? $this->parse("NFIRST") : "",
				"CAN_ACTIVE" => $this->can("active",$row["oid"]) ? $this->parse("CAN_ACTIVE") : "",
				"PERIODIC" => $this->can("periodic",$row["oid"]) ? $this->parse("PERIODIC") : "",
				"CAN_CHANGE" => $this->can("edit",$row["oid"]) ? $this->parse("CAN_CHANGE") : "",
				"CAN_DELETE" => $this->can("delete",$row["oid"]) ? $this->parse("CAN_DELETE") : "",
				"CAN_SEL_PERIOD" => $row["periodic"] == 1 ? $this->parse("CAN_SEL_PERIOD") : "",
				"CAN_ACL" => $this->can("admin",$row["oid"]) ? $this->parse("CAN_ACL") : ""
			));
			$l.=$this->parse("LINE");
		} // eow
		
		classload("languages");
		$la = new languages;
		$this->vars(array(
			"LINE" => $l,
			"reforb"	=> $this->mk_reforb("submit_order", array("parent" => $parent,"period" => $period,"from_menu" => 1)),
			"order1" => $sortby == "name" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
			"sortedimg1"	=> $sortby == "name" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
			"order2"=> $sortby == "jrk" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
			"sortedimg2"	=> $sortby == "jrk" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
			"order3"=> $sortby == "modifiedby" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
			"sortedimg3"	=> $sortby == "modifiedby" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
			"order4"=> $sortby == "modified" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
			"sortedimg4"	=> $sortby == "modified" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
			"order5"=> $sortby == "status" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
			"sortedimg5"	=> $sortby == "status" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
			"order6"=> $sortby == "periodic" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
			"sortedimg6"	=> $sortby == "periodic" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
			"yah"	=> $this->mk_path($parent,"",0,false),
			"cut" => $this->mk_orb("cut_menus", array("parent" => $parent)),
			"paste" => $this->mk_orb("paste_menus", array("parent" => $parent)),
			"addpromo" => $this->mk_orb("new", array("parent" => $parent),"promo"),
			"lang_name" => $la->get_langid()
		));

		$awt->stop("menuedit::gen_list_menus");
		return $this->parse();
	}

	// genereerib menyydest vaikese nimekirja templateditori jaoks
	function gen_picker($params)
	{
		global $awt;
		$awt->start("menuedit::gen_picker");
		extract($params);
		$q = "SELECT objects.*,menu.*
						FROM objects LEFT JOIN menu ON menu.id = objects.oid
						WHERE objects.class_id = ".CL_PSEUDO." AND objects.status != 0 AND objects.parent = $parent
						ORDER BY objects.jrk";
		$this->db_query($q);
		$this->read_template("popup_objects.tpl");
		$l = "";
		while($row = $this->db_next())
		{
			$this->vars(array(
				"icon"				=> $this->get_icon_url($row["class_id"],$row["name"]),
				"name"				=> $row["name"],
				"oid"					=> $row["oid"],
				"modifiedby"	=> $row["modifiedby"],
				"modified"		=> $this->time2date($row["modified"])
			));
			$l .= $this->parse("LINE");
		};
		$object = $this->get_object($parent);
		$this->vars(array("LINE" => $l,
					"tpl" => $tpl,
					"source" => $object["oid"],
					"objname" => $object["name"] . "(" . $object["oid"] . ")"));
		$awt->stop("menuedit::gen_picker");
		return $this->parse();
	}

	function gen_list_filled_forms($parent)
	{
		classload("form_output");
		$this->read_template("filled_forms.tpl");
		
		$fop = new form_output;
		$opar = array();
		$this->db_query("SELECT el_id,form_id FROM element2form WHERE el_id = ".$parent);
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$fid = $row["form_id"];
			// korjame k6ikide formide v2ljundi stiilid kokku $opar sisse
			$opar[$fid] = $fop->get_op_list(array("id" => $fid));

			$fname = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $fid", "name");
			$this->db_query("SELECT objects.* FROM form_".$fid."_entries LEFT JOIN objects ON objects.oid = form_".$fid."_entries.id WHERE objects.status != 0");
			while ($row = $this->db_next())
			{
				$this->vars(array("filler"		=> $row["createdby"], 
													"hits"			=> $row["hits"], 
													"form"			=> $fname,
													"modified"	=> $this->time2date($row["modified"], 2), "oid" => $row["oid"],
													"change"		=> $this->mk_orb("show", array("id" => $fid, "entry_id" => $row["oid"]), "form"),
													"form_id"		=> $fid));
				$l.=$this->parse("LINE");
			}
			$this->restore_handle();
		}
		reset($opar);
		while (list($fid, $ar) = each($opar))
		{
			$this->vars(array("form_id" => $fid));
			$fop = ""; $cnt=0;
			reset($ar);
			while (list($opid, $opname) = each($ar))
			{
				$this->vars(array("op_id" => $opid, "op_name" => $opname, "cnt" => $cnt));
				$fop.=$this->parse("FORM_OP");
				$cnt++;
			}
			$this->vars(array("FORM_OP" => $fop));
			$f.=$this->parse("FORM");
		}
		$this->vars(array("LINE" => $l, "FORM" => $f,
											"reforb"	=> $this->mk_reforb("change", array("id" => 0), "form")));
		return $this->parse();
	}

	function gen_list_objs($parent,$popup = 0)
	{
		if (is_array($parent))
		{
			extract($parent);
		}

		if (!$this->can("view", $parent))
		{
			return $this->acl_error("view",$parent);
		}

		global $awt;
		$awt->start("menuedit::gen_list_objs");

		$this->mk_path($parent, "");

		$mtype = $this->db_fetch_field("SELECT type FROM menu WHERE id = $parent", "type");
		
		$pobj = $this->get_object($parent);
		
		// the default document for the menu is in menu[last][$lang_id]
		// ma arvan, et tegelikult seda ei peaks last-is hoidma
		$lastar = unserialize($pobj["last"]);
		$default_doc = $lastar[$GLOBALS["lang_id"]];
		$period = $GLOBALS["period"];

		if ($popup == 1)
		{
			$this->read_template("popup_objects.tpl");
		}
		elseif ($period)
		{
			$this->read_template("pobjects.tpl");
		}
		else
		{
			$this->read_template("objects.tpl");
		}

		global $sortby;
		if ($sortby == "")
		{
			$sortby = "jrk";
		}

		global $order,$baseurl;
		if ($order == "")
		{
			$order = "ASC";
		}

		$types = array();
		global $class_defs;
		reset($class_defs);
		// listime ainult need objektid, mida igale poole lisada saab
		while (list($id,$ar) = each($class_defs))
		{
			if (isset($ar["can_add"]) && $ar["can_add"])	
			{
				$types[$id] = $ar["name"];
			}
		}
		$this->vars(array(
			"parent" => $parent,
			"types" => $this->option_list(0,$types),
			"period" => $period
		));
		
		$this->vars(array("ADD_CAT" => $this->can("add",$parent) ? $this->parse("ADD_CAT") : ""));
		if ($this->can("EDIT",$parent))
		{
			$this->vars(array("EDIT_LINKS" => $this->parse("EDIT_LINKS")));
		};

		global $class_defs;
		$fentries = array();	
		$fstrs = array();
		$ffound = false;
		// form entries among the objects . uuh, they get special treatment!
		// deal with all the form entries among the objecs shown
		$this->db_query("SELECT objects.oid,form_entries.form_id FROM objects LEFT JOIN form_entries ON form_entries.id = objects.oid WHERE objects.parent = $parent AND objects.status != 0 AND objects.class_id =".CL_FORM_ENTRY);
		while ($row = $this->db_next())
		{
			$fentries[$row["oid"]] = $row["form_id"];
			$fstrs[] = $row["oid"];
			$ffound = true;
		}

		$fshn = "";
		if ($ffound)
		{
			classload("form_output");
			$fop = new form_output;
			$opar = array();
			$forms = array();
			$fesstr = join(",",$fstrs);
			$ops = $fop->get_op_list();
			$q = "SELECT distinct(form_id) AS form_id FROM form_entries WHERE form_entries.id IN ($fesstr)";
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$forms[] = $row["form_id"];
				$cnt = 0;
				$o = "";
				if (is_array($ops[$row["form_id"]]))
				{
					reset($ops[$row["form_id"]]);
					while (list($opid,$opname) = each($ops[$row["form_id"]]))
					{
						$this->vars(array("form_id" => $row["form_id"], "cnt" => $cnt, "op_id" => $opid, "op_name" => $opname));
						$o.=$this->parse("FORM_OP");
						$cnt++;
					}
				}
				$this->vars(array("FORM_OP" => $o));
				$f.=$this->parse("FORM");
			}
			$this->vars(array("FORM" => $f));
			$fshn = $this->parse("FORMS_SHOWN");
		}

		// failide jura et pealkirjale klikkides tulex kohe lahti vastavate settingutega
		$filearr = array();
		$this->db_query("SELECT objects.*, files.newwindow FROM objects LEFT JOIN files ON files.id = objects.oid WHERE objects.status != 0 AND objects.parent = $parent");
		while ($row = $this->db_next())
		{
			$filearr[$row["oid"]] = $row;
		}

		if (!$period)
		{
			$this->listacl("(objects.class_id != ".CL_PSEUDO." AND objects.class_id != ".CL_PROMO." AND objects.class_id != ".CL_BROTHER.") AND objects.status != 0 AND objects.parent = $parent AND objects.period = 0 AND objects.lang_id = ".$GLOBALS["lang_id"]);
			$this->db_query("SELECT objects.* FROM objects WHERE (objects.class_id != ".CL_PSEUDO." AND objects.class_id != ".CL_PROMO." AND objects.class_id != ".CL_BROTHER.") AND objects.status != 0 AND objects.parent = $parent AND (objects.period = 0 OR objects.period IS NULL) AND objects.lang_id = ".$GLOBALS["lang_id"]." ORDER BY $sortby $order");
		}
		else
		{
			$this->listacl("objects.class_id = ".CL_PERIODIC_SECTION." AND objects.status != 0 AND objects.parent = $parent");
			$q  = "SELECT objects.*,documents.* FROM objects LEFT JOIN documents ON documents.docid = objects.brother_of WHERE (objects.class_id = ".CL_PERIODIC_SECTION." OR objects.class_id = ".CL_BROTHER_DOCUMENT.") AND objects.status != 0 AND objects.parent = $parent AND objects.period = $period ORDER BY $sortby $order";
			$this->db_query($q);
		}
		$total = 0; 
		$ffound = false;
		$def_found = false;
		global $cut_objects,$copied_objects;
		$cut = $this->parse("CUT");
		$copied = $this->parse("COPIED");
		$nocut = $this->parse("NORMAL");
		$l = "";
		while ($row = $this->db_next())
		{
			if (!$this->can("view", $row["oid"]))
			{
				continue;
			}

			$total++;
			$this->dequote(&$row["name"]);
			$inf = $class_defs[$row["class_id"]];

			$target = "";

			$change = $this->mk_orb("change", array("id" => $row["oid"], "parent" => $row["parent"]), $inf["file"]);
			if ($row["class_id"] == CL_FILE)
			{
				if ($filearr[$row["oid"]]["newwindow"] == 1)
				{
					$target = "target=\"_blank\"";
				}
				$change = $GLOBALS["baseurl"]."/files.".$GLOBALS["ext"]."/id=".$row["oid"]."/".urlencode($row["name"]);
			}
			$name = ($row["name"]) ? strip_tags($row["name"]) : " (no name)";
			$this->vars(array(
				"is_cut"			=> $cut_objects[$row["oid"]] ? $cut : ($copied_objects[$row["oid"]] ? $copied : $nocut),
				"target"			=> $target,
				"name"				=> $name,
				"class_id"		=> $row["class_id"],
				"oid"					=> $row["oid"], 
				"order"				=> $row["jrk"], 
				"form_id"			=> $row["class_id"] == CL_FORM_ENTRY ? $fentries[$row["oid"]] : 0,
				"active"			=> ($row["status"] == 2 ? "CHECKED" : ""),
				"active2"			=> $row["status"],
				"modified"		=> $this->time2date($row["modified"],2),
				"esilehel_uudis"    => ($row["esilehel_uudis"] > 0 ? "checked" : ""),
				"showlead"    => ($row["showlead"] > 0 ? "checked" : ""),
				"text_ok"			=> ($row["text_ok"] > 0 ? "checked" : ""),
				"pic_ok"			=> ($row["pic_ok"] > 0 ? "checked" : ""),
				"modifiedby"	=> $row["modifiedby"],
				"is_forum"    => ($row["is_forum"] > 0 ? "checked" : ""),
				"esilehel"    => ($row["esilehel"] > 0 ? "checked" : ""),
				"jrk1"				=> $row["jrk1"],
				"jrk2"				=> $row["jrk2"],
				"icon"				=> $this->get_icon_url($row["class_id"],$row["name"]),
				"type"				=> $GLOBALS["class_defs"][$row["class_id"]]["name"],
				"change"			=> $change,
				"checked"			=> checked($default_doc == $row["oid"]),
				"link"				=> $GLOBALS["baseurl"]."/".$row["oid"]));
			if (!$def_found)
			{
				$def_found = $default_doc == $row["oid"] ? true : false;
			}

			$can_change = "";
			$can_view = "";
			if ($this->can("edit", $row["oid"]))
			{
				$can_change = $this->parse("CAN_CHANGE");
			}
			else
			{
				$can_view = $this->parse("CAN_VIEW");
			}
			$this->vars(array(
				"NFIRST" => $this->can("order",$row["oid"]) ? $this->parse("NFIRST") : "",
				"CAN_ACTIVE" => $this->can("active",$row["oid"]) ? $this->parse("CAN_ACTIVE") : "",
				"FE"			=> $row["class_id"] == CL_FORM_ENTRY ? $this->parse("FE") : $this->parse("NFE"),
				"CAN_CHANGE" => $can_change,
				"CAN_VIEW" => $can_view
			));

			if ($row["class_id"] == CL_FORM_ENTRY)
			{
				$ffound = true;
			}
			$l.=$this->parse("LINE");
		}

		$paste = "";
		if (count($cut_objects) > 0 || count($copied_objects) > 0)
		{
			$paste = $this->parse("PASTE");
		}
		$odata = $this->get_object($parent);
		classload("languages");
		$la = new languages;
		$this->vars(array("LINE" => $l,
				"CUT"	=> "",
				"NORMAL"	=> "", 
				"PASTE"	=> $paste,
				"total"	=> verbalize_number($total),
				"objname" => $odata["name"],
				"parent" => $parent,
				"FORMS_SHOWN" => $fshn,
				"reforb" => $this->mk_reforb("submit_order_doc", array("parent" => $parent,"period" => $period)),
				"order1"			=> $sortby == "name" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg1"	=> $sortby == "name" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"order2"			=> $sortby == "jrk" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg2"	=> $sortby == "jrk" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"order3"			=> $sortby == "modifiedby" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg3"	=> $sortby == "modifiedby" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"order4"			=> $sortby == "modified" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg4"	=> $sortby == "modified" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"order5"			=> $sortby == "class_id" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg5"	=> $sortby == "class_id" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"order6"			=> $sortby == "status" ? $order == "ASC" ? "DESC" : "ASC" : "ASC",
				"sortedimg6"	=> $sortby == "status" ? $order == "ASC" ? "<img src='$baseurl/images/up.gif'>" : "<img src='$baseurl/images/down.gif'>" : "",
				"lang_name" => $la->get_langid(),
				"yah"	=> $this->mk_path($parent,"",0,false),
		));

		if (!$period && !$popup)
		{	
			$this->vars(array(
				"default" => $this->option_list($default_doc,$this->mk_docsel($parent)),
				"checked" => checked(!$def_found)
			));
		}

		$awt->stop("menuedit::gen_list_objs");
		return $this->parse();
	}

	function mk_docsel($parent = 0)
	{
		global $awt;
		$awt->start("menuedit::mk_docsel");
		// let the user pick a default document
		// select all documents that are non-periodic and not under this menu and active
		$this->extrarr = array();
		$this->db_query("SELECT objects.oid as oid,documents.title as title ,objects.parent as parent FROM objects LEFT JOIN documents ON documents.docid = objects.oid WHERE parent != $parent AND status = 2 AND periodic = 0 AND site_id = ".$GLOBALS["SITE_ID"]." AND class_id = ".CL_DOCUMENT." AND objects.lang_id = ".$GLOBALS["lang_id"]);
		while ($row = $this->db_next())
		{
			$this->extrarr[$row["parent"]][] = array("docid" => $row["oid"], "name" => substr($row["title"],0,15).".aw");
		}

		$ss = "";
		$this->menucache = array();
		if ($GLOBALS["lang_menus"] == 1)
		{
			$ss = "AND (objects.lang_id=".$GLOBALS["lang_id"]." OR menu.type= ".MN_CLIENT.")";
		}
		$this->db_query("SELECT objects.oid as oid,
				objects.parent as parent,
				objects.name as name,
				objects.last as last,
				objects.status as status,
				objects.jrk as jrk,
				objects.class_id as class_id,
				menu.type as mtype,
				menu.periodic as mperiodic,
				menu.is_copied as is_copied,
				menu.data as data,
				menu.clickable as clickable,
				menu.hide_noact as hide_noact,
				menu.target as target
				FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.") AND objects.status != 0  AND ((objects.site_id = ".$GLOBALS["SITE_ID"].") OR (objects.site_id IS NULL)) $ss
				GROUP BY objects.oid
				ORDER BY objects.parent, jrk");
		// tsykkel yle menyyelementide
		while ($row = $this->db_next()) 
		{
			$sets = unserialize(!isset($row["data"]) ? "" : $row["data"]);
			$row["name"] = substr($row["name"],0,15);
			$this->menucache[$row["parent"]][] = array("data" => $row);
			if (is_array($sets["section"]))
			{
				reset($sets["section"]);
				while(list(,$v) = each($sets["section"]))
				{
					// topime menyystruktuuri arraysse
					$row["name"] = substr($row["name"],0,12);
					$this->menucache[$v][] = array("data" => $row);
				}
			}
		}

		$this->docs = array("0" => "");
		// uh. leave out the forst level of objecs
		if (is_array($this->menucache[$GLOBALS["admin_rootmenu2"]]))
		{
			reset($this->menucache[$GLOBALS["admin_rootmenu2"]]);
			while (list(,$ar) = each($this->menucache[$GLOBALS["admin_rootmenu2"]]))
			{
				$this->mk_folders($ar["data"]["oid"],"");
			}
		}
		$awt->stop("menuedit::mk_docsel");
		return $this->docs;
	}

	function get_feature_icon_url($fid)
	{
		global $awt;
		$awt->start("menuedit::get_feature_icon_url");
		$awt->count("menuedit::get_feature_icon_url");
		if (!$this->feature_icons_loaded)
		{
			$c = new db_config;
			$this->pr_icons = unserialize($c->get_simple_config("program_icons"));
		}
		$i = $this->pr_icons[$fid]["url"];
		$awt->stop("menuedit::get_feature_icon_url");
		return $i == "" ? "/images/icon_aw.gif" : $i;
	}

	function get_icon_url($clid,$name)
	{
		classload("defs");
		return get_icon_url($clid,$name);
	}

	function command_redirect($arr)
	{
		extract($arr);
		if (!$oid && is_array($sel))
		{
			reset($sel);
			list($oid) = each($sel);
		}

		$obj = $this->get_object($oid);

		global $class_defs,$ext;
		$inf = $class_defs[$obj["class_id"]];
		if (!is_array($inf))
			$this->raise_error(ERR_MNEDIT_CMDREDIR,"menuedit->command_redirect($oid): Unknown class $row[class_id]",true);

		if ($subaction == "configure")
		{
			if ( ($obj["class_id"] == CL_FORUM) || ($obj["class_id"] == CL_CALENDAR) )
			{

			}
			else
			{
				$this->raise_error(ERR_MNEDIT_NOCONF,"menuedit->command_redirect($oid): this class has no configure method",true);
			};
		};

		$url = $this->mk_orb($subaction, array("id" => $oid,"parent" => $obj["parent"],"period" => $period), $inf["file"]);
		header("Location: $url");
		die();
	}

	function menuedit_newobj($arr)
	{
		extract($arr);

		if ($type == CL_BROTHER_DOCUMENT)
		{
			// special case, b8888888888
			header("Location: ".$this->mk_orb("add_bro", array("parent" => $parent),"document"));
			die();
		}

		global $class_defs,$ext;
		$inf = $class_defs[$type];
		if (!is_array($inf))
			$this->raise_error(ERR_MNEDIT_UCLASS,"menuedit->command_redirect($oid): Unknown class ".$row["class_id"],true);
		
		if (!$period)
		{
			$period = 0;
		}

		$url = $this->mk_orb("new", array("parent" => $parent, "period" => $period), $inf["file"]);
		header("Location: $url");
		die();		
	}

	function add($arr)
	{
		extract($arr);

		if (!$this->can("add",$parent))
			$this->raise_error(ERR_MNEDIT_ACL_NOADD,LC_MENUEDIT_NOT_ALLOW, true);

		// just add the damn thing and be don withit
		$id = $this->new_object(array(
			"parent" => $parent, 
			"name" => "", 
			"class_id" => CL_PSEUDO, 
			"comment" => "","status" => 1
		));
		$this->db_query("INSERT INTO menu (id,link,type,is_l3,left_pane,right_pane) VALUES($id,'$link',".MN_CONTENT.",0,1,1)");
		header("Location: ".$this->mk_my_orb("change", array("id" => $id, "parent" => $parent,"period" => $period)));
		die();

		global $ext;
		$this->mk_path($parent,LC_MENUEDIT_ADD);
		$this->read_template("nadd.tpl");
		//$par_info = $this->get_menu($parent);
		$par_info = $this->mar($parent);
		if ((($parent == 29) && $GLOBALS["SITE_ID"] < 100)) 
		#if ((($parent == 1) || ($parent == 29) && $GLOBALS["SITE_ID"] < 100)) 
		{
			$classlist = $this->option_list(1,array("69" => LC_MENUEDIT_CLIENT));
			// sektsioonid, mida saab teha kohe kliendi alla
		} 
		else
		if ($par_info["type"] == MN_CLIENT) 
		{
			$classlist = $this->option_list(1,array("70" => LC_MENUEDIT_SECTION,
																							"71" => LC_MENUEDIT_ADMINN_MENU));
		} 
		else
		if ($par_info["type"] == MN_ADMIN1) 
		{
			$classlist = $this->option_list(1,array("71" => LC_MENUEDIT_ADMINN_MENU,"72" => LC_MENUEDIT_DOCUMENT));
		} 
		else
		if ($par_info["type"] == MN_HOME_FOLDER || $par_info["type"] == MN_HOME_FOLDER_SUB) 
		{
			$classlist = $this->option_list(1,array("75" => LC_MENUEDIT_CATALOG));
		} 
		else 
		{
			$classlist = $this->option_list(1,array("70" => LC_MENUEDIT_SECTION));
		};
		$this->vars(array("parent"  => $parent,
											"name"    => "",
											"alias"   => "",
											"class_select" => $classlist,
											"comment" => "",
											"id"      => 0,
											"reforb" => $this->mk_reforb("submit", array("parent" => $parent))));
		return $this->parse();
	}

	function nsubmit(&$arr)
	{
		$this->quote(&$arr);
		extract($arr);

		$this->invalidate_menu_cache();

		// stripime aliasest tyhikud v2lja
		str_replace(" ","",$alias);
		// kui muudame olemasolevat menyyd, siis ........
		if ($id) 
		{
			if (!$this->can("edit",$id))
				$this->raise_error(ERR_MNEDIT_ACL_NOCHANGE,LC_MENUEDIT_NOT_ALLOW, true);

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

			// 2 updates, this is so wrong.
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
					"aip_filename" => $arr["aip_filename"]
				),
			));
			
			if ($arr["keywords"] || $arr["description"])
			{
				classload("file","php");
				$awf = new file();

				$old = $awf->get_special_file(array(
					"name" => "meta.tags",
				));

				$serializer = new php_serializer();
				$meta = $serializer->php_unserialize($old);

				$meta[$id] = array(
					"keywords" => $arr["keywords"],
					"description" => $arr["description"],
				);
				$ser_meta = $serializer->php_serialize($meta);

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
						$not_changed[$v] = $v;
					else
						$added[$v] = $v;
					$a[$v]=$v;
				}
			}
			$deleted = array();
			reset($sar);
			while (list($oid,) = each($sar))
			{
				if (!$a[$oid])
					$deleted[$oid] = $oid;
			}

			reset($deleted);
			while (list($oid,) = each($deleted))
			{
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
				$deact_stamp = mktime($deactivate_at["hour"],$deactivate_at["minute"],0,$deactivate_at["month"],
															$deactivate_at["day"],$deactivate_at["year"]);
				$autodeactivate = 1;
			};

			$charr = array("oid"      => $id,
										"name"     => $name,
										"status"   => $status,
										"autoactivate" => $autoactivate,
										"autodeactivate" => $autodeactivate,
										"activate_at" => $act_stamp,
										"deactivate_at" => $deact_stamp,
										"comment"  => $comment,
										"alias"    => $alias);
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
					$sar[$v] = $v;
			}
			$sss = serialize($sar);
			$par = array();
			if (is_array($pers))
			{
				reset($pers);
				while (list(,$v) = each($pers))
					$par[$v] = $v;
			}
			$pers = serialize($par);
			// pildi uploadimine
			global $img_act,$img_act_type;
			$tt2 = "";
			if ($img_act != "none" && $img_act != "")
			{
				classload("images");
				$t = new db_images;
				$im = $t->_upload(array("filename" => $img_act, "file_type" => $img_act_type, "oid" => $id));
				$img = $t->get_img_by_id($im["id"]);
				$this->set_object_metadata(array(
					"oid" => $id,
					"data" => array(
						"img_act_id" => $im["id"],
						"img_act_url" => $img["url"],
					),
				));
			}
			if ($img2 != "none" && $img2 != "")
			{
				classload("images");
				$t = new db_images;
				$im = $t->_upload(array("filename" => $img2, "file_type" => $img2_type, "oid" => $id));
				$img = $t->get_img_by_id($im["id"]);
				$this->set_object_metadata(array(
					"oid" => $id,
					"data" => array(
						"img2_id" => $im["id"],
						"img2_url" => $img["url"],
					),
				));
			}
			if ($img3 != "none" && $img3 != "")
			{
				classload("images");
				$t = new db_images;
				$im = $t->_upload(array("filename" => $img3, "file_type" => $img3_type, "oid" => $id));
				$img = $t->get_img_by_id($im["id"]);
				$this->set_object_metadata(array(
					"oid" => $id,
					"data" => array(
						"img3_id" => $im["id"],
						"img3_url" => $img["url"],
					),
				));
			}

			global $num_menu_images; 

			classload("images");
			$t = new db_images;

			$imgar = $meta["menu_images"];
			for ($i=0; $i < $num_menu_images; $i++)
			{
				if ($img_del[$i] == 1)
				{
					unset($imgar[$i]);
				}
				else
				{
					$imgar[$i]["ord"] = $img_ord[$i];
					$var = "img".$i;
					$var_t = "img".$i."_type";
					global $$var, $$var_t;
					if ($$var != "none" && $$var != "")
					{
						$im = $t->_upload(array("filename" => $$var, "file_type" => $$var_t, "oid" => $id));
						$imgar[$i]["id"] = $im["id"];
						$img = $t->get_img_by_id($im["id"]);
						$imgar[$i]["url"] = $img["url"];
					}
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

			global $lang_id;
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
		return $this->mk_orb("change", array("parent" => $arr["parent"],"id" => $id,"period" => $arr["period"]));
	}

	function submit_order($arr)
	{
		$this->invalidate_menu_cache();

		$obj = $this->get_object($arr["parent"]);
		$ar = unserialize($obj["last"]);
		if ($arr["default_doc"] == -1)
		{
			$ar[$GLOBALS["lang_id"]] = $arr["default_doc2"];
		}
		else
		{
			// menu's default document is kept in objects last, because menus don't really need it otherwise.
			$ar[$GLOBALS["lang_id"]] = $arr["default_doc"];
		}
		$this->db_query("UPDATE objects SET last = '".serialize($ar)."' WHERE oid = ".$arr["parent"]);


		// ord sisaldab vormist sisestatud jarjekorranumbreid
		// old_ord sisaldab "vanu" jarjekorranumbreid (s.t. neid, mis olid enne)
		$ord = $arr["ord"];
		$old_ord = $arr["old_ord"];
		if (is_array($ord)) 
		{
			while(list($oid,$value) = each($ord)) 
			{
				// paringu teeme ainult siis, kui jarjekorranumbrid erinevad
				if ($old_ord[$oid] != $value) 
				{
					$q = "UPDATE objects SET jrk = '$value' WHERE oid = '$oid'";
					$this->db_query($q);
				}; // if
			}; // while
		}; // is_array
	
		// act sisaldab vormis klikitud "aktiivsus" checkboxe	
		// old_act sisaldab "vanu" aktiivsuseindikaatoreid
		$act = $arr["act"];
		$old_act = $arr["old_act"];
		if (is_array($old_act)) 
		{
			while(list($oid,$value) = each($old_act)) 
			{
				$_act = ($act[$oid] == "on") ? 2 : 1;
				// if status changed
				if ($value != $_act) 
				{
					$this->upd_object(array("oid"    => $oid,
																	"status" => $_act));
				}; // if
			}; // while
		}; // if

		// prd sisaldab vormis klikitud "perioodiline" checkboxe
		// old_prd sisaldab "vanu" perioodilisusindikaatoreid
		$prd = $arr["prd"];
		$old_prd = $arr["old_prd"];
		if (is_array($old_prd)) 
		{
			while(list($oid,$value) = each($old_prd)) 
			{
				$_prd = ($prd[$oid] == "on") ? 1 : 0;
				// if periodic flag changed
				if ($value != $_prd) 
				{
					$q = "UPDATE menu
										SET periodic = '$_prd'
										WHERE id = '$oid'";
					$this->db_query($q);
					$q = "UPDATE objects
										SET periodic = '$_prd'
										WHERE oid = '$oid'";
					$this->db_query($q);
				};
			};
		};

		// clk sisaldab vormis klikitud "klikitav" checkboxe
		// old_clk sisaldab "vanu" klikitavusindikaatoreid
		$clk = $arr["clk"];
		$old_clk = $arr["old_clk"];
		if (is_array($old_clk)) 
		{
			while(list($oid,$value) = each($old_clk)) 
			{
				$_clk = ($clk[$oid] == "on") ? 1 : 0;
				if ($value != $_clk)
				{
					$this->db_query("UPDATE menu SET clickable = '$_clk' WHERE id = '$oid'");
				}
			};
		};

		// new sisaldab vormis klikitud "uues aknas" checkboxe
		// old_new sisaldab "vanu" uueaknaindikaatoreid
		$new = $arr["new"];
		$old_new = $arr["old_new"];
		if (is_array($old_new)) 
		{
			while(list($oid,$value) = each($old_new)) 
			{
				$_new = ($new[$oid] == "on") ? 1 : 0;
				if ($value != $_new)
				{
					$this->db_query("UPDATE menu SET target = '$_new' WHERE id = '$oid'");
				}
			};
		};

		// mkd sisaldab vormis klikitud "mitteaktiivne kui dokusid pole" checkboxe
		// old_mkd sisaldab "vanu" mitteaktiivne kui dokusid poleindikaatoreid
		$mkd = $arr["mkd"];
		$old_mkd = $arr["old_mkd"];
		if (is_array($old_mkd)) 
		{
			while(list($oid,$value) = each($old_mkd)) 
			{
				$_mkd = ($mkd[$oid] == "on") ? 1 : 0;
				if ($value != $_mkd)
				{
					$this->db_query("UPDATE menu SET hide_noact = '$_mkd' WHERE id = '$oid'");
				}
			};
		};

		// cp arrays on need elemendid, mida kopeerida soovitakse 
		$cp = $arr["cp"];
		if (is_array($cp)) 
		{
			// tyhistame koik senised kopeerimised 
			$this->db_query("UPDATE menu SET is_copied = 0");

			// kopeerime margitud elemendid
			while(list($cpk,$cpv) = each($cp)) 
			{
				$q = "UPDATE menu
						SET is_copied = 1
						WHERE id = '$cpk'";
				$this->db_query($q);
			};
		};

		$this->flush_cache();
		
		return $this->mk_orb("menu_list", array("parent" => $arr["parent"],"period" => $arr["period"]));
	}

	function submit_order2($arr)
	{
		$jrk = $arr["jrk"];
		if (!is_array($jrk))
		{
			$jrk = $arr["ord"];
		}
		$act = $arr["act"];
		if (!is_array($act))
			$act = $arr["active"];
		$is_forum = $arr["is_forum"];
		$showlead = $arr["showlead"];
		$text_ok = $arr["text_ok"];
		$pic_ok = $arr["pic_ok"];
		$esilehel = $arr["esilehel"];
		$esilehel_uudis = $arr["esilehel_uudis"];
		$jrk1 = $arr["jrk1"];
		$jrk2 = $arr["jrk2"];

		// saveme default dokumendi.
		if ($arr["default"] == -1)
		{
			$def_doc = $arr["default2"];
		}
		else
		{
			$def_doc = $arr["default"];
		}
		$o = $this->get_object($arr["parent"]);
		$od = unserialize($o["last"]);
		$od[$GLOBALS["lang_id"]] = $def_doc;
		$os = serialize($od);
		$this->upd_object(array("oid" => $arr["parent"], "last" => $os));

		$period = $GLOBALS["period"];

		if (!is_array($jrk))
		{
			return;
		}
		while(list($k,$v) = each($jrk)) 
		{
			if ($act[$k] == 1) 
			{
				$part = "status = 2";
			} 
			else 
			{
				$part = "status = 1";
			};
			$q = "UPDATE objects SET jrk = '$v',$part WHERE oid = '$k'";
			$this->db_query($q);

			if ($period)
			{
				// these settings are in the template only if we are showing periodic documents, so ignore them otherwise
				$dparts = array();
				if ($is_forum[$k] == 1) 
				{
					$dparts[] = " is_forum = 1 ";
				} 
				else 
				{
					$dparts[] = " is_forum = 0 ";
				};
				if ($showlead[$k] == 1) 
				{
					$dparts[] = " showlead = 1 ";
				} 
				else 
				{
					$dparts[] = " showlead = 0 ";
				};
				if ($esilehel[$k] == 1) 
				{
					$dparts[] = " esilehel = 1 ";
				} 
				else 
				{
					$dparts[] = " esilehel = 0 ";
				};
				if ($text_ok[$k] == 1) 
				{
					$dparts[] = " text_ok = 1 ";
				} 
				else 
				{
					$dparts[] = " text_ok = 0 ";
				};
				if ($pic_ok[$k] == 1) 
				{
					$dparts[] = " pic_ok = 1 ";
				} 
				else 
				{
					$dparts[] = " pic_ok = 0 ";
				};
				if ($esilehel_uudis[$k] == 1) 
				{
					$dparts[] = " esilehel_uudis = 1 ";
				} 
				else 
				{
					$dparts[] = " esilehel_uudis = 0 ";
				};
				$dparts[] = " jrk1 = '".$jrk1[$k]."' ";
				$dparts[] = " jrk2 = '".$jrk2[$k]."' ";
				if (sizeof($dparts) > 0) 
				{
					$q = "UPDATE documents SET " . join(",",$dparts) . "WHERE docid = '$k'";
					$this->db_query($q);
				};
			}
		};
		return $this->mk_orb("obj_list", array("parent" => $arr["parent"],"period" => $arr["period"]));
	}

	function ndelete($arr)
	{
		extract($arr);
		if (!$this->can("delete",$id))
			$this->raise_error(ERR_MNEDIT_ACL_NODEL,LC_MENUEDIT_NOT_ALLOW, true);

		$this->rd($id);
		$name = $this->db_fetch_field("SELECT name FROM objects WHERE oid = $id","name");
		$this->_log("menuedit",sprintf(LC_MENUEDIT_ERASED_MENU,$name));

		$this->invalidate_menu_cache();

		header("Location: ".$this->mk_orb("menu_list", array("parent" => $arr["parent"])));
	}

	function change($arr)
	{
		extract($arr);
		global $period;
		$this->mk_path($id, "Muuda");
		if (!$this->can("edit",$id))
			$this->raise_error(ERR_MNEDIT_ACL_NOCHANGE,LC_MENUEDIT_NOT_ALLOW, true);

		global $basedir,$baseurl;
		global $ext;
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
			$this->raise_error(ERR_MNEDIT_NOMENU,"menuedit->gen_change_html($id): No such menu!", true);

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
		$edit_templates = array();
		while($tpl = $this->db_fetch_row()) {
			$edit_templates[$tpl["id"]] = $tpl["name"];
		};
		// kysime infot lyhikeste templatede kohta
		$q = "SELECT * FROM template WHERE type = 1 ORDER BY id";
		$this->db_query($q);
		$short_templates = array();
		while($tpl = $this->db_fetch_row()) {
			$short_templates[$tpl["id"]] = $tpl["name"];
		};
		// kysime infot pikkade templatede kohta
		$q = "SELECT * FROM template WHERE type = 2 ORDER BY id";
		$this->db_query($q);
		$long_templates = array();
		while($tpl = $this->db_fetch_row()) {
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
			$il = $this->parse("IS_LAST");

		// kui see on adminni menyy, siis kuvame kasutajale featuuride listi, 
		// mille hulgast ta siis valida saab, et mis selle menyy alt avaneb. 
		if ($row["type"] == MN_ADMIN1)
		{
			$this->vars(array("admin_feature" => $this->picker($row["admin_feature"],$this->get_feature_sel())));
			$af = $this->parse("ADMIN_FEATURE");
		}
		classload("images");
		$t = new db_images;
		global $num_menu_images;
		$imgar = $meta["menu_images"];

		for ($i=0; $i < $num_menu_images; $i++)
		{
			$image = "";
			if ($imgar[$i]["id"])
			{
				$img = $t->get_img_by_id($imgar[$i]["id"]);
				$image = "<img src='".$img["url"]."'>";
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

		$icon = $row["icon_id"] ? "<img src=\"".$baseurl."/icon.".$ext."?id=".$row["icon_id"]."\">" : ($row["admin_feature"] ? "<img src=\"".$this->get_feature_icon_url($row["admin_feature"])."\">" : "");

		classload("periods");
		$dbp = new db_periods($GLOBALS["per_oid"]);

		$oblist = $ob->get_list();

		// seealso asi on nyt nii. et esiteks on metadata[seealso_refs] - seal on kirjas, mis menyyde all see menyy seealso item on
		// ja siis menu.seealso on nagu enne serializetud array menyydest mis selle menyy all seealso on et n2itamisel kiirelt teada saax
		$sa = $meta["seealso_refs"];
		$rsar = $sa[$GLOBALS["lang_id"]];

		classload("form_base");
		$fb = new form_base;
		$flist = $fb->get_flist(array("type" => FTYPE_ENTRY));

		$img2 = $meta["img_act_url"] != "" ? "<img src='".$meta["img_act_url"]."'>" : "";
		global $template_sets;
		$template_sets = array_merge(array("" => "kasuta parenti valikut"),$template_sets);

		$types = array(
			"69" => LC_MENUEDIT_CLIENT,
			"70" => LC_MENUEDIT_SECTION,
			"71" => LC_MENUEDIT_ADMINN_MENU,
			"72" => LC_MENUEDIT_DOCUMENT,
			"75" => LC_MENUEDIT_CATALOG
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
			"name"				=> htmlentities($row["name"]), 
			"number"			=> $row["number"],
			"comment"			=> $row["comment"], 
			"links"				=> checked($row["links"]), 
			"users_only"  => ($meta["users_only"] == 1) ? "checked" : "",
			"show_lead" => ($meta["show_lead"] == 1) ? "checked" : "",
			"id"					=> $id,
			"active"	    => ($row["status"] == 2) ? "checked" : "",
			"clickable"	    => ($row["clickable"] == 1) ? "checked" : "",
			"hide_noact"   => ($row["hide_noact"] == 1) ? "checked" : "",
			"alias"				=> $row["alias"],
			"created"			=> $this->time2date($row["created"],2),
			"target"		=> ($row["target"]) ? "checked" : "",
			"autoactivate" => ($row["autoactivate"]) ? "checked" : "",
			"autodeactivate" => ($row["autodeactivate"]) ? "checked" : "",
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
			"sep_checked"	=> ($row["type"] == 4 ? "CHECKED" : ""),
			"mid"	=> ($row["mid"] == 1 ? "CHECKED" : ""),
			"doc_checked"	=> ($row["type"] == 6 ? "CHECKED" : ""),
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
			"aip_filename" => $meta["aip_filename"]
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

	function get_feature_sel()
	{
		// @desc: tagastab array adminni featuuridest, mida sobib ette s88ta aw_template->picker funxioonile
		global $programs;
		$ret = array();
		reset($programs);
		while (list($id,$v) = each($programs))
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
	// !override the mk_path on core.aw , cause in menuedit mk_path is used in the upper frame, not in the objects frame
	// !and thus must go to a different place when clicked.
	function mk_path($oid,$text = "",$period = 0,$set = true)
	{
		global $awt;
		$awt->start("menuedit::mk_path");
		global $ext;

		$ch = $this->get_object_chain($oid,false,$GLOBALS["admin_rootmenu2"]);
		$path = "";
		reset($ch);
		while (list(,$row) = each($ch))
		{
			$path="<a target='list' href='menuedit_right.$ext?parent=".$row["oid"]."&period=".$period."'>".strip_tags($row["name"])."</a> / ".$path;
		}

		if ($set)
		{
			$GLOBALS["site_title"] = $path.$text;
		}
		$awt->stop("menuedit::mk_path");
		return $path;
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
			$c->set_program_icon($af,$icon_id);
		}
		$this->db_query("UPDATE menu SET icon_id = $icon_id WHERE id = $id");
	}

	////
	// !generates link collection content instead of document for menu
	function do_link_collection($parent)
	{
		global $awt;
		$awt->start("menuedit::do_link_collection");
		$this->read_template("link_collection.tpl");

		global $ext, $baseurl;
		
		// ehitame asukoha linkit
		$p = $parent;
		$yah = array();
		while ($p)
		{
			$this->_push($this->mar[$p],"yaha");
			if ($this->mar[$p]["links"])
			{
				$p = 0;
			}	
			$p = $this->mar[$p]["parent"];
		}

		while ($v = $this->_pop("yaha"))
		{
			$url = $baseurl."/".$v["oid"];
			$this->vars(array("url" => $url, "name" => $v["name"], "oid" => $v["oid"]));
			if ($y == "")
			{
				$y =$this->parse("YAH_BEGIN");
			}
			else
			{
				$y.=$this->parse("YAH");
			}
		}
		$this->vars(array("YAH" => $y, "YAH_BEGIN" => ""));

		if (is_array($this->mpr[$parent]))
		{
			$cnt = - (LINKC_MENUSPERLINE-1);
			reset($this->mpr[$parent]);
			while (list(,$ar) = each($this->mpr[$parent]))
			{
				$url = $baseurl."/".$ar["oid"];
				$this->vars(array("url" => $url, "name" => $ar["name"], "oid"=>$ar["oid"]));
				$c.= $this->parse("SECTIONS_COL");

				if (($cnt % LINKC_MENUSPERLINE) == 0)
				{
					$this->vars(array("SECTIONS_COL" => $c));
					$l.=$this->parse("SECTIONS_LINE");
					$c = "";
				}
				$cnt++;
			}
			$this->vars(array("SECTIONS_COL" => $c));
			$l.=$this->parse("SECTIONS_LINE");
			$this->vars(array("SECTIONS_LINE" => $l));
		}

		$c = ""; $l = "";
		$cnt = - (LINKC_LINKSPERLINE-1);
		$this->db_query("SELECT extlinks.*,objects.comment AS comment FROM objects LEFT JOIN extlinks ON extlinks.id = objects.oid WHERE objects.class_id = ".CL_EXTLINK." AND objects.status = 2 AND objects.parent = $parent");
		while ($row = $this->db_next())
		{
			$target = "";
			if ($row["newwindow"])
			{
				$target = "target='_new'";
			}
			$this->vars(array("url" => $row["url"], "name" => $row["name"], "text" => $row["comment"], "target" => $target));
			$c.=$this->parse("LINK_COL");

			if (($cnt % LINKC_LINKSPERLINE) == 0)
			{
				$this->vars(array("LINK_COL" => $c));
				$l.=$this->parse("LINK_LINE");
				$c = "";
			}
			$cnt++;
		}
		$this->vars(array("LINK_COL" => $c));
		$l.=$this->parse("LINK_LINE");
		$this->vars(array("LINK_LINE" => $l));
		$con =  $this->parse();
		$awt->stop("menuedit::do_link_collection");
		return $con;
	}

	function req_draw_menu($parent,$name,&$path,$ignore_path)
	{
		global $baseurl, $ext, $menu_check_acl;
		$this->sub_merge = 1;
		$this->level++;

		$cnt = 0;

		if (!isset($this->mpr[$parent]) || !is_array($this->mpr[$parent]))
		{
			$this->level--;
			return 0;
		}
		
		global $awt;
		$awt->start("menuedit::req_draw_menu");
		$awt->count("menuedit::req_draw_menu");
	
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

		$in_path = in_array($this->mar[$parent]["oid"],$path);
		$parent_tpl = $this->is_parent_tpl($mn2, $mn);
		if (!(($in_path||$this->level == 1)||($parent_tpl&&$in_path)||$ignore_path))
		{
			// don't show unless the menu is selected (in the path)
			// or the next level subtemplates are nested in this one
			// which signifies that we sould show them anyway
			// ignore all these if the meny is a 1st level menu 
			$this->level--;
			$awt->stop("menuedit::req_draw_menu");
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
		$total = sizeof($this->mpr[$parent]) - 1;

		while (list(,$row) = each($this->mpr[$parent]))
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
			/*
			$meta = $this->get_object_metadata(array(
					"metadata" => $row["metadata"],
			));
			*/
			// it's already uncompressed, use it
			$meta = $row["meta"];

			// see on siis nädala parema paani leadide näitamine
			// nõme häkk. FIX ME.
			if ($meta["show_lead"])
			{
				$activeperiod = $GLOBALS["act_per_id"];
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
			if ($row["mtype"] != MN_CONTENT && $row["mtype"] != MN_CLIENT && $row["mtype"] != MN_HOME_FOLDER_SUB)
			{
				continue;
			}

			if ($row["hide_noact"] || $GLOBALS["all_menus_makdp"] == true)
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

			if (in_array($row["oid"], $path))
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
				if (!in_array($row["parent"],$path))
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

			if ($row["link"] != "")
			{
				$link = $row["link"];
			}
			else
			{
				$link = $baseurl."/";
				if (defined("LONG_SECTION_URL"))
				{
					if ($row["alias"] != "")
					{
						$link .= $row["alias"];
					}
					else
					{
						$link .= "?section=".$row["oid"];
					}
				}
				else
				{
					$link .= ($row["alias"] != "") ? $row["alias"] : $row["oid"];
				};
			}

			$target = ($row["target"] == 1) ? sprintf("target='%s'","_new") : "";

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
				$imgurl2 = $imgurl;
			}
			else
			{
				$imgurl = $row["img_url"];
			}

			if ($imgurl != "")
			{
				$imgurl = preg_replace("/^http:\/\/.*\//","/",$imgurl);
				$imgurl = sprintf("<img src='%s' border='0'>",$imgurl);
			}
			else
			{
				$imgurl = "";
			};

			global $num_menu_images;
			$has_image = false;
			$imgar = $meta["menu_images"];
			for ($_i=0; $_i < $num_menu_images; $_i++)
			{
				if ($imgar[$_i]["url"] != "")
				{
					$imgurl = preg_replace("/^http:\/\/.*\//","/",$imgar[$_i]["url"]);
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
					$_hi =  $this->parse($mn.$ap.".HAS_IMAGE");
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
				if ($this->is_template("HAS_SUBITEMS_".$name))
				{
					$this->parse("HAS_SUBITEMS_".$name);
				}
				$hsl = "";
				if ($this->is_template("HAS_SUBITEMS_".$name."_L".$this->level))
				{
					$hsl = $this->parse("HAS_SUBITEMS_".$name."_L".$this->level);
				}
				if (in_array($row["oid"],$path))	// this menu is selected
				{
					$_tmp = "";
					if ($this->is_template("HAS_SUBITEMS_".$name."_L".$this->level."_SEL"))
					{
						$_tmp = $this->parse("HAS_SUBITEMS_".$name."_L".$this->level."_SEL");
					}
					$this->vars(array(
							"HAS_SUBITEMS_".$name."_L".$this->level."_SEL" => $_tmp
					));
					if ($this->is_template("HAS_SUBITEMS_".$name."_L".$this->level."_SEL_MID"))
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
							$hslm = $this->parse("HAS_SUBITEMS_".$name."_L".$this->level."_SEL_MID");
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
				if ($this->is_template("NO_SUBITEMS_".$name))
				{
					$hs = $this->parse("NO_SUBITEMS_".$name);
				}
				$hsl = "";
				if ($this->is_template("NO_SUBITEMS_".$name."_L".$this->level))
				{
					$hsl = $this->parse("NO_SUBITEMS_".$name."_L".$this->level);
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

			$noshowu = $GLOBALS["uid"] == "" && $meta["users_only"] && $GLOBALS["no_show_users_only"] == true;
			// v6i menyy nimi on tyhi, v6i menyyle on 8eldud et users only ja kasutraja pole sisse loginud const.aw sees 
			// on defineeritud $no_show_users_only
			if ($selonly && $row["name"] != "" && !$noshowu)
			{
				if ($this->is_template($mn.$ap))
				{
					if ($is_mid)
					{
						$l_mid.=$this->parse($mn.$ap);
					}
					else
					{
						$l.=$this->parse($mn.$ap);
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
			// if the next level subtemplate is nested in this levels subtemplate, then we must clear the variable in tehe
			// template parser for the next level 
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
		$awt->stop("menuedit::req_draw_menu");
		return $cnt;
	}

	////
	// !draws MENU_$name_SEEALSO_ITEM 's for the menu given in $row
	function do_seealso_items($row,$name)
	{
		global $ext,$baseurl,$lang_id,$awt;
		$awt->start("menuedit::do_seealso_items");
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

				/*
				$meta = $this->get_object_metadata(array(
					"metadata" => $samenu["metadata"],
				));
				*/
				// use uncompressed version
				$meta = $samenu["meta"];

				if (!($meta["users_only"] == 1 && $GLOBALS["uid"] == ""))
				{
					$this->vars(array(
						"target" => $samenu["target"] ? "target=\"blank\"" : "",
						"link" => $link,
						"text" => str_replace("&nbsp;","",strip_tags($samenu["name"]))
					));
					$this->parse("MENU_".$name."_SEEALSO_ITEM");
				}
			}
		}
		$awt->stop("menuedit::do_seealso_items");
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

		$this->invalidate_menu_cache();

		header("Location: ".$GLOBALS["baseurl"]."/automatweb/".$this->mk_orb("menu_list", array("parent" => $parent)));
		return $this->mk_orb("menu_list", array("parent" => $parent));
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

			// tegime vanema menyy 2ra, teeme lapsed ka.
			$this->req_import_menus($db["oid"],$menus,$id);
		}
	}

	////
	// !cuts the selected objects
	function cut($arr)
	{
		extract($arr);

		$GLOBALS["cut_objects"] = array();
		if ($oid)
		{
			$GLOBALS["cut_objects"][$oid] = $oid;
		}

		if (is_array($sel))
		{
			reset($sel);
			while (list($oid,) = each($sel))
			{
				$GLOBALS["cut_objects"][$oid] = $oid;
			}
		}

		if ($from_menu)
		{
			return $this->mk_orb("menu_list", array("parent" => $parent, "period" => $period));
		}
		else
		{
			return $this->mk_orb("obj_list", array("parent" => $parent, "period" => $period));
		}
	}

	////
	// !copies the selected objects
	function copy($arr)
	{
		extract($arr);

		$GLOBALS["copied_objects"] = array();

		if ($oid)
		{
			$r = $this->serialize(array("oid" => $oid));
			if ($r != false)
			{
				$GLOBALS["copied_objects"][$oid] = $r;
			}
		}

		if (is_array($sel))
		{
			reset($sel);
			while (list($oid,) = each($sel))
			{
				$r = $this->serialize(array("oid" => $oid));
				if ($r != false)
				{
					$GLOBALS["copied_objects"][$oid] = $r;
				}
			}
		}
		
		return $this->mk_orb("obj_list", array("parent" => $parent, "period" => $period));
	}

	////
	// !pastes the cut objects 
	function paste($arr)
	{
		extract($arr);

		global $cut_objects;
		if (is_array($cut_objects))
		{
			reset($cut_objects);
			while (list(,$oid) = each($cut_objects))
			{
				$this->upd_object(array("oid" => $oid, "parent" => $parent,"period" => $period));
			}
		}
		$GLOBALS["cut_objects"] = array();

		global $copied_objects;
		if (is_array($copied_objects))
		{
			reset($copied_objects);
			while (list($oid,$str) = each($copied_objects))
			{
				$this->unserialize(array("str" => $str, "parent" => $parent, "period" => $period));
			}
		}

		$this->invalidate_menu_cache();

		$GLOBALS["copied_objects"] = array();
		if ($from_menu)
		{
			return $this->mk_orb("menu_list", array("parent" => $parent, "period" => $period));
		}
		else
		{
			return $this->mk_orb("obj_list", array("parent" => $parent, "period" => $period));
		}
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
	}

	function make_menu_caches($where = "objects.status = 2")
	{
		global $awt,$lang_id,$SITE_ID;
		$awt->start("menuedit::make_menu_caches");
		$cache = new cache();
		$ms = $cache->file_get("menuedit::menu_cache::lang::".$lang_id."::site_id::".$SITE_ID);
		if (!$ms)
		{
			// make one big array for the whole menu
			$this->mar = array();
			// see laheb ja loeb kokku, mitu last mingil sektsioonil on
			// salvestatakse $this->subs array sisse, key on objekti oidiman

			$this->db_prep_listall($where);
			$this->db_listall_lite($where);

			while ($row = $this->db_next())
			{
				// some places need raw metadata, others benefit from reading
				// the already uncompressed metainfo from the cache
				$row["meta"] = aw_unserialize($row["metadata"]);
				$row["mtype"] = $row["type"];
				// Maybe this means that some people come with knives after me sometimes,
				// but I'm pretty sure that we do not need to save unpacked metadata
				// in the cache, since it's available in $row[meta] anyway
				unset($row["metadata"]);
				$this->mpr[$row["parent"]][] = $row;
				$this->mar[$row["oid"]] = $row;
			}

			// write the data to the cache
			$cached = array();
			$cached["mar"] = $this->mar;
			$cached["mpr"] = $this->mpr;
			$cached["subs"] = $this->subs;

			$cache = new cache();
			$c_d = aw_serialize($cached,SERIALIZE_PHP);
			$cache->file_set("menuedit::menu_cache::lang::".$lang_id."::site_id::".$SITE_ID,$c_d);
		}
		else
		{
			// unserialize the cache
			$cached = aw_unserialize($ms,1);
			$this->mar = $cached["mar"];
			$this->mpr = $cached["mpr"];
			$this->subs = $cached["subs"];
		}
		$awt->stop("menuedit::make_menu_caches");
	}

	function is_link_collection($section)
	{
		global $awt;
		$awt->start("menuedit::is_link_collection");
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
		$awt->stop("menuedit::is_link_collection");
		return $links;
	}

	function is_shop($section)
	{
		global $awt;
		$awt->start("menuedit::is_shop");
		$p = $section; 
		$links = false;
		$cnt = 0;
		while ($p && ($cnt < 20))
		{
			$cnt++;
			if (!is_array($this->mar[$p]))
			{
				//$this->db_query("SELECT objects.*,menu.* FROM objects LEFT JOIN menu ON menu.id = objects.oid WHERE oid = $p");
				$this->mar[$p] = $this->get_menu($p);
			}

			if (isset($this->mar[$p]["is_shop"]) && $this->mar[$p]["is_shop"] == 1)
			{
				$sh_id = $this->mar[$p]["shop_id"];
				$p = 0;
				$links = true;
			}	
			isset($this->mar[$p]["parent"]) ? $p = $this->mar[$p]["parent"] : $p = 0;
		}
		$awt->stop("menuedit::is_shop");
		if (!$links)
		{
			return false;
		}
		else
		{
			$this->right_pane = false;
			return $sh_id;
		}
	}

	function show_periodic_documents($section,$obj)
	{
		global $awt;
		$awt->start("menuedit::show_periodic_documents");
		$d = new document();
		$cont = "";
		// if $section is a periodic document then emulate the current period for it and show the document right away
		if ($obj["class_id"] == CL_PERIODIC_SECTION)
		{
			/*
			$template = $this->_get_template_filename($this->properties["tpl_view"]);
			if (not($template))
			{
				$template = $this->properties["ftpl_view"];
			};
			*/
			$template = $this->get_long_template($section);
			$activeperiod = $obj["period"];
			$cont = $d->gen_preview(array(
						"docid" => $section,
						"boldlead" => 1,
						"tpl" => $template));
			$this->vars(array("docid" => $section));
			$PRINTANDSEND = $this->parse("PRINTANDSEND");
		}
		else
		{
			$activeperiod = $GLOBALS["act_per_id"];
			$d->set_period($activeperiod);
			$d->list_docs($section, $activeperiod,2);
			if ($d->num_rows > 1)		// the database driver sets this
			{
				/*
				$template = $this->_get_template_filename($this->properties["tpl_lead"]);
				if (not($template))
				{
					$template = $this->properties["ftpl_lead"];
				};
				*/
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
						"doc"	=> $row));
					$d->restore_handle();
				}; // while
			} // if
			// on 1 doku
			else 
			{
				$row = $d->db_next();
				/*
				$template = $this->_get_template_filename($this->properties["tpl_view"]);
				if (not($template))
				{
					$template = $this->properties["ftpl_view"];
				};
				*/
				$template = $this->get_long_template($section);
				$cont = $d->gen_preview(array(
							"docid" => $row["docid"],
							"boldlead" => 1,
							"tpl" => $template));
				$this->vars(array("docid" => $row["docid"]));
				$PRINTANDSEND = $this->parse("PRINTANDSEND");
			}
		}
		$awt->stop("menuedit::show_periodic_documents");
		return $cont;
	}

	function show_documents($section,$docid,$template = "")
	{
		global $awt;
		global $DEBUG;
		$awt->start("menuedit::show_documents");
		classload("document");
		$d = new document();
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

		/*
		$template = $this->_get_template_filename($this->properties["tpl_view"]);
		if (not($template))
		{
			$template = $this->properties["ftpl_view"];
		};
		*/
		$template = $this->get_long_template($section);
		
		if (is_array($docid)) 
		{
			/*
			$template = $this->_get_template_filename($this->properties["tpl_lead"]);
			if (not($template))
			{
				$template = $this->properties["ftpl_lead"];
			};
			*/
			$template = $this->get_lead_template($section);
			$template = $template == "" ? "plain.tpl" : $template;
			$template2 = file_exists($GLOBALS["tpldir"]."/automatweb/documents/".$template."2") ? $template."2" : $template;
			$ct = ""; 
			$dk=1;
			// I hate this. docid on dokumendi id,
			// ja seda ei peaks arrayna kasutama
			reset($docid);
			while (list(,$did) = each($docid)) 
			{
				$ct.=$d->gen_preview(array(
					"docid" => $did,
					"tpl" => ($dk & 1 ? $template : $template2),
					"leadonly" => 1,
					"section" => $section,
					"strip_img" => $strip_img,
					"tpls" => $tpls,
					"no_strip_lead" => $GLOBALS["no_strip_lead"]
				));
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
				$ct = $d->gen_preview(array(
					"docid" => $docid,
					"section" => $section,
					"no_strip_lead" => $GLOBALS["no_strip_lead"],
					"notitleimg" => 0,
					"tpl" => $template,
					"boldlead" => $GLOBALS["boldlead"]
				));
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
				if (is_array($d->blocks))
				{
					$this->blocks = $this->blocks + $d->blocks;
				};
			}
		}
		$awt->stop("menuedit::show_documents");
		return $ct;
	}

	function get_path($section,$obj)
	{
		global $awt;
		$awt->start("menuedit::get_path");
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
		}
		// now the path is in the correct order on the "root" stack

		for ($i=0; $i < $cnt; $i++) 
		{
			$path[$i+1] = $this->_pop();
		};
		// and now in the $path array
		$awt->stop("menuedit::get_path");
		return $path;
	}

	 function make_yah($path)
	{
		global $awt;
		$awt->start("menuedit::make_yah");
		// now build "you are here" links from the path
		$ya = "";  
		$show = false;
		$cnt = count($path);
		global $DEBUG;
		if ($DEBUG)
		{
			print "<pre>";
			print_r($path);
			print "</pre>";
		};
		for ($i=0; $i < $cnt; $i++)	
		{
			if ($show)
			{
				$link = "/".$this->mar[$path[$i+1]]["oid"];
				if ($this->mar[$path[$i+1]]["link"] != "")
				{
					$link = $this->mar[$path[$i+1]]["link"];
				}

				$this->vars(array(
					"link" => $link,
					"text" => str_replace("&nbsp;","",strip_tags($this->mar[$path[$i+1]]["name"])), 
					"ysection" => $this->mar[$path[$i+1]]["oid"]
				));

				if ($this->mar[$path[$i+1]]["clickable"] == 1)
				{
					$ya.=$this->parse("YAH_LINK");
				}
			}
			// don't show things that are before $frontpage
			if (isset($path[$i]) && isset($this->mar[$path[$i]]) && $this->mar[$path[$i]]["oid"] == $GLOBALS["rootmenu"])
			{
				$show = true;
			}
		}
		$awt->stop("menuedit::make_yah");
		return $ya;
	}




	function make_yah2($path)
	{
		global $awt;
		$awt->start("menuedit::make_yah");
		// now build "you are here" links from the path
		$ya = "";  
		$show = false;
		$cnt = count($path);

		foreach($this->path as $val)
		{
			if ($show)
			{
				// default to the link field in the object
				$link = $this->menu_chain[$val]["link"];
				// or if that is not set, create a link
				if (not($link))
				{
					$link = "/".$this->menu_chain[$val]["oid"];
				};
				
				$text = $this->menu_chain[$val]["name"];

				$this->vars(array(
					"link" => $link,
					"text" => str_replace("&nbsp;","",strip_tags($text)), 
					"ysection" => $val,
				));

				if ($this->menu_chain[$val]["clickable"] == 1)
				{
					$ya.=$this->parse("YAH_LINK");
				}
			}

			if ($val == $GLOBALS["rootmenu"])
			{
				$show = true;
			}
		}
		$awt->stop("menuedit::make_yah");
		return $ya;
	}

	////
	// !See jupp siin teeb promokasti
	function make_promo_boxes($section)
	{
		global $awt;
		$awt->start("menuedit::make_promo_boxes");
		$doc = new document;
		$right_promo = "";
		$left_promo = "";
		$scroll_promo = "";
		/*
		$template = $this->_get_template_filename($this->properties["tpl_lead"]);
		if (not($template))
		{
			$template = $this->properties["ftpl_lead"];
		};
		*/
		$template = $this->get_lead_template($section);
		if ($GLOBALS["lang_menus"])
		{
			$lai = "AND objects.lang_id = ".$GLOBALS["lang_id"];
		}
		if (defined("PROMO_LEAD_ONLY"))
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
				WHERE objects.status = 2 AND objects.class_id = 22 AND (objects.site_id = ".$GLOBALS["SITE_ID"]." OR objects.site_id is null) $lai
				ORDER by jrk";
		$this->db_query($q);
		global $gidlist;
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
			$ar = unserialize($row["comment"]);
			if (((isset($ar["section"][$section]) && $ar["section"][$section]) || ($row["comment"] == "all_menus" && $row["site_id"] == $GLOBALS["SITE_ID"])) && $found)
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
						$pr_c.=str_replace("\r","",str_replace("\n","",$doc->gen_preview(array("docid" => $d, "tpl" => $row["filename"],"leadonly" => $leadonly, "section" => $section, 	"strip_img" => false,"showlead" => 1, "boldlead" => 0,"no_strip_lead" => 1))));
						}
					}
				}
				else
				{
					$pr_c.=$doc->gen_preview(array("docid" => $docid, "tpl" => $row["filename"],"leadonly" => $leadonly, "section" => $section, 	"strip_img" => false,"showlead" => 1, "boldlead" => 0,"no_strip_lead" => 1));
						
				}

				global $DEBUG;
				if ($DEBUG)
				{
					print $pr_c;
				};

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
		$awt->stop("menuedit::make_promo_boxes");
	}

	function make_poll()
	{
		global $awt;
		global $lang_id;
		global $SITE_ID;
		if ( ($lang_id == 3) && ($SITE_ID == 667) )
		{
			 return "";
		};

		$awt->start("menuedit::make_poll");
		classload("poll");
		$t = new poll;
		$this->vars(array("POLL" => $t->gen_user_html()));
		$awt->stop("menuedit::make_poll");
	}

	function make_search()
	{
		global $awt;
		$awt->start("menuedit::make_search");
		if ($this->is_template("SEARCH_SEL"))
		{
			global $section,$frontpage;
			$id = $section;
			if (!$id)
			{
				$id=$frontpage;
			}
			classload("search_conf");
			$t = new search_conf;
			$def = $section;
			$sl = $t->get_search_list(&$def);
			$this->vars(array(
				"search_sel" => $this->option_list($def,$sl),
				"section" => $id,
			));
			$this->vars(array("SEARCH_SEL" => $this->parse("SEARCH_SEL")));
		}
		$awt->stop("menuedit::make_search");
	}

	function make_nadalanagu()
	{
		global $awt;
		$awt->start("menuedit::make_nadalanagu");
		classload("nagu");
		$t = new nagu;
		$nagu = $t->get_active($GLOBALS["per_oid"]);
		$tmp = $nagu["content"];
		if ($nagu["num"] > 0 && is_array($tmp))
		{
			reset($tmp);
			uasort($tmp,__nagu_sort);
			reset($tmp);
			$max = $nagu["num"];
			// kui 3, siis 3
			$max = 3;
			for ($i=0; $i < $max; $i++)
			{	
				list(,$v) = each($tmp);
				$this->vars(array("pos" => $i+1, "name" => $v["eesnimi"]." ".$v["kesknimi"]." ".$v["perenimi"]));
				$l.=$this->parse("NAME");
				if ($i == 0)
				{
					$wurl = $v["imgurl"];
				}
			}
			if ($wurl == "")
			{
				$wurl = $GLOBALS["baseurl"]."/images/transa.gif";
			}
			$this->vars(array("NAME" => $l,"winnerurl" => $wurl));
			$nn = $this->parse("NADALA_NAGU");
			$this->vars(array("NADALA_NAGU" => $nn));
		}
		$awt->stop("menuedit::make_nadalanagu");
	}

	function do_rdf($section,$obj,$format,$docid)
	{
		classload("rdf");
		global $baseurl,$ext;
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
				$activeperiod = $GLOBALS["act_per_id"];
			}
			$d->set_period($activeperiod);
			$d->list_docs($section, $activeperiod,2);
			$cont = "";
			if ($d->num_rows > 1) 
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
		global $banner_defs,$banner_server,$ext,$uid;

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
		global $awt;
		$awt->start("menuedit::do_shop");
		classload("shop");
		$sh = new shop;
		$ret = $sh->show(array("section" => $section,"id" => $shop_id));
		$this->vars(array("shop_menus" => $sh->shop_menus));
		$awt->stop("menuedit::do_shop");
		return $ret;
	}

	function mdelete($arr)
	{
		extract($arr);
		if (is_array($sel))
		{
			foreach($sel as $oid => $one)
			{
				$this->delete_object($oid);
			}
		}

		$this->invalidate_menu_cache();

		return "menuedit.".$GLOBALS["ext"]."?parent=".$parent."&type=menus&period=".$period;
	}

	function make_langs()
	{
		global $awt;
		$awt->start("menuedit::make_langs");
		global $lang_id;
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
		$awt->stop("menuedit::make_langs");
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

			$t = new db_images;
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
			/*
			$sel_menu_meta = $this->get_object_metadata(array(
				"metadata" => $this->mar[$si_parent]["metadata"],
			));
			*/
			$sel_menu_meta = $this->mar[$si_parent]["meta"];
			if (is_array($sel_menu_meta["menu_images"]) && count($sel_menu_meta["menu_images"]) > 0)
			{
				$imgs = true;
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
					$sel_image = "<img name='sel_menu_image' src='".preg_replace("/^http:\/\/.*\//","/",$dat["url"])."' border='0'>";
					$sel_image_url = $dat["url"];
				}
				$this->vars(array(
					"url" => preg_replace("/^http:\/\/.*\//","/",$dat["url"])
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

	function invalidate_menu_cache()
	{
		$cache = new cache;
		global $lang_id,$SITE_ID;
		$cache->file_invalidate("menuedit::menu_cache::lang::".$lang_id."::site_id::".$SITE_ID);
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
		}

		classload("keywords");
		$kwd = new keywords;
		$kwd->update_menu_keyword_bros(array("menu_ids" => array($id)));
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
							$link = $GLOBALS["baseurl"]."/".$mprow["oid"];
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
			foreach($sub_callbacks as $sub => $fun)
			{
				if ($this->is_template($sub))
				{
					$fun(&$this);
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
		global $baseurl;
		header("Location: $baseurl/$url");
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
			
		global $gidlist;
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
					$mopts = array();
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
			$meta = aw_unserialize($row["metadata"]);
			if (is_array($meta["menus"]))
			{
				foreach($meta["menus"] as $key => $val)
				{
					if ($val == $section)
					{
						$popups .= "window.open('$meta[url]','popup','toolbar=0,location=0,menubar=0,scrollbars=0,width=$meta[width],height=$meta[height]');";
					};
				};
			};
		};
		return (strlen($popups) > 0) ? "<script language='Javascript'>$popups</a>" : "";
	}
}
?>
