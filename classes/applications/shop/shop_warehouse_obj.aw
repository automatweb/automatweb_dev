<?php

class shop_warehouse_obj extends _int_object
{
	const STATCALC_FIFO = 1;
	const STATCALC_WEIGHTED_AVERAGE = 2;

	function get_status_calc_options()
	{
		return array(
			self::STATCALC_FIFO => t("FIFO"),
			self::STATCALC_WEIGHTED_AVERAGE => t("Kaalutud keskmine")
		);
	}

	/** Gets warehouse product movements
		@attrib api=1 params=name
		@param category optional type=oid
			Product category id
		@param from optional type=int
			timestamp
		@param to optional type=int
			timestamp
		@param after_time optional type=int
		@returns object list
	**/
	public function get_movements($arr)
	{
		$filter = array();
		$filter["class_id"] = CL_SHOP_WAREHOUSE_MOVEMENT;
		$filter["lang_id"] = array();
		$filter["site_id"] = array();

		if(is_oid($arr["category"]))
		{
			$ot = new object_tree(array(
				"parent" => $arr["category"],
				"class_id" => CL_SHOP_PRODUCT_CATEGORY,
				"sort_by" => "objects.jrk"
			));
			$cat_ids = $ot->ids();
			if(is_array($cat_ids) && sizeof($cat_ids))
			{
				$cat_ids[] = $arr["category"];
				$arr["category"] = $cat_ids;
			}
			$filter["product.RELTYPE_CATEGORY"] = $arr["category"];
		}
		
		if($arr["after_time"])
		{
			$arr["from"] = $arr["to"]+1;
			$arr["to"] = time()*2;
		}

		if($arr["from"] && $arr["to"])
		{
			$filter["date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $arr["from"], $arr["to"]);
		}
		else
		{
			if($arr["from"])
			{
				$filter["date"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $arr["from"]);
			}
			if($arr["to"])
			{
				$filter["date"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $arr["to"]);

			}
		}
;
		return new object_list($filter);
	}

	/** Gets warehouse delivery notes
		@attrib api=1 params=name
		@returns object list
	**/
	public function get_delivery_note_rows($arr)
	{
		$filter = array();
		$filter["class_id"] = CL_SHOP_DELIVERY_NOTE;
		$filter["lang_id"] = array();
		$filter["site_id"] = array();
/*
		if(is_oid($arr["category"]))
		{
			$ot = new object_tree(array(
				"parent" => $arr["category"],
				"class_id" => CL_SHOP_PRODUCT_CATEGORY,
				"sort_by" => "objects.jrk"
			));
			$cat_ids = $ot->ids();
			if(is_array($cat_ids) && sizeof($cat_ids))
			{
				$cat_ids[] = $arr["category"];
				$arr["category"] = $cat_ids;
			}
			$filter["product.RELTYPE_CATEGORY"] = $arr["category"];
		}

		if($arr["from"] && $arr["to"])
		{
			$filter["date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $arr["from"], $arr["to"]);
		}
		else
		{
			if($arr["from"])
			{
				$filter["date"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $arr["from"]);
			}
			if($arr["to"])
			{
				$filter["date"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $arr["to"]);

			}
		}*/

		return new object_list($filter);
	}

	/** Gets all warehouse products
		@attrib api=1 params=name
		@param category optional type=oid
			Product category id
		@returns object list
	**/
	public function get_products($arr)
	{
		$filter = array();
		$filter["class_id"] = CL_SHOP_PRODUCT;
		$filter["lang_id"] = array();
		$filter["site_id"] = array();

		if(is_oid($arr["category"]))
		{
			$ot = new object_tree(array(
				"parent" => $arr["category"],
				"class_id" => CL_SHOP_PRODUCT_CATEGORY,
				"sort_by" => "objects.jrk"
			));
			$cat_ids = $ot->ids();
			if(is_array($cat_ids) && sizeof($cat_ids))
			{
				$cat_ids[] = $arr["category"];
				$arr["category"] = $cat_ids;
			}
			$filter["CL_SHOP_PRODUCT.RELTYPE_CATEGORY"] = $arr["category"];
		}
		return new object_list($filter);
	}

	/** Gets all warehouse product packagings
		@attrib api=1 params=name
		@param category optional type=oid
			Product category id
		@returns object list
	**/
	public function get_packagings($arr)
	{
		$filter = array();
		$filter["class_id"] = CL_SHOP_PRODUCT_PACKAGING;
		$filter["lang_id"] = array();
		$filter["site_id"] = array();

		if(is_oid($arr["category"]))
		{
			$ot = new object_tree(array(
				"parent" => $arr["category"],
				"class_id" => CL_SHOP_PRODUCT_CATEGORY,
				"sort_by" => "objects.jrk"
			));
			$cat_ids = $ot->ids();
			if(is_array($cat_ids) && sizeof($cat_ids))
			{
				$cat_ids[] = $arr["category"];
				$arr["category"] = $cat_ids;
			}
			$filter["CL_SHOP_PRODUCT.RELTYPE_CATEGORY"] = $arr["category"];
		}
		return new object_list($filter);
	}

	function get_inventories($arr)
	{
		extract($arr);
		if(!$arr["warehouses"])
		{
			$arr["warehouses"] = array($this->id());
		}
		$params = array(
			"class_id" => CL_SHOP_WAREHOUSE_INVENTORY,
			"lang_id" => array(),
			"site_id" => array(),
			"warehouse" => $arr["warehouses"],
		);
//		$group = $this->get_search_group($arr);
//		if($n = $arr["request"][$group."_s_name"])
//		{
//			$params["name"] = "%".$n."%";
//		}
//		if($s = $arr["request"][$group."_s_status"])
//		{
//			if($s == STORAGE_FILTER_CONFIRMED)
//			{
//				$params["confirmed"] = 1;
//			}
//			elseif($s == STORAGE_FILTER_UNCONFIRMED)
//			{
//				$params["confirmed"] = new obj_predicate_not(1);
//			}
//		}
//		$from = date_edit::get_timestamp($arr["request"][$group."_s_from"]);
//		$to = date_edit::get_timestamp($arr["request"][$group."_s_to"]);
		if($from > 0 && $to > 0)
		{
			$to += 24 * 60 * 60 -1;
			$params["date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, $from, $to);
		}
		elseif($from > 0)
		{
			$params["date"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $from);
		}
		elseif($to > 0)
		{
			$to += 24 * 60 * 60 -1;
			$params["date"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $to);
		}
		$ol = new object_list($params);
		return $ol;
	}

	/** get product's warehouse amounts
		@attrib api=1
		@param prod optional type=int
		@param single optional type=int
		@param unit optional type=int
		@param singlecode optional type=int
		@returns object list of shop_warehouse_amount objects
	**/
	function get_amounts($arr)
	{
		if(isset($arr["prod"]))
		{
			$params["product"] = $arr["prod"];
		}
		if(isset($arr["single"]))
		{
			$params["single"] = $arr["single"];
		}
		elseif($this->can("view", $arr["prod"]))
		{
			$po = obj($arr["prod"]);
			if(!$po->prop("serial_number_based") && !$po->prop("order_based"))
			{
				$params["single"] = null;
			}
		}
		if(count($params))
		{
			if($arr["unit"])
			{
				$params["unit"] = $arr["unit"];
			}
			$params["warehouse"] = $this->id();
			if($arr["singlecode"])
			{
				$params["CL_SHOP_WAREHOUSE_AMOUNT.single.code"] = $arr["singlecode"];
			}
			$params["class_id"] = CL_SHOP_WAREHOUSE_AMOUNT;
			$params["lang_id"] = array();
			$params["site_id"] = array();
			$ol = new object_list($params);
			return $ol;
		}
		return false;
	}

	/** get product's warehouse amount
		@attrib api=1
		@param prod optional type=int
		@param single optional type=int
		@param unit optional type=int
		@param singlecode optional type=int
		@returns double
	**/
	function get_amount($arr)
	{
		$amounts = $this->get_amounts(array(
			"prod" => $arr["prod"],
		));
		$count = 0;
		if($amounts->count())
		{
			$amount = reset($amounts->arr());
			$count = $amount->prop("amount");
		}
		return $count;
	}

}