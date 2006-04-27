<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/gallery/mini_gallery.aw,v 1.25 2006/04/27 08:14:38 kristo Exp $
// mini_gallery.aw - Minigalerii 
/*

@classinfo syslog_type=ST_MINI_GALLERY relationmgr=yes no_status=1

@default table=objects

@default group=general

	@property folder type=relpicker reltype=RELTYPE_IMG_FOLDER field=meta method=serialize 
	@caption Piltide kataloog

	@property cols type=textbox size=5 field=meta method=serialize default=2
	@caption Tulpi

	@property rows type=textbox size=5 field=meta method=serialize
	@caption Ridu

	@property comments type=checkbox field=flags method=bitmask ch_value=1
	@caption Pildid kommenteeritavad

	@property style type=relpicker reltype=RELTYPE_STYLE field=meta method=serialize
	@caption Piltide stiil

@default group=import

	@property zip_file type=fileupload store=no
	@caption Uploadi ZIP fail

@default group=manage

	@property mg_tb type=toolbar no_caption=1 store=no

	@property mg_table type=table no_caption=1 store=no

@groupinfo import caption="Import"
@groupinfo manage caption="Halda pilte" submit=no


@reltype IMG_FOLDER value=1 clid=CL_MENU
@caption Piltide kataloog

@reltype STYLE value=2 clid=CL_CSS
@caption Stiil

*/

class mini_gallery extends class_base
{
	function mini_gallery()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/gallery/mini_gallery",
			"clid" => CL_MINI_GALLERY
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "mg_tb":
				$this->_mg_tb($arr);
				break;

			case "mg_table":
				$this->_mg_table($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "zip_file":
				if (is_uploaded_file($_FILES["zip_file"]["tmp_name"]))
				{
					$this->_do_zip_import($arr["obj_inst"], $_FILES["zip_file"]["tmp_name"]);
				}
				break;
		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		$res = $this->show(array("id" => $arr["alias"]["target"]));
		if (isset($arr["tpls"]["mini_gallery_inplace"]))
		{
			$res = array(
				"replacement" => localparse($arr["tpls"]["mini_gallery_inplace"], array("content" => $res)),
				"inplace" => "mini_gallery_inplace"
			);
		}
		return $res;
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");

		$images = new object_list(array(
			"class_id" => CL_IMAGE,
			"parent" => $ob->prop("folder"),
			"sort_by" => "objects.jrk",
			"lang_id" => array(),
			"site_id" => array()
		));

		if (!$images->count())
		{
			return;
		}

		$img_c = $images->count();
		if ($ob->prop("cols") == 0)
		{
			$rows = $img_c;
			$cols = 1;
		}
		else
		if ($ob->prop("rows"))
		{
			$rows = $ob->prop("rows");
			$cols = $ob->prop("cols");
		}
		else
		{
			$rows = (int)($img_c / $ob->prop("cols"));
			$cols = $ob->prop("cols");
		}
		$img = $images->begin(); 

		if ($ob->prop("rows"))
		{
			$this->_do_pageselector($ob, $img_c, $rows, $cols);
		}

		if ($_GET["mg_pg"])
		{
			for($i = 0; $i < ($_GET["mg_pg"] * $rows * $cols); $i++)
			{
				$img = $images->next();
				$img_c--;
			}
		}

		$ii = get_instance(CL_IMAGE);

		$tplar = array();

		if ($this->is_template("IMAGE"))
		{
			$imtpl = $this->get_template_string("IMAGE");
			$tplar["image"] = $imtpl;
		}

		if ($this->is_template("IMAGE_LINKED"))
		{
			$imtpl = $this->get_template_string("IMAGE_LINKED");
			$tplar["image_linked"] = $imtpl;
		}

		if ($this->is_template("IMAGE_HAS_BIG"))
		{
			$imtpl = $this->get_template_string("IMAGE_HAS_BIG");
			$tplar["image_has_big"] = $imtpl;
		}

		if ($this->is_template("IMAGE_BIG_LINKED"))
		{
			$imtpl = $this->get_template_string("IMAGE_BIG_LINKED");
			$tplar["image_big_linked"] = $imtpl;
		}
		$s_id = $ob->prop("style");
		if(is_oid($s_id) && $this->can("view", $s_id))
		{
			$style_i = get_instance(CL_STYLE);
			active_page_data::add_site_css_style($s_id);
			$use_style = $style_i->get_style_name($s_id);
		}

		$str = "";
		for ($r = 0; $r < $rows; $r++)
		{
			$l = "";
			for($c = 0; $c < $cols; $c++)
			{
				if ($imgc < $img_c)
				{
					$args = array(
						"alias" => array(
							"target" => $img->id()
						),
						"tpls" => $tplar,
						"use_style" => $use_style,
						"force_comments" => $ob->prop("comments"),
						"link_prefix" => empty($arr['link_prefix']) ? "" : $arr['link_prefix'],
					);
					$tmp = $ii->parse_alias($args);
					$this->vars(array(
						"imgcontent" => $tmp["replacement"],
					));
					$img = $images->next();
					$imgc ++;
				}
				else
				{
					$this->vars(array(
						"imgcontent" => ""
					));
				}
				$l .= $this->parse("COL");
			}

			$this->vars(array(
				"COL" => $l
			));
			$str .= $this->parse("ROW");
		}

		$this->vars(array(
			"ROW" => $str
		));

		return $this->parse();
	}

