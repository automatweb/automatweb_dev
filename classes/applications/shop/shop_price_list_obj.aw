<?php

class shop_price_list_obj extends _int_object
{
	public function awobj_get_matrix_cols()
	{
		return (int)parent::prop("matrix_cols");
	}

	public function awobj_get_matrix_rows()
	{
		return (int)parent::prop("matrix_rows");
	}

	/**
		@attrib params=name
		@param valid optional type=bool
	**/
	public static function get_price_lists($arr = array())
	{
		static $retval;
		$hash = serialize($arr);
		if(!isset($retval[$hash]))
		{
			$prms = array(
				"class_id" => CL_SHOP_PRICE_LIST,
				"lang_id" => array(),
				"site_id" => array(),
				new obj_predicate_sort(array(
					"jrk" => "ASC",
				)),
			);

			if(!empty($arr["valid"]))
			{	// VALID
				$prms["valid_from"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, time());
				$prms["valid_to"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, time());
			}
			elseif(isset($arr["valid"]))
			{	// INVALID
				$prms[] = new object_list_filter(array(
					"logic" => "OR",
					"conditions" => array(
						"CL_SHOP_PRICE_LIST.valid_from" => new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, time()),
						"CL_SHOP_PRICE_LIST.valid_to" => new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, time()),
					)
				));
			}

