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
		load_vcl("table");
		$meta = $this->obj_get_meta(array("oid" => $args["oid"]));
		$t = new aw_table(array(
                                "prefix" => "tpledit_browse",
                                "imgurl"    => $GLOBALS["baseurl"]."/img",
                                "tbgcolor" => "#C3D0DC",
                        ));
		$t->parse_xml_def($GLOBALS["basedir"]."/xml/generic_table.xml");
		$t->set_header_attribs(array(
                                "class" => "tpledit",
                                "action" => "browse",
                                "parent" => $args["parent"],
                ));

		$t->define_field(array(
                                "name" => "oid",
                                "caption" => "ID",
                                "talign" => "center",
                                "nowrap" => 1,
				"sortable" => 1,
				"align" => "right",
                ));
		$t->define_field(array(
                                "name" => "name",
                                "caption" => "Nimi",
                                "talign" => "center",
                                "nowrap" => 1,
				"sortable" => 1,
				"align" => "right",
                ));
		$t->define_field(array(
                                "name" => "date",
                                "caption" => "Muudetud",
                                "talign" => "center",
                                "nowrap" => 1,
				"sortable" => 1,
				"align" => "right",
                ));
		$t->define_field(array(
                                "name" => "size",
                                "caption" => "Suurus",
                                "talign" => "center",
                                "nowrap" => 1,
				"sortable" => 1,
				"align" => "right",
                ));
		$t->define_field(array(
                                "name" => "uid",
                                "caption" => "Muutja",
                                "talign" => "center",
                                "nowrap" => 1,
				"sortable" => 1,
				"align" => "right",
                ));
		$t->define_field(array(
                                "name" => "arc",
                                "caption" => "Arhiveeritakse",
                                "talign" => "center",
                                "nowrap" => 1,
				"align" => "center",
                ));
		$t->define_action(array(
				"link" => "class=tpledit&action=edit",
				"field" => "file",
				"caption" => "Muuda",
		));
		$t->define_action(array(
				"link" => "class=tpledit&action=archive",
				"field" => "oid",
				"caption" => "Arhiiv",
		));
		$t->define_action(array(
				"link" => "class=tpledit&action=upload",
				"field" => "fullname",
				"caption" => "Upload",
		));
		$t->define_action(array(
				"link" => "class=tpledit&action=download",
				"field" => "file",
				"caption" => "Download",
		));

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

			$mx_data = $this->obj_get_meta(array("oid" => $oid));

			$archived = ($mx_data["archived"]) ? "checked" : "";

			$t->define_data(array(
				"oid" => $tdata[$relname]["oid"],
				"size" => $stat[$fullname][FILE_SIZE],
				"name" => $name,
				"uid" => $tdata[$relname]["modifiedby"],
				"date" => $this->time2date($stat[$fullname][FILE_MODIFIED],6),
				"file" => $relname,
				"fullname" => $fullname,
				"arc" => "<input type=checkbox name=arc[$oid] $archived value=1><input type=hidden name=exists[$oid] value=1>",
				"parent" => $parent,
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
		
		$title_path = array();
		$title_path[] = "<a href='" . $this->mk_orb("browse",array("parent" => "root")) . "'>TemplateEditor</a>";

		$title_path = $title_path + map2("<a href='orb.aw?class=tpledit&action=browse&parent=%s'>%s</a>",$path);
		$t->sort_by(array("field" => $args["sortby"]));

		$this->vars(array(
			"directory" => $d,
			"path" => $path,
			"files" => $t->draw(),
			"reforb" => $this->mk_reforb("submit_browse",array("parent" => $args["parent"])),
		));

		$test = $this->mk_orb("shak",array("kala" => "tursk2"));
		$GLOBALS["site_title"] = join(" / ",$title_path);
		$retval = $this->parse();
		return $retval;
	}

	function submit_browse($args = array())
	{
		extract($args);
		if (is_array($exists))
		{
			foreach($exists as $key => $val)
			{
				$this->obj_set_meta(array(
					"oid" => $key,
					"meta" => array("archived" => $arc[$key]),
				));
			};
		};
		return $this->mk_orb("browse",array("parent" => $parent));
	}

	// Kuvab faili edimise vormi
	function edit($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$meta_obj = $this->_fetch_tpl_obj(array("name" => $file));
		$oid = $meta_obj["oid"];
		if ($meta_obj["oid"])
		{
			$meta = $this->obj_get_meta(array(
					"oid" => $meta_obj["oid"],
			));
		};
		print "<pre>";
		print_r($meta);
		print "</pre>";
		$dirname = dirname($file);
		$source = join("",@file($tpldir . "/" . $file));
		$this->read_template("edit.tpl");
		$parents = explode("/",$file);

		$fullname = "";
	
		array_pop($parents);	

		$path = array();
		$path[] = "<a href='" . $this->mk_orb("browse",array("parent" => "root")) . "'>TemplateEditor</a>"; 
		foreach($parents as $name)
		{
			if (strlen($name) == 0)
			{
				continue;
			};
			$fullname .= "/$name";
			$index = (strlen($fullname) > 0) ? $fullname : $name;
			$path[$index] = sprintf("<a href='orb.aw?class=tpledit&action=browse&parent=%s'>%s</a>",$fullname,$name);
		};

		$this->vars(array(
			"file" => $file,
			"source" => htmlspecialchars($source),
			"name" => $meta["name"],
			"comment" => $meta["comment"],
			"rawlink" => $this->mk_orb("source",array("file" => $file)),
			"arclink" => $this->mk_orb("archive",array("oid" => $oid)),
			"reforb" => $this->mk_reforb("submit",array("file" => $file)),
		));
		$GLOBALS["site_title"] = join(" / ",$path);
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
			$timestamp = 0;
		}
		else
		{
			// Uuendame olemasoleva objekti metainfot
			$this->upd_object(array(
					"oid" => $meta_obj["oid"],
					"name" => $file,
			));

			$oid = $meta_obj["oid"];

			$ser = $this->_archive(array("oid" => $meta_obj["oid"]));
			// add a new copy of the template object to the archive
			$timestamp = $arc->commit(array(
				"oid" => $meta_obj["oid"],
				"content" => trim($ser),
				"name" => $name,
				"comment" => $comment,
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

	////
	// !Kuvab vormi, kust saab valida faili uploadimiseks
	function upload($args = array())
	{
		extract($args);
		$this->read_template("upload.tpl");
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_upload",array()),
		));
		$GLOBALS["site_title"] = "Upload template";
		return $this->parse();
	}


	function download($args = array())
	{
		extract($args);
		global $tpldirs,$HTTP_HOST;
		$tpldir = $tpldirs[$HTTP_HOST];
		$src_path = $tpldir . $file;
		$source = join("",@file($tpldir . "/" . $file));
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
		load_vcl("table");
		$meta = $this->obj_get_meta(array("oid" => $args["oid"]));
		$t = new aw_table(array(
                                "prefix" => "mailbox",
                                "imgurl"    => $GLOBALS["baseurl"]."/img",
                                "tbgcolor" => "#C3D0DC",
                        ));
		$t->parse_xml_def($GLOBALS["basedir"]."/xml/generic_table.xml");
		$t->set_header_attribs(array(
                                "class" => "tpledit",
                                "action" => "archive",
                                "oid" => $args["oid"],
                ));

		$t->define_field(array(
                                "name" => "name",
                                "caption" => "Nimi",
                                "talign" => "center",
                                "nowrap" => 1,
				"sortable" => 1,
                ));
		$t->define_field(array(
                                "name" => "uid",
                                "caption" => "Muutja",
                                "talign" => "center",
                                "nowrap" => 1,
				"sortable" => 1,
                ));
		$t->define_field(array(
                                "name" => "date",
                                "caption" => "Kuupäev",
                                "talign" => "center",
                                "nowrap" => 1,
				"sortable" => 1,
                ));
		$t->define_field(array(
                                "name" => "size",
                                "caption" => "Suurus",
                                "talign" => "center",
				"align" => "right",
                                "nowrap" => 1,
				"sortable" => 1,
                ));
		$t->define_field(array(
                                "name" => "activate",
                                "caption" => "Aktiveeri",
                                "talign" => "center",
				"align" => "center",
                                "nowrap" => 1,
                ));
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
				$t->define_data(array(
					"name" => $meta["archive"][$element[FILE_MODIFIED]]["name"],
					"uid" => $meta["archive"][$element[FILE_MODIFIED]]["uid"],
					"date" => $this->time2date($element[FILE_MODIFIED],9),
					"size" => $element[FILE_SIZE],
					"activate" => "<input type=radio name=active value=" . $element[FILE_MODIFIED] . ">",
				));

			};
		};
		$t->sort_by(array("field" => $args["sortby"]));
		$this->vars(array(
			"table" => $t->draw(),
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
