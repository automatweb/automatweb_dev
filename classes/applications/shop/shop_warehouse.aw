<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_warehouse.aw,v 1.5 2004/05/06 12:19:25 kristo Exp $
// shop_warehouse.aw - Ladu 
/*

@tableinfo aw_shop_warehouses index=aw_oid master_table=objects master_index=brother_of

@classinfo syslog_type=ST_SHOP_WAREHOUSE relationmgr=yes

@default table=objects
@default group=general

@property conf type=relpicker reltype=RELTYPE_CONFIG table=aw_shop_warehouses field=aw_config 
@caption Konfiguratsioon

/////////// products tab
@groupinfo products caption="Tooted" submit=no

@property products_toolbar type=toolbar no_caption=1 group=products store=no

@property products_list type=text store=no group=products no_caption=1 
@caption Toodete nimekiri 

/////////// packets tab
@groupinfo packets caption="Paketid" submit=no

@property packets_toolbar type=toolbar no_caption=1 group=packets store=no

@property packets_list type=text store=no group=packets no_caption=1
@caption Pakettide nimekiri

/////////// storage tab
@groupinfo storage caption="Laoseis"
@groupinfo storage_storage parent=storage caption="Laoseis" submit=no
@groupinfo storage_income parent=storage caption="Sissetulekud" 
@groupinfo storage_export parent=storage caption="V&auml;ljaminekud"

@property storage_list type=table store=no group=storage_storage no_caption=1
@caption Laoseis

@property storage_income_toolbar type=toolbar no_caption=1 group=storage_income store=no

@property storage_income type=table store=no group=storage_income no_caption=1
@caption Sissetulekud

@property storage_export_toolbar type=toolbar no_caption=1 group=storage_export store=no

@property storage_export type=table store=no group=storage_export no_caption=1
@caption V&auml;ljaminekud

////////// ordering tab
@groupinfo order caption="Telli"
@groupinfo order_unconfirmed parent=order caption="Kinnitamata"
@groupinfo order_confirmed parent=order caption="Kinnitatud"
@groupinfo order_orderer_cos parent=order caption="Tellijad"

@property order_unconfirmed_toolbar type=toolbar no_caption=1 group=order_unconfirmed store=no
@property order_unconfirmed type=table store=no group=order_unconfirmed no_caption=1

@property order_confirmed_toolbar type=toolbar no_caption=1 group=order_confirmed store=no
@property order_confirmed type=table store=no group=order_confirmed no_caption=1

@layout hbox_oc type=hbox group=order_orderer_cos 

@property order_orderer_cos_tree type=text store=no parent=hbox_oc group=order_orderer_cos no_caption=1
@property order_orderer_cos type=table store=no parent=hbox_oc group=order_orderer_cos no_caption=1

////////// reltypes
@reltype CONFIG value=1 clid=CL_SHOP_WAREHOUSE_CONFIG
@caption konfiguratsioon

@reltype PRODUCT value=2 clid=CL_SHOP_PRODUCT
@caption toode

@reltype PACKET value=2 clid=CL_SHOP_PACKET
@caption pakett

@reltype STORAGE_INCOME value=3 clid=CL_SHOP_WAREHOUSE_RECEPTION
@caption lao sissetulek

@reltype STORAGE_EXPORT value=4 clid=CL_SHOP_WAREHOUSE_EXPORT
@caption lao v&auml;jaminek

@reltype ORDER value=5 clid=CL_SHOP_ORDER
@caption tellimus

@reltype EMAIL value=6 clid=CL_ML_MEMBER
@caption saada tellimused

*/

