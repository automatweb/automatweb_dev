<?php

global $orb_defs;
$orb_defs["shop_menuedit"] = "xml";

// ok, this is how we do it. basically, the problem is that we use menuedit to draw the menus admin interface, BUT
// it writes the menu path's urls using menuedit_right.aw, but we must use some orb function. 
// so we derive a class from menuedit and override the function. yeah. maybe some others as well, l8r. 
// oop rulez. 
class shop_menuedit extends menuedit
{
	function shop_menuedit()
	{
		$this->menuedit();
	}

	function mk_path($oid,$text = "",$period = 0,$set = true)
	{
		global $ext;

		$ch = $this->get_object_chain($oid,false,$GLOBALS["admin_rootmenu2"]);
		$path = "";
		reset($ch);
		while (list(,$row) = each($ch))
		{
			$path="<a target='list' href='".$this->mk_my_orb("categories_right", array("parent" => $row["oid"]),"shop_admin",false, true)."'>".strip_tags($row["name"])."</a> / ".$path;
		}

		if ($set)
		{
			$GLOBALS["site_title"] = $path.$text;
		}
		return $path;
	}
}
?>
