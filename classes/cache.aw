<?php
// $Header: /home/cvs/automatweb_dev/classes/cache.aw,v 2.44 2006/01/31 15:25:59 kristo Exp $

// cache.aw - klass objektide cachemisex. 
// cachet hoitakse failisysteemis, kataloogis, mis peax olema defineeritud ini muutujas cache.page_cache
// cache kasutamist kontrollib muutuja USE_PAGE_CACHE
// cachetakse ainult mitte sisse-loginud kasutajatele

class cache extends core
{
	function cache()
	{
		$this->db_init();
		aw_config_init_class(&$this);
	}
	
	////
	// !kirjutab cachesse
	// $oid - objekti id, mille kohta cache tehakse
	// $arr - array objekti kuju identivatest parameetritest (periood ntx), millest moodustatakse cache faili nimi.
	// $content - cachetav asi
	// $clear_flag - kui see on false, siis ei clearita cache_dirty flagi sellele objektile
	//               idee on selles, et siis saab yhele objektile ka mitu cachet teha.
	function set($oid,$arr,$content,$clear_flag = true, $real_section = NULL)
	{
		if ($real_section === NULL)
		{
			$real_section = $oid;
		}
		if ($this->cfg["use_page_cache"] && !aw_global_get("uid") && !aw_global_get("no_cache"))
		{
			$fname = "/".str_replace("/","_",$oid);
			foreach($arr as $v)
			{
				$fname.="-".str_replace("/","_",str_replace(" ","_",$v));
			}

			if (strlen($fname) > 100)
			{
				$fname = "/".md5($fname);
			}

			$this->file_set_pt_oid("html", $real_section, $fname, $content);

			if ($clear_flag)
			{
				$this->clear_cache($real_section, $fname);
			}
		}
	}

	////
	// !tshekib et kas objekt on cachetud ja kas cachemist yldse kasutatakse. 
	// kui kasutatakse ja objekt on olemas, siis tagastab objekti cache
	// kui ei, siis false
	// $oid - objekti id, mille kohta cahet kysitaxe
	// $arr - array objekti kuju identivatest parameetritest (periood ntx), millest moodustatakse cache faili nimi.
	function get($oid,$arr, $real_oid = NULL)
	{
		if ($real_oid === NULL)
		{
			$real_oid = $oid;
		}
		if ($this->cfg["use_page_cache"] && !aw_global_get("uid"))
		{
			$fname = "/".str_replace("/","_",$oid);
			foreach($arr as $v)
			{
				$fname.="-".str_replace("/","_",str_replace(" ","_",$v));
			}

			if (strlen($fname) > 100)
			{
				$fname = "/".md5($fname);
			}

			if ($this->cache_dirty($real_oid, $fname))
			{
				return false;
			}
			else
			{
				return $this->file_get_pt_oid("html", $real_oid, $fname);
			}
		}
		else
		{
			return false;
		}
	}

	function file_set($key,$value)
	{
		if ($this->cfg["page_cache"] != "")
		{
			$fname = $this->cfg["page_cache"];

			$hash = md5($key);
			// make 3-level folder structure
			$fname .= "/".$hash{0};
			if (!is_dir($fname))
			{
				@mkdir($fname, 0777);
				chmod($fname, 0777);
			}

			$fname .= "/$key";
			$this->put_file(array("file" => $fname, "content" => $value));
			@chmod($fname, 0666);
		}
	}

	function file_get($key)
	{
		if ($this->cfg["page_cache"] == "")
		{
			return false;
		}

		return $this->get_file(array(
			"file" => $this->get_fqfn($key)
		));
	}

	function get_fqfn($key)
	{
		$hash = md5($key);
		//return $this->cfg["page_cache"]."/".$hash{0}."/".$hash{1}."/".$hash{2}."/".$key;
		if ($key{0} == "/")
		{
			return $this->cfg["page_cache"]."/".$hash{0}.$key;
		}
		else
		{
			return $this->cfg["page_cache"]."/".$hash{0}."/".$key;
		}
	}

	function file_get_ts($key, $ts)
	{
		if ($this->cfg["page_cache"] == "")
		{
			return false;
		}
		$fqfn = $this->get_fqfn($key);
		if ((@filemtime($fqfn)) < $ts)
		{
			return false;
		}

		return $this->get_file(array("file" => $fqfn));
	}

	function file_invalidate($key)
	{
		global $awt;
		if ($this->cfg["page_cache"] != "")
		{
			@unlink($this->get_fqfn($key));
		}
	}