			$retval[$hash] = new object_list($prms);
		}
		return $retval[$hash];
	}

	/**
		Returns true if conditions should be stored
	*/
	public static function store_condition($data)
	{
		if(
			$data["value"] === "0" ||	// The price is set to 0
			aw_math_calc::string2float(trim($data["value"], "+-%")) > 0 ||	// False for something like this: +0% -0 etc
			aw_math_calc::string2float(trim($data["bonus"], "+-%")) > 0	// False for something like this: +0% -0 etc
		)
		{
			return true;
		}
		return false;
	}

	/**
		@attrib name=price params=name

		@param product required type=int acl=view
			The OID of the product
		@param amount optional type=float default=1
			The amount of the product prices are asked for
		@param prices optional type=float
			The before prices by currencies
		@param bonuses optional type=float default=0
			The before points
		@param customer_category optional type=array/int acl=view
			OIDs of client categories.
		@param client_data optional type=int acl=view
			OID of client_data object
		@param location optional type=array/int acl=view
			OIDs of locations
		@param product_category optional type=array/int acl=view
			OIDs of product categories
		@param structure optional type=bool default=false
			If set, the structure of the prices will be returned, otherwise only the final prices will be returned.
	**/
	public static function price($arr)
	{
		enter_function("shop_price_list_obj::price");
		/**
			# STRUCTURE of $retval (if $arr["structure"] is true)
			array(
				[currency OID] => array(
					price => array(
						in => [PRICE_IN]
						out => [PRICE_OUT]
					)
					bonus => array(
						in => [BONUS_IN]
						out => [BONUS_OUT]
					)
					log => array(	// Following will be tracked for every cell row passed
						array(
							type
							diff => array(
								price => [PRICE_DIFF]		// ABS
								bonus => [BONUS_DIFF]		// ABS
							)
						)
					)
				)
			)

		**/
		$retval = array();
		$prices_only_retval = $arr["prices"];
		foreach(array_keys($arr["prices"]) as $currency)
		{
			$bonus = isset($arr["bonuses"][$currency]) ? $arr["bonuses"][$currency] : 0;
			$retval[$currency] = array(
				"price" => array(
					"in" => aw_math_calc::string2float($arr["prices"][$currency]),
					"out" => aw_math_calc::string2float($arr["prices"][$currency]),
				),
				"bonus" => array(
					"in" => aw_math_calc::string2float($bonus),
					"out" => aw_math_calc::string2float($bonus),
				),
				"log" => array(),
			);
		}

		// Find all valid price list objects
		// Later on this should leave out the ones that don't have given customers, products etc..
		$ol = self::get_price_lists(array(
			"valid" => true,
		));

		// Prepare the arguments for price evaluation code
		$args = self::handle_arguments($arr);

		foreach($ol->arr() as $o)
		{
			$price_datas = $o->run_price_evaluation_code($args);
			foreach($price_datas as $currency => $price_data)
			{
				$args["prices"][$currency] = $prices_only_retval[$currency] = $retval[$currency]["price"]["out"] = $price_data["price"]["out"];
				$args["bonuses"][$currency] = $retval[$currency]["bonus"]["out"] = $price_data["bonus"]["out"];
				$retval[$currency]["log"] = array_merge($retval[$currency]["log"], safe_array($price_data["log"]));
			}
		}
		exit_function("shop_price_list_obj::price");
		return empty($arr["structure"]) ? $prices_only_retval : $retval;
	}

	protected static function handle_arguments($arr)
	{
		$args = array(
			"amount" => isset($arr["amount"]) ? $arr["amount"] : 1,
			"prices" => $arr["prices"],
			"bonuses" => isset($arr["bonuses"]) ? $arr["bonuses"] : array(),
			"currencies" => array_keys($arr["prices"]),
			"customers" => safe_array(ifset($arr, "customer_category")),
			"locations" => safe_array(ifset($arr, "location")),
			"default" => array(0),
		);

		if(!isset($arr["product_category"]))
		{
			$arr["product_category"] = shop_product_obj::get_categories_for_id($arr["product"]);
		}
		$args["rows"] = array_merge(array($arr["product"]), (array)$arr["product_category"]);

		return $args;
	}

	public function run_price_evaluation_code($args)
	{
		$f = create_function('$args', $this->prop("code"));
		return safe_array($f($args));
	}

	public static function get_matrix_structure($o)
	{
		static $retval;
		if(!isset($retval[$o->id()]))
		{
			$matrix = array(
				"rows" => array(
					"products" => array(),
				),
				"cols" => array(
					"customers" => array(),
					"locations" => array(),
				),
				"ids" => array(),
				"names" => array(),
				"priorities" => array(),
			);
			$company_inst = new crm_company_obj;
			$product_category_inst = new shop_product_category_obj;
			$admin_struct_inst = new country_administrative_structure_object;

			foreach(safe_array($o->prop("matrix_customer_categories")) as $id)
			{
				$matrix["cols"]["customers"][$id] = $company_inst->get_customer_categories_hierarchy($id);
			}

			foreach(safe_array($o->prop("matrix_countries")) as $id)
			{
				$matrix["cols"]["locations"][$id] = $admin_struct_inst->prop(array(
					"prop" => "units_by_country",
					"country" => $id,
				))->ids_hierarchy();
			}

			foreach(safe_array($o->prop("matrix_product_categories")) as $id)
			{
				$matrix["rows"]["products"][$id] = $product_category_inst->get_categories_hierarchy($id);
			}

			$matrix["ids"]["customers"] = self::get_matrix_ids($matrix["cols"]["customers"]);
			$matrix["ids"]["locations"] = self::get_matrix_ids($matrix["cols"]["locations"]);
			$matrix["ids"]["products"] = self::get_matrix_ids($matrix["rows"]["products"]);

			foreach($product_category_inst->get_products($matrix["ids"]["products"]) as $cat => $ol)
			{
				$matrix["ids"]["products"];
				foreach($ol->ids() as $id)
				{
					self::get_matrix_structure_add_product_to_category($id, $cat, $matrix["rows"]["products"]);
					$matrix["ids"]["products"][] = $id;
				}
			}

			if(count($ids = array_merge($matrix["ids"]["customers"], $matrix["ids"]["products"], $matrix["ids"]["locations"])) > 0)
			{
				// Names, clids
				$odl = new object_data_list(
					array(
						"oid" => $ids,
						"lang_id" => array(),
						"site_id" => array(),
					),
					array(
						CL_SHOP_PRODUCT => array("class_id", "name"),
					)
				);
				$matrix["names"] = $odl->get_element_from_all("name");
				$matrix["clids"] = $odl->get_element_from_all("class_id");

				// Priorities
				foreach(connection::find(array("from.class_id" => CL_SHOP_PRICE_LIST, "from" => $o->id(), "to" => $ids, "type" => "RELTYPE_PRIORITY")) as $conn)
				{
					$matrix["priorities"][$conn["to"]] = aw_math_calc::string2float($conn["data"]);
				}
			}

			// Sort rows, cols
			if(count($matrix["priorities"]) > 0)
			{
				$matrix["cols"]["locations"] = self::matrix_sort_lvl($matrix["cols"]["locations"], $matrix["priorities"]);
				$matrix["cols"]["customers"] = self::matrix_sort_lvl($matrix["cols"]["customers"], $matrix["priorities"]);
				$matrix["rows"]["products"] = self::matrix_sort_lvl($matrix["rows"]["products"], $matrix["priorities"]);
			}

			$matrix["parents"] = array();
			self::get_matrix_structure_parents($matrix["rows"]["products"] + $matrix["cols"]["customers"] + $matrix["cols"]["locations"] + $matrix["rows"]["products"], $matrix["parents"]);

			$retval[$o->id()] = $matrix;
		}		

		return $retval[$o->id()];
	}

	protected static function get_matrix_structure_add_product_to_category($id, $cat, &$products)
	{
		foreach($products as $product => $subproducts)
		{
			if($product == $cat)
			{
				$products[$product][$id] = array();
			}
			self::get_matrix_structure_add_product_to_category($id, $cat, $products[$product]);
		}
	}

	protected static function get_matrix_structure_parents($data, &$retval, $parents = array())
	{
		foreach($data as $k => $v)
		{
			$retval[$k] = array_merge(safe_array(ifset($retval, $k)), $parents);
			self::get_matrix_structure_parents($v, &$retval, $parents + array($k => $k));
		}
	}

	protected static function matrix_sort_lvl($data, $priorities)
	{
		$_priorities = array();
		foreach($priorities as $k => $v)
		{
			$_priorities[] = "'$k' => '$v'";
		}

		$cmp = create_function('$a, $b', '$p = array('.implode(",", $_priorities).'); return (float)ifset($p, $b) - (float)ifset($p, $a);');
		uksort($data, $cmp);
		foreach($data as $k => $v)
		{
			$data[$k] = self::matrix_sort_lvl($v, $priorities);
		}
		return $data;
	}

	protected static function get_matrix_ids($d)
	{
		$r = array();
		foreach($d as $k => $v)
		{
			$r[$k] = $k;
			$r = array_merge($r, self::get_matrix_ids($v));
		}
		return $r;
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
				"class_id" => CL_SHOP_PRICE_LIST_CONDITION,
				"price_list" => $this->id(),
				"lang_id" => array(),
				"site_id" => array(),
				"currency" => new obj_predicate_compare(OBJ_COMP_GREATER, 0, false, "int"),
			),
			array(
				CL_SHOP_PRICE_LIST_CONDITION => array("row", "col", "type", "value", "bonus", "quantities", "currency"),
			)
		);
		foreach($odl->arr() as $cond_id => $cond)
		{
			$this->cells[$cond["row"]][$cond["col"]]["conditions"][$cond["currency"]][$cond_id] = array(
				"type" => $cond["type"],
				"value" => $cond["value"],
				"bonus" => $cond["bonus"],
				"quantities" => $cond["quantities"],
			);
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
				foreach($cell_data["conditions"] as $currency => $conditions)
				{
					$i->vars(array(
						"currency" => $currency,
					));
					$HANDLE_CELL_ROW = "";
					foreach($conditions as $condition_id => $cond)
					{
						$quantity_conditions = $this->update_code_handle_quantities($cond["quantities"]);
						if(count($quantity_conditions) > 0)
						{
							$QUANTITY_CONDITION = "";
							$quantity_condition_count = 0;
							foreach($quantity_conditions as $quantity_condition)
							{
								$QUANTITY_CONDITION_SINGLE = "";
								$QUANTITY_CONDITION_RANGE = "";
								switch($quantity_condition["type"])
								{
									case "single":
										$i->vars(array(
											"quantity" => $quantity_condition["quantity"],
										));
										$QUANTITY_CONDITION_SINGLE .= rtrim($i->parse("QUANTITY_CONDITION_SINGLE"), "\t");
										break;
										
									case "range":
										$i->vars(array(
											"quantity_from" => $quantity_condition["quantity_from"],
											"quantity_to" => $quantity_condition["quantity_to"],
										));
										$QUANTITY_CONDITION_RANGE .= rtrim($i->parse("QUANTITY_CONDITION_RANGE"), "\t");
										break;
								}
								$i->vars(array(
									"QUANTITY_CONDITION_SINGLE" => $QUANTITY_CONDITION_SINGLE,
									"QUANTITY_CONDITION_RANGE" => $QUANTITY_CONDITION_RANGE,
								));							
								$QUANTITY_CONDITION .= rtrim($i->parse("QUANTITY_CONDITION".(++$quantity_condition_count === 1 ? "_FIRST" : "")), "\t");
							}
							$i->vars(array(
								"QUANTITY_CONDITION_FIRST" => "",
								"QUANTITY_CONDITION" => $QUANTITY_CONDITION,
							));
							$i->vars(array(
								"QUANTITY_CONDITION_START" => rtrim($i->parse("QUANTITY_CONDITION_START"), "\t"),
								"QUANTITY_CONDITION_END" => rtrim($i->parse("QUANTITY_CONDITION_END"), "\t"),
							));
						}
						$i->vars(array(
							"condition_id" => $condition_id,
							"type" => $cond["type"],
							"price_formula" => $cond["value"],
							"bonus_formula" => $cond["bonus"],
						));
						$HANDLE_CELL_ROW .= rtrim($cond["type"] ? $i->parse("HANDLE_CELL_ROW_CUSTOM") : $i->parse("HANDLE_CELL_ROW_AUTO"), "\t");
					}
					if(strlen($HANDLE_CELL_ROW) > 0)
					{
						$i->vars(array(
							"row" => $row,
							"col" => $col,
							"HANDLE_CELL_ROW_CUSTOM" => "",
							"HANDLE_CELL_ROW_AUTO" => $HANDLE_CELL_ROW,
						));
						$HANDLE_CELL .= rtrim($i->parse("HANDLE_CELL"), "\t");
					}
				}
			}
		}
		$i->vars(array(
			"passing_order" => "'".implode("','", array_merge(array_keys(safe_array($this->meta("matrix_col_order"))), array("default")))."'",
			"PARENTS" => $PARENTS,
			"PRIORITIES" => $PRIORITIES,
			"HANDLE_CELL" => $HANDLE_CELL,
		));


		$this->set_prop("code", $i->parse());
		$this->save();
		arr($i->parse(), true, true);
	}

	protected function update_code_handle_quantities($str)
	{
		$retval = array();
		if(strlen($str) > 0)
		{
			foreach(explode(",", $str) as $_str)
			{
				if(($hq = $this->update_code_handle_quantity(trim($_str))) !== false)
				{
					$retval[] = $hq;
				}
			}
		}
		return $retval;
	}

	protected function update_code_handle_quantity($str)
	{
		//	1, 27, 63, 14 etc...
		if(is_numeric($str))
		{
			return array(
				"type" => "single",
				"quantity" => (float)$str,
			);
		}
		// 10-80, 17-19, 100-200 etc...
		elseif(strpos($str, "-") !== false)
		{
			list($from, $to) = explode("-", $str);
			return array(
				"type" => "range",
				"quantity_from" => $from,
				"quantity_to" => $to,
			);
		}
		else
		{
			return false;
		}
	}

	protected function update_code_find_closest_cell_with_conditions($data)
	{
		if (count($data["conditions"]))
		{
			return $data;
		}
		elseif (!empty($data["parent"]) && isset($this->cells[$data["parent"]["row"]][$data["parent"]["col"]]))
		{
			return $this->update_code_find_closest_cell_with_conditions($this->cells[$data["parent"]["row"]][$data["parent"]["col"]]);
		}
		else
		{
			return false;
		}
	}

	protected function update_code_add_cell($row, $col, $parent = array(), $subrows = array(), $subcols = array())
	{
		$this->cells[$row][$col] = array(
			"row" => $row,
			"col" => $col,
			"parent" => $parent,
			"conditions" => array(),
		);		
		foreach($subcols as $_col => $_subcols)
		{
			$this->update_code_add_cell($row, $_col, array("row" => $row, "col" => $col), array(), $_subcols);
		}
		foreach($subrows as $_row => $_subrows)
		{
			$this->update_code_add_cell($_row, $col, array("row" => $row, "col" => $col), $_subrows, array());
		}
	}

	public static function evaluate_price_list_conditions_auto($old_price, $bonus, $price_formula, $bonus_formula)
	{
		$price_formula = trim($price_formula);
		$bonus_formula = trim($bonus_formula);
		$price = $old_price;

		// Handle price formula
		if(substr($price_formula, -1) === "%")		// relative
		{
			$price = $price * (1 + aw_math_calc::string2float($price_formula) / 100);
		}
		elseif(substr($price_formula, 0, 1) === "+" || substr($price_formula, 0, 1) === "-")	// absolute +-
		{
			$price += aw_math_calc::string2float($price_formula);
		}
		elseif(strlen($price_formula) > 0)	// absolute price
		{
			$price = aw_math_calc::string2float($price_formula);
		}

		// Handle bonus formula
		if(substr($bonus_formula, -1) === "%")		// relative
		{
			$bonus += ($price - $old_price) * (aw_math_calc::string2float($bonus_formula) / 100);
		}
		elseif(substr($bonus_formula, 0, 1) === "+" || substr($bonus_formula, 0, 1) === "-")	// absolute +-
		{
			$bonus += aw_math_calc::string2float($bonus_formula);
		}
		elseif(strlen($bonus_formula) > 0)	// absolute bonus
		{
			$bonus = aw_math_calc::string2float($bonus_formula);
		}

		return array($price, $bonus);
	}

	public function prioritize()
	{
		$this->matrix_structure = $this->get_matrix_structure($this);
		foreach($this->matrix_structure["cols"]["customers"] as $id => $children)
		{
			$this->prioritize_level($id, $children);
		}
		foreach($this->matrix_structure["cols"]["locations"] as $id => $children)
		{
			$this->prioritize_level($id, $children);
		}
		foreach($this->matrix_structure["rows"]["products"] as $id => $children)
		{
			$this->prioritize_level($id, $children);
		}
	}

	protected function prioritize_level($id, $children, $parent_priority = 0)
	{
		if(empty($this->matrix_structure["priorities"][$id]))
		{
			$this->matrix_structure["priorities"][$id] = $parent_priority + 1000;
			$this->connect(array(
				"to" => $id,
				"type" => "RELTYPE_PRIORITY",
				"data" => $this->matrix_structure["priorities"][$id],
			));
		}

		foreach($children as $_id => $_children)
		{
			$this->prioritize_level($_id, $_children, $this->matrix_structure["priorities"][$id]);
		}
	}
}

?>
