<?php
/*
@classinfo  maintainer=markop
*/
class crm_bill_row_object extends _int_object
{
	function set_name($name)
	{
		$rv = parent::set_name($name);
		$this->set_prop("desc", $name);
		return $rv;
	}

	function set_prop($p, $v)
	{
		$rv = parent::set_prop($p, $v);
		if ($p == "name")
		{
			$this->set_prop("desc", $v);
		}
		return $rv;
	}

	function get_sum()
	{
		if($this->prop("sum"))
		{
			$sum = $this->prop("sum");
		}
		else
		{
			$sum = str_replace(",", ".", $this->prop("amt")) * str_replace(",", ".", $this->prop("price"));
		}
		return $sum;
	}

	/** checks if task_row is connected to task
		@attrib api=1
		@returns boolean
			reservation price
	**/
	function has_task_row()
	{
		foreach($this->connections_from(array("type" => "RELTYPE_TASK_ROW"))as $c)
		{
			return 1;
		}
		return 0;
	}

	/** Returns task row or bug connected to this bill row
		@attrib api=1
		@returns oid
			bug or task row id
	**/
	public function get_task_row_or_bug_id()
	{
		foreach($this->connections_from(array("type" => "RELTYPE_TASK_ROW"))as $c)
		{
			return $c->prop("to");
		}
		foreach($this->connections_from(array("type" => "RELTYPE_BUG"))as $c)
		{
			return $c->prop("to");
		}
		return "";
	}

	/** Returns task row or bug orderer person name
		@attrib api=1
		@returns string
			Person name
	**/
	public function get_orderer_person_name()
	{
		$problem = $this->get_task_row_or_bug_id();
		if($problem)
		{
			$problem = obj($problem);
		}
		else
		{
			return "";
		}
		if($problem->class_id() == CL_BUG)
		{
			if($ret = $problem->prop("customer_person.name"))
			{
				return $ret;
			}
		}
		if($problem->class_id() == CL_TASK_ROW)
		{
			if($ret = $problem->prop("task.customer.name"))
			{
				return $ret;
			}
		}
		return "";
	}

	/** returns bill row bill id
		@attrib api=1
		@returns oid
			bill id
	**/
	function get_bill()
	{
		$bills_list = new object_list(array(
			"class_id" => CL_CRM_BILL,
			"lang_id" => array(),
			"CL_CRM_BILL.RELTYPE_ROW" => $this->id(),
		));
		return reset($bills_list->ids());
	}

	/** checks if bill has other customers...
		@attrib api=1
		@param customer type=oid
		@returns string/int
			error, if true, if not, then 0
	**/
	function check_if_has_other_customers($customer)
	{
		$bill = $this->get_bill();
		if(!is_oid($bill) || !is_oid($customer))
		{
			return 0;
		}
		$bill = obj($bill);
		if(!$bill->prop("customer"))
		{
			return 0;
		}
		if($customer != $bill->prop("customer"))
		{
			return "on teised kliendid...";
		}
		return 0;
	}

	/** connects bill row to a task row
		@attrib api=1
		@returns 
			error string if unsuccessful
	**/
	function connect_task_row($row)
	{
		if(!is_oid($row))
		{
			return t("Pole piisavalt p&auml;dev klassi id");
		}
		$row_obj = obj($row);
		if(!is_oid($row_obj->prop("task")))
		{
			return t("Ridadel pole toimetust m&auml;&auml;ratud");
		}
		$tasko = obj($row_obj->prop("task"));
		$error = $this->check_if_has_other_customers($tasko->prop("project.orderer"));
		if($error)
		{
			return $error;
		}
		$this->connect(array("to"=> $row, "type" => "RELTYPE_TASK_ROW"));
		$this->connect(array("to"=> $row_obj->prop("task"), "type" => "RELTYPE_TASK"));
		$bill = $this->get_bill();
		if(is_oid($bill))
		{
			$billo = obj($bill);
			$billo->connect(array("to"=> $row_obj->prop("task"), "type" => "RELTYPE_TASK"));
		}
		$tasko->connect(array("to"=> $bill, "type" => "RELTYPE_BILL"));
		$row_obj->set_prop("bill_id" , $bill);
		$row_obj->save();
		return 0;
	}

	/** connects bill row to a bug comment
		@attrib api=1 params=pos
		@param id required type=oid
			bug comment object id
		@returns 
	**/
	function connect_bug_comment($id)
	{
		if(!is_oid($id))
		{
			return t("Pole piisavalt p&auml;dev klassi id");
		}
		$obj = obj($id);
		$bug = obj($obj->parent());
		if($bug->class_id() != CL_BUG)
		{
			return t("Kommentaaril pole bugi");
		}
		$error = $this->check_if_has_other_customers($bug->prop("customer"));
		if($error)
		{
			return $error;
		}
		$this->connect(array("to"=> $bug->id(), "type" => "RELTYPE_BUG"));

		return 0;
		//tegelt ma ei teagi kas on yldse m6tet rida ka siduma hakata

	}

}
?>
