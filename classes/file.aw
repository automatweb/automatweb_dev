<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/file.aw,v 2.115 2005/10/21 21:06:45 duke Exp $
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
	@classinfo trans=1 relationmgr=yes syslog_type=ST_FILE
	@default table=files
	@default group=general

	@property filename type=text store=no field=name form=+emb
	@caption Faili nimi

	@property file type=fileupload form=+emb
	@caption Vali fail

	@property type type=hidden

	@property ord type=textbox size=3 table=objects field=jrk
	@caption J&auml;rjekord

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

	@property j_time type=date_select group=dates
	@caption Jõustumise kuupäev

	@property act_date type=date_select group=dates
	@caption Avaldamise kuupäev

	@groupinfo settings caption=Seadistused
    @groupinfo dates caption=Ajad

	@property udef1 type=textbox display=none
	@caption User-defined 1

	@property udef2 type=textbox display=none
	@caption User-defined 2

	@tableinfo files index=id master_table=objects master_index=oid	

	@reltype KEYWORD value=2 clid=CL_KEYWORD
	@caption Märksõna
*/


class file extends class_base
{
	////
	// !Konstruktor
	function file()
	{
		//obj_set_opt("no_cache", 1);
		$this->init(array(
			"clid" => CL_FILE,
			"tpldir" => "file",
		));
		lc_load("definition");
		$this->lc_load("file","lc_file");

	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "name":
				$retval = PROP_IGNORE;
				break;

			case "filename":
				if ($arr["new"])
				{
					$retval = PROP_IGNORE;
				}
				classload("core/icons");

				$fname = basename($arr["obj_inst"]->prop("file"));

				if ($fname == "" && $arr["obj_inst"]->prop("file_url") == "")
				{
					$file = $this->cfg["site_basedir"]."/files/".$fname[0]."/".$fname;
					$data["value"] = t("fail puudub");
					return PROP_OK;
				}
				else
				{
					$file = $this->cfg["site_basedir"]."/files/".$fname{0}."/".$fname;
				}

				if (is_file($file))
				{
					$size = @filesize($file);
					if ($size > 1024)
					{
						$filesize = number_format($size / 1024, 2)."kb";
					}
					else
					if ($size > (1024*1024))
					{
						$filesize = number_format($size / (1024*1024), 2)."mb";
					}
					else
					{
						$filesize = $size." b";
					}

					$name = $arr["obj_inst"]->prop("name");
					if (empty($name))
					{
						$name = $arr["obj_inst"]->prop("file");
					}

					$data["value"] = html::href(array(
						"url" => $this->get_url($arr["obj_inst"]->id(), $arr["obj_inst"]->name()),
						"caption" => html::img(array(
							"url" => icons::get_icon_url(CL_FILE,$name),
							"border" => "0"
							))." ".$name.", ".$filesize,
						"target" => "_blank",
					));
				}
				else
				{
					$fu = $arr["obj_inst"]->prop("file_url");
					$name = basename($fu);
					$data["value"] = html::href(array(
						"url" => $fu,
						"caption" => html::img(array(
							"url" => icons::get_icon_url(CL_FILE,$name),
							"border" => "0"
							))." ".$name,
						"target" => "_blank",
					));
				}
				break;

			case "file":
				$data["value"] = "";
				break;

		}
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$request = &$arr["request"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "name":
				$retval = PROP_IGNORE;
				break;

			case "file_url":
				if (!empty($data["value"]))
				{
					$proto_find = get_instance("protocols/protocol_finder");
					$proto_inst = $proto_find->inst($data["value"]);

					$str = $proto_inst->get($data["value"]);
					preg_match("/<title>(.*)<\/title>/isU", $str, $mt);
					if ($mt[1] == "")
					{
						$mt[1] = basename($data["value"]);
					}
					$arr["obj_inst"]->set_name($mt[1]);
				}
				break;

			case "file":
				// see asi eeldab ajutise faili tegemist eksole?

				// ah sa raisk küll, siinkohal on mul ju konkreetse faili sisu
				if (is_array($data["value"]))
				{
					$file = $data["value"]["tmp_name"];
					$file_type = $data["value"]["type"];
					$file_name = $data["value"]["name"];
				}
				else
				{
					$file = $_FILES["file"]["tmp_name"];
					$file_name = $_FILES["file"]["name"];
					$file_type = $_FILES["file"]["type"];
				};

				if (is_uploaded_file($file))
				{
					if ($this->cfg["upload_virus_scan"])
					{
						if (($vir = $this->_do_virus_scan($file)))
						{
							$data["error"] = "Uploaditud failis on viirus $vir!";
							return PROP_FATAL_ERROR;
						}
					}

					
					$pathinfo = pathinfo($file_name);
					if (empty($file_type))
					{
						$mimeregistry = get_instance("core/aw_mime_types");
						$realtype = $mimeregistry->type_for_ext($pathinfo["extension"]);
						$file_type = $realtype;
					};
					
					$final_name = $this->generate_file_path(array(
						"type" => $file_type,
					));
						
					move_uploaded_file($file, $final_name);
					$data["value"] = $final_name;
					$arr["obj_inst"]->set_name($file_name);
					$arr["obj_inst"]->set_prop("type", $file_type);
					$this->file_type = $file_type;
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;
				
			case "type":
				if ($this->file_type)
				{
					$data["value"] = $this->file_type;
				}
				break;
		};
		// cause everything is alreay handled here
		return $retval;
	}

