<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/images.aw,v 2.7 2001/06/18 20:19:58 kristo Exp $
// klass piltide manageerimiseks
global $orb_defs;
$orb_defs["images"] = array("new"						=> array("function"	=> "add",		"params"	=> array("parent")),
														"submit"				=> array("function"	=> "submit","params"	=> array("parent")),
														"submit_change"	=> array("function"	=> "submit_change","params"	=> array("id","parent","idx")),
														"change"				=> array("function"	=> "change","params"	=> array("id")),
														"delete"				=> array("function"	=> "delete","params"	=> array("id"), "opt" =>array("parent","docid"))
														);


// wrapper, et saax asja orbiga kasutada
// why the flying fuck do we have 2 classes in here? What the fuck is going on?
class images extends aw_template
{
	function images()
	{
		$this->di = new db_images;
	}

	///
	// !Kasutatakse ntx dokumendi sees olevate aliaste asendamiseks. Kutsutakse välja callbackina
	function parse_alias($args = array())
	{
		extract($args);
		$idata = $this->di->get_img_by_oid($oid,$matches[3]);
		$replacement = "";
		$align= array("k" => "align=\"center\"", "p" => "align=\"right\"" , "v" => "align=\"left\"" ,"" => "");
		if ($idata)
		{
			$vars = array(
					"imgref" => $idata["url"],
					"imgcaption" => $idata["comment"],
					"align" => $align[$matches[4]],
					"plink" => $idata["link"],
			);
 
			if ($idata["link"] != "")
			{
				$replacement = $this->localparse($tpls["image_linked"],$vars);
			}
			else
			{
				$replacement = $this->localparse($tpls["image"],$vars);
			}	
		};
		return $replacement;
	}

	function add($arr)
	{
		$this->di->mk_path($arr["parent"], "Lisa pilt");
		$this->di->read_template("nupload.tpl");
		$this->di->vars(array("reforb" => $this->mk_reforb("submit", array("parent" => $arr["parent"]))));
		return $this->di->parse();
	}

	function submit($arr)
	{	
		global $pilt, $pilt_type,$comment;
		$ar = $this->di->_upload(array("filename" => $pilt, "file_type" => $pilt_type, "oid" => $arr["parent"], "descript" => $comment,"link" => $arr["link"], "newwindow" => $arr["newwindow"]));
		return $this->mk_my_orb("change", array("id" => $arr["parent"]),"document");
	}

	function change($arr)
	{
		extract($arr);
		$this->di->read_template("nedit.tpl");
		$pic = $this->di->get_img_by_id($id);
		$this->di->vars(array(
			"comment" => $pic["comment"],
			"link" => $pic["link"],
			"url"	=> $pic["url"],
			"newwindow"	=> checked($pic["newwindow"]),
			"reforb" => $this->mk_reforb("submit_change", array("id" => $pic["id"], "parent" => $pic["parent"],"idx" => $pic["idx"]))
		));
		return $this->di->parse();
	}

	function submit_change($arr)
	{
		global $pilt, $pilt_type,$comment;
		$ar = $this->di->_replace(array("filename" => $pilt, "file_type" => $pilt_type, "oid" => $arr["parent"], "comment" => $comment,"poid" => $arr["id"],"idx" => $arr["idx"],"link" => $arr["link"], "newwindow" => $arr["newwindow"]));
	
		return $this->mk_my_orb("change", array("id" => $arr["parent"]),"document");
	}

	function delete($arr)
	{
		extract($arr);
		$this->di->delete_object($id);
		if ($parent)
		{
			header("Location: menuedit.".$GLOBALS["ext"]."?parent=$parent&type=objects");
		}
		else
		if ($docid)
		{
			header("Location: ".$this->mk_my_orb("change", array("id" => $docid),"document"));
		}
	}
}

class db_images extends aw_template 
{
	var $imgdir;
	var $imgurl;
	var $itypes;

	function db_images() 
	{
		global $basedir;
		global $baseurl,$site_basedir;
		$this->imgdir = $site_basedir . "/img";	// we put images here,but some older images are 
		$this->imgdir2 = $basedir . "/img";			// in here so if we can't find them from the prev one, we look here too
		$this->imgurl = $baseurl . "/img";			
		// lubatud failitüüpide nimekiri
		$this->itypes = array("jpg" => "image/jpeg",
		                      "gif"  => "image/gif",
				      "jpg"  => "image/jpg",
				      "jpg"  => "image/pjpeg");
		$this->db_init();
		$this->tpl_init("automatweb/images");
		$this->proc_parent=-1;
	}

