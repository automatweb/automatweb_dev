<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_calendar.aw,v 2.7 2002/08/21 12:05:30 duke Exp $
// form_calendar.aw - manages formgen controlled calendars
class form_calendar extends form_base
{
	function form_calendar($args = array())
	{
		$this->form_base();
	}

	////
	// !Loads calendar controller form
	// id(int) - controller form
	// eid(int) - chain entry id
	// start(timestamp) - start of timeframe
	// end(timestamp) - end of timeframe
	
	// cal_controller on see _korduv vorm pärjas, mis sisaldab infot selle
	// konkreetse kalendri vakantside kohta
	function load_cal_controller($args = array())
	{
		extract($args);
		$id = (int)$id;
		$this->load($id);

		if ($this->arr["has_calendar"] && $this->arr["cal_controller"])
		{
			$cal_controller = $this->arr["cal_controller"];
			$event_entry_form = $this->arr["event_entry_form"];
		};

		if (not($cal_controller))
		{
			// FIXME: shouldn't just drop out
			print "this form has no calendar controller, cannot show calendar!";
			exit;
		};

		// need to figure out what elements of the controller form control the behaviour of
		// the calendar
		$els = $this->get_form_elements(array("id" => $cal_controller));
		

		foreach($els as $key => $el)
		{
			// start of the period
			if ( ($el["type"] == "date") && ($el["subtype"] == "from") )
			{
				$this->start_el = $el["id"];
			};

			// end of the period
			if ( ($el["type"] == "date") && ($el["subtype"] == "to") )
			{
				$this->end_el = $el["id"];
			};

			// max entries in one window
			if ( ($el["type"] == "textbox") && ($el["subtype"] == "count") )
			{
				$this->max_el = $el["id"];
			};

			// length of the windows
			if ( ($el["type"] == "timeslice") && (!$this->tslice_el) )
			{
				$this->tslice_el = $el["id"];
			};

			// pregap, reserved time before the start of the window
			if ( ($el["type"] == "timeslice") && ($this->tslice_el > 0) )
			{
				$this->tslice_el2 = $el["id"];
			};
		};
		
		// need all the entry id-s for that chain entry
		$eids = $this->get_form_entries_for_chain_entry($eid,$cal_controller);
		
		// figure out all the windows defined by the controller form entries
		$blocks = array();


		foreach($eids as $entry_id)
		{
			$new_blocks = $this->_ctrl_process_entry(array(
							"entry_id" => $entry_id,
							"start" => $start,
							"end" => $end,
			));

			$blocks = $blocks + $new_blocks;
		};
		

		$this->vector = array();
		
		
		// get events in range and build an incremental vector of all events. for
		// example, if we have 2 events, one from 10:00-13:00 and other from
		// 11:00-14:00, then the vector will look akin to (keys are timestamps)
		// [10:00] -> +1
		// [11:00] -> +1
		// [13:00] -> -1
		// [14:00] -> -1

		// --------

		if ($event_entry_form)
		{
			$events = $this->_get_entries_in_range(array(
								"id" => $event_entry_form,
								"start" => $start,
								"end" => $end,
								"cal_id" => $eid,
								"ignore" => $ignore,
			));
		}
		else
		{
			$events = array();
		};
		

		// now we have all the ranges (if any) and events (if any)
		// and are going to build the events for calendar


		ksort($this->vector);
		// I have three kinds of data
		// 1) time windows defined by the calendar controller form (blocks)
		// 2) events that were entered through the event entry form
		// 3) incremental timeline for all events
		
		// there will be two kinds of those events
		// 1 - usual entries, which link to their editing forms
		// 2 - special entries, which show how many vacancies are left
		// in a given time slot and link to the reservation form again
		// but this time that form will be prefilled with start date.
		// special entries are shown only if there are actually vacancies in
		// that slot

		// so then, first I will go over all the blocks and figure out how 
		// many reservations each one has
		$this->blocks = $blocks;
		reset($this->blocks);
		
		if ( is_array($this->blocks) && (sizeof($this->blocks) > 0) )
		{
			array_walk($this->vector,array(&$this,"_process_frame"));
		}
		
		
		// If I want to find vacancies, then I have to check whether each block has
		// the required amount of blocks available:
		

		if ($check == "vacancies")
		{
			$this->has_vacancies = true;
		}
			
		// do not show usual entries		
		//$events = array();

		// no blocks, just bail out
		if (sizeof($this->blocks) == 0)
		{
			$this->has_vacancies = false;
		}

		// done. now we can build the special entries for the calenar
		
		foreach($this->blocks as $bid => $block)
		{
			$dkey = date("dmY",$block["start"]);
			$cnt = (int)$this->event_counts[$dkey];
			//$vac = $block["max"] - $block["cnt"];
			$vac = $block["max"] - $cnt;

			$title = "<small>free <b>$vac of $block[max]</b></small>";

			if ($check == "vacancies")
			{
				if ($vac < $count)
				{
					$this->has_vacancies = false;
				};
			};


			if ($vac == $block["max"])
			{
				$color = "#AAFFAA";
			}
			elseif ($vac == 0)
			{
				//$title = "fully booked ($block[max])!";
				$color = "#FF6633";
			}
			// shouldn't happen, maybe should even be configurable?
			elseif ($vac < 0)
			{
				$title = sprintf("<b>OVERBOOKED BY %d!</b>",abs($vac));
				$color = "#FF0000";
			}
			else
			{
				$color = "#FFFFAA";
			};
			
			$this->raw_headers[$dkey] = $title;
		
			$dummy = array(
				"title" => $title,
				"start" => $block["start"],
				"end" => $block["end"],
				"color" => $color,
				"target" => "_new",
			);

			if ($vac != 0)
			{
				$events[$dkey][] = $dummy;
			}
		};

		// and now add the usual entries too


		// and then there is that problem somewhere behind the horizon ...
		// how do we check whether the entered event is a valid one?


		if ($check == "vacancies")
		{
			return $this->has_vacancies;
		}
		else
		{
			return $events;
		};

		

	}
	
