<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/Attic/date_chooser.aw,v 1.1 2004/10/16 22:34:42 duke Exp $
// date_chooser
class date_chooser extends core
{
	function date_chooser()
	{
		$this->init("");
	}

	function init_vcl_property($arr)
	{
		$prop = &$arr["prop"];
		$prop["type"] = "datetime_select";
		if ($arr["new"])
		{
			list($day,$mon,$yr,$hr,$min) = explode("-",date("d-m-Y-H-i"));
			$tstamp = time();
			if ($arr["request"]["date"])
			{
				$parts = explode("-",$arr["request"]["date"]);
				// MM-YYYY
				if (sizeof($parts) == 2)
				{
					$tstamp = mktime($hr,$min,0,$parts[0],$day,$parts[1]);
				}
				// DD-MM-YYYY
				elseif (sizeof($parts) == 3)
				{
					$tstamp = mktime($hr,$min,0,$parts[1],$parts[0],$parts[2]);


				};
					
			};
			$prop["value"] = $tstamp;
		};
		return array($prop["name"] => $prop);
	}

	function process_vcl_property($arr)
	{
		$prop = &$arr["prop"];
		load_vcl("date_edit");
		$prop["value"] = date_edit::get_timestamp($prop["value"]);
	}
};
?>
