<?php
/*
@classinfo syslog_type=ST_SHOP_PRODUCT_PURVEYANCE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=robert
@tableinfo aw_shop_product_purveyance master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_product_purveyance
@default group=general

@property product type=relpicker reltype=RELTYPE_PRODUCT
@caption Toode

@property company type=relpicker reltype=RELTYPE_COMPANY
@caption Tarnija

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE
@caption Ladu

@property weekday type=select
@caption Tarnep&auml;ev

@property days type=textbox
@caption Tarneaeg p&auml;evades

@property date1 type=date_select
@caption Kuup&auml;ev 1

@property date2 type=date_select
@caption Kuup&auml;ev 2

@reltype PRODUCT value=1 clid=CL_SHOP_PRODUCT
@caption Toode

@reltype COMPANY value=2 clid=CL_CRM_COMPANY
@caption Tarnija

@reltype WAREHOUSE value=3 clid=CL_SHOP_WAREHOUSE
@caption Ladu
*/

class shop_product_purveyance extends class_base
{
	function shop_product_purveyance()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_product_purveyance",
			"clid" => CL_SHOP_PRODUCT_PURVEYANCE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "weekday":
				$prop["options"] = array(
					0 => t("--vali--"),
					1 => t("Esmasp&auml;ev"),
					2 => t("Teisip&auml;ev"),
					3 => t("Kolmap&auml;ev"),
					4 => t("Neljap&auml;ev"),
					5 => t("Reede"),
					6 => t("Laup&auml;ev"),
					7 => t("P&uuml;hap&auml;ev"),
				);
				break;
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
			$this->db_query("CREATE TABLE aw_shop_product_purveyance(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "product":
			case "company":
			case "warehouse":
			case "days":
			case "weekday":
			case "date1":
			case "date2":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}
}

?>
