<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_packet.aw,v 1.3 2004/04/13 12:36:34 kristo Exp $
// shop_packet.aw - Pakett 
/*

@classinfo syslog_type=ST_SHOP_PACKET relationmgr=yes no_status=1 

@default table=objects
@default group=general

@property item_count type=hidden table=aw_shop_packets field=aw_count
@caption Mitu laos

@property separate_items type=checkbox ch_value=1 table=aw_shop_packets field=separate_items
@caption Kas tooted on eraldi

@property price type=textbox table=aw_shop_packets field=aw_price
@caption Hind

@groupinfo packet caption="Paketi sisu"

@property packet group=packet field=meta method=serialize type=table no_caption=1

@tableinfo aw_shop_packets index=aw_oid master_table=objects master_index=brother_of
@reltype PRODUCT value=1 clid=CL_SHOP_PRODUCT
@caption paketi toode
*/

class shop_packet extends class_base
{
	function shop_packet()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_packet",
			"clid" => CL_SHOP_PACKET
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "packet":
				$this->do_packet_table($arr);
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
			case "packet":
				$this->save_packet_table($arr);
				break;
		}
		return $retval;
	}	

	function _init_packet_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi"
		));

		$t->define_field(array(
			"name" => "count",
			"caption" => "Mitu paketis",
			"align" => "center"
		));
	}

	function do_packet_table(&$arr)
	{
		$pd = $arr["obj_inst"]->meta("packet_content");

		$this->_init_packet_table($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_PRODUCT)) as $c)
		{
			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"count" => html::textbox(array(
					"name" => "pd[".$c->prop("to")."]",
					"value" => $pd[$c->prop("to")],
					"size" => 5
				))
			));
		}
	}

	function save_packet_table(&$arr)
	{
		$arr["obj_inst"]->set_meta("packet_content", $arr["request"]["pd"]);
	}

	function get_price($o)
	{
		return $o->prop("price");
	}

	/** returns the html for the product

		@comment

			uses the $layout object to draw the product $prod
			from the layout reads the template and inserts correct vars
			optionally you can give the $quantity parameter
	**/
	function do_draw_product($arr)
	{
		extract($arr);

		$l_inst = $layout->instance();
		$l_inst->read_template($layout->prop("template"));
		$l_inst->vars(array(
			"name" => $prod->name(),
			"price" => $prod->prop("price"),
			"id" => $prod->id(),
			"quantity" => (int)($arr["quantity"])
		));

		return $l_inst->parse();
	}
}
?>
