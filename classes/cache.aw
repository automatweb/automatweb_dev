<?php
// $Header: /home/cvs/automatweb_dev/classes/cache.aw,v 2.2 2001/10/02 10:16:58 cvs Exp $

// cache.aw - klass objektide cachemisex. 
// cachet hoitakse failisysteemis, kataloogis, mis peax olema defineeritud muutujas PAGE_CACHE
// cache kasutamist kontrollib muutuja USE_PAGE_CACHE
// cachetakse ainult mitte sisse-loginud kasutajatele

class cache extends core
{
	function cache()
	{
		$this->db_init();
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
		if (($GLOBALS["USE_PAGE_CACHE"] || defined("USE_PAGE_CACHE")) && !$GLOBALS[uid])
		{
			$fname = PAGE_CACHE . "/$oid";
			reset($arr);
			while (list(,$v) = each($arr))
			{
				$fname.="-".$v;
			}
			$this->put_file(array("file" => $fname, "content" => $content));
			echo "<!-- cache put file $fname -->\n";
			if ($clear_flag)
			{
				$this->clear_cache($oid);
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
		if (($GLOBALS["USE_PAGE_CACHE"] || defined("USE_PAGE_CACHE")) && !$GLOBALS[uid])
		{
			$fname = PAGE_CACHE . "/$oid";
			reset($arr);
			while (list(,$v) = each($arr))
			{
				$fname.="-".$v;
			}

			if ($this->cache_dirty($oid))
			{
				#echo "<!-- SS NO cache dirty! $oid ",join(",",$arr)," -->\n";
				return false;
			}
			else
			{
				$content = $this->get_file(array("file" => $fname));
				if ($content == false)
				{
					#echo "<!-- SS NO cache content! $oid ",join(",",$arr)," -->\n";
					return false;
				} 
				else 
				{
					#echo "<!-- SS using cache! $oid ",join(",",$arr)," , file $fname -->\n";
					return $content;
				};
			};
		}
		else
		{
			#echo "<!-- SS NO cache! $oid ",join(",",$arr)," -->\n";
			return false;
		};
	}
};
?>
