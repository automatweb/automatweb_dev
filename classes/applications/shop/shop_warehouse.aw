<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_warehouse.aw,v 1.1 2004/03/17 16:06:58 kristo Exp $
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
@groupinfo storage caption="Lasoseis"
@groupinfo storage_storage parent=storage caption="Laoseis" submit=no
@groupinfo storage_income parent=storage caption="Sissetulekud" submit=no
@groupinfo storage_export parent=storage caption="V&auml;ljaminekud" submit=no

@property storage_list type=table store=no group=storage_storage no_caption=1
@caption Laoseis

@property storage_income_toolbar type=toolbar no_caption=1 group=storage_income store=no

@property storage_income type=table store=no group=storage_income no_caption=1
@caption Sissetulekud

@property storage_export_toolbar type=toolbar no_caption=1 group=storage_export store=no

@property storage_export type=table store=no group=storage_export no_caption=1
@caption V&auml;ljaminekud

////////// reltypes
@reltype CONFIG value=1 clid=CL_SHOP_CONFIG
@caption konfiguratsioon

@reltype PRODUCT value=2 clid=CL_SHOP_PRODUCT
@caption toode

@reltype PACKET value=2 clid=CL_SHOP_PACKET
@caption pakett

@reltype STORAGE_INCOME value=3 clid=CL_SHOP_WAREHOUSE_RECEPTION
@caption lao sissetulek

@reltype STORAGE_EXPORT value=4 clid=CL_SHOP_WAREHOUSE_EXPORT
@caption lao v&auml;jaminek

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
			return PROP_ERROR;
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
			$tb->define_data(array(
				"name" => $o->path_str(array("to" => $this->prod_fld)),
				"cnt" => $o->prop("item_count"),
				"item_type" => $tp,
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
					), CL_SHOP_PRODUCT),
					"caption" => "Muuda"
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
			"name" => "change",
			"caption" => "Muuda",
			"align" => "center"
		));
	}

	function _init_view(&$arr)
	{
		if (!$arr["obj_inst"]->prop("conf"))
		{
			$arr["prop"]["value"] =  "VIGA: konfiguratsioon on valimata!";
			return false;
		}
		$this->config = obj($arr["obj_inst"]->prop("conf"));
		if (!$this->config->prop("prod_fld"))
		{
			$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on toodete kataloog valimata!";
			return false;
		}
		if (!$this->config->prop("pkt_fld"))
		{
			$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on pakettide kataloog valimata!";
			return false;
		}
		if (!$this->config->prop("reception_fld"))
		{
			$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on sissetulekute kataloog valimata!";
			return false;
		}
		if (!$this->config->prop("export_fld"))
		{
			$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on v&auml;jaminekute kataloog valimata!";
			return false;
		}
		if (!$this->config->prop("prod_type_fld"))
		{
			$arr["prop"]["value"] =  "VIGA: konfiguratsioonist on toodete t&uuml;&uuml;pide kataloog valimata!";
			return false;
		}

		$this->prod_fld = $this->config->prop("prod_fld");
		$this->prod_tree_root = isset($_GET["tree_filter"]) ? $_GET["tree_filter"] : $this->config->prop("prod_fld");

		$this->pkt_fld = $this->config->prop("pkt_fld");
		$this->pkt_tree_root = isset($_GET["tree_filter"]) ? $_GET["tree_filter"] : $this->config->prop("pkt_fld");

		$this->reception_fld = $this->config->prop("reception_fld");
		$this->export_fld = $this->config->prop("export_fld");
		$this->prod_type_fld = $this->config->prop("prod_type_fld");

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
		$this->_init_prod_list_list_tbl($tb);

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
			$tb->define_data(array(
				"name" => $o->path_str(array("to" => $this->pkt_fld)),
				"cnt" => $o->prop("item_count"),
				"change" => html::href(array(
					"url" => $this->mk_my_orb("change", array(
						"id" => $o->id(),
					), CL_SHOP_PACKET),
					"caption" => "Muuda"
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
			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $name,
				"type" => $type,
				"count" => $i->prop("item_count"),
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
	}

	function do_storage_income_tbl(&$arr)
	{
		$this->_init_storage_income_tbl($arr["prop"]["vcl_inst"]);

		foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_STORAGE_INCOME)) as $c)
		{
			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"view" => html::href(array(
					"caption" => "Vaata",
					"url" => $this->mk_my_orb("change", array(
						"id" => $c->prop("to")
					), CL_SHOP_WAREHOUSE_RECEPTION)
				)),
				"modifiedby" => $c->prop("to.modifiedby"),
				"modified" => $c->prop("to.modified")
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
			$arr["prop"]["vcl_inst"]->define_data(array(
				"name" => $c->prop("to.name"),
				"view" => html::href(array(
					"caption" => "Vaata",
					"url" => $this->mk_my_orb("change", array(
						"id" => $c->prop("to")
					), CL_SHOP_WAREHOUSE_EXPORT)
				)),
				"modifiedby" => $c->prop("to.modifiedby"),
				"modified" => $c->prop("to.modified")
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


	///////////////////////////////////////////////
	// warehouse public interface functions      //
	///////////////////////////////////////////////

	/** returns a list of packets in the warehouse $id

		@attrib param=name

		@param id required
	**/
	function get_packet_list($arr)
	{
		$wh = obj($arr["id"]);
		$conf = obj($wh->prop("conf"));

		$ot = new object_tree(array(
			"parent" => $conf->prop("pkt_fld"),
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
			"parent" => $conf->prop("prod_fld"),
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
}
?>
