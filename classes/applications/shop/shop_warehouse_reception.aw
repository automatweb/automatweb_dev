<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_warehouse_reception.aw,v 1.3 2005/03/23 10:31:34 kristo Exp $
// shop_warehouse_reception.aw - Lao sissetulek 
/*

@classinfo syslog_type=ST_SHOP_WAREHOUSE_RECEPTION relationmgr=yes no_status=1

@default table=objects
@default group=general

@property confirm type=checkbox ch_value=1 field=meta method=serialize
@caption Kinnita

@groupinfo income caption="Sissetuleku sisu"

@property income group=income field=meta method=serialize type=table no_caption=1

@reltype PRODUCT value=1 clid=CL_SHOP_PRODUCT,CL_SHOP_PACKET
@caption sissetulnud toode

*/

class shop_warehouse_reception extends class_base
{
	function shop_warehouse_reception()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_warehouse_reception",
			"clid" => CL_SHOP_WAREHOUSE_RECEPTION
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "income":
				$this->do_inc_table($arr);
				break;

			case "confirm":
				if ($arr["obj_inst"]->prop("confirm") == 1)
				{
					// can't unconfirm after confirmation
					return PROP_IGNORE;
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
			case "income":
				$this->save_inc_table($arr);
				break;

			case "confirm":
				if ($arr["obj_inst"]->prop("confirm") != 1 && $data["value"] == 1)
				{
					// confirm was clicked, do the actual add
					$this->do_confirm($arr["obj_inst"]);
				}
				break;
		}
		return $retval;
	}	

	function _init_inc_table(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi")
		));

		$t->define_field(array(
			"name" => "count",
			"caption" => t("Mitu tuli"),
			"align" => "center"
		));
	}

	function do_inc_table(&$arr)
	{
		$pd = $arr["obj_inst"]->meta("inc_content");

		$this->_init_inc_table($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_PRODUCT)) as $c)
		{
			if ($arr["obj_inst"]->prop("confirm") == 1)
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

	function save_inc_table(&$arr)
	{
		$arr["obj_inst"]->set_meta("inc_content", $arr["request"]["pd"]);
	}

	function do_confirm($o)
	{
		if ($o->prop("confirm") == 1)
		{
			// make sure we don't re-confirm receptions
			return;
		}

		$pd = $o->meta("inc_content");
		foreach($o->connections_from(array("type" => 1/*RELTYPE_PRODUCT*/)) as $c)
		{
			$to = $c->to();
			$to->set_prop("item_count", $to->prop("item_count") + $pd[$to->id()]);
			$to->save();
		}
		$o->set_prop("confirm", 1);
		$o->save();
	}
}
?>
