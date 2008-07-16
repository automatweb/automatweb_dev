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

	function prop($k)
	{
		if(($k == "location" || substr($k, 0, 9) == "location.") && !is_oid(parent::prop("location")) && $this->can("view", parent::prop("event")))
		{
			// If the location is always the same, there's no need to copy it into event_time object.
			return obj(parent::prop("event"))->prop($k);
		}

		return parent::prop($k);
	}
}

?>
