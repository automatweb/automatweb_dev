<?php

classload("extlinks","config","planner");

define("FN_TYPE_SECID",1);
define("FN_TYPE_NAME",2);
define("FN_TYPE_HASH",3);
define("FN_TYPE_ALIAS",4);

class export extends aw_template
{
	function export()
	{
		$this->init("export");
		$this->menu_cache = get_instance("menu_cache");
		$this->menu_cache->make_caches();

		$this->type2ext = array(
			"text/html" => "html",
			"text/html; charset=iso-8859-1" => "html",
			"text/html; charset=iso-8859-15" => "html",
			"text/css" => "css",
			"text/richtext" => "rtf",
			"image/gif" => "gif",
			"image/jpeg" => "jpg",
			"image/jpg" => "jpg",
			"image/pjpeg" => "jpg",
			"application/pdf" => "pdf",
			"application/x-javascript" => "js",
			"application/zip" => "zip",
			"application/msword" => "doc"
		);
		$this->hash2url[2]["http://editwww.ut.ee/index.aw?section=20&set_lang_id=2"] = "english";
		$this->hash2url[2]["http://editwww.ut.ee/index.aw?set_lang_id=2"] = "english";
	}

	function orb_export($arr)
	{
		extract($arr);
		$this->read_template("export.tpl");

		$folder = $this->get_cval("export::folder");
		if (strpos($folder, $this->cfg["site_basedir"]."/public") !== false)
		{
			$url = $this->cfg["baseurl"].substr($folder, strlen($this->cfg["site_basedir"]."/public"))."/index.html";
		}
		else
		{
			$url = "Veebiv&auml;line";
		}

		$cal_id = $this->get_cval("export::cal_id");
		$event_id = $this->get_cval("export::event_id");
		if (!$cal_id)
		{
			$pl = new planner;
			$pl->submit_add(array("parent" => 1));
			$cal_id = $pl->id;
			$event_id = $pl->bron_add_event(array("parent" => $cal_id,"start" => time(), "end" => time()+1));			

			$c = new config;
			$c->set_simple_config("export::cal_id",$cal_id);
			$c->set_simple_config("export::event_id",$event_id);
		}

		$fn_type = $this->get_cval("export::fn_type");
		if (!$fn_type)
		{
			$fn_type = 3;
		}

		$fr = aw_unserialize($this->get_cval("export::rule_folders".aw_global_get("lang_id")));
		$o = get_instance("objects");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_export"),
			"folder" => $folder,
			"url" => $url,
			"zip_file" => $this->get_cval("export::zip_file"),
			"aw_zip_folder" => $this->picker($this->get_cval("export::aw_zip_folder"),$o->get_list()),
			"aw_zip_fname" => $this->get_cval("export::aw_zip_fname"),
			"sel_period" => $this->mk_my_orb("repeaters", array("id" => $event_id),"cal_event",false,true),
			"automatic" => checked($this->get_cval("export::automatic") == 1),
			"static_site" => checked($this->get_cval("export::static_site") == 1),
			"fn_type_1" => checked($fn_type == FN_TYPE_SECID),
			"fn_type_2" => checked($fn_type == FN_TYPE_NAME),
			"fn_type_3" => checked($fn_type == FN_TYPE_HASH),
			"fn_type_4" => checked($fn_type == FN_TYPE_ALIAS),
			"gen_url" => $this->mk_my_orb("do_export"),
			"rules" => $this->mk_my_orb("rules"),
			"rule_folders" => $this->multiple_option_list($fr,$o->get_list()),
			"public_symlink_name" => $this->get_cval("export::public_symlink_name"),
			"pick_active" => $this->mk_my_orb("pick_active_version", array()),
			"view_log" => $this->mk_my_orb("view_log", array())
		));
		return $this->parse();
	}

	function submit_export($arr)
	{
		extract($arr);

		classload("config");
		$c = new config;
		$c->set_simple_config("export::folder",$folder);
		$c->set_simple_config("export::zip_file",$zip_file);
		$c->set_simple_config("export::aw_zip_folder",$aw_zip_folder);
		$c->set_simple_config("export::aw_zip_fname",$aw_zip_fname);
		$c->set_simple_config("export::automatic",$automatic);
		$c->set_simple_config("export::static_site",$static_site);
		$c->set_simple_config("export::fn_type",$fn_type);
		$c->set_simple_config("export::public_symlink_name",$public_symlink_name);
		$str = aw_serialize($this->make_keys($rule_folders));
		$this->quote(&$str);

		$c->set_simple_config("export::rule_folders".aw_global_get("lang_id"),$str);

		$sched = get_instance("scheduler");
		$sched->remove(array(
			"event" => $this->mk_my_orb("do_export"),
			"rep_id" => $this->get_cval("export::event_id")
		));
		if ($automatic)
		{
			$sched->add(array(
				"event" => str_replace("/automatweb","",$this->mk_my_orb("do_export")),
				"rep_id" => $this->get_cval("export::event_id")
			));
		}

		return $this->mk_my_orb("export");
	}

	function rep_dates($str)
	{
		$str = str_replace("%y", date("Y"),$str);
		$str = str_replace("%m", date("m"),$str);
		$str = str_replace("%d", date("d"),$str);
		$str = str_replace("%h", date("H"),$str);
		$str = str_replace("%n", date("i"),$str);
		return str_replace("%s", date("s"),$str);
	}

	function init_settings()
	{
		$this->fn_type = $this->get_cval("export::fn_type");

		// take the folder thing and add the date to it so we can make several copies in the same folder
		$folder = $this->rep_dates($this->get_cval("export::folder"));
		@mkdir($folder,0777);
 		$this->folder = $folder;

		// add the counter to the folder as folder-cnt
		$counter = (int)$this->get_cval("export::folder_counter");
		if (!$counter)
		{
			$counter = 1;
			$conf = get_instance("config");
			$conf->set_simple_config("export::folder_counter", 1);
		}
		$this->folder .= '/version-'.$counter;
		if (!is_dir($this->folder))
		{
			mkdir($this->folder, 0777);
			$this->db_query("INSERT INTO export_folders(folder, created) VALUES('$this->folder','".time()."')");
		}

		$this->hashes = array();

		// import exclusion list
		if (is_array($this->cfg["exclude_urls"]))
		{
			$this->exclude_urls = $this->cfg["exclude_urls"];
		}

		$this->err_log = array();
		$this->added_files = array();
		$this->removed_files = array();
		$this->changed_files = array();
	}

	function do_export($arr)
	{
		extract($arr);

		$this->start_time = time();
		$this->db_query("INSERT INTO export_log(start, rule_id) VALUES('$this->start_time','$rule_id')");
		$this->log_entry_id = $this->db_last_insert_id();

		$zip_file = $this->rep_dates($this->get_cval("export::zip_file"));
		$aw_zip_folder = $this->get_cval("export::aw_zip_folder");
		$aw_zip_fname = $this->rep_dates($this->get_cval("export::aw_zip_fname"));
		$automatic = $this->get_cval("export::automatic");

		$this->init_settings();

		if (!is_dir($this->folder))
		{
			$this->raise_error(ERR_SITEXPORT_NOFOLDER,"Folder $this->folder does not exist on server!",true);
		}

		if ($rule_id)
		{
			$this->load_rule($rule_id);
		}

		// ok, this is the complicated bit.
		// so, how do we do this? first. forget the time limit, this is gonna take a while.
		set_time_limit(0);
		ignore_user_abort(true);

		echo "exporting site to folder $this->folder ... <br><br>\n\n";
		flush();

		if ($rule_id)
		{
			// if we are doing a rule, do all pages in rule
			foreach($this->loaded_rule["meta"]["menus"] as $mnid)
			{
				$this->fetch_and_save_page(
					$this->cfg["baseurl"]."/index.aw?section=$mnid&set_lang_id=".$this->loaded_rule["lang_id"],
					$this->loaded_rule["lang_id"]
				 );
			}
		}
		else
		{
			// ok, start from the front page
			$this->fetch_and_save_page($this->cfg["baseurl"]."/?set_lang_id=".aw_global_get("lang_id"),aw_global_get("lang_id"));
		}

		// now fetch the empty template page.
		$this->fetch_and_save_page(
			$this->cfg["baseurl"]."/?section=66666666&set_lang_id=".aw_global_get("lang_id"),
			aw_global_get("lang_id"),
			true,
			"page_template.html"
		);

		// copy needed files
		if (is_array($this->cfg["copy_files"]))
		{
			foreach($this->cfg["copy_files"] as $fil)
			{
				$filf = $this->cfg["baseurl"]."/".$fil;
				$fp = fopen($filf,"r");
				$nname = $this->folder."/".$fil;
				if (!is_dir(dirname($nname)))
				{
					@mkdir(dirname($nname),0777);
				}

				$this->put_file(array(
					"file" => $nname,
					"content" => fread($fp, 10000000)
				));
				echo "copied file $fil to $nname <br>";
				fclose($fp);
			}
		}

		// check for files that need to be removed
		// build a list of all the menus and submenus for this rule
		if ($rule_id)
		{
			$o = get_instance("objects");
			$allmenus = array();
			foreach($this->loaded_rule["meta"]["menus"] as $mnid)
			{
				// since rules do not recurse on submenus neither should we here.
//				$allmenus += $o->get_list(false,false,$mnid);
				$allmenus += array($mnid => $mnid);
			}

			// for each menu , get a list of files for that menu from the database
			$ara = new aw_array(array_keys($allmenus));
			$this->db_query("SELECT id,filename,section FROM export_filelist WHERE section IN(".$ara->to_sql().") AND lang_id = ".aw_global_get("lang_id"));
			$files = array();
			while ($row = $this->db_next())
			{
				$fln = basename($row["filename"]);
				$files[$fln] = $row;
			}
			// now go over the list and remove all files that were changed or added by this export
			foreach($this->added_files as $fn)
			{
				$fn = basename($fn['name']);
				unset($files[$fn]);
			}
			foreach($this->changed_files as $fn)
			{
				$fn = basename($fn['name']);
				unset($files[$fn]);
			}
			// and we got the list of files to delete!
			foreach($files as $fn => $fd)
			{
				$this->removed_files[] = $fn;
				unlink($this->folder."/".$fn);
				// ignore folders!
				$this->db_query("DELETE FROM export_filelist WHERE filename LIKE '%fn%'");
				$this->db_query("DELETE FROM export_content WHERE filename LIKE '%$fn%'");
				echo "removing file $fn <br>\n";
				flush();
			}
		}

		@unlink(aw_ini_get("server.tmpdir")."/exp_running.".$this->rule_id);

		if ($zip_file != "")
		{
			// $zip_file contains the path and name of the file into which we should zip the exported site
			// first, delete the old zip
			@unlink($zip_file);
			echo "creating zip file $zip_file <br>\n";
			flush();
			if (!chdir($this->folder))
			{
				echo "can't change dir to $this->folder <br>\n";
			}
			$cmd = aw_ini_get("server.zip_path")." -r $zip_file *";
			$res = `$cmd`;
			echo "created zip file $zip_file<br>\n";
			flush();
		}

		if ($aw_zip_fname != "" && $aw_zip_folder)
		{
			echo "creating zip file $aw_zip_fname in AW <br>\n";
			flush();
			if (!chdir($this->folder))
			{
				echo "can't change dir to temp folder <br>\n";
			}
			$cmd = aw_ini_get("server.zip_path")." -r ".aw_ini_get("server.tmpdir")."/aw_zip_temp.zip *";
			$res = `$cmd`;

			// check if the file already exists
			$oid = $this->db_fetch_field("SELECT oid FROM objects WHERE parent = $aw_zip_folder AND status != 0 AND lang_id = ".aw_global_get("lang_id")." AND class_id = ".CL_FILE." AND name = '$aw_zip_fname'", "oid");

			$f = get_instance("file");

			if ($oid)
			{
				$f->save_file(array(
					"name" => $aw_zip_fname,
					"type" => "application/zip",
					"file_id" => $oid,
					"content" => $this->get_file(array("file" => aw_ini_get("server.tmpdir")."/aw_zip_temp.zip"))
				));
			}
			else
			{
				$f->put(array(
					"filename" => $aw_zip_fname,
					"type" => "application/zip",
					"parent" => $aw_zip_folder,
					"content" => $this->get_file(array("file" => aw_ini_get("server.tmpdir")."/aw_zip_temp.zip"))
				));
			}
			unlink(aw_ini_get("server.tmpdir")."/aw_zip_temp.zip");
			echo "uploaded zip file to AW<br>\n";
			flush();
		}

		echo "creating log entry ...<br>\n";
		flush();
		$this->write_log();
		echo "<br>all done. <br><br>\n\n";
		die();
	}

	function fetch_and_save_page($url, $lang_id, $single_page_only = false, $file_name = false)
	{
		// check if we must stop
		$_stfn = aw_ini_get("server.tmpdir")."/aw.export.stop";
		if (file_exists($_stfn))
		{
			$_fp = fopen($_stfn,"r");
			$rid = fread($_fp, 100);
			fclose($_fp);
			if (($this->rule_id && $rid == $this->rule_id) || !$this->rule_id)
			{
				echo "<b>Found stop flag as ".$_stfn.", shutting down.</b><br>\n";
				$this->err_log[] = array(
					"tm" => time(),
					"msg" => "Found stop flag as ".$_stfn.", shutting down."
				);
				$this->write_log();
				unlink($_stfn);
				die();
			}
		}

		// set export running flag
		if ($this->rule_id)
		{
			touch(aw_ini_get("server.tmpdir")."/exp_running.".$this->rule_id);
		}

		$url = $this->rewrite_link($url);
		$_url = $url;
		//echo "fetch_and_save_page($url, $lang_id) <br>";
		if ($url == "")
		{
			echo "<p><Br>VIGA, tyhi url! </b><Br>";
			$this->err_log[] = array(
				"tm" => time(),
				"msg" => "VIGA, tyhi url!"
			);
		}

		$url = $this->add_session_stuff($url, $lang_id);

		// if we have done this page already, let's not do it again!
		if (isset($this->hashes[$url]) || $this->check_excludes($url))
		{
			$tmp = $this->hashes[$url].".".$this->get_ext_for_link($url,$http_response_header);
//			echo "fetch_and_save_page($_url, $lang_id) returning $tmp <br>";
			return $tmp;
		}

		$this->fsp_level++;

		// here we track the active language in the url
		$t_lang_id = $lang_id;
		if (preg_match("/set_lang_id=(\d*)/", $url,$mt))
		{
			$t_lang_id=$mt[1];
		}

		// if we switch languages, we have to remake menu caches
		$this->menu_cache->make_caches(array("lang_id" => $t_lang_id));
//		echo "made cache for $t_lang_id <br>";

		// set the hash table
		$this->hashes[$url] = $this->get_hash_for_url($url,$t_lang_id);
		$current_section = $this->current_section;

		// read content
//		echo "$url <br>\n";
		$fc = $this->get_page_content($url);

		// pause for set number of seconds
		if ($this->cfg["sleep_between_pages"])
		{
			sleep($this->cfg["sleep_between_pages"]);
		}

		if ($file_name === false)
		{
			$f_name = $this->hashes[$url].".".$this->get_ext_for_link($url,$http_response_header);
			$name = $this->folder."/".$f_name;
		}
		else
		{
			$name = $this->folder."/".$file_name;
		}
		echo "saving $url as $name (req level: $this->fsp_level)<br>\n";
		flush();

		// now. convert all the links in the page
		$this->convert_links($fc,$t_lang_id, $single_page_only, $url);

		$is_print = false;
		if (strpos($url, "print=1") !== false)
		{
			$is_print = true;
		}
		if (strpos($url, "class=document") !== false && strpos($url, "action=print") !== false)
		{
			$is_print = true;
		}
//		echo "url = $url, print = ",($is_print ? "jah" : "ei")," name = $name <br>";
		$this->save_file($fc,$name, $is_print, $current_section, $t_lang_id, $url);

//		echo "fetch_and_save_page($_url, $lang_id) returning $f_name <br>";
		$this->fsp_level--;
		return $f_name;
	}

	function convert_links(&$fc,$lang_id, $single_page_only, $url = false)
	{
//		echo "convert_links(fc,$lang_id) <br>";
		// uukay. so the links we gotta convert are identified by having $baseurl in them. so look for that
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];

		$ends = array("'","\"",">"," ","\n");
		$len = strlen($fc);

		// do a replace for malformed links for img.aw
		$fc = str_replace("\"/img","\"".$baseurl."/img",$fc);
		$fc = str_replace("'/img","'".$baseurl."/img",$fc);
		// fix some other common mistakes 
		$fc = str_replace("\"/index.".$ext,"\"".$baseurl."/index.".$ext,$fc);
		$fc = str_replace("'/index.".$ext,"'".$baseurl."/index.".$ext,$fc);
		// sitemap
		$fc = str_replace("\"/sitemap","\"".$baseurl."/sitemap",$fc);
		$fc = str_replace("'/sitemap","'".$baseurl."/sitemap",$fc);

		// href='/666' type of links
		$fc = preg_replace("/href='\/(\d*)'/iU","href='".$baseurl."/\\1'",$fc);
		$fc = preg_replace("/href=\"\/(\d*)\"/iU","href=\"".$baseurl."/\\1\"",$fc);

