<?php

class crm_working_time_scenario_obj extends _int_object
{
	function set_prop($name,$value)
	{
		parent::set_prop($name,$value);
	}

	function set_weekdays($data)
	{
		$this->set_meta("weekdays" , $data);
	}

	function set_free_times($data)
	{
		$this->set_meta("free_times" , $data);
	}

	function get_weekdays()
	{
		return $this->meta("weekdays");
	}
	
	function get_free_times()
	{
		return $this->meta("free_times");
	}

	function set_time($data)
	{
		$this->set_meta("time" , $data);
	}
	
	function get_time()
	{
		return $this->meta("time");
	}

	function get_scenario_data()
	{
		return $this->meta("scenario_data");
	}
	
	function set_scenario_data($data)
	{
		$this->set_meta("scenario_data" , $data);
	}

	function get_date_options($d)
	{
		$ret = "";
		$sd = $this->get_scenario_data();
		$weekday = date("w" , $d);
		$weekday--;
		if($weekday < 0)
		{
			$weekday = 6;
		}
		//arr(date("w" , $d));
		foreach($sd[$weekday] as $opt)
		{
			$time = mktime($opt["start"]["hour"],$opt["start"]["minute"],0,date("m",$d),date("d",$d),date("Y",$d));
			$ret.= html::checkbox(array("name" => "bron_times[".$time."][accept]" , "checked" => 1));
			$ret.= "";
			$ret.= html::time_select(array("name" => "bron_times[".$time."][start]" , "value" => $opt["start"]));
			$ret.= "-";
			$ret.= html::time_select(array("name" => "bron_times[".$time."][end]" , "value" => $opt["end"]));
			$ret.= "\n<br>";
		}
		if($ret)
		{
			return "<table WIDTH=240>".$ret."</table>";
		}

		return $ret;
	}
}

?>
