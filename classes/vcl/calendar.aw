<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/calendar.aw,v 1.29 2004/10/29 16:00:24 duke Exp $
// calendar.aw - VCL calendar
class vcalendar extends aw_template
{
	function vcalendar($arr = array())
	{
		// calendar_view class needs templates from its own directory
		$this->cal_tpl_dir = isset($arr["tpldir"]) ? $arr["tpldir"] : "calendar";
		$this->init(array(
			"tpldir" => $this->cal_tpl_dir,
		));


		$this->container_template = "container.tpl";
	}

	function init_calendar($arr)
	{
		// this is the place where I need to calculate the range
		$this->items = array();
		$this->overview_items = array();
	}

	////
	// !Configures the calendar view
	// overview_func -> a function that is used to define the presence of the quick navigator
	function configure($arr = array())
	{
		if (!empty($arr["tasklist_func"]))
		{
			$this->tasklist_func = $arr["tasklist_func"];
		};
		// fact is, event list and overview should use different functions,
		// cause they need different kinds of data. It should be faster with
		// different functions
		if (!empty($arr["overview_func"]))
		{
			$this->overview_func = $arr["overview_func"];
		};

		if (!empty($arr["overview_range"]))
		{
			$this->overview_range = $arr["overview_range"];
		};

		if (!empty($arr["container_template"]))
		{
			$this->container_template = $arr["container_template"];
		};

		if (!empty($arr["show_days_with_events"]))
		{
			$this->show_days_with_events = 1;
		};

		if (!empty($arr["skip_empty"]))
		{
			$this->skip_empty = $arr["skip_empty"];
		};
		
		if (!empty($arr["full_weeks"]))
		{
			$this->full_weeks = $arr["full_weeks"];
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

		if (empty($this->overview_range))
		{
			$this->overview_range = 3;
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
		if ($arr["limit_events"])
		{
			$range["limit_events"] = $arr["limit_events"];
		}

		if ($this->overview_range == 3)
		{
			// start of the previous month
			$range["overview_start"] = mktime(0,0,0,$m-1,1,$y);
			// end of the next month
			$range["overview_end"] = mktime(23,59,59,$m+2,0,$y);
		}
		elseif ($this->overview_range == 1)
		{
			// start of the this month
			$range["overview_start"] = mktime(0,0,0,$m,1,$y);
			// end of the this month
			$range["overview_end"] = mktime(23,59,59,$m+1,0,$y);
		};
		if ($viewtype == "relative")
		{
			$range["past"] = $this->past_limit;
			$range["future"] = $this->future_limit;
			$range["end"] += 86400 * 60;
		};
		$this->el_count = 0;
		$this->evt_list = array();
		$this->range = $range;
		return $range;
	}

	// I need methods for adding item AND for drawing
	// timestamp 
	// data - arr
	function add_item($arr)
	{
		if ($GLOBALS["DD"] == 1)
		{
			echo "id = ".$arr["data"]["id"]." name = ".$arr["data"]["name"]." start = ".date("d.m.Y H:i", $arr["data"]["start1"])." <br>";
		}

		// convert timestamp to day, since calendar is usually day based
		$use_date = date("Ymd",$arr["timestamp"]);
		$this->el_count++;
		$data = $arr["data"];
		$data["timestamp"] = $arr["timestamp"];
		$data["_id"] = $this->el_count;
		$data['comment'] = $arr['data']['comment'];
		$data["utextarea1"] = nl2br($data["utextarea1"]);

		$this->evt_list[$this->el_count] = $data;
		$this->items[$use_date][] = &$this->evt_list[$this->el_count];

		if (isset($arr["recurrence"]) && is_array($arr["recurrence"]))
		{
			$this->recur_info[$this->el_count] = $arr["recurrence"];
			foreach($arr["recurrence"] as $tm)
			{
				$use_date = date("Ymd",$tm);
				$this->items[$use_date][] = &$this->evt_list[$this->el_count];
			};
		};
		// this is used for relational view
		//if ($data["timestamp"] < $this->range["timestamp"])
		if ($data["timestamp"] < time())
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
		if (!empty($arr["url"]))
		{
			$this->overview_urls[$use_date] = $arr["url"];
		};
	}

	function get_html($arr = array())
	{
		global $awt;
		$awt->start("gen-calendar-html");
		$this->styles = array();
		if (is_array($arr["style"]))
		{
			$this->styles = $arr["style"];
		};
		classload("date_calc");
		if (!is_array($this->range))
		{
			$this->range = get_date_range(array(
				"time" => time(),
				"viewtype" => "day",
			));
			$this->range["viewtype"] = "day";
		};

		$this->evt_tpl = get_instance("aw_template");

		$this->evt_tpl->tpl_init($this->cal_tpl_dir);
		$tpl = $this->range["viewtype"] == "relative" ? "sub_event2.tpl" : "sub_event.tpl";
		if ($arr["event_template"])
		{
			$tpl = $arr["event_template"];
		};
		$this->evt_tpl->read_template($tpl);
		switch($this->range["viewtype"])
		{
			case "month":
				$awt->start("draw-month");
				$content = $this->draw_month();
				$awt->stop("draw-month");
				$caption = locale::get_lc_month(date("m",$this->range["timestamp"]));
				$caption .= " ";
				$caption .= date("Y",$this->range["timestamp"]);
				break;

			case "week":
			case "last_events":
				$content = $this->draw_week();
				$ms = locale::get_lc_month(date("m",$this->range["start"]));
				$me = locale::get_lc_month(date("m",$this->range["end"]));
				$caption = date("j. ",$this->range["start"]) . "$ms - " . date("j. ",$this->range["end"]) . " " . $me;
				break;

			case "relative":
				$content = $this->draw_relative();
				$caption = date("j. ",$this->range["timestamp"]) . locale::get_lc_month(date("m",$this->range["timestamp"]));
				break;

			case "year":
				$content = $this->draw_year();
				$caption = "";
				break;

	
			default:
				$content = $this->draw_day();
				$caption = date("j. ",$this->range["timestamp"]) . locale::get_lc_month(date("m",$this->range["timestamp"])) . date(" Y",$this->range["timestamp"]);
		};
		
		classload("date_calc");
		$m = date("m",$this->range["timestamp"]);
		$y = date("Y",$this->range["timestamp"]);

		$mn = "";
		// this one draws overview months... teheheheeee
		// if overview_func is not defined, then no overview thingie will be drawn
		// it's that easy.
		if ($this->overview_func)
		{

			$ostart = $this->range["overview_start"];
			$oend = $this->range["overview_end"];

			$inst = $this->overview_func[0];
			$meth = $this->overview_func[1];
			if (method_exists($inst,$meth))
			{
				$awt->start("get-overview");
				$overview_items = $inst->$meth(array(
					"start" => $ostart,
					"end" => $oend,
				));
				$awt->stop("get-overview");
			};

			if (is_array($overview_items))
			{
				foreach($overview_items as $tm => $tmp)
				{
					if (is_array($tmp))
					{
						$this->add_overview_item(array(
							"timestamp" => $tmp["timestamp"],
							"url" => $tmp["url"],
						));
					}
					else
					{
						$this->add_overview_item(array(
							"timestamp" => $tm,
						));
					};
				};
			};

			// I need to figure out how many months should be shown.
			// actually, it should be set from the configure item

			// I also need to figure out which days to show, so that
			// holidays will get be excluded
			if ($this->overview_range == 3)
			{
				$ri =  -1;
				$re = 1;
			}
			else
			{
				$ri = 0;
				$re = 0;
			};
			
			$awt->start("draw-s-month");
			for ($i = $ri; $i <= $re; $i++)
			{
				$range = get_date_range(array(
					"date" => sprintf("%d-%d-%d",1,$m+$i,$y),
					"type" => "month",
				));
				$mn .= $this->draw_s_month($range);
			};
			$awt->stop("draw-s-month");
		};


		$this->read_template($this->container_template);
		$types = array(
			"day" => "Päev",
			"week" => "Nädal",
			"month" => "Kuu",
			"relative" => "Ülevaade",
		);
		$ts = "";

		foreach($types as $type => $name)
		{
			$link = aw_url_change_var("viewtype",$type);
			$this->vars(array(
				"link" => aw_url_change_var("viewtype",$type),
				"text" => $name,
			));
			$ts .= $this->parse(($type == $this->range["viewtype"]) ? "SEL_PAGE" : "PAGE");
		};

		$tasks = array();
		if (isset($this->tasklist_func))
		{
			$awt->start("get-tasklist");
			$inst = $this->tasklist_func[0];
			$meth = $this->tasklist_func[1];
			if (method_exists($inst,$meth))
			{
				$tasks = $inst->$meth();
			};
			$awt->stop("get-tasklist");
		};

		$tstr = "";
		foreach($tasks as $task)
		{
			$this->vars(array(
				"task_url" => $task["url"],
				"task_name" => parse_obj_name($task["name"]),
			));
			$tstr .= $this->parse("TASK");
		};

		if (!empty($tstr))
		{
			$this->vars(array(
				"TASK" => $tstr,
				"tasks_title" => "Toimetused",
			));

			$this->vars(array(
				"TASKS" => $this->parse("TASKS"),
			));
		};

		for ($i = 1; $i <= 12; $i++)
		{
			$mnames[$i] = locale::get_lc_month($i);
		};

		for ($i = 2002; $i <= 2005; $i++)
		{
			$years[$i] = $i;
		};

		// I'm trying to get the javascript function inside the template to generate
		// a correct url
		$urlsufix = strpos(aw_global_get("REQUEST_URI"),"?") === false ? "?" : "";

		$this->vars(array(
			"PAGE" => $ts,
			"mininaviurl" => aw_url_change_var("date","") . $urlsufix,
			"naviurl" => aw_url_change_var("date",""),
			"mnames" => html::picker((int)$m,$mnames),
			"years" => html::picker($y,$years),
			"content" => $content,
			"caption" => $caption,
			"prevlink" => aw_url_change_var("date",$this->range["prev"]),
			"nextlink" => aw_url_change_var("date",$this->range["next"]),
			"overview" => $mn,
			"today_url" => aw_url_change_var(array("viewtype" => "day","date" => date("d-m-Y"))),
			"today_date" => date("d.m.Y"),
			"act_day_tm" => $this->range["timestamp"],
		));

		$awt->stop("gen-calendar-html");

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
			if ($i >= 6 && !$this->full_weeks)
			{
				continue;
			};
			$dn = locale::get_lc_weekday($i,true);
			$this->vars(array(
				"dayname" => $dn,
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

		$now = date("Ymd");

		$calendar_blocks = array();

		$s_parts = unpack("a4year/a2mon/a2day",date("Ymd",$realstart));
		for ($j = $realstart; $j <= $realend; $j = $j + (7*86400))
		{
			for ($i = $j; $i <= $j + (7*86400)-1; $i = $i + 86400)
			{
				$reals = mktime(0,0,0,$s_parts["mon"],$s_parts["day"],$s_parts["year"]);
				$s_parts["day"]++;

				#$dstamp = date("Ymd",$i);
				$dstamp = date("Ymd",$reals);
				$events_for_day = "";
				$wn = date("w",$reals);
				if ($wn == 0)
				{
					$wn = 7;
				};

				if (!$this->full_weeks && $wn > 5)
				{
					continue;
				};

				// uh, but we parse day by day. How do I deal with recurring information?
				if (is_array($this->items[$dstamp]))
				{
					$events = $this->items[$dstamp];
					uasort($events,array($this,"__asc_sort"));
					foreach($events as $event)
					{
						$sday = $this->draw_event($event);
						$events_for_day .= $sday;
					};
				};
				$calendar_blocks[date("Ymd",$reals)] = $events_for_day;
			};
		};

		$last = 0;

		$s_parts = unpack("a4year/a2mon/a2day",date("Ymd",$realstart));
		for ($j = $realstart; $j < $realend; $j = $j + (7*86400))
		{
			for ($i = $j; $i <= $j + (7*86400)-1; $i = $i + 86400)
			{
				$reals = mktime(0,0,0,$s_parts["mon"],$s_parts["day"],$s_parts["year"]);
				$s_parts["day"]++;

				$dstamp = date("Ymd",$reals);
				/*
				if ($last == $dstamp)
				{
					continue;
				};
				*/
				$events_for_day = "";
				$wn = date("w",$reals);
				if ($wn == 0)
				{
					$wn = 7;
				};
				if (!$this->full_weeks && $wn > 5)
				{
					continue;
				};
				
				$this->vars(array(
					"EVENT" => $calendar_blocks[date("Ymd",$reals)],
					"daynum" => date("j",$reals),
					"dayname" => date("F d, Y",$reals),
					"daylink" => aw_url_change_var(array(
						"viewtype" => "day",
						"date" => date("d-m-Y",$reals),
					)),
				));
				$tpl = $dstamp == $now ? "TODAY" : "DAY";
				$rv .= $this->parse($tpl);

				$last = $dstamp;
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
			"month_name" => locale::get_lc_month($this->range["m"]),	
			"year" => $this->range["y"],
		));
		return $this->parse();
	}
	
	function draw_year()
	{
		$this->read_template("year_view.tpl");
		//$rv = "";

		$header = "";
		$rv = "";

		$this->vars(array(
			"year" => $this->range["y"],
		));

		//dbg::p5($this->range);
		list($d,$m,$y) = explode("-",date("d-m-Y",$this->range["timestamp"]));

		for ($i = 1; $i <= 12; $i++)
		{
			$this->vars(array(
				"month_name" => locale::get_lc_month($i),	
			));
			$header = $this->parse("HEADER");
			$footer = $this->parse("FOOTER");
			// nüüd on mul vaja iga kuu kohta algust ja lõppu

			$ms = mktime(0,0,0,$i,1,$y);
			$me = mktime(0,0,0,$i+1,0,$y);

			$et = "";

			$ev_count = 0;

			for ($j = $ms; $j <= $me; $j = $j + 86400)
			{
				$dstamp = date("Ymd",$j);
				$events_for_day = "";
				if (is_array($this->items[$dstamp]))
				{
					$events = $this->items[$dstamp];
					uasort($events,array($this,"__asc_sort"));
					foreach($events as $event)
					{
						$sday = $this->draw_event($event);
						$events_for_day .= $sday;
						$ev_count++;
					};
				};
				$et .= $events_for_day;

			};

			// XX: add optional skip_empty argument
			if (!$this->skip_empty || $ev_count > 0)
			{
				$this->vars(array(
					"HEADER" => $header,
					"EVENT" => $et,
					"FOOTER" => $footer,
				));

				$rv .= $this->parse("MONTH");
			};
		};

		$this->vars(array(
			"MONTH" => $rv,
		));

		return $this->parse();
	}
	
	function draw_week()
	{
		$this->read_template("week_view.tpl");
		$now = date("Ymd");
		if ($this->skip_empty)
		{
			$this->show_days_with_events = 1;
		};

		$s_parts = unpack("a4year/a2mon/a2day",date("Ymd",$this->range["start"]));
		#$e_parts = unpack("a4year/a2mon/a2day",date("Ymd",$this->range["end"]));

		// alright, aga see saast siis ju eeldab et mul on 
		#for ($t = 0; $t < 7; $t++)
		#{

		for ($i = $this->range["start"]; $i <= $this->range["end"]; $i = $i + 86400)
		{
			// XXX: relative view joonistab draw_weeki, but shouldnt
			$reals = mktime(0,0,0,$s_parts["mon"],$s_parts["day"],$s_parts["year"]);
			$s_parts["day"]++;

			// kuidas kurat ma siis kompenseerin seda daylight saving timet?
			$dstamp = date("Ymd",$reals);
			#$dstamp = $s_parts["year"] . $s_parts["mon"] . $s_parts["day"];
			$events_for_day = "";
			if (is_array($this->items[$dstamp]))
			{
				$events = $this->items[$dstamp];
				uasort($events,array($this,"__asc_sort"));
				foreach($events as $event)
				{
					$events_for_day .= $this->draw_event($event);
				};
			}
			elseif ($this->show_days_with_events == 1)
			{
				continue;	
			};
			$wn = date("w",$reals);
			if ($wn == 0)
			{
				$wn = 7;
			};
		
			$dt = date("d",$reals);
                	$mn = locale::get_lc_month(date("m",$reals));
                	$mn2 = $mn . " " . date("H:i",$reals);


			$this->vars(array(
				"EVENT" => $events_for_day,
				"daynum" => date("j",$reals),
				"dayname" => date("F d, Y",$reals),
				"lc_weekday" => get_lc_weekday($wn,$reals),
				"lc_month" => $mn,
				"daylink" => aw_url_change_var(array("viewtype" => "day","date" => date("d-m-Y",$reals))),
                        	"date_and_time" => $dt . ". " . $mn2,
				"day_name" => locale::get_lc_weekday($wn,true),
				"long_day_name" => locale::get_lc_weekday($wn),
                        	"date" => locale::get_lc_date($reals,5),
			));
			$tpl = $dstamp == $now ? "TODAY" : "DAY";
			$rv .= $this->parse($tpl);
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
		$i = $this->range["start"];
		$dt = date("d",$i);
		$mn = get_lc_month(date("m",$i));
		$this->vars(array(
			"EVENT" => $events_for_day,
			"daynum" => date("j",$this->range["start"]),
			"dayname" => date("F d, Y",$this->range["start"]),
			"long_day_name" => locale::get_lc_weekday($this->range["wd"]),
                       	"date" => locale::get_lc_date($this->range["start"],5),
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
	function draw_s_month($arr)
	{
		//$this->read_template("mini2.tpl");
		$rv = "";

		// the idea here is that drawing of month always starts from
		// the first day of the week in which the month starts and ends
		// on the last day of the week in which the month ends
		$realstart = ($arr["start"] - ($arr["start_wd"] - 1) * 86400);
		$realend = ($arr["end"] + (7 - $arr["end_wd"]) * 86400);

		$now = date("Ymd");

		$active_day = aw_global_get("date");
		if (empty($active_day))
		{
			$active_day = date("d-m-Y");
		};
		list($d,$m,$y) = explode("-",$active_day);
		// perhaps the date was in dd-mm-YYYY form?
		if (empty($y))
		{
			$y = $m;
			$m = $d;
			$d = 1;
		};
		$act_tm = mktime(0,0,0,$m,$d,$y);
		$act_stamp = date("Ymd",$act_tm);

		// modes
		//  0: day with events
		//  1: day with no events
		//  2: day outside the current range

		// styles
		// minical_cell - usual cell with no events  - day_without_events
		// minical_cellact - usual cell with events  - day_with_events 
		// minical_cellselected - selected (active) cell - day_active
		// minical_cell_today - day_today
		// minical_cell_deact  - deactiv (outside teh current range) - day_deactive

		$style_day_with_events = "minical_cellact";
		if (isset($this->styles["minical_day_with_events"]))
		{
			$style_day_with_events = $this->styles["minical_day_with_events"];
		};

		$style_day_without_events = "minical_cell";
		if (isset($this->styles["minical_day_without_events"]))
		{
			$style_day_without_events = $this->styles["minical_day_without_events"];
		};
		
		$style_day_active = "minical_cellselected";
		if (isset($this->styles["minical_day_active"]))
		{
			$style_day_active = $this->styles["minical_day_active"];
		};
		
		$style_day_deactive = "minical_cell_deact";
		if (isset($this->styles["minical_day_deactive"]))
		{
			$style_day_deactive = $this->styles["minical_day_deactive"];
		};
		
		$style_day_today = "minical_cell_today";
		if (isset($this->styles["minical_day_today"]))
		{
			$style_day_today = $this->styles["minical_day_today"];
		};

		$style_title = "minical_table";
		if (isset($this->styles["minical_title"]))
		{
			$style_title = $this->styles["minical_title"];
		};
		
		$style_background = "minical_table";
		if (isset($this->styles["minical_background"]))
		{
			$style_background = $this->styles["minical_background"];
		};

		$j = $realstart;
		$s_parts = unpack("a4year/a2mon/a2day",date("Ymd",$realstart));
		while($j <= $realend)
		{
			$i = $j;
			while($i <= $j + (7*86400)-1)
			{
				$reals = mktime(0,0,0,$s_parts["mon"],$s_parts["day"],$s_parts["year"]);
				$s_parts["day"]++;

				$dstamp = date("Ymd",$reals);
				$has_events = $this->overview_items[$dstamp];
				$style = $has_events ? $style_day_with_events : $style_day_without_events;
				if (between($i,$arr["start"],$arr["end"]))
				{
					$mode = 0;
					// if a day has no events and "cell_empty" sub is defined, use it.
					if (empty($has_events))
					{
						$mode = 1;
					};

					if ($act_stamp == $dstamp)
					{
						$style = $style_day_active;
					};

					if ($now == $dstamp)
					{
						$style = $style_day_today;
					};
				}
				else
				{
					// cells outside the current range will always be drawn with
					// this subtemplate
					$mode = 2;
					$style = $style_day_deactive;
				};

				if (!empty($this->overview_urls[$dstamp]))
				{
					$day_url = $this->overview_urls[$dstamp];
				}
				else
				{
					$day_url = aw_url_change_var(array("viewtype" => "day","date" => date("d-m-Y",$reals)));
				};

				// cell_empty has class, doesn't have a link, used to show days with no events
				// cell - has class, has link, used to show days with events
				// cell_deact - has a class, doesn't have a link, used to show days outside the current range

				// and that pretty much is it.

				// I set default styles in the container template and let them be overriden

				$rv .= "<td class='$style'>";

				if ($mode == 0)
				{
					$rv .= "<a href='$day_url'>";
				};

				// daynum
				$rv .= date("j",$reals);

				if ($mode == 0)
				{
					$rv .= "</a>";
				};
				$rv .= "</td>";
				$i = $i + 86400;
			};

			$w .= "<tr>" . $rv . "</tr>";
			$rv = "";
			$j = $j + (7*86400);
		};

		// now, how to make those configurable?
		$mon = locale::get_lc_month(date("m",$arr["timestamp"]));
		$caption =  $mon . " " . date("Y",$arr["timestamp"]);

		$caption_url = aw_url_change_var(array("viewtype" => "month","date" => date("d-m-Y",$arr["timestamp"])));

		$prev_url = aw_url_change_var(array(
			"viewtype" => "month",
			"date" => date("d-m-Y",mktime(0,0,0,$m-1,$d,$y)),
		));
		
		$next_url = aw_url_change_var(array(
			"viewtype" => "month",
			"date" => date("d-m-Y",mktime(0,0,0,$m+1,$d,$y)),
		));
		$caption = "<div class='$style_title'><a href='$prev_url'>&lt;&lt;</a> <a href='$caption_url'>$caption</a> <a href='$next_url'>&gt;&gt;</a>";
		return $caption . "<table border=0 cellspacing=0 cellpadding=0 width='100%'><tr><td class='$style_background'><table border=0 cellpadding=0 cellspacing=1 width='100%'>" . $w . "</table></td></tr></table>";
	}

	function draw_event($evt)
	{
		$m = date("m",$evt["timestamp"]);
		$lc_month = get_lc_month($m);
		if (isset($evt["url"]))
		{
			$evt["link"] = $evt["url"];
		};
	
		// XXX: this should SO not be here
		if ($this->evt_tpl->template_has_var("first_image"))
		{
			$evt_obj = new object($evt["id"]);
			$pic_conns = $evt_obj->connections_from(array(
				"type" => "RELTYPE_PICTURE",
			));
			$first = reset($pic_conns);
			$img_url = "/img/trans.gif";
			$img = get_instance(CL_IMAGE);
			if (is_object($first))
			{
				$img_url = $img->get_url_by_id($first->prop("to"));
			}
			else if (!empty($evt["project_image"]))
			{
				$img_url = $evt["project_image"];
			};
			$evt["first_image"] = $img_url;
		};

		$this->evt_tpl->vars(array(
			"parent_1_name" => "",
			"parent_2_name" => "",
			"parent_3_name" => "",
		));

		$this->evt_tpl->vars($evt);

		$dt = date("d",$evt["start1"]);
                $mn = locale::get_lc_month(date("m",$evt["start1"]));
                $mn .= " " . date("H:i",$evt["start1"]);

		
		$this->evt_tpl->vars(array(
			"time" => date("H:i",$evt["timestamp"]),
			"date" => date("j-m-Y H:i",$evt["timestamp"]),
			"datestamp" => date("d.m.Y",$evt["timestamp"]),
			"aw_date" => date("d-m-Y",$evt["timestamp"]),
			"lc_date" => date("j",$evt["timestamp"]) . ". " . $lc_month . " " . date("Y H:i",$evt["timestamp"]),
			"name" => $evt["name"],
			"id" => $evt["id"],
			"link" => !empty($evt["link"]) ? $evt["link"] : "javascript:void(0)",
			"modifiedby" => $evt["modifiedby"],
			"iconurl" => !empty($evt["icon"]) ? $evt["icon"] : "/automatweb/images/trans.gif",
			"COMMENT" => "",
			"comment" => $evt["comment"],
			"day_name" => substr(get_lc_weekday(date("w",$evt["start1"])),0,1),
                        "date_and_time" => $dt . ". " . $mn,

		));


		if (!empty($evt["comment"]))
		{
			$this->evt_tpl->vars(array(
					'comment_content' => $evt['comment'],
			));

			$this->evt_tpl->vars(array(
				"COMMENT" => $this->evt_tpl->parse("COMMENT"),
			));
		}
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
