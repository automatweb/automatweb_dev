<?php
// $Header: /home/cvs/automatweb_dev/classes/menuedit.aw,v 2.331 2004/09/09 11:13:49 kristo Exp $
// menuedit.aw - menuedit. heh.

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
		// ja eeldame, et meil on v?emalt parent ja name olemas.   
		$o = obj();
		$o->set_name($args["name"]);
		$o->set_parent($args["parent"]);
		$o->set_status((isset($args["status"]) ? $args["status"] : 2));
		$o->set_class_id(CL_MENU);
		$o->set_ord($args["jrk"]);
		$o->set_meta("pclass", $args["pclass"]);
		$o->set_meta("pm_url_admin", $args["pm_url_admin"]);
		$o->set_prop("type", $args["type"] ? $args["type"] : MN_HOME_FOLDER_SUB);
		$o->set_prop("link", $args["link"]);
		$newoid = $o->save();

		$this->_log(ST_MENUEDIT, SA_ADD, $args["name"], $newoid);   

		if (!$args['no_flush'])   
		{   
			$this->invalidate_menu_cache();
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
	function gen_site_html($params)
	{
		// handle favicon
		if (($params["section"]."") == "favicon.ico")
		{
			$c = get_instance("config");
			$c->show_favicon(array());
		}

		// kontrollib sektsiooni ID-d, tagastab oige numbri kui tegemist oli
		// aliasega, voi lopetab t, kui miskit oli valesti
		$section = $this->check_section($params["section"]);



		// at this point $section is already numeric,
		// we checked it in $this->request_startup()
//		$section = aw_global_get("section");
//		echo "section = $section <br />";


		$obj = obj($section);
		$meta = $obj->meta();
		$params["section"] = $section;

		global $format;
		$act_per_id = aw_global_get("act_per_id");
		if ($format == "rss")
		{
			$rss = get_instance("output/xml/rss");
			$rss->gen_rss_feed(array("period" => $act_per_id,"parent" => $section));
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
			$site_content = get_instance("contentmgmt/site_content");
			$site_content->raw = $this->raw;
			$res = $site_content->_gen_site_html($params);
			$this->sel_menus = $site_content->sel_menus;
			$this->sel_section = $site_content->sel_section;
			$this->path = $site_content->path;

			$apd = get_instance("layout/active_page_data");
			$res .= $apd->on_shutdown_get_styles();
			if ($use_cache && !aw_global_get("no_cache_content"))
			{
				$cache->set($section,$cp,$res);
			};
//			echo "<!-- no cache $section <pre>",join("-",$cp),"</pre>\n-->";
		}
		else
		{
			// kui asi on caches, siis paneme kirja et mis lehte vaadatati.
			$this->sel_section = $section;
			$tmp = obj($section);
			$str = $tmp->path_str();
			$this->do_syslog_core($str,$section);
		}

		// make sure that the banner random id's are different each time around, even when the site is cached.
		$banner_defs = aw_ini_get("menuedit.banners");
		if (is_array($banner_defs))
		{
			//reset($banner_defs);
			//while (list($name,$gid) = each($banner_defs))
			foreach($banner_defs as $name => $gid)
			{
				$res = str_replace("[ss".$gid."]",gen_uniq_id(),$res);
			}
		}
		$res .= $this->build_popups();
		return $res;
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
				menu.periodic as mperiodic
			FROM objects 
				LEFT JOIN menu ON menu.id = objects.oid
				WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")  AND $where $aa
				ORDER BY objects.parent, jrk,objects.created";
		$this->db_query($q);
	}


	function do_syslog_core($log,$section)
	{
		global $artid,$sid,$mlxuid;
		if ($artid)	// tyyp tuli meilist, vaja kirja panna
		{
			if (is_number($artid))
			{
				$sid = (int)$sid;

				$ml_msg = obj($sid);

				$this->db_query("SELECT ml_users.*,objects.name as name FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE id = $artid");
				if (($ml_user = $this->db_next()))
				{
					$this->_log(ST_MENUEDIT, SA_PAGEVIEW ,$ml_user["name"]." (".$ml_user["mail"].") tuli lehele $log meilist ".$ml_msg->name(),$section);

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
		if ($this->cfg["log_pageviews"] == 1)
		{
			global $XX3;
			if ($XX3)
			{
				print "hua";
			};
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
		// moved here from syslog - it doesn't make sense to load assload of code to do only that

		// well, syslog now records referers on alla pageviews, so this is obsolete, yes? - terryf
		$referer = aw_global_get("HTTP_REFERER");
		if (preg_match("/^(http:\/\/.+)\//i",$referer,$mt))
		{
			if ($mt[1] != aw_ini_get("baseurl"))
			{
				$this->_log(ST_REFERER, SA_ADD, $referer);
				aw_session_set("referer",$mt[1]);
			};
		};
		// end of move
		$section = aw_global_get("section");
		$realsect = $this->check_section($section);
		$set_lang_id = false;
		if ($this->can("view",$realsect))
		{
		$_obj = obj($realsect);
		if ($_obj->class_id() == CL_MENU)
		{
			if (!($_obj->prop("type") == MN_CLIENT || aw_ini_get("config.object_translation") == 1))
			{
				$set_lang_id = $_obj->lang_id();
			};
		}
		else
		{
			$set_lang_id = $_obj->lang_id();
			// we do document hit count logging here, because
			// we know if it's a document or not here
			if (1 == aw_ini_get("document_statistics.use") && $realsect != aw_ini_get("frontpage") && ($_obj->class_id() == CL_DOCUMENT || $_obj->class_id() == CL_BROTHER_DOCUMENT || $_obj->class_id() == CL_PERIODIC_SECTION))
			{
				$dt = get_instance("contentmgmt/document_statistics");
				$dt->add_hit($realsect);
			}
		};
		};

		// let logged-in users see not-active language stuff
		if (aw_global_get("uid") != "")
		{
			$st = " AND status != 0 ";
		}
		else
		{
			$st = " AND status = 2 ";
		}
		$q = "SELECT name FROM languages WHERE id = '$set_lang_id' $st";
		$this->db_query($q);
		$row = $this->db_next();


		if ($set_lang_id)
		{
			if ($row)
			{
				$la = get_instance("languages");
				$la->set_active($set_lang_id);
				$this->lc_load("menuedit","lc_menuedit");
				lc_site_load("menuedit",$this);
				lc_load("definition");
				// we must reset the objcache here, because
				// it already contains the section obj
				// and after the language switch it contains the old language
				// objects and that messes up the auto_translation
				// anyway, tyhis does not add much overhead, 
				// because here we should only have the section object loaded
				$GLOBALS["objects"] = array();
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
		// check frontpage - if it is array, pick the correct one from the language
		$frontpage = $this->cfg["frontpage"];
		if (is_array($frontpage))
		{
			$frontpage = $frontpage[aw_global_get("lang_id")];
			$GLOBALS["cfg"]["__default"]["frontpage"] = $frontpage;
			$this->cfg["frontpage"] = $frontpage;
		}

		// kui sektsiooni viimane m?k on "-", paneme selle objekti sees psti
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
			$ret = $frontpage < 1 ? 1 : $frontpage;

			if (!headers_sent())
			{
				header("X-AW-Section: ".$frontpage);
			}
			return $ret;
		}

		if ($section == 'favicon.ico')
		{
			// if user requested favicon, then just show the thing here and be done with it
			$c = get_instance("config");
			$c->show_favicon(array());
		}

	
		// sektsioon ei olnud numbriline
		if (!is_oid($section))
		{

			if ($this->cfg['recursive_aliases'])
			{
				// first I have to check whether the alias contains /-s and if so, split
				// the url into pieces
				$sections = explode("/",$section);
	
				// if it contains a single $section, it is now located in $sections[0]
			
				$candidates = array();
				$last = array_pop($sections);
						
				// well, I think I have a better idea .. I'll start from the last item
				// calculate all possible aliases and then select one
				$flt = array(
					"alias" => $last,
					"status" => STAT_ACTIVE,
					"site_id" => aw_ini_get("site_id"),
					"lang_id" => array(),
				);

				$clist = new object_list($flt);

				for($check_obj = $clist->begin(); !$clist->end(); $check_obj = $clist->next())
				{
					// put it in correct order and remove the first element (object itself)
					$path = array_reverse($check_obj->path());
					$curr_id = $check_obj->id();
					$candidates[$curr_id] = "";

					$stop = false;

					foreach($path as $path_obj)
					{
						if (!$stop)
						{
							$alias = $path_obj->alias();
							if (strlen($alias) > 0)
							{
								$candidates[$curr_id] = $alias . "/" . $candidates[$curr_id];
							}
							else
							{
								$stop = true;
							};
						};
					};
				};

				foreach($candidates as $cand_id => $cand_path)
				{
					$path_for_obj = substr($cand_path,0,-1);
					if ($path_for_obj == $section)
					{
						$obj = new object($cand_id);
					};
				};

			}
			else
			{
				// vaatame, kas selle nimega aliast on?
				$this->quote(&$section);
				$ol = new object_list(array(
					"alias" => $section,
					"status" => STAT_ACTIVE,
					"site_id" => aw_ini_get("site_id"),
					"lang_id" => array()
				));
				if ($ol->count() < 1)
				{
					$obj = false;
				}
				else
				{
					$obj = $ol->begin();
				}
			}

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
					$this->_do_error_redir($section);
				}
				else
				{
					return false;
				}
			} 
			else 
			{
				$section = $obj->id();
			};
		} 
		else 
		{
			// mingi kontroll, et kui sektsioon ei eksisteeri, siis n?tame esilehte
			if (!(($section > 0) && $this->can("view", $section))) 
			{
				$this->_log(ST_MENUEDIT, SA_NOTEXIST,sprintf(LC_MENUEDIT_TRIED_ACCESS2,$section), $section);
				$section = $frontpage;
			}
			else
			{
				$o = obj($section);
				if ($o->site_id() != aw_ini_get("site_id") && aw_global_get("uid") == "")
				{
					if ($show_errors)
					{
						$this->_do_error_redir($section);
					}
					else
					{
						$section = $frontpage;
					}
				}
			}
		};

		if (!$section)
		{
			$section = aw_ini_get("frontpage");
		}
	
		if (!headers_sent())
		{
			header("X-AW-Section: ".$section);
		}
		return $section;
	}

	function get_feature_icon_url($fid)
	{
		return aw_ini_get("icons.server")."/prog_".$fid.".gif";
	}

	////
	// !Tagastab nimekirja erinevatest mentpidest
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

	function invalidate_menu_cache()
	{
		$cache = get_instance("cache");

		// here we gots to invalidate the objects::get_list cache as well, cause it also contains menus
		$cache->file_invalidate_regex("objects::get_list::.*");
		$cache->file_invalidate_regex("menuedit::menu_cache::.*");
	}

	// builds HTML popups
	function build_popups()
	{
		// that sucks. We really need to rewrite that
		// I mean we always read information about _all_ the popups
		$ss = get_instance("contentmgmt/site_show");
		$tmp = array();
		$ss->_init_path_vars($tmp);
		$ss->sel_section = $ss->_get_sel_section(aw_global_get("section"));
		return $ss->build_popups();
	}

	function get_path($section,$obj)
	{
		// now find the path through the menu
		$path = array();
		if ($obj["class_id"] != CL_PSEUDO)
		{
			$sec = $obj["parent"];
			$section = $obj["parent"];
		}
		else
		{
			$sec = $section; 
		}
		$cnt = 0;
		$tmp = array();
		// kontrollime seda ka, et kas see "sec" yldse olemas on,
		// vastasel korral satume loputusse tsyklisse
		while ($sec && ($sec != 1)) 
		{
			array_push($tmp,$sec);
			if (!isset($this->mar[$sec]))
			{
				$mc = get_instance("menu_cache");
				$this->mar[$sec] = $mc->get_cached_menu($sec);
			}
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
			$path[$i+1] = array_pop($tmp);
		};
		// and now in the $path array
		return $path;
	}

	function _do_error_redir($section)
	{
		$si = __get_site_instance();
		if (is_object($si) && method_exists($si, "handle_error_redir"))
		{
			$tmp = $si->handle_error_redir($section);
			if ($tmp != "")
			{
				header("Location: $tmp");
				die();
			}
		}

		$this->_log(ST_MENUEDIT, SA_ACL_ERROR,sprintf(LC_MENUEDIT_TRIED_ACCESS,$section), $section);
		// neat :), kui objekti ei leita, siis saadame 404 koodi
		$r404 = $this->cfg["404redir"];
		if (is_array($r404))
		{
			$r404 = $r404[aw_global_get("lang_id")];
		}
		if ($r404 && "/".$GLOBALS["section"] != $r404)
		{
			header("Location: " . $r404);
		}
		else
		{
			header ("HTTP/1.1 404 Not Found");
			printf(E_ME_NOT_FOUND);
		};
		exit;
	}
}
?>
