<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_meeting.aw,v 1.13 2004/08/02 10:48:53 duke Exp $
// kohtumine.aw - Kohtumine 
/*
HANDLE_MESSAGE_WITH_PARAM(MSG_MEETING_DELETE_PARTICIPANTS,CL_CRM_MEETING, submit_delete_participants_from_calendar);

@classinfo syslog_type=ST_CRM_MEETING relationmgr=yes no_status=1

@default table=objects
@default group=general

@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property start1 type=datetime_select field=start table=planner 
@caption Algab 

@property end type=datetime_select table=planner 
@caption Lõpeb

@property whole_day type=checkbox ch_value=1 field=meta method=serialize
@caption Kestab terve päeva

@property content type=textarea cols=60 rows=30 table=documents
@caption Sisu

@property summary type=textarea cols=60 rows=30 table=planner field=description
@caption Kokkuvõte

@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Aliastehaldur

@default field=meta
@default method=serialize

@property task_toolbar type=toolbar no_caption=1 store=no group=participants
@caption "Toolbar"

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,recur_type,end,weekdays,interval_daily,interval_weekly,interval_montly,interval_yearly,
@caption Kordused

@property calendar_selector type=callback callback=cb_calendar_selector store=no group=calendars
@caption Kalendrid

@property project_selector type=callback callback=cb_project_selector store=no group=projects
@caption Projektid

@property comment_list type=comments group=comments no_caption=1
@caption Kommentaarid

@property participant type=callback callback=cb_participant_selector store=no group=participants no_caption=1
@caption Osalejad

@groupinfo recurrence caption=Kordumine
@groupinfo calendars caption=Kalendrid
@groupinfo projects caption=Projektid
@groupinfo comments caption=Kommentaarid
@groupinfo participants caption=Osalejad

@tableinfo documents index=docid master_table=objects master_index=brother_of
@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption Kordus

*/

class crm_meeting extends class_base
{
	var $return_url;
	
	function crm_meeting()
	{
		$this->init(array(
			"clid" => CL_CRM_MEETING,
		));
	}

	function get_property($arr)
	{
		$data = &$arr['prop'];
		switch($data['name'])
		{
			case 'task_toolbar' :
			{
				$tb = &$data['toolbar'];
				$tb->add_button(array(
					'name' => 'del',
					'img' => 'delete.gif',
					'tooltip' => 'Kustuta valitud',
					'action' => 'submit_delete_participants_from_calendar',
				));

				$tb->add_separator();

				$tb->add_button(array(
					'name' => 'Search',
					'img' => 'search.gif',
					'tooltip' => 'Otsi',
				));

				$this->return_url=aw_global_get('REQUEST_URI');
				break;
			}
		}
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

	function cb_participant_selector($arr)
	{
		$elib = get_instance('calendar/event_property_lib');
		return $elib->participant_selector($arr);
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
			
			case "whole_day":
				if ($data["value"])
				{
					list($m,$d,$y) = explode("-",date("m-d-Y"));
					$daystart = mktime(9,0,0,$m,$d,$y);
					$dayend = mktime(17,0,0,$m,$d,$y);
					$arr["obj_inst"]->set_prop("start1",$daystart);
					$arr["obj_inst"]->set_prop("end",$dayend);
				};
				break;
		};
		return $retval;
	}

	/**
      @attrib name=submit_delete_participants_from_calendar
      @param id required type=int acl=view
   **/
   function submit_delete_participants_from_calendar($arr)
   {
		if(is_array($arr['check']))
		{
			foreach($arr['check'] as $person_id)
			{
				$obj = new object($person_id);
				$obj->disconnect(array('from'=>$arr['event_id']));
			}
		}		
   }
};
?>
