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
			"text/css" => "css",
			"image/gif" => "gif",
			"image/jpeg" => "jpg",
			"image/pjpeg" => "jpg",
			"application/pdf" => "pdf",
			"application/x-javascript" => "js",
			"application/zip" => "zip"
		);
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
			"gen_url" => $this->mk_my_orb("do_export"),
			"rules" => $this->mk_my_orb("rules")
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

		$sched = get_instance("scheduler");
		$sched->remove(array(
			"event" => $this->mk_my_orb("do_export"),
			"rep_id" => $this->get_cval("export::event_id")
		));
		if ($automatic)
		{
			$sched->add(array(
				"event" => $this->mk_my_orb("do_export"),
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

	function do_export($arr)
	{
		extract($arr);

		$folder = $this->rep_dates($this->get_cval("export::folder"));
		$zip_file = $this->rep_dates($this->get_cval("export::zip_file"));
		$aw_zip_folder = $this->get_cval("export::aw_zip_folder");
		$aw_zip_fname = $this->rep_dates($this->get_cval("export::aw_zip_fname"));
		$automatic = $this->get_cval("export::automatic");
		$this->fn_type = $this->get_cval("export::fn_type");

		// take the folder thing and add the date to it so we can make several copies in the same folder
		@mkdir($folder,0777);
		if (!is_dir($folder))
		{
			$this->raise_error(ERR_SITEXPORT_NOFOLDER,"Folder $folder does not exist on server!",true);
		}
 		$this->folder = $folder;

		// ok, this is the complicated bit. 
		// so, how do we do this? first. forget the time limit, this is gonna take a while.
		set_time_limit(0);

		echo "exporting site to folder $this->folder ... <br><br>\n\n";
		flush();
		$this->hashes = array();

		// import exclusion list
		if (is_array($this->cfg["exclude_urls"]))
		{
			$this->exclude_urls = $this->cfg["exclude_urls"];
		}

		// ok, start from the front page
		$this->fetch_and_save_page($this->cfg["baseurl"]."/?set_lang_id=1",1);

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

		echo "<br>all done. <br><br>\n\n";
		die();
	}

	function fetch_and_save_page($url, $lang_id)
	{
		if ($url == "")
		{
			echo "<p><Br>VIGA, tyhi url! </b><Br>";
		}

		$url = $this->add_session_stuff($url, $lang_id);

		// if we have done this page already, let's not do it again!
		if (isset($this->hashes[$url]) || $this->check_excludes($url))
		{
			return $this->hashes[$url].".".$this->get_ext_for_link($url,$http_response_header);
		}

		// here we track the active language in the url
		$t_lang_id = $lang_id;
		if (preg_match("/set_lang_id=(\d*)/", $url,$mt))
		{
			$t_lang_id=$mt[1];
		}

		// set the hash table
		$this->hashes[$url] = $this->get_hash_for_url($url,$t_lang_id);

		// read content
		$fp = fopen($url,"r");
		$fc = fread($fp,10000000);
		fclose($fp);

		$f_name = $this->hashes[$url].".".$this->get_ext_for_link($url,$http_response_header);
		$name = $this->folder."/".$f_name;
		echo "saving $url as $name <br>\n";
		flush();

		// now. convert all the links in the page
		$this->convert_links($fc,$t_lang_id);

		$this->save_file($fc,$name);
		return $f_name;
	}

	function convert_links(&$fc,$lang_id)
	{
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

		while (($pos = strpos($fc,$baseurl)) !== false)
		{
			// now find all of the link - we do that by looking for ' " > or space
			$begin = $pos;
			$end = $pos;
			$link = "";
			while (!in_array($fc[$end],$ends) && $end < $len)
			{
				$end++;
			}

			// correct the link
			$link = $this->rewrite_link(substr($fc,$begin,($end-$begin)));

			// fetch the page
			$fname = $this->fetch_and_save_page($link,$lang_id);

			// replace the link in the html
			$fc = substr($fc,0,$begin).$fname.substr($fc,$end);
		}
	}

	function save_file($fc,$name)
	{
//		echo "saving file as $name <br>\n";
		$fp = fopen($name,"w");
		fwrite($fp,$fc);
		fclose($fp);
	}

	function get_ext_for_link($link, $headers)
	{
		if (isset($this->link2type[$link]))
		{
			return $this->link2type[$link];
		}

		if (count($headers) < 1)
		{
			// we gotta get the page, cause we haven't yet
			$fp = fopen($link,"r");
			fread($fp, 1000000);
			fclose($fp);
			$headers = $http_response_header;
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
		}

		$this->link2type[$link] = $this->type2ext[$ct];
		return $this->type2ext[$ct];
	}

	////
	// !checks the link and rewrites it, so all section links are the same and some other weirdness to make
	// things work correctly
	function rewrite_link($link)
	{
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		$frontpage = $this->cfg["frontpage"];

		// do link processing as aw would upon request startup
		$ud = parse_url($link);
		if (!preg_match("/(shop.aw|banner.aw|graphs.aw|css|poll|files|ipexplorer|icon.aw|gallery.aw|login|stats|vcl|misc|index|images|feedback|forms|indexx|showimg|sorry|monitor|vv|automatweb|img|reforb|orb)/",$link))
		{
			$url = $baseurl."/index.".$ext.$ud["path"].$ud["query"].$ud["fragment"];
			echo "changed $link to $url <br>";
		}

		if ( isset($PATH_INFO) && (strlen($PATH_INFO) > 1))
		{
			$pi = $PATH_INFO;
		};
		if ( isset($QUERY_STRING) && (strlen($QUERY_STRING) > 1))
		{
			$pi .= "?".$QUERY_STRING;
		};

		if ($pi) 
		{
			// uh, why do you use sprintf to do string->int conversion? I mean, it's GOTTA be wayyy slower than $i = (int)$str;

			// I think type-cast had problems in some situations, but right now I can't really reproduce any of those
			$section = (int)substr($pi,1);

			// if $pi contains & or = 
			if (preg_match("/[&|=]/",$pi)) 
			{
				// expand and import PATH_INFO
				// replace ? and / with & in $pi and output the result to HTTP_GET_VARS
				// why so?
				parse_str(str_replace("?","&",str_replace("/","&",$pi)),$HTTP_GET_VARS);
				extract($HTTP_GET_VARS);
			} 
			else 
			{
				$section = substr($pi,1);
			};
		};


		return $link;

		// here we check the link for weirdness:
		// if it is == $baseurl, we need to rewrite it to $baseurl/index.aw?section=$frontpage
		if ($link == $baseurl)
		{
			$link = $baseurl."/index.".$ext."?section=".$frontpage;
		}

		// and we should rewrite links $baseurl/section and $baseurl/index.aw/section=section
		// to $baseurl/index.aw?section=section as well. but right now lets make this thing work first
		if (strpos($link,"index.".$ext."/section=") !== false)
		{
			$link = str_replace("index.".$ext."/section=","index.".$ext."?section=",$link);
		}

		$ud = parse_url($link);
		if (is_number(substr($ud["path"],1)))	// substr, because path is preceded by /
		{
			// we found a section link $baseurl/section
			$link = $baseurl."/index.".$ext."?section=".substr($ud["path"],1);
		}

		// rewrite indexx.aw links to their real address
		if ($ud["path"] == "/indexx.".$ext || strpos($ud["query"], "class=links&action=show") !== false)
		{
//			echo "extlink detected in $link , rewriting to real adress. query = $ud[query] <br>\n";
			$el = new extlinks;
			preg_match("/id=(\d*)/", $ud["query"], $mt);
			$ld = $el->get_link($mt[1]);
			$link = $ld["url"];
			if (strpos($link,$baseurl) === false && $link[0] == "/")
			{
				$link = $baseurl.$link;
			}
//			echo "rewrote extlink to $link  <Br>";
			// and also recurse once to fix the link pointed by the extlink
			$link = $this->rewrite_link($link);
//			echo "final extlink is $link  <Br>";
		}

		// uukey, gallery popups need to be rewritten as well.
		if (strpos($link,"/gallery.".$ext."/") !== false)
		{
//			echo "gallery link detected - $link <br>\n";
			$link = str_replace("/gallery.".$ext."/","gallery.".$ext."?",$ud["path"]);
			$link = $baseurl."/".str_replace("/","&",$link);
//			echo "rewrote gallery link to $link <br>\n";
		}
		return $link;
	}

	function load_rules()
	{
		$rd = $this->get_cval("export::rules");
//		echo "rd = <pre>", htmlentities($rd),"</pre> <br>";
		$this->rules = aw_unserialize($rd);
		if (!is_array($this->rules))
		{
			$this->rules = array();
		}
	}

	function rules($arr)
	{
		extract($arr);
		$this->read_template("rules.tpl");

		$this->load_rules();
//		echo "rules = <pre>",var_dump($this->rules),"</pre> <br>";
		foreach($this->rules as $rid => $rdat)
		{
			$this->vars(array(
				"name" => $rdat["name"],
				"change" => $this->mk_my_orb("change_rule", array("id" => $rid)),
				"delete" => $this->mk_my_orb("del_rule", array("id" => $rid))
			));
			$l.=$this->parse("LINE");
		}
		$this->vars(array(
			"LINE" => $l,
			"add" => $this->mk_my_orb("add_rule"),
			"settings" => $this->mk_my_orb("export"),
			"gen_url" => $this->mk_my_orb("do_export")
		));
		return $this->parse();
	}

	function add_rule($arr)
	{
		extract($arr);
		$this->read_template("add_rule.tpl");
		$this->mk_path(0,"<a href='".$this->mk_my_orb("rules")."'>Ruulid</a> / Lisa");

		$o = get_instance("objects");
		$this->vars(array(
			"menus" => $this->multiple_option_list(array(),$o->get_list()),
			"reforb" => $this->mk_reforb("submit_rule")
		));
		return $this->parse();
	}

	function submit_rule($arr)
	{
		extract($arr);
		$this->load_rules();

		if ($id)
		{
			$this->rules[$id]["name"] = $name;
			$this->rules[$id]["menus"] = $this->make_keys($menus);
		}
		else
		{
			$id = $this->gen_uniq_id();
			$arr = array("name" => $name, "menus" => $this->make_keys($menus));

			$pl = new planner;
			$pl->submit_add(array("parent" => 1));
			$arr["cal_id"] = $pl->id;
			$arr["event_id"] = $pl->bron_add_event(array("parent" => $cal_id,"start" => time(), "end" => time()+1));			

			$this->rules[$id] = $arr;
		}

		$this->save_rules();
		return $this->mk_my_orb("change_rule", array("id" => $id));
	}

	function change_rule($arr)
	{
		extract($arr);
		$this->load_rules();
		$this->mk_path(0,"<a href='".$this->mk_my_orb("rules")."'>Ruulid</a> / Muuda");
		$this->read_template("add_rule.tpl");
		$o = get_instance("objects");
		$this->vars(array(
			"name" => $this->rules[$id]["name"],
			"menus" => $this->multiple_option_list($this->rules[$id]["menus"],$o->get_list()),
			"reforb" => $this->mk_reforb("submit_rule", array("id" => $id)),
			"sel_period" => $this->mk_my_orb("repeaters", array("id" => $this->rules[$id]["event_id"]),"cal_event",false,true),
		));
		$this->vars(array(
			"CHANGE" => $this->parse("CHANGE")
		));
		return $this->parse();
	}

	function save_rules()
	{
		$ser = aw_serialize($this->rules, SERIALIZE_PHP);
		$this->quote(&$ser);
		$c = get_instance("config");
		$c->set_simple_config("export::rules", $ser);
	}

	function del_rule($arr)
	{
		extract($arr);
		$this->load_rules();
		$this->delete_object($this->rules[$id]["cal_id"]);
		$this->delete_object($this->rules[$id]["event_id"]);
		unset($this->rules[$id]);
		$this->save_rules();
		header("Location: ".$this->mk_my_orb("rules"));
	}

	function get_hash_for_url($url, $lang_id)
	{
		if ($url == $this->cfg["baseurl"]."/?set_lang_id=1&automatweb=aw_export")
		{
			return "index";
		}

		if ($this->fn_type == FN_TYPE_SECID)
		{
			// figure out the section id from the url
			preg_match("/section=(\d*)/",$url,$mt);
			$secid = $mt[1];
			if ($secid)
			{
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
					return $res;
				}
			}
		}
		else
		if ($this->fn_type == FN_TYPE_ALIAS)
		{
			preg_match("/section=(\d*)/",$url,$mt);
			$secid = $mt[1];
			if ($secid)
			{
				$md = $this->menu_cache->get_cached_menu($secid);
				$mn = $md["alias"];
				if ($mn != "")
				{
					$cnt = 1;
					$_res = str_replace(" ", "_", str_replace("/","_",$mn));
					$res = $_res;
					while (isset($this->fta_used[$res]))
					{
						$res = $_res.",".($cnt++);
					}
					$this->fta_used[$res] = true;
					return $res;
				}
			}
		}

		return $this->gen_uniq_id($url).",".$lang_id;
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

		if (strpos($url, "automatweb=aw_export") === false)
		{
			$url = $url.$sep."automatweb=aw_export";
		}

		if (strpos($url, "set_lang_id=") === false)
		{
			$url = $url."&set_lang_id=".$lang_id;
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
}
?>