<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/calendar_view.aw,v 1.11 2004/10/18 20:39:23 duke Exp $
// calendar_view.aw - Kalendrivaade 
/*
// so what does this class do? Simpel answer - it allows us to choose different templates
// for showing calendars. 

// also, all view related functions from CL_PLANNER will move over here

@classinfo syslog_type=ST_CALENDAR_VIEW relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property use_template type=chooser orient=vertical
@caption Välimus

@property num_next_events type=textbox size=5 
@caption Mitu j&auml;rgmist

@property default_view type=select 
@caption Vaade

@property show_current_events type=checkbox ch_value=1 
@caption Aktiivse päeva sisu näitamine

@property show_days_with_events type=checkbox ch_value=1
@caption Näita ainult sündmustega päevi

@default group=style
@property minical_day_with_events type=relpicker reltype=RELTYPE_STYLE
@caption Sündmustega päev

@property minical_day_without_events type=relpicker reltype=RELTYPE_STYLE
@caption Ilma sündmusteta päev

@property minical_day_today type=relpicker reltype=RELTYPE_STYLE
@caption Tänane päev

@property minical_day_active type=relpicker reltype=RELTYPE_STYLE
@caption Aktiivne päev

@property minical_day_deactive type=relpicker reltype=RELTYPE_STYLE
@caption Deaktiivne päev

@property minical_title type=relpicker reltype=RELTYPE_STYLE
@caption Pealkiri

@property minical_background type=relpicker reltype=RELTYPE_STYLE
@caption Taust

@default group=show_events

@property show_events type=calendar no_caption=1
@caption Sündmused

@groupinfo style caption=Stiilid
@groupinfo show_events caption=Sündmused submit=no

@reltype EVENT_SOURCE value=1 clid=CL_PLANNER,CL_DOCUMENT_ARCHIVE,CL_PROJECT
@caption Võta sündmusi

@reltype OUTPUT value=2 clid=CL_RELATION
@caption väljund
	
@reltype STYLE value=3 clid=CL_CSS
@caption Stiil
*/


