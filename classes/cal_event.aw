<?php
// cal_event.aw - Kalendri event
// $Header: /home/cvs/automatweb_dev/classes/Attic/cal_event.aw,v 2.9 2002/02/07 02:08:05 duke Exp $
global $class_defs;
$class_defs["cal_event"] = "xml";

class cal_event extends aw_template {
	function cal_event($args = array())
	{	
		extract($args);
		$this->db_init();
		$this->tpl_init("cal_event");
	}
	
	////
	// !Joonistab menüü
	// argumendid:
	// activelist(array), levelite kaupa info selle kohta, millised elemendid
	// aktiivsed on
	// vars(array) - muutujad, mida xml-i sisse pannakse
	function gen_menu($args = array())
	{
		extract($args);
		global $basedir;
		load_vcl("xmlmenu");
		$xm = new xmlmenu();
		$xm->vars($vars);
		$xm->load_from_files(array(
					"xml" => $basedir . "/xml/planner/event_menu.xml",
					"tpl" => $this->template_dir . "/menus.tpl",
				));
		return $xm->create(array(
				"activelist" => $activelist,
			));
	}

	////
	// !Fills the event editing form with data
	function _fill_event_form($args = array())
	{
		load_vcl("date_edit");
		$start = new date_edit("start");
		$start->configure(array("day" => 1,"month" => 2,"year" => 3));
		$start_ed = $start->gen_edit_form("start",$args["start"]);
		list($shour,$smin) = split("-",date("G-i",$args["start"]));
		if ($args["end"])
		{
			$dsec = $args["end"] - $args["start"];
			$dhour = (int)($dsec / (60 * 60));
			$dsec = $dsec - ($dhour * 60 * 60);
			$dmin = (int)($dsec / 60);
		}
		else
		{
			$dsec = $args["start"];
			$dhour = 1;
			$dmin = 0;
		};
		$colors = array(
			"#000000" => "must",
			"#990000" => "punane",
			"#009900" => "roheline",
			"#000099" => "sinine",
		);

		$calendars = array();
		$this->get_objects_by_class(array(
			"class" => CL_CALENDAR,
			"active" => 1,
		));
		while($row = $this->db_next())
		{
			if ($row["name"])
			{
				$calendars[$row["oid"]] = $row["name"];
			};
		};

		// nimekiri tundidest
                $h_list = range(0,23);
                // nimekiri minutitest
                $m_list = array("0" => "00", "15" => "15", "30" => "30", "45" => "45");
		if ($args["oid"])
		{
			$obj = $this->get_object($args["oid"]);
		};
		$types = array("linki objektile","objekti ennast");
		$this->vars(array(
			"start" => $start_ed,
			"shour" => $this->picker($shour,$h_list),
			"smin" => $this->picker($smin,$m_list),
			"calendars" => $this->picker($args["folder"],$calendars),
			"showtype" => $this->picker($args["meta"]["showtype"],$types),
			"dhour" => $this->picker($dhour,$h_list),
			"dmin" => $this->picker($dmin,$m_list),
			"object" => $obj["name"],
			"obj_icon" => get_icon_url($obj["class_id"],""),
			"color" => $this->picker($args["color"],$colors),
			"calendar_url" => $this->mk_my_orb("view",array("id" => $args["folder"]),"planner"),
			"icon_url" => get_icon_url(CL_CALENDAR,""),
		));
	}

	////
	// !Kuvab uue eventi lisamise vormi
	function add($args = array())
	{
		extract($args);
		$par_obj = $this->get_object($parent);
		$this->read_template("edit.tpl");
		$this->mk_path($parent,"Lisa kalendrisündmus");
		if ($date)
		{
			list($d,$m,$y) = explode("-",$date);
			$start = mktime(9,0,0,$m,$d,$y);
		}
		else
		{
			$start = time();
		};
		$this->_fill_event_form(array("folder" => $args["folder"],"start" => $start));
		$this->vars(array(
			"reforb" => $this->mk_reforb("submit",array("parent" => $parent)),
		));
		return $this->parse();
	}

