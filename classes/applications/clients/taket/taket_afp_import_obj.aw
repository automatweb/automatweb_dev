<?php

class taket_afp_import_obj extends _int_object
{
	private $prod_fld;
	private $org_fld;

	private $warehouse; // ???
	private $warehouses = array();

	// short code controller
	private $controller_inst;
	private $controller_id;

	// this is a cache class, i use it to make db queries
	private $db_obj; 

	private function init_vars()
	{
		// ERROR REPORTING
		automatweb::$instance->mode(automatweb::MODE_DBG);

		// this will take some time
		aw_set_exec_time(AW_LONG_PROCESS);

		// controller for short product codes ...
		if($this->controller_id = $this->prop("code_ctrl"))
		{
			$this->controller_inst = get_instance(CL_CFGCONTROLLER);	
		}

		$this->db_obj = $GLOBALS["object_loader"]->cache;
		
		$this->warehouses = $this->get_warehouses();

		$this->prod_fld = $this->get_products_folder();
		$this->org_fld = $this->get_suppliers_folder();
	}

	private function download_amounts_file()
	{
		$warehouses = $this->get_warehouses();

		$this->print_line("Download amounts files ... ");
		// download amounts files
		foreach ($warehouses as $oid => $name)
		{
			$o = new object($oid);
			$adr = $o->comment();

			$download_urls[$oid] = $adr."/amounts.csv";
		}

		$res = $this->parallel_url_fetch($download_urls);

		foreach ($res as $oid => $v)
		{
			$filename = aw_ini_get('site_basedir').'/files/amounts_'.$oid.'.csv';
			file_put_contents($filename, $v);
			$this->print_line("saved file: ".$filename);
		}
	}

	public function get_suppliers()
	{
		$ol = new object_list(array(
			'class_id' => CL_CRM_COMPANY,
			'parent' => $this->org_fld
		));

		return $ol->arr();
	}

	private function update_suppliers($suppliers)
	{
		$ol = new object_list(array(
			'class_id' => CL_CRM_COMPANY,
			'code' => array_keys($suppliers)
		));

		// update existing supplier organisation objects:
		foreach ($ol->arr() as $oid => $o)
		{
			echo "Update supplier obj (".$oid.") <strong>".$o->name()."</strong> ";
			if ($o->name() != $suppliers[$o->prop('code')])
			{
				echo "set to <strong>".$suppliers[$o->prop('code')]."</strong> ";

				$o->set_name($suppliers[$o->prop('code')]);
				$o->save();
			}
			echo "code: [".$o->prop('code')."]<br />\n";
			unset($suppliers[$o->prop('code')]);
		}

		// remaining suppliers are not present in aw, so lets add them:
		foreach ($suppliers as $id => $name)
		{
			echo "Create new organisation (".$id.") - <strong>".$name."</strong> ... ";
			$org = obj();
			$org->set_class_id(CL_CRM_COMPANY);
			$org->set_name($name);
			$org->set_parent($this->org_fld);
			$org->set_prop("code", $id);
			$org->save();
			echo "[ok]<br />\n";
		}
		echo "Suppliers updated<br />\n";
	}

	// obsolete
	private function add_product($product)
	{
		$code = urldecode($product["product_code"]);

		$o = $product['oid'];

		if(!$o)
		{
			$o = obj();
			$o->set_class_id(CL_SHOP_PRODUCT);
			$o->set_parent($this->prod_fld);
			$o->save();

			$o->connect(array(
				"type" => "RELTYPE_WAREHOUSE",
				"to" => $this->warehouse,
			));

			$org_id = urldecode($product["supplier_id"]);
			$ol = new object_list(array(
				"class_id" => CL_CRM_COMPANY,
				"site_id" => array(),
				"lang_id" => array(),
				"code" => $org_id,
			));
			$org = $ol->begin();

			if(!$org)
			{
				$org = obj();
				$org->set_class_id(CL_CRM_COMPANY);
				$org->set_name(urldecode($product["supplier_name"]));
				$org->set_parent($this->org_fld);
				$org->set_prop("code", $org_id);
				$org->save();
			}

			for($i = 0; $i < 6; $i++)
			{
				$p_o = obj();
				$p_o->set_class_id(CL_SHOP_PRODUCT_PURVEYANCE);
				$p_o->set_parent($o->id());
				$p_o->set_name(sprintf(t("%s tarnetingimus"), $o->name()));
				$p_o->set_prop("warehouse", $this->whs[$i]);
				$p_o->set_prop("company", $org->id());
				$p_o->set_prop("product", $o->id());
				$p_o->save();
			}

			echo $k.' -- '.urldecode($v['product_name']).' ('.$code.') created ...<br>'."\n";
		}
		else
		{
			echo $k.' -- '.urldecode($v['product_name']).' ('.$code.') already existed ...<br>'."\n";
			$o = new object($o);
		}

		$o->set_name(urldecode($product["product_name"]));

		$o->set_prop("code", $code);

		if($ctrli)
		{
			$short_code = $ctrli->check_property($cid, null, $code, null, null, null);
			if ($o->prop('short_code') != $short_code)
			{
				$o->set_prop("short_code", $short_code);
				$changed = true;
			}
		}
		$o->set_prop("search_term", urldecode($product["search_term"]));
		$o->set_prop("user1", urldecode($product["replacement_product_code"]));
		$o->save();
	}

