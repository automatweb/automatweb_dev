<?php

class aip_ctl_list extends aw_template
{
	function aip_ctl_list()
	{
		$this->init("aip_ctl_list");
	}

	function orb_list($arr)
	{
		extract($arr);
		$this->read_template("list.tpl");

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_FILE." AND status != 0");
		while ($row = $this->db_next())
		{
			$meta = $this->get_object_metadata(array(
				"metadata" => $row["metadata"]
			));

			$this->vars(array(
				"name" => $row["name"],
				"time" => $this->time2date($meta["act_time"], 2),
				"j_time" => $this->time2date($meta["j_time"], 2),
				"change" => $this->mk_my_orb("change", array(
					"id" => $row["oid"], 
					"return_url" => urlencode($this->mk_my_orb("list"))
				),"file")
			));

			$l.= $this->parse("LINE");
		}

		$tb = get_instance("toolbar");


		$_mn = get_instance("menuedit");
		$_mn->read_template("java_popup_menu.tpl");

		$host = str_replace("http://","",$_mn->cfg["baseurl"]);
		preg_match("/.*:(.+?)/U",$host, $mt);
		if ($mt[1])
		{
			$host = str_replace(":".$mt[1], "", $host);
		}

		// make applet for adding objects
		$_mn->vars(array(
			"icon_over" => $_mn->cfg["baseurl"]."/automatweb/images/icons/new2_over.gif",
			"icon" => $_mn->cfg["baseurl"]."/automatweb/images/icons/new2.gif",
			"oid" => get_root(),
			"bgcolor" => "#D4D7DA",
			"nr" => 2,
			"key" => "addmenu",
			"val" => 1,
			"name" => "",
			"height" => 22,
			"width" => 23,
			"url" => $host,
			"content" => $this->get_add_menu(array("section" => get_root()))
		));
		$up = $_mn->parse("URLPARAM");
		$_mn->vars(array(
			"URLPARAM" => $up,
			"FETCHCONTENT" => $_mn->parse("FETCHCONTENT")
		));

		$tb->add_cdata($_mn->parse());

		$tb->add_button(array(
			"name" => "ules",
			"tooltip" => "&Uuml;les",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".get_root()."&aip=1",
			"imgover" => "kaust_tagasi_over.gif",
			"img" => "kaust_tagasi.gif"
		));

		$tb->add_button(array(
			"name" => "import",
			"tooltip" => "Impordi kaustad",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".get_root()."&action=importmenus",
			"imgover" => "import_over.gif",
			"img" => "import.gif"
		));

