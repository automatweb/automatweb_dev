<?php
// $Header: /home/cvs/automatweb_dev/classes/cache.aw,v 2.12 2002/09/19 15:13:05 kristo Exp $

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

			global $DBG;
			if ($DBG)
			{
				var_dump(in_array($this->referer,$this->metaref));
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

	function file_invalidate($key)
	{
		if ($this->cfg["page_cache"] != "")
		{
			@unlink($this->cfg["page_cache"]."/".$key);
		}
	}
};
?>
