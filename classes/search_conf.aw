<?php
define("PER_PAGE",10);

class search_conf extends aw_template 
{
	function search_conf()
	{
		$this->init("search_conf");
		lc_load("definition");
		$this->lc_load("search_conf","lc_search_conf");
		lc_site_load("search",&$this);
	}

	function gen_admin($level)
	{
		$lang_id = aw_global_get("lang_id");
		$SITE_ID = $this->cfg["site_id"];

		$ob = get_instance("objects");
		$c = get_instance("config");
		$conf = unserialize($c->get_simple_config("search_conf"));

		if (!$level)
		{
			$this->read_template("conf1.tpl");
			$this->vars(array("section"		=> $ob->multiple_option_list($conf[$SITE_ID][$lang_id]["sections"],$ob->get_list())));
			return $this->parse();
		}
		else
		{
			$sarr = $ob->get_list();

			$this->read_template("conf2.tpl");
			reset($conf[$SITE_ID][$lang_id]["sections"]);
			while (list(,$v) = each($conf[$SITE_ID][$lang_id]["sections"]))
			{
				$this->vars(array("section" => $sarr[$v],"section_id" => $v,"section_name" => $conf[$SITE_ID][$lang_id]["names"][$v],"order" => $conf[$SITE_ID][$lang_id]["order"][$v]));
				$s.= $this->parse("RUBR");
			}
			$this->vars(array("RUBR" => $s));
			return $this->parse();
		}
	}

	function submit($arr)
	{
		$lang_id = aw_global_get("lang_id");
		$SITE_ID = $this->cfg["site_id"];

		extract($arr);

		if (is_array($section))
		{
			reset($section);
			$a = array();
			while (list(,$v) = each($section))
			{
				$a[$v]=$v;
			}
		}

		$c = get_instance("config");
		$conf = unserialize($c->get_simple_config("search_conf"));

		if (!$level)
		{
			$conf[$SITE_ID][$lang_id]["sections"] = $a;
			$c->set_simple_config("search_conf",serialize($conf));
			return 1;
		}
		else
		{
			$conf[$SITE_ID][$lang_id]["names"] = array();
			reset($arr);
			while (list($k,$v) = each($arr))
			{
				if (substr($k,0,3) == "se_")
				{
					$id = substr($k,3);
					$conf[$SITE_ID][$lang_id]["names"][$id] = $v;
				}
			}

			$conf[$SITE_ID][$lang_id]["order"] = array();
			reset($arr);
			while (list($k,$v) = each($arr))
			{
				if (substr($k,0,3) == "so_")
				{
					$id = substr($k,3);
					$conf[$SITE_ID][$lang_id]["order"][$id] = $v;
				}
			}
			$c->set_simple_config("search_conf",serialize($conf));
			return 1;
		}
	}

	function get_search_list(&$default)
	{
		$grps = $this->get_groups();
		$ret = array();
		foreach($grps as $grpid => $gdata)
		{
			if (aw_global_get("uid") != "" || $gdata["users_only"] != 1)
			{
				if (is_array($gdata["menus"]))
				{
					foreach($gdata["menus"] as $mn1 => $mn2)
					{
						if ($mn1 == $default)
						{
							$def = $grpid;
						}
					}
				};
				$ret[$grpid] = $gdata["name"];
			}
		}
		$default = $def;
		return $ret;
	}

