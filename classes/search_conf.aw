<?php
lc_load("search_conf");
classload("objects");
classload("config");

global $orb_defs;
$orb_defs["search_conf"] = "xml";

define("PER_PAGE",10);

class search_conf extends aw_template 
{
	function search_conf()
	{
		$this->tpl_init("search_conf");
		$this->db_init();
		lc_load("definition");
		global $lc_search_conf;
		if (is_array($lc_search_conf))
		{
			$this->vars($lc_search_conf);}
	}

	function gen_admin($level)
	{
		global $lang_id,$SITE_ID;

		$ob = new db_objects;
		$c = new db_config;
		$conf = unserialize($c->get_simple_config("search_conf"));

		if (!$level)
		{
			$this->read_template("conf1.tpl");
			$this->vars(array("section"		=> $ob->multiple_option_list($conf[$SITE_ID][$lang_id][sections],$ob->get_list())));
			return $this->parse();
		}
		else
		{
			$sarr = $ob->get_list();

			$this->read_template("conf2.tpl");
			reset($conf[$SITE_ID][$lang_id][sections]);
			while (list(,$v) = each($conf[$SITE_ID][$lang_id][sections]))
			{
				$this->vars(array("section" => $sarr[$v],"section_id" => $v,"section_name" => $conf[$SITE_ID][$lang_id][names][$v],"order" => $conf[$SITE_ID][$lang_id][order][$v]));
				$s.= $this->parse("RUBR");
			}
			$this->vars(array("RUBR" => $s));
			return $this->parse();
		}
	}

	function submit($arr)
	{
		global $lang_id,$SITE_ID;

		extract($arr);

		if (is_array($section))
		{
			reset($section);
			$a = array();
			while (list(,$v) = each($section))
				$a[$v]=$v;
		}

		$c = new db_config;
		$conf = unserialize($c->get_simple_config("search_conf"));

		if (!$level)
		{
			$conf[$SITE_ID][$lang_id][sections] = $a;
			$c->set_simple_config("search_conf",serialize($conf));
			return 1;
		}
		else
		{
			$conf[$SITE_ID][$lang_id][names] = array();
			reset($arr);
			while (list($k,$v) = each($arr))
			{
				if (substr($k,0,3) == "se_")
				{
					$id = substr($k,3);
					$conf[$SITE_ID][$lang_id][names][$id] = $v;
				}
			}

			$conf[$SITE_ID][$lang_id][order] = array();
			reset($arr);
			while (list($k,$v) = each($arr))
			{
				if (substr($k,0,3) == "so_")
				{
					$id = substr($k,3);
					$conf[$SITE_ID][$lang_id][order][$id] = $v;
				}
			}
			$c->set_simple_config("search_conf",serialize($conf));
			return 1;
		}
	}

	function get_search_list()
	{
		$c = new db_config;
		$conf = unserialize($c->get_simple_config("search_conf"));
		if (is_array($conf[$GLOBALS["SITE_ID"]][$GLOBALS["lang_id"]]["names"]))
		{
			// we must sort the damn thing now
			$tmp = $conf[$GLOBALS["SITE_ID"]][$GLOBALS["lang_id"]]["order"];
			if (is_array($tmp))
			{
				asort($tmp,SORT_NUMERIC);
				reset($tmp);
				$ret = array();
				while (list($id,) = each($tmp))
					$ret[$id] = $conf[$GLOBALS["SITE_ID"]][$GLOBALS["lang_id"]]["names"][$id];
			}
			else
				$ret = $conf[$GLOBALS["SITE_ID"]][$GLOBALS["lang_id"]]["names"];
			return $ret;
		}
		else
			return array();
	}

