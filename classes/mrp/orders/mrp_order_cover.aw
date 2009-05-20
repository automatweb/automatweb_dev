<?php
/*
@classinfo syslog_type=ST_MRP_ORDER_COVER relationmgr=yes no_comment=1 prop_cb=1 maintainer=kristo
@tableinfo aw_mrp_order_cover master_index=brother_of master_table=objects index=aw_oid

@default table=aw_mrp_order_cover
@default group=general

	@property cover_type type=select field=aw_cover_type
	@caption Katte t&uuml;&uuml;p

	@property cover_amt type=textbox size=10 field=aw_cover_amt
	@caption Katte summa v&otilde;i protsent

@default group=applies

		@property belongs_group type=relpicker reltype=RELTYPE_APPLIES_GROUP field=aw_group
		@caption Kuulub gruppi

	@layout applies_all_lay type=vbox closeable=1 area_caption=Kehtib&nbsp;k&otilde;ikidele

		@property applies_all type=checkbox ch_value=1 default=1 field=aw_applies_all 
		@caption Kehtib kogusummale

	@layout applies_resources_lay type=vbox closeable=1 area_caption=Kehtib&nbsp;ressurssidele

		@property applies_resources_tb type=toolbar store=no no_caption=1 parent=applies_resources_lay
		@caption Kehtib resurssidele toolbar

		@property applies_resources type=table store=no no_caption=1 parent=applies_resources_lay
		@caption Kehtib resurssidele

	@layout applies_materials_lay type=vbox closeable=1 area_caption=Kehtib&nbsp;materjalidele

		@property applies_materials_tb type=toolbar store=no no_caption=1 parent=applies_materials_lay
		@caption Kehtib materjalidele toolbar

		@property applies_materials type=table store=no no_caption=1 parent=applies_materials_lay
		@caption Kehtib materjalidele

@default group=stats

	@layout stats_split type=hbox width=20%:80%

		@layout left_bit type=vbox parent=stats_split

			@layout stats_period_lay type=vbox parent=left_bit closeable=1 area_caption=Perioodi&nbsp;filter
	
				@property stats_period type=treeview store=no no_caption=1 parent=stats_period_lay

			@layout stats_customer_lay type=vbox parent=left_bit closeable=1 area_caption=Kliendi&nbsp;filter

				@property stats_customer type=treeview store=no no_caption=1 parent=stats_customer_lay

			@layout stats_status_lay type=vbox parent=left_bit closeable=1 area_caption=Tellimuse&nbsp;staatuse&nbsp;filter

				@property stats_status type=treeview store=no no_caption=1 parent=stats_status_lay

		@property stats_table type=table store=no no_caption=1 parent=stats_split


@groupinfo applies caption="Kehtimine"
@groupinfo stats caption="Aruanded" submit=no

@reltype APPLIES_RESOURCE value=1 clid=CL_MRP_RESOURCE
@caption Kehtib ressursile

@reltype APPLIES_PROD value=2 clid=CL_SHOP_PRODUCT
@caption Kehtib tootele

@reltype APPLIES_GROUP value=3 clid=CL_MRP_ORDER_COVER_GROUP
@caption Asub grupis

*/

