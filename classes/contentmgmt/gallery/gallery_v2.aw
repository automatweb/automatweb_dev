<?php
// gallery.aw - gallery management
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/gallery/gallery_v2.aw,v 1.8 2003/03/28 17:42:23 kristo Exp $

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

@groupinfo import caption=Impordi
@groupinfo preview caption=Eelvaade

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property conf_id type=text size=3 field=meta method=serialize
@caption Konfiguratsioon:

@property reinit_layout type=checkbox ch_value=1 field=meta method=serialize
@caption Uuenda layout (kustutab k&otilde;ik pildid!)

@property num_pages type=textbox size=3 field=meta method=serialize
@caption Mitu lehte:

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

@property import_ftp type=checkbox ch_value=1 field=meta method=serialize group=import
@caption Impordi FTP serverist

@property ftp_login type=relpicker field=meta method=serialize group=import reltype=RELATION_FTP_LOGIN
@caption FTP Server

@property ftp_folder type=textbox field=meta method=serialize group=import
@caption FTP Serveri kataloog

@property import_local type=checkbox ch_value=1 field=meta method=serialize group=import
@caption Impordi kataloogist

@property local_folder type=textbox field=meta method=serialize group=import
@caption Kataloog

@property import_overwrite type=checkbox ch_value=1 field=meta method=serialize group=import
@caption Importimisel kirjuta olemasolevad pildid &uuml;le

@property import_add_pages type=checkbox ch_value=1 field=meta method=serialize group=import
@caption Importimisel lisa vajadusel lehek&uuml;lgi

@property do_import type=submit field=meta method=serialize group=import value=Impordi

*/