	function file_get_incl_pt_oid($pt, $oid, $fn)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".substr($oid, -1, 1)."/".$fn;
		if (!file_exists($fq))
		{
			return false;
		}
		include($fq);
		return $arr;
	}

	function file_get_incl_pt($pt, $subf, $fn)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".$subf."/".$fn;
		if (!file_exists($fq))
		{
			return false;
		}
		include($fq);
		return $arr;
	}

	function file_set_incl_pt_oid($pt, $oid, $fn, $dat)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".substr($oid, -1, 1)."/".$fn;

		$str = "<?php\n";
		$str .= aw_serialize($dat, SERIALIZE_PHP_FILE);
		$str .= "?>";
		
		return $this->file_set_pt($pt, substr($oid, -1, 1), $fn, $str);
	}

	function file_set_incl_pt($pt, $subf, $fn, $dat)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".$subf."/".$fn;

		$str = "<?php\n";
		$str .= aw_serialize($dat, SERIALIZE_PHP_FILE);
		$str .= "?>";
		
		return $this->file_set_pt($pt, $subf, $fn, $str);
	}

	function file_set_pt_oid($pt, $oid, $fn, $cont)
	{
		//echo "file_set_pt_oid pt = $pt ,oid =  $oid, fn = $fn <br>";
		return $this->file_set_pt($pt, substr($oid, -1, 1), $fn, $cont);
	}

	function file_get_pt_oid($pt, $oid, $fn)
	{
		//echo "file_get_pt_oid pt = $pt ,oid =  $oid, fn = $fn <br>";
		return $this->file_get_pt($pt, substr($oid, -1, 1), $fn);
	}

	function file_get_pt_oid_ts($pt, $oid, $fn, $ts)
	{
		//echo "file_get_pt_oid_ts pt = $pt ,oid =  $oid, fn = $fn, ts = $ts <br>";
		return $this->file_get_pt_ts($pt, substr($oid, -1, 1), $fn, $ts);
	}

	function file_set_pt($pt, $subf, $fn, $cont)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".$subf."/".$fn;
		$f = fopen($fq, "w");
		if (!$f)
		{
			error::raise(array(
				"id" => "ERR_CACHE_FILE",
				"msg" => sprintf(t("cache::file_set_pt(%s, %s, %s): could not open file %s for writing!"), $pt, $subf, $fn, $fq)
			));
		}
		fwrite($f, $cont);
		fclose($f);
		@chmod($fname, 0666);
	}

	function file_get_pt($pt, $subf, $fn)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".$subf."/".$fn;
		$f = fopen($fq, "r");
		if (!$f)
		{
			return false;
		}
		$ret = fread($f, filesize($fq));
		fclose($f);
		return $ret;
	}

	function file_get_pt_ts($pt, $subf, $fn, $ts)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".$subf."/".$fn;

		if ((@filemtime($fq)) < $ts)
		{
			return false;
		}

		$f = fopen($fq, "r");
		if (!$f)
		{
			return false;
		}
		$ret = fread($f, filesize($fq));
		fclose($f);
		return $ret;
	}

	function file_clear_pt($pt)
	{
		// now, this is where the magic happens. 
		// basically, we rename the whole folder and clear it's contents later. 
		$fq = $this->cfg["page_cache"]."/".$pt;
		$nn = $this->cfg["page_cache"]."/temp/".$pt."_".gen_uniq_id();

		if (!rename($fq, $nn))
		{
			error::raise(array(
				"id" => "ERR_CACHE_CLEAR",
				"msg" => sprintf(t("cache::file_clear_pt(%s): could not rename %s to %s!"), $pt, $fq, $nn)
			));
		}

		$this->_crea_fld($pt);
	}

	function file_clear_pt_oid($pt, $oid)
	{
		$of = substr($oid, -1, 1);
		$fq = $this->cfg["page_cache"]."/".$pt."/".$of;
		$nn = $this->cfg["page_cache"]."/temp/".$pt."_".$of."_".gen_uniq_id();

		if (!rename($fq, $nn))
		{
			error::raise(array(
				"id" => "ERR_CACHE_CLEAR",
				"msg" => sprintf(t("cache::file_clear_pt_oid(%s, %s): could not rename %s to %s!"), $pt, $oid, $fq, $nn)
			));
		}

		// recreate
		mkdir($fq, 0777);
		chmod($fq, 0777);
	}

	function file_clear_pt_oid_fn($pt, $oid, $fn)
	{
		$of = substr($oid, -1, 1);
		$fq = $this->cfg["page_cache"]."/".$pt."/".$of."/".$fn;

		// here we know the full path to the file, so just delete the damn thing
		@unlink($fq);
	}

	function file_clear_pt_sub($pt, $subf)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".$subf;
		$nn = $this->cfg["page_cache"]."/temp/".$pt."_".$subf."_".gen_uniq_id();

		if (!rename($fq, $nn))
		{
			error::raise(array(
				"id" => "ERR_CACHE_CLEAR",
				"msg" => sprintf(t("cache::file_clear_pt_sub(%s, %s): could not rename %s to %s!"), $pt, $sub, $fq, $nn)
			));
		}

		// recreate
		mkdir($fq, 0777);
		chmod($fq, 0777);
	}

	function _crea_fld($f)
	{
		$fq = $this->cfg["page_cache"]."/".$f;
		if (!mkdir($fq, 0777))
		{
			error::raise(array(
				"id" => "ERR_NO_FOLD",
				"msg" => sprintf(t("cache::_crea_fld(%s): could not create folder %s"), $f, $fq)
			));
			die();
		}

		chmod($fq, 0777);
		for($i = 0; $i < 16; $i++)
		{
			$ffq = $fq ."/".($i < 10 ? $i : chr(ord('a') + ($i- 10)));
			if (!mkdir($ffq, 0777))
			{
				error::raise(array(
					"id" => "ERR_NO_FOLD",
					"msg" => sprintf(t("cache::_crea_fld(%s): could not create folder %s"), $f, $ffq)
				));
				die();
			}
			chmod($ffq, 0777);
		}
	}

	// fname = fully qualified file name minus basedir
	// unserializer = reference in form of array("classname","function") to the unserializer function
	function get_cached_file($args = array())
	{
		extract($args);

		// now calculate fqfn
		// XXX: is stripping dots good enough?
		$pathinfo = pathinfo($args["fname"]);
		$dirname = str_replace(".","",$pathinfo["dirname"]);

		$fqfn = $this->cfg["basedir"] . $dirname . "/" . $pathinfo["basename"];
		
		// this is all nice and good, but I need a way to load files from the
		// site directory as well. 

		if (is_file($fqfn) && is_readable($fqfn))
		{
			// figure out the cache id for the file
			// xml/properties/search.xml becomes properties_search.cache
			// xml/orb/search.xml becomes orb_search.cache
			$prefix = substr($dirname,strrpos($dirname,"/")+1);
			if (strlen($prefix) == 0)
			{
				// could not calculate a valid cache_id, bail out
				return false;
			};
			$cache_id = $prefix . "_" . $pathinfo["basename"] . ".cache";
		}
		else
		{
			// no source file, bail out

			// an idea to consider, perhaps we _should_ have a way to 
			// work only with serialized files? 
			return false;
		};

		$cachedir = $this->cfg["page_cache"];

		$cachefile = $cachedir . "/" . $cache_id;
			
		//if (!is_writable($cachedir))
		//{
			// cannot write cache, bail out
			// OTOH this is not a fatal error, we can still work without cache
		//	return false;
		//}

		// now get mtime for both files, source and cache
		$source_mtime = @filemtime($fqfn);
		$cache_mtime = @filemtime($cachefile);

		// get the cache contents here, so we can check whether it is empty, cause for some weird reason 
		// cache files get to be empty sometimes, damned if I know why

		$src = $this->get_file(array(
			"file" => $cachefile,
		));

		if (($source_mtime > $cache_mtime) || (strlen($src) < 1))
		{
			//print "need to reparse<br />";
			// 1) get an instance of the unserializer class,
			
			$clobj = &$args["unserializer"][0];
			$clmeth = $unserializer[1];
			if (is_object($clobj) && method_exists($clobj,$clmeth))
			{
				// 2) get the contents of the source file
				$contents = $this->get_file(array("file" => $fqfn));
				// 3) pass them to unserializer
				$result = $clobj->$clmeth(array(
					"fname" => $fqfn,
					"content" => $contents,
				));
			};
			$clobj = &$args["loader"][0];
			$clmeth = $loader[1];
			if (is_object($clobj) && method_exists($clobj,$clmeth))
			{
				$clobj->$clmeth(array("data" => $result));
			};
			if (is_writable($cachedir))
			{
				$ser_res = aw_serialize($result,SERIALIZE_PHP);
				$this->put_file(array(
					"file" => $cachefile,
					"content" => $ser_res,
				));
				chmod($cachefile, 0666);
			};
			// Now I somehow need to retrieve the results of unserialization
			// and write them out to the file
			// 4) aquire reference to results
		}
		else
		{
			// 1) get the contents of cached file
			// 2) awunserialize the data
			
			$clobj = &$args["loader"][0];
			$clmeth = $loader[1];
			if (is_object($clobj) && method_exists($clobj,$clmeth))
			{
				$clobj->$clmeth(array("data" => aw_unserialize($src)));
			};
		};

	}

	////
	// invalidates all cache files that match pcre regex $regex
	// returns the number of caches that were invalidated

	// this can be really dangerous, we don't check whether the file is actually
	// in the correct directory. 

	// why preg_match is bad?
	// [preg_match] => 2.6577 (14.42%)
	// [counter_preg_match] => 26608
	function file_invalidate_regex($regex)
	{
		if (aw_global_get("no_cache_flush") == 1)
		{
			return;
		}
		$this->__fir_cnt = 0;
		$this->cache_files = aw_cache_get_array("cache_files");
		global $awt;
		if (!is_array($this->cache_files))
		{
			$this->cache_files = array();
			// XXX: this is really slow. Check whether files could be
			// scattered into directories more intelligently
			$awt->start("gather-cache-files");
			$this->_get_cache_files($this->cfg["page_cache"]);
			$awt->stop("gather-cache-files");
			sort($this->cache_files);
			//aw_cache_set_array("cache_files",$this->cache_files);
		}
		//$is_flushed = aw_cache_get("flush_cache",$regex);
		//if (!$is_flushed)
		//{
			$this->_file_inv_re_req($this->cfg["page_cache"], $regex);
			//aw_cache_set("flush_cache",$regex,true);
		//};
		return $this->__fir_cnt;
	}

	function _get_cache_files($fld)
	{
		if ($dir = @opendir($fld))
		{
			while (($file = readdir($dir)) !== false) 
			{
				if (!($file == "." || $file == ".."))
				{
					if (is_dir($fld."/".$file))
					{
						$this->_get_cache_files($fld."/".$file);
					}
					else
					{
						$this->cache_files[] = $file;
						$this->cache_files2[] = $fld."/".$file;
					};
				};
			};
		}
	}


	function _file_inv_re_req($fld, $regex)
	{
		// we don't really need regular expressions here, do we?
		// simple string comparision is more than enough
		$r_regex = str_replace("(.*)","",$regex);
		$r_regex = str_replace(".*","",$r_regex);

		// basic binary search, finds first matching element from the cache_files array
		// it's a lot faster than a linear search
		$high = count($this->cache_files);
		$low = 0;
		$nlen = strlen($r_regex);
		
		while ($high - $low > 1)
		{
			$probe = (int)(($high + $low) / 2);
			if (substr($this->cache_files[$probe],0,$nlen) < $r_regex)
			{
			   $low = $probe;
			}
			else
			{
			   $high = $probe;
			}
		}
		
		if ($high == count($this->cache_files) || @strpos($this->cache_files[$high],$r_regex) === false)
		{
			$lookfor = false;
		}
		else
		{
			$lookfor = $high;
		}


		// this gives the position of first item in the list that matches the "regex"
		if (!empty($lookfor))
		{
			// delete all matching cache files
			while(substr($this->cache_files[$lookfor],0,$nlen) == $r_regex)
			{
				$this->file_invalidate($this->cache_files[$lookfor]);
				$lookfor++;
			};
		};
	}

	/** returns the timestamp of the last modified object. caches it as well.

		@attrib api=1

	**/
	function get_objlastmod()
	{
		static $last_mod;
		if (!$last_mod)
		{
			if (($last_mod = $this->file_get("objlastmod")) === false)
			{
				$add = "";
				if (aw_ini_get("site_show.objlastmod_only_menu"))
				{
					$add = " WHERE class_id = ".CL_MENU;
				}
				$last_mod = $this->db_fetch_field("SELECT MAX(modified) as m FROM objects".$add, "m");
				$this->file_set("objlastmod", $last_mod);
			}
		}
		return $last_mod;
	}

	/** completely clears the cache

		@attrib api=1

	**/
	function full_flush()
	{
		if (aw_global_get("no_cache_flush") == 1)
		{
			return;
		}
		$this->cache_files = array();
		$this->cache_files2 = array();
		$this->_get_cache_files(aw_ini_get("cache.page_cache"));

		foreach($this->cache_files2 as $file)
		{
			@unlink($file);
		}
	}
};
?>
