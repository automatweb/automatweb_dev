<?php

classload("file","objects");

class image extends aw_template
{
	function image()
	{
		$this->db_init();
		$this->tpl_init("automatweb/images");
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		if ($return_url)
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa pilt");
		}
		else
		{
			$this->mk_path($parent,"Lisa pilt");
		}

		$ob = new objects;
		$this->vars(array(
			"parents" => $this->picker($parent, $ob->get_list()),
			"reforb" => $this->mk_reforb("submit", array("parent" => $parent,"return_url" => $return_url,"alias_to" => $alias_to))
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		$_fi = new file;
		if ($id)
		{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"parent" => $parent
			));

			global $file,$file_name,$file_type;
			if ($file != "" && $file != "none")
			{
				if (is_uploaded_file($file))
				{
					$f = fopen($file,"r");
					$fc = fread($f,filesize($file));
					fclose($f);
					$fl = $_fi->_put_fs(array("type" => $file_type, "content" => $fc));

					$this->db_query("UPDATE images SET file = '$fl' WHERE id = $id");
				}
			}
		}
		else
		{
			global $file,$file_name,$file_type;

			if ($name == "")
			{
				$name = $file_name;
			}

			$id = $this->new_object(array(
				"parent" => $parent,
				"class_id" => CL_IMAGE,
				"status" => 2,
				"name" => $name,
				"comment" => $comment
			));

			if ($file != "" && $file != "none")
			{
				if (is_uploaded_file($file))
				{
					$f = fopen($file,"r");
					$fc = fread($f,filesize($file));
					fclose($f);
					$fl = $_fi->_put_fs(array("type" => $file_type, "content" => $fc));

					$this->db_query("INSERT INTO images(id,file) VALUES($id,'$fl')");
				}
			}
		}

		if ($alias_to)
		{
			$this->delete_alias($alias_to,$id);
			$this->add_alias($alias_to,$id);
		}

		$this->db_query("UPDATE images SET link = '$link' , newwindow = '$newwindow' WHERE id = $id");
		return $this->mk_my_orb("change", array("id" => $id,"return_url" => urlencode($return_url),"alias_to" => $alias_to));
	}

	function change($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		
		$obj = $this->get_object($id);
		if ($return_url)
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda pilti");
		}
		else
		{
			$this->mk_path($obj["parent"],"Muuda pilti");
		}
	
		$img = $this->get_image_by_id($id);

		if ($this->is_flash($img["file"]))
		{
			$ima = "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=4,0,2,0\" width=\"165\" height=\"75\" hspace=\"0\" vspace=\"0\" border=\"0\" align=\"absmiddle\"><param name=movie value=\"".$img["url"]."\"><param name=quality value=high><param name=\"BGCOLOR\" value=\"#336600\"><embed width=\"150\" height=\"150\" hspace=\"0\" vspace=\"0\" border=\"0\" align=\"absmiddle\" quality=\"high\" pluginspage=\"http://www.macromedia.com/shockwave/download/\" src=\"".$img["url"]."\" bgcolor=\"#336600\"></embed></object>";
		}
		else
		{
			$ima = "<img src='".$img["url"]."'>";
		}

		$ob = new objects;
		$this->vars(array(
			"parents" => $this->picker($obj["parent"],$ob->get_list()),
			"name" => $obj["name"],
			"comment" => $obj["comment"],
			"img" => $ima,
			"link" => $img["link"],
			"newwindow" => checked($img["newwindow"]==1),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "return_url" => $return_url,"alias_to" => $alias_to))
		));
		return $this->parse();
	}

	function get_image_by_id($id)
	{
		$q = "SELECT objects.*,images.* FROM images
			LEFT JOIN objects ON (objects.oid = images.id)
			WHERE images.id = '$id'";
		$this->db_query($q);
		$row = $this->db_fetch_row();
		$row["url"] = $this->get_url($row["file"]);
		return $row;
	}

	function get_url($url) 
	{
		global $ext;
//		$url = $this->mk_my_orb("show", array("file" => basename($url)),"image",false,true);
		$url = $GLOBALS["baseurl"]."/img.".$GLOBALS["ext"]."?file=".basename($url);
		return $url;
	}

	///
	// !Kasutatakse ntx dokumendi sees olevate aliaste asendamiseks. Kutsutakse välja callbackina
	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->imagealiases) || $this->imagealiasoid != $oid)
		{
			$this->imagealiases = $this->get_aliases(array(
								"oid" => $oid,
								"type" => CL_IMAGE,
							));
			$this->imagealiasoid = $oid;
		};
		$f = $this->imagealiases[$matches[3] - 1];
		if (!$f["target"])
		{
			// now try and list images by the old way 
			$idata = $this->get_img_by_oid($oid,$matches[3]);
			if (!is_array($idata))
			{
				return "";
			}
		}
		else
		{
			$idata = $this->get_image_by_id($f["target"]);
		}		

		$replacement = "";
		$align= array("k" => "align=\"center\"", "p" => "align=\"right\"" , "v" => "align=\"left\"" ,"" => "");
		if ($idata)
		{
			$vars = array(
				"imgref" => $idata["url"],
				"imgcaption" => $idata["comment"],
				"align" => $align[$matches[4]],
				"plink" => $idata["link"],
				"target" => ($idata["newwindow"] ? "target=\"_blank\"" : "")
			);
 
			if ($this->is_flash($idata["file"]))
			{
				$replacement = $this->localparse($tpls["image_flash"],$vars);
			}
			else
			if ($idata["link"] != "")
			{
				$replacement = $this->localparse($tpls["image_linked"],$vars);
			}
			else
			{
				if ($tpls["image_inplace"] && !$this->image_inplace_used)
				{
					$tpl = "image_inplace";
					$inplace = $tpl;
					$this->image_inplace_used = true;
				}
				else
				{
					$tpl = "image";
					$inplace = 0;
				};
				if (isset($tpls[$tpl]))
				{
					$replacement = $this->localparse($tpls[$tpl],$vars);
				}
				else
				{
					$replacement = sprintf("<img src='%s'><br>%s",$idata["url"],$idata["comment"]);
				};
			}	
		};
		$retval = array(
				"replacement" => $replacement,
				"inplace" => $inplace,
		);
		return $retval;
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

	function is_flash($file)
	{
		$pos = strrpos($file,".");
		$ext = substr($file,$pos);
		if ($ext == ".x-shockwave-flash")
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>
