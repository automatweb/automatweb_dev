<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/gallery/mini_gallery.aw,v 1.12 2005/04/27 11:50:02 kristo Exp $
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
@caption ridu

@groupinfo import caption="Import"
@default group=import

	@property zip_file type=fileupload store=no
	@caption Uploadi ZIP fail

@reltype IMG_FOLDER value=1 clid=CL_MENU
@caption piltide kataloog
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
		return $this->show(array("id" => $arr["alias"]["target"]));
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
		$rows = $ob->prop("rows") ? $ob->prop("rows") : $img_c / $ob->prop("cols");
		$cols = $ob->prop("cols");
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
						)
					);
					$args["tpls"] = $tplar;
					$tmp = $ii->parse_alias($args);
					$this->vars(array(
						"imgcontent" => $tmp["replacement"]

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

		$imgi = get_instance(CL_IMAGE);
		$fi = get_instance(CL_FILE);
		foreach($files as $file)
		{
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

		@rmdir($tn);
	}

	function _do_pageselector($ob, $img_c, $rows, $cols)
	{
		if ($rows * $cols >= $img_c)
		{
			if (aw_global_get("uid") == "kix")
			{
				echo "reta r*c = ".($rows * $cols)." imgc = $img_c <br>";
			}
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
}
?>
