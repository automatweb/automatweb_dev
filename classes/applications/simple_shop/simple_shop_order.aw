<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/simple_shop/Attic/simple_shop_order.aw,v 1.3 2005/05/25 15:36:57 ahti Exp $
// simple_shop_order.aw - Lihtne tellimus 
/*

@classinfo syslog_type=ST_SIMPLE_SHOP_ORDER relationmgr=yes no_comment=1 no_status=1 r2=yes

@default table=objects
@default group=general

@groupinfo orderer caption="Tellija andmed" submit=no
@default group=orderer

@property orderer type=callback callback=callback_orderer no_caption=1
@caption Tellija andmed

@groupinfo order caption="Tellimus" submit=no
@default group=order

@property order_tb type=toolbar no_caption=1
@caption Tellimuste toolbar

@property order type=table no_caption=1
@caption Tellimuste tabel

@property order_sum type=text
@caption Kogusumma

@reltype ORDERITEM value=1 clid=CL_SIMPLE_SHOP_PRODUCT
@caption Toode

@reltype ORDERER value=2 clid=CL_CRM_PERSON
@caption Tellija

@reltype ORDERER_INFO value=3 clid=CL_REGISTER_DATA

*/

class simple_shop_order extends class_base
{
	function simple_shop_order()
	{
		$this->init(array(
			"tpldir" => "applications/simple_shop/simple_shop_order",
			"clid" => CL_SIMPLE_SHOP_ORDER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "order_tb":
				$this->mk_order_tb($arr);
				break;
				
			case "order":
				$this->mk_order_table($arr);
				break;
				
			case "order_sum":
				$prop["value"] = $this->sum;
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "order":
				$arr["obj_inst"]->set_meta("order_info", $arr["request"]["order"]);
				$arr["obj_inst"]->save();
				break;
		}
		return $retval;
	}
	
	function callback_mod_tab($arr)
	{
		if($arr["id"] == "orderer" && !$this->form)
		{
			return false;
		}
	}
	
	function callback_order($arr)
	{
		
		if(!($this->form = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_ORDERER")) or !($this->form = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_ORDERER_INFO")))
		{
			return false;
		}
	}
	
	function mk_order_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "delete",
			"tooltip" => t("Eemalda tooted"),
			"confirm" => t("Oled kindel, et sooovid tooted tellimusest eemaldada?"),
			"action" => "delete_items",
			"img" => "delete.gif",
		));
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => t("Salvesta"),
			"action" => "",
			"img" => "save.gif",
		));
	}
	
	function mk_order_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$prod_i = get_instance(CL_SIMPLE_SHOP_PRODUCT);
		$props = $prod_i->load_defaults();
		foreach($props as $prop)
		{
			$t->define_field(array(
				"name" => $prop["name"],
				"caption" => $prop["caption"],
			));
		}
		$t->define_field(array(
			"name" => "quant",
			"caption" => t("Kogus"),
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
		));
		$order = $arr["obj_inst"]->meta("order_info");
		$sum = 0;
		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_ORDERITEM")) as $prod)
		{
			$prod = $prod->to();
			$id = $prod->id();
			$sum += $prod->prop("price") * $order[$id];
			$t->define_data($prod->properties() + array(
				"oid" => $id,
				"quant" => html::textbox(array(
					"name" => "order[$id]",
					"size" => 6,
					"value" => $order[$id],
				)),
			));
		}
		$this->sum = $sum;
	}
	
	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
	
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}
	
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}
?>
