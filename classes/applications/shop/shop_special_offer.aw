<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_special_offer.aw,v 1.9 2004/12/01 14:04:12 ahti Exp $
// shop_special_offer.aw - Poe eripakkumine 
/*

@classinfo syslog_type=ST_SHOP_SPECIAL_OFFER relationmgr=yes no_caption=1 no_status=1

@default table=objects
@default group=general

@property oc type=relpicker reltype=RELTYPE_ORDER_CENTER field=meta method=serialize 
@caption Tellimiskeskkond

@groupinfo vis caption="N&auml;itamine"
@default group=vis

@property template type=relpicker reltype=RELTYPE_ITEM_LAYOUT field=meta method=serialize automatic=1
@caption Kujundusmall

@property use_controller type=relpicker reltype=RELTYPE_CONTROLLER field=meta method=serialize 
@caption Kasuta toodete n&auml;itamiseks kontrollerit

@groupinfo prods caption="Tooted"
@default group=prods

@property prods type=table store=no no_caption=1

@reltype PRODUCT value=1 clid=CL_SHOP_PRODUCT,CL_SHOP_PACKET
@caption toode

@reltype ITEM_LAYOUT value=2 clid=CL_SHOP_PRODUCT_LAYOUT
@caption toote kujundusmall

@reltype CONTROLLER value=3 clid=CL_FORM_CONTROLLER
@caption kontroller

@reltype ORDER_CENTER value=4 clid=CL_SHOP_ORDER_CENTER
@caption tellimiskeskkond
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

		$t->define_field(array(
			"name" => "price_comment",
			"caption" => "Hind kommentaariga",
			"align" => "center"
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
				"price_comment" => html::textbox(array(
					"name" => "prodat[$id][price_comment]",
					"value" => $prodat[$id]["price_comment"]
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

		error::raise_if(!$ob->prop("template"), array(
			"id" => "ERR_NO_LAYOUT",
			"msg" => "shop_special_offer::show(): no layout set for product display in special offer!"
		));

		$layout = obj($ob->prop("template"));
		$prodat = $ob->meta("prodat");

		$html = "";
		$plut = array();
		foreach($ob->connections_from(array("type" => "RELTYPE_PRODUCT")) as $c)
		{
			$plut[$c->prop("to")] = $prodat[$c->prop("to")]["ord"];
		}

		asort($plut);

		if ($ob->prop("use_controller"))
		{
			$param = array(
				"layout" => $layout,
				"prod" => $prod,
				"prodat" => $prodat,
				"plut" => $plut,
			);

			$param["special_offer"] = $ob;
			$fg = get_instance(CL_FORM_CONTROLLER);
			$html = $fg->eval_controller($ob->prop("use_controller"), $param);
		}
		else
		{
			foreach($plut as $pid => $tmp)
			{
				$prod = obj($pid);
				$prod_i = $prod->instance();

				$param = array(
					"layout" => $layout,
					"prod" => $prod,
					"prodat" => $prodat,
					"plut" => $plut,
					"price" => $prodat[$pid]["price"]
				);

				$html .= $prod_i->do_draw_product($param);
			}
		}

		return $html;
	}
}
?>
