<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/otto/otto_prod_search.aw,v 1.22 2008/01/21 14:53:29 dragut Exp $
// otto_prod_search.aw - Otto toodete otsing 
/*

@classinfo syslog_type=ST_OTTO_PROD_SEARCH relationmgr=yes maintainer=dragut

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

	var $search_fld_fin = array(
		array("Naiset", array(318)),
		array("Miehet", array(319)),
		array("Lapset ja teinit", array(1426)),
		array("Kengät", array(142)),
		array("Urheilu", array(1424)),
		array("Sisustus", array(1427))
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
		'1' => array("Naistele", array(83)),
	//	array("Noortele", array(101)),
		'2' => array("Lastele", array(100)),
		'3' => array("Jalatsid", array(2119)),
		'4' => array("Sport & vaba aeg", array(2120)),
		'5' => array("Veelgi soodsam", array(2121))

	);

	var $search_fld_bp_lat = array(
		'1' => array("Sievieðu mode", array(135883)),
		'2' => array("Virieðu mode", array(135836)),
		'3' => array("Bernu un pusaudþu mode", array(135962)),
		'4' => array("Apavi", array(135963)),
		'5' => array("Sporta preces", array(135964)),
		'6' => array("Majturiba", array(135965))
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

		if (aw_global_get("lang_id") == 13)
		{
			$this->search_fld = $this->search_fld_fin;
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
		$sel_folder = safe_array($_GET['search_fld']);
		$this->read_template("minisearch.tpl");

		$res = "";
		foreach ($this->search_fld as $nr => $data)
		{
			$this->vars(array(
				'folder' => $nr,
				'selected' => selected( in_array($nr, $sel_folder) ),
				'folder_name' => $data[0]
			));
			$res .= $this->parse('SEARCH_FOLDER');
		}

		$this->vars(array(
			"str" => $_GET["str"],
			"extsearch" => $this->mk_my_orb("exts"),
			'SEARCH_FOLDER' => $res
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
					"user6" => "%".substr($arr["str"], 0,7)."%",
					"user6" => "%".substr($arr["str"], 0,6)."%",
					"userta2" => "%".$arr["str"]."%",
				)
			)),
		//	"price" => new obj_predicate_not(10000000), // is it really needed here ? --dragut 06.12.2007
			"user3" => new obj_predicate_not(''), // don't show products, which don't have images --dragut
			"userch3" => new obj_predicate_not(1), // this is for bonprix, don't show sold out products, i guess it won't affect otto ... --dragut
		);

		// lets add the category support for minisearch
		$parents = array();
		$awa = new aw_array($_GET["search_fld"]);

		foreach($awa->get() as $fld)
		{

			$flds = $this->search_fld[$fld][1];
			if (is_array($flds))
			{
				foreach($flds as $rfld)
				{
					$ot = new object_tree(array(
						"parent" => $rfld,
						"class_id" => CL_MENU,
						'status' => STAT_ACTIVE
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
			// only for bonprix now:
			if (aw_ini_get("site_id") == 276 || aw_ini_get("site_id") == 277)
			{
				// so, i need all those categories which should be shown under those parents:
				$categories = array();
				$parents_str = implode(',', $parents);

				$sql = "SELECT * FROM otto_imp_t_aw_to_cat WHERE aw_folder IN ($parents_str) AND lang_id=".aw_global_get('lang_id');
				$this->db_query($sql);
				while ($row = $this->db_next())
				{
					$categories[$row['category']] = $row['category'];
				}
				$arr['categories'] = $categories;
			}
			else
			{
			//	$filter["parent"] = $parents;
			}

		}
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
		
		enter_function('otto_prod_search::do_draw_res::gen_page_list');
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
		exit_function('otto_prod_search::do_draw_res::gen_page_list');

		enter_function('otto_prod_search::do_draw_res::slice_out_current_page');
		
		$filter['limit'] = $from.", 10";

		$ol = new object_list($filter);

		// I need sections for those products:
		if ($ol->count())
		{
			$sections = $this->db_fetch_array("
				select
					otto_prod_to_section_lut.product as product_oid,
					otto_prod_to_section_lut.section as section
				from
					otto_prod_to_section_lut
					left join objects on (otto_prod_to_section_lut.section = objects.oid)
				where
					product in (".implode(',', $ol->ids()).") and
					otto_prod_to_section_lut.lang_id = ".aw_global_get('lang_id')." and
					objects.status > 0
			");
		}
		$sections_lut = array();
		foreach ($sections as $value)
		{
			// XXX see sektsioonide raalimine tuleb p6hjalikumalt yle vaadata !!! --dragut
			if (!array_key_exists($value['product_oid'], $sections_lut) && $this->can('view', $value['section']))
			{
				$sections_lut[$value['product_oid']] = $value['section'];
			}
		
		}
		exit_function('otto_prod_search::do_draw_res::slice_out_current_page');

		enter_function('otto_prod_search::do_draw_res::draw_current_page');
		
		$counter = 0;
		$ps = '';
		foreach ($ol->arr() as $product_oid => $product_obj)
		{
			if (!$sections_lut[$product_oid])
			{
				$_s = reset($sections_lut);
			}
			else
			{
				$_s = $sections_lut[$product_oid];
			}
			$prod_inst = $product_obj->instance();
			if (!$sections_lut[$product_oid])
			{
				continue;
			}
			$viewlink = $this->mk_my_orb('show_items', array(
				'section' => $_s,
				'id' => aw_ini_get('shop.prod_fld_path_oc'),
				'oview' => 2,
				'apid' => $product_oid
			), 'shop_order_center');

			$images = explode(',', $product_obj->prop('user3'));
			if ($images[0] == "")
			{
				unset($images[0]);
			}
			$image = html::img(array(
				'url' => $this->get_image_url(reset($images), 2),
				'width' => 80,
				'border' => '0'
			));

			// i need to check for the discount products new price:
/*
			if (!empty($discount_products_data[$data['product_code']]))
			{
				$new_price = $discount_products_data[$data['product_code']]['new_price'];
				$prod_price = number_format(str_replace(',', '', $new_price), 2);
			}
			else
			{
				$prod_price = $prod_inst->get_price($product_obj);
			}
*/
			$prod_price = number_format($product_obj->prop('user14'), 2, '.', ''); // min price

			$this->vars(array(
				'prod_link' => $viewlink,
				'prod_name' => $this->char_replace($product_obj->name()),
				'prod_desc' => $this->char_replace($product_obj->prop('userta2')),
				'prod_price' => $prod_price,
				'pimg' => html::href(array(
					'url' => $viewlink,
					'caption' => $image
				)),
			));
			$ps .= $this->parse('PROD');
			$counter++;
			if ( (($counter % 2) == 0) || ($counter == $total) )
			{
				$this->vars(array(
					'PROD' => $ps
				));
				$ps = '';
				$l .= $this->parse('LINE');
			}
		}
		exit_function('otto_prod_search::do_draw_res::draw_current_page');

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
				"class_id" => CL_SHOP_PRODUCT,
				"user3" => new obj_predicate_not(''), // don't show products, which don't have images --dragut
				"userch3" => new obj_predicate_not(1), // this is for bonprix, don't show sold out products, i guess it won't affect otto ... --dragut
			);
			if ($_GET["prod_name"] != "")
			{
				$product_name = strip_tags($_GET["prod_name"]);

				$filter[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array(
						"name" => "%".$product_name."%",
						"user6" => "%".substr($product_name, 0,7)."%",
						"user6" => "%".substr($product_name, 0,6)."%",
						"userta2" => "%".$product_name."%",
					)
				));
			}
			if ($_GET["prod_color"] != "")
			{
				$filter["user7"] = "%".$_GET["prod_color"]."%";
			}

			if ($_GET["price_from"] > 0 && $_GET["price_to"] > 0)
			{
				$filter["user14"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $_GET["price_from"], $_GET["price_to"]);
			}
			else
			if ($_GET["price_from"] > 0)
			{
				$filter["user14"] = new obj_predicate_compare(OBJ_COMP_GREATER, $_GET["price_from"]);
			}
			else
			if ($_GET["price_to"] > 0)
			{
				$filter["user14"] = new obj_predicate_compare(OBJ_COMP_LESS, $_GET["price_to"]);
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
							"class_id" => CL_MENU,
							'status' => STAT_ACTIVE
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
		
				// so, i need all those categories which should be shown under those parents:
				$categories = array();
				$parents_str = implode(',', $parents);

				$sql = "SELECT * FROM otto_imp_t_aw_to_cat WHERE aw_folder IN ($parents_str) AND lang_id=".aw_global_get('lang_id');
				$this->db_query($sql);
				while ($row = $this->db_next())
				{
					$categories[$row['category']] = $row['category'];
				}
				$arr['categories'] = $categories;

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

	function get_image_url($imnr, $format)
	{
		return aw_ini_get('baseurl').'/vv_product_images/'.$imnr{0}.'/'.$imnr{1}.'/'.$imnr.'_'.$format.'.jpg';
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

	function my_debug($str)
	{
		if ($_SERVER['REMOTE_ADDR'] == '89.219.147.106')
		{
			arr($str);
		}
	}
}
?>
