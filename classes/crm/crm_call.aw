<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_call.aw,v 1.13 2004/06/22 09:19:30 rtoomas Exp $
// crm_call.aw - phone call
/*

@classinfo syslog_type=ST_CRM_CALL relationmgr=yes no_status=1 

@default table=planner
@default group=general

@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property start1 type=datetime_select field=start 
@caption Algus

@property end type=datetime_select field=end 
@caption Lõpp

@property content type=textarea cols=60 rows=30 field=description
@caption Sisu

@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Aliastehaldur

@default table=objects
@default field=meta
@default method=serialize

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE group=recurrence rel_id=first props=start,weekdays,end
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

@reltype CALLER value=2 clid=CL_CRM_PERSON
@caption Helistaja

*/

class crm_call extends class_base
{
	function crm_call()
	{
		$this->init(array(
			"tpldir" => "crm/call",
			"clid" => CL_CRM_CALL
		));
	}

	function request_execute($obj)
	{
		classload("icons");
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $obj->name(),
			"icon" => icons::get_icon_url($obj),
			"time" => date("d-M-y H:i",$obj->prop("start1")),
			"content" => nl2br(create_links($obj->prop("content"))),
		));
		return $this->parse();
	}

	function parse_alias($arr)
	{
		// shows a phone call
		$obj = new object($arr["id"]);
		$done = $obj->prop("is_done");
		$done .= $obj->prop("name");
		return $done;
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
