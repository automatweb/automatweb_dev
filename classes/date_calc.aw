<?php
// date_calc.aw - Kuup�evaaritmeetika
// $Header: /home/cvs/automatweb_dev/classes/Attic/date_calc.aw,v 2.4 2003/05/29 15:38:52 duke Exp $

////
// !get_date_range
function get_date_range($args = array())
{
	extract($args);
	if ($date)
	{
		list($d,$m,$y) = split("-",$date);
	}
	else
	{
		list($d,$m,$y) = split("-",date("d-m-Y",$time));
	};
		
	$timestamp = mktime(0,0,0,$m,$d,$y);

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
		
		
		case "week":
			$next = mktime(0,0,0,$m,$d+7,$y);
			$prev = mktime(0,0,0,$m,$d-7,$y);
			$daycode = convert_wday(date("w",$timestamp));
			// aga meil siin algab n�dal siiski esmasp�evast
			$monday = $d - $daycode + 1;
			$start_ts = mktime(0,0,0,$m,$monday,$y);
			$end_ts = mktime(23,59,59,$m,$monday+6,$y);
			break;

		case "overview":
			$next = mktime(0,0,0,$m,$d+7,$y);
			$prev = mktime(0,0,0,$m,$d-7,$y);
			$start_ts = $timestamp;
			$end_ts = mktime(23,59,59,$m,$d+6,$y);
			break;


		case "relative":
                        $next = mktime(0,0,0,0,0,0);
                        $prev = mktime(0,0,0,0,0,0);
			// if we are supposed to show future events, then set the start range to 
			// this same day
			if ($args["direction"] === "0")
			{
				$start_ts = mktime(0,0,0,$m,$d,$y);
			}
			else
			{
                        	$start_ts = mktime(0,0,0,1,1,2003);
			};
			global $XX1;
			if ($XX1)
			{
				var_dump($start_ts);
			};
                        $end_ts = mktime(23,59,59,12,31,2003);
                        break;
		
		case "day":
		default:
			$next = mktime(0,0,0,$m,$d+1,$y);
			$prev = mktime(0,0,0,$m,$d-1,$y);
			$start_ts = $timestamp;
			$end_ts = mktime(23,59,59,$m,$d,$y);
			break;

	};

	$arr = array(
		"start" => $start_ts,
		"end" => $end_ts,
		"start_wd" => convert_wday(date("w",$start_ts)),
		"end_wd" => convert_wday(date("w",$end_ts)),
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
