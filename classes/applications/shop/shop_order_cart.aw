<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order_cart.aw,v 1.5 2004/05/27 08:51:27 kristo Exp $
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
		@param section optional type=int
	**/
	function show($arr)
	{
		extract($arr);
		$this->read_template("show.tpl");

		$soce = new aw_array(aw_global_get("soc_err"));
		$soce_arr = $soce->get();
		foreach($soce->get() as $prid => $errmsg)
		{
			$this->vars(array(
				"msg" => $errmsg["msg"],
				"prod_name" => $errmsg["prod_name"],
				"prod_id" => $errmsg["prod_id"],
				"must_order_num" => $errmsg["must_order_num"],
				"ordered_num" => $errmsg["ordered_num"]
			));
			$err .= $this->parse("ERROR");
		}
		$this->vars(array(
			"ERROR" => $err
		));

		aw_session_del("soc_err");

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
					"quantity" => $quant,
					"oc_obj" => $oc
				))
			));

			$total += ($quant * $inst->get_price($i));

			$str .= $this->parse("PROD");
		}

		$this->vars(array(
			"PROD" => $str,
			"total" => number_format($total, 2),
			"reforb" => $this->mk_reforb("submit_add_cart", array("oc" => $arr["oc"], "update" => 1, "section" => $arr["section"]))
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

		$order_ok = true;
		$awa = new aw_array($arr["add_to_cart"]);
		foreach($awa->get() as $iid => $quant)
		{
			$i_o = obj($iid);
			$i_i = $i_o->instance();

			if ($arr["update"] == 1)
			{
				$cc = $quant;
			}
			else
			{
				$cc = $_SESSION["cart"]["items"][$iid] + $quant;
			}

			$mon = $i_i->get_must_order_num($i_o);
			if ($mon)
			{
				if (($cc % $mon) != 0)
				{
					$soce = aw_global_get("soc_err");
					if (!is_array($soce))
					{
						$soce = array();
					}

					$soce[$iid] = array(
						"msg" => $i_o->name()." peab tellima ".$mon." kaupa, hetkel kokku $cc!",
						"prod_name" => $i_o->name(),
						"prod_id" => $i_o->id(),
						"must_order_num" => $mon,
						"ordered_num" => $cc,
						"ordered_num_enter" => $quant
					);
					aw_session_set("soc_err", $soce);
					$order_ok = false;
				}
			}
		}

		if (!$order_ok)
		{
			if (!$arr["return_url"])
			{
				header("Location: ".$this->mk_my_orb("show_cart", array("oc" => $arr["oc"], "section" => $arr["section"])));
			}
			else
			{
				header("Location: ".$arr["return_url"]);
			}
			die();
		}

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
			$this->start_order();
			return $this->mk_my_orb("show", array("id" => $ordid, "section" => $arr["section"]), "shop_order");
		}
		else
		{
			return $this->mk_my_orb("show_cart", array("oc" => $arr["oc"], "section" => $arr["section"]));
		}
	}

	function do_create_order_from_cart($oc)
	{
		$so = get_instance("applications/shop/shop_order");
		$oc = obj($oc);
		$so->start_order(obj($oc->prop("warehouse")), $oc);

		$awa = new aw_array($_SESSION["cart"]["items"]);
		foreach($awa->get() as $iid => $quant)
		{
			$so->add_item($iid, $quant);
		}

		return $so->finish_order();
	}

	function start_order()
	{
		$_SESSION["cart"] = array();
	}

	function add_item($iid, $quant)
	{
		$_SESSION["cart"]["items"][$iid] += $quant;
	}

	function get_cart_value()
	{
		$total = 0;

		$awa = new aw_array($_SESSION["cart"]["items"]);
		foreach($awa->get() as $iid => $quant)
		{
			$i = obj($iid);
			$inst = $i->instance();
			$total += ($quant * $inst->get_price($i));
		}

		return $total;
	}
}
?>