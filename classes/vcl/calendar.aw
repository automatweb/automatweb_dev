<?php
class calendar extends aw_template
{
	function calendar()
	{
		$this->init(array(
			"tpldir" => "calendar",
		));
	}

	function init_calendar($arr)
	{
		// this is the place where I need to calculate the range
		$this->items = array();
		$this->overview_items = array();
	}

	////
	// !Initializes the calendar view
	// overview_func -> a function that is used to define the presence of the quick navigator
	function initialize($arr = array())
	{
		if ($arr["overview_func"])
		{
			$this->overview_func = $arr["overview_func"];
		};
	}

	////
	// date - timestamp
	function get_range($arr = array())
	{
		// called from get_property to determine the range of events to be shown
		$viewtype = !empty($arr["viewtype"]) ? $arr["viewtype"] : "month";
		classload("date_calc");
		$range_args = array(
			"type" => $viewtype,
		);
		if (empty($arr["date"]))
		{
			$range_args["time"] = time();
		}
		else
		{
			$range_args["date"] = $arr["date"];
		};
		// this should also return overview range. no?
		// depending on amount of months we have in the overview.
		// yees. For starters, let's assume that we have 3 of them

		$this->past_limit = 15;
		$this->future_limit = 5;

		$range = get_date_range($range_args);
		$m = date("m",$range["timestamp"]);
		$y = date("Y",$range["timestamp"]);
		$range["viewtype"] = $viewtype;
		// start of the previous month
		$range["overview_start"] = mktime(0,0,0,$m-1,1,$y);
		// end of the next month
		$range["overview_end"] = mktime(23,59,59,$m+2,0,$y);
		if ($viewtype == "relative")
		{
			$range["past"] = $this->past_limit;
			$range["future"] = $this->future_limit;
			$range["end"] += 86400 * 60;
		};
		$this->range = $range;
		return $range;
	}

	// I need methods for adding item AND for drawing
	// timestamp 
	// data - arr
	function add_item($arr)
	{
		// convert timestamp to day, since calendar is usually day based
		$use_date = date("Ymd",$arr["timestamp"]);
		$data = $arr["data"];
		$data["timestamp"] = $arr["timestamp"];
		$this->items[$use_date][] = $data;
		// this is used for relational view
		if ($data["timestamp"] < $this->range["timestamp"])
		{
			$this->past[] = &$this->items[$use_date][sizeof($this->items[$use_date])-1];
		}
		else
		{
			$this->future[] = &$this->items[$use_date][sizeof($this->items[$use_date])-1];
		};
	}

	function add_overview_item($arr)
	{
		$use_date = date("Ymd",$arr["timestamp"]);
		$this->overview_items[$use_date] = true;
	}

	function get_html()
	{
		// let's start with a random view
		$this->evt_tpl = get_instance("aw_template");
		$this->evt_tpl->tpl_init("calendar");
		$tpl = $this->range["viewtype"] == "relative" ? "sub_event2.tpl" : "sub_event.tpl";
		$this->evt_tpl->read_template($tpl);
		switch($this->range["viewtype"])
		{
			case "month":
				$content = $this->draw_month();
				$caption = date("F Y",$this->range["timestamp"]);
				break;

			case "week":
				$content = $this->draw_week();
				$caption = date("d F",$this->range["start"]) . " - " . date("d F",$this->range["end"]);
				break;

			case "relative":
				$content = $this->draw_relative();
				$caption = date("F d",$this->range["timestamp"]);
				break;
	
			default:
				$content = $this->draw_day();
				$caption = date("F d",$this->range["timestamp"]);
		};
		
		classload("date_calc");
		$m = date("m",$this->range["timestamp"]);
		$y = date("Y",$this->range["timestamp"]);
		$this->origrange = $this->range;
		$m--;

		$mn = "";
		for ($i = 0; $i <= 2; $i++)
		{
			$range = get_date_range(array(
				"date" => sprintf("%d-%d-%d",1,$m+$i,$y),
				"type" => "month",
			));
			$this->range = $range;
			$mn .= $this->draw_s_month();
		};

		$this->range = $this->origrange;

		$this->read_template("container.tpl");
		$types = array(
			"day" => "Päev",
			"week" => "Nädal",
			"month" => "Kuu",
			"relative" => "Suhteline",
		);
		$ts = "";
		foreach($types as $type => $name)
		{
			$this->vars(array(
				"link" => aw_url_change_var("viewtype",$type),
				"text" => $name,
			));
			$ts .= $this->parse(($type == $this->range["viewtype"]) ? "SEL_PAGE" : "PAGE");
		};

		for ($i = 1; $i <= 12; $i++)
		{
			$mnames[$i] = get_est_month($i);
		};

		for ($i = 2002; $i <= 2004; $i++)
		{
			$years[$i] = $i;
		};
		$this->vars(array(
			"PAGE" => $ts,
			"naviurl" => aw_url_change_var("date",""),
			"mnames" => html::picker($this->range["m"],$mnames),
			"years" => html::picker($this->range["y"],$years),
			"content" => $content,
			"caption" => $caption,
			"prevlink" => aw_url_change_var("date",$this->range["prev"]),
			"nextlink" => aw_url_change_var("date",$this->range["next"]),
			"overview" => $mn,
			"today_url" => aw_url_change_var(array("viewtype" => "day","date" => date("d-m-Y"))),
		));

		return $this->parse();
	}

