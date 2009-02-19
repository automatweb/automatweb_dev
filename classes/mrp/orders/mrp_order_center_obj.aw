<?php

class mrp_order_center_obj extends _int_object
{
	/** lists all order managers for orders
		@attrib api=1
	**/
	public function get_all_order_managers()
	{
		// all orders -> customer -> client_manager unique list
		$odl = new object_data_list(array(
			"class_id" => CL_CRM_PERSON,
			"lang_id" => array(),
			"site_id" => array(),
			"CL_CRM_PERSON.RELTYPE_CLIENT_MANAGER(CL_CRM_COMPANY).RELTYPE_CUSTOMER(CL_MRP_ORDER).workspace" => $this->id()
		),
		array(
			CL_CRM_PERSON => array(new obj_sql_func(OBJ_SQL_UNIQUE, "oid", "objects.oid"))
		));
		$tmp = $odl->arr();
		$ol = new object_list(array(
			"oid" => $tmp[0],
			"lang_id" => array(),
			"site_id" => array()
		));
		return $ol->names();
	}
}

?>
