<?php
/*
@classinfo maintainer=markop
*/
class task_row_obj extends _int_object
{
	function prop($pv)
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
		return parent::prop($pn);
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
		parent::save();
		if(is_oid($this->prop("task")))
		{
			$task = obj($this->prop("task"));
		}
		if(is_object($task))
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
	}

}
?>