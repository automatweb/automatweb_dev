<?php

class crm_data extends class_base
{
	function crm_data()
	{
		$this->init();
	}
	
	///////////// BILLS

	/**
		@comment
			co - object of company to return bills for
			filter - array of bill filters
				keys:
					monthly - 1/0 - to return only monthly bills
					bill_no - bill number to search by
					bill_date_range - array("from" => time, "to" => time)
					state - 0 - being created, 1 - sent, 2 - paid
					client_mgr - client manager for bill customer, text
					customer - customer for bill, text
			returns an object_list of bills found
	**/
	function get_bills_by_co($co, $filter = NULL)
	{
		$of = array(
			"class_id" => CL_CRM_BILL,
			"parent" => $co->id()
		);

		if ($filter !== NULL)
		{
			error::raise_if(!is_array($filter), array(
				"id" => "ERR_CRM_PARAM",
				"msg" => sprintf(t("crm_data::get_bills_by_co(): second parameter must be an array, if set!"))
			));

			if (isset($filter["bill_no"]))
			{
				$of["bill_no"] = $filter["bill_no"];
			}
			if (isset($filter["monthly"]))
			{
				$of["monthly_bill"] = $filter["monthly"];
			}
			if (isset($filter["bill_date_range"]))
			{
				$r = $filter["bill_date_range"];

				if ($r["from"] > 100 && $r["to"] > 100)
				{
					$of["bill_date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $r["from"], $r["to"]);
				}
				else
				if ($r["from"] > 100)
				{
					$of["bill_date"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $r["from"]);
				}
				else
				if ($r["to"] > 100)
				{
					$of["bill_date"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $r["to"]);
				}
			}
			if (isset($filter["state"]))
			{
				$of["CL_CRM_BILL.state"] = $filter["state"];
			}

			$of2 = $of;

			if (isset($filter["client_mgr"]))
			{
				$of["CL_CRM_BILL.customer(CL_CRM_COMPANY).client_manager.name"] = map("%%%s%%", explode(",", $filter["client_mgr"]));
				$of2["CL_CRM_BILL.customer(CL_CRM_PERSON).client_manager.name"] = map("%%%s%%", explode(",", $filter["client_mgr"]));
			}

			if (isset($filter["customer"]))
			{
				$of["CL_CRM_BILL.customer(CL_CRM_COMPANY).name"] = "%".$filter["customer"]."%";
				$of2["CL_CRM_BILL.customer(CL_CRM_PERSON).name"] = "%".$filter["customer"]."%";
			}
		}
		$ret =  new object_list($of);
		if (isset($of2))
		{
			$ret->add(new object_list($of2));
		}
		return $ret;
	}
}
?>
