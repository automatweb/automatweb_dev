<?php
/*
@classinfo syslog_type=ST_SHOP_ORDERER_DATA_SITE_SHOW_BILLS relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=smeedia
@tableinfo aw_shop_orderer_data_site_show_bills master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_orderer_data_site_show_bills
@default group=general

*/

class shop_orderer_data_site_show_bills extends class_base
{
	function shop_orderer_data_site_show_bills()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_orderer_data_site_show_bills",
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
			$this->db_query("CREATE TABLE aw_shop_orderer_data_site_show_bills(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => ""
				));
				return true;
		}
	}
}

?>
