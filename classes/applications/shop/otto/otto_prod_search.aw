<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/otto/otto_prod_search.aw,v 1.3 2004/09/09 10:57:13 kristo Exp $
// otto_prod_search.aw - Otto toodete otsing 
/*

@classinfo syslog_type=ST_OTTO_PROD_SEARCH relationmgr=yes

@default table=objects
@default group=general

*/

class otto_prod_search extends class_base
{
	var $search_fld = array(
		array("Naiste mood", array(136)),
		array("Ehted ja Kellad", array(137)),
		array("Meeste mood", array(138)),
		array("Lapsed ja teismelised", array(140)),
		array("Jalatsid", array(142)),
		array("Spordirõivad", array(1383)),
		array("Mööbel", array(143)),
		array("Kodusisustus", array(144))	
	);

	var $search_fld_lat = array(
		array("Sieviešu mode", array(135883)),
		array("Viriešu mode", array(135836)),
		array("Bernu un pusaudu mode", array(135962)),
		array("Apavi", array(135963)),
		array("Sporta preces", array(135964)),
		array("Majturiba", array(135965))
	);

	function otto_prod_search()
	{
		$this->init(array(
			"tpldir" => "applications/shop/otto/otto_prod_search",
			"clid" => CL_OTTO_PROD_SEARCH
		));

		if (aw_global_get("lang_id") == 6)
		{
			$this->search_fld = $this->search_fld_lat;
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	////
	// !this must set the content for subtemplates in main.tpl
	// params
	//	inst - instance to set variables to
	//	content_for - array of templates to get content for
	function on_get_subtemplate_content($arr)
	{
		$this->read_template("minisearch.tpl");

		$this->vars(array(
			"str" => $_GET["str"],
			"extsearch" => $this->mk_my_orb("exts")
		));

		$arr["inst"]->vars(array(
			"OTTOSEARCH" => $this->parse()
		));
	}

	/**

		@attrib name=do_minisearch nologin="1"

		@param str optional

	**/
	function do_minisearch($arr)
	{
		// do search then give results to displayer

		// fulltext search - fields are 
		$filter = array(
			"class_id" => CL_SHOP_PRODUCT,
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"name" => "%".$arr["str"]."%",
					"user3" => "%".$arr["str"]."%",
					"userta2" => "%".$arr["str"]."%",
					"user20" => "%".substr($arr["str"], 0,6)."%",
				)
			))
		);

