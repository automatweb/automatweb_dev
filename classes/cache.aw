<?php
// $Header: /home/cvs/automatweb_dev/classes/cache.aw,v 2.36 2004/11/03 14:54:38 kristo Exp $

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
	function set($oid,$arr,$content,$clear_flag = true)
	{
		if ($this->cfg["use_page_cache"] && !aw_global_get("uid") && !aw_global_get("no_cache"))
		{
			$fname = "/".str_replace("/","_",$oid);
			reset($arr);
			while (list(,$v) = each($arr))
			{
				$fname.="-".str_replace("/","_",str_replace(" ","_",$v));
			}
			if (is_array($this->metaref) && (in_array($this->referer,$this->metaref)) )
			{
				$fname .= "-" . substr($this->referer,7);
			}
			if (strlen($fname) > 100)
			{
				$fname = "/".md5($fname);
			}

			$hash = md5($fname);
			$fqfn = $this->cfg["page_cache"]."/".$hash{0};
			if (!is_dir($fqfn))
			{
				mkdir($fqfn, 0777);
				chmod($fqfn, 0777);
			}

			/*
			$fqfn .= "/".$hash{1};
			if (!is_dir($fqfn))
			{
				mkdir($fqfn, 0777);
				chmod($fqfn, 0777);
			}

			$fqfn .= "/".$hash{2};
			if (!is_dir($fqfn))
			{
				mkdir($fqfn, 0777);
				chmod($fqfn, 0777);
			}
			*/

			
			$fname = $fqfn.$fname;
			$this->put_file(array("file" => $fname, "content" => $content));
			@chmod($fname, 0666);
			if ($clear_flag)
			{
				$this->clear_cache($oid, $fname);
			}
		}
	}

	////
	// !tshekib et kas objekt on cachetud ja kas cachemist yldse kasutatakse. 
	// kui kasutatakse ja objekt on olemas, siis tagastab objekti cache
	// kui ei, siis false
	// $oid - objekti id, mille kohta cahet kysitaxe
	// $arr - array objekti kuju identivatest parameetritest (periood ntx), millest moodustatakse cache faili nimi.
	function get($oid,$arr)
	{
		if ($this->cfg["use_page_cache"] && !aw_global_get("uid"))
		{
			$fname = "/".str_replace("/","_",$oid);
			reset($arr);
			while (list(,$v) = each($arr))
			{
				$fname.="-".str_replace("/","_",str_replace(" ","_",$v));
			}

			if (is_array($this->metaref) && (in_array($this->referer,$this->metaref)) )
			{
				$fname .= "-" . substr($this->referer,7);
			}

			if (strlen($fname) > 100)
			{
				$fname = "/".md5($fname);
			}
			$fname = $this->get_fqfn($fname);
			if ($this->cache_dirty($oid, $fname))
			{
				return false;
			}
			else
			{
				$content = $this->get_file(array("file" => $fname));
				if ($content == false)
				{
					return false;
				} 
				else 
				{
					return $content;
				};
			};
		}
		else
		{
			return false;
		};
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

			/*
			$fname .= "/".$hash{1};
			if (!is_dir($fname))
			{
				@mkdir($fname, 0777);
				chmod($fname, 0777);
			}

			$fname .= "/".$hash{2};
			if (!is_dir($fname))
			{
				@mkdir($fname, 0777);
				chmod($fname, 0777);
			}
			*/

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
		$awt->start("file-invalidate");
		$awt->count("file-invalidate");
		if ($this->cfg["page_cache"] != "")
		{
			@unlink($this->get_fqfn($key));
		}
		$awt->stop("file-invalidate");
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
			aw_cache_set_array("cache_files",$this->cache_files);
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
		
		if ($high == count($this->cache_files) || strpos($this->cache_files[$high],$r_regex) === false)
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


		/*
		foreach($this->cache_files as $cache_file)
		{
			$awt->start("preg_match");
			$awt->count("preg_match");
			*(
			/*
			if (preg_match("/$regex/", $cache_file))
			{
				$this->file_invalidate($cache_file);
				$this->__fir_cnt++;
			}
			*/
			/*
			if (strpos($cache_file,$r_regex) === 0)
			{
				if (aw_global_get("uid") == "duke")
				{
					print "f = $cache_file<br>";
				};
				$this->file_invalidate($cache_file);
				$this->__fir_cnt++;
			};
			$awt->stop("preg_match");
		};
		*/
		/*
		if ($dir = @opendir($fld)) 
		{
			while (($file = readdir($dir)) !== false) 
			{
				if (!($file == "." || $file == ".."))
				{
					if (is_dir($fld."/".$file))
					{
						$this->_file_inv_re_req($fld."/".$file, $regex);
					}
					else
					{
						$awt->start("preg_match");
						$awt->count("preg_match");
						if (preg_match("/$regex/", $file))
						{
							$this->file_invalidate($file);
							$this->__fir_cnt++;
						}
						$awt->stop("preg_match");
					}
				}
			}
		}  
		closedir($dir);
		*/
	}
};
?>
