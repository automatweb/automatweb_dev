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

property import type=text store=no
caption Import

reltype DATA_SOURCE value=1 clid=CL_TAKET_AFP_IMPORT
caption Andmeallikas

@reltype WAREHOUSE value=2 clid=CL_SHOP_WAREHOUSE
@caption Ladu

reltype PRODUCTS_FOLDER value=3 clid=CL_MENU
caption Lao toodete kaust

	@groupinfo general_general parent=general caption="&Uuml;ldine"
	@groupinfo aw_warehouses parent=general caption="AW Laod"
	@groupinfo import_warehouses parent=general caption="Imporditavad laod"

@groupinfo import_status caption="Importide staatus"

	@groupinfo product_status caption="Toodete import" parent=import_status
	@groupinfo product_prices caption="Toodete hinnad" parent=import_status
	@groupinfo product_amounts caption="Toodete laoseisud" parent=import_status
	@groupinfo pricelists caption="Hinnakirjad" parent=import_status
	@groupinfo customers caption="Kliendid" parent=import_status

@groupinfo import_timing caption="Importide ajastus"


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
			'name' => 'products',
			'caption' => t('Tooted')
		));
		$t->define_field(array(
			'name' => 'amounts',
			'caption' => t('Laoseis')
		));
		$t->define_field(array(
			'name' => 'prices',
			'caption' => t('Hinnad')
		));
		$t->define_field(array(
			'name' => 'price_list',
			'caption' => t('Hinnakiri')
		));

		foreach ($arr['obj_inst']->get_warehouses() as $wh_id => $wh_name)
		{
			$t->define_data(array(
				'warehouse' => $wh_name
			));
		}

		return PROP_OK;
	}

	function _get_import($arr)
	{
		$links[] = html::href(array(
			'caption' => t('Hindade import'),
			'url' => $this->mk_my_orb('import_prices', array(
				'id' => $arr['obj_inst']->id(),
				'return_url' => get_ru()
			)),
		));

		$links[] = html::href(array(
			'caption' => t('Hinnakirja import'),
			'url' => $this->mk_my_orb('import_price_list', array(
				'id' => $arr['obj_inst']->id(),
				'return_url' => get_ru()
			)),
		));

		$arr['prop']['value'] = implode(', ', $links);

		return PROP_OK;
	}

	/**
		@attrib name=import_prices all_args=1
	**/
	function import_prices($arr)
	{
		if (!$this->can('view', $arr['id']))
		{
			exit($arr['id'].' is not readable');
		}
		$o = new object($arr['id']);
	}

	/**
		@attrib name=import_price_list all_args=1
	**/
	function import_price_list($arr)
	{
	//	automatweb::$instance->mode(automatweb::MODE_DBG);
		if (!$this->can('view', $arr['id']))
		{
			exit($arr['id'].' is not readable');
		}

		$o = new object($arr['id']);

	//	$o->clear_price_list();
	//	exit('delete done');
		$o->update_price_list();
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
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
			case "":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => ""
				));
				return true;
		}
	}
}


interface warehouse_import_if
{
	const STATE_PREPARING = 1;
	const STATE_FETCHING = 2;
	const STATE_PROCESSING = 3;
	const STATE_WRITING = 4;

	public function get_warehouse_list();
}
?>
