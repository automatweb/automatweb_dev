<?php
// date_calc.aw - Kuupäevaaritmeetika
// $Header: /home/cvs/automatweb_dev/classes/Attic/date_calc.aw,v 2.3 2002/11/07 10:52:19 kristo Exp $

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
		
		
		case "week":
			$next = mktime(0,0,0,$m,$d+7,$y);
			$prev = mktime(0,0,0,$m,$d-7,$y);
			$daycode = convert_wday(date("w",$timestamp));
			// aga meil siin algab nädal siiski esmaspäevast
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

		case "day":
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
