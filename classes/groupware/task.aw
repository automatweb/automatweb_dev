<?php
// $Header: /home/cvs/automatweb_dev/classes/groupware/Attic/task.aw,v 1.5 2004/02/06 14:11:02 duke Exp $
// task.aw - Toimetus 

/*
	if a task is active, then it is not done.
	if a task is not active, then it is done (off the radar)
*/
/*

@classinfo syslog_type=ST_TASK relationmgr=yes

@default table=objects
@default group=general

@property is_done type=checkbox field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property start1 type=datetime_select field=start table=planner
@caption Algus

@property content type=textarea cols=60 rows=30 field=description table=planner
@caption Sisu

@property aliasmgr type=aliasmgr store=no
@caption Seostehaldur

@tableinfo planner index=id master_table=objects master_index=brother_of

@classinfo no_status=1

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
		};
		return $retval;
	}
}
?>
