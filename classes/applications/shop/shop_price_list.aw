<?php
/*
@classinfo syslog_type=ST_SHOP_PRICE_LIST relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=robert

@tableinfo aw_shop_price_list master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_price_list
@default group=general

@property valid_from type=date_select field=valid_from
@caption Kehtib alates

@property valid_to type=date_select field=valid_to
@caption Kehtib kuni

@property groups type=relpicker multiple=1 reltype=RELTYPE_GROUP field=groups
@caption Kehtib gruppidele

@property orgs type=relpicker multiple=1 reltype=RELTYPE_ORG field=orgs
@caption Kehtib organisatsioonidele

@property persons type=relpicker multiple=1 reltype=RELTYPE_PERSON field=persons
@caption Kehtib isikutele

@property discount type=textbox field=discount
@caption Allahindlus

@property base_price type=checkbox field=base_price
@caption Baashindade alusel

@property prod_category type=relpicker multiple=1 reltype=RELTYPE_CATEGORY field=categories
@caption Kaubagrupp

@reltype GROUP value=1 clid=CL_GROUP
@caption Grupp

@reltype ORG value=2 clid=CL_CRM_COMPANY
@caption Organisatsioon

@reltype PERSON value=3 clid=CL_CRM_PERSON
@caption Isik

@reltype CATEGORY value=4 clid=CL_SHOP_PRODUCT_CATEGORY
@caption Kaubagrupp
*/

class shop_price_list extends class_base
{
	function shop_price_list()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_price_list",
			"clid" => CL_SHOP_PRICE_LIST
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
			$this->db_query("CREATE TABLE aw_shop_price_list(aw_oid int primary key)");
			return true;
		}
		$ret = false;
		switch($f)
		{
			case "valid_from":
			case "valid_to":
			case "base_price":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				$ret = true;
				break;
			case "groups":
			case "orgs":
			case "persons":
			case "categories":
			case "discount":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(255)"
				));
				$ret = true;
				break;
		}

		switch($f)
		{
			case "groups":
			case "orgs":
			case "persons":
			case "categories":
				$this->db_query("ALTER TABLE aw_shop_price_list ADD INDEX(".$f.")");
		}
		return $ret;
	}
}

?>
