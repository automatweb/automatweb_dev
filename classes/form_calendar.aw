<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_calendar.aw,v 2.1 2002/05/10 02:56:23 duke Exp $
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


		

		// need to figure out what elements of controller form control the behaviour of
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

		array_walk($this->vector,array(&$this,"_process_frame"));

		// done. now we can build the special entries for the calenar
		foreach($this->blocks as $block)
		{
			$dkey = date("dmY",$block["start"]);
			$vac = $block["max"] - $block["cnt"];
			$title = "has <b>$vac</b> vacations";
			if ($vac == $block["max"])
			{
				$color = "#AAFFAA";
			}
			elseif ($vac == 0)
			{
				$title = "fully booked ($block[max])!";
				$color = "#FF6633";
			}
			// shouldn't happen
			elseif ($vac < 0)
			{
				$title = sprintf("<b>OVERBOOKED BY %d!</b>",abs($vac));
				$color = "#FF0000";
			}
			else
			{
				$color = "#FFFFAA";
			};
		
			$dummy = array(
				"title" => $title,
				"start" => $block["start"],
				"end" => $block["end"],
				"color" => $color,
			);
			$events[$dkey][] = $dummy;
		};

		// and now add the usual entries too


		// and then there is that problem somewhere behind the horizon ...
		// how do we check whether the entered event is a valid one?
		/*
		print "<pre>";	
		print_r($events);
		print "</pre>";
		*/

		return $events;

		

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
		while ( ($key > $this->current_block["end"]) && ($this->bid < sizeof($this->blocks)) )
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
			$this->blocks[$this->bid]["cnt"] = $this->state;
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

		};

		$ft_name = sprintf("form_%s_entries",$id);

		// this query could be faster if the date elements in the database
		// would be integers
		$q = sprintf("SELECT *,objects.name AS name FROM $ft_name
				LEFT JOIN objects ON ($ft_name.id = objects.oid)
				WHERE (el_%s >= %d) AND (el_%s <= %d)",
				$el_start,$start,$el_start,$end);

		$this->db_query($q);
		$events = array();

		while($row = $this->db_next())
		{
			$e_start = $row["el_" . $el_start];
			$e_end = $row["el_" . $el_end];

			$dkey = date("dmY",$e_start);

			$event = array(
				"start" => $e_start,
				"end" => $e_end,
				"title" => $row["name"],
			);
			
			$events[$dkey][] = $event;	
		
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
			$this->vector[$e_start]++;
			$this->vector[$e_end]--;
		}
		return $events;
	}

	////
	// !Processes one controller form entry
	function _ctrl_process_entry($args = array())
	{
		extract($args);
		/*
		print "<pre>";
		print_r($args);
		print "</pre>";
		*/
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

		$blocks = array();

		// XXX 3600
		$timeshift = $ct_pregap * 3600;
		for ($i = ($start + $timeshift); $i <= $end; $i=$i+(3600 * $ct_cnt)+$timeshift)
		{
			// if it is in range, then ..
			if ( ($i >= $ct_start) && ($i <= $ct_end) )
			{
				$blocks[] = array(
					"start" => $i,
					"end" => $i+(3600 * $ct_cnt) - 1,
					"max" => $ct_max,
				);
			};
		}

		return $blocks;
        }



};
?>
