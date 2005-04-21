<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/simple_shop/Attic/simple_shop_product.aw,v 1.1 2005/04/21 09:40:49 ahti Exp $
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
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/simple_shop/simple_shop_product",
			"clid" => CL_SIMPLE_SHOP_PRODUCT
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//

		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	//-- methods --//
}
?>
