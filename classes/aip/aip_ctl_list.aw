<?php

class aip_ctl_list extends aw_template
{
	function aip_ctl_list()
	{
		$this->init("aip_ctl_list");
	}

	/**  
		
		@attrib name=list params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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

		$tb->add_button(array(
			"name" => "add",
			"tooltop" => "Uus",
			"url" => "#",
			"onClick" => "return buttonClick(event, 'aw_menu_0');",
			"img" => "new.gif",
			"imgover" => "new_over.gif",
			"class" => "menuButton",
		));

		$tb->add_button(array(
			"name" => "ules",
			"tooltip" => "&Uuml;les",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".aip::get_root()."&aip=1",
			"imgover" => "kaust_tagasi_over.gif",
			"img" => "kaust_tagasi.gif"
		));

		$tb->add_button(array(
			"name" => "import",
			"tooltip" => "Impordi kaustad",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".aip::get_root()."&action=importmenus",
			"imgover" => "import_over.gif",
			"img" => "import.gif"
		));

		$this->vars(array(
			"LINE" => $l,
			"upload" => $this->mk_my_orb("upload"),
			"toolbar" => $tb->get_toolbar().get_add_menu(array("section" => aip::get_root())),
			"rootmenu" => aip::get_root()
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=upload params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function upload($arr)
	{
		extract($arr);
		$this->read_template("upload.tpl");

		$this->mk_path(0,"<a href='".$this->mk_my_orb("list")."'>Nimekiri</a> / Uploadi fail");

		$tb = get_instance("toolbar");

		$tb->add_button(array(
			"name" => "add",
			"tooltop" => "Uus",
			"url" => "#",
			"onClick" => "return buttonClick(event, 'aw_menu_0');",
			"img" => "new.gif",
			"imgover" => "new_over.gif",
			"class" => "menuButton",
		));

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
			"url" => aw_ini_get("baseurl")."/index.aw?section=".aip::get_root()."&aip=1",
			"imgover" => "kaust_tagasi_over.gif",
			"img" => "kaust_tagasi.gif"
		));

		$tb->add_button(array(
			"name" => "import",
			"tooltip" => "Impordi kaustad",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".aip::get_root()."&action=importmenus",
			"imgover" => "import_over.gif",
			"img" => "import.gif"
		));

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_upload"),
			"toolbar" => $tb->get_toolbar().get_add_menu(array("section" => aip::get_root())),
			"rootmenu" => aip::get_root()
		));
		return $this->parse();
	}

	/**  
		
		@attrib name=submit_upload params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_upload($arr)
	{
		extract($arr);

		// format:
		/*
			GEN 3
			0.1-1%2003-08-02 18:20:00%2003-08-02 18:20:00
			0.1-2%2003-08-02 18:20:00%2003-08-02 18:20:00
			0.1-3%2003-08-02 18:20:00%2003-08-02 18:20:00
			AD 2
			0.1-1%2003-08-02 18:20:00%2003-08-02 18:20:00
			0.1-2%2003-08-02 18:20:00%2003-08-02 18:20:00
		*/

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
			
//			echo "name = $ar[name] oid = $oid<br />\n";
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

	/**  
		
		@attrib name=log params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
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


		$tb->add_button(array(
			"name" => "add",
			"tooltop" => "Uus",
			"url" => "#",
			"onClick" => "return buttonClick(event, 'aw_menu_0');",
			"img" => "new.gif",
			"imgover" => "new_over.gif",
			"class" => "menuButton",
		));

		$tb->add_button(array(
			"name" => "ules",
			"tooltip" => "&Uuml;les",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".aip::get_root()."&aip=1",
			"imgover" => "kaust_tagasi_over.gif",
			"img" => "kaust_tagasi.gif"
		));

		$tb->add_button(array(
			"name" => "import",
			"tooltip" => "Impordi kaustad",
			"url" => aw_ini_get("baseurl")."/index.aw?section=".aip::get_root()."&action=importmenus",
			"imgover" => "import_over.gif",
			"img" => "import.gif"
		));

		$tb->add_button(array(
			"name" => "toimeta",
			"tooltip" => "Toimeta",
			"url" => $this->mk_my_orb("list", array("section" => aip::get_root()),"aip_ctl_list", false,true),
			"imgover" => "edit_over.gif",
			"img" => "edit.gif"
		));

		$this->vars(array(
			"rootmenu" => aip::get_root(),
			"LINE" => $l,
			"toolbar" => $tb->get_toolbar().get_add_menu(array("section" => aip::get_root()))
		));
		return $this->parse();
	}
}
?>
