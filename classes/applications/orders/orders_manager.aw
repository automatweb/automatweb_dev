<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/orders/orders_manager.aw,v 1.1 2004/11/02 14:18:08 sven Exp $
// orders_manager.aw - Tellimuste haldus 
/*

@classinfo syslog_type=ST_ORDERS_MANAGER relationmgr=yes
@default table=objects
@default group=ordermnager


@property orders_table type=table  no_caption=1

@groupinfo ordermnager caption="Tellimused" submit=no

*/

class orders_manager extends class_base
{
	function orders_manager()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_ORDERS_MANAGER
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
			case "orders_table":
				$this->do_orderstable($arr);
			break;
		};
		return $retval;
	}
	
	function do_orderstable($arr)
	{
		$table = &$arr["prop"]["vcl_inst"];
		
		$table->define_field(array(
			"name" => "orderer",
			"caption" => "Tellija"
		));
		
		$table->define_field(array(
			"name" => "date",
			"caption" => "Kuupäev",
			"sortable" => 1,
			"type" => "time",
			"format" => "H:i d-M",
			"width" => 80,
			"align" => "center",
		));
		
		
		$table->define_field(array(
			"name" => "view",
			"caption" => "Vaata tellimust",
			"width" => 80,
		));
		
		$ol = new object_list(array(
			"class_id" => CL_ORDERS_ORDER,
			"order_completed" => 1,
		));
		
		foreach ($ol->arr() as $order)
		{
			unset($person_name);
			if($person = $order->get_first_obj_by_reltype("RELTYPE_PERSON"))
			{
				$person_name = $person->prop("firstname")." ".$person->prop("lastname");
			}
			$table->define_data(array(
				"orderer" => $person_name,
				"date" => $order->created(),
				"view" => html::href(array(
					"caption" => "Vaata tellimust",
					"url" => $this->mk_my_orb("change", array("id" => $order->id()), CL_ORDERS_ORDER)
				)),
			));
		}
	}

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
