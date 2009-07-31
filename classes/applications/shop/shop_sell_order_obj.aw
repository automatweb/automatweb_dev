<?php

class shop_sell_order_obj extends _int_object
{
	public function get_sum()
	{
		$sum = 0;
		foreach($this->connections_from(array("type" => "RELTYPE_ROW")) as $c)
		{
			$row = $c->to();
			$c_sum = $row->amount * $row->price;
			$sum += $c_sum;
		}
		return $sum;
	}

	public function add_row($data)
	{
		$o = new object();
		$o->set_class_id(CL_SHOP_ORDER_ROW);
		$o->set_name($this->name()." ".t("rida"));
		$o->set_parent($this->id());
		$o->set_prop("prod" , $data["product"]);
		$o->set_prop("items" , $data["amount"]);
		$o->save();
		$this->connect(array(
			"to" => $o->id(),
			"type" => "RELTYPE_ROW"
		));
	}
}

?>