class mrp_order_cover extends class_base
{
	function mrp_order_cover()
	{
		$this->init(array(
			"tpldir" => "mrp/orders/mrp_order_cover",
			"clid" => CL_MRP_ORDER_COVER
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
		$arr["search_res"] = "0";
		$arr["search_mat"] = "0";
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
			$this->db_query("CREATE TABLE aw_mrp_order_cover(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_cover_amt":
			case "aw_cover_tot_price_pct":
			case "aw_cover_amt_piece":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "double"
				));
				return true;

			case "aw_applies_all":
			case "aw_cover_type":
			case "aw_group":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	function _get_applies_resources_tb($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_search_button(array(
			"pn" => "search_res",
			"clid" => array(CL_MRP_RESOURCE),
		));
		$tb->add_delete_rels_button();
	}

	function _get_applies_materials_tb($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_search_button(array(
			"pn" => "search_mat",
			"clid" => array(CL_SHOP_PRODUCT),
		));
		$tb->add_delete_rels_button();
	}

	function _get_applies_materials($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->table_from_ol(
			new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_APPLIES_PROD"))),
			array("name", "created", "createdby_person"),
			CL_SHOP_PRODUCT
		);
	}

	function _get_applies_resources($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->table_from_ol(
			new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_APPLIES_RESOURCE"))),
			array("name", "created", "createdby_person"),
			CL_MRP_RESOURCE
		);
	}

	function callback_post_save($arr)
	{
		$ps = get_instance("vcl/popup_search");
		$ps->do_create_rels($arr["obj_inst"], $arr["request"]["search_res"], "RELTYPE_APPLIES_RESOURCE");
		$ps->do_create_rels($arr["obj_inst"], $arr["request"]["search_mat"], "RELTYPE_APPLIES_PROD");
	}

	function _get_cover_type($arr)
	{
		$arr["prop"]["options"] = $arr["obj_inst"]->get_cover_types();
	}

	function _get_stats_period($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->add_item(0, array(
			"id" => "cur_mon",
			"name" => t("Jooksev kuu"),
			"url" => aw_url_change_var("s_period", "cur_mon")
		));
		$t->add_item(0, array(
			"id" => "prev_mon",
			"name" => t("Eelmine kuu"),
			"url" => aw_url_change_var("s_period", "prev_mon")
		));
		$t->add_item(0, array(
			"id" => "total",
			"name" => t("K&otilde;ik perioodid"),
			"url" => aw_url_change_var("s_period", "total")
		));

		$act = "cur_mon";
		if (isset($arr["request"]["s_period"]))
		{
			$act = $arr["request"]["s_period"];
		}
		$t->set_selected_item($act);
	}

	function _get_stats_customer($arr)
	{
		$t = $arr["prop"]["vcl_inst"];

		// cust groups
		$oc = $this->_get_oc($arr["obj_inst"]);
		if (!$oc)
		{
			return PROP_IGNORE;
		}

		$co = $oc->owner_co();
		$this->_req_customer_tree($t, $co, 0);

		$t->add_item(0, array(
			"id" => "total",
			"name" => t("K&otilde;ik kliendid"),
			"url" => aw_url_change_var("s_customer", null)
		));

		$act = "total";
		if (isset($arr["request"]["s_customer"]))
		{
			$act = $arr["request"]["s_customer"];
		}
		$t->set_selected_item($act);
	}

	private function _req_customer_tree($t, $co, $pt)
	{
		foreach($co->connections_from(array("type" => "RELTYPE_CATEGORY")) as $c)
		{
			$t->add_item(0, array(
				"id" => $c->prop("to"),
				"name" => $c->prop("to.name"),
				"url" => aw_url_change_var("s_customer", $c->prop("to"))
			));
			$this->_req_customer_tree($t, $c->to(), $c->prop("to"));
		}
	}

	function _get_oc($o)
	{
		foreach($o->path() as $item)
		{
			if ($item->class_id == CL_MRP_ORDER_CENTER)
			{
				return $item;
			}
		}
		return null;
	}

	function _get_stats_status($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		foreach(get_instance("mrp/orders/mrp_order")->get_state_list() as $id => $name)
		{
			$t->add_item(0, array(
				"id" => "state_".$id,
				"name" => $name,
				"url" => aw_url_change_var("s_state", $id)
			));
		}

		$t->add_item(0, array(
			"id" => "state_all",
			"name" => t("K&otilde;ik"),
			"url" => aw_url_change_var("s_state", null)
		));

		$act = "state_all";
		if (isset($arr["request"]["s_state"]))
		{
			$act = "state_".$arr["request"]["s_state"];
		}
		$t->set_selected_item($act);
	}

	private function _init_stats_table($t)
	{
		$t->define_field(array(
			"name" => "customer",
			"caption" => t("Klient"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "state",
			"caption" => t("Staatus"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "order",
			"caption" => t("Tellimus"),
			"align" => "left",
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "created",
			"caption" => t("Loodud"),
			"align" => "left",
			"sortable" => 1,
			"numeric" => 1,
			"type" => "time",
			"format" => "d.m.Y H:i"
		));
		$t->define_field(array(
			"name" => "price",
			"caption" => t("Hind"),
			"align" => "right"
		));
		$t->define_field(array(
			"name" => "mat_price",
			"caption" => t("Materjalide"),
			"align" => "right",
			"parent" => "price",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "res_price",
			"caption" => t("Resursside"),
			"align" => "right",
			"parent" => "price",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "cover_price",
			"caption" => t("Katete"),
			"align" => "right",
			"parent" => "price",
			"numeric" => 1,
			"sortable" => 1
		));
		$t->define_field(array(
			"name" => "tot_price",
			"caption" => t("Kokku"),
			"align" => "right",
			"parent" => "price",
			"numeric" => 1,
			"sortable" => 1
		));
	}

	function _get_stats_table($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_stats_table($t);

		$filt = array(
			"class_id" => CL_MRP_ORDER_PRINT,
			"lang_id" => array(),
			"site_id" => array(),
			new obj_predicate_sort(array("customer" => "asc", "created" => "desc"))
		);
		$this->_request2filt($filt, $arr["request"]);
		
		$ol = new object_list($filt);

		$sums = array("mat_price" => 0, "res_price" => 0, "tot_price" => 0);
		$states = get_instance("mrp/orders/mrp_order")->get_state_list();
		$pc = "";
		foreach($ol->arr() as $o)
		{
			if ($pc != $o->customer)
			{
				// add customer totals row
				$t->define_data(array(
					"customer" => html::strong(t("Kokku kliendile")),
					"order" => "",
					"state" => "",
					"created" => "",
					"mat_price" => html::strong(number_format($tot_mat_price, 2)),
					"res_price" => html::strong(number_format($tot_res_price, 2)),
					"cover_price" => html::strong(number_format($tot_cover_price, 2)),
					"tot_price" => html::strong(number_format($tot_tot_price, 2))
				));
				$tot_mat_price = 0;
				$tot_tot_price = 0;
				$tot_res_price = 0;
				$tot_cover_price = 0;
			}
			$pc = $o->customer;
			$mat_price = $o->get_materials_price();
			$res_price = $o->get_resource_price();
			$tot_price = $o->get_total_price();
			$cover_price = $o->get_cover_price();

			$tot_mat_price += $mat_price;
			$tot_tot_price += $tot_price;
			$tot_res_price += $res_price;
			$tot_cover_price += $cover_price;

			$t->define_data(array(
				"order" => html::obj_change_url($o),
				"customer" => html::obj_change_url($o->customer()),
				"state" => $states[$o->state],
				"created" => $o->created(),
				"mat_price" => number_format($mat_price, 2),
				"res_price" => number_format($res_price, 2),
				"cover_price" => number_format($cover_price, 2),
				"tot_price" => number_format($tot_price, 2)
			));

			$sums["mat_price"] += $mat_price;
			$sums["res_price"] += $res_price;
			$sums["tot_price"] += $tot_price;
			$sums["cover_price"] += $cover_price;
		}

		$t->define_data(array(
			"customer" => html::strong(t("Kokku kliendile")),
			"order" => "",
			"state" => "",
			"created" => "",
			"mat_price" => html::strong(number_format($tot_mat_price, 2)),
			"res_price" => html::strong(number_format($tot_res_price, 2)),
			"cover_price" => html::strong(number_format($tot_cover_price, 2)),
			"tot_price" => html::strong(number_format($tot_tot_price, 2))
		));

//		$t->set_default_sortby("created");

//		$t->sort_by(array(
	//		"rgroupby" => array("customer" => "customer"),
		//));

		$t->set_sortable(false);
		$t->define_data(array(
			"order" => html::strong(t("Summa")),
			"customer" => "",
			"state" => "",
			"created" => "",
			"mat_price" => html::strong(number_format($sums["mat_price"], 2)),
			"res_price" => html::strong(number_format($sums["res_price"], 2)),
			"tot_price" => html::strong(number_format($sums["tot_price"], 2)),
			"cover_price" => html::strong(number_format($sums["cover_price"], 2)),
		));
	}

	function _request2filt(&$filt, $r)
	{
		if (!empty($r["s_state"]))
		{
			$filt["state"] = $r["s_state"];
		}

		if (!empty($r["s_customer"]))
		{
			// get customers for category
			$custs = array();
			foreach(obj($r["s_customer"])->connections_from(array("type" => "RELTPE_CUSTOMER")) as $c)
			{
				$custs[] = $c->prop("to");
			}
			$filt["customer"] = $custs;
		}

		if (empty($r["s_period"]))
		{
			$filt["created"] = new obj_predicate_compare(OBJ_COMP_GREATER, mktime(0,0, 0, 1, date("m"), date("Y")));
		}
		else
		{
			switch($r["s_period"])
			{
				case "prev_mon":
					$filt["created"] = new obj_predicate_compare(OBJ_COMP_BETWEEN, mktime(0,0, 0, 1, date("m")-1, date("Y")), mktime(0,0, 0, 1, date("m"), date("Y")));
					break;

				case "total":
				default:
					break;
			}
		}
	}
}

?>
