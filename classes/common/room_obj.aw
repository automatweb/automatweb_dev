<?php
 
class room_obj extends _int_object
{
	/** Returns the color for the given setting, based on the current settings
		@attrib api=1 params=pos

		@param var required type=string
			The setting to return the value for. 

	**/
	function get_color($var)
	{
		$default = null;
		switch($var)
		{
			case "available":
				$default = "#E1E1E1";
			default:
				if($color = $this->get_setting("col_".$var))
				{
					return "#".$color;
				}
				else
				{
					return $default;
				}
		}
	}

	/** Returns the current active settings for the room
		@attrib api=1 

		@returns
			The cl_room_settings object active for the current user or null if none found
	**/
	function get_settings()
	{
		enter_function("room::get_settings_for_room");
		$si = get_instance(CL_ROOM_SETTINGS);
		$rv = $si->get_current_settings($this);
		exit_function("room::get_settings_for_room");
		return $rv;
	}

	/** Returns a setting from the current active room settings
		@attrib api=1 params=pos

		@param setting required type=string
			A setting property name from the room_settings class

		@returns
			The value for the setting in the currently active settings or "" if no settings are active
	**/
	function get_setting($setting)
	{
		if(!is_object($this->settings))
		{
			$this->settings = $this->get_settings();
		}
		if(!is_object($this->settings))
		{
			return "";
		}
		if(!$this->settings->is_property($setting))
		{
			return "";
		}
		return $this->settings->prop($setting);
	}