	////
	// !How to I make overview work. It's longer than the usual span ..
	// so I need some way to do define additonal items for the navigator.

	function draw_month()
	{
		$this->read_template("month_view.tpl");
		$rv = "";

		for ($i = 1; $i <= 7; $i++)
		{
			$dn = get_lc_weekday($i);
			$this->vars(array(
				"dayname" => strtoupper(substr($dn,0,1)),
			));
			$header .= $this->parse("HEADER_CELL");
		};

		$this->vars(array(
			"HEADER_CELL" => $header,
		));

		$header = $this->parse("HEADER");


		// the idea here is that drawing of month always starts from
		// the first day of the week in which the month starts and ends
		// on the last day of the week in which the month ends
		$realstart = ($this->range["start"] - ($this->range["start_wd"] - 1) * 86400);
		$realend = ($this->range["end"] + (7 - $this->range["end_wd"]) * 86400);

		for ($j = $realstart; $j <= $realend; $j = $j + (7*86400))
		{
			for ($i = $j; $i <= $j + (7*86400)-1; $i = $i + 86400)
			{
				$dstamp = date("Ymd",$i);
				$events_for_day = "";
				if (is_array($this->items[$dstamp]))
				{
					$events = $this->items[$dstamp];
					uasort($events,array($this,"__asc_sort"));
					foreach($events as $event)
					{
						$events_for_day .= $this->draw_event($event);
					};
				};
				$this->vars(array(
					"EVENT" => $events_for_day,
					"daynum" => date("j",$i),
					"dayname" => date("F d, Y",$i),
				));
				$rv .= $this->parse("DAY");
			};
			$this->vars(array(
				"DAY" => $rv,
			));
			$rv = "";
			$w .= $this->parse("WEEK");
		};
		$this->vars(array(
			"HEADER" => $header,
			"WEEK" => $w,
		));
		return $this->parse();
	}
	
	function draw_week()
	{
		$this->read_template("week_view.tpl");
		for ($i = $this->range["start"]; $i <= $this->range["end"]; $i = $i + 86400)
		{
			$dstamp = date("Ymd",$i);
			$events_for_day = "";
			if (is_array($this->items[$dstamp]))
			{
				$events = $this->items[$dstamp];
				uasort($events,array($this,"__asc_sort"));
				foreach($events as $event)
				{
					$events_for_day .= $this->draw_event($event);
				};
			};
			$this->vars(array(
				"EVENT" => $events_for_day,
				"daynum" => date("j",$i),
				"dayname" => date("F d, Y",$i),
			));
			$rv .= $this->parse("DAY");
		};
		$this->vars(array(
			"DAY" => $rv,
		));
		return $this->parse();
	}
	
