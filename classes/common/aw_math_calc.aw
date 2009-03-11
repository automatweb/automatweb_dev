<?php

class aw_math_calc
{
	public static function safe_settype_float($string_float)
	{
		$separators = ".,";
		$int = (int) preg_replace ("/\s*/S", "", strtok ($value, $separators));
		$dec = preg_replace ("/\s*/S", "", strtok ($separators));
		return (float) ("{$int}.{$dec}");
	}
}

?>
