<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order_center.aw,v 1.3 2004/05/17 14:34:19 kristo Exp $
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

@property sortbl type=table store=no
@caption Toodete sorteerimine

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

			case "sortbl":
				$this->do_sortbl($arr);
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

			case "sortbl":
				$this->do_save_sortbl($arr);
				break;
		}
		return $retval;
	}	

	function do_save_layoutbl(&$arr)
	{
		$arr["obj_inst"]->set_meta("itemlayouts", $arr["request"]["itemlayout"]);
		$arr["obj_inst"]->set_meta("itemlayouts_long", $arr["request"]["itemlayout_long"]);
		$arr["obj_inst"]->set_meta("tblayouts", $arr["request"]["tblayout"]);
	}

	function do_save_sortbl(&$arr)
	{
		$awa = new aw_array($arr["request"]["itemsorts"]);
		$res = array();
		foreach($awa->get() as $idx => $dat)
		{
			if ($dat["element"])
			{
				$res[] = $dat;
			}
		}

		$arr["obj_inst"]->set_meta("itemsorts", $res);
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

		$t->define_field(array(
			"name" => "item_layout_long",
			"caption" => "Paketi vaate kujundus",
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
		$this->itemlayouts_long = $o->meta("itemlayouts_long");

		$this->itemlayout_items = array("0" => "--vali--");
		foreach($o->connections_from(array("type" => RELTYPE_ITEM_LAYOUT)) as $c)
		{
			$this->itemlayout_items[$c->prop("to")] = $c->prop("to.name");
		}

		$o = obj($conf->prop("pkt_fld"));
		$this->layoutbl_ot_cb($o, $t);

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
			"item_layout_long" => html::select(array(
				"name" => "itemlayout_long[".$o->id()."]",
				"options" => $this->itemlayout_items,
				"selected" => $this->itemlayouts_long[$o->id()]
			)),
		));
	}

	function _init_sortbl(&$t)
	{
		$t->define_field(array(
			"name" => "sby",
			"caption" => "Sorditav v&auml;li",
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "sby_ord",
			"caption" => "Kasvav / kahanev",
			"align" => "center"
		));
	}

	function do_sortbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sortbl($t);

		$elements = array("" => "");
		list($GLOBALS["properties"][CL_SHOP_PRODUCT], $GLOBALS["tableinfo"][CL_SHOP_PRODUCT], $GLOBALS["relinfo"][CL_SHOP_PRODUCT]) = $GLOBALS["object_loader"]->load_properties(array(
			"clid" => CL_SHOP_PRODUCT
		));
		foreach($GLOBALS["properties"][CL_SHOP_PRODUCT] as $pn => $pd)
		{
			$elements[$pn] = $pd["caption"];
		}
		

		$maxi = 0;
		$is = new aw_array($arr["obj_inst"]->meta("itemsorts"));
		foreach($is->get() as $idx => $sd)
		{
			$t->define_data(array(
				"sby" => html::select(array(
					"options" => $elements,
					"selected" => $sd["element"],
					"name" => "itemsorts[$idx][element]"
				)),
				"sby_ord" => html::select(array(
					"options" => array("asc" => "Kasvav", "desc" => "Kahanev"),
					"selected" => $sd["ord"],
					"name" => "itemsorts[$idx][ord]"
				))
			));
			$maxi = max($maxi, $idx);
		}
		$maxi++;

		$t->define_data(array(
			"sby" => html::select(array(
				"options" => $elements,
				"selected" => "",
				"name" => "itemsorts[$maxi][element]"
			)),
			"sby_ord" => html::select(array(
				"options" => array("asc" => "Kasvav", "desc" => "Kahanev"),
				"selected" => "",
				"name" => "itemsorts[$maxi][ord]"
			))
		));

		$t->set_sortable(false);
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		return $this->my_orders(array());
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

		@attrib name=show_items nologin="1"

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

		$pl = $wh->get_packet_list(array(
			"id" => $wh_id,
			"parent" => $section
		));

		$this->do_sort_packet_list($pl, $soc->meta("itemsorts"));

		return $this->do_draw_prods_with_layout(array(
			"t_layout" => $t_layout, 
			"layout" => $layout, 
			"pl" =>  $pl,
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
				"layout" => $layout,
				"oc_obj" => $soc
			)));
		}

		return $tl_inst->finish_table();
	}

	/** returns the long layout object for the product 

		@comment
			$soc - order center object
			$prod - product object
	**/
	function get_long_layout_for_prod($arr)
	{
		extract($arr);
		$il = $soc->meta("itemlayouts_long");
		foreach(array_reverse($prod->path()) as $p)
		{
			if ($il[$p->id()])
			{
				return obj($il[$p->id()]);
			}
		}
		return false;
	}

	/** shows the user a list of his/her previous orders

		@attrib name=my_orders is_public=1 caption="Minu tellimused"

	**/
	function my_orders($arr)
	{
		extract($arr);
		
		// get current person and get the orders from that
		$u = get_instance("core/users/user");
		$p = obj($u->get_current_person());

		$this->read_template("orders.tpl");

		foreach($p->connections_from(array("type" => "RELTYPE_ORDER")) as $c)
		{
			$ord = $c->to();
			$this->vars(array(
				"name" => $ord->name(),
				"tm" => $ord->created(),
				"sum" => number_format($ord->prop("sum"), 2),
				"view_link" => obj_link($ord->id()),
				"id" => $ord->id()
			));
			$l .= $this->parse("LINE");
		}

		$this->vars(array(
			"LINE" => $l,
			"reforb" => $this->mk_reforb("submit_my_orders")
		));

		return $this->parse();
	}

	/** 

		@attrib name=submit_my_orders 

	**/
	function submit_my_orders($arr)
	{
		extract($arr);
		$ord_i = get_instance(CL_SHOP_ORDER);
		$warehouse = 0;
		$items = array();
		if (is_array($sel) && count($sel) > 0 && !empty($makenew))
		{
			// create new order based on the selected orders
			$first = true;
			foreach($sel as $ordid)
			{
				$ord = obj($ordid);
				if ($first)
				{
					// get order center
					$oc = $ord->prop("oc");
				}

				// get all items from order
				foreach($ord_i->get_items_from_order($ord) as $i_id => $quant)
				{
					$items[$i_id] += $quant;
				}
				$first = false;
			}

			// must not create a real order, just stuff the items in the session
			$soc = get_instance(CL_SHOP_ORDER_CART);
			$soc->start_order();
			foreach($items as $iid => $q)
			{
				$soc->add_item($iid, $q);
			}
			return $this->mk_my_orb("show_cart" , array("oc" => $oc), CL_SHOP_ORDER_CART);
		}

		return $this->mk_my_orb("my_orders");
	}

	function do_sort_packet_list(&$pl, $itemsorts)
	{
		if (!is_array($itemsorts))
		{
			return;
		}
		$this->__is = $itemsorts;
		usort($pl, array(&$this, "__is_sorter"));
	}

	function __is_sorter($a, $b)
	{
		$comp_a = NULL;
		$comp_b = NULL;
		// find the first non-matching element
		foreach($this->__is as $isd)
		{
			$comp_a = $a->prop($isd["element"]);
			$comp_b = $b->prop($isd["element"]);
			$ord = $isd["ord"];
			if ($comp_a != $comp_b)
			{
				break;
			}
		}
		// sort by that element
		if ($comp_a  == $comp_b)
		{
			return 0;
		}

		if ($ord == "asc")
		{
			return $comp_a > $comp_b ? 1 : -1;
		}
		else
		{
			return $comp_a > $comp_b ? -1 : 1;
		}
	}
}
?>