	////
	//! Processes frames
	function _process_frame($val,$key)
	{
		// meid huvitab ainult vektori see osa, mis asub enne meid huvitava
		// perioodi lõppu, s.t. me peame kokku liitma kõik väärtused, mis
		// enne perioodi lõppu toimuvad
		// ma pean teadma, mis seisus on vastav vektor enne mind huvitava
		// ajalõigu algust, this code will take care of it

		// this will be invoked only at the first pass
		if (not($this->current_block))
		{
			list($this->bid,$this->current_block) = each($this->blocks);
			$this->state = 0;
		};

		// advance the other pointer, repeat and rinse until we find the first block,
		// which actually matches the first key of the vector (try to sync the array pointers)
		while ( ($key > $this->current_block["start"]) && ($this->bid) && ($this->bid < sizeof($this->blocks)) )
		{
			
			$this->blocks[$this->bid]["cnt"] = $this->state;
			list($this->bid,$this->current_block) = each($this->blocks);
		};
		
		$this->state += $val;

		if ($key <= $this->current_block["end"])
		{
			$this->blocks[$this->bid]["cnt"] = $this->state;

		}
		else
		{
			// still, I have to set the state up to the end of the blocks
			if (isset($this->bid))
			{
				$this->blocks[$this->bid]["cnt"] = $this->state;
			};
		};
			
		// advance the other pointer too			
		list($this->bid,$this->current_block) = each($this->blocks);
	}

