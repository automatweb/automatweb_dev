<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/class_designer/property_tree_branch.aw,v 1.2 2005/03/17 13:42:38 kristo Exp $
// property_tree_branch.aw - Puu oks 
/*

@classinfo syslog_type=ST_PROPERTY_TREE_BRANCH relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

*/

class property_tree_branch extends class_base
{
	function property_tree_branch()
	{
		$this->init(array(
			"tpldir" => "applications/class_designer/property_tree_branch",
			"clid" => CL_PROPERTY_TREE_BRANCH
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["return_url"] = $_SERVER["REQUEST_METHOD"] == "GET" ? post_ru() : $arr["return_url"];
	}

	function get_vis_tree_item(&$tv, $o, $var, $el)
	{
		$oname = $o->name();
		$num = $o->id();

		if ($var && $_GET[$var] == $num)
		{
			$oname = "<b>".$oname."</b>";
		}

		$parent = $o->parent();
		if ($parent == $el->id())
		{
			$parent = 0;
		}

		$tv->add_item($parent,array(
			"name" => $oname,
			"id" => $num,
			"url" => aw_url_change_var ($var, $num),
			"iconurl" => (icons::get_icon_url(CL_MENU,"")),
			"checkbox" => $checkbox_status,
		));
	}

	function do_generate_method($el, $item, $var)
	{
		$ret = "";

		$pt = $item->parent() == $el->id() ? "0" : $item->parent();

		$ret .= "\t\t\$t->add_item($pt, array(\n";
		$ret .= "\t\t\t\"name\" => \$arr[\"request\"][\"$var\"] == ".$item->id()." ? \"<b>".$item->name()."</b>\" : \"".$item->name()."\",\n";
		$ret .= "\t\t\t\"id\" => ".$item->id().",\n";
		$ret .= "\t\t\t\"url\" => aw_url_change_var(\"$var\", ".$item->id()."),\n";
		$ret .= "\t\t\t\"iconurl\" => icons::get_icon_url(CL_MENU, \"\"),\n";
		$ret .= "\t\t));\n";
		$ret .= "\t\t\n";

		return $ret;
	}
}
?>
