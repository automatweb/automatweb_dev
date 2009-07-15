<?php

class crm_company_customer_data_obj extends _int_object
{

	public function get_discounts()
	{
		return discount_obj::get_discounts();
	}

	public function get_bills()
	{
		$filter = array(
			"class_id" => CL_CRM_BILL,
			"site_id" => array(),
			"lang_id" => array(),
			"customer" => $this->prop("buyer"),
			"CL_CRM_BILL.RELTYPE_IMPL" => $this->prop("seller"),
		);
		return new object_list($filter);
	}

	public function get_price_lists()
	{
		$filter = array(
			"class_id" => CL_SHOP_PRICE_LIST,
			"site_id" => array(),
			"lang_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_SHOP_PRICE_LIST.RELTYPE_ORG" => $this->prop("buyer"),
					"CL_SHOP_PRICE_LIST.RELTYPE_PERSON" => $this->prop("buyer"),
				),
			))
		);
		return new object_list($filter);
	}

	public function get_orders()
	{
		$filter = array(
			"class_id" => CL_SHOP_ORDER,
			"site_id" => array(),
			"lang_id" => array(),
			"orderer_company" => $this->prop("buyer"),
			"seller_company" => $this->prop("seller"),
		);
		return new object_list($filter);
	}

	public function get_sell_orders()
	{
		$filter = array(
			"class_id" => CL_SHOP_SELL_ORDER,
			"site_id" => array(),
			"lang_id" => array(),
			"purchaser" => $this->prop("buyer"),
//			"seller_company" => $this->prop("seller"),
		);
		return new object_list($filter);
	}

	public function get_recalls()
	{
		$filter = array(
			"class_id" => CL_SHOP_ORDER,
			"site_id" => array(),
			"lang_id" => array(),
		);
		return new object_list($filter);
	}

	public function get_delivery_notes()
	{
		$filter = array(
			"class_id" => CL_SHOP_DELIVERY_NOTE,
			"site_id" => array(),
			"lang_id" => array(),
			"customer" => $this->prop("buyer"),
			"impl" => $this->prop("seller"),
		);
		return new object_list($filter);
	}

}

?>
