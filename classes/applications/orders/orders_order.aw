<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/orders/orders_order.aw,v 1.5 2005/02/15 13:15:58 kristo Exp $
// orders_order.aw - Tellimus 
/*
@classinfo syslog_type=ST_ORDERS_ORDER relationmgr=yes
@tableinfo orders_order index=oid master_table=objects master_index=brother_of

@default table=orders_order
@default group=orderinfo

@property firstname type=textbox store=no
@caption Eesnimi

@property lastname type=textbox store=no
@caption Perekonnanimi

@property personal_id type=textbox store=no
@caption Isikukood

@property person_email type=textbox store=no
@caption E-mail

@property person_phone type=textbox store=no
@caption Telefon

@property person_contact type=textarea store=no 
@caption Aadress

@property person_birthday type=date_select year_from=1930 year_to=2010 default=-1 store=no
@caption Sünnipäev

@property order_completed type=hidden field=meta method=serialize table=objects

@property udef_textbox1 type=textbox user=1
@property udef_textbox2 type=textbox user=1
@property udef_textbox3 type=textbox user=1
@property udef_textbox4 type=textbox user=1
@property udef_textbox5 type=textbox user=1
@property udef_textbox6 type=textbox user=1 field=meta method=serialize table=objects
@property udef_textbox7 type=textbox user=1 field=meta method=serialize table=objects


@property udef_textarea1 type=textarea user=1
@property udef_textarea2 type=textarea user=1
@property udef_textarea3 type=textarea user=1
@property udef_textarea4 type=textarea user=1
@property udef_textarea5 type=textarea user=1

@property udef_picker1 type=classificator user=1
@property udef_picker2 type=classificator user=1
@property udef_picker3 type=classificator user=1
@property udef_picker4 type=classificator user=1
@property udef_picker5 type=classificator user=1

@property udef_checkbox1 type=checkbox user=1 field=meta method=serialize table=objects


@property submit2 type=submit action=do_persondata_submit store=no group=orderinfo
@caption Kinnita tellimus

@property orders type=releditor store=no props=name,product_code,product_color,product_size,product_count,product_price reltype=RELTYPE_ORDER group=orderitems
@property submit type=submit group=orderitems store=no
@caption Lisa tellimus

@property orders_table type=table store=no group=orderitems no_caption=1

@property forward type=submit action=do_persondata_form group=orderitems store=no
@caption Edasi


@reltype ORDER value=1 clid=CL_ORDERS_ITEM
@caption Tellitud asi

@reltype PERSON value=2 clid=CL_CRM_PERSON
@caption Tellija

@groupinfo orderinfo caption="Tellimuse andmed"
@groupinfo orderitems caption="Tellitud tooted"
*/

