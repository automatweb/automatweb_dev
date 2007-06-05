<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/budgeting/budget.aw,v 1.1 2007/06/05 09:41:23 kristo Exp $
// budget.aw - Eelarve 
/*

@classinfo syslog_type=ST_BUDGET relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo aw_budgets index=aw_oid master_table=objects master_index=brother_of

@default table=aw_budgets
@default group=general

	@property project type=relpicker reltype=RELTYPE_PROJECT field=aw_project
	@caption Project

	@property total type=textbox size=5
	@caption Kogusumma


@reltype PROJECT value=1 CL_PROJECT
@caption Projekt

*/

class budget extends class_base
{
	function budget()
	{
		$this->init(array(
			"tpldir" => "applications/budgeting/budget",
			"clid" => CL_BUDGET
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
		
	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_budgets (aw_oid int primary key, aw_project int)");
			return true;
		}

		switch($f)
		{
			case "total":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "double"
				));
				return true;
				break;
		}
	}
}
?>
