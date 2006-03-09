<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/otto/otto_prod_search.aw,v 1.14 2006/03/09 20:55:39 dragut Exp $
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
		//array("Eripakkumised", array(149113))
	);

	var $search_fld_lat = array(
		array("Sievieðu mode", array(135883)),
		array("Virieðu mode", array(135836)),
		array("Bernu un pusaudþu mode", array(135962)),
		array("Apavi", array(135963)),
		array("Sporta preces", array(135964)),
		array("Majturiba", array(135965))
	);

	var $search_fld_bp_ee = array(
//		array("Naistele", array(83)),
//		array("Noortele", array(101)),
//		array("Lastele", array(100)),
//		array("Jalatsid", array(2119)),
//		array("Sport & vaba aeg", array(2120)),
//		array("Veelgi soodsam", array(2121))
	);

	var $search_fld_bp_lat = array(
//		array("Sievieðu mode", array(135883)),
//		array("Virieðu mode", array(135836)),
//		array("Bernu un pusaudþu mode", array(135962)),
//		array("Apavi", array(135963)),
//		array("Sporta preces", array(135964)),
//		array("Majturiba", array(135965))
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

		if ( (aw_ini_get("site_id") == 276) || (aw_ini_get("site_id") == 277) )
		{
			if (aw_global_get("lang_id") == 1)
			{
				$this->search_fld = $this->search_fld_bp_ee;
			}
			else
			{
				$this->search_fld = $this->search_fld_bp_lat;
			}
		}	
	}
/*
// if those fn.-s will be needed, comment them in
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
*/
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

		$arr["str"] = trim(str_replace(" ", "", $arr["str"]));

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
			)),
			"price" => new obj_predicate_not(10000000),
