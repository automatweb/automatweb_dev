<?php

define("IMAGE_PNG", 1);
define("IMAGE_JPEG", 2);
define("IMAGE_GIF", 3);
define("IMAGE_WBMP", 4);

class image_convert extends class_base
{
	var $driver;

	function image_convert()
	{
		$this->init();

		$driver = $this->_get_driver();

		if ($driver == "")
		{
			$this->raise_error(ERR_IMAGE_DRIVER, t("image_covert: could not detect any supported imagehandlers!"));
		}

		if ($driver != "")
		{
			$this->driver = new $driver;
			$this->dirver->ref =& $this;
		}
	}

	// this is here, because the authors of the php gd module are stupid idiots.
	// there is *NO* safe way of telling which version of gd is installed for all 4.x versions of php
	// except for this.
	function my_gd_info() 
	{
		ob_start();
		eval("phpinfo();");
		$info = ob_get_contents();
		ob_end_clean();

		foreach(explode("\n", $info) as $line)
		{
			if(strpos($line, "GD Version")!==false)
			{
				$ret = trim(str_replace("GD Version", "", strip_tags($line)));
			}
		}
		return $ret;
	}

	function _get_driver()
	{
		$driver = "";

		// detect if gd is available
		if (function_exists("imagecreatetruecolor"))
		{
			// some kind of gd, but check to be sure
			if (function_exists("gd_info"))
			{
				$dat = gd_info();
				if ($dat["JPG Support"] && $dat["PNG Support"] && strpos($dat["GD Version"], "2.") !== false)
				{
					// accept all gd's that can use jpg and png and have version number 2.x
					$driver = "gd";
				}
			}
			else
			{
				// god this sucks
				$ver = $this->my_gd_info();
				if (strpos($ver, "2.") !== false)
				{
					// accept all gd's have version number 2.x
					$driver = "gd";
				}
			}
		}

		if ($driver == "")
		{
			// try to detect imagemagick
			$convert = aw_ini_get("server.convert_dir");
			if (file_exists($convert) && is_executable($convert))
			{
				$driver = "imagick";
			}
		}

		if ($driver == "")
		{
			return "";
		}

		$driver = "_int_image_convert_driver_".$driver;
		return $driver;
	}

	function can_convert()
	{
		return $this->_get_driver() != "" ? true : false;
	}
	
	function load_from_string($str)
	{
		$this->driver->load_from_string($str);
	}

	function load_from_file($str)
	{
		$this->driver->load_from_file($str);
	}

	
	function size()
	{
		return $this->driver->size();
	}

	////
	// !resize
	// x, y, width, height, new_width, new_height
	function resize($arr)
	{
		return $this->driver->resize($arr);
	}

	function resize_simple($width, $height)
	{
		list($w, $h) = $this->size();
		return $this->driver->resize(array(
			"x" => 0, 
			"y" => 0,
			"height" => $h,
			"width" => $w,
			"new_width" => $width,
			"new_height" => $height
		));
	}

	function get($type)
	{
		return $this->driver->get($type);
	}

	function save($filename, $type)
	{
		return $this->driver->save($filename, $type);
	}

	////
	// !returns an instance of this class that has the same image loaded, but any operations on it, will not affect the original image
	function &copy()
	{
		$ic = new image_convert;
		$ic->driver = $this->driver->copy();
		return $ic;
	}

	function destroy()
	{
		$this->driver->destroy();
		unset($this);
	}

	////
	// !merges the current image with the image given as $img
	// $source, $x, $y, $pct
	function merge($img)
	{
		$this->driver->merge($img);
	}

	function set_error_reporting($rep)
	{
		$this->driver->error_rep = $rep;
	}

	function is_error()
	{
		return $this->driver->is_error;
	}
}

class _int_image_convert_driver_gd extends aw_template
{
	var $image;
	var $ref;

	function _int_image_convert_driver_gd()
	{
		$this->error_rep = true;
	}

