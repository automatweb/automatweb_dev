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
					$of["bill_date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $r["from"], $r["to"]);
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
			if (isset($filter["state"]) && $filter["state"] != -1)
			{
				$of["CL_CRM_BILL.state"] = $filter["state"];
			}

			$of2 = $of;

			if (isset($filter["client_mgr"]))
			{
				$relist = new object_list(array(
					"class_id" => CL_CRM_COMPANY_ROLE_ENTRY,
					"CL_CRM_COMPANY_ROLE_ENTRY.person.name" => map("%%%s%%", explode(",", $filter["client_mgr"]))
				));

				$rs = array();
				foreach($relist->arr() as $o)
				{
					$rs = $o->prop("client");
				}

				$ft = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array(
						"CL_CRM_BILL.customer(CL_CRM_COMPANY).client_manager.name" => map("%%%s%%", explode(",", $filter["client_mgr"])),
						"CL_CRM_BILL.customer(CL_CRM_COMPANY).client_manager.name" => map("%%%s%%", explode(",", $filter["client_mgr"])),
						"oid" => $rs
					)
				));

				$of[] = $ft;
				$of2[] = $ft;
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

	///////////////// customers

	/** returns customers for company 
	**/
	function get_customers_for_company($co)
	{
		$ret = array();
		$this->_int_req_get_cust_co($co, $ret);

		return $ret;
	}

	function _int_req_get_cust_co($co, &$ret)
	{
		foreach($co->connections_from(array("type" => "RELTYPE_CUSTOMER")) as $c)
		{
			$ret[$c->prop("to")] = $c->prop("to");
		}
		
		foreach($co->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$this->_int_req_get_cust_co($c->to(), $ret);
		}
	}

	//////////////////////////////////////////// company

	/** returns sections from the given company 
	**/
	function get_section_picker_from_company($co)
	{
		$ret = array();
		$this->req_level = -1;
		$this->_req_get_sect_picker($co, $ret);
		return $ret;
	}

	function _req_get_sect_picker($o, &$ret)
	{
		$this->req_level++;
		foreach($o->connections_from(array("type" => "RELTYPE_SECTION")) as $c)
		{
			$ret[$c->prop("to")] = str_repeat("&nbsp;&nbsp;&nbsp;", $this->req_level).$c->prop("to.name");
			$this->_req_get_sect_picker($c->to(), $ret);
		}
		$this->req_level--;
	}

	////////////////////////////////////////// current person

	function get_current_section()
	{
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
		
		$cs = $p->connections_to(array("from.class_id" => CL_CRM_SECTION));
		$c = reset($cs);
		if (!$c)
		{
			return NULL;
		}

		return $c->prop("from");
	}

	function get_current_profession()
	{
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
		
		$cs = $p->connections_from(array("to.class_id" => CL_CRM_PROFESSION));
		$c = reset($cs);
		if (!$c)
		{
			return NULL;
		}

		return $c->prop("to");
	}


	//////////// people
	function get_employee_picker($co = NULL, $add_empty = false, $important_only = true)
	{
		if ($co === NULL)
		{
			$u = get_instance(CL_USER);
			$cco_id = $u->get_current_company();
			if (!$this->can("view", $cco_id))
			{
				return array();
			}
			$co = obj($cco_id);
		}
		$i = get_instance(CL_CRM_COMPANY);
		return $i->get_employee_picker($co, $add_empty, $important_only);
	}
}
?>
