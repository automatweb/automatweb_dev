<?php
define("FL_IMAGE_CAN_COMMENT", 1);
class image_obj extends _int_object
{
	function set_prop($k, $v)
	{
		if($k == "file" || $k == "file2")
		{
			parent::set_meta("old_file", parent::prop("file"));
		}
		return parent::set_prop($k, $v);
	}

	/** Creates HTML image tag
	@attrib name=view nologin="1" 
	@returns
		HTML image tag
	**/
	public function get_html()
	{
		$idata = $this->get_image_data();
		$GLOBALS["object_loader"]->cache->mk_path($idata["parent"],"Vaata pilti");
		$retval = html::img(array(
			"url" => $idata["url"],
			'height' => (isset($args['height']) ? $args['height'] : NULL),
		));
		return $retval;
	}

	/** Get image url
		@attrib api=1 
		@errors 
			none
		@returns 
			empty value if the image object has no view access, url to the image othervise
	**/
	public function get_url()
	{
		$url = $this->fix_url($this->prop("file"));
		return $this->check_url($url);
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
	**/
	private function fix_url($url) 
	{
		if ($url)
		{
			if (aw_ini_get("image.imgbaseurl") != "")
			{
				$imgbaseurl = aw_ini_get("image.imgbaseurl");
				$first = substr(basename($url),0,1);
				if (substr($imgbaseurl, 0, 4) == "http")
				{
					$url = $imgbaseurl . "/" . $first . "/" . basename($url);
				}
				else
				{
					$url = aw_ini_get("baseurl") . $imgbaseurl . "/" . $first . "/" . basename($url);
				}
			}
			else
			{
				$url = $GLOBALS["object_loader"]->cache->mk_my_orb("show", array("fastcall" => 1,"file" => basename($url)),"image",false,true,"/");
			}
			$retval = str_replace("automatweb/", "", $url);
		}
		else
		{
			$retval = "";
		};
		return $retval;
	}



	/** Creates big image HTML image tag
	@attrib name=view nologin="1" 
	@returns
		HTML image tag
	**/
	public function get_big_html()
	{
		$idata = $this->get_image_data();
		$GLOBALS["object_loader"]->cache->mk_path($idata["parent"],"Vaata pilti");
		$retval = html::img(array(
			"url" => $idata["big_url"],
			'height' => (isset($args['height']) ? $args['height'] : NULL),
		));
		return $retval;
	}

	/** returns image size
	@attrib name=view nologin="1" 
	@returns
		array
	**/
	public function get_size()
	{
		enter_function("image::get_size");
		$idata = $this->get_image_data();
		$size = @getimagesize($idata["file"]);
		exit_function("image::get_size");
		return $size;
	}

	/** 

		@attrib name=get_image_by_id api=1

		@errors 
			none

		@returns 
			- array with image data
			- false if the id parameter is array
			- false if the id parameter is not numeric

		@comment 
			none

		@examples
			$image_object->get_image_data();

	**/
	function get_image_data()
	{
		// it shouldn't be, but it is an array, if a period is loaded
		// from a stale cache.
		if (!($row = aw_cache_get("get_image_by_id",$this->id())))
		{
			$q = "SELECT objects.*,images.* FROM images
				LEFT JOIN objects ON (objects.oid = images.id)
				WHERE images.id = '".$this->id()."'";
			if (method_exists($GLOBALS["object_loader"]->cache, "db_query"))
			{
				$GLOBALS["object_loader"]->cache->db_query($q);
				$row = $GLOBALS["object_loader"]->cache->db_fetch_row();
			};


			if ($row)
			{
				array_walk($row ,create_function('&$arr','$arr=trim($arr);')); 
				$row["url"] = $this->get_url();

				// if the image is from another site, then make the url point to that
				if ($row["site_id"] != aw_ini_get("site_id"))
				{
					$sl = get_instance("install/site_list");
					$row["url"] = str_replace(aw_ini_get("baseurl"), $sl->get_url_for_site($row["site_id"]), $row["url"]);
				}
				$row["meta"] = aw_unserialize($row["metadata"]);
				if (!isset($row["meta"]["big_flash"]))
				{
					$row["meta"]["big_flash"] = null;
				}
				$row["can_comment"] = $row["flags"] & FL_IMAGE_CAN_COMMENT;
				$row["big_url"] = null;
				if (!empty($row["meta"]["file2"]))
				{
					$row["big_url"] = $this->get_url($row["meta"]["file2"]);
					$_tmp = basename($row["meta"]["file2"]);
					$f1 = substr($_tmp,0,1);
					$row["meta"]["file2"] = aw_ini_get("site_basedir") . "/files/$f1/" . $_tmp;
					$row['file2'] = &$row['meta']['file2'];
				}
				aw_cache_set("get_image_by_id", $this->id(), $row);
			}

			$row["name"] = $this->trans_get_val("name");
			$row["comment"] = $this->trans_get_val("comment");
			$row["link"] = $this->trans_get_val("link");
			$row["meta"]["author"] = $this->trans_get_val("author");
			$row["meta"]["alt"] = $this->trans_get_val("alt");
			if ($row["meta"]["alt"] == "" && aw_ini_get("image.default_alt_text_is_name"))
			{
				$row["meta"]["alt"] = $row["name"];
			}
		}
		return $row;
	}




	/** Get big image url
		@attrib api=1 
		@errors 
			none
		@returns 
			empty value if the image object has no view access, url to the image othervise
	**/
	public function get_big_url()
	{
		$url = $this->fix_url($this->prop("file2"));
		return $this->check_url($url);
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
	public static function check_url($url)
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
				$url = aw_ini_get("baseurl") . $imgbaseurl . "/" . $first . "/" . $fname;
				if (substr($url,-11) == "/aw_img.jpg")
				{
					$url = str_replace("/aw_img.jpg","",$url);
				};

			};
		}
		return $url;
	}

	/** Adds big image to image object
		@attrib api=1 params=pos
		@param file required type=string
			file location
		@errors 
			none
	**/
	public function add_image_big($file)
	{
		$_fi = get_instance(CL_FILE);
		$mime = get_instance("core/aw_mime_types");

		$f2 = $_fi->_put_fs(array(
			"type" => $mime->type_for_file(basename($file)),
			"content" => file_get_contents($file)
		));

		$this->set_prop("file2", $f2);
	}

	/** Composes javascript onClick code to open big image in popup window
		@attrib name=get_on_click_js params=pos api=1 
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
	public function get_on_click_js()
	{
		if ($this->prop("file2") == "")
		{
			return "";
		}
		$size = @getimagesize($this->_get_fs_path($this->prop("file2")));
		$bi_show_link = aw_ini_get("baseurl")."/orb.aw?class=image&action=show_big&id=".$this->id();
		return "window.open(\"$bi_show_link\",\"popup\",\"width=".($size[0]).",height=".($size[1])."\");";
	}

	private function _get_fs_path($path)
	{
		if (file_exists($path))
		{
			return $path;
		}
		$tmp = basename($path);
		$tmp = aw_ini_get("site_basedir")."/files/".$tmp[0]."/".$tmp;
		if (file_exists($tmp))
		{
			return $tmp;
		}
		$tmp = dirname($path);
		$slp = strrpos($tmp, "/");
		$tmp = aw_ini_get("site_basedir")."/files/".substr($tmp, $slp)."/".basename($path);
		return $tmp;
	}

	/** Resizes images as conf says
		@attrib name=do_resize_image params=name api=1 
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
			$bigf = $this->prop("file2");

			if (file_exists($bigf))
			{
				$img = get_instance("core/converters/image_convert");
				$img->set_error_reporting(false);
				$img->load_from_file($bigf);
				if ($img->is_error())
				{
					$bigf = false;
				}
			}

			if($this->prop("file") != $this->meta("old_file") && $bigf)
			{
				// If we changed the small file, change the big file also!
				unlink($bigf);
				$bigf = false;
			}

			if (!$bigf)
			{
				// no big file, copy from small file
				$bigf = $this->prop("file");
				if ($bigf)
				{
					$f = get_instance(CL_FILE);
					$bigf = $f->_put_fs(array(
						"type" => "image/jpg",
						"content" => $this->get_file(array("file" => $bigf))
					));
					$this->set_prop("file2", $bigf);
					$this->save();
				}
			}

			if ($bigf)
			{
				// do the actual resize-file thingie
				$this->do_resize_file_in_fs($bigf, $conf, "");
			}
		}

		// now small
		$smallf = $this->prop("file");
		if (!$smallf)
		{
			// do copy-big-to-small
			$smallf = $this->prop("file2");
			if ($smallf)
			{
				$f = get_instance(CL_FILE);
				$smallf = $f->_put_fs(array(
					"type" => "image/jpg",
					"content" => $this->get_file(array("file" => $smallf))
				));
				$this->set_prop("file", $smallf);
				$this->save();
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

	/** Apply gallery conf to an image
		@attrib name=do_apply_gal_conf params=pos api=1 
		@errors 
			none
		@returns 
			none
		@comment 
			Applies the gallery configuration to an image. Gallery configuration is set to the image's parent.
		@examples
			none
	**/
	function do_apply_gal_conf()
	{
		if ($this->prop("no_apply_gal_conf"))
		{
			return;
		}
		$conf = $this->_get_conf_for_folder($this->parent(), true);
		if ($conf)
		{
			// resize image as conf says
			$this->do_resize_image(array(
				"conf" => obj($conf)
			));
		}
	}



}

?>
