<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/ical_import.aw,v 1.1 2007/10/19 13:21:21 robert Exp $
// ical_import.aw - Sündmuste import (iCal) 
/*

@classinfo syslog_type=ST_ICAL_IMPORT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

	@property calendar type=relpicker reltype=RELTYPE_CALENDAR field=meta method=serialize store=connect
	@caption Kalender

	@property vevent_type type=select field=meta method=serialize
	@caption VEVENT t&uuml;&uuml;p aw-s

	@property url type=textbox field=meta method=serialize
	@caption Impordi url

	@property file type=fileupload store=no
	@caption Kalendrifail

@reltype CALENDAR value=1 clid=CL_PLANNER
@caption Kalender
*/

class ical_import extends class_base
{
	function ical_import()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/ical_import",
			"clid" => CL_ICAL_IMPORT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "vevent_type":
				$prop["options"] = array(
					CL_CRM_MEETING => "Kohtumine",
					CL_CRM_CALL => "Kõne",
					CL_TASK => "Toimetus",
					CL_CALENDAR_EVENT => "Kalendrisündmus"
				);
				if(!$prop["value"])
				{
					$prop["value"] = CL_CRM_MEETING;
				}
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
			case "file":
				$file = $_FILES['file'];
				$args = array(
					"id" => $arr["obj_inst"]->id(),
					"filename" => $file["tmp_name"],
					"ru" => $arr["request"]["post_ru"]
				);
				$this->import($args);
				break;
		}
		return $retval;
	}	

	/**
	@attrib name=import all_args=1
	**/
	function import($arr)
	{
		if(is_oid($arr["id"]))
		{
			$obj = obj($arr["id"]);
			$cal = obj($obj->prop("calendar"));
			if($cal)
			{
				require_once(aw_ini_get("basedir").'/addons/ical/iCalcreator.aw');
				$vcalendar = new vcalendar();
				if($arr["filename"])
				{
					$filename = $arr["filename"];
				}
				elseif($url = $obj->prop("url"))
				{
					$filename = $url;
				}
				if($filename)
				{
					$ef = $cal->get_first_obj_by_reltype("RELTYPE_EVENT_FOLDER");
					$c = new vcalendar();
					$c->setConfig("filename", $filename);
					$c->parse($filename);
					while($e = $c->getComponent())
					{
						$this->create_event($e, $ef, $obj);
					}
					die();
				}
			}
		}
		if($arr["ru"])
		{
			$ru = $arr["ru"];
		}
		else
		{
			$ru = $this->mk_my_orb("change", array("id" => $arr["id"]));
		}
		return $ru;
	}

	function create_event($c, $ef, $obj)
	{
		$type = get_class($c);
		switch($type)
		{
			case "vtodo":
				$e = new object();
				$e->set_class_id(CL_TASK);
				$e->set_parent($ef);
				$d = $c->getProperty("due");
				if($d["tz"] == "Z")
				{
					$add = 3;
				}
				$deadline = mktime($d["hour"]+$add, $d["min"], $d["sec"], $d["month"], $d["day"], $d["year"]);
				$e->set_prop("deadline",$deadline);
				break;
			case "vevent":
				$vt = $obj->prop("vevent_type");
				if(!$vt)
				{
					$vt = CL_CRM_MEETING;
				}
				$e = new object();
				$e->set_class_id($vt);
				$e->set_parent($ef);
				$d = $c->getProperty("dtend");
				if($d["tz"] == "Z")
				{
					$add = 3;
				}
				$end= mktime($d["hour"]+$add, $d["min"], $d["sec"], $d["month"], $d["day"], $d["year"]);
				$e->set_prop("end", $end);
				break;
		}
		$d = $c->getProperty("dtstart");
		if($d["tz"] == "Z")
		{
			$add = 3;
		}
		$start = mktime($d["hour"]+$add, $d["min"], $d["sec"], $d["month"], $d["day"], $d["year"]);
		$e->set_prop("start1", $start);
		$name = $c->getProperty("summary");
		$name = iconv("UTF-8",aw_global_get("charset"), $name);
		if(!strlen($name))
		{
			$name = "Ülesanne";
		}
		$e->set_name($name);
		$comment = $c->getProperty("description");
		$comment = iconv("UTF-8",aw_global_get("charset"), $comment);
		$e->set_comment($comment);
		$attendee = $c->getProperty("attendee");
		arr($attendee);
	}

	function callback_post_save($arr)
	{
		$obj = $arr["obj_inst"];
		if(!$obj->prop("calendar"))
		{
			$conn = $obj->connections_to(array(
				"type" => "RELTYPE_ICAL_IMPORT",
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

//-- methods --//
}
?>
