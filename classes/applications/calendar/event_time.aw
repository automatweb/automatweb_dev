<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/event_time.aw,v 1.7 2008/07/15 10:14:50 instrumental Exp $
// event_time.aw - Toimumisaeg 
/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_FROM, CL_CALENDAR_EVENT, on_connect_event_to_time)

@tableinfo aw_event_time index=aw_oid master_table=objects master_index=brother_of 
@classinfo syslog_type=ST_EVENT_TIME relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo

@default group=general
@default table=aw_event_time

default table=objects
default group=general

#GENERAL
@property start type=datetime_select field=start
@caption Algab

@property end type=datetime_select field=end
@caption L&otilde;peb

@property location type=relpicker reltype=RELTYPE_LOCATION field=location
@caption Toimumiskoht

@property event type=relpicker reltype=RELTYPE_EVENT field=event
@caption S&uuml;ndmus


#RELTYPES
@reltype LOCATION value=1 clid=CL_SCM_LOCATION
@caption Toimumiskoht

@reltype EVENT value=1 clid=CL_CALENDAR_EVENT
@caption Toimumiskoht

*/

class event_time extends class_base
{
	function event_time()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/event_time",
			"clid" => CL_EVENT_TIME
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "" && $t == "aw_event_time")
		{
			$this->db_query("CREATE TABLE aw_event_time(
				aw_oid int primary key,
				start int,
				end int,
				location int
			)");
			return true;
		}
		else
		{
			switch($f)
			{
				case "start":
				case "end":
				case "location":
				case "event":
					$this->db_add_col($t, array(
						"name" => $f,
						"type" => "int"
					));
					break;
			}
			return true;
		}
		return false;
	}

	function on_connect_event_to_time($arr)
	{
		$conn = $arr["connection"];
		$target_obj = $conn->to();
		if ($target_obj->class_id() == CL_EVENT_TIME)
		{
			$target_obj->event = $conn->prop("from");
			$target_obj->save();
		}
	}
}
?>
