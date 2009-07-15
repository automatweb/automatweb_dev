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
}

?>
