<?php
/*
@classinfo syslog_type=ST_MATERIAL_EXPENSE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=robert
@tableinfo aw_material_expense master_index=brother_of master_table=objects index=aw_oid

@default table=aw_material_expense
@default group=general

@property product type=relpicker reltype=RELTYPE_PRODUCT
@caption Materjal

@property job type=relpicker reltype=RELTYPE_JOB
@caption Tegevus

@property amount type=textbox datatype=int
@caption Kogus

@property unit type=relpicker reltype=RELTYPE_UNIT
@caption &Uuml;hik

@reltype PRODUCT value=1 clid=CL_SHOP_PRODUCT
@caption Materjal

@reltype JOB value=2 clid=CL_MRP_JOB
@caption Tegevus

@reltype UNIT value=3 clid=CL_UNIT
@caption &Uuml;hik
*/

class material_expense extends class_base
{
	function material_expense()
	{
		$this->init(array(
			"tpldir" => "mrp/material_expense",
			"clid" => CL_MATERIAL_EXPENSE
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
			$this->db_query("CREATE TABLE aw_material_expense(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "product":
			case "job":
			case "unit":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
			case "amount":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(20)"
				));
				return true;
		}
	}
}

?>