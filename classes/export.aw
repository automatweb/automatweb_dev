<?php

classload("extlinks");

class export extends aw_template
{
	function export()
	{
		$this->tpl_init("export");
		$this->db_init();
	}

	function orb_export($arr)
	{
		extract($arr);
		$this->read_template("export.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_export"),
			"folder" => $this->get_cval("export::folder")
		));
		return $this->parse();
	}

	function submit_export($arr)
	{
		extract($arr);

		classload("config");
		$c = new config;
		$c->set_simple_config("export::folder",$folder);

		// take the folder thing and add the date to it so we can make several copies in the same folder
		if (!is_dir($folder))
		{
			$this->raise_error(ERR_SITEXPORT_NOFOLDER,"Folder $folder does not exist on server!",true);
		}
		global $stitle;
		$fname = $stitle."-".date("Y")."-".date("m")."-".date("d")."-".time();
		mkdir($folder."/".$fname,0777);
 		$this->folder = $folder."/".$fname;

		// ok, this is the complicated bit. 
		// so, how do we do this? first. forget the time limit, this is gonna take a while.
		set_time_limit(0);

		echo "exporting site to folder $this->folder ... <br><br>\n\n";
		flush();
		$this->hashes = array();

		// so. here we go. make a list of menus and get the page for each menu, convert links and save it as a file. 
		// shouldn't be hard.
		// right. funny. 
		global $baseurl,$ext;
/*		$this->db_query("SELECT oid FROM objects WHERE class_id = ".CL_PSEUDO." AND status != 0 AND site_id = ".$GLOBALS["SITE_ID"]);
		while ($row = $this->db_next())
		{
//			echo "exporting section $row[oid] as file ".$folder."/".$hashes[$row["oid"]]." <br>\n";
			flush();
			$url = $baseurl."/index.".$ext."?section=".$row["oid"];
			$this->fetch_and_save_page($url);
		}*/

		// ok, try this for a change. 
		$this->fetch_and_save_page($baseurl."/");
		
		if ($zip_file != "")
		{
			// $zip_file contains the path and name of the file into which we should zip the exported site
		}
		echo "<br>all done. <br><br>\n\n";
		die();
	}

	function fetch_and_save_page($url)
	{
		if (isset($this->hashes[$url]))
		{
			// if the hash for the link is set, we have already saved it
//			echo "hash for url $url found, returning <br>\n";
			return;
		}

		global $baseurl,$ext,$frontpage;

		// make sure that the frontpage is named index.html
		if ($url == $baseurl."/")
		{
			$this->hashes[$url] = "index";
		}
		else
		{
			$this->hashes[$url] = $this->gen_uniq_id();
		}

		$name = $this->folder."/".$this->hashes[$url].".".$this->get_ext_for_link($url);

		echo "saving $url as $name <br>\n";
		flush();
//		echo "fetching $url <br>\n\n";
		flush();

		// we do this, so we don't get several copies of the same page with differend session ids
		if (strpos($url,"?") === false)
		{
			$url.="?automatweb=aw_export";
		}
		else
		{
			$url.="&automatweb=aw_export";
		}
		$fp = fopen($url,"r");
		$fc = fread($fp,10000000);
		fclose($fp);
//		echo "fetched page $url <br>\n";

		// now. convert all the links in the page
		$this->convert_links($fc);

		$this->save_file($fc,$name);
	}

	function convert_links(&$fc)
	{
		// uukay. so the links we gotta convert are identified by having $baseurl in them. so look for that
		global $baseurl,$ext,$frontpage;

		$ends = array("'","\"",">"," ","\n");
		$len = strlen($fc);

		// do a replace for malformed links for img.aw
		$fc = str_replace("\"/img","\"".$baseurl."/img",$fc);
		$fc = str_replace("'/img","'".$baseurl."/img",$fc);
		// fix some other common mistakes 
		$fc = str_replace("\"/index.".$ext,"\"".$baseurl."/index.".$ext,$fc);
		$fc = str_replace("'/index.".$ext,"'".$baseurl."/index.".$ext,$fc);

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

			$link = substr($fc,$begin,($end-$begin));

			$link = $this->rewrite_link($link);

			if (!isset($this->hashes[$link]))
			{
//				echo "found link $link , fetching it..<br>\n";
//				flush();
				$this->fetch_and_save_page($link);
			}

			// find the file extension for the link
			$_ext = $this->get_ext_for_link($link);

//			echo "rewrote link $link to ".$this->hashes[$link].".".$_ext." <br>\n";
			$fc = substr($fc,0,$begin).$this->hashes[$link].".".$_ext.substr($fc,$end);
		}
	}

	function save_file($fc,$name)
	{
//		echo "saving file as $name <br>\n";
		$fp = fopen($name,"w");
		fwrite($fp,$fc);
		fclose($fp);
	}

	function get_ext_for_link($link)
	{
		global $baseurl,$ext;
		$ud = parse_url($link);
		// if path == img.aw, then we need to get the extensiob from query, not path
		if ($ud["path"] == "/img.".$ext)
		{
			$pt = $ud["query"];
		}
		else
		{
			$pt = $ud["path"];
		}

		$_rp = strrpos($pt,".");
		if ($_rp === false)
		{
			return "html";
		}

		$_ext = substr($pt,$_rp+1);
		if ($_ext == $ext)
		{
			$_ext = "html";
		}
		return $_ext;
	}

	////
	// !checks the link and rewrites it, so all section links are the same and some other weirdness to make
	// things work correctly
	function rewrite_link($link)
	{
		global $baseurl,$ext,$frontpage;
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
		if ($ud["path"] == "/indexx.".$ext)
		{
//			echo "extlink detected in $link , rewriting to real adress. query = $ud[query] <br>\n";
			$el = new extlinks;
			$ld = $el->get_link(substr($ud["query"],strlen("id=")));
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
}
?>