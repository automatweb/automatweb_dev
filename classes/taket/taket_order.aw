<?php
// $Header: /home/cvs/automatweb_dev/classes/taket/Attic/taket_order.aw,v 1.3 2005/04/21 08:54:58 kristo Exp $
// taket_order.aw - Taketi tellimus 
/*
@tableinfo taket_orders index=id master_table=objects master_index=oid
@property comments table=taket_orders
@property transport table=taket_orders
@property timestmp table=taket_orders datatype=int
@property price table=taket_orders datatype=float
@property contact table=taket_orders
@property status table=taket_orders 
@property user_id table=taket_orders datatype=int

@classinfo syslog_type= relationmgr=yes

@default table=objects
@default group=general

*/

class taket_order extends class_base
{
	function taket_order()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "taket/taket_order",
			"clid" => CL_TAKET_ORDER
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them


	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		};
		return $retval;
	}


	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
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
}
?>
