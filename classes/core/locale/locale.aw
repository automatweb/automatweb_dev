<?php

/** all localization functions are grouped here **/
class locale
{
	private $default_locale = "en";
	private $lc_date_inst = false;

	function locale()
	{
		$this->lc_date_inst = @get_instance("core/locale/".(aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC"))."/date", array(), false);

		if(!is_object($this->lc_date_inst))
		{
			$this->lc_date_inst = get_instance("core/locale/" . ($this->default_locale ? $this->default_locale : "en") . "/date");
		}
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
	function get_lc_weekday($num, $short = false, $ucfirst = false)
	{
		static $lc_date_inst;
		$lc_date_inst = @get_instance("core/locale/".(aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC"))."/date", array(), false);
//			$lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
		if(!is_object($lc_date_inst))
		{
			$lc_date_inst = get_instance("core/locale/en/date");
		};
		if (method_exists($lc_date_inst,"get_lc_weekday"))
		{
			return $lc_date_inst->get_lc_weekday($num,$short,$ucfirst);
		}
		else
		{
			return "";
		};
	}

	/** returns the name of the month in the current language
		@attrib api=1 params=pos

		@param num required type=int
			The number of the month to return the name for

	**/
	function get_lc_month($num)
	{
		static $lc_date_inst;
		$lc_date_inst = get_instance("core/locale/".(aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC"))."/date", array(), false);

		if(!is_object($lc_date_inst))
		{
			$lc_date_inst = get_instance("core/locale/en/date");
		}

		if (method_exists($lc_date_inst,"get_lc_month"))
		{
			return $lc_date_inst->get_lc_month($num);
		}
		else
		{
			return "";
		}
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
	function get_lc_date($timestamp,$format)
	{
		static $lc_date_inst;
//			$lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/date", array(), false);
		$lc_date_inst = @get_instance("core/locale/".(aw_ini_get("user_interface.full_content_trans") ? aw_global_get("ct_lang_lc") : aw_global_get("LC"))."/date", array(), false);
		if(!is_object($lc_date_inst))
		{
			$lc_date_inst = get_instance("core/locale/en/date");
		};
		if (method_exists($lc_date_inst,"get_lc_date"))
		{
			return $lc_date_inst->get_lc_date($timestamp,$format);
		}
		else
		{
			return "";
		};
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
	function get_lc_number($number)
	{
		static $lc_date_inst;
		$lc_date_inst = @get_instance("core/locale/".aw_global_get("LC")."/number", array(), false);
		if(!is_object($lc_date_inst))
		{
			$lc_date_inst = get_instance("core/locale/en/number");
		};
		if (method_exists($lc_date_inst,"get_lc_number"))
		{
			return $lc_date_inst->get_lc_number($number);
		}
		else
		{
			return $number;
		};
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
	function get_lc_money_text($number, $currency, $lc = NULL)
	{
		if (!$lc)
		{
			$lc = aw_global_get("LC");
		}

		static $lc_date_inst;
		$lc_date_inst[$lc] = @get_instance("core/locale/".$lc."/number", array(), false);
		if(!is_object($lc_date_inst[$lc]))
		{
			$lc_date_inst[$lc] = get_instance("core/locale/" .$lc. "/number");
		};
		if (method_exists($lc_date_inst[$lc],"get_lc_money_text"))
		{
			return $lc_date_inst[$lc]->get_lc_money_text($number, $currency);
		}
		else
		{
			return $number;
		}
	}
}

?>
