<?php
// $Header: /home/cvs/automatweb_dev/classes/cache.aw,v 2.22 2003/10/17 13:11:07 kristo Exp $

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
			$fname = $this->cfg["page_cache"].$fname;
			$this->put_file(array("file" => $fname, "content" => $content));
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
			$fname = $this->cfg["page_cache"].$fname;
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

	////
	// !writes a row to the cache table with the key $key - if the row exists, overwrites it, otherwise creates one
	function db_set($key,$value)
	{
		$this->quote(&$value);
		$this->db_query("REPLACE cache (id,content,valid) VALUES('$key','$value',1)");
	}

	////
	// !tries to read the entry for $key from the cache table and if it exists, returns it, otherwise or if it is not valid, returns false
	function db_get($key)
	{
		$this->db_query("SELECT * FROM cache WHERE id = '$key'");
		$row = $this->db_next();
		if (is_array($row) && $row["valid"] == 1)
		{
			return $row["content"];
		}
		return false;
	}

	////
	// !sets the not-valid flag for the row in the cache
	function db_invalidate($key)
	{
		$this->db_query("UPDATE cache SET valid = 0 WHERE id = '$key'");
	}

	function file_set($key,$value)
	{
		if ($this->cfg["page_cache"] != "")
		{
			$fname = $this->cfg["page_cache"] . "/$key";
			$this->put_file(array("file" => $fname, "content" => $value));
		}
	}

	function file_get($key)
	{
		if ($this->cfg["page_cache"] == "")
		{
			return false;
		}
		return $this->get_file(array("file" => $this->cfg["page_cache"]."/".$key));
	}

	function file_get_ts($key, $ts)
	{
		if ($this->cfg["page_cache"] == "")
		{
			return false;
		}
		$fqfn = $this->cfg["page_cache"]."/".$key;
		if ((@filemtime($fqfn)) < $ts)
		{
			return false;
		}

		return $this->get_file(array("file" => $fqfn));
	}

	function file_invalidate($key)
	{
		if ($this->cfg["page_cache"] != "")
		{
			@unlink($this->cfg["page_cache"]."/".$key);
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
				$result = $clobj->$clmeth(array("content" => $contents));
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
	function file_invalidate_regex($regex)
	{
		$cnt = 0;
		if ($dir = @opendir($this->cfg["page_cache"])) 
		{
		  while (($file = readdir($dir)) !== false) 
			{
				if (!($file == "." || $file == ".."))
				{
					if (preg_match("/$regex/", $file))
					{
						$this->file_invalidate($file);
						$cnt++;
					}
				}
			}
	  }  
	  closedir($dir);
		return $cnt;
	}
};
?>
