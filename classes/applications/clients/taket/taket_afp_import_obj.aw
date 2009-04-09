<?php

class taket_afp_import_obj extends _int_object
{
	private $prod_fld;
	private $warehouse;
	private $controller_inst;
	private $controller_id;
	private $db_obj; // this is a cache class, i use it to make sb queries

	function get_data($arr)
	{
		$wh = $this->prop("warehouse");

		if($this->can("view", $wh))
		{
			$who = obj($wh);
			$cid = $who->prop("conf");
			$this->warehouse = $wh;
		}
		if($this->can("view", $cid))
		{
			$co = obj($cid);
			$prod_fld = $co->prop("prod_fld");
		}

		if(!$prod_fld)
		{
			die(t("Lao toodete kataloog on m&auml;&auml;ramata"));
		}
		elseif(!$this->can("add", $prod_fld))
		{
			die(t("Lao toodete kataloogi alla ei ole &otilde;igusi lisamiseks"));
		}


		$org_fld = $this->prop("org_fld");

		if(!$org_fld)
		{
			die(t("Organisatsioonide kataloog on m&auml;&auml;ramata"));
		}
		elseif(!$this->can("add", $org_fld))
		{
			die(t("Organisatsioonide kataloogi alla ei ole &otilde;igusi lisamiseks"));
		}
/* ???
// might become handy actually, if I want to eventually some kind of products searching / loading things put into search class ...
// but for now it is not needed 
		$ol = new object_list(array(
			"class_id" => CL_TAKET_SEARCH,
			"site_id" => array(),
			"lang_id" => array(),
		));
		$s_o = $ol->begin();
		if(!$s_o)
		{
			die(t("S&uuml;steemis puudub taketi otsingu objekt"));
		}
*/
		// controller for short product codes ...
		if($this->controller_id = $this->prop("code_ctrl"))
		{
			$this->controller_inst = get_instance(CL_CFGCONTROLLER);	
		}

		$this->db_obj = $GLOBALS["object_loader"]->cache;
		
		$this->load_warehouses();
/* ???
		$this->whs[0] = $s_o->prop("warehouse0");
		$this->whs[1] = $s_o->prop("warehouse1");
		$this->whs[2] = $s_o->prop("warehouse2");
		$this->whs[3] = $s_o->prop("warehouse3");
		$this->whs[4] = $s_o->prop("warehouse4");
		$this->whs[5] = $s_o->prop("warehouse5");
*/
		$this->prod_fld = $prod_fld;
		$this->org_fld = $org_fld;

		if ($arr['from_file'] == 1)
		{
			$this->get_data_from_file($arr);
			return;
		}

		// this here probably never is needed ...
		define('AMOUNT', $this->prop("amount"));
		
		require(aw_ini_get("basedir")."addons/ixr/IXR_Library.inc.php");
	
		$c = new IXR_Client("84.50.96.150", "/xmlrpc/index.php", "8080");
		$c2 = new IXR_Client("84.50.96.150", "/xmlrpc/index.php", "8080");

		$this->download($c, $c2);

	}