class orders_order extends class_base
{
	function orders_order()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/orders",
			"clid" => CL_ORDERS_ORDER
		));
	}
	
	//////
	// class_base classes usually need those, uncomment them if you want to use them

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		
		$person = $arr["obj_inst"]->get_first_obj_by_reltype('RELTYPE_PERSON');
		switch($prop["name"])
		{
			case "firstname":
				if($person)
				{
					$prop["value"] = $person->prop("firstname");
				}
			break;
			
			
			case "lastname":
				if($person)
				{
					$prop["value"] = $person->prop("lastname");
				}
			break;
			
			case "personal_id":
				if($person)
				{
					$prop["value"] = $person->prop("personal_id");
				}
			break;
			case "person_email":
				if($person && $person->prop("email"))
				{
					$email = &obj($person->prop("email"));
					$prop["value"] = $email->prop("mail");
				}
			break;
			
			case "person_phone":
				if($person && $person->prop("phone"))
				{
					$phone = &obj($person->prop("phone"));
					$prop["value"] = $phone->prop("name");
				}
			break;
			case "person_contact":
				if($person)
				{
					$prop["value"] = $person->prop("comment");
				}
			break;
			
			case "orders_table":
				$table = &$prop["vcl_inst"];
				$table->define_field(array(
					"name" => "product_name",
					"caption" => "Toode",
				));
			
				$table->define_field(array(
					"name" => "product_code",
					"caption" => "Toote kood",
				));
					
				$table->define_field(array(
					"name" => "product_color",
					"caption" => "Värv",
				));
				
				$table->define_field(array(
					"name" => "product_size",
					"caption" => "Suurus",
				));
				
				$table->define_field(array(
					"name" => "product_count",
					"caption" => "Kogus",
				));
					
				$table->define_field(array(
					"name" => "product_price",
					"caption" => "Hind",
				));
				
				$conns = $arr["obj_inst"]->connections_from(array(
					"type" => "RELTYPE_ORDER"
				));
				
				$ol = new object_list($conns);
				
				foreach ($ol->arr() as $obj)
				{
					$table->define_data(array(
						"product_name" => $obj->name(),
						"product_code" => $obj->prop("product_code"),
						"product_color"=> $obj->prop("product_color"),
						"product_size" => $obj->prop("product_size"),
						"product_count" => $obj->prop("product_count"),
						"product_price" => $obj->prop("product_price"),
					));	
				}
				
			break;
			
			/*
			case "submit":
			break;*/
			
		};
		return $retval;
	}
	

	/*
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
	
	function callback_post_save($arr)
	{
		$props = $arr["request"];
		if($props["firstname"] || $props["lastname"] || $props["person_id"] || $props["person_email"] || $props["person_phone"] || $props["person_contact"])
		{
			if($person = $arr["obj_inst"]->get_first_obj_by_reltype('RELTYPE_PERSON'))
			{
				$person->set_prop("firstname", $props["firstname"]);
				$person->set_prop("lastname", $props["lastname"]);
				$person->set_prop("personal_id", $props["personal_id"]);
				$person->set_prop("comment", $props["person_contact"]);
				/*$person->set_prop("birthday", strtotime($props["person_birthday"]["year"]."-".$props["person_birthday"]["month"]."-".$props["person_birthday"]["day"]));*/
				
				$person->set_prop("birthday", mktime(0, 0, 0, $props["person_birthday"]["month"], $props["person_birthday"]["day"], $props["person_birthday"]["year"]));
				
				$person->save();
				
				if($props["person_email"])
				{
					$email = obj();
					$email->set_parent($person->id());
					$email->set_class_id(CL_ML_MEMBER);
					$email->set_prop("mail", $props["person_email"]);
					$email->save();
					
					$person->set_prop("email", $email->id());
					$person->save();
					$person->connect(array(
						"to" => $email->id(),
						"reltype" => "RELTYPE_EMAIL",
					));
				}
				
				if($props["person_phone"])
				{
					
					$phone = obj();
					$phone->set_parent($person->id());
					$phone->set_class_id(CL_CRM_PHONE);
					$phone->set_prop("name", $props["person_phone"]);
					$phone->save();
					
					$person->set_prop("phone", $phone->id());
					$person->save();
					$person->connect(array(
						"to" => $phone->id(),
						"reltype" => "RELTYPE_PHONE",
					));
				}
				
			}
			else
			{
				$person = obj();
				$person->set_parent($arr["obj_inst"]->id());
				$person->set_class_id(CL_CRM_PERSON);
		
				$person->set_prop("firstname", $props["firstname"]);
				$person->set_prop("lastname", $props["lastname"]);
				$person->set_prop("personal_id", $props["personal_id"]);
				$person->set_prop("comment", $props["person_contact"]);
				$person->set_prop("birthday", mktime(0, 0, 0, $props["person_birthday"]["month"], $props["person_birthday"]["day"], $props["person_birthday"]["year"]));
				$person->save();
				
				if($props["person_email"])
				{
					$email = obj();
					$email->set_parent($person->id());
					$email->set_class_id(CL_ML_MEMBER);
					$email->set_prop("mail", $props["person_email"]);
					$email->save();
					
					$person->set_prop("email", $email->id());
					$person->save();
					$person->connect(array(
						"to" => $email->id(),
						"reltype" => "RELTYPE_EMAIL",
					));
				}
				
				if($props["person_phone"])
				{
					
					$phone = obj();
					$phone->set_parent($person->id());
					$phone->set_class_id(CL_CRM_PHONE);
					$phone->set_prop("name", $props["person_phone"]);
					$phone->save();
					
					$person->set_prop("phone", $phone->id());
					$person->save();
					$person->connect(array(
						"to" => $phone->id(),
						"reltype" => "RELTYPE_PHONE",
					));
				}
				
				$arr["obj_inst"]->connect(array(
					"to" => $person->id(),
					"reltype" => "RELTYPE_PERSON",
				));	
			}	
		}
	}
	
