<?php

class crm_bill_obj extends _int_object
{
	function set_prop($name,$value)
	{
		if($name == "bill_no")
		{
//			if(!$this->name() || strlen($this->name()) < 9)
//			{
				$this->set_name(t("Arve nr")." ".$value);
//			}
//			elseif($this->prop("bill_no") && substr_count($this->name() , $this->prop("bill_no")))
//			{
//				$this->set_name(str_replace($this->prop("bill_no"), $value , $this->name()));
//			}
		}

		parent::set_prop($name,$value);
	}

	function get_bill_print_popup_menu()
	{
		$bill_inst = get_instance(CL_CRM_BILL);
		$pop = get_instance("vcl/popup_menu");
		$pop->begin_menu("bill_".$this->id());
		$pop->add_item(Array(
			"text" => t("Prindi arve"),
			"link" => "#",
			"oncl" => "onClick='window.open(\"".$bill_inst->mk_my_orb("change", array("openprintdialog" => 1,"id" => $this->id(), "group" => "preview"), CL_CRM_BILL)."\",\"billprint\",\"width=100,height=100\");'"
		));
		$pop->add_item(Array(
			"text" => t("Prindi arve lisa"),
			"link" => "#",
			"oncl" => "onClick='window.open(\"".$bill_inst->mk_my_orb("change", array("openprintdialog" => 1,"id" => $this->id(), "group" => "preview_add"), CL_CRM_BILL)."\",\"billprintadd\",\"width=100,height=100\");'"
		));
		$pop->add_item(array(
			"text" => t("Prindi arve koos lisaga"),
			"link" => "#",
			"oncl" => "onClick='window.open(\"".$bill_inst->mk_my_orb("change", array("openprintdialog_b" => 1,"id" => $this->id(), "group" => "preview"), CL_CRM_BILL)."\",\"billprintadd\",\"width=100,height=100\");'"
		));
		return $pop->get_menu();
	}
  
	function get_bill_currency_id()
	{
		if($this->prop("customer.currency"))
		{
			return $this->prop("customer.currency");
		}
		$co_stat_inst = get_instance("applications/crm/crm_company_stats_impl");
		$company_curr = $co_stat_inst->get_company_currency();
		return $company_curr;
	}

	function get_bill_currency_name()
	{
		if($this->prop("customer.currency"))
		{
			$company_curr = $this->prop("customer.currency");
		}
		else
		{
			$co_stat_inst = get_instance("applications/crm/crm_company_stats_impl");
			$company_curr = $co_stat_inst->get_company_currency();
		}
		if(is_oid($company_curr) && $this->can("view" , $company_curr))
		{
			$cu_o = obj($company_curr);
			return $cu_o->name();
		}
		return "EEK";
	}


	/**
		@attrib api=1 all_args=1
	@param payment optional type=oid
		payment id you want to ignore
	@returns string error
	@comment
		returns sum not paid for bill
	**/
	function get_bill_needs_payment($arr)
	{
		$payment = $arr["payment"];
		$bi = get_instance(CL_CRM_BILL);
		$bill_sum = $bi->get_bill_sum($this);
		$sum = 0;
		foreach($this->connections_from(array("type" => "RELTYPE_PAYMENT")) as $conn)
		{
			$p = $conn->to();//echo $p->id();
			if($payment && $payment == $p->id())
			{
				if(($bill_sum - $sum) > $p->prop("sum")) // kui arve summa - juba makstud summa on suurem kui antud laekumine , siis tagastaks selle sama laekumise summa, sest rohkem vtta ju pole
				{
					return $p->prop("sum");
				}
				break;
			}
			$sum = $sum + $p->get_free_sum($this->id());
		}
		if($bill_sum < $sum)
		{
			$sum = $bill_sum;
		}
		return $bill_sum - $sum;
	}

	/** Adds payment in the given amount to the bill
		@attrib api=1 params=pos

		@param sum optional type=double 
			The sum the payment was for. defaults to the entire sum of the bill

		@param tm optional type=int
			Time for the payment. defaults to current time

		@returns
			oid of the payment object
	**/
	function add_payment($sum = 0, $tm = null)
	{
		if ($tm === null)
		{
			$tm = time();
		}
		$i = get_instance(CL_CRM_BILL);
		if(!$sum)
		{
			$sum = $i->get_bill_sum($this,BILL_SUM) - $this->prop("partial_recieved");
		}
		$p = new object();
		$p-> set_parent($this->id());
		$p-> set_name($this->name() . " " . t("laekumine"));
		$p-> set_class_id(CL_CRM_BILL_PAYMENT);
		$p-> set_prop("date", $tm);
		$p->save();
/*
		$this->connect(array(
			"to" => $p->id(),
			"type" => "RELTYPE_PAYMENT"
		));

		$p-> set_prop("sum", $sum);//see koht sureb miskiprast
		$curr = $i->get_bill_currency_id($this);
		if($curr)
		{
			$ci = get_instance(CL_CURRENCY);
			$p -> set_prop("currency", $curr);
			$rate = 1;
			if(($default_c = $ci->get_default_currency) != $curr)
			{
				$rate = $ci->convert(array(
					"sum" => 1,
					"from" => $curr,
					"to" => $default_c,
					"date" => time(),
				));
			}
			$p -> set_prop("currency_rate", $rate);
		}
		$p-> save();*/
		$p->add_bill(array(
			"sum" => $sum,
			"o" => $this,
		));
		return $p->id();
	}

	function get_bill_payments_data()
	{
		$data = array();
		foreach($this->connections_from(array("type" => "RELTYPE_PAYMENT")) as $conn)
		{
			$p = $conn->to();
			$data[$p->id()]["currency"] = $p->get_currency_name();
			$bill_sums = $p->meta("sum_for_bill");
			$data[$p->id()]["sum"] = $bill_sums[$this->id()];
			$data[$p->id()]["total_sum"] = $p->prop("sum");
			$data[$p->id()]["date"] = $p->prop("date");
		}

		return $data;
	}
}

?>
