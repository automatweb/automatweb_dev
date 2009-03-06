<?php
/*
@classinfo syslog_type=ST_MRP_ORDER_COVER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_mrp_order_cover master_index=brother_of master_table=objects index=aw_oid

@default table=aw_mrp_order_cover
@default group=general

	@property cover_amt type=textbox size=10 field=aw_cover_amt
	@caption Katte summa

	@property cover_tot_price_pct type=textbox size=10 field=aw_cover_tot_price_pct
	@caption Katte protsent hinnalt

	@property cover_amt_piece type=textbox size=10 field=aw_cover_amt_piece
	@caption Kate t&uuml;kilt
*/

class mrp_order_cover extends class_base
{
	function mrp_order_cover()
	{
		$this->init(array(
			"tpldir" => "mrp/orders/mrp_order_cover",
			"clid" => CL_MRP_ORDER_COVER
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
			$this->db_query("CREATE TABLE aw_mrp_order_cover(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_cover_amt":
			case "aw_cover_tot_price_pct":
			case "aw_cover_amt_piece":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "double"
				));
				return true;
		}
	}
}

?>
