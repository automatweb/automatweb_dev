<?php
/*
@classinfo syslog_type=ST_WAREHOUSE_IMPORT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=smeedia
@tableinfo aw_warehouse_import master_index=brother_of master_table=objects index=aw_oid

@default table=aw_warehouse_import
@default group=general_general

	@property data_source type=select table=objects field=meta method=serialize
	@caption Andmeallikas

@default group=aw_warehouses 

	@property aw_warehouses_tb type=toolbar store=no no_caption=1
	@caption AW Laod toolbar

	@property aw_warehouses type=table store=no no_caption=1
	@caption AW Laod

@default group=import_warehouses 

	@property config_table type=table store=no no_caption=1
	@caption Seaded

@default group=import_timing

	@layout timing_prods_lay type=vbox area_caption=Toodete&nbsp;impordi&nbsp;ajastus closeable=1

		@property timing_prods type=releditor reltype=RELTYPE_PROD_REPEATER use_form=emb rel_id=first field=aw_timing_prods parent=timing_prods_lay
		@caption Toodete impordi ajastus

	@layout timing_prices_lay type=vbox area_caption=Hindade&nbsp;impordi&nbsp;ajastus closeable=1

		@property timing_prices type=releditor reltype=RELTYPE_PRICES_REPEATER use_form=emb rel_id=first field=aw_timing_prices parent=timing_prices_lay
		@caption Hindade impordi ajastus

	@layout timing_amounts_lay type=vbox area_caption=Koguste&nbsp;impordi&nbsp;ajastus closeable=1

		@property timing_amounts type=releditor reltype=RELTYPE_AMOUNTS_REPEATER use_form=emb rel_id=first field=aw_timing_amounts parent=timing_amounts_lay
		@caption Koguste impordi ajastus

	@layout timing_price_lists_lay type=vbox area_caption=Hinnakirjade&nbsp;impordi&nbsp;ajastus closeable=1

		@property timing_price_lists type=releditor reltype=RELTYPE_PRICE_LISTS_REPEATER use_form=emb rel_id=first field=aw_timing_price_lists parent=timing_price_lists_lay
		@caption Hinnakirjade impordi ajastus

@default group=product_status

	@layout stat_prods_lay type=vbox area_caption=Toodete&nbsp;impordi&nbsp;staatus closeable=1

		@property product_status type=text store=no no_caption=1 parent=stat_prods_lay

@default group=product_prices

	@layout stat_prices_lay type=vbox area_caption=Hindade&nbsp;impordi&nbsp;staatus closeable=1

		@property prices_status type=text store=no no_caption=1 parent=stat_prices_lay

@default group=product_amounts

	@layout stat_amounts_lay type=vbox area_caption=Koguste&nbsp;impordi&nbsp;staatus closeable=1

		@property amounts_status type=text store=no no_caption=1 parent=stat_amounts_lay

@default group=pricelists

	@layout stat_pricelists_lay type=vbox area_caption=Hinnakirjade&nbsp;impordi&nbsp;staatus closeable=1

		@property pricelists_status type=text store=no no_caption=1 parent=stat_pricelists_lay


	@groupinfo general_general parent=general caption="&Uuml;ldine"
	@groupinfo aw_warehouses parent=general caption="AW Laod"
	@groupinfo import_warehouses parent=general caption="Imporditavad laod"

@groupinfo import_status caption="Importide staatus"

	@groupinfo product_status caption="Toodete import" parent=import_status submit=no
	@groupinfo product_prices caption="Toodete hinnad" parent=import_status submit=no
	@groupinfo product_amounts caption="Toodete laoseisud" parent=import_status  submit=no
	@groupinfo pricelists caption="Hinnakirjad" parent=import_status submit=no
	@groupinfo customers caption="Kliendid" parent=import_status submit=no

@groupinfo import_timing caption="Importide ajastus"


@reltype WAREHOUSE value=10 clid=CL_SHOP_WAREHOUSE
@caption AW Ladu

@reltype PROD_REPEATER value=11 clid=CL_RECURRENCE
@caption Toodete kordaja

@reltype PRICES_REPEATER value=12 clid=CL_RECURRENCE
@caption Hindade kordaja

@reltype AMOUNTS_REPEATER value=13 clid=CL_RECURRENCE
@caption Koguste kordaja

@reltype PRICE_LISTS_REPEATER value=14 clid=CL_RECURRENCE
@caption Hinnakirjade kordaja

*/

// types of import:
//	main product data
//	product prices
//	product amounts
//	price lists
//	customers

