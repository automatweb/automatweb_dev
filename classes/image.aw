<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/image.aw,v 2.185 2007/01/23 10:46:57 kristo Exp $
// image.aw - image management
/*
	@classinfo trans=1
	@classinfo syslog_type=ST_IMAGE

	@tableinfo images index=id master_table=objects master_index=oid


@default group=general

	@property subclass type=hidden table=objects

	@property file type=fileupload table=images form=+emb
	@caption Pilt

	@property dimensions type=text group=general,resize store=no
	@caption M&otilde;&otilde;tmed

	@property comment table=objects field=comment type=textbox
	@caption Pildi allkiri

	@property author table=objects field=meta method=serialize type=textbox
	@caption Pildi autor

	@property alt type=textbox table=objects field=meta method=serialize
	@caption Alt

	@property link type=textbox table=images field=link
	@caption Link

	@property date_taken type=datetime_select table=images field=aw_date_taken 
	@caption Pildistamise aeg

	@property can_comment type=checkbox table=objects field=flags method=bitmask ch_value=1
	@caption K&otilde;ikjal kommenteeritav

	@property no_apply_gal_conf type=checkbox table=objects field=meta method=serialize ch_value=1
	@caption &Auml;ra kasuta galerii seadeid

	/@property file_show type=text store=no editonly=1
	/@caption Eelvaade 

@groupinfo show caption="N&auml;itamine"
@default group=show

	@property show_conditions type=chooser multiple=1 store=no
	@caption Tingimused

	@property newwindow type=checkbox ch_value=1 table=images field=newwindow
	@caption Uues aknas

	@property no_print type=checkbox ch_value=1 table=objects field=meta method=serialize
	@caption &Auml;ra n&auml;ita print-vaates

	@property ord type=textbox size=3 table=objects field=jrk
	@caption J&auml;rjekord

@groupinfo img2 caption="Suur pilt"
@default group=img2

	@property file2 type=fileupload table=objects field=meta method=serialize
	@caption Suur pilt

	@property file2_del type=checkbox ch_value=1 store=no
	@caption Kustuta suur pilt

	@property big_flash type=relpicker reltype=RELTYPE_FLASH table=objects field=meta method=serialize
	@caption Flash 
	
@groupinfo resize caption="Muuda suurust"
@default group=resize

	@property new_w type=textbox field=meta method=serialize size=6 store=no
	@caption Uus laius

	@property new_h type=textbox field=meta method=serialize size=6 store=no
	@caption Uus k&otilde;rgus

	@property do_resize type=submit field=meta method=serialize store=no
	@caption Muuda

@groupinfo resize_big caption="Muuda suure pildi suurust"
@default group=resize_big

	@property dimensions_big type=text store=no
	@caption M&otilde;&otilde;tmed
	
	@property new_w_big type=textbox field=meta method=serialize size=6 store=no
	@caption Uus laius (suur)

	@property new_h_big type=textbox field=meta method=serialize size=6 store=no
	@caption Uus k&otilde;rgus (suur)


	/@property ord table=objects field=jrk type=text size=5
	/@caption J&auml;rjekord

	/@property file_show2 type=text group=img2 store=no editonly=1
	/@caption Eelvaade

	@property resize_warn type=text store=no
	@caption Info


@groupinfo transl caption=T&otilde;lgi
@default group=transl
	
	@property transl type=callback callback=callback_get_transl
	@caption T&otilde;lgi

@reltype MOD_COMMENT value=1 clid=CL_COMMENT
@caption Moderaatori kommentaar

@reltype FLASH value=2 clid=CL_FLASH
@caption Flash
*/

define("FL_IMAGE_CAN_COMMENT", 1);

class image extends class_base
{
	function image()
	{
		$this->init(array(
			"tpldir" => "automatweb/images",
			"clid" => CL_IMAGE,
		));

		$this->trans_props = array(
			"comment", "author", "alt", "link"
		);
	}

