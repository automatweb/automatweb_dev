<?php
class date
{
	function date()
	{
		$this->month = array("tammikuu", "helmikuu", "maaliskuu", "huhtikuu", "toukokuu", "kes�kuu", "hein�kuu", "elokuu", "syyskuu", "lokakuu", "marraskuu", "joulukuu");

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
		$names = array("Maanantai","Tiistai","Keskiviikko","Torstai","Perjantai","Lauantai","Sunnuntai");
		$num--;
		return $short ? substr($names[$num],0,2) : $names[$num];
	}
	
	function get_lc_month($num)
	{
		return ucfirst($this->month[$num-1]);
	}
	
	
}
?>