	function load_from_string($str)
	{
		if (function_exists("imagecreatefromstring"))
		{
			$this->image = imagecreatefromstring($str);
		}
		else
		{
			// save temp file
			$tn = tempnam(aw_ini_get("server.tmpdir"), "aw_img_conv");
			$this->put_file(array(
				"file" => $tn,
				"content" => $str
			));
			$this->load_from_file($tn);
			unlink($tn);
		}

		if (!$this->image)
		{
			if ($this->error_rep == false)
			{
				$this->is_error = true;
			}
			else
			{
				error::raise(array(
					"id" => ERR_IMAGE_FORMAT,
					"msg" => t("image_convert::gd_driver::load_from_string(): could not detect image format!")
				));
			}
		}
	}

	function load_from_file($tn)
	{
		$this->image = @imagecreatefromjpeg($tn);
		if (!$this->image)
		{
			$this->image = @imagecreatefrompng($tn);
		}
		if (!$this->image && function_exists("imagecreatefromgif"))
		{
			$this->image = @imagecreatefromgif($tn);
		}
		if (!$this->image && function_exists("imagecreatefromwbmp"))
		{
			$this->image = @imagecreatefromwbmp($tn);
		}
		if (!$this->image)
		{
			if ($this->error_rep == false)
			{
				$this->is_error = true;
			}
			else
			{
				error::raise(array(
					"id" => ERR_IMAGE_FORMAT,
					"msg" => t("image_convert::gd_driver::load_from_file(): could not detect image format!")
				));
			}
		}
	}

	
	function size()
	{
		return array(imagesx($this->image), imagesy($this->image));
	}

	////
	// !resize
	// x, y, width, htight, new_width, new_height
	function resize($arr)
	{
		$tmpimg = imagecreatetruecolor($arr["new_width"], $arr["new_height"]);
		imagecopyresampled($tmpimg, $this->image,0,0, $arr["x"], $arr["y"], $arr["new_width"], $arr["new_height"], $arr["width"], $arr["height"]);
		imagedestroy($this->image);
		$this->image = $tmpimg;
	}

	function get($type)
	{
		$tn = tempnam(aw_ini_get("server.tmpdir"), "aw_img_conv");
		switch($type)
		{
			case IMAGE_PNG:
				imagepng($this->image, $tn);
				break;
			case IMAGE_JPEG:
				imagejpeg($this->image, $tn);
				break;
			case IMAGE_GIF:
				imagegif($this->image, $tn);
				break;
			case IMAGE_WBMP:
				imagewbmp($this->image, $tn);
				break;
			default:
				$this->raise_error(ERR_IMAGE_TYPE, sprintf(t("image_convert::get(%s): unknown image type!"), $type));
		}
		$content = $this->get_file(array("file" => $tn));
		unlink($tn);
		return $content;
	}

	function save($tn, $type)
	{
		switch($type)
		{
			case IMAGE_PNG:
				imagepng($this->image, $tn);
				break;
			case IMAGE_JPEG:
				imagejpeg($this->image, $tn);
				break;
			case IMAGE_GIF:
				imagegif($this->image, $tn);
				break;
			case IMAGE_WBMP:
				imagewbmp($this->image, $tn);
				break;
			default:
				if ($this->error_rep == false)
				{
					$this->is_error = true;
				}
				else
				{
					$this->raise_error(ERR_IMAGE_TYPE, sprintf(t("image_convert::save(%s,%s): unknown image type!"), $filename,$type));
				}
		}
	}

	function copy()
	{
		$inst = new _int_image_convert_driver_gd;
		$inst->image = imagecreatetruecolor(imagesx($this->image), imagesy($this->image));
		imagecopy($inst->image, $this->image, 0, 0, 0, 0,imagesx($this->image), imagesy($this->image));
		return $inst;
	}

	function destroy()
	{
		imagedestroy($this->image);
		unset($this);
	}

	// $source, $x, $y, $pct
	function merge($arr)
	{
		extract($arr);
		// make transparency as well.	
		$trans = imagecolorat ($source->driver->image, 0, 0);
		imagecolortransparent($source->driver->image, $trans);

		list($w, $h) = $source->size();

		imagecopymerge($this->image, $source->driver->image, $x, $y, 0,0, $w, $h, $pct);
	}
}

class _int_image_convert_driver_imagick extends aw_template
{
	var $filename;
	var $identify;
	var $convert;
	var $copmposite;