	// I'll assume here, that the product object doesn't exist in aw
	// So I need to add a line in objects table, aw_shop_products table and make those purveyance objects
	private function add_product_sql($data)
	{
		$obj_base = $this->db_obj->db_fetch_array("select * from objects where class_id = '".CL_SHOP_PRODUCT."' limit 1");
		$obj_base = reset($obj_base);
		$obj_base['oid'] = 0;
		$obj_base['createdby'] = '110';
		$obj_base['modifiedby'] = '110';

		$obj_base['name'] = addslashes($data['product_name']);
		$obj_base['comment'] = addslashes($data['product_code']);
		$sql = "
			INSERT INTO 
				objects 
			VALUES (".implode(',', map('"%s"', $obj_base)).");
		";

		$this->db_obj->db_query($sql);

		$oid = $this->db_obj->db_last_insert_id();

		// brother_of value has to be the same as oid
		$this->db_obj->db_query("UPDATE objects set brother_of = ".$oid." WHERE oid = ".$oid);

		$sql = "
			INSERT INTO 
				aw_shop_products 
			SET
				aw_oid = ".$oid.",
				code = '".addslashes($data['product_code'])."',
				search_term = '".addslashes($data['search_term'])."',
				type_code = '".addslashes($data['replacement_product_code'])."',
				short_code = '".$this->apply_controller($data['product_code'])."'
		";
		$this->db_obj->db_query($sql);
		return $oid;
	}

	// the product object exists in the system, so i need to update the data
	private function update_product_sql($oid, $data)
	{
		$sql = "
			UPDATE 
				objects
			SET
				name = '".addslashes($data['product_name'])."',
				comment = '".addslashes($data['product_code'])."',
				brother_of = ".$oid."
			WHERE
				oid = ".$oid."
		";
		$this->db_obj->db_query($sql);

		$sql = "
			UPDATE
				aw_shop_products
			SET
				code = '".addslashes($data['product_code'])."',
				search_term = '".addslashes($data['search_term'])."',
				type_code = '".addslashes($data['replacement_product_code'])."',
				short_code = '".$this->apply_controller($data['product_code'])."'
			WHERE
				aw_oid = ".$oid."
		";
		$this->db_obj->db_query($sql);
	}

	// maybe i don't have to make this one with sql-s?
	private function delete_product_sql($oid)
	{
		$sql = "DELETE FROM objects WHERE oid = ".$oid;
		$this->db_obj->db_query($sql);

		$sql = "DELETE FROM aw_shop_products WHERE aw_oid = ".$oid;
		$this->db_obj->db_query($sql);
	}


	private function update_purveyances($data)
	{
		$sql = "
			SELECT
				oid,
				name,
				warehouse,
				company,
				product
			FROM
				objects LEFT JOIN aw_shop_product_purveyance ON (oid = aw_oid)
			WHERE
				parent = ".$data['product_oid']." and
				class_id = '".CL_SHOP_PRODUCT_PURVEYANCE."'
		";
		$res = $this->db_obj->db_fetch_array($sql);

		$purveyances = array();
		foreach ($res as $v)
		{
			$purveyances[$v['warehouse']] = $v;
		}
		$wh_counter = 1;
		foreach ($this->warehouses as $wh_oid => $wh)
		{
			if (!isset($purveyances[$wh_oid]))
			{
				$this->add_purveyance_sql(array(
					'parent' => $data['product_oid'],
					'product_oid' => $data['product_oid'],
					'product_name' => $data['product_name'],
					'warehouse_oid' => $wh_oid,
					'supplier_oid' => 0
				));
				echo "[ ".$wh_counter." ] Added purveyance: ".$data['product_name']." => ".$wh."<br />\n";
			}
			else
			{
			//	$this->update_purveyance_sql($purveyances[$wh_oid]['oid'], array(
			//		'parent' => $data['product_oid'],
			//		'product_oid' => $data['product_oid'],
			//		'product_name' => $data['product_name'],
			//		'warehouse_oid' => $wh_oid,
			//		'supplier_oid' => 0
			//	));
			//	echo "Updated purveyance: ".$data['product_name']." => ".$wh."<br />\n";
			//	echo "[ ".$wh_counter." ] Updated purveyance: ".$data['product_name']." => ".$wh."<br />\n";
			}
			$wh_counter++;
		}

	}

