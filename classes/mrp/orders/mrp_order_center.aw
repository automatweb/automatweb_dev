<?php
/*
@classinfo syslog_type=ST_MRP_ORDER_CENTER relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_mrp_order_center master_index=brother_of master_table=objects index=aw_oid

@default table=aw_mrp_order_center
@default group=general

	@property owner_co type=relpicker reltype=RELTYPE_OWNER field=aw_owner
	@caption Omanik

	@property mrp_workspace type=relpicker reltype=RELTYPE_MRP_WORKSPACE field=aw_mrp_workspace
	@caption Ressursihalduse t&ouml;&ouml;laud

@default group=order_order

	@property order_tb type=toolbar no_caption=1 store=no

	@layout order_split type=hbox width=20%:80%

		@layout order_left type=hbox parent=order_split

			@layout order_left_top type=vbox parent=order_left closeable=1 area_caption=Filter

				@property order_filter_tree type=treeview store=no no_caption=1 parent=order_left_top


		@property order_list type=table store=no no_caption=1 parent=order_split

@default group=customer

	@property customer_tb type=toolbar no_caption=1 store=no

	@layout customer_split type=hbox width=20%:80%

		@layout customer_left type=hbox parent=customer_split

			@layout customer_left_top type=vbox parent=customer_left closeable=1 area_caption=Filter

				@property customer_filter_tree type=treeview store=no no_caption=1 parent=customer_left_top


		@property customer_list type=table store=no no_caption=1 parent=customer_split

@default group=pricelists

	@property pr_tb type=toolbar store=no no_caption=1

	@property pr_table type=table store=no no_caption=1

@groupinfo order caption="Tellimused"
	@groupinfo order_order caption="Tellimused" parent=order submit=no

@groupinfo customer caption="Kliendid" submit=no
@groupinfo pricelists caption="Hinnakirjad" submit=no

@reltype OWNER value=1 clid=CL_CRM_COMPANY
@caption Omanik

@reltype MRP_WORKSPACE value=2 clid=CL_MRP_WORKSPACE
@caption ERP T&ouml;&ouml;laud

@reltype MRP_PRICELIST value=4 clid=CL_MRP_PRICELIST
@caption Hinnakiri

*/

class mrp_order_center extends class_base
{
	function mrp_order_center()
	{
		$this->init(array(
			"tpldir" => "mrp/orders/mrp_order_center",
			"clid" => CL_MRP_ORDER_CENTER
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
			$this->db_query("CREATE TABLE aw_mrp_order_center(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_owner":
			case "aw_mrp_workspace":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	function _get_order_filter_tree($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->add_item(0, array(
			"id" => "stat",	
			"name" => t("Staatused"),
			"url" => aw_url_change_var(array("stat" => null, "custmgr" => null))
		));
		foreach(mrp_order::get_state_list() as $idx => $stat)
		{
			$t->add_item("stat", array(
				"id" => "stat_".$idx,	
				"name" => $stat,
				"url" => aw_url_change_var(array("stat" => $idx, "custmgr" => null))
			));
		}

		$t->add_item(0, array(
			"id" => "mgr",	
			"name" => t("Kliendihaldur"),
			"url" => aw_url_change_var(array("stat" => null, "custmgr" => null))
		));
		foreach($arr["obj_inst"]->get_all_order_managers() as $id => $name)
		{
			$t->add_item("mgr", array(
				"id" => "custmgr_".$id,	
				"name" => $name,
				"url" => aw_url_change_var(array("stat" => null, "custmgr" => $id))
			));
		}

		if (!empty($arr["request"]["stat"]))
		{
			$t->set_selected_item("stat_".$arr["request"]["stat"]);
		}
		else
		if (!empty($arr["request"]["custmgr"]))
		{
			$t->set_selected_item("custmgr_".$arr["request"]["custmgr"]);
		}
	}

	private function _init_order_list_table($t)
	{
		$t->define_field(array(
			"name" => "customer",
			"caption" => t("Klient"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "orderer_person",
			"caption" => t("Tellija"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "state",
			"caption" => t("Staatus"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "when",
			"caption" => t("Millal"),
			"align" => "center",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
			"sortable" => 1
		));
		$t->define_chooser(array(
			"name" => "sel",
			"field" => "id"
		));
	}

	function _get_order_list($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_order_list_table($t);

		$states = mrp_order::get_state_list();
		foreach($this->_get_orders_in_list($arr["request"], $t, $arr["obj_inst"]) as $order)
		{
			$t->define_data(array(
				"customer" => html::obj_change_url($order->customer),
				"orderer_person" => html::obj_change_url($order->orderer_person),
				"state" => $states[$order->state],
				"when" => $order->created,
				"name" => html::obj_change_url($order->name),
				"id" => $order->id
			));
		}
	}

	private function _get_orders_in_list($r, $t, $o)
	{
		$filt = array(
			"class_id" => CL_MRP_ORDER_PRINT,
			"lang_id" => array(),
			"site_id" => array(),
			"workspace" => $o->id()
		);
		if (!empty($r["stat"]))
		{
			$filt["state"] = $r["stat"];
			$sl = mrp_order::get_state_list();
			$t->set_caption(sprintf(t("Tellimused staatusega %s"), $sl[$filt["state"]]));
		}
		else
		if (!empty($r["custmgr"]))
		{
			$filt["CL_MRP_ORDER_PRINT.customer.client_manager"] = $r["custmgr"];
			$t->set_caption(sprintf(t("Tellimused kliendihalduriga %s"), obj($r["custmgr"])->name));
		}
		else
		{
			$t->set_caption(t("Tellimused"));
		}
		$ol = new object_list($filt);
		return $ol->arr();
	}

	function _get_order_tb($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_new_button(array(CL_MRP_ORDER_PRINT), $arr["obj_inst"]->id(), null, array("ws" => $arr["obj_inst"]->id()));
		$tb->add_delete_button();
	}

	function _get_customer_tb($arr)
	{
		$tmp = array(
			"prop" => $arr["prop"],
			"obj_inst" => $arr["obj_inst"]->owner_co(),
			"request" => $arr["request"]
		);
		get_instance("applications/crm/crm_company_cust_impl")->_get_my_customers_toolbar($tmp);
	}

	function _get_customer_filter_tree($arr)
	{
		$tmp = array(
			"prop" => $arr["prop"],
			"obj_inst" => $arr["obj_inst"]->owner_co(),
			"request" => $arr["request"]
		);
		get_instance("applications/crm/crm_company_cust_impl")->_get_customer_listing_tree($tmp);
	}

	function _get_customer_list($arr)
	{
		$tmp = array(
			"prop" => $arr["prop"],
			"obj_inst" => $arr["obj_inst"]->owner_co(),
			"request" => $arr["request"]
		);
		get_instance("applications/crm/crm_company_cust_impl")->_get_my_customers_table($tmp);
	}

	function _get_pr_tb($arr)
	{
		$arr["prop"]["vcl_inst"]->add_new_button(array(CL_MRP_PRICELIST), $arr["obj_inst"]->id(), 4 /* MRP_PRICELIST */);
		$arr["prop"]["vcl_inst"]->add_delete_button();
	}

	function _get_pr_table($arr)
	{
		$arr["prop"]["vcl_inst"]->table_from_ol(
			new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_MRP_PRICELIST"))),
			array("name", "created", "act_from", "act_to"),
			CL_MRP_PRICELIST
		);
	}
}

?>