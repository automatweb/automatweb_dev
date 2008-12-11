<?php
/*
@classinfo maintainer=markop
*/
class task_row_obj extends _int_object
{
	function prop($pn)
	{
		switch($pn)
		{
			case "comment":
				$pn = "content";
				break;
			case "add_wh_guess":
				$pn = "time_guess";
				break;
			case "add_wh":
				$pn = "time_real";
				break;
			case "add_wh_cust":
				$pn = "time_to_cust";
				break;
			case "send_bill":
				$pn = "on_bill";
				break;
			case "bill":
				$pn = "bill_id";
				break;
			case "bug":
				$pn = "task";
				break;
		}
		$val =  parent::prop($pn);
	
		switch($pn)
		{
			case "date":
				if(!$val)
				{
					$val = $this->created();
				}
				break;
		}

		return $val;
	}

	/** returns row's task id
		@attrib api=1
		@returns oid
	**/
	public function task_id()
	{
		if($this->prop("task"))
		{
			return $this->prop("task");
		}
		$possible_task_classes = array(CL_BUG,CL_TASK,CL_CRM_MEETING,CL_CRM_CALL);
		$conn = $this->connections_to(array(
//			"type" => "RELTYPE_ROW",//erinevate klasside puhul ei paista toimivat
			"from.class_id" => $possible_task_classes,
		));
		$c = reset($conn);
		if ($c)
		{
			$task_o = $c->from();
		}
		else
		{
			$task_o = obj($this->parent());
		}
		if(!is_object($task_o) || !in_array($task_o->class_id(), $possible_task_classes))
		{
			print t("Toimetuse rea toimetust pole 6ieti:") ." ".$this->id();
		}
		$this->set_prop("task" , $task_o->id());
		$this->save();
		return $task_o->id();
	}

	/** returns row's task object
		@attrib api=1
		@returns object
	**/
	public function task()
	{
		if($this->task_id())
		{
			return obj($this->task_id());
		}
		else
		{
			return false;
		}
	}

	function set_comment($comment)
	{
		$this->set_prop("content" , $comment);
	}

	function comment()
	{
		if(parent::comment())
		{
			return parent::comment();
		}
		return $this->prop("content");
	}

	function set_prop($pn, $pv)
	{
		switch($pn)
		{
			case "comment":
				$pn = "content";
				break;
			case "add_wh_guess":
				$pn = "time_guess";
				break;
			case "add_wh":
				$pn = "time_real";
				break;
			case "add_wh_cust":
				$pn = "time_to_cust";
				break;
			case "send_bill":
				$pn = "on_bill";
				break;
			case "bill":
				$pn = "bill_id";
				break;
			case "bug":
				$pn = "task";
				break;
		}

		switch($pn)
		{
			case "time_real":
			case "time_guess":
			case "time_to_cust":
				$pv = str_replace("," , "." , $pv);

				if($pn == "time_real" && $pv != $this->prop("time_real"))
				{
					$this->update_time_real = 1;
				}
				if($pn == "time_to_cust" && $pv != $this->prop("time_to_cust"))
				{
					$this->update_time_cust = 1;
				}
				break;
		}

		$ret =  parent::set_prop($pn, $pv);

		return $ret;
	}

	function save($arr = array())
	{
		//igal salvestamisel v6iks osa infot yle kontrollida toimetuse juurest
		if($this->prop("primary"))
		{
			$this->set_prop("done" , 1);
		}
		if(!is_oid($this->id()))
		{
			if(!$this->prop("date"))
			{
				$this->set_prop("date" , time());
			}
			if(!$this->prop("impl"))
			{
				$cp = get_current_person();
				$this->set_prop("impl",$cp->id());
			}
		}
		$ret = parent::save();
		if(is_oid($this->prop("task")))
		{
			$task = obj($this->prop("task"));
		}
		if(is_object($task))
		{
			if($task->class_id() != CL_BUG)
			{
				if(isset($this->update_time_real))
				{
					if($task->created() > 1227022000)
						$task->update_hours();
				}
				if(isset($this->update_time_cust))
				{
					if($task->created() > 1227022000)
						$task->update_cust_hours();
				}
			}
			foreach($task->connections_from(array(
				"type" => "RELTYPE_CUSTOMER",
			)) as $c)
			{
				if (!$this->is_connected_to(array("to" => $c->prop("to"), "type" => "RELTYPE_CUSTOMER")))
				{
					$this->connect(array(
						"to" => $c->prop("to"),
						"reltype" => "RELTYPE_CUSTOMER"
					));
				}
			}

			foreach($task->connections_from(array(
				"type" => "RELTYPE_PROJECT",
			)) as $c)
			{
				if (!$this->is_connected_to(array("to" => $c->prop("to"), "type" => "RELTYPE_PROJECT")))
				{
					$this->connect(array(
						"to" => $c->prop("to"),
						"reltype" => "RELTYPE_PROJECT"
					));
				}
			}
		}
		return $ret;
	}

}
?>