<?php
// gallery.aw - gallery management
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/gallery/gallery_v2.aw,v 1.1 2003/03/13 13:49:39 kristo Exp $

/*

@classinfo syslog_type=ST_GALLERY relationmgr=yes

@groupinfo general caption=Üldine
@groupinfo page_1 caption=Lehek&uuml;lg&nbsp;1
@groupinfo page_2 caption=Lehek&uuml;lg&nbsp;2
@groupinfo page_3 caption=Lehek&uuml;lg&nbsp;3
@groupinfo page_4 caption=Lehek&uuml;lg&nbsp;4
@groupinfo page_5 caption=Lehek&uuml;lg&nbsp;5
@groupinfo page_6 caption=Lehek&uuml;lg&nbsp;6
@groupinfo page_7 caption=Lehek&uuml;lg&nbsp;7
@groupinfo page_8 caption=Lehek&uuml;lg&nbsp;8
@groupinfo page_9 caption=Lehek&uuml;lg&nbsp;9

@groupinfo preview caption=Eelvaade

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property conf_id type=text size=3 field=meta method=serialize
@caption Konfiguratsioon:

@property num_pages type=textbox size=3 field=meta method=serialize
@caption Mitu lehte:

@property def_layout type=relpicker field=meta method=serialize reltype=RELATION_LAYOUT
@caption Default layout:

@property pg_1_content type=text field=meta method=serialize group=page_1 no_caption=1
@property pg_2_content type=text field=meta method=serialize group=page_2 no_caption=1
@property pg_3_content type=text field=meta method=serialize group=page_3 no_caption=1
@property pg_4_content type=text field=meta method=serialize group=page_4 no_caption=1
@property pg_5_content type=text field=meta method=serialize group=page_5 no_caption=1
@property pg_6_content type=text field=meta method=serialize group=page_6 no_caption=1
@property pg_7_content type=text field=meta method=serialize group=page_7 no_caption=1
@property pg_8_content type=text field=meta method=serialize group=page_8 no_caption=1
@property pg_9_content type=text field=meta method=serialize group=page_9 no_caption=1

@property preview type=text field=meta method=serialize group=preview no_caption=1


*/

define("RELATION_LAYOUT",1);

classload("image");
class gallery_v2 extends class_base
{
	function gallery_v2($id = 0)
	{
		$this->init(array(
			"tpldir" => "gallery",
			"clid" => CL_GALLERY_V2
		));
		$this->sub_merge = 1;
	}

	function parse_alias($args = array())
	{
		extract($args);
		return $this->show(array(
			"oid" => $alias["target"]
		));
	}
		
	function get_property(&$arr)
	{
		$prop =& $arr['prop'];
		if ($prop['name'] == "preview")
		{
			$prop['value'] = $this->show(array(
				"oid" => $arr['obj']['oid']
			));
		}
		else
		if ($prop['name'] == "conf_id")
		{
			if (!($pt = $arr['obj']['parent']))
			{
				$pt = $arr['request']['parent'];
			}
			$cid = $this->_get_conf_for_folder($pt);
			if (!$cid)
			{
				$prop['value'] = "Sellele kataloogile pole konfiguratsiooni valitud!";
			}
			else
			{
				$prop['value'] = html::href(array(
					'url' => $this->mk_my_orb('change', array('id' => $cid), 'gallery_conf'),
					'caption' => 'Muuda'
				));
			}
		}
		else
		if (substr($prop['name'], 0, 3) == 'pg_')
		{
			$prop['value'] = $this->_get_edit_page(array(
				"oid" => $arr['obj']['oid'],
				"page" => (int)substr($prop['name'], 3, 1)
			));
		}
		return PROP_OK;
	}

	function _get_conf_for_folder($pt)
	{
		$oc = $this->get_object_chain($pt);
		foreach($oc as $dat)
		{
			if (($mnid = $this->db_fetch_field("SELECT conf_id FROM gallery_conf2menu WHERE menu_id = '$dat[oid]'","conf_id")))
			{
				return $mnid;
			}
		}
		return false;
	}

	function callback_get_rel_types()
	{
		return array(
			RELATION_LAYOUT => "layout",
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELATION_LAYOUT)
		{
			return array(CL_LAYOUT);
		}
	}

	function callback_mod_tab($parm)
	{
		$id = $parm['id'];
		if (substr($id, 0, 5) == 'page_')
		{
			$od = $this->get_object($parm['coredata']['oid']);
			$pgnr = substr($id, 5);
			if ($pgnr > $od['meta']['num_pages'])
			{
				return false;
			}
		}
		return true;
	}

	function _get_edit_page($arr)
	{
		extract($arr);
		$obj = $this->get_object($oid);
		
		$page_data = $obj['meta']['page_data'][$page]['layout'];
		if (!$page_data && $obj['meta']['def_layout'])
		{
			// this the first time this page is edited, so get the default layout for it
			$l = get_instance("layout");
			$page_data = $l->get_layout($obj['meta']['def_layout']);
		}
		
		$ge = get_instance("vcl/grid_editor");
		return $ge->on_edit($page_data, $oid, array(
			"cell_content_callback" => array(&$this, "_get_edit_cell_content", array("obj" => $obj, "page" => $page))
		));
	}

