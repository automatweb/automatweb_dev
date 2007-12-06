<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/project_analysis_col.aw,v 1.2 2007/12/06 14:33:32 kristo Exp $
// project_analysis_col.aw - Projekti anal&uuml;&uuml;si tulp 
/*

@classinfo syslog_type=ST_PROJECT_ANALYSIS_COL relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop

@default table=objects
@default group=general

@property ord type=textbox field=jrk size=5
@caption J&auml;rjekord

@property group_name type=textbox field=meta method=serialize
@caption Grupp

@property weight type=textbox field=meta method=serialize
@caption Kaal

@property priority type=textbox field=meta method=serialize
@caption Prioriteed

*/

class project_analysis_col extends class_base
{
	function project_analysis_col()
	{
		$this->init(array(
			"tpldir" => "applications/groupware/project_analysis_col",
			"clid" => CL_PROJECT_ANALYSIS_COL
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
