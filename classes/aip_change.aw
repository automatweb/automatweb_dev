<?php
define("FILE_STAT_NEW",1);
define("FILE_STAT_MODIFIED",2);
define("FILE_STAT_DELETED",3);
define("FILE_STAT_SAME",4);

define("CREATE_FOLDER",1);
define("CREATE_FILE",2);
define("UPDATE_FILE",3);
define("DELETE_FILE",4);

class aip_change extends aw_template
{
	function aip_change()
	{
		$this->init("aip_change");
		lc_site_load("aip_change", &$this);
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path(0, "<a href='".$this->mk_my_orb("list")."'>Nimekiri</a> / Lisa muudatus");

		load_vcl("date_edit");
		$de = new date_edit("act_time");
		$de->configure(array(
			"year" => 1,
			"month" => 1,
			"day" => 1,
			"hour" => 1,
			"minute" => 1,
			"classid" => "formselect"
		));

		$this->vars(array(
			"act_time" => $de->gen_edit_form("act_time", time()),
			"j_time" => $de->gen_edit_form("j_time", time()),
			"files" => $this->multiple_option_list(array(), $this->get_chfile_list()),
			"types" => $this->picker(0,array("1" => "AIP AMDT", "2" => "AIRAC AIP AMDT")),
			"reforb" => $this->mk_reforb("submit"),
			"toolbar" => $this->make_toolbar("javascript:document.q.submit()"),
			"rootmenu" => get_root(),
			"date" => $this->time2date(time(), 2)
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		load_vcl("date_edit");
		$de = new date_edit("act_time");

		classload("scheduler");
		$sch = new scheduler;

		if ($id)
		{
			$ch = $this->load($id);
			$sch->remove(array(
				"event" => $this->mk_my_orb("do_change", array("id" => $id))
			));

			$f = get_instance("file");

			global $change_pdf_1, $change_pdf_2, $change_pdf_3;
			if (is_uploaded_file($change_pdf_1))
			{
				$f1_dat = $f->add_upload_image("change_pdf_1", 1, $ch["meta"]["pfiles"]["1"]["id"]);
			}
			else
			{
				$f1_dat = $ch["meta"]["pfiles"]["1"];
			}

			if (is_uploaded_file($change_pdf_2))
			{
				$f2_dat = $f->add_upload_image("change_pdf_2", 1, $ch["meta"]["pfiles"]["2"]["id"]);
			}
			else
			{
				$f2_dat = $ch["meta"]["pfiles"]["2"];
			}

			if (is_uploaded_file($change_pdf_3))
			{
				$f3_dat = $f->add_upload_image("change_pdf_3", 1, $ch["meta"]["pfiles"]["3"]["id"]);
			}
			else
			{
				$f3_dat = $ch["meta"]["pfiles"]["3"];
			}

			if ($del_chp_1 == 1)
			{
				$f1_dat = array();
			}
			if ($del_chp_2 == 1)
			{
				$f2_dat = array();
			}
			if ($del_chp_3 == 1)
			{
				$f3_dat = array();
			}

			$this->upd_object(array(
				"oid" => $id, 
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"act_time" => $de->get_timestamp($act_time),
					"j_time" => $de->get_timestamp($j_time),
					"files" => $this->make_keys($files),
					"upd_type" => $type,
					"pfiles" => array(
						"1" => $f1_dat,
						"2" => $f2_dat,
						"3" => $f3_dat
					)
				)
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent, 
				"class_id" => CL_AIP_CHANGE,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"act_time" => $de->get_timestamp($act_time),
					"j_time" => $de->get_timestamp($j_time),
					"files" => $this->make_keys($files),
					"upd_type" => $type
				)
			));
		}

		$sch->add(array(
			"time" => $de->get_timestamp($act_time),
			"event" => $this->mk_my_orb("do_change", array("id" => $id))
		));
		return $this->mk_my_orb("change", array("id" => $id), "", false, true);
	}

