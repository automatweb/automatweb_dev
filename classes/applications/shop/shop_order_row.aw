<?php
// shop_order_row.aw - Tellimuse rida
/*

@classinfo syslog_type=ST_SHOP_ORDER_ROW relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo aw_shop_order_rows index=aw_oid master_index=brother_of master_table=objects

@default table=objects
@default group=general

@property prod_name type=textbox table=aw_shop_order_rows field=aw_prod_name
@caption Toote nimi

@property prod type=relpicker reltype=RELTYPE_PRODUCT table=aw_shop_order_rows field=aw_product
@caption Toode

@property price type=textbox table=aw_shop_order_rows field=aw_prod_price
@caption Toote hind

@property items type=textbox size=3 table=aw_shop_order_rows field=aw_items
@caption Kogus

@reltype PRODUCT value=1 clid=CL_SHOP_PRODUCT
@caption Toode
*/

class shop_order_row extends class_base
{
	function shop_order_row()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_order_row",
			"clid" => CL_SHOP_ORDER_ROW
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

	function do_db_upgrade($table, $field, $q, $err)
	{
		if ($table === "aw_shop_order_rows")
		{
			if($field=="")
			{
				$this->db_query("CREATE TABLE aw_shop_order_rows (`aw_oid` int primary key)");
				return true;
			}
			switch($field)
			{
				case "aw_prod_name":
					$this->db_add_col($table, array(
						"name" => $field,
						"type" => "VARCHAR(255)"
					));
					return true;
					break;
				case "aw_prod_price":
				case "aw_product":
				case "aw_items":
					$this->db_add_col($table, array(
						"name" => $field,
						"type" => "int"
					));
					return true;
					break;
			}
		}
	}
}

?>
