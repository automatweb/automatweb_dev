<?php
class date
{
	function date()
	{
		$this->month = array("tammikuu", "helmikuu", "maaliskuu", "huhtikuu", "toukokuu", "kesäkuu", "heinäkuu", "elokuu", "syyskuu", "lokakuu", "marraskuu", "joulukuu");

	}


	function get_lc_date($timestamp, $format)
	{
		if ($timestamp==0)
		{
			$timestamp=time();
		}

		$mname = $this->month[date("m", $timestamp)-1] . "ta";
		
		switch ($format)
		
		{
			case 1:
				$newdate=date("d.m.y", $timestamp);
				return $newdate;
			case 2:
			
				$newdate=date("d.m.Y", $timestamp);
				return $newdate;
				
			case 3:
				$newdate=date("d. ", $timestamp).$mname.date(" y",$timestamp);
				return $newdate;
				
			case 4:
				$newdate=date("d. ", $timestamp).$mname.date(" Y",$timestamp);
				return $newdate;
			
			case 5:
				$rv = date("d. ",$timestamp).$mname;
				return $rv;
		}
	}

	function get_lc_weekday($num, $short = false)
	{
		$names = array("maanantai","tiistai","keskiviikko","torstai","perjantai","lauantai","sunnuntai");
		$num--;
		return $short ? substr($names[$num],0,2) : $names[$num];
	}
	
	function get_lc_month($num)
	{
		return $this->month[$num-1];
	}
	
	
}
?>