	function change($arr)
	{
		extract($arr);
		$ch = $this->load($id);
		$this->mk_path(0, "<a href='".$this->mk_my_orb("list")."'>Nimekiri</a> / Muuda");
		$this->read_template("add.tpl");

		load_vcl("date_edit");
		$de = new date_edit("act_time");
		$de->configure(array(
			"year" => 1,
			"month" => 1,
			"day" => 1,
			"hour" => 1,
			"minute" => 1,
			"classid" => "formselect"
		));

		$this->vars(array(
			"cur_pdf_1" => file::check_url($ch["meta"]["pfiles"]["1"]["url"]),
			"cur_pdf_2" => file::check_url($ch["meta"]["pfiles"]["2"]["url"]),
			"cur_pdf_3" => file::check_url($ch["meta"]["pfiles"]["3"]["url"]),
		));

		if ($ch["meta"]["pfiles"]["1"]["id"])
		{
			$this->vars(array("IS_PDF1" => $this->parse("IS_PDF1")));
		}

		if ($ch["meta"]["pfiles"]["2"]["id"])
		{
			$this->vars(array("IS_PDF2" => $this->parse("IS_PDF2")));
		}

		if ($ch["meta"]["pfiles"]["3"]["id"])
		{
			$this->vars(array("IS_PDF3" => $this->parse("IS_PDF3")));
		}

		$this->vars(array(
			"act_time" => $de->gen_edit_form("act_time", $ch["meta"]["act_time"]),
			"j_time" => $de->gen_edit_form("j_time", $ch["meta"]["j_time"]),
			"files" => $this->multiple_option_list($ch["meta"]["files"], $this->get_chfile_list($ch["meta"]["files"])),
			"name" => $ch["name"],
			"types" => $this->picker($ch["meta"]["upd_type"],array("1" => "AIP AMDT", "2" => "AIRAC AIP AMDT")),
			"toolbar" => $this->make_toolbar("javascript:this.document.q.submit()"),
			"reforb" => $this->mk_reforb("submit", array("id" => $id)),
			"rootmenu" => get_root(),
			"comment" => $ch["comment"],
			"date" => $this->time2date(time(), 2)
		));
		return $this->parse();
	}