	function draw_day()
	{
		$this->read_template("day_view.tpl");
		$dstamp = date("Ymd",$this->range["start"]);
		$events_for_day = "";
		if (is_array($this->items[$dstamp]))
		{
			$events = $this->items[$dstamp];
			uasort($events,array($this,"__asc_sort"));
			foreach($events as $event)
			{
				$events_for_day .= $this->draw_event($event);
			};
		};
		$this->vars(array(
			"EVENT" => $events_for_day,
			"daynum" => date("j",$this->range["start"]),
			"dayname" => date("F d, Y",$this->range["start"]),
		));
		return $this->parse();
	}

	function draw_relative()
	{
		$this->read_template("relative_view.tpl");
		$ok = false;
		$events = $this->past;

		$limit = $this->past_limit;
		$count = 0;
		
		// do the past
		$past = $future = "";

		if (is_array($this->past))
		{
			uasort($this->past,array($this,"__desc_sort"));
			foreach($this->past as $event)
			{
				$count++;
				$this->vars(array(
					"PT_EVENT" => $this->draw_event($event),
				));
				$past = $this->parse("PAST_EVENT") . $past;
				if ($count >= $limit)
				{
					break;
				};
			};
		};

		$limit = $this->future_limit;
		$count = 0;
		
		if (is_array($this->future))
		{
			uasort($this->future,array($this,"__asc_sort"));
			foreach($this->future as $event)
			{
				$count++;
				$this->vars(array(
					"FT_EVENT" => $this->draw_event($event),
				));
				$future = $future . $this->parse("FUTURE_EVENT");

				if ($count >= $limit)
				{
					break;
				};
			};
		};

		$this->vars(array(
			"PAST_EVENT" => $past, 
			"FUTURE_EVENT" => $future,
		));
		$past = $this->parse("PAST");
		$future = $this->parse("FUTURE");
		$this->vars(array(
			"PAST" => $past,
			"FUTURE" => $future,
			"PAST_LIMIT" => $this->past_limit,
			"FUTURE_LIMIT" => $this->future_limit,
		));
		return $this->parse();
	}

	////
	// XXX: this can and should be done without any templates. so it can be faster
	function draw_s_month()
	{
		$this->read_template("mini2.tpl");
		$rv = "";

		// the idea here is that drawing of month always starts from
		// the first day of the week in which the month starts and ends
		// on the last day of the week in which the month ends
		$realstart = ($this->range["start"] - ($this->range["start_wd"] - 1) * 86400);
		$realend = ($this->range["end"] + (7 - $this->range["end_wd"]) * 86400);

		for ($j = $realstart; $j <= $realend; $j = $j + (7*86400))
		{
			for ($i = $j; $i <= $j + (7*86400)-1; $i = $i + 86400)
			{
				$dstamp = date("Ymd",$i);
				$events_for_day = "";
				$style = ($this->overview_items[$dstamp]) ? "minical_cellact" : "minical_cell";
				$this->vars(array(
					"content" => $events_for_day,
					"daynum" => date("j",$i),
					"daycell_style" => $style,
					"day_url" => aw_url_change_var(array("viewtype" => "day","date" => date("d-m-Y",$i))),
				));
				$rv .= $this->parse("cell");
			};
			$this->vars(array(
				"cell" => $rv,
			));
			$rv = "";
			$w .= $this->parse("line");
		};
		$this->vars(array(
			"caption" => date("F",$this->range["timestamp"]),
			"caption_url" => aw_url_change_var(array("viewtype" => "month","date" => date("d-m-Y",$this->range["timestamp"]))),
			"line" => $w,
		));
		return $this->parse();
	}

	function draw_event($evt)
	{
		$this->evt_tpl->vars(array(
			"time" => date("H:i",$evt["timestamp"]),
			"date" => date("d-m-Y H:i",$evt["timestamp"]),
			"name" => $evt["name"],
			"link" => !empty($evt["link"]) ? $evt["link"] : "javascript:void(0)",
		));
		return $this->evt_tpl->parse();
	}

	function __asc_sort($el1,$el2)
	{
		return (int)($el1["timestamp"] - $el2["timestamp"]);
	}
	
	function __desc_sort($el1,$el2)
	{
		return (int)($el2["timestamp"] - $el1["timestamp"]);
	}

};
?>
