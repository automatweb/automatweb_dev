<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/image.aw,v 2.132 2005/01/28 09:42:56 kristo Exp $
// image.aw - image management
/*
	@classinfo trans=1
	@default group=general
	@default table=objects
	
	@property subclass type=hidden
	
	/@property ord table=objects field=jrk type=text size=5 
	/@caption J&auml;rjekord

	@property file type=fileupload table=images form=+emb
	@caption Pilt

	@property dimensions type=text group=general,resize store=no
	@caption Mõõtmed
	
	/@property file_show type=text store=no editonly=1
	/@caption Eelvaade 

	@property file2 type=fileupload group=img2 table=objects field=meta method=serialize 
	@caption Suur pilt

	/@property file_show2 type=text group=img2 store=no editonly=1
	/@caption Eelvaade

	@property file2_del type=checkbox ch_value=1 group=img2 store=no
	@caption Kustuta suur pilt

	@property comment table=objects field=comment type=textbox
	@caption Pildi allkiri

	@property author table=objects field=meta method=serialize type=textbox
	@caption Pildi autor

	@property alt type=textbox table=objects field=meta method=serialize
	@caption Alt

	@property link type=textbox table=images field=link group=settings
	@caption Link

	@property newwindow type=checkbox ch_value=1 table=images field=newwindow group=settings
	@caption Uues aknas

	@property no_print type=checkbox ch_value=1 table=objects field=meta method=serialize group=settings
	@caption &Auml;ra n&auml;ita print-vaates

	@groupinfo settings caption="Seaded"
	@groupinfo img2 caption="Suur pilt"
	@groupinfo resize caption="Muuda suurust"
	@groupinfo resize_big caption="Muuda suure pildi suurust"
	@classinfo syslog_type=ST_IMAGE
		
	@tableinfo images index=id master_table=objects master_index=oid	


	@property new_w type=textbox group=resize field=meta method=serialize size=6 store=no
	@caption Uus laius

	@property new_h type=textbox group=resize field=meta method=serialize size=6 store=no
	@caption Uus k&otilde;rgus

	@property dimensions_big type=text group=resize_big store=no
	@caption Mõõtmed
	
	@property new_w_big type=textbox group=resize_big field=meta method=serialize size=6 store=no
	@caption Uus laius (suur)

	@property new_h_big type=textbox group=resize_big field=meta method=serialize size=6 store=no
	@caption Uus k&otilde;rgus (suur)

	@property do_resize type=submit field=meta method=serialize group=resize value=Muuda store=no

	@property ord type=textbox size=3 table=objects field=jrk group=settings
	@caption J&auml;rjekord

	@property resize_warn type=text store=no
	@caption Info

	@reltype MOD_COMMENT value=1 clid=CL_COMMENT
	@caption Moderaatori kommentaar
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
				array_walk($row ,create_function('&$arr','$arr=trim($arr);')); 
				$row["url"] = $this->get_url($row["file"]);
				$row["meta"] = aw_unserialize($row["metadata"]);
				if ($row["meta"]["file2"] != "")
				{
					$row["big_url"] = $this->get_url($row["meta"]["file2"]);
					$_tmp = basename($row["meta"]["file2"]);
					$f1 = substr($_tmp,0,1);
					$row["meta"]["file2"] = aw_ini_get("site_basedir") . "/files/$f1/" . $_tmp;
					$row['file2'] = &$row['meta']['file2'];
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
				if (substr($imgbaseurl, 0, 4) == "http")
				{
					$url = $imgbaseurl . "/" . $first . "/" . basename($url);
				}
				else
				{
					$url = $this->cfg["baseurl"] . $imgbaseurl . "/" . $first . "/" . basename($url);
				}
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

		if ($alias["aliaslink"] == 1)
		{
			return html::href(array(
				"url" => $idata["url"],
				"caption" => $idata["name"],
				"target" => ($idata["newwindow"] ? "_blank" : "")
			));
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

			if ($idata["url"] == "")
			{
				return "";
			}
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
				"author" => $idata["meta"]["author"],
				"docid" => $args["oid"]
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
						$replacement = sprintf("<table border=0 cellpadding=0 cellspacing=0 %s><tr><td align=\"center\"><a href='%s' %s><img src='%s' border='0' alt='$alt' title='$alt'></a></td></tr><tr><td align=\"center\" class=\"imagecomment\">&nbsp;%s</td></tr></table>",$vars["align"],$idata["link"],$vars["target"],$idata["url"],$idata["comment"]);
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
					if ($vars["align"] != "")
					{
						$replacement = "<table border=0 cellpadding=5 cellspacing=0 $vars[align]><tr><td>";
					}
					else
					{
						$replacement = "";
					}
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
						$replacement .= "<BR><span class=\"imagecomment\">".$idata["comment"]."</span>";
					};
					if ($vars["align"] != "")
					{
						$replacement .= "</td></tr></table>";
					}
				};
			}	
		}

		$retval = array(
				"replacement" => trim($replacement),
				"inplace" => trim($inplace),
		);
		return str_replace("\n", "", $retval);
	}

	function get_img_by_oid($oid,$idx) 
	{
		$o = obj($oid);
		$c = reset($o->connections_from(array("idx" => $idx, "to.class_id" => CL_IMAGE)));
		if (is_object($c))
		{
			return $this->get_image_by_id($c->prop("to"));
		}
		else
		{
			$q = "SELECT images.*,objects.* FROM objects
				LEFT JOIN images ON objects.oid = images.id
				WHERE parent = '$oid' AND idx = '$idx' AND objects.status = 2 AND objects.class_id = 6
				ORDER BY created DESC";
			$this->db_query($q);
			$row = $this->db_next();
			if (is_array($row))
			{
				array_walk($row ,create_function('&$arr','$arr=trim($arr);')); 
			}

			$row["url"] = $this->get_url($row["file"]);
			$row["meta"] = aw_unserialize($row["metadata"]);
			if ($row["meta"]["file2"] != "")
			{
				$row["big_url"] = $this->get_url($row["meta"]["file2"]);
			}

			return $row;
		}
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

		$_fi = get_instance("file");
		if ($_FILES[$name]['tmp_name'] != "" && $_FILES[$name]['tmp_name'] != "none")
		{
			if (!$img_id)
			{
				$img_obj = new object();
				$img_obj->set_parent($parent);
				$img_obj->set_class_id(CL_IMAGE);
				$img_obj->set_status(STAT_ACTIVE);
				$img_obj->set_name($_FILES[$name]["name"]);
				$img_obj->save();
				$img_id = $img_obj->id();
			}
			$img_obj = obj($img_id);

			if (is_uploaded_file($_FILES[$name]['tmp_name']))
			{
				$sz = getimagesize($_FILES[$name]['tmp_name']);

				$fl = $_fi->_put_fs(array(
					"type" => $_FILES[$name]['type'],
					"content" => $this->get_file(array(
						"file" => $_FILES[$name]['tmp_name'],
					)),
				));

				$img_obj->set_prop("file", $fl);
				$img_obj->save();
			}
		}
		else
		{
			if ($img_id)
			{
				$id = $this->get_image_by_id($img_id);
				// we need to return the image size as well
				$sz = @getimagesize($id['file']);
				$fl = $id["file"];
 				return array(
					"id" => $img_id,
					"url" => $id["url"],
					"sz" => $sz,
				);
			}
			else
			{
				return false;
			}
		}

		return array("id" => $img_id,"url" => $this->get_url($fl), "sz" => $sz);
	}

	/**  
		
		@attrib name=show params=name nologin="1" 
		
		@param file required
		
		@returns
		
		
		@comment

	**/
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

	/**  
		
		@attrib name=view params=name nologin="1" 
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
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

		$url = str_replace(aw_ini_get("baseurl"), "", $url);
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
			return "<img border=\"0\" src=\"".aw_ini_get("baseurl")."/automatweb/images/trans.gif\" alt=\"$alt\" title=\"$alt\">";
		}
		else
		{
			return "<img border=\"0\" src=\"$url\" alt=\"$alt\" title=\"$alt\">";
		}
	}

	function get_property($arr)
	{
		$prop = &$arr['prop'];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "resize_warn":
				if (is_oid($arr["obj_inst"]->id()))
				{
					if (($id = $this->_get_conf_for_folder($arr["obj_inst"]->parent(), true)))
					{
						$o = obj($id);
						$prop["value"] = t("Piltide automaatset suurendamist kontrollib objekt ").html::href(array(
							"url" => $this->mk_my_orb("change", array("id" => $id), $o->class_id()),
							"caption" => $o->name()
						));
						return PROP_OK;
					}
				}
				return PROP_IGNORE;
				break;
			/*
			case "file_show":
			case "file_show2":
				$propname = ($prop["name"] == "file_show") ? "file" : "file2";
				$url = $this->get_url($arr["obj_inst"]->prop($propname));
				if ($url != '')
				{
					$prop['value'] = html::img(array('url' => $url));
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;
			*/

			case "file":
			case "file2":
				$url = $this->get_url($arr["obj_inst"]->prop($prop["name"]));
				if ($url != '')
				{
					$prop['value'] = html::img(array('url' => $url));
				}
				else
				{
					$prop["value"] = "";
				};
				break;

			case "dimensions_big":
				$fl = $arr["obj_inst"]->prop("file2");
				if (!empty($fl))
				{
					if ($fl{0} != "/")
					{
						$fl = $this->cfg["site_basedir"]."/files/".$fl{0}."/".$fl;
					}
					$sz = @getimagesize($fl);
					$prop["value"] = $sz[0] . " X " . $sz[1];
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;
			case "dimensions":
				$fl = $arr["obj_inst"]->prop("file");
				if (!empty($fl))
				{
					// rewrite $fl to be correct if site moved
					$fl = basename($fl);
					$fl = $this->cfg["site_basedir"]."/files/".$fl{0}."/".$fl;

					$sz = @getimagesize($fl);
					$prop["value"] = $sz[0] . " X " . $sz[1];
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
		switch ($prop["name"])
		{
			case "file":
				$set = false;
				// see on siis, kui tuleb vormist
				if (is_uploaded_file($_FILES["file"]["tmp_name"]))
				{
					$_fi = get_instance("file");
					$fl = $_fi->_put_fs(array(
						"type" => $_FILES["file"]["type"],
						"content" => $this->get_file(array("file" => $_FILES["file"]["tmp_name"])),
					));
					$prop["value"] = $fl;
					$set = true;
					if ($arr["obj_inst"]->name() == "")
					{
						$arr["obj_inst"]->set_name($_FILES["file"]["name"]);
					}

				}
				// XXX: this is not the correct way to detect this
				elseif (!empty($prop["value"]["type"]))
				{
					$_fi = get_instance("file");
					$fl = $_fi->_put_fs(array(
						"type" => !empty($prop["value"]["type"]) ? $prop["value"]["type"] : "image/jpg",
						// this I think has something to do with copy&paste of objects
						"content" => $prop["value"]["contents"],
					));
					if ($arr["obj_inst"]->name() == "")
					{
						$arr["obj_inst"]->set_name($prop["value"]["name"]);
					}

					$prop["value"] = $fl;
					$set = true;
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "file2":
				if ($arr["request"]["file2_del"] == 1)
				{
					$prop['value'] = '';
				}
				else
				{
					if (is_uploaded_file($_FILES["file2"]["tmp_name"]))
					{
						$_fi = get_instance("file");
						$fl = $_fi->_put_fs(array(
							"type" => $_FILES["file2"]["type"],
							"content" => $this->get_file(array("file" => $_FILES["file2"]["tmp_name"])),
						));
						$prop["value"] = $fl;
					}
					// XXX: this is not the correct way to detect this
					elseif (!empty($prop["value"]["type"]))
					{
						$_fi = get_instance("file");
						$fl = $_fi->_put_fs(array(
							"type" => !empty($prop["value"]["type"]) ? $prop["value"]["type"] : "image/jpg",
							"content" => $prop["value"]["contents"],
						));
						if ($arr["obj_inst"]->name() == "")
						{
							$arr["obj_inst"]->set_name($prop["value"]["name"]);
						}

						$prop["value"] = $fl;
						$set = true;
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
			
			case "new_h_big":
				$this->new_h_big = $prop["value"];
				break;
			
			case "new_w_big":
				$this->new_w_big = $prop["value"];
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
			$img_obj = new object();
			$img_obj->set_parent($parent);
			$img_obj->set_class_id(CL_IMAGE);
			$img_obj->set_status(STAT_ACTIVE);
			$img_obj->set_name($orig_name);
			$img_obj->save();
			$oid = $img_obj->id();
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

		$this->db_query("UPDATE images SET file = '$fl' WHERE id = '$oid'");
		$sz = getimagesize($fl);
		return array("id" => $oid,"url" => $this->get_url($fl), "sz" => $sz);
	}

	function resize_picture(&$arr)
	{
		$im = $this->get_image_by_id($arr["id"]);
		$file = $arr['file'];

		$img = get_instance("core/converters/image_convert");
		if ($im[$file]{0} != "/")
		{
			$im[$file] = $this->cfg["site_basedir"]."/files/".$im[$file]{0}."/".$im[$file];
		}
		$img->load_from_file($im[$file]);

		list($i_width, $i_height) = $img->size();

		$width = $arr['width'];
		$height = $arr['height'];

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
				//$this->new_h = $height;
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
				//$this->new_w = $width;
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
		if($i_width < $width && $i_height < $height)
		{
			$width = $i_width;
			$height = $i_height;
		}
		elseif($i_height < $height && $i_width > $width)
		{
			$ratio = $i_height / $height;
			$width = (int)($i_width * $ratio);
			$height = (int)($i_height * $ratio);
		}
		elseif($i_width < $width && $i_height > $height)
		{
			$ratio = $i_width / $width;
			$width = (int)($i_width * $ratio);
			$height = (int)($i_height * $ratio);
		}
		$img->resize_simple($width, $height);

		$this->put_file(array(
			'file' => $im[$file],
			"content" => $img->get(IMAGE_JPEG)
		));

	}

	function callback_post_save($arr)
	{
		if($this->new_h_big || $this->new_w_big)
		{
			$arr['file'] = 'file2';
			$arr['height'] = $this->new_h_big;
			$arr['width'] = $this->new_w_big;
			/*echo $arr['file'],":<br>";
			echo $arr['height'],"<br>";
			echo $arr['width'],"<br>";*/
			$this->resize_picture($arr);
		}
		
		if($this->new_w || $this->new_h)
		{
			$arr['file'] = 'file';
			$arr['height'] = $this->new_h;
			$arr['width'] = $this->new_w;
			/*echo $arr['file'],":<br>";
			echo $arr['height'],"<br>";
			echo $arr['width'],"<br>";*/
			$this->resize_picture($arr);
		}
		
		$this->do_apply_gal_conf(obj($arr["id"]), $prop["value"]);
	}

	/**  
		
		@attrib name=show_big params=name nologin="1" 
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function show_big($arr)
	{
		extract($arr);
		$im = $this->get_image_by_id($id);
		$this->read_any_template("show_big.tpl");
		$this->vars(array(
			"big_url" => $this->get_url($im["meta"]["file2"]),
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

	function _get_conf_for_folder($pt, $apply_image = false)
	{
		if (!is_oid($pt))
		{
			return false;
		}

		$oc = obj($pt);
		$oc = $oc->path();

		$rv = false;
		if ($apply_image)
		{
			$appi = " AND apply_image = 1 ";
		}
		foreach($oc as $dat)
		{
			$q = "SELECT conf_id FROM gallery_conf2menu LEFT JOIN objects ON objects.oid = gallery_conf2menu.conf_id WHERE menu_id = '".$dat->id()."' AND objects.status != 0 $appi";
			if (($mnid = $this->db_fetch_field($q,"conf_id")))
			{
				$rv = $mnid;
			}
		}
		// that config object might have been deleted, check it and return false, if so
		if (!$this->can("view",$rv))
		{
			$rv = false;
		};
		return $rv;
	}


	function do_apply_gal_conf($o)
	{
		$conf = $this->_get_conf_for_folder($o->parent(), true);
		if ($conf)
		{
			// resize image as conf says
			$this->do_resize_image(array(
				"o" => $o,
				"conf" => obj($conf)
			));
		}
	}


	/** resizes images as conf says

		@comment
		
			$o - image object
			$conf - gallery conf object
	**/
	function do_resize_image($arr)
	{
		extract($arr);
		// big first
		if (($conf->prop("v_width") || $conf->prop("v_height") || $conf->prop("h_width") || $conf->prop("h_height")))
		{
			$bigf = $o->prop("file2");
			if (!$bigf)
			{
				// no big file, copy from small file
				$bigf = $o->prop("file");
				if ($bigf)
				{
					$f = get_instance("file");
					$bigf = $f->_put_fs(array(
						"type" => "image/jpg",
						"content" => $this->get_file(array("file" => $bigf))
					));
					$o->set_prop("file2", $bigf);
					$o->save();
				}
			}

			if ($bigf)
			{
				// do the actual resize-file thingie
				$this->do_resize_file_in_fs($bigf, $conf, "");
			}
		}
	
		// now small
		$smallf = $o->prop("file");
		if (!$smallf)
		{
			// do copy-big-to-small
			$smallf = $o->prop("file2");
			if ($smallf)
			{
				$f = get_instance("file");
				$smallf = $f->_put_fs(array(
					"type" => "image/jpg",
					"content" => $this->get_file(array("file" => $smallf))
				));
				$o->set_prop("file", $smallf);
				$o->save();
			}
		}

		if ($smallf)
		{
			$this->do_resize_file_in_fs($smallf, $conf, "tn_");
		}
	}

	function do_resize_file_in_fs($file, $conf, $prefix)
	{
		$img = get_instance("core/converters/image_convert");
		$img->load_from_file($file);

		// get image size
		list($i_width, $i_height) = $img->size();

		$conf_i = $conf->instance();
		$xyd = $conf_i->get_xydata_from_conf(array(
			"conf" => $conf, 
			"prefix" => $prefix, 
			"w" => $i_width, 
			"h" => $i_height
		));
		if ($xyd["is_subimage"] && $xyd["si_width"] && $xyd["si_height"])
		{
			// make subimage
			$img->resize(array(
				"x" => $xyd["si_left"],
				"y" => $xyd["si_top"],
				"width" => $xyd["si_width"],
				"height" => $xyd["si_height"],
				"new_width" => $xyd["width"],
				"new_height" => $xyd["height"]
			));
		}
		else
		if ($xyd["width"] != $i_width || $xyd["height"] != $i_height)
		{
			$img->resize_simple($xyd["width"], $xyd["height"]);
		}

		$img->save($file, IMAGE_JPEG);
	}

	function make_img_tag_wl($id, $alt = NULL, $has_big_alt = NULL)
	{
		$that = get_instance("image");
		$u = $that->get_url_by_id($id);

		$o = obj($id);

		if ($alt === NULL)
		{
			$alt = $o->name();
		}

		if ($o->prop("file2") != "")
		{
			$file2 = basename($o->prop("file2"));
			$file2 = $this->cfg["site_basedir"]."/files/".$file2{0}."/".$file2;
			if ($has_big_alt !== NULL)
			{
				$alt = $has_big_alt;
			}
			$imagetag = image::make_img_tag($u, $alt);

			$size = @getimagesize($file2);

			$bi_show_link = $that->mk_my_orb("show_big", array("id" => $id), "image");
			$bi_link = "window.open(\"$bi_show_link\",\"popup\",\"width=".($size[0]).",height=".($size[1])."\");";

			$imagetag = html::href(array(
				"url" => "javascript:void(0)",
				"onClick" => $bi_link,
				"caption" => $imagetag,
				"title" => $alt
			));
		}
		else
		{
			$imagetag = image::make_img_tag($u, $alt);
		}

		return $imagetag;
	}

	function get_on_click_js($id)
	{
		$o = obj($id);
		if ($o->prop("file2") == "")
		{
			return "";
		}

		$that = new image;
		$size = @getimagesize($o->prop("file2"));
		$bi_show_link = $that->mk_my_orb("show_big", array("id" => $id), "image");
		return  "window.open(\"$bi_show_link\",\"popup\",\"width=".($size[0]).",height=".($size[1])."\");";
	}

	function mime_type_for_image($arr)
	{


	}

	function callback_mod_tab($arr)
	{
		if ($arr["id"] == "resize" || $arr["id"] == "resize_big")
		{
			$cv = get_instance("core/converters/image_convert");
			$ret = $cv->can_convert();
			if ($ret)
			{
				$cv->set_error_reporting(false);

				$prop = "file2";
				if ($arr["id"] == "resize")
				{
					$prop = "file";
				}

				if ($arr["obj_inst"]->prop($prop) == "")
				{
					$ret = false;
				}
				else
				{
					$cv->load_from_file($arr["obj_inst"]->prop($prop));
					if ($cv->is_error())
					{
						$ret = false;
					}
				}
			}
			return $ret;
		}
		return true;
	}
}
?>