	////
	// !shows the search form
	function search($arr)
	{
		extract($arr);
		$this->read_template("search.tpl");

		if (($bs = aw_ini_get("search.baseurl")) != "")
		{
			$this->cfg["baseurl"] = $bs;
			$this->vars(array("baseurl" => $bs));
		}

		$k = get_instance("keywords");

		$keys = array();
		$sel_keys = false;
		if (is_array($s_keywords))
		{
			foreach($s_keywords as $kw)
			{
				$keys[$kw] = $kw;
				$sel_keys = true;
			}
		}
		$search_list = $this->get_search_list(&$def);
		// if s_parent isn't numeric, set it to zero. otherwise various
		// interesting effects will happen. I spent fscking 2 hours debugging
		// this in www.eas.ee
		if ($s_parent != sprintf("%d",$s_parent))
		{
			$s_parent = 0;
		};

		$sp = "";
		$first = true;
		foreach($search_list as $sl_idx => $sl_val)
		{
			$this->vars(array(
				"sp_val" => $sl_idx,
				"sp_text" => $sl_val,
				"sp_sel" => checked(($s_parent ? $s_parent == $sl_idx : $first) )
			));
			$sp.=$this->parse("SEARCH_PARENT");
			$first = false;
		}

		$this->vars(array(
			"SEARCH_PARENT" => $sp,
			"search_sel" => $this->option_list($s_parent,$search_list),
			"sstring_title" => $sstring_title,
			"sstring" => $sstring,
			"t2c_or" => selected($t2c_log == "OR"),
			"t2c_and" => selected($t2c_log == "AND"),
			"c2k_or" => selected($c2k_log == "OR"),
			"c2k_and" => selected($c2k_log == "AND"),
			"t_type1" => selected($t_type == 1),
			"t_type2" => selected($t_type == 2),
			"t_type3" => selected($t_type == 3),
			"c_type1" => selected($c_type == 1),
			"c_type2" => selected($c_type == 2),
			"c_type3" => selected($c_type == 3),
			"keywords" => $this->multiple_option_list($keys,$k->get_all_keywords(array("type" => ARR_KEYWORD))),
			"reforb"	=> $this->mk_reforb("search", array("reforb" => 0,"search" => 1,"section" => aw_global_get("section"), "set_lang_id" => aw_global_get("lang_id")))
		));

		$this->quote(&$sstring);
		$this->quote(&$sstring_title);
		// this means that we only have one textbox, that sould search from title || body
		if ($search_all)
		{
			$sstring = $sstring_title;
			$t2c_log = "OR";
			$c_type = $t_type;
		}

		if ($search && ($sstring_title != "" || $sstring != ""))
		{
			// if we should be actually searching from a form, let formgen work it's magick
			$grps = $this->get_groups();
			if ($grps[$s_parent]["search_form"])
			{
				// do form search
				$finst = get_instance("formgen/form");
				// we must load the form before we can set element values
				$finst->load($grps[$s_parent]["search_form"]);

				$s_q = $sstring != "" ? $sstring : $sstring_title;

				// set the search elements values
				foreach($grps[$s_parent]["search_elements"] as $el)
				{
					$finst->set_element_value($el, $s_q, true);
				}

				global $restrict_search_el,$restrict_search_val,$use_table,$search_form;
				$this->vars(array(
					"SEARCH" => $finst->new_do_search(array(
						"restrict_search_el" => $restrict_search_el,
						"restrict_search_val" => $restrict_search_val,
						"use_table" => $use_table,
						"section" => $section,
						"search_form" => $search_form
					))
				));

				return $this->parse();
			}
			else
			if ($grps[$s_parent]["static_search"])
			{
				return $this->do_static_search($sstring != "" ? $sstring : $sstring_title, $page, $arr, $s_parent, $t_type);
			}


			// and here we do the actual searching bit!

			// assemble the search criteria sql
			if ($sstring_title != "")
			{
				if ($t_type == 1)	//	m6ni s6na
				{
					$q_cons2.="(".join(" OR ",map("(title LIKE '%%%s%%')",explode(" ",$sstring_title))).")";
				}
				else
				if ($t_type == 2)	//	k6ik s6nad
				{
					$q_cons2.="(".join(" AND ",map("(title LIKE '%%%s%%')",explode(" ",$sstring_title))).")";
				}
				else
				if ($t_type == 3)	//	fraas
				{
					$q_cons2.="(title LIKE '%".$sstring_title."%')";
				}

				if ($sstring != "" || $sel_keys)	// these can't be moved to the next if, cause then we'd have to check if $sstring != ""
				{
					if ($t2c_log == "OR")
					{
						$q_cons2.=" OR ";
					}
					else
					{
						$q_cons2.=" AND ";
					}
				}
			}

			if ($sstring != "")
			{
				if ($c_type == 1)	//	m6ni s6na
				{
					// dokude tabelist otsing
					$q_cons2.="(".join(" OR ",map("(content LIKE '%%%s%%' OR lead LIKE '%%%s%%')",explode(" ",$sstring))).")";
					// failide tabelist otsing
					$q_fcons2="(".join(" OR ",map("(content LIKE '%%%s%%')",explode(" ",$sstring))).")";
					// tabelite tabelist otsing
					$q_tcons2="(".join(" OR ",map("(contents LIKE '%%%s%%')",explode(" ",$sstring))).")";
				}
				else
				if ($c_type == 2)	//	k6ik s6nad
				{
					$q_cons2.="(".join(" AND ",map("(content LIKE '%%%s%%' OR lead LIKE '%%%s%%')",explode(" ",$sstring))).")";
					$q_fcons2="(".join(" AND ",map("(content LIKE '%%%s%%')",explode(" ",$sstring))).")";
					$q_tcons2="(".join(" AND ",map("(contents LIKE '%%%s%%')",explode(" ",$sstring))).")";
				}
				else
				if ($c_type == 3)	//	fraas
				{
					$q_cons2.="(content LIKE '%".$sstring."%' OR lead LIKE '%".$sstring."%')";
					$q_fcons2="(content LIKE '%".$sstring."%')";
					$q_tcons2="(contents LIKE '%".$sstring."%')";
				}

				if ($sel_keys != "")
				{
					if ($c2k_log == "OR")
					{
						$q_cons2.=" OR ";
					}
					else
					{
						$q_cons2.=" AND ";
					}
				}
			}

			if ($sel_keys)
			{
				$q_cons2.="(".join(" OR ",map("documents.keywords LIKE '%%%s%%'",$keys)).")";
			}

			// search from files and tables here. ugh. ugly. yeah. I know.

			// oh crap. siin peab siis failide seest ka otsima. 
			if ($q_fcons2 != "")
			{
				$mtfiles = array();
				$this->db_query("SELECT id FROM files WHERE files.showal = 1 AND $q_fcons2 ");
				while ($row = $this->db_next())
				{
					$mtfiles[] = $row["id"];
				}
				$fstr = join(",",$mtfiles);
				if ($fstr != "")
				{
					// nyyd leiame k6ik aliased, mis vastavatele failidele tehtud on
					$this->db_query("SELECT source FROM aliases WHERE target IN ($fstr)");
					while ($row = $this->db_next())
					{
						$faliases[] = $row["source"];
					}
					// nyyd on $faliases array dokumentidest, milles on tehtud aliased matchivatele failidele.
					if (is_array($faliases))
					{
						$fasstr = "OR documents.docid IN (".join(",",$faliases).")";
					}
				}
			}

			if ($q_tcons != "")
			{
				// nini. otsime tabelite seest ka.
				$mts = array();
				$this->db_query("SELECT id FROM aw_tables WHERE $q_tcons");
				while ($row = $this->db_next())
				{
					$mts[] = $row["id"];
				}

				$mtsstr = join(",",$mts);
				if ($mtsstr != "")
				{
					// nyyd on teada k6ik tabelid, ksu string sisaldub
					// leiame k6ik aliased, mis on nendele tabelitele tehtud
					$this->db_query("SELECT source FROM aliases WHERE target IN ($mtsstr)");
					while ($row = $this->db_next())
					{
						$mtals[$row["source"]] = $row["source"];
					}

					// see on siis nimekiri dokudest, kuhu on tehtud aliased tabelitest, mis matchisid
					$mtalsstr = "OR documents.docid IN (".join(",",$mtals).")";
					//echo "ms = $mtalsstr<br>";
				}
			}

			// now fit in the results from searching files and tables
			if ($q_cons2 != "")
			{
				$q_cons2="((".$q_cons2.") $fasstr $mtalsstr)";
			}
			else
			{
				if ($fasstr != "" || $mtalsstr != "")
				{
					$q_cons2="($fasstr $mtalsstr)";
				}
			}

			// get all the parents under what the document can be
			$p_arr = $this->get_parent_arr($s_parent);
			
			$_tpstr = join(",",$p_arr);
			if ($_tpstr != "")
			{
				$q_cons = "status = 2 AND parent IN (".$_tpstr.") ";
			}
			else
			{
				$q_cons = "status = 2  ";
			}

			if ($q_cons2 != "")
			{
				$q_cons.=" AND (".$q_cons2.")";
			}

			// make pageselector
			$cnt = $this->db_fetch_field("SELECT count(*) as cnt FROM documents LEFT JOIN objects ON objects.oid = documents.docid WHERE $q_cons","cnt");

			$this->vars(array(
				"PAGESELECTOR" => $this->do_pageselector($cnt,$arr)
			));

			$ap = $this->do_sorting($arr);

			$mned = get_instance("contentmgmt/site_content");
			$mc = get_instance("menu_cache");
			$mc->make_caches();

			$this->db_query("SELECT objects.*,documents.* FROM documents LEFT JOIN objects ON objects.oid = documents.docid WHERE $q_cons $ap LIMIT ".($page*PER_PAGE).",".PER_PAGE);
			while ($row = $this->db_next())
			{
				$co = strip_tags($row["content"]);
				$co = preg_replace("/#(.*)#/","",substr($co,0,strpos($co,"\n")));

				$sec = $row["docid"];
				if ($mc->subs[$row["parent"]] == 1)
				{
					// we need to push all parent menus aliases to menuedit::menu_aliases for make_menu_link to work
					$mrow = $mc->get_cached_menu($row["parent"]);
					$mpr = $mrow["parent"];
					$mned->menu_aliases = array();

					while ($mpr > 0 && $mpr != $this->cfg["rootmenu"])
					{
						$mrow = $mc->get_cached_menu($mpr);
						if ($mrow["alias"] != "")
						{
							array_push($mned->menu_aliases,$mrow["alias"]);
						}
						$mpr = $mrow["parent"];
					}
					$mr = $mc->get_cached_menu($row["parent"]);
					if (!is_array($mr))
					{
						$mr = array();
					}
					$sec = $mned->make_menu_link($mr);
				}

				if (aw_ini_get("search.rewrite_urls"))
				{
					$exp = get_instance("export");
					$exp->fn_type = aw_ini_get("search.rewrite_url_type");
					$sec = $exp->rewrite_link($sec);
					$sec = aw_ini_get("baseurl")."/".$exp->get_hash_for_url($sec,aw_global_get("lang_id"));
				}

				$this->vars(array(
					"section" => $sec,
					"title" => $row["title"],
					"modified" => $row["tm"] != "" ? $row["tm"] : $this->time2date($row["modified"],2),
					"content" => $co
				));
				$mat.=$this->parse("MATCH");
			}
			$this->vars(array(
				"MATCH" => $mat
			));
			if ($mat != "")
			{
				$this->vars(array(
					"SEARCH" => $this->parse("SEARCH")
				));
			}
			else
			{
				$this->vars(array(
					"NO_RESULTS" => $this->parse("NO_RESULTS")
				));
			}

			// logime ka et tyyp otsis ja palju leidis.
			$this->do_log($search_list,$s_parent,$t_type,$sstring_title,$sstring,$t2c_log,$sel_keys,$keys,$c2k_log,$cnt);
		}
		return $this->parse();
	}

