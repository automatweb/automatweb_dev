<?php

/*
@classinfo maintainer=voldemar
*/

class datepicker extends core implements vcl_interface
{
	function __construct()
	{
		$this->init("");
	}

	/**
		@attrib params=name api=1

		@param name required type=string
			String to indetify the object picker

		@param textsize type=string default=null
			Textbox text size. CSS font size expression (e.g. '11px').

		@param from type=int default=0
			Unix timestamp. Time to allow selecting from

		@param to type=int default=null
			Time in seconds. Time to allow selecting until. Default is not limited

		@param disabled type=bool default=false
			Element is disabled/not disabled

		@returns string
			The HTML of the date picker.
	**/
	public function create($arr)
	{
		if (empty($arr["name"]) or !is_string($arr["name"]))
		{
			throw new awex_datepicker_param("Name is required and must be a string.");
		}

		load_javascript("jquery/plugins/datepick/jquery.datepick.min.js");
		load_javascript("jquery/plugins/datepick/jquery.datepick-et.js");
		load_javascript("jquery/plugins/ptTimeSelect/jquery.ptTimeSelect.js");
		$date_textbox = html::textbox(array(
			"name" => $arr["name"]."[date]",
			"value" => isset($arr["value"]["date"]) ? $arr["value"]["date"] : "",
			"disabled" => isset($arr["disabled"]) ? $arr["disabled"] : false,
			"size" => 10,
			"textsize" => !empty($arr["textsize"]) ? $arr["textsize"] : null
		));
		$datepicker = <<<EOS
<script type="text/javascript">
$("input[name='{$arr["name"]}[date]']").datepick();
</script>
EOS;
		$time_textbox = html::textbox(array(
			"name" => $arr["name"]."[time]",
			"value" => isset($arr["value"]["time"]) ? $arr["value"]["time"] : "",
			"disabled" => isset($arr["disabled"]) ? $arr["disabled"] : false,
			"size" => 5,
			"textsize" => !empty($arr["textsize"]) ? $arr["textsize"] : null
		));
		$timepicker = <<<EOS
<script type="text/javascript">
$("input[name='{$arr["name"]}[time]']").ptTimeSelect();
</script>
EOS;
		return $date_textbox . $datepicker . $time_textbox . $timepicker;
	}

	public function init_vcl_property($arr)
	{
		$prop = $arr["property"];
		$name = $prop["name"];
		$prop["value"] = $arr["obj_inst"]->prop($name);
		if (isset($prop["value"]) and $prop["value"] > 1)
		{
			$prop["value"] = array(
				"date" => date("d.m.Y", $prop["value"]),
				"time" => date("H:i", $prop["value"])
			);
		}
		$prop["value"] = $this->create($prop);
		return array($prop["name"] => $prop);
	}

	public function process_vcl_property(&$arr)
	{
		$prop =& $arr["prop"];
		$name = $prop["name"];
		$timestamp = self::get_timestamp($prop["value"]);

		if ($timestamp > 1)
		{
			$prop["value"] = $timestamp;
			$arr["obj_inst"]->set_prop($name, $timestamp);
		}
	}

/** Converts datepicker value to UNIX timestamp
	@attrib api=1 params=pos
	@param value type=array
		array("date" => ddmmyyyy, "time" => hh:mm)
	@returns int
**/
	public static function get_timestamp($value)
	{
		$date = $value["date"];
		$time = $value["time"];
		$day = $month = $year = $hour = $min = 0;

		if (!empty($date))
		{
			list($day, $month, $year) = explode(".", $date, 3);
		}

		if (!empty($time))
		{
			list($hour, $min) = explode(":", $time, 2);
		}

		$timestamp = $year ? mktime((int)$hour, (int)$min, 0, (int)$month, (int)$day, (int)$year) : 0;
		return $timestamp;
	}
}

class awex_datepicker extends awex_vcl {}
class awex_datepicker_param extends awex_datepicker {}

?>
