<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/project_goal.aw,v 1.1 2005/06/02 09:21:09 kristo Exp $
// project_goal.aw - Verstapost 
/*

@classinfo syslog_type=ST_PROJECT_GOAL relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

@tableinfo aw_project_goals index=aw_oid

@property start1 type=datetime_select table=aw_project_goals field=aw_start
@caption Algus

@property end type=datetime_select table=aw_project_goals field=aw_end
@caption L&otilde;pp

@property content type=textarea rows=20 cols=50 table=aw_project_goals field=aw_content
@caption Sisu

*/

class project_goal extends class_base
{
	function project_goal()
	{
		$this->init(array(
			"tpldir" => "applications/groupware/project_goal",
			"clid" => CL_PROJECT_GOAL
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
