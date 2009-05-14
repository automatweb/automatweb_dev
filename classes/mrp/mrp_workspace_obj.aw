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
}

/** Generic workspace error **/
class awex_mrp_ws extends awex_mrp {}

/** Generic workspace scheduling operations error **/
class awex_mrp_ws_schedule extends awex_mrp_ws {}


?>
