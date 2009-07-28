<?php

class shop_delivery_method_obj extends _int_object
{
	public function prop($k)
	{
		switch($k)
		{
			case "price":
				return aw_math_calc::string2float(parent::prop($k));

			default:
				return parent::prop($k);
		}
	}

	/**
		@attrib name=get_valid_delivery_methods api=1 params=name

		@param product_categories optional type=array acl=view
			Array of OIDs of product category objects.
		@param customer_categories optional type=array acl=view
			Array of OIDs of customer category objects.
		@param object_data_list optional type=bool default=false
			If true, object_data_list will be returned instead of object_list.

		@returns
			Object_(data_)list containing delivery_method objects that match the whole criteria.

	**/
	public static function get_valid_delivery_methods($arr = array())
	{
		/*
		$params = array(
			"class_id" => CL_SHOP_DELIVERY_METHOD,
			"lang_id" => array(),
			"site_id" => array(),
		);

		if(!empty($arr["product_categories"]))
		{
			$params["CL_SHOP_DELIVERY_METHOD.RELTYPE_DELIVERY_METHOD(CL_SHOP_DELIVERY_METHOD_CONDITIONS).row"] = $arr["product_categories"];
		}
		
		if()
		{
			$params["CL_SHOP_DELIVERY_METHOD.RELTYPE_DELIVERY_METHOD(CL_SHOP_DELIVERY_METHOD_CONDITIONS).row"] = $arr["product_categories"];
		}

		if(empty($arr["object_data_list"]))
		{
			$ol = new object_data_list($params, array(
				CL_SHOP_DELIVERY_METHOD => array("name", "type", "price"),
			));
		}
		else
		{
			$ol = new object_list($params);
		}

		return $ol;
		*/
	}
}

?>
