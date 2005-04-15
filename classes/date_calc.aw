<?php
// date_calc.aw - Kuupäevaaritmeetika
// $Header: /home/cvs/automatweb_dev/classes/Attic/date_calc.aw,v 2.20 2005/04/15 09:52:03 ahti Exp $

////
// !get_date_range
function get_date_range($args = array())
{
	extract($args);
	if ($date)
	{
		list($d,$m,$y) = split("-",$date);
		// deal with 2 part url-s
		if (empty($y))
		{
			$y = $m;
			$m = $d;
			$d = 1;
		};
	}
	else
	{
		list($d,$m,$y) = split("-",date("d-m-Y",$time));
	};
		
	$timestamp = mktime(0,0,0,$m,$d,$y);
	$timestamp2 = mktime(23,59,59,$m,$d,$y);
	
	// if a range is specified then use that as the base for our calculations
	$range_start = $args["range_start"];
	if ($range_start > 0)
	{
		$timestamp = $range_start;
		list($d,$m,$y) = explode("-",date("d-m-Y",$timestamp));
	}
			
	// current = 0, backward = 1, forward = 2
	// current - start or end from/at the timestamp
	if ($args["direction"] == 0)
	{
		$rg_start = $timestamp;
		// this will be calculated from the range type
		$rg_end = 0;
	}
	elseif ($args["direction"] == 1)
	{
		// this time, this will be calculated from the range type
		$rg_start = 0;
		$rg_end = $timestamp2;
	}
	else
	{
		$rg_start = $timestamp;
		$rg_end = $timestamp2;
	};

	if (empty($type))
	{
		$type = "day";
	};

	$diff = 0;

	$eti = $args["event_time_item"];

	if (!empty($eti) && is_numeric($eti))
	{
		if ($type == "day")
		{
			$diff = $eti;
		}
		elseif ($type == "week")
		{
			$diff = $eti * 7;
		};
	}

	// if range start is 0 and we know how many days we want, then base the calculations on that
	if ($rg_start == 0)
	{
		$rg_start = $timestamp - (86400 * $diff);
	}

	if ($rg_end == 0)
	{
		$rg_end = $timestamp2 + (86400 * $diff);
	}		


	switch($type)
	{
		case "month":
			$start_ts = mktime(0,0,0,$m,1,$y);
			$end_ts = mktime(23,59,59,$m+1,0,$y);

			// special flag - fullweeks, if set we return dates from
			// the first monday of the month to the last sunday of the month

			

			
			// siin on next ja prev-i arvutamine monevorra special
			// kui päev on suurem, kui järgmises kuus päevi kokku
			// järgmise kuu viimase päeva. Sama kehtib eelmise kohta
			$next_mon = date("d",mktime(0,0,0,$m+2,0,$y));
			$prev_mon = date("d",mktime(0,0,0,$m,0,$y));
	
			if ($d > $next_mon)
			{
				$next = mktime(0,0,0,$m+1,$next_mon,$y);
			}
			else
			{
				$next = mktime(0,0,0,$m+1,$d,$y);
			};

			if ($d > $prev_mon)
			{
				$prev = mktime(0,0,0,$m-1,$prev_mon,$y);
			}
			else
			{
				$prev = mktime(0,0,0,$m-1,$d,$y);
			};
			break;

		case "year":
			$start_ts = mktime(0,0,0,1,1,$y);
			$end_ts = mktime(23,59,59,12,31,$y);
			
			$prev = mktime(0,0,0,$m,$d,$y-1);
			$next = mktime(23,59,59,$m,$d,$y+1);
			break;
		
		case "3month":
			$start_ts = mktime(0,0,0,$m-1,1,$y);
			$end_ts = mktime(23,59,59,$m+1,0,$y);
			break;
		
		
		case "week":
			$next = mktime(0,0,0,$m,$d+7,$y);
			$prev = mktime(0,0,0,$m,$d-7,$y);
			$daycode = convert_wday(date("w",$timestamp));
			// aga meil siin algab nädal siiski esmaspäevast
			$monday = $d - $daycode + 1;
			$start_ts = mktime(0,0,0,$m,$monday,$y);
			$end_ts = mktime(23,59,59,$m,$monday+6,$y);
			break;

		case "relative":
                        $next = mktime(0,0,0,0,0,0);
                        $prev = mktime(0,0,0,0,0,0);
			// if we are supposed to show future events, then set the start range to 
			// this same day
			// forward = 0, backward = 1
			if ($args["direction"] == "0")
			{
				if (!empty($args["event_time_item"]))
				{
					$d2 = $d + $args["event_time_item"];
				}
				else
				{
					$d2 = $d;
				};
				$start_ts = mktime(0,0,0,$m,$d,$y);
				$end_ts = mktime(0,0,0,$m,$d2,$y);
			}
			elseif (($args["direction"] == 1) && (isset($args["time"])) || (isset($args["date"])))
			{
				if (!empty($args["event_time_item"]))
				{
					$d2 = $d - $args["event_time_item"];
				}
				else
				{
					$d2 = $d;
				};
				$end_ts = mktime(0,0,0,$m,$d,$y);
				$start_ts = mktime(0,0,0,$m,$d2,$y-1);
			}
			else
			{
				$start_ts = mktime(0,0,0,1,1,2003);
			};

			if (empty($end_ts))
			{
				$end_ts = mktime(23,59,59,12,31,2003);
			};
			break;
		
		case "day":
			$start_ts = $rg_start;
			$end_ts = $rg_end;

			$next = $end_ts + 1;
			$prev = $start_ts - 1;
			break;

		case "relative":
			$next = mktime(0,0,0,0,0,0);
			$prev = mktime(0,0,0,0,0,0);
			$start_ts = mktime(0,0,0,1,1,2003);
			$end_ts = mktime(23,59,59,12,31,2003);
			break;

		case "last_events":
			$start_ts = $rg_start;
			$end_ts = time()+24*3600*100; // far enough methinks

			$next = $end_ts + 1;
			$prev = $start_ts - 1;
			break;
	};

	$start_wd = convert_wday(date("w",$start_ts));
	$end_wd = convert_wday(date("w",$end_ts));

	if ($args["fullweeks"] == 1)
	{
		if ($start_wd > 1)
		{
			$tambov = $start_wd - 1;
			$start_ts = $start_ts - ($tambov * 86400);
		};

		if ($end_wd < 7)
		{
			$tambov = 7 - $end_wd;
			$end_ts = $end_ts + ($tambov * 86400);
		};

	};

	$arr = array(
		"start" => $start_ts,
		"end" => $end_ts,
		"start_wd" => $start_wd,
		"end_wd" => $end_wd,
		"m" => $m,
		"y" => $y,
		"wd" => convert_wday(date("w",$timestamp)),
		"prev" => date("d-m-Y",$prev),
		"next" => date("d-m-Y",$next),
		"timestamp" => $timestamp,
	);
	return $arr;
}	