	function _int_image_convert_driver_imagick()
	{
		$this->identify = aw_ini_get("server.identify_dir");
		$this->convert = aw_ini_get("server.convert_dir");
		$this->composite = aw_ini_get("server.composite_dir");
		$this->error_rep = true;
	}

	function load_from_string($str)
	{
		// save string to temp file
		$tn = tempnam(aw_ini_get("server.tmpdir"), "aw_img_conv");
		$this->put_file(array(
			"file" => $tn,
			"content" => $str
		));
		$this->filename = $tn;
	}

	function load_from_file($str)
	{
		// copy to temp file
		$str = $this->get_file(array("file" => $str));
		if ($str === false)
		{
			if ($this->error_rep == false)
			{
				$this->is_error = true;
			}
			else
			{
				$this->raise_error(ERR_NO_FILE, sprintf(t("image_convert::load_from_file(%s): no such file!"), $str));
			}
		}
		$this->load_from_string($str);
	}

	
	function size()
	{
		$cmd = $this->identify." -format \"%w %h\" ".$this->filename;
		$op = shell_exec($cmd);
		return explode(" ", $op);
	}

	////
	// !resize
	// x, y, width, height, new_width, new_height
	function resize($arr)
	{
		extract($arr);
		$tn = tempnam(aw_ini_get("server.tmpdir"), "aw_img_conv");
		$cmd = $this->convert." -geometry ".$new_width."x".$new_height."+".$x."+".$y."! ".$this->filename." ".$tn;
		$op = shell_exec($cmd);
		unlink($this->filename);
		$this->filename = $tn;
	}

	function get($type)
	{
		$tn = tempnam(aw_ini_get("server.tmpdir"), "aw_img_conv");
		switch($type)
		{
			case IMAGE_PNG:
				shell_exec($this->convert." ".$this->filename." png:".$tn);
				break;
			case IMAGE_JPEG:
				$cmd = $this->convert." ".$this->filename." jpeg:".$tn;
				$op = shell_exec($cmd);
				break;
			case IMAGE_GIF:
				shell_exec($this->convert." ".$this->filename." gif:".$tn);
				break;
			case IMAGE_WBMP:
				shell_exec($this->convert." ".$this->filename." wbmp:".$tn);
				break;
			default:
				if ($this->error_rep == false)
				{
					$this->is_error = true;
				}
				else
				{
					$this->raise_error(ERR_IMAGE_TYPE, sprintf(t("image_convert::get(%s): unknown image type!"), $type));
				}
		}
		$fc = $this->get_file(array("file" => $tn));
		unlink($tn);
		return $fc;
	}

	function save($filename, $type)
	{
		$tn = $this->filename;
		switch($type)
		{
			case IMAGE_PNG:
				shell_exec($this->convert." ".$tn." png:".$filename);
				break;
			case IMAGE_JPEG:
				shell_exec($this->convert." ".$tn." jpeg:".$filename);
				break;
			case IMAGE_GIF:
				shell_exec($this->convert." ".$tn." gif:".$filename);
				break;
			case IMAGE_WBMP:
				shell_exec($this->convert." ".$tn." wbmp:".$filename);
				break;
			default:
				if ($this->error_rep == false)
				{
					$this->is_error = true;
				}
				else
				{
					$this->raise_error(ERR_IMAGE_TYPE, sprintf(t("image_convert::save(%s, %s): unknown image type!"), $filename,$type));
				}
		}
	}

	////
	// !returns an instance of this class that has the same image loaded, but any operations on it, will not affect the original image
	function &copy()
	{
		$inst = new _int_image_convert_driver_imagick;
		$inst->load_from_string($this->get(IMAGE_PNG));
		return $inst;
	}

	function destroy()
	{
		unlink($this->filename);
		unset($this);
	}

	////
	// !merges the current image with the image given as $img
	// $source, $x, $y, $pct
	function merge($arr)
	{
		extract($arr);
		$tn = tempnam(aw_ini_get("server.tmpdir"), "aw_img_conv");

		$cmd = $this->composite." -geometry +".$x."+".$y." ".$source->driver->filename." ".$this->filename." ".$tn;
		shell_exec($cmd);
		unlink($this->filename);
		$this->filename = $tn;
	}
}

?>