		return $this->do_draw_res($arr, $filter);
	}

	function do_draw_res($arr, $filter)
	{
		$this->read_template("search_res.tpl");

		$ol_cnt = new object_list($filter);

		$names = $ol_cnt->names();
		$tt = $used = array();
		foreach($names as $n_id => $n_n)
		{
			if ($used[$n_n])
			{
				continue;
			}
			$used[$n_n] = $n_id;
			$tt[] = $n_id;
		}
		$ol_cnt = new object_list();
		$ol_cnt->add($tt);

		$total = $ol_cnt->count();
		$per_page = 10;
		$page = $_GET["page"] ? $_GET["page"] : 0;
		$from = $page * $per_page;
		$to = min($total, ($page+1) * $per_page);
		$pages = $total / $per_page;
	
		$ps = array();

		for ($i = 0; $i < $pages; $i++)
		{
			$this->vars(array(
				"p_nr" => $i+1,
				"link" => aw_url_change_var("page", $i)
			));

			if ($i == $page)
			{
				$ps[] = $this->parse("SEL_PAGE");
			}
			else
			{
				$ps[] = $this->parse("PAGE");
			}

			if (($i+1) == $page)
			{
				$this->vars(array(
					"PREV" => $this->parse("PREV")
				));
			}

			if (($i-1) == $page)
			{
				$this->vars(array(
					"NEXT" => $this->parse("NEXT")
				));
			}
		}

		$this->vars(array(
			"PAGE" => join($this->parse("PAGE_SEP"), $ps),
			"SEL_PAGE" => "",
			"total" => $total,
			"cur_page" => ($page+1)
		));

		$ids = array_values($ol_cnt->ids());

		$u_ids = array();
		for($i = $from; $i < $to; $i++)
		{
			$u_ids[] = $ids[$i];
		}
		//$filter["limit"] = $from.",".$to;

		$filter["oid"] = $u_ids;
		if (count($u_ids) < 1)
		{
			$ol = new object_list();
		}
		else
		{
			$ol = new object_list($filter);
		}

		$prod = $ol->begin();
		for ($r = 0; $r < 5; $r++)
		{
			$ps = "";
			for($i = 0; $i < 2; $i++)
			{
				if (!is_object($prod) || !is_oid($prod->id()))
				{
					$i = $r = 100;
					continue;
				}

				$viewlink = $this->mk_my_orb("show_items", array(
					"section" => $prod->parent(),
					"id" => aw_ini_get("shop.prod_fld_path_oc"),
					"page" => $prod->prop("user18"),
					"oview" => 2,
					"apid" => $prod->id()
				), "shop_order_center");

				$imnr = $this->db_fetch_field("SELECT imnr FROM otto_prod_img WHERE pcode = '".$prod->prop("user20")."' AND nr=1","imnr");

				//echo "addar $i $a ".$prod->id()."<br>";
				if ($imnr != "")
				{
					$imnr = html::img(array(
						"url" => "http://image01.otto.de/m2bilder/OttoDe/de_DE/images/formatb/".$imnr.".jpg",
						"width" => 80,
						//"height" => 140,
						"border" => "0"
					));

					// link to prod
					$imnr = html::href(array(
						"url" => $viewlink,
						"caption" => $imnr
					));
				}
				else
				{
					$imnr = html::img(array(
						"url" => aw_ini_get("baseurl")."/automatweb/images/transparent.gif",
						"width" => 80,
						//"height" => 140,
						"border" => "0"
					));

					// link to prod
					$imnr = html::href(array(
						"url" => $viewlink,
						"caption" => $imnr
					));
				}

				$prod_i = $prod->instance();
				$this->vars(array(
					"prod_link" => $viewlink,
					"prod_name" => $prod->name(),
					"prod_desc" => $prod->prop("userta2"),
					"prod_price" => $prod_i->get_price($prod),
					"path" => $prod->path_str(array(
						"to" => aw_ini_get("shop.prod_fld_path"),
						"no_self" => 1,
						"max_len" => 3
					)),
					"pimg" => $imnr
				));

				$ps .= $this->parse("PROD");

				$prod = $ol->next();
			}

			$this->vars(array(
				"PROD" => $ps
			));

			$l .= $this->parse("LINE");
		}

		$this->vars(array(
			"LINE" => $l
		));

		return $this->parse();
	}

	/**

		@attrib name=exts nologin=1

	**/
	function exts($arr)
	{
		$awa = new aw_array($_GET["search_fld"]);

		if ($_GET["dos"])
		{
			$filter = array(
				"class_id" => CL_SHOP_PRODUCT
			);
			if ($_GET["prod_name"] != "")
			{
				$filter["name"] = "%".$_GET["prod_name"]."%";
			}
			if ($_GET["prod_color"] != "")
			{
				$filter["user17"] = "%".$_GET["prod_color"]."%";
			}

			if ($_GET["price_from"] > 0 && $_GET["price_to"] > 0)
			{
				$filter["price"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $_GET["price_from"], $_GET["price_to"]);
			}
			else
			if ($_GET["price_from"] > 0)
			{
				$filter["price"] = new obj_predicate_compare(OBJ_COMP_GREATER, $_GET["price_from"]);
			}
			else
			if ($_GET["price_to"] > 0)
			{
				$filter["price"] = new obj_predicate_compare(OBJ_COMP_LESS, $_GET["price_to"]);
			}

			$parents = array();
			foreach($awa->get() as $fld)
			{
				$flds = $this->search_fld[$fld][1];
				if (is_array($flds))
				{
					foreach($flds as $rfld)
					{
						$ot = new object_tree(array(
							"parent" => $rfld,
							"class_id" => CL_MENU
						));
						$ol = $ot->to_list();
						foreach($ol->ids() as $fldo)
						{
							$parents[$fldo] = $fldo;
						}
					}
				}
			}

			if (count($parents) > 0)
			{
				$filter["parent"] = $parents;
			}

			$str = $this->do_draw_res($arr, $filter);
		}

		$this->read_template("exts.tpl");

		$prcs = $this->make_keys(array(
			"","10", "20","50","100","200","300","500","700","1000","2000","3000","5000","10000","20000"
		));

		$sfs = "";
		foreach($this->search_fld as $nr => $dat)
		{
			$this->vars(array(
				"fld" => $nr,
				"checked" => checked(in_array($nr, $awa->get())),
				"fld_name" => $dat[0]
			));
			$sfs .= $this->parse("SEARCH_FLD");
		}

		$this->vars(array(
			"SEARCH_FLD" => $sfs,
			"s_price_from" => $this->picker($_GET["price_from"], $prcs),
			"s_price_to" => $this->picker($_GET["price_to"], $prcs),
			"s_prod_name" => $_GET["prod_name"],
			"s_prod_color" => $_GET["prod_color"],
			"reforb" => $this->mk_reforb("exts", array("dos" => 1, "reforb" => 0))
		));

		return $this->parse().$str;
	}
}
?>
