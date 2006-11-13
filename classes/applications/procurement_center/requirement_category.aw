<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/procurement_center/requirement_category.aw,v 1.1 2006/11/13 10:29:06 kristo Exp $
// requirement_category.aw - N&otilde;ude kategooria 
/*

@classinfo syslog_type=ST_REQUIREMENT_CATEGORY relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class requirement_category extends class_base
{
	function requirement_category()
	{
		$this->init(array(
			"tpldir" => "applications/procurement_center/requirement_category",
			"clid" => CL_REQUIREMENT_CATEGORY
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
		$arr["post_ru"] = post_ru();
	}
}
?>
