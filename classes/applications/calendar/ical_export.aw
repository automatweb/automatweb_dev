<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/ical_export.aw,v 1.9 2008/01/25 08:05:33 hannes Exp $
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

@groupinfo google_calendar caption="Google kalender"
@default group=google_calendar
	
	@groupinfo google_calendar_settings caption="Seaded" parent=google_calendar
	@default group=google_calendar_settings
		
		@property google_calendar_url type=hidden field=meta method=serialize
		
		@property google_calendar_settings_title type=textbox field=meta method=serialize
		@caption Kalendri pealkiri
		
		@property google_calendar_settings_summary type=textarea field=meta method=serialize cols=60 rows=10
		@caption Kirjeldus
		
		@property google_calendar_settings_location type=textbox field=meta method=serialize
		@caption Asukoht
		
		@property google_calendar_settings_color type=select field=meta method=serialize cols=60 rows=10
		@caption V&auml;rv
		
		@property automatic_sync type=checkbox ch_value=1 field=meta method=serialize
		@caption Automaatne sünkroniseerimine (15 min intervalliga)... hetkel ei toimi
		
		@property do_import type=checkbox store=no ch_value=1
		@caption Impordi praegu
		
	@groupinfo google_calendar_user caption="Kasutaja" parent=google_calendar
	@default group=google_calendar_user
	
		@property google_calendar_uid type=textbox field=meta method=serialize
		@caption Kasutaja
		
		@property google_calendar_password_1 type=password field=meta method=serialize
		@caption Parool
		
		@property google_calendar_password_2 type=password field=meta method=serialize
		@caption Parool uuesti
		
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

		define ("GOOGLE_CALENDAR_PASSWORD_1_HASH", "X1X1X1X1X1");
		define ("GOOGLE_CALENDAR_PASSWORD_2_HASH", "X2X2X2X2X2");
	}
	
	// todo : +02:00 should not be static
	function timestamp_to_google_date($timestamp)
	{
		return date  ("Y-m-d\Th:i:s.000+02:00", $timestamp);
	}
	
	function google_date_to_timestamp($s_date)
	{
		$year = substr($s_date, 0, 4);
		$month = substr($s_date, 5, 2);
		$day = substr($s_date, 8, 2);
		$hour = substr($s_date, 11, 2);
		$minute = substr($s_date, 14, 2);
		$second = substr($s_date, 17, 2);
		return mktime  ($hour, $minute, $second, $month, $day, $year);
	}
	
	function _set_automatic_sync($arr)
	{
		$prop = & $arr["prop"];
		$o = & $arr["obj_inst"];
		
		if ($prop["value"] == 1)
		{
			$this->google_calendar_sync($arr);
			
			$sc = get_instance("scheduler");
			$sc->add(array(
            	"event" => $this->mk_my_orb("sync_google", array("id" => $o->id())),
				"time" => time()+30,
			));
		}
		return PROP_OK;
	}
	
	// codes from  http://www.mail-archive.com/google-calendar-help-dataapi@googlegroups.com/msg04033.html
	function _get_google_calendar_settings_color($arr)
	{
	    $property =& $arr["prop"];
	    $property["options"]  = array(
	           	"#A32929" => "#A32929",	
				"#B1365F" => "#B1365F",
				"#7A367A" => "#7A367A",
				"#5229A3" => "#5229A3",
				"#29527A" => "#29527A",
				"#2952A3" => "#2952A3",
				"#1B887A" => "#1B887A",
				"#28754E" => "#28754E",
				"#0D7813" => "#0D7813",
				"#528800" => "#528800",
				"#88880E" => "#88880E",
				"#AB8B00" => "#AB8B00",
				"#BE6D00" => "#BE6D00",
				"#B1440E" => "#B1440E",
				"#865A5A" => "#865A5A",
				"#705770" => "#705770",
				"#4E5D6C" => "#4E5D6C",
				"#5A6986" => "#5A6986",
				"#4A716C" => "#4A716C",
				"#6E6E41" => "#6E6E41",
				"#8D6F47" => "#8D6F47",
	    );
	}
	
	
	function _set_do_import($arr)
	{
		$prop = & $arr["prop"];
		if ($prop["value"] == 1)
		{
			$this->google_calendar_sync($arr);
		}
	}
	
	 /**
		@attrib name=sync_google all_args=1 nologin=1
		@param id required type=int acl=view
	**/
	function sync_google($arr)
	{
		$this->google_calendar_sync($arr);
		die();
	}
	
	function google_calendar_sync($arr)
	{
		$o = new object($arr["id"]);
		ini_set ("include_path", ".:".aw_ini_get("basedir")."/addons/ZendGdata-1.0.3/library/");
		include_once("Zend/Loader.php");
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_AuthSub');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Calendar');
		
		$service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
		$client = Zend_Gdata_ClientLogin::getHttpClient($o->prop("google_calendar_uid")."@gmail.com",$o->prop("google_calendar_password_1"),$service);
		
		// todo timezone to aw admin -- it should be dropdown
		$this->create_google_calendar(array(
				"obj_inst" => & $o,
				"client" => $client,
				"title" => $o->prop("google_calendar_settings_title"),
				"summary" => $o->prop("google_calendar_settings_summary"),
				"location" => $o->prop("google_calendar_settings_location"),
				"color" => $o->prop("google_calendar_settings_color"),
		));
		$this->sync_events(array(
				"obj_inst" => & $o,
				"client" => $client,
				"cal_parent" => $arr["cal_parent"]
		));
	}
	
	function sync_events($arr)
	{
		//arr($this->google_date_to_timestamp("2008-01-02T11:00:00.000+02:00"));
		//arr($this->timestamp_to_google_date(1199264400));
		$a_aw_events = $this->get_aw_calendar_events_array($arr);
		$a_google_events = $this->get_google_calendar_events_array($arr);
		
		// first fetch new events from google calendar
		foreach ($a_google_events as $event)
		{
			$this->db_query("SELECT id FROM aw_google_calendar_event_relations_to_aw_events WHERE aw_calexport_id = ".$arr["obj_inst"]->id()." AND google_id = '".$event["google_id"]."';");
			$row = $this->db_next();
			if (!isset($row["id"]))
			{
				$o = new object(array(
					"class_id" => CL_CALENDAR_EVENT,
					"parent" => $arr["cal_parent"],
					"name" => $event["title"],
				));
				$o->set_class_id(CL_CALENDAR_EVENT);
				$o->set_prop("start1", $event["start"]);
				$o->set_prop("end", $event["end"]);
				$o->set_prop("description", $event["end"]);
				$o->save();
				$this->db_query("insert into aw_google_calendar_event_relations_to_aw_events (aw_calexport_id, aw_id, google_id) values (".$arr["obj_inst"]->id().", ".$o->id().", '".$event["google_id"]."')");
			}
		}
		
		// now fetch new events from aw calendar to google calendar
		foreach ($a_aw_events as $event)
		{
			$this->db_query("SELECT id FROM aw_google_calendar_event_relations_to_aw_events WHERE aw_calexport_id = ".$arr["obj_inst"]->id()." AND aw_id = '".$event["aw_id"]."';");
			$row = $this->db_next();
			if (!isset($row["id"]))
			{
				$title = utf8_encode ($event["title"]);
				$start = $this->timestamp_to_google_date($event["start"]);
				$end = $this->timestamp_to_google_date($event["end"]);
				$google_id = $this->create_google_event ($arr["client"],$arr["obj_inst"]->prop("google_calendar_url"), $title,'','', $start,$end);
				$this->db_query("insert into aw_google_calendar_event_relations_to_aw_events (aw_calexport_id, aw_id, google_id) values (".$arr["obj_inst"]->id().", ".$event["aw_id"].", '".$google_id."')");
			}
		}
		
		// check if some events needs to be synced
	}
	
	function get_google_calendar_events_array($arr)
	{
		$a_events = array();
		$client = $arr["client"];
		$obj = $arr["obj_inst"];
		$gdataCal = new Zend_Gdata_Calendar($client);
		$eventFeed = $gdataCal->getCalendarEventFeed($obj->prop("google_calendar_url"));
		foreach ($eventFeed as $event) {
			$a_events[] = array(
				"google_id" => $event->id->text,
				"title" => utf8_decode ($event->title->text),
				"description" => utf8_decode ($event->content->text),
				"where" => utf8_decode ($event->where->text),
			);
			foreach ($event->when as $when)
			{
				$start = $when->startTime;
				$a_events[count($a_events)-1]["start"] = $this->google_date_to_timestamp($start);
				$end = $when->endTime;
				$a_events[count($a_events)-1]["end"] = $this->google_date_to_timestamp($end);
			}
			
			foreach ($event->where as $where) {
				$a_events[count($a_events)-1]["where"] = $where->valueString;
			}
		}
		return $a_events;
	}

	function get_aw_calendar_events_array($arr)
	{
		$a_events = array();
		$obj = $arr["obj_inst"];
		if($calid = $obj->prop("calendar"))
		{
			$cal = obj($calid);
			$ef = $cal->get_first_obj_by_reltype("RELTYPE_EVENT_FOLDER");
			$filters = array(
				"class_id" => array(CL_TASK, CL_CRM_CALL, CL_CRM_MEETING, CL_CALENDAR_EVENT),
				"parent" => $ef->id()
			);
			$events = new object_list($filters);
			
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
						$a_events[] = array(
							"aw_id" => $event->id(),
							"title" => $event->prop("name"),
							"description" => $event->prop("content"),
							"start" => $event->prop("start1"),
							"end" => $event->prop("end"),
							"where" => $event->prop(""),
						);
					break;
					case CL_CRM_CALL:
						$a_events[] = array(
							"aw_id" => $event->id(),
							"title" => $event->prop("name"),
							"description" => $event->prop("content"),
							"start" => $event->prop("start1"),
							"end" => $event->prop("end"),
							"where" => $event->prop(""),
						);
					break;
				}
			}
		}
		return $a_events;
	}
	
	function _get_google_calendar_settings_title($arr)
	{
		$prop = & $arr["prop"];
		$o = & $arr["obj_inst"];
		if ($o->prop("google_calendar_settings_title") == "")
		{
			$o->set_prop("google_calendar_settings_title", $o->prop("name"));
			$o->save();
			$prop["value"] = $o->prop("name");
		}
		$prop["post_append_text"] = t(" &uuml;le 15 sümboli (ka t&uuml;hikud) ei soovita nimeks, kuna pikem tekst j&auml;&auml;b Google kalendris paani taha peitu.");
	}
	
	// creating new calendar is hack for now cuz Google Calendar APIs don't support this
	// todo calendar name, color and stuff should be synced but right now we look at events
	function create_google_calendar($arr)
	{
		$obj = & $arr["obj_inst"];
		
		if ($obj->prop("google_calendar_url") == "")
		{
			$client = $arr["client"];
			$s_title = strlen($arr["title"])>0 ?  utf8_encode($arr["title"]) : t("aw kalender");
			$s_tmp_title = md5($s_title);
			$s_summary = utf8_encode($arr["summary"]);
			$s_location = utf8_encode($arr["location"]);
			$s_timezone = strlen($arr["timezone"])>0 ?  $arr["timezone"] : "Europe/Tallinn";
			$s_color = strlen($arr["color"])>0 ?  $arr["color"] : "#A32929";
			
			
			$xml = "<entry xmlns='http://www.w3.org/2005/Atom'
						xmlns:gd='http://schemas.google.com/g/2005'
						xmlns:gCal='http://schemas.google.com/gCal/2005'>
						<title type='text'>[TITLE]</title>
						<summary type='text'>[SUMMARY]</summary>
						<gCal:timezone value='[TIMEZONE]'></gCal:timezone>
						<gCal:hidden value='false'></gCal:hidden>
						<gCal:color value='[COLOR]'></gCal:color>
						<gd:where rel='' label='' valueString='[LOCATION]'></gd:where>
						</entry> ";
			
			$gdataCal = new Zend_Gdata_Calendar($client);
			$uri = 'http://www.google.com/calendar/feeds/default/owncalendars/full';
			$xml = str_replace('[TITLE]', $s_tmp_title, $xml);
			$xml = str_replace('[SUMMARY]', $s_summary, $xml);
			$xml = str_replace('[LOCATION]', $s_location, $xml);
			$xml = str_replace('[TIMEZONE]', $s_timezone, $xml);
			$xml = str_replace('[COLOR]', $s_color, $xml);
			$gdataCal->post($xml, $uri); 
			
			$s_cal_url = $this->get_google_calendar_url_by_name($client, $s_tmp_title);
			$obj->set_prop("google_calendar_url", $s_cal_url);
			$obj->save();
		}
	}
	
	function _get_google_calendar_uid($arr)
	{
		$prop = & $arr["prop"];
		$prop["post_append_text"] = " @gmail.com";
	
		//header('Content-Type: text/html; charset=utf-8');
		//error_reporting(E_ALL);
		/*
		ini_set ("include_path", ".:".aw_ini_get("basedir")."/addons/ZendGdata-1.0.3/library/");
		include_once("Zend/Loader.php");
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_AuthSub');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Calendar');
		*/
		
		//$user = 'hkirsman@gmail.com';
		//$pass = 'm77cIQ';
		//$service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
		
		//$client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);
		
		//$this->create_google_calendar($client);
		
		//$this->outputCalendarList($client);
		//$this->outputCalendar($client);
		//$this->createQuickAddEvent($client, "Dinner at Joe's on Friday at 8 PM");
		//$this->createEvent($client);
		//die();
	}
	
	function _get_google_calendar_settings_location($arr)
	{
		$prop = & $arr["prop"];
		$prop["post_append_text"] = t(" näiteks Tallinn v&otilde;i Haapsalu. Kui Google kalender on avalik, siis see aitab inimestel Sinu s&uuml;ndmusi paremini leida.");
	}
	
	function _get_google_calendar_password_1($arr)
	{
		$prop = & $arr["prop"];
		$prop["value"] = GOOGLE_CALENDAR_PASSWORD_1_HASH;
	}
	
	function _set_google_calendar_password_1($arr)
	{
		$prop = & $arr["prop"];
		if ($prop["value"] == GOOGLE_CALENDAR_PASSWORD_1_HASH)
		{
			return PROP_IGNORE;
		}
		return PROP_OK;
	}
	
	function _get_google_calendar_password_2($arr)
	{
		$prop = & $arr["prop"];
		$prop["value"] = GOOGLE_CALENDAR_PASSWORD_2_HASH;
	}
	
	function _set_google_calendar_password_2($arr)
	{
		$prop = & $arr["prop"];
		$o = & $arr["obj_inst"];
		
		if ($prop["value"] == GOOGLE_CALENDAR_PASSWORD_2_HASH)
		{
			return PROP_IGNORE;
		}
		
		if ($prop["value"] != $o->prop("google_calendar_password_1") )
		{
			$prop["error"] = t("Paroolid ei ühti");
			return PROP_FATAL_ERROR;
		}
		
		return PROP_OK;
	}
	
	function create_google_event ($client, $s_url, $title = 'nimetu', $desc='', $where = '',
    $start = '', $end='', $tzOffset = '+02')
	{
		$gdataCal = new Zend_Gdata_Calendar($client);
		$newEvent = $gdataCal->newEventEntry();
		
		$newEvent->title = $gdataCal->newTitle($title);
		$newEvent->where = array($gdataCal->newWhere($where));
		$newEvent->content = $gdataCal->newContent("$desc");
		
		$when = $gdataCal->newWhen();
		$when->startTime = $this->timestamp_to_google_date($start);
		$when->endTime = $this->timestamp_to_google_date($end);
		$newEvent->when = array($when);
		
		// Upload the event to the calendar server
		// A copy of the event as it is recorded on the server is returned
		// todo... why isn't second parameter working???
		//$createdEvent = $gdataCal->insertEvent($newEvent, $s_url);
		$createdEvent = $gdataCal->insertEvent($newEvent);
		return $createdEvent->id->text;
	}
	
	function outputCalendar($client) 
	{
		$three_months_in_seconds = 60 * 60 * 24 * 28 * 3;
		$three_months_ago = date("Y-m-d\Th:i:sP", time() - $three_months_in_seconds);
		$three_months_from_today = date("Y-m-d\Th:i:sP", time() + $three_months_in_seconds);
		
		$gdataCal = new Zend_Gdata_Calendar($client);
		$query = $gdataCal->newEventQuery();
		$query->setUser('default');
		$query->setVisibility('private');
		$calID = 'default';
		$query->setUser($calID); 
		$query->setProjection('full');
		$query->setOrderby('starttime');
		$query->setFutureevents(true); 
		$query->setOrderby('starttime');
		$query->setStartMin($three_months_ago);
		$query->setStartMax($three_months_from_today);
	  	
		//arr($query->getQueryUrl(),1);
		//arr($query->getQueryUrl(),1);
		
		// Retrieve the event list from the calendar server
		try {
			//$eventFeed = $gdataCal->getCalendarEventFeed("http://www.google.com/calendar/feeds/484heptdg36fotti5fg7tnqrmc%40group.calendar.google.com/private/full");
			//$eventFeed = $gdataCal->getCalendarEventFeed("http://www.google.com/calendar/feeds/default/0hfd6vcbncn6p5hn1t4d2b2k4s%40group.calendar.google.com/private/full");
			
			//$eventFeed = $gdataCal->getCalendarEventFeed($query);
		} catch (Zend_Gdata_App_Exception $e) {
			echo "Error: " . $e->getResponse();
		}

	  echo "<ul>\n";
	  foreach ($eventFeed as $event) {
	    echo "\t<li>" . $event->title->text .  " (" . $event->id->text . ")\n";
	    echo "\t\t<ul>\n";
	    foreach ($event->when as $when) {
	      echo "\t\t\t<li>Starts: " . $when->startTime . "</li>\n";
	    }
	    echo "\t\t</ul>\n";
	    echo "\t</li>\n";
	  }
	  echo "</ul>\n";
	}
	
	function createQuickAddEvent ($client, $quickAddText) {
		$gdataCal = new Zend_Gdata_Calendar($client);
		$event = $gdataCal->newEventEntry();
		$event->content = $gdataCal->newContent($quickAddText);
		$event->quickAdd = $gdataCal->newQuickAdd('true');
		$newEvent = $gdataCal->insertEvent($event);
	}
	
	function get_google_calendar_url_by_name($client, $s_name)
	{
		$gdataCal = new Zend_Gdata_Calendar($client);
		$calFeed = $gdataCal->getCalendarListFeed();
		foreach ($calFeed as $calendar)
		{
			if ($calendar->title->text == $s_name )
			{
				return $calendar->link[0]->href;
			}
	  	}
	}
	
	function outputCalendarList($client) 
	{
		$gdataCal = new Zend_Gdata_Calendar($client);
		$calFeed = $gdataCal->getCalendarListFeed();
		echo '<h1>' . $calFeed->title->text . '</h1>';
		echo '<ul>';
		//arr($calFeed,1);
		foreach ($calFeed as $calendar) {
			echo '<li>' . $calendar->title->text . '</li>';
			arr($calendar->link[0]);
			
			try {
				//$eventFeed = $gdataCal->getCalendarEventFeed($calendar->id->text."/private/full");
				//$eventFeed = $gdataCal->getCalendarEventFeed($query);
			} catch (Zend_Gdata_App_Exception $e) {
				echo "Error: " . $e->getResponse();
			}
			/*
			echo "<ul>\n";
			foreach ($eventFeed as $event) {
			 echo "\t<li>" . $event->title->text .  " (" . $event->id->text . ")\n";
			 echo "\t\t<ul>\n";
			 foreach ($event->when as $when) {
			   echo "\t\t\t<li>Starts: " . $when->startTime . "</li>\n";
			 }
			 echo "\t\t</ul>\n";
			 echo "\t</li>\n";
			}
			echo "</ul>\n";
			*/
		
	  }
	  echo '</ul>';
	}
	
	/**
	@attrib name=export all_args=1 nologin=1
	**/
	function export($arr)
	{
		if(is_oid($arr["id"]))
		{
			$obj = obj($arr["id"]);
			$events = 0;
			if($arr["basket"])
			{
				$basket = obj($arr["basket"]);
				$bi = get_instance(CL_OBJECT_BASKET);
				$objs = $bi->get_basket_content($basket);
				$oids = array();
				foreach($objs as $o)
				{
					$oids[$o["oid"]] = $o["oid"];
				}
				$events = new object_list(array(
					"oid" => $oids
				));
			}
			elseif($calid = $obj->prop("calendar"))
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
			}
			if($events)
			{
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
	
	function callback_mod_tab($parm)
	{
		$obj = $parm["obj_inst"];
		$id = $parm['id'];
		
		// this hides google settings tab before google account settings are not set
		{
			if ($id == "google_calendar")
			{
				if ($obj->prop("google_calendar_uid")=="" && $obj->prop("google_calendar_password_1") == "")
				{
					$parm["link"] = str_replace  ( "group=google_calendar", "group=google_calendar_user"  , $parm["link"]);
				}
			}
			
			if ($id == "google_calendar_settings")
			{
				if ($obj->prop("google_calendar_uid")== "" && $obj->prop("google_calendar_password_1") == "")
				{
					return false;
				}
			}
		}
		return true;
	}
//-- methods --//
}
?>