	////
	// !Fetches all form entries from a date range
	// id(int) - form id
	// start(int) - start of the range
	// end(int) - end of the range
	function _get_entries_in_range($args = array())
	{
		extract($args);
		$this->load($id);
		$ctls = $this->get_form_elements(array("id" => $id));
		foreach($ctls as $key => $val)
		{
			if ( ($val["type"] == "date") && ($val["subtype"] == "from") )
			{
				$el_start = $val["id"];
			}

			if ( ($val["type"] == "date") && ($val["subtype"] == "to") )
			{
				$el_end = $val["id"];
			}
			
			if ( ($val["type"] == "textbox") && ($val["subtype"] == "count") )
			{
				$count_el = $val["id"];
			}

		};

		$this->ev_entry_start_el = $el_start;
		$this->ev_entry_end_el = $el_end;

		$ft_name = sprintf("form_%s_entries",$id);

		// this means that we can ignore one entry when doing our calculations
		$ignore = (int)$ignore;

		// this query could be faster if the date elements in the database
		// would be integers

		// I should ignore events which
		// 1) end before my time slot starts ($ev[end] < $start)
		// 2) start after my time slot ends ($ev[start] > $end)
		// 3) are $ignored
		$chains = $this->get_chains_for_form($id);
		list($cid,) = each($chains);

		$q = sprintf("SELECT *,objects.name AS name FROM $ft_name
				LEFT JOIN objects ON ($ft_name.id = objects.oid)
				LEFT JOIN form_entries ON ($ft_name.id = form_entries.id)
				WHERE form_entries.cal_id = '$cal_id' AND objects.status = 2 AND objects.oid != $ignore AND (el_%s >= %d) AND (el_%s <= %d)",
				$el_end,$start,$el_start,$end);

		$this->db_query($q);
		$events = array();
		$this->raw_events = array();
		$this->raw_headers = array();

		while($row = $this->db_next())
		{
			$e_start = $row["el_" . $el_start];
			$e_end = $row["el_" . $el_end];
			$e_count = (int)$row["el_" . $count_el];

			$dkey = date("dmY",$e_start);

			$event = array(
				"start" => $e_start,
				"end" => $e_end,
				"title" => $row["name"],
				"link" => $this->mk_my_orb("show",array("id" => $cid,"entry_id" => $row["oid"]),"form_chain"),
			);

			$events[$dkey][] = $event;	

			for ($xi = $e_start; $xi < $e_end; $xi = $xi + (60*60*24))
			{
				$dkey = date("dmY",$xi);
				$this->raw_events[$dkey][] = $row;
				$this->event_counts[$dkey] += $e_count;
			}
		
			// update the timeline vector as well	
			// --------------
			// set 'em to zero, if we encounter them the first time
			if (not($this->vector[$e_start]))
			{
				$this->vector[$e_start] = 0;
			};
			
			if (not($this->vector[$e_end]))
			{
				$this->vector[$e_end] = 0;
			};

			// and now increment/decrement the value
			$this->vector[$e_start] += $e_count;
			$this->vector[$e_end] -= $e_count;
		}

		return $events;
	}
	
	////
	// !Fetches all form entries from a date range
	// id(int) - form id
	// start(int) - start of the range
	// end(int) - end of the range
	function _get_entries_in_range2($args = array())
	{
		extract($args);

		$ft_name = sprintf("form_%s_entries",$eform);

		$q = "SELECT * FROM calendar2event
			LEFT JOIN objects ON calendar2event.entry_id = objects.oid
			WHERE cal_id = '$cal_id' AND relation = '$ctrl' AND start >= '$start' AND end <= '$end' AND objects.status = 2";
		$this->db_query($q);
		$events = array();
		$this->raw_events = array();
		$this->raw_headers = array();

		while($row = $this->db_next())
		{
			$e_start = $row["start"];
			$e_end = $row["end"];
			if (!$e_end)
			{
				$e_end = $e_start + 86399;
			};
			$e_count = (int)$row["items"];

			$dkey = date("dmY",$e_start);

			$event = array(
				"start" => $e_start,
				"end" => $e_end,
				"title" => $row["name"],
				"link" => $this->mk_my_orb("show",array("id" => $cid,"entry_id" => $row["oid"]),"form_chain"),
			);

			$events[$dkey][] = $event;	

			for ($xi = $e_start; $xi < $e_end; $xi = $xi + (60*60*24))
			{
				$dkey = date("dmY",$xi);
				$this->raw_events[$dkey][] = $row;
				$this->event_counts[$dkey] += $e_count;
			}
		
			// update the timeline vector as well	
			// --------------
			// set 'em to zero, if we encounter them the first time
			if (not($this->vector[$e_start]))
			{
				$this->vector[$e_start] = 0;
			};
			
			if (not($this->vector[$e_end]))
			{
				$this->vector[$e_end] = 0;
			};

			// and now increment/decrement the value
			$this->vector[$e_start] += $e_count;
			$this->vector[$e_end] -= $e_count;
		}

		return $events;
	}