//		$fc = preg_replace("/href='\/(\d*)\?automatweb=aw_export'/iU","href='".$baseurl."/\\1'",$fc);
//		$fc = preg_replace("/href=\"\/(\d*)\?automatweb=aw_export\"/iU","href=\"".$baseurl."/\\1\"",$fc);

		$fc = preg_replace("/<form(.*)action=([\"'])http:\/\/(.*)\/(.*)([\"'])(.*)>/isU","<form\\1action=\\2"."__form_action_url__"."/\\4\\5\\6>",$fc);

		$temps = array();

		while (($pos = strpos($fc,$baseurl)) !== false)
		{
			// now find all of the link - we do that by looking for ' " > or space
			$begin = $pos;
			$end = $pos+strlen($baseurl);
			$link = $baseurl;
			while (!in_array($fc[$end],$ends) && $end < $len)
			{
				$end++;
			}

			// correct the link
			$link = $this->rewrite_link(substr($fc,$begin,($end-$begin)));

			if ($this->is_external($link) || $this->is_dynamic($link))
			{
				$fname = gen_uniq_id();
				$temps[$fname] = $link;
			}
			else
			{
				// fetch the page
				if (($this->rule_id && $this->is_out_of_rule($link)) || $single_page_only)
				{
					$link = $this->add_session_stuff($link, $lang_id);

					// if we have done this page already, let's not do it again!
					if (isset($this->hashes[$link]) || $this->check_excludes($link))
					{
						$fname = $this->hashes[$link].".".$this->get_ext_for_link($link,$http_response_header);
					}
					else
					{
						// here we track the active language in the url
						$t_lang_id = $lang_id;
						if (preg_match("/set_lang_id=(\d*)/", $link,$mt))
						{
							$t_lang_id=$mt[1];
						}
						$this->menu_cache->make_caches(array("lang_id" => $t_lang_id));
						$fname = $this->get_hash_for_url($link,$t_lang_id).".".$this->get_ext_for_link($link,$http_response_header);
					}
					$tid = gen_uniq_id();
					$temps[$tid] = $fname;
	//				echo "fname = $fname , tid = $tid <br>";
					$fname = $tid;
				}
				else
				{
					$fname = $this->fetch_and_save_page($link,$lang_id);
				}
			}
			// we still gotta replace the link, even if it is an extlink outta here, 
			// cause otherwise we would end up in an infinite loop

//			echo "replace $link with $fname begin = $begin end = $end , url = $url <br>";
			// replace the link in the html
			$fc = substr($fc,0,$begin).$fname.substr($fc,$end);
		}

		// right, now. this kinda sucks, but that's how it is
		// el no translado forms
		$fc = preg_replace("/<form(.*)action=([\"'])__form_action_url__\/(.*)([\"'])(.*)>/isU","<form\\1action=\\2".$this->cfg["form_server"]."\\4\\5>",$fc);

		// and now replace temp links back
		foreach($temps as $r => $l)
		{
			$fc = str_replace($r,$l, $fc);
		}

		// convert poll links
		$fc = str_replace("$baseurl/poll.aw?", "/dyn.aw?type=poll&", $fc);
