<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order.aw,v 1.1 2004/03/24 11:00:18 kristo Exp $
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

@tableinfo aw_shop_orders index=aw_oid master_table=objects master_index=brother_of

@groupinfo items caption="Tellimuse sisu"

@property items group=items field=meta method=serialize type=table no_caption=1

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

*/

class shop_order extends class_base
{
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
		// find warehouse - check if a relation exists to this object from a warehouse
		// if so, then that's that
		// else if a connection exists from order center, then get warehouse from that

		$parent = 0;

		$conn = $o->connections_to(array(
			"type" => 5 // RELTYPE_ORDER from warehouse
		));
		if (count($conn) > 0)
		{
			$c = reset($conn);
			$warehouse = $c->from();
			
			$conf = obj($warehouse->prop("conf"));

			$parent = $conf->prop("export_fld");
		}

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
	
		return $sum;
	}
}
?>
