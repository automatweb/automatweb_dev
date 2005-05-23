<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/orders/orders_form.aw,v 1.15 2005/05/23 09:29:56 kristo Exp $
// orders_form.aw - Tellimuse vorm 
/*

@classinfo syslog_type=ST_ORDERS_FORM relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize


@property postal_fee type=textbox
@caption Postikulu

@property orders_post_to type=textbox
@caption Mail kuhu tellimus saata

@property add_attach type=checkbox ch_value=1
@caption Lisa tellimus manusena

@property orders_to_mail type=checkbox ch_value=1
@caption Saada e-mail tellijale

@property orders_post_from type=textbox 
@caption Kliendile saatja (e-mail)

@property no_pdata_check type=checkbox ch_value=1
@caption Kasutajaandmed isikust

//@property order_center type=relpicker reltype=RELTYPE_ORDER_CENTER
//@caption Tellimiskeskkond


@groupinfo config caption=Seaded 
@default group=config

@property orderform type=relpicker reltype=RELTYPE_ORDERFORM
@caption Tellimuse seadetevorm

@property itemform type=relpicker reltype=RELTYPE_ITEMFORM
@caption Tellimuse rea seadetevorm

@property num_rows type=textbox size=6
@caption Mitu rida saab korraga korvi lisada

@property ordemail type=relpicker reltype=RELTYPE_MAIL
@caption Mail tellijale

@property thankudoc type=relpicker reltype=RELTYPE_THANKU
@caption Dokument kuhu suunata peale esitamist



@groupinfo ordering caption=Tellimine submit=no
@property ordering type=callback group=ordering no_caption=1 callback=do_order_form



@groupinfo payment caption=Makseviisid
@default group=payment

@property has_rent type=checkbox ch_value=1 
@caption Saab maksta j&auml;relmaksuga

@property rent_min_amt type=textbox size=6
@caption J&auml;relmasu min. summa

@property rent_min_amt_payment type=textbox size=6
@caption &Uuml;he makse miinimumsumma

@property rent_min_amt_payment_text type=textbox
@caption Miinimumsumma veateade

@property rent_max_amt_warn type=textbox size=6
@caption J&auml;relmaksu maksimaalne summa

@property rent_max_amt_warn_text type=textbox
@caption Maksimaalse summa &uuml;letamise hoiatus

@property rent_item_types type=table 
@caption Makseperioodid

@reltype ORDERFORM value=1 clid=CL_CFGFORM
@caption Tellimuse seadetevorm

@reltype ITEMFORM value=2 clid=CL_CFGFORM
@caption Tellimuse rea seadetevorm

@reltype THANKU value=3 clid=CL_DOCUMENT
@caption Dokument

@reltype ADDORDER value=4 clid=CL_ORDERS_ITEM
@caption Tellimuse lisa

@reltype MAIL value=5 clid=CL_MESSAGE
@caption Mail

//@reltype ORDER_CENTER value=6 clid=CL_SHOP_ORDER_CENTER
//@caption Tellimiskeskkond

*/
class orders_form extends class_base
{
	function orders_form()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_ORDERS_FORM,
			"tpldir" =>  "applications/orders",
		));
		$this->prod_statuses = array(
			"" => "t&auml;psustamisel",
			NULL => "t&auml;psustamisel",
			0 => "puudub",
			1 => "laos",
			2 => "pikk tarnet&auml;htaeg"
		);
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
	/*
	function do_order_form($arr)
	{
		$retval[] = array(
			"name" => "order_form_id",
			"type" => "hidden",
			"value" => $arr["obj_inst"]->id(),
		);
		$retval[] = array(
			"name" => "shop_cart",
			"type" => "text",
			"value" => $this->do_shop_cart($arr),
			"no_caption" => 1,
			
		);
		
		return $retval;
	}*/
	
	

	/**
		@attrib name=delete_from_order nologin=1
		@param id required type=int acl=delete
	**/
	function delete_from_order($arr)
	{
		if(is_oid($arr["id"]) && $this->can("delete", $arr["id"]) && $this->can("view", $arr["id"]))
		{
			$obj = &obj($arr["id"]);
			$obj->delete();
		}
		return aw_ini_get("baseurl")."/".$_SESSION["orders_section"];
	}
	
	function do_shop_cart($arr)
	{	
		aw_global_set("no_cache", 1);
		if(!$_SESSION["order_cart_id"])
		{
			$order = new object(array(
				"class_id" => CL_ORDERS_ORDER,
				"parent" => $arr["obj_inst"]->id(),
			));
			$order->save();
			$_SESSION["order_cart_id"] = $order->id();
			$_SESSION["order_form_id"] = $arr["obj_inst"]->id();
			if($conns = $arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ADDORDER")))
			{
				foreach ($conns as $conn)
				{
					$order->connect(array(
						"to" => $conn->prop("to"),
						"reltype" => "RELTYPE_ADDORDER"
					));
				}
			}
		}
		else
		{
			$order = &obj($_SESSION["order_cart_id"]);
		}
		
		$orders_inst = get_instance(CL_ORDERS_ORDER);
		$order->save();
		if($arr["request"]["persondata"] == 1)
		{
			$subgroup = "orderinfo";
						
			$cfgform = $arr["obj_inst"]->prop("orderform");

		}
		else
		{
			$subgroup = "orderitems";
		}
		
		
		$retval = $orders_inst->change(array(
			"class" => "orders_order",
			"group" => $subgroup,
			"id" => $order->id(),
			"cb_part" => 1,
			"cfgform" => $cfgform,
		));
		
		return $retval;
	}
	
	function parse_alias($arr)
	{
		$_SESSION["orders_section"] = $arr["alias"]["from"];
		$arr["id"] = $arr["alias"]["target"];
		$arr["group"] = "ordering";
		$arr["cb_part"] = 1;
		
		return $this->change($arr);
	}

	/**
		@attrib name=change nologin=1 all_args=1
	**/
	function change($arr)
	{
		//If admin side then dont use templates
		if(strstr($_SERVER['REQUEST_URI'], "/automatweb"))
		{
			return parent::change($arr);
		}
		
		if(($_GET["group"] == "confirmpage") || ($_GET["group"] == "persondata"))
		{
			if(!$_SESSION["order_cart_id"] || !$_SESSION["order_form_id"])
			{
				return aw_ini_get("baseurl");
			}
		}
		
		if(!is_oid($_SESSION["order_cart_id"]) || !$this->can("view", $_SESSION["order_cart_id"]) || !$_SESSION["order_form_id"])
		{
			$order = new object();
			$order->set_class_id(CL_ORDERS_ORDER);
			$order->set_parent($arr["oid"] ? $arr["oid"] : $arr["id"]);
			$order->save();

			$_SESSION["order_cart_id"] = $order->id();
			$_SESSION["order_form_id"] = $arr["alias"]["to"];
			
			if (!is_oid($_SESSION["order_form_id"]) || !$this->can("view", $_SESSION["order_form_id"]))
			{
				$ol = new object_list(array("class_id" => CL_ORDERS_FORM));
				$tmp = $ol->begin();
				$_SESSION["order_form_id"] = $tmp->id();
			}

			$form_obj = &obj($_SESSION["order_form_id"]);
		
			if($conns = $form_obj->connections_from(array("type" => "RELTYPE_ADDORDER")))
			{
				foreach ($conns as $conn)
				{
					$order->connect(array(
						"to" => $conn->prop("to"),
						"reltype" => 1
					));
				}
			}
		}
		else
		{
			$form_obj = obj($_SESSION["order_form_id"]);
			$order = obj($_SESSION["order_cart_id"]);
		}
		
		$this->read_template("orders_form.tpl");
		$this->submerge = 1;
		
		if($_GET["group"] == "persondata")
		{
			$this->vars(array(
				"add_persondata" => $this->get_persondata_form($arr),
				"shop_table" => $this->get_cart_table(),
			));
		}
		elseif ($_GET["group"] == "confirmpage" || $arr["show_order"] == 1)
		{
			$vars = array("order" => $order);
			if($form_obj->prop("no_pdata_check") == 1)
			{
				$vars["no_pdata_check"] = 1;
			}
			$vars = $vars + $arr;
			$this->vars(array(
				"show_confirm" => $this->get_confirm_persondata($vars),
				"shop_table" => ($_SESSION["orders_form"]["payment"]["type"] == "rent" ? $this->get_rent_table() : $this->get_cart_table()),
			));
		}
		else
		{
			$this->vars(array(
				"forwardurl" => aw_url_change_var(array("group" => ($form_obj->prop("no_pdata_check") == 1 ? "confirmpage" : "persondata"))),
			));
			$forward = $this->parse("forward_link");
			$this->vars(array(
				"add_items" => $this->get_additems_form($arr),
			));
			
			$conns = $order->connections_from(array(
				"type" => "RELTYPE_ORDER"
			));
		
			if($conns)
			{
				$this->vars(array(
					"forward_link" => $forward,
					"shop_table" => $this->get_cart_table(),
				));
			}
		}
		return $this->parse();
	}
	

	function get_confirm_persondata($arr)
	{
		extract($arr);
		$this->read_template("orders_confirm_persondata.tpl");

		if($no_pdata_check == 1)
		{
			if(aw_global_get("uid") != "")
			{
				$user = obj(aw_global_get("uid_oid"));
				$person = $user->get_first_obj_by_reltype("RELTYPE_PERSON");
			}
		}
		else
		{
			$person = current($order->connections_from(array(
				"type" => "RELTYPE_PERSON",
			)));
			if ($person)
			{
				$person = $person->to();
			}
		}
		
		if(!$person)
		{
			$this->read_template("orders_form.tpl");
			return;
		}
		
		if($person->prop("email"))
		{
			$mail_o = &obj($person->prop("email"));
			$email = $mail_o->prop("mail");
		}
		
		if($person->prop("phone"))
		{
			$phone_obj = &obj($person->prop("phone"));
			$phonenr = $phone_obj->name();
		}

		$this->vars(array(
			"person_name" => $person->name(),
			"firstname" => $person->prop("firstname"),
			"lastname" => $person->prop("lastname"),
			"personal_id" => $person->prop("personal_id"),
			"person_email" => $email,//$mail->prop("mail"),
			"person_phone" => $phonenr, //$phone->name(),
			"sendurl" => $this->mk_my_orb("send_order", array(), CL_ORDERS_ORDER), 
			"client_nr" => $order->prop("udef_textbox1"),
			"udef_textbox2" => $order->prop("udef_textbox2"),
			"udef_textbox3" => $order->prop("udef_textbox3"),
			"udef_textbox4" => $order->prop("udef_textbox4"),
			"udef_textbox5" => $order->prop("udef_textbox5"),
			"udef_textbox6" => $order->prop("udef_textbox6"),
			"udef_textbox7" => $order->prop("udef_textbox7"),
			"person_contact" => $person->prop("comment"),
			"birthday" => get_lc_date($person->prop("birthday"), 1),
			"payment_type" => ($_SESSION["orders_form"]["payment"]["type"] == "cod" ? "Lunamaks" : "J&auml;relmaks"),
		));
		$add_props = array();
		if(aw_global_get("uid") != "")
		{
			$user = obj(aw_global_get("uid_oid"));
			if($user->is_connected_to(array(
				"type" => "RELTYPE_PERSON",
				"to" => $person->id(),
			)))
			{
				foreach($user->properties() as $name => $val)
				{
					$add_props["user_data_$name"] = $val;
				}
			}
		}
		if($company = reset($person->connections_from(array("type" => "RELTYPE_WORK"))))
		{
			$com = $company->to();
			foreach($com->properties() as $name => $val)
			{
				if($name == "email_id" && is_oid($val) && $this->can("view", $val))
				{
					$ob = obj($val);
					$add_props["org_data_email_value"] = $ob->prop("mail");
				}
				elseif($name == "phone_id" && is_oid($val) && $this->can("view", $val))
				{
					$ob = obj($val);
					$add_props["org_data_phone_value"] = $ob->name();
				}
				elseif($name == "contact" && is_oid($val) && $this->can("view", $val))
				{
					$ob = obj($val);
					$add_props["org_data_address_value"] = $ob->name();
				}
				$add_props["org_data_$name"] = $val;
			}
		}
		$this->vars($add_props);

		if ($_SESSION["orders_form"]["payment"]["type"] == "rent")
		{
			$o = obj($_GET["id"]);
			$this->get_rent_table();
			if ($this->_totalsum > $o->prop("rent_max_amt_warn"))
			{
				$this->vars(array(
					"too_large_err" => $o->prop("rent_max_amt_warn_text")
				));
				$this->vars(array(
					"TOO_LARGE" => $this->parse("TOO_LARGE")
				));
			}
		}
		if($arr["show_order"] != 1)
		{
			$this->vars(array(
				"CONF_BLOCK" => $this->parse("CONF_BLOCK"),
				"SUBMIT_BLOCK" => $this->parse("SUBMIT_BLOCK"),
			));
		}

		$retval = $this->parse();
		$this->read_template("orders_form.tpl");

		return $retval;
	}
	
	function get_persondata_form($arr)	
	{
		$this->read_template("orders_persondata.tpl");
		
		$this->vars(array(
			"id" => $_SESSION["order_cart_id"],
			"udef_checkbox1_error" => $_SESSION["udef_checkbox1_error"]
		));
		unset($_SESSION["udef_checkbox1_error"]);
		if($errors = aw_global_get("cb_values"))
		{
			foreach ($errors as $key => $value)
			{
				$tmp = array(
					$key."_error" => $value["error"]
				);
				$this->vars($tmp);
				unset($tmp);
			}
		}
		if($_SESSION["person_form_values"])
		{
			
			$this->vars(array(
				"selected_day".$_SESSION['person_form_values']['person_birthday']['day'] => "SELECTED",
				"selected_month".$_SESSION['person_form_values']['person_birthday']['day'] => "SELECTED"
			)); 
			foreach ($_SESSION["person_form_values"] as $key => $value)
			{
				$tmp = array(
					$key."_value" => $value
				);
				$this->vars($tmp);
				unset($tmp);
			}	
		}
		
		$yoptions[-1] = "--";
		for($i=1930; $i<date("Y"); $i++)
		{
			$yoptions[$i] = $i;
		}
		
		$year_select = html::select(array(
			"name" => 'person_birthday[year]',
			"options" => $yoptions,
			"value" => $_SESSION['person_form_values']['person_birthday']['year'],
		));
		
		if($_SESSION['person_form_values']['udef_textbox6'] == "esmakordselt")
		{	
			$udef_check2 = true;
		}
		else
		{
			$udef_check1 = true;
		}
		
		//XXX: temporary hack
		if($_SESSION["LC"]=="lv")
		{
			$pysiklient = "pastàvïgais klients";	
			$esmakordselt = "jauns klients";
		}
		else
		{
			$pysiklient = "p&uuml;siklient";
			$esmakordselt = "esmakordselt";
		}
		
		$cv = aw_global_get("cb_values");
		$this->vars(array(
			"year_select" => $year_select,
			"customer_type1" => html::radiobutton(array(
				"name" => "udef_textbox6",
				"value" => $pysiklient,
				"checked" => $udef_check1,
				"onclick" => "check_rent()"
			)),
			"udef_checkbox1" => html::checkbox(array(
				"name" => "udef_checkbox1",
				"value" => 1,
				"checked" => $_SESSION['person_form_values']['udef_checkbox1'],
			)),
			"customer_type2" => html::radiobutton(array(
				"name" => "udef_textbox6",
				"value" => $esmakordselt,
				"checked" => $udef_check2,
				"onclick" => "check_rent()"
			)),
			"udef_checkbox1_error" => $cv["udef_checkbox1"]["error"]
		));

		$o = obj($arr["id"]);
		if ($o->prop("has_rent"))
		{
			$cr = false;
			if ($_SESSION["orders_form"]["payment"]["type"] == "rent" || $_SESSION['person_form_values']['udef_textbox6'] != "esmakordselt")
			{
				$cr = true;
			}

			if ($this->get_cart_sum() < $o->prop("rent_min_amt"))
			{
				$cr = false;
			}

			$this->vars(array(
				"cod_selected" => checked($_SESSION["orders_form"]["payment"]["type"] != "rent"),
				"rent_selected" => checked($_SESSION["orders_form"]["payment"]["type"] == "rent")
			));

			if ($cr)
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
		
		unset($_SESSION["cb_values"]);
		$retval = $this->parse();
		$this->read_template("orders_form.tpl");
		return $retval;
	}
	
	function get_additems_form($arr)
	{
		$rval = "";
		$obj_inst = obj($arr["id"]);
		$this->read_template("orders_order_item.tpl");
		$num_rows = ((int)$obj_inst->prop("num_rows")) >= 1 ? (int)$obj_inst->prop("num_rows") : 1;
		$add_change_caption = "Lisa tellimusse";
		
		if($_GET["editid"])
		{
			$num_rows = 1;
			$obj = &obj($_GET["editid"]);
			$_SESSION["order_eoid"] = $_GET["editid"];
			
			$values[0]["name"] = $obj->prop("name");
			$values[0]["product_size"] = $obj->prop("product_size");
			$values[0]["product_color"] = $obj->prop("product_color");
			$values[0]["product_code"] = $obj->prop("product_code");
			$values[0]["product_count"] = $obj->prop("product_count");
			$values[0]["product_price"] = $obj->prop("product_price");
			$values[0]["product_page"] = $obj->prop("product_page");
			$values[0]["product_image"] = $obj->prop("product_image");
			
			$add_change_caption = "Salvesta muudatused";
		}
		else 
		{
			$values = safe_array($_SESSION["order_form_values"]);
		}
		$_tmp = $values;
		
		$errors = $_SESSION["order_form_errors"]["items"];
		$_ertmp = $errors;
		$this->vars(array(
			"add_change_caption" => $add_change_caption,
			"id" => $_SESSION["order_cart_id"],
		));
		reset($values);
		for($i = 0; $i < $num_rows; $i++)
		{
			if(list($key, $values) = @each($values))
			{
				$errors = $_ertmp[$key];
			}
			else
			{
				$values = array();
				$errors = array();
			}
			$this->vars(array(
				"num" => $i,
				"product_code_error" => $errors["product_code"]["msg"],
				"product_code_value" => $values["product_code"],
				
				"product_name_error" => $errors["name"]["msg"],
				"product_name_value" => $values["name"],
				
				"product_size_error" => $errors["product_size"]["msg"],
				"product_size_value" => $values["product_size"],
				
				"product_color_error" => $errors["product_color"]["msg"],
				"product_color_value" => $values["product_color"],
				
				"product_count_error" => $errors["product_count"]["msg"],
				"product_count_value" => $values["product_count"],
				
				"product_price_error" => $errors["product_price"]["msg"],
				"product_price_value" => $values["product_price"],
				
				"product_page_error" => $errors["product_page"]["msg"],
				"product_page_value" => $values["product_page"],
				
				"product_image_error" => $errors["product_image"]["msg"],
				"product_image_value" => $values["product_image"],
			));
			
			unset($_SESSION["order_form_errors"]["items"][$key]);
			unset($_SESSION["order_form_values"][$key]);
			$rval .= $this->parse("ELEMENT"); 
		}
		
		$this->vars(array(
			"ELEMENT" => $rval,
		));
		//$this->submerge = 1;
		$retval = $this->parse();
		$this->read_template("orders_form.tpl");
		$this->submerge = 1;
		
		return $retval;
	}

	function get_cart_items()
	{
		if (!is_oid($_SESSION["order_cart_id"]))
		{
			return aw_ini_get("baseurl");
		}
		$order = &obj($_SESSION["order_cart_id"]);
		$form = &obj($_SESSION["order_form_id"]);
		$conns = $order->connections_from(array(
			"type" => "RELTYPE_ORDER"
		));

		return new object_list($conns);
	}	

	function get_cart_sum()
	{
		$totalsum = 0;

		$order = &obj($_SESSION["order_cart_id"]);
		$form = &obj($_SESSION["order_form_id"]);
		$conns = $order->connections_from(array(
			"type" => "RELTYPE_ORDER"
		));

		$ol = new object_list($conns);
		foreach ($ol->arr() as $item)
		{
			$totalsum = $totalsum + $item->prop("product_count") * str_replace(",", ".", $item->prop("product_price"));
		}

		return $totalsum;
	}

	function get_rent_table()
	{
		$o = obj($_GET["id"]);
		$inf = $o->meta("rent_data");

		// get items in cart
		$items = $this->get_cart_items();

		$cats = array();
		foreach($items->arr() as $item)
		{
			$cats[(int)$_SESSION["orders_form"]["payment"]["itypes"][$item->id()]][$item->id()] = $item;
		}

		$states = $this->get_states();

		// display cats
		$item_cat = "";
		$totalsum = 0;
		foreach($cats as $cat => $items)
		{
			$item_in_cat = "";
			$tot_price = 0;
			foreach($items as $item)
			{
				$this->_insert_item_inf($item, $states);

				$tot_price += $item->prop("product_count") * str_replace(",", ".", $item->prop("product_price"));
				$item_in_cat .= $this->parse("ITEM_IN_CAT");
			}

			$dat = $inf[(int)$_SESSION["orders_form"]["payment"]["itypes"][$item->id()]];

			$prepayment = (($tot_price / 100.0) * (float)$inf[$cat]["prepayment"]);
			$num_payments = max($_SESSION["orders_form"]["payment"]["lengths"][$item->id()], $dat["min_mons"]);

			$cp = $tot_price - $prepayment;

			$percent = $inf[$cat]["interest"];

			$payment = ($cp+($cp*$num_payments*(1+($percent/100))/100))/($num_payments+1);

			$rent_price = $payment * ($num_payments+1) + $prepayment;

			$totalsum += $rent_price;

			$this->vars(array(
				"catalog_price" => number_format($tot_price, 2),
				"prepayment_price" => number_format($prepayment,2),
				"prepayment" => (int)$inf[$cat]["prepayment"],
				"num_payments" => $num_payments+1,
				"rent_payment" => number_format($payment,2),
				"total_rent_price" => number_format($rent_price,2)
			));

			$this->vars(array(
				"cat_name" => $inf[$cat]["type"],
				"ITEM_IN_CAT" => $item_in_cat,
				"HAS_PREPAYMENT" => ($inf[$cat]["prepayment"] > 0 ? $this->parse("HAS_PREPAYMENT") : "")
			));

			$item_cat .= $this->parse("ITEM_CAT");
		}
			
		$form = &obj($_SESSION["order_form_id"]);
		$this->vars(array(
			"ITEM_CAT" => $item_cat,
			"totalsum" => number_format($totalsum + $form->prop("postal_fee"), 2),
			"postal_fee" => $form->prop("postal_fee"),
			"print_url" => aw_url_change_var("print", 1)
		));
		$this->_totalsum = $totalsum + $form->prop("postal_fee");

		$retval = $this->parse("shop_table_rent");
		return $retval;
	}

	function get_cart_table()
	{	
		$order = &obj($_SESSION["order_cart_id"]);
		$form = &obj($_SESSION["order_form_id"]);
		if(!$conns = $order->connections_from(array(
			"type" => "RELTYPE_ORDER"
		)))
		{
			return;
		}

		$states_f = @file(aw_ini_get("site_basedir")."/public/laoseis.txt");
		$states = array();
		foreach(safe_array($states_f) as $s_l)
		{
			list($s_c, $s_v) = ecplode(";", $s_l);
			$states[$s_c] = $s_v;
		}
		
		$states = $this->get_states();

		$ol = new object_list($conns);
		$this->submerge = 1;
		foreach ($ol->arr() as $item)
		{
			$_state = $states[$item->prop("product_code")];
			$this->vars(array(
				"name" => $item->name(),
				"editurl" => aw_url_change_var(array(
					"editid" => $item->id(), "group" => "")),
				"delete_href" => html::href(array(
					"url" => $this->mk_my_orb("delete_from_order",array(
						"id" => $item->id(),
					), CL_ORDERS_FORM),
					"caption" => t("Kustuta"))),
				"delete_url" => $this->mk_my_orb("delete_from_order",array(
						"id" => $item->id(),
					), CL_ORDERS_FORM),	
				
				"product_code" => $item->prop("product_code"),
				"product_color" => $item->prop("product_color"),
				"product_size" => $item->prop("product_size"),
				"product_count" => $item->prop("product_count"),
				"product_price" => $item->prop("product_price"),
				"product_image" => $item->prop("product_image"),
				"product_page" => $item->prop("product_page"),
				"product_sum" => $item->prop("product_count") * str_replace(",", ".", $item->prop("product_price")),
				"product_status" => ""/*$this->prod_statuses[$_state]*/
			));
			if(!$_SESSION["show_order"])
			{
				$this->vars(array("CHANGE_BLOCK" => $this->parse("CHANGE_BLOCK")));
			}
			$retval.= $this->parse("shop_cart_table");
			$totalsum = $totalsum + $item->prop("product_count") * str_replace(",", ".", $item->prop("product_price"));
		}
		
		$totalsum = $totalsum + $form->prop("postal_fee");
		
		$this->vars(array(
			"shop_cart_table" => $retval,
			"totalsum" => $totalsum,
			"postal_fee" => $form->prop("postal_fee"),
		));
		$retval = $this->parse("shop_table");
		return $retval;
	}

	function get_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "rent_item_types":
				$this->_do_rent_item_types($arr);
				break;
		}
		return PROP_OK;
	}

	function _init_rent_item_types_t(&$t)
	{
		$t->define_field(array(
			"name" => "type",
			"caption" => t("Kauba t&uuml;&uuml;p"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "min_mons", 
			"caption" => t("Min. Kuud"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "max_mons",
			"caption" => t("Max. Kuud"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "prepayment",
			"caption" => t("Esmase sissemakse %"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "interest",
			"caption" => t("Intressi %"),
			"align" => "center"
		));
	}

	function _do_rent_item_types($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_rent_item_types_t($t);

		$rent_data = safe_array($arr["obj_inst"]->meta("rent_data"));
		$rent_data[""] = array();

		foreach($rent_data as $dat)
		{
			++$idx;
			$t->define_data(array(
				"type" => html::textbox(array(
					"name" => "dat[$idx][type]",
					"value" => $dat["type"]
				)),
				"min_mons" => html::textbox(array(
					"name" => "dat[$idx][min_mons]",
					"value" => $dat["min_mons"],
					"size" => 5,
				)),
				"max_mons" => html::textbox(array(
					"name" => "dat[$idx][max_mons]",
					"value" => $dat["max_mons"],
					"size" => 5,
				)),
				"prepayment" => html::textbox(array(
					"name" => "dat[$idx][prepayment]",
					"value" => $dat["prepayment"],
					"size" => 5,
				)),
				"interest" => html::textbox(array(
					"name" => "dat[$idx][interest]",
					"value" => $dat["interest"],
					"size" => 5,
				)),
			));
		}
		$t->set_sortable(false);
	}

	function set_property($arr)
	{
		$prop =& $arr["prop"];
		switch($prop["name"])
		{
			case "rent_item_types":
				$inf = array();
				foreach(safe_array($arr["request"]["dat"]) as $idx => $dat)
				{
					if ($dat["type"] != "" && $dat["min_mons"] && $dat["max_mons"])
					{
						$inf[] = $dat;
					}
				}
				$arr["obj_inst"]->set_meta("rent_data", $inf);
				break;
		}
		return PROP_OK;
	}

	function _insert_item_inf($item, $states = NULL)
	{
		$name = $item->name();
		if (false && isset($states[$item->prop("product_code")]))
		{
			$str = $this->prod_statuses[$states[$item->prop("product_code")]];
			$name = "<a href='javascript:void(0)' alt='$str' title='$str'>$name</a>";
		}
		$this->vars(array(
			"product_code" => $item->prop("product_code"),
			"product_color" => $item->prop("product_color"),
			"product_size" => $item->prop("product_size"),
			"product_count" => $item->prop("product_count"),
			"product_price" => $item->prop("product_price"),
			"product_image" => $item->prop("product_image"),
			"product_page" => $item->prop("product_page"),
			"product_sum" => $item->prop("product_count") * str_replace(",", ".", $item->prop("product_price")),
			"name" => $name,
		));
	}

	function get_states()
	{
		$states_f = @file(aw_ini_get("site_basedir")."/public/laoseis.txt");
		$states = array();
		foreach(safe_array($states_f) as $s_l)
		{
			if (trim($s_l) != "")
			{
				list($s_c, $s_v) = explode(";", $s_l);
				$states[$s_c] = trim($s_v);
			}
		}

		return $states;
	}
}
?>
