<?php
// $Header: /home/cvs/automatweb_dev/classes/menuedit.aw,v 2.311 2002/12/24 15:23:46 kristo Exp $
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
		// ja eeldame, et meil on v�emalt parent ja name olemas.   
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


		$obj = $this->get_object($section);
		$meta = $obj["meta"];
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
			$ch = $this->get_object_chain($section, true);
			//reset($ch);
			//while (list($k,$v) = each($ch))
			foreach($ch as $k => $v)
			{
				$str=$v["name"]." / ".$str;
			}
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

				$ml_msg = $this->get_object($sid);

				$this->db_query("SELECT ml_users.*,objects.name as name FROM ml_users LEFT JOIN objects ON objects.oid = ml_users.id WHERE id = $artid");
				if (($ml_user = $this->db_next()))
				{
					$this->_log(ST_MENUEDIT, SA_PAGEVIEW ,$ml_user["name"]." (".$ml_user["mail"].") tuli lehele $log meilist ".$ml_msg["name"],$section);

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
		$_mn = $this->get_menu($realsect);
		$set_lang_id = false;
		if ($_mn)
		{
			if (!($_mn["type"] == MN_CLIENT && aw_ini_get("config.object_translation") == 0))
			{
				$set_lang_id = $_mn["lang_id"];
			};
		}
		else
		{
			$_obj = $this->get_object($realsect);
			$set_lang_id = $_obj["lang_id"];
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
				if ($set_lang_id != aw_global_get("lang_id"))
				{
					$la = get_instance("languages");
					$la->set_active($set_lang_id);
					$this->lc_load("menuedit","lc_menuedit");
					lc_site_load("menuedit",$this);
					lc_load("definition");
				}
				else
				{
					aw_global_set("lang_id",$set_lang_id);
				}
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

		// kui sektsiooni viimane m�k on "-", paneme selle objekti sees psti
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
		if (!is_number($section)) 
		{

			if ($this->cfg['recursive_aliases'])
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
						print "checking $sval<br />";
					}
					if ($obj !== false)
					{
						// ok, ini option: menuedit.recursive_aliases  - if true, aliases are checked by parents
						// vaatame, kas selle nimega aliast on?
						$filter = array(
							"alias" => $sval,
							"status" => STAT_ACTIVE,
							"site_id" => aw_ini_get("site_id")
						);
						if ($prnt)
						{
							$filter["parent"] = $prnt;
						}

						$ol = new object_list($filter);
						if ($ol->count() == 0)
						{
							$obj = false;
						}
						else
						{
							$obj = $ol->begin();
						}

						// need to check one more thing, IF prnt = 0 then fetch the parent
						// of this object and see whether it has an alias. if so, do not
						// let him access this menu directly
						if ($prnt == 0 && is_object($obj))
						{
							$pobj = obj($obj->parent());
							if (strlen($pobj->alias()) > 0)
							{
								$obj = false;
							}
						};

						if ( ($prnt != 0) && ($obj->parent() != $prnt) )
						{
							$obj = false;
						}
						else
						{
							if (is_object($obj))
							{
								$prnt = $obj->id();
							}
						}
					}
				}
			}
			else
			{
				// vaatame, kas selle nimega aliast on?
				$this->quote(&$section);
				$ol = new object_list(array(
					"alias" => $section,
					"status" => STAT_ACTIVE,
					"site_id" => aw_ini_get("site_id")
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
			// mingi kontroll, et kui sektsioon ei eksisteeri, siis n�tame esilehte
			if (!(($section > 0) && ($this->object_exists($section) && $this->can("view", $section)))) 
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

	// builds HTML popups
	function build_popups()
	{
		// that sucks. We really need to rewrite that
		// I mean we always read information about _all_ the popups
		$pl = new object_list(array(
			"status" => STAT_ACTIVE,
			"class_id" => CL_HTML_POPUP
		));
		for ($o = $pl->begin(); !$pl->end(); $o = $pl->next())
		{
			$ar = new aw_array($o->meta("menus"));
			$sub = $o->meta("section_include_submenus");

			foreach($ar->get() as $key => $val)
			{
				$show = false;
				if ($sub[$val])
				{
					if (!is_array($this->path))
					{
						$so = $this->get_object($this->sel_section);
						$this->path = $this->get_path($this->sel_section,$so);
					}
					if (in_array($val, $this->path))
					{
						$show = true;
					}
				}
				else
				{
					if ($val == $this->sel_section)
					{
						$show = true;
					}
				}

				if ($o->meta("only_once") && aw_global_get("aw_popup_shown_".$o->id()) == 1)
				{
					$show = false;
				}

				if ($show)
				{
					$popups .= sprintf("window.open('%s','popup','top=0,left=0,toolbar=0,location=0,menubar=0,scrollbars=0,width=%s,height=%s');",
						$this->mk_my_orb("show", array("id" => $o->meta("show_obj"), "no_menus" => 1), "objects"),
						$o->meta("width"), 
						$o->meta("height")
					);
					aw_session_set("aw_popup_shown_".$o->id(), "1");
				}
			}
		}
		$retval = (strlen($popups) > 0) ? "<script language='Javascript'>$popups</script>" : "";
		return $retval;
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