class warehouse_import extends class_base
{
	function warehouse_import()
	{
		$this->init(array(
			"tpldir" => "applications/shop/warehouse_import",
			"clid" => CL_WAREHOUSE_IMPORT
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

	function _get_aw_warehouses($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'warehouse',
			'caption' => t('Ladu')
		));

		$t->define_field(array(
			"name" => "import_matrix",
			"caption" => t("Vali kust imporditakse"),
			"align" => "center"
		));

		$t->define_field(array(
			'name' => 'products',
			'caption' => t('Tooted'),
			"parent" => "import_matrix",
		));
		$t->define_field(array(
			'name' => 'amounts',
			"parent" => "import_matrix",
			'caption' => t('Laoseis')
		));
		$t->define_field(array(
			'name' => 'prices',
			"parent" => "import_matrix",
			'caption' => t('Hinnad')
		));
		$t->define_field(array(
			'name' => 'price_list',
			"parent" => "import_matrix",
			'caption' => t('Hinnakiri')
		));
		$t->define_chooser(array(
			'field' => 'oid',
			'name' => 'sel'
		));

		// make picker options for ext wh
		$ext_wh = array("" => t("--vali--"));
		foreach($arr["obj_inst"]->list_external_warehouses(true) as $id => $data)
		{
			$ext_wh[$id] = $data["name"];
		}

		foreach ($arr['obj_inst']->list_aw_warehouses() as $wh_id => $wh_data)
		{
			$t->define_data(array(
				'warehouse' => $wh_data["name"],
				"oid" => $wh_id,
				"products" => html::select(array(
					"name" => "imp[$wh_id][products]",
					"options" => $ext_wh,
					"value" => $wh_data["imp_products"]
				)),
				"amounts" => html::select(array(
					"name" => "imp[$wh_id][amounts]",
					"options" => $ext_wh,
					"value" => $wh_data["imp_amounts"]
				)),
				"prices" => html::select(array(
					"name" => "imp[$wh_id][prices]",
					"options" => $ext_wh,
					"value" => $wh_data["imp_prices"]
				)),
				"price_list" => html::select(array(
					"name" => "imp[$wh_id][price_list]",
					"options" => $ext_wh,
					"value" => $wh_data["imp_price_list"]
				)),
			));
		}

		return PROP_OK;
	}

	function _set_aw_warehouses($arr)
	{
		$arr["obj_inst"]->set_import_matrix($arr["request"]["imp"]);
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
		$arr["swh"] = "0";
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
			$this->db_query("CREATE TABLE aw_warehouse_import(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "aw_timing_prods":
			case "aw_timing_prices":
			case "aw_timing_amounts":
			case "aw_timing_price_lists":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	function _get_data_source($arr)
	{
		$arr["prop"]["options"] = array("" => t("--vali--")) + $this->make_keys(class_index::get_classes_by_interface("warehouse_import_if"));
	}

	function _get_aw_warehouses_tb($arr)
	{
		$tb = $arr["prop"]["vcl_inst"];
		$tb->add_search_button(array(
			"name" => "swh",
			"pn" => "swh",
			"clid" => array(CL_SHOP_WAREHOUSE)
		));
		$tb->add_delete_rels_button();
	}

	function callback_post_save($arr)
	{
		$ps = get_instance("vcl/popup_search");
		$ps->do_create_rels($arr["obj_inst"], $arr["request"]["swh"], "RELTYPE_WAREHOUSE");
	}

	private function _init_config_table($t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "use",
			"caption" => t("Kasutusel"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "info",
			"caption" => t("Lisainfo"),
			"align" => "center"
		));
	}

	function _get_config_table($arr)
	{
		$t = $arr["prop"]["vcl_inst"];
		$t->set_sortable(false);
		$this->_init_config_table($t);

		foreach($arr["obj_inst"]->list_external_warehouses() as $id => $data)
		{
			$t->define_data(array(
				"name" => $data["name"],
				"use" => html::checkbox(array(
					"name" => "use[$id]",
					"value" => 1,
					"checked" => $data["used"] == 1
				)),
				"info" => $data["info"]
			));
		}
	}

	function _set_config_table($arr)
	{
		$arr["obj_inst"]->set_used_external_warehouses($arr["request"]["use"]);
	}

	private function _init_timing_table($t)
	{
		$t->define_field(array(
			"name" => "type",
			"caption" => t("Impordi t&uuml;&uuml;p"),
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "timing",
			"caption" => t("Impordi ajakava"),
			"align" => "center"
		));
	}

	function _get_import_timing($arr)
	{	
		$t = $arr["prop"]["vcl_inst"];
		$this->_init_import_timing($t);
	}

	function _get_product_status($arr)
	{
		$arr["prop"]["value"] = $this->_describe_import($arr["obj_inst"], "products", "RELTYPE_PROD_REPEATER");
	}

	function _get_prices_status($arr)
	{
		$arr["prop"]["value"] = $this->_describe_import($arr["obj_inst"], "prices", "RELTYPE_PRICES_REPEATER");
	}

	function _get_amounts_status($arr)
	{
		foreach($arr["obj_inst"]->list_aw_warehouses() as $wh_id => $wh_data)
		{
			$arr["prop"]["value"] .= "<h1>".sprintf(t("Ladu %s"), obj($wh_id)->name())."</h1>";
			$arr["prop"]["value"] .= $this->_describe_import($arr["obj_inst"], "amounts", "RELTYPE_AMOUNTS_REPEATER", $wh_id);
		}
	}

	function _get_pricelists_status($arr)
	{
		$arr["prop"]["value"] = $this->_describe_import($arr["obj_inst"], "pricelists", "RELTYPE_PRICE_LISTS_REPEATER");
	}

	private function _describe_import($o, $type, $rt, $wh_id = null)
	{
		$t = "";
		if (($pid = $o->import_is_running($type, $wh_id)))
		{
			$full_stat = $o->full_import_status($type, $wh_id);
			$t = html::strong(t("Import k&auml;ib!"));
			$t .= "<br/>".sprintf(t("Staatus: %s, protsess: %s, tooteid t&ouml;&ouml;deldud %s tooteid kokku %s, algusaeg %s"), 
				self::name_for_status($full_stat[2]),
				$pid, 
				(int)$full_stat[4],
				(int)$full_stat[5],
				date("d.m.Y H:i:s", $full_stat[0])
			);

			if ($o->need_to_stop_now($type, $wh_id))
			{
				$t .= "<br/>".html::href(array(
					"url" => $this->mk_my_orb("reset_import", array("type" => $type, "wh_id" => $wh_id, "id" => $o->id(), "post_ru" => get_ru())),
					"caption" => t("Reset")
				));
			}
			else
			{
				$t .= "<br/>".html::href(array(
					"url" => $this->mk_my_orb("stop_import", array("type" => $type, "wh_id" => $wh_id, "id" => $o->id(), "post_ru" => get_ru())),
					"caption" => t("Peata kohe")
				));
			}
		}
		else
		{
			$rec = $o->get_first_obj_by_reltype($rt);
			if ($rec)
			{
				$ne = $rec->instance()->get_next_event(array("id" => $rec->id()));
				if ($ne > 10)
				{
					$t = sprintf(t("J&auml;rgmine import algab %s"), date("d.m.Y H:i", $ne));
				}
				else
				{
					$t = t("Impordi kordaja on l&otilde;ppenud!");
				}
			}
			else
			{
				$t = t("Impordile pole automaatset k&auml;ivitust m&auml;&auml;ratud!");
			}

			$t .= "<br/>".html::href(array(
				"url" => $this->mk_my_orb("do_".$type."_import", array("id" => $o->id(), "wh_id" => $wh_id, "post_ru" => get_ru())),
				"caption" => t("K&auml;ivita kohe")
			));
		}

		if (($prev = $o->get_import_log($type, $wh_id)))
		{
			$tb = new vcl_table();
			$tb->set_sortable(false);

			$tb->define_field(array(
				"caption" => t("Alustati"),
				"name" => "start",
				"align" => "center",
				"type" => "time",
				"format" => "d.m.Y H:i:s",
				"numeric" => 1
			));
			$tb->define_field(array(
				"caption" => t("L&otilde;petati"),
				"name" => "end",
				"align" => "center",
				"type" => "time",
				"format" => "d.m.Y H:i:s",
				"numeric" => 1
			));
			$tb->define_field(array(
				"caption" => t("Edukas"),
				"name" => "success",
				"align" => "center",
			));
			$tb->define_field(array(
				"caption" => t("Imporditud toodete arv"),
				"name" => "prod_count",
				"align" => "center",
				"numeric" => 1
			));
			$tb->define_field(array(
				"caption" => t("Kogu toodete arv"),
				"name" => "total",
				"align" => "center",
				"numeric" => 1
			));
			$tb->define_field(array(
				"caption" => t("L&otilde;petamise p&otilde;hjus"),
				"name" => "reason",
				"align" => "center",
			));

			foreach($prev as $entry)
			{
				$tb->define_data(array(
					"start" => $entry["full_status"][0],
					"end" => $entry["finish_tm"],
					"success" => $entry["success"] ? t("Jah") : t("Ei"),
					"prod_count" => $entry["full_status"][4],
					"total" => $entry["full_status"][5],
					"reason" => $entry["reason"]
				));
			}

			$tb->set_caption(t("Eelneva 10 impordi info"));
			$t .= "<br/>".$tb->get_html();
		}

		return $t;
	}

	/**
		@attrib name=reset_import
		@param id required 
		@param type required
		@param wh_id optional
		@param post_ru optional
	**/
	function reset_import($arr)
	{	
		$o = obj($arr["id"]);
		$o->reset_import($arr["type"], $arr["wh_id"]);
		return $arr["post_ru"];
	}

	function run_backgrounded($act, $id, $wh_id = null)
	{
		$url = $this->mk_my_orb("run_backgrounded", array("wh_id" => $wh_id, "act" => $act, "id" => $id));
		$url = str_replace("/automatweb", "", $url);
		$h = new http;
		exit($url);  // DEBUG:
		$h->get($url);
	}

	/**
		@attrib name=stop_import
		@param type required
		@param wh_id optional
		@param id required
		@param post_ru optional
	**/
	function stop_import($arr)
	{
		$o = obj($arr["id"]);
		$o->stop_import($arr["type"], $arr["wh_id"]);
		return $arr["post_ru"];
	}

	/**
		@attrib name=run_backgrounded nologin="1"
		@param id required
		@param wh_id optional
		@param act required
	**/
	function do_run_bg($arr)
	{
		session_write_close();
		while(ob_get_level()) { ob_end_clean(); }
/**/
// If it is needed to debug the imports, then comment the following lines until 'flush()'
		// let the user continue with their business
		ignore_user_abort(1);
		header("Content-Type: image/gif");
		header("Content-Length: 43");
		header("Connection: close");
		echo base64_decode("R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==")."\n";
		flush();
/**/
		aw_set_exec_time(AW_LONG_PROCESS);

		$act = $arr["act"];
		$this->$act($arr["id"], $arr["wh_id"]);
		die("all done!");
	}

	static function name_for_status($stat)
	{
		$lut = array(
			warehouse_import_if::STATE_PREPARING => t("Alustamine"),
			warehouse_import_if::STATE_FETCHING  => t("Andmete t&otilde;mbamine"),
			warehouse_import_if::STATE_PROCESSING => t("Andmete t&ouml;&ouml;tlemine"),
			warehouse_import_if::STATE_WRITING => t("Andmete salvestamine"),
			warehouse_import_if::STATE_FINISHING => t("L&otilde;petamine")
		);
		return $lut[$stat];
	}


//////////// actual imports


	/**
		@attrib name=do_prices_import
		@param id required type=int acl=view
		@param post_ru optional
	**/
	function do_prices_import($arr)
	{
		$this->run_backgrounded("real_prices_import", $arr["id"]);
		return $arr["post_ru"];
	}

	function real_prices_import($id)
	{
		$o = obj($id);
		$o->start_prices_import();
	}


	/**
		@attrib name=do_amounts_import
		@param id required type=int acl=view
		@param wh_id optional
		@param post_ru optional
	**/
	function do_amounts_import($arr)
	{
		// for all aw warehouses
		$this->run_backgrounded("real_amounts_import", $arr["id"], $arr["wh_id"]);
		return $arr["post_ru"];
	}

	function real_amounts_import($id, $wh_id)
	{
		$o = obj($id);
		$o->start_amounts_import($wh_id);
	}

	/**
		@attrib name=do_pricelists_import
		@param id required type=int acl=view
		@param post_ru optional
	**/
	function do_pricelists_import($arr)
	{
		$this->run_backgrounded("real_pricelists_import", $arr["id"]);
		return $arr["post_ru"];
	}

	function real_pricelists_import($id)
	{
		$o = obj($id);
		$o->update_price_list();
	}




	/**
		@attrib name=do_products_import
		@param id required type=int acl=view
	**/
	function do_products_import($arr)
	{
		$o = obj($arr["id"]);
		$o->start_prod_import($this->mk_my_orb("callback_xml_done", array("id" => $arr["id"])));
	}

	/**
		@attrib name=callback_xml_done
		@param id required type=int acl=view
		@param prod_xml required 
	**/
	function callback_xml_done($arr)
	{
		$o = obj($arr["id"]);
		$o->process_product_xml($arr["prod_xml"]);
	}
}


interface warehouse_import_if
{
	const STATE_PREPARING = 1;
	const STATE_FETCHING = 2;
	const STATE_PROCESSING = 3;
	const STATE_WRITING = 4;
	const STATE_FINISHING = 5;

	public function get_warehouse_list();
	public function get_pricelist_xml();
	public function get_prices_xml();
	public function get_amounts_xml($wh_id = null);
}
?>