	////
	// !Processes one controller form entry
	function _ctrl_process_entry($args = array())
	{
		extract($args);

		$this->load_entry($entry_id);

		// maybe this function should return the building blocks instead?
		// available date ranges and max items in each?
		$ct_start = $this->entry[$this->start_el];
		$ct_end = $this->entry[$this->end_el];
		$ct_max = $this->entry[$this->max_el];
		$ct_tslice = aw_unserialize($this->entry[$this->tslice_el]);
		$ct_cnt = ($ct_tslice["count"] > 0) ? $ct_tslice["count"] : 1;
		$ct_tslice2 = aw_unserialize($this->entry[$this->tslice_el2]);
		// this one can be zero as well
		$ct_pregap = $ct_tslice2["count"];

		$shift = ($ct_tslice["type"] == "day") ? 3600 * 24 : 3600;

		$blocks = array();

		// XXX 3600
		$timeshift = $ct_pregap * 3600;
		for ($i = ($start + $timeshift); $i <= $end; $i=$i+($shift * $ct_cnt)+$timeshift)
		{
			// if it is in range, then ..
			if ( ($i >= $ct_start) && ($i <= $ct_end) )
			{
				$blocks[] = array(
					"start" => $i,
					"end" => $i+($shift * $ct_cnt) - 1,
					"max" => $ct_max,
				);
			};
		}

		return $blocks;
        }

	function new_event($args = array())
	{
		extract($args);
		$q = "INSERT INTO calendar2event (entry_id,cal_id,start,end,items)
			VALUES ('$entry_id','$cal_id','$start','$end','$items')";
		$this->db_query($q);
	}

	function upd_event($args = array())
	{
		extract($args);
		$q = "DELETE FROM calendar2event WHERE entry_id = '$entry_id'";
		$this->db_query($q);

		$this->new_event($args);

	}

	////
	// !Registers a new calendar (invoked from form_chain submit)
	// cal_id - int
	// form_id - int id of event entry form
	// vform_id - int id of the period definition form inside the chain
	function new_calendar($args = array())
	{
		extract($args);
		$q = "INSERT INTO calendar2object (cal_id,form_id,vform_id)
			VALUES ('$cal_id','$form_id','$vform_id')";
		$this->db_query($q);
	}
	
	////
	// !Updates an existing calendar (invoked from form_chain submit)
	// cal_id - int
	// form_id - int id of event entry form
	// vform_id - int id of the period definition form inside the chain
	function upd_calendar($args = array())
	{
		extract($args);
		// reap the old entry
		$q = "DELETE FROM calendar2object WHERE cal_id = '$cal_id'";
		$this->db_query($q);

		// only re-add if the user still wishes to have a calendar
		// for that chain
		if ($active)
		{
			$this->new_calendar($args);
		}

		// event entry form needs to know to which calendar 
		// it will store it's entries
		$fb = get_instance("form_base");
		$fb->load($form_id);
		$fb->meta["calendar_chain"] = $cal_id;
		$fb->save();
	}

	////
	// !Retrieves calender record
	// cal_id - int
	function get_calendar($args = array())
	{
		extract($args);
		$row = false;
		if ($cal_id)
		{
			$q = "SELECT * FROM calendar2object WHERE cal_id = '$cal_id'";
			$this->db_query($q);
			$row = $this->db_next();
		}
		return $row;
	}