	////
	// it needs params:
	// - parent - product objects oid
	// - product_name
	// - supplier_oid
	// - warehouse_oid
	// - product_oid
	private function add_purveyance_sql($arr)
	{
		$obj_base = $this->db_obj->db_fetch_array("select * from objects where class_id = '".CL_SHOP_PRODUCT_PURVEYANCE."' limit 1");
		$obj_base = reset($obj_base);
		$obj_base['oid'] = 0;
		$obj_base['createdby'] = '110';
		$obj_base['modifiedby'] = '110';
		$obj_base['parent'] = $arr['parent'];
		// i should add prod code as well actually, to make the name more informative and therefore useful
		$obj_base['name'] = sprintf(t("%s tarnetingimus"), addslashes($arr['product_name']));

		$sql = "
			INSERT INTO 
				objects 
			VALUES (".implode(',', map('"%s"', $obj_base)).");
		";

		$this->db_obj->db_query($sql);

		$oid = $this->db_obj->db_last_insert_id();
		
		// brother_of value has to be the same as oid
		$this->db_obj->db_query("UPDATE objects set brother_of = ".$oid." WHERE oid = ".$oid);

		$sql = "
			INSERT INTO 
				aw_shop_product_purveyance
			SET
				aw_oid = ".$oid.",
				warehouse = ".$arr['warehouse_oid'].",
				company = ".$arr['supplier_oid'].",
				product = ".$arr['product_oid']."
		";
		$this->db_obj->db_query($sql);
		echo "Insert new product puveyance: <br />\n";
	}

	private function update_purveyance_sql($oid, $data)
	{
		$sql = "
			UPDATE 
				objects
			SET
				name = '".addslashes($data['product_name'])."',
				brother_of = ".$oid."
			WHERE
				oid = ".$oid."
		";
		$this->db_obj->db_query($sql);

		$sql = "
			UPDATE
				aw_shop_product_purveyance
			SET
				warehouse = ".$data['warehouse_oid'].",
				company = ".$data['supplier_oid'].",
				product = ".$data['product_oid']."
			WHERE
				aw_oid = ".$oid."
		";
		$this->db_obj->db_query($sql);
	
	}

	private function delete_purveyance_sql($oid)
	{
		$sql = "DELETE FROM objects WHERE oid = ".$oid;
		$this->db_obj->db_query($sql);

		$sql = "DELETE FROM aw_shop_product_purveyance WHERE aw_oid = ".$oid;
		$this->db_obj->db_query($sql);
	
	}


	public function import_amounts($arr, $that)
	{
		$that->_update_status("amounts", warehouse_import_if::STATE_FETCHING, 0);

		$this->init_vars();

		// i need here:
		// product objects ids <-> product codes lookup table
		// so i can check if the product code has amount object with current warehouse or not ...
		// create the product_oids <-> product_codes lut
		$products_lut = array();
		$sql = "
			select
				aw_oid,
				code
			from 
				aw_shop_products
		";
		$this->db_obj->db_query($sql);
		while ($row = $this->db_obj->db_next()){
			$products_lut[$row['code']] = $row['aw_oid'];
		}

		$total = count($products_lut);
		$that->_update_status("amounts", warehouse_import_if::STATE_PROCESSING, 0, $total);

		foreach ($this->get_warehouses() as $oid)
		{
			$wh_obj = new object($oid);
			echo "Update amounts in warehouse ".$wh_obj->name().": <br />\n";
			flush();
			$this->update_amounts($oid, $products_lut, $that);
		}
	
		exit();
	}

