<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/simple_shop/Attic/simple_shop.aw,v 1.5 2005/05/25 15:36:57 ahti Exp $
// simple_shop.aw - Lihtne tootekataloog 
/*

@classinfo syslog_type=ST_SIMPLE_SHOP relationmgr=yes no_comment=1 no_status=1 r2=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE
@caption Tootekataloog

@property orders_folder type=relpicker reltype=RELTYPE_FOLDER
@caption Tellimuste kataloog

@property user_data type=relpicker reltype=RELTYPE_USER_DATA
@caption Kasutaja andmete vorm

@property controller type=relpicker reltype=RELTYPE_CONTROLLER
@caption Tootelingi kontroller

@property ud_from_logged type=checkbox ch_value=1
@caption Kui kasutaja on sisse loginud, võta isikuandmed objektidest

@groupinfo import caption="Import"
@default group=import

@property folder type=relpicker reltype=RELTYPE_FOLDER
@caption Toodete kaust

@property import_file type=fileupload store=no
@caption Andmed failist

@property replace_prods type=checkbox ch_value=1 store=no
@caption Asenda tooted

@groupinfo orders caption="Tellimused" submit_method=get
@default group=orders

@property order_time type=date_select store=no
@caption Pakkumine alates

@property order_time2 type=date_select store=no
@caption Pakkumine kuni

@property order_orderer type=textbox store=no
@caption Tellija

@property order_cont type=textarea rows=4 cols=20 store=no
@caption Tellimuse sisu

@property search type=submit
@caption Otsi

@property orders_toolbar type=toolbar no_caption=1

@property orders_table type=table no_caption=1

@reltype WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption Tootekataloog

@reltype FOLDER value=2 clid=CL_MENU
@caption Kaust

@reltype USER_DATA value=3 clid=CL_CFGFORM
@caption Kasutaja andmed

@reltype CONTROLLER value=4 clid=CL_FORM_CONTROLLER
@caption Kontroller

*/

class simple_shop extends class_base
{
	function simple_shop()
	{
		$this->init(array(
			"tpldir" => "applications/simple_shop/simple_shop",
			"clid" => CL_SIMPLE_SHOP
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "order_time":
			case "order_time2":
			case "order_orderer":
			case "order_cont":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;
				
			case "import_file":
			case "replace_prods":
				if($arr["new"])
				{
					return PROP_IGNORE;
				}
				$fld = $arr["obj_inst"]->prop("folder");
				if(is_oid($fld) && $this->can("view", $fld))
				{
					return $retval;
				}
				return PROP_IGNORE;
				break;
				
			case "orders_table":
				$this->mk_orders_table($arr);
				break;
				
			case "orders_toolbar":
				$this->mk_orders_toolbar($arr);
				break;
		}
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "import_file":
				$this->_import_file($arr);
				break;
		}
		return $retval;
	}
	
	function mk_orders_toolbar($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->add_button(array(
			"name" => "delete",
			"tooltip" => t("Kustuta tellimused"),
			"confirm" => t("Oled kindel, et sooovid valitud tellimused eemaldada?"),
			"action" => "delete_items",
			"img" => "delete.gif",
		));
	}
	
