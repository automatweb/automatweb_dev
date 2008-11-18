<?php

// maintainer=markop 
class task_object extends _int_object
{
	function task_object()
	{
		parent::_int_object();
	}

	function set_prop($pn, $pv)
	{
		switch($pn)
		{
			case "time_real":
				if($this->class_id() == CL_TASK)
				{
					$pn = "num_hrs_real";
				}
			case "num_hrs_real":
				if($GLOBALS["do_not_change_task_real_time"])
				{
					return "";
				}
				break;
			case "time_to_cust":
				if($this->class_id() == CL_TASK)
				{
					$pn = "num_hrs_to_cust";
				}
			case "num_hrs_to_cust":
				if($GLOBALS["do_not_change_task_cust_time"])
				{
					return "";
				}
				break;
		}

		$ret =  parent::set_prop($pn, $pv);
		return $ret;
	}

	//millegi p2rast m6nes olukorras on vendade kustutamisega probleeme ja annab errorit... seega teeb selle enne 2ra
	function delete($arr = array())
	{
		list($tmp) = $GLOBALS["object_loader"]->ds->search(array(
			"brother_of" => $this->id(),
			"lang_id" => array(),
			"site_id" => array()
		));
		$todelete = array_keys($tmp);
		$inst = get_instance(CL_TASK);
		foreach($todelete as $id)
		{
			if($id != $this->id() && $inst->can("delete" , $id))
			{
				$brother = obj($id);
				$brother->delete();
			}
		}
		parent::delete();
	}

	function name()
	{
		if ($this->_no_display)
		{
			return t("Isiklik");
		}
		return parent::name();
	}

	function comment()
	{
		if ($this->_no_display)
		{
			return t("Isiklik");
		}
		return parent::comment();
	}

	function prop($pn)
	{
		$show_props = array(
			"start1", "end", "deadline", "is_personal"
		);
		if (!$this->_no_display || in_array($pn, $show_props))
		{
			return parent::prop($pn);
		}

		if ($pn == "name")
		{
			return t("Isiklik");
		}
		return "";
	}

	function _init_override_object()
	{
		$this->_no_display = 0;
		if ($this->meta("is_personal"))
		{
			if (aw_global_get("uid") != $this->createdby())
			{
				$this->_no_display = 1;
			}
		}
	}

	function sum_guess()
	{
		$sum = 0;
		if($this->prop("num_hrs_guess"))
		{
			$sum = $this->get_hr_price()*$this->prop("num_hrs_guess");
		}
		return $sum;
	}

	function get_hr_price()
	{
		if ($this->prop("hr_price"))
		{
			return $this->prop("hr_price");
		}
		$conns = $this->connections_to(array());
		foreach($conns as $conn)
		{
			if($conn->prop('from.class_id')==CL_CRM_PERSON)
			{
				$pers = $conn->from();
				// get profession
				$rank = $pers->prop("rank");
				if (is_oid($rank) && $this->can("view", $rank))
				{
					$rank = obj($rank);
					if($rank->prop("hr_price"))
					{
						//salvestada, et m6nes teises vaates teisi arve ei n2itaks
						$this->set_prop("hr_price" , $rank->prop("hr_price"));
						$this->save();
						return $rank->prop("hr_price");
					}
				}
			}
		}
		return 0;
	}

	/** returns all row object ids
		@attrib api=1
		@returns array
	**/
	public function get_all_rows()
	{
		$ret = array();
		$conns = $this->connections_from(array(
			"type" => "RELTYPE_ROW",
		));
		foreach($conns as $con)
		{
			$ret[] = $con->prop("to");
		}
		return $ret;
	}

	/** sets task "real time" to rows "real time" sum
		@attrib api=1
	**/
	public function update_hours()
	{
		$hours = $this->get_row_hours();
		$this->set_prop("time_real", $hours);
		$this->save();
		$GLOBALS["do_not_change_task_real_time"] = 1;//nii ei saa yle salvestada vana v22rtuse klassi vaates
	}

	/** sets task "time to customer" to rows "time to customer" sum
		@attrib api=1
	**/
	public function update_cust_hours()
	{
		$hours = $this->get_row_cust_hours();
		$this->set_prop("time_to_cust", $hours);
		$this->save();
		$GLOBALS["do_not_change_task_cust_time"] = 1;
	}

	/** returns all rows time to customer sum
		@attrib api=1
		@returns int
			row hours sum
	**/
	public function get_row_cust_hours()
	{
		$hours = 0;
		foreach($this->get_rows_data() as $bcs)
		{
			$hours+= $bcs["time_to_cust"];
		}
		return $hours;
	}

	/** returns all rows real time sum
		@attrib api=1
		@returns int
			row hours sum
	**/
	public function get_row_hours()
	{
		$hours = 0;
		foreach($this->get_rows_data() as $bcs)
		{
			$hours+= $bcs["time_real"];
		}
		return $hours;
	}

	/** returns all rows data
		@attrib api=1
		@returns array
			row object
	**/
	public function get_rows_data()
	{
		$filter = array(
			"class_id" => CL_TASK_ROW,
			"task" => $this->id(),
			"lang_id" => array(),
		);
		$req = array
		(
			CL_TASK_ROW => array(
				 "time_real" => "time_real",
				"time_to_cust" => "time_to_cust",
			),
		);
		$row_arr = new object_data_list($filter , $req);
		return $row_arr->list_data;
	}

