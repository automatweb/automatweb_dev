<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/ical_export.aw,v 1.6 2007/12/28 20:24:02 hannes Exp $
// ical_export.aw - Sündmuste eksport (iCal) 
/*

@classinfo syslog_type=ST_ICAL_EXPORT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=robert

@default table=objects
@default group=general
	
	@property export_tb type=toolbar no_caption=1 store=no	

	@property name type=textbox table=objects
	@caption Nimi

	@property calendar type=relpicker reltype=RELTYPE_CALENDAR field=meta method=serialize store=connect
	@caption Kalender

	@property startdate type=datetime_select default=-1 field=meta method=serialize
	@caption Ajavahemiku algus

	@property enddate type=datetime_select default=-1 field=meta method=serialize
	@caption Ajavahemiku l&otilde;pp

	@property personal_not type=checkbox ch_value=1 field=meta method=serialize
	@caption &Auml;ra ekspordi isiklikke sündmusi

	@property url type=text store=no
	@caption Faili url

	@property url_google type=text store=no
	@caption Faili url Google kalendri jaoks

@reltype CALENDAR value=1 clid=CL_PLANNER
@caption Kalender
*/

class ical_export extends class_base
{
	function ical_export()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/ical_export",
			"clid" => CL_ICAL_EXPORT
		));
	}

	/**
	@attrib name=export_to_google_calendar all_args=1 nologin=1
	**/
	function export_to_google_calendar($arr)
	{
		aw_disable_acl();

		$obj = new object($arr["id"]);
		$cal_id = $obj->prop("calendar");
		$obj_cal = new object($cal_id);

		$this->read_template("google_calendar.tpl");
		$this->submerge=1;
		
		$ef = $obj_cal->get_first_obj_by_reltype("RELTYPE_EVENT_FOLDER");
		$filters = array(
			"class_id" => array(CL_TASK, CL_CRM_CALL, CL_CRM_MEETING, CL_CALENDAR_EVENT),
			"parent" => $ef->id()
		);
		if($arr["start"])
		{
			$filters["start1"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $arr["start"]);
		}
		if($arr["end"])
		{
			$filters["end"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $arr["end"]);
		}
		$events = new object_list($filters);
		
		$s_events = "";
		foreach($events->ids() as $oid)
		{
			if($obj->prop("personal_not") && $event->prop("is_personal"))
			{
				continue;
			}
			$event = obj($oid);
			
			switch($event->class_id())
			{
				case CL_TASK:
					$s_start = date  ( "Ymd\THis" , $event->prop("start1") );
					$s_end = date  ( "Ymd\THis" , $event->prop("end") );
					$s_title = $event->prop("name")." (".t("toimetus").")";
					$s_description = $event->prop("content");
					break;
				case CL_CALENDAR_EVENT:
					$i_start = date  ( "Ymd\THis" , $event->prop("start1") );
					$i_end = date  ( "Ymd\THis" , $event->prop("end") );
					$s_title = $event->prop("name")." (".t("kalendrisündus").")";
					$s_description = $event->prop("description");
					break;
				case CL_CRM_CALL:
					$i_start = date  ( "Ymd\THis" , $event->prop("start1") );
					$i_end = date  ( "Ymd\THis" , $event->prop("end") );
					$s_title = $event->prop("name")." (".t("kõne").")";
					$s_description = $event->prop("description");
					break;
				case CL_CRM_MEETING:
					$i_start = date  ( "Ymd\THis" , $event->prop("start1") );
					$i_end = date  ( "Ymd\THis" , $event->prop("end") );
					$s_title = $event->prop("name")." (".t("kohtumine").")";
					$s_description = $event->prop("description");
			}
			
			$this->vars(array(
				"start" => $s_start,
				"end" => $s_end,
				"title" => utf8_encode($s_title),
				//"description" => utf8_encode($s_description),
			));
			$s_events .= $this->parse("EVENT");
		}
		
		$this->vars(array(
			"calendar_name" => $obj_cal->prop("name"),
			"EVENT" => $s_events,
		));
		
		$s_out = $this->parse();
		
		header( 'Content-Type: text/calendar; charset=UTF-8' );
		header( 'Content-Length: '.mb_strlen  ( $s_out, "UTF-8" ));
		header( 'Content-Disposition: attachment; filename="export.ics"' );
		header( 'Cache-Control: max-age=10' );
		aw_restore_acl();
		die  (iconv(aw_global_get("charset"), "UTF-8", $s_out));
	}

	/**
	@attrib name=export all_args=1 nologin=1
	**/
	function export($arr)
	{
		aw_disable_acl();
		if(is_oid($arr["id"]))
		{
			$obj = obj($arr["id"]);
			if($calid = $obj->prop("calendar"))
			{
				$cal = obj($calid);
				$ef = $cal->get_first_obj_by_reltype("RELTYPE_EVENT_FOLDER");
				$filters = array(
					"class_id" => array(CL_TASK, CL_CRM_CALL, CL_CRM_MEETING, CL_CALENDAR_EVENT),
					"parent" => $ef->id()
				);
				if($arr["start"])
				{
					$filters["start1"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $arr["start"]);
				}
				if($arr["end"])
				{
					$filters["end"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $arr["end"]);
				}
				$events = new object_list($filters);
				require_once(aw_ini_get("basedir").'/addons/ical/iCalcreator.aw');

				$c = new vcalendar();
				$c->setConfig("lang" ,"ee");

				foreach($events->ids() as $oid)
				{
					if($obj->prop("personal_not") && $event->prop("is_personal"))
					{
						continue;
					}
					$event = obj($oid);
					$this->setevent($event, $c);
				}
				header('Content-type: text/calendar; charset=UTF-8');
				header('Content-Disposition: attachment; filename="export.ics"');

				$str = $c->createCalendar();
				die(iconv(aw_global_get("charset"), "UTF-8", $str));
			}
		}
		aw_restore_acl();
		return $this->mk_my_orb("change", array("id" => $arr["id"]));
	}

	function setevent($event, &$c)
	{
		switch($event->class_id())
		{
			case CL_TASK:
				$types = array(10,8);
				$e = new vtodo();
				$e->setProperty("priority", $event->prop("priority"));
				$e->setProperty("due", 
					date("Y", $event->prop("deadline")),
					date("m", $event->prop("deadline")),
					date("d", $event->prop("deadline")),
					date("H", $event->prop("deadline")),
					date("i", $event->prop("deadline")),
					date("s", $event->prop("deadline"))
				);
				break;
			case CL_CALENDAR_EVENT:
				$types = 0;
				$e = new vevent();
				$e->setProperty("dtend",
					date("Y", $event->prop("end")),
					date("m", $event->prop("end")),
					date("d", $event->prop("end")),
					date("H", $event->prop("end")),
					date("i", $event->prop("end")),
					date("s", $event->prop("end"))
				);
				break;
			case CL_CRM_CALL:
				$types = 9;
				$e = new vevent();
				$e->setProperty("dtend",
					date("Y", $event->prop("end")),
					date("m", $event->prop("end")),
					date("d", $event->prop("end")),
					date("H", $event->prop("end")),
					date("i", $event->prop("end")),
					date("s", $event->prop("end"))
				);
				break;
			case CL_CRM_MEETING:
				$types = 8;
				$e = new vevent();
				$e->setProperty("dtend",
					date("Y", $event->prop("end")),
					date("m", $event->prop("end")),
					date("d", $event->prop("end")),
					date("H", $event->prop("end")),
					date("i", $event->prop("end")),
					date("s", $event->prop("end"))
				);
				break;
		}
		if($types)
		{
			foreach($event->connections_to(array("type" => $types)) as $co)
			{
				$p = obj($co->conn["from"]);
				$email = obj($p->prop("email"));
				$e->setProperty("attendee", $email->name(), array(
					"PARTSTAT" => "NEEDS_ACTION",
					"RSVP" => "FALSE",
					"CN" => $p->name(),
					"ROLE" => "OPT-PARTICIPANT"
				));
			}
		}
		$e->setProperty("summary", $event->name());
		$e->setProperty("description", $event->comment());
		$e->setProperty("dtstart",
			date("Y", $event->prop("start1")),
			date("m", $event->prop("start1")),
			date("d", $event->prop("start1")),
			date("H", $event->prop("start1")),
			date("i", $event->prop("start1")),
			date("s", $event->prop("start1"))
		);
		$c->setComponent($e);
	}

	function get_property($arr)
	{
		$obj = $arr["obj_inst"];
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "export_tb":
				$tb = &$arr["prop"]["vcl_inst"];
				$tb->add_button(array(
					"name" => "submit",
					"img" => "save.gif",
					"tooltip" => "Salvesta failina",
					"url" => $this->mk_my_orb("export",array("id"=>$arr["obj_inst"]->id()))
				));
				break;

			case "url":
				$url = $this->mk_my_orb("export", array(
					"id" => $arr["obj_inst"]->id(),
					"start" => $obj->prop("startdate"), 
					"end"=> $obj->prop("enddate"),
				));
				$url = str_replace(array ("automatweb/", "?", "&"), array("", "/", "/"), $url)."/export.ics";
				$prop["value"] = html::href(array(
					"url" => $url,
					"caption" => $url
				));
				break;
			case "url_google":
				$url = $this->mk_my_orb("export_to_google_calendar", array(
					"id" => $arr["obj_inst"]->id(),
					"start" => $obj->prop("startdate"), 
					"end"=> $obj->prop("enddate"),
				));
				$url = str_replace(array ("automatweb/", "?", "&"), array("", "/", "/"), $url)."/export.ics";
				$prop["value"] = html::href(array(
					"url" => $url,
					"caption" => $url
				));
				break;
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

	function callback_post_save($arr)
	{
		$obj = $arr["obj_inst"];
		if(!$obj->prop("calendar"))
		{
			$conn = $obj->connections_to(array(
				"type" => "RELTYPE_ICAL_EXPORT",
				"from.class_id" => CL_PLANNER
			));
			foreach($conn as $c)
			{
				$calendar = obj($c->conn["from"]);
			}
			if($calendar)
			{
				$obj->set_prop("calendar", $calendar->id());
			}
		}
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

//-- methods --//
}
?>
