<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/file.aw,v 2.13 2001/08/03 03:18:27 duke Exp $
// file.aw - Failide haldus
global $orb_defs;
$orb_defs["file"] = "xml";

lc_load("file");
class file extends aw_template
{
	////
	// !Konstruktor
	function file()
	{
		$this->tpl_init("file");
		$this->db_init();
		lc_load("definition");
	global $lc_file;
		{if (is_array($lc_file))
		
			$this->vars($lc_file);
		}
	}

	////
	// !Aliaste parsimine
	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->filealiases))
		{
			$this->filealiases = $this->get_aliases(array(
								"oid" => $oid,
								"type" => CL_FILE,
							));
		};
		$f = $this->filealiases[$matches[3] - 1];
		if (!$f["target"])
		{
			return "";
		}

		$fi = $this->get_file_by_id($f["target"]);
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
                		
				preg_match("/<body (.*)>(.*)<\/body>/imsU",$fi["content"],$map);
				// return only the body of the file
                		$replacement = str_replace("\n","",$map[2]);
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
			
			$replacement = "<a $ss class=\"sisutekst\" href='".$GLOBALS["baseurl"]."/files.aw/id=".$f["target"]."/".urlencode($fi[name])."'>$comment</a>";
		}
		return $replacement;
	}
	
	////
	// !Selle funktsiooni abil salvestatakse fail systeemi sisse,
	// soltuvalt parameetrist store väärtusest
	// argumendid:
	// store(string) - kuhu fail salvestada ("db" || "file")
	// filename(string) - faili nimi
	// type(string) - faili tyyp (MIME)
	// content(string) - faili sisu
	function put($args = array())
	{
		extract($args);
		$retval = "";
		if ($store == "fs")
		{
			$fname = basename($this->_put_fs($args));
			$oid = $this->new_object(array(
				"parent" => $parent,
				"name" => $filename,
				"class_id" => CL_FILE,
				"comment" => $comment,
			));
			$q = "INSERT INTO files (id,type,file) VALUES('$oid','$type','$fname')";
			$this->db_query($q);
		};
		return $retval;

	}

	////
	// !Salvestab faili failisysteemi. For internal use, s.t. kutsutakse välja put-i seest
	// returns the name of the file that the data was saved in
	function _put_fs($args = array())
	{
		// find the extension for the file
		list($major,$minor) = explode("/",$args["type"]);

		// first, we need to find a path to put the file
		$filename = $this->gen_uniq_id();
		$prefix = substr($filename,0,1);
		$file = SITE_DIR . "/files/" . $prefix . "/" . "$filename.$minor";
		$this->put_file(array(
					"file" => $file,
					"content" => $args["content"],
			));
		return $file;
	}

	////
	// !Salvestab special faili, ehk siis otse files kataloogi
	// argumendid:
	// name(string) - faili nimi
	// data(string) - faili sisu
	// sys(bool) - kas panna faili systeemi juurde?
	function put_special_file($args = array())
	{
		if ($args["sys"])
		{
			$path = AW_PATH . "/files";
		}
		else
		{
			$path = SITE_DIR . "/files";
		};

		$success =$this->put_file(array(
			"file" => $path . "/" . $args["name"],
			"content" => $args["content"],
		));

		return $success;
	}

	////
	// !Votab faili andmebaasist ja näitab seda kasutajale
	// argumendid:
	// id(int) - faili id
	function get($args = array())
	{
		extract($args);
		$q = "SELECT *,objects.name AS oname FROM files LEFT JOIN objects ON (files.id = objects.oid) WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$fname = $row["file"];
		$prefix = substr($fname,0,1);
		$file = SITE_DIR . "/files/" . $prefix . "/" . "$fname";
		$contents = $this->get_file(array(
						"file" => $file,
			));
		if (!$contents)
		{
			$contents = $row["content"];
		};
		$retval = array(
				"name" => $row["oname"],
				"type" => $row["type"],
				"file" => $contents,
			);
		return $retval;
	}

	////
	// !Teeb failiobjekist koopia uue parenti alla
	// argumendid:
	// id - faili id, millest koopia teha
	// parent - koht, mille alla koopia teha
	function cp($args = array())
	{
		extract($args);
		$old = $this->get(array("id" => $id));
		$this->put(array(
				"store" => "fs",
				"parent" => $parent,
				"filename" => $old["name"],
				"type" => $old["type"],
				"content" => $old["file"],
		));
		// well, we should be done by now
				
	}

	////
	// !checks whether the directory needed for file storing exists and is writable
	function check_environment($args = array())
	{
		// um, yeah, what is this doing here? - terryf 
/*		$this->db_list_tables();
		while($name = $this->db_next_table())
		{
			print "name = $name<br>";
			$q = "SELECT * FROM $name LIMIT 1";
			$this->db_query($q);
			$fields = $this->db_get_fields();
			print "<pre>";
			print_r($fields);
			print "</pre>";
		};*/

		$retval = "";
		if (!defined("SITE_DIR"))
		{
			$retval .= LC_FILE_DIR_NOT_DEFINED;
		}
		else
		{
			$dir = SITE_DIR . "/files";
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

		$this->vars(array("reforb" => $this->mk_reforb("submit_add", array(
								"id" => $id,
								"msg_id" => $msg_id,
								"parent" => $parent,
								"user" => $user))));
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


		// "none" on ka miski special väärtus, ehk kasutaja ei valinud faili
		if ($file != "none")
		{
			// fail sisse
			$fc = $this->get_file(array(
				"file" => $file,
			));

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
				$pid = $this->new_object(array(
					"parent" => $parent,
					"name" => $file_name,
					"class_id" => CL_FILE,
					"comment" => $comment,
				));

				// id on dokumendi ID, kui fail lisatakse doku juurde
				// add_alias teeb voimalikus #fn# tagi kasutamise doku kuvamise juures
				if ($id)
				{
					$this->add_alias($id,$pid);
				}


				// See kahekordne kvootimine oli millegipärast vajalik, aga vat hetkel ma ma küll
				// ei mäleta milleks täpselt

				$this->quote(&$fc);
				$this->quote(&$fc);

				// Ja see mulle ka ei meeldi, et igast binary räga mysql-i baasis salvestatakse
				// Ühest poolest on see suhteliselt clean lahendus, samas - baasis voiks ikka ollagi
				// ainult data, millega mingeid operatsioone tehakse - otsimine, sorteerimine.

				$this->db_query("INSERT INTO files (id,showal,type,content,newwindow)
							VALUES('$pid','$show','$file_type','$fc','$newwindow')");

				$this->_log("fail","Lisas faili $file_name ($pid)");
			};

			// defineerime voimalikud orb-i väärtused siin ära

			// parent on menüü
			$orb_urls = array(
				// saidi seest lisati doku juurde fail
				"user" => $this->mk_site_orb(array("class" => "document","action" => "change","id" => $id)),

				// aw-st lisati doku juurde fail
				"awdoc" => $this->mk_orb("change", array("id" => $id), "document",$user),

				// menueditist lisati fail
				"awfile" => $this->mk_orb("obj_list", array("parent" => $parent), "menuedit",$user),

				// messengeri külge attachitud fail
				"messenger" => $this->mk_site_orb(array("class" => "messenger","action" => "edit","id" => $msg_id)),
			);

			if ($id)
			{
				// $user argument tähendab, et request tuli saidi seest
				// ja vastavalt sellele suuname kliendi ringi
				$retval = ($user) ? $orb_urls["user"] : $orb_urls["awdoc"];
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
	// !Kustutab faili
	function delete($arr)
	{
		extract($arr);
		$this->delete_object($id);
		$this->db_query("DELETE FROM aliases WHERE target = $id");
		header("Location: ".$this->mk_orb("obj_list", array("parent" => $parent),"menuedit"));
	}

	////
	// !Muudab faili
	function change($arr)
	{
		extract($arr);
		global $doc;

		$this->read_template("edit.tpl");
		$fi = $this->get_file_by_id($id);
		$this->mk_path($parent, LC_FILE_CHANGE_FILE);
		$this->vars(array("reforb"	=> $this->mk_reforb("submit_change",array("id" => $id, "parent" => $parent,"doc" => $doc,"user" => $user)),"comment" => $fi[comment],"checked" => $fi[showal] ? "CHECKED" : "", "newwindow" => checked($fi[newwindow])));
		return $this->parse();
	}

	////
	// !Salvestab muudatused
	function submit_change($arr)
	{

		// <terryf> failide tabelis on v2li
		// <terryf> showal
		// <terryf> mis n2itab et kas faili n2datakse kohe v6i ei
		// <terryf> ja siis kui showal=1
		// <terryf> siis ocitaxe selle faili seest kui ta on m6ne dokumendi juurde aliasex pandud

		extract($arr);
		global $file, $file_type,$file_name;

		if ($file == "none")
		{
			// uut failinime ei määratud, muudame infot
			$this->upd_object(array(
				"oid" => $id,
				"comment" => $comment
			));
			$this->db_query("UPDATE files
						SET showal = '$show',
						 newwindow = '$newwindow'
					 WHERE id = $id");
			$this->_log("fail","Muutis faili $id andmeid");
		}
		else
		{
			$pid = $this->upd_object(array(
				"oid" => $id,
				"name" => $file_name,
				"comment" => $comment
			));
			$f = fopen($file,"r");
			$fc = fread($f,filesize($file));
			fclose($f);
			$this->quote(&$fc);
			$this->quote(&$fc);

			$this->db_query("UPDATE files
						SET 	showal = '$show',
						 	type = '$file_type',
							content = '$fc',
							newwindow = '$newwindow'
					WHERE id = $id");
			$this->_log("fail","Muutis faili $id");
		}

		// Probleemikoht. Mis siis, kui ma tahan monda teise kohta minna peale submitti?
		if ($doc)
		{
			$retval = $this->mk_my_orb("change", array("id" => $doc),"document");
		}
		else
		{
			if ($GLOBALS["user"])
			{
				$retval = $this->mk_my_orb("gen_home_dir", array("id" => $parent),"users");
			}
			else
			{
				$retval = "menuedit.".$GLOBALS["ext"]."?type=objects&parent=".$parent;
			}
		};
		return $retval;
	}

	////
	// !Fail id järgi. Kahtlane värk
	function get_file_by_id($id) 
	{
		$this->db_query("SELECT * FROM objects WHERE oid = $id");
		$row = $this->db_next();
		$this->db_query("SELECT * FROM files WHERE id = $id");
		return array_merge($row,$this->db_next());
	}

	////
	// !Näitab faili. DUH.
	function show($id)
	{
		if (is_array($id))
		{
			extract($id);
		}
		$this->db_query("SELECT files.* FROM files WHERE id = $id");
		$o = $this->db_next();
		if ($o)
		{
			header("Content-type: ".$o[type]);
			return $o[content];
		}
	}

	////
	// !Näitab faili arrayst tulevate andmete pohjal
	function show2($args)
	{
		$type = ($args["type"]) ? $args["type"] : $args["file_type"];
		header("Content-type: ".$type);
		print $args["content"];
		// I know, I know, this is bad.
		exit;
	}
};
?>
