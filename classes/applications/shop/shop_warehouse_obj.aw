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
}