<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/calendar_view.aw,v 1.2 2004/02/25 15:49:28 duke Exp $
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

@property use_template type=chooser 
@caption Vali template

@property default_view type=select 
@caption Vaade

@default group=show_events

@property show_events type=calendar no_caption=1
@caption Sündmused

@groupinfo show_events caption=Sündmused submit=no

@reltype EVENT_SOURCE value=1 clid=CL_PLANNER
@caption Võta sündmusi
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
				$data["options"] = array("intranet1.tpl" => "intranet1.tpl");
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

	function _gather_event($arr)
	{
		$conns = $arr["obj_inst"]->connections_from(array(
			"reltype" => RELTYPE_EVENT_SOURCE,
		));



	}

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

		$pl = get_instance(CL_PLANNER);
		$this->obj_inst = $arr["obj_inst"];
		$conns = $this->obj_inst->connections_from(array(
			"reltype" => RELTYPE_EVENT_SOURCE,
		));


		foreach ($conns as $conn)
		{
			$to_o = $conn->to();
			// so, how do I get events from that calendar now?
			// no matter HOW, that function needs to accept range arguments
			if ($to_o->class_id() == CL_PLANNER)
			{
				$events = $pl->_init_event_source(array(
					"id" => $to_o->id(),
					"type" => $range["viewtype"],
					"flatlist" => 1,
					"date" => date("d-m-Y",$range["timestamp"]),
				));

				foreach($events as $event)
				{
					$arr["prop"]["vcl_inst"]->add_item(array(
						"timestamp" => $event["start"],
						"data" => array(
							"name" => $event["name"],
							"icon" => $event["event_icon_url"],
							"link" => $event["link"],
							"comment" => $event["comment"],
						),
					));
				};
			};
		};
		

	}

	function get_overview($arr)
	{
		// now for each of those bloody things I need to figure out the date range as well
		$conns = $this->obj_inst->connections_from(array(
			"reltype" => RELTYPE_EVENT_SOURCE,
		));

		$pl = get_instance(CL_PLANNER);

		foreach ($conns as $conn)
		{
			$to_o = $conn->to();
			// so, how do I get events from that calendar now?
			// no matter HOW, that function needs to accept range arguments
			if ($to_o->class_id() == CL_PLANNER)
			{
				$overview = $pl->get_event_list(array(
					"id" => $to_o->id(),
					"start" => $arr["start"],
					"end" => $arr["end"],
				));

				foreach($overview as $event)
				{
					$rv[$event["start"]] = 1;
				};
			}
		};
		return $rv;
	}

	////
	//! 
	function parse_alias($arr)
	{
		$this->obj_inst = new object($arr["alias"]["target"]);
		$conns = $this->obj_inst->connections_from(array(
			"reltype" => RELTYPE_EVENT_SOURCE,
		));

		classload("vcl/calendar");
		// XXX: Maybe we want different template sets
		$vcal = new vcalendar(array(
			"tpldir" => "calendar/calendar_view",
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
		
		$pl = get_instance(CL_PLANNER);


		foreach ($conns as $conn)
		{
			$to_o = $conn->to();
			// so, how do I get events from that calendar now?
			// no matter HOW, that function needs to accept range arguments
			if ($to_o->class_id() == CL_PLANNER)
			{
				$events = $pl->_init_event_source(array(
					"id" => $to_o->id(),
					"type" => $range["viewtype"],
					"flatlist" => 1,
					"date" => date("d-m-Y",$range["timestamp"]),
				));

				foreach($events as $event)
				{
					$vcal->add_item(array(
						"timestamp" => $event["start"],
						"data" => array(
							"name" => $event["name"],
							"icon" => $event["event_icon_url"],
							"link" => $event["link"],
							"id" => $event["id"],
							"comment" => $event["comment"],
						),
					));
				};
			};
		};

		// there is no event function, jees?

		
		return $vcal->get_html();

	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