	/** makes new task row
		@attrib api=1
		@returns object
			row object
	**/
	function add_row()
	{
		$new_row = new object();
		$new_row->set_class_id(CL_TASK_ROW);
		$new_row->set_parent($this->id());
		$new_row->set_prop("task" , $this->id());
		$time = $this->prop("end");
		if(!($time > 0))
		{
			$time = $this->prop("start1");
		}
		if(!($time > 0))
		{
			$time =  time();
		}
		$new_row->set_prop("date" , $time);
		$new_row->save();
		$this->connect(array(
			"to" => $new_row->id(),
			"type" => "RELTYPE_ROW"
		));
		return $new_row;
	}

	function get_all_expenses()
	{
		$ret = array();
		$conns = $this->connections_from(array(
			"type" => "RELTYPE_EXPENSE",
		));
		foreach($conns as $con)
		{
			$ret[] = $con->prop("to");
		}
		return $ret;
	}

	/** returns all billable expenses
		@attrib api=1
		@returns object list
	**/
	function get_billable_expenses()
	{
		$ret = new object_list();
		$conns = $this->connections_from(array(
			"type" => "RELTYPE_EXPENSE",
		));
		$ti = get_instance(CL_TASK);
		foreach($conns as $con)
		{
			$o = $con->to();
			if(!$ti->can("view" , $o->prop("bill_id")))
			{
				$ret->add($o->id());
			}
		}
		return $ret;
	}

	/** sets bill_id prop to all billable expenses
		@attrib api=1
		@param bill_id required type=oid
		@returns boolean
			1 if successful, else 0
	**/
	function set_billable_oe_bill_id($bill_id)
	{
		if(!is_oid($bill_id))
		{
			return 0;
		}
		$billable_oe = $this->get_billable_expenses();
		foreach($billable_oe->arr() as $boe)
		{
			$boe->set_prop("bill_id" , $bill_id);
			$boe->save();
		}
		return 1;
	}

	/** returns task client manager oid
		@attrib api=1
		@returns oid	
	**/
	function get_client_mgr()
	{
		return $this->prop("customer.client_manager");
	}

	/** returns task client manager name
		@attrib api=1
		@returns string	
	**/
	function get_client_mgr_name()
	{
		return $this->prop("customer.client_manager.name");
	}

	/** user can check the send_bill checkbox or not
		@attrib api=1
		@returns boolean
			true , if can, false if not
	**/
	function if_can_set_billable()
	{
		$seti = get_instance(CL_CRM_SETTINGS);
		$sts = $seti->get_current_settings();
		if ($sts && $sts->prop("billable_only_by_mrg"))
		{
			$u = get_instance(CL_USER);
			$p = $u->get_current_person();//arr($p); arr($this -> get_client_mgr());
			if($this -> get_client_mgr() == $p)
			{
				return true;
			}
			return false;
		}
		else
		{
			return true;
		}
	}

	/** returns task primary row for person
		@attrib api=1 params=pos
		@param person required type=oid
			person object id
		@returns object
			row object
	**/
	public function get_primary_row_for_person($person)
	{
		if(!is_oid($person)) return null;
		$ol = new object_list(array(
			"class_id" =>  CL_TASK_ROW,
			"lang_id" => array(),
			"CL_TASK_ROW.RELTYPE_IMPL" => $person,
			"site_id" => array(),
			"primary" => 1,
			"task" => $this->id(),
		));
		return reset($ol->arr());
	}

	public function set_primary_row($data)
	{
		if(!$data["person"])
		{
			$u = get_instance(CL_USER);
			$data["person"] = $u->get_current_person();
		}
		$row = $this->get_primary_row_for_person($data["person"]);
		if(!$row)
		{
			$person = obj($data["person"]);
			$row = $this->add_row();
			$name = $this->name()." ".($person->name() ? $person->name() : "")." ".t("tegevus");
			$row->set_name($name);
			$row->set_prop("content" , $name);
			$row->set_prop("impl" , $data["person"]);
			$row->set_prop("primary" , 1);
			$row->set_prop("done" , 1);
		}
		foreach($data as $prop => $value)
		{
			if($row->is_property($prop))
			{
				$row->set_prop($prop , $value);
			}
		}
		$row->save();
	}

	public function set_party($data)
	{
		if(!$data["participant"])
		{
			$u = get_instance(CL_USER);
			$data["participant"] = $u->get_current_person();
		}
		$row = $this->get_party_obj($data["participant"]);
		if(!$row)
		{
			$row = $this->add_party($data["participant"]);
		}
		foreach($data as $prop => $value)
		{
			if($row->is_property($prop))
			{
				$row->set_prop($prop , $value);
			}
		}
		$row->save();
	}

	private function add_party($part)
	{
		$p = obj($part);
		$new_row = new object();
		$new_row->set_class_id(CL_CRM_PARTY);
		$new_row->set_parent($this->id());
		$new_row->set_name($p->name()." ".$this->name()." ".t("osalus"));
		$new_row->set_prop("task" , $this->id());
		$new_row->set_prop("participant" , $part);
		$new_row->save();
		return $new_row;
	}

	public function has_work_time()
	{
		$u = get_instance(CL_USER);
		if(!is_oid($person = $u->get_current_person()))
		{
			return null;
		}
		$row = $this->get_primary_row_for_person($person);
		if(is_object($row) && $row->prop("time_real"))
		{
			return 1;
		}
		return null;
	}

	/** returns party object for participant
		@attrib api=1 params=pos
		@param part required type=oid
		@returns object
	**/
	public function get_party_obj($part)
	{
		if(!is_oid($part)) return null;
		$ol = new object_list(array(
			"class_id" =>  CL_CRM_PARTY,
			"lang_id" => array(),
			"participant" => $part,
			"site_id" => array(),
			"task" => $this->id(),
			"limit" => 1,
		));
		return reset($ol->arr());
	}

}
?>