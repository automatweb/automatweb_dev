<?php
// deals with those pesky doc_events
class doc_event extends core
{
	function doc_event()
	{
		$this->db_init();
	}

	function _get_event_folders($args = array())
	{
		$calendars = array($args["folder"]);
		$this->save_handle();
		// TODO: register all reltypes as constants or something, so that they can be accessed from outside the class.
		$q = "SELECT metadata FROM aliases LEFT JOIN objects ON (aliases.target = objects.oid) WHERE source = '$args[id]' AND reltype = " . 2;	// RELTYPE_EVENT_SOURCE from planner
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$mx = aw_unserialize($row["metadata"]);
			if (!empty($mx["event_folder"]))
			{
				$calendars[] = $mx["event_folder"];
			};
		};

		$calstring = join(",",$calendars);
		return $calstring;
	
	}

	////
	// !Retrieves a list of events in the given date range
	// conf(array) - metadata of the planner object we are using.
	// start(timestamp) - start of the period
	// end(timestamp) - end of the period
	// type(type of the range) - day, or something
	// folder(int) - id of the event folder of the calendar - but we can get that from the conf as well
	// id(int) - id of the planner object itself
	function get_events_in_range($args = array())
	{
		extract($args);
			
		$by_calendar = array();

		if (!empty($args["relobj_id"]))
		{
			// create a list of aliases .. for the currently shown planner object
			// and I also need to know, to which calendar that freaking relation
			// belongs to
			$q = "SELECT oid,name,aliases.target,aliases2.target AS cal_id FROM aliases
				LEFT JOIN aliases as aliases2 ON (aliases.target = aliases2.relobj_id)
				LEFT JOIN objects ON (aliases2.source = objects.oid)
				WHERE aliases.source = '$args[relobj_id]'";
			$this->db_query($q);
			$foundcount = 0;
			$use_target = 0;
			while($row = $this->db_next())
			{
				if (!empty($row["cal_id"]))
				{
					$foundcount++;
					$by_calendar[$row["cal_id"]] = $row["oid"];
				}
				else
				{	
					$use_target = $row["target"];
				};
			};

			if ($foundcount == 0)
			{
				// lets try it this way then
				$q = "SELECT oid,name,aliases.target,aliases2.target AS cal_id FROM aliases
					LEFT JOIN aliases as aliases2 ON (aliases.target = aliases2.relobj_id)
					LEFT JOIN objects ON (aliases2.source = objects.oid)
					WHERE aliases.source = '$use_target'";
				$this->db_query($q);
				while($row = $this->db_next())
				{
					if (!empty($row["cal_id"]))
					{
						$by_calendar[$row["cal_id"]] = $row["oid"];
					}
				};
			}

		}

		$calstring = $this->_get_event_folders($args);

		$content_generator = $args["conf"]["content_generator"];
                $generator_class = $generator_method = "";
                list($cl,$met) = explode("/",$content_generator);
                if (isset($cl) && isset($met))
                {
                        $generator_class = $cl;
                        $generator_method = $met;
                };

		// the thing is .. if a custom method is requested, then I need to figure out which events
		// actually provide that custom content, and then use only those to find my events. 
		// now how on earth am I going to do that?

		// - for galleries it's rather easy, I can do something like:
		// LEFT JOIN aliases ON (planner.id = aliases.source) WHERE aliases.type = 190
		// (190 is gallery) and get only the events which have a gallery attached ...
		// alltho then I _might_ probably have a problem with multiple galleries
		// but oh well, I'll think of _THAT_ later
		
		// actually, I rather dislike the idea of having to set the flyer by the document ..
		// because then I might want to do the exact same thing for galleries .. to help me
		// decide which is the correct gallery that should be used

		$fsql = $fwhere = $lmt = "";
		if (($args["type"] == "relative") && is_numeric($args["conf"]["event_max_items"]))
		{
			$lmt = " LIMIT " . ($args["conf"]["event_max_items"]);
			$max_items = $args["conf"]["event_max_items"];
		}
		// XXX: temporary
		if ($cl == "gallery_v2")
		{
			$ftype = CL_GALLERY_V2;
			$fpri = 1;
			$fsql = " LEFT JOIN aliases ON (planner.id = aliases.source) ";
			$fwhere = " aliases.type = $ftype AND aliases.pri = $fpri AND ";
		}

		if ($cl == "doc")
		{
			$ftype = CL_IMAGE;
			$fpri = 1;
			$fsql = " LEFT JOIN aliases ON (planner.id = aliases.source) ";
			$fwhere = " aliases.type = $ftype AND aliases.pri = $fpri AND ";
		}

		$this->_get_next_event(array(
			"calstring" => $calstring,
			"start" => $end,
			"fsql" => $fsql,
			"fwhere" => $fwhere,
		));

		$next = $this->db_next();

		$this->_get_prev_event(array(
			"calstring" => $calstring,
			"start" => $start,
			"fsql" => $fsql,
			"fwhere" => $fwhere,
		));

		// for relative events, I do not know where our range starts or end, this 
		// means I have to calculate the prev/next links _after_ I'm done with my cycle

		$prev = $this->db_next();

		$order = ($args["conf"]["event_direction"] == 1) ? "DESC" : "";

		$q = "SELECT objects2.name AS name, objects2.class_id AS class_id,objects2.metadata AS metadata,
			objects.oid AS real_oid,
			objects.parent,planner.*,documents.lead,documents.moreinfo,documents.content FROM objects
			LEFT JOIN objects AS objects2 ON (objects.brother_of = objects2.oid)
			LEFT JOIN planner ON (objects.brother_of = planner.id)
			LEFT JOIN documents ON (objects.brother_of = documents.docid)
			$fsql 
			WHERE $fwhere objects.parent IN ($calstring) AND ((start >= '$start') AND (start <= '$end')) AND objects.status != 0
			GROUP BY objects.brother_of
			ORDER BY planner.start $order,objects.created $lmt";
		$this->db_query($q);
		$results = array();
		$count = $this->num_rows();
		$almgr = get_instance("aliasmgr");
		$next_active = false;

		if (($count == 0) && $conf["only_days_with_events"])
		{
			// this is made so that we can actually show the previous or the next event

			// btw, this is causes a bug with views other than "day", requesting
			// a time period after the last event, makes us only show the last
			// active event in the requested view
			$this->_get_next_event(array(
				"calstring" => $calstring,
				"start" => $end,
				"fsql" => $fsql,
				"fwhere" => $fwhere,
			));

			$next_active = true;
			$count = $this->num_rows();

			if ($count == 0)
			{
				$this->_get_prev_event(array(
					"calstring" => $calstring,
					"start" => $start,
					"fsql" => $fsql,
					"fwhere" => $fwhere,
				));
			};
		};

		$xstart = 0;
		$ccounter = 0;
		$tmp = aw_ini_get("classes");

		while($row = $this->db_next())
		{
			$ccounter++;
			$xstart = $row["start"];
			if (($args["type"] == "relative") && ($ccounter == 1))
			{
				$rel_next_start = $row["start"];
			};

			if (($args["type"] == "relative") && ($ccounter == $count))
			{
				$rel_prev_start = $row["start"];
			};

			if ($next_active)
			{
				$this->start_time = $row["start"];
			};

			$row["meta"] = aw_unserialize($row["metadata"]);
			unset($row["metadata"]);
                                
			$cal_rel = $row["meta"]["calendar_relation"];
			if (!empty($cal_rel))
			{
				$this->save_handle();
				$q = "SELECT aliases2.target AS tgt FROM aliases
					LEFT JOIN aliases AS aliases2 ON (aliases.target = aliases2.relobj_id)
					WHERE aliases.relobj_id = '$cal_rel'";

				$_cal_id = $this->db_fetch_field($q,"tgt");


				$target = isset($by_calendar[$_cal_id]) ? $by_calendar[$_cal_id] : "";
				$st = date("d-m-Y",$row["start"]);
				if ($target)
				{
					$row["ev_link"] = aw_ini_get("baseurl") . "/section=" . $target . "/date=" . $st;
				}
				else
				{
					global $XX7;
					if ($XX7)
					{
						print "no target found for $_cal_id<br>";
					};
					$row["ev_link"] = aw_ini_get("baseurl");
				};
				$this->restore_handle();

			};

			$gx = date("dmY",$row["start"]);
			$fl = $tmp[$row["class_id"]]["file"];
			if ($fl == "document")
			{
				$row["caption"] = html::href(array(
					"url" => "/" . $row["id"],
					"caption" => $row["name"],
					"date" => $st,
				));
			}
			else
			{
				$row["caption"] = html::href(array(
					"url" => $this->mk_my_orb("view",array("id" => $row["id"]),$fl),
					"caption" => $row["name"],
				));
			};
			$row["title"] = $row["name"];
			$ignore = false;
			// generate custom content if requested

			// now, this is the place where I have to figure out whether the do_orb_method_call
			// returned anything or not
			if (!empty($generator_class) && !empty($generator_method))
			{
				$this->save_handle();
				// I always use the lead field for showing the calendar, while
				// instead I should be using the field that actually contains the alias
				$row["lead"] = $this->do_orb_method_call(array(
					"class" => $generator_class,
					"action" => $generator_method,
					"params" => array(
						"id" => $row["id"],
						"by_calendar" => $by_calendar,
					),
				));
				if (strlen($row["lead"]) == 0)
				{
					$ignore = true;
				};
				$row["moreinfo"] = $row["name"] = $row["caption"] = $row["content"] = "";
				$this->restore_handle();
			}
			// or simply show the requested event
			// week view is NOT generated by doc_gen_preview but I need to parse
			// aliases there as well. 
			elseif ($type != "day")
			{
				// would be nice, if I could figure out a way to parse all those aliases
				// in a single pass
				$almgr->parse_oo_aliases($row["id"],$row["lead"]);
				$almgr->parse_oo_aliases($row["id"],$row["content"]);
				$almgr->parse_oo_aliases($row["id"],$row["moreinfo"]);
			};

			$results[$gx][] = $row;
		}

		$this->next_event = $this->prev_event = "";

		global $XX3;
		if ($XX3)
		{
			arr($args);
		};
		
		if ($this->start_time || $rel_next_start)
		{
			classload("date_calc");

			$range = get_date_range(array(
				"time" => ($type == "relative") ? $rel_next_start : $this->start_time,
				"type" => $type,
				"direction" => 1,
			));


			$this->_get_next_event(array(
				"calstring" => $calstring,
				"start" => $range["end"],
				"fsql" => $fsql,
				"fwhere" => $fwhere,
				"limit" => ($type == "relative") ? $max_items : 0,
			));

			$next = $this->db_next();

			if ($rel_prev_start)
			{
				$range = get_date_range(array(
					"time" => $rel_prev_start,
					"type" => "relative",
					"direction" => 0,
				));
			};

			$this->_get_prev_event(array(
				"calstring" => $calstring,
				"start" => $range["start"],
				"fsql" => $fsql,
				"fwhere" => $fwhere,
			));

			$prev = $this->db_next();
		}

		if (isset($next["start"]))
		{
			$this->next_event = "/section=" . aw_global_get("section") . "/" . "date=" . date("d-m-Y",$next["start"]);
		}

		if (isset($prev["start"]))
		{
			$this->prev_event = "/section=" . aw_global_get("section") . "/" . "date=" . date("d-m-Y",$prev["start"]);
		};
		return $results;
	}

	////
	// !Retrieves the next event for the calendar
	function _get_next_event($args = array())
	{
		extract($args);
		if (!empty($limit))
		{
			$limit = "LIMIT " . ($limit - 1) . ",1";
		}
		else
		{
			$limit = "LIMIT 1";
		};
		$q = "SELECT name,class_id,parent,metadata,planner.*,documents.lead,documents.moreinfo,documents.content FROM objects LEFT JOIN planner ON (objects.brother_of = planner.id) LEFT JOIN documents ON (objects.brother_of = documents.docid) $fsql WHERE $fwhere objects.status != 0 AND parent IN ($calstring) AND (start > '$start') GROUP BY objects.brother_of ORDER BY planner.start,objects.created $limit";
		$this->db_query($q);
	}

	////
	// !Retrieves the previous event for the calendar 
	function _get_prev_event($args = array())
	{
		extract($args);
		$q = "SELECT name,class_id,parent,metadata,planner.*,documents.lead,documents.moreinfo,documents.content FROM objects LEFT JOIN planner ON (objects.brother_of = planner.id) LEFT JOIN documents ON (objects.brother_of = documents.docid) $fsql WHERE $fwhere objects.status != 0 AND parent IN ($calstring) AND (start < '$start') GROUP BY objects.brother_of ORDER BY planner.start DESC,objects.created LIMIT 1";
		$this->db_query($q);
	}

};
?>
