<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/image.aw,v 2.44 2003/03/13 13:46:29 kristo Exp $
// image.aw - image management
/*
	@default group=general

	@property file type=fileupload table=images
	@caption Pilt

	@property file_show type=text store=no
	@caption Eelvaade 

	@property file2 type=fileupload group=img2 table=objects field=meta method=serialize 
	@caption Suur pilt

	@property file_show2 type=text group=img2 store=no
	@caption Eelvaade

	@property comment table=objects field=comment type=textbox
	@caption Pildi allkiri

	@property alt type=textbox table=objects field=meta method=serialize
	@caption Alt

	@property link type=textbox table=images field=link
	@caption Link

	@property newwindow type=checkbox ch_value=1 table=images field=newwindow
	@caption Uues aknas

	@groupinfo img2 caption=Suur_pilt
	@classinfo syslog_type=ST_IMAGE
		
	@tableinfo images index=id master_table=objects master_index=oid	

*/
class image extends class_base
{
	function image()
	{
		$this->init(array(
			"tpldir" => "automatweb/images",
			"clid" => CL_IMAGE
		));
	}

	function get_image_by_id($id)
	{
		if (!($row = aw_cache_get("get_image_by_id",$id)))
		{
			$q = "SELECT objects.*,images.* FROM images
				LEFT JOIN objects ON (objects.oid = images.id)
				WHERE images.id = '$id'";
			$this->db_query($q);
			$row = $this->db_fetch_row();
			if ($row)
			{
				$row["url"] = $this->get_url($row["file"]);
				$row["meta"] = aw_unserialize($row["metadata"]);
				aw_cache_set("get_image_by_id", $id, $row);
			}
		}
		return $row;
	}

	function get_url($url) 
	{
		$url = $this->mk_my_orb("show", array("fastcall" => 1,"file" => basename($url)),"image",false,true,"/");
		return str_replace("automatweb/", "", $url);
	}

	function parse_alias_list($arr)
	{
		extract($arr);
		$ret = array();
		foreach($aliases as $akey => $adat)
		{
			$ret[$akey] = $this->parse_alias(array(
				"oid" => $oid,
				"matches" => $adat["val"],
				"alias" => $adat,
				"tpls" => &$tpls
			));
		}
		return $ret;
	}