	/** 

		@attrib name=get_image_by_id api=1 params=pos

		@param id required type=int
			id of the image in images database table
		@errors 
			none

		@returns 
			- array with image data
			- false if the id parameter is array
			- false if the id parameter is not numeric

		@comment 
			none

		@examples
			$image_inst = get_instance(CL_IMAGE);
			$image_data = $image_inst->get_image_by_id(1234);

	**/
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
			};


			if ($row)
			{
				array_walk($row ,create_function('&$arr','$arr=trim($arr);')); 
				$row["url"] = $this->get_url($row["file"]);
				$row["meta"] = aw_unserialize($row["metadata"]);
				$row["can_comment"] = $row["flags"] & FL_IMAGE_CAN_COMMENT;
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

			if ($this->can("view", $id))
			{
				$o = obj($id);
				$row["comment"] = $this->trans_get_val($o, "comment");
				$row["link"] = $this->trans_get_val($o, "link");
				$row["meta"]["author"] = $this->trans_get_val($o, "author");
				$row["meta"]["alt"] = $this->trans_get_val($o, "alt");
				if ($row["meta"]["alt"] == "")
				{
					$row["meta"]["alt"] = $row["name"];
				}
			}
		}
		return $row;
	}

	/** fixes image url

		@attrib name=get_url api=1 params=pos

		@param url required type=string
			url to be fixed
		@errors 
			none

		@returns 
			If url parameter evaluates false (ie. '', 0) then returns empty value.
		@comment 
			none

		@examples
			none
	**/
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
	// !Kasutatakse ntx dokumendi sees olevate aliaste asendamiseks. Kutsutakse v&auml;lja callbackina
	//  force_comments - shows comment count and links to comment window even if not set in images prop
	function parse_alias($args = array())
	{
		// Defaults
		$force_comments = false;
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
		
		// show commentlist and popup to if set in property or forced
		$do_comments = (!empty($idata["can_comment"]) || $force_comments);

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
			// Count comments, if needed
			$num_comments = 0;
			$show_link_arr = array("id" => $f["target"]);
			if ($do_comments)
			{
				$com = get_instance(CL_COMMENT);
				$num_comments = $com->get_comment_count(array(
					'parent' => $idata["id"],
				));
				$show_link_arr["comments"] = 1; // Passed to popup window

				$idata["comment"] .= ' ('.$num_comments.' '. ($num_comments == 1 ? t("kommentaar") : t("kommentaari")) .')';
			}
		
			$alt = $idata["meta"]["alt"];

			if ($idata["meta"]["file2"] != "")
			{
				$size = @getimagesize($idata["meta"]["file2"]);
			};
			if ($this->can("view", $idata["meta"]["big_flash"]))
			{
				$flo = obj($idata["meta"]["big_flash"]);
				$size = array($flo->prop("width"), $flo->prop("height"));
			}
			$bi_show_link = $this->mk_my_orb("show_big", $show_link_arr);
			$popup_width = min(1000, $size[0] + ($do_comments ? 500 : 0));
			$popup_height = max(5, $size[1]);// + ($do_comments ? 200 : 0);
			$bi_link = "window.open('$bi_show_link','popup','width=".($popup_width).",height=".($popup_height)."');";

			// for case if there is a big pic, a little one is missing. then usual text link is shown with images name
			if($idata["file"] == "" && $idata["file2"] != "")
			{
				if(strlen($idata["meta"]["alt"]))
				{
					$alt = " alt=\"".$idata["meta"]["alt"]."\"";
				}
				return array(
					"replacement" => "<a href=\"javascript:void(0)\" onClick=\"$bi_link\"".$alt.">".$idata["name"]."</a>",
					"inplace" => "",
				);
			}

			if ($idata["file"] != "")
			{
				$i_size = @getimagesize($idata["file"]);
				if (empty($idata['meta']['file2']) && $do_comments)
				{
					$size = $i_size;
				}
			};
			if ($idata["url"] == "")
			{
				return "";
			}
			
			if (!empty($args['link_prefix'])) // Override image link
			{
				$idata['link'] = $args['link_prefix'].$idata['oid'];
			}
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
				"docid" => $args["oid"],
				"comments" => $num_comments,

			);

			if ($this->can("view", $idata["meta"]["big_flash"]))
			{
				$idata["big_url"] = " ";
			}
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
					$authortxt = "";
					if ($idata['meta']['author'] != "")
					{
						$authortxt = ' ('.$idata['meta']['author'].')';
					}
					if ($idata["comment"] != "" || $authortxt != "")
					{
						$replacement = sprintf("<table border=0 cellpadding=0 cellspacing=0 %s><tr><td align=\"center\"><a href='%s' %s><img src='%s' border='0' alt='$alt' title='$alt' class='$use_style'/></a></td></tr><tr><td align=\"center\" class=\"imagecomment\">&nbsp;%s%s</td></tr></table>",$vars["align"],$idata["link"],$vars["target"],$idata["url"],$idata["comment"], $authortxt);
					}
					else
					if ($vars["align"] != "")
					{
						$replacement = sprintf("<table border=0 cellpadding=0 cellspacing=0 %s><tr><td><a href='%s' %s><img src='%s' border='0' alt='$alt' title='$alt' class='$use_style'/></a></td></tr></table>",$vars['align'],$idata["link"],$vars["target"],$idata["url"]);
					}
					else
					{
						$replacement = sprintf("<a href='%s' %s><img src='%s' border='0' alt='$alt' title='$alt' class='$use_style'/></a>", $idata["link"], $vars["target"], $idata["url"]);
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
					$replacement = "";
					if ($vars["align"] != "")
					{
						$replacement .= "<table border=0 cellpadding=5 cellspacing=0 $vars[align]><tr><td>";
					}
					if (!empty($idata["big_url"]) || $do_comments)
					{
						$replacement .= "<a href=\"javascript:void(0)\" onClick=\"$bi_link\">";
					};
					$replacement .= "<img src='$idata[url]' alt='$alt' title='$alt' border=\"0\" class=\"$use_style\"/>";
					if (!empty($idata["big_url"]) || $do_comments)
					{
						$replacement .= "</a>";
					}
					
					$subtxt = "";
					if (!empty($idata["comment"]))
					{
						$subtxt .= $idata['comment'];
					}
					if (!empty($idata['meta']['author']))
					{
						$subtxt .= ' ('.$idata['meta']['author'].')';
					}
					if (strlen($subtxt))
					{
						$replacement .= "<BR><span class=\"imagecomment\">".$subtxt."</span>";
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

			$row["comment"] = $this->trans_get_val($o, "comment");
			$row["link"] = $this->trans_get_val($o, "link");
			$row["meta"]["author"] = $this->trans_get_val($o, "author");
			$row["meta"]["alt"] = $this->trans_get_val($o, "alt");

			return $row;
		}
	}

	/** Checks if the file is shockwave-flash file or not

		@attrib name=is_flash api=1 params=pos

		@param file required type=string
			path to the imagefile
		@errors 
			none

		@returns 
			true if it is flash file, false othervise

		@comment 
			none

		@examples
			$inst = get_instance(CL_IMAGE);
			$o = new object(1234);
			var_dump( $inst->is_flash( $o->prop('file') ) );
	**/
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

	/** Saves a image that was uploaded in a form to the database

		@attrib name=is_flash api=1 params=pos

		@param name required type=string
			the name of the image input in form
		@param parent required type=oid
			the parent object of the image
		@param img_id optional type=int
			image id, if not specified, image will be added, else changed

		@errors 
			none

		@returns 
			- array of image data (image_id, image url and image size)
			- false if img_id is set and evaluates to false

		@comment 
			none

		@examples
			none
	**/
	function add_upload_image($name,$parent,$img_id = 0)
	{
		$img_id = (int)$img_id;

		$_fi = get_instance(CL_FILE);
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

	/** Creates HTML image tag
		
		@attrib name=view params=name nologin="1" 
		
		@param id required type=int
			image id
		@param height optional type=int
			image's height

		@returns
			HTML image tag

		@comment
			none
		
		@examples
			none
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

	/** Rewrites the image's url to the correct value
		
		@attrib name=view params=name nologin="1" 
		
		@param url required type=string
			URL to be rewritten

		@returns
			- Rewrote URL
			- If url parameter is empty, then returns empty value

		@comment
			removes host name from url
			if url is site/img.aw , rewrites to the correct orb fastcall
			adds baseurl
		
		@examples
			none
	**/
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


	/** Creates HTML image tag
		@attrib name=make_img_tag params=pos 
		
		@param url required type=string
			URL to the image
		@param alt optional type=string
			Alt text of the image

		@param size optional type=array
			array(
				height => int,
				width => int
			)
			sets img tag height and width

		@returns
			- Rewrote URL
			- If url parameter is empty, then returns empty value

		@comment
			removes host name from url
			if url is site/img.aw , rewrites to the correct orb fastcall
			adds baseurl
		
		@examples
			none
	**/
	function make_img_tag($url, $alt = "", $size = array())
	{
		$tag = $size["height"]?" height=\"".$size["height"]."\"":"";
		$tag .= $size["width"]?" width=\"".$size["width"]."\"":"";
		if ($url == "")
		{
			return "<img border=\"0\" src=\"".aw_ini_get("baseurl")."/automatweb/images/trans.gif\" alt=\"$alt\" title=\"$alt\"".$tag.">";
		}
		else
		{
			return "<img border=\"0\" src=\"$url\" alt=\"$alt\" title=\"$alt\"".$tag.">";
		}
	}

	function get_property($arr)
	{
		$prop = &$arr['prop'];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "date_taken":
				if (!is_oid($arr["obj_inst"]->id()) || $prop["value"] < 100)
				{
					$prop["value"] = -1;
				}
				break;

			case "newwindow":
			case "no_print":
				$retval = PROP_IGNORE;
				break;

			case "show_conditions":
				$prop["options"] = array(
					"newwindow" => t("Uues aknas"),
					"no_print" => t("&Auml;ra n&auml;ita print-vaates"),
				);
				$prop["value"]["newwindow"] = $arr['obj_inst']->prop("newwindow");
				$prop["value"]["no_print"] = $arr['obj_inst']->prop("no_print");
				break;


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
				$prop["value"] = image::make_img_tag_wl($arr["obj_inst"]->id());
				if (is_oid($arr["obj_inst"]->id()))
				{
					$url = $this->mk_my_orb("fetch_image_tag_for_doc", array("id" => $arr["obj_inst"]->id()));
					$image_url = $this->get_url_by_id($arr["obj_inst"]->id());
					$alias_url = $this->mk_my_orb("gen_image_alias_for_doc", array(
						"img_id" => $arr["obj_inst"]->id(),
						"close" => true,
					), CL_IMAGE);
					$prop["value"] .= "&nbsp;&nbsp;
						<script language=\"javascript\">
						function getDocID()
						{
							doc_id = 0;
							q = window.parent.location.href;
							ar = new Array();
							ar = q.split('&');
							for(i=0;i<ar.length;i++)
							{
								pair = ar[i].split('=');
								if(pair[0]=='doc')
								{
									doc_url = pair[1];
									break;
								}
							}
							ar = doc_url.split('%26');
							for(i=0;i<ar.length;i++)
							{
								pair=ar[i].split('%3D');
								if(pair[0]=='id')
								{
									doc_id = pair[1];
									break;
								}
							}
							return doc_id;
						}
						if (window.parent.name == \"InsertAWImageCommand\")
						{
							aw_url = '".$alias_url."';
							url = aw_url + '&doc_id=' + getDocID();
							document.write(\"<a href='\"+url+\"' onClick='submit_changeform();FCK=window.parent.opener.FCK;var eSelected = FCK.Selection.GetSelectedElement() ;if (\\\"\\\"+eSelected == \\\"HTMLImageElement\\\") { eSelected.src=\\\"$image_url\\\"; } else { ct=aw_get_url_contents(\\\"$url\\\"); FCK.InsertHtml(ct); } '>Insert into document</a>\");
						}
					</script>
					";
				}
				break;
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
			case "transl":
				$this->trans_save($arr, $this->trans_props);
				break;

			case "newwindow":
			case "no_print":
				$retval = PROP_IGNORE;
				break;

			case "show_conditions":
				$arr['obj_inst']->set_prop("newwindow",isset($prop["value"]["newwindow"]) ? 1 : 0);
				$arr['obj_inst']->set_prop("no_print",isset($prop["value"]["no_print"]) ? 1 : 0);
				break;


			case "file":
			case "file2":
				$src_file = $ftype = "";
				$oldfile = $arr["obj_inst"]->prop($prop["name"]);
				if (!empty($prop["value"]["tmp_name"]))
				{
					// this happens if for example releditor is used
					$src_file = $prop["value"]["tmp_name"];
					$ftype = $prop["value"]["type"];
					// I'm not quite sure how the type can be empty, but the code was here before,
					// so it must be needed
					if (empty($ftype))
					{
						$ftype = "image/jpg";
					};
				};

				if (is_uploaded_file($_FILES[$prop["name"]]["tmp_name"]))
				{
					// this happens if file is uploaded from the image class directly
					$src_file = $_FILES[$prop["name"]]["tmp_name"];
					$ftype = $_FILES[$prop["name"]]["type"];
				};

				// if a file was found, then move it to wherever it should be located
				if (is_uploaded_file($src_file))
				{
					$_fi = get_instance(CL_FILE);
					$final_name = $_fi->generate_file_path(array(
						"type" => $ftype,
					));
				
					move_uploaded_file($src_file, $final_name);

					if (function_exists("exif_read_data"))
					{
						$dat = exif_read_data($final_name);
						$dt = $dat["DateTime"];
						$dt = strptime($dt, "%Y:%m:%d %H:%M:%S");
						$this->_set_dt = $dt;
					}
					// get rid of the old file
					if (file_exists($oldfile))
					{
						@unlink($oldfile);
					}
					if ($arr["obj_inst"]->name() == "")
					{
						if ($prop["value"]["name"] != "")
						{
							$arr["obj_inst"]->set_name($prop["value"]["name"]);
						}
						else
						{
							$arr["obj_inst"]->set_name($_FILES[$prop["name"]]["name"]);
						}
					}
					$prop["value"] = $final_name;
				}
				else
				{
					$retval = PROP_IGNORE;
				};
				break;

			case "date_taken":
				if ($this->_set_dt)
				{
					$prop["value"] = $this->_set_dt;
					$arr["obj_inst"]->set_prop("date_taken", $prop["value"]);
					return PROP_OK;
				}
				break;

			case "file2_del":
				if ($prop["value"] == 1)
				{
					$oldfile = $arr["obj_inst"]->prop("file2");
					if (file_exists($oldfile))
					{
						@unlink($oldfile);
					};
					$arr["obj_inst"]->set_prop("file2","");
				};
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

	/** Adds an image to the system

		@attrib name=orb_name params=[name|pos] default="0" nologin="1" api=1 all_args=1 caption="foo" is_public=1

		@param from required type=string 
			Method how image is passed to the function. Options: [file|string|url]
		@param str optional type=string
			if from value is "string", then this is the file content
		@param file optional type=string
			if from is file, then this is the filename for file content
		@param url optional type=string
			if from is url, then this is the url for file, will be downloaded
		@param orig_name optional type=string
			the original name of the file, used as the object name
		@param parent required type=int
			the folder where to save the image
		@param id optional type=int
			the id of the image to change

		@errors 
			none			

		@returns 
			array with image data (image object id, image url and image size)

		@comment 
			none

		@examples
			none
	**/
	function add_image($arr)
	{
		extract($arr);
		if ($from == "file")
		{
			$str = $this->get_file(array("file" => $file));
		}

		if ($from == "url" && !empty($url))
		{
			$str = file_get_contents($url); // since php 4.3.0 
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

		$_fi = get_instance(CL_FILE);
		$mime = get_instance("core/aw_mime_types");
		$fl = $_fi->_put_fs(array(
			"type" => $mime->type_for_file($orig_name),
			"content" => $str
		));

		$this->db_query("UPDATE images SET file = '$fl' WHERE id = '$oid'");
		$sz = getimagesize($fl);
		return array("id" => $oid,"url" => $this->get_url($fl), "sz" => $sz);
	}


	/** Resizes picture

		@attrib name=resize_picture params=name api=1

		@param id required type=int
			image id 
		@param file required type=string

		@param width required type=int
			new width of the picture
		@param height required type=int
			new height of the picture
		@errors 
			none

		@returns 
			none

		@comment 
			after resizing picture converts all pictures to JPG format!
		@examples
			none

	**/
	function resize_picture(&$arr)
	{
		$im = $this->get_image_by_id($arr["id"]);
		$file = $arr['file'];

		$img = get_instance("core/converters/image_convert");
		$fn = basename($im[$file]);
		$fn = $this->cfg["site_basedir"]."/files/".$fn{0}."/".$fn;
		$img->load_from_file($fn);
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
			'file' => $fn,
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
		
		if ($this->_set_dt)
		{
			$arr["obj_inst"]->set_prop("date_taken", $this->_set_dt);
			$arr["obj_inst"]->save();
		}

		$this->do_apply_gal_conf(obj($arr["id"]), $prop["value"]);
	}


	/** Adding comment 
		
		@attrib name=submit_comment params=name nologin="1" api=1
		
		@param id required type=int
		@param comments optional type=int
		
		@returns
			URL to the big image view
	**/
	function submit_comment($arr)
	{
		// Submitted new comment
		if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($arr['image_comm']) && is_array($arr['image_comm']))
		{
			$img = $arr['image_comm']['obj_id'];
			$comment = $arr['image_comm']['comment'];
			$o_img = is_oid($img) ? obj($img) : null;
			if (is_object($o_img) && $o_img->class_id() == CL_IMAGE && $this->can("view", $img) && !empty($comment))
			{
				// Store comment
				classload("vcl/comments");
				$comm = get_instance(CL_COMMENT);
				$added = $comm->submit(array(
					'parent' => $img,
					'commtext' => htmlspecialchars($comment),
					'return' => "id",
				));
				return $this->mk_my_orb(CL_IMAGE, array(
					'comments' => 1,
					'id' => $img,
					'action' => "show_big",
				));
			}
			
		}
		return (aw_global_get("HTTP_REFERER"));
		
	}

	/** Shows the big image 
		
		@attrib name=show_big params=name nologin="1" api=1
		
		@param id required type=int
		@param comments optional type=int

	**/
	function show_big($arr)
	{
		// Defaults
		$comments = 0;
		$parse = "minimal"; // name of SUB in template
		extract($arr);
	
		$im = $this->get_image_by_id($id);
		$imo = obj($id);
		$this->read_any_template("show_big.tpl");
		lc_site_load("image", &$this);
		if ($this->can("view", $imo->prop("big_flash")))
		{
			$fli = get_instance(CL_FLASH);
			$this->vars(array(
				"FLASH" => $fli->view(array("id" => $imo->prop("big_flash")))
			));
		}
		else
		{
			if (empty($im['meta']['file2']) || !is_file($im['meta']['file2']))
			{
				$img_url = $im['url']; // Revert to small image
			}
			else
			{
				$img_url = $this->get_url($im["meta"]["file2"]);
			}
			$this->vars(array(
				"big_url" => $img_url,
			));
			$this->vars(array(
				"IMAGE" => $this->parse("IMAGE")
			));
		}
		if ($comments)
		{
			$parse = "with_comments";
			classload("vcl/comments");
			$comments = new comments();
			$ret_list = $comments->init_vcl_property(array(
				'property' => array(
					'name' => "image_comm",
					"no_form" => 1,
					'no_heading' => 1,
					'sort_by' => "created desc", // Newer first
				),
				'obj_inst' => obj($id),
			));
			//$ret_form = array();
			$ret_form = $comments->init_vcl_property(array(
				'property' => array(
					'name' => "image_comm",
					"only_form" => true,
					'no_heading' => 1,
					'textarea_cols' => 30,
					'textarea_rows' => 5,
				),
				'obj_inst' => obj($id),
			));
			$ret_form += array(
				'submitbtn' => array(
					'type' => "submit",
					'value' => t("Lisa"),
				),
			);
			classload("cfg/htmlclient");
			$hc_inst = new htmlclient(array(
				'template' => "real_webform",
			));
			foreach (($ret_form + $ret_list) as $el)
			{
				$hc_inst->add_property($el);
			}
			$hc_inst->finish_output(array(
				'action' => 'submit_comment',
				'data' => array('orb_class' => 'image'),
			));
			$out = $hc_inst->get_result(array('form_only' => true));
		
			$this->vars(array(
				'comments'=> $out,
			));
		}

		if ($this->is_template("NEXT_LINK"))
		{
			$images = new object_list(array(
				"class_id" => CL_IMAGE,
				"parent" => $im["parent"],
				"sort_by" => "objects.jrk,objects.created desc",
				"lang_id" => array(),
				"site_id" => array()
			));
			foreach($images->ids() as $im_id)
			{
				if ($set_next)
				{
					$this->vars(array(
						"next_url" => aw_url_change_var("id", $im_id)
					));
					$this->vars(array(
						"NEXT_LINK" => $this->parse("NEXT_LINK")
					));
					break;
				}
				if ($im_id == $id)
				{
					if ($prev)
					{
						$this->vars(array(
							"prev_url" => aw_url_change_var("id", $prev)
						));
						$this->vars(array(
							"PREV_LINK" => $this->parse("PREV_LINK")
						));
					}
					$set_next = true;
				}
				$prev = $im_id;
			}
		}
		if (!$this->is_template($parse))
		{
			die($this->parse());	
		}
		die($this->parse($parse));
	}

	/**  
		
		@attrib name=show_small params=name nologin="1" 
		
		@param id required type=int
		
		@returns
		
		
		@comment

	**/
	function show_small($arr)
	{
		extract($arr);
		$im = obj($id);
		$this->read_any_template("show_big.tpl");
		$this->vars(array(
			"big_url" => $this->get_url($im->prop("file")),
		));
		die($this->parse());
	}

	function request_execute($obj)
	{
		$this->show(array(
			"file" => basename($obj->prop("file"))
		));
	}


	/** Get image url by image id

		@attrib name=get_url_by_id params=pos api=1 

		@param id required type=int
			Image object's id
		@errors 
			none

		@returns 
			empty value if the image object has no view access, url to the image othervise

		@comment 
			none

		@examples
			none
	**/
	function get_url_by_id($id)
	{
		if (!$this->can("view", $id))
		{
			return "";
		}
		$o = obj($id);
		$url = $this->get_url($o->prop("file"));
		return $this->check_url($url);
	}

	function _get_conf_for_folder($pt, $apply_image = false)
	{
		if (!is_oid($pt) || !$this->can("view", $pt))
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

	/** Apply gallery conf to an image

		@attrib name=do_apply_gal_conf params=pos api=1 

		@param o required type=object
			Image object
		@errors 
			none

		@returns 
			none

		@comment 
			Applies the gallery configuration to an image. Gallery configuration is set to the image's parent.

		@examples
			none
	**/
	function do_apply_gal_conf($o)
	{
		if ($o->prop("no_apply_gal_conf"))
		{
			return;
		}
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

	/** Resizes images as conf says

		@attrib name=do_resize_image params=name api=1 

		@param o required type=object
			Image object
		@param conf required type=object
			Gallery configuration object

		@errors 
			none

		@returns 
			none

		@comment 
			Applies the gallery configuration to an image. Gallery configuration is set to the image's parent.

		@examples
			none
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
					$f = get_instance(CL_FILE);
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
				$f = get_instance(CL_FILE);
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
			// if controller is set, let it do it's thing
			if ($this->can("view", $conf->prop("controller")))
			{
				$ctr = obj($conf->prop("controller"));
				$ctr_i = $ctr->instance();
				$ctr_i->eval_controller_ref($ctr->id(), $conf, $smallf, $smallf);
			}
		}

	}

	/** Resizes images in filesystem

		@attrib name=do_resize_image_in_fs params=pos api=1 

		@param file required type=string
			Image file
		@param conf required type=object
			Gallery configuration object
		@param prefix required type=string
			
	
		@errors 
			none

		@returns 
			none

		@comment 
			none

		@examples
			none
	**/
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
			if ($conf->prop("resize_before_crop"))
			{
				if ($i_width != $xyd["si_width"] || $i_height != $xyd["si_height"])
				{
					$img->resize_simple($xyd["width"], $xyd["height"]);
				}
				$img->resize(array(
					"x" => $xyd["si_left"],
					"y" => $xyd["si_top"],
					"width" => $xyd["si_width"],
					"height" => $xyd["si_height"],
					"new_width" => $xyd["si_width"],
					"new_height" => $xyd["si_height"]
				));
			}
			else
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
		}
		else
		if ($xyd["width"] != $i_width || $xyd["height"] != $i_height)
		{
			$img->resize_simple($xyd["width"], $xyd["height"]);
		}
		
		$gv = get_instance(CL_GALLERY_V2);
		$img = $gv->_do_logo($img, $conf, $prefix);

		$img->save($file, IMAGE_JPEG);
	}

	/** Composes img tag with a link to the big image

		@attrib name=make_img_tag_wl params=pos api=1 

		@param id required type=int
			Image object's id
		@param alt optional type=string default=NULL
			Images alternate text
		@param has_big_alt optional type=string default=NULL
			If big image is set, then this is the big image's alternate text.	

		@param size optional type=array
			array(
				height => int,
				width => int
			)
			sets img tag height and width	

		@errors 
			none

		@returns 
			HTML image tag, with link when big image is set

		@comment 
			none

		@examples
			none
	**/
	function make_img_tag_wl($id, $alt = NULL, $has_big_alt = NULL, $size = array())
	{
		$that = get_instance(CL_IMAGE);
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
			$imagetag = image::make_img_tag($u, $alt, $size);

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
			$imagetag = image::make_img_tag($u, $alt, $size);
		}

		return $imagetag;
	}

	/** Composes javascript onClick code to open big image in popup window

		@attrib name=get_on_click_js params=pos api=1 

		@param id required type=int
			Image object's id
	
		@errors 
			none

		@returns 
			Empty value when big image is not set
			javascript onclick code to open big image in popup window

		@comment 
			none

		@examples
			none
	**/
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
					$cv->load_from_file($this->_mk_fn($arr["obj_inst"]->prop($prop)));
					if ($cv->is_error())
					{
						$ret = false;
					}
				}
			}
			return $ret;
		}

		if ($arr["id"] == "transl" && aw_ini_get("user_interface.content_trans") != 1)
		{
			return false;
		}
		return true;
	}

	function callback_get_transl($arr)
	{
		return $this->trans_callback($arr, $this->trans_props);
	}

	function _mk_fn($fn)
	{
		$ret = basename($fn);
		return aw_ini_get("site_basedir")."/files/".$ret{0}."/".$ret;
	}

	/**
		@attrib name=fetch_image_tag_for_doc params=name 

		@param id required type=int
	**/
	function fetch_image_tag_for_doc($arr)
	{
		$s = $this->parse_alias(array("alias" => array("target" => $arr["id"])));
		die($s["replacement"]);
	}
	
	/**
		@attrib name=gen_image_alias_for_doc params=name
		@param img_id required type=int
		@param doc_id optional 
		@param close optional type=bool
	**/
	function gen_image_alias_for_doc($arr)
	{
		$close = "<script language=\"javascript\">javascript:window.parent.close();</script>";
		if (!is_oid($arr["doc_id"]))
		{
			die($close);
		}
		$c = new connection();
		$c->load(array(
			"from" => $arr["doc_id"],
			"to" => $arr["img_id"],
		));
		$c->save();
		$out = $arr["close"]?$close:$c->id();
		die($out);
	}

	function do_db_upgrade($t, $f)
	{
		switch($f)
		{
			case "aw_date_taken":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}
}
?>
