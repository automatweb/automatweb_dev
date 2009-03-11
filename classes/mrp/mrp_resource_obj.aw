<?php

class mrp_resource_obj extends _int_object
{
	public function awobj_set_production_feedback_option_values($value = array())
	{
		if (!is_array($value))
		{
			throw new awex_obj_type("Array required.");
		}

		$quantities = array();
		foreach ($value as $quantity)
		{
			$quantities[] = (int) $quantity; ///!!! unit-i j2rgi, v6ibolla float kuskil hoopis
		}

		return $this->set_prop("production_feedback_option_values", $value);
	}

	public function awobj_get_production_feedback_option_values()
	{
		return (array) $this->prop("production_feedback_option_values");
	}

	/**
	@attrib api=1 params=pos
	@returns array of material_expense_condition objects
		Objects that refer to products that can be used as input materials on this resource. Object id-s as index.
	**/
	public function get_possible_materials()
	{
		$ol = new object_list(array(
			"class_id" => CL_MATERIAL_EXPENSE_CONDITION,
			"lang_id" => array(),
			"site_id" => array(),
			"resource" => $this->id()
		));
		return $ol->arr();
	}

	/** Calculates and returns fixed unavailable periods effective between points in time specified by $start and $end
	@attrib api=1 params=pos
	@param start required type=int UNIX timestamp
	@param end required type=int UNIX timestamp
	@returns array
		array (
			period1_start_int_unixtimestamp => period1_end_int_unixtimestamp,
			...
		)
	**/
	public function get_unavailable_periods ($start, $end)
	{
// /* dbg */ if ($resource->id () == 6670  ) {
// /* dbg */ $this->mrpdbg=1;
// /* dbg */ }

		$period_start = $start;
		$period_end = $end;
		$unavailable_dates = array ();
		$dates = $this->prop ("unavailable_dates");
		$dates = explode (";", $dates);
		$separators = " ,.:/|-\\";
		$period_start_year = date ("Y", $period_start);
		foreach ($dates as $date)
		{
			$start_day = (int) strtok ($date, $separators);
			$start_mon = (int) strtok ($separators);
			$start_hour = (int) strtok ($separators);
			$start_min = (int) strtok ($separators);
			$end_day = (int) strtok ($separators);
			$end_mon = (int) strtok ($separators);
			$end_hour = (int) strtok ($separators);
			$end_min = (int) strtok ($separators);
			$in_period_range = true;
			$year = $period_start_year;

			while ($in_period_range)
			{
				$start = mktime ($start_hour, $start_min, 0, $start_mon, $start_day, $year);
				$end = mktime ($end_hour, $end_min, 0, $end_mon, $end_day, $year);

				if ($start < $period_end)
				{
					if ($start < $end)
					{
						$unavailable_dates[$start] = max ($end, $unavailable_dates[$start]);
					}
				}
				else
				{
					$in_period_range = false;
				}

				$year++;
			}
		}

		foreach ($unavailable_dates as $start => $end)
		{
			if ($end <= $period_start)
			{
				unset ($unavailable_dates[$start]);
			}
		}

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "unavailable_dates:";
// /* dbg */ arr ($unavailable_dates);
// /* dbg */ }

		return $unavailable_dates;
	}

