<?php
/*
@classinfo syslog_type=ST_PRODUCTS_SHOW relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=smeedia
@tableinfo aw_products_show master_index=brother_of master_table=objects index=aw_oid

@default table=aw_products_show
@default group=general


@property categories type=relpicker multiple=1 store=connect reltype=RELTYPE_CATEGORY 
@caption Tootekategooriad
@comment Tootekategooriad millesse toode peaks kuuluma, et teda kuvataks

@property template type=select
@caption Toodete n&auml;itamise template

@reltype CATEGORY value=1 clid=CL_SHOP_PRODUCT_CATEGORY
@caption Tootekategooria



*/

class products_show extends class_base
{
	function products_show()
	{
		$this->init(array(
			"tpldir" => "applications/shop/products_show",
			"clid" => CL_PRODUCTS_SHOW
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
			$this->db_query("CREATE TABLE aw_products_show(aw_oid int primary key)");
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