class calendar_view extends class_base
{
	function calendar_view()
	{
		$this->init(array(
			"tpldir" => "calendar/calendar_view",
			"clid" => CL_CALENDAR_VIEW
		));
		
		lc_site_load("calendar_view",&$this);
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "use_template":
				$data["options"] = array(
					"intranet1.tpl" => "Kuuvaade & nädala sündmused",
					"month" => "Kuukalender",
					"futureevents" => "Algavad sündmused",
					"weekview" => "Nädala vaade",
					"last_events" => "Järgmised sündmused",
				);
				break;

			case "show_events":
				$this->gen_calendar_contents($arr);
				break;

			case "default_view":
				$data["options"] = array(
					"" => "",
					"day" => "päev",
					"week" => "nädal",
					"month" => "kuu",
					"last" => "Järgmised",
				);
				break;

			case "num_next_events":
				if ($arr["obj_inst"]->prop("use_template") != "last_events")
				{
					return PROP_IGNORE;
				}
				break;
		};
		return $retval;
	}

	// now to the meat .. I have to generate a list of events from our sources
	// but first I have to get the calendar to work

	/*
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	
	*/

	function gen_calendar_contents($arr)
	{
		$args = array();
		$arr["prop"]["vcl_inst"]->configure(array(
			//"tasklist_func" => array(&$this,"get_tasklist"),
			"overview_func" => array(&$this,"get_overview"),
			"overview_range" => 1,
		));

		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"] ? $arr["request"]["viewtype"] : $viewtype,
		));

		$this->obj_inst = $arr["obj_inst"];


		$this->_export_events(array(
			"obj_inst" => &$this->obj_inst,
			"cal_inst" => &$arr["prop"]["vcl_inst"],
			"range" => $range,
		));

	}

	function get_overview($arr)
	{
		$out_conns = $this->obj_inst->connections_from(array(
			"type" => RELTYPE_OUTPUT,
		));

		if (sizeof($out_conns) > 0)
		{
			$first = reset($out_conns);
			$al_id = $first->prop("to");
			// XXX: how do accomplish this with storage?
			$q = "SELECT source,target FROM aliases WHERE relobj_id = '$al_id'";
			$row = $this->db_fetch_row($q);
			$target_doc = $row["source"];
		};


		// now for each of those bloody things I need to figure out the date range as well
		$conns = $this->obj_inst->connections_from(array(
			"type" => RELTYPE_EVENT_SOURCE,
		));

		$overview = array();

		$item = array();
		if (!empty($target_doc))
		{
			$item["url"] = aw_ini_get("baseurl") . "/" . $target_doc;
		};

		foreach ($conns as $conn)
		{
			$to_o = $conn->to();
			// so, how do I get events from that calendar now?
			// no matter HOW, that function needs to accept range arguments
			if ($to_o->class_id() == CL_PLANNER)
			{
				$pl = get_instance(CL_PLANNER);
				$overview = $pl->get_event_list(array(
					"id" => $to_o->id(),
					"start" => $arr["start"],
					"end" => $arr["end"],
				));
			};

			if ($to_o->class_id() == CL_DOCUMENT_ARCHIVE)
			{
				$da = get_instance(CL_DOCUMENT_ARCHIVE);
				$overview = $da->get_days_with_events(array(
					"id" => $to_o->id(),
					"start" => $arr["start"],
					"end" => $arr["end"],
				));
			};

			foreach($overview as $event)
			{
				$item["timestamp"] = $event["start"];
				$item["url"] = $event["url"];
				if (!empty($item["url"]))
				{
					$item["url"] = aw_url_change_var("date",date("d-m-Y",$event["start"]),$item["url"]);
				};
				$rv[$event["start"]] = $item;
			};
		};
		// now, I need a document which will carry the news articles
		return $rv;
	}

	////
	// !Scans events source connections and exports them to the calendar component
	function _export_events($arr)
	{
		// alright .. this function needs to accept an object id from which to ask events
		$range = $arr["range"];
		$arr["cal_inst"]->vars($this->vars);

		if (is_oid($arr["oid"]))
		{
			$obj = new object($arr["oid"]);
			$events = $this->get_events_from_object(array(
				"obj_inst" => $obj,
				"range" => $range,
				"status" => $arr["status"],
			));

			foreach($events as $event)
			{
				$data = $event;
				$evt_obj = new object($event["id"]);
				$data = $evt_obj->properties() + $data;
				$data["icon"] = "event_icon_url";
				$arr["cal_inst"]->add_item(array(
					"timestamp" => $event["start"],
					"data" => $data,
				));
			};

		}
		else
		{
			$conns = $arr["obj_inst"]->connections_from(array(
				"type" => RELTYPE_EVENT_SOURCE,
			));

			foreach ($conns as $conn)
			{
				$to_o = $conn->to();
				$events = $this->get_events_from_object(array(
					"obj_inst" => $to_o,
					"range" => $range,
				));

				foreach($events as $event)
				{
					$data = $event;
					$evt_obj = new object($event["id"]);
					$data = $evt_obj->properties() + $data;
					$data["name"] = $evt_obj->name();
					$data["icon"] = "event_icon_url";
					$arr["cal_inst"]->add_item(array(
						"timestamp" => $event["start"],
						"data" => $data,
					));
				};

			};
		};
	}

	// common interface for getting events out of any class that can contain events
	// probably should not even be in this class
	function get_events_from_object($arr)
	{
		$events = array();
		$o = $arr["obj_inst"];
		$range = $arr["range"];
		$clid = $o->class_id();

		switch($clid)
		{
			case CL_PLANNER:
				$pl = get_instance(CL_PLANNER);
				$events = $pl->_init_event_source(array(
					"id" => $o->id(),
					"type" => $range["viewtype"],
					"flatlist" => 1,
					"date" => date("d-m-Y",$range["timestamp"]),
				));
				break;

			case CL_DOCUMENT_ARCHIVE:
				$da = get_instance(CL_DOCUMENT_ARCHIVE);
				$events = $da->get_events(array(
					"id" => $o->id(),
					"range" => $range,
				));	
				break;

			case CL_PROJECT:
				$pr = get_instance(CL_PROJECT);

				$events = $pr->get_events(array(
					"id" => $o->id(),
					"range" => $range,
					"status" => $arr["status"],
				));
				break;
		};

		return $events;
	}

	////
	//! 
	function parse_alias($arr)
	{
		if ($arr["obj_inst"])
		{
			$this->obj_inst = $arr["obj_inst"];
		}
		else
		{
			$this->obj_inst = new object($arr["alias"]["target"]);
		};
		
		classload("vcl/calendar");

		// figure out correct tpldir
		$tpldir = "calendar/calendar_view";
		$use_template = isset($arr["use_template"]) ? $arr["use_template"] : $this->obj_inst->prop("use_template");

		$use2dir = array(
			"month" => "calendar/calendar_view/month",
			"weekview" => "calendar/calendar_view/week",
			"year" => "calendar/calendar_view/year",
			"day" => "calendar/calendar_view/day",
			"last_events" => "calendar/calendar_view/last_events", 
		);

		if ($use2dir[$use_template])
		{
			$tpldir = $use2dir[$use_template];
		};
		
		$vcal = new vcalendar(array(
			"tpldir" => $tpldir,
		));

		$args = array(
			"container_template" => "intranet1.tpl",
		);

		if ("futureevents" != $this->obj_inst->prop("use_template"))
		{
			$args["overview_func"] = array(&$this,"get_overview");
			$args["overview_range"] = 1;
		};

		if (1 == $this->obj_inst->prop("show_days_with_events"))
		{
			$args["show_days_with_events"] = 1;
		};

		if ($arr["skip_empty"])
		{
			$args["skip_empty"] = $arr["skip_empty"];
		};

		if ($arr["full_weeks"])
		{
			$args["full_weeks"] = $arr["full_weeks"];
		};

		$vcal->configure($args);

		$viewtype = $this->obj_inst->prop("default_view");
		if ($viewtype == "")
		{
			$viewtype = "week";
		};
		if ($arr["viewtype"])
		{
			$viewtype = $arr["viewtype"];
		};

		$range_p = array(
			"viewtype" => $viewtype,
			"date" => aw_global_get("date"),
		);
		if ($use_template == "last_events")
		{
			$range_p["limit_events"] = $this->obj_inst->prop("num_next_events");
			$range_p["viewtype"] = "last_events";
			$range_p["type"] = "last_events";
		}

		
		$range = $vcal->get_range($range_p);

		// this cycle creates the "big" calendar, minicalendar is done in the 
		// get_overview callback

		$exp_args = array(
			"obj_inst" => &$this->obj_inst,
			"cal_inst" => &$vcal,
			"range" => $range,
			"status" => $arr["status"],
		);

		if ($use_template == "last_events")
		{
			$exp_args["limit_events"] = $this->obj_inst->prop("num_next_events");
		};

		if ($arr["obj_inst"])
		{
			$exp_args["oid"] = $arr["obj_inst"]->id();
		};

		$this->_export_events($exp_args);

		classload("layout/active_page_data");

		$style = array();

		// export all defined styles to the active page and the
		// current template
		$style_props = array(
			"minical_day_with_events",
			"minical_day_without_events",
			"minical_day_today",
			"minical_day_active",
			"minical_day_deactive",
			"minical_title",
			"minical_background",
		);

		$props = $this->obj_inst->properties();
		foreach($style_props as $style_prop)
		{
			$prop_value = $props[$style_prop];
			if (0 != $prop_value)
			{
				active_page_data::add_site_css_style($prop_value);
				$style[$style_prop] = "st" . $prop_value;
			};
		}

		$args = array(
			"style" => $style,
		);

		
		if ($this->obj_inst->prop("use_template") ==  "weekview")
		{
			$args["tpl"] = "week.tpl";
		};

		if ($arr["event_template"])
		{
			$args["event_template"] = $arr["event_template"];
		};

		return $vcal->get_html($args);
	}
}
?>
