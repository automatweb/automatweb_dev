<?php

class shop_delivery_method_obj extends shop_matrix_obj
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
		@attrib name=price params=name

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
	**/
	public function valid($arr)
	{
		try
		{
			self::valid_validate_arguments($arr);
		}
		catch (Exception $e)
		{
			throw $e;
		}

		enter_function("shop_delivery_method_obj::valid");

		// Prepare the arguments for price evaluation code
		$args = self::handle_arguments($arr);
		$retval = $this->run_validation_evaluation_code($args);
		exit_function("shop_delivery_method_obj::valid");
		return $retval;
	}

	protected static function valid_validate_arguments($arr)
	{
		foreach(array(
			"shop" => t("Parameter 'shop' must me a valid OID!"),
			"product" => t("Parameter 'product' must me a valid OID!"),
		) as $k => $msg)
		{
			if(!isset($arr[$k]) || !is_oid($arr[$k]))
			{
				$e = new awex_shop_delivery_method_parameter($msg);
//				throw $e;
			}
		}
	}

	protected static function handle_arguments($arr)
	{
		$args = array(
			"amount" => isset($arr["amount"]) ? $arr["amount"] : 1,
			"customers" => safe_array(ifset($arr, "customer_category")),
			"locations" => safe_array(ifset($arr, "location")),
			"default" => array(0),
		);

		if(!isset($arr["product"]))
		{
			$arr["product"] = isset($arr["product_packaging"]) ? shop_product_packaging_obj::get_products_for_id($arr["product_packaging"]) : array();
		}

		if(!isset($arr["product_category"]))
		{
			$arr["product_category"] = isset($arr["product"]) ? shop_product_obj::get_categories_for_id($arr["product"]) : array();
		}

		if(!isset($arr["product_packaging"]))
		{
			$arr["product_packaging"] = array();
		}
		$args["rows"] = array_merge((array)$arr["product_packaging"], (array)$arr["product"], (array)$arr["product_category"]);

		return $args;
	}

	public function set_price($arr)
	{
		// LATER ON SHOULD BE BUILT ON PRICE OBJECTS. I CAN'T UNDERSTAND THE LOGIC BEHIND THOSE AT THE MOMENT -kaarel 30.07.2009
		$this->set_meta("prices", $arr);
	}

	public function get_price()
	{
		static $prices;
		if(!isset($prices))
		{
			// LATER ON SHOULD BE BUILT ON PRICE OBJECTS. I CAN'T UNDERSTAND THE LOGIC BEHIND THOSE AT THE MOMENT -kaarel 30.07.2009
			$prices = $this->meta("prices");
		}
		return $prices;
	}

	public function update_code()
	{
		$this->prioritize();

		$i = $this->instance();
		$i->read_template("code.aw");

		$matrix_structure = $this->get_matrix_structure($this);
		$this->cells = array();
		foreach($matrix_structure["rows"]["products"] as $row => $subrows)
		{
			$this->update_code_add_cell($row, 0);
			foreach($matrix_structure["cols"]["customers"] as $col => $subcols)
			{
				$this->update_code_add_cell($row, $col, array("row" => $row, "col" => 0), $subrows, $subcols);
			}
		}
		$odl = new object_data_list(
			array(
				"class_id" => CL_SHOP_DELIVERY_METHOD_CONDITIONS,
				"delivery_method" => $this->id(),
				"lang_id" => array(),
				"site_id" => array(),
			),
			array(
				CL_SHOP_DELIVERY_METHOD_CONDITIONS => array("row", "col", "enable"),
			)
		);
		foreach($odl->arr() as $cond)
		{
			$matrix[$cond["row"]][$cond["col"]] = $cond["enable"] ? 1 : 2;
			$this->cells[$cond["row"]][$cond["col"]]["enable"] = $cond["enable"] ? 1 : 2;
		}

		$PRIORITIES = "";
		foreach($matrix_structure["priorities"] as $id => $priority)
		{
			$i->vars(array(
				"id" => $id,
				"priority" => $priority,
			));
			$PRIORITIES .= $i->parse("PRIORITIES");
		}

		$PARENTS = "";
		foreach($matrix_structure["parents"] as $id => $parents)
		{
			$i->vars(array(
				"id" => $id,
				"parents" => count($parents) ? "'".implode("','", $parents)."'" : "",
			));
			$PARENTS .= $i->parse("PARENTS");
		}

		$HANDLE_CELL = "";
		foreach($this->cells as $row => $cols)
		{
			foreach($cols as $col => $cell_data)
			{
				if(isset($cell_data["enable"]))
				{
					$i->vars(array(
						"row" => $row,
						"col" => $col,
						"enable" => $cell_data["enable"] == 2 ? "false" : "true",
					));
					$HANDLE_CELL .= rtrim($i->parse("HANDLE_CELL"), "\t");
				}
			}
		}

		$i->vars(array(
			"enabled_by_default" => $this->prop("enabled") ? "true" : "false",
			"passing_order" => "'".implode("','", array_merge(array_keys(safe_array($this->meta("matrix_col_order"))), array("default")))."'",
			"PARENTS" => $PARENTS,
			"PRIORITIES" => $PRIORITIES,
			"HANDLE_CELL" => $HANDLE_CELL,
		));

		$this->set_prop("code", $i->parse());
		$this->save();
	}

	public function run_validation_evaluation_code($args)
	{
		$f = create_function('$args', $this->prop("code"));
		return $f($args);
	}
}

/* Generic price list exception */
class awex_shop_delivery_method extends aw_exception {}

/* Indicates invalid argument */
class awex_shop_delivery_method_parameter extends awex_price_list {}


?>
