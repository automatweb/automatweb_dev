<?php
class date
{
	function date()
	{
		$this->month = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");

	}

	function get_lc_date($timestamp, $format)
	{
		if ($timestamp==0)
		{
			$timestamp=time();
		}
		
		switch ($format)
		{
			case 1:
				$newdate=date("d.m.y", $timestamp);
				return $newdate;
			case 2:
			
				$newdate=date("d.m.Y", $timestamp);
				return $newdate;
				
			case 3:
				$newdate=date("d. ", $timestamp).$this->month[date("m", $timestamp)-1].date(" y",$timestamp);
				return $newdate;
				
			case 4:
				$newdate=date("d. ", $timestamp).$this->month[date("m", $timestamp)-1].date(" Y",$timestamp);
				return $newdate;
		}
	}
	
	function get_lc_weekday($num, $short = false)
	{
		$names = array("Monday","Tueday","Wednesday","Thursday","Friday","Saturday","Sunday");
		$num--;
		return $short ? substr($names[$num],0,3) : $names[$num];
	}

	function get_lc_month($num)
	{
		return ucfirst($this->month[$num-1]);
	}
	
	
}
?>
