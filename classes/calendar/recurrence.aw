<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/recurrence.aw,v 1.1 2004/03/08 16:51:23 duke Exp $
// recurrence.aw - Kordus 
/*

@classinfo syslog_type=ST_RECURRENCE relationmgr=yes

@default table=objects
@default group=general

@property start type=date_select table=calendar2recurrence 
@caption Alates

@property recur_type type=select field=meta method=serialize
@caption Korduse t��p

@property interval_daily type=textbox size=2 field=meta method=serialize
@caption Iga X p�eva tagant

@property interval_weekly type=textbox size=2 field=meta method=serialize
@caption Iga X n�dala tagant

@property interval_monthly type=textbox size=2 field=meta method=serialize
@caption Iga X kuu tagant

@property interval_yearly type=textbox size=2 field=meta method=serialize
@caption Iga X aasta tagant

property opt1 type=text subtitle=1
caption se seadistused

@property weekdays type=chooser multiple=1 field=meta method=serialize
@caption Nendel p�evadel

@property month_days type=textbox field=meta method=serialize
@caption Kindlatel p�evadel

@property month_rel_weekdays type=chooser multiple=1 field=meta method=serialize
@caption Valitud n�dalap�evadel

@property month_weekdays type=chooser multiple=1 field=meta method=serialize
@caption N�dalap�evad

// l�ppu per-se ei ole. Kuigi selle v�ib m��rata. Igal juhul on see optional
@property end type=date_select table=calendar2recurrence
@caption Kuni

@property test type=text store=no group=test
@caption Test

@groupinfo test caption=Test

@tableinfo calendar2recurrence index=obj_id master_table=objects master_index=brother_of

// recurrence always has a beginning and an end.
// where do I save those?

// do I need fast access to those? Probably not

// the fact is, I need a relation type to bind a recurrence to an event
// and then... if and when I do that, I need to do some fast math
// compensate

// the reason I need a separate table for saving recurrence information is search.
// Searching events should not read in all the existing events and then do some math
// on those. It needs a way to gather events in the requested range only.

*/
define("RECUR_DAILY",1);
define("RECUR_WEEKLY",2);
define("RECUR_MONTHLY",3);
define("RECUR_YEARLY",4);

