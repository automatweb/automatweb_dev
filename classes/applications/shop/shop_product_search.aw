<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_product_search.aw,v 1.5 2005/06/27 11:01:35 kristo Exp $
// shop_product_search.aw - Lao toodete otsing 
/*

@classinfo syslog_type=ST_SHOP_PRODUCT_SEARCH relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta 
@default method=serialize

	@property wh type=relpicker reltype=RELTYPE_WAREHOUSE automatic=1 
	@caption Ladu

	@property oc type=relpicker reltype=RELTYPE_OC automatic=1 
	@caption Tellimiskeskkond

	@property objs_in_res type=select 
	@caption Tulemuseks on 

@default group=s_form
	@property s_form type=table no_caption=1

	@property search_btn_caption type=textbox 
	@caption Otsi nupu tekst

@default group=s_res
	@property s_tbl type=table no_caption=1

@default group=s_res_ctr

	@property s_tbl_ctr type=relpicker reltype=RELTYPE_CONTROLLER
	@caption Tulemuste andmete n&auml;itamise kontroller

@default group=search
	@property search_form type=callback callback=callback_gen_search_form

	@property s_res type=text no_caption=1


@groupinfo s_form caption="Koosta otsinguvorm"
@groupinfo s_res caption="Koosta tulemuste tabel"
@groupinfo s_res_ctr caption="Kontrollerid"
@groupinfo search caption="Otsi" submit_method=get submit=no

@reltype WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption ladu

@reltype CONTROLLER value=2 clid=CL_FORM_CONTROLLER
@caption kontroller

@reltype OC value=3 clid=CL_SHOP_ORDER_CENTER
@caption tellimiskeskkond
*/

class shop_product_search extends class_base
{
	function shop_product_search()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_product_search",
			"clid" => CL_SHOP_PRODUCT_SEARCH
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "s_form":
				$this->_s_form($arr);
				break;

			case "s_tbl":
				$this->_s_tbl($arr);
				break;

			case "s_res":
				$this->_s_res($arr);
				break;

			case "s_res_tb":
				$this->_s_res_tb($arr);
				break;

			case "objs_in_res":
				$prop["options"] = array(
					CL_SHOP_PACKET => "Paketid",
					CL_SHOP_PRODUCT => "Tooted",
					CL_SHOP_PRODUCT_PACKAGING => "Pakendid"
				);
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
			case "s_form":
				$this->_save_s_form($arr);
				break;

			case "s_tbl":
				$this->_save_s_tbl($arr);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	/** 

		@attrib name=show nologin="1"

		@param id required type=int acl=view
	**/
	function show($arr)
	{
		aw_session_set("no_cache", 1);
		$o = obj($arr["id"]);

		$request = array(
			"MAX_FILE_SIZE" => $_GET["do_search"],
			"s" => $_GET["s"]
		);

		$props =  $this->callback_gen_search_form(array(
			"obj_inst" => $o,
			"request" => $request
		));

		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		foreach($props as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1
		));

		$prop = array();
		$this->_s_res(array(
			"obj_inst" => &$o,
			"request" => $request,
			"prop" => &$prop
		));
		$table = $prop["value"];

