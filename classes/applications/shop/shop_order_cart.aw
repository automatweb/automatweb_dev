<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order_cart.aw,v 1.25 2005/01/18 10:51:25 kristo Exp $
// shop_order_cart.aw - Poe ostukorv 
/*

@classinfo syslog_type=ST_SHOP_ORDER_CART relationmgr=yes no_status=1

@default table=objects
@default group=general

@property prod_layout type=relpicker reltype=RELTYPE_PROD_LAYOUT field=meta method=serialize
@caption Kujundus, mida korvis kasutatakse

@property postal_price type=textbox field=meta method=serialize size=5
@caption Postikulu (liidetakse korvi hinnale)

@property email_subj type=textbox field=meta method=serialize
@caption Tellimuse e-maili subjekt

@property update_handler type=relpicker reltype=RELTYPE_CONTROLLER field=meta method=serialize
@caption Korvi uuendamise kontroller

@property finish_handler type=relpicker reltype=RELTYPE_CONTROLLER field=meta method=serialize
@caption Tellimise kontroller

@reltype PROD_LAYOUT value=1 clid=CL_SHOP_PRODUCT_LAYOUT
@caption toote kujundus

@reltype CONTROLLER value=2 clid=CL_FORM_CONTROLLER
@caption kontroller

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
		error::raise_if(!$cart_o->prop("prod_layout"), array(
			"id" => "ERR_NO_PROD_LAYOUT",
			"msg" => "shop_order_cart::show(): no product layout set for cart (".$cart_o->id().") "
		));
		$layout = obj($cart_o->prop("prod_layout"));

		$total = 0;
		$prod_total = 0;

		$awa = new aw_array($_SESSION["cart"]["items"]);
		$show_info_page = true;
		foreach($awa->get() as $iid => $quant)
		{
			if ($quant < 1)
			{
				continue;
			}

			if (!$this->can("view", $iid) || !is_oid($iid))
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
			if (get_class($inst) == "shop_product_packaging")
			{
				$prod_total += ($quant * $inst->get_prod_calc_price($i));
			}
			else
			{
				$prod_total = $total;
			}

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

			$vars["logged"] = $this->parse("logged");
			$this->vars($vars);
		}

		$this->vars(array(
			"user_data_form" => $html,
			"PROD" => $str,
			"total" => number_format($total, 2),
			"prod_total" => number_format($prod_total, 2),
			"postal_price" => number_format($cart_o->prop("postal_price"),2),
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
			"not_logged" => $lln,
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

		aw_session_set("order.accept_cond", $arr["order_cond_ok"]);

		// get cart to user from oc
		$cart_o = obj($oc->prop("cart"));

		// now get item layout from cart
		$layout = obj($cart_o->prop("prod_layout"));

		$order_ok = true;

		if (!empty($arr["add_to_cart_id"]))
		{
			$arr["add_to_cart"] = $add_to_cart = array(
				$arr["add_to_cart_id"] => $arr["quantity"]
			);
			$arr["order_data"] = $order_data = array(
				$arr["add_to_cart_id"] => $arr["order_data"]
			);
		}

		$awa = new aw_array($arr["add_to_cart"]);
		foreach($awa->get() as $iid => $quant)
		{
			if (!is_oid($iid) || !$this->can("view", $iid))
			{
				continue;
			}
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

		if ($arr["from"] != "confirm" && $arr["from"] != "")
		{
			$_SESSION["cart"]["user_data"] = $GLOBALS["user_data"];
		}

		// check cfgform controllers for user data
		$cfgf = $oc->prop("data_form");
		if ($cfgf && $arr["from"] != "confirm")
		{
			$is_valid = $this->validate_data(array(
				"cfgform_id" => $cfgf,
				"request" => $user_data
			));
			if (count($is_valid) > 0)
			{
				$order_ok = false;
				// save the errors in session
				aw_session_set("soc_err_ud", $is_valid);
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
			aw_session_set("no_cache", 1);

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

		if ($arr["from"] == "pre" && !$arr["order_cond_ok"])
		{
			aw_session_set("order_cond_fail", 1);
			aw_session_set("no_cache", 1);
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

		if ($arr["from"] != "confirm")
		{
			$awa = safe_array($order_data);
			foreach($awa as $iid => $dat)
			{
				$_SESSION["cart"]["item_data"][$iid] = $dat;
			}

			if (isset($awa["all_items"]) && is_array($awa["all_items"]))
			{
				$awa_i = new aw_array($arr["add_to_cart"]);
				$awa_all = safe_array($awa["all_items"]);
				foreach($awa_i->get() as $iid => $quant)
				{
					if ($quant > 0)
					{
						if (!isset($_SESSION["cart"]["item_data"][$iid]) || !is_array($_SESSION["cart"]["item_data"][$iid]))
						{
							$_SESSION["cart"]["item_data"][$iid] = array();
						}

						foreach($awa_all as $aa_k => $aa_v)
						{
							$_SESSION["cart"]["item_data"][$iid][$aa_k] = $aa_v;
						}
					}
				}
			}
			if (isset($awa["all_pkts"]) && is_array($awa["all_pkts"]))
			{
				foreach($awa["all_pkts"] as $iid => $k_d)
				{
					if (is_oid($iid) && $this->can("view", $iid))
					{
						$tmp = obj($iid);
						foreach($tmp->connections_from(array("type" => "RELTYPE_PACKAGING")) as $c)
						{
							$_SESSION["cart"]["item_data"][$c->prop("to")] = $k_d;
						}
					}
				}
			}
		}

		if (is_oid($cart_o->prop("update_handler")) && $this->can("view", $cart_o->prop("update_handler")))
		{
			$ctr = get_instance(CL_FORM_CONTROLLER);
			if (!$ctr->do_check($cart_o->prop("update_handler"), NULL, $cart_o, $oc))
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
			}
		}

		if (!empty($arr["clear_cart"]))
		{
			$this->clear_cart();
		}

		if (!empty($arr["pre_confirm_order"]))
		{
			// go to separate page with order non modifiable and user data form below
			return $this->mk_my_orb("pre_finish_order", array("oc" => $arr["oc"], "section" => $arr["section"]));
		}
		else
		if (!empty($arr["final_confirm_order"]))
		{
			// go to separate page with order non modifiable and user data form below
			return $this->mk_my_orb("final_finish_order", array("oc" => $arr["oc"], "section" => $arr["section"]));
		}
		else
		if (!empty($arr["confirm_order"]))
		{
			// do confirm order and show user
			// if cart is empty, redirect to front page
			$awa = new aw_array($_SESSION["cart"]["items"]);
			$tmp = $awa->get();
			if (count($tmp) < 1)
			{
				return aw_ini_get("baseurl");
			}

			if (is_oid($cart_o->prop("finish_handler")) && $this->can("view", $cart_o->prop("finish_handler")))
			{
				$ctr = get_instance(CL_FORM_CONTROLLER);
				$ctr->do_check($cart_o->prop("finish_handler"), NULL, $cart_o, $oc);
			}

			aw_session_del("order.accept_cond");
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
				error::raise(array(
					"id" => "ERR_NO_WAREHOOS",
					"msg" => "shop_order_cart::do_creat_order_from_cart(): no warehouse set for cart ".$oc->id()."!"
				));
			}
			$warehouse = $oc->prop("warehouse");
		}

		// get cart from oc (order center)
		if ($oc->prop("cart"))
		{
			$order_cart = obj($oc->prop("cart"));
			// now, get postal_price from cart
			$params["postal_price"] = $order_cart->prop("postal_price");
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

	function get_cart_value($prod = false)
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
				if ($prod)				
				{
					if ($i->class_id() == CL_SHOP_PRODUCT_PACKAGING)
					{
						$prod_total += ($quant * $inst->get_prod_calc_price($i));
					}	
					else
					{
						$prod_total = $total;
					}
				}
			}
		}

		if ($prod)
		{
			return array($total, $prod_total);
		}
		else
		{
			return $total;
		}
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
			"ERROR" => $err,
			"order_cond_ok" => checked(aw_global_get("order.accept_cond"))
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
		error::raise_if(!$cart_o->prop("prod_layout"), array(
			"id" => "ERR_NO_PROD_LAYOUT",
			"msg" => "shop_order_cart::show(): no product layout set for cart (".$cart_o->id().") "
		));
		$layout = obj($cart_o->prop("prod_layout"));
		$layout->set_prop("template", "prod_pre_confirm.tpl");

		$total = 0;
		$prod_total = 0;

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
			if (get_class($inst) == "shop_product_packaging")
			{
				$prod_total += ($quant * $inst->get_prod_calc_price($i));
			}
			else
			{
				$prod_total = $total;
			}

			$str .= $this->parse("PROD");
		}

		$swh = get_instance(CL_SHOP_WAREHOUSE);
		$wh_o = obj($oc->prop("warehouse"));

		// fake user data
		$wh_o->set_meta("order_cur_ud", $_SESSION["cart"]["user_data"]);

		$els = $swh->callback_get_order_current_form(array(
			"obj_inst" => $wh_o,
			"no_data" => (aw_global_get("uid") == "" ? true : false)
		));

		if ($els["userdate1"])
		{
			$els["userdate1"]["year_from"] = 1930;
			$els["userdate1"]["year_to"] = date("Y");
			$els["userdate1"]["no_default"] = true;
			$els["userdate1"]["value"] = -1;
		}

		if ($els["userdate2"])
		{
			$els["userdate2"]["year_from"] = date("Y");
			$els["userdate2"]["year_to"] = date("Y")+3;
		}

		// if there are errors
		$els = $this->do_insert_user_data_errors($els);

		$rd = get_instance(CL_REGISTER_DATA);
		$els = $rd->parse_properties(array(
			"properties" => $els,
			"name_prefix" => ""
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

		if (aw_global_get("order_cond_fail"))
		{
			$this->vars(array(
				"ACC_ERROR" => $this->parse("ACC_ERROR")
			));
			aw_session_del("order_cond_fail");
		}

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
			$vars["logged"] = $this->parse("logged");
			$this->vars($vars);
		}

		$this->vars(array(
			"user_data_form" => $html,
			"PROD" => $str,
			"total" => number_format($total, 2),
			"prod_total" => number_format($prod_total, 2),
			"reforb" => $this->mk_reforb("submit_add_cart", array("oc" => $arr["oc"], "update" => 1, "section" => $arr["section"], "from" => "pre")),
			"postal_price" => number_format($cart_o->prop("postal_price"))
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

	/**

		@attrib name=final_finish_order nologin=1

		@param oc required
		@param section optional

	**/
	function final_finish_order($arr)
	{
		extract($arr);
		$this->read_template("final_finish_order.tpl");

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
		error::raise_if(!$cart_o->prop("prod_layout"), array(
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

		// if there are errors
		$els = $this->do_insert_user_data_errors($els);
		$prevd = $els["userdate1"]["value"];

		$rd = get_instance(CL_REGISTER_DATA);
		$els = $rd->parse_properties(array(
			"properties" => $els,
			"name_prefix" => ""
		));
		$els["user_data[userdate1]"]["value"] = $prevd;

		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		foreach($els as $pn => $pd)
		{
			if ($pd["type"] == "date_select")
			{
				if ($pd["value"] == -1)
				{
					$pd["value"] = "---.---.---";
				}
				else
				{
					$pd["value"] = date("d.m.Y", $pd["value"]);
				}
			}
			else
			if ($pd["type"] == "chooser")
			{
				if (is_oid($pd["value"]) && $this->can("view", $pd["value"]))
				{
					$tmp = obj($pd["value"]);
					$pd["value"] = $tmp->name();
				}
				else
				{
					$pd["value"] = "";
				}
			}

			$pd["type"] = "text";
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
			"reforb" => $this->mk_reforb("submit_add_cart", array("oc" => $arr["oc"], "update" => 1, "section" => $arr["section"], "from" => "confirm")),
			"postal_price" => number_format($cart_o->prop("postal_price"))
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

	function do_insert_user_data_errors($props)
	{
		$errs = new aw_array(aw_global_get("soc_err_ud"));
		$errs = $errs->get();

		$ret = array();
		foreach($props as $pn => $pd)
		{
			if (isset($errs[$pn]))
			{
				$ret[$pn."_err"] = array(
					"name" => $pn."_err",
					"type" => "text",
					"store" => "no",
					"value" => "<font color=red>".$errs[$pn]["msg"]."</font>",
					"no_caption" => 1
				);
			}
			$ret[$pn] = $pd;
		}

		aw_session_del("soc_err_ud");
		return $ret;
	}
}
?>