////
// !This is the place we need to modify to support countries where
// the week starts on sunday.
function convert_wday($daycode)
{
	return ($daycode == 0) ? 7 : $daycode;
}
	
////
// Takes 2 timestamps and calculates the difference between them in days
//	args: time1, time2
function get_day_diff($time1,$time2)
{
	$diff = $time2 - $time1;
	$days = (int)($diff / 86400);
	return $days;
}

/** returns the timestamp for 00:00 on the last monday
**/
function get_week_start()
{
	$wd_lut = array(0 => 6, 1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 5);
	$wday = $wd_lut[date("w")];

	return mktime(0,0,0, date("m"), date("d")-$wday, date("Y"));
}

/** returns the timestamp for 00:00 on the 1st of the current month
**/
function get_month_start()
{
	return mktime(0,0,0, date("m"), 1, date("Y"));
}

/** returns the timestamp for 00:00 today
**/
function get_day_start($tm = NULL)
{
	if ($tm === NULL)
	{
		$tm = time();
	}
	return mktime(0,0,0, date("m",$tm), date("d",$tm), date("Y",$tm));
}

/** returns true if the given timespans ($a_from, $a_to) - ($b_from - $b_to) overlap
**/
function timespans_overlap($a_from, $a_to, $b_from, $b_to)
{
	// test for NOT overlapping, that's simpler. 
	// two options here: completely before or completely after
	if ($a_to <= $b_from)
	{
		return false;
	}
	if ($a_from >= $b_to)
	{
		return false;
	}
	return true;
}
?>
