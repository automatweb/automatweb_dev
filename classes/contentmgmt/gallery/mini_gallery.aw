<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/gallery/mini_gallery.aw,v 1.23 2006/04/25 11:46:28 kristo Exp $
// mini_gallery.aw - Minigalerii 
/*

@classinfo syslog_type=ST_MINI_GALLERY relationmgr=yes no_status=1

@default table=objects
@default group=general

@property folder type=relpicker reltype=RELTYPE_IMG_FOLDER field=meta method=serialize parent=this.parent
@caption Piltide kataloog

@property cols type=textbox size=5 field=meta method=serialize default=2
@caption Tulpi

@property rows type=textbox size=5 field=meta method=serialize
@caption Ridu

@property comments type=checkbox field=flags method=bitmask ch_value=1
@caption Pildid kommenteeritavad

@property style type=relpicker reltype=RELTYPE_STYLE field=meta method=serialize
@caption Piltide stiil

@groupinfo import caption="Import"
@default group=import

@property zip_file type=fileupload store=no
@caption Uploadi ZIP fail

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

		$img_c = $images->count();
		if ($ob->prop("cols") == 0)
		{
			$rows = $img_c;
			$cols = 1;
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
}?>
