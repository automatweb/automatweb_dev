<?php

// shop_order_center.aw - Tellimiskeskkond
/*

@tableinfo aw_shop_order_center index=aw_id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_SHOP_ORDER_CENTER relationmgr=yes maintainer=kristo no_comment=1 no_status=1 prop_cb=1

@default group=general

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE table=aw_shop_order_center field=aw_warehouse_id
@caption Ladu

@property cart type=relpicker reltype=RELTYPE_CART table=aw_shop_order_center field=aw_cart_id
@caption Ostukorv

@default table=objects
@default field=meta
@default method=serialize

@property cart_type type=chooser
@caption Ostukorvi t&uuml;&uuml;p

@property multi_items type=checkbox ch_value=1
@caption Ostukorvis v&otilde;ib olla mitu sama ID-ga toodet

@property show_unconfirmed type=checkbox ch_value=1
@caption N&auml;ita tellijale tellimuste nimekirjas ainult kinnitamata tellimusi

@property only_prods type=checkbox ch_value=1
@caption Ostukorvis on tooted ilma pakendite, piltide jms

@property pdf_template type=textbox
@caption PDF Template faili nimi

@property show_prod_and_package type=checkbox ch_value=1
@caption N&auml;ita selgituses toodet/paketti

@property chart_show_template type=select
@caption Ostukorvi vaade template

@property chart_final_template type=select
@caption Ostukorvi l&ouml;ppvaate template

@property mail_template type=select
@caption E-maili template

@property integration_class type=select
@caption Integratsiooni klass


@default group=mail_settings_orderer

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

	@property mail_group_by type=select
	@caption Toodete grupeerimine meilis

	@property mails_sep_by_el type=checkbox ch_value=1
	@caption Saada eraldi meilid vastavalt klassifikaatorile

	@property send_attach type=checkbox ch_value=1
	@caption Lisa meili manusega tellimus


@default group=payment1

	@property web_discount type=textbox size=5
	@caption Veebis allahindlus (%)

	@property data_form_discount type=select
	@caption Allahindluse element andmete vormis

	@property rent_min_amt type=textbox
	@caption J&auml;relmaksu min. summa

	@property rent_prop type=select
	@caption Elemendi

	@property rent_prop_val type=textbox
	@caption v&auml;&auml;rtus j&auml;relmaksuks


@default group=appear_settings

	@property only_active_items type=checkbox ch_value=1
	@caption Ainult aktiivsed tooted

	@property use_controller type=checkbox ch_value=1
	@caption N&auml;itamiseks kasuta kontrollerit

	@property use_cart_controller type=checkbox ch_value=1
	@caption Ostukorvi n&auml;itamiseks kasuta kontrollerit

	@property no_show_cart_contents type=checkbox ch_value=1
	@caption &Auml;ra n&auml;ita korvi kinnitusvaadet

	@property controller type=relpicker reltype=RELTYPE_CONTROLLER
	@caption Vaikimisi n&auml;itamise kontroller

	@property order_show_controller type=relpicker reltype=RELTYPE_CONTROLLER
	@caption Tellimuse n&auml;itamise kontroller

	@property sortbl type=table store=no
	@caption Toodete sorteerimine

	@property grouping type=select
	@caption Toodete grupeerimine

	@property disp_cart_in_web type=checkbox ch_value=1
	@caption Kuva korvi toodete all

	@property show_delivery type=checkbox ch_value=1
	@caption Kuva kohaletoimetamise valikut

	@property no_change_button type=checkbox ch_value=1
	@caption &Auml;ra kuva tellimiskeskkonnas toote k&otilde;rvale "Muuda" nuppu

	@property prods_are_folders type=checkbox ch_value=1
	@caption Veebis tooted on kataloogid


@default group=appear_ctr

	@property controller_tbl type=callback callback=callback_get_controller_tbl store=no
	@caption Kontrollerid kataloogidele

@default group=appear_layout

	@property layoutbl type=table store=no no_caption=1
	@caption Toodete layout


@default group=data_settings

	@property data_form type=relpicker reltype=RELTYPE_ORDER_FORM
	@caption Tellija andmete vorm

	@property data_form_person type=select
	@caption Isiku nime element andmete vormis

	@property data_form_company type=select
	@caption Organisatsiooni nime element andmete vormis

@default group=psfieldmap

	@property psfieldmap type=table store=no
	@caption Vali millised elemendid tellimuse andmete vormis vastavad isukuandmetele

@default group=orgfieldmap

	@property orgfieldmap type=table store=no
	@caption Vali millised elemendid tellimuse andmete vormis vastavad firma andmetele

@default group=payment_settings

	@property bank_payment type=relpicker reltype=RELTYPE_BANK_PAYMENT
	@caption Pangamakse objekt

	@property bank_id type=select
	@caption Panga muutuja

	@property orderer_mail type=select
	@caption Tellija mailiaadressi muutuja

	@property bank_lang type=select
	@caption Panga keele muutuja

@default group=filter_settings

	@property use_filtering type=checkbox ch_value=1
	@caption Filtreeri tooteid

	@property filter_fields_class type=table store=no no_caption=1
	@caption Filtrid integratsiooniklassist

	@property filter_fields_props type=table store=no no_caption=1
	@caption Filtrid toote omadustest

@default group=filter_select

	@property filter_settings_tb type=toolbar store=no no_caption=1
	@property filter_settings type=table store=no no_caption=1

@default group=filter_set_folders

	@property filter_sel_for_folders type=table store=no no_caption=1


@default group=delivery_cfg
	@property delivery_show_controller type=relpicker reltype=RELTYPE_DELIVERY_SHOW_CONTROLLER
	@caption N&auml;itamise kontroller

	@property delivery_save_controller type=relpicker reltype=RELTYPE_DELIVERY_SAVE_CONTROLLER
	@caption Salvestamise kontroller

	@property delivery_exec_controller type=relpicker reltype=RELTYPE_DELIVERY_EXEC_CONTROLLER
	@caption Teostamiselesaatmise kontroller

	@property cart_value_controller type=relpicker reltype=RELTYPE_CART_VALUE_CONTROLLER
	@caption Korvi hinna kontroller


@groupinfo mail_settings caption="Meiliseaded"
	@groupinfo mail_settings_orderer caption="Tellijale" parent=mail_settings
	@groupinfo mail_settings_seller caption="Pakkujale" parent=mail_settings

@groupinfo payment caption="Maksmine"
	@groupinfo payment1 caption="Seaded" parent=payment
	@groupinfo payment_settings caption="Pangamakse seaded" parent=payment

@groupinfo appear caption="N&auml;itamine"
	@groupinfo appear_settings parent=appear caption="Seaded"
	@groupinfo appear_ctr parent=appear caption="Kontrollerid"
	@groupinfo appear_layout parent=appear caption="Layoudid"

@groupinfo data caption="Andmed"
	@groupinfo data_settings caption="Seaded" parent=data
	@groupinfo psfieldmap caption="Isukuandmete kaart" parent=data
	@groupinfo orgfieldmap caption="Firma andmete kaart" parent=data
	@groupinfo delivery_cfg caption="Kohaletoimetamise seaded" parent=data

@groupinfo filter caption="Filtreerimine"
	@groupinfo filter_settings caption="Seaded" parent=filter
	@groupinfo filter_select caption="Koosta filter" parent=filter submit=no
	@groupinfo filter_set_folders caption="Vali kehtivad filtrid" parent=filter

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

@reltype DELIVERY_SHOW_CONTROLLER value=14 clid=CL_FORM_CONTROLLER
@caption kohaletoimetamise n&auml;itamise kontroller

@reltype DELIVERY_SAVE_CONTROLLER value=15 clid=CL_FORM_CONTROLLER
@caption kohaletoimetamise salvestamise kontroller

@reltype DELIVERY_EXEC_CONTROLLER value=16 clid=CL_FORM_CONTROLLER
@caption kohaletoimetamise teostamiselesaatmise kontroller

@reltype CART_VALUE_CONTROLLER value=17 clid=CL_FORM_CONTROLLER
@caption Korvi hinna kontroller

@reltype ORDER_NAME_CTR value=7 clid=CL_FORM_CONTROLLER
@caption tellimuse nime kontroller

@reltype BANK_PAYMENT value=11 clid=CL_BANK_PAYMENT
@caption Pangalingi objekt

@reltype FILTER value=12 clid=CL_SHOP_ORDER_CENTER_FILTER_ENTRY
@caption Toodete filtri sisestus
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

	function callback_mod_tab($arr)
	{
		if ($arr["group"] === "delivery_cfg" and !$arr["obj_inst"]->prop("show_delivery"))
		{
			return false;
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "bank_id":
			case "orderer_mail":
			case "bank_lang":
				$cx = get_instance("cfg/cfgutils");
				$props = $cx->load_class_properties(array(
					"clid" => CL_REGISTER_DATA,
				));
				foreach($props as $p => $dsadsad)
				{
					$prop["options"][$p] = $p;
				}
				break;
			case "cart_type":
				$prop["options"] = array(
					0 => t("Sessionip&otilde;hine"),
					1 => t("Kasutajap&otilde;hine"),
				);
				break;

			case "chart_show_template":
			case "chart_final_template":
			case "mail_template":
				$tm = get_instance("templatemgr");
				$prop["options"] = $tm->template_picker(array(
					"folder" => "applications/shop/shop_order_cart/"
				));
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
//					return PROP_IGNORE;
				}
				$this->do_layoutbl($arr);
				break;

			case "sortbl":
				$this->do_sortbl($arr);
				break;
			case "grouping":
				$prop["options"] = array("" => "" , "parent" => t("Kaust"));
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

	function parse_alias($arr = array())
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

		if ($level > 0 && $parent)
		{
			$ol = new object_list(array(
				"parent" => $parent->id(),
				"class_id" => $conf->prop("prod_tree_clids"),
				"sort_by" => "objects.jrk,objects.created",
				"status" => STAT_ACTIVE
			));
			if (!$ol->count() && $o->prop("prods_are_folders"))
			{
				// list prods for this folder instead of folders
				$ol = new object_list(array(
					"parent" => $parent->id(),
					"class_id" => CL_SHOP_PRODUCT,
					"sort_by" => "objects.jrk,objects.created",
					"status" => STAT_ACTIVE
				));
			}
		}
		else
		{
			$ol = new object_list(array(
				"parent" => $conf->prop("pkt_fld"),
				"class_id" => $conf->prop("prod_tree_clids"),
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

		$sect = $o->id();
		if (aw_ini_get("user_interface.full_content_trans"))
		{
			$sect = aw_global_get("ct_lang_lc")."/".$sect;
		}
		if ($ref === NULL)
		{
			$link =  $this->mk_my_orb("show_items", array("id" => $this->folder_obj->id(), "section" => $sect));
		}
		else
		{
			$link =  $this->mk_my_orb("show_items", array("id" => $ref->id(), "section" => $sect));
		}
		return urldecode($link);
	}

	/** shows shop items

		@attrib name=show_items nologin="1"

		@param id required type=int acl=view
		@param f optional
		@param show_prod optional
		@param section required

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
			$so = obj(aw_global_get("section"));
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

		$so = obj(aw_global_get("section"));
		if ($so->class_id() == CL_SHOP_PRODUCT)
		{
			$pl = array($so);
		}
		else
		if ($soc->prop("prods_are_folders"))
		{
			$pl = array();
		}
		else
		{
			$pl = $wh->get_packet_list(array(
				"id" => $wh_id,
				"parent" => aw_global_get("section"),
				"only_active" => $soc->prop("only_active_items")
			));
		}
		if (!empty($arr["show_prod"]) && $this->can("view", $arr["show_prod"]))
		{
			$pl = array(obj($arr["show_prod"]));
		}

		if (isset($arr["f"]) && is_array($arr["f"]))
		{
			$this->do_filter_packet_list($pl, $arr["f"], $soc);
		}

		$this->do_sort_packet_list($pl, $soc->meta("itemsorts"));

		$section = aw_global_get("section");
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

		if ($soc->prop("disp_cart_in_web"))
		{
			$cart = get_instance(CL_SHOP_ORDER_CART);
			$html .= $cart->pre_finish_order(array(
				"oc" => $soc->id(),
				"section" => aw_global_get("section")
			));
		}

		exit_function("shop_order_center::show_items");
		return $html;
	}

	function get_prod_layout_for_folder($soc, $section)
	{
		if(!$section)
		{
			return false;
		}
		$il = $soc->meta("itemlayouts");
		$_p = obj($section);
		foreach(array_reverse($_p->path()) as $p)
		{
			if (!empty($il[$p->id()]))
			{
				return obj($il[$p->id()]);
			}
		}
		return false;
	}

	function get_prod_table_layout_for_folder($soc, $section)
	{
		$il = $soc->meta("tblayouts");
		if(!$section)
		{
			return false;
		}
		$_p = obj($section);
		foreach(array_reverse($_p->path()) as $p)
		{
			if (!empty($il[$p->id()]))
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
				$total_count - number of total products
				$pl_on_page - list ofprods on the current page - if set, $pl is ignored
	**/
	function do_draw_prods_with_layout($arr)
	{
		extract($arr);
		$soce = aw_global_get("soc_err");
//arr($_SESSION["soc_err"]);
		error::raise_if(!is_object($t_layout), array(
			"id" => "ERR_NO_LAYOUT",
			"msg" => "do_draw_prods_with_layout(): layout not set!"
		));
		$tl_inst = $t_layout->instance();
		$tl_inst->start_table($t_layout, $soc);
		if(!empty($this->web_discount))
		{
			$tl_inst->web_discount = $this->web_discount;
		}
		$xi = 0;
		$l_inst = $layout->instance();
		$l_inst->read_template($layout->prop("template"));

		lc_site_load("shop_order_center", &$this);
		$last_menu = "";
		if (isset($arr["pl_on_page"]))
		{
			$tl_inst->cnt = $tl_inst->per_page * (int)$_GET["sptlp"];
			$this->_init_draw_prod();
			foreach($arr["pl_on_page"] as $o)
			{
				$this->_draw_one_prod($o, $tl_inst, $layout, $soc, $l_inst, $soce, ifset($arr, "prod_link_cb"));
			}
			$tl_inst->cnt = $arr["total_count"];
		}
		else
		{
			foreach($pl as $o)
			{
				$tl_inst->cnt++;
				if ($tl_inst->is_on_cur_page())
				{
					$this->_draw_one_prod($o, $tl_inst, $layout, $soc, $l_inst, $soce, ifset($arr, "prod_link_cb"));
					$tl_inst->cnt--;
				}
				$this->last_menu =  $o->parent();
			}
			$tl_inst->cnt = count($pl);
		}
		return $tl_inst->finish_table();
	}

	private function _init_draw_prod()
	{
		$this->xi = 0;
		$this->last_menu = "";
	}

	private function _draw_one_prod($o, $tl_inst, $layout, $soc, $l_inst, $soce, $prod_link_cb)
	{
		$i = $o->instance();
		$oid = $o->id();
		$tl_inst->add_product($i->do_draw_product(array(
			"bgcolor" => isset($this->xi) && $this->xi % 2 ? "cartbgcolor1" : "cartbgcolor2",
			"prod" => $o,
			"layout" => $layout,
			"oc_obj" => $soc,
			"l_inst" => $l_inst,
			"quantity" => $soce[$oid]["ordered_num_enter"],
			"is_err" => $soce[$oid]["is_err"],
			"prod_link_cb" => $prod_link_cb,
			"last_product_menu" => isset($this->last_menu) ? $this->last_menu : NULL,
			"soce" => $soce,
		)));
		$this->xi++;
		$this->last_menu =  $o->parent();
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
		lc_site_load("shop_order_center", &$this);
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
			$this->vars_safe(array(
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

		lc_site_load("shop_order_center", &$this);

		foreach($ool->arr() as $ord)
		{
			$this->vars_safe(array(
				"name" => $ord->name(),
				"tm" => $ord->created(),
				"sum" => number_format($ord->prop("sum"), 2),
				"view_link" => obj_link($ord->id()),
				"id" => $ord->id()
			));
			$l .= $this->parse("LINE2");
		}


		$this->vars_safe(array(
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

	function do_sort_packet_list(&$pl, $itemsorts,$groups=null)
	{
		if (!is_array($itemsorts))
		{
			return;
		}
		$this->__is = $itemsorts;
		if($groups=="parent")
		{
			$items = array();
			$result = array();
			$menu = null;
			foreach($pl as $key => $item)
			{
				if($item->parent() != $menu)
				{
					if(sizeof($items))
					{
						usort($items, array(&$this, "__is_sorter"));
						$result = array_merge($result ,$items);
						$items = array($key => $item);
						$menu = $item->parent();
						continue;
					}
				}
				$menu = $item->parent();
				$items[$key] = $item;
			}
			if(sizeof($items))
			{
				usort($items, array(&$this, "__is_sorter"));
				$result = array_merge($result,$items);
			}
			$pl = $result;
			return ;
		}
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
		$tmp_obj = obj();
		$tmp_obj->set_class_id($class_id);

		$class_i->cfgform_id = $cff->id();
		$all_ps = $class_i->parse_properties(array(
			"properties" => &$all_ps,
			"obj_inst" => $tmp_obj
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

		$cart = get_instance(CL_SHOP_ORDER_CART)->get_cart($oc);

		$override_params = array("rows" , "cols" , "caption");
		// rewrite names as user_data[prop]
		foreach($cf_ps as $pn => $pd)
		{
			if ($pn == "is_translated" || $pn == "needs_translation")
			{
				continue;
			}
			$ret[$pn] = $all_ps[$pn];
			foreach($override_params as $override_param)
			{
				if($pd[$override_param])
				{
					$ret[$pn][$override_param] = $pd[$override_param];
				}
			}

			$ret[$pn]["name"] = "user_data[$pn]";

			if (aw_global_get("uid") != "")
			{
				if (($fld = array_search($pn, $ps_pmap)))
				{
					$cud[$pn] = $cur_p->prop($fld);
				}

				if (($fld = array_search($pn, $org_pmap)))
				{
					$cud[$pn] = $cur_co->prop($fld);
				}
			}

			if (!empty($cart["user_data"][$pn]))
			{
				$ret[$pn]["value"] = $cart["user_data"][$pn];
			}
			else
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
				"desc" => $pd["caption"]." [$pn]",
				"empty" => html::radiobutton(array(
					"name" => "pmap[$pn]",
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
					"name" => "pmap[$pn]",
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

	function _get_integration_class($arr)
	{
		$arr["prop"]["options"] = array("" => t("--vali--"));
		$clss = aw_ini_get("classes");
		foreach(class_index::get_classes_by_interface("shop_order_center_integrator") as $class_name)
		{
			$clid = clid_for_name($class_name);
			$arr["prop"]["options"][$clid] = $clss[$clid]["name"];
		}

		foreach($clss as $clid => $clinf)
		{
			if ($clinf["site_class"] == 1)
			{
				// check if site class implements interface
				$anal = get_instance("aw_code_analyzer");
				$data = $anal->analyze_file(aw_ini_get("site_basedir")."/classes/".$clinf["file"].".".aw_ini_get("ext"), true);
				foreach($data["classes"] as $class_name => $class_data)
				{
					if (in_array("shop_order_center_integrator", $class_data["implements"]))
					{
						$arr["prop"]["options"][$clid] = $clinf["name"];
					}
				}
			}
		}
	}

	function _get_filter_fields_class($arr)
	{
		if (!is_class_id($ic = $arr["obj_inst"]->prop("integration_class")))
		{
			return PROP_IGNORE;
		}
		$t = $arr["prop"]["vcl_inst"];

		$class_filter_fields = $arr["obj_inst"]->meta("class_filter_fields");

		$this->_init_filter_fields_table($t);
		$clss = aw_ini_get("classes");
		foreach(get_instance($clss[$ic]["file"])->get_filterable_fields() as $field_name => $field_caption)
		{
			$t->define_data(array(
				"field" => $field_caption,
				"select" => html::checkbox(array(
					"name" => "class_filter_select[$field_name]",
					"value" => 1,
					"checked" => $class_filter_fields[$field_name] == 1
				))
			));
		}
		$t->set_caption(t("Filtrid integratiooniklassist"));
	}

	function _set_filter_fields_class($arr)
	{
		$arr["obj_inst"]->set_meta("class_filter_fields", $arr["request"]["class_filter_select"]);
	}

	function _get_filter_fields_props($arr)
	{
		$t = $arr["prop"]["vcl_inst"];

		$prop_filter_fields = $arr["obj_inst"]->meta("prop_filter_fields");

		$this->_init_filter_fields_table($t);
		foreach(obj()->set_class_id(CL_SHOP_PRODUCT)->get_property_list() as $field_name => $field_data)
		{
			$t->define_data(array(
				"field" => $field_data["caption"]." [$field_name]",
				"select" => html::checkbox(array(
					"name" => "prop_filter_select[$field_name]",
					"value" => 1,
					"checked" => $prop_filter_fields[$field_name] == 1
				))
			));
		}
		$t->set_caption(t("Filtrid toote omadustest"));
	}

	function _set_filter_fields_props($arr)
	{
		$arr["obj_inst"]->set_meta("prop_filter_fields", $arr["request"]["prop_filter_select"]);
	}

	private function _init_filter_fields_table($t)
	{
		$t->define_field(array(
			"name" => "field",
			"caption" => t("V&auml;li"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "select",
			"caption" => t("Vali"),
			"align" => "center"
		));
	}

	function _get_filter_settings($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->table_from_ol(
			new object_list($arr["obj_inst"]->connections_from(array("type" => "RELTYPE_FILTER"))),
			array("name"),
			CL_SHOP_ORDER_CENTER_FILTER_ENTRY
		);
	}

	function _get_filter_settings_tb($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->add_new_button(array(CL_SHOP_ORDER_CENTER_FILTER_ENTRY), $arr["obj_inst"]->id(),12, array("set_oc" => $arr["obj_inst"]->id()));
		$t->add_delete_button();
	}

	private function _init_filter_sel_table($t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Kataloog")
		));

		$t->define_field(array(
			"name" => "sel_filter",
			"caption" => t("Kehtiv filter"),
			"align" => "center"
		));
	}

	function _set_filter_sel_for_folders($arr)
	{
		$arr["obj_inst"]->filter_set_active_by_folder($arr["request"]["sel_filter"]);
	}

	function _get_filter_sel_for_folders($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_filter_sel_table($t);

		$wh = get_instance(CL_SHOP_WAREHOUSE);

		$o = $arr["obj_inst"];
		$this->_get_folder_ot_from_o($o);

		$ol = new object_list($o->connections_from(array("type" => "RELTYPE_FILTER")));
		$this->filter_sel = array("" => t("--vali--")) + $ol->names();
		$this->selected_filters = $o->meta("filter_by_folder");

		$this->_oinst = &$o;

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
		$this->filter_table_ot_cb($o, $t);

		$ot = new object_tree(array(
			"class_id" => CL_MENU,
			"parent" => $o->id(),
		));

		$ot->foreach_cb(array(
			"func" => array(&$this, "filter_table_ot_cb"),
			"param" => &$t,
			"save" => false
		));

		$t->sort_by();
	}

	function filter_table_ot_cb(&$o, &$t)
	{
		$t->define_data(array(
			"name" => $o->path_str(),
			"sel_filter" => html::select(array(
				"name" => "sel_filter[".$o->id()."]",
				"options" => $this->filter_sel,
				"selected" => $this->selected_filters[$o->id()]
			)),
		));
		$t->set_default_sortby("name");
	}

	function do_filter_packet_list(&$pl, $f, $soc)
	{
//die(dbg::dump($f));
		$filter_ic = array();
		$filter_prod = array();
		foreach($f as $filter_name => $filter_value)
		{
			if (!is_array($filter_value) || !count($filter_value))
			{
				continue;
			}

			list($type, $name) = explode("::", $filter_name);
			if ($type == "ic")
			{
				$filter_ic[$name] = $filter_value;
			}
			else
			{
				$filter_prod[$name] = $filter_value;
			}
		}
		if (count($filter_ic) && count($pl))
		{
			$inst = $soc->get_integration_class_instance();
			$inst->apply_filter_to_product_list($pl, $filter_ic);
		}
		if (count($filter_prod) && count($pl))
		{
			$this->apply_filter_to_product_list($pl, $filter_prod);
		}
	}

	function apply_filter_to_product_list(&$pl, $filter_prod)
	{
		enter_function("shop_product::apply_filter_to_product_list");
		$filt = array(
			"oid" => $pl,
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_SHOP_PRODUCT
		);
		foreach($filter_prod as $prop => $vals)
		{
			$filt[$prop] = array_keys($vals);
		}
		$ol = new object_list($filt);
		$pl = $this->make_keys($ol->ids());
		exit_function("shop_product::apply_filter_to_product_list");
	}
}

/** If you want to create a class that can be used in shop order center to filter products and other things, then implement this interface **/
interface shop_order_center_integrator
{
	/** Returns a list of fields that can be used for filtering
		@attrib api=1

		@returns
			array { filter_field => filter field caption, ... }
	**/
	public function get_filterable_fields();

	/** Returns a list of all values for the given filter
		@attrib api=1 params=pos

		@param filter_name required type=string
			The name of the filter field to return values for

		@returns
			array { filter_value => filter_value_caption, ... }
	**/
	public function get_all_filter_values($filter_name);

	/** Applies the given filter to the product list
		@attrib api=1 params=pos

		@param pl required type=array
			Array of produxts to filter { index => product_obj, ... }

		@param filter_prod type=array
			The filter array { filter_name => array { filter_value => 1 }, ... }
	**/
	public function apply_filter_to_product_list(&$pl, $filter_prod);
}
?>
