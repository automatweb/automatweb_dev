<?php
class date
{
	function date()
	{
		$this->month = array("jaanuar", "veebruar", "m&auml;rts", "aprill", "mai", "juuni", "juuli", "august", "september", "oktoober", "november", "detsember");
	}

	function get_lc_date($timestamp, $format)
	{
		if (empty($timestamp))
		{
			$timestamp = time();
		}

		$rv = "";
		
		switch ($format)
		{
			case 1:
				$rv = date("j.m.y", $timestamp);
				break;

			case 2:
				$rv = date("j.m.Y", $timestamp);
				break;
				
			case 3:
				$rv = date("j. ", $timestamp).$this->month[date("m", $timestamp)-1].date(" y",$timestamp);
				break;
				
			case 4:
				$rv = date("j. ", $timestamp).$this->month[date("m", $timestamp)-1].date(" Y",$timestamp);
				break;

			case 5:
				$rv = date("j. ",$timestamp).$this->month[date("m",$timestamp)-1];
				break;
			
			case 6:
				$rv = date("j. ",$timestamp).$this->month[date("m",$timestamp)-1] . date(" Y",$timestamp);
				break;
			case 7:
				$rv = date("H:i j.m.Y", $timestamp);
				break;
				


		}

		return $rv;
	}

	function get_lc_weekday($num, $short = false)
	{
		$names = array("esmaspäev","teisipäev","kolmapäev","neljapäev","reede","laupäev","pühapäev");
		// array starts from 0, estonian weekdays from 1
		$num--;
		return $short ? substr($names[$num],0,1) : $names[$num];
	}
	
	function get_lc_month($num)
	{
		return $this->month[$num-1];
	}
	
	
}
?>
