<?php
/*
@classinfo syslog_type=ST_MATERIAL_EXPENSE_CONDITION relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=robert
@tableinfo aw_material_expense_condition master_index=brother_of master_table=objects index=aw_oid

@default table=aw_material_expense_condition
@default group=general

@property product type=relpicker reltype=RELTYPE_PRODUCT
@caption Materjal

@property resource type=relpicker reltype=RELTYPE_RESOURCE
@caption Ressurss

@reltype PRODUCT value=1 clid=CL_SHOP_PRODUCT
@caption Materjal

@reltype RESOURCE value=2 clid=CL_MRP_RESOURCE
@caption Ressurss

*/

class material_expense_condition extends class_base
{
	function material_expense_condition()
	{
		$this->init(array(
			"tpldir" => "mrp/material_expense_condition",
			"clid" => CL_MATERIAL_EXPENSE_CONDITION
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
			$this->db_query("CREATE TABLE aw_material_expense_condition(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "product":
			case "resource":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}
}

?>
