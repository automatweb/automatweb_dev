<?php
// $Header: /home/cvs/automatweb_dev/classes/groupware/Attic/task.aw,v 1.8 2004/04/28 14:10:17 duke Exp $
// task.aw - TODO item
/*

@classinfo syslog_type=ST_TASK relationmgr=yes no_status=1 no_comment=1

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

@default field=meta
@default method=serialize

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,recur_type,end,weekdays,interval_daily,interval_weekly,interval_montly,interval_yearly,
@caption Kordused

@property calendar_selector type=callback callback=cb_calendar_selector store=no group=calendars
@caption Kalendrid

@property project_selector type=callback callback=cb_project_selector store=no group=projects
@caption Projektid

@property comment_list type=comments group=comments no_caption=1
@caption Kommentaarid

@groupinfo recurrence caption=Kordumine
@groupinfo calendars caption=Kalendrid
@groupinfo projects caption=Projektid
@groupinfo comments caption=Kommentaarid

@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

*/

class task extends class_base
{
	function task()
	{
		$this->init(array(
			"tpldir" => "groupware/task",
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
	
	function cb_project_selector($arr)
	{
		$elib = get_instance("calendar/event_property_lib");
		return $elib->project_selector($arr);
	}

	function cb_calendar_selector($arr)
	{
		$elib = get_instance("calendar/event_property_lib");
		return $elib->calendar_selector($arr);
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "project_selector":
				$elib = get_instance("calendar/event_property_lib");
				$elib->process_project_selector($arr);
				break;

			case "calendar_selector":
				$elib = get_instance("calendar/event_property_lib");
				$elib->process_calendar_selector($arr);
				break;
		};
		return $retval;
	}
	
	function request_execute($obj)
	{
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $obj->name(),
			"time" => date("d-M-y H:i",$obj->prop("start1")),
			"content" => nl2br($obj->prop("content")),
		));
		return $this->parse();
	}
}
?>
