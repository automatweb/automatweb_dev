<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_order_center.aw,v 1.33 2005/08/23 08:38:02 kristo Exp $
// shop_order_center.aw - Tellimiskeskkond 
/*

@tableinfo aw_shop_order_center index=aw_id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_SHOP_ORDER_CENTER relationmgr=yes

@default group=general

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE table=aw_shop_order_center field=aw_warehouse_id
@caption Ladu

@property cart type=relpicker reltype=RELTYPE_CART table=aw_shop_order_center field=aw_cart_id
@caption Ostukorv

@default table=objects
@default field=meta
@default method=serialize

@property cart_type type=chooser
@caption Ostukorvi tüüp

@property multi_items type=checkbox ch_value=1
@caption Ostukorvis võib olla mitu sama ID-ga toodet

@property show_unconfirmed type=checkbox ch_value=1
@caption Näita tellijale tellimuste nimekirjas ainult kinnitamata tellimusi

@property no_change_button type=checkbox ch_value=1
@caption Ära kuva tellimiskeskkonnas toote kõrvale "Muuda" nuppu

@property only_prods type=checkbox ch_value=1
@caption Ostukorvis on tooted ilma pakendite, piltide jms

@property pdf_template type=textbox
@caption PDF Template faili nimi

@property data_form type=relpicker reltype=RELTYPE_ORDER_FORM
@caption Tellija andmete vorm

@property data_form_person type=select
@caption Isiku nime element andmete vormis

@property data_form_company type=select
@caption Organisatsiooni nime element andmete vormis

@property data_form_discount type=select
@caption Allahindluse element andmete vormis

@property only_active_items type=checkbox ch_value=1
@caption Ainult aktiivsed tooted

@property use_controller type=checkbox ch_value=1
@caption N&auml;itamiseks kasuta kontrollerit

@groupinfo mail_settings caption="Meiliseaded"
	@groupinfo mail_settings_orderer caption="Tellijale" parent=mail_settings
	@groupinfo mail_settings_seller caption="Pakkujale" parent=mail_settings

@default group=mail_settings_orderer

	@property mail_group_by type=select 
	@caption Toodete grupeerimine meilis

	@property send_attach type=checkbox ch_value=1
	@caption Lisa meili manusega tellimus

	@property mail_to_client type=checkbox ch_value=1
	@caption Saada tellijale e-mail

	@property mail_to_el type=select
	@caption E-maili element, kuhu saata tellimus

	@property mail_from_addr type=textbox
	@caption Meili From aadress

	@property mail_from_name type=textbox 
	@caption Meili From nimi

	@property mail_cust_content type=textarea rows=10 cols=80
	@caption Meili sisu (kui t&uuml;hi, siis templatest)

@default group=mail_settings_seller

	@property mail_to_seller_in_el type=select
	@caption Saada meil aadressile, mis on elemendis

@groupinfo payment caption="Makseviisid"
@default group=payment

@property rent_min_amt type=textbox
@caption J&auml;relmaksu min. summa

@property rent_prop type=select
@caption Elemendi

@property rent_prop_val type=textbox
@caption v&auml;&auml;rtus j&auml;relmaksuks


@groupinfo appear caption="N&auml;itamine"
@default group=appear

@property no_show_cart_contents type=checkbox ch_value=1
@caption &Auml;ra n&auml;ita korvi kinnitusvaadet

@property controller type=relpicker reltype=RELTYPE_CONTROLLER
@caption Vaikimisi n&auml;itamise kontroller

@property controller_tbl type=callback callback=callback_get_controller_tbl store=no
@caption Kontrollerid kataloogidele

@property layoutbl type=table store=no
@caption Toodete layout

@property sortbl type=table store=no
@caption Toodete sorteerimine


@groupinfo psfieldmap caption="Isukuandmete kaart"
@default group=psfieldmap

@property psfieldmap type=table store=no 
@caption Vali millised elemendid tellimuse andmete vormis vastavad isukuandmetele

@groupinfo orgfieldmap caption="Firma andmete kaart"
@default group=orgfieldmap

@property orgfieldmap type=table store=no 
@caption Vali millised elemendid tellimuse andmete vormis vastavad firma andmetele


@reltype WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption ladu

@reltype TABLE_LAYOUT value=2 clid=CL_SHOP_PRODUCT_TABLE_LAYOUT
@caption toodete tabeli kujundus

@reltype ITEM_LAYOUT value=3 clid=CL_SHOP_PRODUCT_LAYOUT
@caption toote kujundus

@reltype CART value=4 clid=CL_SHOP_ORDER_CART
@caption ostukorv

@reltype ORDER_FORM value=5 clid=CL_CFGFORM
@caption vorm tellija andmete jaoks

@reltype CONTROLLER value=6 clid=CL_FORM_CONTROLLER
@caption n&auml;itamise kontroller

@reltype ORDER_NAME_CTR value=7 clid=CL_FORM_CONTROLLER
@caption tellimuse nime kontroller
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
			case "cart_type":
				$prop["options"] = array(
					0 => t("Sessionipõhine"),
					1 => t("Kasutajapõhine"),
				);
				break;
				
			case "rent_prop":
				$df = $arr["obj_inst"]->prop("data_form");
				$opts = array();
				if (is_oid($df) && $this->can("view", $df))
				{
					$cu = get_instance(CL_CFGFORM);
					$ps = $cu->get_props_from_cfgform(array(
						"id" => $df
					));
					foreach($ps as $pn => $pd)
					{
						$opts[$pn] = $pd["caption"];
					}
				}
				$prop["options"] = $opts;
				break;

			case "layoutbl":
				if ($arr["obj_inst"]->prop("use_controller"))
				{
					return PROP_IGNORE;
				}
				$this->do_layoutbl($arr);
				break;

			case "sortbl":
				$this->do_sortbl($arr);
				break;

			case "controller":
				if (!$arr["obj_inst"]->prop("use_controller"))
				{
					return PROP_IGNORE;
				}
				break;			

			case "controller_tbl":
				if (!$arr["obj_inst"]->prop("use_controller"))
				{
					return PROP_IGNORE;
				}
				break;			

			case "data_form_person":
			case "data_form_company":
			case "data_form_discount":
			case "mail_to_el":
			case "mail_to_seller_in_el":
				if (!$arr["obj_inst"]->prop("data_form"))
				{	
					return PROP_IGNORE;
				}
				$opts = array("" => "");
				$props = $this->get_properties_from_data_form($arr["obj_inst"]);
				foreach($props as $pn => $pd)
				{
					$opts[$pn] = $pd["caption"];
				}
				$prop["options"] = $opts;
				break;

			case "psfieldmap":
				return $this->do_psfieldmap($arr);
				break;

			case "orgfieldmap":
				return $this->do_orgfieldmap($arr);
				break;

			case "mail_group_by":
				$cu = get_instance("cfg/cfgutils");
				$ps = $cu->load_properties(array("clid" => CL_SHOP_PRODUCT));
				$v = array("" => "");
				foreach($ps as $pn => $pd)
				{
					$v[$pn] = $pd["caption"];
				}	
				$prop["options"] = $v;
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

			case "psfieldmap":
				$arr["obj_inst"]->set_meta("ps_pmap", $arr["request"]["pmap"]);
				break;

			case "orgfieldmap":
				$arr["obj_inst"]->set_meta("org_pmap", $arr["request"]["pmap"]);
				break;

			case "controller_tbl":
				$this->save_ctr_t($arr);
				break;
		}
		return $retval;
	}	

	function do_save_layoutbl(&$arr)
	{
		$arr["obj_inst"]->set_meta("itemlayouts", $arr["request"]["itemlayout"]);
		$arr["obj_inst"]->set_meta("itemlayouts_long", $arr["request"]["itemlayout_long"]);
		$arr["obj_inst"]->set_meta("itemlayouts_long_2", $arr["request"]["itemlayout_long_2"]);
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
			"caption" => t("Kataloog")
		));

		$t->define_field(array(
			"name" => "tbl_layout",
			"caption" => t("Tabeli kujundus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "item_layout",
			"caption" => t("Paketi kujundus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "item_layout_long",
			"caption" => t("Paketi vaate kujundus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "item_layout_long_2",
			"caption" => t("Paketi teise vaate kujundus"),
			"align" => "center"
		));

		$t->set_default_sortby("name");
	}

	function do_layoutbl(&$arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_layoutbl($t);

		$wh = get_instance(CL_SHOP_WAREHOUSE);

		$o = $arr["obj_inst"];
		$this->_get_folder_ot_from_o($o);

		$this->_oinst = &$o;

		$this->tblayouts = $o->meta("tblayouts");
		$this->tblayout_items = array("0" => t("--vali--"));
		foreach($o->connections_from(array("type" => "RELTYPE_TABLE_LAYOUT")) as $c)
		{
			$this->tblayout_items[$c->prop("to")] = $c->prop("to.name");
		}

		$this->itemlayouts = $o->meta("itemlayouts");
		$this->itemlayouts_long = $o->meta("itemlayouts_long");
		$this->itemlayouts_long_2 = $o->meta("itemlayouts_long_2");

		$this->itemlayout_items = array("0" => "--vali--");
		foreach($o->connections_from(array("type" => "RELTYPE_ITEM_LAYOUT")) as $c)
		{
			$this->itemlayout_items[$c->prop("to")] = $c->prop("to.name");
		}

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
		$o = obj($conf->prop("pkt_fld"));
		$this->layoutbl_ot_cb($o, $t);

		$ot = new object_tree(array(
			"class_id" => CL_MENU,
			"parent" => $o->id(),
		));

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
			"item_layout_long_2" => html::select(array(
				"name" => "itemlayout_long_2[".$o->id()."]",
				"options" => $this->itemlayout_items,
				"selected" => $this->itemlayouts_long_2[$o->id()]
			)),
		));
	}

	function _init_sortbl(&$t)
	{
		$t->define_field(array(
			"name" => "sby",
			"caption" => t("Sorditav v&auml;li"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "sby_ord",
			"caption" => t("Kasvav / kahanev"),
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
		$elements["jrk"] = t("J&auml;rjekord");
		

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
				"sort_by" => "objects.jrk,objects.created",
				"status" => STAT_ACTIVE
			));
		}
		else
		{
			$ol = new object_list(array(
				"parent" => $conf->prop("pkt_fld"),
				"class_id" => CL_MENU,
				"sort_by" => "objects.jrk,objects.created",
				"status" => STAT_ACTIVE
			));
		}

		return $ol;
	}

	function make_menu_link($o, $ref = NULL)
	{
		if ($o->prop("link") != "")
		{
			return $o->prop("link");
		}

		if ($ref === NULL)
		{
			$link =  $this->mk_my_orb("show_items", array("id" => $this->folder_obj->id(), "section" => $o->id()));
		}
		else
		{
			$link =  $this->mk_my_orb("show_items", array("id" => $ref->id(), "section" => $o->id()));
		}
		return $link;
	}

	/** shows shop items

		@attrib name=show_items nologin="1"

		@param id required type=int acl=view
		@param section required type=int acl=view

	**/
	function show_items($arr)
	{
		enter_function("shop_order_center::show_items");
		extract($arr);
		$soc = obj($arr["id"]);
		if ($soc->prop("use_controller"))
		{
			$ctr = $soc->prop("controller");

			// see if this folder has a special controller
			$vals = safe_array($soc->meta("fld_controllers"));
			$so = obj($arr["section"]);
			enter_function("shop_order_center::show_items::path");
			$path = $so->path();

			foreach($path as $po)
			{
				$po_id = $po->id();
				if (!empty($vals[$po_id]))
				{
					$ctr = $vals[$po_id];
				}
			}
			exit_function("shop_order_center::show_items::path");
			
			if (is_oid($ctr) && $this->can("view", $ctr))
			{
				enter_function("shop_order_center::show_items::controller");
				$fc = get_instance(CL_FORM_CONTROLLER);
				$html = $fc->eval_controller($ctr, array(
					"soc" => $soc
				));
				exit_function("shop_order_center::show_items::controller");
				exit_function("shop_order_center::show_items");
				return $html; 
			}
		}

		$wh_id = $soc->prop("warehouse");

		$wh = get_instance(CL_SHOP_WAREHOUSE);

		// also show docs
		$ss = get_instance("contentmgmt/site_show");
		$tmp = array();
		$ss->_init_path_vars($tmp);
		$html = $ss->show_documents($tmp);
		
		$pl = $wh->get_packet_list(array(
			"id" => $wh_id,
			"parent" => $section,
			"only_active" => $soc->prop("only_active_items")
		));

		$this->do_sort_packet_list($pl, $soc->meta("itemsorts"));
		
		// get the template for products for this folder
		$layout = $this->get_prod_layout_for_folder($soc, $section);

		// get the table layout for this folder
		$t_layout = $this->get_prod_table_layout_for_folder($soc, $section);
		
		$html .= $this->do_draw_prods_with_layout(array(
			"t_layout" => $t_layout, 
			"layout" => $layout, 
			"pl" =>  $pl,
			"soc" => $soc
		));
		exit_function("shop_order_center::show_items");
		return $html;
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
		$soce = aw_global_get("soc_err");

		$tl_inst = $t_layout->instance();
		$tl_inst->start_table($t_layout, $soc);

		$xi = 0;
		$l_inst = $layout->instance();
		$l_inst->read_template($layout->prop("template"));
		foreach($pl as $o)
		{
			$i = $o->instance();
			if ($tl_inst->is_on_cur_page())
			{
				$oid = $o->id();
				$tl_inst->add_product($i->do_draw_product(array(
					"bgcolor" => $xi % 2 ? "cartbgcolor1" : "cartbgcolor2",
					"prod" => $o,
					"layout" => $layout,
					"oc_obj" => $soc,
					"l_inst" => $l_inst,
					"quantity" => $soce[$oid]["ordered_num_enter"],
					"is_err" => $soce[$oid]["is_err"],
					"prod_link_cb" => $arr["prod_link_cb"]
				)));
				$xi++;
			}
		}

		return $tl_inst->finish_table();
	}

	/** returns the long layout object for the product, based on the view given in the url

		@comment
			$soc - order center object
			$prod - product object
	**/
	function get_long_layout_for_prod($arr)
	{
		extract($arr);
		if ($GLOBALS["view"] == 2)
		{
			$il = $soc->meta("itemlayouts_long_2");
		}
		else
		{
			$il = $soc->meta("itemlayouts_long");
		}
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
		$u = get_instance(CL_USER);
		$p = obj($u->get_current_person());
		$this->read_template("orders.tpl");
		if($ord = $p->get_first_obj_by_reltype("RELTYPE_ORDER"))
		{
			$center = $ord->get_first_obj_by_reltype("RELTYPE_ORDER_CENTER");
			$unconfed = $center->prop("show_unconfirmed");
		}
		foreach($p->connections_from(array("type" => "RELTYPE_ORDER")) as $c)
		{
			$ord = $c->to();
			$ord_item_data = safe_array($ord->meta('ord_item_data'));
			$read_price_total = 0;
			foreach($ord_item_data as $_prod_id => $inf)
			{
				foreach($inf as $num => $dat)
				{
					$read_price_total += str_replace(",", "", $dat["read_price"]);
				}
			}

			if($unconfed == 1 && $confirmed = $ord->prop("confirmed") == 1)
			{
				continue;
			}
			$this->vars(array(
				"name" => $ord->name(),
				"tm" => $ord->created(),
				"sum" => number_format($ord->prop("sum"), 2),
				"order_data_read_price_total" => number_format($read_price_total,2),
				"view_link" => obj_link($ord->id()),
				"id" => $ord->id()
			));
			$l .= $this->parse("LINE");
		}

		$ord_ids = array();
		foreach($p->connections_to(array("from.class_id" => CL_ORDERS_ORDER)) as $c)
		{
			$ord = $c->from();
			if ($ord->prop("order_confirmed") == 1)
			{
				continue;
			}
			$ord_ids[] = $ord->id();
		}

		if (!count($ord_ids))
		{
			$ool = new object_list();
		}
		else
		{
			$ool = new object_list(array(
				"class_id" => CL_ORDERS_ORDER,
				"oid" => $ord_ids,
				"sort_by" => "objects.created"
			));
		}

		foreach($ool->arr() as $ord)
		{
			$this->vars(array(
				"name" => $ord->name(),
				"tm" => $ord->created(),
				"sum" => number_format($ord->prop("sum"), 2),
				"view_link" => obj_link($ord->id()),
				"id" => $ord->id()
			));
			$l .= $this->parse("LINE2");
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
			if ($isd["element"] == "jrk")
			{
				$comp_a = $a->ord();
				$comp_b = $b->ord();
			}
			else
			{
				$comp_a = $a->prop($isd["element"]);
				$comp_b = $b->prop($isd["element"]);
			}

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

	function get_properties_from_data_form($oc, $cud = array())
	{
		$ret = array();

		// get data form from that
		if (!$oc->prop("data_form"))
		{
			return $ret;
		}

		// get props from conf form
		if (!$this->can("view", $oc->prop("data_form")))
		{
			return $ret;
		}
		$cff = obj($oc->prop("data_form"));
		$class_id = $cff->prop("ctype");
		if (!$class_id)
		{
			return $ret;
		}
		$class_i = get_instance($class_id == CL_DOCUMENT ? "doc" : $class_id);
 
		$cf_ps = $class_i->load_from_storage(array(
			"id" => $cff->id()
		));

		$v_ctrs = safe_array($cff->meta("view_controllers"));

		// get all props
		$cfgx = get_instance("cfg/cfgutils");
		$all_ps = $cfgx->load_properties(array(
			"clid" => $class_id,
		));

		$ps_pmap = safe_array($oc->meta("ps_pmap"));
		$org_pmap = safe_array($oc->meta("org_pmap"));

		$u_i = get_instance(CL_USER);
		$cur_p_id = $u_i->get_current_person();
		$cur_p = obj();
		if (is_oid($cur_p_id) && $this->can("view", $cur_p_id))
		{
			$cur_p = obj($cur_p_id);
		}

		$cur_co_id = $u_i->get_current_company();
		$cur_co = obj();
		if (is_oid($cur_co_id) && $this->can("view", $cur_co_id))
		{
			$cur_co = obj($cur_co_id);
		}

		// rewrite names as user_data[prop]
		foreach($cf_ps as $pn => $pd)
		{
			if ($pn == "is_translated" || $pn == "needs_translation")
			{
				continue;
			}
			$ret[$pn] = $all_ps[$pn];
			$ret[$pn]["caption"] = $pd["caption"];
			$ret[$pn]["name"] = "user_data[$pn]";

			if (($fld = array_search($pn, $ps_pmap)))
			{
				$cud[$pn] = $cur_p->prop($fld);
			}

			if (($fld = array_search($pn, $org_pmap)))
			{
				$cud[$pn] = $cur_co->prop($fld);
			}

			if ($ret[$pn]["type"] == "date_select")
			{
				$ret[$pn]["value"] = date_edit::get_timestamp($cud[$pn]);
			}
			else
			{
				$ret[$pn]["value"] = $cud[$pn];
			}

			$ret[$pn]["view_controllers"] = $v_ctrs[$pn];
		}

		return $ret;
	}

	function _init_fieldm_t(&$t, $props)
	{
		$t->define_field(array(
			"name" => "desc",
			"caption" => t("&nbsp")
		));

		// now, for each property in the selected form do a column
		foreach($props as $pn => $pd)
		{
			$t->define_field(array(
				"name" => "f_".$pn,
				"caption" => $pd["caption"],
				"align" => "center"
			));
		}

		$t->define_field(array(
			"name" => "empty",
			"caption" => t("Vali t&uuml;hjaks"),
			"align" => "center"
		));
	}

	function do_psfieldmap($arr)
	{
		// get props from cfgform
		$dat = $this->get_props_from_cfgform($arr);
		if ($dat == PROP_ERROR)
		{
			return $dat;
		}

		$t =&$arr["prop"]["vcl_inst"];
		$this->_init_fieldm_t($t, $dat);

		// now, insert rows for the person object
		$cu = get_instance("cfg/cfgutils");
		$props = $cu->load_properties(array(
			"clid" => CL_CRM_PERSON
		));
		uasort($props, create_function('$a,$b', 'return strcasecmp($a["caption"], $b["caption"]);'));

		$pmap = $arr["obj_inst"]->meta("ps_pmap");

		foreach($props as $pn => $pd)
		{
			$row = array(
				"desc" => $pd["caption"],
				"empty" => html::radiobutton(array(
					"name" => "pmap[empty]",
					"value" => "",
				))
			);

			foreach($dat as $dpn => $dpd)
			{
				$row["f_".$dpn] = html::radiobutton(array(
					"name" => "pmap[$pn]",
					"value" => $dpn,
					"checked" => checked($pmap[$pn] == $dpn)
				));
			}

			$t->define_data($row);
		}

		$t->set_sortable(false);
		return PROP_OK;
	}

	function do_orgfieldmap($arr)
	{
		// get props from cfgform
		$dat = $this->get_props_from_cfgform($arr);
		if ($dat == PROP_ERROR)
		{
			return $dat;
		}

		$t =&$arr["prop"]["vcl_inst"];
		$this->_init_fieldm_t($t, $dat);

		// now, insert rows for the person object
		$cu = get_instance("cfg/cfgutils");
		$props = $cu->load_properties(array(
			"clid" => CL_CRM_COMPANY
		));
		uasort($props, create_function('$a,$b', 'return strcasecmp($a["caption"], $b["caption"]);'));

		$pmap = $arr["obj_inst"]->meta("org_pmap");

		foreach($props as $pn => $pd)
		{
			$row = array(
				"desc" => $pd["caption"],
				"empty" => html::radiobutton(array(
					"name" => "pmap[empty]",
					"value" => "",
				))
			);

			foreach($dat as $dpn => $dpd)
			{
				$row["f_".$dpn] = html::radiobutton(array(
					"name" => "pmap[$pn]",
					"value" => $dpn,
					"checked" => checked($pmap[$pn] == $dpn)
				));
			}

			$t->define_data($row);
		}

		$t->set_sortable(false);
		return PROP_OK;
	}

	function get_props_from_cfgform($arr)
	{
		if (!is_oid($arr["obj_inst"]->prop("data_form")) || !$this->can("view", $arr["obj_inst"]->prop("data_form")))
		{
			$arr["prop"]["error"] = t("Tellija andmete vorm valimata!");
			return PROP_ERROR;
		}

		$cff = get_instance(CL_CFGFORM);
		$ret =  $cff->get_props_from_cfgform(array(
			"id" => $arr["obj_inst"]->prop("data_form")
		));
		return $ret;
	}

	function get_property_map($oc_id, $type)
	{
		$o = obj($oc_id);
		switch($type)
		{
			case "person":
				return array_flip(safe_array($o->meta("ps_pmap")));
				break;

			case "org":
				return array_flip(safe_array($o->meta("org_pmap")));
				break;

			default:
				error::raise(array(
					"id" => "ERR_WRONG_MAP",
					"msg" => sprintf(t("shop_order_center::get_property_map(%s, %s): the options for type are 'person' and 'org'"), $oc_id, $type)
				));
		}
	}

	function get_discount_from_order_data($oc_id, $data)
	{
		$oc = obj($oc_id);
		if (!$oc->prop("data_form_discount"))
		{
			return 0;
		}
		return $data[$oc->prop("data_form_discount")];
	}

	function callback_get_controller_tbl($arr)
	{
		if (!$arr["obj_inst"]->prop("use_controller"))
		{
			return array();
		}
		$ret = array();
		$ot = $this->_get_folder_ot_from_o($arr["obj_inst"]);
		$ol = $ot->to_list();

		$opts = new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_CONTROLLER")));
		$opts = array("" => t("--vali--")) + $opts->names();
		$vals = $arr["obj_inst"]->meta("fld_controllers");
		foreach($ol->arr() as $o)
		{
			$nm = "fld_".$o->id();
			$ret[$nm] = array(
				"name" => $nm,
				"type" => "select",
				"options" => $opts,
				"value" => $vals[$o->id()],
				"caption" => $o->path_str()
			);
		}
		return $ret;
	}

	function _get_folder_ot_from_o($o)
	{
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
		return $ot;
	}

	function save_ctr_t($arr)
	{
		$vals = array();
		foreach(safe_array($arr["request"]) as $k => $v)
		{
			if (substr($k, 0, 4) == 'fld_')
			{
				$vals[substr($k, 4)] = $v;
			}
		}
		$arr["obj_inst"]->set_meta("fld_controllers", $vals);
	}
}
?>
