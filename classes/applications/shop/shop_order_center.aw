<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order_center.aw,v 1.1 2004/04/13 12:36:34 kristo Exp $
// shop_order_center.aw - Tellimiskeskkond 
/*

@tableinfo aw_shop_order_center index=aw_id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_SHOP_ORDER_CENTER relationmgr=yes

@default table=objects
@default group=general

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE table=aw_shop_order_center field=aw_warehouse_id
@caption Ladu

@property cart type=relpicker reltype=RELTYPE_CART table=aw_shop_order_center field=aw_cart_id
@caption Ostukorv

@groupinfo appear caption="N&auml;itamine"
@default group=appear

@property layoutbl type=table store=no
@caption Toodete layout

@reltype WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption ladu

@reltype TABLE_LAYOUT value=2 clid=CL_SHOP_PRODUCT_TABLE_LAYOUT
@caption toodete tabeli kujundus

@reltype ITEM_LAYOUT value=3 clid=CL_SHOP_PRODUCT_LAYOUT
@caption toote kujundus

@reltype CART value=4 clid=CL_SHOP_ORDER_CART
@caption ostukorv

*/

class shop_order_center extends class_base
{
	function shop_order_center()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_order_center",
			"clid" => CL_SHOP_ORDER_CENTER
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "layoutbl":
				$this->do_layoutbl($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "layoutbl":
				$this->do_save_layoutbl($arr);
				break;
		}
		return $retval;
	}	

	function do_save_layoutbl(&$arr)
	{
		$arr["obj_inst"]->set_meta("itemlayouts", $arr["request"]["itemlayout"]);
		$arr["obj_inst"]->set_meta("tblayouts", $arr["request"]["tblayout"]);
	}

	function _init_layoutbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => "Kataloog"
		));

		$t->define_field(array(
			"name" => "tbl_layout",
			"caption" => "Tabeli kujundus",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "item_layout",
			"caption" => "Paketi kujundus",
			"align" => "center"
		));

		$t->set_default_sortby("name");
	}

	function do_layoutbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_layoutbl($t);

		$wh = get_instance("applications/shop/shop_warehouse");

		$o = $arr["obj_inst"];
		
		if (!$o->prop("warehouse"))
		{
			return;
		}
		$wh = obj($o->prop("warehouse"));

		if (!$wh->prop("conf"))
		{
			return;
		}
		$conf = obj($wh->prop("conf"));
		
		if (!$conf->prop("pkt_fld"))
		{
			return;
		}

		$ot = new object_tree(array(
			"parent" => $conf->prop("pkt_fld"),
			"class_id" => CL_MENU,
		));

		$this->_oinst = &$o;

		$this->tblayouts = $o->meta("tblayouts");
		$this->tblayout_items = array("0" => "--vali--");
		foreach($o->connections_from(array("type" => RELTYPE_TABLE_LAYOUT)) as $c)
		{
			$this->tblayout_items[$c->prop("to")] = $c->prop("to.name");
		}

		$this->itemlayouts = $o->meta("itemlayouts");
		$this->itemlayout_items = array("0" => "--vali--");
		foreach($o->connections_from(array("type" => RELTYPE_ITEM_LAYOUT)) as $c)
		{
			$this->itemlayout_items[$c->prop("to")] = $c->prop("to.name");
		}

		$ot->foreach_cb(array(
			"func" => array(&$this, "layoutbl_ot_cb"),
			"param" => &$t,
			"save" => false
		));

		$t->sort_by();
	}

	function layoutbl_ot_cb(&$o, &$t)
	{
		$t->define_data(array(
			"name" => $o->path_str(),
			"tbl_layout" => html::select(array(
				"name" => "tblayout[".$o->id()."]",
				"options" => $this->tblayout_items,
				"selected" => $this->tblayouts[$o->id()]
			)),
			"item_layout" => html::select(array(
				"name" => "itemlayout[".$o->id()."]",
				"options" => $this->itemlayout_items,
				"selected" => $this->itemlayouts[$o->id()]
			)),
		));
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
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

	function get_folders_as_object_list($o, $level, $parent)
	{
		$this->folder_obj = $o;

		if (!$o->prop("warehouse"))
		{
			return new object_list();
		}
		$wh = obj($o->prop("warehouse"));

		if (!$wh->prop("conf"))
		{
			return new object_list();
		}
		$conf = obj($wh->prop("conf"));
		
		if (!$conf->prop("pkt_fld"))
		{
			return new object_list();
		}
		
		if ($level > 0)
		{
			$ol = new object_list(array(
				"parent" => $parent->id(),
				"class_id" => CL_MENU,
			));
		}
		else
		{
			$ol = new object_list(array(
				"parent" => $conf->prop("pkt_fld"),
				"class_id" => CL_MENU,
			));
		}
		
		return $ol;
	}

	function make_menu_link($o)
	{
		/*static $i;
		if (!is_object($i))
		{
			$i = get_instance("contentmgmt/site_show");
		}
		return $i->make_menu_link($o);*/

		
		return $this->mk_my_orb("show_items", array("id" => $this->folder_obj->id(), "section" => $o->id()));
	}

	/** shows shop items

		@attrib name=show_items

		@param id required type=int acl=view
		@param section required type=int acl=view

	**/
	function show_items($arr)
	{
		extract($arr);

		$soc = obj($arr["id"]);
		$wh_id = $soc->prop("warehouse");

		$wh = get_instance("applications/shop/shop_warehouse");

		// get the template for products for this folder
		$layout = $this->get_prod_layout_for_folder($soc, $section);

		// get the table layout for this folder
		$t_layout = $this->get_prod_table_layout_for_folder($soc, $section);

		return $this->do_draw_prods_with_layout(array(
			"t_layout" => $t_layout, 
			"layout" => $layout, 
			"pl" => $wh->get_packet_list(array(
				"id" => $wh_id,
				"parent" => $section
			)),
			"soc" => $soc
		));
	}

	function get_prod_layout_for_folder($soc, $section)
	{
		$il = $soc->meta("itemlayouts");
		$_p = obj($section);
		foreach(array_reverse($_p->path()) as $p)
		{
			if ($il[$p->id()])
			{
				return obj($il[$p->id()]);
			}
		}
		return false;
	}

	function get_prod_table_layout_for_folder($soc, $section)
	{
		$il = $soc->meta("tblayouts");
		$_p = obj($section);
		foreach(array_reverse($_p->path()) as $p)
		{
			if ($il[$p->id()])
			{
				return obj($il[$p->id()]);
			}
		}
		return false;
	}

	/** returns the html for the products given

		@comment
			
			params:
				$t_layout - table layout to use
				$layout - product layout to use
				$pl - array of product object instances
	**/
	function do_draw_prods_with_layout($arr)
	{
		extract($arr);

		$tl_inst = $t_layout->instance();
		$tl_inst->start_table($t_layout, $soc);

		foreach($pl as $o)
		{
			$i = $o->instance();
			$tl_inst->add_product($i->do_draw_product(array(
				"prod" => $o,
				"layout" => $layout
			)));
		}

		return $tl_inst->finish_table();
	}
}
?>
