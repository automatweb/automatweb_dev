<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order.aw,v 1.28 2005/04/01 12:06:16 kristo Exp $
// shop_order.aw - Tellimus 
/*

@classinfo syslog_type=ST_SHOP_ORDER relationmgr=yes no_status=1

@default table=objects
@default group=general

@property confirmed type=checkbox ch_value=1 table=aw_shop_orders field=confirmed 
@caption Kinnitatud

@property orderer_person type=relpicker reltype=RELTYPE_PERSON table=aw_shop_orders field=aw_orderer_person 
@caption Tellija esindaja

@property orderer_company type=relpicker reltype=RELTYPE_ORG table=aw_shop_orders field=aw_orderer_company 
@caption Tellija

@property seller_person type=relpicker reltype=RELTYPE_PERSON table=objects field=meta method=serialize
@caption M&uuml;&uuml;ja esindaja

@property seller_company type=relpicker reltype=RELTYPE_ORG table=objects field=meta method=serialize
@caption M&uuml;&uuml;ja

@property oc type=relpicker reltype=RELTYPE_ORDER_CENTER table=aw_shop_orders field=aw_oc_id 
@caption Tellimiskeskkond

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE table=aw_shop_orders field=aw_warehouse_id 
@caption Ladu

@tableinfo aw_shop_orders index=aw_oid master_table=objects master_index=brother_of

@groupinfo items caption="Tellimuse sisu"

@property items_toolbar type=toolbar group=items no_caption=1
@caption Tellimuste toolbar

@property items_orderer group=items type=text store=no
@caption Tellija andmed

@property items group=items field=meta method=serialize type=table
@caption Tellitud tooted

@property sum type=textbox table=aw_shop_orders field=aw_sum group=items
@caption Summa


@reltype PRODUCT value=1 clid=CL_SHOP_PRODUCT,CL_SHOP_PACKET
@caption tellimuse toode

@reltype EXPORT value=2 clid=CL_SHOP_WAREHOUSE_EXPORT
@caption lao v&auml;ljaminek

@reltype PERSON value=3 clid=CL_CRM_PERSON
@caption tellija esindaja

@reltype ORG value=4 clid=CL_CRM_COMPANY
@caption tellija organisatsioon

@reltype WAREHOUSE value=5 clid=CL_SHOP_WAREHOUSE
@caption ladu

@reltype ORDER_CENTER value=6 clid=CL_SHOP_ORDER_CENTER
@caption tellimiskeskkond

@reltype ORDER_TABLE_LAYOUT value=7 clid=CL_SHOP_PRODUCT_TABLE_LAYOUT
@caption Tellimuste tabeli kujundus

*/

class shop_order extends class_base
{
	var $order_item_data = array();

	function shop_order()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_order",
			"clid" => CL_SHOP_ORDER
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "items":
				$this->do_ord_table($arr);
				break;
				
			case "items_toolbar":
				$t = &$arr["prop"]["vcl_inst"];
				$t->add_button(array(
					"name" => "delete",
					"tooltip" => t("Eemalda tellimusest tooted"),
					"confirm" => t("Oled kindel, et soovitud valitud tooted tellimusest eemaldada?"),
					"action" => "remove_items",
					"img" => "delete.gif",
				));
				break;
				
			case "confirmed":
				if ($arr["obj_inst"]->prop("confirmed") == 1)
				{
					// can't unconfirm after confirmation
					return PROP_IGNORE;
				}
				break;

			case "sum":
				$data["value"] = $this->get_price($arr["obj_inst"]);
				break;
		
			case "items_orderer":
				$data["value"] = "";
				if ($arr["obj_inst"]->prop("orderer_person"))
				{
					$po = obj($arr["obj_inst"]->prop("orderer_person"));
					$data["value"] = $po->name();
				}
				if ($arr["obj_inst"]->prop("orderer_company"))
				{
					$co = obj($arr["obj_inst"]->prop("orderer_company"));
					if ($data["value"] != "")
					{
						$data["value"] .= " / ";
					}
					$data["value"] .= $co->name();
				}
			
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "items":
				$this->save_ord_table($arr);
				break;

