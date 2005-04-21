<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_product_search.aw,v 1.1 2005/04/21 14:23:55 kristo Exp $
// shop_product_search.aw - Lao toodete otsing 
/*

@classinfo syslog_type=ST_SHOP_PRODUCT_SEARCH relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default field=meta 
@default method=serialize

	@property wh type=relpicker reltype=RELTYPE_WAREHOUSE automatic=1 
	@caption Ladu

	@property objs_in_res type=select 
	@caption Tulemuseks on 

@default group=s_form
	@property s_form type=table no_caption=1

	@property search_btn_caption type=textbox 
	@caption Otsi nupu tekst

@default group=s_res
	@property s_tbl type=table no_caption=1

@default group=search
	@property search_form type=callback callback=callback_gen_search_form

	@property s_res type=table no_caption=1

@groupinfo s_form caption="Koosta otsinguvorm"
@groupinfo s_res caption="Koosta tulemuste tabel"
@groupinfo search caption="Otsi" submit_method=get submit=no

@reltype WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption ladu

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

			case "objs_in_res":
				$prop["options"] = array(
					CL_SHOP_PACKET => "Paketid",
					CL_SHOP_PRODUCT => "Tooted",
					CL_SHOP_PACKAGING => "Pakendid"
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

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
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

		$form_props = safe_array($o->meta("s_form"));
		foreach($form_props as $clid => $ps)
		{
			foreach($ps as $pn => $pd)
			{
				if ($pd["in_form"] == 1)
				{
					$nm = "s[$clid][".$pn."]";
					$ret[$nm] = array(
						"name" => $nm,
						"type" => "textbox",
						"caption" => $pd["caption"],
						"store" => "no",
						"value" => $arr["request"]["s"][$clid][$pn]
					);
				}
			}
		}

		$ret["do_search"] = array(
			"name" => "do_search",
			"type" => "submit",
			"caption" => $arr["obj_inst"]->prop("search_btn_caption")
		);

		return $ret;
	}

	function _s_res($arr)
	{
		$t =& $arr["prop"]["vcl_inst"];

		$cols = safe_array($arr["obj_inst"]->meta("s_tbl"));
		foreach($cols as $clid => $cold)
		{
			foreach($cold as $coln => $coli)
			{
				if ($coli["in_form"] == 1)
				{
					$t->define_field(array(
						"name" => $clid."_".$coln,
						"caption" => $coli["caption"]
					));
				}
			}
		}

		if ($arr["request"]["MAX_FILE_SIZE"] != "")
		{
			$results = $this->get_search_results($arr["obj_inst"], $arr["request"]["s"]);
			foreach($results as $o)
			{
				$clid = $o->class_id();
				$data = array();
				foreach($cols[$clid] as $coln => $cold)
				{
					$data[$clid."_".$coln] = $o->prop($coln);
				}
				$t->define_data($data);
			}
		}
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
			foreach($opts as $pn => $pv)
			{
				if ($pv == "")
				{
					continue;
				}

				$v = "%".$pv."%";
				// now, based on the result object we must calc the way to search
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
		}
		$ol = new object_list($filt);
		return $ol->arr();
	}
}
?>
