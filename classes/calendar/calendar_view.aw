<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/calendar_view.aw,v 1.4 2004/04/29 10:14:12 duke Exp $
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

@property default_view type=select 
@caption Vaade

@property show_current_events type=checkbox ch_value=1 
@caption Aktiivse päeva sisu näitamine

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

@reltype EVENT_SOURCE value=1 clid=CL_PLANNER,CL_DOCUMENT_ARCHIVE
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
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "use_template":
				//$data["options"] = $this->get_template_files();
				$data["options"] = array(
					"intranet1.tpl" => "Kuuvaade & nädala sündmused",
					"month" => "Kuukalender",
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
				);
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
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_EVENT_SOURCE,
		));

		$range = $arr["range"];
		
		foreach ($conns as $conn)
		{
			$to_o = $conn->to();
			// so, how do I get events from that calendar now?
			// no matter HOW, that function needs to accept range arguments
			if ($to_o->class_id() == CL_PLANNER)
			{
				$pl = get_instance(CL_PLANNER);
				$events = $pl->_init_event_source(array(
					"id" => $to_o->id(),
					"type" => $range["viewtype"],
					"flatlist" => 1,
					"date" => date("d-m-Y",$range["timestamp"]),
				));
			};
			
			if ($to_o->class_id() == CL_DOCUMENT_ARCHIVE)
			{
				$da = get_instance(CL_DOCUMENT_ARCHIVE);

				$events = $da->get_events(array(
					"id" => $to_o->id(),
					"range" => $range,
				));	

			};

			foreach($events as $event)
			{
				$arr["cal_inst"]->add_item(array(
					"timestamp" => $event["start"],
					"data" => array(
						"id" => $event["id"],
						"name" => $event["name"],
						"icon" => $event["event_icon_url"],
						"link" => $event["link"],
						"comment" => $event["comment"],
					),
				));
			};

		};
	}

	////
	//! 
	function parse_alias($arr)
	{
		$this->obj_inst = new object($arr["alias"]["target"]);
		
		classload("vcl/calendar");

		// figure out correct tpldir
		$tpldir = "calendar/calendar_view";
		if ($this->obj_inst->prop("use_template") == "month")
		{
			$tpldir = "calendar/calendar_view/month";
		};

		$vcal = new vcalendar(array(
			"tpldir" => $tpldir,
		));

		$vcal->configure(array(
			"overview_func" => array(&$this,"get_overview"),
			"overview_range" => 1,
			"container_template" => "intranet1.tpl",
		));

		$viewtype = $this->obj_inst->prop("default_view");
		if ($viewtype == "")
		{
			$viewtype = "week";
		};

                $range = $vcal->get_range(array(
                        "viewtype" => $viewtype,
                        "date" => aw_global_get("date"),
                ));
		
		// this cycle creates the "big" calendar, minicalendar is done in the 
		// get_overview callback

		$this->_export_events(array(
			"obj_inst" => &$this->obj_inst,
			"cal_inst" => &$vcal,
			"range" => $range,
		));

		classload("layout/active_page_data");

		$style = array();

		// XXX: fuck this

		if ($this->obj_inst->prop("minical_day_with_events") != "")
		{
			active_page_data::add_site_css_style($this->obj_inst->prop("minical_day_with_events"));
			$style["minical_day_with_events"] = "st" . $this->obj_inst->prop("minical_day_with_events");
		};
		
		if ($this->obj_inst->prop("minical_day_without_events") != "")
		{
			active_page_data::add_site_css_style($this->obj_inst->prop("minical_day_without_events"));
			$style["minical_day_without_events"] = "st" . $this->obj_inst->prop("minical_day_without_events");
		};
		
		if ($this->obj_inst->prop("minical_day_today") != "")
		{
			active_page_data::add_site_css_style($this->obj_inst->prop("minical_day_today"));
			$style["minical_day_today"] = "st" . $this->obj_inst->prop("minical_day_today");
		};
		
		if ($this->obj_inst->prop("minical_day_active") != "")
		{
			active_page_data::add_site_css_style($this->obj_inst->prop("minical_day_active"));
			$style["minical_day_active"] = "st" . $this->obj_inst->prop("minical_day_active");
		};
		
		if ($this->obj_inst->prop("minical_day_deactive") != "")
		{
			active_page_data::add_site_css_style($this->obj_inst->prop("minical_day_deactive"));
			$style["minical_day_deactive"] = "st" . $this->obj_inst->prop("minical_day_deactive");
		};
		
		if ($this->obj_inst->prop("minical_title") != "")
		{
			active_page_data::add_site_css_style($this->obj_inst->prop("minical_title"));
			$style["minical_title"] = "st" . $this->obj_inst->prop("minical_title");
		};

		if ($this->obj_inst->prop("minical_background") != "")
		{
			active_page_data::add_site_css_style($this->obj_inst->prop("minical_background"));
			$style["minical_background"] = "st" . $this->obj_inst->prop("minical_background");
		};

		return $vcal->get_html(array(
			"style" => $style,
		));
	}
}
?>