	// update amounts data in a warehouse
	private function update_amounts($warehouse_oid, $products_lut, $that)
	{
		if (!$this->can('view', $warehouse_oid))
		{
			echo "Couldn't load the Warehouse object (".$warehouse_oid.") <br />\n";
			return false;
		}

		// I need existing amounts data from AW:
		$sql = "
			select
				aw_oid,
				amount,
				warehouse,
				product
			from
				aw_shop_warehouse_amount
			where
				warehouse = ".$warehouse_oid."
		";
		$existing_amounts_data = array();
		$this->db_obj->db_query($sql);
		while ($r = $this->db_obj->db_next())
		{
			$existing_amounts_data[$r['product']] = $r;
		}
		
		$wh_obj = new object($warehouse_oid);
		$amounts_data = file($wh_obj->comment()."/amounts.csv");
		$total = count($amounts_data);
		foreach ($amounts_data as $line)
		{
			$items = explode("\t", $line);
			$product_oid = $products_lut[trim($items[0])];
			if (empty($product_oid))
			{
				continue;
			}
			
			if (empty($existing_amounts_data[$product_oid]))
			{
				// we don't have the amount object for this product in current warehouse
				$this->add_amount_sql(array(
					'product_code' => $items[0],
					'product_oid' => $product_oid,
					'amount' => trim($items[1]),
					'warehouse_name' => $wh_obj->name(),
					'warehouse_oid' => $warehouse_oid
				));
				echo "INSERT: ".$wh_obj->name()." - ".$items[0]." - ".$product_oid." - ".$items[1]."<br />\n";
			}
			else
			{
			
				$this->update_amount_sql($existing_amounts_data[$product_oid]['aw_oid'], array(
					'product_code' => $items[0],
					'product_oid' => $product_oid,
					'amount' => trim($items[1]),
					'warehouse_name' => $wh_obj->name(),
					'warehouse_oid' => $warehouse_oid
				));
				echo "UPDATE: ".$wh_obj->name()." - ".$items[0]." - ".$product_oid." - ".$items[1]."<br />\n";
			
			}
			flush();

			if ((++$counter % 100) == 1)
			{
				$that->_update_status("amounts", warehouse_import_if::STATE_PROCESSING, $counter, $total, sprintf(t("Import laost %s"), $wh_obj->name()));
				if ($that->need_to_stop_now("amounts"))
				{
					$that->_end_import_from_flag("amounts");
					die("stopped for flag");
				}
			} 
		}
	}

	////
	// product_code
	// warehouse_name
	// warehouse_oid
	// amount
	// product_oid
	private function add_amount_sql($data)
	{
		$obj_base = $this->db_obj->db_fetch_array("select * from objects where class_id = '".CL_SHOP_WAREHOUSE_AMOUNT."' limit 1");
		$obj_base = reset($obj_base);
		$obj_base['oid'] = 0;
		$obj_base['createdby'] = '110';
		$obj_base['modifiedby'] = '110';
		$obj_base['parent'] = $data['parent'];
		// i should add prod code as well actually, to make the name more informative and therefore useful
		$name = sprintf(t("Toote %s laoseis %s laos"), $data['product_code'], $data['warehouse_name']);
		$obj_base['name'] = addslashes($name);

		$sql = "
			INSERT INTO 
				objects 
			VALUES (".implode(',', map('"%s"', $obj_base)).");
		";

		$this->db_obj->db_query($sql);

		$oid = $this->db_obj->db_last_insert_id();

		// brother_of value has to be the same as oid
		$this->db_obj->db_query("UPDATE objects set brother_of = ".$oid." WHERE oid = ".$oid);

		$sql = "
			INSERT INTO 
				aw_shop_warehouse_amount
			SET
				aw_oid = ".$oid.",
				warehouse = ".$data['warehouse_oid'].",
				amount = ".$data['amount'].",
				product = ".$data['product_oid']."
		";
		$this->db_obj->db_query($sql);
	
	}

	////
	// product_code
	// warehouse_name
	// warehouse_oid
	// amount
	// product_oid
	private function update_amount_sql($oid, $data)
	{
		$name = sprintf(t("Toote %s laoseis %s laos"), $data['product_code'], $data['warehouse_name']);
		$sql = "
			UPDATE 
				objects
			SET
				name = '".addslashes($name)."',
				brother_of = ".$oid."
			WHERE
				oid = ".$oid."
		";
		$this->db_obj->db_query($sql);

		$sql = "
			UPDATE
				aw_shop_warehouse_amount
			SET
				warehouse = ".$data['warehouse_oid'].",
				amount = ".$data['amount'].",
				product = ".$data['product_oid']."
			WHERE
				aw_oid = ".$oid."
		";
		$this->db_obj->db_query($sql);
	}

