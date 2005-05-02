<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/simple_shop/Attic/simple_shop_product.aw,v 1.2 2005/05/02 11:36:26 ahti Exp $
// simple_shop_product.aw - Lihtne toode 
/*

@classinfo syslog_type=ST_SIMPLE_SHOP_PRODUCT no_comment=1 no_status=1

@tableinfo aw_simple_products index=aw_id master_table=objects master_index=brother_of

@default table=objects
@default group=general

@property name type=textbox field=name
@caption Nimetus

@default table=aw_simple_products

@property prod_code type=textbox
@caption Tootekood

@property unit type=textbox
@caption Ühik

@property price type=textbox datatype=int
@caption Hind

*/

class simple_shop_product extends class_base
{
	function simple_shop_product()
	{
		$this->init(array(
			"tpldir" => "applications/simple_shop/simple_shop_product",
			"clid" => CL_SIMPLE_SHOP_PRODUCT
		));
	}
	
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
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
	
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
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
}
?>