	////
	// !Submitib uue kalendri eventi
	function submit($args = array())
	{
		$this->quote($args);
		extract($args);
		// sellest teeme timestampi
		$st = mktime($shour,$smin,0,$start["month"],$start["day"],$start["year"]);
		// lopu aeg
		$et = mktime($shour + $dhour,$smin + $dmin,59,$start["month"],$start["day"],$start["year"]);

		if ($parent)
		{
			$par_obj = $this->get_object($parent);
			$parent_class = $par_obj["class_id"];
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $title,
				"class_id" => CL_CAL_EVENT,
				"status" => 2,
			));

			$this->upd_object(array(
				"oid" => $id,
				"metadata" => array("repcheck" => $repcheck,"showtype" => $showtype),
			));

			$q = "INSERT INTO planner
				(id,start,end,title,place,description,color,folder)
                                VALUES ('$id','$st','$et','$title','$place','$description','$color','$folder')";
			$this->db_query($q);
		}
		else
		{
			if ($repcheck)
			{
				$mt = array("repcheck" => $repcheck,"showtype" => $showtype);
			}
			else
			{
				// if the repcheck checkbox was unchecked then we need 
				// to zero out all the information about repeaters
				$mt = array("repcheck" => $repcheck,"repeaters" => array(),"showtype" => $showtype);
			}
			$obj = $this->get_object($id);
			$par_obj = $this->get_object($obj["parent"]);
			$parent_class = $par_obj["class_id"];
			$this->upd_object(array(
				"oid" => $id,
				"name" => $title,
				"metadata" => $mt,
			));
			                         
			$q = "UPDATE planner SET
				start = '$st',
				end = '$et',
				title = '$title',
				color = '$color',
				place = '$place',
				folder = '$folder',
				description = '$description'
				WHERE id = '$id'";
			$this->db_query($q);
		}

		if ($parent_class == CL_CALENDAR)
		{
			$clid = "planner";
			$act = ($object) ? "event_object_search" : "change_event";
		}
		else
		{
			$clid = "cal_event";
			$act = ($object) ? "search" : "change";
		};
		// we saved the event, and now we can go and check whether and how
		// to add an object
		if ($object)
		{
			return $this->mk_my_orb($act,array("id" => $id),$clid);
		}
		else
		{
			return $this->mk_my_orb($act,array("id" => $id),$clid);
		};
	}

	////
	// !Kuvab olemasoleva eventi muutmise objekti
	function change($args = array())
	{
		extract($args);
		$object = $this->get_obj_meta($id);
		$par_obj = $this->get_object($object["parent"]);
		$meta = $object["meta"];

		$menubar = $this->gen_menu(array(
				"activelist" => array("event"),
				"vars" => array("id" => $id),
		));

		$q = "SELECT *,planner.* FROM objects LEFT JOIN planner ON (objects.oid = planner.id) WHERE objects.oid = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$this->read_template("edit.tpl");
		$row["meta"] = $meta;
		$this->_fill_event_form($row);
		$cal_link = sprintf("<a href='%s'><img border='0' src='%s'>Kalender</a>",$this->vars["calendar_url"],$this->vars["icon_url"]);
		$this->mk_path($object["parent"],"$cal_link | Muuda kalendrisündmust");

		if ($par_obj["class_id"] == CL_CALENDAR)
		{
			$rep_link = $this->mk_my_orb("event_repeaters",array("id" => $id),"planner");
		}
		else
		{
			$rep_link = $this->mk_my_orb("repeaters",array("id" => $id));
		};

		$this->vars(array(
			"rep_link" => $rep_link,
			"rep_link2" => $this->mk_my_orb("event_repeaters",array("id" => $id,"stage" => 2),"planner"),
		));

		$this->vars(array(
			"menubar" => $menubar,
			"id" => $id,
			"title" => $row["title"],
			"place" => $row["place"],
			"repcheck" => checked($meta["repcheck"]),
			"repeaters" => ($meta["repcheck"]) ? $this->parse("repeaters") : "",
			"description" => $row["description"],
			"reforb" => $this->mk_reforb("submit",array("id" => $id)),
		));
		return $this->parse();
	}

	////
	// !Kuvab repeaterite sisestamise vormi
	function repeaters($args = array())
	{
		extract($args);

		$obj = $this->get_obj_meta($id);
		$obj_meta = $obj["meta"];

		$par_obj = $this->get_object($obj["parent"]);
		
		$menubar = $this->gen_menu(array(
				"activelist" => array("repeaters"),
				"vars" => array("id" => $id),
		));

		$this->mk_path($par_obj["parent"],"Muuda eventit");


		// what's that?
		$caldata = $obj_meta["calconfig"];
		$cycle_counter = $obj_meta["cycle_counter"];

		if (is_array($caldata))
		{
			extract($caldata);
		}

		// if cycle is set, then we alter a specific cycle, otherwise we just
		// show the list
		$new = false;
		if ($cycle)
		{
			$this->read_template("edit_repeater.tpl");
			// if it is a predefined cycle, then we load it
			if (is_number($cycle))
			{
				$meta = $this->get_object_metadata(array(
							"oid" => $id,
							"key" => "repeaters" . $cycle,
				));
			}
			// so it must be a new one
			else
			{
				// we have to figure out the last cycle number in use
				$cycle = $this->get_object_metadata(array(
							"oid" => $id,
							"key" => "cycle_counter",
				));
				$new = 1;
				// us the next available
				if ($cycle)
				{
					$cycle++;
				}
				else
				{
					// nothing found, so we set it to 1
					$cycle = 1;
				};
				$meta = array();
			};
		
			load_vcl("date_edit");
			$repend = new date_edit("start");
			$repend->configure(array("day" => 1,"month" => 2,"year" => 3));
			if ($meta["repend"])
			{
				$_tmp = mktime(0,0,0,$meta["repend"]["month"],$meta["repend"]["day"],$meta["repend"]["year"]);
			}
			else
			{
				$_tmp = strtotime("+1 week");
			};
			$repend_ed= $repend->gen_edit_form("repend",$_tmp);
			// oh, I know, this is sooo ugly
			$this->vars(array(
					"region1" => checked($meta["region1"]),
					"dayskip" => ($meta["dayskip"] > 0) ? $meta["dayskip"] : 1,
					"day1" => checked($meta["day"] == 1),
					"day2" => checked($meta["day"] == 2),
					"day3" => checked($meta["day"] == 3),
					"wday1" => checked($meta["wday"][1]),
					"wday2" => checked($meta["wday"][2]),
					"wday3" => checked($meta["wday"][3]),
					"wday4" => checked($meta["wday"][4]),
					"wday5" => checked($meta["wday"][5]),
					"wday6" => checked($meta["wday"][6]),
					"wday7" => checked($meta["wday"][7]),
					"monpwhen2" => $meta["monpwhen2"],
					"region2" => checked($meta["region2"]),
					"week1" => checked($meta["week"] == 1),
					"week2" => checked($meta["week"] == 2),
					"weekskip" => ($meta["weekskip"] > 0) ? $meta["weekskip"] : 1,
					"mweek1" => checked($meta["mweek"][1]),
					"mweek2" => checked($meta["mweek"][2]),
					"mweek3" => checked($meta["mweek"][3]),
					"mweek4" => checked($meta["mweek"][4]),
					"mweek5" => checked($meta["mweek"][5]),
					"mweeklast" => checked($meta["mweek"]["last"]),
					"region3" => checked($meta["region3"]),
					"month1" => checked($meta["month"] == 1),
					"month2" => checked($meta["month"] == 2),
					"monthskip" => ($meta["monthskip"] > 0) ? $meta["monthskip"] : 1,
					"yearpwhen" => ($meta["yearpwhen"] > 0) ? $meta["yearpwhen"] : 1,
					"region4" => checked($meta["region4"]),
					"yearskip" => ($meta["yearskip"] > 0) ? $meta["yearskip"] : 1,
					"repend" => $repend_ed,
					"repeats" => ($meta["repeats"]) ? $meta["repeats"] : 6,
					"rep1_checked" => ($meta["rep"]) ? checked($meta["rep"] == 1) : "checked",
					"rep2_checked" => checked($meta["rep"] == 2),
					"rep3_checked" => checked($meta["rep"] == 3),
					"reforb" => $this->mk_reforb("submit_repeaters",array("id" => $id,"cycle" => $cycle,"new" => $new),"cal_event"),
			));
		}
		else
		{
			$this->read_template("repeaters.tpl");

			$content = "";

			if ( ($cycle_counter > 0) && (is_array($obj_meta)) )
			{
				for ($i = 1; $i <= $cycle_counter; $i++)
				{
					$key = sprintf("repeaters%d",$i);
					if ($obj_meta[$key])
					{
						$repdat = $obj_meta[$key];
						$this->vars(array(
							"id" => $i,
							"start" => "trill",
							"end" => "trall",
						));
						$content .= $this->parse("line");
					};
				};
			};

			$this->vars(array(
				"add_link" => $this->mk_my_orb("event_repeaters",array("id" => $id,"cycle" => "new"),"planner"),
				"ed_link" => $this->mk_my_orb("event_repeaters",array("id" => $id),"planner"),
				"del_link" => $this->mk_my_orb("delete_repeater",array("id" => $id),"planner"),
				"line" => $content,
			));
		}

		$this->vars(array(
			"menubar" => $menubar,
		));
			
		
		return $this->parse();
	}

	////
	// !Saves the repeaters and calculates the dates when the correspondending event occurs
	function submit_repeaters($args = array())
	{
		extract($args);
		
		// store them inside the object so we can use them elsewhere
		$this->repeats = $args;
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		$parent_class = $par_obj["class_id"];

		$key = "repeaters" . $cycle;
		$this->set_object_metadata(array(
			"oid" => $id,
			"overwrite" => 1,
			"key" => $key,
			"value" => $args,
		));
		
		// if that one was a new cycle, then we need update the object as well
		if ($new)
		{
			$cycle = $this->set_object_metadata(array(
					"oid" => $id,
					"key" => "cycle_counter",
					"value" => $cycle, // 1
			));
		};

		$q = "SELECT *,planner.* FROM objects LEFT JOIN planner ON (objects.oid = planner.id)
			WHERE objects.oid = '$id'";
		$this->db_query($q);
		$event = $this->db_next();


		if ($stage == 2)
		{
			// have to figure out the day the previous repeaters end
			$event["start"] = $event["rep_until"];
			$this->reps = aw_unserialize($event["repeaters"]);
		}
		else
		{
			$this->reps = array();
		}
		
			
		$rep_end = $this->process_repeaters($event,$args);

		$reps = aw_serialize($this->reps,SERIALIZE_PHP_NOINDEX);
		$this->quote($reps);
		$q = "UPDATE planner SET rep_until = '$rep_end', repeaters = '$reps' WHERE id = '$id'";
		$this->db_query($q);

		// FIXME: this sucks
		if ($parent_class == CL_CALENDAR)
		{
			return $this->mk_my_orb("event_repeaters",array("id" => $id,"stage" => $stage),"planner");
		}
		else
		{
			return $this->mk_my_orb("repeaters",array("id" => $id));
		};
	}
	
	////
	// !Processes repeaters that deal with days
	function _process_day($args = array())
	{
		// every X days
		// print "wd = $this->wd<br>";
		if ($this->repeats["day"] == 1)
		{
			if ( ($this->daynum % $this->dayskip) == 0)
			{
				$this->found = true;
				//print "$this->daynum JAGUB $this->dayskip<br>";
			}
		}
		// on _those_ days inside the week
		elseif ($this->repeats["day"] == 2)
		{
			if ($this->repeats["wday"][$this->wd])
			{
				$this->found = true;
				//print "$this->wd päev matchib DAYS_IN_WEEK ruuliga<br>";
			};
		}
		// on _those_ days inside the month
		elseif ($this->repeats["day"] == 3)
		{
			if (in_array($this->d,$this->days))
			{
				$this->found = true;
				//print "$this->d päev matchib DAYS_IN_MONTH ruuliga<br>";
			};
		}
	}

	////
	// Cycles over all days in a single month
	// month(int) - month number
	// year(int) - year number
	function _process_month($args = array())
	{
		extract($args);
		// dm is the amount of days in this mount
		// wd is the number of day in the current week (1-7), below we use it
		// as our internal counter, since we have to know the number of day
		// we are currently processing
		list($dm,$this->wd) = explode("-",date("t-w",mktime(0,0,0,$month,1,$year))); 
			
		// our week starts on monday
		if ($this->wd == 0)
		{
			$this->wd = 7;
		};

		// $repeats[day] is 1, if the "every X days" line in the form was selected 
		// $repeats[dayskip] is the X
		$this->dayskip = sprintf("%d",($this->repeats["day"] == 1) ? $this->repeats["dayskip"] : 1);
		
		// dayskip has to be integer, thus if it wasn't, we set it to default value and that's 1
		if ($this->dayskip == 0)
		{
			$this->dayskip = 1;
		};
		

		// region1 is the whole day block of the form
		// $repeats[day] == 3, if the "every month on those days" option was selected
		if ( ($this->repeats["region1"]) && ($this->repeats["day"] == 3) )
		{
			// we should check for integers here as well
			$this->days = explode(",",$this->repeats["monpwhen2"]);
		};

		if ($this->from_scratch)
		{
			$this->weeknum = 1; // global week number
			$this->daynum = 1; // global day number
			//print "fresh start, (gwkn)!<br>";
		};
		
		$wkn = 1; // week number in this month

		$this->found = false;

		//$sd = ($month == $this->start_month) ? $this->start_day : 1;

		// this take care of the time periods before the actual event starts.
		// Maybe that should be configurable as well?
		$this_is = mktime(0,0,0,$month,1,$year);
		if ($this_is < $this->start)
		{
			$sd = $this->start_day;
			$this->wd = date("w",mktime(0,0,0,$month,$sd,$year)); 
		}
		else
		{
			$sd = 1;
		};

		//$sd = 1;

		// cycle over all (remaining) days in that month
		for ($this->d = $sd; $this->d <= $dm; $this->d++)
		{

			if ($this->wd == 8)
			{
				// week rollover
				$this->wd = 1;
				// nädala kontroll
				if ($this->region2)
				{
					if ($week == 1)
					{
						if (($gwkn % $weekskip) == 0)
						{
							//print "$gwkn jagub $weekskip<br>";
							//print $gwkn % $weekskip;
							//print "<br>";
						};
					}
					elseif ($week == 2)
					{
						if ($mweek[$wkn])
						{
							//print "$wkn matchib ruuliga WEEKS_IN_MONTH<br>";
						};
					};

				}
				$wkn++;
				$gwkn++;
			};
			
			if ($this->repeats["region1"])
			{
				$this->_process_day();
			};

			$this->wd++;
			if ($this->found)
			{
				$this->rep_count++;
				//$ts = mktime(0,0,0,$month,$this->d,$year);
				// something weird is going on
				$ts = mktime(23,59,59,1,$this->gdaynum,2001);
				$this->reps[] = $this->gdaynum;
				//print "<b>MATCH:</b> " . date("l, d-m-Y",$ts) . "<br>";;
				$this->found = false;
				// check whether we have reached the max count
				if ( isset($this->max_rep_count) && ($this->rep_count == $this->max_rep_count) )
				{
					$this->finished = true;
					$this->rep_end = $ts;
					return;
				};

				if ( ($ts + 1) > $this->rep_end )
				{
					$this->finished = true;
					return;
				}
					
			};
		
			$this->daynum++;
			$this->gdaynum++;
				


		}
	}

	////
	// Processes all events
	function process_repeaters($event,$args)
	{
		// here.
		// I also have to know the week day of the event
		extract($args);
		$start = $event["start"];
		$this->start = $start;
		list($start_year,$start_wday) = explode("-",date("Y-w",$start));

		list($sx_m,$sx_d) = explode("-",date("n-j",$start));
		$this->start_day = $sx_d;
		
		$this->start_month = $sx_m;


		// that's a semaphore, which is used to decide whether we should drop out
		// from the calculations
		$this->finished = false;
		// we use this to count the repeaters we have to use
		$this->rep_count = 0;

		// then, we have to find out the time where the repeaters end
		switch($rep)
		{
			case "1":
				// so yes, it's not really forever
				$end_year = "2037";
				$end_date = "31-12-2037";
				break;

			case "2":
				// we need to know the count of events to use that,
				// so for the moment we just guess
				$this->max_rep_count = $repeats;
				$end_year = "2037";
				$end_date = "31-12-2037";
				break;

			case "3":
				$end_year = $repend["year"];
				$end_date = sprintf("%02d-%02d-%04d",$repend["day"],$repend["month"],$repend["year"]);
				break;
		};
	
		list($_d,$_m,$_y) = explode("-",$end_date);
		$rep_end = mktime(23,59,59,$_m,$_d,$_y);

		$this->rep_end = $rep_end;

		classload("date_calc");
		$timebase = mktime(0,0,0,1,1,2001);
		$ddiff = get_day_diff($timebase,$start);
		$this->gdaynum = $ddiff + 1;


		$years = array();

		// if the user selected the every X year option, so we alter the skip accordingly
		$yearskip = ($region4) ? $yearskip : 1;
		


		// we need four cycles inside each other, years, months, weeks, days
		$last_year = 0;
		$last_mon = 0;
		$last_day = 0;

		$this->from_scratch = true;
		//print "start_year = $start_year, end_year = $end_year, yearskip = $yearskip<br>";
		for ($y = $start_year; $y <= $end_year; $y = $y + $yearskip)
		{
		
			$months = array();
			// every X months
			if ($month == 2)
			{
				$months = explode(",",$yearpwhen);
			}
			else
			{
				$monthskip = ($region3) ? $monthskip : 1;

				$sm = ($y == $start_year) ? $sx_m : 1;
				for ($m = $sm; $m <= 12; $m = $m + $monthskip)
				{
					$months[] = $m;
				};
			};

			$this->year = $y;
			if ( ($y - 1) != $last_year)
			{
				$this->from_scratch = true;
				//print "fresh start $y (y)!<br>";
			};
			$last_year = $y;

			foreach($months as $m)
			{
				if ($last_mon == 12)
				{
					if ($m != 1)
					{
						$this->from_scratch = true;
						//print "fresh start (m1)!<br>";
					}
				}
				elseif ( $last_mon != ($m - 1) )
				{
					$this->from_scratch = true;
					//print "fresh start (m2)!<br>";
				};
				$last_mon = $m;
		
				//print "<i>Processing:</i> $m/$y<br>";
				if ( ($y == $start_year) && ($m < $sx_m) )
				{
					// it's too early, do nothing
				}
				else
				{
					$this->_process_month(array("month" => $m,"year" => $y));
					if ($this->finished)
					{
						return $this->rep_end;
					};
				};
				$this->from_scratch = false;
				//print "<br>";


			}

		}
		return $this->rep_end;
	}		
	
	////
	// !Allows to search for objects to include in the document
	// intended to replace pickobject.aw
	function search($args = array())
	{
		extract($args);
		classload("aliasmgr");
		$amgr = new aliasmgr;
		$amgr->_init_aliases();
		$this->defs = $amgr->defs;
		$this->read_template("search_doc.tpl");
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		$parent_class = $par_obj["class_id"];
		global $s_name, $s_comment,$s_type,$SITE_ID;
		if ($parent_class == CL_CALENDAR)
		{
			$back_link = $this->mk_my_orb("change_event",array("id" => $id),"planner");
		}
		else
		{
			$back_link = $this->mk_my_orb("change",array("id" => $id));
		};
		$this->mk_path(0,"<a href='$back_link'>Tagasi</a> | <b>Otsi objekti</b>");
                if ($s_name != "" || $s_comment != "" || $s_type > 0)
                {
			$se = array();
			if ($s_name != "")
			{
				$se[] = " objects.name LIKE '%".$s_name."%' ";
			}
			if ($s_comment != "")
			{
				//$se[] = " objects.comment LIKE '%".$s_comment."%' ";
			}
			if ($s_type > 0)
			{
				$se[] = " objects.class_id = '".$s_type."' ";
			}
			else
			{
				$se[] = " objects.class_id IN (".join(",",$this->typearr).") ";
			}

			$q = "SELECT objects.name as name,objects.oid as oid,objects.class_id as class_id,objects.created as created,objects.createdby as createdby,objects.modified as modified,objects.modifiedby
as modifiedby,pobjs.name as parent_name FROM objects, objects AS pobjs WHERE pobjs.oid = objects.parent AND objects.status != 0 AND (objects.site_id = $SITE_ID OR objects.site_id IS NULL) AND ".join("AND",$se);
			$this->db_query($q);
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"],
					"id" => $row["oid"],
					"type"  => $GLOBALS["class_defs"][$row["class_id"]]["name"],
					"created" => $this->time2date($row["created"],2),
					"modified" => $this->time2date($row["modified"], 2),
					"createdby" => $row["createdby"],
					"modifiedby" => $row["modifiedby"],
					"parent_name" => $row["parent_name"],
					"pick_url" => $this->mk_orb("addalias",array("id" => $id, "alias" => $row["oid"]),"cal_event"),
				));
				$l.=$this->parse("LINE");
			}
			$this->vars(array("LINE" => $l));
		}
		else
		{
			$s_name = "%";
			$s_comment = "%";
			$s_type = 0;
		}

		$tar = array(0 => LC_OBJECTS_ALL);
		foreach($this->defs as $key => $val)
		{
			$clid = $val["class_id"];
			$tar[$clid] = $GLOBALS["class_defs"][$clid]["name"];
		}
		$this->vars(array("id" => $id,
				"class" => ($parent_class == CL_CALENDAR) ? "planner" : "cal_event",
				"action" => ($parent_class == CL_CALENDAR) ? "event_object_search" : "search",
				"s_name"  => $s_name,
				"s_type"  => $s_type,
				"s_comment" => $s_comment,
				"pick_link" => $this->mk_my_orb("pick",array("id" => $id)),
				"types" => $this->picker($s_type, $tar)));
		return $this->parse();
	}	

	function addalias($args = array())
	{
		$this->quote($args);
		extract($args);
		$obj = $this->get_object($id);
		$par_obj = $this->get_object($obj["parent"]);
		$q = "UPDATE planner SET oid = '$alias' WHERE id = '$id'";
		$this->db_query($q);
		if ($par_obj["class_id"] == CL_CALENDAR)
		{
			return $this->mk_my_orb("change_event",array("id" => $id),"planner");
		}
		else
		{
			return $this->mk_my_orb("change",array("id" => $id));
		};
	}

	////
	// !Displays the form for setting reminders
	function reminder($args = array())
	{
		extract($args);
		$this->read_template("reminder.tpl");
		$menubar = $this->gen_menu(array(
				"activelist" => array("reminder"),
				"vars" => array("id" => $id),
		));
		$this->vars(array(
			"menubar" => $menubar,
		));

		return $this->parse();
	}
};
?>
