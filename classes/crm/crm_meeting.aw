<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_meeting.aw,v 1.7 2004/03/08 16:49:15 duke Exp $
// kohtumine.aw - Kohtumine 
/*

@classinfo syslog_type=ST_CRM_MEETING relationmgr=yes no_status=1

@default table=objects
@default group=general

@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property start1 type=datetime_select field=start table=planner 
@caption Algab 

@property end type=datetime_select table=planner 
@caption Lõpeb

@property content type=textarea cols=60 rows=30 table=documents
@caption Sisu

@property summary type=textarea cols=60 rows=30 table=planner field=description
@caption Kokkuvõte

@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Aliastehaldur

@default field=meta
@default method=serialize

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,recur_type,end,weekdays,interval_daily,interval_weekly,interval_montly,interval_yearly,
@caption Kordused

@property calendar_selector type=callback callback=cb_calendar_selector store=no group=calendars
@caption Kalendrid

@property project_selector type=callback callback=cb_project_selector store=no group=projects
@caption Projektid

// releditor needs a feature where it will load the first item of the requested relation type

@groupinfo recurrence caption=Kordumine
@groupinfo calendars caption=Kalendrid
@groupinfo projects caption=Projektid

@tableinfo documents index=docid master_table=objects master_index=brother_of
@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

*/

// and now, all I have to do is to define a new property, that allows me to enter the recurrency information..
// and that would be a releditor, my friend!

class crm_meeting extends class_base
{
	function crm_meeting()
	{
		$this->init(array(
			"clid" => CL_CRM_MEETING,
		));
	}

	function parse_alias($arr)
	{
		$target = new object($arr["alias"]["target"]);
		return html::href(array(
			//"url" => aw_ini_get("baseurl") . "/" . $target->id(),
			"url" => $this->mk_my_orb("change",array("id" => $target->id()),$target->class_id(),true,true),
			"caption" => $target->name(),
		));
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
};
?>