	function do_log($search_list,$s_parent,$t_type,$sstring_title,$sstring,$t2c_log,$sel_keys,$keys,$c2k_log,$cnt)
	{
		$this->db_query("INSERT INTO searches(str,s_parent,numresults,ip,tm) VALUES('$sstring','$s_parent','$cnt','".aw_global_get("REMOTE_ADDR")."','".time()."')");

		$sel_parent = $search_list[$s_parent];
		if ($t_type == 1)
		{
			$s = LC_SEARCH_CONF_SOME_WORD;
		}
		else
		if ($t_type == 2)
		{
			$s = LC_SEARCH_CONF_ALL_WORDS;
		}
		else
		if ($t_type == 3)
		{
			$s = LC_SEARCH_CONF_PHRASE;
		}
		if ($sstring_title != "")
		{
			$l=LC_SEARCH_CONF_IN_TITLE.$s.sprintf(LC_SEARCH_CONF_FROM_STRING,$sstring_title);
			if ($sstring != "")
			{
				if ($t2c_log == "OR")
				{
					$l.=LC_SEARCH_CONF_OR;
				}
				else	// AND
				{
					$l.=LC_SEARCH_CONF_AND;
				}
			}
		}

		if ($c_type == 1)
		{
			$s = LC_SEARCH_CONF_SOME_WORD;
		}
		else
		if ($c_type == 2)
		{
			$s = LC_SEARCH_CONF_ALL_WORDS;
		}
		else
		if ($c_type == 3)
		{
			$s = LC_SEARCH_CONF_PHRASE;
		}
		if ($sstring != "")
		{
			$l.=LC_SEARCH_CONF_IN_SUBJECT.$s.sprintf(LC_SEARCH_CONF_FROM_STRING,$sstring);
			if ($sel_keys != "")
			{
				if ($c2k_log == "OR")
				{
					$l.=LC_SEARCH_CONF_OR;
				}
				else	// AND
				{
					$l.=LC_SEARCH_CONF_AND;
				}
			}
		}

		if ($sel_keys)
		{
			$l.=LC_SEARCH_CONF_WITH_KEYWORD;
			$l.=join(",",map("%s",$keys));
		}
		$this->_log(ST_SEARCH, SA_DO_SEARCH, sprintf(LC_SEARCH_CONF_LOOK_ANSWER,$sel_parent,$l,$cnt));
	}