/**
	@attrib name=add_to_cart nologin=1 all_args=1
**/
	function add_to_cart($arr)
	{
		//This solutions sucks, but cant find better one now
		$submit_data = $arr["orders"];
		$submit_data["class"] = "orders_item";
		$submit_data["group"] = "general";
		$submit_data["parent"] = $_SESSION["order_cart_id"];
		
		$oform = &obj($_SESSION["order_form_id"]);
		$check["cfgform_id"] = $oform->prop("itemform");
		$check["request"] = $submit_data;
	
		$errors = $this->validate_data($check);
		
		if(!$errors)
		{
			$item = &obj($_SESSION["order_eoid"]);
			$item->set_class_id(CL_ORDERS_ITEM);
			$item->set_parent($_SESSION["order_cart_id"]);
			$item->set_prop("name", $arr["orders"]["name"]);
			$item->set_prop("product_code", $arr["orders"]["product_code"]);
			$item->set_prop("product_color", $arr["orders"]["product_color"]);
			$item->set_prop("product_size", $arr["orders"]["product_size"]);
			$item->set_prop("product_count", $arr["orders"]["product_count"]);
			$item->set_prop("product_price", $arr["orders"]["product_price"]);
			$item->set_prop("product_image", $arr["orders"]["product_image"]);
			$item->set_prop("product_page", $arr["orders"]["product_page"]);
		
			$item->save();
			$conn = new connection();
			
			$conn->load(array(
				"from" => $_SESSION["order_cart_id"],
				"to" => $item->id(),
				"reltype" => 1,
			));
			$conn->save();
			unset($_SESSION["order_eoid"]);
		}
		else
		{
			$_SESSION["order_form_errors"]["items"] = $errors;
			$_SESSION["order_form_values"] = $arr["orders"];
		}

		return $this->mk_my_orb("change", array(
			"id" => $_SESSION["order_form_id"],
			"section" => $_SESSION["orders_section"],
			), CL_ORDERS_FORM);
	}
	
/**
	@attrib name=submit nologin=1 all_args=1
**/
	/*function submit($arr)
	{
		parent::submit($arr);
		/*return str_replace("orb.aw","",$this->mk_my_orb("change", 
			array(
				"id" => $_SESSION["order_form_id"],
				"group" => "ordering",
			), CL_ORDERS_FORM));
	}*/
	
/**
	@attrib name=do_persondata_form nologin=1
**/
	function do_persondata_form($arr)
	{
	
		return $this->mk_my_orb("change", 
			array(
				"id" => $_SESSION["order_form_id"],
				"group" => "ordering",
				"persondata" => 1,
				
			), CL_ORDERS_FORM);
	}
	
