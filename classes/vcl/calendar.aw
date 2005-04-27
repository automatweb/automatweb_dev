<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/calendar.aw,v 1.58 2005/04/27 11:25:05 ahti Exp $
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

		$this->output_initialized = false;
		
	}

	function has_feature($feature)
	{
		$retval = false;
		// call this after configure ..
		if (is_object($this->evt_tpl) && $feature == "first_image")
		{
			if ($this->evt_tpl->template_has_var("first_image"))
			{
				$retval = true;
			}
		};

		if (is_object($this->evt_tpl) && $feature == "project_media")
		{
			if ($this->evt_tpl->is_template("project_media"))
			{
				$retval = true;
			}
		};
		return $retval;

	}

	function init_output($arr)
	{
		$this->evt_tpl = get_instance("aw_template");
		$this->evt_tpl->tpl_init($this->cal_tpl_dir);
		$tpl = $this->range["viewtype"] == "relative" ? "sub_event2.tpl" : "sub_event.tpl";
		$better_template = "";
		if ($arr["event_template"])
		{
			$tpl = $arr["event_template"];
		}
		else
		if ($this->range["viewtype"] == "week")
		{
			$better_template = "week_event.tpl";
		};
		$got_it = false;
		if ($better_template)
		{
			$got_it = $this->evt_tpl->read_template($better_template,1);
		};
		if (!$got_it)
		{
			$this->evt_tpl->read_template($tpl);
		};
		$this->output_initialized = true;
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
	// day_start - array (hour,minute) of day start
	// day_end - array (hour,minute) of day end
	function configure($arr = array())
	{
		$attribs = array("tasklist_func","overview_func","overview_range",
			"container_template","show_days_with_events","skip_empty",
			"full_weeks","target_section","day_start","day_end", "show_ec", "filt_views");

		foreach($attribs as $attrib)
		{
			if (!empty($arr[$attrib]))
			{
				$this->$attrib = $arr[$attrib];
			};
		};

		// fact is, event list and overview should use different functions,
		// cause they need different kinds of data. It should be faster with
		// different functions
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
		if($arr["show_ec"])
		{
			$range = $arr["show_ec"] + $range;
		}
		$this->range = $range;
		return $range;
	}

	// I need methods for adding item AND for drawing
	// timestamp 
	// data - arr
	function add_item($arr)
	{
		if ($_GET["DD"] == 1)
		{
			arr($arr);
			//echo "id = ".$arr["data"]["id"]." name = ".$arr["data"]["name"]." start = ".date("d.m.Y H:i", $arr["data"]["start1"])." <br>";
		}

		if (!empty($arr["item_start"]))
		{
			$arr["timestamp"] = $arr["item_start"];
			if (empty($arr["item_end"]))
			{
				$arr["item_end"] = $arr["item_start"];
			};
			// experimental support for multiday events
			if ($arr["item_start"] > $arr["item_end"])
			{
				// hehe, textbook problem .. swap variables
				// but I don't do that, since I'm afraid that there are too many
				// events where the start date is later than end. But maybe
				// I shouldn't care 
				$arr["item_end"] = $arr["item_start"];
				/*
				$tmp = $arr["item_end"];
				$arr["item_end"] = $item["start"];
				$arr["item_start"] = $tmp;
				*/

			};
		};

		// convert timestamp to day, since calendar is usually day based
		$use_date = date("Ymd",$arr["timestamp"]);
		$this->el_count++;
		$data = $arr["data"];
		$data["timestamp"] = $arr["timestamp"];

		if ($arr["item_start"])
		{
			$data["item_start"] = $arr["item_start"];
			$start_tm = (int)($data["item_start"] / 86400);
		};
		if ($arr["item_end"])
		{
			$data["item_end"] = $arr["item_end"];
			$end_tm = (int)($data["item_end"] / 86400);
			list($ed,$em,$ey) = explode("/",date("d/m/Y",$arr["item_end"]));
		};

		$data["_id"] = $this->el_count;
		$data["id"] = $arr["data"]["id"];
		$data["comment"] = $arr["data"]["comment"];
		$data["utextarea1"] = nl2br($data["utextarea1"]);


		if ($end_tm > $start_tm)
		{
			$data["item_end"] = mktime($this->day_end["hour"],$this->day_end["minute"],59,$em,$ed,$ey);
			//$data["time"] = "Algab: " . date("H:i",$data["item_start"]);
		};

		$this->evt_list[$this->el_count] = $data;
		$this->items[$use_date][] = &$this->evt_list[$this->el_count];

		// deal with passed recurrence information

		// aga ääki peaks seda üldse recurrence abil tegema? Sest kalendri komponent
		// ei tea ju midagi kalendripäeva algusest ja lõpust. Ja ei saagi teada

		// actually, I can pass that information to the component through the configure method
		// so it is not really a problem
		if (isset($arr["recurrence"]) && is_array($arr["recurrence"]))
		{
			$this->recur_info[$this->el_count] = $arr["recurrence"];
			foreach($arr["recurrence"] as $tm)
			{
				$use_date = date("Ymd",$tm);
				$this->items[$use_date][] = &$this->evt_list[$this->el_count];
			};
		};

		// this will deal with multi-day events and will add events on other days
		// besides the first one
		if (isset($arr["item_end"]))
		{
			$arr["item_start"]+= 86400;
			//$arr["item_end"]+= 85399;
			//$arr["item_start"] += 86400;
			// okey .. first day needs to end at the specified time
			// second (and all the remainders start at specified time)
			$days_between = $end_tm - $start_tm;
			$day_counter = 0;
			for ($i = $arr["item_start"]; $i <= $arr["item_end"]; $i = $i + 86400)
			{
				$day_counter++;
				$use_date = date("Ymd",$i);
				// but .. I do not need to use those references .. yees?
				$tmp = $this->evt_list[$this->el_count];
				// aga siin on mul vaja teada päeva algust
				$tmp["item_start"] = mktime($this->day_start["hour"],$this->day_start["minute"],0,1,1,2005);
				//$tmp["item_start"] = mktime(0,0,0,1,1,2005);

				// siin tuleb kuidagi kindlaks teha .. et kui tegemist on viimase
				// päevaga, siis näitame ka õiget kellaaega
				if ($day_counter == $days_between)
				{
					//$tmp["time"] = "Lõpeb: " . date("H:i",$arr["item_end"]);
					$tmp["item_end"] =  $arr["item_end"];
				}
				else
				{
					//$tmp["time"] = "";
				};

				// ahaa.. sest see on juba lisatud!
				// miks see kontroll lisab asju, mis on juba lisatud?
				/*
				if ($i == $arr["item_start"])
				{
					$tmp["item_end"] = 666;
				};
				*/
				/*
				if ($_GET["XX6"])
				{
					print "adding";
					arr($tmp);
				};
				*/
				$this->items[$use_date][] = $tmp;
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
		if ($GLOBALS["SITT"])
		{
			var_dump(error_reporting(0));

		};
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
		$this->aliasmgr = get_instance("aliasmgr");
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

		if (!$this->output_initialized)
		{
			$this->init_output($arr);
		};
		
		$this->event_counter = 0;


		if (!empty($arr["text"]))
		{
			$content = $arr["text"];
		}
		else
		{
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
					$content = $this->draw_day($arr);
					$caption = date("j. ",$this->range["timestamp"]) . locale::get_lc_month(date("m",$this->range["timestamp"])) . date(" Y",$this->range["timestamp"]);
			};
		};
		
		classload("date_calc");
		$m = date("m",$this->range["timestamp"]);
		$y = date("Y",$this->range["timestamp"]);

		$mn = "";
		// this one draws overview months... teheheheeee
		// if overview_func is not defined, then no overview thingie will be drawn
		// it's that easy.
		enter_function("vcl/calendar::get_html::overview");
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
		exit_function("vcl/calendar::get_html::overview");


		$this->read_template($this->container_template);
		$types = array(
			"today" => t("Täna"),
			"day" => t("Päev"),
			"week" => t("Nädal"),
			"month" => t("Kuu"),
			"relative" => t("Ülevaade"),
		);
		$ts = "";
		if(count($this->filt_views) > 0)
		{
			$funcs = $this->filt_views;
		}
		else
		{
			$funcs = array_keys($types);
		}

		foreach($types as $type => $name)
		{
			if(!in_array($type, $funcs))
			{
				continue;
			}
			$link = aw_url_change_var("viewtype",$type);
			$this->vars(array(
				"link" => aw_url_change_var("viewtype",$type),
				"text" => $name,
			));
			if($type == "today")
			{
				$ts .= $this->parse("TODAY");
			}
			else
			{
				$ts .= $this->parse(($type == $this->range["viewtype"]) ? "SEL_PAGE" : "PAGE");
			}
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
				"tasks_title" => t("Toimetused"),
			));

			$this->vars(array(
				"TASKS" => $this->parse("TASKS"),
			));
		};

		for ($i = 1; $i <= 12; $i++)
		{
			$mnames[$i] = locale::get_lc_month($i);
		};

		for ($i = 2003; $i <= 2010; $i++)
		{
			$years[$i] = $i;
		};

		// I'm trying to get the javascript function inside the template to generate
		// a correct url
		$urlsufix = strpos(aw_global_get("REQUEST_URI"),"?") === false ? "?" : "";
		$prevlink = aw_url_change_var(array(
			"date" => $this->range["prev"],
			"section" => $this->target_section,
		));

		$nextlink = aw_url_change_var(array(
			"date" => $this->range["next"],
			"section" => $this->target_section,
		));
		if(!empty($this->show_days_with_events) && !empty($this->event_sources) && $this->fix_links)
		{
			enter_function("vcalendar::show_days_with_events");
			if(!empty($this->first_event))
			{
				$objs = new object_list(array(
					"parent" => $this->event_sources,
					"class_id" => $this->event_entry_classes,
					"start1" => new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $this->first_event["start1"]),
					"brother_of" => new obj_predicate_prop("id"),
					"oid" => new obj_predicate_not($this->first_event["id"]),
					"status" => $this->obj_status,
					"limit" => $this->limit_events,
				));
				if($obj = $objs->end())
				{
					$this->vars(array(
						"prevlink" => aw_url_change_var(array(
							"date" => date("d-m-Y", $obj->prop("start1")),
							"section" => $this->target_section,
						))
					));
					$prev = $this->parse("PREV");
				}
			}
			if(!empty($this->last_event))
			{
				$objs = new object_list(array(
					"parent" => $this->event_sources,
					"class_id" => $this->event_entry_classes,
					"start1" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $this->first_event["start1"]),
					"brother_of" => new obj_predicate_prop("id"),
					"oid" => new obj_predicate_not($this->first_event["id"]),
					"status" => $this->obj_status,
					"limit" => $this->limit_events,
				));
				if($obj = $objs->begin())
				{
					$this->vars(array(
						"nextlink" => aw_url_change_var(array(
							"date" => date("d-m-Y", $obj->prop("start1")),
							"section" => $this->target_section,
						)),
					));
					$next = $this->parse("NEXT");
				}
			}
			exit_function("vcalendar::show_days_with_events");
		}
		if ($this->template_has_var("prevweek_link"))
		{
			$weekrange = get_date_range(array(
				"type" => "week",
				"time" => $this->range["timestamp"],
			));

			$this->vars(array(
				"prevweek_link" => aw_url_change_var(array(
					"viewtype" => "week",
					"date" => $weekrange["prev"],
					"target_section" => $this->target_section,
				)),
				"nextweek_link" => aw_url_change_var(array(
					"viewtype" => "week",
					"date" => $weekrange["next"],
					"target_section" => $this->target_section,
				)),
			));
		};

		$this->vars(array(
			"RANDOM" => $this->random,
			"YEARS" => $this->years,
			"PAGE" => $ts,
			"PREV" => $prev,
			"NEXT" => $next,
			"mininaviurl" => aw_url_change_var("date","") . $urlsufix,
			"naviurl" => aw_url_change_var("date",""),
			"mnames" => html::picker((int)$m,$mnames),
			"years" => html::picker($y,$years),
			"content" => $content,
			"caption" => $caption,
			"prevlink" => $prevlink,
			"nextlink" => $nextlink,
			"overview" => $mn,
			"today_url" => aw_url_change_var(array("viewtype" => "day","date" => date("d-m-Y"))),
			"today_date" => date("d.m.Y"),
			"act_day_tm" => $this->range["timestamp"],
		));

		$awt->stop("gen-calendar-html");

		$rv = $this->parse();
		return $rv;
	}

	////
	// !How to I make overview work. It's longer than the usual span ..
	// so I need some way to do define additonal items for the navigator.

	function draw_month()
	{
		//$this->read_template("week_view.tpl");
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
					if(!$this->first_event)
					{
						$this->first_event = reset($events);
					}
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

				$block_id = date("Ymd",$reals);

				// this will most most likely break templates with grids, but works
				// fine with event lists .. which is the way it should be used anyway
				if (empty($calendar_blocks[$block_id]) && 1 == $this->show_days_with_events)
				{
					continue;
				};

				$this->vars(array(
					"EVENT" => $calendar_blocks[$block_id],
					"daynum" => date("j",$reals),
					"dayname" => date("F d, Y",$reals),
					"date" => locale::get_lc_date($reals,5),
					"lc_weekday" => locale::get_lc_weekday(date("w",$reals)),
					"lc_month" => locale::get_lc_month(date("m",$reals)),
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
		$this->last_event = $event;

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
		if ($GLOBALS["DX"] == 1)
		{
			arr($this->items);
		};


		for ($i = 1; $i <= 12; $i++)
		{
			$this->vars(array(
				"month_name" => locale::get_lc_month($i),	
			));
			$header = $this->parse("HEADER");
			$footer = $this->parse("FOOTER");
			// nüüd on mul vaja iga kuu kohta algust ja lõppu

			$ms = mktime(0,0,0,$i,1,$y);
			$me = mktime(23,59,59,$i+1,0,$y);

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
					if(!$this->first_event)
					{
						$this->first_event = reset($events);
					}
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
		$this->last_event = $event;

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
		if($this->adm_day)
		{
			$dcheck = $this->parse("DCHECK");
		}

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
				uasort($events, array($this,"__asc_sort"));
				if(!$this->first_event)
				{
					$this->first_event = reset($events);
				}
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
				"DCHECK" => $dcheck,
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
		$this->last_event = $event;
		$this->vars(array(
			"DAY" => $rv,
		));
		return $this->parse();
	}
	
	function draw_day($arr = array())
	{
		$ct_template = !empty($arr["container_template"]) ? $arr["container_template"] : "day_view.tpl";
		$this->vars(array(
			"EVENT" => "",
		));
		$this->read_template($ct_template);
		$dcheck = "";
		if($this->adm_day)
		{
			$dcheck = $this->parse("DCHECK");
		}
		$dstamp = date("Ymd",$this->range["start"]);
		$events_for_day = "";
		if (is_array($this->items[$dstamp]))
		{
			$events = $this->items[$dstamp];
			uasort($events,array($this,"__asc_sort"));
			if(!$this->first_event)
			{
				$this->first_event = reset($events);
			}
			foreach($events as $event)
			{
				$events_for_day .= $this->draw_event($event);
			};
		};
		$this->last_event = $event;
		$i = $this->range["start"];
		$dt = date("d",$i);
		$mn = get_lc_month(date("m",$i));
		$this->vars(array(
			"DCHECK" => $dcheck,
			"EVENT" => $events_for_day,
			"daynum" => date("j",$this->range["start"]),
			"dayname" => date("F d, Y",$this->range["start"]),
			"long_day_name" => locale::get_lc_weekday($this->range["wd"]),
			"date" => locale::get_lc_date($this->range["start"],5),
			"caption" => $arr["caption"],
			"lc_weekday" => locale::get_lc_weekday(date("w",$this->range["start"])),
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
		$this->read_template("minical.tpl");


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
			$day = "";
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
					$day_url = aw_url_change_var(array(
						"viewtype" => "day",
						"event_id" => "",
						"date" => date("d-m-Y",$reals),
						"section" => $this->target_section,
					));
				};

				// cell_empty has class, doesn't have a link, used to show days with no events
				// cell - has class, has link, used to show days with events
				// cell_deact - has a class, doesn't have a link, used to show days outside the current range

				// and that pretty much is it.

				// I set default styles in the container template and let them be overriden
				if($mode == 0)
				{
					$link = "<a href='$day_url'>".date("j",$reals)."</a>";
				}
				else
				{
					$link = date("j",$reals);
				}
				$this->vars(array(
					"style" => $style,
					"link" => $link,
				));
				$day .= $this->parse("DAY");
				$i = $i + 86400;
			};
			$rv = "";
			$this->vars(array(
				"DAY" => $day,
			));
			$week .= $this->parse("WEEK");
			$j = $j + (7*86400);
		};

		// now, how to make those configurable?
		$this->vars(array(
			"WEEK" => $week,
			"style_title" => $style_title,
			"style_background" => $style_background,
			"caption" => locale::get_lc_month(date("m", $arr["timestamp"])) . " " . date("y",$arr["timestamp"]),
			"caption_url" => aw_url_change_var(array(
				"viewtype" => "month",
				"date" => date("d-m-Y",$arr["timestamp"]),
				"section" => $this->target_section,
			)),
			"prev_date" => date("d-m-Y",mktime(0,0,0,$m-1,$d,$y)),
			"next_date" => date("d-m-Y",mktime(0,0,0,$m+1,$d,$y)),
			"section_id" => $this->target_section,
			"next_url" => aw_url_change_var(array(
				"viewtype" => "month",
				"date" => date("d-m-Y",mktime(0,0,0,$m+1,$d,$y)),
				"section" => $this->target_section,
			)),
			"prev_url" => aw_url_change_var(array(
				"viewtype" => "month",
				"date" => date("d-m-Y",mktime(0,0,0,$m-1,$d,$y)),
				"section" => $this->target_section,
			))
		));
		return $this->parse();
	}

	function draw_event($evt)
	{
		$m = date("m",$evt["timestamp"]);
		$lc_month = get_lc_month($m);
		if (isset($evt["url"]))
		{
			$evt["link"] = $evt["url"];
		};

		$this->evt_tpl->vars(array(
			"parent_1_name" => "",
			"parent_2_name" => "",
			"parent_3_name" => "",
		));
		if($evt["class_id"] == CL_PARTY)
		{
			$fa = safe_array($evt["from_artist"]);
			$obj = obj($evt["id"]);
			$objs = new object_list(array(
				"brother_of" => $obj->brother_of(),
			));
			$proj = array();
			foreach($objs->arr() as $obz)
			{
				$obx = obj($obz->parent());
				if($obx->class_id() == CL_PROJECT)
				{
					$proj[] = $obx->name();
				}
			}
			$evt["project"] = implode(", ", $proj);
			$meta = $obj->meta("artists");
			unset($fa[0]);
			if(count($fa) > 0 && ($artist = $obj->get_first_obj_by_reltype("RELTYPE_ARTIST")))
			{
				if($fa["content"])
				{
					$evt["content"] = $artist->prop("notes");
				}
				if($fa["image"])
				{
					$evt["image"] = $artist->prop("picture");
				}
			}
			else
			{
				$art = array();
				foreach($artists = $obj->connections_from(array("type" => "RELTYPE_ARTIST")) as $artist)
				{
					$id = $artist->prop("to");
					$art[] = array(
						"id" => $id,
						"name" => $artist->prop("to.name"),
						"ord" => $meta["ord"][$id],
						"profession" => $meta["profession"][$id],
					);
				}
				uasort($art, array($this, "__sort_by_ord"));
				$xz = array();
				foreach($art as $a)
				{
					$x = html::href(array(
						"url" => obj_link($a["id"]),
						"caption" => $a["name"],
					));
					/*
					if(count($a["profession"]) > 0)
					{
						$profs = array();
						foreach($a["profession"] as $prof)
						{
							if(is_oid($prof) && $this->can("view", $prof))
							{
								$ob = obj($prof);
								$profs[] = $ob->name();
							}
						}
						$x .= " - ".implode(", ", $profs);
					}
					*/
					$xz[] = $x;
				}
				$evt["artist"] = implode(", ", $xz);
			}
			if($image = $obj->get_first_obj_by_reltype("RELTYPE_FLYER"))
			{
				$flyer_i = get_instance(CL_FLYER);
				$evt["image"] = $flyer_i->show($image);
			}
			$evt["content"] = nl2br($evt["content"]);
			$this->aliasmgr->parse_oo_aliases($evt["id"], $evt["content"]);
		}
		

		$this->evt_tpl->vars($evt);

		$dt = date("d",$evt["start1"]);
		$mn = locale::get_lc_month(date("m",$evt["start1"]));
		$mn .= " " . date("H:i",$evt["start1"]);

		if($this->adm_day)
		{
			$dchecked = $this->evt_tpl->parse("DCHECKED");
		}
		/*
		if ($_GET["XX6"])
		{
			arr($evt);
		};
		*/
		if (!isset($evt["time"]))
		{
			if ($evt["item_start"] && $evt["item_end"] && $evt["item_start"] != $evt["item_end"])
			{
				$time = date("H:i",$evt["item_start"]) . " - " . date("H:i",$evt["item_end"]);
			}
			else
			{
				$time = date("H:i",$evt["timestamp"]);
			};
		}
		else
		{
			$time = $evt["time"];
		}
		$title = sprintf(t("Lisas [%s] %s /  Muutis [%s] %s"), $evt["createdby"], date("d.m.y", $evt["created"]), $evt["modifiedby"], date("d.m.y", $evt["modified"]));
		
		$this->evt_tpl->vars(array(
			"title" => $title,
			"odd" => $this->event_counter % 2,
			"time" => $time,
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
			"DCHECKED" => $dchecked,
			"comment" => $evt["comment"],
			"day_name" => strtoupper(substr(get_lc_weekday(date("w",$evt["start1"])),0,1)),
			"date_and_time" => $dt . ". " . $mn,
			"section" => aw_global_get("section")
		));
		$this->event_counter++;


		if (!empty($evt["comment"]))
		{
			$this->evt_tpl->vars(array(
				'comment_content' => $evt['comment'],
			));

			$this->evt_tpl->vars(array(
				"COMMENT" => $this->evt_tpl->parse("COMMENT"),
			));
		}

		if ($this->evt_tpl->is_template("project_media"))
		{
			$this->evt_tpl->vars($evt["media"]);
			$x = $this->evt_tpl->parse("project_media");
			$this->evt_tpl->vars(array(
				"project_media" => $x,
			));
		};
		

		return $this->evt_tpl->parse();
	}
	
	function __sort_by_ord($el1, $el2)
	{
		return $el1["ord"] - $el2["ord"];
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
