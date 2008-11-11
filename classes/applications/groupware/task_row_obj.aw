<?php
/*
@classinfo maintainer=markop
*/
class task_row_obj extends _int_object
{
	function set_prop($pn, $pv)
	{
		switch($pn)
		{
			case "time_guess":
			case "time_real":
			case "time_to_cust":
				$pv = str_replace("," , "." , $pv);
				break;
		}

		return parent::set_prop($pn, $pv);
	}

	function save($arr = array())
	{
		//igal salvestamisel v6iks osa infot yle kontrollida toimetuse juurest
		$task = obj($this->prop("task"));
		if(is_object($task))
		{
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
			)) as $conn)
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

		parent::save();
	}

}
?>