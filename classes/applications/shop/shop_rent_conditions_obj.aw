<?php

class shop_rent_conditions_obj extends _int_object
{
	public function prop($k)
	{
		switch($k)
		{
			case "min_amt":
			case "max_amt":
			case "min_payment":
			case "prepayment_interest":
			case "yearly_interest":
			case "period_min":
			case "period_max":
			case "period_step":
				return aw_math_calc::string2float(parent::prop($k));

			default:
				return parent::prop($k);
		}
	}

	public function set_prop($k, $v)
	{
		switch($k)
		{
			case "valid_to":
				$d = explode("-", date("d-m-Y", $v));
				return $v = mktime(23, 59, 59, $d[0], $d[1], $d[2]);

			default:
				return parent::set_prop($k, $v);
		}
	}
}

?>
