<?php
// tpledit.aw - Template Editor
global $orb_defs;
$orb_defs["tpledit"] = "xml";
classload("defs");

define("FILE_SIZE",7);
define("FILE_MODIFIED",9);

class tpledit extends aw_template {
	function tpledit($args =array())
	{
		$this->tpl_init("tpledit");
		$this->db_init();

	}

	function browse($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$tplroot = $tpldir;
		if ($parent == "root")
		{
			$parent = "/";
		};
		#$parent = substr($parent,1);
		$tpldir .= "$parent";
		$files = array();
		$dirs = array();
		if ($dir = @opendir($tpldir)) {
			while ($file = readdir($dir)) {
				$fullname = $tpldir . $file;
				if (is_dir($fullname))
				{
					if (strpos($file,".") === false)
					{
						$dirs[] = $file;
					};
				}
				else
				{
					$files[] = $file;
				};
				$stat[$fullname] = stat($fullname);
			}  
			closedir($dir);
		};
		$this->read_template("browse.tpl");
		$d = "";
		$f = "";

		foreach($dirs as $name)
		{
			$fullname = $tpldir .$name;
			$this->vars(array(
				"name" => $name,
				"date" => $this->time2date($stat[$fullname][FILE_MODIFIED],6),
				"size" => $stat[$fullname][FILE_SIZE],
				"dirlink" => $this->mk_orb("browse",array("parent" => $parent . "/" . $name)),
			));

			$d .= $this->parse("directory");
		};

		foreach($files as $name)
		{
			$fullname = $tpldir . $name;
			$relname = substr($fullname,strlen($tplroot) + 1);
			$this->vars(array(
				"name" => $name,
				"date" => $this->time2date($stat[$fullname][FILE_MODIFIED],6),
				"size" => $stat[$fullname][FILE_SIZE],
				"edlink" => $this->mk_orb("edit",array("file" => $relname)),
				"dnlink" => $this->mk_orb("download",array("file" => $relname)),
			));

			$f .= $this->parse("file");
		};
	
		$path = array();
		$path["root"] = $HTTP_HOST;

		$parents = explode("/",$parent);

		$fullname = "";
		
		foreach($parents as $name)
		{
			$index = (strlen($fullname) > 0) ? $fullname : $name;
			$path[$index] = $name;
			$fullname .= "/$name";
		};

		$path = join(" / ",map2("<a href='orb.aw?class=tpledit&action=browse&parent=%s'>%s</a>",$path));

		$this->vars(array(
			"directory" => $d,
			"file" => $f,
			"path" => $path,
		));

		$GLOBALS["site_title"] = "<a href='" . $this->mk_orb("browse",array()) . "'>TemplateEditor</a>";
		return $this->parse();
	}

	function edit($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$source = join("",@file($tpldir . $file));
		$this->read_template("edit.tpl");
		$this->vars(array(
			"file" => $file,
			"source" => htmlspecialchars($source),
			"rawlink" => $this->mk_orb("source",array("file" => $file)),
			"reforb" => $this->mk_reforb("submit",array("file" => $file)),
		));
		$GLOBALS["site_title"] = "<a href='" . $this->mk_orb("browse",array()) . "'>TemplateEditor</a>";
		return $this->parse();
	}

	function submit($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$fullpath = $tpldir . $args["file"];
		$this->put_file(array(
			"file" => $fullpath,
			"content" => stripslashes($source),
		));
		return $this->mk_orb("edit",array("file" => $file));
	}

	function download($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$source = join("",@file($tpldir . $file));
		$realname = basename($file);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: filename=$realname");
		print $source;
		exit;
	}
	
	function source($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$source = join("",@file($tpldir . $file));
		$realname = basename($file);
		header("Content-Type: text/html");
		header("Content-Disposition: filename=$realname");
		print $source;
		exit;
	}
};
?>
