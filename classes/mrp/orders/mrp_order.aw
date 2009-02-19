<?php
/*
@classinfo syslog_type=ST_MRP_ORDER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_mrp_order master_index=brother_of master_table=objects index=aw_oid

@default table=aw_mrp_order
@default group=general

	@property workspace type=relpicker reltype=RELTYPE_WORKSPACE field=aw_workspace
	@caption T&ouml;&ouml;laud

	@property customer type=relpicker reltype=RELTYPE_CUSTOMER field=aw_customer
	@caption Klient

	@property orderer_person type=relpicker reltype=RELTYPE_ORDERER_PERSON field=aw_orderer_person
	@caption Kliendipoolne kontakt

	@property seller_person type=relpicker reltype=RELTYPE_SELLER_PERSON field=aw_seller_person
	@caption Teostajapoolne kontakt

	@property mrp_case type=relpicker reltype=RELTYPE_CASE field=aw_mrp_case
	@caption Arendustoode

	@property mrp_pricelist type=relpicker reltype=RELTYPE_MRP_PRICELIST field=aw_mrp_pricelist
	@caption Hinnakiri

	@property state type=select field=aw_state
	@caption Staatus

@reltype WORKSPACE value=1 clid=CL_MRP_ORDER_CENTER
@caption T&ouml;&ouml;laud

@reltype CUSTOMER value=2 clid=CL_CRM_COMPANY,CL_CRM_PERSON
@caption Klient

@reltype ORDERER_PERSON value=3 clid=CL_CRM_PERSON
@caption Tellija isik

@reltype CASE value=4 clid=CL_MRP_CASE
@caption Arendustoode

@reltype SELLER_PERSON value=5 clid=CL_CRM_PERSON
@caption M&uuml;&uuml;ja isik

@reltype MRP_PRICELIST value=6 clid=CL_MRP_PRICELIST
@caption ERP Hinnakiri

*/

class mrp_order extends class_base
{
	function mrp_order()
	{
		$this->init(array(
			"tpldir" => "mrp/orders/mrp_order",
			"clid" => CL_MRP_ORDER
		));
	}

	/** Returns a list of possible states
		@attrib api=1
	**/
	public static function get_state_list()
	{
		return array(
			0 => t("Uus"),
			1 => t("&Uuml;levaatamisel"),
			2 => t("Kinnitatud"),
			3 => t("Saadetud"),
		);
	}

	function _get_state($arr)
	{	
		$arr["prop"]["options"] = self::get_state_list();
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
			$this->db_query("CREATE TABLE aw_mrp_order(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_workspace":
			case "aw_customer":
			case "aw_orderer_person":
			case "aw_seller_person":
			case "aw_mrp_case":
			case "aw_state":
			case "aw_mrp_pricelist":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}
}

?>
