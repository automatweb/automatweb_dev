<?php

/**
@classinfo  maintainer=voldemar
Localisation utilities class.
**/
class locale
{
	protected static $lc_data = array( // locale data by locale code
		"de" => array(),
		"en" => array(),
		"es" => array(),
		"et" => array(),
		"fi" => array(),
		"fr" => array(),
		"lt" => array(),
		"lv" => array(),
		"ru" => array()
	);

	private static $default_locale = "en";
	private static $current_locale = false;

	public function locale()
	{
	}

	/** returns the name of the weekday in the current language
		@attrib api=1 params=pos

		@param num required type=int
			The number of the weekday to return.

		@param short optional type=bool
			If true, the short name of the weekday is returned. Defaults to false

		@param ucfirst optional type=bool
			If true, the first characters are uppercased. ddefaults to false

		@comment
			The number of the weekday is 0-7 inclusive. 0 and 7 both are for sunday.
	**/
	public static function get_lc_weekday($num, $short = false, $ucfirst = false)
	{
		$lc = self::get_lc();
		$method = array("awlc_date_{$lc}", "get_lc_weekday");
		$weekday = "";
		if (is_readable(AW_DIR . "classes/core/locale/date/date_{$lc}" . AW_FILE_EXT))
		{
			$weekday = call_user_func($method, $num, $short = false, $ucfirst = false);
		}
		return $weekday;
	}

	/** returns the name of the month in the current language
		@attrib api=1 params=pos

		@param num required type=int
			The number of the month to return the name for

	**/
	public static function get_lc_month($num)
	{
		$lc = self::get_lc();
		$method = array("awlc_date_{$lc}", "get_lc_month");
		$month = "";
		if (is_readable(AW_DIR . "classes/core/locale/date/date_{$lc}" . AW_FILE_EXT))
		{
			$month = call_user_func($method, $num);
		}
		return $month;
	}

	/** returns a localized date in the current language
		@attrib api=1 params=pos

		@param timestamp required type=int
			The unix timestamp to return the date for

		@param format required type=int
			One of the defined date formats

		@comment
			The date formats are:
				LC_DATE_FORMAT_SHORT = For example: 20.06.88 or 05.12.98
				LC_DATE_FORMAT_SHORT_FULLYEAR = For example: 20.06.1999 or 05.12.1998
				LC_DATE_FORMAT_LONG = For example: 20. juuni 99
				LC_DATE_FORMAT_LONG_FULLYEAR = For example: 20. juuni 1999
	**/
	public static function get_lc_date($timestamp, $format)
	{
		$lc = self::get_lc();
		$method = array("awlc_date_{$lc}", "get_lc_date");
		$date = "";
		if (is_readable(AW_DIR . "classes/core/locale/date/date_{$lc}" . AW_FILE_EXT))
		{
			$date = call_user_func($method, $timestamp, $format);
		}
		return $date;
	}

	/** returns a readable string for the number given
		@attrib api=1 params=pos

		@param number required type=int
			The number to stringify

		@returns
			the text version of the number.

		@examples
			if the language is english, then
				locale::get_lc_number(7);
			returns "seven"
	**/
	public static function get_lc_number($number)
	{
		$lc = self::get_lc();
		$method = array("awlc_number_{$lc}", "get_lc_number");
		if (is_readable(AW_DIR . "classes/core/locale/number/number_{$lc}" . AW_FILE_EXT))
		{
			$number = call_user_func($method, $number);
		}
		return $number;
	}

	/** returns the given amount of money as text with the currency name n the right place
		@attrib api=1 params=pos

		@param number required type=double
			The sum to stringify

		@param currency required type=object
			The currency object to use for the sum.

		@param lc optional type=string
			The locale code, defaults to the current one

		@comment
			Does the same, as get_lc_number, but appends/prepends the currency name and unit names as needed. Used for writing the amount on bills as text.
	**/
	public static function get_lc_money_text($number, $currency, $lc = NULL)
	{
		if (!self::is_valid_lc_code($lc))
		{
			$lc = self::get_lc();
		}

		$method = array("awlc_number_{$lc}", "get_lc_money_text");
		$lc = self::get_lc();
		if (is_readable(AW_DIR . "classes/core/locale/number/number_{$lc}" . AW_FILE_EXT))
		{
			$number = call_user_func($method, $number, $currency);
		}
		return $number;
	}

	/** returns genitive case of a proper name
		@attrib api=1 params=pos

		@param name required type=string

		@param lc optional type=string
			The locale code, defaults to the current one
	**/
	public static function get_genitive_for_name($name, $lc = NULL)
	{
		settype($name, "string");

		if (!self::is_valid_lc_code($lc))
		{
			$lc = self::get_lc();
		}

		$method = array("awlc_cases_{$lc}", "get_genitive_for_name");
		if (is_readable(AW_DIR . "classes/core/locale/cases/cases_{$lc}" . AW_FILE_EXT))
		{
			$name = call_user_func($method, $name);
		}
		return $name;
	}

	/** checks if lc code is valid
		@attrib api=1 params=pos
		@param code required type=string
		@returns bool
	**/
	public static function is_valid_lc_code($code)
	{
		return isset(self::$lc_data[(string) $code]);
	}

	private static function get_lc()
	{
		if (false === self::$current_locale)
		{
			$lc = aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC");
			if (!self::is_valid_lc_code($lc))
			{
				$lc = self::$default_locale;
			}
			self::$current_locale = $lc;
		}
		return self::$current_locale;
	}
}

/** Generic locale class error **/
class awex_locale extends aw_exception {}


interface awlc_date
{
	public static function get_lc_month($num);
	public static function get_lc_date($timestamp, $format);
	public static function get_lc_weekday($num, $short = false, $ucfirst = false);
}

interface awlc_number
{
	public static function get_lc_number($number);
	public static function get_lc_money_text($number, $currency);
}

interface awlc_cases
{
	public static function get_genitive_for_name($name);
}

?>
