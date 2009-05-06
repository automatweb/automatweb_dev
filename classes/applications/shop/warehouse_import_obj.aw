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

	public function update_price_list()
	{
		$ol = new object_list(array(
			'class_id' => CL_TAKET_AFP_IMPORT
		));
		$ds = $ol->begin();

		// get the pricelist data as XML
		$xml_data = $ds->get_pricelist_data();	
		
		$xml = new SimpleXMLElement($xml_data);

		$price_list_obj = $this->get_price_list();
		// product categories ... 
		$product_categories = $this->get_product_categories();
		$client_categories = $this->get_client_categories();

		$price_list_matrix = $this->get_price_list_matrix($price_list_obj->id());

		foreach ($xml->product_category as $cat)
		{
			// need to create product_category
			$prod_cat_oid = array_search($cat->name, $product_categories);
			if ($prod_cat_oid === false)
			{
				$prod_cat = new object();
				$prod_cat->set_name($cat->name);
				$prod_cat->set_class_id(CL_SHOP_PRODUCT_CATEGORY);
				$prod_cat->set_parent($price_list_obj->id());
				$prod_cat_oid = $prod_cat->save();

				$product_categories[$prod_cat_oid] = (string)$cat->name;

				echo "Add new product category ".$cat->name."<br />\n";
			}

			if ( !$price_list_obj->is_connected_to(array('to' => $prod_cat_oid)) )
			{
				$price_list_obj->connect(array(
					"type" => "RELTYPE_MATRIX_CATEGORY",
					"to" => $prod_cat_oid,
				));
			}

			foreach ($cat->client_category as $client)
			{
				$client_cat_oid = array_search($client->name, $client_categories);
				if ($client_cat_oid === false)
				{
					$client_cat = new object();
					$client_cat->set_name($client->name);
					$client_cat->set_class_id(CL_CRM_CATEGORY);
					$client_cat->set_parent($price_list_obj->id());
					$client_cat_oid = $client_cat->save();

					$client_categories[$client_cat_oid] = (string)$client->name;
					
					echo "Add new client category ".$client->name."<br />\n";
				}

				if ( !$price_list_obj->is_connected_to(array('to' => $client_cat_oid)) )
				{
					$price_list_obj->connect(array(
						"type" => "RELTYPE_MATRIX_ORG_CAT",
						"to" => $client_cat_oid,
					));
				}

				$discount = (string)$client->value;

				// checking CL_SHOP_PRICELIST_CUSTOMER_DISCOUNT
				if($oid = $price_list_matrix[$prod_cat_oid][$client_cat_oid])
				{
					$cust_disc_o = obj($oid);
					$cust_disc_o->set_prop("discount", $discount);
					$cust_disc_o->save();
				}
				else
				{
					$cust_disc_o = obj();
					$cust_disc_o->set_class_id(CL_SHOP_PRICE_LIST_CUSTOMER_DISCOUNT);
					$cust_disc_o->set_name(sprintf(t("%s kliendigrupi allahindlus"), $price_list_obj->name()));
					$cust_disc_o->set_parent($price_list_obj->id());
					$cust_disc_o->set_prop("pricelist", $price_list_obj->id());
					$cust_disc_o->set_prop("crm_category", $client_cat_oid);
					$cust_disc_o->set_prop("prod_category", $prod_cat_oid);
					$cust_disc_o->set_prop("discount", $discount);
					$cust_disc_o->save();
				}
			}
		}
		
	}

	public function clear_price_list()
	{
		$price_list = $this->get_price_list();
		$matrix = $this->get_price_list_matrix($price_list->id());
		$to_delete = array();

		foreach ($matrix as $prod_cat_oid => $clients)
		{
			$to_delete[$prod_cat_oid] = $prod_cat_oid;
			foreach ($clients as $client_cat_oid => $customer_discount_oid)
			{
				$to_delete[$client_cat_oid] = $client_cat_oid;
				$to_delete[$customer_discount_oid] = $customer_discount_oid;
			}
		}

		foreach($to_delete as $oid)
		{
			try {
			$o = new object($oid);
			$o->delete(true);
			} catch (Exception $e) {}
		}
	
	}
}

?>
