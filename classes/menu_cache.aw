<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/menu_cache.aw,v 2.8 2002/07/23 05:21:24 kristo Exp $
// menu_cache.aw - Menüüde cache
class menu_cache extends aw_template
{
	function menu_cache($args = array())
	{
		$this->init("");
		$this->period = aw_global_get("act_per_id");
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
		$q = "SELECT objects.parent AS parent,COUNT(*) AS subs
			FROM objects WHERE $where $sufix
			GROUP BY parent";

//		echo "q = $q <br>";
		if (not($this->db_query($q,false)))
		{
			print "false!";
		};

		while($row = $this->db_next(true))
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
		$ignore_lang = ($args["lang_ignore"]) ? $args["lang_ignore"] : false;
		$lang_id = $args["lang_id"] ? $args["lang_id"] : aw_global_get("lang_id");

		if (!$ignore)
		{
			// loeme sisse koik objektid
			$aa = sprintf(" AND objects.site_id = '%d' ",$this->cfg["site_id"]);
    };
    if ($this->cfg["lang_menus"] == 1 && $ignore_lang == false)
    {
			$aa .= sprintf(" AND (objects.lang_id='%d' OR menu.type = '%d') ",$lang_id,MN_CLIENT);
    }

     $q = "SELECT objects.oid as oid, 
									objects.parent AS parent,
									objects.name AS name,
									objects.last AS last,
									objects.jrk AS jrk,
									objects.alias AS alias,
									objects.status AS status,
									objects.brother_of AS brother_of,
									objects.metadata AS metadata,
									objects.class_id AS class_id,
									objects.comment AS comment,
									menu.*
					FROM objects 
						      LEFT JOIN menu ON menu.id = objects.oid
          WHERE (objects.class_id = ".CL_PSEUDO." OR objects.class_id = ".CL_BROTHER.")
									AND menu.type != ".MN_FORM_ELEMENT." AND $where $aa
          ORDER BY objects.parent, jrk,objects.created";

//		echo "q = $q <br>";
		if (not($this->db_query($q,false)))
		{
			return false;
		};	

		while ($row = $this->db_next(true))
		{
			// some places need raw metadata, others benefit from reading
			// the already uncompressed metainfo from the cache
			$row["meta"] = aw_unserialize($row["metadata"]);
			$row["mtype"] = $row["type"];

			// we need to do this, cause if the name contains quotes, then in the db they will be \\\" , then
			// when php reads them from the db they will be \\"
			// and when aw reads them from php (in db_next) they will be turned into \"
			// so here we need to do another dequote
			// how do they get like that? dunno. - terryf
			$this->dequote(&$row["name"]);
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
		extract($args);

		$cache = new cache();
		$where = ($args["where"]) ? $args["where"] : " objects.status = 2";
		if (!$lang_id)
		{
			$lang_id = aw_global_get("lang_id");
		}
		$SITE_ID = $this->cfg["site_id"];
		$filename = "menuedit::menu_cache::lang::" . $lang_id . "::site_id::" . $SITE_ID."::period::".$this->period;
		$fn = aw_ini_get("cache.page_cache")."/".$filename;
		if ($this->loaded_cache != $filename)
		{
			// argh. lets NOT clear the menu cache if we are not going to reload it !
			$this->mar = array();
			$this->mpr = array();
			$this->subs = array();
		}
		if (file_exists($fn))
		{
			if ($this->loaded_cache != $filename)
			{
				include($fn);
				$this->loaded_cache = $filename;
			}
		}
		else
    {
			$cached = array();
			// avoid writing to the menu cache if the queries didn't succeed,
			// otherwise we are stuck with whatever (void most likely) lands
			// in the cache until the cache is invalidated
			$subsql = " (class_id = ".CL_DOCUMENT." OR class_id = ".CL_PERIODIC_SECTION.") AND objects.status = 2 AND objects.lang_id = ".$lang_id." AND objects.site_id = ".aw_ini_get("site_id");
			if ( $this->_list_subs(array("where" => $subsql)) &&	$this->_list_menus(array("where" => $where,"lang_id" => $lang_id)) )
			{
				// make sure that we ust have to include this file and the menu cache will be read into
				// the correct member arrays

				$c_d = "<?php";

				classload("php");
				$php = new php_serializer;	
				$php->set("for_include", 1);

				$php->set("arr_name", "this->mar");
				$c_d .= "\n".$php->php_serialize($this->mar,true);

				$php->set("arr_name", "this->mpr");
				$c_d .= "\n".$php->php_serialize($this->mpr,true);

				$php->set("arr_name", "this->subs");
				$c_d .= "\n".$php->php_serialize($this->subs,true);

				$c_d .= "\n?>";
       	$cache->file_set("menuedit::menu_cache::lang::".$lang_id."::site_id::".$SITE_ID."::period::".$this->period,$c_d);
			};
		}
	}

	function get_cached_menu($oid)
	{
		return $this->mar[$oid];
	}
}
?>
