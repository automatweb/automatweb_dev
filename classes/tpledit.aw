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

	////
	// !Displays the directory browser
	function browse($args = array())
	{
		extract($args);

		// gather information about all template metaobjects
		$q = "SELECT * FROM objects WHERE class_id = " . CL_TEMPLATE;
		$this->db_query($q);
		$tdata = array();
		while($row = $this->db_next())
		{
			$tdata[$row["name"]] = $row;
		}

		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$tplroot = $tpldir;
		if (!$parent)
		{
			$parent = "root";
		};
		if ($parent == "root")
		{
			$parent = "/";
		};
		$tpldir .= "$parent";
		$files = array();
		$dirs = array();
		$sep = (strlen($parent) > 1) ? "/" : "";
		if ($dir = @opendir($tpldir)) {
			while ($file = readdir($dir)) {
				$fullname = $tpldir . $sep . $file;
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

		// Joonistame kataloogid
		foreach($dirs as $name)
		{
			$fullname = $tpldir .$name;
			$this->vars(array(
				"name" => $name,
				"date" => $this->time2date($stat[$fullname][FILE_MODIFIED],6),
				"size" => $stat[$fullname][FILE_SIZE],
				"dirlink" => $this->mk_orb("browse",array("parent" => $parent . $sep . $name)),
			));

			$d .= $this->parse("directory");
		};

		// Joonistame failid
		foreach($files as $name)
		{
			$fullname = $tpldir . $sep . $name;
			$relname = substr($fullname,strlen($tplroot) + 1);
			$oid = $tdata[$relname]["oid"];
			if ($oid)
			{
				$arclink = sprintf("<a href='%s'>%s</a>",$this->mk_orb("archive",array("oid" => $oid)),"Arhiiv");
			}
			else
			{
				$arclink = "";
			};
			$this->vars(array(
				"name" => $name,
				"date" => $this->time2date($stat[$fullname][FILE_MODIFIED],6),
				"size" => $stat[$fullname][FILE_SIZE],
				"modifiedby" => $tdata[$relname]["modifiedby"],
				"oid" => $tdata[$relname]["oid"],
				"edlink" => $this->mk_orb("edit",array("file" => $relname)),
				"dnlink" => $this->mk_orb("download",array("file" => $relname)),
				"arclink" => $arclink,
			));

			$f .= $this->parse("file");
		};
	
		$path = array();
		$path["root"] = $HTTP_HOST;

		$parents = explode("/",$parent);

		$fullname = "";
		
	
		foreach($parents as $name)
		{
			if (strlen($name) == 0)
			{
				continue;
			};
			$fullname .= "/$name";
			$index = (strlen($fullname) > 0) ? $fullname : $name;
			$path[$index] = $name;
		};

		$path = join(" / ",map2("<a href='orb.aw?class=tpledit&action=browse&parent=%s'>%s</a>",$path));
		$this->vars(array(
			"directory" => $d,
			"file" => $f,
			"path" => $path,
		));

		$test = $this->mk_orb("shak",array("kala" => "tursk2"));
		$GLOBALS["site_title"] = "<a href='" . $this->mk_orb("browse",array("parent" => "root")) . "'>TemplateEditor</a>";
		return $this->parse();
	}

	// Kuvab faili edimise vormi
	function edit($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$meta_obj = $this->_fetch_tpl_obj(array("name" => $file));
		$dirname = dirname($file);
		$source = join("",@file($tpldir . "/" . $file));
		$this->read_template("edit.tpl");
		$this->vars(array(
			"file" => $file,
			"source" => htmlspecialchars($source),
			"rawlink" => $this->mk_orb("source",array("file" => $file)),
			"reforb" => $this->mk_reforb("submit",array("file" => $file)),
		));
		$GLOBALS["site_title"] = "<a href='" . $this->mk_orb("browse",array("parent" => "/$dirname")) . "'>TemplateEditor</a>";
		return $this->parse();
	}

	function submit($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$fullpath = $tpldir . "/" . $args["file"];
		$meta_obj = $this->_fetch_tpl_obj(array("name" => $file));
			
		classload("archive");
		$arc = new archive();

		if (not($meta_obj))
		{
			// Objekti polnud, teeme uue
			$oid = $this->new_object(array(
					"class_id" => CL_TEMPLATE,
					"name" => $file,
			));
			// create archive
			$arc->add(array("oid" => $oid));
		}
		else
		{
			// Uuendame olemasoleva objekti metainfot
			$this->upd_object(array(
					"oid" => $meta_obj["oid"],
					"name" => $file,
			));

			$ser = $this->_archive(array("oid" => $meta_obj["oid"]));
			// add a new copy of the template object to the archive
			$arc->commit(array(
				"oid" => $meta_obj["oid"],
				"content" => trim($ser),
			));

			
		}
		$this->put_file(array(
			"file" => $fullpath,
			"content" => stripslashes($source),
		));
		return $this->mk_orb("edit",array("file" => $file));
	}

	// Fetchib template metaobjekti
	// name(string) - template nimi
	function _fetch_tpl_obj($args = array())
	{
		extract($args);
		$q = sprintf("SELECT * FROM objects WHERE class_id = %d AND name = '%s'",
					CL_TEMPLATE,$name);
		$this->db_query($q);
		$row = $this->db_next();
		return $row;
	}


	function download($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$source = join("",@file($tpldir . $file));
		$realname = basename($file);
		header("Content-Type: text/plain");
		header("Content-Disposition: filename=$realname");
		print $source;
		exit;
	}
	
	function source($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$source = join("",@file($tpldir . "/" . $file));
		$realname = basename($file);
		header("Content-Type: text/html");
		header("Content-Disposition: filename=$realname");
		print $source;
		exit;
	}

	////
	// !Fetches contents of a template file
	function _fetch_template($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$source = join("",@file($tpldir . "/" . $file));
		return $source;
	}
		

	// Arhiivi kasutamise prototüüp, antakse ette objekti ID, mille vastu
	// huvi tuntakse, ning see klass tagastab ta serialiseeritud kujul
	// argumendid. Arhiveerida saab ainult sellist objekti, millel objekti-
	// tabelis kirje olemas on.
	// oid(int) - objekti id
	function _archive($args = array())
	{
		extract($args);
		$obj = $this->get_object($args["oid"]);
		$ser = $this->_fetch_template(array("file" => $obj["name"]));
		return $ser;
	}

	////
	// Näitab templatearhiivi sisu
	function show_archive($args = array())
	{
		classload("archive");
		$arc = new archive();
		$contents = $arc->get($args);
		// FIXME: check the object class too
		$obj = $this->get_object($args["oid"]);
		$this->read_template("archive.tpl");
		$GLOBALS["site_title"] = "<a href='" . $this->mk_orb("browse",array("parent" => "root")) . "'>TemplateEditor</a>";
		$c = "";
		if (is_array($contents))
		{
			foreach($contents as $element)
			{
				$this->vars(array(
					"date" => $this->time2date($element[FILE_MODIFIED],9),
					"action" => "Näita",
					"size" => $element[FILE_SIZE],
					"id" => $element[FILE_MODIFIED],
				));

				$c .= $this->parse("line");
			};
		};
		$this->vars(array(
			"line" => $c,
			"reforb" => $this->mk_reforb("submit_archive",array("oid" => $args["oid"])),
		));
		return $this->parse();
		
	}

	////
	// !Submitib arhiivi
	function submit_archive($args = array())
	{
		// active (int) - faili nimi arhiivist, mida aktiveerima peab
		// oid (int) - millise objekti arhiivi lugeda]
		classload("archive");
		$arc = new archive();
		$new_template = $arc->checkout(array("oid" => $args["oid"],"version" => $args["active"]));

		$obj = $this->get_object($args["oid"]);
		// We got the template the user wants to active, 
		// now we have to copy the _current_ template to archive.
			
		$ser = $this->_archive(array("oid" => $args["oid"]));
		$ser = trim($ser);
		$arc->commit(array(
			"oid" => $args["oid"],
			"content" => $ser,
		));
		
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$fullpath = $tpldir . "/" . $obj["name"];

		// lets put the old object instead of the current one
		$this->put_file(array(
			"file" => $fullpath,
			"content" => $new_template,
		));

		// touch the object
		$this->upd_object(array(
			"oid" => $args["oid"],
		));

		return $this->mk_orb("archive",array("oid" => $args["oid"]));
	}
};
?>