//		echo "convert_links(fc,$lang_id) returning <br>";
	}

	function save_file($fc,$name, $no_db = false, $cur_sec = "", $lang_id = "", $url = '')
	{
//		echo "save_file(fc,$name) <br>";
//		echo "saving file as $name <br>\n";
		preg_match("/__global = (.*)/",$fc,$mt);
		if (file_exists($name))
		{
			$this->changed_files[] = array(
				'name' => $name,
				'global' => $mt[1],
				'url' => $url
			);
		}
		else
		{
			$this->added_files[] = array(
				'name' => $name,
				'global' => $mt[1],
				'url' => $url
			);
		}

		$fp = fopen($name,"w");
		fwrite($fp,$fc);
		fclose($fp);
		chmod($name, 0644);

		// now also save file to database, but only if it's a html file
		if (substr($name, -5) == ".html" && !$no_db)
		{
			$this->quote($fc);
			preg_match("/<!-- MODIFIED:(\d*) -->/U", $fc, $mt);
			$fn = basename($name);
			preg_match("/<!-- PAGE_TITLE (.*) \/PAGE_TITLE -->/U", $fc, $mt_t);
			$title = $mt_t[1];
			$this->quote(&$title);
			if (($id = $this->db_fetch_field("SELECT id FROM export_content WHERE filename = '$fn'","id")))
			{
				$this->db_query("UPDATE export_content SET lang_id = '$lang_id', content = '$fc',modified = '$mt[1]', section = '$cur_sec',title = '$title' WHERE id = '$id'");
			}
			else
			{
				$this->db_query("INSERT INTO export_content(filename, content, modified, section, lang_id,title) VALUES('$fn', '$fc','$mt[1]','$cur_sec','$lang_id','$title')");
			}
		}

		if ($cur_sec)
		{
			$name = basename($name);
			$id = $this->db_fetch_field("SELECT id FROM export_filelist WHERE filename = '$name'","id");
			if (!$id)
			{
				$this->db_query("INSERT INTO export_filelist(filename,section,lang_id) VALUES('$name','$cur_sec','$lang_id')");
			}
		}
//		echo "save_file(fc,$name) returning <br>";
	}

	function get_ext_for_link($link, $headers)
	{
//		echo "get_ext_for_link($link, headers) <br>";
		if (isset($this->link2type[$link]))
		{
//			echo "get_ext_for_link($link, headers) returning ",$this->link2type[$link],"<br>";
			return $this->link2type[$link];
		}

		if (count($headers) < 1)
		{
			// we gotta get the page, cause we haven't yet
/*			$fp = fopen($link,"r");
			fread($fp, 1000000);
			fclose($fp);

			$headers = $http_response_header;*/

			$this->get_page_content($link);
			$headers = explode("\n", $this->last_request_headers);
		}

		// deduct the type from the headers - muchos beteros that way
		$ct = "text/html";
		foreach($headers as $hd)
		{
			if (preg_match("/Content\-Type\: (.*)/", $hd, $mt))
			{
				$ct = $mt[1];
			}
		}

		if (!isset($this->type2ext[$ct]))
		{
			echo "<B><font color=red><br>VIGA! EI LEIDNUD ext for type $ct <br></font></b>";
			$this->err_log[] = array(
				"tm" => time(),
				"url" => $link,
				"msg" => "VIGA! EI LEIDNUD ext for type $ct"
			);
		}

		$this->link2type[$link] = $this->type2ext[$ct];
//		echo "get_ext_for_link($link, headers) returning ",$this->link2type[$ct],"<br>";
		return $this->type2ext[$ct];
	}

	////
	// !checks the link and rewrites it, so all section links are the same and some other weirdness to make
	// things work correctly
	function rewrite_link($link)
	{
		if (isset($this->rewrite_link_cache[$link]))
		{
			return $this->rewrite_link_cache[$link];
		}
//		echo "rewrite_link($link) <br>";
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		$frontpage = $this->cfg["frontpage"];
		$basedir = $this->cfg["site_basedir"]."/public";
		$_link = $link;

		$link = str_replace($baseurl."/?",$baseurl."/index.$ext?",$link);
		if (substr($link,0,2) == "/?")
		{
			$link = $baseurl."/index.$ext?".substr($link,2);
		}

		// do link processing as aw would upon request startup
		$ud = parse_url($link);
		if (!preg_match("/(shop.aw|banner.aw|graphs.aw|css|poll|files|ipexplorer|icon.aw|gallery.aw|login|stats|vcl|misc|index|images|feedback|forms|indexx|showimg|sorry|monitor|vv|automatweb|img|reforb|orb)/",$link))
		{
			// treat the damn thing as an alias
			// aliases will not contain ? and & so do this:
			$end = "";
			if (substr($ud["path"],1) != "" || $ud["query"] != "" || $ud["fragment"] != "")
			{
				$sec_str = substr($ud["path"],1,7) == "section" ? "" : "section=";
				$end = "?".$sec_str.str_replace("?", "&",substr($ud["path"],1)."&".$ud["query"].$ud["fragment"]);
			}
			$link = $baseurl."/index.".$ext.$end;
			// now just extract it again
			$ud = parse_url($link);
			// damn, this does not handle urls like http://bla/index.aw?section=2345/oid=333
			parse_str($ud["query"],$HG);
			// so we do some subtle trickery here. basically, section cannot contain =
			// so we check for that
			if (($eqpos = strpos($HG["section"], "=")) !== false)
			{
//				echo "doing weird magick for link $_link <br>";
				$tp = substr($HG["section"], 0, $eqpos);
				$lslpos = strrpos($tp, "/");
				$ttp = substr($tp, 0, $lslpos);
				// now $ttp contains the real section value, so we can replace the / after the section with &
				// and now we should also replace all other /'s with &'s after the end of the section variable
				$seclen = strlen("section=".$ttp."&");
				$aftersec = str_replace("/", "&", substr($ud["query"], $seclen));
				// and now put the full string together again
				$tq = "section=".$ttp."&".$aftersec;
				parse_str($tq,$HG);
//				echo "hg = <pre>", var_dump($HG),"</pre> <br>";
//				echo "returning tq = $tq <br>";
			}

			$js = "";
			foreach($HG as $k => $v)
			{
				if ($k == "section" && !is_number($v))
				{
					// we must turn the section into a number always. 
					$mned = get_instance("menuedit");
					$v = $mned->check_section($v,false);
				}
				if (is_array($v))
				{
					$vs = $this->make_array_url_string($k,$v);
				}
				else
				{
					$vs = $k."=".$v;
				}
				if ($js != "")
				{
					$js.="&";
				}
				$js.=$vs;
			}
//			$js = join("&", map2("%s=%s", $HG));
			if ($js != "")
			{
				$js = "?".$js;
			}
			$link = $baseurl."/index.aw".$js;
//			echo "returned1 $link for $_link <Br>";
//			echo "rewrite_link($_link) returning $link <br>";
			$this->rewrite_link_cache[$_link] = $link;
			return $link;
		}
		else
		{
			// first check if the file exists. if it does, then no rewrite the link.
			if ($this->link_is_file($link))
			{
				$this->rewrite_link_cache[$_link] = $link;
				return $link;
			}
	
			$link = str_replace($this->cfg["baseurl"]."/index.".$this->cfg["ext"], "", $link);
			$link = str_replace($this->cfg["baseurl"]."/reforb.".$this->cfg["ext"], "", $link);
			$link = str_replace($this->cfg["baseurl"]."/login.".$this->cfg["ext"], "", $link);
			$link = $this->do_aw_parse_url($link);
			// now that we got a nice link, check if it is a class=links&action=show, cause those do redirects and
			// php's fopen can't handle that
			if (strpos($link, "class=links") !== false && strpos($link, "action=show") !== false)
			{
				preg_match("/id=(\d*)/", $link,$mt);

				$el = get_instance("extlinks");
				$ld = $el->get_link($mt[1]);
				$link = $ld["url"];
				if (substr($link,0,4) == "http" && strpos($link,$baseurl) === false)
				{
					// external link, should not be touched I guess
//					echo "rewrite_link($_link) returning $link <br>";
					$this->rewrite_link_cache[$_link] = $link;
					return $link;
				}

				if (strpos($link,$baseurl) === false && $link[0] == "/")
				{
					$link = $baseurl.$link;
				}
				$link = $this->rewrite_link($link);
//				echo "rewrote extlink $_link to $link  <br>";
			}
			$this->rewrite_link_cache[$_link] = $link;
//			echo "rewrite_link($_link) returning $link <br>";
			return $link;
		}
	}

	function load_rule($id)
	{
		$this->loaded_rule = $this->get_object($id);
		$this->rule_id = $id;
//		echo "rule = <pre>", var_dump($this->loaded_rule),"</pre> <br>";
	}

	function add_rule($arr)
	{
		extract($arr);
		$this->read_template("add_rule.tpl");
		$this->mk_path($parent,"Lisa");
		$o = get_instance("objects");
		$fr = aw_unserialize($this->get_cval("export::rule_folders".aw_global_get("lang_id")));
		$lst = $o->get_list();
		$ls = array();
		foreach($fr as $mnid)
		{
			$ls[$mnid] = $lst[$mnid];
		}

		$this->vars(array(
			"menus" => $this->multiple_option_list(array(),$ls),
			"reforb" => $this->mk_reforb("submit_rule", array("parent" => $parent))
		));
		return $this->parse();
	}

	function submit_rule($arr)
	{
		extract($arr);

		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"metadata" => array(
					"menus" => $this->make_keys($menus)
				)
			));
		}
		else
		{
			$id = $this->new_object(array(
				"name" => $name,
				"parent" => $parent,
				"class_id" => CL_EXPORT_RULE,
				"metadata" => array(
					"menus" => $this->make_keys($menus),
				)
			));

			$pl = new planner;
			$pl->submit_add(array("parent" => $id));

			$this->upd_object(array(
				"oid" => $id,
				"metadata" => array(
					"cal_id" => $pl->id,
					"event_id" => $pl->bron_add_event(array("parent" => $pl->id,"start" => time(), "end" => time()+1))
				)
			));
		}

		$ob = $this->get_object($id);
		if ($ob['meta']['event_id'])
		{
			$sched = get_instance("scheduler");
			$sched->remove(array(
				"event" => str_replace("/automatweb","",$this->mk_my_orb("do_export", array("rule_id" => $id))),
				"rep_id" => $ob['meta']['event_id']
			));
			$sched->add(array(
				"event" => str_replace("/automatweb","",$this->mk_my_orb("do_export", array("rule_id" => $id))),
				"rep_id" => $ob['meta']['event_id']
			));
		}

		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change_rule($arr)
	{
		extract($arr);
		$this->load_rule($id);
		$this->mk_path($this->loaded_rule["parent"],"Muuda ekspordi ruuli");
		$this->read_template("add_rule.tpl");
		$o = get_instance("objects");
		$fr = aw_unserialize($this->get_cval("export::rule_folders".aw_global_get("lang_id")));
		$lst = $o->get_list();
		$ls = array();
		foreach($fr as $mnid)
		{
			$ls += $o->get_list(false, false, $mnid);
			$ls[$mnid] = $lst[$mnid];
		}
		$this->vars(array(
			"name" => $this->loaded_rule["name"],
			"menus" => $this->multiple_option_list($this->loaded_rule["meta"]["menus"],$ls),
			"stop_rule" => $this->mk_my_orb("stop_rule", array("id" => $id)),
			"reforb" => $this->mk_reforb("submit_rule", array("id" => $id)),
			"sel_period" => $this->mk_my_orb("repeaters", array("id" => $this->loaded_rule["meta"]["event_id"]),"cal_event",false,true),
			"do_rule" => $this->mk_my_orb("do_export", array("rule_id" => $id))
		));
		$this->vars(array(
			"CHANGE" => $this->parse("CHANGE")
		));
		return $this->parse();
	}

	function get_hash_for_url($url, $lang_id)
	{
		if (isset($this->hash2url[$lang_id][$url]))
		{
			return $this->hash2url[$lang_id][$url];
		}

		$this->menu_cache->make_caches(array("lang_id" => $lang_id));
		//echo "get_hash_for_url($url, $lang_id)<br>";
		$fpurls = array(
			$this->cfg["baseurl"]."/?set_lang_id=1&automatweb=aw_export",
			$this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?set_lang_id=1&automatweb=aw_export",
			$this->cfg["baseurl"]."/?automatweb=aw_export&set_lang_id=1",
			$this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?automatweb=aw_export&set_lang_id=1",
			$this->cfg["baseurl"]."/?set_lang_id=1",
			$this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?set_lang_id=1",
			$this->cfg["baseurl"]."/?set_lang_id=1",
			$this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?set_lang_id=1",
			$this->cfg["baseurl"]."/index.".$this->cfg["ext"]."?section=".aw_ini_get("frontpage")."&set_lang_id=1",
		);
		if (in_array($url,$fpurls))
		{
//			echo "get_hash_for_url($url, $lang_id) returning index<br>";
			$this->hash2url[$lang_id][$url] = "index";
			return "index";
		}

		if ($this->fn_type == FN_TYPE_SECID)
		{
			// figure out the section id from the url
			preg_match("/section=(\d*)/",$url,$mt);
			$secid = $mt[1];
			if ($secid)
			{
//				echo "get_hash_for_url($url, $lang_id) returning ",$secid.",".$lang_id,"<br>";
				$this->hash2url[$lang_id][$url] = $secid.",".$lang_id;
				return $secid.",".$lang_id;
			}
			// if no secid, still do the hash thingie
		}
		else
		if ($this->fn_type == FN_TYPE_NAME)
		{
			preg_match("/section=(\d*)/",$url,$mt);
			$secid = $mt[1];
			if ($secid)
			{
				$md = $this->menu_cache->get_cached_menu($secid);
				$mn = $md["name"];
				if ($mn != "")
				{
					$cnt = 1;
					$_res = str_replace(" ", "_", $mn);
					$res = $_res;
					while (isset($this->ftn_used[$res]))
					{
						$res = $_res.",".($cnt++);
					}
					$this->ftn_used[$res] = true;
//					echo "get_hash_for_url($url, $lang_id) returning ",$res,"<br>";
					$this->hash2url[$lang_id][$url] = $res;
					return $res;
				}
			}
		}
		else
		if ($this->fn_type == FN_TYPE_ALIAS)
		{
			$qu = $url;
			$qu = preg_replace("/tbl_sk=([^&$]*)/", "tbl_sk=tbl_sk", $qu);
			$qu = preg_replace("/old_sk=([^&$]*)/", "old_sk=old_sk", $qu);
			$this->quote(&$qu);
			preg_match("/section=([^&=?]*)/",$url,$mt);
			$secid = $mt[1];
			if ($secid != "")
			{
				if (!is_number($secid))
				{
					// secid is alias, resolve it to numeric 
					$mned = get_instance("menuedit");
					$secid = $mned->check_section($secid,false);
				}
				$this->current_section = $secid;

				$mn = "";
				$cnt = 0;

				// we need to check if the section is not a document
				$obj = $this->get_object($secid);
				if ($obj["class_id"] == CL_DOCUMENT)
				{
					$mn = strip_tags($obj["name"])."/";
					$secid = $obj["parent"];
				}

				// here we need to find all the aliases of the menus upto $rootmenu as well and
				// add them together
				while ($secid && ($secid != 1) && $secid != $this->cfg["rootmenu"]) 
				{
					$sec = $this->menu_cache->get_cached_menu($secid);
					$secid = $sec["parent"];
					if ($sec["alias"] != "")
					{
						$mn = $sec["alias"]."/".$mn;
					}
					
					$cnt++;
					if ($cnt > 10)
					{
						break;
					}
				}

				if ($mn[0] == "/")
				{
					$mn = substr($mn,1);
				}
				if (substr($mn,strlen($mn)-1) == "/")
				{
					$mn = substr($mn,0,strlen($mn)-1);
				}
				$mn = str_replace("õ", "o", $mn);
				$mn = str_replace("ä", "a", $mn);
				$mn = str_replace("ö", "o", $mn);
				$mn = str_replace("ü", "u", $mn);
				$mn = str_replace("Ö", "O", $mn);
				$mn = str_replace("Ä", "A", $mn);
				$mn = str_replace("Õ", "O", $mn);
				$mn = str_replace("Ü", "U", $mn);
				$mn = strip_tags($mn);

				if ($mn != "")
				{
					$cnt = 1;
					$_res = str_replace(" ", "_", str_replace("/","_",$mn));
					$_res = str_replace("&nbsp;", "_", $_res);
					$res = $_res;
					
					$mcnt = 0;
					// now check if any files with that name exist in the database
					$row = $this->db_fetch_row("SELECT * FROM export_url2filename WHERE sec_name = '$res'");
					if (is_array($row))
					{
//						echo "found $res filename on db <br>";
						// urls with that name exist
						// check for the current url
						$row = $this->db_fetch_row("SELECT * FROM export_url2filename WHERE url = '$qu'");
						if (is_array($row))
						{
							// found the name for the current url, use it as final filename
							$res = $row['filename'];
//							echo "found url in db $qu filename = $res <Br>";
						}
						else
						{
							// no row for current url, find count and insert new filename and url
							$mcnt = $this->db_fetch_field("SELECT MAX(count) AS cnt FROM export_url2filename WHERE sec_name = '$res'", "cnt")+1;
							$res = $_res.",".$mcnt;
							$this->db_query("INSERT INTO export_url2filename(url, filename, sec_name, count)
								VALUES('$qu','$res','$_res','$mcnt')");
//							echo "did not find url in db, try sec_name $_res , got mcount $mcnt final res = $res <br>";
						}
					}
					else
					{
						// no files by that name exist, insert into the db
						$this->db_query("INSERT INTO export_url2filename(url, filename, sec_name, count)
							VALUES('$qu','$res','$_res','0')");
//						echo "no file by name $res found, insert $qu <br>";
					}
					$this->fta_used[$res] = true;
					$this->hash2url[$lang_id][$url] = $res;
					return $res;
				}
			}
		}

		$tmp = gen_uniq_id(str_replace($this->cfg["baseurl"],"",$url)).",".$lang_id;
//		echo "made hash for link $url = $tmp <br>";
//		echo "get_hash_for_url($url, $lang_id) returning ",$tmp,"<br>";
		$this->hash2url[$lang_id][$url] = $tmp;
		return $tmp;
	}

	function add_session_stuff($url, $lang_id)
	{
		if (strpos($url,"?") === false)
		{
			$sep = "?";
		}
		else
		{
			$sep = "&";
		}

/*		if (strpos($url, "automatweb=aw_export") === false)
		{
			$url = $url.$sep."automatweb=aw_export";
		}*/

		if (strpos($url, "set_lang_id=") === false)
		{
			$url = $url.$sep."set_lang_id=".$lang_id;
		}
		return $url;
	}

	function check_excludes($url)
	{
		if (is_array($this->exclude_urls))
		{
			foreach($this->exclude_urls as $eu)
			{
				if (strncasecmp($url,$eu, strlen($eu)) == 0)
				{
					echo "excluded $url <br>";
					return true;
				}
			}
		}
	}

	function is_external($link)
	{
		if (substr($link, 0, 4) == "ftp:" || (substr($link,0,4) == "http" && strpos($link, $this->cfg["baseurl"]) === false))
		{
//			echo "is_external($link) returning true <br>";
			return true;
		}
//		echo "is_external($link) returning false<br>";
		return false;
	}

	function is_dynamic($link)
	{
		if (strpos($link, "/poll.".$this->cfg["ext"]) !== false)
		{
			return true;
		}
		else
		if (strpos($link, "class=document&action=change") !== false)
		{
			return true;
		}
		return false;
	}

	function is_out_of_rule($url)
	{
		if ($this->link_is_file($url) || strpos($url, "orb.".$this->cfg["ext"]) !== false)
		{
			return false;
		}
		preg_match("/section=([^&=\?]*)/",$url,$mt);
		$secid = $mt[1];
		if ($secid != "")
		{
			if (!is_number($secid))
			{
				$mned = get_instance("menuedit");
//				$seco = $this->_get_object_by_alias($secid);
//				$secid = $seco["oid"];
				$secid = $mned->check_section($secid, false);
			}

			if (is_number($secid))
			{
				do {
					$seco = $this->get_object($secid);
					if ($seco["class_id"] != CL_PSEUDO)
					{
						$secid = $seco["parent"];
					}
				} while ($seco["class_id"] != CL_PSEUDO);

				if ($this->loaded_rule["meta"]["menus"][$secid] == $secid)
				{
					return false;
				}
				else
				{
//					echo "is out of rule $url <Br>";
					return true;
				}
			}
			else
			{
//				echo "is out of rule2 $url <Br>";
				return true;
			}
		}
		else
		{
//			echo "is out of rule3 $url <Br>";
			return true;
		}
	}

	function exp_reset()
	{
		$this->hashes = array();
		$this->link2type = array();
		$this->hash2url = array();
		$this->ftn_used = array();
		$this->fta_used = array();
		$this->hash2url[2]["http://editwww.ut.ee/index.aw?section=20&set_lang_id=2"] = "english";
		$this->hash2url[2]["http://editwww.ut.ee/index.aw?set_lang_id=2"] = "english";
	}
	
	////
	// !this tries to keep session between pages
	function get_page_content($url)
	{
/*		$fp = fopen($url,"r");
		$fc = fread($fp,10000000);
		fclose($fp);*/
		if (!$this->cookie)
		{
			$this->get_session();
		}

		$host = str_replace("http://","",$this->cfg["baseurl"]);
		preg_match("/.*:(.+?)/U",$host, $mt);
		if ($mt[1])
		{
			$host = str_replace(":".$mt[1], "", $host);
		}
		$port = ($mt[1] ? $mt[1] : 80);

		$req  = "GET $url HTTP/1.0\r\n";
		$req .= "Host: ".$host.($port != 80 ? ":".$port : "")."\r\n";
		$req .= "Cookie: automatweb=".$this->cookie."\r\n";
		$req .= "\r\n";
		classload("socket");
		$socket = new socket(array(
			"host" => $host,
			"port" => $port,
		));
		$socket->write($req);
		$ipd = "";
		while($data = $socket->read(10000000))
		{
			$ipd .= $data;
		};
		list($headers,$data) = explode("\r\n\r\n",$ipd,2);
		$this->last_request_headers = $headers;

		// check the data for errors
		if (strpos($headers, "X-AW-Error: 1") !== false)
		{
			preg_match("/<b>AW_ERROR: (.*)<\/b>/",$data,$mt);
			$this->err_log[] = array(
				"tm" => time(),
				"url" => $url,
				"msg" => $mt[1]
			);
		}
		return $data;
	}

	function get_session()
	{
		$host = str_replace("http://","",$this->cfg["baseurl"]);
		preg_match("/.*:(.+?)/U",$host, $mt);
		if ($mt[1])
		{
			$host = str_replace(":".$mt[1], "", $host);
		}
		$port = ($mt[1] ? $mt[1] : 80);
		classload("socket");
		$socket = new socket(array(
			"host" => $host,
			"port" => $port,
		));
		
		$op = "HEAD / HTTP/1.0\r\n";
		$op .= "Host: $host".($port != 80 ? ":".$port : "" )."\r\n\r\n";

		print "<pre>";
		print "Acquiring session\n";
		flush();

//		echo "op = $op <br>";
		$socket->write($op);

		$ipd="";
		
		while($data = $socket->read())
		{
			$ipd .= $data;
		};

//		echo "ipd = $ipd <br>";
		if (preg_match("/automatweb=(\w+?);/",$ipd,$matches))
		{
			$cookie = $matches[1];
		};

		$this->cookie = $cookie;

		print "Got session, ID is $cookie\n</pre>";
	}

	function make_array_url_string($k,$v)
	{
		$ret = array();
		foreach($v as $_k => $_v)
		{
			if (is_array($_v))
			{
				$ret[] =$this->make_array_url_string($k."[$_k]", $_v);
			}
			else
			{
				$ret[] =$k."[]=".urlencode($_v);
			}
		}
		return join("&", $ret);
	}

	function do_aw_parse_url($link)
	{
		$_link = $link;
//		echo "enter do_aw_parse_url($link) <br>\n\n";
//		flush();
		$pi = str_replace($this->cfg["baseurl"], "", $link);

		$HG = array();
		if ($pi) 
		{
			// if $pi contains & or = 
			if (preg_match("/[&|=]/",$pi)) 
			{
				// expand and import PATH_INFO
				// replace ? and / with & in $pi and output the result to HTTP_GET_VARS
				// why so?
				parse_str(str_replace("?","&",str_replace("/","&",$pi)),$HG);
		//		echo "gv = <pre>", var_dump($HTTP_GET_VARS),"</pre> <br>";
			} 

			if (($_pos = strpos($pi, "section=")) === false)
			{
				// ok, we need to check if section is followed by = then it is not really the section but 
				// for instance index.aw/set_lang_id=1
				// we check for that like this:
				// if there are no / or ? chars before = then we don't prepend

				$qpos = strpos($pi, "?");
				$slpos = strpos($pi, "/");
				$eqpos = strpos($pi, "=");
				$qpos = $qpos ? $qpos : 20000000;
				$slpos = $slpos ? $slpos : 20000000;

				if (!$eqpos || ($eqpos > $qpos || $slpos > $qpos))
				{
					// if no section is in url, we assume that it is the first part of the url and so prepend section = to it
					$pi = str_replace("?", "&", "section=".substr($pi, 1));
				}
			}

			if (($_pos = strpos($pi, "section=")) !== false)
			{
				// this here adds support for links like http://bla/index.aw/section=291/lcb=117
				$t_pi = substr($pi, $_pos+strlen("section="));
				if (($_eqp = strpos($t_pi, "="))!== false)
				{
					$t_pi = substr($t_pi, 0, $_eqp);
					$_tpos1 = strpos($t_pi, "?");
					$_tpos2 = strpos($t_pi, "&");
					if ($_tpos1 !== false || $_tpos2 !== false)
					{
						// if the thing contains ? or & , then section is the part before it
						if ($_tpos1 === false)
						{
							$_tpos = $_tpos2;
						}
						else
						if ($_tpos2 === false)
						{
							$_tpos = $_tpos1;
						}
						else
						{
							$_tpos = min($_tpos1, $_tpos2);
						}
						$section = substr($t_pi, 0, $_tpos);
					}
					else
					{
						// if not, then te section is the part upto the last /
						$_lslp = strrpos($t_pi, "/");
						if ($_lslp !== false)
						{
							$section = substr($t_pi, 0, $_lslp);
						}
						else
						{
							$section = $t_pi;
						}
					}
				}
				else
				{
					$section = $t_pi;
				}
			}
		};
		if ($section != "")
		{
			$HG["section"] = $section;
		}
		$js = "";
		foreach($HG as $k => $v)
		{
			if ($k == "section" && !is_number($v))
			{
				// we must turn the section into a number always. 
				$mned = get_instance("menuedit");
				$v = $mned->check_section($v,false);
			}
			if ($k == "section" && trim($v) === "")
			{
				continue;
			}
			if (is_array($v))
			{
				$vs = $this->make_array_url_string($k,$v);
			}
			else
			{
				$vs = $k."=".$v;
			}
			if ($js != "")
			{
				$js.="&";
			}
			$js.=$vs;
		}
//			$js = join("&", map2("%s=%s", $HG));
		if ($js != "")
		{
			$js = "?".$js;
		}

		if (strpos($_link, "orb.".$this->cfg["ext"]) !== false)
		{
			$link = $this->cfg["baseurl"]."/orb.".$this->cfg["ext"].$js;
		}
		else
		if (strpos($_link, "poll.".$this->cfg["ext"]) !== false)
		{
			$link = $this->cfg["baseurl"]."/poll.".$this->cfg["ext"].$js;
		}
		else
		{
			$link = $this->cfg["baseurl"]."/index.".$this->cfg["ext"].$js;
		}
//		echo "exit do_aw_parse_url($link) = $link <br>\n\n";
//		flush();
		return $link;
	}

	function link_is_file($link)
	{
		$link = str_replace($this->cfg["baseurl"], "", $link);
		$basedir = $this->cfg["site_basedir"]."/public";
		// now separate it by /'s and find the first one that matches a file
		$pathbits = array();
		
		$pt = strtok($link, "?&/");
		while ($pt !== false)
		{
			if ($pt != "")
			{
				if (!$found)
				{
					if (strpos($pt, "/") !== false)
					{
						$cname .= $pt;
					}
					else
					{
						$cname .= "/".$pt;
					}
					$trypath = $basedir.$cname;
					if (@is_file($trypath))
					{
						if (strpos($trypath, "orb.".$this->cfg["ext"]) === false &&
								strpos($trypath, "index.".$this->cfg["ext"]) === false && 
								strpos($trypath, "poll.".$this->cfg["ext"]) === false)
						{
							$found = true;
						}
					}
				}
			}
			$pt = strtok("?&/");
		}
		return $found; 
	}

	function stop_rule($arr)
	{
		extract($arr);
		$fp = fopen(aw_ini_get("server.tmpdir")."/aw.export.stop","w");
		fwrite($fp, $id);
		fclose($fp);
		die("Kirjutasin expordi stop flagi faili ".aw_ini_get("server.tmpdir")."/aw.export.stop<br><a href='".$this->mk_my_orb("change", array("id" => $id))."'>Tagasi</a>");
	}

	function pick_active($arr)
	{
		classload("html");
		extract($arr);
		$this->read_template("pick_active.tpl");

		$liname = $this->get_cval("export::public_symlink_name");
		$li = @readlink($liname);

		$this->db_query("SELECT * FROM export_folders");
		while ($row = $this->db_next())
		{
			preg_match("/version-(\d*)/", $row["folder"],$mt);
			$this->vars(array(
				"folder" => $row["folder"],
				"folder_n" => $row["folder"],
				"time" => $this->time2date($row["created"], 2),
				"checked" => checked($li == $row["folder"]),
				"delete" => html::href(array(
					'url' => $this->mk_my_orb("delete_version", array("id" => $mt[1])),
					'caption' => "Kustuta"
				))
			));
			$r.= $this->parse("ROW");
		}
		$this->vars(array(
			"folder" => "Loo uus aktiivse versiooni p&otilde;hjal",
			"folder_n" => "new",
			"time" => "",
			"checked" => "",
			"delete" => ""
		));
		$r.= $this->parse("ROW");

		$this->vars(array(
			"ROW" => $r,
			"admin_url" => $this->mk_my_orb("export", array()),
			"gen_url" => $this->mk_my_orb("do_export", array()),
			"view_log" => $this->mk_my_orb("view_log", array()),
			"reforb" => $this->mk_reforb("submit_pick_active", array())
		));
		return $this->parse();
	}

	function submit_pick_active($arr)
	{
		extract($arr);
		// get the active link. 
		$liname = $this->get_cval("export::public_symlink_name");
		$li = @readlink($liname);

		if ($active_version == "new")
		{
			// increment counter, create new folder and copy all old files to it
			$c = get_instance("config");
			$c->set_simple_config("export::folder_counter", $this->get_cval("export::folder_counter")+1);
			$this->init_settings();

			set_time_limit(0);
			$this->copy_contents($li, $this->folder);
			die("<a href='".$this->mk_my_orb("pick_active_version", array())."'>Tagasi</a>");
		}
		else
		{
			// if it is different, recreate the link
			if ($li != $active_version && $active_version != "")
			{
				@unlink($liname);
				@symlink($active_version, $liname);
			}
		}

		return $this->mk_my_orb("pick_active_version", array());
	}

	function copy_contents($from, $to)
	{
		echo "copying files... \n <Br>";
		flush();
		if ($dir = @opendir($from)) 
		{
			while (($file = readdir($dir)) !== false) 
			{
				if (!($file == "." || $file == ".."))
				{
					$fn = $from."/".$file;
					$tn = $to."/".$file;
					if (is_dir($fn))
					{
						// copy subdirs as well
						mkdir($tn,0777);
						echo "copying subfolder $fn to $tn <br>\n";
						$this->copy_contents($fn, $tn);
					}
					else
					{
						copy($fn, $tn);
						chmod($tn,0666);
						echo "$fn => $tn <br>\n";
						flush();
					}
				}
			}  
			closedir($dir);
		}
		echo "finished! <br>\n";
	}

	function view_log($arr)
	{
		extract($arr);
		$this->read_template("view_log.tpl");

		$this->db_query("
			SELECT 
				export_log.start AS start,
				export_log.finish AS finish,
				export_log.id AS id,
				objects.name AS rule_name,
				export_log.rule_id AS rule_id
			FROM export_log 
				LEFT JOIN objects ON objects.oid = export_log.rule_id 
			ORDER BY start DESC");
		while ($row = $this->db_next())
		{
			$this->vars(array(
				"start" => $this->time2date($row["start"], 2),
				"finish" => ($row["finish"] ? $this->time2date($row["finish"], 2) : ""),
				"id" => $row["id"],
				"rule_url" => $this->mk_my_orb("change", array("id" => $row["rule_id"])),
				"rule_name" => $row["rule_name"],
				"view" => $this->mk_my_orb("view_log_entry", array("id" => $row["id"])),
				"delete" => $this->mk_my_orb("del_log_entry", array("id" => $row["id"]))
			));
			$r .= $this->parse("ROW");
		}

		$this->vars(array(
			"pick_act" => $this->mk_my_orb("pick_active_version", array()),
			"admin_url" => $this->mk_my_orb("export", array()),
			"gen_url" => $this->mk_my_orb("do_export", array()),
			"pick_act" => $this->mk_my_orb("pick_active_version", array()),
			"ROW" => $r
		));
		return $this->parse();
	}

	function view_log_entry($arr)
	{
		classload("html");
		extract($arr);
		$this->read_template("view_log_entry.tpl");
		$this->mk_path(0,html::href(array(
				'url' => $this->mk_my_orb("view_log", array()),
				'caption' => "Vaata tervet logi"
			))." / Vaata logi kirjet"
		);

		$row = $this->db_fetch_row("
			SELECT 
				export_log.start AS start,
				export_log.finish AS finish,
				export_log.id AS id,
				objects.name AS rule_name,
				export_log.rule_id AS rule_id,
				export_log.log AS content,
				export_log.added_files AS added_files,
				export_log.changed_files AS changed_files,
				export_log.removed_files AS removed_files
			FROM export_log 
				LEFT JOIN objects ON objects.oid = export_log.rule_id
			WHERE id = '$id'
			");

		if ($type == "errors")
		{
			$lstr = "<b>VEAD:</b><br><br>";
			$log = new aw_array(aw_unserialize($row["content"]));
			foreach($log->get() as $entry)
			{
				$lstr.="VIGA (".$this->time2date($entry["tm"],2).")<br>\n";
				$lstr.="URL: $entry[url] <br>\n";
				$lstr.="TEADE: $entry[msg] <br>\n-----------------------------<br>\n";
			}
			$lstr .=" <Br><br>";
		}
		if ($type == "added")
		{
			$lstr .= "<b>LISATUD FAILID:</b><br><br>";
			$lstr .= "<table border=1><tr><td class='celltext'>URL</td><td class='celltext'>FAIL</td><td class='celltext'>GLOBAL</td></tr>";
			$log = new aw_array(aw_unserialize($row["added_files"]));
			foreach($log->get() as $entry)
			{
				$entry['global'] = "&nbsp;".round($entry['global'],2)."&nbsp;";
				$lstr.="<tr><td class='celltext'>$entry[url]&nbsp;</td><td class='celltext'>$entry[name]</td><td class='celltext'>$entry[global]</td></tr>\n";
			}
			$lstr .=" </table><Br><br>";
		}
		if ($type == "removed")
		{
			$lstr .= "<b>KUSTUTATUD FAILID:</b><br><br>";
			$log = new aw_array(aw_unserialize($row["removed_files"]));
			foreach($log->get() as $entry)
			{
				$lstr .= "FAIL: $entry<br>\n";
			}
			$lstr .=" <Br><br>";
		}
		if ($type == "changed")
		{
			$lstr .= "<b>MUUDETUD FAILID:</b><br><br>";
			$lstr .= "<table border=1><tr><td class='celltext'>URL</td><td class='celltext'>FAIL</td><td class='celltext'>GLOBAL</td></tr>";
			$log = new aw_array(aw_unserialize($row["changed_files"]));
			foreach($log->get() as $entry)
			{
				$entry['global'] = "&nbsp;".round($entry['global'],2)."&nbsp;";
				$lstr .= "<tr><td class='celltext'>$entry[url]&nbsp;</td><td class='celltext'>$entry[name]</td><td class='celltext'>$entry[global]</td></tr>\n";
			}
			$lstr .= "</table>";
		}

		$this->vars(array(
			"start" => $this->time2date($row["start"], 2),
			"finish" => $this->time2date($row["finish"], 2),
			"id" => $row["id"],
			"rule_url" => $this->mk_my_orb("change", array("id" => $row["rule_id"])),
			"rule_name" => $row["rule_name"],
			"delete" => $this->mk_my_orb("del_log_entry", array("id" => $row["id"])),
			"content" => $lstr,
			"errors" => $this->mk_my_orb("view_log_entry", array("id" => $row["id"], "type" => "errors")),
			"added" => $this->mk_my_orb("view_log_entry", array("id" => $row["id"], "type" => "added")),
			"removed" => $this->mk_my_orb("view_log_entry", array("id" => $row["id"], "type" => "removed")),
			"changed" => $this->mk_my_orb("view_log_entry", array("id" => $row["id"], "type" => "changed")),
			"globals" => $this->mk_my_orb("view_log_entry", array("id" => $row["id"], "type" => "globals")),
		));
		return $this->parse();
	}

	function del_log_entry($arr)
	{
		extract($arr);
		$this->db_query("DELETE FROM export_log WHERE id = '$id'");
		return $this->mk_my_orb("view_log");
	}

	function delete_version($arr)
	{
		extract($arr);
		set_time_limit(0);
		$folder = $this->rep_dates($this->get_cval("export::folder"));
		// add the counter to the folder as folder-cnt
		$folder .= '/version-'.$id;
		// right. delete all files in folder and then the folder iteself
		$this->del_folder($folder);

		$this->db_query("DELETE FROM export_folders WHERE folder = '$folder'");
		classload("html");
		die(html::href(array(
			'url' => $this->mk_my_orb("pick_active_version"),
			'caption' => "Tagasi"
		)));
	}

	function del_folder($folder)
	{
		if (!is_dir($folder))
		{
			return;
		}
		echo "delete folder $folder .......<br>\n";
		flush();
		if ($dir = @opendir($folder)) 
		{
			while (($file = readdir($dir)) !== false) 
			{
				if (!($file == "." || $file == ".."))
				{
					$fn = $folder."/".$file;
					if (is_dir($fn))
					{
						$this->del_folder($fn);
					}
					else
					{
						echo "delete file $fn<br>\n";
						flush();
						unlink($fn);
					}
				}
			}  
			echo "delf $folder <br>";
			flush();
			closedir($dir);
			rmdir($folder);
		}
	}

	function write_log()
	{
		echo "creating log entry ...<br>\n";
		flush();
		$lg = aw_serialize($this->err_log,SERIALIZE_XML);
		$this->quote(&$lg);
		$lg_a = aw_serialize($this->added_files,SERIALIZE_XML);
		$this->quote(&$lg_a);
		$lg_r = aw_serialize($this->removed_files,SERIALIZE_XML);
		$this->quote(&$lg_r);
		$lg_c = aw_serialize($this->changed_files,SERIALIZE_XML);
		$this->quote(&$lg_c);

		$lg_g = aw_serialize($this->log_globals,SERIALIZE_XML);
		$this->quote(&$lg_g);
		if ($this->log_entry_id)
		{
			$this->db_query("UPDATE export_log SET finish = '".time()."', log = '$lg',added_files = '$lg_a',removed_files = '$lg_r',changed_files = '$lg_c',globals = '$lg_g' WHERE id = '$this->log_entry_id'"); 
		}
		else
		{
			$this->db_query("INSERT INTO export_log(start, finish, rule_id, log,added_files,removed_files,changed_files,globals) 
				VALUES('$this->start_time','".time()."','$this->rule_id','$lg','$lg_a','$lg_r','$lg_c','$lg_g')");
		}
	}
	
	function kix_test()
	{
		$this->init_settings();
		$this->fetch_and_save_page("http://editwww.ut.ee/index.aw?set_lang_id=2", 2, true);
	}
}
?>
