<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/sitemap.aw,v 2.12 2005/04/21 08:33:55 kristo Exp $
// sitemap.aw - Site Map

// DEPRECATED - duke's new menu tree showing class deprecates this one. 

class sitemap extends aw_template 
{
	function sitemap()
	{
		$this->init("sitemap");
		lc_load("definition");
	}

	function mk_map($no_docs = false,$rootmenu = -1)
	{
		$this->read_template("sitemap.tpl");
		$m = get_instance("menuedit");
		$m->db_listall(" objects.status = 2 ");
		while ($row = $m->db_next())
		{
//			$can = $this->can("view",$row["oid"]);
			$can = true;
			if (aw_global_get("uid") == "" && aw_ini_get("menuedit.no_show_users_only"))
			{
				$meta = aw_unserialize($row["metadata"]);
				if ($meta["users_only"] == 1)
				{
					$can = false;
				}
			}

			if ($can)
			{
				$this->mar[$row["parent"]][] = $row;
			}
		}

		if (!$no_docs)
		{
			$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_DOCUMENT." AND status = 2 AND lang_id = ".aw_global_get("lang_id")." ORDER BY jrk");
			while ($row = $this->db_next())
			{
					$this->mar[$row["parent"]][] = $row;
			};
		}

		$parent = $rootmenu == -1 ? aw_ini_get("sitemap.rootmenu") : $rootmenu;
		return $this->req_map($parent);
	}

	function req_map($parent)
	{
		if (!is_array($this->mar[$parent]))
		{
			return "";
		}

		// kui menyy all on aint 1 doku, siis ei n2ita seda
		reset($this->mar[$parent]);
		list(,$row) = each($this->mar[$parent]);
		if (count($this->mar[$parent]) == 1 && $row["class_id"] == CL_DOCUMENT)
		{
			return "";
		}

		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		$menu_defs_v2 = aw_ini_get("menuedit.menu_defs");

		$this->level++;
		$r = $this->parse("LEVEL_BEGIN");

		$ar = $this->mar[$parent];
		uasort($ar,__sm_sorter);

		reset($ar);
		while (list(,$row) = each($ar))
		{
			$meta = aw_unserialize($row["metadata"]);

			if (!($meta["users_only"] == 1 && aw_global_get("uid") ==""))
			{
				$this->vars(array("url" => $baseurl."/index.".$ext."/section=".$row["oid"],"name" => $row["name"],"oid" => $row["oid"]));
				$r.=$this->parse("ITEM");
				$r.=$this->req_map($row["oid"]);
			}
		}
		$r.=$this->parse("LEVEL_END");
		$this->level--;
		return $r;
	}
}

function __sm_sorter($a, $b) 
{   
	if ($a["jrk"] == $b["jrk"]) return 0;
	return ($a["jrk"] < $b["jrk"]) ? -1 : 1;
}


?>