	function mk_orders_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "time",
			"caption" => t("Aeg"),
			"type" => "time",
			"format" => "H:i d-m-Y",
			"numeric" => 1,
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "orderer",
			"caption" => t("Tellija"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "change",
			"caption" => t(""),
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id",
		));
		$of = $arr["obj_inst"]->prop("orders_folder");
		if(!is_oid($of) or !$this->can("view", $of))
		{
			return;
		}
		$vars = array(
			"parent" => $of,
			"class_id" => CL_SIMPLE_SHOP_ORDER,
		);
		$orderer = &$arr["request"]["order_orderer"];
		$cont = &$arr["request"]["order_cont"];
		if($orderer != "")
		{
			$vars["CL_SIMPLE_SHOP_ORDER.RELTYPE_ORDERER.name"] = "%$orderer%";
		}
		if($cont != "")
		{
			$vars["CL_SIMPLE_SHOP_ORDER.RELTYPE_ORDERITEM.name"] = "%$cont%";
		}
		$v = &$arr["request"]["order_time"];
		$v2 = &$arr["request"]["order_time2"];
		if(strpos(implode($v), "-") === false && !empty($v))
		{
			$x = mktime(0, 0, 0, $v["month"], $v["day"], $v["year"]);
		}
		if(strpos(implode($v2), "-") === false && !empty($v2))
		{
			$x2 = mktime(23, 59, 59, $v2["month"], $v2["day"], $v2["year"]);
		}
		if($x2 && $x)
		{
			$vars["created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, $x, $x2);
		}
		elseif($x)
		{
			$vars["created"] = new obj_predicate_compare(OBJ_COMP_GREATER_OR_EQ, $x);
		}
		elseif($x2)
		{
			$vars["created"] = new obj_predicate_compare(OBJ_COMP_LESS_OR_EQ, $x2);
		}
		$obs = new object_list($vars);
		if($obs->count() <= 0)
		{
			return;
		}
		$num = 100;
		$t->define_pageselector(array(
			"records_per_page" => $num,
			"type" => "text",
			"d_row_cnt" => $obs->count(),
			"no_recount" => 1,
		));
		$objs = new object_list(array(
			"oid" => array_slice($obs->ids(), ($arr["request"]["ft_page"]* $num), $num),
		));
		foreach($objs->arr() as $obj)
		{
			$id = $obj->id();
			if($pers = $obj->get_first_obj_by_reltype("RELTYPE_ORDERER"))
			{
				$orderer = $pers->name();
			}
			$t->define_data(array(
				"id" => $id,
				"time" => $obj->created(),
				"orderer" => $orderer,
				"change" => html::get_change_url($id, array("group" => "order", "return_url" => get_ru()), t("Muuda")),
			));
		}
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		enter_function("simple_shop::show");
		aw_session_set("no_cache", 1);
		$o = obj($arr["id"]);
		$props = array();
		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		$prod = get_instance(CL_SIMPLE_SHOP_PRODUCT);
		$props = array();
		foreach($prod->get_properties_by_name(array("clid" => CL_SIMPLE_SHOP_PRODUCT, "props" => array("name", "prod_code"))) as $key => $val)
		{
			$val["value"] = $_REQUEST[$key];
			$props[$key] = $val;
		}
		$props = $props + array(
			"srch" => array(
				"name" => "srch",
				"type" => "hidden",
				"value" => 1,
			),
			"sbt" => array(
				"name" => "sbt",
				"caption" => t("Otsi"),
				"type" => "submit",
			),
		);
		foreach($props as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1
		));
		$arr = $_REQUEST;
		$arr["obj_inst"] = &$o;
		if($arr["srch"])
		{
			$table = $this->_init_search_table($arr);
			$submit = $this->parse("SUBMIT");
		}
		$prop = array();
		$this->read_template("show.tpl");
		$this->vars(array(
			"form" => $html,
			"section" => aw_global_get("section"),
			"table" => $table,
			"reforb" => $this->mk_reforb("submit_add_cart", array("section" => aw_global_get("section")), "simple_shop"),
			"submit" => $submit,
		));
		exit_function("simple_shop::show");
		return $this->parse();
	}
	
	function load_cart()
	{
		if(aw_global_get("uid") != "")
		{
			$user = obj(aw_global_get("uid_oid"));
			$cart = safe_array($user->meta("simple_shop_cart"));
		}
		else
		{
			$cart = safe_array($_SESSION["simple_shop_cart"]);
		}
		return $cart;
	}
	
	function save_cart($cart)
	{
		if(aw_global_get("uid") != "")
		{
			$user = obj(aw_global_get("uid_oid"));
			$user->set_meta("simple_shop_cart", $cart);
			$user->save();
		}
		else
		{
			$_SESSION["simple_shop_cart"] = $cart;
		}
	}
	
	/** shows the cart to user
	
		@attrib name=my_offers is_public=1 params=name caption="Minu pakkumised" 
		
		@param section optional
		@param id optional
	**/
	function my_offers($arr)
	{
		$cart = $this->load_cart();
		classload("vcl/table");
		$t = new aw_table();
		$t->define_field(array(
			"name" => "prod_code",
			"caption" => t("Tootekood"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimetus"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "unit",
			"caption" => t("Ühik"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "quant",
			"caption" => t("Kogus"),
		));
		foreach($cart as $id => $item)
		{
			if(!is_oid($id) or !$this->can("view", $id))
			{
				unset($cart[$id]);
				continue;
			}
			$obj = obj($id);
			$t->define_data($obj->properties() + array(
				"quant" => html::textbox(array(
					"name" => "quant[$id]",
					"size" => 4,
					"value" => $item,
				)),
				"id" => $id,
			));
		}
		$this->save_cart($cart);
		if($arr["finish_order"] == 1)
		{
		}
		else
		{
			$t->define_chooser(array(
				"name" => "sel",
				"field" => "id",
				"caption" => t("Eemalda korvist"),
			));
		}
		$this->read_template("show.tpl");
		$this->vars(array(
			"section" => aw_global_get("section"),
			"table" => $t->draw(),
			"submit" => $this->parse("FINISH"),
			"reforb" => $this->mk_reforb("submit_add_cart", array("section" => aw_global_get("section")), "simple_shop"),
		));
		return $this->parse();
	}
	
	function _show_order_table()
	{
	}
	
	/** shows the cart to user
	
		@attrib name=submit_add_cart

		@param id optional
		@param sel optional
		@param quant optional
		@param section optional 
		@param finish_order optional
		
	**/
	function submit_add_cart($arr)
	{
		$cart = $this->load_cart();
		foreach(safe_array($arr["quant"]) as $id => $quant)
		{
			$cart[$id] = $quant;
		}
		foreach(safe_array($arr["sel"]) as $id => $sel)
		{
			unset($cart[$id]);
		}
		$this->save_cart($cart);
		return $this->mk_my_orb("my_offers", array("id" => $arr["id"], "section" => $arr["section"], "finish_order" => $arr["finish_order"]), "simple_shop");
	}
	
	function _init_search_table($arr)
	{
		classload("vcl/table");
		$t = new aw_table();
		$t->define_field(array(
			"name" => "prod_code",
			"caption" => t("Tootekood"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimetus"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "unit",
			"caption" => t("Ühik"),
			"sortable" => 1,
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"sortable" => 1,
		));
		if(aw_global_get("uid") != "")
		{
			$t->define_chooser(array(
				"name" => "sel",
				"field" => "id",
				"caption" => t("Lisa korvi"),
			));
		}
		$fld = $arr["obj_inst"]->prop("folder");
		if(!is_oid($fld) or !$this->can("view", $fld))
		{
			return "";
		}
		$params = array(
			"parent" => $fld,
			"class_id" => CL_SIMPLE_SHOP_PRODUCT,
			"sort_by" => "name",
		);
		if($arr["name"] != "")
		{
			$params["name"] = "%".$arr["name"]."%";
		}
		if($arr["prod_code"] != "")
		{
			$params["prod_code"] = "%".$arr["prod_code"]."%";
		}
		$num = 100;
		$objs = new object_list($params);
		$t->define_pageselector(array(
			"records_per_page" => $num,
			"type" => "text",
			"d_row_cnt" => $objs->count(),
			"no_recount" => 1,
		));
		$flds = array();
		$wh = $arr["obj_inst"]->prop("warehouse");
		if(is_oid($wh) && $this->can("view", $wh))
		{
			$whx = obj($wh);
			$conf = $whx->prop("conf");
			if(is_oid($conf) && $this->can("view", $conf))
			{
				$conf = obj($conf);
				$wh_i = get_instance(CL_SHOP_WAREHOUSE);
				list($asd, $fld) = $wh_i->get_packet_folder_list(array("id" => $wh));
				$flds = $fld->ids();
			}
			$sc = $whx->prop("order_center");
			if(is_oid($sc) && $this->can("view", $sc))
			{
				$soc = $sc;
			}
		}
		if($objs->count() > 0)
		{
			$ctr = $arr["obj_inst"]->prop("controller");
			$params["oid"] = array_slice($objs->ids(), ($arr["ft_page"]* $num), $num);
			$objs = new object_list($params);
			$fc = get_instance(CL_FORM_CONTROLLER);
			$isctr = (is_oid($ctr) && $this->can("view", $ctr));
			foreach($objs->arr() as $obj)
			{
				if(!empty($flds) && $isctr)
				{
					$obx = new object_list(array(
						"parent" => $flds,
						"class_id" => CL_SHOP_PRODUCT_PACKAGING,
						"user2" => $obj->prop("prod_code"),
					));
					$obz = $obx->begin();
				}
				$vars = $obj->properties();
				if(is_object($obz))
				{
					$vars["name"] = html::popup(array(
						"caption" => $vars["name"],
						"url" => $fc->eval_controller($ctr, array(
							"pid" => $obz->id(),
							"soc" => $soc,
						)),
					));
				}
				elseif(aw_global_get("uid") != "")
				{
					$vars["id"] = $obj->id();
				}
				$t->define_data($vars);
			}
		}
		return $t->draw();
	}

	function _import_file($arr)
	{
		$file = $_FILES["import_file"]["tmp_name"];
		if(is_uploaded_file($file))
		{
			$fc = $this->get_file(array(
				"file" => $file,
			));
		}
		else
		{
			return PROP_IGNORE;
		}
		$fc = explode("\n", $fc);
		unset($fc[0]);
		$fld = $arr["obj_inst"]->prop("folder");
		if($arr["request"]["replace_prods"] == 1)
		{
			enter_function("simple_shop::replace_prods");
			$prods = new object_list(array(
				"class_id" => CL_SIMPLE_SHOP_PRODUCT,
				"parent" => $fld,
			));
			echo sprintf(t("kustutan %d objekti"), $prods->count())."<br />";
			$prods->delete();
			exit_function("simple_shop::replace_prods");
			echo t("kustutatud")."<br />";
		}
		
		$count = 0;
		obj_set_opt("no_cache", 1);
		echo sprintf(t("impordin %d objekti"), count($fc))."<br />";
		enter_function("simple_shop::prod_import");
		foreach($fc as $row)
		{
			echo sprintf(t("impordin objekti %d... "), $count);
			$row = explode("\t", $row);
			// kill some overkills.. ehehehehe... :(
			if($count > 17000)
			{
				break;
			}
			$count++;
			$obj = obj();
			$obj->set_class_id(CL_SIMPLE_SHOP_PRODUCT);
			$obj->set_parent($fld);
			$obj->set_prop("name", $row[1]);
			$obj->set_prop("prod_code", $row[0]);
			$obj->set_prop("unit", $row[2]);
			$obj->set_prop("price", $row[3]);
			$obj->save();
			echo t("imporditud")."<br />";
		}
		$cache = get_instance("cache");
		$cache->full_flush();
		exit_function("simple_shop::prod_import");
		global $awt;
		echo t("imporditud")."<br />";
		arr($awt->summaries());
	}
}
?>