<?php
class date
{
	function get_lc_date($timestamp, $format)
	{
		if ($timestamp==0)
		{
			$timestamp=time();
		}
		
		$month = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");
		
		
		switch ($format)
		{
			case 1:
				$newdate=date("d.m.y", $timestamp);
				return $newdate;
			case 2:
			
				$newdate=date("d.m.Y", $timestamp);
				return $newdate;
				
			case 3:
				$newdate=date("d. ", $timestamp).$month[date("m", $timestamp)-1].date(" y",$timestamp);
				return $newdate;
				
			case 4:
				$newdate=date("d. ", $timestamp).$month[date("m", $timestamp)-1].date(" Y",$timestamp);
				return $newdate;
		}
	}
	
	
}
?>