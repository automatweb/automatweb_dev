<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_offer.aw,v 1.7 2004/03/08 16:49:15 duke Exp $
// pakkumine.aw - Pakkumine 
/*

@classinfo syslog_type=ST_CRM_OFFER relationmgr=yes

@default table=objects
@default group=general
@default field=meta

default group=calendar
@property start1 type=datetime_select field=start table=planner
@caption Algab 

@property duration type=time_select field=end table=planner 
@caption Kestab

@property content type=textarea cols=60 rows=30 table=planner field=description
@caption Sisu

@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Aliastehaldur

default group=other_calendars
@tableinfo planner index=id master_table=objects master_index=brother_of

@default method=serialize
@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,weekdays,end
@caption Kordused

@property calendar_selector type=callback callback=cb_calendar_selector store=no group=calendars
@caption Kalendrid

@property project_selector type=callback callback=cb_project_selector store=no group=projects
@caption Projektid

@groupinfo recurrence caption=Kordumine
@groupinfo calendars caption=Kalendrid
@groupinfo projects caption=Projektid

@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

*/

class crm_offer extends class_base
{
	function crm_offer()
	{
		$this->init(array(
			"clid" => CL_CRM_OFFER
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

}
?>
