<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/orders/orders_manager.aw,v 1.7 2005/06/02 12:19:56 kristo Exp $
// orders_manager.aw - Tellimuste haldus 
/*

@classinfo syslog_type=ST_ORDERS_MANAGER relationmgr=yes
@default table=objects

@property export_folder type=textbox field=meta method=serialize group=general
@caption Ekspordi kataloog


@groupinfo ordermnager caption="Tellimused" submit=no
@default group=ordermnager

@property orders_toolbar type=toolbar no_caption=1
@caption Tellimuste toolbar

@property orders_table type=table no_caption=1
@caption Tellimuste tabel

@reltype CFGMANAGER value=1 clid=CL_CFGMANAGER
@caption Seadete haldur
*/

class orders_manager extends class_base
{
	function orders_manager()
	{
		$this->init(array(
			"clid" => CL_ORDERS_MANAGER
		));
	}
	
	function callback_on_load($arr)
	{
		if(is_oid($arr["request"]["id"]) && $this->can("view", $arr["request"]["id"]))
		{
			$obj = obj($arr["request"]["id"]);
			if($cfgmanager = $obj->get_first_conn_by_reltype("RELTYPE_CFGMANAGER"))
			{
				$this->cfgmanager = $cfgmanager->prop("to");
			}
		}
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
				$this->do_orders_table($arr);
				break;
			
			case "orders_toolbar":
				$this->do_orders_toolbar($arr);
				break;
		};
		return $retval;
	}
	
	function do_orders_toolbar($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		$tb->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta tellimusi"),
			"action" => "delete_orders",
			"confirm" => t("Oled kindel, et soovid valitud tellimused kustutada?"),
		));

		$tb->add_button(array(
			"name" => "confirm",
			"img" => "save.gif",
			"tooltip" => t("Kinnita tellimused"),
			"action" => "confirm_orders",
			"confirm" => t("Oled kindel, et soovid valitud tellimused kinnitada?"),
		));
	}
	
	function do_orders_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "orderer",
			"caption" => t("Tellija")
		));
		$t->define_field(array(
			"name" => "date",
			"caption" => t("Kuupäev"),
			"sortable" => 1,
			"type" => "time",
			"format" => "H:i d-m-y",
			"width" => 80,
			"align" => "center",
		));
		$t->define_field(array(
			"name" => "view",
			"caption" => t("Vaata tellimust"),
			"width" => 80,
		));
		$t->define_field(array(
			"name" => "cf",
			"caption" => t("Kinnitatud?"),
			"width" => 80,
			"align" => "center"
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "oid",
		));
		
		$ol = new object_list(array(
			"class_id" => CL_ORDERS_ORDER,
			"order_completed" => 1,
			"sort_by" => "objects.created DESC",
		));
		
		foreach ($ol->arr() as $order)
		{
			unset($person_name);
			if($person = $order->get_first_obj_by_reltype("RELTYPE_PERSON"))
			{
				$person_name = $person->prop("firstname")." ".$person->prop("lastname");
				if($company = $person->get_first_conn_by_reltype("RELTYPE_WORK"))
				{
					$person_name .= " / ".$company->prop("to.name");
				}
			}
			$t->define_data(array(
				"oid" => $order->id(),
				"orderer" => $person_name,
				"date" => $order->created(),
				"view" => html::href(array(
					"caption" => t("Vaata tellimust"),
					"url" => $this->mk_my_orb("change", array("id" => $order->id(), "group" => "orderitems", "return_url" => get_ru()), CL_ORDERS_ORDER)
				)),
				"cf" => ($order->prop("order_confirmed") ? t("Jah") : "")
			));
		}
		$t->set_sortable(false);
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
	
	/**

		@attrib name=delete_orders

		@param id required type=int acl=view
		@param group optional
		@param sel required
	**/
	function delete_orders($arr)
	{
		foreach(safe_array($arr["sel"]) as $sel)
		{
			if(is_oid($sel) && $this->can("delete", $sel))
			{
				$obj = obj($sel);
				if($obj->class_id() == CL_ORDERS_ORDER)
				{
					foreach($obj->connections_from(array("type" => "RELTYPE_ORDER")) as $it)
					{
						$item = $it->to();
						$item->delete();
					}
				}
				$obj->delete();
			}
		}
		return html::get_change_url($arr["id"], array("group" => $arr["group"]));
	}
	

	/** exports orders after the last batch to textfile

		@attrib name=export_to_file nologin=1

		@param id required type=int acl=view

	**/
	function export_to_file($arr)
	{
		$o = obj($arr["id"]);

		// get time of last export
		$last_export = (int)$this->get_cval("orders_manager::last_export_time");

		echo "fetch orders since ".date("d.m.Y H:i", $last_export)." <br>\n";
		flush();

		// get orders created since then
		$ol = new object_list(array(
			"class_id" => CL_ORDERS_ORDER,
			"created" => new obj_predicate_compare(OBJ_COMP_GREATER, $last_export),
			"lang_id" => aw_global_get("lang_id"),
			"site_id" => array(),
			"order_completed" => 1,
			"sort_by" => "objects.created"
		));

		echo "got ".$ol->count()." orders <br>\n";
		flush();

		// get todays counter
		$counter = (int)$this->get_cval("orders_manager::today_counter")+1;

		if ($counter > 4)
		{
			$counter = 1;
		}

		// make file name
		$fn = $o->prop("export_folder")."/".date("Y")."-".date("m")."-".date("d")."-".$counter.".csv";

		echo "export to file $fn <br>";

		$ex_props = array(
			"item" => array(
				"name" => t("Toote nimi"), 
				"product_code" => t("Kood"), 
				"product_color" => t("V&auml;rvus"),
				"product_size" => t("Suurus"),
				"product_count" => t("Kogus"),
				"product_price" => t("Hind"),
				"product_page" => t("Lehek&uuml;lg"),
				"product_image" => t("Pilt")
			),
			"person" => array(
				"firstname" => t("Eesnimi"),
				"lastname" => t("Perekonnanimi"),
				"comment" => t("Aadress"),
				"birthday" => t("S&uuml;nnip&auml;ev"),
				"email" => t("E-mail"),
				"phone" => t("Telefon"),
			),
			"order" => array(
				"udef_textbox1" => t("Kliendi number"),
				"udef_textbox2" => t("Postiindeks"),
				"udef_textbox3" => t("Linn"),
				"udef_textbox4" => t("Telefon t&ouml;&ouml;l"),
				"udef_textbox5" => t("Mobiil"),
				"udef_textbox6" => t("Kliendo t&uuml;&uuml;p"),
			)
		);

		$lines = array();
		$sep = ",";
		$first = true;
		$header = array(t("OID"), t("Millal"));

		// foreach orders
		foreach($ol->arr() as $order)
		{
			$person = $order->get_first_obj_by_reltype("RELTYPE_PERSON");
			if (!$person)
			{
				continue;
			}

			// foreach order items
			foreach($order->connections_from(array("type" => "RELTYPE_ORDER")) as $c)
			{
				$line = array(
					$order->id(),
					date("d.m.Y H:i", $order->created())
				);

				$item = $c->to();

				// write order line to file
				foreach($ex_props as $obj => $dat)
				{
					foreach($dat as $prop => $head)
					{
						if ($first)
						{
							$header[] = $head;
						}
						$line[] = str_replace(",", " ",$$obj->prop_str($prop));
					}
				}

				if ($first)
				{
					$lines[] = join($sep, $header);
				}
				$lines[] = join($sep, $line);
				$first = false;
			}
		}
	
		$this->put_file(array(
			"file" => $fn,
			"content" => join("\n", $lines)
		));

		// write last export date
		$this->set_cval("orders_manager::last_export_time", time());
		$this->set_cval("orders_manager::today_counter", $counter);
		

		die(t("all done!"));
	}

	/**

		@attrib name=confirm_orders

	**/
	function confirm_orders($arr)
	{
		if (is_array($arr["sel"]) && count($arr["sel"]))
		{
			$ol = new object_list(array(
				"oid" => $arr["sel"]
			));
			foreach($ol->arr() as $o)
			{
				$o->set_prop("order_confirmed", 1);
				$o->save();
			}
		}
		return $this->mk_my_orb("change", array("id" => $arr["id"], "group" => "ordermnager"));
	}
}
?>