	/** Calculates and returns recurrent unavailable periods effective between points in time specified by $start and $end
	@attrib api=1 params=pos
	@param start required type=int UNIX timestamp
	@param end required type=int UNIX timestamp
	@returns array
		Array of recurrent period (RP) definitions in associative array format:
		array (
			"length" => $length, // int seconds, length of RP
			"start" => $start, // int UNIX timestamp start date of first RP by this definition
			"time" => $time, // int seconds start time of first RP by this definition. Actual RP start is therefore $start+$time
			"end" => $end, // int UNIX timestamp End time of this RP's effectiveness
			"max_span" => $end + $length,
			"interval" => $interval // int seconds Interval between RP-s -- the second period by this definition starts at $start+$time+$interval
		)
	**/
	public function get_recurrent_unavailable_periods($start, $end)
	{
// /* dbg */ if ($this->id () == 6670  ) {
// /* dbg */ $this->mrpdbg=1;
// /* dbg */ }

		### unavailable recurrences
		$recurrent_unavailable_periods = array ();
		$start = mktime (0, 0, 0, date ("m", $start), date ("d", $start), date("Y", $start));
		$end = mktime (0, 0, 0, date ("m", $end), date ("d", $end), date("Y", $end));

		if ($this->prop ("unavailable_weekends"))
		{
			$weekend_start = date_calc::get_week_start($start) + (5 * 86400);
			$weekend_length = 172800;
			$recurrent_unavailable_periods[] = array (
				"length" => $weekend_length,
				"start" => $weekend_start,
				"time" => 0,
				"end" => $end,
				"max_span" => $end + $weekend_length,
				"interval" => 604800,
			);
		}

		foreach ($this->connections_from (array ("type" => "RELTYPE_RECUR")) as $connection)
		{
			$recurrence = $connection->to ();

			if ( !(($recurrence->prop ("start") > $end) or ($recurrence->prop ("end") < $start)) )
			{
				switch ($recurrence->prop ("recur_type"))
				{
					case RECUR_DAILY: //day
						$interval = $recurrence->prop ("interval_daily");
						$interval = round (($interval ? $interval : 1) * 86400);
						break;

					case RECUR_WEEKLY: //week
						$interval = $recurrence->prop ("interval_weekly");
						$interval = round (($interval ? $interval : 1) * 86400 * 7);
						break;

					case RECUR_YEARLY: //year
						$interval = $recurrence->prop ("interval_yearly");
						$interval = round (($interval ? $interval : 1) * 86400 * 365);
						break;

					default:
						continue;
				}

				$recurrence_starttime = $recurrence->prop ("time");
				$recurrence_starttime = explode (":", $recurrence_starttime);
				$recurrence_starttime_hours = $recurrence_starttime[0] ? (int) $recurrence_starttime[0] : 0;
				$recurrence_starttime_minutes = $recurrence_starttime[1] ? (int) $recurrence_starttime[1] : 0;
				$recurrence_starttime = $recurrence_starttime_hours * 3600 + $recurrence_starttime_minutes * 60;

				$recurrent_unavailable_periods[] = array (
					"length" => round (aw_math_calc::safe_settype_float ($recurrence->prop ("length")) * 3600),
					"start" => $recurrence->prop ("start"),
					"time" => $recurrence_starttime,
					"end" => $recurrence->prop ("end"),
					"max_span" => $recurrence->prop ("end") + $time + $length,
					"interval" => $interval,
				);
			}
		}


		### add workhours (available recurrences)
		$recurrent_available_periods = array ();

		foreach ($this->connections_from (array ("type" => "RELTYPE_RECUR_WRK")) as $connection)
		{
			$recurrence = $connection->to ();

			if ( !(($recurrence->prop ("start") > $end) or ($recurrence->prop ("end") < $start)) )
			{
				$interval = 86400;
				list ($recurrence_time_hours, $recurrence_time_minutes) = explode (":", $recurrence->prop ("time"), 2);
				$recurrence_time = abs ((int) $recurrence_time_hours) * 3600 + abs ((int) $recurrence_time_minutes) * 60;
				$recurrence_length = round (aw_math_calc::safe_settype_float ($recurrence->prop ("length")) * 3600);

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "recurrent_available_period time:" . $recurrence_time . "<br>";
// /* dbg */ }

				$recurrent_available_periods[] = array (
					"length" => $recurrence_length,
					"start" => $recurrence->prop ("start"),
					"time" => $recurrence_time,
					"end" => $recurrence->prop ("end"),
					"interval" => $interval,
				);
			}
		}

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "recurrent_available_periods:";
// /* dbg */ arr ($recurrent_available_periods);
// /* dbg */ }

		### transmute recurrently available periods to unavailables
		### throw away erroneous definitions
		foreach ($recurrent_available_periods as $key => $available_period)
		{
			if ( ($available_period["start"] >= $available_period["end"]) or ($available_period["length"] > 86400) or ($available_period["length"] < 1) )
			{
				unset ($recurrent_available_periods[$key]);
			}
		}

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "recurrent_available_periods after errorcheck:";
// /* dbg */ arr ($recurrent_available_periods);
// /* dbg */ exit;
// /* dbg */ }

		### find combinations of available periods
		$combination_breakpoints = array ($start, $end);

		foreach ($recurrent_available_periods as $available_period)
		{
			if (($available_period["start"] > $start) and ($available_period["start"] < $end))
			{
				$combination_breakpoints[] = $available_period["start"];
			}

			if (($available_period["end"] > $start) and ($available_period["end"] < $end))
			{
				$combination_breakpoints[] = $available_period["end"];
			}
		}

		### make unavailable recurrence definitions according to these combinations
		usort ($recurrent_available_periods, array ($this, "sort_recurrences_by_start"));
		sort ($combination_breakpoints, SORT_NUMERIC);
		$interval = 86400;

		foreach ($combination_breakpoints as $bp_key => $breakpoint)
		{
			if (isset ($combination_breakpoints[$bp_key + 1]))
			{
				$combination_start = $breakpoint;
				$combination_end = $combination_breakpoints[$bp_key + 1];
				$combination = array ();

				foreach ($recurrent_available_periods as $available_period)
				{
					if ( ($available_period["start"] <= $combination_start) and ($available_period["end"] >= $combination_end) )
					{
						$combination[] = $available_period;
					}
				}

				usort ($combination, array ($this, "sort_recurrences_by_time"));

				foreach ($combination as $key => $available_period)
				{
					$time = ($available_period["time"] + $available_period["length"]) % $interval;

					if (isset ($combination[$key + 1]))
					{
						$end_time = $combination[$key + 1]["time"];
					}
					else
					{
						$end_time = $combination[0]["time"];
					}

					if ($end_time > $time)
					{
						$length = $end_time - $time;
					}
					else
					{
						$length = $end_time + ($interval - $time);
					}

					$recurrent_unavailable_periods[] = array (
						"length" => $length,
						"start" => $combination_start,
						"end" => $combination_end,
						"time" => $time,
						"max_span" => $recurrence->prop ("end") + $time + $length,
						"interval" => $interval,
					);
				}
			}
		}

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "return recurrent_unavailable_periods:";
// /* dbg */ foreach ($recurrent_unavailable_periods as $key => $value){ echo "<hr>";
// /* dbg */ echo "length: " . ($value["length"]/3600).  "h<br>";
// /* dbg */ echo "start: " . date (MRP_DATE_FORMAT, $value["start"]).  "<br>";
// /* dbg */ echo "end: " . date (MRP_DATE_FORMAT, $value["end"]).  "<br>";
// /* dbg */ echo "time: " . date ("H.i", mktime(0,0,0,1,1,2005) + $value["time"]).  "<br>";
// /* dbg */ echo "interval: " . ($value["interval"]/3600).  "h<br>";}
// /* dbg */ }

		return $recurrent_unavailable_periods;
	}

	protected static function sort_recurrences_by_start ($recurrence1, $recurrence2)
	{
		if ($recurrence1["start"] > $recurrence2["start"])
		{
			$result = 1;
		}
		elseif ($recurrence1["start"] < $recurrence2["start"])
		{
			$result = -1;
		}
		else
		{
			$result = 0;
		}

		return $result;
	}

	protected static function sort_recurrences_by_time ($recurrence1, $recurrence2)
	{
		if ($recurrence1["time"] > $recurrence2["time"])
		{
			$result = 1;
		}
		elseif ($recurrence1["time"] < $recurrence2["time"])
		{
			$result = -1;
		}
		else
		{
			$result = 0;
		}

		return $result;
	}
}

?>
