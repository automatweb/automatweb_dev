<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/sitemap.aw,v 2.3 2001/07/28 03:27:10 duke Exp $
// sitemap.aw - Site Map
classload("menuedit");
class sitemap extends aw_template 
{
	function sitemap()
	{
		$this->db_init();
		$this->tpl_init("sitemap");
		lc_load("definition");
	}

	function mk_map()
	{
		$this->read_template("sitemap.tpl");
		$m = new menuedit;
		$m->db_listall(" objects.status = 2 ");
		while ($row = $m->db_next())
		{
			$this->mar[$row[parent]][] = $row;
		}

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_DOCUMENT." AND status = 2 AND lang_id = ".$GLOBALS["lang_id"]." ORDER BY jrk");
		while ($row = $this->db_next())
			$this->mar[$row[parent]][] = $row;

		$parent = $GLOBALS["sitemap_rootmenu"];
		return $this->req_map($parent);
	}

	function req_map($parent)
	{
		if (!is_array($this->mar[$parent]))
			return "";

		// kui menyy all on aint 1 doku, siis ei n2ita seda
		reset($this->mar[$parent]);
		list(,$row) = each($this->mar[$parent]);
		if (count($this->mar[$parent]) == 1 && $row[class_id] == CL_DOCUMENT)
			return "";

		global $baseurl, $ext,$menu_defs_v2;

		$this->level++;
		$r = $this->parse("LEVEL_BEGIN");

		$ar = $this->mar[$parent];
		uasort($ar,__sm_sorter);

		reset($ar);
		while (list(,$row) = each($ar))
		{
				$this->vars(array("url" => $baseurl."/index.".$ext."/section=".$row[oid],"name" => $row[name],"oid" => $row[oid]));
				$r.=$this->parse("ITEM");
				$r.=$this->req_map($row[oid]);
		}
		$r.=$this->parse("LEVEL_END");
		$this->level--;
		return $r;
	}
}

function __sm_sorter($a, $b) 
{   
	if ($a[jrk] == $b[jrk]) return 0;
  return ($a[jrk] < $b[jrk]) ? -1 : 1;
}


?>