	///
	// !Kasutatakse ntx dokumendi sees olevate aliaste asendamiseks. Kutsutakse välja callbackina
	function parse_alias($args = array())
	{
		extract($args);
		$f = $alias;
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
			$alt = $idata["meta"]["alt"];
			$vars = array(
				"imgref" => $idata["url"],
				"imgcaption" => $idata["comment"],
				"align" => $align[$matches[4]],
				"plink" => $idata["link"],
				"target" => ($idata["newwindow"] ? "target=\"_blank\"" : ""),
				"img_name" => $idata["name"],
				"alt" => $alt,
			);
 
			if ($this->is_flash($idata["file"]))
			{
				$replacement = localparse($tpls["image_flash"],$vars);
			}
			else
			if ($idata["link"] != "")
			{
				if (isset($tpls["image_inplace_linked"]))
				{
					$replacement = localparse($tpls["image_inplace_linked"],$vars);
					$inplace = "image_inplace_linked";
				}
				else if (isset($tpls["image_linked"]))
				{
					$replacement = localparse($tpls["image_linked"],$vars);
				}
				else if (!$this->cfg["no_default_template"])
				{
					if ($idata["comment"] != "")
					{
						$replacement = sprintf("<table border=0 cellpadding=0 cellspacing=0 %s><tr><td><a href='%s' %s><img src='%s' border='0' alt='$alt' title='$alt'></a><br>%s</td></tr></table>",$vars["align"],$idata["link"],$vars["target"],$idata["url"],$idata["comment"]);
					}
					else
					{
						$replacement = sprintf("<table border=0 cellpadding=0 cellspacing=0 %s><tr><td><a href='%s' %s><img src='%s' border='0' alt='$alt' title='$alt'></a></td></tr></table>",$vars['align'],$idata["link"],$vars["target"],$idata["url"]);
					}
				};
			}
			else
			{
				if ($tpls["image_inplace"] && !$this->image_inplace_used)
				{
					$tpl = "image_inplace";
					$inplace = $tpl;
					// mix seda lauset vaja on?
					// sellep2rast et kui on 2 pilti pandud - siis esimese jaoks kasutatakse image_inplace subi ja j2rgmiste jaoks
					// tavalist image subi juba - terryf
					$this->image_inplace_used = true;
				}
				else
				{
					$tpl = "image";
					$inplace = 0;
				};
				if (isset($tpls[$tpl]))
				{
					$replacement = localparse($tpls[$tpl],$vars);
				}
				else if (!$this->cfg["no_default_template"])
				{
					if ($idata["comment"] != "")
					{
						$replacement = sprintf("<table border=0 cellpadding=0 cellspacing=0 %s><tr><td><img src='%s' alt='$alt' title='$alt'><br>%s</td></tr></table>",$vars['align'],$idata["url"],$idata["comment"]);
					}
					else
					{
						$replacement = sprintf("<table border=0 cellpadding=0 cellspacing=0 %s><tr><td><img src='%s' alt='$alt' title='$alt'></td></tr></table>",$vars['align'],$idata["url"]);
					}
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

	////
	// !saves a image that was uploaded in a form to the db
	// $name - the name of the image input in form
	// $parent - the parent object of the image
	// $img_id - if not specified, image will be added, else changed
	function add_upload_image($name,$parent,$img_id = 0)
	{
		$img_id = (int)$img_id;

		global $HTTP_POST_FILES;
		$_fi = get_instance("file");
		if ($HTTP_POST_FILES[$name]['tmp_name'] != "" && $HTTP_POST_FILES[$name]['tmp_name'] != "none")
		{
			if (!$img_id)
			{
				$id = $this->new_object(array(
					"parent" => $parent,
					"class_id" => CL_IMAGE,
					"status" => 2,
					"name" => $HTTP_POST_FILES[$name]["name"],
				));
			}

			if (is_uploaded_file($HTTP_POST_FILES[$name]['tmp_name']))
			{
				$sz = getimagesize($HTTP_POST_FILES[$name]['tmp_name']);

				$fl = $_fi->_put_fs(array("type" => $HTTP_POST_FILES[$name]['type'], "content" => $this->get_file(array("file" => $HTTP_POST_FILES[$name]['tmp_name']))));

				if (!$img_id)
				{
					$this->db_query("INSERT INTO images(id,file) VALUES($id,'$fl')");
				}
				else
				{
					$id = $img_id;
					$this->db_query("UPDATE images SET file = '$fl' WHERE id = '$id'");
				}
			}
		}
		else
		{
			if ($img_id)
			{
				$id = $this->get_image_by_id($img_id);
				// we need to return the image size as well
				$sz = getimagesize($id['file']);
 				return array("id" => $img_id,"url" => $id["url"], "sz" => $sz);
			}
			else
			{
				return false;
			}
		}

		return array("id" => $id,"url" => $this->get_url($fl), "sz" => $sz);
	}

	function show($arr)
	{
		extract($arr);
		$rootdir = $this->cfg["site_basedir"];
		$f1 = substr($file,0,1);
		$fname = $rootdir . "/img/$f1/" . $file;
		if ($file) 
		{
			if (strpos("/",$file) !== false) 
			{
				header("Content-type: text/html");
				print "access denied,";
			} 

			// the site's img folder
			$passed = false;	
			if (is_file($fname) && is_readable($fname)) 
			{
				$passed = true;
			}

			if (!$passed)
			{
				$rootdir = $this->cfg["site_basedir"];
				$fname = $rootdir . "/files/$f1/" . $file;
				if (is_file($fname) && is_readable($fname)) 
				{
					$passed = true;
				}
			}

			if ($passed)
			{
				if ($this->is_flash($file))
				{
					$size[2] = 69;
				}
				else
				{
					$size = GetImageSize($fname);
				};

				if (!is_array($size)) 
				{
					print "access denied.";
				} 
				else 
				{
					switch($size[2]) 
					{
						case "1":
							$type = "image/gif";
							break;
						case "2":
							$type = "image/jpg";
							break;
						case "3":
							$type = "image/png";
							break;
						case "69":
							$type = "application/x-shockwave-flash";
							break;

					};
					header("Content-type: $type");
					readfile($fname);
				};
			} 
			else 
			{
				print "access denied:";
			};
		} 
		else 
		{
			print "access denied;";
		};
		die();
	}

	function view($args = array())
	{
		$idata = $this->get_image_by_id($args["id"]);
		$this->mk_path($idata["parent"],"Vaata pilti");
		$retval = html::img(array(
			"url" => $idata["url"],
		));
		return $retval;
	}

	////
	// !rewrites the image's url to the correct value
	// removes host name from url
	// if url is site/img.aw , rewrites to the correct orb fastcall
	// adds baseurl
	function check_url($url)
	{
		if ($url == "")
		{
			return $url;
		}

		$url = preg_replace("/^http:\/\/.*\//U","/",$url);
		$url = preg_replace("/^https:\/\/.*\//U","/",$url);
		if (substr($url,0,4) == "/img")
		{
			$fname = substr($url,13);
			$url = aw_ini_get("baseurl")."/orb.".aw_ini_get("ext")."/class=image/action=show/fastcall=1/file=".$fname;
		}
		else
		{
			if ($url == "")
			{
				$url = "/automatweb/images/trans.gif";
			}
			$url = aw_ini_get("baseurl").$url;
		}
		$url = str_replace("automatweb/", "", $url);
		return $url;
	}

	////
	// !returns an <img tag that refers to the image 
	// $url - the url of the image in the >img tag
	// $alt - alt text for the image
	function make_img_tag($url, $alt = "")
	{
		if ($url == "")
		{
			return "<img border=\"0\" src=\"".aw_ini_get("baseurl")."/automatweb/images/trans.gif\" alt=\"$alt\">";
		}
		else
		{
			return "<img border=\"0\" src=\"$url\" alt=\"$alt\">";
		}
	}

	function get_property($arr)
	{
		$prop = &$arr['prop'];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "file_show":
				if ($arr["obj"]["oid"])
				{
					$imd = $this->get_image_by_id($arr['obj']['oid']);
					if ($imd['file'] != '')
					{
						$prop['value'] = html::img(array('url' => $imd['url']));
					};
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;
			
			case "file_show2":
				if ($arr["obj"]["oid"])
				{
					$url = $this->get_url($arr["obj"]["meta"]["file2"]);
					if ($url != '')
					{
						$prop['value'] = html::img(array('url' => $url));
					};
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "file":
			case "file2":
				$prop["value"] = "";
				break;
		};

		return $retval;
	}

	function set_property($arr)
	{
		$prop = &$arr['prop'];
		$retval = PROP_OK;
		$form_data = &$arr["form_data"];
		if ($prop['name'] == 'file')
		{
			global $file,$file_type;
			$_fi = get_instance("file");
			if (is_uploaded_file($file))
			{
				$fl = $_fi->_put_fs(array("type" => $file_type, "content" => $this->get_file(array("file" => $file))));
				$prop["value"] = $fl;
			}
			else
			{
				$retval = PROP_IGNORE;
			};
		}
		else
		if ($prop['name'] == 'file2')
		{
			global $file2,$file_type2;
			$_fi = get_instance("file");
			if (is_uploaded_file($file2))
			{
				$fl = $_fi->_put_fs(array("type" => $file_type2, "content" => $this->get_file(array("file" => $file2))));
				$prop["value"] = $fl;
			}
			else
			{
				$retval = PROP_IGNORE;
			};
		};
		return $retval;
	}
}
?>