	function do_sorting($pa)
	{
		$sortby = $pa["sortby"];
		if ($sortby == "")
		{
			$sortby = "modified";
		}

		$pa["sortby"] = "modified";
		$this->vars(array(
			"sort_modified" => $this->mk_my_orb("search", $pa)
		));
		$pa["sortby"] = "title";
		$this->vars(array(
			"sort_title" => $this->mk_my_orb("search", $pa)
		));
		$pa["sortby"] = "content";
		$this->vars(array(
			"sort_content" => $this->mk_my_orb("search", $pa)
		));
		$sort_m = $this->parse("SORT_MODIFIED");
		$sort_t = $this->parse("SORT_TITLE");
		$sort_c = $this->parse("SORT_CONTENT");
		if ($sortby == "modified")
		{
			$ap = "ORDER BY documents.modified DESC";
			$sort_m = $this->parse("SORT_MODIFIED_SEL");
		}
		else
		if ($sortby == "title")
		{
			$ap = "ORDER BY documents.title";
			$sort_t = $this->parse("SORT_TITLE_SEL");
		}
		else
		if ($sortby == "content")
		{
			$ap = "ORDER BY documents.content";
			$sort_c = $this->parse("SORT_CONTENT_SEL");
		}
		$this->vars(array(
			"SORT_MODIFIED" => $sort_m,
			"SORT_MODIFIED_SEL" => "",
			"SORT_TITLE" => $sort_t,
			"SORT_TITLE_SEL" => "",
			"SORT_CONTENT" => $sort_c,
			"SORT_CONTENT_SEL" => "",
		));
		return $ap;
	}

