<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order_cart.aw,v 1.2 2004/04/14 14:37:31 kristo Exp $
// shop_order_cart.aw - Poe ostukorv 
/*

@classinfo syslog_type=ST_SHOP_ORDER_CART relationmgr=yes no_status=1

@default table=objects
@default group=general

@property prod_layout type=relpicker reltype=RELTYPE_PROD_LAYOUT field=meta method=serialize
@caption Kujundus, mida korvis kasutatakse

@reltype PROD_LAYOUT value=1 clid=CL_SHOP_PRODUCT_LAYOUT
@caption toote kujundus
*/

class shop_order_cart extends class_base
{
	function shop_order_cart()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_order_cart",
			"clid" => CL_SHOP_ORDER_CART
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

	/** shows the cart to user
		
		@attrib name=show_cart

		@param oc optional type=int
	**/
	function show($arr)
	{
		extract($arr);
		$this->read_template("show.tpl");

		$oc = obj($oc);

		// get cart to user from oc
		$cart_o = obj($oc->prop("cart"));

		// now get item layout from cart
		$layout = obj($cart_o->prop("prod_layout"));

		$total = 0;

		$awa = new aw_array($_SESSION["cart"]["items"]);
		foreach($awa->get() as $iid => $quant)
		{
			if ($quant < 1)
			{
				continue;
			}

			$i = obj($iid);
			$inst = $i->instance();
				
			$this->vars(array(
				"prod_html" => $inst->do_draw_product(array(
					"layout" => $layout,
					"prod" => $i,
					"quantity" => $quant
				))
			));

			$total += ($quant * $inst->get_price($i));

			$str .= $this->parse("PROD");
		}

		$this->vars(array(
			"PROD" => $str,
			"total" => $total,
			"reforb" => $this->mk_reforb("submit_add_cart", array("oc" => $arr["oc"], "update" => 1))
		));

		return $this->parse();
	}

	/** order submit page, must add items to cart

		@attrib name=submit_add_cart

		@param oc required type=int acl=view
		@param add_to_cart optional
		@param is_update optional type=int

	**/
	function submit_add_cart($arr)
	{
		extract($arr);
		$oc = obj($oc);

		// get cart to user from oc
		$cart_o = obj($oc->prop("cart"));

		// now get item layout from cart
		$layout = obj($cart_o->prop("prod_layout"));

		$awa = new aw_array($arr["add_to_cart"]);
		foreach($awa->get() as $iid => $quant)
		{
			if ($arr["update"] == 1)
			{
				$_SESSION["cart"]["items"][$iid] = $quant;
			}
			else
			{
				$_SESSION["cart"]["items"][$iid] += $quant;
			}
		}

		if (!empty($arr["confirm_order"]))
		{
			// do confirm order and show user
			$ordid = $this->do_create_order_from_cart($arr["oc"]);
			return $this->mk_my_orb("show", array("id" => $ordid), "shop_order");
		}
		else
		{
			return $this->mk_my_orb("show_cart", array("oc" => $arr["oc"]));
		}
	}

	function do_create_order_from_cart($oc)
	{
		$so = get_instance("applications/shop/shop_order");
		$oc = obj($oc);
		$so->start_order(obj($oc->prop("warehouse")));

		$awa = new aw_array($_SESSION["cart"]["items"]);
		foreach($awa->get() as $iid => $quant)
		{
			$so->add_item($iid, $quant);
		}

		return $so->finish_order();
	}
}
?>