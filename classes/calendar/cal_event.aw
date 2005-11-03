<?php
// cal_event.aw - Kalendri event
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/cal_event.aw,v 1.13 2005/11/03 13:25:30 duke Exp $

/*
	@default table=objects
	@default group=general

	@property start type=date_select table=planner field=start
	@caption Algab

	@property end type=date_select table=planner field=end
	@caption Lõpeb

	@property repeater_obj type=relpicker clid=CL_REPEATER_OBJ field=meta method=serialize
	@caption Korduste kontroller

	@tableinfo planner index=id master_table=objects master_index=oid
	@classinfo relationmgr=yes syslog_type=ST_CAL_EVENT

*/

/*

HANDLE_MESSAGE_WITH_PARAM(MSG_STORAGE_ALIAS_ADD_TO, CL_CAL_EVENT, on_add_alias)

*/


class cal_event extends class_base 
{
	function cal_event($args = array())
	{	
		$this->init(array(
			"clid" => CL_CAL_EVENT,
			"tpldir" => "cal_event",
		));
	}

	function callback_get_rel_types()
	{
		return array("1" => t("korduste kontroller"));
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$request = &$args["request"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "start":
				if ($request["date"])
				{
					list($d,$m,$y) = explode("-",$request["date"]);
					$ts = mktime(9,0,0,$m,$d,$y);
					$data["value"] = $ts;
				};
				break;

			case "end":
				if ($request["date"])
				{
					list($d,$m,$y) = explode("-",$request["date"]);
					$ts = mktime(9,0,0,$m,$d,$y);
					$data["value"] = $ts + (2*3600);
				};
				break;
		};
		return $retval;

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
		load_vcl("xmlmenu");
		$xm = new xmlmenu();
		$xm->vars($vars);
		$xm->load_from_files(array(
			"xml" => $this->cfg["basedir"] . "/xml/planner/event_menu.xml",
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
		classload('core/icons');
		load_vcl("date_edit");
		$start = new date_edit("start");
		$start->configure(array("day" => 1,"month" => 2,"year" => 3));
		$start_ed = $start->gen_edit_form("start",$args["start"]);
		list($shour,$smin) = split("-",date("G-i",$args["start"]));
		if ($args["end"])
		{
			$ehour = date("H",$args["end"]);
			$emin = date("i",$args["end"]);
		}
		else
		{
			$dsec = $args["start"];
			$ehour = date("H",$args["start"]) + 1;
			$emin = 0;
		};

		$colors = array(
			"#000000" => t("must"),
			"#990000" => t("punane"),
			"#009900" => t("roheline"),
			"#000099" => t("sinine"),
		);

		$calendars = array();
		$ol = new object_list(array(
			"class_id" => CL_PLANNER,
			"status" => STAT_ACTIVE
		));
		$calendars = $ol->names();

		// nimekiri tundidest
		$h_list = range(0,23);
		// nimekiri minutitest
		$m_list = array("00" => "00", "05" => "05", "10" => "10", "15" => "15", 
			"20" => "20", "25" => "25", "30" => "30", "35" => "35", "40" => "40",
			"45" => "45", "50" => "50", "55" => "55",
		);
		$smin = (sprintf("%d",$smin / 5) * 5);
		if ($args["oid"])
		{
			$obj = obj($args["oid"]);
		};
		$types = array(t("linki objektile"),t("objekti ennast"));
		$this->vars(array(
			"start" => $start_ed,
			"shour" => $this->picker($shour,$h_list),
			"smin" => $this->picker($smin,$m_list),
			"calendars" => $this->picker($args["folder"],$calendars),
			"showtype" => $this->picker($args["meta"]["showtype"],$types),
			"ehour" => $this->picker($ehour,$h_list),
			"emin" => $this->picker($emin,$m_list),
			"object" => $obj->name(),
			"obj_icon" => icons::get_icon_url($obj->class_id(),""),
			"color" => $this->picker($args["color"],$colors),
			"calendar_url" => $this->mk_my_orb("view",array("id" => $args["folder"]),"planner"),
			"icon_url" => icons::get_icon_url(CL_CALENDAR,""),
		));
	}

	////
	// !Kuvab uue eventi lisamise vormi
	function _add($args = array())
	{
		extract($args);
		$this->read_template("edit.tpl");
		$this->mk_path($parent,t("Lisa kalendrisündmus"));
		if ($time)
		{
			list($hr,$mn) = explode(":",$time);
			list($d,$m,$y) = explode("-",$date);
			$start = mktime($hr,$mn,00,$m,$d,$y);
		}
		elseif ($date)
		{
			list($d,$m,$y) = explode("-",$date);
			list($sh,$sm,$ss) = explode("-",date("H-i-s"));
			$start = mktime($sh,$sm,$ss,$m,$d,$y);
			//$start = time();
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
	function _submit($args = array())
	{
		extract($args);
		// sellest teeme timestampi
		$st = mktime($shour,$smin,0,$start["month"],$start["day"],$start["year"]);
		// lopu aeg
		$et = mktime($ehour,$emin,0,$start["month"],$start["day"],$start["year"]);

		//$et = mktime($shour + $dhour,$smin + $dmin,59,$start["month"],$start["day"],$start["year"]);

		if ($parent)
		{
			$par_obj = obj($parent);
			$parent_class = $par_obj->class_id();

			$o = obj();
			$o->set_parent($parent);
			$o->set_name($title);
			$o->set_class_id(CL_CAL_EVENT);
			$o->set_status(STAT_ACTIVE);
			$o->set_meta("repcheck",$repcheck);
			$o->set_meta("showtype",$showtype);
			$id = $o->save();

			$q = "UPDATE planner 
				SET 
					start = '$start',
					end = '$et',
					title = '$title',
					place = '$place',
					description = '$description',
					color = '$color',
					folder = '$folder'
				WHERE 
					id = '$id'
			";
			$this->db_query($q);
			// flush cache
			$o->save();
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
			$obj = obj($id);
			foreach($mt as $k => $v)
			{
				$obj->set_meta($k, $v);
			}
			$obj->set_name($title);

			$par_obj = obj($obj->parent());
			$parent_class = $par_obj->class_id();
			                         
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
			$obj->save();
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
	function _change($args = array())
	{
		extract($args);
		$object = obj($id);
		$par_obj = obj($object->parent());
		$meta = $object->meta();

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
		$this->mk_path($object->parent(),sprintf(t("%s | Muuda kalendrisündmust"), $cal_link));

		if ($par_obj->class_id() == CL_CALENDAR)
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

	/** Kuvab repeaterite sisestamise vormi 
		
		@attrib name=repeaters params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function repeaters($args = array())
	{
		extract($args);

		$obj = obj($id);

		// I wonder what happens if a non-existing ID is given
		$q = "SELECT * FROM planner WHERE id = '$id'";
		$this->db_query($q);
		$event = $this->db_next();

		$obj_meta = $obj->meta();

		$par_obj = obj($obj->parent());
		
		$menubar = $this->gen_menu(array(
			"activelist" => array("repeaters"),
			"vars" => array("id" => $id),
		));

		$this->mk_path($par_obj->parent(),t("Muuda eventit"));

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
			$tmp = obj($id);
			if (is_numeric($cycle))
			{
				$meta = $tmp->meta("repeaters" . $cycle);
			}
			// so it must be a new one
			else
			{
				// we have to figure out the last cycle number in use
				$cycle = $tmp->meta("cycle_counter");
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
			// set up the time editor

			if ($meta["own_time"])
			{
				$reptime_val = mktime($meta["reptime"]["hour"],$meta["reptime"]["minute"],0,1,1,2001);
			}
			else
			{
				if ($event["start"])
				{
					$reptime_val = $event["start"];
				}
				else
				{
					$reptime_val = 0;
				};
			};

			$reptime = new date_edit("time");
			$reptime->configure(array("hour" => 1,"minute" => 2));
			$reptime_ed = $reptime->gen_edit_form("reptime",$reptime_val);
			// set up the date editor for cycle end
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
			$repend_ed= $repend->gen_edit_form("repend",$_tmp,2001,2010);
			
			// set up the date editor for cycle start
			$repstart = new date_edit("start");
			$repstart->configure(array("day" => 1,"month" => 2,"year" => 3));
			if (is_array($meta["repstart"]))
			{
				$_tmp = mktime(0,0,0,$meta["repstart"]["month"],$meta["repstart"]["day"],$meta["repstart"]["year"]);
			}
			else
			{
				$_tmp = $event["start"];
			};
			$repstart_ed= $repstart->gen_edit_form("repstart",$_tmp,2001,2010);
			// oh, I know, this is sooo ugly
			$this->vars(array(
					"region1" => checked($meta["region1"]),
					"dayskip" => ($meta["dayskip"] > 0) ? $meta["dayskip"] : 1,
					"time" => $reptime_ed,
					"day1" => checked($meta["day"] == 1),
					"day2" => checked($meta["day"] == 2),
					"day3" => checked($meta["day"] == 3),
					"wday1" => checked($meta["wday"][1]),
					"wday2" => checked($meta["wday"][2]),
					"wday3" => checked($meta["wday"][3]),
					"wday4" => checked($meta["wday"][4]),
					"wday5" => checked($meta["wday"][5]),
					"own_time" => checked($meta["own_time"]),
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
					"repstart" => $repstart_ed,
					"repeats" => ($meta["repeats"]) ? $meta["repeats"] : 6,
					"rep1_checked" => ($meta["rep"]) ? checked($meta["rep"] == 1) : "checked",
					"rep2_checked" => checked($meta["rep"] == 2),
					"rep3_checked" => checked($meta["rep"] == 3),
					"reforb" => $this->mk_reforb("submit_repeaters",array("id" => $id,"cycle" => $cycle,"new" => $new)),
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
						$_tmp = $repdat["repstart"];
						$_st = sprintf("%d.%s %d",$_tmp["day"],get_lc_month($_tmp["month"]),$_tmp["year"]);
						$_tmp = $repdat["repend"];
						$_et = sprintf("%d.%s %d",$_tmp["day"],get_lc_month($_tmp["month"]),$_tmp["year"]);
						$this->vars(array(
							"id" => $i,
							"start" => $_st,
							"end" => $_et,
						));
						$content .= $this->parse("line");
					};
				};
			};

			if (!$use_class)
			{
				$use_class = "planner";
			};

			if (!$use_method)
			{
				$use_method = "event_repeaters";
			};

			$this->vars(array(
				"add_link" => $this->mk_my_orb($use_method,array("id" => $id,"cycle" => "new"),$use_class),
				"ed_link" => $this->mk_my_orb($use_method,array("id" => $id),$use_class),
				"del_link" => $this->mk_my_orb("delete_repeater",array("id" => $id),$use_class),
				"line" => $content,
			));
		}

		if (not($hide_menubar))
		{
			$this->vars(array(
				"menubar" => $menubar,
			));
		};
			
		return $this->parse();
	}

	/** Saves the repeaters and calculates the dates when the correspondending event occurs 
		
		@attrib name=submit_repeaters params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function submit_repeaters($args = array())
	{
		extract($args);
		
		// save the current settings
		$key = "repeaters" . $cycle;
	
		// now we fetch them all back again, so that we can perform our calculations
		$obj = obj($id);
		$obj->set_meta($key, $args);
		$obj->save();

		$obj_meta = $obj->meta();
		$par_obj = obj($obj->parent());
		$parent_class = $par_obj->class_id();
		
		// if that one was a new cycle, then we need update the object as well
		if ($new)
		{
			$obj->set_meta("cycle_counter", $cycle);
			$obj->save();
			$cycle_counter = $cycle;
		}
		else
		{
			// find out how many cycles we have
			$tmp = obj($id);
			$cycle_counter = $tmp->meta("cycle_counter");
		}
	
		// zero everything out
		$this->reps = array();
	
		// now we recalculate all the cycles and repeaters this event has
		// NOTE: we really should only recalculate the data for the current cycle
		for ($i = 1; $i <= $cycle_counter; $i++)
		{
			$key = sprintf("repeaters%d",$i);
			if ($obj_meta[$key])
			{
				$this->repeats = $obj_meta[$key];
				$this->process_repeaters($obj_meta[$key]);
			};
		}
	
		sort($this->reps);

		// find out the first and the last day
		$first = reset($this->reps);
		$end = end($this->reps);

		$rep_from = mktime(0,0,0,1,$first,2001);
		$rep_end = mktime(23,59,59,1,$end,2001);
		
		$reps = aw_serialize($this->reps,SERIALIZE_PHP_NOINDEX);
		$this->quote($reps);
		// if we are adding repeaters for the scheduler, then there is likely
		// no record in the planner table, so we create it, when necessary.
		$q = "SELECT id FROM planner WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		if ($row)
		{
			$q = "UPDATE planner SET rep_until = '$rep_end', rep_from = '$rep_from', repeaters = '$reps' WHERE id = '$id'";
		}
		else
		{
			$q = "INSERT INTO planner (id,rep_until,rep_from,repeaters)
				VALUES ('$id','$rep_end','$rep_from','$reps')";
		};
		$this->db_query($q);

		$sched = get_instance("scheduler");
		$sched->update_repeaters(array("id" => $id));

		// FIXME: this sucks
		if ($obj->class_id() == CL_REPEATER_OBJ)
		{
			return $this->mk_my_orb("set_time",array("id" => $id),"repeater_obj");
		}
		else
		if ($obj->class_id() == CL_SCHEDULER)
		{
			return $this->mk_my_orb("set_time",array("id" => $id),"scheduler");
		}
		else
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
		if ($this->repeats["day"] == 1)
		{
			if ( ($this->daynum % $this->dayskip) == 0)
			{
				$this->found = true;
				//print "$this->daynum JAGUB $this->dayskip<br />";
			}
		}
		// on _those_ days inside the week
		elseif ($this->repeats["day"] == 2)
		{
			if ($this->repeats["wday"][$this->wd])
			{
				$this->found = true;
				//print "$this->wd päev matchib DAYS_IN_WEEK ruuliga<br />";
			};
		}
		// on _those_ days inside the month
		elseif ($this->repeats["day"] == 3)
		{
			if (in_array($this->d,$this->days))
			{
				$this->found = true;
				//print "$this->d päev matchib DAYS_IN_MONTH ruuliga<br />";
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
			//print "fresh start, (gwkn)!<br />";
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
							//print "$gwkn jagub $weekskip<br />";
							//print $gwkn % $weekskip;
							//print "<br />";
						};
					}
					elseif ($week == 2)
					{
						if ($mweek[$wkn])
						{
							//print "$wkn matchib ruuliga WEEKS_IN_MONTH<br />";
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
				
				// something weird is going on
				//print "<b>MATCH:</b> " . date("l, d-m-Y",$ts) . "<br />";;
				$ts = mktime(23,59,59,$this->start_month,$this->start_day + $this->daynum,$this->start_year);

				// temp workaround for "every week on those days";
				$delta = ($this->repeats["day"] == 2) ? 0 : 1;
				// try to avoid duplicate repeaters
				if (not(in_array($this->gdaynum + $delta,$this->reps)))
				{
					$this->reps[] = $this->gdaynum + $delta;
				};
				//print "<b>MATCH:</b> " . date("l, d-m-Y",$ts) . "<br />";;
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
	function process_repeaters($args)
	{
		extract($args);
		$repstart = mktime(0,0,0,$repstart["month"],$repstart["day"],$repstart["year"]);
		$this->start = $repstart;
		list($start_year,$start_wday) = explode("-",date("Y-w",$repstart));

		list($sx_m,$sx_d) = explode("-",date("n-j",$repstart));
		$this->start_day = $sx_d;
		
		$this->start_month = $sx_m;
		$this->start_year = $start_year;

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
		$ddiff = get_day_diff($timebase,$repstart);
		$this->gdaynum = $ddiff + 1;


		$years = array();

		// if the user selected the every X year option, so we alter the skip accordingly
		$yearskip = ($region4) ? $yearskip : 1;
		
		$last_year = 0;
		$last_mon = 0;
		$last_day = 0;

		$this->from_scratch = true;

		// cycle over all the years in the repeater cycle
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
			};
			$last_year = $y;

			foreach($months as $m)
			{
				if ($last_mon == 12)
				{
					if ($m != 1)
					{
						$this->from_scratch = true;
						//print "fresh start (m1)!<br />";
					}
				}
				elseif ( $last_mon != ($m - 1) )
				{
					$this->from_scratch = true;
					//print "fresh start (m2)!<br />";
				};
				$last_mon = $m;
		
				if ( ($y == $start_year) && ($m < $sx_m) )
				{
					// it's too early, do nothing
				}
				else
				{
					//print "<i>Processing:</i> $m/$y<br />";
					$this->_process_month(array("month" => $m,"year" => $y));
					if ($this->finished)
					{
						return $this->rep_end;
					};
				};
				$this->from_scratch = false;
				//print "<br />";
			}
		}
		return $this->rep_end;
	}		
	
	/** Allows to search for objects to include in the document 
		
		@attrib name=search params=name default="0"
		
		@param id required type=int
		@param s_name optional
		@param s_type optional
		@param s_comment optional
		
		@returns
		
		
		@comment

	**/
	function search($args = array())
	{
		extract($args);
		$amgr = get_instance("aliasmgr");
		$this->read_template("search_doc.tpl");
		$obj = obj($id);
		$par_obj = obj($obj->parent());
		$parent_class = $par_obj->class_id();
		global $s_name, $s_comment,$s_type;
		if ($parent_class == CL_CALENDAR)
		{
			$back_link = $this->mk_my_orb("change_event",array("id" => $id),"planner");
		}
		else
		{
			$back_link = $this->mk_my_orb("change",array("id" => $id));
		};
		$this->mk_path(0,"<a href='$back_link'>Tagasi</a> | <b>Otsi objekti</b>");
		$amgr->make_alias_typearr();
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
				$se[] = " objects.class_id IN (".join(",",$amgr->typearr).") ";
			}

			$q = "SELECT objects.name as name,objects.oid as oid,objects.class_id as class_id,objects.created as created,objects.createdby as createdby,objects.modified as modified,objects.modifiedby
as modifiedby,pobjs.name as parent_name FROM objects, objects AS pobjs WHERE pobjs.oid = objects.parent AND objects.status != 0 AND (objects.site_id = ".$this->cfg["site_id"]." OR objects.site_id IS NULL) AND ".join("AND",$se);
			$this->db_query($q);
			$tmp = aw_ini_get("classes");
			while ($row = $this->db_next())
			{
				$this->vars(array(
					"name" => $row["name"],
					"id" => $row["oid"],
					"type"  => $tmp[$row["class_id"]]["name"],
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

		$amgr->make_alias_classarr();
		$this->vars(array("id" => $id,
			"class" => ($parent_class == CL_CALENDAR) ? "planner" : "cal_event",
			"action" => ($parent_class == CL_CALENDAR) ? "event_object_search" : "search",
			"s_name"  => $s_name,
			"s_type"  => $s_type,
			"s_comment" => $s_comment,
			"pick_link" => $this->mk_my_orb("pick",array("id" => $id)),
			"types" => $this->picker($s_type, array(0 => LC_OBJECTS_ALL) + $amgr->classarr)
		));
		return $this->parse();
	}	

	function on_add_alias($args = array())
	{
		extract($args);
		$obj = obj($args["connection"]->prop("from"));
		$par_obj = obj($obj->parent());

		$q = "UPDATE planner SET oid = '".$args["connection"]->prop("to")."' WHERE id = '".$args["connection"]->prop("from")."'";
		$this->db_query($q);
		if ($par_obj->class_id() == CL_CALENDAR)
		{
			return $this->mk_my_orb("change_event",array("id" => $args["connection"]->prop("from")),"planner");
		}
		else
		{
			return $this->mk_my_orb("change",array("id" => $args["connection"]->prop("from")));
		};
	}

	/** Displays the form for setting reminders 
		
		@attrib name=reminder params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
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
