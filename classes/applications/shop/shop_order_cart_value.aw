<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order_cart_value.aw,v 1.2 2004/08/19 07:52:51 kristo Exp $
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
	function show($arr)
	{
		$soc = get_instance(CL_SHOP_ORDER_CART);
		$ob = new object($arr["id"]);

		$count = 0;
		foreach($soc->get_items_in_cart() as $dat)
		{
			$count += $dat;
		}

		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
			"value" => number_format($soc->get_cart_value(),2),
			"count" => $count
		));
		return $this->parse();
	}
}
?>