	function do_pageselector($cnt,$arr)
	{
		$page = $arr["page"];
		$num_pages = $cnt / PER_PAGE;
		$pa = $arr;
		$pg = "";
		$prev = "";
		$nxt = "";
		for ($i=0; $i < $num_pages; $i++)
		{
			$pa["page"] = $i;
			$this->vars(array(
				"page" => $this->mk_my_orb("search", $pa),
				"page_from" => $i*PER_PAGE,
				"page_to" => min(($i+1)*PER_PAGE,$cnt)
			));
			if ($i == $page)
			{
				$pg.=$this->parse("SEL_PAGE");
			}
			else
			{
				$pg.=$this->parse("PAGE");
			}
		}
		$pa["page"] = max((int)$page-1,0);
		$this->vars(array(
			"prev" => $this->mk_my_orb("search", $pa)
		));
		$pa["page"] = min((int)$page+1,$num_pages-1);
		$this->vars(array(
			"next" => $this->mk_my_orb("search", $pa)
		));
		if ($page > 0)
		{
			$prev = $this->parse("PREVIOUS");
		}
		if ($page < ($num_pages-1))
		{
			$nxt = $this->parse("NEXT");
		}
		$this->vars(array(
			"PREVIOUS" => $prev, 
			"NEXT" => $nxt,
			"PAGE" => $pg, 
			"SEL_PAGE" => ""
		));
		return $this->parse("PAGESELECTOR");
	}