	function _get_edit_cell_content($params, $row, $col)
	{
		$obj = $params['obj'];
		$page = $params['page'];

		$pd = $obj['meta']['page_data'][$page]['content'][$row][$col];
		$this->read_template("grid_edit_cell.tpl");
		$this->vars(array(
			'page' => $page,
			'row' => $row,
			'col' => $col,
			"imgurl" => image::check_url($pd['tn']['url']),
			"bigurl" => image::check_url($pd['img']['url']),
			'caption' => $pd['caption'],
			'date' => $pd['date'],
			'has_textlink' => checked($pd['has_textlink']),
			'textlink' => $pd['textlink'],
			'ord' => $pd['ord'],
		));

		if ($pd['tn']['id'])
		{
			$this->vars(array(
				"HAS_IMG" => $this->parse("HAS_IMG")
			));
		}
		if ($pd['img']['id'])
		{
			$this->vars(array(
				"BIG" => $this->parse("BIG")
			));
		}
		return $this->parse();
	}

	function set_property(&$arr)
	{
		$prop = &$arr['prop'];
		$obj = $arr['obj'];
		if (substr($prop['name'],0,3) == "pg_")
		{
			$page_number = (int)substr($prop['name'], 3, 1);

			$page_data = $obj['meta']['page_data'][$page_number]['layout'];
			if (!$page_data && $obj['meta']['def_layout'])
			{
				// this the first time this page is edited, so get the default layout for it
				$l = get_instance("layout");
				$page_data = $l->get_layout($obj['meta']['def_layout']);
			}

			$this->_page_content = $obj['meta']['page_data'][$page_number]['content'];
			$obj['meta']['image_folder'] = $this->_get_image_folder($obj);

			$ge = get_instance("vcl/grid_editor");
			$obj['meta']['page_data'][$page_number]['layout'] = $ge->on_edit_submit(
				$page_data, 
				$arr['form_data'],
				array(
					"cell_content_callback" => array(&$this, "_set_edit_cell_content", array("obj" => $obj, "page" => $page_number))
				)
			);
			$obj['meta']['page_data'][$page_number]['content'] = $this->_page_content;
			$arr['metadata'] = $obj['meta'];
		}
		return PROP_OK;
	}

	function _set_edit_cell_content($params, $row, $col, $post_data)
	{
		$obj = $params['obj'];
		$page = $params['page'];
		
		$old = $this->_page_content[$row][$col];
		// check uploaded images and shit
		$cd = $post_data['g'][$page][$row][$col];
		$this->_page_content[$row][$col] = $cd;
	
		// also upload images
		$img_n = "g_".$page."_".$row."_".$col."_img";
		$f = get_instance("image");
		$this->_page_content[$row][$col]["img"] = $f->add_upload_image(
			$img_n, 
			$obj['meta']['image_folder'],
			$old['img']['id']
		);
		$img_n = "g_".$page."_".$row."_".$col."_tn";
		$f = get_instance("image");
		$this->_page_content[$row][$col]["tn"] = $f->add_upload_image(
			$img_n, 
			$obj['meta']['image_folder'],
			$old['tn']['id']
		);

		$del = $post_data['erase'][$page][$row][$col];
		if ($del)
		{
			$this->_page_content[$row][$col]["img"] = array();
			$this->_page_content[$row][$col]["tn"] = array();
		}
	}

	function _get_image_folder($obj)
	{
		// get it from conf
		$cf = get_instance("contentmgmt/gallery/gallery_conf");
		return $cf->get_image_folder($this->_get_conf_for_folder($obj['parent']));
	}