	////
	// !updates calende time period definitons
	function upd_timedef_xxx($args = array())
	{
		extract($args);
		// figure out which form element does what
		// but that's obsolete, I already know 
		// how it's done
		foreach($els as $key => $el)
		{
			// start of the period
			if ( ($el["type"] == "date") && ($el["subtype"] == "from") )
			{
				$start_el = $el["id"];
			};

			// end of the period
			if ( ($el["type"] == "date") && ($el["subtype"] == "to") )
			{
				$end_el = $el["id"];
			};

			// max entries in one window
			if ( ($el["type"] == "textbox") && ($el["subtype"] == "count") )
			{
				$max_el = $el["id"];
			};

			// length of the windows
			if ( ($el["type"] == "timeslice") && (!$tslice_el) )
			{
				$tslice_el = $el["id"];
			};

			// pregap, reserved time before the start of the window
			if ( ($el["type"] == "timeslice") && ($tslice_el > 0) )
			{
				$tslice_el2 = $el["id"];
			};
		};
		
		if (($args["arr"]["event_start_el"]))
		{
			$start_el = $args["arr"]["event_start_el"];
		};

		/*
		print "start = $start_el<br>";
		print "end = $end_el<br>";
		print "max = $max_el<br>";
		print "tslice_el = $tslice_el<br>";
		print "tslice_el2 = $tslice_el2<br>";
		*/
		// now I have the indexes for $entry array so I have to form the query 
		// for calendar2timedef table

		// first delete the existing record
		
		$q = "DELETE FROM calendar2timedef WHERE entry_id = '$entry_id'";
		$this->db_query($q);

		$ct_start = $entry[$start_el];
		if (not($end_el))
		{
			$ct_end = $ct_start + 3600;
		}
		else
		{ 
			$ct_end = $entry[$end_el];
		};

		$ct_max = $entry[$max_el];

		$ct_tslice = (int)$entry[$tslice_el]["count"];
		if ($ct_tslice == 0)
		{
			$ct_tslice = 1;
		};

		//$ct_tslice2 = $entry[$tslice_el2]);
		// this one can be zero as well
		//$ct_pregap = $ct_tslice2["count"];

		$q = "INSERT INTO calendar2timedef 
			(entry_id,cal_id,start,end,timedef,max_items)
			VALUES ('$entry_id','$cal_id','$ct_start','$ct_end','$ct_tslice',
				'$ct_max')";

		$this->db_query($q);
		//print $q;
	}
		
	function _process_blocks($args = array())
	{
		//$shift = ($ct_tslice["type"] == "day") ? 3600 * 24 : 3600;
		extract($args);
		$shift = 3600 * 24;
		$blocks = array();

		// XXX 3600
		$timeshift = $pregap * 3600;

		if ($period_cnt < 1)
		{
			$period_cnt = 1;
		};

		//for ($i = ($start + $timeshift); $i <= $end; $i=$i+($shift * $timedef)+$timeshift)
		for ($i = ($start); $i <= $end; $i=$i+($shift * $period_cnt))
		{
			flush();
			// if it is in range, then ..
			if ( ($i >= $start) && ($i <= $end) )
			{
				$blocks[] = array(
					"start" => $i,
					"end" => $i+($shift) - 1,
					"max" => $max_items,
				);
			};
		}
		return $blocks;
	}
	
	function check_vacancies($args = array())
	{
		$id = (int)$id;
		extract($args);	
		$found = false;
		$blocks = array();

		if ($eid)
		{
			$q = "SELECT * FROM calendar2timedef LEFT JOIN objects ON (calendar2timedef.entry_id = objects.oid) WHERE cal_id = '$eid' AND start <= '$start' AND end >= '$end' AND status = 2";
			$this->db_query($q);
			while($row = $this->db_next())
			{
				$found = true;
				$new_blocks = $this->_process_blocks($row);
				$blocks = $blocks + $new_blocks;
			}
			$this->vector = array();
		
		
			// get events in range and build an incremental vector of all events. for
			// example, if we have 2 events, one from 10:00-13:00 and other from
			// 11:00-14:00, then the vector will look akin to (keys are timestamps)
			// [10:00] -> +1
			// [11:00] -> +1
			// [13:00] -> -1
			// [14:00] -> -1

			// --------

			$events = $this->_get_entries_in_range2(array(
							"start" => $start,
							"end" => $end,
							"cal_id" => $eid,
							"eform" => $eform,
			));


			// now we have all the ranges (if any) and events (if any)
			// and are going to build the events for calendar
			ksort($this->vector);
			$this->blocks = $blocks;
			reset($this->blocks);
		
			if ( is_array($this->blocks) && (sizeof($this->blocks) > 0) )
			{
				array_walk($this->vector,array(&$this,"_process_frame"));
			}
		
			// If I want to find vacancies, then I have to check whether each block has
			// the required amount of blocks available:
			

			//if ($check == "vacancies")
			//{
				$this->has_vacancies = true;
			//}
			
			// do not show usual entries		
			//$events = array();

			// no blocks, just bail out
			if (sizeof($this->blocks) == 0)
			{
				$this->has_vacancies = false;
			}

			// done. now we can build the special entries for the calenar
			
			foreach($this->blocks as $bid => $block)
			{
				$dkey = date("dmY",$block["start"]);
				$cnt = (int)$this->event_counts[$dkey];
				//$vac = $block["max"] - $block["cnt"];
				$vac = $block["max"] - $cnt;
				$title = "<small>free <b>$vac of $block[max]</b></small>";
	
				if ($vac < $count)
				{
					$this->has_vacancies = false;
				};
	
			};
	
		}
		return $this->has_vacancies;
		#return $found;

	}
	