			case "confirmed":
				if ($arr["obj_inst"]->prop("confirmed") != 1 && $data["value"] == 1)
				{
					// confirm was clicked, do the actual add
					$this->do_confirm($arr["obj_inst"]);
				}
				break;

			case "sum":
				$data["value"] = $this->get_price($arr["obj_inst"]);
				break;
		}
		return $retval;
	}	

	function callback_post_save($arr)
	{
		if ($arr["new"])
		{
			// check if the current user has an organization
			$us = get_instance("core/users/user");
			if (($p = $us->get_current_person()))
			{
				$arr["obj_inst"]->connect(array(
					"to" => $p,
					"reltype" => 3 // RELTYPE_PERSON
				));
				$arr["obj_inst"]->set_prop("orderer_person", $p);
			}

			if (($p = $us->get_current_company()))
			{
				$arr["obj_inst"]->connect(array(
					"to" => $p,
					"reltype" => 4 // RELTYPE_COMPANY
				));
				$arr["obj_inst"]->set_prop("orderer_company", $p);
			}
			$arr["obj_inst"]->save();
		}
	}

	function do_ord_table(&$arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$pd = $arr["obj_inst"]->meta("ord_content");
		$pd_data = $arr["obj_inst"]->meta("ord_item_data");
		
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi")
		));
		$t->define_field(array(
			"name" => "count",
			"caption" => t("Mitu"),
			"align" => "center"
		));
		$t->parse_xml_def("shop/prods_table");
		$matchers = array();
		foreach($t->rowdefs as $row)
		{
			if($row["name"] == "name" || $row["name"] == "count")
			{
				continue;
			}
			$matchers[$row["name"]] = $row["name"];
		}
		
		$t->define_chooser(array(
			"field" => "id",
			"name" => "sel",
		));
		$conf = $arr["obj_inst"]->prop("confirmed") == 1;
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PRODUCT")) as $c)
		{
			$_tmp = array();
			$id = $c->prop("to");
			$vals = array();
			foreach(safe_array($pd_data[$id]) as $fl => $val)
			{
				$vals[$fl] = html::textbox(array(
					"name" => "prod_data[$id][$fl]",
					"value" => $val,
					"size" => 10,
				));
				$_tmp[$fl] = $fl;
			}
			$leftover = array_diff($matchers, $_tmp);
			foreach($leftover as $key)
			{
				$vals[$key] = html::textbox(array(
					"name" => "prod_data[$id][$key]",
					"size" => 10,
				));
			}
			if ($conf)
			{
				$cnt = $pd[$id];
			}
			else
			{
				$cnt = html::textbox(array(
					"name" => "pd[$id]",
					"value" => $pd[$id],
					"size" => 5
				));
			}
			$name = $c->prop("to.name");
			$name = html::get_change_url($id, array(), $name);

			$to = $c->to();
			$prod_conn = reset($to->connections_to(array("from.class_id" => "CL_SHOP_PRODUCT")));
			if ($prod_conn)
			{
				$name = t("Pakend: ").$name.",  ".t("Toode: ").html::get_change_url($prod_conn->prop("from"), array(), $prod_conn->prop("from.name"));
			}

			$t->define_data(array(
				"name" => $name,
				"count" => $cnt,
				"id" => $id,
			) + $vals);
		}
	}

	function save_ord_table(&$arr)
	{
		$arr["obj_inst"]->set_meta("ord_item_data", $arr["request"]["prod_data"]);
		$arr["obj_inst"]->set_meta("ord_content", $arr["request"]["pd"]);
	}

	function do_confirm($o)
	{
		if ($o->prop("confirmed") == 1)
		{
			// make sure we don't re-confirm orders
			return;
		}

		// create wh_export, add products to that and confirm THAT
		// how to find the folder where to create the export -
		// find the warehouse, from that get config, from that exp folder
		// find warehouse - order is connected to warehouse

		$parent = 0;

		$warehouse = obj($o->prop("warehouse"));
			
		$conf = obj($warehouse->prop("conf"));

		$parent = $conf->prop("export_fld");

		error::raise_if(!$parent, array(
			"id" => ERR_ORDER,
			"msg" => t("shop_order::do_confirm(): could not find parent folder for warehouse export!")
		));


		$e = obj();
		$e->set_class_id(CL_SHOP_WAREHOUSE_EXPORT);
		$e->set_parent($parent);
		$e->set_name(sprintf(t("Lao v&auml;ljaminek tellimuse %s p&otilde;hjal"), $o->name()));
		$e->set_meta("exp_content", $o->meta("ord_content"));
		$e->save();

		// go over all products in order
		foreach($o->connections_from(array("type" => 1)) as $c)
		{
			$e->connect(array(
				"to" => $c->prop("to"),
				"reltype" => 1 // RELTYPE_PRODUCT
			));
		}

		// also connect the export to warehouse
		$warehouse->connect(array(
			"to" => $e,
			"reltype" => 4 // RELTYPE_STORAGE_EXPORT
		));

		$o->connect(array(
			"to" => $e->id(),
			"reltype" => 2 // RELTYPE_EXPORT
		));

		$e->connect(array(
			"to" => $o->id(),
			"reltype" => 2 // RELTYPE_ORDER
		));

		// now, also confirm export
		$exp = get_instance(CL_SHOP_WAREHOUSE_EXPORT);
		$exp->do_confirm($e);

		$o->set_prop("confirmed", 1);
		$o->save();
	}

	function get_price($o)
	{
		$d = $o->meta("ord_content");

		$sum = 0;

		// go over all products in order
		foreach($o->connections_from(array("type" => 1)) as $c)
		{
			$it = $c->to();
			$inst = $it->instance();

			$sum += $inst->get_price($it) * $d[$it->id()];
		}
	
		return number_format($sum, 2);
	}

	function start_order($warehouse, $oc = NULL)
	{
		$this->order_items = array();
		$this->order_item_data = array();
		$this->order_warehouse = $warehouse;
		$this->order_center = $oc;
	}

	function add_item($item, $quantity, $item_data=false)
	{
		$this->order_items[$item] = $quantity;
		if($item_data)
		{
			$this->order_item_data[$item] = $item_data;
		}
	}

	/** returns order id 
	**/
	function finish_order($params = array())
	{
		extract($params);

		$wh = $this->order_warehouse->instance();

		$oi = obj();
		$oi->set_parent($wh->get_order_folder($this->order_warehouse));
		$oi->set_name(sprintf(t("Tellimus laost %s"), $this->order_warehouse->name()));
		$oi->set_class_id(CL_SHOP_ORDER);
		$oi->set_prop("warehouse", $this->order_warehouse->id());
		
		if ($params["user_data"])
		{
			$oi->set_meta("user_data", $params["user_data"]);
		}
		else
		{
			$oi->set_meta("user_data", $cart["user_data"]);
		}

		$oi->set_meta("discount", $params["discount"]);
		$oi->set_meta("prod_paging", $params["prod_paging"]);
		$oi->set_meta("postal_price", $params["postal_price"]);
		$oi->set_meta("payment", $params["payment"]);
		$oi->set_meta("payment_type", $params["payment_type"]);

		if ($this->order_center)
		{
			$oi->set_prop("oc", $this->order_center->id());
		}
		$id = $oi->save();

		$oi->connect(array(
			"to" => $this->order_warehouse->id(),
			"reltype" => 5 // RELTYPE_WAREHOUSE
		));
		// also, warehouse -> order connection
		$this->order_warehouse->connect(array(
			"to" => $oi->id(),
			"reltype" => 5 // RELTYPE_ORDER
		));

		if ($this->order_center)
		{
			$oi->connect(array(
				"to" => $this->order_center->id(),
				"reltype" => 6 // RELTYPE_ORDER_CENTER
			));
		}

		// connect to current person/company
		if (!$pers_id)
		{
			$us = get_instance(CL_USER);
			$pers_id = $us->get_current_person();
		}

		if ($pers_id)
		{
			$oi->connect(array(
				"to" => $pers_id,
				"reltype" => 3 // RELTYPE_PERSON
			));
			$oi->set_prop("orderer_person", $pers_id);
			$p_o = obj($pers_id);
			$p_o->connect(array(
				"to" => $oi->id(),
				"reltype" => 20 // RELTYPE_ORDER
			));
		}

		if (!$com_id)
		{
			$com_id = $us->get_current_company();
		}

		if ($com_id)
		{
			$oi->connect(array(
				"to" => $com_id,
				"reltype" => 4 // RELTYPE_ORG
			));
			$oi->set_prop("orderer_company", $com_id);
			$p_o = obj($com_id);
			$p_o->connect(array(
				"to" => $oi->id(),
				"reltype" => 27 // RELTYPE_ORDER
			));
		}

		// seller, seller_company fro current user
		if (aw_global_get("uid") != "")
		{
			$us = get_instance(CL_USER);
			$c_com_id = $us->get_current_company();
			if ($wh->is_manager_co($this->order_warehouse, $c_com_id))
			{
				$oi->connect(array(
					"to" => $c_com_id,
					"reltype" => 4 // RELTYPE_ORG
				));
				$oi->set_prop("seller_company", $c_com_id);
				
				$c_per_id = $us->get_current_person();
				
				$oi->connect(array(
					"to" => $c_per_id,
					"reltype" => 3 // RELTYPE_PERSON
				));
				$oi->set_prop("seller_person", $c_per_id);
			}
		}
		

		// now, products
		$mp = array();
		$sum = 0;
		foreach($this->order_items as $iid => $quant)
		{
			if ($quant < 1 || !is_oid($iid) || !$this->can("view", $iid))
			{
				continue;
			}
			$i_o = obj($iid);
			$i_inst = $i_o->instance();

			$oi->connect(array(
				"to" => $iid,
				"reltype" => 1 // RELTYPE_PRODUCT
			));
			$mp[$iid] = $quant;
			$sum += ($quant * $i_inst->get_price($i_o));
		}
		$oi->set_meta('ord_item_data', $this->order_item_data);
		$oi->set_meta("ord_content", $mp);
		$oi->set_prop("sum", $sum);
		$oi->save();

		$email_subj = sprintf(t("Tellimus laost %s"), $this->order_warehouse->name());
		$mail_from_addr = "automatweb@automatweb.com";
		$mail_from_name = str_replace("http://", "", aw_ini_get("baseurl"));
		
		$oc_id = $this->order_warehouse->prop("order_center");
		if (is_oid($oc_id) && $this->can("view", $oc_id))
		{
			$order_center = obj($oc_id);
			if (is_oid($order_center->prop("cart")) && $this->can("view", $order_center->prop("cart")))
			{
				$cart_o = obj($order_center->prop("cart"));
				if ($cart_o->prop("email_subj") != "")
				{
					$email_subj = $cart_o->prop("email_subj");
				}
			}
			if ($order_center->prop("mail_from_addr"))
			{
				$mail_from_addr = $order_center->prop("mail_from_addr");
			}
			if ($order_center->prop("mail_from_name"))
			{
				$mail_from_name = $order_center->prop("mail_from_name");
			}
		}

		// also, if the warehouse has any e-mails, then generate html from the order and send it to those dudes
		$emails = $this->order_warehouse->connections_from(array("type" => "RELTYPE_EMAIL"));
		if (count($emails) > 0)
		{
			$html = $this->show(array(
				"id" => $oi->id()
			));

			foreach($emails as $c)
			{
				$eml = $c->to();
			

				$awm = get_instance("protocols/mail/aw_mail");
				$awm->create_message(array(
					"froma" => $mail_from_addr,
					"fromn" => $mail_from_name,
					"subject" => $email_subj,
					"to" => $eml->prop("mail"),
					"body" => strip_tags(str_replace("<br>", "\n",$html)),
				));
				$awm->htmlbodyattach(array(
					"data" => $html
				));
				$awm->gen_mail();
			}
		}

		// if the order center has an e-mail element selected, send the order to that one as well
		// but using a different template
		$ud = $oi->meta("user_data");
		//echo "mail to el = ".$this->order_center->prop("mail_to_el")." <br>";
		if (!$arr["no_send_mail"] && $this->order_center->prop("mail_to_el") != "" && ($_send_to = $ud[$this->order_center->prop("mail_to_el")]) != "")
		{
			if ($this->order_center->prop("mail_cust_content") != "")
			{
				$html = nl2br($this->order_center->prop("mail_cust_content"));
			}
			else
			{
				$html = $this->show(array(
					"id" => $oi->id(),
					"template" => "show_cust.tpl"
				));
			}

		//echo "sent to $_send_to content = $html <br>";
			$awm = get_instance("protocols/mail/aw_mail");
			$awm->create_message(array(
				"froma" => $mail_from_addr,
				"fromn" => $mail_from_name,
				"subject" => $email_subj,
				"to" => $_send_to,
				"body" => strip_tags(str_replace("<br>", "\n",$html)),
			));
			$awm->htmlbodyattach(array(
				"data" => $html
			));
			$awm->gen_mail();
		}

		return $oi->id();
	}

	/** shows thes order

		@attrib name=show nologin="1"

		@param id required type=int acl=view
	**/
	function show($arr)
	{
		if (!$arr["template"])
		{
			$arr["template"] = "show.tpl";
		}
		$this->read_any_template($arr["template"]);
		
		$o = obj($arr["id"]);
		$tp = $o->meta("ord_content");
		$ord_item_data = $o->meta('ord_item_data');

		// we need to sort the damn products based on their page values. if they are set of course. blech.
		// so go over prods, make sure all have page numbers and then sort by page numbers
		$prods = array();
		$pages = $o->meta("prod_paging");
		if (!is_array($pages))
		{
			$pages = array(1 => 1);
		}
		foreach($o->connections_from(array("type" => 1 /* RELTYPE_PRODUCT */)) as $c)
		{
			$prod = $c->to();
			if (!$pages[$prod->id()])
			{
				$pages[$prod->id()] = max($pages);
			}
			$prods[] = $prod;
		}
		$this->__sp = $pages;
		usort($prods, array(&$this, "__prod_show_sort"));

		$p = "";
		$total = 0;
		
		foreach($prods as $prod)
		{
			$inst = $prod->instance();
			$pr = $inst->get_calc_price($prod);
			
			$product_info = reset($prod->connections_to(array(
				"from.class_id" => CL_SHOP_PRODUCT,
			)));

			if (is_object($product_info))
			{
				$product_info = $product_info->from();
			}

			if (!is_object($product_info))
			{
				$product_info = $prod;
			}

			for( $i=1; $i<21; $i++)
			{
				$ui = $product_info->prop("user".$i);

				if ($i == 16 && aw_ini_get("site_id") == 139 && $product_info->prop("userch5"))
				{
					$ui = $prod->prop("user3");
				}

				$this->vars(array(
					'user'.$i => $ui,
					"packaging_user".$i => $prod->prop("user".$i),
					"packaging_uservar".$i => $prod->prop_str("uservar".$i)
				));
			}

			$product_info_i = $product_info->instance();
			$cur_tot = $tp[$prod->id()] * $product_info_i->get_calc_price($product_info);
			$prod_total += $cur_tot;
			$this->vars(array(
				"prod_name" => $product_info->name(),
				"prod_price" => $product_info_i->get_price($product_info),
				"prod_tot_price" => number_format($cur_tot, 2)
			));

			foreach(safe_array($ord_item_data[$prod->id()]) as $__nm => $__vl)
			{
				$this->vars(array(
					"order_data_".$__nm => $__vl
				));
			}

			$this->vars(array(
				"name" => $prod->name(),
				"p_name" => ($product_info ? $product_info->name() : $prod->name()),
				"quant" => $tp[$prod->id()],
				"price" => number_format($pr,2),
				"obj_tot_price" => number_format(((int)($tp[$prod->id()]) * $pr), 2),
				"order_data_color" => $ord_item_data[$prod->id()]["color"],
				"order_data_size" => $ord_item_data[$prod->id()]['size'],
				"order_data_price" => $ord_item_data[$prod->id()]['price'],
				"logged" => (aw_global_get("uid") == "" ? "" : $this->parse("logged"))
			));
			$total += ($pr * $tp[$prod->id()]);

			$p .= $this->parse("PROD");
		}

		$this->vars(array(
			"print_link" => aw_url_change_var("print", 1)
		));

		$objs = array();

		$oc = obj($o->prop("oc"));
		$oc_i = $oc->instance();

		// get person
		if ($o->prop("orderer_person"))
		{
			$po = obj($o->prop("orderer_person"));
			$this->vars(array(
				"person_name" => $po->name(),
			));
			$objs["user_data_person_"] = $po;
		}
		else
		if (($pp = $oc->prop("data_form_person")))
		{
			$_ud = $o->meta("user_data");
			$this->vars(array(
				"person_name" => $ud[$pp],
			));
		}

		if ($o->prop("orderer_company") && $this->can("view", $o->prop("orderer_company")))
		{
			$co = obj($o->prop("orderer_company"));
			$this->vars(array(
				"company_name" => $co->name(),
			));

			$objs["user_data_org_"] = $co;
		}
		else
		if (($pp = $oc->prop("data_form_company")))
		{
			$_ud = $o->meta("user_data");
			$this->vars(array(
				"person_name" => $ud[$pp],
			));
		}

		if (aw_global_get("uid") != "")
		{
			$vars = array();
			foreach($objs as $prefix => $obj)
			{
				$ops = $obj->properties();
				
				foreach($ops as $opk => $opv)
				{
					if($opk == "email_id" && is_oid($opv) && $this->can("view", $opv))
					{
						$ob = obj($opv);
						$vars[$prefix."email_value"] = $ob->prop("mail");
					}
					elseif($opk == "phone_id" && is_oid($opv) && $this->can("view", $opv))
					{
						$ob = obj($opv);
						$vars[$prefix."phone_value"] = $ob->name();
					}
					elseif($opk == "")
					{
					}
					
					$vars[$prefix.$opk] = $opv;
				}
			}

			$vars["logged"] = $this->parse("logged");
			$vars["username"] = aw_global_get("uid");
			$this->vars($vars);
		}

		$awa = new aw_array($o->meta("user_data"));
		foreach($awa->get() as $ud_k => $ud_v)
		{
			if (is_array($ud_v) && $ud_v["year"] != "")
			{
				$ud_v = $ud_v["day"].".".$ud_v["month"].".".$ud_v["year"];
			}
			$this->vars(array(
				"user_data_".$ud_k => $ud_v
			));
		}
		//arr($this->vars);

		$pl = "";
		if ($this->is_template("PROD_LONG"))
		{
			$prev_page = NULL;
			foreach($prods as $prod)
			{
				$pb = "";
				if ($pages[$prod->id()] != $prev_page && $prev_page != NULL)
				{
					$pb = $this->parse("PAGE_BREAK");
				}
				$inst = $prod->instance();

				$this->vars(array(
					"prod_html" => $inst->do_draw_product(array(
						"prod" => $prod,
						"layout" => $oc_i->get_long_layout_for_prod(array(
							"soc" => $oc,
							"prod" => $prod
						)),
						"oc_obj" => $oc,
						"quantity" => $tp[$prod->id()],
					)),
					"PAGE_BREAK" => $pb
				));
				
				$pl .= $this->parse("PROD_LONG");
				$prev_page = $pages[$prod->id()];
			}
		}

		// sellers
		$hs = "";
		if (is_oid($o->prop("seller_company")) && $this->can("view", $o->prop("seller_company")))
		{
			$seller_comp = obj($o->prop("seller_company"));
			$seller_person = obj();
			if (is_oid($o->prop("seller_person")) && $this->can("view", $o->prop("seller_person")))
			{
				$seller_person = obj($o->prop("seller_person"));
			}
			$this->vars(array(
				"seller_company" => $seller_comp->name(),
				"seller_person" => $seller_person->name()
			));
			$hs = $this->parse("HAS_SELLER");
		}
		else
		{
			$hs = $this->parse("NO_SELLER");
		}

		$total += $o->meta("postal_price");

		$total_incl_disc = ($total - ($total * ($o->meta("discount") / 100.0)));

		$this->vars(array(
			"HAS_SELLER" => $hs,
			"NO_SELLER" => "",
			"PROD" => $p,
			"PROD_LONG" => $pl,
			"total" => number_format($total,2),
			"prod_total" => number_format($prod_total,2),
			"total_incl_disc" => number_format($total_incl_disc,2),
			"id" => $o->id(),
			"order_pdf" => $this->mk_my_orb("gen_pdf", array("id" => $o->id())),
			"discount" => $o->meta("discount"),
			"postal_price" => number_format($o->meta("postal_price"))
		));

		if (!$arr["is_pdf"])
		{
			$this->vars(array(
				"IS_NOT_PDF" => $this->parse("IS_NOT_PDF")
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

		if (($imp = aw_ini_get("otto.import")) && $o->meta("payment_type") == "rent" && $this->is_template("HAS_RENT"))
		{
			$i = obj($imp);
			$cl_pgs = $this->make_keys(explode(",", $i->prop("jm_clothes")));
			$ls_pgs = $this->make_keys(explode(",", $i->prop("jm_lasting")));
			$ft_pgs = $this->make_keys(explode(",", $i->prop("jm_furniture")));
			foreach($prods as $prod)
			{
				$quant = $tp[$prod->id()];

				$pr = $prod;
				if ($pr->class_id() == CL_SHOP_PRODUCT_PACKAGING)
				{
					$c = reset($pr->connections_to(array("from.class_id" => CL_SHOP_PRODUCT)));
					$pr = $c->from();
				}

				$product_info = reset($prod->connections_to(array(
					"from.class_id" => CL_SHOP_PRODUCT,
				)));

				if (is_object($product_info))
				{
					$product_info = $product_info->from();
				}

				if (!is_object($product_info))
				{
					$product_info = $prod;
				}

				for( $i=1; $i<21; $i++)
				{
					$ui = $product_info->prop("user".$i);
					$this->vars(array(
						'user'.$i => $ui,
						"packaging_user".$i => $prod->prop("user".$i),
						"packaging_uservar".$i => $prod->prop_str("uservar".$i)
					));
				}

				$product_info_i = $product_info->instance();
				$cur_tot = $tp[$prod->id()] * $product_info_i->get_calc_price($product_info);
				$prod_total += $cur_tot;
				$this->vars(array(
					"prod_name" => $product_info->name(),
					"prod_price" => $product_info_i->get_price($product_info),
					"prod_tot_price" => number_format($cur_tot, 2)
				));

				foreach(safe_array($ord_item_data[$prod->id()]) as $__nm => $__vl)
				{
					$this->vars(array(
						"order_data_".$__nm => $__vl
					));
				}

				$_pr = $inst->get_calc_price($prod);

				$this->vars(array(
					"name" => $prod->name(),
					"p_name" => ($product_info ? $product_info->name() : $prod->name()),
					"quant" => $tp[$prod->id()],
					"price" => number_format($_pr,2),
					"obj_tot_price" => number_format(((int)($tp[$prod->id()]) * $_pr), 2),
					'order_data_color' => $ord_item_data[$prod->id()]['color'],
					'order_data_size' => $ord_item_data[$prod->id()]['size'],
					'order_data_price' => $ord_item_data[$prod->id()]['price'],
				));

				//$pr_price= ($_pr * $tp[$prod->id()]);

				$p .= $this->parse("PROD");

				if (get_class($inst) == "shop_product_packaging")
				{
					$pr_price = ($quant * $inst->get_prod_calc_price($prod));
				}
				else
				{
					$pr_price = ($quant * $inst->get_price($prod));
				}

				if ( $cl_pgs[$pr->parent()])
				{
					$cl_total += $pr_price;
					$cl_str .= $this->parse("PROD");
				}
				else
				if ($ft_pgs[$pr->parent()])
				{
					$ft_total += $pr_price;
					$ft_str .= $this->parse("PROD");
				}
				else
				if ($ls_pgs[$pr->parent()])
				{
					$ls_total += $pr_price;
					$ls_str .= $this->parse("PROD");
				}
			}

			$pmt = $o->meta("payment");
			$npc = max(2,$pmt["num_payments"]["clothes"]);
			$cl_payment = ($cl_total+($cl_total*($npc)*1.25/100))/($npc+1);
			$cl_tot_wr = ($cl_payment * ($npc+1));

			$ft_npc = max(2,$pmt["num_payments"]["furniture"]);
			$ft_first_payment = ($ft_total/5);
			$ft_payment = ($ft_total-$ft_first_payment+(($ft_total-$ft_first_payment)*$ft_npc*1.25/100))/($ft_npc+1);
			$ft_total_wr = $ft_payment * ($ft_npc+1) + $ft_first_payment;

			$ls_npc = max(2,$pmt["num_payments"]["last"]);
			$ls_payment = ($ls_total+($ls_total*($ls_npc)*1.25/100))/($ls_npc+1);
			$ls_total_wr = ($ls_payment * ($ls_npc+1));

			$this->vars(array(
				"PROD_RENT_CLOTHES" => $cl_str,
				"PROD_RENT_FURNITURE" => $ft_str,
				"PROD_RENT_LAST" => $ls_str,
				"total_clothes_price" => number_format($cl_total,2),
				"num_payments_clothes" => $npc+1,
				"payment_clothes" => number_format($cl_payment,2),
				"total_clothes_price_wr" => number_format($cl_tot_wr,2),
				"total_furniture_price" => number_format($ft_total,2),
				"first_payment_furniture" => number_format($ft_total/5,2),
				"num_payments_furniture" => $ft_npc+1,
				"payment_furniture" => number_format($ft_payment,2),
				"total_furniture_price_wr" => number_format($ft_total_wr,2),
				"total_last_price" => number_format($ls_total,2),
				"num_payments_last" => $ls_npc+1,
				"payment_last" => number_format($ls_payment,2),
				"total_last_price_wr" => number_format($ls_total_wr,2),
				"total_price_rent" => number_format($cl_tot_wr + $ft_total_wr + $ls_total_wr,2),
				"total_price_rent_w_pst" => number_format($cl_tot_wr + $ft_total_wr + $ls_total_wr + $o->meta("postal_price"),2),
				"postal_price" => number_format($o->meta("postal_price"))
			));
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

		return $this->parse();
	}

	function get_orderer($o)
	{
		$mb = $o->modifiedby();
		if (is_oid($o->prop("orderer_person")) && $this->can("view", $o->prop("orderer_person")))
		{
			$_person = obj($o->prop("orderer_person"));
			$mb = $_person->name();
		}

		if (is_oid($o->prop("orderer_company")) && $this->can("view", $o->prop("orderer_company")))
		{
			$_comp = obj($o->prop("orderer_company"));
			$mb .= " / ".$_comp->name();
		}

		return $mb;
	}

	function request_execute($o)
	{
		return $this->show(array(
			"id" => $o->id()
		));
	}

	function get_items_from_order($ord)
	{
		return $ord->meta("ord_content");
	}

	/** generates a pdf from the order

		@attrib name=gen_pdf nologin="1"

		@param id required type=int acl=view
		@param html optional

	**/
	function gen_pdf($arr)
	{
		$o = obj($arr["id"]);
		if ($o->prop("oc"))
		{
			$oc_o = obj($o->prop("oc"));
			$arr["template"] = $oc_o->prop("pdf_template");

			$arr["is_pdf"] = 1;
			$html = $this->show($arr);

			/*if ($tpl != "")
			{
				$this->read_template($tpl);
				$this->vars(array(
					"content" => $html
				));
				$html = $this->parse();
			}*/
		}

		if ($arr["html"])
		{
			if ($arr["return"] == 1)
			{
				return $html;
			}
			die($html);
		}

		header("Content-type: application/pdf");
		$conv = get_instance("core/converters/html2pdf");
		die($conv->convert(array(
			"source" => $html
		)));
	}

	function __prod_show_sort($a, $b)
	{
		$a_pg = $this->__sp[$a->id()];
		$b_pg = $this->__sp[$b->id()];
		if ($a_pg == $b_pg)
		{
			return 0;
		}
		return ($a_pg > $b_pg ? -1 : 1);
	}
	
	
	/**
		@attrib name=remove_items
		
		@param id required type=int acl=view
		@param group optional
		@param return_url optional
		@param sel required
	**/
	function remove_items($arr)
	{
		$obj = obj($arr["id"]);
		$prp_data = $obj->meta("ord_item_data");
		$prp_count = $obj->meta("ord_content");
		foreach(safe_array($arr["sel"]) as $sel)
		{
			unset($prp_data[$sel]);
			unset($prp_count[$sel]);
			$obj->disconnect(array(
				"from" => $sel,
				"reltype" => "RELTYPE_PRODUCT",
				"errors" => false,
			));
		}
		$obj->set_meta("ord_item_data", $prp_data);
		$obj->set_meta("ord_content", $prp_count);
		$obj->save();
		return html::get_change_url($arr["id"], array("group" => $arr["group"], "return_url" => urlencode($arr["return_url"])));
	}
}
?>