	function get_parent_arr($parent)
	{
		if ($this->cfg["lang_menus"] == 1)
		{
			$ss = " AND objects.lang_id = ".aw_global_get("lang_id");
		}

		$this->menucache = array();
		$this->db_query("SELECT objects.oid as oid, objects.parent as parent,objects.last as last,objects.status as status
										 FROM objects 
										 WHERE objects.class_id = 1 AND objects.status = 2 $ss");
		while ($row = $this->db_next())
		{
			$this->menucache[$row["parent"]][] = $row;
		}

		// now, make a list of all menus below $parent
		$this->marr = array();
		// list of default documents
		$this->darr = array();

		// $parent is the id of the menu group, not the parent menu
		// so now we figure out the parent menus and do rec_list for all of them 
		$mens = $this->get_menus_for_grp($parent);
		$this->marr = $mens;
		if (is_array($mens))
		{
			foreach($mens as $mn)
			{
				$this->rec_list($mn);
			}
		};
		return (is_array($this->marr)) ? $this->marr : array(0);
	}

	function rec_list($parent)
	{
		if (!is_array($this->menucache[$parent]))
		{
			return;
		}

		reset($this->menucache[$parent]);
		while(list(,$v) = each($this->menucache[$parent]))
		{
			//if ($v["status"] == 2)
			if ($v["status"] > 0)
			{
				$this->marr[] = $v["oid"];
				if ($v["last"] > 0)
					$this->darr[] = $v["last"];
				$this->rec_list($v["oid"]);
			}
		}
	}

	// updates documents timestamp from document::tm and objects::modified to documents::modified
	function upd_dox()
	{
		$this->db_query("SELECT objects.oid as oid,objects.modified as modified,documents.tm as tm,documents.title as title FROM objects LEFT JOIN documents ON documents.docid = objects.oid WHERE objects.class_id = 7");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$modified = $row["modified"];
			if ($row["tm"] != "")
			{
				list($day,$mon,$year) = explode("/",$row["tm"]);

				$ts = mktime(0,0,0,$mon,$day,$year);
				if ($ts)
				{
					$modified = $ts;
				}
			}

			$this->db_query("UPDATE documents SET modified = $modified WHERE docid = ".$row["oid"]);
			echo "modified doc ",$row["title"], " , tm = ",$row["tm"], " set date to ", $this->time2date($modified,3), "<br>\n";
			flush();
			$this->restore_handle();
		}
	}

	function change($arr)
	{
		$this->read_template("change.tpl");

		$act_grp = $this->get_cval("search::default_group");

		$grps = $this->get_groups();
		foreach($grps as $grpid => $grpdata)
		{
			$this->vars(array(
				"grpid" => $grpid,
				"name" => $grpdata["name"],
				"ord" => $grpdata["ord"],
				"change" => $this->mk_my_orb("change_grp", array("id" => $grpid)),
				"delete" => $this->mk_my_orb("delete_grp", array("id" => $grpid)),
				"checked" => checked($act_grp == $grpid)
			));
			$l.=$this->parse("LINE");
		}

		$id = @max(array_keys($grps))+1;

		$this->vars(array(
			"add" => $this->mk_my_orb("change_grp", array("id" => $id)),
			"LINE" => $l,
			"s_log" => $this->mk_my_orb("search_log", array()),
			"no_act_search" => checked(!$act_grp),
			"reforb" => $this->mk_reforb("submit_conf")
		));
		return $this->parse();
	}

