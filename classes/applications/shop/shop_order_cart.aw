<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order_cart.aw,v 1.48 2006/02/14 10:42:21 ahti Exp $
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
	/*

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
	*/
	
	function get_cart($oc)
	{
		if($oc && $oc->prop("cart_type") == 1 && aw_global_get("uid") != "")
		{
			$user = obj(aw_global_get("uid_oid"));
			// well, it would be wise to syncronize the session aswell...
			$_SESSION["cart"] = $user->meta("shop_cart");
			return $user->meta("shop_cart");
		}
		else
		{
			return $_SESSION["cart"];
		}
	}
	
	function set_cart($arr)
	{
		extract($arr);
		if($oc->prop("cart_type") == 1 && aw_global_get("uid") != "")
		{
			$user = obj(aw_global_get("uid_oid"));
			$user->set_meta("shop_cart", $cart);
			$user->save();
		}
		$_SESSION["cart"] = $cart;
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
		lc_site_load("shop_order_cart", &$this);

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

		if ($oc->prop("no_show_cart_contents"))
		{
			return $this->pre_finish_order($arr);
		}

		// get cart to user from oc
		if ($arr["id"])
		{
			$cart_o = obj($arr["id"]);
		}
		else
		{
			$cart_o = obj($oc->prop("cart"));
		}
		
		error::raise_if(!$cart_o->prop("prod_layout"), array(
			"id" => "ERR_NO_PROD_LAYOUT",
			"msg" => sprintf(t("shop_order_cart::show(): no product layout set for cart (%s) "), $cart_o->id())
		));
		$layout = obj($cart_o->prop("prod_layout"));

		$total = 0;
		$prod_total = 0;
		$cart = $this->get_cart($oc);
		$awa = new aw_array($cart["items"]);
		$show_info_page = true;
		$l_inst = $layout->instance();
		$l_inst->read_template($layout->prop("template"));
		foreach($awa->get() as $iid => $quantx)
		{
			if(!is_oid($iid) || !$this->can("view", $iid))
			{
				continue;
			}
			$quantx = new aw_array($quantx);
			$i = obj($iid);
			$inst = $i->instance();
			$price = $inst->get_price($i);
			
			foreach($quantx->get() as $x => $quant)
			{
				if($quant["items"] < 1)
				{
					continue;
				}
				$this->vars(array(
					"prod_html" => $inst->do_draw_product(array(
						"layout" => $layout,
						"prod" => $i,
						"it" => $x,
						"l_inst" => $l_inst,
						"quantity" => $quant["items"],
						"oc_obj" => $oc,
						"is_err" => ($soce_arr[$iid]["is_err"] ? "class=\"selprod\"" : "")
					))
				));
				$show_info_page = false;
				$total += ($quant["items"] * $price);
				if (get_class($inst) == "shop_product_packaging")
				{
					$prod_total += ($quant["items"] * $inst->get_prod_calc_price($i));
				}
				else
				{
					$prod_total = $total;
				}
				$str .= $this->parse("PROD");
			}
		}

		$swh = get_instance(CL_SHOP_WAREHOUSE);
		$wh_o = obj($oc->prop("warehouse"));

		// fake user data
		$wh_o->set_meta("order_cur_ud", $cart["user_data"]);

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
			$us = get_instance(CL_USER);
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

		if ($cart_o->prop("postal_price") > 0)
		{
			$this->vars(array(
				"HAS_POSTAGE_FEE" => $this->parse("HAS_POSTAGE_FEE")
			));
		}
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
		//arr($arr);
		extract($arr);
		$oc = obj($oc);
		//$this->clear_cart($oc);
		//die();
		$cart = $this->get_cart($oc);
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
		foreach($awa->get() as $iid => $quantx)
		{
			if (!is_oid($iid) || !$this->can("view", $iid))
			{
				unset($arr["add_to_cart"]["items"][$iid]);
				continue;
			}
			$i_o = obj($iid);
			$i_i = $i_o->instance();
			$mon = $i_i->get_must_order_num($i_o);
			$quantx = new aw_array($quantx);
			foreach($quantx->get() as $x => $quant)
			{
				if ($arr["update"] == 1)
				{
					$cc = $quant;
				}
				else
				{
					$cc = (int)$cart["items"][$iid][$x]["items"] + $quant;
				}
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
							"msg" => sprintf(t("%s peab tellima %s kaupa, hetkel kokku %s!"), $i_o->name(),$mon, $cc),
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
		}

		if ($arr["from"] != "confirm" && $arr["from"] != "")
		{
			$cart["user_data"] = $GLOBALS["user_data"];
		}
		
		if (isset($arr["payment_method"]))
		{
			$cart["payment_method"] = $arr["payment_method"];
		}
		if (isset($arr["num_payments"]))
		{
			$cart["payment"]["num_payments"] = $arr["num_payments"];
		}
		$this->set_cart(array(
			"oc" => $oc,
			"cart" => $cart,
		));

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
		
		// i'm quite sure that you don't want to know, whatta hell is going on in here
		// neighter do i... -- ahz
		$awa = new aw_array($arr["add_to_cart"]);
		$order_data = safe_array($order_data);

		foreach($awa->get() as $iid => $quantx)
		{
			$cart["items"][$iid] = safe_array($cart["items"][$iid]);
			if (is_numeric($quantx))
			{
				$quantx = new aw_array(array($quantx));
			}
			else
			{
				$quantx = new aw_array($quantx);
			}
			
			foreach($quantx->get() as $x => $quant)
			{
				if ($arr["update"] == 1)
				{
					$cart["items"][$iid][$x] = safe_array($cart["items"][$iid][$x]);
					$cart["items"][$iid][$x]["items"] = $quant;
				}
				else
				{
					if($oc->prop("multi_items") == 1)
					{
						$x = 0;
						// get the highest id from items -- ahz
						if(count($cart["items"][$iid]) > 0)
						{
							foreach($cart["items"][$iid] as $key => $val)
							{
								if($key > $x)
								{
									$x = $key;
								}
							}
							$x++;
						}
						$cart["items"][$iid][$x]["items"] = $quant;
					}
					else
					{
						$cart["items"][$iid][$x]["items"] += $quant;
					}
				}
				if($arr["from"] != "confirm")
				{
					foreach($order_data[$iid] as $key => $val)
					{
						if((string)$key == "all_items" || (string)$key == "all_pkts")
						{
							continue;
						}
						if(is_array($val))
						{
							if($key == $x)
							{
								$tmp = $cart["items"][$iid][$x];
								$cart["items"][$iid][$x] = $val + $tmp;
							}
						}
						else
						{
							$cart["items"][$iid][$x][$key] = $val;
						}
					}
				}
			}
		}
		
		if($arr["from"] != "confirm")
		{
			if (is_array($order_data["all_items"]))
			{
				$awa_i = new aw_array($arr["add_to_cart"]);
				$awa_all = safe_array($order_data["all_items"]);
				foreach($awa_i->get() as $iid => $quantx)
				{
					$quantx = new aw_array($quantx);
					foreach($quantx->get() as $x => $quant)
					{
						if($quant > 0)
						{
							$cart["items"][$iid][$x] = safe_array($cart["items"][$iid][$x]);
							foreach($awa_all as $aa_k => $aa_v)
							{
								$cart["items"][$iid][$x][$aa_k] = $aa_v;
							}
						}
					}
				}
			}
			if (is_array($order_data["all_pkts"]))
			{
				foreach($order_data["all_pkts"] as $iid => $k_d)
				{
					if (!is_oid($iid) || !$this->can("view", $iid))
					{
						continue;
					}
					$tmp = obj($iid);
					foreach($tmp->connections_from(array("type" => "RELTYPE_PACKAGING")) as $c)
					{
						foreach($cart["items"][$c->prop("to")] as $key => $val)
						{
							foreach(safe_array($k_d) as $k_k => $k_v)
							{
								$cart["items"][$c->prop("to")][$key][$k_k] = $k_v;
							}
						}
					}
				}
			}
		}
		foreach(safe_array($to_remove) as $xid => $rm)
		{
			$rm = new aw_array($rm);
			foreach($rm->get() as $key => $val)
			{
				if($val == 1)
				{
					unset($cart["items"][$xid][$key]);
				}
			}
		}
		foreach(safe_array($cart["items"]) as $iid => $val)
		{
			if(count($val) <= 0)
			{
				unset($cart["items"][$iid]);
			}
		}
		//arr($cart);
		$this->set_cart(array(
			"oc" => $oc,
			"cart" => $cart,
		));
		//arr($cart);

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
			$this->clear_cart($oc);
		}

		if (!empty($arr["go_to_after"]))
		{
			return $arr["go_to_after"];
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
		if (!empty($arr["update_final_finish"]))
		{
			return $this->mk_my_orb("final_finish_order", array("oc" => $arr["oc"], "section" => $arr["section"]));
		}
		else
		if (!empty($arr["confirm_order"]))
		{
			// do confirm order and show user
			// if cart is empty, redirect to front page
			$awa = new aw_array($cart["items"]);
			$empty = true;
			foreach($awa->get() as $val)
			{
				if (count($val) >= 1)
				{
					$empty = false;
					break;
				}
			}
			if($empty)
			{
				return aw_ini_get("baseurl");
			}
			if(is_oid($cart_o->prop("finish_handler")) && $this->can("view", $cart_o->prop("finish_handler")))
			{
				$ctr = get_instance(CL_FORM_CONTROLLER);
				$ctr->do_check($cart_o->prop("finish_handler"), NULL, $cart_o, $oc);
			}
			aw_session_del("order.accept_cond");
		 	$ordid = $this->do_create_order_from_cart($arr["oc"], NULL, array(
				"payment" => $cart["payment"],
				"payment_type" => $cart["payment_method"]
			));
			$this->clear_cart($oc);
			return $this->mk_my_orb("show", array("id" => $ordid, "section" => $arr["section"]), "shop_order");
		}
		else
		{
			return $this->mk_my_orb("show_cart", array("oc" => $arr["oc"], "section" => $arr["section"]));
		}
	}

	function do_create_order_from_cart($oc, $warehouse = NULL, $params = array())
	{
		$so = get_instance(CL_SHOP_ORDER);
		$oc = obj($oc);

		if ($warehouse === NULL)
		{
			if (!is_oid($oc->prop("warehouse")))
			{
				error::raise(array(
					"id" => "ERR_NO_WAREHOOS",
					"msg" => sprintf(t("shop_order_cart::do_creat_order_from_cart(): no warehouse set for cart %s!"), $oc->id())
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
		$cart = $this->get_cart($oc);

		$this->update_user_data_from_order($oc, $warehouse, $params);

		$so->start_order(obj($warehouse), $oc);
		
		$params["cart"] = $cart;
		$awa = new aw_array($cart["items"]);
		foreach($awa->get() as $iid => $quant)
		{
			$qu = new aw_array($quant);
			foreach($qu->get() as $key => $val)
			{
				$so->add_item(array("iid" => $iid, "item_data" => $cart["items"][$iid][$key], "it" => $key));
			}
		}
		$rval = $so->finish_order($params);
		$this->clear_cart($oc);
		return $rval;
	}

	function add_item($arr)
	{
		extract($arr);
		$prod_data = safe_array($prod_data);
		$cart = $this->get_cart($oc);
		
		$multi = $oc->prop("multi_items");
		$cart["items"][$iid] = safe_array($cart["items"][$iid]);
		foreach($cart["items"][$iid] as $iid => $qx)
		{
			if($multi == 1)
			{
				$x = 0;
				foreach($cart["items"][$iid] as $high => $low)
				{
					if($high > $x)
					{
						$x = $high;
					}
				}
				$x++;
			}
			$tmp = array();
			$tmp["items"] = $cart["items"][$iid][$x]["items"] + $quant;
			$cart["items"][$iid][$x] = $tmp + $prod_data;
		}
		$this->set_cart(array(
			"oc" => $oc,
			"cart" => $cart,
		));
	}

	function set_item($arr)
	{
		extract($arr);
		$it = !$it ? 0 : $it;
		$cart = $this->get_cart($oc);
		if ($quant == 0)
		{
			unset($cart["items"][$iid][$it]);
		}
		else
		{
			$cart["items"][$iid][$it]["items"] = $quant;
		}
		$this->set_cart(array(
			"oc" => $oc,
			"cart" => $cart,
		));
	}

	function get_cart_value($prod = false)
	{
		$total = 0;

		$awa = new aw_array($_SESSION["cart"]["items"]);
		foreach($awa->get() as $iid => $quantx)
		{
			if(!is_oid($iid) || !$this->can("view", $iid))
			{
				continue;
			}
			$i = obj($iid);
			$inst = $i->instance();
			$price = $inst->get_calc_price($i);
			$quantx = new aw_array($quantx);
			foreach($quantx->get() as $x => $quant)
			{
				$total += ($quant["items"] * $price);
				if ($prod)
				{
					if ($i->class_id() == CL_SHOP_PRODUCT_PACKAGING)
					{
						$prod_total += ($quant["items"] *  $inst->get_prod_calc_price($i));
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
			$q = new aw_array($q);
			foreach($q->get() as $v => $z)
			{
				if ($z > 0)
				{
					$ret[$iid][$v] = $z;
				}
			}
		}
		return $ret;
	}
		/* siin pannakse andmed lõplikku tabelisse */
	function get_item_in_cart($arr)
	{
		$it = !$arr["it"] ? 0 : $arr["it"];
		return safe_array($_SESSION["cart"]["items"][$arr["iid"]][$it]);
	}

	function clear_cart($oc)
	{
		$this->set_cart(array(
			"oc" => $oc,
			"cart" => array(),
		));
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
		lc_site_load("shop_order_cart", &$this);

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
			"msg" => sprintf(t("shop_order_cart::show(): no product layout set for cart (%s) "), $cart_o->id())
		));
		$layout = obj($cart_o->prop("prod_layout"));
		$layout->set_prop("template", "prod_pre_confirm.tpl");
		
		$l_inst = $layout->instance();
		$l_inst->read_template($layout->prop("template"));

		$total = 0;
		$prod_total = 0;
		
		$cart = $this->get_cart($oc);
		
		$awa = new aw_array($cart["items"]);
		foreach($awa->get() as $iid => $quantx)
		{
			if(!is_oid($iid) || !$this->can("view", $iid))
			{
				continue;
			}
			$quantx = new aw_array($quantx);
			$i = obj($iid);
			$inst = $i->instance();
			foreach($quantx->get() as $x => $quant)
			{
				if ($quant["items"] < 1)
				{
					continue;
				}
				$this->vars(array(
					"prod_html" => $inst->do_draw_product(array(
						"layout" => $layout,
						"prod" => $i,
						"it" => $x,
						"l_inst" => $l_inst,
						"quantity" => $quant["items"],
						"oc_obj" => $oc,
						"is_err" => ($soce_arr[$iid]["is_err"] ? "class=\"selprod\"" : "")
					))
				));
				$read_price_total += ($quant["items"] * str_replace(",","", $quant["read_price"]));
				$read_price_total_sum += (str_replace(",","", $quant["read_price"]));
				if (get_class($inst) == "shop_product_packaging")
				{
					$prod_total += ($quant["items"] * $inst->get_prod_calc_price($i));
				}
				else
				{
					$prod_total += ($quant["items"] * $inst->get_calc_price($i));
				}
				/*
				else
				{
					$prod_total = $total;
				}
				*/
	
				$str .= $this->parse("PROD");
			}
		}
		$swh = get_instance(CL_SHOP_WAREHOUSE);
		$wh_o = obj($oc->prop("warehouse"));

		// fake user data
		$wh_o->set_meta("order_cur_ud", $cart["user_data"]);

		$els = $swh->callback_get_order_current_form(array(
			"obj_inst" => $wh_o,
			"no_data" => (aw_global_get("uid") == "" ? true : false)
		));

		$do = false;
		if ($this->is_template("RENT"))
		{
			$cr = "";
			if ($prod_total > $oc->prop("rent_min_amt"))
			{
				$do = true;
				if ($oc->prop("rent_prop") != "" && $oc->prop("rent_prop_val") != "")
				{
					if ($els[$oc->prop("rent_prop")]["value"] != $oc->prop("rent_prop_val"))
					{

						$do = false;
					}
				}
			}
			else
			{
				$do = false;	
			}
	
			$this->vars(array(
				"cod_selected" => checked($cart["payment_method"] == "cod" || !$do || $cart["payment_method"] == ""),
				"rent_selected" => checked($cart["payment_method"] == "rent"),
			));

			if ($do)
			{
				$this->vars(array(
					"can_rent" => $this->parse("can_rent")
				));
			}
			else
			{
				$this->vars(array(
					"no_can_rent" => $this->parse("no_can_rent")
				));
			}
			$this->vars(array(
				"RENT" => $this->parse("RENT")
			));
		}

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

		// apply view controllers
		foreach($els as $el_pn => $el_inf)
		{
			foreach(safe_array($el_inf["view_controllers"]) as $v_ctr_id)
			{
				$vc = get_instance(CL_CFG_VIEW_CONTROLLER);
				$vc->check_property($els[$el_pn], $v_ctr_id, array());
			}
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
			if ($pn == "user_data[uservar1]" && aw_ini_get("otto.import") && $prod_total > 1000)
			{
				$pd["onclick"] = "upd_rent(this)";
			}
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
			$us = get_instance(CL_USER);
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
			"read_price_total" => number_format($read_price_total, 2),
			"read_price_total_sum" => number_format($read_price_total_sum, 2),
			"reforb" => $this->mk_reforb("submit_add_cart", array("oc" => $arr["oc"], "update" => 1, "section" => $arr["section"], "from" => "pre")),
			"postal_price" => number_format($cart_o->prop("postal_price"))
		));

		if ($cart_o->prop("postal_price") > 0)
		{
			$this->vars(array(
				"HAS_POSTAGE_FEE" => $this->parse("HAS_POSTAGE_FEE")
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

	/**

		@attrib name=final_finish_order nologin=1

		@param oc required
		@param section optional

	**/
	function final_finish_order($arr)
	{
		extract($arr);
		$this->read_template("final_finish_order.tpl");
		lc_site_load("shop_order_cart", &$this);

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
			"msg" => sprintf(t("shop_order_cart::show(): no product layout set for cart (%s) "), $cart_o->id())
		));
		$layout = obj($cart_o->prop("prod_layout"));
		$layout->set_prop("template", "prod_pre_confirm.tpl");

		$total = 0;
		
		$cart = $this->get_cart($oc);
		
		$awa = new aw_array($cart["items"]);
		foreach($awa->get() as $iid => $quantx)
		{
			if (!is_oid($iid) or !$this->can("view", $iid))
			{
				continue;
			}
			$i = obj($iid);
			$inst = $i->instance();
			$price = $inst->get_price($i);
			foreach($quantx as $quant)
			{
				if($quant["items"] < 1)
				{
					continue;
				}
				$this->vars(array(
					"prod_html" => $inst->do_draw_product(array(
						"layout" => $layout,
						"prod" => $i,
						"quantity" => $quant["items"],
						"oc_obj" => $oc,
						"is_err" => ($soce_arr[$iid]["is_err"] ? "class=\"selprod\"" : "")
					))
				));
				$total += ($quant["items"] * $price);
				$str .= $this->parse("PROD");
			}
		}

		$swh = get_instance(CL_SHOP_WAREHOUSE);
		$wh_o = obj($oc->prop("warehouse"));

		// fake user data
		$wh_o->set_meta("order_cur_ud", $cart["user_data"]);

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
			$us = get_instance(CL_USER);
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

		if ($cart_o->prop("postal_price") > 0)
		{
			$this->vars(array(
				"HAS_POSTAGE_FEE" => $this->parse("HAS_POSTAGE_FEE")
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

		$can_confirm = true;
		if (($imp = aw_ini_get("otto.import")) && $cart["payment_method"] == "rent" && $this->is_template("HAS_RENT"))
		{
			$i = obj($imp);
			$cl_pgs = $this->make_keys(explode(",", $i->prop("jm_clothes")));
			$ls_pgs = $this->make_keys(explode(",", $i->prop("jm_lasting")));
			$ft_pgs = $this->make_keys(explode(",", $i->prop("jm_furniture")));
			$awa = new aw_array($cart["items"]);
			foreach($awa->get() as $iid => $quantx)
			{
				if (!is_oid($iid) || !$this->can("view", $iid))
				{
					continue;
				}
				$pr = obj($iid);
				if ($pr->class_id() == CL_SHOP_PRODUCT_PACKAGING)
				{
					$c = reset($pr->connections_to(array("from.class_id" => CL_SHOP_PRODUCT)));
					$pr = $c->from();
				}
				$i = obj($iid);
				$inst = $i->instance();
				$calc_price = $inst->get_prod_calc_price($i);
				$price = $inst->get_price($i);
				$parent = $pr->parent();
				foreach($quantx as $quant)
				{
					if($quant["items"] < 1)
					{
						continue;
					}
					$this->vars(array(
						"prod_html" => $inst->do_draw_product(array(
							"layout" => $layout,
							"prod" => $i,
							"quantity" => $quant["items"],
							"oc_obj" => $oc,
							"is_err" => ($soce_arr[$iid]["is_err"] ? "class=\"selprod\"" : "")
						))
					));
	
					if (get_class($inst) == "shop_product_packaging")
					{
						$pr_price = ($quant["items"] * $calc_price);
					}
					else
					{
						$pr_price = ($quant["items"] * $price);
					}
	
					if ( $cl_pgs[$parent] || (!$ft_pgs[$parent] && !$ls_pgs[$parent]))
					{
						$cl_total += $pr_price;
						$cl_str .= $this->parse("PROD");
					}
					else
					if ($ft_pgs[$parent])
					{
						$ft_total += $pr_price;
						$ft_str .= $this->parse("PROD");
					}
					else
					if ($ls_pgs[$parent])
					{
						$ls_total += $pr_price;
						$ls_str .= $this->parse("PROD");
					}
				}
			}

			$npc = max(2,$cart["payment"]["num_payments"]["clothes"]);
			$cl_payment = ($cl_total+($cl_total*($npc)*1.25/100))/($npc+1);
			$cl_tot_wr = ($cl_payment * ($npc+1));

			$ft_npc = max(2,$cart["payment"]["num_payments"]["furniture"]);
			$ft_first_payment = ($ft_total/5);
			$ft_payment = ($ft_total-$ft_first_payment+(($ft_total-$ft_first_payment)*$ft_npc*1.25/100))/($ft_npc+1);
			$ft_total_wr = $ft_payment * ($ft_npc+1) + $ft_first_payment;

			$ls_npc = max(2,$cart["payment"]["num_payments"]["last"]);
			$ls_payment = ($ls_total+($ls_total*($ls_npc)*1.25/100))/($ls_npc+1);
			$ls_total_wr = ($ls_payment * ($ls_npc+1));

			$this->vars(array(
				"PROD_RENT_CLOTHES" => $cl_str,
				"PROD_RENT_FURNITURE" => $ft_str,
				"PROD_RENT_LAST" => $ls_str,
				"total_clothes_price" => number_format($cl_total,2),
				"num_payments_clothes" => $this->picker($npc, array("2" => "2 kuud","3" => "3 kuud", "4" => "4 kuud", "5" => "5 kuud", "6" => "6 kuud")),
				"num_payments_clothes_show" => $npc+1,
				"payment_clothes" => number_format($cl_payment,2),
				"total_clothes_price_wr" => number_format($cl_tot_wr,2),
				"total_furniture_price" => number_format($ft_total,2),
				"first_payment_furniture" => number_format($ft_total/5,2),
				"num_payments_furniture" => $this->picker($ft_npc, array("2" => "2 kuud","3" => "3 kuud", "4" => "4 kuud", "5" => "5 kuud", "6" => "6 kuud","7" => "7 kuud", "8" => "8 kuud", "9" => "9 kuud", "10" => "10 kuud", "11" => "11 kuud", "12" => "12 kuud")),
				"num_payments_furniture_show" => $ft_npc+1,
				"payment_furniture" => number_format($ft_payment,2),
				"total_furniture_price_wr" => number_format($ft_total_wr,2),
				"total_last_price" => number_format($ls_total,2),
				"num_payments_last" => $this->picker($ls_npc, array("2" => "2 kuud","3" => "3 kuud", "4" => "4 kuud", "5" => "5 kuud", "6" => "6 kuud","7" => "7 kuud", "8" => "8 kuud", "9" => "9 kuud", "10" => "10 kuud", "11" => "11 kuud", "12" => "12 kuud")),
				"num_payments_last_show" => $ls_npc+1,
				"payment_last" => number_format($ls_payment,2),
				"total_last_price_wr" => number_format($ls_total_wr,2),
				"total_price_rent" => number_format($cl_tot_wr + $ft_total_wr + $ls_total_wr,2),
				"total_price_rent_w_pst" => number_format($cl_tot_wr + $ft_total_wr + $ls_total_wr + $cart_o->prop("postal_price"),2),
				"postal_price" => number_format($cart_o->prop("postal_price"))
			));

			if ($cart["payment_method"] == "rent" && ($cl_total + $ft_total + $ls_total) < $oc->prop("rent_min_amt"))
			{
				$this->read_template("cart_too_small_for_rent.tpl");
				$this->vars(array(
					"cancel_order" => $this->mk_my_orb("clear_cart", array("oc" => $oc->id()))
				));
				return $this->parse();
			}


			if ($cl_tot_wr + $ft_total_wr + $ls_total_wr > 8000)
			{
				$this->vars(array(
					"RENT_TOO_LARGE" => $this->parse("RENT_TOO_LARGE")
				));
			}
			if ($cl_payment + $ft_payment + $ls_payment < 100)
			{
				$this->vars(array(
					"RENT_TOO_SMALL" => $this->parse("RENT_TOO_SMALL")
				));
				$can_confirm = false;
			}
			if ($cl_tot_wr > 0)
			{
				$this->vars(array(
					"HAS_PROD_RENT_CLOTHES" => $this->parse("HAS_PROD_RENT_CLOTHES"),
				));
			}
			if ($ft_total_wr > 0)
			{
				$this->vars(array(
					"HAS_PROD_RENT_FURNITURE" => $this->parse("HAS_PROD_RENT_FURNITURE"),
				));
			}
			if ($ls_total_wr > 0)
			{
				$this->vars(array(
					"HAS_PROD_RENT_LAST" => $this->parse("HAS_PROD_RENT_LAST"),
				));
			}
			$this->vars(array(
				"HAS_RENT" => $this->parse("HAS_RENT")
			));
			$str = "";
		}
		else
		{
			$this->vars(array(
				"NO_RENT" => $this->parse("NO_RENT")
			));
		}

		if ($can_confirm)
		{
			$this->vars(Array(
				"CAN_CONFIRM" => $this->parse("CAN_CONFIRM")
			));
		}
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

	/**

		@attrib name=clear_cart nologin=1

		@param oc required type=int acl=view
	**/
	function orb_clear_cart($arr)
	{
		$oc = obj($arr["oc"]);
		$this->clear_cart($oc);
		return aw_ini_get("baseurl");
	}

	function update_user_data_from_order($oc, $wh, $params)
	{
		if (aw_global_get("uid") == "")
		{
			return;
		}
		$ud = safe_array($_POST["user_data"]);
		
		$ps_pmap = safe_array($oc->meta("ps_pmap"));
		$org_pmap = safe_array($oc->meta("org_pmap"));

		$u_i = get_instance(CL_USER);
		$cur_p_id = $u_i->get_current_person();
		$cur_p = obj();
		if (is_oid($cur_p_id) && $this->can("view", $cur_p_id))
		{
			$cur_p = obj($cur_p_id);
		}

		$cur_co_id = $u_i->get_current_company();
		$cur_co = obj();
		if (is_oid($cur_co_id) && $this->can("view", $cur_co_id))
		{
			$cur_co = obj($cur_co_id);
		}

		foreach($ud as $pn => $pv)
		{
			if ($key = array_search($pn, $ps_pmap))
			{
				$cur_p->set_prop($key, $pv);
				$p_m = true;
			}
			if ($key = array_search($pn, $org_pmap))
			{
				$cur_co->set_prop($key, $pv);
				$c_m = true;
			}
		}

		if ($p_m)
		{
			aw_disable_acl();
			$cur_p->save();
			aw_restore_acl();
		}		

		if ($c_m)
		{
			aw_disable_acl();
			$cur_co->save();
			aw_restore_acl();
		}		
	}
}
?>