//			"user18" => $this->_get_pgs()
		);

		return $this->do_draw_res($arr, $filter);
	}

	function do_draw_res($arr, $filter)
	{
		enter_function('otto_prod_search::do_draw_res');
		$this->read_template("search_res.tpl");

		$lang_id = aw_global_get('lang_id');
		$prod_inst = get_instance(CL_SHOP_PRODUCT);
		$import_object = obj(aw_ini_get("otto.import"));

		enter_function('otto_prod_search::do_draw_res::create_object_list');
		$ol_cnt = new object_list($filter);
		exit_function('otto_prod_search::do_draw_res::create_object_list');

		// filter by name does NOT work. at all. must filter by image number. 
		// so write some badass sql here
		enter_function('otto_prod_search::do_draw_res::imnr_and_objects_and_prods_data');
		$jstr = trim(join(",", $ol_cnt->ids()));
		if ($jstr == "")
		{
			$jstr = "-1";
		}

		$q = "
			SELECT 
				o.brother_of as oid,
				pi.imnr as imnr,
				pr.user20 as product_code,
				pr.user11 as user11,
				pr.user18 as user18
			FROM 
				aw_shop_products pr
				LEFT JOIN otto_prod_img pi ON (pi.pcode = pr.user20 AND pi.nr = 1)
				LEFT JOIN objects o ON pr.aw_oid = o.oid 
			WHERE
				o.status > 0 AND o.lang_id = ".$lang_id." AND
				pr.aw_oid IN (".$jstr.") AND
				pi.imnr IS NOT NULL
			GROUP BY
				pi.imnr
		";
		$this->db_query($q);


		$ol_cnt = new object_list();
		$product_data = array();

		while ($row = $this->db_next())
		{
			$product_data[$row['oid']] = $row;
			$product_codes[$row['oid']] = $row['product_code'];
		}

		exit_function('otto_prod_search::do_draw_res::imnr_and_objects_and_prods_data');

		enter_function('otto_prod_search::do_draw_res::prod_to_cat_lut');
		$product_codes_str = implode(',', map("'%s'", $product_codes));
		$query = "SELECT * FROM otto_imp_t_prod_to_cat WHERE product_code IN ($product_codes_str) AND lang_id=$lang_id";
		$this->db_query($query);

		// products to categories 
		$prod_to_cat_lut = array();
		while ($row = $this->db_next())
		{
			$prod_to_cat_lut[$row['product_code']][] = $row['category'];
		}
		exit_function('otto_prod_search::do_draw_res::prod_to_cat_lut');

		enter_function('otto_prod_search::do_draw_res::discount_prods');
		// discount products parents:
		$discount_products_parents = explode(',', $import_object->prop('discount_products_parents'));
	
		// get discount products data:
		$discount_products_data = array();
		$query = "SELECT * FROM bp_discount_products WHERE product_code IN ($product_codes_str) AND amount>0 AND lang_id=$lang_id ORDER BY size DESC";
		$this->db_query($query);
		while ($row = $this->db_next())
		{
			$discount_products_data[$row['product_code']] = $row;
		}
		exit_function('otto_prod_search::do_draw_res::discount_prods');

		enter_function('otto_prod_search::do_draw_res::get_products_to_show');
		foreach ($product_data as $product_oid => $prod_data)
		{
			$product_id = $product_oid;
			$product_code = $product_data[$product_id]['product_code'];
			$section = NULL;
			if (!empty($prod_to_cat_lut[$product_code]))
			{
				enter_function('prod_to_cat_lut');
				$categories_str = implode(',', map("'%s'", $prod_to_cat_lut[$product_code]));
				$sections = $this->db_fetch_array("select aw_folder from otto_imp_t_aw_to_cat where category in ($categories_str) and lang_id=$lang_id");
				foreach ($sections as $section_data)
				{
					if (in_array($section_data['aw_folder'], $discount_products_parents))
					{
						if (!empty($discount_products_data[$product_code]))
						{
							$section = $section_data['aw_folder'];
						}
					}
					else
					{
						$section = $section_data['aw_folder'];	
					}	
				}
				exit_function('prod_to_cat_lut');

			}
			else
			{
				$category = $prod_data['user11'];
			
				if (empty($category))
				{
					$category = $prod_data['user18'];
				}
		
				$section = $this->db_fetch_field("select aw_folder from otto_imp_t_aw_to_cat where category='$category' and lang_id=$lang_id", "aw_folder");
			}

			if (!empty($section))
			{
				$data = array(
				//	'product_obj' => $product_obj,
					'product_oid' => $product_oid,
					'section' => $section,
					'imnr' => $product_data[$product_id]['imnr'],
					'product_code' => $product_code
				);
			//	$image_data[$product_data[$product_id]['imnr']] = $data;
				$image_data[$prod_data['imnr']] = $data;
			}
		}

		exit_function('otto_prod_search::do_draw_res::get_products_to_show');

		enter_function('otto_prod_search::do_draw_res::gen_page_list');
		$total = count($image_data);

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
		exit_function('otto_prod_search::do_draw_res::gen_page_list');
/*

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

		$used_ims = array();
*/
		enter_function('otto_prod_search::do_draw_res::slice_out_current_page');
		$image_data = array_slice($image_data, $from, 10); 
		exit_function('otto_prod_search::do_draw_res::slice_out_current_page');

		// lets take the first picture
		$data = reset($image_data);
		enter_function('otto_prod_search::do_draw_res::draw_current_page');
		for ($j = 0; $j < 5; $j++)
		{
			$ps = "";
			for ($i = 0; $i < 2; $i++)
			{
				if ($data !== false)
				{
				//	$this->vars($data);
					$product_obj = new object($data['product_oid']);
				//	$product_obj = $data['product_obj'];

					$prod_inst = $product_obj->instance();
					enter_function('otto_prod_search::do_draw_res::draw_current_page::viewlink');

					$viewlink = $this->mk_my_orb('show_items', array(
						'section' => $data['section'],
						'id' => aw_ini_get('shop.prod_fld_path_oc'),
						'oview' => 2,
						'apid' => $product_obj->id()
					), 'shop_order_center');
					exit_function('otto_prod_search::do_draw_res::draw_current_page::viewlink');
					enter_function('otto_prod_search::do_draw_res::draw_current_page::img_tag');
					$image = html::img(array(
						'url' => $this->get_img_url($data['imnr'], 'formatb'),
						'width' => 80,
						'border' => '0'
					));
					exit_function('otto_prod_search::do_draw_res::draw_current_page::img_tag');

					// i need to check for the discount products new price:
					enter_function('otto_prod_search::do_draw_res::draw_current_page::prod_price');

					if (!empty($discount_products_data[$data['product_code']]))
					{
						$new_price = $discount_products_data[$data['product_code']]['new_price'];
						$prod_price = number_format(str_replace(',', '', $new_price), 2);
					}
					else
					{
						$prod_price = $prod_inst->get_price($product_obj);
					}
					exit_function('otto_prod_search::do_draw_res::draw_current_page::prod_price');

					enter_function('otto_prod_search::do_draw_res::draw_current_page::prod_vars');
					$this->vars(array(
						'prod_link' => $viewlink,
						'prod_name' => $this->char_replace($product_obj->name()),
						'prod_desc' => $this->char_replace($product_obj->prop(userta2)),
						'prod_price' => $prod_price,
						'pimg' => html::href(array(
							'url' => $viewlink,
							'caption' => $image
						)),
					));
					exit_function('otto_prod_search::do_draw_res::draw_current_page::prod_vars');
					$ps .= $this->parse('PROD');
				}

				$data = next($image_data);
			}
			$this->vars(array(
				'PROD' => $ps
			));
			
			$l .= $this->parse('LINE');
		}
		exit_function('otto_prod_search::do_draw_res::draw_current_page');

/*
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
				
				$product_code = $prod->prop('user20');
				$lang_id = aw_global_get('lang_id');

				if ($_GET['dragut'])
				{
					$cat = $this->db_fetch_field("select category from otto_imp_t_prod_to_cat where product_code='$product_code' and lang_id=$lang_id", "category");
					if (empty($cat)){
						$cat = $prod->prop('user11');
					}
					$section = $this->db_fetch_field("select aw_folder from otto_imp_t_aw_to_cat where category='$cat' and lang_id=$lang_id", "aw_folder");
				}
	
				$viewlink = $this->mk_my_orb("show_items", array(
//					"section" => $prod->parent(),
					'section' => (empty($section)) ? $prod->parent() : $section,
					"id" => aw_ini_get("shop.prod_fld_path_oc"),
					"page" => $prod->prop("user18"),
					"oview" => 2,
					"apid" => $prod->id()
				), "shop_order_center");

				$imnr = $this->db_fetch_field("SELECT imnr FROM otto_prod_img WHERE pcode = '".$prod->prop("user20")."' AND nr = 1","imnr");

				if (isset($used_ims[$imnr]))
				{
					$prod = $ol->next();
					if ($i > 0)
					{
						$i--;
					}
					else
					{
						$i = 1; 
						$r--;
					}
					continue;
				}
				$used_ims[$imnr] = 1;

				//echo "addar $i $a ".$prod->id()."<br>";

				if ($imnr != "")
				{
					$imnr = html::img(array(
						"url" => $this->get_img_url($imnr, "formatb"),
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
					//$i--;
					//$prod = $ol->next();
					//continue;
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
				//echo "q = $q , f = $folder";
				// start PATH
				if (is_oid($folder) && $this->can("view", $folder))
				{
					$fo = obj($folder);
					$path = $fo->path_str(array(
						"to" => aw_ini_get("shop.prod_fld_path"),
						"no_self" => 1,
						"max_len" => 3
					));
				}
				else
				{
					$path = $prod->path_str(array(
						"to" => aw_ini_get("shop.prod_fld_path"),
						"no_self" => 1,
						"max_len" => 3
					));
				}
				// end PATH

				$prod_i = $prod->instance();
				$this->vars(array(
					"prod_link" => $folder ? aw_url_change_var("section", $folder, $viewlink) : $viewlink,
					"prod_name" => $this->char_replace($prod->name()),
					"prod_desc" => $this->char_replace($prod->prop("userta2")),
					"prod_price" => $prod_i->get_price($prod),
					"path" => $path,
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
*/

		$this->vars(array(
			"LINE" => $l
		));
		exit_function('otto_prod_search::do_draw_res');
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

			$filter["user18"] = $this->_get_pgs();
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

	function _get_pgs()
	{
		$ret = array();
		$this->db_query("SELECT distinct(pg) as pg FROM otto_imp_t_p2p WHERE lang_id = ".aw_global_get("lang_id"));
		while ($row = $this->db_next())
		{
			$ret[$row["pg"]] = $row["pg"];
		}
		return $ret;
	}

	function get_img_url($imnr,$f = "formatb")
	{
		if (aw_ini_get("site_id") == 276 || aw_ini_get("site_id") == 277)
		{
//			list($i, $f) = explode("_", $imnr);
			$i = substr($imnr, 0, strrpos($imnr, "_"));
			$f = substr($imnr, strrpos($imnr, "_") + 1 );
			$img_location = "http://image01.otto.de/bonprixbilder/varianten/artikel_ansicht/$f/$i.jpg";

			// [XXX-dragut]
			// kontrollin, kas sellelt aadressilt pilt tuleb, ja kui ei tule, siis panen teise
			// aadressi. Millegi präast tuleb sealt aga väike pilt ja paistab, et suur pilt tuleb
			// kui l6ppu _039 asemele panna _280, so kui midagi katki läheb, siis v6imalik, et kala
			// tuleb siit sisse
			$img_info = getimagesize($img_location);
			if (empty($img_info))
			{
		//		$img_location = "http://www.bonprix.pl/fotki/link/images/all/".$i."_280.jpg";
				$img_location = "http://www.bonprix.pl/fotki/link/images/all/".$i."_120.jpg";

			}

			return $img_location;

		}

		return "http://image01.otto.de/pool/OttoDe/de_DE/images/$f/".$imnr.".jpg";
	}

	function char_replace($str)
	{
		if ($GLOBALS["dbg"])
		{
			echo "str = $str <br>";
			for($i = 0; $i < strlen($str); $i++)
			{
				echo "$i: ".$str{$i}." nr = ".ord($str{$i})." <br>";
			}
			echo "-------------------- <br>";
			/*for ($i = 0; $i < 255; $i++)
			{
				echo "i = $i chr = ".chr($i)." <br>";
			}*/
		}
		$str = str_replace(chr(200), "\"", $str);
		$str = str_replace(chr(199), "\"", $str);
		$str = str_replace(chr(208), "-", $str);
		$str = str_replace(chr(236), chr(158), $str);
		$str = str_replace(chr(161), chr(176), $str);
		$str = str_replace(chr(202), "", $str);
		$str = str_replace(chr(128), "&Auml;", $str);
		$str = str_replace(chr(158), "&#381;", $str);
		$str = str_replace(chr(133), "&Ouml;", $str);


		return $str;
	}
	
	function get_section_by_pcode($args)
	{
		$product_code = $args['product_code'];
		$product_obj = $args['product_obj'];
		$lang_id = aw_global_get('lang_id');

		$cat = $this->db_fetch_field("select category from otto_imp_t_prod_to_cat where product_code='$product_code' and lang_id=$lang_id", "category");

		// kui kategooriaid (user11/extrafld) oli rohkem, ehk komadega eraldatult pandud, siis peaks nad olema
		// otto_imp_t_prod_to_cat tabelis kirjas, kui ei ole, siis 2kki pole seda toodet uuesti imporditud, aga 
		// vanast j2rjest on olemas extrafld v2lja sisu, kus siis oli ainult 1 kategooria:
		if (empty($cat))
		{
			$cat = $product_obj->prop('user11');
		}

		// ja kui seal extrafld e. user11 v2ljas ei olnud midagi, siis 2kki on kategooriaks hoopis leht, kus toode asub:
		if (empty($cat))
		{
			$cat = $product_obj->prop('user18');
		}


		$section = $this->db_fetch_field("select aw_folder from otto_imp_t_aw_to_cat where category='$cat' and lang_id=$lang_id", "aw_folder");
		return $section;

	}
}
?>
