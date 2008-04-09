<?php

class event_time_obj extends _int_object
{
	function set_prop($name,$value)
	{
		switch($name)
		{
			case "event":
				if($value)
				{
					$ev = obj($value);
					$ev->connect(array("to" => $this->id(), "reltype" => "RELTYPE_EVENT_TIME"));
				}
		}
		parent::set_prop($name,$value);
		switch($name)
		{
			case "start":
			case "end":
				if($this->prop("event"))
				{
					$event = obj($this->prop("event"));
					$event->set_start_end();
				}
		}
	}
}

?>