	function submit_conf($arr)
	{
		extract($arr);
		
		$this->set_cval("search::default_group", $act_search);
		return $this->mk_my_orb("change");
	}

	function change_grp($arr)
	{
		extract($arr);
		$this->read_template("change_grp.tpl");
		$this->mk_path(0,"<a href='".$this->mk_my_orb("change", array())."'>Gruppide nimekiri</a> / Muuda gruppi");
		$grps = $this->get_groups();

		$o = get_instance("objects");

		$f = get_instance("formgen/form");
		$flist = $f->get_flist(array(
			"type" => FTYPE_SEARCH, 
			"addempty" => true, 
			"addfolders" => true,
			"sort" => true
		));

		$els = $f->get_elements_for_forms(array($grps[$id]["search_form"]));

		$this->vars(array(
			"name" => $grps[$id]["name"],
			"ord" => $grps[$id]["ord"],
			"id" => $id,
			"menus" => $this->multiple_option_list($grps[$id]["menus"],$o->get_list()),
			"no_usersonly" => checked($grps[$id]["no_usersonly"] == 1),
			"users_only" => checked($grps[$id]["users_only"] == 1),
			"static_search" => checked($grps[$id]["static_search"] == 1),
			"min_len" => $grps[$id]["min_len"],
			"max_len" => $grps[$id]["max_len"],
			"empty_no_docs" => checked($grps[$id]["empty_search"] < 2),
			"empty_all_docs" => checked($grps[$id]["empty_search"] == 2),
			"search_forms" => $this->picker($grps[$id]["search_form"], $flist),
			"search_elements" => $this->mpicker($grps[$id]["search_elements"], $els),
			"reforb" => $this->mk_reforb("submit_change_grp", array("id" => $id))
		));
		return $this->parse();
	}

	function submit_change_grp($arr)
	{
		extract($arr);

		$grps = $this->get_groups();

		$grps[$id]["name"] = $name;
		$grps[$id]["ord"] = $ord;
		$grps[$id]["no_usersonly"] = $no_usersonly;
		$grps[$id]["users_only"] = $users_only;
		$grps[$id]["static_search"] = $static_search;
		$grps[$id]["min_len"] = $min_len;
		$grps[$id]["max_len"] = $max_len;
		$grps[$id]["empty_search"] = $empty_search;
		$grps[$id]["menus"] = $this->make_keys($menus);
		$grps[$id]["search_form"] = $search_form;
		$grps[$id]["search_elements"] = $this->make_keys($search_elements);
		
		$this->save_grps($grps);

		return $this->mk_my_orb("change_grp", array("id" => $id));
	}

	function _grp_sort($a,$b)
	{
		if ($a["ord"] == $b["ord"])
		{
			return 0;
		}
		return $a["ord"] < $b["ord"] ? -1 : 1;
	}

	function save_grps($grps)
	{
		// here we must first sort the $grps array based on user entered order
		uasort($grps,array($this,"_grp_sort"));
		$cache = get_instance("cache");

		$lgps = $this->get_groups(true);
		$lgps[$this->cfg["site_id"]][aw_global_get("lang_id")] = $grps;

		$cache->file_set("search_groups::".$this->cfg["site_id"],aw_serialize($lgps));
		$dat = aw_serialize($lgps,SERIALIZE_XML);
		$this->quote(&$dat);
		$c = get_instance("config");
		$c->set_simple_config("search_grps", $dat);
	}

	function get_groups($no_strip = false)
	{
		$cache = get_instance("cache");
		$cs = $cache->file_get("search_groups::".$this->cfg["site_id"]);
		if ($cs)
		{
			$ret = aw_unserialize($cs);
		}
		else
		{
			$dat = $this->get_cval("search_grps");
			$ret = aw_unserialize($dat);
			$cache->file_set("search_groups::".$this->cfg["site_id"],aw_serialize($ret));
		};

		if ($no_strip)
		{
			$r = $ret;
		}
		else
		{
			$r = $ret[$this->cfg["site_id"]][aw_global_get("lang_id")];
		}

		if (!is_array($r))
		{
			return array();
		}
		else
		{
			return $r;
		}
	}

