<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order.aw,v 1.9 2004/07/29 13:30:09 rtoomas Exp $
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
@caption M&uuml;ja esindaja

@property seller_company type=relpicker reltype=RELTYPE_ORG table=objects field=meta method=serialize
@caption M&uuml;ja

@property oc type=relpicker reltype=RELTYPE_ORDER_CENTER table=aw_shop_orders field=aw_oc_id 
@caption Tellimiskeskkond

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE table=aw_shop_orders field=aw_warehouse_id 
@caption Ladu

@tableinfo aw_shop_orders index=aw_oid master_table=objects master_index=brother_of

@groupinfo items caption="Tellimuse sisu"

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

	function _init_ord_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi"
		));

		$t->define_field(array(
			"name" => "count",
			"caption" => "Mitu",
			"align" => "center"
		));
	}

	function do_ord_table(&$arr)
	{
		$pd = $arr["obj_inst"]->meta("ord_content");

		$this->_init_ord_table($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_PRODUCT)) as $c)
		{
			if ($arr["obj_inst"]->prop("confirmed") == 1)
			{
				$cnt = $pd[$c->prop("to")];
			}
			else
			{
				$cnt = html::textbox(array(
					"name" => "pd[".$c->prop("to")."]",
					"value" => $pd[$c->prop("to")],
					"size" => 5
				));
			}
			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"count" => $cnt
			));
		}
	}

	function save_ord_table(&$arr)
	{
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

		error::throw_if(!$parent, array(
			"id" => ERR_ORDER,
			"msg" => "shop_order::do_confirm(): could not find parent folder for warehouse export!"
		));


		$e = obj();
		$e->set_class_id(CL_SHOP_WAREHOUSE_EXPORT);
		$e->set_parent($parent);
		$e->set_name("Lao v&auml;ljaminek tellimuse ".$o->name()." p&otilde;hjal");
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
		$exp = get_instance("applications/shop/shop_warehouse_export");
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
		$oi->set_name("Tellimus laost ".$this->order_warehouse->name());
		$oi->set_class_id(CL_SHOP_ORDER);
		$oi->set_prop("warehouse", $this->order_warehouse->id());
		
		if ($params["user_data"])
		{
			$oi->set_meta("user_data", $params["user_data"]);
		}
		else
		{
			$oi->set_meta("user_data", $_SESSION["cart"]["user_data"]);
		}

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
			$us = get_instance("core/users/user");
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
			$us = get_instance("core/users/user");
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
			if ($quant < 1)
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

				$awm = get_instance("aw_mail");
				$awm->create_message(array(
					"froma" => "automatweb@automatweb.com",
					"fromn" => str_replace("http://", "", aw_ini_get("baseurl")),
					"subject" => "Tellimus laost ".$this->order_warehouse->name(),
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
		if ($this->order_center->prop("mail_to_el") != "" && ($_send_to = $ud[$this->order_center->prop("mail_to_el")]) != "")
		{
			$html = $this->show(array(
				"id" => $oi->id(),
				"template" => "show_cust.tpl"
			));

			$awm = get_instance("aw_mail");
			$awm->create_message(array(
				"froma" => "automatweb@automatweb.com",
				"fromn" => str_replace("http://", "", aw_ini_get("baseurl")),
				"subject" => "Tellimus laost ".$this->order_warehouse->name(),
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

		//SIIN TEHAKSE VIIMANE TABEL
		$p = "";
		$total = 0;
		foreach($o->connections_from(array("type" => 1 /* RELTYPE_PRODUCT */)) as $c)
		{
			$prod = $c->to();
			$inst = $prod->instance();
			$pr = $inst->get_calc_price($prod);
			
			$product_info = reset($prod->connections_to(array(
						"from.class_id" => CL_SHOP_PRODUCT,
			)));
			if(is_object($product_info))
			{
				$product_info = $product_info->from();
				for($i=1;$i<21;$i++)
				{
					$this->vars(array('user'.$i=>$product_info->prop('user'.$i)));
				}
			}

			$this->vars(array(
				"name" => $prod->name(),
				"quant" => $tp[$prod->id()],
				"price" => number_format($pr,2),
				"obj_tot_price" => number_format(((int)($tp[$prod->id()]) * $pr), 2),
				'order_data_color' => $ord_item_data[$prod->id()]['color'],
				'order_data_size' => $ord_item_data[$prod->id()]['size'],
			));

			$total += ($pr * $tp[$prod->id()]);

			$p .= $this->parse("PROD");
		}

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
					$vars[$prefix.$opk] = $opv;
				}
			}

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

		$pl = "";
		if ($this->is_template("PROD_LONG"))
		{
			foreach($o->connections_from(array("type" => "RELTYPE_PRODUCT")) as $c)
			{
				$prod = $c->to();
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
					))
				));
				
				$pl .= $this->parse("PROD_LONG");
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

		$this->vars(array(
			"HAS_SELLER" => $hs,
			"NO_SELLER" => "",
			"PROD" => $p,
			"PROD_LONG" => $pl,
			"total" => number_format($total,2),
			"id" => $o->id(),
			"order_pdf" => $this->mk_my_orb("gen_pdf", array("id" => $o->id()))
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

		return $this->parse();
	}

	function get_orderer($o)
	{
		$m = $o->modifiedby();
		$mb = $m->name();
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

		header("Content-type: application/pdf");
		$conv = get_instance("core/converters/html2pdf");
		die($conv->convert(array(
			"source" => $html
		)));
	}
}
?>