	////
	// !shows the search form
	function search($arr)
	{
		extract($arr);
		$this->read_template("search.tpl");

		classload("keywords");
		$k = new keywords;

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
		$search_list = $this->get_search_list();
		$this->vars(array(
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
			"reforb"	=> $this->mk_reforb("search", array("reforb" => 0,"search" => 1))
		));

		if ($search)
		{
			// and here we do the actual searching bit!

			// assemble the search criteria sql
			if ($sstring_title != "")
			{
				if ($t_type == 1)	//	m6ni s6na
				{
					$q_cons2.="(".join(" OR ",$this->map("(title LIKE '%%%s%%')",explode(" ",$sstring_title))).")";
				}
				else
				if ($t_type == 2)	//	k6ik s6nad
				{
					$q_cons2.="(".join(" AND ",$this->map("(title LIKE '%%%s%%')",explode(" ",$sstring_title))).")";
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
					$q_cons2.="(".join(" OR ",$this->map("(content LIKE '%%%s%%' OR lead LIKE '%%%s%%')",explode(" ",$sstring))).")";
					// failide tabelist otsing
					$q_fcons2="(".join(" OR ",$this->map("(content LIKE '%%%s%%')",explode(" ",$sstring))).")";
					// tabelite tabelist otsing
					$q_tcons2="(".join(" OR ",$this->map("(contents LIKE '%%%s%%')",explode(" ",$sstring))).")";
				}
				else
				if ($c_type == 2)	//	k6ik s6nad
				{
					$q_cons2.="(".join(" AND ",$this->map("(content LIKE '%%%s%%' OR lead LIKE '%%%s%%')",explode(" ",$sstring))).")";
					$q_fcons2="(".join(" AND ",$this->map("(content LIKE '%%%s%%')",explode(" ",$sstring))).")";
					$q_tcons2="(".join(" AND ",$this->map("(contents LIKE '%%%s%%')",explode(" ",$sstring))).")";
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
				$q_cons2.="(".join(" OR ",$this->map("documents.keywords LIKE '%%%s%%'",$keys)).")";
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
			$q_cons = "status = 2 AND parent IN (".join(",",$p_arr).") ";

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

			$this->db_query("SELECT objects.*,documents.* FROM documents LEFT JOIN objects ON objects.oid = documents.docid WHERE $q_cons $ap LIMIT ".($page*PER_PAGE).",".PER_PAGE);
			while ($row = $this->db_next())
			{
				$co = strip_tags($row["content"]);
				$co = preg_replace("/#(.*)#/","",substr($co,0,strpos($co,"\n")));

				$this->vars(array(
					"section" => $row["docid"],
					"title" => $row["title"],
					"modified" => $row["tm"] != "" ? $row["tm"] : $this->time2date($row["modified"],2),
					"content" => $co
				));
				$mat.=$this->parse("MATCH");
			}
			$this->vars(array(
				"MATCH" => $mat
			));
			$this->vars(array(
				"SEARCH" => $this->parse("SEARCH")
			));

			// logime ka et tyyp otsis ja palju leidis.
			$this->do_log($search_list,$s_parent,$t_type,$sstring_title,$sstring,$t2c_log,$sel_keys,$keys,$c2k_log,$cnt);
		}
		return $this->parse();
	}

	function do_log($search_list,$s_parent,$t_type,$sstring_title,$sstring,$t2c_log,$sel_keys,$keys,$c2k_log,$cnt)
	{
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
			$l.=join(",",$this->map("%s",$keys));
		}
		$this->_log("search",sprintf(LC_SEARCH_CONF_LOOK_ANSWER,$sel_parent,$l,$cnt));
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
		if ($GLOBALS["lang_menus"] == 1)
		{
			$ss = " AND objects.lang_id = ".$GLOBALS["lang_id"];
		}
		$this->menucache = array();
		$this->db_query("SELECT objects.oid as oid, objects.parent as parent,objects.last as last,objects.status as status
										 FROM objects 
										 WHERE objects.class_id = 1 AND objects.status = 2 $ss");
		while ($row = $this->db_next())
		{
			$this->menucache[$row[parent]][] = $row;
		}

		// now, make a list of all menus below $parent
		$this->marr = array();
		// list of default documents
		$this->darr = array();
		$this->rec_list($parent);
		return $this->marr;
	}

	function rec_list($parent)
	{
		if (!is_array($this->menucache[$parent]))
			return;

		reset($this->menucache[$parent]);
		while(list(,$v) = each($this->menucache[$parent]))
		{
			if ($v["status"] == 2)
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
}
?>