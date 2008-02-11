<?php

class crm_company_obj extends _int_object
{
	function set_prop($name,$value)
	{
		parent::set_prop($name,$value);
	}

	function get_undone_orders()
	{
		$filter = array(
			"class_id" => CL_SHOP_ORDER,
			"orderer_company" => $this->id(),
//			"CL_CHOP_ORDER.order_completed" => 1,
			"site_id" => array(),
			"lang_id" => array(),
		);
		$ol = new object_list($filter);
//see ei ole hea, et peab kindlasti ümber tegema, kuid va toodet on igalpool kasutuses, et ei taha hetkel selle muutmisele mõelda
		foreach($ol->arr() as $o)
		{
			if($o->meta("order_completed"))
			{
				$ol->remove($o->id());
			}
		}
		return $ol;
	}

	function get_unpaid_bills()
	{
		$filter = array(
			"class_id" => CL_CRM_BILL,
			"customer" => $this->id(),
			"state" => new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, 1, 2),
			"site_id" => array(),
			"lang_id" => array(),
		);
		$ol = new object_list($filter);
		return $ol;
	}

	function get_cash_flow($start , $end)
	{
		$filter = array(
			"class_id" => CL_CRM_BILL,
			"customer" => $this->id(),
//			"state" => 2,
			"site_id" => array(),
			"lang_id" => array(),
		);

		if(!$start)
		{
			$start = mktime(0, 0, 0, 0, 0, (date("Y", time())) - 1);
		}

		if ($end > 100)
		{
			$filter["bill_date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $start, $end);
		}
		else
		{
			$filter["bill_date"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $start);
		}

		$ol = new object_list($filter);
//		return $ol;

		$bill_i = get_instance(CL_CRM_BILL);
		$co_stat_inst = get_instance("applications/crm/crm_company_stats_impl");
		$company_curr = $co_stat_inst->get_company_currency();

		foreach($ol->arr() as $bill)
		{
			$cursum = $bill_i->get_bill_sum($bill,$tax_add);

			//paneme ikka oma valuutasse ümber asja
			$curid = $bill->prop("customer.currency");
			if($company_curr && $curid && ($company_curr != $curid))
			{
				$cursum  = $this->convert_to_company_currency(array(
					"sum" =>  $cursum,
					"o" => $bill,
				));
			}
			$sum+= $cursum;
		}

		return number_format($sum , 2);
	}
}

?>
