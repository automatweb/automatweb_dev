<?php

// ok, this is how we do it. basically, the problem is that we use menuedit to draw the menus admin interface, 
// so we derive a class from menuedit and override the function thet draws the path line.
// yeah. maybe some others as well, l8r. 
// oop rulez. 
class shop_menuedit extends menuedit
{
	function shop_menuedit()
	{
		$this->menuedit();
	}

	function mk_path($oid,$text = "",$period = 0,$set = true,$cat_right = true)
	{
		$ch = $this->get_object_chain($oid,false,$this->cfg["admin_rootmenu2"]);
		$path = "";
		reset($ch);
		while (list(,$row) = each($ch))
		{
			$path="<a target='list' href='".$this->mk_my_orb($cat_right ? "categories_right" : "article_right", array("parent" => $row["oid"]),"shop_admin",false, true)."'>".strip_tags($row["name"])."</a> / ".$path;
		}

		if ($set)
		{
			$GLOBALS["site_title"] = $path.$text;
		}
		return $path;
	}
}
?>
