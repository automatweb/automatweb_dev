<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/file.aw,v 2.27 2002/07/02 12:51:14 duke Exp $
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

class file extends aw_template
{
	////
	// !Konstruktor
	function file()
	{
		$this->init("file");
		lc_load("definition");
		$this->lc_load("file","lc_file");
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
			else
			{
				header("Content-type: ".$fi["type"]);
				die($fi["content"]);
			}
		}
		elseif ($fi["meta"]["show_framed"] == 1)
		{
			$replacement = $fi["content"];
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
			
			$url = $this->get_url($alias["target"],$fi["name"]);
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
		$filename = $this->gen_uniq_id();
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
	// !Kuvab faili lisamise vormi
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,LC_FILE_ADD_FILE);
		// kui messenger argument on seatud, siis submit_add peaks tagasi messengeri minema
		if ($msg_id)
		{
			$tpl = "attach.tpl";
		}
		else
		{
			$tpl = ($arr["tpl"]) ? $arr["tpl"] : "upload.tpl";
		};

		$this->read_template($tpl);

		load_vcl("date_edit");
		$de = new date_edit("act_time");
		$de->configure(array(
			"year" => 1,
			"month" => 1,
			"day" => 1,
			"hour" => 1,
			"minute" => 1,
			"classid" => "formselect"
		));

