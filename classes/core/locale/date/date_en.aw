<?php
/*
@classinfo  maintainer=kristo
*/
class awlc_date_en implements awlc_date
{
	protected static $month = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

	public static function get_lc_date($timestamp, $format)
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
				$newdate=date("d ", $timestamp).self::$month[date("m", $timestamp)-1].date(" y",$timestamp);
				return $newdate;

			case 4:
				$newdate=date("d ", $timestamp).self::$month[date("m", $timestamp)-1].date(" Y",$timestamp);
				return $newdate;

			case 5:
				$rv = ucfirst(self::$month[date("m",$timestamp)-1]) . " " . date("d",$timestamp);
				return $rv;

			case 6:
				$rv = ucfirst(self::$month[date("m",$timestamp)-1]) . " " . date("d",$timestamp) . date(" Y",$timestamp);
				return $rv;
			case 7:
				$newdate=date("H:i d.m.y", $timestamp);
				return $newdate;

		}
	}

	public static function get_lc_weekday($num, $short = false, $ucfirst = true)
	{
		// date("w") returns 0 for sunday, but for historical reasons 7 should also be sunday
//		$names = array("Sunday","Monday","Tueday","Wednesday","Thursday","Friday","Saturday","Sunday");
		$names = array("sunday","monday","tuesday","wednesday","thursday","friday","saturday","sunday");
		$name = ($ucfirst) ? ucfirst($names[$num]) : $names[$num];
		return $short ? substr($name,0,3) : $name;
	}

	public static function get_lc_month($num)
	{
		return self::$month[$num-1];
	}


}
?>
