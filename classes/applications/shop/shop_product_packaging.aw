<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_product_packaging.aw,v 1.1 2004/05/06 12:19:25 kristo Exp $
// shop_product_packaging.aw - Toote pakend 
/*

@classinfo syslog_type=ST_SHOP_PRODUCT_PACKAGING relationmgr=yes

@default table=objects
@default group=general

*/

class shop_product_packaging extends class_base
{
	function shop_product_packaging()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_product_packaging",
			"clid" => CL_SHOP_PRODUCT_PACKAGING
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
