<?php

class shop_order_cart_obj extends _int_object
{
	/**
		@attrib params=name

		@param product_packaging optional type=int/array acl=view
			The OID(s) of the product packagings
		@param product optional type=int/array acl=view
			The OID(s) of the product
		@param amount optional type=float default=1
			The amount of the product prices are asked for
		@param product_category optional type=array/int acl=view
			OIDs of product categories
		@param prices optional type=float
			The before prices by currencies
		@param bonuses optional type=float default=0
			The before points
		@param customer_data optional type=int acl=view
			OID of customer_data object
		@param customer_category optional type=array/int acl=view
			OIDs of customer categories.
		@param location optional type=array/int acl=view
			OIDs of locations

		@returns object_list of all valid delivery methods
	**/
	public function delivery_methods($arr = array())
	{
		enter_function("shop_order_center_obj::delivery_mehtods");
		if(is_object($o = $this->get_shop_order_center()))
		{
			$customer_data = $o->get_customer_data();
			if(is_object($customer_data))
			{
				$arr = array_merge(array(
					"customer_data" => $customer_data->id(),
					"customer_category" => $customer_data->get_customer_categories()->ids(),
					"location" => $customer_data->get_locations()->ids(),
				), $arr);
			}
		}
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

	public function get_shop_order_center()
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_ORDER_CENTER,
			"cart" => $this->id(),
			"lang_id" => array(),
			"site_id" => array(),
		));
		return $ol->begin();
	}

	function prop($prop)
	{
		switch($prop["name"])
		{
			case "cart_type":
				$oc = $this->get_oc();
				return $oc->prop("cart_type");
		}
		return parent::prop($prop);
	}

	private function set_oc()
	{
		if(empty($this->oc))
		{
			$ol = new object_list(array(
				"class_id" => CL_SHOP_ORDER_CENTER,
				"lang_id" => array(),
				"cart" => $this->id(),
			));
			$ids = $ol->ids();
			$this->oc = obj(reset($ids));
			if(!is_object($this->oc))
			{
				error::raise(array(
					"id" => "ERR_NO_WAREHOOS",
					"msg" => sprintf(t("shop_order_cart_obj::creat_osell_order(): no order_center set for cart %s!"), $this->id())
				));
			}
		}
	}

	/**	returns cart order center object
		@attrib api=1
	**/
	public function get_oc()
	{
		$this->set_oc();
		return $this->oc;
	}

	/**
		@attrib name=get_price api=1 params=pos
		@param product optional type
			The OID of the shop_order_center object the prices are asked for. If not given, no price list will be applied!
	**/
	public function get_prod_amount($product)
	{
		$cart = $this->get_cart();
		$items = $cart["items"];
		foreach($items as $prod => $val)
		{
			if($prod = $product)
			{
				return $val[0]["items"];
			}
		}
		return 0;
	}


	/**
		@attrib api=1
	**/
	public function get_cart()
	{
		if($this->prop("cart_type") == 1 && aw_global_get("uid") != "")
		{
			$user = obj(aw_global_get("uid_oid"));
			// well, it would be wise to syncronize the session aswell...
			$_SESSION["cart"] = $user->meta("shop_cart");
			return $user->meta("shop_cart");
		}
		else
		{
			return ifset($_SESSION, "cart");
		}
	}

	/**
		@attrib api=1
	**/
	public function set_cart($arr)
	{
		if($this->prop("cart_type") == 1 && aw_global_get("uid") != "")
		{
			$user = obj(aw_global_get("uid_oid"));
			$user->set_meta("shop_cart", $arr["cart"]);
			$user->save();
		}
		$_SESSION["cart"] = $arr["cart"];
	}

	/**
		@attrib api=1
	**/
	public function set_order_data($arr)
	{
		foreach($arr as $key => $val)
		{
			$_SESSION["cart"]["order_data"][$key] = $arr[$key];
			$_SESSION["cart"]["user_data"][$key] = $arr[$key];//ajutiselt, et vanemates funktsioonides need muutujad ka sisse saaks
		}
	}

	/**
		@attrib api=1
	**/
	public function get_order_data()
	{
		return $_SESSION["cart"]["order_data"];
	}

	/** makes new warehouse sell order object
		@attrib name=get_price api=1
	**/
	public function create_sell_order()
	{
		$this->set_oc();
		if (!is_oid($this->oc->prop("warehouse")))
		{
			error::raise(array(
				"id" => "ERR_NO_WAREHOOS",
				"msg" => sprintf(t("shop_order_cart::do_creat_order_from_cart(): no warehouse set for ordering center %s!"), $this->oc->id())
			));
		}
		$warehouse = $oc->prop("warehouse");

		$params["postal_price"] = $this->prop("postal_price");
		$cart = $this->get_cart();

		$o = new object();
		$o->set_name(t("M&uuml;&uuml;gitellimus")." ".date("d.m.Y H:i"));
		$o->set_parent($oc->id());
		$o->set_class_id(CL_SHOP_SELL_ORDER);
		$o->save();
		$awa = new aw_array($cart["items"]);
		foreach($awa->get() as $iid => $quant)
		{
			$qu = new aw_array($quant);
			foreach($qu->get() as $key => $val)
			{
				if($val["cart"] && !$this->check_confirm_carts($val["cart"]))
				{
					continue;
				}
				$o->add_row(array(
					"product" => $iid,
					"amount" => $cart["items"][$iid][$key],
				));
			}
		}		
		return $rval;
	}

	public function remove_product($product)
	{
/*Array
(
    [items] => Array
        (
            [359296] => Array
                (
                    [0] => Array
                        (
                            [items] => 4
                        )

                )

            [359294] => Array
                (
                    [0] => Array
                        (
                            [items] => 11
                        )

                )

        )

)*/
		$cart = $this->get_cart();
		if(isset($cart["items"][$product]))
		{
			unset($cart["items"][$product]);
		}
		$this->set_cart($cart);
	}
}

?>