	function _do_zip_import($o, $zip)
	{
		if (!$this->can("add", $o->prop("folder")))
		{
			die(t("Valitud piltide kataloogi ei ole &otilde;igusi objekte lisada!"));
		}

		if (extension_loaded("zip"))
		{
			$folder = aw_ini_get("server.tmpdir")."/".gen_uniq_id();
			mkdir($folder, 0777);
			$tn = $folder;
			$zip = zip_open($zip);
			while ($zip_entry = zip_read($zip)) 
			{
				zip_entry_open($zip, $zip_entry, "r");
				$fn = $folder."/".zip_entry_name($zip_entry);
				$files[] = basename($fn);
				$fc = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				$this->put_file(array(
					"file" => $fn,
					"content" => $fc
				));
			}
		}
		else
		{
			$zf = escapeshellarg($zip);
			$zip = aw_ini_get("server.unzip_path");
			$tn = aw_ini_get("server.tmpdir")."/".gen_uniq_id();
			mkdir($tn,0777);
			$cmd = $zip." -d $tn $zf";
			$op = shell_exec($cmd);


			$files = array();
			if ($dir = @opendir($tn)) 
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

		$imgi = get_instance(CL_IMAGE);
		$fi = get_instance(CL_FILE);
		foreach($files as $file)
		{
			echo "leidsin faili $file <br>\n";
			flush();
			$fp = $tn."/".$file;

			$img = obj();
			$img->set_class_id(CL_IMAGE);
			$img->set_parent($o->prop("folder"));
			$img->set_status(STAT_ACTIVE);
			$img->set_name($file);
			
			$fl = $fi->_put_fs(array(
				"type" => substr($file, strrpos(".", $file)),
				"content" => $this->get_file(array("file" => $fp))
			));
			$img->set_prop("file", $fl);

			$img->save();

			$imgi->do_apply_gal_conf($img);

			@unlink($fp);
		}
		echo "valmis<br>\n";
		flush();
		@rmdir($tn);
	}

	function _do_pageselector($ob, $img_c, $rows, $cols)
	{
		$rows = (int)$rows;
		if ((int)$rows * $cols >= $img_c)
		{
			return;
		}

		$prev_page = $next_page = "";
		$num_pgs = $img_c / ($rows * $cols);
		for($i = 0; $i < $num_pgs; $i++)
		{
			$this->vars(array(
				"page_link" => aw_url_change_var("mg_pg", $i),
				"page_nr" => $i+1
			));

			if ($_GET["mg_pg"] == $i)
			{
				$pgs[] = $this->parse("PAGE_SEL");
			}
			else
			{
				$pgs[] = $this->parse("PAGE");
			}

			if ($i+1 == $_GET["mg_pg"])
			{
				$prev_page = $this->parse("PREV_PAGE");
			}
			if ($i-1 == $_GET["mg_pg"])
			{
				$next_page = $this->parse("NEXT_PAGE");
			}
		}

		$this->vars(array(
			"PAGE" => join($this->parse("PAGE_SEPRATOR"), $pgs),
			"PAGE_SEPARATOR" => "",
			"PAGE_SEL" => "",
			"PREV_PAGE" => $prev_page,
			"NEXT_PAGE" => $next_page
		));

		$this->vars(array(
			"PAGESELECTOR" => $this->parse("PAGESELECTOR")
		));
	}

	function callback_post_save($arr)
	{
		if ($arr["request"]["new"])
		{
			// create folders and set props
			$folder = obj();
			$folder->set_parent($arr["obj_inst"]->parent());
			$folder->set_name(t("Galerii ").$arr["obj_inst"]->name().t(" pildid"));
			$folder->set_class_id(CL_MENU);
			$folder->save();
			$arr["obj_inst"]->set_prop("folder", $folder->id());
			$arr["obj_inst"]->save();
		}
	}

	function _mg_tb($arr)
	{
		$tb =& $arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			'name' => 'new',
			'img' => 'new.gif',
			'tooltip' => t('Lisa uus pilt'),
			'url' => html::get_new_url(CL_IMAGE, $arr["obj_inst"]->prop("folder"), array("return_url" => get_ru()))
		));
		$tb->add_button(array(
			'name' => 'save',
			'img' => 'save.gif',
			'tooltip' => t('Salvesta pildid'),
			'action' => 'save_image_list',
		));
		$tb->add_button(array(
			'name' => 'del',
			'img' => 'delete.gif',
			'tooltip' => t('Kustuta valitud pildid'),
			'action' => 'delete_images',
			'confirm' => t("Kas oled kindel et soovid valitud pildid kustudada?")
		));
	}

	function _init_mg_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => t("Muutja"),
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => t("Muudetud"),
			"align" => "center",
			"type" => "time",
			"numeric" => 1,
			"format" => "d.m.Y H:i"
		));

		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid"
		));
	}

	function _mg_table($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_mg_table($t);

		$images = new object_list(array(
			"class_id" => CL_IMAGE,
			"parent" => $arr["obj_inst"]->prop("folder"),
			"sort_by" => "objects.jrk",
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($images->arr() as $im)
		{
			$t->define_data(array(
				"name" => html::obj_change_url($im),
				"ord" => html::textbox(array(
					"name" => "ord[".$im->id()."]",
					"value" => $im->ord(),
					"size" => 5
				)),
				"modifiedby" => $im->modifiedby(),
				"modified" => $im->modified(),
				"oid" => $im->id(),
				"h_ord" => $im->ord()
			));
		}
		$t->set_sortable(false);
	}

	/**
		@attrib name=save_image_list
	**/
	function save_image_list($arr)
	{
		$o = obj($arr["id"]);
		$images = new object_list(array(
			"class_id" => CL_IMAGE,
			"parent" => $o->prop("folder"),
			"sort_by" => "objects.jrk",
			"lang_id" => array(),
			"site_id" => array()
		));
		foreach($images->arr() as $im)
		{
			if ($arr["ord"][$im->id()] != $im->ord())
			{
				$im->set_ord($arr["ord"][$im->id()]);
				$im->save();
			}
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=delete_images
	**/
	function delete_images($arr)
	{
		object_list::iterate_list($arr["sel"], "delete");
		return $arr["post_ru"];
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

}
?>