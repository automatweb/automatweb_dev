<?php
// date_calc.aw - Kuup�evaaritmeetika
// $Header: /home/cvs/automatweb_dev/classes/Attic/date_calc.aw,v 2.11 2004/01/29 17:36:55 duke Exp $

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
			// siin on next ja prev-i arvutamine monevorra special
			// kui p�ev on suurem, kui j�rgmises kuus p�evi kokku
			// j�rgmise kuu viimase p�eva. Sama kehtib eelmise kohta
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
		
		case "3month":
			$start_ts = mktime(0,0,0,$m-1,1,$y);
			$end_ts = mktime(23,59,59,$m+1,0,$y);
			break;
		
		
		case "week":
			$next = mktime(0,0,0,$m,$d+7,$y);
			$prev = mktime(0,0,0,$m,$d-7,$y);
			$daycode = convert_wday(date("w",$timestamp));
			// aga meil siin algab n�dal siiski esmasp�evast
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

	};

	$arr = array(
		"start" => $start_ts,
		"end" => $end_ts,
		"start_wd" => convert_wday(date("w",$start_ts)),
		"end_wd" => convert_wday(date("w",$end_ts)),
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
?>
