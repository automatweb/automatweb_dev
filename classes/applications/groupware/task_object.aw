<?php

// maintainer=markop 
class task_object extends _int_object
{
	function task_object()
	{
		parent::_int_object();
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

	function get_all_rows()
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
}
?>