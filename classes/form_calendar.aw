<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/form_calendar.aw,v 2.10 2002/08/23 22:38:10 duke Exp $
// form_calendar.aw - manages formgen controlled calendars
class form_calendar extends form_base
{
	function form_calendar($args = array())
	{
		$this->form_base();
	}

	////
	//! Processes frames
	function _process_frame($val,$key)
	{
		// meid huvitab ainult vektori see osa, mis asub enne meid huvitava
		// perioodi lıppu, s.t. me peame kokku liitma kıik v‰‰rtused, mis
		// enne perioodi lıppu toimuvad
		// ma pean teadma, mis seisus on vastav vektor enne mind huvitava
		// ajalıigu algust, this code will take care of it

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
			$this->save_handle();
			$q = "SELECT * FROM form_" . $row["form_id"] . "_entries WHERE id = '$row[entry_id]'";
			$this->db_query($q);
			$row2 = $this->db_next();
			$this->restore_handle();
			$row = $row + $row2;
			$e_start = $row["start"];
			$e_end = $row["end"];
			if (!$e_end)
			{
				$e_end = $e_start + 86399;
			};
			$e_count = (int)$row["items"];

			$dkey = date("dmY",$e_start);
			$ekey = date("dmY",$e_end);

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
		extract($args);	
		$id = (int)$id;
		/*
		print "check_vacs()<br>";
		print "<pre>";
		print_r($args);
		print "</pre>";
		print "<br>";
		*/

		if (is_array($entry_id))
		{
			$r_entry_id = join(",",$entry_id);
		}
		else
		{
			$r_entry_id = $entry_id;
		};
		
		/*
		$q = "SELECT SUM(max_items) AS max FROM calendar2timedef
			 WHERE oid = '$contr' 
				AND relation IN ($r_entry_id) AND start <= '$end' AND end >= '$start'";
		*/
		$q = "SELECT MIN(max_items) AS max FROM calendar2timedef
			 WHERE oid = '$contr' 
				AND relation IN ($r_entry_id) AND start <= '$end' AND end >= '$start'";

		/*
		print "start = $start, end = $end<br>";
		*/

		$this->db_query($q);
		$row2 = $this->db_next();
		$max = (int)$row2["max"];
		//print "loading window for $entry_id - it has $max max slots<br>";
		// and now, for each calendar figure out how many
		// free spots does it have in the requested period.
		// for this, I'll have to query the calendar2event table

		// kui eventi_lopp >= otsingu_algus OR eventi_algus <= otsingu_lopp
		// siis langeb see event meid huvitava ajavahemiku sisse ja ma tean
		// tema broneeritud ruumide summaga arvestama
		$q = "SELECT SUM(items) AS sum FROM calendar2event
			LEFT JOIN objects ON (calendar2event.entry_id = objects.oid)
			WHERE objects.status = 2 AND cal_id = '$cal_id' AND form_id = '$id'
				AND relation = '$entry_id' 
				AND end >= '$start' AND start <= '$end'";
		/*
		print $q;
		print "<br>";
		*/
		$this->db_query($q);
		$row2 = $this->db_next();
		$sum = (int)$row2["sum"];
		$vac = $max - $sum - $req_items;
		//print "id = $r_entry_id, max avail = $max, reserved = $sum, vac = $vac, requested = $req_items<br>";
		//print "$sum ruumi on broneeritud<br>";
		//print "$vac j‰‰ks j‰rgi<br>";
		return $vac;
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
		$q = "SELECT * FROM calendar2timedef LEFT JOIN objects ON (calendar2timedef.entry_id = objects.oid) WHERE calendar2timedef.oid = '$eform' AND start <= '$start' AND end >= '$end' AND relation = '$ctrl' AND status = 2";
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

	////
	// !Returns the number of vacancies
	// start (array) - period start
	// end (timestamp) - period end
	// cal_id (int) - calendar id
	// entry_id (int) - entry id
	// contr (int) - calendar controller id
	// req_items (int) - how many items do we want?
	// rel(int) - selector for calendar
	function get_vac_by_contr($args = array())
	{
		extract($args);
		$_start = get_ts_from_arr($args["start"]);
		list($_d,$_m,$_y) = explode("-",date("d-m-Y",$_start));
		$_end = mktime(23,59,59,$_m,$_d,$_y);
		$q = "SELECT SUM(max_items) AS max FROM calendar2timedef
			 WHERE oid = '$contr' AND relation IN ($rel2)
				AND start <= '$_end' AND end >= '$_start'";
		$this->db_query($q);
		$row2 = $this->db_next();
		$max = (int)$row2["max"];
		// and now, for each calendar figure out how many
		// free spots does it have in the requested period.
		// for this, I'll have to query the calendar2event table
		
		// kui eventi_lopp >= otsingu_algus OR eventi_algus <= otsingu_lopp
		// siis langeb see event meid huvitava ajavahemiku sisse ja ma tean
		// tema broneeritud ruumide summaga arvestama

		$q = "SELECT SUM(items) AS sum FROM calendar2event
			LEFT JOIN objects ON (calendar2event.entry_id = objects.oid)
			WHERE oid != '$entry_id' AND relation = '$rel' AND objects.status = 2 AND
				cal_id = '$cal_id' AND form_id = '$id' AND end >= '$_start' AND
				start <= '$_end'";
		$this->db_query($q);
		$row2 = $this->db_next();
		$sum = (int)$row2["sum"];
		//print "max = $max, sum = $sum, req = $req_items<br>";
		$vac = $max - $sum - $req_items;
		return $vac;
	}

	function get_rel_el($args = array())
	{
		extract($args);
		$q = "SELECT * FROM calendar2forms WHERE cal_id = '$id'";
		$this->db_query($q);
		$c2f = $this->db_next();
		// now c2f["form_id"] has the id of event entry form
		// and c2f["el_relation"] has the id of the element in that form, which
		// interests us. A relation element. Now we figure from which form
		// that element originates
		$q = "SELECT * FROM form_relations WHERE el_to = $c2f[el_relation]";
		$this->db_query($q);
		$f_r = $this->db_next();

		// $f_r[form_from] is it.
		// $f_r[el_from] is the element id which contains the information we want
		/*
		print "form: $f_r[form_from] el: $f_r[el_from]<br>";
		*/

		// and now the final step - figure out, which
		// load the current chain entry

		$q = "SELECT * FROM form_chain_entries WHERE id = '$chain_entry_id'";
		$this->db_query($q);
		$fce = $this->db_next();
		$_eids = aw_unserialize($fce["ids"]);

		/*
		print "<pre>";
		print_r($_eids);
		print "</pre>";
		*/

		$_eid = $_eids[$f_r["form_from"]];
		return $_eid;

	}

	function get_other_entries($args = array())
	{
		extract($args);
		if ($this->cached_others["id"] == $id)
		{
			return $this->cached_others["data"];
		};
		$q = "SELECT form_id FROM form_entries WHERE id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();
		$others = array();
		$q = "SELECT id FROM form_entries WHERE form_id = '$row[form_id]'";
		$this->db_query($q);
		while($row = $this->db_next())
		{
			$others[$row["id"]] = $row["id"];

		}
		$this->cached_others["id"] = $id;
		$this->cached_others["data"] = $others;
		return $others;
	}
		
};
?>
