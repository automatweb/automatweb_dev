<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/menu_cache.aw,v 2.3 2002/03/20 19:36:08 duke Exp $
// menu_cache.aw - Menüüde cache
class menu_cache extends acl_base {
	function menu_cache($args = array())
	{
		$this->db_init();
		$this->period = $args["period"];
	}

	////
	// !Returns a reference to member variable
	function get_ref($name)
	{
		return $this->$name;
	}

	////
	// !Calculates the amount of subobjects each menu has
	function _list_subs($args = array())
	{
		// query only periodic objects if a period is set
		$sufix = ($this->period) ? " AND period = '$this->period' " : "";
		$where = ($args["where"]) ? $args["where"] : "";
		$q = "SELECT objects.parent AS parent,count(*) as subs
			FROM objects WHERE $where $sufix
			GROUP BY parent";

		if (not($this->db_query($q,false)))
		{
			return false;
		};

		while($row = $this->db_next())
		{
			$this->subs[$row["parent"]] = $row["subs"];
		};

		return true;
	}

	////
	// !Reads in all the menus
	function _list_menus($args = array())
	{
		$where = ($args["where"]) ? $args["where"] : "objects.status != 0";
		$ignore = ($args["ignore"]) ? $args["ignore"] : false;
		$ignore_lang = ($args["lang_ignore"]) ? $args["lang_ingore"] : false;

		if (!$ignore)
		{
			// loeme sisse koik objektid
			$aa = sprintf(" AND objects.site_id = '%d' ",$GLOBALS["SITE_ID"]);
                };
                if ($GLOBALS["lang_menus"] == 1 && $ignore_lang == false)
                {
			$aa .= sprintf(" AND (objects.lang_id='%d' OR menu.type = '%d') ",$GLOBALS["lang_id"],MN_CLIENT);
                }

                $q = "SELECT objects.oid as oid, 
                                objects.parent as parent,
                                objects.name as name,
                                objects.last as last,
                                objects.jrk as jrk,
                                objects.alias as alias,
                                objects.brother_of as brother_of,
                                objects.metadata as metadata,
                                objects.class_id as class_id,
                                objects.comment as comment,
                                menu.*
                        FROM objects 
                                LEFT JOIN menu ON menu.id = objects.oid
                                WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")
				AND menu.type != ".MN_FORM_ELEMENT." AND $where $aa
                                ORDER BY objects.parent, jrk,objects.created";
                if (not($this->db_query($q,false)))
		{
			return false;
		};	

		while ($row = $this->db_next())
		{
			// some places need raw metadata, others benefit from reading
			// the already uncompressed metainfo from the cache
			$row["meta"] = aw_unserialize($row["metadata"]);
			$row["mtype"] = $row["type"];
			// Maybe this means that some people come with knives after me sometimes,
			// but I'm pretty sure that we do not need to save unpacked metadata
			// in the cache, since it's available in $row[meta] anyway
			unset($row["metadata"]);
			$this->mpr[$row["parent"]][] = $row;
			$this->mar[$row["oid"]] = $row;
		}

		return true;

	}

	function make_caches($args = array())
	{
		classload("cache");
		$cache = new cache();
		$where = ($args["where"]) ? $args["where"] : " objects.status = 2";
		global $awt,$lang_id,$SITE_ID;
		$filename = "menuedit::menu_cache::lang::" . $lang_id . "::site_id::" . $SITE_ID;
                $ms = $cache->file_get($filename);
		$this->mar = array();
		$this->mpr = array();
		$this->subs = array();
		if (!$ms)
                {
			$cached = array();
			// avoid writing to the menu cache if the queries didn't succeed,
			// otherwise we are stuck with whatever (void most likely) lands
			// in the cache until the cache is invalidated
			if ( $this->_list_subs(array("where" => $where)) &&
				$this->_list_menus(array("where" => $where)) )
			{
				// I don't know for sure, but I hope that doing this avoids copying
				// the whole data around in memory
				$cached["mar"] = $this->mar;
				$cached["mpr"] = $this->mpr;
				$cached["subs"] = $this->subs;
				$c_d = aw_serialize($cached,SERIALIZE_PHP);
                        	$cache->file_set("menuedit::menu_cache::lang::".$lang_id."::site_id::".$SITE_ID,$c_d);
			};
		}
		else
		{
			// unserialize the cache
			$cached = aw_unserialize($ms,1);
			$this->mar = $cached["mar"];
			$this->mpr = $cached["mpr"];
			$this->subs = $cached["subs"];
		};


	}

	function get_cached_menu($oid)
	{
		return $this->mar[$oid];
	}
}
?>
