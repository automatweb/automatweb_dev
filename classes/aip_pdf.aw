<?php
define("FILE_STAT_NEW",1);
define("FILE_STAT_MODIFIED",2);
define("FILE_STAT_DELETED",3);
define("FILE_STAT_SAME",4);

define("CREATE_FOLDER",1);
define("CREATE_FILE",2);
define("UPDATE_FILE",3);
define("DELETE_FILE",4);

class aip_pdf extends aw_template
{
	function aip_pdf()
	{
		enter_function("aip_pdf::aip_pdf",array());
		$this->init("aip_pdf");

		$this->statuses = array(
			FILE_STAT_NEW => "<font color='red'>Uus</font>",
			FILE_STAT_MODIFIED => "<font color='red'>Muudetud</font>",
			FILE_STAT_DELETED => "<font color='red'>Kustutatud</font>",
			FILE_STAT_SAME => "Ei ole muudetud"
		);
		exit_function("aip_pdf::aip_pdf");
	}

	function mk_header()
	{
		return make_toolbar($this->section, &$this, "javascript:document.q.submit()", true);
	}

	function listfiles($arr)
	{
		enter_function("aip_pdf::listfiles",array());
		extract($arr);
		$this->read_template("file_list.tpl");

		$folder = $this->get_cval("aip_pdf_upload_folder");
		$parent = $this->get_cval("aip_pdf_aw_folder");
		$this->section = $section;

		if ($folder != "" && $parent)
		{
			$fd = $this->mk_file_list($folder,$parent);
			foreach($fd as $fn => $fstat)
			{
				$this->vars(array(
					"file" => $fn,
					"file_status" => $this->statuses[$fstat]
				));
				$f.=$this->parse("FILE");
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
						$aid = $this->gen_uniq_id();
						$this->vars(array(
							"file" => $fn,
							"action" => "Failile vastavat kataloogi ei leitud, loome uue (".$this->get_new_folder_name_for_file($fn).")",
							"action_id" => $aid,
							"checked" => checked(false)
						));
						$actions[$aid] = array("action" => CREATE_FOLDER,"file" => $fn);
						$a.=$this->parse("CHANGE");
					}
					$aid = $this->gen_uniq_id();
					$this->vars(array(
						"file" => $fn,
						"action" => "Fail lisati, lisame systeemi",
						"action_id" => $aid,
						"checked" => checked(true)
					));
					$actions[$aid] = array("action" => CREATE_FILE,"file" => $fn);
					$a.=$this->parse("CHANGE");
				}
				else
				if ($fstat == FILE_STAT_MODIFIED)
				{
					// file changed, replace and archive old
					$aid = $this->gen_uniq_id();
					$this->vars(array(
						"file" => $fn,
						"action" => "Fail uuendati, arhiveerime vana ja uuendame faili AW's",
						"action_id" => $aid,
						"checked" => checked(true)
					));
					$actions[$aid] = array("action" => UPDATE_FILE,"file" => $fn);
					$a.=$this->parse("CHANGE");
				}
				else
				if ($fstat == FILE_STAT_DELETED)
				{
					// file removed, remove from system. if no files left under menu, remove menu
					$aid = $this->gen_uniq_id();
					$this->vars(array(
						"file" => $fn,
						"action" => "Fail kustutati, kustutame AW'st",
						"action_id" => $aid,
						"checked" => checked(true)
					));
					$actions[$aid] = array("action" => DELETE_FILE,"file" => $fn);
					$a.=$this->parse("CHANGE");
				}
			}
		}

		$ob = get_instance("objects");
		$this->vars(array(
			"FILE" => $f,
			"CHANGE" => $a,
			"header" => $this->mk_header(),
			"folder" => $folder,
			"folders" => $this->picker($parent,$ob->get_list()),
			"reforb" => $this->mk_reforb("submit_list", array("section" => $section)),
			"rootmenu" => get_root()
		));
		exit_function("aip_pdf::listfiles");
		return $this->parse();
	}

	function submit_list($arr)
	{
		enter_function("aip_pdf::submit_list",array());
		extract($arr);

		set_time_limit(0);
		$co = get_instance("config");
		$co->set_simple_config("aip_pdf_upload_folder", $folder);
		$co->set_simple_config("aip_pdf_aw_folder", $parent);

		// here we do the actions that are selected.
		global $actions;

		$data = "";
		if (is_array($sactions) && $do_actions == 1)
		{
			foreach($sactions as $aid)
			{
				// now get the action from the array in the session so we won't have to find the damn things again
				// and possibly fuck up the order or something. 
				$act = $actions[$aid];
				echo "processing action type ",$act["action"]," for file ",$act["file"]," <br>";
				$data.= "processing action type ".$act["action"]." for file ".$act["file"]." <br>";
				if ($act["action"] == CREATE_FOLDER)
				{
					echo "createfolder <br>";
					$data .= "createfolder <br>";
					$m = get_instance("menuedit");
					// create new menu
					// for that we have to split the damn filename into pieces and for each piece check if a menu for that 
					// bit exists already
					$nar = $this->split_filename($act["file"]);

					if (!($par = $this->find_parent_for_file($nar[0],$parent,strlen($nar[0]))))
					{
						echo "no parent for $nar[0] creating under $parent <br>";
						$data .= "no parent for $nar[0] creating under $parent <br>";
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
						$this->flush_menu_cache();
					}

					if (!($par2 = $this->find_parent_for_file($nar[0]." ".$nar[1],$par,strlen($nar[0]." ".$nar[1]))))
					{
						echo "no parent for $nar[1] creating under $par <br>";
						$data .= "no parent for $nar[1] creating under $par <br>";
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
						$this->flush_menu_cache();
					}

					if (!($par3 = $this->find_parent_for_file($nar[0]." ".$nar[1].".".$nar[2],$par2,strlen($nar[0]." ".$nar[1].".".$nar[2]))))
					{
						echo "no parent for $nar[2] creating under $par2 <br>";
						$data .= "no parent for $nar[2] creating under $par2 <br>";
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
						$this->flush_menu_cache();
					}
				}
				else
				if ($act["action"] == CREATE_FILE)
				{
					$f = get_instance("file");
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

					$this->db_query("INSERT INTO aip_files(id,filename,tm,menu_id) VALUES($pid,'$act[file]','".time()."',$pr)");
				}
				else
				if ($act["action"] == UPDATE_FILE)
				{
					// find file id
					$id = $this->db_fetch_field("SELECT id FROM aip_files WHERE filename = '".$act["file"]."'","id");

					if (!$id)
					{
						echo "<font color=red>ERROR: no such file $act[file] <br>";
						$data .= "<font color=red>ERROR: no such file $act[file] <br>";
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
		exit_function("aip_pdf::submit_list");

		// add log entry for pdf upload
		$this->quote(&$data);
		$this->db_query("INSERT INTO aip_pdf_log (createdby, created, content) VALUES('".aw_global_get("uid")."','".time()."','$data')");
		die("<a href='".$this->mk_my_orb("listfiles", array("section" => $section),"",false,true)."'>Tagasi</a>");
	}

	function get_file_data($parent)
	{
		enter_function("aip_pdf::get_file_data",array());
		$this->mk_menu_cache();
		$menus = array();
		$this->get_menus_below($parent,$menus);

		$ret = array();
		$this->db_query("SELECT aip_files.*,objects.parent as parent FROM aip_files LEFT JOIN objects ON objects.oid = aip_files.id WHERE objects.status != 0");
		while($row = $this->db_next())
		{
			// now check if the parent menu exists
			if ($this->mar[$row["parent"]])
			{
				$ret[$row["filename"]] = $row;
			}
			else
			{
				$this->save_handle();
				$this->db_query("DELETE FROM aip_files WHERE id = $row[id]");
				$this->restore_handle();
			}
		}
		exit_function("aip_pdf::get_file_data");
		return $ret;
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
				if ($file != "." && $file != ".." && !is_dir($folder."/".$file))
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

	function flush_menu_cache()
	{
		enter_function("aip_pdf::flush_menu_cache",array());
		$this->mpr = false;
		exit_function("aip_pdf::flush_menu_cache");
	}

	function mk_menu_cache()
	{
		enter_function("aip_pdf::mk_menu_cache",array());
		if (!is_array($this->mpr))
		{
			$this->_list_menus(array("where" => "objects.status != 0","lang_id" => aw_global_get("lang_id")));
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

	function get_new_folder_name_for_file($fn)
	{
		enter_function("aip_pdf::get_new_folder_name_for_file",array());
		list($n,$pt) = explode(" ",$fn);
		list($pt1,$ptn) = explode(".",$pt);
		list($pt2,$t) = explode("-",$ptn);
		exit_function("aip_pdf::get_new_folder_name_for_file");
		return $n."/".$pt1."/".$pt2;
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

	function orb_log($arr)
	{
		extract($arr);
		$this->read_template("log.tpl");

		$mid = $this->db_fetch_field("SELECT max(id) as id FROM aip_pdf_log","id");

		$this->db_query("SELECT * FROM aip_pdf_log ORDER BY created ASC");
		while($row = $this->db_next())
		{
			$this->vars(array(
				"created" => $this->time2date($row["created"], 2),
				"createdby" => $row["createdby"],
				"act" => ($row["id"] == $mid ? "Aktuaalne" : ""),
				"ajalugu" => $this->mk_my_orb("view_log", array("id" => $row["id"]), "", false, true)
			));
			$l .= $this->parse("LINE");
		}
		$this->section = get_root();
		$this->vars(array(
			"LINE" => $l,
			"rootmenu" => get_root(),
			"toolbar" => $this->mk_header()
		));
		return $this->parse();
	}

	function view_log($arr)
	{
		extract($arr);
		$row = $this->db_fetch_row("SELECT * FROM aip_pdf_log WHERE id = $id");
		$this->read_template("view_log.tpl");

		$mid = $this->db_fetch_field("SELECT max(id) as id FROM aip_pdf_log","id");
		$this->section = get_root();
		$this->vars(array(
			"created" => $this->time2date($row["created"], 2),
			"createdby" => $row["createdby"],
			"act" => ($row["id"] == $mid ? "Aktuaalne" : ""),
			"ajalugu" => $row["log"],
			"rootmenu" => get_root(),
			"toolbar" => $this->mk_header()
		));
		return $this->parse();
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