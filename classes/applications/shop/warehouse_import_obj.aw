<?php

class warehouse_import_obj extends _int_object
{
	public function get_warehouses()
	{
		$wh_conns = $this->connections_from(array(
			'type' => 'RELTYPE_WAREHOUSE',
			'sort_by_num' => 'to.jrk',
			'sort_dir' => 'asc'
		));

		$result = array();
		foreach ($wh_conns as $conn)
		{
			$result[$conn->prop('to')] = $conn->prop('to.name');
		}
		return $result;
	}

	// maybe this one should be price_list functionality at the first place?
	// same goes with saving the price list data ....
	public function get_price_list_matrix($price_list_oid)
	{
		$data = array();
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRICE_LIST_CUSTOMER_DISCOUNT,
			"pricelist" => $price_list_oid
		));
		foreach($ol->arr() as $o)
		{
			$data[$o->prop("prod_category")][$o->prop("crm_category")] = $o->id();
		}
		return $data;
	}

	// maybe this one should be price list functionality as well ?
	public function get_product_categories()
	{
		$ol = new object_list(array(
			'class_id' => CL_SHOP_PRODUCT_CATEGORY
		));

		$result = array();
		foreach ($ol->arr() as $id => $o)
		{
			$result[$id] = $o->name();
		}
		return $result;
	}

	// and this one should be price_list functionality?
	public function get_client_categories()
	{
		$ol = new object_list(array(
			'class_id' => CL_CRM_CATEGORY
		));
		
		$result = array();
		foreach ($ol->arr() as $id => $o)
		{
			$result[$id] = $o->name();
		}
		return $result;
	}

	// Should return the price list object which is used in import
	// It is definitely not the best way to that - I probably need to ask this info from warehouses config ...
	public function get_price_list()
	{
		$ol = new object_list(array(
			'class_id' => CL_SHOP_PRICE_LIST
		));
		return $ol->begin();
	}
}

?>
