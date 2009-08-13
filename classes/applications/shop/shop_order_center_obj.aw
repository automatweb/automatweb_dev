<?php

class shop_order_center_obj extends _int_object
{
	/**
		@attrib api=1

		@param sum required type=float
			The total sum of products/packagings
		@param currency required type=int
			The OID of currency
		@param product optional type=array/int acl=view
			OIDs of products
		@param product_packaging optional type=array/int acl=view
			OIDs of products
		@param product_category optional type=array/int acl=view
			OIDs of product categories
		@param customer_data optional type=int acl=view
			OID of customer_data object
		@param customer_category optional type=array/int acl=view
			OIDs of customer categories.
		@param location optional type=array/int acl=view
			OIDs of locations

		@returns OID of rent_conditions object or NULL if none found
	**/
	public function get_rent_conditions($arr)
	{
		$customer_data = $this->get_customer_data();
		if(is_object($customer_data))
		{
			$arr = array_merge(array(
				"customer_data" => $customer_data->id(),
				"customer_category" => $customer_data->get_customer_categories()->ids(),
				"location" => $customer_data->get_locations()->ids(),
			), $arr);
		}
		return is_oid($id = $this->prop("rent_configuration")) ? obj($id)->valid_conditions($arr) : NULL;
	}

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
		@comment
	**/
	public function get_customer_data()
	{
		if(!is_oid($this->prop("warehouse.conf.owner")))
		{
			return FALSE;
		}
		else
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_COMPANY_CUSTOMER_DATA,
				"buyer" => array(user::get_current_company(), user::get_current_person()),
				"seller" => $this->prop("warehouse.conf.owner"),
				"lang_id" => array(),
				"site_id" => array(),
				new obj_predicate_limit(1),
			));
			return $ol->count() > 0 ? $ol->begin() : FALSE;
		}
	}

	public function get_product_show_obj($menu, $make_new = false)
	{
		$o = "";
		$docs = new object_list(array(
			"class_id" => CL_DOCUMENT,
			"parent" => $menu,
			"lang_id" => array(),
		));
		$doc = $docs->count() ? $docs->begin() : "";
		if(!is_object($doc) && $make_new)
		{
			$doc = new object();
			$doc->set_class_id(CL_DOCUMENT);
			$doc->set_parent($menu);
			$doc->set_name($menu);
			$doc->save();
		}
		if(is_object($doc))
		{
			foreach($doc->connections_from(array("to.class_id" => CL_PRODUCTS_SHOW)) as $c)
			{
				$o = $c->to();
				break;
			}
			if(!is_object($o) && $make_new)
			{
				$o = new object();
				$o->set_class_id(CL_PRODUCTS_SHOW);
				$o->set_parent($menu);
				$o->set_name($menu." ".t("toodete n&auml;itamine"));
				$o->set_prop("oc" , $this->id());
				$o->save();
				$doc->set_prop("content" , $doc->prop("content")."#show_products1#");
				$doc->save();
				$doc->connect(array(
					"type" => "RELTYPE_ALIAS",
					"to" => $o->id(),
				));
			}
		}
		return $o;
	}



}