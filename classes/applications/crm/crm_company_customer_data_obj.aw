<?php

class crm_company_customer_data_obj extends _int_object
{
	const SALESSTATE_NEW = 1;
	const SALESSTATE_LEAD = 2;
	const SALESSTATE_NEWCALL = 3;
	const SALESSTATE_PRESENTATION = 4;
	const SALESSTATE_SALE = 5;
	const SALESSTATE_REFUSED = 6;

	private static $sales_state_names = array();

	public function get_discounts()
	{
		return discount_obj::get_discounts();
	}

	public function awobj_set_sales_state($state)
	{
		$states = self::sales_state_names();
		if (!isset($states[$state]))
		{
			throw new awex_co_cust_data_sales_state("Not a valid sales state: '{$state}'");
		}
		$this->set_prop("sales_state", $state);
	}

	/** Customer's sales state names or name
	@attrib api=1 params=pos
	@param state optional type=int
		State for which to get name. One of SALESSTATE constant values.
	@comment
	@returns mixed
		Array of constant values (keys) and names (array values) if $state parameter not specified. String name corresponding to that state if $state parameter given. Names are in currently active language. Empty string if invalid state parameter given.
	**/
	public static function sales_state_names($state = null)
	{
		if (empty(self::$sales_state_names))
		{
			self::$sales_state_names = array(
				self::SALESSTATE_NEW => t("Uus"),
				self::SALESSTATE_LEAD => t("Soovitus"),
				self::SALESSTATE_NEWCALL => t("Uus k&otilde;ne"),
				self::SALESSTATE_PRESENTATION => t("Presentatsioon"),
				self::SALESSTATE_SALE => t("Ostja"),
				self::SALESSTATE_REFUSED => t("Keeldund kontaktist")
			);
		}

		if (!isset($state))
		{
			$names = self::$sales_state_names;
		}
		elseif (is_scalar($state) and isset(self::$sales_state_names[$state]))
		{
			$names = self::$sales_state_names[$state];
		}
		else
		{
			$names = "";
		}

		return $names;
	}

	public function get_bills()
	{
		$filter = array(
			"class_id" => CL_CRM_BILL,
			"site_id" => array(),
			"lang_id" => array(),
			"customer" => $this->prop("buyer"),
			"CL_CRM_BILL.RELTYPE_IMPL" => $this->prop("seller")
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


	public function save($exclusive = false, $previous_state = null)
	{
		if ((!$this->prop("sales_state") or self::SALESSTATE_NEW == $this->prop("sales_state")) and is_oid($this->prop("sales_lead_source")))
		{
			$this->awobj_set_sales_state(self::SALESSTATE_LEAD);
		}
		$r = parent::save($exclusive, $previous_state);
	}
}

class awex_co_cust_data extends awex_obj {}
class awex_co_cust_data_sales_state extends awex_co_cust_data {}

?>
