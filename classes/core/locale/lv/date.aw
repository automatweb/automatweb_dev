<?php
class date
{
	function get_lc_date($timestamp, $format)
	{
		if ($timestamp==0)
		{
			$timestamp=time();
		}
		
		$month = array("jaanuar", "veebruar", "m&auml;rts", "aprill", "mai", "juuni", "juuli", "august", "september", "oktoober", "november", "detsember");
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