	function get_events($args = array())
	{
		$id = (int)$id;
		extract($args);	
		$found = false;
		$blocks = array();
		$events = array();
		$this->raw_events = array();
		$this->raw_headers = array();

		//$this->load($eform);
		$q = "SELECT * FROM calendar2timedef LEFT JOIN objects ON (calendar2timedef.entry_id = objects.oid) WHERE calendar2timedef.oid = '$eform' AND start <= '$start' AND end >= '$end' AND status = 2";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$found = true;
			$new_blocks = $this->_process_blocks($row);
			$blocks = $blocks + $new_blocks;
		}

		$this->vector = array();

		if ($eform)
		{
			//$q = "SELECT * FROM calendar2timedef LEFT JOIN objects ON (calendar2timedef.entry_id = objects.oid) WHERE calendar2timedef.oid = '$eid' AND start <= '$start' AND end >= '$end' AND status = 2";

			// get events in range and build an incremental vector of all events. for
			// example, if we have 2 events, one from 10:00-13:00 and other from
			// 11:00-14:00, then the vector will look akin to (keys are timestamps)
			// [10:00] -> +1
			// [11:00] -> +1
			// [13:00] -> -1
			// [14:00] -> -1

			// --------
	
			$events = $this->_get_entries_in_range2(array(
							"start" => $start,
							"end" => $end,
							"cal_id" => $eid,
							"ctrl" => $ctrl,
			));

			// now we have all the ranges (if any) and events (if any)
			// and are going to build the events for calendar
			ksort($this->vector);
			$this->blocks = $blocks;
			reset($this->blocks);
		
			if ( is_array($this->blocks) && (sizeof($this->blocks) > 0) )
			{
				array_walk($this->vector,array(&$this,"_process_frame"));
			}
		
			// If I want to find vacancies, then I have to check whether each block has
			// the required amount of blocks available:
			

			//if ($check == "vacancies")
			//{
				$this->has_vacancies = true;
			//}
			
			// do not show usual entries		
			//$events = array();

			// no blocks, just bail out
			if (sizeof($this->blocks) == 0)
			{
				$this->has_vacancies = false;
			}

			// done. now we can build the special entries for the calenar

			
			foreach($this->blocks as $bid => $block)
			{
				$dkey = date("dmY",$block["start"]);
				$cnt = (int)$this->event_counts[$dkey];
				//$vac = $block["max"] - $block["cnt"];
				$vac = $block["max"] - $cnt;

				$title = "<small>free <b>$vac of $block[max]</b></small>";

				if ($vac == $block["max"])
				{
					$color = "#AAFFAA";
				}
				elseif ($vac == 0)
				{
					//$title = "fully booked ($block[max])!";
					$color = "#FF6633";
				}
				// shouldn't happen, maybe should even be configurable?
				elseif ($vac < 0)
				{
					$title = sprintf("<b>OVERBOOKED BY %d!</b>",abs($vac));
					$color = "#FF0000";
				}
				else
				{
					$color = "#FFFFAA";
				};
			
				$this->raw_headers[$dkey] = $title;
		
				$dummy = array(
					"title" => $title,
					"start" => $block["start"],
					"end" => $block["end"],
					"color" => $color,
					"target" => "_new",
				);

				if ($vac != 0)
				{
					$events[$dkey][] = $dummy;
				}
			};
	
		}
		return $events;
		#return $found;

	}
		
};
?>
