<?php
/*
@classinfo syslog_type=ST_MRP_PRICELIST relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_mrp_pricelist master_index=brother_of master_table=objects index=aw_oid

@default table=aw_mrp_pricelist
@default group=general

	@property act_from type=date_select field=aw_act_from
	@caption Kehtib alates

	@property act_to type=date_select field=aw_act_to
	@caption Kehtib kuni

@default group=res_prices

	@property res_prices type=table no_caption=1 store=no

@groupinfo res_prices caption="Ressursside hinnad" 
*/

class mrp_pricelist extends class_base
{
	function mrp_pricelist()
	{
		$this->init(array(
			"tpldir" => "mrp/orders/mrp_pricelist",
			"clid" => CL_MRP_PRICELIST
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_mrp_pricelist(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_act_from":
			case "aw_act_to":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	private function _init_res_prices_table($t)
	{
		$t->define_field(array(
			"name" => "cnts",
			"caption" => t("Kogused"),
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "cnt_from",
			"caption" => t("Alates"),
			"align" => "center",
			"parent" => "cnts"
		));
		$t->define_field(array(
			"name" => "cnt_to",
			"caption" => t("Kuni"),
			"align" => "center",
			"parent" => "cnts"
		));

		$t->define_field(array(
			"name" => "prices",
			"caption" => t("Hinnad"),
			"align" => "center",
		));

		$t->define_field(array(
			"name" => "config",
			"caption" => t("Seadistamine"),
			"align" => "center",
			"parent" => "prices",
		));
		$t->define_field(array(
			"name" => "item_price",
			"caption" => t("Tk hind"),
			"align" => "center",
			"parent" => "prices"
		));

		$t->set_rgroupby(array("res" => "res"));
	}

	function _get_res_prices($arr)
	{	
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_res_prices_table($t);

		foreach($arr["obj_inst"]->get_resource_list() as $res)
		{
			foreach($arr["obj_inst"]->get_ranges_for_resource($res) as $range)
			{
				$t->define_data(array(
					"item_price" => html::textbox(array("name" => "t[".$res->id."][".$range->id."][item_price]", "size" => 8, "value" => $range->item_price)),
					"config" => html::textbox(array("name" => "t[".$res->id."][".$range->id."][config_price]", "size" => 8, "value" => $range->config_price)),
					"cnt_to" => html::textbox(array("name" => "t[".$res->id."][".$range->id."][cnt_to]", "size" => 5, "value" => $range->cnt_to)),
					"cnt_from" => html::textbox(array("name" => "t[".$res->id."][".$range->id."][cnt_from]", "size" => 5, "value" => $range->cnt_from)),
					"res" => html::strong($res->name()),
					"sfld" => $res->id()."0"
				));
			}
			$t->define_data(array(
				"item_price" => html::textbox(array("name" => "t[".$res->id."][-1][item_price]", "size" => 8)),
				"config" => html::textbox(array("name" => "t[".$res->id."][-1][config_price]", "size" => 8)),
				"cnt_to" => html::textbox(array("name" => "t[".$res->id."][-1][cnt_to]", "size" => 5)),
				"cnt_from" => html::textbox(array("name" => "t[".$res->id."][-1][cnt_from]", "size" => 5)),
				"res" => html::strong($res->name()),
				"sfld" => $res->id()."1"
			));
		}

		$t->set_caption(t("Ressursside hinnad"));
		$t->set_default_sortby("sfld");
	}

	function _set_res_prices($arr)
	{
		foreach($arr["obj_inst"]->get_resource_list() as $res)
		{
			$arr["obj_inst"]->set_ranges_for_resource($res, $arr["request"]["t"][$res->id()]);
		}
	}
}

?>
