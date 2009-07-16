<?php
/*
@classinfo syslog_type=ST_SHOP_ORDERER_DATA_SITE_SHOW_BILLS relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@tableinfo aw_shop_orderer_data_site_show master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_orderer_data_site_show
@default group=general

@property template type=select
@caption Template
*/

class shop_orderer_data_site_show_bills extends shop_orderer_data_site_show
{
	function shop_orderer_data_site_show_bills()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_orderer_data_site_show",
			"clid" => CL_SHOP_ORDERER_DATA_SITE_SHOW_BILLS
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

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$tpl = "show.tpl";
		if($arr["tpl"])
		{
			$tpl = $arr["tpl"];
		}
		$this->read_template($tpl);

		$vars = array(
			"name" => $ob->prop("name"),
		);
		$bills = new object_list();
		$rows = "";
		foreach($bills->arr() as $bill)
		{
			$bill_vars = array();
			$bill_vars["id"] = $bill->id();
			$bill_vars["number"] = $bill->prop("nr");
			$bill_vars["currency"] = $bill->prop("currency.name");
			$bill_vars["date"] = date("d.m.Y" , $bill->prop("date"));
			$bill_vars["deadline"] = date("d.m.Y" , $bill->prop("deadline"));
			$bill_vars["sum"] = $bill->get_bill_sum();
			
			$this->vars($bill_vars);
			$rows.=$this->parse("ROW");
		}
		$this->vars($vars);
		$vars["ROW"] = $rows;
		return $this->parse();
	}
}

?>