	private function delete_amount_sql($oid)
	{
		$sql = "DELETE FROM objects WHERE oid = ".$oid;
		$this->db_obj->db_query($sql);

		$sql = "DELETE FROM aw_shop_warehouse_amount WHERE aw_oid = ".$oid;
		$this->db_obj->db_query($sql);
	}

	public function import_prices($arr, $that)
	{
		$this->init_vars();

		$that->_update_status("prices", warehouse_import_if::STATE_FETCHING, 0);

		$prices_data = $this->get_prices_data();
		$products_data = $this->get_products_lut();

		$existing_prices_data = array();
		$sql = "select oid,parent from objects where class_id = ". CL_SHOP_ITEM_PRICE." and status > 0";
		$this->db_obj->db_query($sql);
		while ($row = $this->db_obj->db_next())
		{
			$existing_prices_data[$row['parent']] = $row['oid'];
		}
		$total = count($products_data);
		arr($total);

		$that->_update_status("prices", warehouse_import_if::STATE_PROCESSING, 0, $total);
		$counter = 0;
		foreach ($products_data as $code => $prod_oid)
		{
			if (!isset($prices_data[$code]))
			{
				echo "No price for product ".$code." (".$prod_oid.")<br />\n";
				continue;
			}
			$prices = $prices_data[$code];
			if (empty($existing_prices_data[$prod_oid]))
			{
				$this->add_price_sql(array(
					'product_oid' => $prod_oid,
					'product_code' => $code,
					'price' => $prices['price']
				));
				echo "Add price ".$prices['price']." to product ".$code." (".$prod_oid.")<br />\n";
			}
			else
			{
				$this->update_price_sql($existing_prices_data[$prod_oid], array(
					'product_oid' => $prod_oid,
					'product_code' => $code,
					'price' => $prices['price']
				));
				echo "Update price ".$prices['price']." to product ".$code." (".$prod_oid.")<br />\n";
			
			}

			if ((++$counter % 100) == 1)
			{
				$that->_update_status("prices", warehouse_import_if::STATE_PROCESSING, $counter, $total);
				if ($that->need_to_stop_now("prices"))
				{
					$that->_end_import_from_flag("prices");
					die("stopped for flag");
				}
			} 
		}
	}

	private function add_price_sql($data)
	{
		$obj_base = $this->db_obj->db_fetch_array("select * from objects where class_id = '".CL_SHOP_ITEM_PRICE."' limit 1");
		$obj_base = reset($obj_base);
		$obj_base['oid'] = 0;
		$obj_base['createdby'] = '110';
		$obj_base['modifiedby'] = '110';
		$obj_base['parent'] = $data['product_oid'];
		// i should add prod code as well actually, to make the name more informative and therefore useful
		$name = sprintf(t("%s hind"), $data['product_code']);
		$obj_base['name'] = addslashes($name);
		$obj_base['acldata'] = '';

		$sql = "
			INSERT INTO 
				objects 
			VALUES (".implode(',', map('"%s"', $obj_base)).");
		";

		$this->db_obj->db_query($sql);

		$oid = $this->db_obj->db_last_insert_id();

		// brother_of value has to be the same as oid
		$this->db_obj->db_query("UPDATE objects set brother_of = ".$oid." WHERE oid = ".$oid);

		$sql = "
			INSERT INTO 
				aw_shop_item_prices
			SET
				aw_oid = ".$oid.",
				price = ".(int)$data['price'].",
				product = ".$data['product_oid']."
		";
		$this->db_obj->db_query($sql);
	
	}

	////
	// product_code
	// warehouse_name
	// warehouse_oid
	// amount
	// product_oid
	private function update_price_sql($oid, $data)
	{
		$name = sprintf(t("%s hind"), $data['product_code']);
		$obj_base['name'] = addslashes($name);

		$sql = "
			UPDATE 
				objects
			SET
				name = '".addslashes($name)."',
				brother_of = ".$oid."
			WHERE
				oid = ".$oid."
		";
		$this->db_obj->db_query($sql);

		$sql = "
			UPDATE
				aw_shop_item_prices
			SET
				price = ".(int)$data['price'].",
				product = ".$data['product_oid']."
			WHERE
				aw_oid = ".$oid."
		";
		$this->db_obj->db_query($sql);
	}

