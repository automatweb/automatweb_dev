<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_warehouse_inventory.aw,v 1.1 2006/03/17 15:55:49 dragut Exp $
// shop_warehouse_inventory.aw - Inventuur 
/*

@classinfo syslog_type=ST_SHOP_WAREHOUSE_INVENTORY relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@tableinfo shop_warehouse_inventory index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

	@property date type=date_select table=shop_warehouse_inventory
	@caption Kuup&auml;ev

	@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE table=shop_warehouse_inventory
	@caption Ladu

@groupinfo products caption="Tooted"
@default group=products

	@layout products_frame type=hbox width=20%:80%

		@layout product_search_params_frame type=vbox parent=products_frame
			
			@property product_code type=textbox store=no parent=product_search_params_frame captionside=top
			@caption Kood

			@property product_barcode type=textbox store=no parent=product_search_params_frame captionside=top
			@caption Ribakood

			@property product_name type=textbox store=no parent=product_search_params_frame captionside=top
			@caption Nimetus

			@property product_group type=textbox store=no parent=product_search_params_frame captionside=top
			@caption Tootegrupp

		@layout product_result_frame type=vbox parent=products_frame

			@property toolbar type=toolbar parent=product_result_frame no_caption=1
			@caption T&ouml;&ouml;riistariba

			@property product_result type=table parent=product_result_frame
			@caption Tooted

@reltype WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption Ladu
	
*/

class shop_warehouse_inventory extends class_base
{
	function shop_warehouse_inventory()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/shop/shop_warehouse_inventory",
			"clid" => CL_SHOP_WAREHOUSE_INVENTORY
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

	function _get_toolbar($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->add_button(array(
			"name" => "delete",
			"img" => "delete.gif",
			"tooltip" => t("Kustuta"),
			"action" => "_delete_objects",
			"confirm" => t("Oled kindel?"),
		));

	}

	function _get_product_result($arr)
	{
		$t = &$arr['prop']['vcl_inst'];
		$t->set_sortable(false);
/*

Tekstivälja saab sisestada koguse, mis jäetakse meelde suhtena selle inventuuri ja selle toote vahel

*/
		$t->define_field(array(
			"name" => "code",
			"caption" => t("Kood"),
		));
		$t->define_field(array(
			"name" => "barcode",
			"caption" => t("Ribakood"),
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimetus"),
		));
		$t->define_field(array(
			"name" => "warehouse_amount",
			"caption" => t("Kogus laos"),
		));
		$t->define_field(array(
			"name" => "real_amount",
			"caption" => t("Tegelik kogus"),
		));
		$t->define_field(array(
			"name" => "select",
			"caption" => t("Vali"),
		));

		$products = array(1,2,3,4,5);

		foreach ($products as $product)
		{
			$t->define_data(array(
				"code" => "",
				"barcode" => "",
				"name" => "",
				"warehouse_amount" => "",
				"real_amount" => html::textbox(array(
					"name" => "real_amount[$product_id]",
					"size" => 5,
				)),
				"select" => html::checkbox(array(
					"name" => "selected_ids[$product_id]",
				)),
			));
		}


	}


	/**
		@attrib name=_delete_objects
	**/
	function _delete_objects($arr)
	{

		foreach ($arr['selected_ids'] as $id)
		{
			if (is_oid($id) && $this->can("delete", $id))
			{
				$object = new object($id);
				$object->delete();
			}
		}

		return $arr['post_ru'];
	}

	/**
		DB UPGRADE
	**/
	function do_db_upgrade($table, $field, $query, $error)
	{
		// this should be the way to detect, if table exist:
		if (empty($field))
		{
			$this->db_query('CREATE TABLE '.$table.' (oid INT PRIMARY KEY NOT NULL)');
			return true;
		}

		switch ($field)
		{
			case 'date':
			case 'warehouse':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
				return true;
		}

		return false;
	}

}
?>
