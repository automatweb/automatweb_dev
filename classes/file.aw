<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/file.aw,v 2.61 2003/10/15 10:59:15 kristo Exp $
// file.aw - Failide haldus

// if files.file != "" then the file is stored in the filesystem
// otherwise it is stored in the db
//
// original file name is stored in objects.name - kind of silly, yeah, but whatever
//
// <terryf> failide tabelis on v2li
// <terryf> showal
// <terryf> mis n2itab et kas faili n2datakse kohe v6i ei
// <terryf> ja siis kui showal=1
// <terryf> siis ocitaxe selle faili seest kui ta on m6ne dokumendi juurde aliasex pandud
//
// all file saving operatios are done in save_file - they should stay there so we can configure whether 
// we use db storage of filesystem storage for files
//

/*
	@classinfo trans=1
	@default table=files
	@default group=general

	@property filename type=text store=no field=name
	@caption Faili nimi

	@property file type=fileupload 
	@caption Vali fail

	@property type type=hidden

	@property comment type=textbox table=objects field=comment
	@caption Faili allkiri

	@property file_url type=textbox table=objects field=meta method=serialize
	@caption Url, kust saadakse faili sisu

	@property showal type=checkbox ch_value=1
	@caption Näita kohe

	@property newwindow type=checkbox ch_value=1 group=settings
	@caption Uues aknas

	@default table=objects
	@default field=meta
	@default method=serialize
	
	@property show_framed type=checkbox ch_value=1 group=settings
	@caption Näita saidi raamis

	@property view type=text editonly=1
	@caption Näita faili

	@property j_time type=date_select group=dates
	@caption Jõustumise kuupäev

	@property act_date type=date_select group=dates
	@caption Avaldamise kuupäev

	@groupinfo settings caption=Seadistused
    @groupinfo dates caption=Ajad

	@tableinfo files index=id master_table=objects master_index=oid	
	@classinfo trans_id=TR_FILE

*/


class file extends class_base
{
	////
	// !Konstruktor
	function file()
	{
		enter_function("file::file",array());
		$this->init(array(
			"clid" => CL_FILE,
			"tpldir" => "file",
			"trid" => TR_FILE,
		));
		lc_load("definition");
		$this->lc_load("file","lc_file");
		exit_function("file::file");
	}

