<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/image.aw,v 2.78 2003/12/04 10:03:55 kristo Exp $
// image.aw - image management
/*
	@classinfo trans=1
	@default group=general
	@default table=objects

	@property file type=fileupload table=images
	@caption Pilt

	@property cur_width type=text group=general,resize store=no 
	@caption Laius

	@property cur_height type=text group=general,resize store=no
	@caption Kõrgus
	
	@property file_show type=text store=no editonly=1
	@caption Eelvaade 

	@property file2 type=fileupload group=img2 table=objects field=meta method=serialize 
	@caption Suur pilt

	@property file_show2 type=text group=img2 store=no editonly=1
	@caption Eelvaade

	@property file2_del type=checkbox ch_value=1 group=img2 store=no
	@caption Kustuta suur pilt

	@property comment table=objects field=comment type=textbox
	@caption Pildi allkiri

	@property author table=objects field=meta method=serialize type=textbox
	@caption Pildi autor

	@property alt type=textbox table=objects field=meta method=serialize
	@caption Alt

	@property link type=textbox table=images field=link
	@caption Link

	@property newwindow type=checkbox ch_value=1 table=images field=newwindow
	@caption Uues aknas

	@property no_print type=checkbox ch_value=1 table=objects field=meta method=serialize
	@caption &Auml;ra n&auml;ita print-vaates

	@groupinfo img2 caption="Suur pilt"
	@groupinfo resize caption="Muuda suurust"
	@classinfo syslog_type=ST_IMAGE
		
	@tableinfo images index=id master_table=objects master_index=oid	


	@property new_w type=textbox group=resize field=meta method=serialize size=6 store=no
	@caption Uus laius

	@property new_h type=textbox group=resize field=meta method=serialize size=6 store=no
	@caption Uus k&otilde;rgus

	@property do_resize type=submit field=meta method=serialize group=resize value=Muuda store=no

	@classinfo no_status=1

*/
class image extends class_base
{
	function image()
	{
		$this->init(array(
			"tpldir" => "automatweb/images",
			"clid" => CL_IMAGE,
		));
	}

	function get_image_by_id($id)
	{
		// it shouldn't be, but it is an array, if a period is loaded
		// from a stale cache.
		if (is_array($id) || !is_numeric($id))
		{
			return false;
		}
		if (!($row = aw_cache_get("get_image_by_id",$id)))
		{
			$q = "SELECT objects.*,images.* FROM images
				LEFT JOIN objects ON (objects.oid = images.id)
				WHERE images.id = '$id'";
			if (method_exists($this, "db_query"))
			{
				$this->db_query($q);
				$row = $this->db_fetch_row();
			}
			if ($row)
			{
				$row["url"] = $this->get_url($row["file"]);
				$row["meta"] = aw_unserialize($row["metadata"]);
				if ($row["meta"]["file2"] != "")
				{
					$row["big_url"] = $this->get_url($row["meta"]["file2"]);
					$_tmp = basename($row["meta"]["file2"]);
					$f1 = substr($_tmp,0,1);
					$row["meta"]["file2"] = aw_ini_get("site_basedir") . "/files/$f1/" . $_tmp;
				}
				aw_cache_set("get_image_by_id", $id, $row);
			}
		}
		return $row;
	}

	function get_url($url) 
	{
		if ($url)
		{
			$imgbaseurl = $this->cfg["imgbaseurl"];
			if (!empty($imgbaseurl))
			{
				$first = substr(basename($url),0,1);
				$url = $this->cfg["baseurl"] . $imgbaseurl . "/" . $first . "/" . basename($url);
			}
			else
			{
				$url = $this->mk_my_orb("show", array("fastcall" => 1,"file" => basename($url)),"image",false,true,"/");
			}
			$retval = str_replace("automatweb/", "", $url);
		}
		else
		{
			$retval = "";
		};
		return $retval;
	}

/*	function parse_alias_list($arr)
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
	}*/

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

		if (($GLOBALS["print"] == 1 || ($GLOBALS["class"] == "document" && $GLOBALS["action"] == "print"))  && $idata["meta"]["no_print"] == 1)
		{
			return "";
		}