	private function download($c, $c2)
	{
		aw_set_exec_time(AW_LONG_PROCESS);
		echo "---------------------------------------------------------------------------\n";
		$prod_offset = 0;
		$overall_start = $this->microtime_float();
		$start = $end = 0;
		
		if($cid = $this->prop("code_ctrl"))
		{
			$ctrli = get_instance(CL_CFGCONTROLLER);	
		}

		while(true)
		{
			$start = $this->microtime_float();
			echo "Getting ".AMOUNT." products from server with offset ".$prod_offset." ... <br>\n";
			$c->query("server.getProductList", array('limit' => $prod_offset.','.AMOUNT));
			echo "[ok]<br>\n";
			$prods = $c->getResponse();
			echo "Got ".count($prods)." products:<br>\n";
			foreach($prods as $k => $v)
			{
				$code = urldecode($v["product_code"]);
				$ol = new object_list(array(
					"class_id" => CL_SHOP_PRODUCT,
					"code" => $code,
				));
				$o = $ol->begin();
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

					$org_id = urldecode($v["supplier_id"]);
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
						$org->set_name(urldecode($v["supplier_name"]));
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
				}

				$o->set_name(urldecode($v["product_name"]));
				$o->set_prop("code", $code);
				if($ctrli)
				{
					$short_code = $ctrli->check_property($cid, null, $code, null, null, null);
					$o->set_prop("short_code", $short_code);
				}
				$o->set_prop("search_term", urldecode($v["search_term"]));
				$o->set_prop("user1", urldecode($v["replacement_product_code"]));
				$c2->query("server.getPrices", array("product_codes" => $prodcodes));
				$prices = $c->getResponse();arr($v);
				die(arr($prices));

				$o->save();
				flush();
			}
			$end = $this->microtime_float();
			echo "[Iteration: ".$prod_offset/AMOUNT." | Time: ".(float)($end - $start)."]<br>\n";
			echo "Waiting for 5 seconds just in case before making new query to server ... <br>\n";
			sleep(5);
			echo "[ok]<br>\n";
			if (count($prods) < AMOUNT){
				echo "Thats all, exit.<br>\n";
				break;
			}
			$prod_offset += AMOUNT;
			flush();

			//remove the next line when the class is complete
		}
		$overall_end = $this->microtime_float();
		echo "---------------------------------------------------------------------------<br>\n";
		echo "[Iterations count: ".(float)($prod_offset / AMOUNT)." Overall time: ".(float)($overall_end - $overall_start)."]\n";
		die();
	}

	public function get_data_from_file($arr)
	{
		// ERROR REPORTING
		ini_set('display_errors', "stdout");
		error_reporting(E_ALL);

		if($cid = $this->prop("code_ctrl"))
		{
			$ctrli = get_instance(CL_CFGCONTROLLER);	
		}

		$start = $this->microtime_float();

		aw_set_exec_time(AW_LONG_PROCESS);

		$path = aw_ini_get('site_basedir').'/files/prods.txt';

		$lines = file($path);
	
		$keys = explode("\t", trim($lines[0]));
		unset($lines[0]);

		foreach ($lines as $line)
		{
			$items = explode("\t", $line);

			foreach ($items as $k => $v)
			{
				$items[$k] = trim(urldecode($v));
			}

			$prod = array_combine($keys, $items);
			$prods[$prod['product_code']] = $prod;

			$suppliers[$prod['supplier_id']] = $prod['supplier_name'];
		}

//		$this->update_suppliers($suppliers);

		$this->update_products($prods);

//		$o = new object($arr['oid']);

		$end = $this->microtime_float();

		echo "time: ".(float)($end - $start)." <br /> \n";

		exit();
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

	private function update_products($data)
	{
		$sql = "
			SELECT
				objects.oid,
				objects.name,
				objects.comment,
				aw_shop_products.search_term as search_term,
				aw_shop_products.user1 as replacement_product_code,
				aw_shop_products.code
			FROM
				objects
				LEFT JOIN aw_shop_products on objects.oid = aw_shop_products.aw_oid
			WHERE
				objects.class_id = 295 and
				objects.status = 1
		";

		$aw_prods = $this->db_obj->db_fetch_array($sql);
		echo "About go over <strong>".count($aw_prods)."</strong> products ... <br />\n";
		$count = 0;
		foreach ($aw_prods as $aw_prod)
		{
			$code = (empty($aw_prod['comment'])) ? trim($aw_prod['code']) : trim($aw_prod['comment']);

			$prod_data = ( isset( $data[$code] ) ) ? $data[$code] : null;
			if ( !empty($prod_data) )
			{
				if ($this->is_product_changed($aw_prod, $prod_data))
				{
					echo "Product is changed - update (".$aw_prod['oid'].")<br />\n";
					$this->update_product_sql($aw_prod['oid'], $prod_data);
				}
				else
				{
				//	echo "Product is not changed <br />\n";
				}

				// the product is in aw, so lets remove it from data array:
				unset($data[$code]);
			}
			else
			{
				echo "Delete product (".$aw_prod['oid'].") <br />\n";
				$this->delete_product_sql($aw_prod['oid']);
			}
		}

		echo "<pre>";
		print_r("Prods to insert aw: ".count($data));
		echo "</pre>";

		foreach ($data as $value)
		{
			$this->add_product_sql($value);
		}

		echo "<pre>";
		print_r("prods update done");
		echo "</pre>";
	}

	// check if the product is changed or not ...
	private function is_product_changed($old, $new)
	{
		// values to check
		// old (from db) => new (from file)
		$check = array(
			'name' => 'product_name',
			'code' => 'product_code',
			'replacement_product_code' => 'replacement_product_code',
			'search_term' => 'search_term'
		);
		
		foreach ($check as $old_key => $new_key)
		{
			if ($old[$old_key] != $new[$new_key])
			{
				return true;
			}
		}
		return false;
	}

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

		$sql = "
			INSERT INTO 
				aw_shop_products 
			SET
				aw_oid = ".$oid.",
				code = '".addslashes($data['product_code'])."',
				search_term = '".addslashes($data['search_term'])."',
				user1 = '".addslashes($data['replacement_product_code'])."',
				short_code = '".$this->apply_controller($data['product_code'])."'
		";
		$this->db_obj->db_query($sql);
		echo "Insert new product: code: (".$data['product_code'].") || oid: ".$oid."<br />\n";
	}

	// the product object exists in the system, so i need to update the data
	private function update_product_sql($oid, $data)
	{
		$sql = "
			UPDATE 
				objects
			SET
				name = '".addslashes($data['product_name'])."',
				comment = '".addslashes($data['product_code'])."'
			WHERE
				oid = ".$oid."
		";
		echo "<pre>";
		print_r($sql);
		echo "</pre>";
		$this->db_obj->db_query($sql);

		$sql = "
			UPDATE
				aw_shop_products
			SET
				code = '".addslashes($data['product_code'])."',
				search_term = '".addslashes($data['search_term'])."',
				user1 = '".addslashes($data['replacement_product_code'])."',
				short_code = '".$this->apply_controller($data['product_code'])."'
			WHERE
				aw_oid = ".$oid."
		";
		echo "<pre>";
		print_r($sql);
		echo "</pre>";
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

	private function apply_controller($code)
	{
		if($this->controller_inst)
		{
			return $this->controller_inst->check_property($this->controller_id, null, $code, null, null, null);
		}
		return false;
	}

	private function load_warehouses()
	{
		$conns = $this->connections_from(array(
			'type' => 'RELTYPE_WAREHOUSE',
			'sort_by_num' => 'to.jrk',
			'sort_dir' => 'asc'
		));

		foreach ($conns as $conn)
		{
			$this->warehouses[$conn->prop('to')] = $conn->prop('to.name')." (".$conn->prop('to.jrk').")";
		}
		
	}

/*
	private function get_changed_products($prods)
	{
		$start = $this->microtime_float();
		$odl = new object_data_list(
			array(
				'class_id' => CL_SHOP_PRODUCT
			),
			array(
				CL_SHOP_PRODUCT => array(
					'oid' => 'oid',
					'name' => 'name',
					'code' => 'product_code',
					'search_term' => 'search_term',
					'user1' => 'replacement_product_code'
				)
			)
		);

		$counter=0;
		foreach ($odl->arr() as $v)
		{
			if ($this->is_product_changed($v, $prods[$v['product_code']])){
				unset($prods[$v['product_code']]);
				$counter++;
			}
			else
			{
				// product is changed, so lets remember the oid it has:
				$prods[$v['product_code']]['oid'] = $v['oid'];
			}
		}
		$end = $this->microtime_float();
		echo "check time: ".(float)($end - $start)." [ $counter ]<br />\n";

		return $prods;
	}
*/

	private function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}

?>
