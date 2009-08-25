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
		@param product_packet optional type=array/int acl=view
			OIDs of product packets
		@param customer_data optional type=int acl=view
			OID of customer_data object
		@param customer_category optional type=array/int acl=view
			OIDs of customer categories.
		@param location optional type=array/int acl=view
			OIDs of locations
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
			new obj_predicate_sort(array(
				"jrk" => "ASC"
			)),
		));
		// Validate
		foreach($ol->arr() as $o)
		{
			if(!$o->valid($arr))
			{
				$ol->remove($o->id());
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

	public function set_oc()
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
	public function set_cart($cart)
	{
		if($this->prop("cart_type") == 1 && aw_global_get("uid") != "")
		{
			$user = obj(aw_global_get("uid_oid"));
			$user->set_meta("shop_cart", $cart);
			$user->save();
		}
		$_SESSION["cart"] = $cart;
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


	/** resets cart
		@attrib api=1
	**/
	public function reset_cart()
	{
		if(!empty($_SESSION["cart"]))
		{
			unset($_SESSION["cart"]);
		}
	}

	/**
		@attrib api=1
	**/
	public function get_order_data()
	{
		return empty($_SESSION["cart"]["order_data"]) ? array() : $_SESSION["cart"]["order_data"];
	}

	/** makes new warehouse sell order object
		@attrib api=1
	**/
	public function create_order()
	{
		$this->set_oc();
		if (!is_oid($this->oc->prop("warehouse")))
		{
			error::raise(array(
				"id" => "ERR_NO_WAREHOOS",
				"msg" => sprintf(t("shop_order_cart::do_creat_order_from_cart(): no warehouse set for ordering center %s!"), $this->oc->id())
			));
		}
		$warehouse = $this->oc->prop("warehouse");
		$cart = $this->get_cart();
		$order_data = $this->get_order_data();
//arr($order_data);die();
		$o = new object();
		$o->set_name(t("M&uuml;&uuml;gitellimus")." ".date("d.m.Y H:i"));
		$o->set_parent($this->oc->id());
		$o->set_class_id(CL_SHOP_SELL_ORDER);
		$o->set_prop("warehouse" , $warehouse);
		$o->set_prop("date" , time());

		$person = $this->_get_person($order_data);
		$o->set_prop("purchaser" , $person->id());
		$o->set_prop("buyer_rep" , $person->id());
		$address = $this->_get_address($order_data);
		$o->set_prop("delivery_address" , $address->id());
		$o->set_prop("transp_type" , $order_data["delivery"]);
		$o->set_prop("payment_type" , $order_data["payment"]);
		$o->set_prop("currency" , $this->oc->get_currency());
		$o->set_prop("channel" , $this->prop("channel"));
		$o->save();

		$awa = new aw_array($cart["items"]);
		foreach($awa->get() as $iid => $quant)
		{
			$qu = new aw_array($quant);
			foreach($qu->get() as $key => $val)
			{
				if($val["cart"] && !$this->check_confirm_carts($val["cart"]) && $iid)
				{
					continue;
				}
				$product = obj($iid);
				$o->add_row(array(
					"product" => $iid,
					"amount" => $cart["items"][$iid][$key]["items"],
					"price" => $product->get_shop_price($this->oc->id()),
				));
			}
		}
		return $o->id();
	}

	public function _get_person($data)
	{

		$person = "";
		//sellisel juhul otsib olemasolevate isikute hulgast, kui on andmeid mille j2rgi otsida
		if(!empty($data["personalcode"]) || !empty($data["customer_no"]) || (!empty($data["birthday"]) && !empty($data["lastname"])))
		{
			$filter = array(
				"class_id" => CL_CRM_PERSON,
				"site_id" => array(),
				"lang_id" => array(),
			);
			if(!empty($data["personalcode"]))
			{
				$filter["personal_id"] = $data["personalcode"];
			}
			if(!empty($data["customer_no"]))
			{
				$filter["external_id"] = $data["customer_no"];
			}
			else
			{
				if(!empty($data["firstname"]))
				{
					$filter["firstname"] = $data["firstname"];
				}
				if(!empty($data["lastname"]))
				{
					$filter["lastname"] = $data["lastname"];
				}
				if(!empty($data["birthday"]))
				{
					if(is_array($data["birthday"]))
					{
						$filter["birthday"] = mktime(0,0,0,$data["birthday"]["month"],$data["birthday"]["day"],$data["birthday"]["year"]);
					}
				}
			}
			$ol = new object_list($filter);

			if($ol->count())
			{
				$person = $ol->begin();
			}
		}
		if(!is_object($person))
		{
			$person = new object();
			$person->set_class_id(CL_CRM_PERSON);
			$person->set_parent($this->oc->id());
			$person->set_name($data["firstname"]." ".$data["lastname"]);
			$person->set_prop("firstname" , $data["firstname"]);
			$person->set_prop("lastname" , $data["lastname"]);
			if(!empty($data["personalcode"]))
			{
				$person->set_prop("personal_id" , $data["personalcode"]);
			}
			if(!empty($data["birthday"]))
			{
				if(is_array($data["birthday"]))
				{
					$person->set_prop("birthday" , mktime(0,0,0,$data["birthday"]["month"],$data["birthday"]["day"],$data["birthday"]["year"]));
					//peaks selle ka salvestama
				}
			}
			if(!empty($data["customer_no"]))
			{
				$person->set_prop("external_id" , $data["customer_no"]);
			}
			$person->save();
			if(!empty($data["email"]))
			{
				$person->set_email($data["email"]);
			}
			if(!empty($data["mobilephone"]))
			{
				$person->set_phone($data["mobilephone"]);
			}
			if(!empty($data["homephone"]))
			{
				$person->set_phone($data["homephone"], "home");
			}
			if(!empty($data["workphone"]))
			{
				$person->set_phone($data["workphone"], "work");
			}

		}
		return $person;
	}

	public function _get_address($data)
	{
		$address = new object();
		$address->set_parent($this->oc->id());
		$address->set_name($data["address"]." ".$data["city"]);
		$address->set_class_id(CL_CRM_ADDRESS);
		$address->set_prop("aadress" ,$data["address"]);
		if(!empty($data["index"]))
		{
			$address->set_prop("postiindeks",$data["index"]);
		}
		$address->save();
		if(!empty($data["city"]))
		{
			$address->set_city($data["city"]);
		}
		return $address;
	}

	public function confirm_order()
	{
		$order = $this->create_order();
		$this->reset_cart();
		$this->set_oc();
		$order_obj = obj($order);
		$order_obj->set_prop("order_status" , "5");
		$order_obj->save();
		$this->oc->send_confirm_mail($order);
		return $order;
	}

	public function remove_product($product)
	{
		$cart = $this->get_cart();
		if(isset($cart["items"][$product]))
		{
			$cart["items"][$product] = null;
		}
		$this->set_cart($cart);
	}


}

?>