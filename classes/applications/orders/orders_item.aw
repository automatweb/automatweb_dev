<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/orders/orders_item.aw,v 1.2 2005/04/21 08:48:47 kristo Exp $
// orders_item.aw - Tellimuse rida 
/*

@classinfo syslog_type=ST_ORDERS_ITEM relationmgr=yes
@tableinfo aw_orders_item index=oid master_table=objects master_index=brother_of

@default table=aw_orders_item
@default group=general

@property name type=textbox table=objects
@caption Toote nimetus

@property product_code type=textbox
@caption Kood

@property product_color type=textbox
@caption Värvus

@property product_size type=textbox
@caption Suurus

@property product_count type=textbox
@caption Kogus

@property product_price type=textbox
@caption Hind

@property product_page type=textbox
@caption Lehekülg

@property product_image type=textbox
@caption Pilt

*/

class orders_item extends class_base
{
	function orders_item()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_ORDERS_ITEM
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	/*
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "order_form_id":
				$prop["value;
			break;
		};
		return $retval;
	}*/
	

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

}
?>
