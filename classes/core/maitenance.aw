<?php
// $Header: /home/cvs/automatweb_dev/classes/core/maitenance.aw,v 1.2 2006/01/31 15:25:59 kristo Exp $
// maitenance.aw - Saidi hooldus 
/*

@classinfo syslog_type=ST_MAITENANCE relatiomgr=yes

@default table=objects
@default group=general

*/

class maitenance extends class_base
{
	function maitenance()
	{
		$this->init(array(
			"tpldir" => "maitenance",
			"clid" => CL_MAITENANCE
		));
	}

	/**  
		
		@attrib name=cache_clear params=name default="0" nologin="1"
		
		@param clear optional
		@param list optional
		
		@returns
		
		
		@comment
		id - the id of the object where the alias will be attached
		alias - the id of the object to attach as an alias
		relobj_id - reference to the relation object
		reltype - type of the relation
		no_cache - if true, cache is not updated
	**/
	function cache_clear($args)
	{
		echo "<br />
		<input type='button' value='clear cache' 
		onclick=\"document.location='".$this->mk_my_orb('cache_clear', array('clear' => '1'))."'\"><br />";
		
		$this->files = array();
		$this->files_from_sd(aw_ini_get("cache.page_cache"));
		echo 'about to delete '.count($this->files).'files<br />';

		if (isset($args['clear']))
		{
			foreach($this->files as $file)
			{
				unlink($file);
			}
			echo '<br />'.count($this->files).' files deleted!!<br />';
		}

		if (!$args["no_die"])
		{
			die();
		}
	}
	
	function files_from_sd($dir)
	{
		if ($dh = opendir($dir)) 
		{
			while (($file = readdir($dh)) !== false) 
			{
				$fp = $dir."/".$file;
				if (!($file == "." || $file == ".."))
				{
					if (is_dir($fp))
					{
						$this->files_from_sd($fp);
					}
					else
					{
						$this->files[] = $fp;
					}
				}
			}
			closedir($dh);
		}
	}

	/** clears the cache for all sites, gets called from media once a day at 3 am

		@attrib name=clear_all_sites nologin=1

	**/
	function clear_all_sites($arr)
	{
		$i = get_instance("admin/foreach_site");
		$i->submit_exec(array(
			"eurl" => "orb.aw?class=maitenance&action=cache_clear&clear=1"
		));
	}

	/** clear stale pagecache entries
		@attrib name=cache_update nologin=1
	**/
	function cache_update($arr)
	{
		// let the user continue with their business
		header("Content-Type: image/gif");
		header("Content-Length: 43");
		header("Connection: close");
		echo base64_decode("R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==")."\n";
		flush();

		// go over temp folder and delete
		$fld = aw_ini_get("cache.page_cache")."/temp";
		$this->_req_cupd($fld);
	}

	function _req_cupd($dir)
	{
		$files = array();
		if ($DH = @opendir($dir)) 
		{
			while (false !== ($file = readdir($DH))) 
			{
				$fn = $dir . "/" . $file;
				if (is_file($fn))
				{
					unlink($fn);
				}
				else
				if (is_dir($fn) && $file != "." && $file != "..")
				{
					$this->_req_cupd($fn);
					rmdir($fn);
				}
			}
			closedir($DH);
		}
	}
}
?>
