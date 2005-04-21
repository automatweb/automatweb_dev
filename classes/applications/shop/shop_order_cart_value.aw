<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order_cart_value.aw,v 1.5 2005/04/21 08:48:47 kristo Exp $
// shop_order_cart_value.aw - Poe ostukorvi v&auml;&auml;rtus 
/*

@classinfo syslog_type=ST_SHOP_ORDER_CART_VALUE relationmgr=yes no_status=1 no_comment=1

@default table=objects
@default group=general

*/

class shop_order_cart_value extends class_base
{
	function shop_order_cart_value()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_order_cart_value",
			"clid" => CL_SHOP_ORDER_CART_VALUE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !shows the object
	function show($arr = array())
	{
		$soc = get_instance(CL_SHOP_ORDER_CART);

		$count = 0;
		foreach($soc->get_items_in_cart() as $dat)
		{
			$count += $dat;
		}

		list($t1, $t2) = $soc->get_cart_value(true);
		$this->read_template("show.tpl");
		$this->vars(array(
			"value" => number_format($t1,2),
			"prod_value" => number_format($t2, 2),
			"count" => $count
		));

		if ($count > 0)
		{
			$this->vars(array(
				"HAS_ITEMS" => $this->parse("HAS_ITEMS")
			));
		}
		else
		{
			$this->vars(array(
				"NO_ITEMS" => $this->parse("NO_ITEMS")
			));
		}
		return $this->parse();
	}
}
?>