class shop_warehouse extends class_base
{
	function shop_warehouse()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_warehouse",
			"clid" => CL_SHOP_WAREHOUSE
		));
	}

	function get_property($arr)
	{
		if (!$this->_init_view($arr))
		{
			return PROP_OK;
		}
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "products_toolbar":
				$this->mk_prod_toolbar($arr);
				break;

			case "products_list":
				$this->do_prod_list($arr);
				break;

			case "packets_toolbar":
				$this->mk_pkt_toolbar($arr);
				break;

			case "packets_list":
				$this->do_pkt_list($arr);
				break;

			case "storage_list":
				$this->do_storage_list_tbl($arr);
				break;

			case "storage_income_toolbar":
				$this->mk_storage_income_toolbar($arr);
				break;

			case "storage_income":
				$this->do_storage_income_tbl($arr);
				break;

			case "storage_export_toolbar":
				$this->mk_storage_export_toolbar($arr);
				break;

			case "storage_export":
				$this->do_storage_export_tbl($arr);
				break;

			case "order_unconfirmed_toolbar":
				$this->mk_order_unconfirmed_toolbar($arr);
				break;

			case "order_unconfirmed":
				$this->do_order_unconfirmed_tbl($arr);
				break;

			case "order_confirmed_toolbar":
				$this->mk_order_confirmed_toolbar($arr);
				break;

			case "order_confirmed":
				$this->do_order_confirmed_tbl($arr);
				break;

			case "order_orderer_cos":
				$this->do_order_orderer_cos_tbl($arr);
				break;

			case "order_orderer_cos_tree":
				$this->do_order_orderer_cos_tree($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "storage_income":
				$this->save_storage_inc_tbl($arr);
				break;

			case "storage_export":
				$this->save_storage_exp_tbl($arr);
				break;

			case "order_unconfirmed":
				$this->save_order_unconfirmed_tbl($arr);
				break;
		}
		return $retval;
	}	

	function mk_prod_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "crt_".$this->prod_type_fld,
			"tooltip" => "Uus"
		));

		$this->_req_add_itypes($tb, $this->prod_type_fld, $data);
	}

	function _req_add_itypes(&$tb, $parent, &$data)
	{
		$ol = new object_list(array(
			"parent" => $parent,
			"class_id" => array(CL_MENU, CL_SHOP_PRODUCT_TYPE),
			"lang_id" => array(),
			"site_id" => array()
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->class_id() != CL_MENU)
			{
				$tb->add_menu_item(array(
					"parent" => "crt_".$parent,
					"text" => $o->name(),
					"link" => $this->mk_my_orb("new", array(
						"item_type" => $o->id(),
						"parent" => $this->prod_tree_root,
						"alias_to" => $data["obj_inst"]->id(),
						"reltype" => RELTYPE_PRODUCT,
						"return_url" => urlencode(aw_global_get("REQUEST_URI")),
						"cfgform" => $o->prop("sp_cfgform")
					), CL_SHOP_PRODUCT)
				));
			}
			else
			{
				$tb->add_sub_menu(array(
					"parent" => "crt_".$parent,
					"name" => "crt_".$o->id(),
					"text" => $o->name()
				));
				$this->_req_add_itypes($tb, $o->id(), $data);
			}
		}
	}

	function do_prod_list(&$arr)
	{
		// this has tree on left, table on right
		$this->read_template("prod_list.tpl");

		$this->vars(array(
			"tree" => $this->_prod_list_tree($arr),
			"list" => $this->_prod_list_list($arr)
		));

		$arr["prop"]["value"] = $this->parse();
	}

	function _prod_list_tree(&$arr)
	{
		$ot = new object_tree(array(
			"parent" => $this->config->prop("prod_fld"),
			"class_id" => CL_MENU,
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
		));
		
		classload("vcl/treeview");
		$tv = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "prods",
				"persist_state" => true,
			),
			"root_item" => obj($this->config->prop("prod_fld")),
			"ot" => $ot,
			"var" => "tree_filter"
		));

		return $tv->finalize_tree();
	}

	function _prod_list_list(&$arr)
	{
		classload("vcl/table");
		$tb = new aw_table(array("layout" => "generic"));
		$this->_init_prod_list_list_tbl($tb);

		// get items 
		$ot = new object_tree(array(
			"parent" => $this->prod_tree_root,
			"class_id" => array(CL_MENU,CL_SHOP_PRODUCT),
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
		));
		$ol = $ot->to_list();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->class_id() == CL_MENU)
			{
				continue;
			}
			if (is_oid($o->prop("item_type")))
			{
				$tp = obj($o->prop("item_type"));
				$tp = $tp->name();
			}
			else
			{
				$tp = "";
			}

			$get = "";
			if ($o->prop("item_count") > 0)
			{
				$get = html::href(array(
					"url" => $this->mk_my_orb("create_export", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => "V&otilde;ta laost"
				));
			}

			$tb->define_data(array(
				"name" => $o->path_str(array("to" => $this->prod_fld)),
				"cnt" => $o->prop("item_count"),
				"item_type" => $tp,
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
					), CL_SHOP_PRODUCT),
					"caption" => "Muuda"
				)),
				"get" => $get,
				"put" => html::href(array(
					"url" => $this->mk_my_orb("create_reception", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => "Vii lattu"
				))
			));
		}
		
		return $tb->draw();
	}

	function _init_prod_list_list_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "item_type",
			"caption" => "T&uuml;&uuml;p",
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "cnt",
			"caption" => "Kogus laos",
			"align" => "center",
			"type" => "int"
		));

		$t->define_field(array(
			"name" => "get",
			"caption" => "V&otilde;ta laost",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "put",
			"caption" => "Vii lattu",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => "Muuda",
			"align" => "center"
		));
	}

	function _init_view(&$arr)
	{
		if (!$arr["obj_inst"]->prop("conf"))
		{
			//$arr["prop"]["value"] =  "VIGA: konfiguratsioon on valimata!";
			return false;
		}
		$this->config = obj($arr["obj_inst"]->prop("conf"));
		if (!$this->config->prop("prod_fld"))
		{
			//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on toodete kataloog valimata!";
			return false;
		}
		if (!$this->config->prop("pkt_fld"))
		{
			//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on pakettide kataloog valimata!";
			return false;
		}
		if (!$this->config->prop("reception_fld"))
		{
			//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on sissetulekute kataloog valimata!";
			return false;
		}
		if (!$this->config->prop("export_fld"))
		{
			//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on v&auml;jaminekute kataloog valimata!";
			return false;
		}
		if (!$this->config->prop("prod_type_fld"))
		{
			//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on toodete t&uuml;&uuml;pide kataloog valimata!";
			return false;
		}
		if (!$this->config->prop("order_fld"))
		{
			//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on tellimuste kataloog valimata!";
			return false;
		}
		if (!$this->config->prop("buyers_fld"))
		{
			//$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on tellijate kataloog valimata!";
			return false;
		}

		$this->prod_fld = $this->config->prop("prod_fld");
		$this->prod_tree_root = isset($_GET["tree_filter"]) ? $_GET["tree_filter"] : $this->config->prop("prod_fld");

		$this->pkt_fld = $this->config->prop("pkt_fld");
		$this->pkt_tree_root = isset($_GET["tree_filter"]) ? $_GET["tree_filter"] : $this->config->prop("pkt_fld");

		$this->reception_fld = $this->config->prop("reception_fld");
		$this->export_fld = $this->config->prop("export_fld");
		$this->prod_type_fld = $this->config->prop("prod_type_fld");
		$this->order_fld = $this->config->prop("order_fld");
		$this->buyers_fld = $this->config->prop("buyers_fld");

		return true;
	}

	function mk_pkt_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_pkt",
			"tooltip" => "Uus"
		));

		$tb->add_menu_item(array(
			"parent" => "create_pkt",
			"text" => "Lisa pakett",
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->pkt_tree_root,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => RELTYPE_PACKET,
				"return_url" => urlencode(aw_global_get("REQUEST_URI"))
			), CL_SHOP_PACKET)
		));
	}

	function do_pkt_list(&$arr)
	{
		// this has tree on left, table on right
		$this->read_template("prod_list.tpl");

		$this->vars(array(
			"tree" => $this->_pkt_list_tree($arr),
			"list" => $this->_pkt_list_list($arr)
		));

		$arr["prop"]["value"] = $this->parse();
	}

	function _pkt_list_tree(&$arr)
	{
		$ot = new object_tree(array(
			"parent" => $this->config->prop("pkt_fld"),
			"class_id" => CL_MENU,
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
		));
		
		classload("vcl/treeview");
		$tv = treeview::tree_from_objects(array(
			"tree_opts" => array(
				"type" => TREE_DHTML,
				"tree_id" => "pkts",
				"persist_state" => true,
			),
			"root_item" => obj($this->config->prop("pkt_fld")),
			"ot" => $ot,
			"var" => "tree_filter"
		));

		return $tv->finalize_tree();
	}

	function _pkt_list_list(&$arr)
	{
		classload("vcl/table");
		$tb = new aw_table(array("layout" => "generic"));
		$this->_init_pkt_list_list_tbl($tb);

		// get items 
		$ot = new object_tree(array(
			"parent" => $this->pkt_tree_root,
			"class_id" => array(CL_MENU,CL_SHOP_PACKET),
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
		));
		$ol = $ot->to_list();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->class_id() == CL_MENU)
			{
				continue;
			}

			$get = "";
			if ($o->prop("item_count") > 0)
			{
				$get = html::href(array(
					"url" => $this->mk_my_orb("create_export", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => "V&otilde;ta laost"
				));
			}

			$tb->define_data(array(
				"name" => $o->path_str(array("to" => $this->pkt_fld)),
				"cnt" => $o->prop("item_count"),
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
					), CL_SHOP_PACKET),
					"caption" => "Muuda"
				)),
				"get" => $get,
				"put" => html::href(array(
					"url" => $this->mk_my_orb("create_reception", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $o->id()
					)),
					"caption" => "Vii lattu"
				))
			));
		}
		
		return $tb->draw();
	}

	function _init_pkt_list_list_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "name",
			"caption" => "Nimi"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "cnt",
			"caption" => "Kogus laos",
			"align" => "center",
			"type" => "int"
		));

		$t->define_field(array(
			"name" => "get",
			"caption" => "V&otilde;ta laost",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "put",
			"caption" => "Vii lattu",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "change",
			"caption" => "Muuda",
			"align" => "center"
		));
	}

	function do_storage_list_tbl(&$arr)
	{
		$this->_init_storage_list_tbl($arr["prop"]["vcl_inst"]);

		$items = $this->get_packet_list(array(
			"id" => $arr["obj_inst"]->id()
		));
		foreach($items as $i)
		{
			if ($i->class_id() == CL_SHOP_PACKET)
			{
				$type = "Pakett";
				$name = $i->path_str(array("to" => $this->config->prop("pkt_fld")));
			}
			else
			{
				$type = "";
				if (is_oid($i->prop("item_type")))
				{
					$type_o = obj($i->prop("item_type"));
					$type = $type_o->name();
				}
				$name = $i->path_str(array("to" => $this->config->prop("prod_fld")));
			}

			$get = "";
			if ($i->prop("item_count") > 0)
			{
				$get = html::href(array(
					"url" => $this->mk_my_orb("create_export", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $i->id()
					)),
					"caption" => "V&otilde;ta laost"
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $name,
				"type" => $type,
				"count" => $i->prop("item_count"),
				"get" => $get,
				"put" => html::href(array(
					"url" => $this->mk_my_orb("create_reception", array(
						"id" => $arr["obj_inst"]->id(),
						"product" => $i->id()
					)),
					"caption" => "Vii lattu"
				))
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _init_storage_list_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "name",
			"caption" => "Nimi"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "type",
			"caption" => "T&uuml;&uuml;p",
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "count",
			"caption" => "Laoseis",
			"type" => "int",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "get",
			"caption" => "V&otilde;ta laost",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "put",
			"caption" => "Vii lattu",
			"align" => "center"
		));

	}

	function do_storage_income_tbl(&$arr)
	{
		$this->_init_storage_income_tbl($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_STORAGE_INCOME)) as $c)
		{
			$to = $c->to();

			if ($to->prop("confirm"))
			{
				$stat = "Sissetulek kinnitatud";
			}
			else
			{
				$stat = html::checkbox(array(
					"name" => "confirm[".$to->id()."]",
					"value" => 1
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"view" => html::href(array(
					"caption" => "Vaata",
					"url" => $this->mk_my_orb("change", array(
						"id" => $c->prop("to")
					), CL_SHOP_WAREHOUSE_RECEPTION)
				)),
				"modifiedby" => $c->prop("to.modifiedby"),
				"modified" => $c->prop("to.modified"),
				"status" => $stat
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _init_storage_income_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "name",
			"caption" => "Nimi"
		));

		$t->define_field(array(
			"name" => "status",
			"caption" => "Staatus",
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "modifiedby",
			"caption" => "Kes",
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "modified",
			"caption" => "Millal",
			"align" => "center",
			"type" => "time",
			"format" => "m.d.Y H:i"
		));

		$t->define_field(array(
			"name" => "view",
			"caption" => "Vaata",
			"align" => "center"
		));
	}

	function save_storage_inc_tbl(&$arr)
	{
		$re = get_instance("applications/shop/shop_warehouse_reception");

		$awa = new aw_array($arr["request"]["confirm"]);
		foreach($awa->get() as $inc => $one)
		{
			if ($one == 1)
			{
				// confirm reception
				$re->do_confirm(obj($inc));
			}
		}
	}

	function mk_storage_income_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_reception",
			"tooltip" => "Uus"
		));

		$tb->add_menu_item(array(
			"parent" => "create_reception",
			"text" => "Lisa sissetulek",
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->reception_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => RELTYPE_STORAGE_INCOME,
				"return_url" => urlencode(aw_global_get("REQUEST_URI"))
			), CL_SHOP_WAREHOUSE_RECEPTION)
		));
	}


	function do_storage_export_tbl(&$arr)
	{
		$this->_init_storage_export_tbl($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_STORAGE_EXPORT)) as $c)
		{
			$to = $c->to();

			if ($to->prop("confirm"))
			{
				$stat = "Sissetulek kinnitatud";
			}
			else
			{
				$stat = html::checkbox(array(
					"name" => "confirm[".$to->id()."]",
					"value" => 1
				));
			}

			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"view" => html::href(array(
					"caption" => "Vaata",
					"url" => $this->mk_my_orb("change", array(
						"id" => $c->prop("to")
					), CL_SHOP_WAREHOUSE_EXPORT)
				)),
				"modifiedby" => $c->prop("to.modifiedby"),
				"modified" => $c->prop("to.modified"),
				"status" => $stat
			));
		}

		$arr["prop"]["vcl_inst"]->sort_by();
	}

	function _init_storage_export_tbl(&$t)
	{
		$t->define_field(array(
			"sortable" => 1,
			"name" => "name",
			"caption" => "Nimi"
		));

		$t->define_field(array(
			"name" => "status",
			"caption" => "Staatus",
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "modifiedby",
			"caption" => "Kes",
			"align" => "center"
		));

		$t->define_field(array(
			"sortable" => 1,
			"name" => "modified",
			"caption" => "Millal",
			"align" => "center",
			"type" => "time",
			"format" => "m.d.Y H:i"
		));

		$t->define_field(array(
			"name" => "view",
			"caption" => "Vaata",
			"align" => "center"
		));
	}

	function save_storage_exp_tbl(&$arr)
	{
		$re = get_instance("applications/shop/shop_warehouse_export");

		$awa = new aw_array($arr["request"]["confirm"]);
		foreach($awa->get() as $inc => $one)
		{
			if ($one == 1)
			{
				// confirm export
				$re->do_confirm(obj($inc));
			}
		}
	}

	function mk_storage_export_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_export",
			"tooltip" => "Uus"
		));

		$tb->add_menu_item(array(
			"parent" => "create_export",
			"text" => "Lisa v&auml;ljaminek",
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->export_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => RELTYPE_STORAGE_EXPORT,
				"return_url" => urlencode(aw_global_get("REQUEST_URI"))
			), CL_SHOP_WAREHOUSE_EXPORT)
		));
	}

	/** creates a new export object and attach a product to it, then redirect user to count entry
	
		@attrib name=create_export

		@param id required type=int acl=view
		@param product required type=int acl=view

	**/
	function create_export($arr)
	{
		extract($arr);
		$o = obj($id);
		$tmp = array(
			"obj_inst" => $o
		);
		$this->_init_view($tmp);

		$p = obj($product);

		// create export object
		$e = obj();
		$e->set_parent($this->export_fld);
		$e->set_class_id(CL_SHOP_WAREHOUSE_EXPORT);
		$e->set_name("Lao v&auml;ljaminek: ".$p->name());
		$e->save();

		$e->connect(array(
			"to" => $p->id(),
			"reltype" => 1 // RELTYPE_PRODUCT
		));

		// also connect the export to warehouse
		$o->connect(array(
			"to" => $e,
			"reltype" => 4 // RELTYPE_STORAGE_EXPORT
		));

		return $this->mk_my_orb("change", array(
			"id" => $e->id(),
			"group" => "export",
			"return_url" => urlencode($this->mk_my_orb("change", array(
				"id" => $o->id(),
				"group" => "storage_export"
			)))
		), CL_SHOP_WAREHOUSE_EXPORT);
	}

	/** creates a new reception object and attach a product to it, then redirect user to count entry
	
		@attrib name=create_reception

		@param id required type=int acl=view
		@param product required type=int acl=view

	**/
	function create_reception($arr)
	{
		extract($arr);
		$o = obj($id);
		$tmp = array(
			"obj_inst" => $o
		);
		$this->_init_view($tmp);

		$p = obj($product);

		// create export object
		$e = obj();
		$e->set_parent($this->reception_fld);
		$e->set_class_id(CL_SHOP_WAREHOUSE_RECEPTION);
		$e->set_name("Lao sissetulek: ".$p->name());
		$e->save();

		$e->connect(array(
			"to" => $p->id(),
			"reltype" => 1 // RELTYPE_PRODUCT
		));

		// also connect the reception to warehouse
		$o->connect(array(
			"to" => $e,
			"reltype" => 3 // RELTYPE_STORAGE_INCOME
		));

		return $this->mk_my_orb("change", array(
			"id" => $e->id(),
			"group" => "income",
			"return_url" => urlencode($this->mk_my_orb("change", array(
				"id" => $o->id(),
				"group" => "storage_income"
			)))
		), CL_SHOP_WAREHOUSE_RECEPTION);
	}

	function mk_order_unconfirmed_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_order",
			"tooltip" => "Uus tellimus"
		));

		$tb->add_menu_item(array(
			"parent" => "create_order",
			"text" => "Lisa tellimus",
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->order_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => RELTYPE_ORDER,
				"return_url" => urlencode(aw_global_get("REQUEST_URI"))
			), CL_SHOP_ORDER)
		));
	}

	function do_order_unconfirmed_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_order_unconfirmed_tbl($t);

		// list orders from order folder
		$ol = new object_list(array(
			"class_id" => CL_SHOP_ORDER,
			"confirmed" => 0
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$m = $o->modifiedby();
			$mb = $m->name();
			if ($o->prop("orderer_person"))
			{
				$_person = obj($o->prop("orderer_person"));
				$mb = $_person->name();
			}

			if ($o->prop("orderer_company"))
			{
				$_comp = obj($o->prop("orderer_company"));
				$mb .= " / ".$_comp->name();
			}

			$t->define_data(array(
				"name" => $o->name(),
				"modifiedby" => $mb,
				"modified" => $o->created(),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
						"group" => "items"
					), CL_SHOP_ORDER),
					"caption" => "Vaata"
				)),
				"confirm" => html::checkbox(array(
					"name" => "confirm[".$o->id()."]",
					"value" => 1
				)),
				"price" => $o->prop("sum")
			));
		}
		$t->set_default_sortby("modified");
		$t->set_default_sorder("DESC");
		$t->sort_by();
	}

	function _init_order_unconfirmed_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => "Hind",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "confirm",
			"caption" => "Kinnita",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Kes",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => "Millal",
			"type" => "time",
			"format" => "d.m.Y H:i",
			"align" => "center",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "view",
			"caption" => "Vaata",
			"align" => "center"
		));
	}

	function mk_order_confirmed_toolbar(&$data)
	{
		$tb =& $data["prop"]["toolbar"];

		$tb->add_menu_button(array(
			"name" => "create_order",
			"tooltip" => "Uus tellimus"
		));

		$tb->add_menu_item(array(
			"parent" => "create_order",
			"text" => "Lisa tellimus",
			"link" => $this->mk_my_orb("new", array(
				"parent" => $this->order_fld,
				"alias_to" => $data["obj_inst"]->id(),
				"reltype" => RELTYPE_ORDER,
				"return_url" => urlencode(aw_global_get("REQUEST_URI"))
			), CL_SHOP_ORDER)
		));
	}

	function do_order_confirmed_tbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_order_confirmed_tbl($t);

		// list orders from order folder
		$ol = new object_list(array(
			"class_id" => CL_SHOP_ORDER,
			"confirmed" => 1
		));
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$m = $o->modifiedby();

			$t->define_data(array(
				"name" => $o->name(),
				"modifiedby" => $m->name(),
				"modified" => $m->modified(),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id()
					), CL_SHOP_ORDER),
					"caption" => "Vaata"
				)),
				"price" => $o->prop("sum")
			));
		}
	}

	function _init_order_confirmed_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi"
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => "Hind",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "modifiedby",
			"caption" => "Kes",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "modified",
			"caption" => "Millal",
			"type" => "time",
			"format" => "d.m.Y H:i",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "view",
			"caption" => "Vaata"
		));
	}

	function save_order_unconfirmed_tbl(&$arr)
	{
		$re = get_instance("applications/shop/shop_order");

		$awa = new aw_array($arr["request"]["confirm"]);
		foreach($awa->get() as $inc => $one)
		{
			if ($one == 1)
			{
				// confirm reception
				$re->do_confirm(obj($inc));
			}
		}
	}

	function _init_order_orderer_cos_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
		));

		$t->define_field(array(
			"name" => "price",
			"caption" => "Hind",
		));
		$t->define_field(array(
			"name" => "who",
			"caption" => "Kes",
		));
		$t->define_field(array(
			"name" => "when",
			"caption" => "Millal",
		));
		$t->define_field(array(
			"name" => "view",
			"caption" => "Vaata",
		));
	}

	function do_order_orderer_cos_tbl($arr)
	{
		$t =&$arr["prop"]["vcl_inst"];
		$this->_init_order_orderer_cos_tbl($t);

		// get orders by orderer
		if ($arr["request"]["tree_worker"])
		{
			$ol = new object_list(array(
				"class_id" => CL_SHOP_ORDER,
				"orderer_person" => $arr["request"]["tree_worker"]
			));
		}
		else
		if ($arr["request"]["tree_company"])
		{
			// get workers for co
			$co = obj($arr["request"]["tree_company"]);
			$ids = array();
			foreach($co->connections_from(array("type" => 8 /* RELTYPE_WORKER */)) as $c)
			{
				$ids[] = $c->prop("to");
			}
			$ol = new object_list(array(
				"class_id" => CL_SHOP_ORDER,
				"orderer_person" => $ids
			));
		}
		else
		if ($arr["request"]["tree_code"])
		{
			// get workers for co
			$categories = new object_list(array(
				"parent" => $this->buyers_fld,
				"class_id" => CL_CRM_SECTOR,
				"kood" => $arr["request"]["tree_code"]."%"
			));
			$ids = array();
			for($cat = $categories->begin(); !$categories->end(); $cat = $categories->next())
			{
				foreach($cat->connections_to(array("from.class_id" => CL_CRM_COMPANY)) as $c)
				{
					$co = $c->from();
					foreach($co->connections_from(array("type" => 8 /* RELTYPE_WORKER */)) as $c)
					{
						$ids[] = $c->prop("to");
					}
				}
			}

			if (count($ids) < 1)
			{
				$ol = new object_list();
			}
			else
			{
				$ol = new object_list(array(
					"class_id" => CL_SHOP_ORDER,
					"orderer_person" => $ids
				));
			}
		}
		else
		{
			$ol = new object_list();
		}

		$oinst = get_instance("applications/shop/shop_order");
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$t->define_data(array(
				"name" => $o->name(),
				"price" => $o->prop("sum"),
				"who" => $oinst->get_orderer($o),
				"when" => $o->modified(),
				"view" => html::href(array(
					"url" => $this->mk_my_orb("change", array("id" => $o->id()), $o->class_id()),
					"caption" => "Vaata"
				))
			));
		}
	}

	function do_order_orderer_cos_tree(&$arr)
	{
		// get categories
		$categories = new object_list(array(
			"parent" => $this->buyers_fld,
			"class_id" => CL_CRM_SECTOR,
		));

		$all_cos = new object_list(array(
			"parent" => $this->buyers_fld,
			"class_id" => CL_CRM_COMPANY
		));
		$this->all_cos_ids = $all_cos->names();

		$tv = $this->get_vcl_tree_from_cat_list($categories);

		// now, add all remaining cos as top level items
		foreach($this->all_cos_ids as $co_id => $con)
		{
			$tv->add_item(0, array(
				"name" => $con,
				"id" => "nocode_co".$co_id,
				"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", $co_id, aw_url_change_var("tree_worker", NULL)))
			));

			$co = obj($co_id);
			// now all people for that company
			foreach($co->connections_from(array("type" => 8 /* RELTYPE_WORKER */)) as $c)
			{
				$tv->add_item("nocode_co".$co->id(), array(
					"name" => $c->prop("to.name"),
					"id" => "nocode_wk".$c->prop("to"),
					"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", NULL, aw_url_change_var("tree_worker", $c->prop("to"))))
				));
			}
		}

	
		$arr["prop"]["value"] = $tv->finalize_tree();
	}

	function get_vcl_tree_from_cat_list($categories)
	{
		// now, gotst to make tree out of them. 
		// algorithm is: sort by length, add the shortest to first level, then start adding by legth
		// prop: kood
		$ta = array();
		$ids = array();
		for($o = $categories->begin(); !$categories->end(); $o = $categories->next())
		{
			$ta[$o->prop("kood")] = $o;
			$ids[] = $o->id();
		}
		uksort($ta, array(&$this, "__ta_sb_cb"));

		// get all companies with these categories
		$cos = new object_list(array(
			"class_id" => CL_CRM_COMPANY,
			"pohitegevus" => $ids
		));
		$this->cos_by_code = array();
		for($o = $cos->begin(); !$cos->end(); $o = $cos->next())
		{
			// get all type rels
			foreach($o->connections_from(array("to.class_id" => CL_CRM_SECTOR)) as $c)
			{
				$s = $c->to();
				$this->cos_by_code[$s->prop("kood")][] = $o;
			}
		}

		// now, start adding things to the tree.
		$tv = get_instance("vcl/treeview");
		$tv->start_tree(array(
			"type" => TREE_DHTML,
			"tree_id" => "shwhordcos",
			"persist_state" => true
		));

		$this->_req_filter_and_add($tv, $ta, "", 0);

		return $tv;
	}

	function _req_filter_and_add(&$tv, $ta, $filter_code, $parent)
	{
		$nta = array();

		$fclen = strlen($filter_code);
		$minl = 1000;
		$cpta = $ta;

		foreach($cpta as $code => $code_o)
		{
			if (substr($code, 0, $fclen) == $filter_code && $code != $filter_code)
			{
				$nta[$code] = $code_o;
				if (strlen($code) < $minl)
				{
					$minl = strlen($code);
				}
			}
		}

		if (count($nta) < 1)
		{
			// we reached the end of the tree, add cos now
			$this->_do_add_cos_by_code($tv, $filter_code);
			return;
		}

		uksort($nta, array(&$this, "__ta_sb_cb"));

		reset($nta);
		list($code, $code_o) = each($nta);
		while (strlen($code) == $minl)
		{
			$tv->add_item($parent, array(
				"name" => $code_o->name(),
				"id" => $code,
				"url" => aw_url_change_var("tree_code", $code, aw_url_change_var("tree_company", NULL, aw_url_change_var("tree_worker", NULL)))
			));

			// now find the children for this. 
			// how to do this? simple, filter the list by the start of this code and sort and insert smallest length, 
			// lather, rinse, repeat
			$this->_req_filter_and_add($tv, $nta, $code, $code);

			list($code, $code_o) = each($nta);
		}
	}

	function _do_add_cos_by_code(&$tv, $code)
	{
		if (!is_array($this->cos_by_code[$code]))
		{
			return;
		}
		foreach($this->cos_by_code[$code] as $co)
		{
			$tv->add_item($code, array(
				"name" => $co->name(),
				"id" => $code."co".$co->id(),
				"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", $co->id(), aw_url_change_var("tree_worker", NULL)))
			));
			unset($this->all_cos_ids[$co->id()]);

			// now all people for that company
			foreach($co->connections_from(array("type" => 8 /* RELTYPE_WORKER */)) as $c)
			{
				$tv->add_item($code."co".$co->id(), array(
					"name" => $c->prop("to.name"),
					"id" => $code."wk".$c->prop("to"),
					"url" => aw_url_change_var("tree_code", NULL, aw_url_change_var("tree_company", NULL, aw_url_change_var("tree_worker", $c->prop("to"))))
				));
			}
		}
	}

	function __ta_sb_cb($a, $b)
	{
		return ($a == $b ? 0 : ((strlen($a) < strlen($b)) ? -1 : 1));
	}

	///////////////////////////////////////////////
	// warehouse public interface functions      //
	///////////////////////////////////////////////

	/** returns a list of packets in the warehouse $id, optionally under folder $parent

		@attrib param=name

		@param id required
		@param parent optional
	**/
	function get_packet_list($arr)
	{
		$wh = obj($arr["id"]);
		$conf = obj($wh->prop("conf"));

		$ot = new object_tree(array(
			"parent" => (!empty($arr["parent"]) ? $arr["parent"] : $conf->prop("pkt_fld")),
			"class_id" => array(CL_MENU,CL_SHOP_PACKET),
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
		));

		$ret = array();
	
		$ol = $ot->to_list();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->class_id() == CL_MENU)
			{
				continue;
			}
			$ret[] = $o;
		}

		$ot = new object_tree(array(
			"parent" => (!empty($arr["parent"]) ? $arr["parent"] : $conf->prop("prod_fld")),
			"class_id" => array(CL_MENU,CL_SHOP_PRODUCT),
			"status" => array(STAT_ACTIVE, STAT_NOTACTIVE)
		));

		$ol = $ot->to_list();
		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			if ($o->class_id() == CL_MENU)
			{
				continue;
			}
			$ret[] = $o;
		}

		return $ret;
	}

	function get_order_folder($w)
	{
		error::throw_if(!$w->prop("conf"), array(
			"id" => ERR_FATAL,
			"msg" => "shop_warehouse::get_order_folder($w): the warehouse has not configuration object set!"
		));

		$conf = obj($w->prop("conf"));
		$tmp = $conf->prop("order_fld");

		error::throw_if(empty($tmp), array(
			"id" => ERR_FATAL,
			"msg" => "shop_warehouse::get_order_folder($w): the warehouse configuration has no order folder set!"
		));

		return $tmp;
	}
}
?>
