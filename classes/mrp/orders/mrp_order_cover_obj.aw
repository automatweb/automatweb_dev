<?php

class mrp_order_cover_obj extends _int_object
{
	public function get_price_for_order_and_amt($order, $amt)
	{
		$cover = 0;
		$cover += $this->prop("cover_amt");
		$cover += ($this->prop("cover_tot_price_pct") * $order->get_total_price_for_amt($amt)) / 100.0;
		$cover += $amt * $this->prop("cover_amt_piece");
		return $cover;
	}
}

?>
