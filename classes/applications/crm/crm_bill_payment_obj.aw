<?php

class crm_bill_payment_obj extends _int_object
{
	function set_prop($name,$value)
	{
/*		if($name == "sum")
		{
			parent::set_prop($name,$value);
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
				$bill_sum = $bi->get_bill_sum($o);
				$sum = 0;
				foreach($o->connections_from(array("type" => "RELTYPE_PAYMENT")) as $conn)
				{
					$p = $conn->to();
					$sum = $sum + $p->get_free_sum($o);
				}
				if($bill_sum < $sum)
				{
					$sum = $bill_sum;
				}
				$o->set_prop("partial_recieved", $sum);
				$o->save();
			}
			return;
		}
*/
		if($name == "currency_rate")
		{
			if(!$value && $this->prop("currency"))
			{
				$ci = get_instance(CL_CURRENCY);
				$value = $ci->convert(array("sum" => 1, "from" => $this->prop("currency"), "date" => $this->prop("date") ? $this->prop("date") : time()));
			}
		}
	//	if($name == "currency")
	//	{
	//		$ol = new object_list(array(
	//			"class_id" => CL_CRM_BILL,
	//			"lang_id" => array(),
	//			"CL_CRM_BILL.RELTYPE_PAYMENT.id" => $this->id(),
	//		));
	//		$o = reset($ol->arr());
	//		$bi = get_instance(CL_CRM_BILL);
	//		if(is_object($o) && $bi->get_bill_currency_id($o) != $value)
	//		{
	//			return;
	//		}
	//	}
		parent::set_prop($name,$value);
	}

	/**
		@attrib api=1 all_args=1
	@returns double
	@param b optional type=oid
		bill object , if you want free sum for bill
	@comment
		returns available payment sum (not connected with bills or with given bill)
	**/
	function get_free_sum($b)
	{
		$sum = $this->prop("sum");
		$ol = new object_list(array(
			"class_id" => CL_CRM_BILL,
			"lang_id" => array(),
			"CL_CRM_BILL.RELTYPE_PAYMENT.id" => $this->id(),
		));

		$bi = get_instance(CL_CRM_BILL);
		foreach($ol->arr() as $o)
		{
			//$bill_sum = $bi->get_bill_sum($o);
			$bill_sum = $bi->get_bill_needs_payment(array(
				"bill" => $o,
				"payment" => $this->id()
			));
			if($b && $b == $o->id())
			{
				return min($bill_sum , $sum);
			}
			$sum = $sum - $bill_sum;
		}

		if($sum < 0)
		{
			$sum = 0;
		}

		return $sum;
	}

	/**
		@attrib api=1 all_args=1
	@param o required type=oid/object
		bill object you want to add
	@returns string error

	@comment
		adds bill to payment or returns error message if cant
	**/
	function add_bill($o)
	{
		//kui in id, siis objektiks
		if(!is_object($o) && is_oid($o) && $this->can("view", $o))
		{
			$o = obj($o);
		}
		$bi = get_instance("applications/crm/crm_bill");

		//mõned asjad mis võivad saada operatsiooni takistuseks
		if(!$this->get_free_sum())
		{
			return t("Laekumisel juba piisava summa eest areveid");
		}
		if($this->prop("currency") && $this->prop("currency") != $bi->get_bill_currency_id($o))
		{
			return t("Laekumise valuuta erineb arve omast");
		}
		$ol = new object_list(array(
			"class_id" => CL_CRM_BILL,
			"lang_id" => array(),
			"CL_CRM_BILL.RELTYPE_PAYMENT.id" => $this->id(),
		));
		$eb = reset($ol->arr());
		if(is_object($eb) && $eb->prop("customer") != $o->prop("customer"))
		{
			return t("laekumine ei saa olla erinevate klientidega arvetele");
		}

		//vigu pole, siis teeb ära
		$o->connect(array(
			"to" => $this->id(),
			"type" => "RELTYPE_PAYMENT"
		));

		if(!$this->prop("currency"))
		{
			$this->set_prop("currency" , $bi->get_bill_currency_id($o));
			$this->save();
		}
		return "";
	}

}

?>