	// pildid on kõik objektide tabelis registeeritud, 
	// käime siis kõik objekti lapsed läbi ja võtame
	// pildid  ning joinime neile külge data piltide tabelist
	function list_by_object($oid,$period = 0,$sortby = "",$sortorder = "") 
	{
		if ($period > 0) 
		{
			$sufix = "AND objects.period = '$period'";
		} 
		else 
		{
			$sufix = "";
		};
		if ($sortby == "")
		{
			$sortby = "idx";
		}
		$q = "SELECT objects.*,images.*
			FROM objects
			LEFT JOIN images ON (objects.oid = images.id)
			WHERE parent = '$oid' AND class_id = '6' AND status = 2 $sufix 
			ORDER BY $sortby $sortorder";
		$this->db_query($q);
	}

	// automatweb/images.aw kasutab seda muudetava pildi kohta
	// info saamiseks
	function get_img_by_id($id) 
	{
		$q = "SELECT objects.*,images.* FROM images
			LEFT JOIN objects ON (objects.oid = images.id)
			WHERE images.id = '$id'";
		$this->db_query($q);
		$row = $this->db_fetch_row();
		$row["url"] = $this->get_url($row["file"]);
		return $row;
	}

	function get_img_by_oid($oid,$idx) 
	{
		$q = "SELECT images.*,objects.* FROM objects
			LEFT JOIN images ON objects.oid = images.id
			WHERE parent = '$oid' AND idx = '$idx' AND objects.status = 2 AND objects.class_id = 6
			ORDER BY created DESC";
		$this->db_query($q);
		$row = $this->db_next();
		$row["url"] = $this->get_url($row["file"]);
		return $row;
	}

	// kontrollib katalooma olemazolu ja kirjutatavust
	function check_dir($dir) 
	{
		$d = $this->imgdir . "/$dir";
		return file_exists($d) && is_writeable($d) && is_dir($d);
	}

	// tekitab katalooma
	function create_dir($dir) 
	{
		$d = $this->imgdir . "/$dir";
		mkdir($d,0777);
	}

	function get_url($url) 
	{
		global $ext;
		$first = substr($url,0,1);
		$url = $GLOBALS["baseurl"]."/img.$ext?file="."$url";
		return $url;
	}

	// kontrollib, kas pakutava pildi mimetüüp on lubatud tüüpide nimekirjas	
	function is_valid_image($type) 
	{
		$valid = 0;
		while(list(,$v) = each($this->itypes)) 
		{
			if ($type == $v) 
			{
				$valid = 1;
			};
		};
		return $valid;
	}

	function get_extension($type) 
	{
		reset($this->itypes);
		while(list($k,$v) = each($this->itypes)) 
		{
			if ($type == $v) 
			{
				$retval = $k;
			};
		};
		return $retval;
	}
	
	function replace($filename,$file_type,$oid,$idx,$descript,$poid, $ignore_type = false, $file_oname = "") 
	{
		$params["filename"] = $filename;
		$params["file_type"] = $file_type;
		$params["oid"] = $oid;
		$params["idx"] = $idx;
		$params["descript"] = $descript;
		$params["poid"] = $poid;
		$params["ignore_type"] = $ignore_type;
		$params["file_oname"] = $file_oname;
		return $this->replace($params);
	}
	
