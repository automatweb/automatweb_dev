<?php

class crm_bill_payment_obj extends _int_object
{
	function set_prop($name,$value)
	{
		if($name == "sum")
		{
			if(!$this->id())
			{
				$this->save();//kui id'd pole , siis läheb lolliks 
			}
			$ol = new object_list(array(
				"class_id" => CL_CRM_BILL,
				"lang_id" => array(),
				"CL_CRM_BILL.RELTYPE_PAYMENT.id" => $this->id(),
			));
			$bi = get_instance(CL_CRM_BILL);
			foreach($ol -> arr() as $o)
			{
				$sum = 0;
				foreach($o->connections_from(array("type" => "RELTYPE_PAYMENT")) as $conn)
				{
					$p = $conn->to();
					if($p -> id() == $this->id())
					{
						$sum = $sum + $value;
					}
					else
					{
						$sum = $sum + $p->prop("sum");
					}
				}
				$o->set_prop("partial_recieved", $sum);
				$o-> save();
			}
		}

// 		if($name == "currency")
// 		{
// 			if(!($_POST["currency_rate"]))
// 			{
// 				$ci = get_instance(CL_CURRENCY);
// 				$this->set_prop("currency_rate" , $ci->convert(array("sum" => 1, "from" => $value, "date" => $this->prop("date") ? $this->prop("date") : time())));$this->save();
// 				unset($_POST["currency_rate"]);
// 			}
// 		}
		if($name == "currency_rate")
		{
			if(!$value && $this->prop("currency"))
			{
				$ci = get_instance(CL_CURRENCY);
				$value = $ci->convert(array("sum" => 1, "from" => $this->prop("currency"), "date" => $this->prop("date") ? $this->prop("date") : time()));
			}
		}

//arr($this->prop("currency_rate"));arr($name);if($name == "currency")die();
		parent::set_prop($name,$value);
	}

	function get_free_sum()
	{
		$sum = $this->prop("sum");
		$ol = new object_list(array(
			"class_id" => CL_CRM_BILL,
			"lang_id" => array(),
			"CL_CRM_BILL.RELTYPE_PAYMENT.id" => $this->id(),
		));

		$bi = get_instance(CL_CRM_BILL);
		foreach($ol -> arr() as $o)
		{
			$sum = $sum - $bi->get_bill_sum($o);
		}

		if($sum < 0)
		{
			$sum = 0;
		}

		return $sum;
	}
}

?>