class recurrence extends class_base
{
	function recurrence()
	{
		$this->init(array(
			"clid" => CL_RECURRENCE
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		$filtered = array("interval_daily","weekdays","interval_weekly","interval_monthly","interval_yearly","month_weekdays","month_rel_weekdays", "month_days");
		$prop_filter = array(
			RECUR_DAILY => array("interval_daily"),
			RECUR_WEEKLY => array("weekdays","interval_weekly"),
			RECUR_MONTHLY => array("interval_monthly","month_weekdays","month_rel_weekdays","month_days"),
			RECUR_YEARLY => array("interval_yearly"),
		);
		$type = $arr["obj_inst"]->prop("recur_type");
		if (empty($type))
		{
			$type = RECUR_DAILY;
		};
		$cur_filter = $prop_filter[$type];
		if (in_array($data["name"],$filtered) && !in_array($data["name"],$cur_filter))
		{
			return PROP_IGNORE;
		};

		switch($data["name"])
		{
			case "weekdays":
				// php date functions give sunday an index of 0, 
				// so I'm doing the same
				$data["options"] = array(
					"1" => "E",
					"2" => "T",
					"3" => "K",
					"4" => "N",
					"5" => "R",
					"6" => "L",
					"0" => "P",
				);
				break;
			
			case "month_weekdays":
				// php date functions give sunday an index of 0, 
				// so I'm doing the same
				$data["options"] = array(
					"1" => "E",
					"2" => "T",
					"3" => "K",
					"4" => "N",
					"5" => "R",
					"6" => "L",
					"0" => "P",
				);
				break;

			case "month_rel_weekdays":
				$data["options"] = array(
					"1" => "esimesel",
					"2" => "teisel",
					"3" => "kolmandal",
					"4" => "neljandal",
					"-1" => "viimasel",

				);
				break;

			case "recur_type":
				$data["options"] = array(
					RECUR_DAILY => "daily",
					RECUR_WEEKLY => "weekly",
					RECUR_MONTHLY => "monthly",
					RECUR_YEARLY => "yearly",
				);
				break;

			case "test":
				$start = $arr["obj_inst"]->prop("start");
				$end = $arr["obj_inst"]->prop("end");
				// now I need to build a cycle from start to the end and have matches at each day
				$rv = $this->calc_range2(array(
					"start" => $start,
					"end" => $end,
					"weekdays" => $arr["obj_inst"]->prop("weekdays"),
				));
				$rv .= "<pre>" . print_r($arr["obj_inst"]->properties(),true) . "</pre>";
				$data["value"] = $rv;
				break;

		};
		return $retval;
	}

	function calc_range2($arr)
	{
		$wd = $arr["weekdays"];
		// bail out, if no weekdays are specified
		if (empty($wd))
		{
			return false;
		};
		$rv = "";
		for ($i = $arr["start"]; $i <= $arr["end"]; $i = $i + 86400)
		{
			$w = date("w",$i);
			// only show days with matches
			if ($wd[$w])
			{
				//$rv .= date("d.m.Y",$i);
				$rv .= date("r",$i);
				$rv .= "<br>";
			};
		};
		return $rv;
	}

	function calc_range_weekly($arr)
	{
		$wd = $arr["weekdays"];
		// bail out, if no weekdays are specified
		if (empty($wd))
		{
			return false;
		};
		// Need to calculate the time shift from the start of the day
		// 3:10 should become 3 * 3600 + 10 * 60

		$start_hour = date("G",$arr["event_start"]);
		$start_min = date("i",$arr["event_start"]);

		$end_hour = date("G",$arr["event_end"]);
		$end_min = date("i",$arr["event_end"]);
		
		$interval = (int)$arr["interval"];
		if ($interval == 0)
		{
			$interval = 1;
		};

		$rv = array();
		// can I calculate the start day, end day and then go from there instead?
		$int_interval = 0;
		for ($i = $arr["start"]; $i <= $arr["end"]; $i = $i + 86400)
		{
			$int_interval++;
			if (0 != ($int_interval % $interval))
			{
				continue;
			};
			$w = date("w",$i);
			if (!$wd[$w])
			{
				continue;
			};
			list($d,$m,$y) = explode("-",date("d-m-Y",$i));
			$day_start = mktime(0,0,0,$m,$d,$y);
			$evt_start = $day_start + (3600 * $start_hour) + (60 * $start_min);
			$evt_end = $day_start + (3600 * $end_hour) + (60 * $end_min);
			$rv[$evt_start] = $evt_end;
		};
		return $rv;
	}
	
	function calc_range_daily($arr)
	{
		// Need to calculate the time shift from the start of the day
		// 3:10 should become 3 * 3600 + 10 * 60

		$interval = (int)$arr["interval"];
		if ($interval == 0)
		{
			$interval = 1;
		};

		$start_hour = date("G",$arr["event_start"]);
		$start_min = date("i",$arr["event_start"]);

		$end_hour = date("G",$arr["event_end"]);
		$end_min = date("i",$arr["event_end"]);

		$rv = array();
		// can I calculate the start day, end day and then go from there instead?
		for ($i = $arr["start"]; $i <= $arr["end"]; $i = $i + ($interval * 86400))
		{
			list($d,$m,$y) = explode("-",date("d-m-Y",$i));
			$day_start = mktime(0,0,0,$m,$d,$y);
			$evt_start = $day_start + (3600 * $start_hour) + (60 * $start_min);
			$evt_end = $day_start + (3600 * $end_hour) + (60 * $end_min);
			$rv[$evt_start] = $evt_end;
		};
		return $rv;
	}

	function calc_range_yearly($arr)
	{
		$interval = (int)$arr["interval"];
		if ($interval == 0)
		{
			$interval = 1;
		};
		
		$start_hour = date("G",$arr["event_start"]);
		$start_min = date("i",$arr["event_start"]);

		$end_hour = date("G",$arr["event_end"]);
		$end_min = date("i",$arr["event_end"]);

		$start_year = date("Y",$arr["start"]);
		$end_year = date("Y",$arr["end"]);

		
		$rv = array();

		list($d,$m) = explode("-",date("d-m",$arr["start"]));

		for ($i = $start_year; $i <= $end_year; $i = $i + $interval)
		{
			$day_start = mktime(0,0,0,$m,$d,$i);
			$evt_start = $day_start + (3600 * $start_hour) + (60 * $start_min);
			$evt_end = $day_start + (3600 * $end_hour) + (60 * $end_min);
			$rv[$evt_start] = $evt_end;
		};

		return $rv;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	

	////
	// !Update recurrence information
	function callback_post_save($arr)
	{
		$this->delete_recurrence(array(
			"id" => $arr["obj_inst"]->id(),
		));

		// now I have to somehow figure out the object id that connects to this
		// recurrence
		$conns = $arr["obj_inst"]->connections_to(array());
		if (sizeof($conns) > 0)
		{
			// retrieving only the first connection is intentional!!!
			$first = reset($conns);
			$src_obj = $first->from();
			$start = $src_obj->prop("start1");
			$end = $src_obj->prop("end");
		};

		$rx = array();

		if (RECUR_WEEKLY == $arr["obj_inst"]->prop("recur_type"))
		{
			$rx = $this->calc_range_weekly(array(
				"start" => $arr["obj_inst"]->prop("start"),
				"end" => $arr["obj_inst"]->prop("end"),
				"event_start" => $start,
				"event_end" => $end,
				"weekdays" => $arr["obj_inst"]->prop("weekdays"),
				"interval" => $arr["obj_inst"]->prop("interval_weekly"),
			));
		}
		elseif (RECUR_DAILY == $arr["obj_inst"]->prop("recur_type"))
		{
			$rx = $this->calc_range_daily(array(
				"start" => $arr["obj_inst"]->prop("start"),
				"end" => $arr["obj_inst"]->prop("end"),
				"event_start" => $start,
				"event_end" => $end,
				"interval" => $arr["obj_inst"]->prop("interval_daily"),
			));
		}
		elseif (RECUR_YEARLY == $arr["obj_inst"]->prop("recur_type"))
		{
			$rx = $this->calc_range_yearly(array(
				"start" => $arr["obj_inst"]->prop("start"),
				"end" => $arr["obj_inst"]->prop("end"),
				"event_start" => $start,
				"event_end" => $end,
				"interval" => $arr["obj_inst"]->prop("interval_yearly"),
			));
		};

		//var_dump($rx);

		if (is_array($rx) && sizeof($rx) > 0)
		{
			$this->create_recurrence(array(
				"id" => $arr["obj_inst"]->id(),
				"start" => $arr["obj_inst"]->prop("start"),
				"end" => $arr["obj_inst"]->prop("end"),
				"tm_list" => $rx,
			));
		};
	}

	function delete_recurrence($arr)
	{
		// recurrence table contains information about a single recurrence.
		$q = "DELETE FROM recurrence WHERE recur_id = '$arr[id]'";
		$this->db_query($q);
	}

	function create_recurrence($arr)
	{
		extract($arr);
		if (!is_array($tm_list) || sizeof($tm_list) == 0)
		{
			return false;
		};
		$parts = array();
		foreach($tm_list as $recur_start => $recur_end)
		{
			// let's make it 1 hour for starters
			//$recur_end = $recur_start + 3600;
			$parts[] = "($id,$recur_start,$recur_end)";

		};
		// that is needless duplication of data. I need to store start and end dates elsewhere!
		// and I need to able to create some shortcuts for events like .. every day ones.
		// there really is no need to write those into the table
		$sql = "INSERT INTO recurrence (recur_id,recur_start,recur_end) VALUES " . join(",",$parts);
		$this->db_query($sql);
	}


	/*
		This binds object table to recurrence table
		mysql> describe calendar2recurrence;
		+--------+---------------------+------+-----+---------+-------+
		| Field  | Type                | Null | Key | Default | Extra |
		+--------+---------------------+------+-----+---------+-------+
		| obj_id | bigint(20) unsigned |      | PRI | 0       |       |
		| start  | bigint(20) unsigned |      |     | 0       |       |
		| end    | bigint(20) unsigned |      |     | 0       |       |
		+--------+---------------------+------+-----+---------+-------+

		This contains information about every single recurrence out there
		mysql> describe recurrence;
		+-------------+---------------------+------+-----+---------+-------+
		| Field       | Type                | Null | Key | Default | Extra |
		+-------------+---------------------+------+-----+---------+-------+
		| recur_id    | bigint(20) unsigned |      | MUL | 0       |       |
		| recur_start | bigint(20) unsigned |      |     | 0       |       |
		| recur_end   | bigint(20) unsigned |      |     | 0       |       |
		+-------------+---------------------+------+-----+---------+-------+


	*/


	/* Now that I have to basic functionality working pretty much fine, I need to figure out a
	  a way to create that "clone" button.

	  1 - create a connections to the original? Or create a connection from the original to the
	  recurring event?
	*/

	/*
		If I'm viewing an event then I can display the prev/next links


	*/

	
}
?>