	function show($arr)
	{
		extract($arr);
		global $page;
		
		$ob = $this->get_object($oid);

		if ($page < 1 || $page > $ob['meta']['num_pages'])
		{
			$page = 1;
		}

		$c = $ob['meta']['page_data'][$page]['content'];
		$l = $ob['meta']['page_data'][$page]['layout'];

		$this->read_any_template("show_v2.tpl");

		// ok, do draw, first draw all rate objs
		$rate = array();
		$rateobjs = $this->_get_rate_objs($ob);
		foreach($rateobjs as $oid => $name)
		{
			$this->vars(array(
				"link" => $this->mk_my_orb("show", array("id" => $oid), "rate"),
				"name" => $name
			));
			$rate[] = $this->parse("RATE_OBJ");
		}
		$this->vars(array(
			"RATE_OBJ" => join($this->parse("RATE_OBJ_SEP"), $rate),
			"RATE_OBJ_SEP" => ""
		));

		// now pageselector
		$pages = array();
		$ps_back = "";
		$ps_fwd = "";
		$num_pages = $ob['meta']['num_pages'];
		for($pg = 1; $pg <= $num_pages; $pg++)
		{
			$url = aw_url_change_var("page", $pg);
			$this->vars(array(
				"link" => $url,
				"page_num" => $pg
			));
			if ($pg == $page)
			{
				$pages[] = $this->parse("SEL_PAGE");
			}
			else
			{
				$pages[] = $this->parse("PAGE");
			}
			if ($pg == $page-1)
			{
				$ps_back = $this->parse("PAGESEL_BACK");
			}
			if ($pg == $page+1)
			{
				$ps_fwd = $this->parse("PAGESEL_FWD");
			}
		}
		$this->vars(array(
			"PAGE" => join($this->parse("PAGE_SEP"), $pages),
			"SEL_PAGE" => "",
			"PAGESEL_BACK" => $ps_back,
			"PAGESEL_FWD" => $ps_fwd
		));
		
		// now all images 
		$this->rating = get_instance("contentmgmt/rate/rate");

		// get all hit counts for all images, so that we won't do a query for each image
		$pd = $ob['meta']['page_data'][$page]['content'];
		$this->hits = $this->_get_hit_counts($pd);
		
		$li = get_instance("vcl/grid_editor");
		$li->_init_table($l);

		$this->vars(array(
			"num_cols" => $li->get_num_cols(),
			"layout" => $li->show_tpl($l, $oid, array(
				"tpl" => "gallery/show_v2_layout.tpl",
				"cell_content_callback" => array(&$this, "_get_show_cell_content", array("obj" => $ob, "page" => $page))
			)),
			"name" => $ob['name']
		));
		return $this->parse();
	}

	function _get_show_cell_content($params, $row, $col)
	{
		$obj = $params['obj'];
		$page = $params['page'];

		$pd = $obj['meta']['page_data'][$page]['content'][$row][$col];
		
		$w = $pd['img']['sz'][0];
		$h = $pd['img']['sz'][1]+70;
		$link = "javascript:remote('no',$w,$h,'".$this->mk_my_orb("show_image", array(
			"id" => $obj['oid'],
			"page" => $page,
			"row" => $row,
			"col" => $col
		))."')";

		$tp = new aw_template;
		$tp->tpl_init("gallery");
		$tp->read_template("show_v2_cell_content.tpl");
		$tp->vars(array(
			"link" => $link,
			"img" => image::make_img_tag(image::check_url($pd['tn']['url'])),
			"rating" => $this->rating->get_rating_for_object($pd['img']['id']),
			"hits" => $this->hits[$pd['img']['id']]
		));
		return $tp->parse();
	}

	function _get_rate_objs($obj)
	{
		$ret = array();

		$cf = get_instance("contentmgmt/gallery/gallery_conf");
		$ros = new aw_array($cf->get_rate_objects($this->_get_conf_for_folder($obj['parent'])));

		$this->db_query("SELECT oid,name FROM objects WHERE oid IN(".$ros->to_sql().")");
		while ($row = $this->db_next())
		{
			$ret[$row['oid']] = $row['name'];
		}
		return $ret;
	}

	function show_image($arr)
	{
		extract($arr);
		
		$ob = $this->get_object($id);
		$pd = $ob['meta']['page_data'][$page]['content'][$row][$col];

		$this->read_template("show_v2_image.tpl");

		$sc = get_instance("contentmgmt/rate/rate_scale");
		$scale = $sc->get_scale_for_obj($pd['img']['id']);
		$rsi = "";
		foreach($scale as $sci_val => $sci_name)
		{
			$this->vars(array(
				"rate_link" => $this->mk_my_orb("rate", array(
					"oid" => $pd['img']['id'],
					"return_url" => urlencode(aw_global_get("REQUEST_URI")),
					"rate" => $sci_val
				), "rate"),
				"scale_value" => $sci_name
			));
			$rsi.=$this->parse("RATING_SCALE_ITEM");
		}
		
		$this->add_hit($pd['img']['id']);

		$r = get_instance("contentmgmt/rate/rate");
		$this->vars(array(
			"avg_rating" => $r->get_rating_for_object($pd['img']['id'], RATING_AVERAGE),
			"print_link" => "javascript:window.print()",
			"email_link" => $email_link,
			"image" => image::make_img_tag(image::check_url($pd['img']['url'])),
			"views" => (int)$this->db_fetch_field("SELECT hits FROM hits WHERE oid = '".$pd['img']['id']."'", "hits"),
			"RATING_SCALE_ITEM" => $rsi,
			"name" => $ob['name']
		));

		die($this->parse());
	}

	function _get_hit_counts($pd)
	{
		$imgids = new aw_array();
		if (is_array($pd))
		{
			foreach($pd as $r_id => $r_dat)
			{
				foreach($r_dat as $c_id => $c_dat)
				{
					if ($c_dat["img"]["id"])
					{
						$imgids->set($c_dat["img"]["id"]);
					}
				}
			}
		}
		$hits = array();
		$this->db_query("SELECT oid,hits FROM hits WHERE oid IN(".$imgids->to_sql().")");
		while ($row = $this->db_next())
		{
			$hits[$row['oid']] = $row['hits'];
		}
		return $hits;
	}
}
?>