	function callback_post_save($arr)
	{
		// overwrite the name if new file is uploaded
		/*if (isset($this->file_name))
		{
			$arr["obj_inst"]->set_name($this->file_name);
			$arr["obj_inst"]->save();
		};*/
	}

	////
	// !Aliaste parsimine
	function parse_alias($args = array())
	{
		extract($args);
		if (!$alias["target"])
		{
			return "";
		}

		$fi = $this->get_file_by_id($alias["target"], false);
		if ($fi["showal"] == 1 && $fi["meta"]["show_framed"])
		{
			$fi = $this->get_file_by_id($alias["target"], true);
			// so what if we have it twice?
			$this->dequote(&$fi["content"]);
			$fi["content"] .= "</body>";
			if (strpos(strtolower($fi["content"]),"<body"))
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
			$fi = $this->get_file_by_id($alias["target"], true);
			// n2itame kohe
			// kontrollime koigepealt, kas headerid on ehk väljastatud juba.
			// dokumendi preview vaatamisel ntx on.
			if (trim($fi["type"]) == "text/html")
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
			elseif (trim($fi["type"]) == "text/xml")
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
				$url = $this->cfg["baseurl"]."/section=".aw_global_get("section")."/oid=$alias[target]";
			}
			else
			{
				$url = $this->get_url($alias["target"],$fi["name"]);
			};

			classload("core/icons");
			$icon = icons::get_icon_url(CL_FILE,$fi["name"]);

			if ($tpls["file_inplace"] != "")
			{
				$replacement = localparse($tpls["file_inplace"], array(
					"file_url" => $url,
					"file_name" => $comment,
					"file_icon" => $icon
				));
				$ret = array(
					"replacement" => $replacement,
					"inplace" => "file_inplace"
				);
				return $ret;
			}
			else
			if ($tpls["file"] != "")
			{
				$replacement = localparse($tpls["file"], array(
					"file_url" => $url,
					"file_name" => $comment,
					"file_icon" => $icon
				));
			}
			else
			{
				if (!aw_ini_get("file.no_icon"))
				{
					$replacement = html::img(array(
						"url" => $icon,
						'border' => 0,
					));
				}
				$replacement .= " <a $ss class=\"sisutekst\" href='".$url."'>$comment</a>";
			}
		}
		return $replacement;
	}

	////
	// !Salvestab faili failisysteemi. For internal use, s.t. kutsutakse välja save_file seest
	// returns the name of the file that the data was saved in
	function _put_fs($arr)
	{
		$file = $this->generate_file_path($arr);
		$this->put_file(array(
			"file" => $file,
			"content" => $arr["content"],
		));
		return $file;
	}

	function generate_file_path($arr)
	{
		$site_basedir = $this->cfg["site_basedir"];
		// find the extension for the file
		list($major,$minor) = explode("/",$arr["type"]);
		if ($minor == "pjpeg" || $minor == "jpeg")
		{
			$minor = "jpg";
		}

		// first, we need to find a path to put the file
		$filename = gen_uniq_id();
		$prefix = substr($filename,0,1);
		if (!is_dir($site_basedir . "/files/" . $prefix))
		{
			mkdir($site_basedir . "/files/" . $prefix,0705);
		}

		$file = $site_basedir . "/files/" . $prefix . "/" . "$filename.$minor";
		return $file;
	}

	////
	// !Checks whether a record in the files table is an image (can be embedded inside the web page)
	// $args should contain line from that table
	function can_be_embedded(&$row)
	{
		if (is_object($row))
		{
			return in_array($row->prop("type"),$this->cfg["embtypes"]);
		}
		else
		{
			return in_array($row["type"],$this->cfg["embtypes"]);
		}
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
		extract($arr);

		if ($content != "")
		{
			// stick the file in the filesystem
			$fs = $this->_put_fs(array("type" => $type, "content" => $content));
		}

		// now if we need to create a new object, do so
		if (!$file_id)
		{
			$o = obj();
			$o->set_parent($parent);
			$o->set_class_id(CL_FILE);
			$o->set_name($name);
			$o->set_comment($comment);
			$o->set_meta("show_framed", $show_framed);
			$o->set_prop("file", $fs);
			$o->set_prop("showal", $showal);
			$o->set_prop("type", $type);
			$o->set_prop("newwindow", $newwindow);
			$file_id = $o->save();
		}
		else
		{
			// change existing
			$o = obj($file_id);
			if ($parent)
			{
				$o->set_parent($parent);
			}
			if (isset($comment))
			{
				$o->set_comment($comment);
			}
			$o->set_meta("show_framed",$show_framed);

			if ($fs != "")
			{
				$o->set_name($name);
				$o->set_prop("file",$fs);
				$o->set_prop("type",$type);
			}

			$o->set_prop("showal", $showal);
			$o->set_prop("newwindow", $newwindow);
			$o->save();
		}

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
		extract($args);
		$this->save_file(array(
			"type" => $type,
			"content" => $content,
			"parent" => $parent,
			"name" => $filename,
			"comment" => $comment
		));
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

		return $success;
	}
	
	function get_special_file($args = array())
	{
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

		return $contents;
	}

	////
	// !Teeb failiobjekist koopia uue parenti alla
	// argumendid:
	// id - faili id, millest koopia teha
	// parent - koht, mille alla koopia teha
	function cp($args = array())
	{
		extract($args);
		$old = $this->get_file_by_id($id);
		$old["file_id"] = 0;
		$old["parent"] = $parent;
		$this->save_file($old);
	}

	////
	// !returns file by id
	function get_file_by_id($id, $fetch_file = true) 
	{
		$tmp = obj($id);
		if ($tmp->class_id() != CL_FILE)
		{
			return array();
		}
		$ret = $tmp->fetch();
		$ret["id"] = $id;

		$ret["file"] = basename($ret["file"]);
		if ($fetch_file)
		{
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
		}
		return $ret;
	}

	/** Näitab faili. DUH. 
		
		@attrib name=preview params=name nologin="1" default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function show($id)
	{
		if (is_array($id))
		{
			extract($id);
		}
		// allow only integer id-s
		$id = (int)$id;
		error::view_check($id);
		$fc = $this->get_file_by_id($id);
		$pi = pathinfo($fc["name"]);
		$mimeregistry = get_instance("core/aw_mime_types");
		$tmp = $mimeregistry->type_for_ext($pi["extension"]);
		if ($tmp != "")
		{
			$fc["type"] = $tmp;
		}
		header("Content-Length: ".strlen($fc["content"]));
		header("Content-type: ".$fc["type"]);
		header("Cache-control: public");
		//header("Content-Disposition: inline; filename=\"$fc[name]\"");
		//header("Content-Length: ".strlen($fc["content"]));
		//header("Pragma: no-cache");
		die($fc["content"]);
	}

	/**  
		
		@attrib name=view params=name nologin="1" default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function view($args = array())
	{
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


	}

	function get_url($id,$name)	
	{
		$retval = str_replace("automatweb/","",$this->mk_my_orb("preview", array("id" => $id),"file", false,true,"/"))."/".urlencode(str_replace("/","_",$name));
//		$retval = $this->mk_my_orb("preview", array("id" => $id),"file", false,true);
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
		if ($url == "")
		{
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
		return aw_ini_get("baseurl").$url;
	}

	////
	// !saves a file that was uploaded in a form to the db
	// $name - the name of the file input in form
	// $parent - the parent object of the file
	// $file_id - if not specified, file will be added, else changed
	function add_upload_image($name,$parent,$file_id = 0)
	{
		$file_id = (int)$file_id;

		$fd = obj();
		if ($file_id)
		{
			$fd = obj($file_id);
		}

		$tmp_name = $_FILES[$name]['tmp_name'];
		if (is_uploaded_file($tmp_name))
		{
			$type = $_FILES[$name]['type'];
			$fname = $_FILES[$name]["name"];

			// if a new file was uploaded, we can forget about the previous one 
			if ($fd->class_id() != CL_FILE)
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

			return array("id" => $id,"url" => $this->get_url($id,$fname), "orig_name" => $fname);
		}
		else
		{
			if ($file_id)
			{
				if ($fd->class_id() != CL_FILE)
				{
					// we gots problems - this is probably an old image file from formgen
					if ($fd->class_id() == CL_IMAGE)
					{
						// let the image class handle this
						$im = get_instance(CL_IMAGE);
						$id = $im->get_image_by_id($file_id);
						return array("id" => $file_id,"url" => $id["url"]);
					}
					// if we get here, we're pretty much fucked, so bail out
					$this->raise_error(ERR_FILE_WRONG_CLASS, "Objekt $file_id on valet tyypi (".$fd->class_id().")",true);
				}
				else
				{
					return array("id" => $file_id,"url" => $this->get_url($file_id, $fd->name()), "orig_name" => $fd->name());
				}
			}
			else
			{
				return false;
			}
		}
	}

	function request_execute($obj)
	{
		return $this->show($obj->id());
	}

	function get_fields($o, $params = array())
	{
		// $o is file object
		// assume it is a csv file
		// parse it and return first row.
		if ($o->prop("file_url") != "")
		{
			$fp = fopen($o->prop("file_url"),"r");
		}
		else
		{
			$fp = fopen($o->prop("file"),"r");
		}
		$delim = ",";
		if ($params["separator"] != "")
		{
			$delim = $params["separator"];
		}
		if ($delim == "/t")
		{
			$delim = "\t";
		}
		$line = fgetcsv($fp, 100000, $delim);
		$ret = array();
		if(is_array($line))
		{
			foreach($line as $idx => $txt)
			{
				$ret[$idx+1] = $txt;
			}
		}

		return $ret;
	}

	function get_objects($o, $params = array())
	{
		$ret = array();
		if ($o->prop("file_url") != "")
		{
			$fp = fopen($o->prop("file_url"),"r");
		}
		else
		{
			$fp = fopen($o->prop("file"),"r");
		}
		$delim = ",";
		if ($params["separator"] != "")
		{
			$delim = $params["separator"];
		}
		if ($delim == "/t")
		{
			$delim = "\t";
		}
		$first = true;
		while ($line = fgetcsv($fp, 100000, $delim))
		{
			if ($first && $params["file_has_header"])
			{
				$first = false;
				continue;
			}
			$first = false;
			$dat = array();
			foreach($line as $idx => $val)
			{
				$dat[$idx+1] = $val;
			}
			$ret[] = $dat;
		}
		return $ret;
	}

	function get_folders($o)
	{
		return $this->get_objects($o);
	}

	// static
	function get_file_size($fn)
	{
		$fn = basename($fn);
		$path = aw_ini_get("site_basedir")."/files/".$fn{0}."/".$fn;
		return @filesize($path);
	}

	/** creates/updates a file object from the arguments
		@attrib api=1
		@param id optional type=int
		@param parent optional type=int
		@param content
		@param name required

	**/
	function create_file_from_string($arr)
	{
		if (isset($arr["id"]))
		{
			$data["id"] = $arr["id"];
		}
		elseif (isset($arr["parent"]))
		{
			$data["parent"] = $arr["parent"];
		}
		else
		{
			error::raise(array(
				"msg" => t("Need either id or parent"),
			));
		};
		$data["return"] = "id";
		$data["file"] = array(
			"content" => $arr["content"],
			"name" => $arr["name"],
		);
		$t = get_instance(CL_FILE);
		$rv = $t->submit($data);
		return $rv;
	}

	/** saves editable fields (given in $ef) to object $id, data is in $data

		@attrib api=1

		
	**/
	function update_object($ef, $id, $data)
	{
		return;
	}

	function _do_virus_scan($file)
	{
		$scanner = get_instance("core/virus_scanner");
		$ret = $scanner->scan_file($file);
		return $ret;
	}


	/** Generate a form for adding or changing an object 
		
		@attrib name=new params=name all_args="1" is_public="1" caption="Lisa"

		@param parent optional type=int acl="add"
		@param period optional
		@param alias_to optional
		@param return_url optional
		@param reltype optional type=int

	**/
	function new_change($args)
	{
		return parent::change($args);
	}
};
?>
