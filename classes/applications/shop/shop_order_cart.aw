<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order_cart.aw,v 1.11 2004/07/29 11:31:10 rtoomas Exp $
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
		
		@attrib name=show_cart nologin="1"

		@param oc optional type=int
		@param section optional 
	**/
	function show($arr)
	{
		extract($arr);
		$this->read_template("show.tpl");

		$soce = new aw_array(aw_global_get("soc_err"));
		$soce_arr = $soce->get();
		foreach($soce->get() as $prid => $errmsg)
		{
			if (!$errmsg["is_err"])
			{
				continue;
			}

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
		if ($arr["id"])
		{
			$cart_o = obj($arr["id"]);
		}
		else
		{
			$cart_o = obj($oc->prop("cart"));
		}

		// now get item layout from cart
		error::throw_if(!$cart_o->prop("prod_layout"), array(
			"id" => "ERR_NO_PROD_LAYOUT",
			"msg" => "shop_order_cart::show(): no product layout set for cart (".$cart_o->id().") "
		));
		$layout = obj($cart_o->prop("prod_layout"));

		$total = 0;

		$awa = new aw_array($_SESSION["cart"]["items"]);
		$show_info_page = true;
		foreach($awa->get() as $iid => $quant)
		{
			if ($quant < 1)
			{
				continue;
			}

			if (!$this->can("view", $iid))
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
					"oc_obj" => $oc,
					"is_err" => ($soce_arr[$iid]["is_err"] ? "class=\"selprod\"" : "")
				))
			));
			$show_info_page = false;

			$total += ($quant * $inst->get_price($i));

			$str .= $this->parse("PROD");
		}

		$swh = get_instance(CL_SHOP_WAREHOUSE);
		$wh_o = obj($oc->prop("warehouse"));

		// fake user data
		$wh_o->set_meta("order_cur_ud", $_SESSION["cart"]["user_data"]);

		$els = $swh->callback_get_order_current_form(array(
			"obj_inst" => $wh_o
		));

		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		foreach($els as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1
		));

		if (aw_global_get("uid") != "")
		{
			$us = get_instance("core/users/user");
			$objs = array(
				"user_data_user_" => obj($us->get_current_user()),
				"user_data_person_" => obj($us->get_current_person()),
				"user_data_org_" => obj($us->get_current_company()),
			);
			$vars = array();
			foreach($objs as $prefix => $obj)
			{
				$ops = $obj->properties();
				
				foreach($ops as $opk => $opv)
				{
					$vars[$prefix.$opk] = $opv;
				}
			}

			$this->vars($vars);
		}

		$this->vars(array(
			"user_data_form" => $html,
			"PROD" => $str,
			"total" => number_format($total, 2),
			"reforb" => $this->mk_reforb("submit_add_cart", array("oc" => $arr["oc"], "update" => 1, "section" => $arr["section"]))
		));

		if($show_info_page)
		{
			$this->vars(array(
				'info_page' => $this->parse('info_page'),
			));
		}
		else
		{
			$this->vars(array(
				'cart_page' => $this->parse('cart_page'),
			));
		}

		$ll = $lln = "";
		if (aw_global_get("uid") != "")
		{
			$ll = $this->parse("logged");
		}
		else
		{
			$lln = $this->parse("not_logged");
		}

		$this->vars(array(
			"logged" => $ll,
			"not_logged" => $lln
		));

		return $this->parse();
	}

	/** order submit page, must add items to cart

		@attrib name=submit_add_cart nologin="1"

		@param oc required type=int acl=view
		@param add_to_cart optional
		@param is_update optional type=int
		@param order_data optional 

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
						"ordered_num_enter" => $quant,
						"is_err" => true
					);
					aw_session_set("soc_err", $soce);
					$order_ok = false;
				}
			}
		}

		if (!$order_ok)
		{
			$awa = new aw_array($arr["add_to_cart"]);
			$soce = aw_global_get("soc_err");
			foreach($awa->get() as $iid => $quant)
			{
				if (isset($soce[$iid]))
				{
					continue;
				}
				$soce[$iid] = array(
					"is_err" => false,
					"ordered_num_enter" => $quant
				);
			}
			aw_session_set("soc_err", $soce);

			if (!$arr["return_url"])
			{
				if ($arr["from"] == "pre")
				{
					header("Location: ".$this->mk_my_orb("pre_finish_order", array("oc" => $arr["oc"], "section" => $arr["section"])));
				}
				else
				{
					header("Location: ".$this->mk_my_orb("show_cart", array("oc" => $arr["oc"], "section" => $arr["section"])));
				}
			}
			else
			{
				header("Location: ".$arr["return_url"]);
			}
			die();
		}

		$_SESSION["cart"]["user_data"] = $GLOBALS["user_data"];

		if ($arr["from"] == "pre" && !$arr["order_cond_ok"])
		{
			if (!$arr["return_url"])
			{
				if ($arr["from"] == "pre")
				{
					header("Location: ".$this->mk_my_orb("pre_finish_order", array("oc" => $arr["oc"], "section" => $arr["section"])));
				}
				else
				{
					header("Location: ".$this->mk_my_orb("show_cart", array("oc" => $arr["oc"], "section" => $arr["section"])));
				}
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
		$awa = new aw_array($order_data);
		foreach($awa->get() as $iid => $dat)
		{
			$_SESSION["cart"]["item_data"][$iid] = $dat;
		}

		if (!empty($arr["pre_confirm_order"]))
		{
			// go to separate page with order non modifiable and user data form below
			return $this->mk_my_orb("pre_finish_order", array("oc" => $arr["oc"], "section" => $arr["section"]));
		}
		else
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

	function do_create_order_from_cart($oc, $warehouse = NULL, $params = array())
	{
		$so = get_instance("applications/shop/shop_order");
		$oc = obj($oc);
		if ($warehouse === NULL)
		{
			if (!is_oid($oc->prop("warehouse")))
			{
				error::throw(array(
					"id" => "ERR_NO_WAREHOOS",
					"msg" => "shop_order_cart::do_creat_order_from_cart(): no warehouse set for cart ".$oc->id()."!"
				));
			}
			$warehouse = $oc->prop("warehouse");
		}
		$so->start_order(obj($warehouse), $oc);

		$awa = new aw_array($_SESSION["cart"]["items"]);
		foreach($awa->get() as $iid => $quant)
		{
			$so->add_item($iid, $quant, $_SESSION['cart']['item_data'][$iid]);
		}

		return $so->finish_order($params);
	}

	function start_order()
	{
		$_SESSION["cart"] = array();
	}

	function add_item($iid, $quant, $prod_data = array())
	{
		$_SESSION["cart"]["items"][$iid] += $quant;
		$_SESSION["cart"]["item_data"][$iid] = $prod_data;
	}

	function set_item($iid, $quant)
	{
		if ($quant == 0)
		{
			unset($_SESSION["cart"]["items"][$iid]);
		}
		else
		{
			$_SESSION["cart"]["items"][$iid] = $quant;
		}
	}

	function get_cart_value()
	{
		$total = 0;

		$awa = new aw_array($_SESSION["cart"]["items"]);
		foreach($awa->get() as $iid => $quant)
		{
			if ($this->can("view", $iid))
			{
				$i = obj($iid);
				$inst = $i->instance();
				$total += ($quant * $inst->get_calc_price($i));
			}
		}

		return $total;
	}

	function get_items_in_cart()
	{
		$awa = new aw_array($_SESSION["cart"]["items"]);
		$ret = array();
		foreach($awa->get() as $iid => $q)
		{
			if ($q > 0)
			{
				$ret[$iid] = $q;
			}
		}
		return $ret;
	}
		/* siin pannakse andmed lõplikku tabelisse */
	function get_item_in_cart($iid)
	{
		return array(
			"quant" => $_SESSION["cart"]["items"][$iid],
			"data" => $_SESSION["cart"]["item_data"][$iid]
		);
	}
	

	function clear_cart()
	{
		unset($_SESSION["cart"]);
	}

	/**

		@attrib name=pre_finish_order nologin=1

		@param oc required
		@param section optional

	**/
	function pre_finish_order($arr)
	{
		extract($arr);
		$this->read_template("show_pre_finish.tpl");

		$soce = new aw_array(aw_global_get("soc_err"));
		$soce_arr = $soce->get();
		foreach($soce->get() as $prid => $errmsg)
		{
			if (!$errmsg["is_err"])
			{
				continue;
			}

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
		if ($arr["id"])
		{
			$cart_o = obj($arr["id"]);
		}
		else
		{
			$cart_o = obj($oc->prop("cart"));
		}

		// now get item layout from cart
		error::throw_if(!$cart_o->prop("prod_layout"), array(
			"id" => "ERR_NO_PROD_LAYOUT",
			"msg" => "shop_order_cart::show(): no product layout set for cart (".$cart_o->id().") "
		));
		$layout = obj($cart_o->prop("prod_layout"));
		$layout->set_prop("template", "prod_pre_confirm.tpl");

		$total = 0;

		$awa = new aw_array($_SESSION["cart"]["items"]);
		foreach($awa->get() as $iid => $quant)
		{
			if ($quant < 1 || !$this->can("view", $iid))
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
					"oc_obj" => $oc,
					"is_err" => ($soce_arr[$iid]["is_err"] ? "class=\"selprod\"" : "")
				))
			));

			$total += ($quant * $inst->get_price($i));

			$str .= $this->parse("PROD");
		}

		$swh = get_instance(CL_SHOP_WAREHOUSE);
		$wh_o = obj($oc->prop("warehouse"));

		// fake user data
		$wh_o->set_meta("order_cur_ud", $_SESSION["cart"]["user_data"]);

		$els = $swh->callback_get_order_current_form(array(
			"obj_inst" => $wh_o
		));

		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		foreach($els as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1
		));

		if (aw_global_get("uid") != "")
		{
			$us = get_instance("core/users/user");
			$objs = array(
				"user_data_user_" => obj($us->get_current_user()),
				"user_data_person_" => obj($us->get_current_person()),
				"user_data_org_" => obj($us->get_current_company()),
			);
			$vars = array();
			foreach($objs as $prefix => $obj)
			{
				$ops = $obj->properties();
				
				foreach($ops as $opk => $opv)
				{
					$vars[$prefix.$opk] = $opv;
				}
			}
			$this->vars($vars);
		}

		$this->vars(array(
			"user_data_form" => $html,
			"PROD" => $str,
			"total" => number_format($total, 2),
			"reforb" => $this->mk_reforb("submit_add_cart", array("oc" => $arr["oc"], "update" => 1, "section" => $arr["section"], "from" => "pre"))
		));

		$ll = $lln = "";
		if (aw_global_get("uid") != "")
		{
			$ll = $this->parse("logged");
		}
		else
		{
			$lln = $this->parse("not_logged");
		}

		$this->vars(array(
			"logged" => $ll,
			"not_logged" => $lln
		));

		return $this->parse();
	}
}
?>
