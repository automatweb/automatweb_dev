<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_special_offer.aw,v 1.1 2004/05/19 10:42:27 kristo Exp $
// shop_special_offer.aw - Poe eripakkumine 
/*

@classinfo syslog_type=ST_SHOP_SPECIAL_OFFER relationmgr=yes no_caption=1 no_status=1

@default table=objects
@default group=general

@groupinfo vis caption="N&auml;itamine"
@default group=vis

@property template type=relpicker reltype=RELTYPE_ITEM_LAYOUT field=meta method=serialize
@caption Kujundusmall

@groupinfo prods caption="Tooted"
@default group=prods

@property prods type=table store=no no_caption=1

@reltype PRODUCT value=1 clid=CL_SHOP_PRODUCT,CL_SHOP_PACKET
@caption toode

@reltype ITEM_LAYOUT value=2 clid=CL_SHOP_PRODUCT_LAYOUT
@caption toote kujundusmall
*/

class shop_special_offer extends class_base
{
	function shop_special_offer()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_special_offer",
			"clid" => CL_SHOP_SPECIAL_OFFER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "prods":
				$this->do_prods_tbl($arr);
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
			case "prods":
				$arr["obj_inst"]->set_meta("prodat", $arr["request"]["prodat"]);
				break;
		}
		return $retval;
	}	

	function _init_prods_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "ord",
			"caption" => "J&auml;rjekord",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi"
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => "Hind"
		));
	}

	function do_prods_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_prods_tbl($t);
		$t->set_numeric_field("hidden_ord");
		$t->set_default_sortby("hidden_ord");

		$prodat = $arr["obj_inst"]->meta("prodat");

		foreach($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_PRODUCT")) as $c)
		{
			$id = $c->prop("to");
			$t->define_data(array(
				"ord" => html::textbox(array(
					"size" => 5,
					"name" => "prodat[$id][ord]",
					"value" => $prodat[$id]["ord"]
				)),
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $id), $c->prop("to.class_id")),
					"caption" => $c->prop("to.name")
				)),
				"price" => html::textbox(array(
					"size" => 5,
					"name" => "prodat[$id][price]",
					"value" => $prodat[$id]["price"]
				)),
				"hidden_ord" => $prodat[$id]["ord"]
			));
		}

		$t->sort_by();
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !shows the special offer
	function show($arr)
	{
		$ob = new object($arr["id"]);
		
		$layout = obj($ob->prop("template"));
		$prodat = $ob->meta("prodat");

		$html = "";
		$plut = array();
		foreach($ob->connections_from(array("type" => "RELTYPE_PRODUCT")) as $c)
		{
			$plut[$c->prop("to")] = $prodat[$c->prop("to")]["ord"];
		}

		asort($plut);

		foreach($plut as $pid => $tmp)
		{
			$prod = obj($pid);
			$prod_i = $prod->instance();
			
			$html .= $prod_i->do_draw_product(array(
				"layout" => $layout,
				"prod" => $prod,
				"price" => $prodat[$prod->id()]["price"]
			));
		}

		return $html;
	}
}
?>