		$replacement = "";
		$align= array("k" => "align=\"center\"", "p" => "align=\"right\"" , "v" => "align=\"left\"" ,"" => "");
		$alstr = array("k" => "center","v" => "left","p" => "right","" => "");
		if ($idata)
		{
			$alt = $idata["meta"]["alt"];
			if ($idata["meta"]["file2"] != "")
			{
				$size = @getimagesize($idata["meta"]["file2"]);
			};
			if ($idata["file"] != "")
			{
				$i_size = @getimagesize($idata["file"]);
			};
			$bi_show_link = $this->mk_my_orb("show_big", array("id" => $f["target"]));
			$bi_link = "window.open('$bi_show_link','popup','width=".($size[0]).",height=".($size[1])."');";
			$vars = array(
				"width" => $i_size[0],
				"height" => $i_size[1],
				"imgref" => $idata["url"],
				"imgcaption" => $idata["comment"],
				"align" => $align[$matches[4]],
				"alignstr" => $alstr[$matches[4]],
				"plink" => $idata["link"],
				"target" => ($idata["newwindow"] ? "target=\"_blank\"" : ""),
				"img_name" => $idata["name"],
				"alt" => $alt,
				"bigurl" => $idata["big_url"],
				"big_width" => isset($size[0]) ? $size[0] : "",
				"big_height" => isset($size[1]) ? $size[1] : "",
				"w_big_width" => isset($size[0]) ? $size[0]+10 : "",
				"w_big_height" => isset($size[1]) ? $size[1]+10 : "",
				"bi_show_link" => $bi_show_link,
				"bi_link" => $bi_link,
				"author" => $idata["meta"]["author"]
			);

			$ha = ""; 
			if ($idata["meta"]["author"] != "")
			{
				$ha = localparse($tpls["HAS_AUTHOR"], $vars);
			}
			$vars["HAS_AUTHOR"] = $ha;
			
			if ($this->is_flash($idata["file"]))
			{
				$replacement = localparse($tpls["image_flash"],$vars);
			}
			else
			if ($idata["link"] != "")
			{
		//		echo "has link! <br>";
				if ($idata["big_url"] != "" && isset($tpls["image_big_linked"]))
				{
					$replacement = localparse($tpls["image_big_linked"],$vars);
				}
				else
				if (isset($tpls["image_inplace_linked"]))
				{
					$replacement = localparse($tpls["image_inplace_linked"],$vars);
					$inplace = "image_inplace_linked";
				}
				else 
				if (isset($tpls["image_linked"]))
				{
					$replacement = localparse($tpls["image_linked"],$vars);
				}
				else 
				if (!$this->cfg["no_default_template"])
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
					if ($idata["big_url"] != "")
					{
						$tpl = "image_has_big";
					}
					$inplace = 0;
				};
				if (isset($tpls[$tpl]))
				{
					$replacement = localparse($tpls[$tpl],$vars);
				}
				else if (!$this->cfg["no_default_template"])
				{
					//$replacement = "<table border=0 cellpadding=0 cellspacing=0 $vars[align]><tr><td>";
					$replacement = "";
					if (!empty($idata["big_url"]))
					{
						$replacement .= "<a href=\"javascript:void(0)\" onClick=\"$bi_link\">";
					};
					$replacement .= "<img src='$idata[url]' alt='$alt' title='$alt' border=\"0\">";
					if (!empty($idata["big_url"]))
					{
						$replacement .= "</a>";
					}
					if (!empty($idata["comment"]))
					{
						$replacement .= "<BR>".$idata["comment"];
					};
					//$replacement .= "</td></tr></table>";
				};
			}	
		}
		$retval = array(
				"replacement" => $replacement,
				"inplace" => $inplace,
		);
		return str_replace("\n", "", $retval);
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
		$row["meta"] = aw_unserialize($row["metadata"]);
		if ($row["meta"]["file2"] != "")
		{
			$row["big_url"] = $this->get_url($row["meta"]["file2"]);
		}

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
				$sz = @getimagesize($id['file']);
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
			if (@is_file($fname) && @is_readable($fname)) 
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
					header("Content-length: ".filesize($fname));
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
			'height' => (isset($args['height']) ? $args['height'] : NULL),
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
		$imgbaseurl = aw_ini_get("image.imgbaseurl");
		if (!empty($imgbaseurl))
		{
			if (preg_match("/file=(.*)$/",$url,$m))
			{
				$fname = $m[1];
				$first = substr($fname,0,1);
				$url = $this->cfg["baseurl"] . $imgbaseurl . "/" . $first . "/" . $fname;
				if (substr($url,-11) == "/aw_img.jpg")
				{
					$url = str_replace("/aw_img.jpg","",$url);
				};

			};
		}
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
				$imd = $this->get_image_by_id($arr['obj_inst']->id());
				if ($imd['file'] != '')
				{
					$prop['value'] = html::img(array('url' => $imd['url']));
				};
				break;
			
			case "file_show2":
				$url = $this->get_url($arr["obj_inst"]->prop("file2"));
				if ($url != '')
				{
					$prop['value'] = html::img(array('url' => $url));
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "file":
			case "file2":
				$fl = get_instance("file");
				$envir = $fl->check_environment();
				if (!empty($envir))
				{
					$prop["error"] = $envir;
					$retval = PROP_ERROR;
				};
				$prop["value"] = "";
				break;

			case "cur_width":
			case "cur_height":
				// damn, I really don't like that approach
				$fl = $arr["obj_inst"]->prop("file");
				if (!empty($fl))
				{
					$sz = @getimagesize($fl);
					$prop["value"] = ($prop["name"] == "cur_width") ? $sz[0] : $sz[1];
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

		};

		return $retval;
	}

	function set_property($arr)
	{
		$prop = &$arr['prop'];
		$retval = PROP_OK;
		$form_data = &$arr["form_data"];
		switch ($prop["name"])
		{
			case "file":
				global $file,$file_type;
				if (is_uploaded_file($file))
				{
					$_fi = get_instance("file");
					$fl = $_fi->_put_fs(array(
						"type" => $file_type,
						"content" => $this->get_file(array("file" => $file)),
					));
					$prop["value"] = $fl;
				}
				// XXX: this is not the correct way to detect this
				elseif (!empty($arr["form_data"]["file_type"]))
				{
					$_fi = get_instance("file");
					$fl = $_fi->_put_fs(array(
						"type" => $arr["form_data"]["file_type"],
						"content" => $prop["value"],
					));
					$prop["value"] = $fl;
					
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "file2":
				if ($arr["form_data"]["file2_del"] == 1)
				{
					$prop['value'] = '';
				}
				else
				{
					global $file2,$file2_type;
					if (is_uploaded_file($file2))
					{
						$_fi = get_instance("file");
						$fl = $_fi->_put_fs(array(
							"type" => $file2_type,
							"content" => $this->get_file(array("file" => $file2)),
						));
						$prop["value"] = $fl;
					}
					else
					{
						$retval = PROP_IGNORE;
					};
				}
				break;

			case "do_resize":
				$this->do_resize = true;
				break;

			case "new_w":
				$this->new_w = $prop["value"];
				break;

			case "new_h":
				$this->new_h = $prop["value"];
				break;
		};
		return $retval;
	}

	////
	// !adds an image to the system
	// parameters:
	//	from - either "file" or "string"
	//	str - if from is string, then this is the file content
	//	file - if from is file, then this is the filename for file content
	//	orig_name - the original name of the file, used as the object name
	//	parent - the folder where to save the image
	//	id - the if of the image to change, optional
	function add_image($arr)
	{
		extract($arr);
		if ($from == "file")
		{
			$str = $this->get_file(array("file" => $file));
		}

		if (!$id)
		{
			$oid = $this->new_object(array(
				"parent" => $parent,
				"class_id" => CL_IMAGE,
				"status" => 2,
				"name" => $orig_name,
			));
		}
		else
		{
			$oid = $id;
		}

		$_fi = get_instance("file");
		$mime = get_instance("core/aw_mime_types");
		$fl = $_fi->_put_fs(array(
			"type" => $mime->type_for_file($orig_name),
			"content" => $str
		));

		if (!$id)
		{
			$this->db_query("INSERT INTO images(id,file) VALUES($oid,'$fl')");
		}
		else
		{
			$this->db_query("UPDATE images SET file = '$fl' WHERE id = '$oid'");
		}
		$sz = getimagesize($fl);
		return array("id" => $oid,"url" => $this->get_url($fl), "sz" => $sz);
	}

	function callback_post_save($arr)
	{
		$im = $this->get_image_by_id($arr["id"]);
		if ($this->do_resize)
		{
			$img = get_instance("core/converters/image_convert");
			$img->load_from_file($im['file']);
	
			list($i_width, $i_height) = $img->size();

			$width = $this->new_w;
			$height = $this->new_h;

			if ($width && !$height)
			{
				if ($width{strlen($width)-1} == "%")
				{
					$height = $width;
				}
				else
				{
					$ratio = $width / $i_width;
					$height = (int)($i_height * $ratio);
				}
			}

			if (!$width && $height)
			{
				if ($height{strlen($height)-1} == "%")
				{
					$width = $height;
				}
				else
				{
					$ratio = $height / $i_height;
					$width = (int)($i_width * $ratio);
				}
			}

			if ($width{strlen($width)-1} == "%")
			{
				$width = (int)($i_width * (((int)substr($width, 0, -1))/100));
			}
			if ($height{strlen($height)-1} == "%")
			{
				$height = (int)($i_height * (((int)substr($height, 0, -1))/100));
			}

			$img->resize_simple($width, $height);

			$this->put_file(array(
				"file" => $im['file'],
				"content" => $img->get(IMAGE_JPEG)
			));
		}
	}

	function show_big($arr)
	{
		extract($arr);
		$im = $this->get_image_by_id($id);
		$this->read_any_template("show_big.tpl");
		$this->vars(array(
			"big_url" => $this->get_url($im["meta"]["file2"])
		));
		die($this->parse());
	}

	function request_execute($obj)
	{
		$this->show(array(
			"file" => basename($obj->prop("file"))
		));
	}

	function get_url_by_id($id)
	{
		$imd = $this->get_image_by_id($id);
		$url = $this->get_url($imd["file"]);
		return $this->check_url($url);
	}
}
?>
