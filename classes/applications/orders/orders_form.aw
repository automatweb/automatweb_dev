<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/orders/orders_form.aw,v 1.3 2004/11/11 15:53:02 sven Exp $
// orders_form.aw - Tellimuse vorm 
/*

@classinfo syslog_type=ST_ORDERS_FORM relationmgr=yes

@default table=objects
@default group=general

@property orderform type=relpicker field=meta reltype=RELTYPE_ORDERFORM method=serialize group=config
@caption Tellimuse seadetevorm

@property itemform type=relpicker field=meta reltype=RELTYPE_ITEMFORM method=serialize group=config
@caption Tellimuse rea seadetevorm

@property ordemail type=relpicker field=meta reltype=RELTYPE_MAIL method=serialize group=config
@caption Mail tellijale


@property thankudoc type=relpicker reltype=RELTYPE_THANKU field=meta method=serialize group=config
@caption Dokument kuhu suunata peale esitamist

@property postal_fee type=textbox field=meta method=serialize
@caption Postikulu

@property orders_post_to type=textbox field=meta method=serialize
@caption Mail kuhu tellimus saata

@property orders_to_mail type=checkbox field=meta method=serialize ch_value=1
@caption Saada e-mail tellijale

@property orders_post_from type=textbox field=meta method=serialize
@caption Kliendile saatja(e-mail)

@property ordering type=callback group=ordering no_caption=1 callback=do_order_form

@reltype ORDERFORM value=1 clid=CL_CFGFORM
@caption Tellimuse seadetevorm

@reltype ITEMFORM value=2 clid=CL_CFGFORM
@caption Tellimuse rea seadetevorm

@reltype THANKU value=3 clid=CL_DOCUMENT
@caption Dokument

@reltype MAIL value=5 clid=CL_MESSAGE
@caption Mail

@reltype ADDORDER value=4 clid=CL_ORDERS_ITEM
@caption Tellimuse lisa

@groupinfo ordering caption=Tellimine submit=no
@groupinfo config caption=Seaded 
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
		if($this->can("delete", $arr["id"]) && $this->can("view", $arr["id"]))
		{
			$obj = &obj($arr["id"]);
			$obj->delete();
			$obj->save();
			$obj = &obj($arr["id"]);
		}
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
			if($conns = $arr["obj_inst"]->connections_from("RELTYPE_ADDORDER"))
			{
				foreach ($conns as $conn)
				{
					$order->connect(array(
						"to" => $conn->prop("to"),
						"reltype" => 4
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
		if(strstr($_SERVER['REQUEST_URI'],"/automatweb"))
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
		
		if(!$_SESSION["order_cart_id"] || !$_SESSION["order_form_id"])
		{
			$order = new object();
			$order->set_class_id(CL_ORDERS_ORDER);
			$order->set_parent($arr["oid"]);
			$order->save();

			$_SESSION["order_cart_id"] = $order->id();
			$_SESSION["order_form_id"] = $arr["alias"]["to"];
			
			$form_obj = &obj($_SESSION["order_form_id"]);
		
			if($conns = $form_obj->connections_from(array("type" => 4)))
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
			$order = &obj($_SESSION["order_cart_id"]);
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
		elseif ($_GET["group"] == "confirmpage")
		{
			$this->vars(array(
				"show_confirm" => $this->get_confirm_persondata($order),
				"shop_table" => $this->get_cart_table(),
			));
		}
		else
		{
			$this->vars(array(
				"forwardurl" => aw_url_change_var(array("group" => "persondata")),
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
	

	function get_confirm_persondata($order)
	{
		$this->read_template("orders_confirm_persondata.tpl");

		$person = current($order->connections_from(array(
			"type" => 2 
		)));
		$person = $person->to();
		
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
			"person_contact" => $person->prop("comment"),
			"birthday" => get_lc_date($person->prop("birthday")),
		));
		$retval = $this->parse();
		$this->read_template("orders_form.tpl");
		return 	$retval;
	}
	
	function get_persondata_form($arr)	
	{
		$this->read_template("orders_persondata.tpl");
		
		$this->vars(array(
			"id" => $_SESSION["order_cart_id"],
		));
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
		
		$this->vars(array(
			"year_select" => $year_select,
			"customer_type1" => html::radiobutton(array(
				"name" => "udef_textbox6",
				"value" => "p&uuml;siklient",
				"checked" => $udef_check1
			)),
			"udef_checkbox1" => html::checkbox(array(
				"name" => "udef_checkbox1",
				"value" => 1,
				"checked" => $_SESSION['person_form_values']['udef_checkbox1'],
			)),
			"customer_type2" => html::radiobutton(array(
				"name" => "udef_textbox6",
				"value" => "esmakordselt",
				"checked" => $udef_check2
			))
		));
		
		unset($_SESSION["cb_values"]);
		$retval = $this->parse();
		$this->read_template("orders_form.tpl");
		return $retval;
	}
	
	function get_additems_form($arr)
	{
		$this->read_template("orders_order_item.tpl");
		$errors = $_SESSION["order_form_errors"]["items"];
		
		$add_change_caption = "Lisa tellimusse";
		
		if($_GET["editid"])
		{
			$obj = &obj($_GET["editid"]);
			$_SESSION["order_eoid"] = $_GET["editid"];
			
			$values["name"] = $obj->prop("name");
			$values["product_size"] = $obj->prop("product_size");
			$values["product_color"] = $obj->prop("product_color");
			$values["product_code"] = $obj->prop("product_code");
			$values["product_count"] = $obj->prop("product_count");
			$values["product_price"] = $obj->prop("product_price");
			$values["product_page"] = $obj->prop("product_page");
			$values["product_image"] = $obj->prop("product_image");
			
			$add_change_caption = "Salvesta muudatused";
		}
		else 
		{
			$values = $_SESSION["order_form_values"];
		}
		
		$this->vars(array(
			"add_change_caption" => $add_change_caption,
			"id" => $_SESSION["order_cart_id"],
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
		
		unset($_SESSION["order_form_errors"]["items"]);
		unset($_SESSION["order_form_values"]);
		
		$this->submerge = 1;
		$retval = $this->parse();
		$this->read_template("orders_form.tpl");
		$this->submerge = 1;
		
		return $retval;
	}
	
	function get_cart_table()
	{	
		$order = &obj($_SESSION["order_cart_id"]);
		$form = &obj($_SESSION["order_form_id"]);
		$conns = $order->connections_from(array(
			"type" => "RELTYPE_ORDER"
		));
		
		if(!$conns)
		{
			return;
		}
		
		$ol = new object_list($conns);
		$this->submerge = 1;
		foreach ($ol->arr() as $item)
		{
			$this->vars(array(
				"name" => $item->name(),
				"editurl" => aw_url_change_var(array(
					"editid" => $item->id(), "group" => "")),
				"delete_href" => html::href(array(
					"url" => $this->mk_my_orb("delete_from_order",array(
						"id" => $item->id(),
					), CL_ORDERS_FORM),
					"caption" => "Kustuta",
				)),
				"product_code" => $item->prop("product_code"),
				"product_color" => $item->prop("product_color"),
				"product_size" => $item->prop("product_size"),
				"product_count" => $item->prop("product_count"),
				"product_price" => $item->prop("product_price"),
				"product_image" => $item->prop("product_image"),
				"product_page" => $item->prop("product_page"),
				"product_sum" => $item->prop("product_count") * $item->prop("product_price"),
			));
			$retval.= $this->parse("shop_cart_table");
			$totalsum = $totalsum + $item->prop("product_count") * $item->prop("product_price");
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
}
?>
