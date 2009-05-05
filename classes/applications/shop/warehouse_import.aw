<?php
/*
@classinfo syslog_type=ST_WAREHOUSE_IMPORT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=smeedia
@tableinfo aw_warehouse_import master_index=brother_of master_table=objects index=aw_oid

@default table=aw_warehouse_import
@default group=general_general

	@property data_source type=select table=objects field=meta method=serialize
	@caption Andmeallikas

@default group=aw_warehouses 

	@property aw_warehouses_tb type=toolbar store=no no_caption=1
	@caption AW Laod toolbar

	@property aw_warehouses type=table store=no no_caption=1
	@caption AW Laod

@default group=import_warehouses 

	@property config_table type=table store=no no_caption=1
	@caption Seaded

property import type=text store=no
caption Import

reltype DATA_SOURCE value=1 clid=CL_TAKET_AFP_IMPORT
caption Andmeallikas

@reltype WAREHOUSE value=2 clid=CL_SHOP_WAREHOUSE
@caption Ladu

reltype PRODUCTS_FOLDER value=3 clid=CL_MENU
caption Lao toodete kaust

	@groupinfo general_general parent=general caption="&Uuml;ldine"
	@groupinfo aw_warehouses parent=general caption="AW Laod"
	@groupinfo import_warehouses parent=general caption="Imporditavad laod"

@groupinfo import_status caption="Importide staatus"

	@groupinfo product_status caption="Toodete import" parent=import_status
	@groupinfo product_prices caption="Toodete hinnad" parent=import_status
	@groupinfo product_amounts caption="Toodete laoseisud" parent=import_status
	@groupinfo pricelists caption="Hinnakirjad" parent=import_status
	@groupinfo customers caption="Kliendid" parent=import_status

@groupinfo import_timing caption="Importide ajastus"


*/

// types of import:
//	main product data
//	product prices
//	product amounts
//	price lists
//	customers

class warehouse_import extends class_base
{
	function warehouse_import()
	{
		$this->init(array(
			"tpldir" => "applications/shop/warehouse_import",
			"clid" => CL_WAREHOUSE_IMPORT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function _get_aw_warehouses($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'warehouse',
			'caption' => t('Ladu')
		));
		$t->define_field(array(
			'name' => 'products',
			'caption' => t('Tooted')
		));
		$t->define_field(array(
			'name' => 'amounts',
			'caption' => t('Laoseis')
		));
		$t->define_field(array(
			'name' => 'prices',
			'caption' => t('Hinnad')
		));
		$t->define_field(array(
			'name' => 'price_list',
			'caption' => t('Hinnakiri')
		));

		foreach ($arr['obj_inst']->get_warehouses() as $wh_id => $wh_name)
		{
			$t->define_data(array(
				'warehouse' => $wh_name
			));
		}

		return PROP_OK;
	}

	function _get_import($arr)
	{
		$links[] = html::href(array(
			'caption' => t('Hindade import'),
			'url' => $this->mk_my_orb('import_prices', array(
				'id' => $arr['obj_inst']->id(),
				'return_url' => get_ru()
			)),
		));

		$links[] = html::href(array(
			'caption' => t('Hinnakirja import'),
			'url' => $this->mk_my_orb('import_price_list', array(
				'id' => $arr['obj_inst']->id(),
				'return_url' => get_ru()
			)),
		));

		$arr['prop']['value'] = implode(', ', $links);

		return PROP_OK;
	}

	/**
		@attrib name=import_prices all_args=1
	**/
	function import_prices($arr)
	{
		if (!$this->can('view', $arr['id']))
		{
			exit($arr['id'].' is not readable');
		}
		$o = new object($arr['id']);
	}

	/**
		@attrib name=import_price_list all_args=1
	**/
	function import_price_list($arr)
	{
		if (!$this->can('view', $arr['id']))
		{
			exit($arr['id'].' is not readable');
		}

		$o = new object($arr['id']);

	// FIXME this is a very temporary thing here, cause terryf's prop changes remove the datasource reltype ...
	//	$ds = $o->get_first_obj_by_reltype('RELTYPE_DATA_SOURCE');
		$ol = new object_list(array(
			'class_id' => CL_TAKET_AFP_IMPORT
		));
		$ds = $ol->begin();

		// get the pricelist data as XML
		$xml_data = $ds->get_pricelist_data();	
		
		$xml = new SimpleXMLElement($xml_data);

		$price_list_obj = $o->get_price_list();
		// product categories ... 
		$product_categories = $o->get_product_categories();
		$client_categories = $o->get_client_categories();

		$price_list_matrix = $o->get_price_list_matrix($price_list_obj->id());

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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_warehouse_import(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => ""
				));
				return true;
		}
	}
}


interface warehouse_import_if
{
	const STATE_PREPARING = 1;
	const STATE_FETCHING = 2;
	const STATE_PROCESSING = 3;
	const STATE_WRITING = 4;

	public function get_warehouse_list();
}
?>
