<?php
// $Header: /home/cvs/automatweb_dev/classes/groupware/Attic/task.aw,v 1.2 2003/11/19 11:32:19 duke Exp $
// task.aw - Toimetus 

/*
	if a task is active, then it is not done.
	if a task is not active, then it is done (off the radar)
*/
/*

@classinfo syslog_type=ST_TASK relationmgr=yes

@default table=objects
@default group=general

@property done type=checkbox ch_value=1 field=status 
@caption Tehtud

@property start1 type=datetime_select field=start table=planner
@caption Algus

@property content type=textarea cols=60 rows=30 field=description table=planner
@caption Sisu

@property aliasmgr type=aliasmgr store=no
@caption Seostehaldur

@tableinfo planner index=id master_table=objects master_index=brother_of

*/

class task extends class_base
{
	function task()
	{
		$this->init(array(
			"clid" => CL_TASK
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "comment":
			case "status":
				$retval = PROP_IGNORE;
				break;

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {
			case "done":
				if (empty($data["value"]))
				{
					$data["value"] = STAT_ACTIVE;
				};
				break;

		}
		return $retval;
	}	
}
?>