		$this->vars(array(
			"act_date" => $de->gen_edit_form("act_time", time()),
			"j_date" => $de->gen_edit_form("j_time", time()),
			"reforb" => $this->mk_reforb("submit_add", array(
				"id" => $id,
				"msg_id" => $msg_id,
				"parent" => $parent,
				"return_url" => $return_url,
				"user" => $user
			))
		));
		return $this->parse();
	}

	////
	// !Lisab faili lisamisvormist tulnud info pohjal
	function submit_add($arr)
	{
		extract($arr);
		// $file, $file_type ja $file_name on special muutujad,
		// mis tekitatakse php poolt faili uploadimisel
		global $file, $file_type,$file_name;

		if (is_uploaded_file($file))
		{
			// fail sisse
			$fc = $this->get_file(array(
				"file" => $file,
			));

			// fuck this sucks
			if ($msg_id)
			{
				classload("messenger");
				$messenger = new messenger();
				$row = array();
				$row["type"] = $file_type;
				$row["content"] = $fc;
				$row["class_id"] = CL_FILE;
				$row["name"] = $file_name;
				$ser = serialize($row);
				$this->quote($ser);
				$messenger->attach_serialized_object(array(
					"msg_id" => $msg_id,
					"data" => $ser,
				));
			}
			else
			{
				load_vcl("date_edit");
				$de = new date_edit("act_time");

				$pid = $this->save_file(array(
					"parent" => $parent,
					"name" => $file_name,
					"comment" => $comment,
					"content" => $fc,
					"showal" => $show,
					"type" => $file_type,
					"newwindow" => $newwindow,
					"j_time" => $de->get_timestamp($j_time),
					"show_framed" => $show_framed,
				));

				// id on dokumendi ID, kui fail lisatakse doku juurde
				// add_alias teeb voimalikus #fn# tagi kasutamise doku kuvamise juures
				if ($id)
				{
					$this->add_alias($id,$pid);
				}
				$this->_log("fail","Lisas faili $file_name ($pid)");
			};

			// defineerime voimalikud orb-i väärtused siin ära

			// parent on menüü
			// fsck. What If I want to add a file from somewhere else?
			// or any other object? We need a better solution for this.
			$orb_urls = array(
				// saidi seest lisati doku juurde fail
				//"user" => $this->mk_site_orb(array("class" => "document","action" => "change","id" => $id)),
				"user" => $this->mk_my_orb("list_aliases", array("id" => $id), "aliasmgr",false,1),

				// aw-st lisati doku juurde fail
				"awdoc" => $this->mk_my_orb("list_aliases", array("id" => $id), "aliasmgr"),

				// menueditist lisati fail
				"awfile" => $this->mk_my_orb("obj_list", array("parent" => $parent), "menuedit"),

				// messengeri külge attachitud fail
				"messenger" => $this->mk_site_orb(array("class" => "messenger","action" => "edit","id" => $msg_id)),

				// saidi poole pealt uploaditi kodukataloogi fail
				//"site" => $this->mk_site_orb(array("class" => "homedir","action" => "gen_home_dir","id" => $parent)),
				//"site" => $this->mk_site_orb(array("class" => "manager","action" => "browse","id" => $parent)),
				"site" => $this->mk_my_orb("browse",array("parent" => $parent),"manager",false,1),
			);

			if ($return_url != "")
			{
				$retval = $return_url;
			}
			else
			if ($id)
			{
				// $user argument tähendab, et request tuli saidi seest
				// ja vastavalt sellele suuname kliendi ringi
				$retval = ($user) ? $orb_urls["user"] : $orb_urls["awdoc"];
			}
			else
			if (strpos(aw_global_get("REQUEST_URI"),"automatweb") === false)
			{
				$retval = $orb_urls["site"];
			}
			elseif ($msg_id)
			{
				$retval = $orb_urls["messenger"];
			}
			else
			{
				$retval = $orb_urls["awfile"];
			}
		} 
		else 
		{
			// Sellist faili polnud. Voi tekkis mingi teine viga
			print LC_FILE_SOME_IS_WRONG;
			$retval = array();
		};
		return $retval;
	}

	////
	// !Muudab faili
	function change($arr)
	{
		extract($arr);
		$this->read_template("edit.tpl");
		$fi = $this->get_file_by_id($id);
		$this->mk_path($parent, LC_FILE_CHANGE_FILE);

		load_vcl("date_edit");
		$de = new date_edit("act_time");
		$de->configure(array(
			"year" => 1,
			"month" => 1,
			"day" => 1,
			"hour" => 1,
			"minute" => 1,
			"classid" => "formselect"
		));

		$this->vars(array(
			"reforb"	=> $this->mk_reforb("submit_change",array("id" => $id, "parent" => $parent,"doc" => $doc,"user" => $user,"return_url" => $return_url)),
			"act_date" => $de->gen_edit_form("act_time", $fi["meta"]["act_time"]),
			"j_date" => $de->gen_edit_form("j_time", $fi["meta"]["j_time"]),
			"comment" => $fi["comment"],
			"checked" => checked($fi["showal"]), 
			"show_framed" => checked($fi["meta"]["show_framed"]),
			"newwindow" => checked($fi["newwindow"])
		));
		return $this->parse();
	}

	////
	// !Salvestab muudatused
	function submit_change($arr)
	{
		extract($arr);
		global $file, $file_type,$file_name;

		load_vcl("date_edit");
		$de = new date_edit("act_time");

		if (!is_uploaded_file($file)) 
		{
			// uut failinime ei määratud, muudame infot
			$this->save_file(array(
				"file_id" => $id,
				"comment" => $comment,
				"showal" => $show,
				"newwindow" => $newwindow,
				"show_framed" => $show_framed,
				"act_time" => $de->get_timestamp($act_time),
				"j_time" => $de->get_timestamp($j_time)
			));
			$this->_log("fail","Muutis faili $id andmeid");
		}
		else
		{
			$pid = $this->save_file(array(
				"file_id" => $id,
				"name" => $file_name,
				"comment" => $comment,
				"content" => $this->get_file(array("file" => $file)),
				"showal" => $show,
				"type" => $file_type,
				"newwindow" => $newwindow,
				"act_time" => $de->get_timestamp($act_time),
				"j_time" => $de->get_timestamp($j_time),
				"show_framed" => $show_framed,
			));
			$this->_log("fail","Muutis faili $pid");
		}

		// Probleemikoht. Mis siis, kui ma tahan monda teise kohta minna peale submitti?
		$obj = $this->get_object($id);
		$parent = $obj["parent"];
		if ($return_url != "")
		{
			$retval = $return_url;
		}
		else
		if ($doc)
		{
			$retval = $this->mk_my_orb("change", array("id" => $doc),"document");
		}
		else
		{
			if ($GLOBALS["user"])
			{
				//$retval = $this->mk_my_orb("gen_home_dir", array("id" => $parent),"users");
				$retval = $this->mk_my_orb("browse", array("id" => $parent),"manager",false,1);
			}
			else
			{
				//$retval = $this->mk_my_orb("obj_list", array("parent" => $parent),"menuedit");
				$retval = $this->mk_my_orb("change",array("id" => $id));
			}
		};
		return $retval;
	}

	////
	// !returns file by id
	function get_file_by_id($id) 
	{
		$row = $this->get_object($id);
		$this->db_query("SELECT * FROM files WHERE id = $id");
		$ret = $row + $this->db_next();

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
		return $this->mk_my_orb("preview", array("id" => $id),"file", false,true,"/")."/".urlencode($name);
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

			return array("id" => $id,"url" => $this->get_url($id,$fname));
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
						classload("image");
						$im = new image;
						$id = $im->get_image_by_id($file_id);
						return array("id" => $file_id,"url" => $id["url"]);
					}
					// if we get here, we're pretty much fucked, so bail out
					$this->raise_error(ERR_FILE_WRONG_CLASS, "Objekt $file_id on valet tyypi ($fd[class_id])",true);
				}
				else
				{
					return array("id" => $file_id,"url" => $this->get_url($file_id, $fd["name"]));
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
