<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/file.aw,v 2.37 2003/01/10 12:31:54 kristo Exp $
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
	@default table=files
	@default group=general

	@property file type=fileupload 
	@caption Vali fail

	@property comment type=textbox table=objects field=comment
	@caption Faili allkiri

	@property showal type=checkbox ch_value=1
	@caption Näita kohe

	@property newwindow type=checkbox ch_value=1
	@caption Uues aknas

	@default table=objects
	@default field=meta
	@default method=serialize
	
	@property show_framed type=checkbox ch_value=1
	@caption Näita saidi raamis

	@property view type=text editonly=1
	@caption Näita faili

	@property j_time type=date_select 
	@caption Jõustumise kuupäev

	@property act_date type=date_select
	@caption Avaldamise kuupäev

	@classinfo objtable=files
	@classinfo objtable_index=id

	@groupinfo general caption=Üldine default=1
        @groupinfo dates caption=Ajad

	@tableinfo files index=id master_table=objects master_index=oid	

	// miski is_aip globaases skoobis on faili salvestamisel oluline

*/


class file extends class_base
{
	////
	// !Konstruktor
	function file()
	{
		$this->init(array(
			"clid" => CL_FILE,
			"tpldir" => "file",
		));
		lc_load("definition");
		$this->lc_load("file","lc_file");
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "view":
				$data["value"] = html::href(array(
					"url" => $this->mk_my_orb("preview",array("id" => $args["obj"]["oid"])),
					"caption" => "Näita",
					"target" => "_blank",
				));
				break;

			case "file":
				$data["value"] = "";
				break;

		}
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form_data = &$args["form_data"];
		global $file, $file_type,$file_name;
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
				};
		
				/*	
				if ($is_aip)
				{
					$this->db_query("INSERT INTO aip_files (filename, tm, menu_id, id) VALUES('$file_name','".time()."','".$parent."','$pid')");
				}
				*/
			};
		};
		// cause everything is alreay handled here
		return PROP_OK;
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

		$fi = $this->get_file_by_id($alias["target"]);
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
    
				$replacement = $fi["content"];
			}
			// embed xml files
			elseif ($fi["type"] == "text/xml")
			{
				$replacement = htmlspecialchars($fi["content"]);
				$replacement = str_replace("\n","<br>\n",$replacement);
				// tabs
				$replacement = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$replacement);
			}
			else
			{
				header("Content-type: ".$fi["type"]);
				die($fi["content"]);
			}
		}
		else
		{
			if ($fi["newwindow"])
			{
				$ss = "target=\"_new\"";
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
		return $replacement;
	}

	////
	// !Salvestab faili failisysteemi. For internal use, s.t. kutsutakse välja save_file seest
	// returns the name of the file that the data was saved in
	function _put_fs($args = array())
	{
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
		return $file;
	}

	////
	// !Checks whether a record in the files table is an image (can be embedded inside the web page)
	// $args should contain line from that table
	function can_be_embedded(&$row)
	{
		 return in_array($row["type"],$this->cfg["embtypes"]);
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
	// !checks whether the directory needed for file storing exists and is writable
	function check_environment($args = array())
	{
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
					$retval .= "Kataloog $dir puudub<br>";
				}
				elseif (!is_dir($dir))
				{
					$retval .= "$dir ei ole kataloog<br>";
				}
				elseif (!is_writable($dir))
				{
					$retval .= "$dir ei ole kirjutatav<br>";
				};
			};
		};
		return $retval;
	}

	////
	// !returns file by id
	function get_file_by_id($id) 
	{
		$row = new aw_array($this->get_object($id));
		$this->db_query("SELECT * FROM files WHERE id = $id");
		$ar = new aw_array($this->db_next());
		$ret = $row->get() + $ar->get();

		$ret["file"] = basename($ret["file"]);

		if ($ret["file"] != "")
		{
			// file saved in filesystem - fetch it
			$file = $this->cfg["site_basedir"]."/files/".$ret["file"][0]."/".$ret["file"];
			$ret["content"] = $this->get_file(array("file" => $file));
		}
		return $ret;
	}

	////
	// !Näitab faili. DUH.
	function show($id)
	{
		if (is_array($id))
		{
			extract($id);
		}
		// allow only integer id-s
		$id = (int)$id;
		$fc = $this->get_file_by_id($id);

		header("Content-type: ".$fc["type"]);
		header("Pragma: no-cache");
		die($fc["content"]);
	}

	function get_url($id,$name)	
	{
		$retval = $this->mk_my_orb("preview", array("id" => $id),"file", false,true,"/")."/".urlencode($name);
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
				$filename = substr($url,strrpos($url,"/"));
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
						return array("id" => $file_id,"url" => $id["url"]);
					}
					// if we get here, we're pretty much fucked, so bail out
					$this->raise_error(ERR_FILE_WRONG_CLASS, "Objekt $file_id on valet tyypi ($fd[class_id])",true);
				}
				else
				{
					return array("id" => $file_id,"url" => $this->get_url($file_id, $fd["name"]), "orig_name" => $fd["name"]);
				}
			}
			else
			{
				return false;
			}
		}
	}
};
?>
