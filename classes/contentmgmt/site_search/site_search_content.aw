<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_search/site_search_content.aw,v 1.21 2004/11/15 17:09:33 sven Exp $
// site_search_content.aw - Saidi sisu otsing 
/*

@classinfo syslog_type=ST_SITE_SEARCH_CONTENT relationmgr=yes

@default table=objects
@default field=meta
@default method=serialize
@default group=general

@groupinfo static caption="Staatiline otsing"
@groupinfo keywords caption="Märksõnade järgi otsing"

@property do_keyword_search type=checkbox group=keywords field=meta method=serialize ch_value=1
@caption Otsing märksõnadest

@property keyword_search_classes type=select multiple=1 group=keywords field=meta method=serialize
@caption Klassid

@property search_static type=checkbox ch_value=1
@caption Otsing staatilisse koopiasse

@property search_live type=checkbox ch_value=1
@caption Otsing aktiivsest saidist

@property default_grp type=relpicker reltype=RELTYPE_SEARCH_GRP
@caption Vaikimisi otsingu grupp

@property default_order type=select 
@caption Vaikimisi sorteeritakse tulemused

@property per_page type=textbox size=5
@caption Mitu tulemust lehel

property static_gen_repeater type=relpicker reltype=RELTYPE_REPEATER group=static
caption Vali kordus, millega tehakse staatilist koopiat otsingu jaoks

@property reledit type=releditor group=static reltype=RELTYPE_REPEATER use_form=emb rel_id=first
@caption Seos

@property static_gen_link type=text store=no group=static
@caption Staatilise genereerimise link

@reltype REPEATER value=1 clid=CL_RECURRENCE
@caption kordus staatilise koopia genereerimiseks

@reltype SEARCH_GRP value=2 clid=CL_SITE_SEARCH_CONTENT_GRP
@caption otsingu grupp

*/

define("S_ORD_TIME", 1);
define("S_ORD_TITLE", 2);
define("S_ORD_CONTENT", 3);

