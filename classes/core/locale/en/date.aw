<?php
class date
{
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
				$newdate=date("d. F y ", $timestamp);
				return $newdate;
				
			case 4:
				$newdate=date("d. F y", $timestamp);
				return $newdate;
		}
	}
	
	
}
?>