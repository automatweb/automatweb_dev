<?php
// $Header: /home/cvs/automatweb_dev/classes/cache.aw,v 2.49 2006/04/07 13:40:25 dragut Exp $

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
	/**
		@attrib params=pos 

		@param oid required type=string
			Object id, which is cached.
		@param arr required type=array
			Array containing parameters identifying object ('period' for example), which is used to compose filename in cache.
		@param content required type=string
			Data which is cached.
		@param clear_flag optional type=bool default=true
			If the value is false, then cache_dirty flag is not cleared for this object. The idea is, that in that way, i can make multiple caches for one object.
			NOTE: cache_dirty flag is a field in objects table, which value may be [1|0]. When it is 1, then http://www.site.ee/object_id comes from cache, othervise not.
		@param real_section optional type=oid default=NULL
			[xxx] If this is set, then oid parameter will be overwritten by this parameter. Seems that oid parameters value may not always be valid object id, so it is possible to supply correct object id via real_section parameter.

		@errors
			none

		@returns
			none
	
		@comment
			Writes data into cache.

		@examples
			none
	**/
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
	
	/**
		@attrib params=pos api=1

		@param key required type=string
			String that is used to compose the path and filename which holds the cached data.

		@param value required type=string	
			Cached data.

		@errors
			none

		@returns
			none
	
		@comment
			Not recommended to use.

		@examples
			$cache = get_instance('cache');
			$cache->file_set('foo', 'bar'	
	**/
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

	/**
		@attrib params=pos api=1

		@param key required type=string
			String that is used to set the filename in cache. 

		@errors
			none

		@returns
			Contents of the file in cache.	
			false if page_cache is not set in aw.ini
	
		@comment
			Not recommended to use.

		@examples
			$cache = get_instance('cache');
			$cache->file_set('foo', 'bar');
			echo $cache->file_get('foo'); // prints 'bar'
	
	**/
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

	/**
		@attrib params=pos api=1

		@param key required type=string
			String that is used to set the filename in cache. 

		@param ts required type=int
			Timestamp

		@errors
			none

		@returns
			Contents of the file in cache.	
			false 
				if page_cache is not set in aw.ini
				if supplied timestamp has newer time than the file's modification time
	
		@comment
			Checks, if the file in cache modification time is older than the time supplied via parameter. If it is older, then returns false, else filecontent.
			Not recommended to use.

		@examples
			$cache = get_instance('cache');
			$cache->file_set('foo', 'bar');
			sleep(5);
			// file in cache is newer than supplied timestamp, so file's content is returned
			var_dump($cache->file_get_ts('foo', time() - 3600));
			// file in cache is older than supplide timestamp, so false is returned
			var_dump($cache->file_get_ts('foo', time()));
		
	**/
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

	/**
		@attrib params=pos api=1

		@param key required type=string
			String that is used to set the filename in cache. 

		@errors
			none

		@returns
			none
	
		@comment
			Deletes the file from cache
			Not recommended to use.

		@examples
			$cache = get_instance('cache');
			$cache->file_set('foo', 'bar');
			var_dump($cache->file_get('foo')); // prints 'bar'
			$cache->file_invalidate('foo');
			var_dump($cache->file_get('foo')); // prints false
		
	**/
	function file_invalidate($key)
	{
		if ($this->cfg["page_cache"] != "")
		{
			@unlink($this->get_fqfn($key));
		}
	}

	/**
		@attrib params=pos api=1

		@param pt required type=string

		@errors
			none

		@returns
			none
	
		@comment
			Not recommended to use, because include() seems to be slower than eval().
		@examples
			none		
	**/
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

	/**
		@attrib params=pos api=1

		@param pt required type=string

		@errors
			none

		@returns
			none
	
		@comment
			Not recommended to use, because include() seems to be slower than eval().
		@examples
			none		
	**/
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

	/**
		@attrib params=pos api=1

		@param pt required type=string

		@errors
			none

		@returns
			none
	
		@comment
			Not recommended to use, because include() seems to be slower than eval().
		@examples
			none		
	**/
	function file_set_incl_pt_oid($pt, $oid, $fn, $dat)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".substr($oid, -1, 1)."/".$fn;

		$str = "<?php\n";
		$str .= aw_serialize($dat, SERIALIZE_PHP_FILE);
		$str .= "?>";
		
		return $this->file_set_pt($pt, substr($oid, -1, 1), $fn, $str);
	}

	/**
		@attrib params=pos api=1

		@param pt required type=string

		@errors
			none

		@returns
			none
	
		@comment
			Not recommended to use, because include() seems to be slower than eval().
		@examples
			none		
	**/
	function file_set_incl_pt($pt, $subf, $fn, $dat)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".$subf."/".$fn;

		$str = "<?php\n";
		$str .= aw_serialize($dat, SERIALIZE_PHP_FILE);
		$str .= "?>";
		
		return $this->file_set_pt($pt, $subf, $fn, $str);
	}

	/**
		@attrib params=pos api=1

		@param param1 required type=string
			comment

		@errors
			none

		@returns
			none
	
		@comment
			none

		@examples
			$cache = get_instance('cache');

	**/
	function file_set_pt_oid($pt, $oid, $fn, $cont)
	{
		//echo "file_set_pt_oid pt = $pt ,oid =  $oid, fn = $fn <br>";
		return $this->file_set_pt($pt, substr($oid, -1, 1), $fn, $cont);
	}

	/**
		@attrib params=pos api=1

		@param param1 required type=string
			comment

		@errors
			none

		@returns
			none
	
		@comment
			none

		@examples
			$cache = get_instance('cache');
	
	**/
	function file_get_pt_oid($pt, $oid, $fn)
	{
		//echo "file_get_pt_oid pt = $pt ,oid =  $oid, fn = $fn <br>";
		return $this->file_get_pt($pt, substr($oid, -1, 1), $fn);
	}

	/**
		@attrib params=pos api=1

		@param param1 required type=string
			comment

		@errors
			none

		@returns
			none
	
		@comment
			none

		@examples
			$cache = get_instance('cache');
	
	**/
	function file_get_pt_oid_ts($pt, $oid, $fn, $ts)
	{
		//echo "file_get_pt_oid_ts pt = $pt ,oid =  $oid, fn = $fn, ts = $ts <br>";
		return $this->file_get_pt_ts($pt, substr($oid, -1, 1), $fn, $ts);
	}

	/**
		@attrib params=pos api=1

		@param param1 required type=string
			comment

		@errors
			none

		@returns
			none
	
		@comment
			none

		@examples
			$cache = get_instance('cache');
		
	**/
	function file_set_pt($pt, $subf, $fn, $cont)
	{
		$fq = $this->cfg["page_cache"]."/".$pt."/".$subf."/".$fn;
		$f = fopen($fq, "w");
		if (!$f)
		{
			return;
			error::raise(array(
				"id" => "ERR_CACHE_FILE",
				"msg" => sprintf(t("cache::file_set_pt(%s, %s, %s): could not open file %s for writing!"), $pt, $subf, $fn, $fq)
			));
		}
		fwrite($f, $cont);
		fclose($f);
		@chmod($fname, 0666);
	}

	/**
		@attrib params=pos api=1

		@param param1 required type=string
			comment

		@errors
			none

		@returns
			none
	
		@comment
			none

		@examples
			$cache = get_instance('cache');
	
	**/
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

	/**
		@attrib params=pos api=1

		@param param1 required type=string
			comment

		@errors
			none

		@returns
			none
	
		@comment
			none

		@examples
			$cache = get_instance('cache');
		
	**/
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

	/**
		@attrib params=pos api=1

		@param param1 required type=string
			comment

		@errors
			none

		@returns
			none
	
		@comment
			none	

		@examples
			$cache = get_instance('cache');

	**/
	function file_clear_pt($pt)
	{
		// now, this is where the magic happens. 
		// basically, we rename the whole folder and clear it's contents later. 
		$fq = $this->cfg["page_cache"]."/".$pt;
		$nn = $this->cfg["page_cache"]."/temp/".$pt."_".gen_uniq_id();

		rename($fq, $nn);
		$this->_crea_fld($pt);
	}

	/**
		@attrib params=pos api=1

		@param param1 required type=string
			comment

		@errors
			none

		@returns
			none
	
		@comment
			none

		@examples
			$cache = get_instance('cache');
			
	**/
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

	/**
		@attrib params=pos api=1

		@param param1 required type=string
			comment

		@errors
			none

		@returns
			none
	
		@comment
			none

		@examples
			$cache = get_instance('cache');
		
	**/
	function file_clear_pt_oid_fn($pt, $oid, $fn)
	{
		$of = substr($oid, -1, 1);
		$fq = $this->cfg["page_cache"]."/".$pt."/".$of."/".$fn;

		// here we know the full path to the file, so just delete the damn thing
		@unlink($fq);
	}

	/**
		@attrib params=pos api=1

		@param param1 required type=string
			comment

		@errors
			none

		@returns
			none
	
		@comment
			none

		@examples
			$cache = get_instance('cache');
			
	**/
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
		mkdir($fq, 0777);
		@chmod($fq, 0777);
		for($i = 0; $i < 16; $i++)
		{
			$ffq = $fq ."/".($i < 10 ? $i : chr(ord('a') + ($i- 10)));
			@mkdir($ffq, 0777);
			@chmod($ffq, 0777);
		}
	}

	/**
		@attrib params=name api=1

		@param fname required type=string
			Fully qualified file name minus basedir.

		@param unserializer required type=array
			Reference in form of array("classname","function") to the unserializer function.

		@errors
			none

		@returns
			none
	
		@comment
		

		@examples
			$cache = get_instance('cache');
		
	**/
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


	/**
		@attrib api=1

		@errors
			none

		@returns
			Returns the timestamp of the last modified object.
	
		@comment
			If the method is called first time and there is no objlastmod file in cache, then last modified object is taken (from the objects table by the field modified) and it will be cached into objlastmod file in cache. If site_show.objlastmod_only_menu aw.ini setting is set (for example to 1), then last modified menu object is taken (class_id = CL_MENU)

		@examples
			$cache = get_instance('cache');
			echo date("d.m.Y H:m:s", $cache->get_objlastmod());
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

	/**
		@attrib params=pos api=1

		@errors
			none

		@returns
			none
	
		@comment
			Completely clears the cache.

		@examples
			$cache = get_instance('cache');
			$cache->file_set('foo', 'bar');
			echo $cache->file_get('foo'); // prints 'bar'
			$cache->full_flush();
			echo $cache->file_get('foo'); // prints nothing

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
