<?php

class shop_order_center_obj extends _int_object
{
	function filter_get_fields()
	{
		$class_filter_fields = $this->meta("class_filter_fields");
		$prop_filter_fields = $this->meta("prop_filter_fields");

		$rv = array();
		$ic_fields = $this->get_integration_class_instance()->get_filterable_fields();
		foreach(safe_array($class_filter_fields) as $field_name => $one)
		{
			if ($one == 1)
			{
				$rv["ic::".$field_name] = $ic_fields[$field_name];
			}
		}

		$prod_props = obj()->set_class_id(CL_SHOP_PRODUCT)->get_property_list();
		foreach(safe_array($prop_filter_fields) as $field_name => $one)
		{
			if ($one == 1)
			{
				$rv["prod::".$field_name] = $prod_props[$field_name]["caption"];
			}
		}
		return $rv;
	}
	
	function filter_get_all_values($filter_name)
	{
		list($type, $field_name) = explode("::",$filter_name);

		if ($type == "ic")
		{
			$inst = $this->get_integration_class_instance();
			return $inst->get_all_filter_values($field_name);
		}
		else
		if ($type == "prod")
		{
			$rv = array();
			$odl = new object_data_list(
				array(
					"lang_id" => array(),
					"site_id" => array(),
					"class_id" => CL_SHOP_PRODUCT,
					"price" => new obj_predicate_not(-1)//see ainult selleks, et toodete tabeli sisse loeks
				),
				array(
					CL_SHOP_PRODUCT => array(new obj_sql_func(OBJ_SQL_UNIQUE, "value", $field_name))
				)
			);
			foreach($odl->arr() as $od)
			{
				$rv[$od["value"]] = $od["value"];
			}
			return $rv;
		}

		return array();
	}

	function get_integration_class_instance()
	{
		if (!is_class_id($ic = $this->prop("integration_class")))
		{
			return null;
		}

		$clss = aw_ini_get("classes");
		return get_instance($clss[$ic]["file"]);
	}

	function filter_set_active_by_folder($data)
	{
		$this->set_meta("filter_by_folder", $data);
	}

	function filter_get_active_by_folder($folder_id)
	{
		$fbf = safe_array($this->meta("filter_by_folder"));
		if (is_oid($fbf[$folder_id]) && $GLOBALS["object_loader"]->can("view", $fbf[$folder_id]))
		{
			return $fbf[$folder_id];
		}
		foreach(obj($folder_id)->path(array("full_path" => 1)) as $path_item)
		{
			if (is_oid($fbf[$path_item->id()]) && $GLOBALS["object_loader"]->can("view", $fbf[$path_item->id()]))
			{
				return $fbf[$path_item->id()];
			}
		}
		return null;
	}

	/**
		Returns array of all possible rent periods
	**/
	public function rent_periods()
	{
		$periods = array();

		for($i = $this->prop("rent_months_min"); $i <= $this->prop("rent_months_max"); $i += $this->prop("rent_months_step"))
		{
			$periods[$i] = $i;
		}

		return $periods;
	}

	/**
		@attrib params=name

		@param core_sum required type=float

		@param rent_period required type=int

		@param precision optional type=int default=2
	**/
	public function calculate_rent($core_sum, $period, $precision = 2)
	{
		if($core_sum < $this->prop("rent_min_amt"))
		{
			return array(
				"error" => sprintf(t("Minimaalne lubatud summa j&auml;relmaksuks on %s!"), $this->prop("rent_min_amt")),
			);
		}

		if($core_sum > $this->prop("rent_max_amt"))
		{
			return array(
				"error" => sprintf(t("Maksimaalne lubatud summa j&auml;relmaksuks on %s!"), $this->prop("rent_max_amt")),
			);
		}

		if(!in_array($period, $this->rent_periods()))
		{
			return array(
				"error" => t("Valitud j&auml;relmaksuperiood ei ole lubatud!"),
			);
		}

		return array(
			"prepayment" => $prepayment = round($core_sum * $this->prop("rent_prepayment_interest") / 100, $precision),
			"single_payment" => $single_payment = round(max($core_sum - $prepayment, 0) * (1 + $this->prop("rent_interest") / 12 / 100 * $period) / $period, $precision),
			"sum_rent" => $single_payment * $period + $prepayment,
		);
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
		@comment DOES NOT WORK YET!! STORAGE IS BROKEN!!
	**/
	public function get_customer_data()
	{
		$ol = new object_list(array(
			"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
			"buyer" => array(),
			"seller.RELTYPE_MANAGER_CO(CL_SHOP_WAREHOUSE_CONFIG).RELTYPE_ORDER_CENTER(CL_SHOP_WAREHOUSE).RELTYPE_WAREHOUSE(CL_SHOP_ORDER_CENTER)" => $this->id(),
			"lang_id" => array(),
			"site_id" => array(),
		));
	}
}