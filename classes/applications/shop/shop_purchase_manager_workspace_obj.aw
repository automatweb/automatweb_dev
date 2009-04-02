<?php

class shop_purchase_manager_workspace_obj extends _int_object
{
	public function get_warehouse_ids()
	{
		$warehouse_ids = array();
		foreach ($this->connections_from(array("type" => "RELTYPE_WAREHOUSE")) as $c)
		{
			$warehouse_ids[] = $c->prop("to");
		}
		return $warehouse_ids;
	}

	/**
		@attrib name=order_products api=1
		
		@param products required type=array
		@param date required type=int
		@param job optional type=oid
	
		@comment
			arr[products] is an array of arrays ( product => oid, unit => oid, amount => int )
			when unit is not defined there, product's default unit is used

		@returns order object that was created
	**/
	public function order_products($arr)
	{
		$o = $this->create_order(array(
			"name" => sprintf("Tellimus %s", date("d.m.Y", $arr["date"])),
			"date" => $arr["date"],
			"job" => $arr["job"],
		));
		foreach($arr["products"] as $product)
		{
			$row = $this->create_order_row($product, $o);
		}
	}

	/**
		@attrib name=order_product api=1

		@param product required type=oid
		@param unit optional type=oid
		@param amount required type=int
		@param date required type=int
		@param job optional type=oid

		@returns order object that was created
	**/
	public function order_product($arr)
	{
		$o = $this->create_order(array(
			"name" => sprintf("%s tellimus %s", obj($arr["product"]->name()), date("d.m.Y", $arr["date"])),
			"date" => $arr["date"],
			"job" => $arr["job"],
		));
		$row = $this->create_order_row($arr, $o);
		return $o;
	}

	private function create_order($arr)
	{
		$o = obj();
		$o->set_class_id(CL_SHOP_SELL_ORDER);
		$o->set_parent($this->id());
		$o->set_name($arr["name"]);
		$o->set_prop("date", $arr["date"]);
		if($arr["job"])
		{
			$o->set_prop("job", $arr["job"]);
		}
		$o->save();
		return $o;
	}

	private function create_order_row($arr, $o)
	{
		
		$row = obj();
		$row->set_class_id(CL_SHOP_ORDER_ROW);
		$row->set_parent($o->id());
		$row->set_name(sprintf(t("%s rida"), $o->name()));
		$row->set_prop("prod", $arr["product"]);
		$row->set_prop("amount", $arr["amount"]);
		$unit = $arr["unit"];
		if(!$unit)
		{
			$units = $po->instance()->get_units($po);
			$unit = $units[0];
		}
		$row->set_prop("unit", $unit);
		$row->save();
		$o->connect(array(
			"to" => $row,
			"type" => "RELTYPE_ROW",
		));
	}

	/**
	@attrib name=get_order_rows
	
	@param product optional type=clid
	@param date optional type=int
	
	@comment
		Returns object list of orders rows by specified date and/or product
	**/
	function get_order_rows($arr)
	{
		$params = array(
			"class_id" => CL_SHOP_ORDER_ROW,
			"RELTYPE_ROW(CL_SHOP_SELL_ORDER).closed" => new obj_predicate_not(1),
			"site_id" => array(),
			"lang_id" => array(),
		);
		if($arr["date"])
		{
			$params["RELTYPE_ROW(CL_SHOP_SELL_ORDER).date"] = new obj_predicate_compare(OBJ_COMP_BETWEEN_INCLUDING, time(), $arr["date"]);
		}
		else
		{
			$params["RELTYPE_ROW(CL_SHOP_SELL_ORDER).date"] = new obj_predicate_compare(OBJ_COMP_GREATER, time());
		}
		if($arr["product"])
		{
			$params["prod"] = $arr["product"];
		}
		return new object_list($params);
	}
}

?>