	/** Returns the current workers for the room
		@attrib api=1 
		@returns
			array(person id => person name)
	**/
	function get_all_workers()
	{
		$pro = array();
		if(is_array($this->prop("professions")))
		{
			$pro = $this->prop("professions");
		}

		$ol2 = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"lang_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_RANK" => $pro,
					"oid" => $pro,
				)
			)),
		));
		return $ol2->names();
	}

	/** Returns the current sellers for the room
		@attrib api=1 
		@returns
			array(person id => person name)
	**/
	function get_all_sellers()
	{
		$pro = array();
		if(is_array($this->prop("seller_professions")))
		{
			$pro = $this->prop("seller_professions");
		}

		$ol2 = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"lang_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_RANK" => $pro,
					"oid" => $pro,
				)
			)),
		));
		return $ol2->names();
	}

	/** extends person current work graph
		@attrib api=1 params=name
		@param person required type=oid
			person id
		@param start required
			person id
		@param end required
			person id
		@returns boolean
			true if success
	**/
	function extend_work_graph($arr)
	{
		$start = date_edit::get_timestamp($arr["start"]);
		$end = date_edit::get_timestamp($arr["end"]);
		$person = $arr["person"];

		$last_reservation = $this->get_last_reservation();
		if(is_oid($last_reservation))
		{
			$r = obj($last_reservation);
			$last_end = $r->prop("end");
			$patterns = $this->get_reservations(array(
				"start" => $last_end - 7*24*3600 ,
				 "end" => $last_end
			));
			$p = array();
			foreach($patterns->arr() as $pat)
			{
				$p[date("N" , $pat->prop("start1"))][] = array(
					"start" => array(
						"hour" => date("H" , $pat->prop("start1")),
						"minute" => date("i" , $pat->prop("start1")),
						"second" => date("s" , $pat->prop("start1")),
					),
					"end" => array(
						"hour" => date("H" , $pat->prop("end")),
						"minute" => date("i" , $pat->prop("end")),
						"second" => date("s" , $pat->prop("end")),
					),
				);
			}
			while($start < $end)
			{
				$ex_res = $this->get_reservations(array(
					"start" => get_day_start($start),
					"end" => (get_day_start($start) + 24*3600),
				));
				if(!sizeof($ex_res->ids()))
				{
					foreach($p[date("N" , $start)] as $data)
					{
/*						$res = $this->add_reservation(array(
							"start" => 
							"end" => 
						));


						if(is_oid($res))
						{
							$reservation = obj($res);
							$reservation->set_prop() /// siia inimene kylge panna
							$reservation->save()
						}

*/
						print "broneering ".date("d.m.Y" , $start)." kell: ".$data["start"]["hour"].":".$data["start"]["minute"].":".$data["start"]["second"]." kuni ".$data["end"]["hour"].":".$data["end"]["minute"].":".$data["end"]["second"]."<br>";
					}
				}
				$start = $start + 24*3600;
			}
		}
		die();
	}

	/** returns room last reservation oid
		@attrib api=1
		@returns oid
	**/
	function get_last_reservation()
	{
		$ol = new object_list(array(
			"class_id" => CL_RESERVATION,
			"lang_id" => array(),
			"resource" => $this->id(),
			"limit" => 1,
			"sort_by" => "planner.end DESC",
			"end" => new obj_predicate_compare(OBJ_COMP_GREATER, 0),
		));
		
		return reset($ol->ids());
	}

	/** returns reservation list
		@attrib api=1
		@param start optional type=int
			start timestamp
		@param end required type=int
			end timestamp
		@param worker optional
			person id
		@returns object list
	**/
	function get_reservations($arr = array())
	{
		$filter = array(
			"class_id" => CL_RESERVATION,
			"lang_id" => array(),
			"resource" => $this->id(),
		);
		if($arr["start"])
		{
			$filter["end"] = new obj_predicate_compare(OBJ_COMP_GREATER, $arr["start"]);
		}
		if($arr["end"])
		{
			$filter["start1"] = new obj_predicate_compare(OBJ_COMP_LESS, $arr["end"]);
		}
		if($arr["end"] || $arr["start"])
		{
			$filter["sort_by"] = "planner.end DESC"; // kui seda planneri tabelit sisse ei loeta, siis ei hakka jamama
		}
		if($arr["worker"])
		{
			$filter["people"] = $arr["worker"];
		}
		$ol = new object_list($filter);
		return $ol;
	}

	/** returns one day reservation list
		@attrib api=1 params=pos
		@param time optional type=int
			timestamp
		@returns object list
	**/
	function get_day_reservations($time)
	{
		$arr = array();
		$arr["start"] = mktime(0, 0, 0, date("m" , $time), date("d" , $time), date("Y" , $time));
		$arr["end"] = mktime(0, 0, 0, date("m" , $time), (date("d" , $time)+1), date("Y" , $time));
		return $this->get_reservations($arr);
	}

	/** returns one day reservation sum
		@attrib api=1 params=pos
		@param time optional type=int
			start timestamp
		@returns array
			summ in different currencys
	**/
	function get_day_sum($time)
	{
		$reserv = $this->get_day_reservations($time);
		$sum = array();
		foreach($reserv->arr() as $r)
		{
			$rs = $r->get_sum();
			foreach($rs as $key => $val)
			{
				$sum[$key]+=$val;
			}
		}
		return $sum;
	}


	function get_person_day_sum($time,$worker)
	{
		$arr = array();
		$arr["start"] = mktime(0, 0, 0, date("m" , $time), date("d" , $time), date("Y" , $time));
		$arr["end"] = mktime(0, 0, 0, date("m" , $time), (date("d" , $time)+1), date("Y" , $time));
		$arr["worker"] = $worker;
 		$reserv = $this->get_reservations($arr);
		$sum = array();
		foreach($reserv->arr() as $r)
		{
			$rs = $r->get_sum();
			foreach($rs as $key => $val)
			{
				$sum[$key]+=$val;
			}
		}
		return $sum;
	}

	/** adds reservation
		@attrib api=1
		@param start required type=int
			start timestamp
		@param end required type=int
			end timestamp
		@returns oid
			reservation oid
	**/
	function add_reservation($arr = array())
	{
		$start = (int)$arr["start"];
		$end = (int)$arr["end"];
		if(!$this->is_available(array(
			"start" => $start,
			"end" => $end,
		)))
		{
			return "";
		}
		if(is_object($this->get_first_obj_by_reltype("RELTYPE_CALENDAR")))
		{
			$cal_obj = $this->get_first_obj_by_reltype("RELTYPE_CALENDAR");
			$cal = $cal_obj->id();
			$parent = $cal_obj->prop("event_folder");
			$step = $room->prop("time_step");
			if (!$parent)
			{
				$parent = $cal_obj->id();
			}
		}
		else
		{
			$parent = $this->id();
		}
		$reservation = new object();
		$reservation->set_class_id(CL_RESERVATION);
		$reservation->set_name($this->name()." bron ".date("d:m:Y" ,$start));
		$reservation->set_parent($parent);
		$reservation->set_prop("deadline", (time() + 15*60));
		$reservation->set_prop("resource" , $this->id());
		$reservation->save();
		return $reservation->id();
	}

	//selle funktsionaalsuse peaks kunagi siia sisse t6stma
	/** checks if the room is available 
		@attrib params=name api=1
		@param room required type=oid
			room id
		@param start required type=int
		@param end required type=int
		@param ignore_booking optional type=int
			If given, the booking with this id will be ignored in the checking - this can be used for changing booking times for instance
		@return boolean
			true if available
			false if not available
	**/
	function is_available($arr)
	{
		if($this->prop("allow_multiple"))
		{
			return true;
		}
		$arr["room"] = $this->id();
		$room_inst = get_instance(CL_ROOM);
		return $room_inst->check_if_available($arr);
	}

}

?>
