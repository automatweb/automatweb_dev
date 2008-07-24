<?php

class calendar_event_obj extends _int_object
{
	function set_prop($name,$value)
	{
		if($name == "name")
		{
			return parent::set_prop("event_name", $value);
		}
		return parent::set_prop($name,$value);
	}

	function prop($k)
	{
		if($k == "name")
		{
			return parent::prop("event_name");
		}
		return parent::prop($k);
	}

	function name()
	{
		return parent::prop("event_name");
	}

	function add_event_time($id)
	{
		$time = obj($id);
		$time->set_prop("event" , $this->id());
		$time->save();
		//$this->connect(array("to" => $time->id(), "reltype" => "RELTYPE_EVENT_TIME"));
		$this->set_start_end();
	}

	function set_start_end()
	{
		$eventstart = 100000000000;
		$eventend = 1;
		foreach($this->connections_from(array("type" => "RELTYPE_EVENT_TIME")) as $c)
		{
			$tm = $c->to();
			if($tm->prop("start") > 1000 && ($eventstart >  $tm->prop("start"))) $eventstart = $tm->prop("start");
			if($eventend <  $tm->prop("end")) $eventend = $tm->prop("end");
		}
		if($eventend > 1)
		{
			$this->set_prop("end", $eventend);
		}
	
		if($eventstart < 100000000000)
		{
			$this->set_prop("start1", $eventstart);
		}
		$this->save();
	}

	function get_locations()
	{
		$ret = array();
		//syndmusega seotud asukohad
		foreach($this->connections_from(array(
			"type" => "RELTYPE_LOCATION",
		)) as $c)
		{
			$ret[$c->prop("to")] = $c->prop("to.name");
		}
		// + syndmusega seotud aegadega seotud asukohad
		foreach($this->connections_from(array(
			"type" => "RELTYPE_EVENT_TIMES",
		)) as $c)
		{
			$event_time = $c->to();
			foreach($event_time->connections_from(array(
				"type" => "RELTYPE_LOCATION",
			)) as $l)
			{
				$ret[$l->prop("to")] = $l->prop("to.name");
			}
		}
		return $ret;
	}

}

?>
