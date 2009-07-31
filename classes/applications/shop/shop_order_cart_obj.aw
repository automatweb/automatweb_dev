<?php

class shop_order_cart_obj extends _int_object
{
	/**
		@attrib name=price params=name

		@param product required type=int acl=view
			The OID of the product
		@param amount optional type=float default=1
			The amount of the product prices are asked for
		@param product_category optional type=array/int acl=view
			OIDs of product categories
		@param customer_data optional type=int acl=view
			OID of customer_data object
		@param customer_category optional type=array/int acl=view
			OIDs of customer categories.
		@param location optional type=array/int acl=view
			OIDs of locations
		@param structure optional type=bool default=false
			If set, the structure of the prices will be returned, otherwise only the final prices will be returned.
	**/
	public function delivery_methods($arr = array())
	{
		enter_function("shop_order_center_obj::delivery_mehtods");
		$ol = new object_list(array(
			"class_id" => CL_SHOP_DELIVERY_METHOD,
			"CL_SHOP_DELIVERY_METHOD.RELTYPE_DELIVERY_METHOD(CL_SHOP_ORDER_CART)" => $this->id(),
			"lang_id" => array(),
			"site_id" => array(),
		));
		// Validate
		if(!empty($arr["product"]) || !empty($arr["product_category"]) || !empty($arr["customer_data"]) || !empty($arr["customer_category"]) || !empty($arr["location"]))
		{
			foreach($ol->arr() as $o)
			{
				if(!$o->valid($arr))
				{
					$ol->remove($o->id());
				}
			}
		}
		exit_function("shop_order_center_obj::delivery_mehtods");
		return $ol;
	}
}

?>