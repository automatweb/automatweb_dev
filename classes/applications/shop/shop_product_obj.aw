<?php

class shop_product_obj extends _int_object
{

	/** Sets the price for the product by currency
		@attrib api=1 params=pos

		@param currency required type=cl_currency
			The currency object to set the price for

		@param price required type=double
			The price

		@comment
			You will need to save the object yourself after this
	**/
	public function price_set_by_currency(object $currency, $price)
	{
		$curp = safe_array($this->meta("cur_prices"));
		$curp[$currency->id()] = (double)$price;
		$this->set_meta("cur_prices", $curp);
	}

	/** Returns the price as a double for the given currency
		@attrib api=1 params=pos

		@param currency required type=cl_currency
			The currency object to return the price for

		@returns
			the price of the product for the given currency
	**/
	public function price_get_by_currency($currency)
	{
		$curp = safe_array($this->meta("cur_prices"));
		return (double)$curp[$currency->id()];
	}

	function set_prop($k, $v)
	{
		if($k == "price" || $k == "purchase_price")
		{
			$tzeros = strrpos($v, ".") !== false ? strlen($v) - strrpos($v, ".") - 1 : 0;
			parent::set_meta($k."_trailing_zeros", $tzeros);
		}
		return parent::set_prop($k, $v);
	}

	function prop($k)
	{
		if($k == "price" || $k == "purchase_price")
		{
			if(parent::meta($k."_trailing_zeros") > 0)
			{
				return sprintf("%.".parent::meta($k."_trailing_zeros")."f", parent::prop($k));
			}
		}
		return parent::prop($k);
 	}

	/** Get units that can be used to measure product quantity
		@attrib api=1
		@returns array of CL_UNIT
			array(0=> unit1, 1=> unit2, etc).
			First unit is the default/base unit
		@comment Some of the results may be undefined, beware of that.//!!!!!! ???
	**/
	public function get_units()
	{
		if($meta_units = $this->meta("units"))
		{
			$units = $meta_units;
		}
		else
		{
			if($dc = $this->meta("def_cat"))
			{
				$cato = obj($dc);
			}
			elseif($dco = $this->get_first_obj_by_reltype("RELTYPE_CATEGORY"))
			{
				$cato = $dco;
			}
			if($cato && $meta_units = $cato->meta("units"))
			{
				$units = $meta_units;
			}
		}

		if(empty($units))
		{
			$units = array();
		}

		foreach ($units as $key => $oid)
		{
			$units[$key] = new object($oid);
		}

		return $units;
	}
}
