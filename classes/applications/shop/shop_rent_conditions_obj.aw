<?php

class shop_rent_conditions_obj extends _int_object
{
	/**
		Returns array of all possible rent periods
	**/
	public function rent_periods()
	{
		$periods = array();

		for($i = $this->prop("period_min"); $i <= $this->prop("period_max"); $i += max($this->prop("period_step"), 1))
		{
			$periods[$i] = $i;
		}

		return $periods;
	}

	/**
		@attrib params=name

		@param core_sum required type=float

		@param rent_period required type=int

		@param precision optional type=int default=2
	**/
	public function calculate_rent($core_sum, $period, $precision = 2)
	{
		if($core_sum < $this->prop("min_amt"))
		{
			return array(
				"error" => sprintf(t("Minimaalne lubatud summa j&auml;relmaksuks on %s!"), $this->prop("rent_min_amt")),
			);
		}

		if($core_sum > $this->prop("max_amt"))
		{
			return array(
				"error" => sprintf(t("Maksimaalne lubatud summa j&auml;relmaksuks on %s!"), $this->prop("rent_max_amt")),
			);
		}

		if(!in_array($period, $this->rent_periods()))
		{
			return array(
				"error" => t("Valitud j&auml;relmaksuperiood ei ole lubatud!"),
			);
		}

		return array(
			"prepayment" => $prepayment = round($core_sum * $this->prop("prepayment_interest") / 100, $precision),
			"single_payment" => $single_payment = round(max($core_sum - $prepayment, 0) * (1 + $this->prop("yearly_interest") / 12 / 100 * $period) / $period, $precision),
			"sum_rent" => $single_payment * $period + $prepayment,
		);
	}
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

	public function description()
	{
		return is_oid($this->id()) ? sprintf(t("%s %s kuni %s %s (min %s %s)<br />Sissemakse %s%%, intress %s%% aastas<br />%s kuni %s kuud (samm %s)"),
			$unit = $this->prop("currency.symbol"),
			$this->prop("min_amt"),
			$unit,
			$this->prop("max_amt"),
			$unit,
			$this->prop("min_payment"),
			$this->prop("prepayment_interest"),
			$this->prop("yearly_interest"),
			$this->prop("period_min"),
			$this->prop("period_max"),
			$this->prop("period_step")
		) : t("M&auml;&auml;ramata");
	}
}

?>