class site_search_content extends class_base
{
	function site_search_content()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. the default folder does not actually exist, 
		// it just points to where it should be, if it existed
		$this->init(array(
			"tpldir" => "contentmgmt/site_search/site_search_content",
			"clid" => CL_SITE_SEARCH_CONTENT
		));
	}

	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "default_order":
				$data["options"] = array(
					S_ORD_TIME => "Muutmise kuup&auml;eva j&auml;rgi",
					S_ORD_TITLE => "Pealkirja j&auml;rgi",
					S_ORD_CONTENT => "Sisu j&auml;rgi"
				);
				break;
				
			case "static_gen_link":
				$data['value'] = html::href(array(
					"url" => $this->mk_my_orb("generate_static", array("id" => $args["obj_inst"]->id())),
					"caption" => "uuenda staatiline koopia"
				));
				break;
			case "keyword_search_classes":
				foreach (aw_ini_get("classes") as $key => $class)
				{
					if($class["alias"])
					{
						$options[$key] = $class["name"];
					}
				}
				asort($options);
				$data["options"] = $options;
			break;
		};
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "static_gen_repeater":
				// set it to scheduler
				$sc = get_instance("scheduler");
				if ($data["value"])
				{
					$sc->add(array(
						"event" => $this->mk_my_orb("generate_static", array("id" => $args["obj_inst"]->id())),
						"rep_id" => $data["value"]
					));
				}
				else
				{
					$sc->remove(array(
						"event" => $this->mk_my_orb("generate_static", array("id" => $args["obj_inst"]->id())),
					));
				}
				break;

			case "reledit":
				$val = $data["value"];
				$d = $val["start"]["day"];
				$m = $val["start"]["month"];
				$y = $val["start"]["year"];

				$time = $val["time"];
				list($hour,$min) = explode(":",$time);
				if ($hour && $min)
				{
					$stamp = mktime($hour,$min,0,$m,$d,$y);
					
				}
				else
				{
					$stamp = mktime(4,0,0,$m,$d,$y);
				};
				// set it to scheduler
				$sc = get_instance("scheduler");
				$sc->add(array(
					"event" => $this->mk_my_orb("generate_static", array("id" => $args["obj_inst"]->id())),
					"time" => $stamp,
				));
				break;
		}
		return $retval;
	}	

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array("id" => $alias["target"]));
	}

	function get_groups($obj)
	{
		$ret = array();
		$co = $obj->connections_from(array(
			"type" => 2 //RELTYPE_SEARCH_GRP
		));
		foreach($co as $c)
		{
			$c_o = $c->to();
			$ret[] = array(
				"oid" => $c_o->id(),
				"name" => $c_o->name(),
				"jrk" => $c_o->ord()
			);
		}

		usort($ret, create_function('$a,$b', 'if ($a["jrk"] == $b["jrk"]) { return 0;}else if ($a["jrk"] > $b["jrk"]) { return 1;}else{return -1;}'));
		
		$rret = array();
		foreach($ret as $v)
		{
			$rret[$v["oid"]] = $v["name"];
		}

		return $rret;
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = new object($id);
		$this->read_template("search.tpl");
		lc_site_load("search_conf", $this);

		$gr = $this->get_groups($ob);
		if (!isset($group) || !$group)
		{
			$group = $ob->meta("default_grp");
		}

		$s_gr = "";
		foreach($gr as $gid => $gname)
		{
			$this->vars(array(
				"group" => $gid,
				"name" => $gname,
				"checked" => checked($group == $gid)
			));
			$s_gr .= $this->parse("GROUP");
		}

		$this->vars(array(
			"GROUP" => $s_gr,
			"reforb" => $this->mk_reforb("do_search", array("id" => $id, "no_reforb" => 1, "section" => aw_global_get("section"))),
			"str" => (isset($str) ? $str : ""),
		));

		return $this->parse();
	}

	/** this will get called via scheduler to generate the static content to search from 
		
		@attrib name=generate_static params=name nologin="1" default="0"
		
		@param id required
		
		@returns
		
		
		@comment
		parameters:
		id - required, id of the search object

	**/
	function generate_static($arr)
	{
		extract($arr);
		
		// these funcs must write data to a db table (static_content), with structure like this:
		// id, content, url, title, modified, section, lang_id, created_by
		// optional fields - url, section, lang_id, set to NULL if not available
		// if NULL, ignored in searches
		// id - md5 hash of the url, used in identifying whether we have the entry already or not
		// created_by - the crawler's id that created the entry, used when deleting removed files.
		
		// here we can add crawlers for different things. right now, only live site crawler
		$this->do_crawl_live_site($arr);

	}

	function do_crawl_live_site($arr)
	{
		// right. now we will have to crawl the site and write all the info to a database table
		// we use export_lite class for this. 
		$ex = get_instance("export_lite");
		$ex->do_crawl();
	}

	////
	// !searches through static_content db table and returns results
	// params:
	//	str - string to search
	//	menus - the menus to search under
	function fetch_static_search_results($arr)
	{
		// rewrite fucked-up letters
		// IE
		$arr["str"] = str_replace(chr(0x9a), "&#0352;", $arr["str"]);
		$arr["str"] = str_replace(chr(0x8a), "&#0352;", $arr["str"]);
		$arr["str"] = str_replace("%9A", "&#0352;", $arr["str"]);
		$arr["str"] = str_replace("%8A", "&#0352;", $arr["str"]);
		
		// mozilla
		$arr["str"] = str_replace(chr(0xa8), "&#0352;", $arr["str"]);
		$arr["str"] = str_replace("%A8", "&#0352;", $arr["str"]);
		$arr["str"] = str_replace(chr(0xa6), "&#0352;", $arr["str"]);
		$arr["str"] = str_replace("%A6", "&#0352;", $arr["str"]);
		
		extract($arr);
	
		$ret = array();

		$ams = new aw_array($menus);	

		$this->quote($str);
		$sql = "
			SELECT 
				url, 
				title, 
				modified,
				content
			FROM 
				static_content 
			WHERE 
				content like '%".$str."%' AND 
				section IN (".$ams->to_sql().") AND
				lang_id = '".aw_global_get("lang_id")."'
		";
		$this->db_query($sql);
		while ($row = $this->db_next())
		{
			$ret[] = array(
				"url" => $row["url"],
				"title" => $row["title"],
				"modified" => $row["modified"],
				"content" => $row["content"]
			);
		}
		return $ret;
	}

	////
	// !searches through the live site database and returns results. just documents
	// it does not even try to be clever - if you want to search everything, then use static search
	// params:
	//	str - string to search
	//	menus - the menus to search under
	function fetch_live_search_results($arr)
	{
		extract($arr);
	
		$ret = array();

		$ams = new aw_array($menus);	

		$this->quote($str);
		$sql = "
			SELECT 
				d.docid as docid, 
				d.title as title, 
				o.modified as modified,
				d.lead as lead,
				d.content as content
			 FROM 
				objects o  
				LEFT JOIN documents d ON o.brother_of = d.docid
			WHERE 
				(
					d.content like '%$str%' OR
					d.title like '%$str%' OR
					d.lead like '%$str%' OR
					d.author like '%$str%' OR
					d.photos like '%$str%' OR
					d.dcache like '%$str%'
				) AND 
				o.parent IN (".$ams->to_sql().") AND
				o.status = 2 AND
				o.lang_id = '".aw_global_get("lang_id")."' AND
				o.site_id = '".aw_ini_get("site_id")."' AND
				o.class_id IN (".CL_DOCUMENT.",".CL_BROTHER_DOCUMENT.",".CL_PERIODIC_SECTION.")
		";
		$this->db_query($sql);
		while ($row = $this->db_next())
		{
			$ret[] = array(
				"url" => $this->cfg["baseurl"]."/".$row["docid"],
				"title" => $row["title"],
				"modified" => $row["modified"],
				"content" => $row["content"],
				"lead" => $row["lead"]
			);
		}
			
		if($arr["obj"]->prop("do_keyword_search"))
		{
			$keyresults = $this->search_keywords($str, $menus, $arr["obj"]);
			if($ret && $keyresults)
			{
				$ret = $ret + $keyresults;
			}
			elseif($keyresults)
			{
				$ret = $keyresults;
			}
		//arr($keyresults);		
				
		}
		return $ret;
	}

	////
	// !merges two result sets together and returns the merged set. results are merged based on titles
	function merge_result_sets($orig, $add)
	{
		$lut = array();
		foreach($orig as $i)
		{
			$lut[strtolower(trim(strip_tags($i["title"])))] = 1;
		}

		$ret = $orig;
		foreach($add as $item)
		{
			if (!isset($lut[strtolower(trim(strip_tags($item["title"])))]))
			{
				$ret[] = $item;
			}
		}

		return $ret;
	}
	
	function search_keywords($str, $menus, $obj)
	{
		$keyword_list = new object_list(array(
			"class_id" => CL_KEYWORD,
			"name" => "%$str%",
		));
			
		//If keyword not found, no point to process it futher
		if($keyword_list->count() == 0)
		{
			return;
		}
		
		$classes = $obj->prop("keyword_search_classes");
		$keyword_aliased_conns = new connection();
		
		$keyword_aliased_conns = $keyword_aliased_conns->find(array(
			"from" => $keyword_list->ids(),
			"to.class_id" => $classes,
		));
		
		if(!$keyword_aliased_conns)
		{
			return;
		}	
		foreach($keyword_aliased_conns as $conn)
		{
			$ids_list[] = $conn["from"];
		}
		
		$aliased_docs_conns = new connection();
		$aliased_docs_conns = $aliased_docs_conns->find(array(
			"to" => $ids_list,
			"from.class_id" => CL_DOCUMENT,
		));
		
		foreach ($aliased_docs_conns as $conn)
		{
			$doc_ids[] = $conn["from"];	
		}
		
		$ol = new object_list(array(
			"oid" => $doc_ids,
			"parent" => $menus,
		));
		$ret = array();	
		foreach ($ol->arr() as $obj)
		{
			$ret[] = array(
				"url" => $this->cfg["baseurl"]."/".$obj->id(),
				"title" => $obj->name(),
				"modified" => $obj->modified("modified"),
				"content" => $obj->prop("content"),
				"lead" => $obj->prop("lead"),
			);
		}
		return $ret;
	}
	
	
	////
	// !returns an array of results matching the search
	// params:
	//	obj - object instance of the search object
	//	str - the search string
	//	group - the group to search from
	function fetch_search_results($arr)
	{
		extract($arr);
		$g = get_instance("contentmgmt/site_search/site_search_content_grp");
		$ms = $g->get_menus(array("id" => $group));

		$ret = array();
		if ($obj->meta("search_static"))
		{
			$ret = $this->fetch_static_search_results(array(
				"menus" => $ms,
				"str" => $str
			));
		}

		if ($obj->meta("search_live"))
		{
			$ret = $this->merge_result_sets($ret, $this->fetch_live_search_results(array(
				"menus" => $ms,
				"str" => $str,
				"obj" => $arr["obj"],
			)));
		}
		// make sure we only get unique titles in results
		$_ret = array();
		foreach($ret as $d)
		{
			$_ret[$d["title"]] = $d;
		}
		
		return $_ret;
	}

	function _sort_title($a, $b)
	{
		return strcmp($a["title"], $b["title"]);
	}

	function _sort_time($a, $b)
	{
		if ($a["modified"] == $b["modified"]) 
		{
        	return 0;
		}
		return ($a["modified"] > $b["modified"]) ? -1 : 1;
	}

	function _sort_content($a, $b)
	{
		return strcmp($a["content"], $b["content"]);
	}

	////
	// !sorts the search results
	// params:
	//	results - array of search results, must be reference
	//	sort_by - the order to sort by
	function sort_results($arr)
	{
		switch($arr["sort_by"])
		{
			case S_ORD_TITLE:
				usort($arr["results"], array(&$this, "_sort_title"));
				break;

			case S_ORD_CONTENT:
				usort($arr["results"], array(&$this, "_sort_content"));
				break;

			case S_ORD_TIME:
			default:
				usort($arr["results"], array(&$this, "_sort_time"));
				break;
		}
	}

	////
	// !displays sorting links in the currently loaded search results template
	// parameters:
	//	params - array of parameters to use to make the sort link
	//	cur_page - the currently selected page
	function display_sorting_links($arr)
	{
		extract($arr);

		$params["page"] = $arr["cur_page"];

		$params1 = $params2 = $params3 = $params;
		$params1["sort_by"] = S_ORD_TIME;
		$params2["sort_by"] = S_ORD_TITLE;
		$params3["sort_by"] = S_ORD_CONTENT;

		$this->vars(array(
			"sort_modified" => $this->mk_my_orb("do_search", $params1),
			"sort_title" => $this->mk_my_orb("do_search", $params2),
			"sort_content" => $this->mk_my_orb("do_search", $params3),
		));

		$so_mod = "";
		if ($params["sort_by"] == S_ORD_TIME)
		{
			$so_mod = $this->parse("SORT_MODIFIED_SEL");
		}
		else
		{
			$so_mod = $this->parse("SORT_MODIFIED");
		}

		$so_title = "";
		if ($params["sort_by"] == S_ORD_TITLE)
		{
			$so_title = $this->parse("SORT_TITLE_SEL");
		}
		else
		{
			$so_title = $this->parse("SORT_TITLE");
		}

		$so_ct = "";
		if ($params["sort_by"] == S_ORD_CONTENT)
		{
			$so_ct = $this->parse("SORT_CONTENT_SEL");
		}
		else
		{
			$so_ct = $this->parse("SORT_CONTENT");
		}
		$this->vars(array(
			"SORT_MODIFIED" => $so_mod,
			"SORT_MODIFIED_SEL" => "",
			"SORT_CONTENT" => $so_ct,
			"SORT_CONTENT_SEL" => "",
			"SORT_TITLE" => $so_title,
			"SORT_TITLE_SEL" => "",
		));
	}

	////
	// !displays pageselector - list of pages and next/back buttons, assumes that a template with the subs is loaded
	// parameters:
	//	num_results - the number of total results
	//	cur_page - the current page in the results
	//	per_page - number of results per page
	//	params - search params, to make the next page link from
	function display_pageselector($arr)
	{
		$page = $arr["cur_page"];
		$cnt = $arr["num_results"];
		$per_page = $arr["per_page"];
		$params = $arr["params"];

		$num_pages = ($cnt / $per_page);

		$pg = "";
		$prev = "";
		$nxt = "";

		for ($i=0; $i < $num_pages; $i++)
		{
			$params["page"] = $i;
			$this->vars(array(
				"page" => $this->mk_my_orb("do_search", $params),
				"page_from" => $i*$per_page,
				"page_to" => min(($i+1)*$per_page,$cnt)
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
		$params["page"] = max((int)$page-1,0);
		$this->vars(array(
			"prev" => $this->mk_my_orb("do_search", $params)
		));

		$params["page"] = min((int)$page+1,$num_pages-1);
		$this->vars(array(
			"next" => $this->mk_my_orb("do_search", $params)
		));
		if ($page > 0)
		{
			$prev = $this->parse("PREVIOUS");
		}
		
		if (((int)$page) < ($num_pages-1))
		{
			$nxt = $this->parse("NEXT");
		}
		$this->vars(array(
			"PREVIOUS" => $prev, 
			"NEXT" => $nxt,
			"PAGE" => $pg, 
			"SEL_PAGE" => ""
		));
		$this->vars(array(
			"PAGESELECTOR" => $this->parse("PAGESELECTOR"),
			"count" => $cnt
		));

		$this->display_sorting_links(array(
			"cur_page" => $arr["cur_page"],
			"params" => $arr["params"]
		));
	}

	function _get_content($ct)
	{
		return "";
		$co = trim(strip_tags($ct));
		$co = substr($co,strpos($co,"\n"));
		$co = trim($co);
		$co = preg_replace("/#(.*)#/","",substr($co,0,strpos($co,"\n")));
		return $co;
	}

	////
	// !displays results on the selected page, assumes template is already loaded
	// parameters:
	//	results - array of all the results
	//	page - the current page
	//	per_page - number of results to show
	function display_result_page($arr)
	{
		extract($arr);
		
		// calc the offsets in the array 
		$from = $page * $per_page;
		$to = ($page+1) * $per_page;

		$res = "";

		$tr = array();
		foreach($results as $result)
		{
			$tr[] = $result;
		}
		$results = $tr;
		
		for ($i = $from; $i < $to; $i++)
		{
			if (!isset($results[$i]))
			{
				continue;
			}

			$this->vars(array(
				"link" => $results[$i]["url"],
				"title" => $results[$i]["title"],
				"modified" => date("d.m.Y", $results[$i]["modified"]),
				"content" => $this->_get_content($results[$i]["content"]),
				"lead" => preg_replace("/#(.*)#/","",$results[$i]["lead"])
			));
			$res .= $this->parse("MATCH");
		}

		$this->vars(array(
			"MATCH" => $res
		));
	}

	////
	// !generates the html for search results
	// parameters:
	//	sort_by - how to sort the results
	//	results - array of results to display
	//	str - the search string
	//	page - the page of the result set to display
	//	per_page - number of results to show per page,
	//	params - the parameters to use to make the next/prev page links
	function display_results($arr)
	{
		extract($arr);

		lc_site_load("search_conf", &$this);
		
		if (count($results) < 1)
		{
			$this->read_template("no_results.tpl");
			$this->vars(array(
				"str" => $str
			));
			return $this->parse();
		}

		$this->read_template("search_results.tpl");

		$this->sort_results(array(
			"results" => &$results, 
			"sort_by" => $sort_by
		));

		$this->display_pageselector(array(
			"num_results" => count($results),
			"cur_page" => $page,
			"per_page" => $per_page,
			"params" => $params
		));

		$this->display_result_page(array(
			"results" => $results,
			"page" => $page,
			"per_page" => $per_page
		));

		return $this->parse();
	}

	//// 
	// !sets the default values to $arr
	function set_defaults($arr)
	{
		$o = obj($arr["id"]);

		if (!isset($arr["group"]) || !$arr["group"])
		{
			$arr["group"] = $o->meta("default_grp");
		}

		if (!isset($arr["sort_by"]) || !$arr["sort_by"])
		{
			$arr["sort_by"] = $o->meta("default_order");
		}
		if (!$arr["sort_by"])
		{
			$arr["sort_by"] = S_ORD_TIME;
		}

		if (!isset($arr["page"]) || !$arr["page"])
		{
			$arr["page"] = 0;
		}
		
		return $arr;
	}

	/**  
		
		@attrib name=do_search params=name nologin="1" default="0"
		
		@param id required
		@param group optional
		@param page optional
		@param str optional
		@param sort_by optional
		
		@returns
		
		
		@comment

	**/
	function do_search($arr)
	{
		extract($this->set_defaults($arr));
		$o = obj($id);

		$ret = $this->show(array(
			"id" => $id,
			"str" => $str,
			"group" => $group
		));

		$results = array();

		if ($str != "" && $group)
		{
			$results = $this->fetch_search_results(array(
				"obj" => $o,
				"str" => $str,
				"group" => $group
			));
		}

		$ret .= $this->display_results(array(
			"results" => $results,
			"obj" => $o, 
			"str" => $str, 
			"group" => $group,
			"sort_by" => $sort_by,
			"str" => $str,
			"per_page" => ($o->meta("per_page") ? $o->meta("per_page") : 20),
			"params" => array("id" => $id, "str" => $str, "sort_by" => $sort_by, "group" => $group, "section" => aw_global_get("section")),
			"page" => $page
		));
		
		return $ret;
	}
}
?>