define("RELATION_FTP_LOGIN", 1);

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

	function view($args = array())
	{
		return $this->show(array(
			"oid" => $args["id"],
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
		else
		if ($prop['name'] == "do_import")
		{
			$prop['value'] = "Impordi";
		}
		else
		if ($prop['name'] == "ftp_host" || $prop['name'] == "ftp_user" || $prop['name'] == "ftp_pass" || $prop['name'] == "ftp_folder")
		{
			classload("core/ftp");
			if (!ftp::is_available())
			{
				return PROP_IGNORE;
			}
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
		if (!$page_data && ($def_layout = $this->_get_default_layout($obj)))
		{
			// this the first time this page is edited, so get the default layout for it
			$l = get_instance("layout");
			$page_data = $l->get_layout($def_layout);
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

		$this->vars(array("HAS_IMG" => ""));
		if ($pd['tn']['id'])
		{
			$this->vars(array(
				"HAS_IMG" => $this->parse("HAS_IMG")
			));
		}
		$this->vars(array("BIG" => ""));
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
			if (!$page_data && ($def_layout = $this->_get_default_layout($obj)))
			{
				// this the first time this page is edited, so get the default layout for it
				$l = get_instance("layout");
				$page_data = $l->get_layout($def_layout);
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
		if ($prop['name'] == "reinit_layout")
		{
			$obj['meta']['page_data'] = array();
			$arr['metadata']['page_data'] = array();
			$prop['value'] = 0;
		}
		return PROP_OK;
	}

	function callback_post_save($arr)
	{
		$ob = $this->get_object($arr["id"]);
		$meta = $ob['meta'];

		if ($meta['do_import'] != "")
		{
			set_time_limit(0);
			if ($meta['import_overwrite'] == 1)
			{
				$this->_clear_images(&$meta);
			}

			if ($meta["import_ftp"] == 1)
			{
				$ftp = get_instance("core/ftp");
				if (!$ftp->is_available())
				{
					return;
				}

				$ftp->connect(array(
					"host" => $meta['ftp_host'],
					"user" => $meta['ftp_user'],
					"pass" => $meta['ftp_pass'],
				));
				$files = $ftp->dir_list($meta['ftp_folder']);
			}
			else
			if ($meta["import_local"] == 1)
			{
				$files = array();
				if ($dir = @opendir($meta['local_folder'])) 
				{
					while (($file = readdir($dir)) !== false) 
					{
						if (!($file == "." || $file == ".."))
						{
							$files[] = $file;
						}
					}  
					closedir($dir);
				}
			}

			$img = get_instance("image");
			$img_folder = $this->_get_image_folder($ob);

			$conf = get_instance("contentmgmt/gallery/gallery_conf");
			$conf_id = $this->_get_conf_for_folder($ob["parent"]);
			$conf_o = $this->get_object($conf_id);

			echo "Impordin faile, palun oodake... <Br><br>\n\n";
			flush();
			foreach($files as $file)
			{
				echo "Leidsin pildi $file <br>\n";
				flush();

				if ($meta["import_ftp"] == 1)
				{
					$fc = $ftp->get($meta["ftp_folder"]."/".$file);
				}
				else
				if ($meta["import_local"] == 1)
				{
					$fc = $this->get_file(array("file" => $meta['local_folder']."/".$file));
				}
			
				// get image size
				$img = $this->_imagecreatefromstring($fc, $file);

				$i_width = imagesx($img);
				$i_height = imagesy($img);

				$xydata = $this->_get_xydata(array(
					"i_width" => $i_width,
					"i_height" => $i_height,
					"conf_o" => $conf_o
				));
				extract($xydata);

				// the conf object may specify a different size for images, so resize if necessary
				if (($width && ($width != $i_width)) || ($height && ($height != $i_height)))
				{
					$n_img = imagecreatetruecolor($width, $height);
					imagecopyresampled($n_img, $img, 0, 0, 0,0, $width, $height, $i_width, $i_height);
					imagedestroy($img);
					$img = $n_img;
				}

				if ($conf_o["meta"]["insert_logo"] == 1)
				{
					$img = $this->_do_logo($img, $conf_o);
				}

				$fc = $this->_get_jpeg($img);				
				
				$img_inst = get_instance("image");
				$idata = $img_inst->add_image(array(
					"str" => $fc, 
					"orig_name" => $file,
					"parent" => $img_folder
				));

				// now we gots to make a thumbnail and add that as well.
				$n_img = imagecreatetruecolor($tn_width, $tn_height);
				
				// now if the user specified that the thumbnail is subimage, then cut it out first
				if ($tn_is_subimage && $tn_si_width && $tn_si_height)
				{
					$t_img = imagecreatetruecolor($tn_si_width, $tn_si_height);
					imagecopy($t_img, $img, 0, 0, $tn_si_left, $tn_si_top, $tn_si_width, $tn_si_height);
					$res = imagecopyresampled($n_img, $t_img, 0, 0, 0,0, $tn_width, $tn_height, $tn_si_width, $tn_si_height);
				}
				else
				{
					$res = imagecopyresampled($n_img, $img, 0, 0, 0,0, $tn_width, $tn_height, $width, $height);
				}
				imagedestroy($img);
				$img = $n_img;

				if ($conf_o["meta"]["tn_insert_logo"] == 1)
				{
					$img = $this->_do_logo($img, $conf_o, "tn_");
				}

				$fc = $this->_get_jpeg($img);
				
				$tn_idata = $img_inst->add_image(array(
					"str" => $fc, 
					"orig_name" => $file,
					"parent" => $img_folder
				));

				// and now we need to add the image to the first empty slot
				$r = $this->_get_next_free_pos($meta, $_pg, $_row, $_col, $ob);
//				echo "r = ".dbg::dump($r)." <br>";
				if ($r === false && $meta['import_add_pages'] == 1)
				{
					// add page to the end
					$this->_add_page(&$meta, $ob);
					$r = $this->_get_next_free_pos($meta, $_pg, $_row, $_col, $ob);
//					echo "r after add page = ".dbg::dump($r)." <br>";
				}

				if ($r != false)
				{
					$_pg = $r[0];
					$_row = $r[1];
					$_col = $r[2];
//					echo "free page = $_pg row = $_row , col = $_col <br>";
					$meta['page_data'][$_pg]['content'][$_row][$_col]['img'] = $idata;
					$meta['page_data'][$_pg]['content'][$_row][$_col]['tn'] = $tn_idata;
				}
				$meta["do_import"] = "";
				$this->upd_object(array(
					"oid" => $arr["id"],
					"metadata" => $meta
				));
			}
			$meta["do_import"] = "";
			$this->upd_object(array(
				"oid" => $arr["id"],
				"metadata" => $meta
			));
			echo "Valmis! <a href='".$this->mk_my_orb("change", array("id" => $arr["id"], "group" => "import"))."'>Tagasi</a><br>\n";
			die();
		}
	}

	function _add_page(&$meta, $ob)
	{
		$l = get_instance("layout");
		$page_data = $l->get_layout($this->_get_default_layout($ob));

		$meta['num_pages']++;
		$meta['page_data'][$meta['num_pages']]['layout'] = $page_data;
	}

	function _get_next_free_pos(&$meta, $page, $row, $col, $ob)
	{
		// ok, we start from the pos, and scan left->right and up->down and then pages, until we find a place
		// that is empty

		if (!$page)
		{
			$page = 1;
		}		
//		echo "gnfp p $page r $row c $col <br>";
		for ($_page = 1; $_page <= $meta['num_pages']; $_page++)
		{
			if (!$meta['page_data'][$_page]['layout'])
			{
				// insert default layout
				$l = get_instance("layout");
				$meta['page_data'][$_page]['layout'] = $l->get_layout($this->_get_default_layout($ob));
			}

//			echo "scanning .. page $_page , rows = ".$meta['page_data'][$_page]['layout']['rows']." <br>";
			for ($_row = 0; $_row < $meta['page_data'][$_page]['layout']['rows']; $_row++)
			{
//				echo "scanning .. page $_page , row $_row ,  cols = ".$meta['page_data'][$_page]['layout']['cols']." <br>";
				for ($_col = 0; $_col < $meta['page_data'][$_page]['layout']['cols']; $_col++)
				{
//					echo "scanning .. page $_page , row = $_row , col = $_col <br>";
					
					$col_data = $meta['page_data'][$_page]['content'][$_row][$_col];
//					echo "col data = ".dbg::dump($col_data)." <br>";
					if ($_page == $page && $_row >= $row)
					{
						if ($_row == $row)
						{
							if ($_col >= $col)
							{
//								echo "first half = row <br>";
								if (!$col_data["img"]["id"])
								{
//									echo "found it! <br>";
									return array($_page, $_row, $_col);
								}
							}
						}
						else
						{
//							echo "first page <br>";
							if (!$col_data["img"]["id"])
							{
//								echo "found! <br>";
								return array($_page, $_row, $_col);
							}
						}
					}
					else
					if ($_page > $page)
					{
//						echo "other page! <br>";
						if (!$col_data["img"]["id"])
						{	
//							echo "found! <br>";
							return array($_page, $_row, $_col);
						}
					}
				}
			}
		}
		return false;
	}

	function _clear_images(&$meta)
	{
		for ($_page = 1; $_page < $meta['num_pages']; $_page++)
		{
			for ($_row = 0; $_row < $meta['page_data'][$_page]['layout']['rows']; $_row++)
			{
				for ($_col = 0; $_col < $meta['page_data'][$_page]['layout']['cols']; $_col++)
				{
					$meta['page_data'][$_page]['content'][$_row][$_col]['img'] = array();
					$meta['page_data'][$_page]['content'][$_row][$_col]['tn'] = array();
				}
			}
		}
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
				"link" => $this->mk_my_orb("show", array("id" => $oid, "section" => aw_global_get("section")), "rate"),
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

		$sp = 1;
		$p2rp = array();
		$cur_sp = 0;
		for($pg = 1; $pg <= $num_pages; $pg++)
		{
			if (!$this->_page_has_images($ob, $pg))
			{
				continue;
			}
			$p2rp[$sp] = $pg;
			if ($pg == $page)
			{
				$cur_sp = $sp;
			}
			$sp++;
		}

		foreach($p2rp as $sp => $pg)
		{
			$url = aw_url_change_var("page", $pg);
			$this->vars(array(
				"link" => $url,
				"page_num" => $sp
			));
			if ($pg == $page)
			{
				$pages[] = $this->parse("SEL_PAGE");
			}
			else
			{
				$pages[] = $this->parse("PAGE");
			}

			if ($cur_sp-1 == $sp)
			{
				$ps_back = $this->parse("PAGESEL_BACK");
			}

			if ($cur_sp+1 == $sp)
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
				"cell_content_callback" => array(&$this, "_get_show_cell_content", array("obj" => $ob, "page" => $page)),
				"ignore_empty" => true
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

		if (!$pd['img']['id'])
		{
			return "";
		}
		
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

		$this->read_any_template("show_v2_image.tpl");

		$p_page = $n_page = $page;
		$p_row = $n_row = $row; 
		$p_col = $n_col = $col;
		do {
			list($p_page, $p_row, $p_col) = $this->_get_prev_img($ob, $p_page, $p_row, $p_col);
		} while ($p_page > 0 && (!$ob['meta']['page_data'][$p_page]['content'][$p_row][$p_col]['img']['id']));

		do {
			list($n_page, $n_row, $n_col) = $this->_get_next_img($ob, $n_page, $n_row, $n_col);
		} while ($n_page <= $ob['meta']['num_pages'] && (!$ob['meta']['page_data'][$n_page]['content'][$n_row][$n_col]['img']['id']));

		if ($n_page > $ob['meta']['num_pages'])
		{
			$post_rate_url = $this->mk_my_orb("show_image", array(
				"id" => $id, 
				"page" => $page,
				"row" => $row, 
				"col" => $col
			));
		}
		else
		{
			$post_rate_url = $this->mk_my_orb("show_image", array(
				"id" => $id, 
				"page" => $n_page,
				"row" => $n_row, 
				"col" => $n_col
			));
		}

		$sc = get_instance("contentmgmt/rate/rate_scale");
		$scale = $sc->get_scale_for_obj($pd['img']['id']);
		$rsi = "";
		foreach($scale as $sci_val => $sci_name)
		{
			$this->vars(array(
				"rate_link" => $this->mk_my_orb("rate", array(
					"oid" => $pd['img']['id'],
					"return_url" => urlencode($post_rate_url),
					"rate" => $sci_val
				), "rate"),
				"scale_value" => $sci_name
			));
			$rsi.=$this->parse("RATING_SCALE_ITEM");
		}
		
		$this->add_hit($pd['img']['id']);

		$email_link = $this->mk_my_orb("send", array("id" => $id, "page" => $page, "row" => $row, "col" => $col));

		$r = get_instance("contentmgmt/rate/rate");
		$this->vars(array(
			"avg_rating" => $r->get_rating_for_object($pd['img']['id'], RATING_AVERAGE),
			"print_link" => "javascript:window.print()",
			"email_link" => $email_link,
			"image" => image::make_img_tag(image::check_url($pd['img']['url'])),
			"views" => (int)$this->db_fetch_field("SELECT hits FROM hits WHERE oid = '".$pd['img']['id']."'", "hits"),
			"RATING_SCALE_ITEM" => $rsi,
			"name" => $ob['name'],
			"prev_image_url" => $this->mk_my_orb("show_image", array("id" => $id, "page" => $p_page, "row" => $p_row , "col" => $p_col)),
			"next_image_url" => $this->mk_my_orb("show_image", array("id" => $id, "page" => $n_page, "row" => $n_row , "col" => $n_col)),
		));

		if ($n_page <= $ob['meta']['num_pages'])
		{
			$this->vars(array(
				"HAS_NEXT_IMAGE" => $this->parse("HAS_NEXT_IMAGE")
			));
		}

		if ($p_page > 0)
		{
			$this->vars(array(
				"HAS_PREV_IMAGE" => $this->parse("HAS_PREV_IMAGE")
			));
		}
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

	function _get_xydata($arr)
	{
		extract($arr);
		if ($i_width > $i_height)
		{
			$tn_width = $conf_o["meta"]["h_tn_width"];
			$tn_height = $conf_o["meta"]["h_tn_height"];
			$width = $conf_o["meta"]["h_width"];
			$height = $conf_o["meta"]["h_height"];
			$is_subimage = $conf_o["meta"]["h_tn_subimage"] == 1;
			if ($is_subimage)
			{
				$si_top = $conf_o["meta"]["h_tn_subimage_top"];
				$si_left = $conf_o["meta"]["h_tn_subimage_left"];
				$si_width = $conf_o["meta"]["h_tn_subimage_width"];
				$si_height = $conf_o["meta"]["h_tn_subimage_height"];
			}
		}
		else
		{
			$tn_width = $conf_o["meta"]["v_tn_width"];
			$tn_height = $conf_o["meta"]["v_tn_height"];
			$width = $conf_o["meta"]["v_width"];
			$height = $conf_o["meta"]["v_height"];
			$is_subimage = $conf_o["meta"]["v_tn_subimage"] == 1;
			if ($is_subimage)
			{
				$si_top = $conf_o["meta"]["v_tn_subimage_top"];
				$si_left = $conf_o["meta"]["v_tn_subimage_left"];
				$si_width = $conf_o["meta"]["v_tn_subimage_width"];
				$si_height = $conf_o["meta"]["v_tn_subimage_height"];
			}
		}

		// check if the user only specified one of width/height and then calc the other one
		if ($width && !$height)
		{
			if ($width{strlen($width)-1} == "%")
			{
				$height = $width;
			}
			else
			{
				$ratio = $width / $i_width;
				$height = (int)($i_height * $ratio);
			}
		}

		if (!$width && $height)
		{
			if ($height{strlen($height)-1} == "%")
			{
				$width = $height;
			}
			else
			{
				$ratio = $height / $i_height;
				$width = (int)($i_width * $ratio);
			}
		}


		if ($tn_width && !$tn_height)
		{
			if ($tn_width{strlen($tn_width)-1} == "%")
			{
				$tn_height = $tn_width;
			}
			else
			{
				$ratio = $tn_width / $i_width;
				$tn_height = (int)($i_height * $ratio);
			}
		}

		if (!$tn_width && $tn_height)
		{
			if ($tn_height{strlen($tn_height)-1} == "%")
			{
				$tn_width = $tn_height;
			}
			else
			{
				$tn_ratio = $tn_height / $i_height;
				$tn_width = (int)($i_width * $ratio);
			}
		}

		if ($si_width && !$si_height)
		{
			if ($si_width{strlen($si_width)-1} == "%")
			{
				$si_height = $si_width;
			}
			else
			{
				$ratio = $si_width / $i_width;
				$si_height = (int)($i_height * $ratio);
			}
		}

		if (!$si_width && $si_height)
		{
			if ($si_height{strlen($si_height)-1} == "%")
			{
				$si_width = $si_height;
			}
			else
			{
				$ratio = $si_height / $i_height;
				$si_width = (int)($i_width * $ratio);
			}
		}


		if (!$width)
		{
			$width = $i_width;
		}
		if (!$height)
		{
			$height = $i_height;
		}

		// now convert to pixels
		if ($width{strlen($width)-1} == "%")
		{
			$width = (int)($i_width * (((int)substr($width, 0, -1))/100));
		}
		if ($height{strlen($height)-1} == "%")
		{
			$height = (int)($i_height * (((int)substr($height, 0, -1))/100));
		}

		if ($tn_width{strlen($tn_width)-1} == "%")
		{
			$tn_width = (int)($width * (((int)substr($tn_width, 0, -1))/100));
		}
		if ($tn_height{strlen($tn_height)-1} == "%")
		{
			$tn_height = (int)($height * (((int)substr($tn_height, 0, -1))/100));
		}

		if ($si_width{strlen($si_width)-1} == "%")
		{
			$si_width = (int)($width * (((int)substr($si_width, 0, -1))/100));
		}
		if ($si_height{strlen($si_height)-1} == "%")
		{
			$si_height = (int)($height * (((int)substr($si_height, 0, -1))/100));
		}

		return array(
			"width" => $width,
			"height" => $height,
			"tn_width" => $tn_width,
			"tn_height" => $tn_height,
			"tn_is_subimage" => $is_subimage,
			"tn_si_top" => $si_top,
			"tn_si_left" => $si_left,
			"tn_si_width" => $si_width,
			"tn_si_height" => $si_height
		);
	}

	function _get_jpeg($img)
	{
		ob_start();
		imagejpeg($img);
		$fc = ob_get_contents();
		ob_end_clean();
		return $fc;
	}

	function _imagecreatefromstring($str, $orig_filename)
	{
		if (function_exists("imagecreatefromstring"))
		{
			return imagecreatefromstring($str);
		}
		else
		{
			// save temp file
			$tn = tempnam(aw_ini_get("server.tmpdir"), "aw_g_v2_conv");
			$this->put_file(array(
				"file" => $orig_filename,
				"content" => $str
			));
			$_o = strtolower($orig_filename);
			$ext = substr($_o, strrpos($_o, ".")+1);
			if ($ext == "jpg" || $ext == "jpeg" || $ext == "pjpeg")
			{
				$img = imagecreatefromjpeg($tn);
			}
			else
			if ($ext == "png")
			{
				$img = imagecreatefrompng($tn);
			}
			else
			{
				// try jpeg for default
				$img = imagecreatefromjpeg($tn);
			}
			unlink($tn);
			return $img;
		}
	}

	function _do_logo($img, $conf_o, $p = "")
	{
		// first, get the damn image
		if (!$conf_o["meta"][$p."logo_img"])
		{
			return $img;
		}

		$iinst = get_instance("image");
		$_img = $iinst->get_image_by_id($conf_o["meta"][$p."logo_img"]);

		$l_img = $this->_imagecreatefromstring($this->get_file(array("file" => $_img["file"])), $_img["file"]);

		// this here finds the transparent color and makes it really be transparent
		$trans = imagecolorclosestalpha($l_img, 0,0,0, 127);
		imagecolortransparent($l_img, $trans);
 
		// now, find where to put the damn thing
		if ($conf_o["meta"][$p."logo_corner"] == CORNER_LEFT_TOP)
		{
			imagecopymerge(
				$img, 
				$l_img, 
				$conf_o["meta"][$p."logo_dist_x"], 
				$conf_o["meta"][$p."logo_dist_y"], 
				0,
				0, 
				imagesx($l_img), 
				imagesy($l_img), 
				($conf_o["meta"][$p."logo_transparency"] ? $conf_o["meta"][$p."logo_transparency"] : 100)
			);
		}
		else
		if ($conf_o["meta"][$p."logo_corner"] == CORNER_LEFT_BOTTOM)
		{
			imagecopymerge(
				$img, 
				$l_img, 
				$conf_o["meta"][$p."logo_dist_x"], 
				imagesy($img) - ($conf_o["meta"][$p."logo_dist_y"] + imagesy($l_img)), 
				0,
				0, 
				imagesx($l_img), 
				imagesy($l_img), 
				($conf_o["meta"][$p."logo_transparency"] ? $conf_o["meta"][$p."logo_transparency"] : 100)
			);
		}
		else
		if ($conf_o["meta"][$p."logo_corner"] == CORNER_RIGHT_TOP)
		{
			imagecopymerge(
				$img, 
				$l_img, 
				imagesx($img) - ($conf_o["meta"][$p."logo_dist_x"] + imagesx($l_img)), 
				$conf_o["meta"][$p."logo_dist_y"], 
				0,
				0, 
				imagesx($l_img), 
				imagesy($l_img), 
				($conf_o["meta"][$p."logo_transparency"] ? $conf_o["meta"][$p."logo_transparency"] : 100)
			);
		}
		else
		if ($conf_o["meta"][$p."logo_corner"] == CORNER_RIGHT_BOTTOM)
		{
			imagecopymerge(
				$img, 
				$l_img, 
				imagesx($img) - ($conf_o["meta"][$p."logo_dist_x"] + imagesx($l_img)), 
				imagesy($img) - ($conf_o["meta"][$p."logo_dist_y"] + imagesy($l_img)), 
				0,
				0, 
				imagesx($l_img), 
				imagesy($l_img), 
				($conf_o["meta"][$p."logo_transparency"] ? $conf_o["meta"][$p."logo_transparency"] : 100)
			);
		}
		return $img;
	}

	function _get_prev_img($ob, $page, $row, $col)
	{
		$p_col = $col - 1;
		if ($p_col < 0)
		{
			$p_row = $row - 1;
			$p_col = $ob['meta']['page_data'][$page]['layout']['cols']-1;
		}
		else
		{
			$p_row = $row;
		}

		if ($p_row < 0)
		{
			$p_page = $page-1;
			$p_row = $ob['meta']['page_data'][$p_page]['layout']['rows']-1;
			$p_col = $ob['meta']['page_data'][$p_page]['layout']['cols']-1;
		}
		else
		{
			$p_page = $page;
		}

		return array($p_page, $p_row, $p_col);
	}

	function _get_next_img($ob, $page, $row, $col)
	{
		$n_col = $col + 1;
		if ($n_col >= $ob['meta']['page_data'][$page]['layout']['cols'])
		{
			$n_row = $row+1;
			$n_col = 0;
		}
		else
		{
			$n_row = $row;
		}

		if ($n_row >= $ob['meta']['page_data'][$page]['layout']['rows'])
		{
			$n_page = $page+1;
			$n_row = 0;
			$n_col = 0;
		}
		else
		{
			$n_page = $page;
		}

		return array($n_page, $n_row, $n_col);
	}

	function send($arr)
	{
		extract($arr);
		$this->read_any_template("send.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_send", $arr)
		));
		return $this->parse();
	}

	function submit_send($arr)
	{
		extract($arr);
		
		$link = $this->mk_my_orb("show_image", array("id" => $id, "page" => $page, "row" => $row, "col" => $col));

		mail($to, $subject, $message."\n\n".$link."\n\n");

		return $link;
	}

	function _page_has_images($ob, $page)
	{
		$rows = $ob['meta']['page_data'][$page]['layout']["rows"];
		$cols = $ob['meta']['page_data'][$page]['layout']["cols"];
		$pd = $ob['meta']['page_data'][$page]['content'];
 
		for ($row = 0; $row < $rows; $row++)
		{
			for ($col = 0; $col < $cols; $col++)
			{
				if ($pd[$row][$col]['img']['id'])
				{
					return true;
				}
			}
		}

		return false;
	}

	function _get_default_layout($obj)
	{
		$conf = get_instance("contentmgmt/gallery/gallery_conf");
		return $conf->get_default_layout($this->_get_conf_for_folder($obj['parent']));
	}

	function callback_get_rel_types()
	{
		return array(
			RELATION_FTP_LOGIN => "ftp login"
		);
	}

	function callback_get_classes_for_relation($args = array())
	{
		if ($args["reltype"] == RELATION_FTP_LOGIN)
		{
			return array(CL_FTP_LOGIN);
		}
	}
}
?>