	private function delete_price_sql($oid)
	{
		$sql = "DELETE FROM objects WHERE oid = ".$oid;
		$this->db_obj->db_query($sql);

		$sql = "DELETE FROM aw_shop_item_prices WHERE aw_oid = ".$oid;
		$this->db_obj->db_query($sql);
	}

	public function get_prices_data()
	{
		$data_file = $this->get_products_file();
		$lines = file($data_file);
		
		unset($lines[0]);

		$result = array();
		foreach ($lines as $line)
		{
			$fields = explode("\t", $line);
			$result[trim($fields[0])] = array(
				'price' => $fields[6],
				'special_price' => $fields[7]
			);
		}
		return $result;
	}

	public function get_products_lut()
	{
		$products_lut = array();
		$sql = "
			select
				aw_oid,
				code
			from 
				aw_shop_products
		";
		$this->db_obj->db_query($sql);
		while ($row = $this->db_obj->db_next()){
			$products_lut[$row['code']] = $row['aw_oid'];
		}
		return $products_lut;
	}

	public function get_products_file()
	{
		return aw_ini_get('site_basedir').'/files/products.csv';
	}

	private function apply_controller($code)
	{
		if($this->controller_inst)
		{
			return $this->controller_inst->check_property($this->controller_id, null, $code, null, null, null);
		}
		return false;
	}

	// query warehouses
	public function get_warehouses()
	{
		if (!empty($this->warehouses))
		{
			return $this->warehouses;
		}

		$conns = $this->connections_from(array(
			'type' => 'RELTYPE_WAREHOUSE',
			'sort_by_num' => 'to.jrk',
			'sort_dir' => 'asc'
		));

		foreach ($conns as $conn)
		{
			$this->warehouses[$conn->prop('to')] = $conn->prop('to');
		}
		return $this->warehouses;
	}

	// this is for personnel import and it should return php array with data
	// and the personnel import can make persons and stuff out of this data (hopefully)
	public function get_users_data($arr)
	{
		$whs = $this->get_warehouses();	
		$wh = new object(reset($whs));
		$adr = $wh->comment();
		$data = file_get_contents($adr.'/index.php?get_users_data=1');

		arr($data);

		$r = array();

		$lines = explode("\n", $data);
		$keys = explode("\t", $lines[0]);
		unset($lines[0]);
		foreach ($lines as $id => $line)
		{

			$items = array_combine($keys, explode("\t", $line));
			$r['PERSONS'][$items['CLIENT_NR']]['USER'] = $items['CLIENT_NR'];
			$r['PERSONS'][$items['CLIENT_NR']]['PID'] = $items['CLIENT_NR'];
			$r['PERSONS'][$items['CLIENT_NR']]['FNAME'] = $items['FIRST_NAME'];
			$r['PERSONS'][$items['CLIENT_NR']]['LNAME'] = $items['LAST_NAME'];
			$r['PERSONS'][$items['CLIENT_NR']]['EMAILS'][0] = $items['EMAIL'];
			$r['PERSONS'][$items['CLIENT_NR']]['PHONES']['MOBILE'][0] = $items['GSM'];
			// company name ? $items['COMPANY_NAME'];
			$r['ORGANIZATIONS'][$items['CLIENT_NR']]['NAME'] = $items['COMPANY_NAME'];
		}
		return $r;

		
	}

	function parallel_url_fetch($d)
	{
		$mh = curl_multi_init();

		$ch = array();
		foreach($d as $nr => $url)
		{
			$ch[$nr] = curl_init();
			curl_setopt($ch[$nr], CURLOPT_URL, $url);
			curl_setopt($ch[$nr], CURLOPT_HEADER, 0);
			curl_setopt($ch[$nr], CURLOPT_RETURNTRANSFER, true);
			curl_multi_add_handle($mh, $ch[$nr]);
		}

		$running = null;
		//execute the handles
		do 
		{
		    curl_multi_exec( $mh, $running );
		} 
		while ( $running > 0 );

		$rv = array();
		foreach($d as $nr => $url)
		{
			$rv[$nr] = curl_multi_getcontent($ch[$nr]);
			curl_multi_remove_handle($mh, $ch[$nr]);
		}

		curl_multi_close($mh);

		return $rv;
	}

	private function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	private function print_line($str, $break = true)
	{
		echo $str;
		if ($break === true)
		{
			echo "<br />\n";
		}
		flush();
	}
}

?>
