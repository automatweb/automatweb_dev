<?php
class date
{
	function date()
	{
		$this->month = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");

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
			
			case 5:
				$rv = ucfirst($this->month[date("m",$timestamp)-1]) . " " . date("d",$timestamp);
				return $rv;
			
			case 6:
				$rv = ucfirst($this->month[date("m",$timestamp)-1]) . " " . date("d",$timestamp) . date(" Y",$timestamp);
				return $rv;
			case 7:
				$newdate=date("H:i d.m.y", $timestamp);
				return $newdate;
				
		}
	}
	
	function get_lc_weekday($num, $short = false)
	{
		// date("w") returns 0 for sunday, but for historical reasons 7 should also be sunday
		$names = array("dimanche","lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche");
		return $short ? substr($names[$num],0,3) . "." : $names[$num];
	}

	function get_lc_month($num)
	{
		return $this->month[$num-1];
	}
	
	
}
?>
