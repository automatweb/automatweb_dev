<?php
// tpledit.aw - Template Editor
classload("defs");

class tpledit extends aw_template 
{
	function tpledit($args =array())
	{
		$this->init("tpledit");
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
			"tbgcolor" => "#C3D0DC",
		));
		$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");

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

		$tpldir = $this->cfg["tpldir"];
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
		if ($dir = @opendir($tpldir)) 
		{
			while ($file = readdir($dir)) 
			{
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
				"date" => $this->time2date($stat[$fullname]["FILE_MODIFIED"],6),
				"size" => $stat[$fullname]["FILE_SIZE"],
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
				"size" => $stat[$fullname]["FILE_SIZE"],
				"name" => $name,
				"uid" => $tdata[$relname]["modifiedby"],
				"date" => $this->time2date($stat[$fullname]["FILE_MODIFIED"],6),
				"file" => $relname,
				"fullname" => $fullname,
				"arc" => "<input type=checkbox name=arc[$oid] $archived value=1><input type=hidden name=exists[$oid] value=1>",
				"parent" => $parent,
			));

			$f .= $this->parse("file");
		};
	
		$path = array();
		$path["root"] = aw_global_get("HTTP_HOST");

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

		$title_path = $title_path + map2("<a href='orb.".$this->cfg["ext"]."?class=tpledit&action=browse&parent=%s'>%s</a>",$path);
		$t->sort_by();

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
		$tpldir = $this->cfg["tpldir"];
		$meta_obj = $this->_fetch_tpl_obj(array("name" => $file));
		$oid = $meta_obj["oid"];
		classload("archive");
		$arc = new archive();
		if ($meta_obj["oid"])
		{
			$meta = $this->obj_get_meta(array(
					"oid" => $meta_obj["oid"],
			));
		};
		
		// we are trying to alter a file from the archive
		if ($revision)
		{
			$source = $arc->checkout(array(
				"oid" => $oid,
				"version" => $revision,
			));

			$arc_name = $meta["archive"][$revision]["name"];
			$arc_comment = $meta["archive"][$revision]["comment"];
		}
		else
		{	
			$source = join("",@file($tpldir . "/" . $file));
			$arc_name = $meta["arc_name"];
			$arc_comment = $meta["arc_comment"];
		}
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
			$path[$index] = sprintf("<a href='orb.".$this->cfg["ext"]."?class=tpledit&action=browse&parent=%s'>%s</a>",$fullname,$name);
		};

		$this->vars(array(
			"file" => $file,
			"source" => htmlspecialchars($source),
			"name" => $arc_name,
			"comment" => $arc_comment,
			"rawlink" => $this->mk_orb("source",array("file" => $file)),
			"arclink" => $this->mk_orb("archive",array("oid" => $oid)),
			"preview_url" => $this->mk_orb("preview",array("file" => $file)),
			"reforb" => $this->mk_reforb("submit",array("file" => $file,"revision" => $revision)),
		));
		$GLOBALS["site_title"] = join(" / ",$path);
		return $this->parse();
	}

	function submit($args = array())
	{
		extract($args);
		$tpldir = $this->cfg["tpldir"];
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
			$arc->add(array("oid" => $oid));
			$timestamp = 0;
			$activate = 1;
			$meta_obj["oid"] = $oid;
		};
		if ($activate)
		{
			// Uuendame olemasoleva objekti metainfot
			$this->upd_object(array(
				"oid" => $meta_obj["oid"],
				"name" => $file,
			));
		};
				
		$oid = $meta_obj["oid"];

		if ($archive || $revision)
		{
			if (not($revision))
			{
				$ser = $this->_archive(array("oid" => $meta_obj["oid"]));
			}
			else
			{
				$ser = stripslashes($source);
			};
			// add a new copy of the template object to the archive
			$timestamp = $arc->commit(array(
				"oid" => $meta_obj["oid"],
				"content" => trim($ser),
				"name" => $name,
				"comment" => $comment,
				"version" => $revision,
			));
		};

		if ($revision)
		{
			$do_update = $activate;
		}
		else
		{
			$do_update = true;
		};

		if ($do_update)
		{
			$this->obj_set_meta(array(
				"oid" => $oid,
				"meta" => array(
					"arc_name" => $name,
					"arc_comment" => $comment,
					),
			));

			$this->put_file(array(
				"file" => $fullpath,
				"content" => stripslashes($source),
			));
		};
		$this->_log("template", "changed template $fullpath");
		return $this->mk_orb("edit",array("file" => $file,"revision" => $revision));
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
			"reforb" => $this->mk_reforb("submit_upload",array("fullname" => $fullname)),
		));
		$GLOBALS["site_title"] = "Upload template";
		return $this->parse();
	}

	function submit_upload($args = array())
	{
		extract($args);
		global $HTTP_POST_FILES;
		$filedat = $HTTP_POST_FILES["template"];
		$tpldir = $this->cfg["tpldir"];
		// we got a file, so register it
		if (is_array($filedat))
		{
			$realname = substr($fullname,strlen($tpldir) + 1);
			$meta_obj = $this->_fetch_tpl_obj(array("name" => $realname));
			// name - original name
			// tmp_name - the current location
		}
	}


	function download($args = array())
	{
		extract($args);
		$tpldir = $this->cfg["tpldir"];
		$src_path = $tpldir . $file;
		$source = join("",@file($tpldir . "/" . $file));
		$realname = basename($file);
		header("Content-Type: text/plain");
		header("Content-Disposition: filename=$realname");
		print $source;
		exit;
	}
	
	function preview($args = array())
	{
		extract($args);
		$tpldir = $this->cfg["tpldir"];
		$src_path = $tpldir . "/" . $file;
		$this->read_template("preview.tpl");
		$this->vars(array(
			"srcurl" => $this->mk_orb("source",array("file" => $file)),
		));
		return $this->parse();
	}
	
	function source($args = array())
	{
		extract($args);
		$tpldir = $this->cfg["tpldir"];
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
		$tpldir = $this->cfg["tpldir"];
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
			"tbgcolor" => "#C3D0DC",
		));
		$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
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
		$t->define_field(array(
			"name" => "act1",
			"caption" => "Tegevus",
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
			foreach($contents as $elname => $element)
			{
				$name = $meta["archive"][$elname]["name"];
				if (strlen($name) == 0)
				{
					$name = "(nimetu)";
				};

				$namelink = $this->mk_orb("edit",array("file" => $obj["name"],"revision" => $elname)); 

				$t->define_data(array(
					"act1" => sprintf("<a href='%s'>%s</a>",$namelink,"Muuda"),
					"name" => $name,
					"uid" => $meta["archive"][$elname]["uid"],
					"date" => $this->time2date($elname,9),
					"size" => $element["FILE_SIZE"],
					"activate" => "<input type=radio name=active value=" . $elname . ">",
				));

			};
		};
		$t->sort_by();
		$this->vars(array(
			"table" => $t->draw(),
			"reforb" => $this->mk_reforb("submit_archive",array("oid" => $args["oid"])),
			"edlink" => $this->mk_orb("edit",array("file" => $obj["name"])),
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
		
		$tpldir = $this->cfg["tpldir"];
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
