<?php
/*
@classinfo syslog_type=ST_MRP_ORDER_PRINT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_mrp_order_print master_index=brother_of master_table=objects index=aw_oid
@extends mrp/orders/mrp_order

@default table=aw_mrp_order_print
@default group=general

	@property amount type=textbox size=5 field=aw_amount
	@caption Kogus

	@property format type=textbox  field=aw_format
	@caption Formaat

	@property tiraazh type=textbox size=5 field=aw_tiraazh
	@caption Tiraazh

	@property deadline type=date_select field=aw_deadline
	@caption T&auml;htaeg

	@property materials type=textbox  field=aw_materials
	@caption Materjalid

*/

class mrp_order_print extends mrp_order
{
	function mrp_order_print()
	{
		$this->init(array(
			"tpldir" => "mrp/orders/mrp_order_print",
			"clid" => CL_MRP_ORDER_PRINT
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
			$this->db_query("CREATE TABLE aw_mrp_order_print(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_amount":
			case "aw_tiraazh":
			case "aw_deadline":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;

			case "aw_format":
			case "aw_materials":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(255)"
				));
				return true;
		}
	}
}

?>
