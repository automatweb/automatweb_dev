<?php

/*
@classinfo  maintainer=voldemar
*/

require_once "mrp_header.aw";

class mrp_workspace_obj extends _int_object
{
/**
	@attrib params=pos api=1
	@returns void
	@errors
		awex_mrp_ws_schedule if rescheduling request fails for some reason
**/
	public function request_rescheduling()
	{
		try
		{
			$this->set_prop("rescheduling_needed", 1);
			aw_disable_acl();
			$this->save();
			aw_restore_acl();
		}
		catch (Exception $E)
		{
			$e = new awex_mrp_ws_schedule("Rescheduling request failed");
			$e->set_forwarded_exception($E);
			throw $e;
		}
	}

	public function get_all_mrp_customers($arr = array())
	{
		$filter = array(
			"class_id" => CL_MRP_CASE,
			"CL_MRP_CASE.customer" =>  new obj_predicate_compare(OBJ_COMP_GREATER, 0),
		);
		if($arr["name"])
		{
			$filter["CL_MRP_CASE.customer.name"] = $arr["name"]."%";

		}
		$t = new object_data_list(
			$filter,
			array(
				CL_MRP_CASE=>  array(new obj_sql_func(OBJ_SQL_UNIQUE, "customer", "mrp_case.customer"))
			)
		);

		return $t->get_element_from_all("customer");
	}

	public function get_all_mrp_cases_data()
	{
		$filter = array(
			"class_id" => CL_MRP_CASE,
			"CL_MRP_CASE.customer" =>  new obj_predicate_compare(OBJ_COMP_GREATER, 0),
		);

		$t = new object_data_list(
			$filter,
			array(
				CL_MRP_CASE=>  array("customer")
			)
		);
		return $t->list_data;
	}

	public function set_priors($priors = array())
	{
		$c = $this->prop("owner");
		$o = obj($c);
		foreach($priors as $cust => $p)
		{
			$o->set_customer_prop($cust , "priority" , $p);
		}
	}

	/** returns all used warehouses
		@attrib api=1
		@returns object list
	**/
	public function get_warehouses()
	{
		$filter = array("class_id" => CL_SHOP_WAREHOUSE, "lang_id" => array(), "site_id" => array());
		return new object_list($filter);
	}

	/** returns material expense data
		@attrib api=1
		@param product type=oid
		@param category type=oid
			product category object id
		@param from type=int
			expenses from timestamp
		@param to type=oid
			expenses to timestamp
		@param resource type=oid/array
			resource ids
		@returns array
	**/
	public function get_material_expense_data($arr = array())
	{
		$filter = $this->_get_material_expense_filter($arr);
		$t = new object_data_list(
			$filter,
			array(
				CL_MATERIAL_EXPENSE=>  array("amount" , "used_amount" , "unit", "product","job","job.resource")
			)
		);
		return $t->list_data;
	}

	/** returns material expense data
		@attrib api=1
		@param product type=oid
		@param category type=oid
			product category object id
		@param from type=int
			expenses from timestamp
		@param to type=oid
			expenses to timestamp
		@param resource type=oid/array
			resource ids
		@returns object list
	**/
	public function get_material_expenses($arr = array())
	{
		$filter = $this->_get_material_expense_filter($arr);
		return new object_list($filter);
	}

	private function _get_material_expense_filter($arr)
	{
		$filter = array(
			"class_id" => CL_MATERIAL_EXPENSE,
			"site_id" => array(),
			"lang_id" => array()
		);
		if(isset($arr["product"]) && $arr["product"])
		{
			$filter["product"] = $arr["product"];
		}
		if(isset($arr["category"]) && $arr["category"])
		{
			$filter["product.RELTYPE_CATEGORY"] = $arr["category"];
		}

		if(isset($arr["from"]) && $arr["from"] > 0 && isset($arr["to"]) && $arr["to"] > 0)
		{
			$to += 24 * 60 * 60 -1;
			$filter["RELTYPE_JOB.started"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $arr["from"], $arr["to"]);
		}
		elseif(isset($arr["from"]) && $arr["from"] > 0)
		{
			$filter["RELTYPE_JOB.started"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $arr["from"]);
		}
		elseif(isset($arr["to"]) && $arr["to"] > 0)
		{
			$to += 24 * 60 * 60 -1;
			$filter["RELTYPE_JOB.started"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $arr["to"]);
		}

		if(isset($arr["resource"]))
		{
			$filter["RELTYPE_JOB.resource"] = $arr["resource"];
		}
		if(isset($arr["people"]))
		{
			$filter["RELTYPE_JOB.RELTYPE_PERSON"] = $arr["people"];
		}

		return $filter;
	}

	public function get_menu_resources($resources_folder)
	{
		$resource_tree_filter = array(
			"parent" => $resources_folder,
			"class_id" => array(CL_MENU, CL_MRP_RESOURCE),
			"sort_by" => "objects.jrk",
		);
		$resource_tree = new object_tree($resource_tree_filter);
		$ids = $resource_tree->ids();
		return $ids;
	}

}

/** Generic workspace error **/
class awex_mrp_ws extends awex_mrp {}

/** Generic workspace scheduling operations error **/
class awex_mrp_ws_schedule extends awex_mrp_ws {}


?>