	function delete_grp($arr)
	{
		extract($arr);
		$grps = $this->get_groups();
		unset($grps[$id]);
		$this->save_grps($grps);
		header("Location: ".$this->mk_my_orb("change", array()));
	}

	function get_menus_for_grp($gp)
	{
		$grps = $this->get_groups();
		return $grps[$gp]["menus"];
	}

	function search_log($arr)
	{
		extract($arr);
		$this->read_template("search_log.tpl");

		$grps = $this->get_groups();

		$this->db_query("SELECT * FROM searches");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"time" => $this->time2date($row["tm"]),
				"str" => $row["str"],
				"s_parent" => $grps[$row["s_parent"]]["name"],
				"numresults" => $row["numresults"],
				"ip" => $row["ip"],
				"s_url" => $this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?class=document&action=search&str=".$row["str"]."&parent=".$row["s_parent"]
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"add" => $this->mk_my_orb("change_grp", array("id" => $id)),
			"LINE" => $l,
			"s_log" => $this->mk_my_orb("search_log", array())
		));
		return $this->parse();
	}

	function do_static_search($str, $page,$arr, $s_parent, $t_type)
	{
		$p_arr = $this->get_parent_arr($s_parent);
		$p_arr_str = join(",",$p_arr);
		if ($p_arr_str != "")
		{
			$p_arr_str = " section IN ($p_arr_str) ";
		}

		if ($t_type == 2)	//	k6ik s6nad
		{
			$q_cons.=" (".join(" AND ",map("(content LIKE '%%%s%%')",explode(" ",$str))).")";
		}
		else
		if ($t_type == 3)	//	fraas
		{
			$q_cons.=" (content LIKE '%".$str."%')";
		}
		else
		{
			$q_cons.=" (".join(" OR ",map("(content LIKE '%%%s%%')",explode(" ",$str))).")";
		}

		$q_cons .= " AND lang_id = '".aw_global_get("lang_id")."'";

		if ($p_arr_str != "" && $q_cons != "")
		{
			$q_cons = " AND ".$q_cons;
		}
		$q = "SELECT count(*) as cnt FROM export_content WHERE $p_arr_str $q_cons";
		$cnt = $this->db_fetch_field($q, "cnt");
		
		$this->do_sorting($arr);

		$public_url = $this->get_cval("export::public_symlink_name");

		$q = "SELECT * FROM export_content WHERE $p_arr_str $q_cons ORDER BY modified LIMIT ".$page*PER_PAGE.",".PER_PAGE;
		$this->db_query($q);
		while ($row = $this->db_next())
		{
			preg_match("/\<!-- PAGE_TITLE (.*) \/PAGE_TITLE -->/U", $row["content"], $mt);
			$title = strip_tags($mt[1]);
			if (file_exists($public_url."/".$row['filename']))
			{
				$this->vars(array(
					"section" => $row["filename"],
					"title" => ($title != "" ? $title : $row["filename"]),
					"modified" => $this->time2date($row["modified"],5),
				));
				$mat.=$this->parse("MATCH");
			}
		}
		$this->vars(array(
			"MATCH" => $mat
		));
		$this->vars(array(
			"PAGESELECTOR" => str_replace($this->cfg["baseurl"]."/orb.aw", $this->cfg["form_server"], $this->do_pageselector($cnt,$arr))
		));
		if ($mat != "")
		{
			$this->vars(array(
				"SEARCH" => $this->parse("SEARCH")
			));
		}
		else
		{
			$this->vars(array(
				"NO_RESULTS" => $this->parse("NO_RESULTS")
			));
		}
		return str_replace($this->cfg["baseurl"]."/orb.aw", $this->cfg["form_server"], $this->parse());
	}
}
?>