		$this->read_template("show.tpl");
		$this->vars(array(
			"form" => $html,
			"section" => aw_global_get("section"),
			"table" => $table,
			"reforb" => $this->mk_reforb("submit_add_cart", array(
				"oc" => $o->prop("oc"),
			), "shop_order_cart")
		));
		return $this->parse();
	}

	function _init_s_form_t(&$t)
	{
		$t->define_field(array(
			"name" => "class",
			"caption" => t("Klass"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "prop",
			"caption" => t("Omadus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "in_form",
			"caption" => t("Vormis?"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "caption",
			"caption" => t("Tekst"),
			"align" => "center"
		));
	}

	function _s_form($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_s_form_t($t);

		$props = $this->_get_prod_props($arr["obj_inst"]);
		$dat = $arr["obj_inst"]->meta("s_form");

		$clss = aw_ini_get("classes");
		foreach($props as $clid => $ps)
		{
			foreach($ps as $pn => $pd)
			{
				// make property name string
				$capts = array();
				foreach($pd as $real_pd)
				{
					$capts[] = $real_pd["caption"];
				}

				
				$t->define_data(array(
					"class" => $clss[$clid]["name"],
					"prop" => join("/", array_unique($capts))." ($pn)",
					"in_form" => html::checkbox(array(
						"name" => "dat[$clid][$pn][in_form]",
						"value" => 1,
						"checked" => $dat[$clid][$pn]["in_form"] == 1
					)),
					"caption" => html::textbox(array(
						"name" => "dat[$clid][$pn][caption]",
						"value" => isset($dat[$clid][$pn]) ? $dat[$clid][$pn]["caption"] : $capts[0]
					)),
					"ord" => html::textbox(array(
						"name" => "dat[$clid][$pn][ord]",
						"value" => $dat[$clid][$pn]["ord"],
						"size" => 5
					))
				));
			}
		}
		$t->set_sortable(false);
	}

	function _get_prod_props($o)
	{
		// get warehouse from object
		if (!is_oid($o->prop("wh")) || !$this->can("view", $o->prop("wh")))
		{
			return array();
		}
		$wh = obj($o->prop("wh"));
		$wh_i = $wh->instance();

		$props = array(
			CL_SHOP_PACKET => array(),
			CL_SHOP_PRODUCT => array(),
			CL_SHOP_PRODUCT_PACKAGING => array()
		);

		$cf = get_instance(CL_CFGFORM);

		// get product props from warehouse
		$cfgforms = $wh_i->get_prod_add_config_forms(array("warehouse" => $wh->id()));
		foreach($cfgforms as $formid)
		{
			$ps = $cf->get_props_from_cfgform(array("id" => $formid));
			foreach($ps as $pn => $pd)
			{
				$props[CL_SHOP_PRODUCT][$pn][] = $pd;
			}
		}

		// get packaging props
		$cfgforms = $wh_i->get_prod_packaging_add_config_forms(array("warehouse" => $wh->id()));
		foreach($cfgforms as $formid)
		{
			$ps = $cf->get_props_from_cfgform(array("id" => $formid));
			foreach($ps as $pn => $pd)
			{
				$props[CL_SHOP_PRODUCT_PACKAGING][$pn][] = $pd;
			}
		}

		// get packet props
		// currently no cfgforms can be set for packets, so return default props from class
		$cu = get_instance("cfg/cfgutils");
		$ps = $cu->load_properties(array(
			"clid" => CL_SHOP_PACKET,
			"file" => "shop_packet"
		));
		foreach($ps as $pn => $pd)
		{
			$props[CL_SHOP_PACKET][$pn][] = $pd;
		}

		return $props;
	}

	function _save_s_form($arr)
	{
		$arr["obj_inst"]->set_meta("s_form", $arr["request"]["dat"]);
	}

	function _init_s_tbl_t(&$t)
	{
		$t->define_field(array(
			"name" => "class",
			"caption" => t("Klass"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "prop",
			"caption" => t("Omadus"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "in_form",
			"caption" => t("Tabelis?"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "ord",
			"caption" => t("J&auml;rjekord"),
			"align" => "center"
		));

		$t->define_field(array(
			"name" => "caption",
			"caption" => t("Tulba pealkiri"),
			"align" => "center"
		));
	}

	function _s_tbl($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_s_tbl_t($t);

		$props = $this->_get_prod_props($arr["obj_inst"]);
		$dat = $arr["obj_inst"]->meta("s_tbl");

		$clss = aw_ini_get("classes");
		foreach($props as $clid => $ps)
		{
			foreach($ps as $pn => $pd)
			{
				// make property name string
				$capts = array();
				foreach($pd as $real_pd)
				{
					$capts[] = $real_pd["caption"];
				}

				
				$t->define_data(array(
					"class" => $clss[$clid]["name"],
					"prop" => join("/", array_unique($capts))." ($pn)",
					"in_form" => html::checkbox(array(
						"name" => "dat[$clid][$pn][in_form]",
						"value" => 1,
						"checked" => $dat[$clid][$pn]["in_form"] == 1
					)),
					"caption" => html::textbox(array(
						"name" => "dat[$clid][$pn][caption]",
						"value" => isset($dat[$clid][$pn]) ? $dat[$clid][$pn]["caption"] : $capts[0]
					)),
					"ord" => html::textbox(array(
						"name" => "dat[$clid][$pn][ord]",
						"value" => $dat[$clid][$pn]["ord"],
						"size" => 5
					))
				));
			}
		}
		$t->set_sortable(false);
	}

	function _save_s_tbl($arr)
	{
		$arr["obj_inst"]->set_meta("s_tbl", $arr["request"]["dat"]);
	}

	function callback_gen_search_form($arr)
	{
		$o = $arr["obj_inst"];

		$ret = array();	

		$cu = get_instance("cfg/cfgutils");

		$form_props = safe_array($o->meta("s_form"));
		foreach($form_props as $clid => $ps)
		{
			$r_props = $cu->load_properties(array("clid" => $clid));
			foreach($ps as $pn => $pd)
			{
				if ($pd["in_form"] == 1)
				{
					$nm = "s[$clid][".$pn."]";
					$ret[$nm] = array(
						"name" => $nm,
						"type" => $r_props[$pn]["type"] == "checkbox" ? "checkbox" : "textbox",
						"caption" => $pd["caption"],
						"store" => "no",
						"value" => $arr["request"]["s"][$clid][$pn],
						"ch_value" => 1,
						"_ord" => $pd["ord"]
					);
				}
			}
		}
		uasort($ret, create_function('$a,$b', 'return $a["_ord"] - $b["_ord"];'));

		$ret["do_search"] = array(
			"name" => "do_search",
			"type" => "submit",
			"caption" => $arr["obj_inst"]->prop("search_btn_caption")
		);

		return $ret;
	}

	function _s_res($arr)
	{
		classload("vcl/table");
		$t = new aw_table(array("layout" => "generic"));

		$cols = safe_array($arr["obj_inst"]->meta("s_tbl"));
		$flds = array();
		foreach($cols as $clid => $cold)
		{
			foreach($cold as $coln => $coli)
			{
				if ($coli["in_form"] == 1)
				{
					$flds[] = array(
						"name" => $clid."_".$coln,
						"caption" => $coli["caption"],
						"_ord" => $coli["ord"]
					);
				}
			}
		}
		uasort($flds, create_function('$a,$b', 'return $a["_ord"] - $b["_ord"];'));
		foreach($flds as $fld)
		{
			$t->define_field($fld);
		}
		$t->define_field(array(
			"name" => "add_to_cart",
			"caption" => t("Vali")
		));

		$ctr = NULL;
		if (is_oid($ctr_id = $arr["obj_inst"]->prop("s_tbl_ctr")) && $this->can("view", $ctr_id))
		{
			$ctr = $ctr_id;
		}
		$ctr_i = get_instance(CL_FORM_CONTROLLER);

		if ($arr["request"]["MAX_FILE_SIZE"] != "")
		{
			$results = $this->get_search_results($arr["obj_inst"], $arr["request"]["s"]);
			foreach($results as $o)
			{
				$clid = $o->class_id();
				$data = array();

				$packet = $prod = $pk = NULL;

				switch($clid)
				{
					case CL_SHOP_PACKET:
						$packet = $o;
						$prod = $o->get_first_obj_by_reltype("RELTYPE_PRODUCT");
						if ($prod)
						{
							$pk = $prod->get_first_obj_by_reltype("RELTYPE_PACKAGING");
						}
						if (!$prod)
						{
							$prod = obj();
							$pk = obj();
						}
						else
						if (!$pk)
						{
							$pk = obj();
						}
						break;
					
					case CL_SHOP_PRODUCT:
						$prod = $o;
						$packet = reset($o->connections_to(array(
							"from.class_id" => CL_SHOP_PACKET
						)));
						if ($packet)
						{
							$packet = $packet->from();
						}
						$pk = $prod->get_first_obj_by_reltype("RELTYPE_PACKAGING");
						if (!$packet)
						{
							$packet = obj();
						}
						if (!$pk)
						{
							$pk = obj();
						}
						break;

					case CL_SHOP_PRODUCT_PACKAGING:
						$pk = $o;
						$prod_c = reset($pk->connections_to(array(
							"from.class_id" => CL_SHOP_PRODUCT
						)));
						if ($prod_c)
						{
							$prod = $prod_c->from();
						}
						if (!$prod)
						{
							$prod = obj();
							$packet = obj();
						}
						else
						{
							$packet_c = reset($prod->connections_to(array(
								"from.class_id" => CL_SHOP_PACKET
							)));
							if ($packet_c)
							{
								$packet = $packet_c->from();
							}
							else
							{
								$packet = obj();
							}
						}
						break;
				}

				foreach(safe_array($cols[CL_SHOP_PACKET]) as $coln => $cold)
				{
					if ($cold["in_form"] == 1)
					{
						$data[CL_SHOP_PACKET."_".$coln] = $packet->prop_str($coln);
					}
				}
				foreach(safe_array($cols[CL_SHOP_PRODUCT]) as $coln => $cold)
				{
					if ($cold["in_form"] == 1)
					{
						$data[CL_SHOP_PRODUCT."_".$coln] = $prod->prop_str($coln);
					}
				}
				foreach(safe_array($cols[CL_SHOP_PRODUCT_PACKAGING]) as $coln => $cold)
				{
					if ($cold["in_form"] == 1)
					{
						$data[CL_SHOP_PRODUCT_PACKAGING."_".$coln] = $pk->prop_str($coln);
					}
				}
				$data[CL_SHOP_PACKET."_oid"] = $packet->id();
				$data[CL_SHOP_PRODUCT."_oid"] = $prod->id();
				$data[CL_SHOP_PRODUCT_PACKAGING."_oid"] = $pk->id();
				$data["add_to_cart"] = html::checkbox(array(
					"name" => "add_to_cart[".$data[$arr["obj_inst"]->prop("objs_in_res")."_oid"]."]",
					"value" => 1
				));
				if ($ctr)
				{
					$ctr_i->eval_controller_ref($ctr, $cols, $data, $data);
				}
				$t->define_data($data);
			}
		}
		$html = $t->draw();
		$arr["prop"]["value"] = $html;
	}

	function get_search_results($o, $params)
	{
		$wh_i = get_instance(CL_SHOP_WAREHOUSE);
		list($main_fld, $subs) = $wh_i->get_packet_folder_list(array("id" => $o->prop("wh")));
		$folders = $subs->ids();
		$folders[] = $main_fld->id();

		$res_type = $o->prop("objs_in_res");
		$filt = array(
			"parent" => $folders,
			"class_id" => $res_type,
		);

		foreach($params as $clid => $opts)
		{
			if ($clid == "_fulltext")
			{
				$this->_insert_ft_search($o, $params, $opts, $filt);
				continue;
			}
			
			foreach($opts as $pn => $pv)
			{
				if ($pv == "")
				{
					continue;
				}

				$v = "%".$pv."%";
				// now, based on the result object we must calc the way to search
				$this->_get_filt_param($clid, $res_type, $pn, $v, &$filt);
			}
		}
		$ol = new object_list($filt);
		return $ol->arr();
	}

	function _get_filt_param($clid, $res_type, $pn, $v, &$filt)
	{
		if ($clid == CL_SHOP_PACKET)
		{
			switch($res_type)
			{	
				case CL_SHOP_PACKET:
					$filt[$pn] = $v;
					break;

				case CL_SHOP_PRODUCT:
					$filt["CL_SHOP_PACKET.RELTYPE_PRODUCT.$pn"] = $v;
					break;

				case CL_SHOP_PRODUCT_PACKAGING:
					$filt["CL_SHOP_PACKET.RELTYPE_PRODUCT.RELTYPE_PACKAGING.$pn"] = $v;
					break;
			}
		}
		else
		if ($clid == CL_SHOP_PRODUCT)
		{
			switch($res_type)
			{	
				case CL_SHOP_PACKET:
					break;

				case CL_SHOP_PRODUCT:
					$filt[$pn] = $v;
					break;

				case CL_SHOP_PRODUCT_PACKAGING:
					$filt["CL_SHOP_PRODUCT.RELTYPE_PACKAGING.$pn"] = $v;
					break;
			}
		}
		else
		if ($clid == CL_SHOP_PRODUCT_PACKAGING)
		{
			switch($res_type)
			{	
				case CL_SHOP_PACKET:
					break;

				case CL_SHOP_PRODUCT:
					break;

				case CL_SHOP_PRODUCT_PACKAGING:
					$filt[$pn] = $v;
					break;
			}
		}
	}

	function _insert_ft_search($o, $params, $str, &$filt)
	{
		$cu = get_instance("cfg/cfgutils");

		$ftf = array();

		$form_props = safe_array($o->meta("s_form"));
		foreach($form_props as $clid => $ps)
		{
			$r_props = $cu->load_properties(array("clid" => $clid));
			foreach($ps as $pn => $pd)
			{
				if ($pd["in_form"] == 1)
				{
					$this->_get_filt_param($clid, $o->prop("objs_in_res"), $pn, "%".$str."%", $ftf);
				}
			}
		}
		$filt[] = new object_list_filter(array(
			"logic" => "OR",
			"conditions" => $ftf
		));
	}

	function scs_get_search_results($arr)
	{
		// emulate fulltext search
		return array(1); //$this->get_search_results(obj($arr["group"]), array("_fulltext" => $arr["str"]));
	}

	function scs_display_search_results($arr)
	{
		$request = array(
			"MAX_FILE_SIZE" => 1,
			"s" => array("_fulltext" => $arr["str"])
		);

		$o = obj($arr["group"]);
		$props = $this->callback_gen_search_form(array(
			"obj_inst" => $o,
			"request" => $request
		));

		$htmlc = get_instance("cfg/htmlclient");
		$htmlc->start_output();
		foreach($props as $pn => $pd)
		{
			$htmlc->add_property($pd);
		}
		$htmlc->finish_output();

		$html = $htmlc->get_result(array(
			"raw_output" => 1
		));

		$prop = array("value" => "");
		$arr = array(
			"obj_inst" => $o,
			"request" => $request,
			"prop" => &$prop
		);
		$this->_s_res($arr);
		$table =  $arr["prop"]["value"];

		$this->read_template("show_scs.tpl");
		$this->vars(array(
			"form" => $html,
			"section" => aw_global_get("section"),
			"table" => $table,
			"reforb" => $this->mk_reforb("submit_add_cart", array(
				"oc" => $o->prop("oc"),
			), "shop_order_cart"),
			"s_ro" => $this->mk_reforb("show", array(
				"id" => $o->id(),
				"no_reforb" => 1
			))
		));
		return $this->parse();
	}
}
?>
