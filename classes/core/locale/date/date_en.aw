<?php
/*
@classinfo  maintainer=voldemar
*/
class awlc_date_en implements awlc_date
{
	protected static $month = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

	public static function get_lc_date($timestamp, $format)
	{
		switch ($format)
		{
			case locale::DATE_SHORT:
				return date("m.d.y", $timestamp);

			case locale::DATE_SHORT_FULLYEAR:
				return date("m.d.Y", $timestamp);

			case locale::DATE_LONG:
				return date("d ", $timestamp).self::$month[date("m", $timestamp)-1].date(" y",$timestamp);

			case locale::DATE_LONG_FULLYEAR:
				return date("d ", $timestamp).self::$month[date("m", $timestamp)-1].date(" Y",$timestamp);

			case 5:
				return ucfirst(self::$month[date("m",$timestamp)-1]) . " " . date("d",$timestamp);

			case 6:
				return ucfirst(self::$month[date("m",$timestamp)-1]) . " " . date("d",$timestamp) . date(" Y",$timestamp);

			case 7:
				return date("H:i d.m.y", $timestamp);

			case locale::DATETIME_SHORT:
				return date("m.d.y g:i a", $timestamp);

			case locale::DATETIME_SHORT_FULLYEAR:
				return date("m.d.Y g:i a", $timestamp);

			case locale::DATETIME_LONG:
				return date("d ", $timestamp).self::$month[date("m", $timestamp)-1].date(" y g:i a",$timestamp);

			case locale::DATETIME_LONG_FULLYEAR:
				return date("d ", $timestamp).self::$month[date("m", $timestamp)-1].date(" Y g:i a",$timestamp);
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
