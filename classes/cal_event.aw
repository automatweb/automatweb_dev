<?php
// cal_event.aw - Kalendri event
// $Header: /home/cvs/automatweb_dev/classes/Attic/cal_event.aw,v 2.3 2002/01/17 01:15:22 duke Exp $
global $class_defs;
$class_defs["cal_event"] = "xml";
define("TIMEBASE",mktime(0,0,0,1,1,2001));

class cal_event extends aw_template {
	function cal_event($args = array())
	{	
		extract($args);
		$this->db_init();
		$this->tpl_init("cal_event");
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
		$this->vars(array(
			"start" => $start_ed,
			"shour" => $this->picker($shour,$h_list),
			"smin" => $this->picker($smin,$m_list),
			"calendars" => $this->picker($args["folder"],$calendars),
			"dhour" => $this->picker($dhour,$h_list),
			"dmin" => $this->picker($dmin,$m_list),
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
		$this->read_template("edit.tpl");
		$this->mk_path($parent,"Lisa kalendrisündmus");
		$this->_fill_event_form(array("folder" => $args["folder"]));
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
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $title,
				"class_id" => CL_CAL_EVENT,
				"status" => 2,
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
				$mt = array("repcheck" => $repcheck);
			}
			else
			{
				// if the repcheck checkbox was unchecked then we need 
				// to zero out all the information about repeaters
				$mt = array("repcheck" => $repcheck,"repeaters" => array());
			}
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
		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	// !Kuvab olemasoleva eventi muutmise objekti
	function change($args = array())
	{
		extract($args);
		$object = $this->get_obj_meta($id);
		$meta = $object["meta"];
		$this->mk_path($object["parent"],"Muuda kalendrisündmust");
		$q = "SELECT *,planner.* FROM objects LEFT JOIN planner ON (objects.oid = planner.id) WHERE objects.oid = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$this->read_template("edit.tpl");
		$this->_fill_event_form($row);
		$this->vars(array(
			"rep_link" => $this->mk_my_orb("repeaters",array("id" => $id)),
		));

		$this->vars(array(
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

		$par_obj = $this->get_object($id);
		//$stamps = array();
		//$q = "SELECT * FROM planner WHERE id = '$id'";
		//$this->db_query($q);
		//$stamps = $this->db_next();
		//$xaa = aw_unserialize($stamps["repeaters"]);

		$grand_parent = $this->get_object($par_obj["parent"]);
		$this->mk_path($grand_parent["parent"],"Muuda eventit");
		$meta = $this->get_object_metadata(array("oid" => $id,"key" => "repeaters"));

		// what's that?
		$caldata = $this->get_object_metadata(array(
						"oid" => $id,
						"key" => "calconfig",
				));
		if (is_array($caldata))
		{
			extract($caldata);
		}

		$this->read_template("repeaters.tpl");
		load_vcl("date_edit");
		$repend = new date_edit("start");
		$repend->configure(array("day" => 1,"month" => 2,"year" => 3));
		$repend_ed= $repend->gen_edit_form("repend",strtotime("+1 week"));
		// oh, I know, this is sooo ugly
		$this->vars(array(
				"region1" => checked($meta["region1"]),
				"dayskip" => ($meta["dayskip"] > 0) ? $meta["dayskip"] : 1,
				"change_link" => $this->mk_my_orb("change",array("id" => $id)),
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
				"menubar" => $menubar,
				"reforb" => $this->mk_reforb("submit_repeaters",array("id" => $id)),
		));
		return $this->parse();
	}
	
	function submit_repeaters($args = array())
	{
		extract($args);
		// store them inside the object so we can use them elsewhere
		$this->repeats = $args;
		$this->set_object_metadata(array(
			"oid" => $id,
			"overwrite" => 1,
			"key" => "repeaters",
			"value" => $args,
		));
		$q = "SELECT *,planner.* FROM objects LEFT JOIN planner ON (objects.oid = planner.id)
			WHERE objects.oid = '$id'";
		$this->db_query($q);
		$event = $this->db_next();
		$this->reps = array();
		$rep_end = $this->process_repeaters($event,$args);
		$reps = aw_serialize($this->reps,SERIALIZE_PHP_NOINDEX);
		$this->quote($reps);
		$q = "UPDATE planner SET rep_until = '$rep_end', repeaters = '$reps' WHERE id = '$id'";
		$this->db_query($q);
		return $this->mk_my_orb("repeaters",array("id" => $id));
	}
	
	////
	// !Processes repeaters that deal with days
	function _process_day($args = array())
	{
		// every X days
		// print "wd = $this->wd<br>";
		$this->gdaynum++;
		if ($this->repeats["day"] == 1)
		{
			if ( ($this->daynum % $this->dayskip) == 0)
			{
				$this->found = true;
				//print "$this->daynum JAGUB $this->dayskip<br>";
			}
		}
		// every
		elseif ($this->repeats["day"] == 2)
		{
			if ($this->repeats["wday"][$this->wd])
			{
				$this->found = true;
				//print "$this->wd päev matchib DAYS_IN_WEEK ruuliga<br>";
			};
		}
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
		
		$this->dayskip = ($this->repeats["day"] == 1) ? $this->repeats["dayskip"] : 1;

		if ( ($this->repeats["region1"]) && ($this->repeats["day"] == 3) )
		{
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

		for ($this->d = 1; $this->d <= $dm; $this->d++)
		{
			$this->daynum++;
			
			if ($this->repeats["region1"])
			{
				$this->_process_day();
			};
			if ($this->wd == 8)
			{
				// rollover
				$this->wd = 1;
				//print "y:$y - m:$m - wn:$wkn - gwkn: $gwkn<br>";
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

			$this->wd++;
			if ($this->found)
			{
				$ts = mktime(0,0,0,$month,$this->d,$year);
				$this->reps[] = $this->gdaynum;
				//print "<b>MATCH:</b> " . date("l, d-m-Y",$ts) . "<br>";;
				$this->found = false;
			};
				


		}
	}
		
	function process_repeaters($event,$args)
	{
		// here.
		// I also have to know the week day of the event
		extract($args);
		$start = $event["start"];
		list($start_year,$start_wday) = explode("-",date("Y-w",$start));


		
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
		classload("date_calc");
		$ddiff = get_day_diff(TIMEBASE,$start);
		$this->gdaynum = $ddiff;


		$years = array();

		// if the user selected the every X year option, so we alter the skip accordingly
		$yearskip = ($region4) ? $yearskip : 1;
		
		$months = array();
		// every X months
		if ($month == 2)
		{
			$months = explode(",",$yearpwhen);
		}
		else
		{
			$monthskip = ($region3) ? $monthskip : 1;
			for ($m = 1; $m <= 12; $m = $m + $monthskip)
			{
				$months[] = $m;
			};
		};


		// we need four cycles inside each other, years, months, weeks, days
		$last_year = 0;
		$last_mon = 0;
		$last_day = 0;

		$this->from_scratch = true;
		//print "start_year = $start_year, end_year = $end_year, yearskip = $yearskip<br>";
		for ($y = $start_year; $y <= $end_year; $y = $y + $yearskip)
		{
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
				$this->_process_month(array("month" => $m,"year" => $y));
				$this->from_scratch = false;
				//print "<br>";


			}

		}
		return $rep_end;
	}		
};
?>