		$this->vars(array(
			"LINE" => $l,
			"upload" => $this->mk_my_orb("upload"),
			"toolbar" => $tb->get_toolbar(),
			"rootmenu" => get_root()
		));
		return $this->parse();
	}

	function upload($arr)
	{
		extract($arr);
		$this->read_template("upload.tpl");

		$this->mk_path(0,"<a href='".$this->mk_my_orb("list")."'>Nimekiri</a> / Uploadi fail");

		$tb = get_instance("toolbar");

		$_mn = get_instance("menuedit");
		$_mn->read_template("java_popup_menu.tpl");

		$host = str_replace("http://","",$_mn->cfg["baseurl"]);
		preg_match("/.*:(.+?)/U",$host, $mt);
		if ($mt[1])
		{
			$host = str_replace(":".$mt[1], "", $host);
		}

		// make applet for adding objects
		$_mn->vars(array(
			"icon_over" => $_mn->cfg["baseurl"]."/automatweb/images/icons/new2_over.gif",
			"icon" => $_mn->cfg["baseurl"]."/automatweb/images/icons/new2.gif",
			"oid" => get_root(),
			"bgcolor" => "#D4D7DA",
			"nr" => 2,
			"key" => "addmenu",
			"val" => 1,
			"name" => "",
			"height" => 22,
			"width" => 23,
			"url" => $host,
			"content" => $this->get_add_menu(array("section" => get_root()))
		));
		$up = $_mn->parse("URLPARAM");
		$_mn->vars(array(
			"URLPARAM" => $up,
			"FETCHCONTENT" => $_mn->parse("FETCHCONTENT")
		));

		$tb->add_cdata($_mn->parse());


		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.a.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif"
		));

		$tb->add_button(array(
			"name" => "ules",
			"tooltip" => "&Uuml;les",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".get_root()."&aip=1",
			"imgover" => "kaust_tagasi_over.gif",
			"img" => "kaust_tagasi.gif"
		));

		$tb->add_button(array(
			"name" => "import",
			"tooltip" => "Impordi kaustad",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".get_root()."&action=importmenus",
			"imgover" => "import_over.gif",
			"img" => "import.gif"
		));

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_upload"),
			"toolbar" => $tb->get_toolbar(),
			"rootmenu" => get_root()
		));
		return $this->parse();
	}

	function submit_upload($arr)
	{
		extract($arr);

		global $file;
		$cfs = array();
		if (is_uploaded_file($file))
		{
			$fc = file($file);

			$pre = "";
			foreach($fc as $line)
			{
				if (strpos($line,"%") !== false)
				{
					$res = explode("%", $line);
					$ar = array();
					$ar["name"] = $pre." ".trim($res[0]);
					$ar["act_time"] = strtotime(trim($res[1]));
//					$ar["j_time"] = strtotime(trim($res[2]));
					$cfs[] = $ar;
				}
				else
				{
					$pre = trim($line);
					list($pre,$nr) = explode(" ", $pre);
				}
			}
		}

		// now iterate over cfs and update files
		foreach($cfs as $ar)
		{
			// find the file by name
			$oid = $this->db_fetch_field("SELECT id FROM aip_files WHERE filename LIKE '%".$ar["name"]."%'","id");
			
//			echo "name = $ar[name] oid = $oid<br>\n";
//			flush();
			if ($oid)
			{
				$this->upd_object(array(
					"oid" => $oid,
					"metadata" => array(
						"upd_type" => $type,
						"act_time" => $ar["act_time"],
//						"j_time" => $ar["j_time"]
					)
				));
			}
		}
		$this->db_query("INSERT INTO aip_ctl_list_log (created, createdby) VALUES('".time()."','".aw_global_get("uid")."')");
		return $this->mk_my_orb("upload", array(), "", false, true);
	}

	function get_add_menu($arr)
	{
		extract($arr);
		$ob = $this->get_object($section);

		$ret = "";
		$ret .= "1|0|Lisa PDF|".$this->mk_my_orb("new", array("is_aip" => 1, "parent" => $section,"return_url" => urlencode(aw_ini_get("baseurl")."/index.aw?aip=1&section=$section")),"file",false,true)."|_top#";

		$ret .= "2|0|Lisa kaust|".aw_ini_get("baseurl")."/index.aw?action=addfolder&parent=$ob[parent]"."|_top#";
//		$ret .= "3|0|Impordi kaustad|".aw_ini_get("baseurl")."/index.aw?section=".$section."&action=importmenus"."|_top#";
		$ret .= "3|0|Konfigureeri|".$this->mk_my_orb("configure", array(), "aip_pdf", false, true)."|_top#";

		$ret .= "4|0|Muudatused||_top#";
		$ret .= "5|4|Lisa muudatus|".$this->mk_my_orb("new", array("parent" => 6885), "aip_change", false, true)."|_top#";
		$ret .= "6|4|Nimekiri|".$this->mk_my_orb("list", array(), "aip_change", false, true)."|_top#";

		$ret .= "7|0|Kontrollnimekiri||_top#";
		$ret .= "8|7|Lisa kontrollnimekiri|".$this->mk_my_orb("upload", array(), "aip_ctl_list", false, true)."|_top#";
		$ret .= "9|7|Nimekiri|".$this->mk_my_orb("log", array(), "aip_ctl_list", false, true)."|_top#";
	
		$ret .= "11|0|PDF Üleslaadimine||_top#";
		$ret .= "12|11|Log|".$this->mk_my_orb("log", array(), "aip_pdf", false, true)."|_top#";
		$ret .= "13|11|Failide raport|".$this->mk_my_orb("report", array(), "aip_pdf", false, true)."|_top#";
		$ret .= "14|11|Uute failide toimetamine|".$this->mk_my_orb("listfiles", array(), "aip_pdf", false, true)."|_top#";


		return $ret;
	}

	function orb_log($arr)
	{
		extract($arr);
		$this->read_template("log.tpl");

		$mid = $this->db_fetch_field("SELECT max(id) as id FROM aip_ctl_list_log", "id");

		$this->db_query("SELECT * FROM aip_ctl_list_log");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"createdby" => $row["createdby"],
				"created" => $this->time2date($row["created"], 2),
				"act" => ($row["id"] == $mid ? "Aktiivne" : "")
			));
			$l .= $this->parse("LINE");
		}
	

			$tb = get_instance("toolbar");


		$_mn = get_instance("menuedit");
		$_mn->read_template("java_popup_menu.tpl");

		$host = str_replace("http://","",$_mn->cfg["baseurl"]);
		preg_match("/.*:(.+?)/U",$host, $mt);
		if ($mt[1])
		{
			$host = str_replace(":".$mt[1], "", $host);
		}

		// make applet for adding objects
		$_mn->vars(array(
			"icon_over" => $_mn->cfg["baseurl"]."/automatweb/images/icons/new2_over.gif",
			"icon" => $_mn->cfg["baseurl"]."/automatweb/images/icons/new2.gif",
			"oid" => get_root(),
			"bgcolor" => "#D4D7DA",
			"nr" => 2,
			"key" => "addmenu",
			"val" => 1,
			"name" => "",
			"height" => 22,
			"width" => 23,
			"url" => $host,
			"content" => $this->get_add_menu(array("section" => get_root()))
		));
		$up = $_mn->parse("URLPARAM");
		$_mn->vars(array(
			"URLPARAM" => $up,
			"FETCHCONTENT" => $_mn->parse("FETCHCONTENT")
		));

		$tb->add_cdata($_mn->parse());

		$tb->add_button(array(
			"name" => "ules",
			"tooltip" => "&Uuml;les",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".get_root()."&aip=1",
			"imgover" => "kaust_tagasi_over.gif",
			"img" => "kaust_tagasi.gif"
		));

		$tb->add_button(array(
			"name" => "import",
			"tooltip" => "Impordi kaustad",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".get_root()."&action=importmenus",
			"imgover" => "import_over.gif",
			"img" => "import.gif"
		));

		$tb->add_button(array(
			"name" => "toimeta",
			"tooltip" => "Toimeta",
			"url" => $this->mk_my_orb("list", array("section" => get_root()),"aip_ctl_list", false,true),
			"imgover" => "edit_over.gif",
			"img" => "edit.gif"
		));

		$this->vars(array(
			"rootmenu" => get_root(),
			"LINE" => $l,
			"toolbar" => $tb->get_toolbar()
		));
		return $this->parse();
	}
}
?>