	function get_property($arr)
	{
		enter_function("file::get_property",array());
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "name":
				$retval = PROP_IGNORE;
				break;
			case "filename":
				classload("icons");
				$name = $arr["obj_inst"]->prop("name");
				$data["value"] = html::img(array(
					"url" => icons::get_icon_url(CL_FILE,$name),
				))." ".$name;
				break;
			case "view":
				$fname = basename($arr["obj_inst"]->prop("file"));
				if (empty($fname))
				{
					$retval = PROP_IGNORE;
				}
				else
				{
					$file = $this->cfg["site_basedir"]."/files/".$fname[0]."/".$fname;
					$size = filesize($file);
					$data["value"] = html::href(array(
						"url" => $this->mk_my_orb("preview",array("id" => $arr["obj_inst"]->id(),"name" => urlencode($arr["obj_inst"]->prop("name")))),
						"caption" => sprintf("%s (%dK)",$arr["obj_inst"]->prop("name"),$size/1024),
						"target" => "_blank",
					));
				};
				break;

			case "file":
				$data["value"] = "";
				break;

		}
		exit_function("file::get_property");
		return $retval;
	}

	function set_property($arr = array())
	{
		enter_function("file::set_property",array());
		$data = &$arr["prop"];
		$form_data = &$arr["form_data"];
		global $file, $file_type,$file_name;
		$retval = PROP_OK;
		if ($data["name"] == "name")
		{
			$retval = PROP_IGNORE;
		};
		if ($data["name"] == "file_url")
		{
			if ($data["value"] != "")
			{
				$proto_find = get_instance("protocols/protocol_finder");
				$proto_inst = $proto_find->inst($data["value"]);

				$str = $proto_inst->get($data["value"]);
				preg_match("/<title>(.*)<\/title>/isU", $str, $mt);
				$this->file_name = $mt[1];
			}
		}
		else
		if ($data["name"] == "file")
		{
			if (is_uploaded_file($file))
			{
				// fail sisse
				$fc = $this->get_file(array(
					"file" => $file,
				));
		
				if ($fc != "")
				{
					// stick the file in the filesystem
					$fs = $this->_put_fs(array(
						"type" => $file_type,
						"content" => $fc,
					));

					$form_data["file"] = $fs;
					// try and resolve the file type from file extension

					$pathinfo = pathinfo($file_name);
					$mimeregistry = get_instance("core/aw_mime_types");

					$realtype = $mimeregistry->type_for_ext($pathinfo["extension"]);
					$form_data["type"] = $realtype;

					$data["value"] = $fs;
					$this->file_name = $file_name;
				};
		
			}
			else
			{
				$retval = PROP_IGNORE;
			};
		};
		// cause everything is alreay handled here
		exit_function("file::set_property");
		return $retval;
	}

	function callback_pre_save($arr)
	{
		enter_function("file::callback_pre_save",array());
		// overwrite the name if new file is uploaded
		if (isset($this->file_name))
		{
			$arr["obj_inst"]->set_prop("name",$this->file_name);
		};
		exit_function("file::callback_pre_save");
	}

	////
	// !Aliaste parsimine
	function parse_alias($args = array())
	{
		enter_function("file::parse_alias",array());
		extract($args);
		if (!$alias["target"])
		{
		exit_function("file::parse_alias");
			return "";
		}

		$fi = $this->get_file_by_id($alias["target"]);
		if ($fi["showal"] == 1 && $fi["meta"]["show_framed"])
		{
			// so what if we have it twice?
			$this->dequote(&$fi["content"]);
			$fi["content"] .= "</body>";
			if (strpos(strtolower($fi["content"]),"<body>"))
			{
				preg_match("/<body(.*)>(.*)<\/body>/imsU",$fi["content"],$map);
				// return only the body of the file
   				$replacement = str_replace("\n","",$map[2]);
			}
			else
			{
				$replacement = $fi["content"];
			};
		}
		else
		if ($fi["showal"] == 1)
		{
			// n2itame kohe
			// kontrollime koigepealt, kas headerid on ehk väljastatud juba.
			// dokumendi preview vaatamisel ntx on.
			if ($fi["type"] == "text/html")
			{
				if (!headers_sent())
				{
					header("Content-type: text/html");
				};
    
				// so what if we have it twice?
				$this->dequote(&$fi["content"]);
				$fi["content"] .= "</body>";
				if (strpos(strtolower($fi["content"]),"<body>"))
				{
					preg_match("/<body(.*)>(.*)<\/body>/imsU",$fi["content"],$map);
					// return only the body of the file
	     				$replacement = str_replace("\n","",$map[2]);
				}
				else
				{
					$replacement = $fi["content"];
				};
			}
			// embed xml files
			elseif ($fi["type"] == "text/xml")
			{
				$replacement = htmlspecialchars($fi["content"]);
				$replacement = str_replace("\n","<br />\n",$replacement);
				// tabs
				$replacement = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$replacement);
			}
			else
			{
				header("Content-type: ".$fi["type"]);
				header("Content-Disposition: filename=$fi[name]");
				die($fi["content"]);
			}
		}
		else
		{
			if ($fi["newwindow"])
			{
				$ss = "target=\"_blank\"";
			}
			
			$comment = $fi["comment"];
			if ($comment == "")
			{
				$comment = $fi["name"];
			}
		
			if ($fi["meta"]["show_framed"])
			{
				$url = $this->cfg["baseurl"]."/section=$alias[source]/oid=$alias[target]";
			}
			else
			{
				$url = $this->get_url($alias["target"],$fi["name"]);
			};
			$replacement = "<a $ss class=\"sisutekst\" href='".$url."'>$comment</a>";
		}
		exit_function("file::parse_alias");
		return $replacement;
	}

	////
	// !Salvestab faili failisysteemi. For internal use, s.t. kutsutakse välja save_file seest
	// returns the name of the file that the data was saved in
	function _put_fs($args = array())
	{
		enter_function("file::_put_fs",array());
		$site_basedir = $this->cfg["site_basedir"];
		// find the extension for the file
		list($major,$minor) = explode("/",$args["type"]);

		// first, we need to find a path to put the file
		$filename = gen_uniq_id();
		$prefix = substr($filename,0,1);
		if (!is_dir($site_basedir . "/files/" . $prefix))
		{
			mkdir($site_basedir . "/files/" . $prefix,0705);
		}

		$file = $site_basedir . "/files/" . $prefix . "/" . "$filename.$minor";
		$this->put_file(array(
			"file" => $file,
			"content" => $args["content"],
		));
		exit_function("file::_put_fs");
		return $file;
	}

	////
	// !Checks whether a record in the files table is an image (can be embedded inside the web page)
	// $args should contain line from that table
	function can_be_embedded(&$row)
	{
		enter_function("file::can_be_embedded",array());
		if (is_object($row))
		{
		exit_function("file::can_be_embedded");
			return in_array($row->prop("type"),$this->cfg["embtypes"]);
		}
		else
		{
		exit_function("file::can_be_embedded");
			return in_array($row["type"],$this->cfg["embtypes"]);
		}
		exit_function("file::can_be_embedded");
	}


	////
	// !writes file to database - internal usage only, most of the parameters can be omitted
	// $file_id - if specified, overwrites it, if not, creates a new one
	// $name - the original name of file
	// $showal - if we should show the file immediately
	// $type - file MIME type
	// $content - file content
	// $newwindow - if one, file link will open in new window
	// $parent - where to save the file in aw 
	// $comment - comment
	// returns the id if the file
	function save_file($arr)
	{
		enter_function("file::save_file",array());
		extract($arr);

		if ($content != "")
		{
			// stick the file in the filesystem
			$fs = $this->_put_fs(array("type" => $type, "content" => $content));
		}

		// now if we need to create a new object, do so
		if (!$file_id)
		{
			$file_id = $this->new_object(array(
				"parent" => $parent,
				"class_id" => CL_FILE,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"act_time" => $act_time,
					"j_time" => $j_time,
					"show_framed" => $show_framed,
				)
			));
			$this->db_query("INSERT INTO files(id,file,showal,type,newwindow) 
				VALUES('$file_id','$fs','$showal','$type','$newwindow') ");
		}
		else
		{
			// change existing
			$co = array("oid" => $file_id);
			if ($parent)
			{
				$co["parent"] = $parent;
			}
			if (isset($comment))
			{
				$co["comment"] = $comment;
			}
			$co["metadata"]["act_time"] = $act_time;
			$co["metadata"]["j_time"] = $j_time;
			$co["metadata"]["show_framed"] = $show_framed;

			$upd = array();
			if ($fs != "")
			{
				$co["name"] = $name;
				$upd[] = "file = '$fs'";
				$upd[] = "type = '$type'";
				$upd[] = "content = ''";	// if file content was specified, remove old file data to save resources
			}
			$upd[] = "showal = '$showal'";
			$upd[] = "newwindow = '$newwindow'";
			$this->upd_object($co);
			$upds = join(",",$upd);
			$this->db_query("UPDATE files SET $upds WHERE id = '$file_id'");
		}

		exit_function("file::save_file");
		return $file_id;
	}

	////
	// !Selle funktsiooni abil salvestatakse fail systeemi sisse,
	// soltuvalt parameetrist store väärtusest
	// argumendid:
	// filename(string) - faili nimi
	// type(string) - faili tyyp (MIME)
	// content(string) - faili sisu
	function put($args = array())
	{
		enter_function("file::put",array());
		extract($args);
		$this->save_file(array(
			"type" => $type,
			"content" => $content,
			"parent" => $parent,
			"name" => $filename,
			"comment" => $comment
		));
		exit_function("file::put");
	}

	////
	// !Salvestab special faili, ehk siis otse files kataloogi
	// argumendid:
	// name(string) - faili nimi
	// data(string) - faili sisu
	// path(string) - path alates "files" kataloogist
	// sys(bool) - kas panna faili systeemi juurde?
	function put_special_file($args = array())
	{
		enter_function("file::put_special_file",array());
		if ($args["sys"])
		{
			$path = $this->cfg["basedir"] . "/files";
		}
		else
		{
			$path = $this->cfg["site_basedir"] . "/files";
		};

		if ($args["path"])
		{
			$path .= "/" . $args["path"];
		};

		$success =$this->put_file(array(
			"file" => $path . "/" . $args["name"],
			"content" => $args["content"],
		));

		exit_function("file::put_special_file");
		return $success;
	}
	
	function get_special_file($args = array())
	{
		enter_function("file::get_special_file",array());
		if ($args["sys"])
		{
			$path = $this->cfg["basedir"] . "/files";
		}
		else
		{
			$path = $this->cfg["site_basedir"] . "/files";
		};

		if ($args["path"])
		{
			$path .= "/" . $args["path"];
		};

		$contents  =$this->get_file(array(
			"file" => $path . "/" . $args["name"],
		));

		exit_function("file::get_special_file");
		return $contents;
	}

	////
	// !Teeb failiobjekist koopia uue parenti alla
	// argumendid:
	// id - faili id, millest koopia teha
	// parent - koht, mille alla koopia teha
	function cp($args = array())
	{
		enter_function("file::cp",array());
		extract($args);
		$old = $this->get_file_by_id($id);
		$old["file_id"] = 0;
		$old["parent"] = $parent;
		$this->save_file($old);
		exit_function("file::cp");
	}

	////
	// !checks whether the directory needed for file storing exists and is writable
	function check_environment($args = array())
	{
		enter_function("file::check_environment",array());
		$retval = "";
		if ($this->cfg["site_basedir"] == "") 
		{
			$retval .= LC_FILE_DIR_NOT_DEFINED;
		}
		else
		{
			$dir = $this->cfg["site_basedir"] . "/files";
			$preflist = array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");
			$dirlist[] = $dir;
			foreach($preflist as $prefix)
			{
				$dirlist[] = $dir . "/" . $prefix;
			};

			foreach($dirlist as $dir)
			{
				if (!file_exists($dir))
				{
					$retval .= "Kataloog $dir puudub<br />";
				}
				elseif (!is_dir($dir))
				{
					$retval .= "$dir ei ole kataloog<br />";
				}
				elseif (!is_writable($dir))
				{
					$retval .= "$dir ei ole kirjutatav<br />";
				};
			};
		};
		exit_function("file::check_environment");
		return $retval;
	}

	////
	// !returns file by id
	function get_file_by_id($id) 
	{
		enter_function("file::get_file_by_id",array());
		$row = new aw_array($this->get_object($id));
		$this->db_query("SELECT * FROM files WHERE id = $id");
		$ar = new aw_array($this->db_next());
		$ret = $row->get() + $ar->get();

		$ret["file"] = basename($ret["file"]);
		if ($ret["meta"]["file_url"] != "")
		{
			$proto_find = get_instance("protocols/protocol_finder");
			$proto_inst = $proto_find->inst($ret["meta"]["file_url"]);

			$ret["content"] = $proto_inst->get($ret["meta"]["file_url"]);
			$ret["type"] = $proto_inst->get_type();
		}
		else
		if ($ret["file"] != "")
		{
			// file saved in filesystem - fetch it
			$file = $this->cfg["site_basedir"]."/files/".$ret["file"][0]."/".$ret["file"];
			$tmp = $this->get_file(array("file" => $file));
			if ($tmp !== false)
			{
				$ret["content"] = $tmp;
			}
		}
		else
		{
			$this->dequote($ret["content"]);
		};
		exit_function("file::get_file_by_id");
		return $ret;
	}

	////
	// !Näitab faili. DUH.
	function show($id)
	{
		enter_function("file::show",array());
		if (is_array($id))
		{
			extract($id);
		}
		// allow only integer id-s
		$id = (int)$id;
		$fc = $this->get_file_by_id($id);

		if ($fc["type"] == "")
		{
			$pi = pathinfo($fc["name"]);
			$mimeregistry = get_instance("core/aw_mime_types");
			$fc["type"] = $mimeregistry->type_for_ext($pi["extension"]);
		}
		header("Content-type: ".$fc["type"]);
		header("Cache-control: public");
		//header("Content-Disposition: inline; filename=\"$fc[name]\"");
		//header("Content-Length: ".strlen($fc["content"]));
		//header("Pragma: no-cache");
		die($fc["content"]);
		exit_function("file::show");
	}

	function view($args = array())
	{
		enter_function("file::view",array());
		extract($args);
		$fc = $this->get_file_by_id($id);
		if ($this->can_be_embedded($fc))
		{
			$this->mk_path($fc["parent"],"Näita faili");
			print $fc["content"];
		}
		else
		{
			if ($fc["type"] == "")
			{
				$pi = pathinfo($fc["name"]);
				$mimeregistry = get_instance("core/aw_mime_types");
				$fc["type"] = $mimeregistry->type_for_ext($pi["extension"]);
			}
			header("Content-type: ".$fc["type"]);
			header("Content-Disposition: filename=$fc[name]");
			header("Pragma: no-cache");
			die($fc["content"]);
		};


		exit_function("file::view");
	}

	function get_url($id,$name)	
	{
		enter_function("file::get_url",array());
		$retval = str_replace("automatweb/","",$this->mk_my_orb("preview", array("id" => $id),"file", false,true,"/"))."/".urlencode(str_replace("/","_",$name));
//		$retval = $this->mk_my_orb("preview", array("id" => $id),"file", false,true);
		exit_function("file::get_url");
		return $retval;
	}

	////
	// !rewrites the url to the correct value
	// removes host name
	// translates site/files.aw/id=666/filename to orb calls
	// adds baseurl
	// removes fastcall=1
	function check_url($url)
	{
		enter_function("file::check_url",array());
		if ($url == "")
		{
		exit_function("file::check_url");
			return $url;
		}
		$url = preg_replace("/^http:\/\/(.*)\//U","/",$url);

		// don't convert image class urls
		if (strpos($url,"class=image") === false)
		{
			if (substr($url,0,6) == "/files")
			{
				$fileid = (int)(substr($url,13));
				$filename = urlencode(substr($url,strrpos($url,"/")));
				$url = "/orb.".aw_ini_get("ext")."/class=file/action=show/id=".$fileid."/".$filename;
			}
			else
			if (($sp = strpos($url,"fastcall=1")) !== false)
			{
				$url = substr($url,0,$sp).substr($url,$sp+10);
			}
		}
		$url = str_replace("automatweb/", "", $url);
		exit_function("file::check_url");
		return aw_ini_get("baseurl").$url;
	}

	////
	// !saves a file that was uploaded in a form to the db
	// $name - the name of the file input in form
	// $parent - the parent object of the file
	// $file_id - if not specified, file will be added, else changed
	function add_upload_image($name,$parent,$file_id = 0)
	{
		enter_function("file::add_upload_image",array());
		$file_id = (int)$file_id;

		if ($file_id)
		{
			$fd = $this->get_object($file_id);
		}

		global $HTTP_POST_FILES;
		$tmp_name = $HTTP_POST_FILES[$name]['tmp_name'];
		if (is_uploaded_file($tmp_name))
		{
			$type = $HTTP_POST_FILES[$name]['type'];
			$fname = $HTTP_POST_FILES[$name]["name"];

			// if a new file was uploaded, we can forget about the previous one 
			if ($fd["class_id"] != CL_FILE)
			{
				$file_id = 0;
				$fd = array();
			}

			$fc = $this->get_file(array("file" => $tmp_name));

			$id = $this->save_file(array(
				"file_id" => $file_id,
				"parent" => $parent,
				"name" => $fname,
				"content" => $fc,
				"type" => $type
			));

		exit_function("file::add_upload_image");
			return array("id" => $id,"url" => $this->get_url($id,$fname), "orig_name" => $fname);
		}
		else
		{
			if ($file_id)
			{
				if ($fd["class_id"] != CL_FILE)
				{
					// we gots problems - this is probably an old image file from formgen
					if ($fd["class_id"] == CL_IMAGE)
					{
						// let the image class handle this
						$im = get_instance("image");
						$id = $im->get_image_by_id($file_id);
		exit_function("file::add_upload_image");
						return array("id" => $file_id,"url" => $id["url"]);
					}
					// if we get here, we're pretty much fucked, so bail out
					$this->raise_error(ERR_FILE_WRONG_CLASS, "Objekt $file_id on valet tyypi ($fd[class_id])",true);
				}
				else
				{
		exit_function("file::add_upload_image");
					return array("id" => $file_id,"url" => $this->get_url($file_id, $fd["name"]), "orig_name" => $fd["name"]);
				}
			}
			else
			{
		exit_function("file::add_upload_image");
				return false;
			}
		}
		exit_function("file::add_upload_image");
	}

	function request_execute($obj)
	{
		enter_function("file::request_execute",array());
		exit_function("file::request_execute");
		return $this->show($obj->id());
	}
};
?>