/**
	@attrib name=do_persondata_submit nologin=1 all_args=1
**/
	function do_persondata_submit($arr)
	{
		if(!$arr["udef_checkbox1"])
		{
			$arr["udef_checkbox1"] = 0;
		}
		
		$_SESSION["orders_form"]["payment"]["type"] = $arr["payment_method"];

		$oform = &obj($_SESSION["order_form_id"]);
		$arr["cfgform"] = $oform->prop("orderform");
		parent::submit($arr);
		
		$_SESSION["person_form_values"] = $arr;

		if (!$arr["udef_checkbox1"])
		{
			$cv = aw_global_get("cb_values");
			$cv["udef_checkbox1"]["error"] = "Tellimiseks peate n&otilde;ustuma tellimistingimustega";
			aw_session_set("cb_values", $cv);
		}
		if(aw_global_get("cb_values"))
		{	
			return $this->mk_my_orb("change", 
			array(
				"id" => $_SESSION["order_form_id"],
				"group" => "persondata",
			), CL_ORDERS_FORM);
			
		}

		// if use selected payent type as rent, go through the rent settings
		if ($arr["payment_method"] == "rent")
		{
			return $this->mk_my_orb("rent_step_1", array(
					"id" => $_SESSION["order_form_id"],
					"group" => "confirmpage",
					"section" => $_SESSION["orders_section"],
				)
			);
		}

		return $this->mk_my_orb("change", 
			array(
				"id" => $_SESSION["order_form_id"],
				"group" => "confirmpage",
				"section" => $_SESSION["orders_section"],
			), CL_ORDERS_FORM);
	}
	
	/**
		@attrib name=change nologin=1 all_args=1
	**//*
	function change($arr)
	{	
		//If admin side then dont use templates
		if(strstr($_SERVER['REQUEST_URI'],"/automatweb"))
		{
			return parent::change($arr);
		}
		
		$this->read_template("orders_order_item.tpl");
	
		return $this->parse();	
	}*/
	
	function send_mail_to_admin()
	{
		$form = &obj($_SESSION["order_form_id"]);
		$admin_mail = $form->prop("orders_post_to");
		
		$msg = &obj();
		$msg->set_class_id(CL_MESSAGE);
		$msg->set_parent($_SESSION["order_form_id"]);
		$msg->save();
		
		
		$form_inst = get_instance(CL_ORDERS_FORM);
		$order = &obj($_SESSION["order_cart_id"]);
		$person = $order->get_first_obj_by_reltype('RELTYPE_PERSON');
		
		if($person && $person->prop("email"))
		{
			$person_email = &obj($person->prop("email"));
			$msg->set_prop("mfrom", $person_email->prop("mail"));
		}
		
		$msg->set_prop("mto", $admin_mail);
		$msg->set_prop("name", $form->name());
		$msg->set_prop("html_mail", 1);
			
		
		$content = $form_inst->get_confirm_persondata($order)."<br />".($_SESSION["orders_form"]["payment"]["type"] == "rent" ? $form_inst->get_rent_table() : $form_inst->get_cart_table());
		$msg->set_prop("message", $content);
		
		$msg->save();
		$mail_inst = get_instance(CL_MESSAGE);	
		$mail_inst->send_message(array(
			"id" => $msg->id(),
		));
	}
	
	function send_mail_to_orderer()
	{
		$form = &obj($_SESSION["order_form_id"]);
		$mail_obj = &obj($form->prop("ordemail"));
		$order = &obj($_SESSION["order_cart_id"]);
		$person = $order->get_first_obj_by_reltype('RELTYPE_PERSON');
		if(!$person || !$person->prop("email"))
		{
			return;
		}
		
		$email = &obj($person->prop("email"));
		$mail_obj->set_prop("mto", $email->prop("mail"));
		$mail_obj->set_prop("mfrom", $form->prop("orders_post_from"));
		$mail_obj->save();
		$mail_inst = get_instance(CL_MESSAGE);	
		$mail_inst->send_message(array(
			"id" => $mail_obj->id(),
		));
	}
	
	/**
		@attrib name=send_order nologin=1
	**/
	function send_order($arr)
	{
		$order_form = &obj($_SESSION["order_form_id"]);
		$order = &obj($_SESSION["order_cart_id"]);
		if ($order->class_id() != CL_ORDERS_ORDER)
		{
			error::raise(array(
				"id" => "ERR_WTF",
				"msg" => "orders_order::send_order(): order_cart_id in session is of wrong class!!"
			));
		}
		$order->set_prop("order_completed", 1);
		if($order_form->prop("orders_to_mail"))
		{
			$this->send_mail_to_orderer();
		}
		$this->send_mail_to_admin();
		$order->save();
		unset($_SESSION["order_form_id"]);
		unset($_SESSION["order_cart_id"]);
		return aw_ini_get("baseurl")."/".$order_form->prop("thankudoc");
	}

	/**

		@attrib name=rent_step_1 nologin=1

		@param id required
		@param section required
	**/
	function rent_step_1($arr)
	{
		$this->read_template("rent_step_1.tpl");
	
		$o = obj($arr["id"]);
		$inf = $o->meta("rent_data");
		$item_types = array();
		foreach(safe_array($inf) as $idx => $dat)
		{
			$item_types[$idx] = $dat["type"];
		}

		// get items in cart
		$f = get_instance(CL_ORDERS_FORM);
		$items = $f->get_cart_items();

		foreach($items->arr() as $item)
		{
			$this->_insert_item_inf($item);
			$this->vars(array(
				"item_types" => html::select(array(
					"name" => "rent_items[".$item->id()."]",
					"options" => $item_types,
					"selected" => $_SESSION["orders_form"]["payment"]["itypes"][$item->id()]
				))
			));

			$rent_item .= $this->parse("RENT_ITEM");
		}

		$this->vars(array(
			"RENT_ITEM" => $rent_item,
			"reforb" => $this->mk_reforb("submit_rent_step_1", $arr),
			"back" => $this->mk_my_orb("change", array("id" => $arr["id"], "section" => $arr["section"], "group" => "persondata"), "orders_form")
		));		

		return $this->parse();
	}

	function _insert_item_inf($item)
	{
		$this->vars(array(
			"product_code" => $item->prop("product_code"),
			"product_color" => $item->prop("product_color"),
			"product_size" => $item->prop("product_size"),
			"product_count" => $item->prop("product_count"),
			"product_price" => $item->prop("product_price"),
			"product_image" => $item->prop("product_image"),
			"product_page" => $item->prop("product_page"),
			"product_sum" => $item->prop("product_count") * str_replace(",", ".", $item->prop("product_price")),
			"name" => $item->name(),
		));
	}

	/**

		@attrib name=submit_rent_step_1 nologin=1

	**/
	function submit_rent_step_1($arr)
	{
		// save item types
		foreach(safe_array($arr["rent_items"]) as $id => $type)
		{
			$_SESSION["orders_form"]["payment"]["itypes"][$id] = $type;
		}

		if ($arr["save_only"] != "")
		{
			return $this->mk_my_orb("rent_step_1", array("id" => $arr["id"], "section" => $arr["section"]));
		}
		return $this->mk_my_orb("rent_step_2", array("id" => $arr["id"], "section" => $arr["section"]));
	}

	/**

		@attrib name=rent_step_2 nologin=1

		@param id required
		@param section required

	**/
	function rent_step_2($arr)
	{
		$this->read_template("rent_step_2.tpl");

		$o = obj($arr["id"]);
		$inf = $o->meta("rent_data");

		// get items in cart
		$f = get_instance(CL_ORDERS_FORM);
		$items = $f->get_cart_items();

		$cats = array();
		foreach($items->arr() as $item)
		{
			$cats[(int)$_SESSION["orders_form"]["payment"]["itypes"][$item->id()]][$item->id()] = $item;
		}

		// display cats
		$item_cat = "";
		foreach($cats as $cat => $items)
		{
			$item_in_cat = "";
			$tot_price = 0;
			foreach($items as $item)
			{
				$this->_insert_item_inf($item);

				$item_in_cat .= $this->parse("ITEM_IN_CAT");

				$tot_price += $item->prop("product_count") * str_replace(",", ".", $item->prop("product_price"));
			}

			$dat = $inf[(int)$_SESSION["orders_form"]["payment"]["itypes"][$item->id()]];
			$lengths = array();
			for($i = $dat["min_mons"]; $i <= $dat["max_mons"]; $i++)
			{
				$lengths[$i] = $i." kuud";
			}

			$prepayment = (($tot_price / 100.0) * (float)$inf[$cat]["prepayment"]);
			$num_payments = max($_SESSION["orders_form"]["payment"]["lengths"][$item->id()], $dat["min_mons"]);

			$cp = $tot_price - $prepayment;

			$percent = $inf[$cat]["interest"];

			$payment = ($cp+($cp*$num_payments*(1+($percent/100))/100))/($num_payments+1);

			$rent_price = $payment * ($num_payments+1) + $prepayment;

			$this->vars(array(
				"catalog_price" => number_format($tot_price, 2),
				"prepayment_price" => number_format($prepayment,2),
				"prepayment" => (int)$inf[$cat]["prepayment"],
				"sel_period" => html::select(array(
					"name" => "rent_lengths[".$item->id()."]",
					"options" => $lengths,
					"selected" => $num_payments
				)),
				"num_payments" => $num_payments+1,
				"rent_payment" => number_format($payment,2),
				"total_rent_price" => number_format($rent_price,2),
			));

			$this->vars(array(
				"cat_name" => $inf[$cat]["type"],
				"ITEM_IN_CAT" => $item_in_cat,
				"HAS_PREPAYMENT" => ($inf[$cat]["prepayment"] > 0 ? $this->parse("HAS_PREPAYMENT") : ""),
				
			));

			$item_cat .= $this->parse("ITEM_CAT");
		}

		$this->vars(array(
			"rent_payment_error" => ($_SESSION["orders_form"]["payment"]["errors"]["too_small"] ? $o->prop("rent_min_amt_payment_text") : "")
		));
		
		$this->vars(array(
			"ITEM_CAT" => $item_cat,
			"reforb" => $this->mk_reforb("submit_rent_step_2", $arr),
			"back" => $this->mk_my_orb("rent_step_1", array("id" => $arr["id"], "section" => $arr["section"])),
			"PAYMENT_ERR" => ($_SESSION["orders_form"]["payment"]["errors"]["too_small"] ? $this->parse("PAYMENT_ERR") : "")
		));		
		unset($_SESSION["orders_form"]["payment"]["errors"]["too_small"]);

		return $this->parse();
	}

	/**

		@attrib name=submit_rent_step_2 nologin=1

	**/
	function submit_rent_step_2($arr)
	{
		foreach(safe_array($arr["rent_lengths"]) as $item => $len)
		{
			$_SESSION["orders_form"]["payment"]["lengths"][$item] = $len;
		}

		$o = obj($arr["id"]);
		$inf = $o->meta("rent_data");

		// get items in cart
		$f = get_instance(CL_ORDERS_FORM);
		$items = $f->get_cart_items();

		$cats = array();
		foreach($items->arr() as $item)
		{
			$cats[(int)$_SESSION["orders_form"]["payment"]["itypes"][$item->id()]][$item->id()] = $item;
		}

		// display cats
		$tot_pm = 0;
		foreach($cats as $cat => $items)
		{
			$tot_price = 0;
			foreach($items as $item)
			{
				$tot_price += $item->prop("product_count") * str_replace(",", ".", $item->prop("product_price"));
			}
			$prepayment = (($tot_price / 100.0) * (float)$inf[$cat]["prepayment"]);
			$num_payments = max($_SESSION["orders_form"]["payment"]["lengths"][$item->id()], $dat["min_mons"]);
			$cp = $tot_price - $prepayment;
			$percent = $inf[$cat]["interest"];
			$payment = ($cp+($cp*$num_payments*(1+($percent/100))/100))/($num_payments+1);

			$tot_pm += $payment;
		}

		if ($tot_pm < $o->prop("rent_min_amt_payment"))
		{
			$_SESSION["orders_form"]["payment"]["errors"]["too_small"] = 1;
			$stay = true;
		}

		if ($arr["save_only"] != "" || $stay)
		{
			return $this->mk_my_orb("rent_step_2", array("id" => $arr["id"], "section" => $arr["section"]));
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "section" => $arr["section"], "group" => "confirmpage"), "orders_form");
	}
}
?>