	function orb_list($arr)
	{
		extract($arr);
		$this->read_template("list.tpl");

		$chd = $this->get_cval("aip_change::change_dir");
		$act_1 = $this->get_cval("aip_change::act_change_1");
		$act_2 = $this->get_cval("aip_change::act_change_2");

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_AIP_CHANGE." AND status != 0");
		while ($row = $this->db_next())
		{
			$meta = $this->get_object_metadata(array(
				"metadata" => $row["metadata"]
			));
			$this->vars(array(
				"name" => $row["name"],
				"time" => $this->time2date($meta["act_time"], 2),
				"j_time" => $this->time2date($meta["j_time"], 2),
				"id" => $row["oid"],
				"checked_1" => checked($act_1 == $row["oid"]),
				"checked_2" => checked($act_2 == $row["oid"]),
				"change" => $this->mk_my_orb("change", array("id" => $row["oid"])),
				"delete" => $this->mk_my_orb("delete", array("id" => $row["oid"])),
				"activate" => $this->mk_my_orb("do_change", array("id" => $row["oid"]))
			));
			$l.= $this->parse("LINE");
		}
		$this->vars(array(
			"change_dir" => $chd,
			"LINE" => $l,
			"add" => $this->mk_my_orb("new", array("parent" => 1)),
			"reforb" => $this->mk_reforb("submit_list"),
			"toolbar" => $this->make_toolbar("javascript:document.q.submit()"),
			"date" => $this->time2date(time(), 2),
			"rootmenu" => get_root()
		));
		return $this->parse();
	}

	function make_toolbar($savelink)
	{
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
			"url" => $savelink,
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
			"name" => "skriptid",
			"tooltip" => "PDF-de üleslaadimine",
			"url" => $this->mk_my_orb("log", array(),"aip_pdf", false,true),
			"imgover" => "pdf_upload_over.gif",
			"img" => "pdf_upload.gif"
		));
		return $tb->get_toolbar();
	}

	function submit_list($arr)
	{
		extract($arr);

		classload("config");
		$co = new config;
		$co->set_simple_config("aip_change::change_dir", $change_dir);
		$co->set_simple_config("aip_change::act_change_1", $act_1);
		$co->set_simple_config("aip_change::act_change_2", $act_2);

		return $this->mk_my_orb("list", array(), "", false, true);
	}

	function load($id)
	{
		return $this->get_object($id);
	}

	function orb_delete($arr)
	{
		extract($arr);

		classload("scheduler");
		$sched = new scheduler;
		$sched->remove(array(
			"event" => $this->mk_my_orb("do_change", array("id" => $id))
		));
		$this->delete_object($id);
		header("Location: ".$this->mk_my_orb("list", array(), "", false, true));
		die();
	}

	function get_chfile_list($add = array())
	{
		$ret = array();

		$dir = $this->get_cval("aip_change::change_dir");
		if ($dir = @opendir($dir)) 
		{
		  while (($file = readdir($dir)) !== false) 
			{
				if (!($file == "." || $file == ".."))
				{
					$ret[$file] = $file;
				}
		  }  
		  closedir($dir);
		}

		return array_merge($ret,$add);
	}

	function do_change($arr)
	{
		extract($arr);
		$folder = $this->get_cval("aip_change::change_dir");
		$this->ob = $this->get_object($id);
		$parent = $this->get_cval("aip_pdf_aw_folder");
		$this->section = $section;

		if ($folder != "" && $parent)
		{
			$fd = $this->mk_file_list($folder,$parent);
			if (!is_array($fd))
			{
				$fd = array();
			}
			global $actions;
			session_register("actions");
			$actions = array();

			// figure out what changes must be made and make a list and remember them
			foreach($fd as $fn => $fstat)
			{
				$msg = "";
				if ($fstat == FILE_STAT_NEW)
				{
					// new file. try and figure out under which folder we should add it. 
					$_tt = $this->split_filename($fn);
					$pr = $this->find_parent_for_file($fn,$parent,strlen($_tt[0]." ".$_tt[1].".".$tt[2]));
					if (!$pr)
					{
						// if no parent found, this means we must create new folder for file.
						// well, actually we will no longer create folders by default..
//						$aid = $this->gen_uniq_id();
//						$actions[$aid] = array("action" => CREATE_FOLDER,"file" => $fn);
					}
					$aid = $this->gen_uniq_id();
					$actions[$aid] = array("action" => CREATE_FILE,"file" => $fn);
				}
				else
				if ($fstat == FILE_STAT_MODIFIED)
				{
					// file changed, replace and archive old
					$aid = $this->gen_uniq_id();
					$actions[$aid] = array("action" => UPDATE_FILE,"file" => $fn);
				}
				else
				if ($fstat == FILE_STAT_DELETED)
				{
					// file removed, remove from system. if no files left under menu, remove menu
					$aid = $this->gen_uniq_id();
					$actions[$aid] = array("action" => DELETE_FILE,"file" => $fn);
				}
			}
		}
	
	
		set_time_limit(0);
		if (is_array($actions))
		{
			foreach($actions as $aid => $act)
			{
				// now get the action from the array in the session so we won't have to find the damn things again
				// and possibly fuck up the order or something. 
				echo "processing action type ",$act["action"]," for file ",$act["file"]," <br>";
				if ($act["action"] == CREATE_FOLDER)
				{
					echo "createfolder <br>";
					classload("menuedit");
					$m = new menuedit;
					// create new menu
					// for that we have to split the damn filename into pieces and for each piece check if a menu for that 
					// bit exists already
					$nar = $this->split_filename($act["file"]);

					if (!($par = $this->find_parent_for_file($nar[0],$parent,strlen($nar[0]))))
					{
						echo "no parent for $nar[0] creating under $parent <br>";
						// no 1st level menu,create it
						$par = $m->add_new_menu(array(
							"name" => $nar[0],
							"parent" => $parent,
						));
						$this->set_object_metadata(array(
							"oid" => $par,
							"key" => "aip_filename",
							"value" => $nar[0]
						));
						$m = get_instance("menuedit");
						$m->invalidate_menu_cache(array());
					}

					if (!($par2 = $this->find_parent_for_file($nar[0]." ".$nar[1],$par,strlen($nar[0]." ".$nar[1]))))
					{
						echo "no parent for $nar[1] creating under $par <br>";
						// no 2nd level menu,create it
						$par2 = $m->add_new_menu(array(
							"name" => $nar[1],
							"parent" => $par,
							"jrk" => $nar[1]
						));
						$this->set_object_metadata(array(
							"oid" => $par2,
							"key" => "aip_filename",
							"value" => $nar[0]." ".$nar[1]
						));
						$m = get_instance("menuedit");
						$m->invalidate_menu_cache(array());
					}

					if (!($par3 = $this->find_parent_for_file($nar[0]." ".$nar[1].".".$nar[2],$par2,strlen($nar[0]." ".$nar[1].".".$nar[2]))))
					{
						echo "no parent for $nar[2] creating under $par2 <br>";
						// no 3rd level menu,create it
						$par3 = $m->add_new_menu(array(
							"name" => $nar[2],
							"parent" => $par2,
							"jrk" => $nar[2]
						));
						$this->set_object_metadata(array(
							"oid" => $par3,
							"key" => "aip_filename",
							"value" => $nar[0]." ".$nar[1].".".$nar[2]
						));
						$m = get_instance("menuedit");
						$m->invalidate_menu_cache(array());
					}
				}
				else
				if ($act["action"] == CREATE_FILE)
				{
					classload("file");
					$f = new file;
					$fc = $this->get_file(array(
						"file" => $folder."/".$act["file"],
					));

					$_tt = $this->split_filename($act["file"]);
					$pr = $this->find_parent_for_file($act["file"],$parent,strlen($_tt[0]." ".$_tt[1].".".$_tt[2]));

					$pid = $this->new_object(array(
						"parent" => $pr,
						"name" => $act["file"],
						"class_id" => CL_FILE,
						"jrk" => $_tt[3]
					));

					$this->quote(&$fc);
					$this->quote(&$fc);
					$this->db_query("INSERT INTO files (id,type,content)
							VALUES('$pid','application/pdf','$fc')");
	
					$_sz = filesize($folder."/".$act["file"]);
					$this->set_object_metadata(array(
						"oid" => $pid,
						"key" => "file_size",
						"value" => $_sz
					));
		
					// and now, also add the file's size to the parent folder's size and to all parent folders above it.
//					$f->add_size_to_parents($pr,$_sz);

					$this->db_query("INSERT INTO aip_files(id,filename,tm,menu_id) VALUES($pid,'$act[file]','".time()."','$pr')");
				}
				else
				if ($act["action"] == UPDATE_FILE)
				{
					// find file id
					$id = $this->db_fetch_field("SELECT id FROM aip_files WHERE name = '".$act["file"]."'","id");

					if (!$id)
					{
						echo "<font color=red>ERROR: no such file $act[file] <br>";
					}
					else
					{
						// get old file's size
						$_old_size = $this->get_object_metadata(array(
							"key" => "file_size",
							"oid" => $id
						));

						$pid = $this->upd_object(array(
							"oid" => $id,
						));
						$fc = $this->get_file(array(
							"file" => $folder."/".$act["file"],
						));
						$this->quote(&$fc);
						$this->quote(&$fc);

						$this->db_query("UPDATE files SET content = '$fc'	WHERE id = $id");
						$_sz = filesize($folder."/".$act["file"]);
						$this->set_object_metadata(array(
							"oid" => $id,
							"key" => "file_size", 
							"value" => $_sz
						));
						$this->db_query("UPDATE aip_files SET tm = ".time()." WHERE id = $id");

						$_ob = $this->get_object($id);
//						$f->add_size_to_parents($_ob["parent"],$_sz-$old_size);
					}
				}
				else
				if ($act["action"] == DELETE_FILE)
				{
				}
				flush();
			}
		}
		$ch_dir = $this->get_cval("aip_change::change_dir");
		$pdf_dir = $this->get_cval("aip_pdf_upload_folder");
		if (is_array($this->ob["meta"]["files"]))
		{
			foreach($this->ob["meta"]["files"] as $fil)
			{
				// if the file exists, delete it so we can overwrite
				@unlink($pdf_dir."/".$fil);
				echo "rename ".$ch_dir."/".$fil." to ".$pdf_dir."/".$fil." <br>";
				@rename($ch_dir."/".$fil, $pdf_dir."/".$fil);
			}
		}
		// now figure out all the menus that have this change and set them as active.
		echo "activating menus ... <Br>\n";
		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_PSEUDO." AND status != 0");
		while ($row = $this->db_next())
		{
			$this->save_handle();
			$meta = $this->get_object_metadata(array(
				"metadata" => $row["metadata"]
			));
			if ($meta["aip_active_change"] == $this->ob["oid"])
			{
				$this->upd_object(array(
					"oid" => $row["oid"],
					"status" => 2
				));
				$this->set_object_metadata(array(
					"oid" => $row["oid"],
					"key" => "aip_active_change",
					"value" => 0
				));
				echo "activated menu $row[name] <br>\n";
			}
			$this->restore_handle();
		}
		echo "<a href='".$this->mk_my_orb("list", array(), "aip_change", false, true)."'>Tagasi</a>";
	}

	function show_files($arr)
	{
		extract($arr);
		$this->read_template("show_files.tpl");

		$ids = array();
		$ch = $this->load($this->get_cval("aip_change::act_change_".$type));
		if (is_array($ch["meta"]["files"]))
		{
			foreach($ch["meta"]["files"] as $fi)
			{
				$id = $this->db_fetch_field("SELECT id FROM aip_files WHERE filename LIKE '%".$fi."%'","id");
				if ($id)
				{
					$ids[] = $id;
				}
			}
		}

		$idss = join(",",$ids);
		if ($idss != "")
		{
			$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_FILE." AND status = 2 AND oid IN (".$idss.")");
			while ($row = $this->db_next())
			{
				$meta = $this->get_object_metadata(array(
					"metadata" => $row["metadata"]
				));

				$this->vars(array(
					"name" => str_replace(".pdf","",$row["name"]),
					"j_time" => $this->time2date($meta["j_time"], 2),
					"act_time" => $this->time2date($meta["act_time"], 2),
					"link" => aw_ini_get("baseurl")."/files.aw/id=".$row["oid"]."/".$row["name"]
				));
				$l.=$this->parse("LINE");
			}
		}

		for ($i=1; $i < 4; $i++)
		{
			if ($ch["meta"]["pfiles"][$i]["id"])
			{
				$this->vars(array(
					"url" => file::check_url($ch["meta"]["pfiles"][$i]["url"]),
					"name" => $ch["meta"]["pfiles"][$i]["orig_name"]
				));
				$p.=$this->parse("CHANGE_PDF");
			}
		}

		$this->vars(array(
			"CHANGE_PDF" => $p,
			"LINE" => $l
		));
		return $this->parse();
	}


	////
	// !creates the list of files and checks each file's status and returns them in an array
	function mk_file_list($folder,$parent)
	{
		enter_function("aip_pdf::mk_file_list",array());
		$fd = $this->get_file_data($parent);
		clearstatcache();
		if ($dir = @opendir($folder)) 
		{
			while (($file = readdir($dir)) !== false) 
			{
				if ($file != "." && $file != ".." && !is_dir($folder."/".$file) && isset($this->ob["meta"]["files"][$file]))
				{
					// here we need to figure out the status of the file - new / changed / unchanged
					$mt = filemtime($folder."/".$file);

					if (!$fd[$file])	// enne faili polnud
					{
						$stat = FILE_STAT_NEW;
					}
					else
					if ($fd[$file]["tm"] < $mt)	// fail on uuem kui see mis baasis on
					{
						$stat = FILE_STAT_MODIFIED;
					}
					else
					{
						$stat = FILE_STAT_SAME;
					}

					$ret[$file] = $stat;
				}
			}  
			closedir($dir);
		}
		foreach($fd as $fn => $fd)
		{
			if (!$ret[$fn])
			{
				$ret[$fn] = FILE_STAT_DELETED;
			}
		}
		exit_function("aip_pdf::mk_file_list");
		return $ret;
	}

	function get_file_data($parent)
	{
		enter_function("aip_pdf::get_file_data",array());
		$this->mk_menu_cache();
		$menus = array();
		$this->get_menus_below($parent,$menus);

		$filstr = join(",",map("'%s'", $this->ob["meta"]["files"]));
		if ($filstr == "")
		{
			return array();
		}

		$ret = array();
		$this->db_query("SELECT aip_files.*,objects.parent as parent FROM aip_files LEFT JOIN objects ON objects.oid = aip_files.id WHERE objects.status != 0 AND filename IN ($filstr)");
		while($row = $this->db_next())
		{
			// now check if the parent menu exists
			if ($this->mar[$row["parent"]])
			{
				$ret[$row["filename"]] = $row;
			}
/*			else
			{
				$this->save_handle();
				$this->db_query("DELETE FROM aip_files WHERE id = $row[id]");
				$this->restore_handle();
			}*/
		}
		exit_function("aip_pdf::get_file_data");
		return $ret;
	}

	function mk_menu_cache()
	{
		enter_function("aip_pdf::mk_menu_cache",array());
		if (!is_array($this->mpr))
		{
			$this->_list_menus(array("where" => "objects.status != 0","lang_id" => aw_global_get("lang_id")));
/*			classload("menuedit");
			$m = new menuedit;
			$m->make_menu_caches("objects.status != 0");
			$this->mpr = $m->mpr;
			$this->mar = $m->mar;*/
		}
		exit_function("aip_pdf::mk_menu_cache");
	}

	function get_menus_below($parent,&$ar)
	{
		enter_function("aip_pdf::get_menus_below",array());
		if (is_array($this->mpr[$parent]))
		{
			foreach($this->mpr[$parent] as $row)
			{
				$ar[$row["oid"]] = $row;
				$this->get_menus_below($row["oid"],$ar);
			}
		}
		exit_function("aip_pdf::get_menus_below");
	}

	function split_filename($name)
	{
		enter_function("aip_pdf::split_filename",array());
		list($n,$pt) = explode(" ",$name);
		list($pt1,$ptn) = explode(".",$pt);
		list($pt2,$t) = explode("-",$ptn);
		$t = (int)$t;
		$ret =  array(0 => $n,1 => $pt1, 2 => $pt2,3 => $t);
		exit_function("aip_pdf::split_filename");
		return $ret;
	}

	////
	// !here we must split ehe filename in parts and figure out under what folder to store the damn thing. 
	function find_parent_for_file($f,$parent,$min_len)
	{
		enter_function("aip_pdf::find_parent_for_file",array());
		$this->mk_menu_cache();
		$ar = array();
		$this->get_menus_below($parent,$ar);

		// first, try to find an exact match
		// and if we don't find an exact match, find the closest one.
		// how do we do this? well. we go through all the menus 
		// and for each see how many characters of the filename the setting for the menu specifies. 
		// and return the menu with the greatest number of characters. 

	//	echo "find parent for file $f $parent $min_len <br>";
		$max_menu = 0;
		$max_chars = 0;
		foreach($ar as $mid => $mdat)
		{
			$meta = $mdat["meta"];

			$_tn = basename($f);
			if (strpos($f,".pdf") !== false)
			{
				$_tn = substr($f,0,strlen($f)-4);
			}

			if ($meta["aip_filename"] == $_tn)
			{
				// ok, we found the menu for this file. return it.
		exit_function("aip_pdf::find_parent_for_file");
				return $mid;
			}

			// now remove the page counters and compare without them
			$tnsp = strpos($_tn, "-");
			$tnpg = 0;
			if ($tnsp)
			{
				$_tns = substr($_tn,0,$tnsp);
				$tnpg = (int)substr($_tn, $tnsp+1);
			}
			else
			{
				$_tns = $_tn;
			}
//			echo "tn = $_tn , tns = $_tns tnpg = $tnpg <br>";

			$asp = strpos($meta["aip_filename"], "-");
			$aspg = 0;
			if ($asp)
			{
				$as = substr($meta["aip_filename"],0,$asp);
				$aspg = (int)substr($meta["aip_filename"], $asp+1);
			}
			else
			{
				$as = $meta["aip_filename"];
			}
//		echo "as = $meta[aip_filename] , tns = $as , aspg = $aspg <br>";


			if ($_tns == $as)
			{
				if (!$aspg)
				{
//					echo "match <Br>";
					return $mid;
				}
				if ($tnpg >= $aspg)
				{
//					echo "match <Br>";
					return $mid;
				}
			}
/*			$len = strlen($meta["aip_filename"]);
			if (strncasecmp($meta["aip_filename"],$_tn,$len) == 0)
			{
				// ok strings match. now check if they are longer than the longest match so far
				if ($max_chars < $len)
				{
					$max_menu = $mdat["oid"];
					$max_chars = $len;
				}
			}*/
		}

		if ($max_chars >= $min_len)
		{
		exit_function("aip_pdf::find_parent_for_file");
			return $max_menu;
		}
		else
		{
		exit_function("aip_pdf::find_parent_for_file");
			return false;
		}
		exit_function("aip_pdf::find_parent_for_file");
	}

	function get_add_menu($arr)
	{
		extract($arr);
		$ob = $this->get_object($section);

		$ret = "";
		$ret .= "1|0|Lisa PDF|".$this->mk_my_orb("new", array("is_aip" => 1, "parent" => $section,"return_url" => urlencode(aw_ini_get("baseurl")."/index.aw?aip=1&section=$section")),"file",false,true)."|_top#";

		$ret .= "2|0|Lisa kaust|".aw_ini_get("baseurl")."/index.aw?action=addfolder&parent=$ob[parent]"."|_top#";
		$ret .= "3|0|Impordi kaustad|".aw_ini_get("baseurl")."/index.aw?section=".$section."&action=importmenus"."|_top#";
		$ret .= "4|0|Muudatused||_top#";
		$ret .= "5|4|Lisa muudatus|".$this->mk_my_orb("new", array("parent" => 6885), "aip_change", false, true)."|_top#";
		$ret .= "6|4|Nimekiri|".$this->mk_my_orb("list", array(), "aip_change", false, true)."|_top#";

		$ret .= "7|0|Kontrollnimekiri||_top#";
		$ret .= "8|7|Lisa kontrollnimekiri|".$this->mk_my_orb("upload", array(), "aip_ctl_list", false, true)."|_top#";
		$ret .= "9|7|Nimekiri|".$this->mk_my_orb("log", array(), "aip_ctl_list", false, true)."|_top#";

		return $ret;
	}

	////
	// !Reads in all the menus
	function _list_menus($args = array())
	{
		$where = ($args["where"]) ? $args["where"] : "objects.status != 0";
		$ignore = ($args["ignore"]) ? $args["ignore"] : false;
		$ignore_lang = ($args["lang_ignore"]) ? $args["lang_ignore"] : false;
		$lang_id = $args["lang_id"] ? $args["lang_id"] : aw_global_get("lang_id");

		if (!$ignore)
		{
			// loeme sisse koik objektid
			$aa = sprintf(" AND objects.site_id = '%d' ",$this->cfg["site_id"]);
    };
    if ($this->cfg["lang_menus"] == 1 && $ignore_lang == false)
    {
			$aa .= sprintf(" AND (objects.lang_id='%d' OR menu.type = '%d') ",$lang_id,MN_CLIENT);
    }

     $q = "SELECT objects.oid as oid, 
									objects.parent AS parent,
									objects.name AS name,
									objects.last AS last,
									objects.jrk AS jrk,
									objects.alias AS alias,
									objects.status AS status,
									objects.brother_of AS brother_of,
									objects.metadata AS metadata,
									objects.class_id AS class_id,
									objects.comment AS comment,
									menu.*
					FROM objects 
						      LEFT JOIN menu ON menu.id = objects.oid
          WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")
									AND menu.type != ".MN_FORM_ELEMENT." 
									AND $where $aa
          ORDER BY objects.parent, jrk,objects.created";

//		echo "q = $q <br>";
		if (not($this->db_query($q,false)))
		{
			return false;
		};	

		global $DBY;
		if ($DBY)
		{
			print $q;
		}

		while ($row = $this->db_next(true))
		{
			// some places need raw metadata, others benefit from reading
			// the already uncompressed metainfo from the cache
			$row["meta"] = aw_unserialize($row["metadata"]);
			$row["mtype"] = $row["type"];

			// we need to do this, cause if the name contains quotes, then in the db they will be \\\" , then
			// when php reads them from the db they will be \\"
			// and when aw reads them from php (in db_next) they will be turned into \"
			// so here we need to do another dequote
			// how do they get like that? dunno. - terryf
			$this->dequote(&$row["name"]);
			// Maybe this means that some people come with knives after me sometimes,
			// but I'm pretty sure that we do not need to save unpacked metadata
			// in the cache, since it's available in $row[meta] anyway
			unset($row["metadata"]);
			$this->mpr[$row["parent"]][] = $row;
			$this->mar[$row["oid"]] = $row;
		}

		return true;
	}
}
?>