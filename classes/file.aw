<?php
// file.aw - Failide haldus
global $orb_defs;
$orb_defs["file"] = "xml";
class file extends aw_template
{
	////
	// !Konstruktor
	function file()
	{
		$this->tpl_init("file");
		$this->db_init();
	}
	////

	////
	// !Kuvab faili lisamise vormi
	function add($arr)
	{
		extract($arr);
		$this->mk_path($parent,"Lisa fail");
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
			print "Midagi on valesti. Faili ei salvestatud";
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
		$this->mk_path($parent, "Muuda faili");
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
		if ($doc || $user)
		{
			$retval = $this->mk_orb("change", array("id" => $doc),"document",$user);
		}
		else
		{
			$retval = "menuedit.".$GLOBALS["ext"]."?type=objects&parent=".$parent;
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