	function _replace($params) 
	{
		extract($params);
		// kontrollime failitüüpi
		if ($this->is_valid_image($file_type)) 
		{
			list(,$ext) = split("/",$file_type);
			// toome kohale vana pildi
			if (!$idx) 
			{
				$idx = 0;
			};
			$old = $this->get_img_by_id($poid);
			// mõtleme välja katalooma, kus vana pilti hoiti
			$start = substr($old["file"],0,1);
			// kui vana pilt ikka existeerib, siis märgime tolle kustutatuks
			if (file_exists($this->imgdir."/$start/$old[file]"))  
			{
				$this->delete_object($old["id"]);
			}
			else
			if (file_exists($this->imgdir2."/$start/$old[file]"))  
			{
				$this->delete_object($old["id"]);
			};
			$fname = $this->gen_uniq_id();
			$start = substr($fname,0,1);
			if (!$this->check_dir($start)) 
			{
				// seda pole, teeme siis
				$this->create_dir($start);
			};
			// leiame faili nime, millesse kirjutada
			$target = $this->imgdir."/$start/$fname.$ext";
			// kopeerime faili
			if (copy($filename,$target)) 
			{
				chmod( $target, 0777 );
				if ($name) 
				{
					$pname = $name;
				} 
				else 
				{
					$pname = "pilt$idx";
				};
				if ($period) 
				{
					$this->period = $period;
				};
				$p_oid = $this->new_object(array("parent" => $oid,"name" => $pname,"class_id" => 6,"comment" => $comment));
				global $link;
				$this->db_query("INSERT INTO images(id,file,idx,link,newwindow) VALUES($p_oid, '$fname.$ext' , '$idx','$link','$newwindow')");
				$this->_log("image","Muutis pilti $p_oid");
				return array("id" => $p_oid, "idx" => $idx);
			} 
			else 
			{
				print "Midagi on valesti. Pilti ei salvestatud";
			};
		} 
		else 
		{
			// failitüüp ei sobinud või uut faili polnudki,
			// uuendame ainult kirjeldust
			$this->upd_object(array("oid" => $poid,"comment" => $comment));
			$this->db_query("UPDATE images SET link = '$link',newwindow='$newwindow' WHERE id = $poid");
		};
	}

		
	function upload($filename,$file_type,$oid,$descript, $ignore_type = false, $file_oname = "", $period = 0) 
	{
		// kontrollime failitüüpi
		$params["filename"] = $filename;
		$params["file_type"] = $file_type;
		$params["oid"] = $oid;
		$params["descript"] = $descript;
		$params["ignore_type"] = $ignore_type;
		$params["file_oname"] = $file_oname;
		$params["period"] = $period;
		return $this->_upload($params);
	}
	
	function _upload($params) 
	{
		$filename 	= $params["filename"];
		$file_type 	= $params["file_type"];
		$oid 		= $params["oid"];
		$link 		= $params["link"];
		$descript 	= $params["descript"];
		$ignore_type 	= ($params["ignore_type"]) ? $params["ignore_type"] : true;
		$file_oname 	= ($params["file_oname"]) ? $params["file_oname"] : $params["filename"];
		$period 	= $params["period"];
		$name 		= $params["name"];
		$set_period = $GLOBALS["set_period"];
		$newwindow = $params["newwindow"];
		
		if (!($this->is_valid_image($file_type) || $ignore_type)) 
		{
			print "See failitüüp ei sobi mulle";
			die;
		};
		// leiame faili laiendi
		$ext = $this->get_extension($file_type);
		// tirime kohale info objektide indeksite kohta
		$olast = $this->get_last($oid);
		// arvutame uue pildi jaox indexi
		$idx = $olast["image"]; 
		if (!$idx) 
		{ 
			$idx = 0; 
		};
		$idx++;
		// genereerime unikaalse nime faili jaoks
		$fname = $this->gen_uniq_id();
		// võtame sellest nimest esimese tähe ja teeme sellenimelise kataloogi
		// idee on selles, et vähendada natuke failisüsteemi koormust ja jagada
		// pildid mitmesse kataloogi
		$start = substr($fname,0,1);
		// kontrollime kataloogi olemasolu
		if (!$this->check_dir($start)) 
		{
			// seda pole, teeme siis
			$this->create_dir($start);
		};
		// leiame faili nime, millesse kirjutada
		$target = $this->imgdir . "/$start/$fname.$ext";
		// kopeerime faili
		if (copy($filename,$target)) 
		{
			// kopeerimine õnnestus
			chmod($target, 0777);
			// registreerimine pildi objektitabelis
			if ($name) 
			{
				$pname = $name;
			} 
			else 
			{
				$pname = "pilt$idx";
			};
			if ($period) 
			{
				$this->period = $period;
			};
			$pp = $this->get_object($oid);
			$pid = $this->new_object(array(
				"parent" => $oid,
				"name" => $pname,
				"class_id" => 6,
				"comment" => "$descript",
				"period" => ($set_period == 1 ? $pp["period"] : $period)
			));
			// ja paigutame info piltide tabelisse
			$this->db_query("INSERT INTO images (id,file,idx,link,newwindow) VALUES('$pid','$fname.$ext','$idx','$link','$newwindow')");
			// paneme paika viimase pildi indexi
			$this->set_last($oid,"image",$idx);
			$this->_log("image","Lisas pildi $pid");
			return array("id" => $pid, "idx" => $idx);
		} 
		else 
		{
			print "Midagi on valesti. Pilti ei salvestatud";
			return array();
		};
	}
};
?>
