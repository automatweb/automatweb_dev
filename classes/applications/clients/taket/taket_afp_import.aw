<?php
/*
@classinfo syslog_type=ST_TAKET_AFP_IMPORT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=robert
@tableinfo aw_taket_afp_import master_index=brother_of master_table=objects index=aw_oid

@default group=general
@default table=objects

@property main_tb type=toolbar no_caption=1

@property name type=textbox
@caption Nimi

@property local_products_file type=textbox field=meta method=serialize
@caption Kohalik toodete fail

@property warehouses_table type=table
@caption Laod

@property import_link type=text store=no
@caption Import

@property warehouse type=relpicker reltype=RELTYPE_WAREHOUSE field=meta method=serialize
@caption Ladu (ilmselt ei peaks siin olema)

@property prod_fld type=relpicker reltype=RELTYPE_PROD_FLD field=meta method=serialize
@caption Toodete kaust (ilmselt ei peaks siin olema)

@property org_fld type=relpicker reltype=RELTYPE_ORG_FLD field=meta method=serialize
@caption Organisatsioonide kaust (ilmselt ei peaks siin olema)

@property amount type=textbox field=meta method=serialize default=5000
@caption Mitu rida korraga importida (ilmselt ei peaks siin olema)

@property code_ctrl type=relpicker reltype=RELTYPE_CODE_CONTROLLER field=meta method=serialize
@caption L&uuml;hikese koodi kontroller


@reltype WAREHOUSE value=1 clid=CL_SHOP_WAREHOUSE
@caption Ladu

@reltype CODE_CONTROLLER value=2 clid=CL_CFGCONTROLLER
@caption L&uuml;hikese koodi kontroller

@reltype ORG_FLD value=3 clid=CL_MENU
@caption Organisatsioonide kaust

@reltype PROD_FLD value=4 clid=CL_MENU
@caption Toodete kaust
*/

class taket_afp_import extends class_base implements warehouse_import_if
{
	function taket_afp_import()
	{
		$this->init(array(
			"tpldir" => "applications/clients/taket/taket_afp_import",
			"clid" => CL_TAKET_AFP_IMPORT
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

	function _get_main_tb($arr)
	{
		$tb = &$arr["prop"]["vcl_inst"];
		
		$tb->add_button(array(
			"name" => "import_button",
			"action" => "import_data",
			"img" => "import.gif",
			"tooltip" => t("Impordi tooteandmed"),
		));
	}

	function _get_warehouses_table($arr)
	{
		$t = &$arr["prop"]["vcl_inst"];
		$t->set_sortable(false);

		$t->define_field(array(
			'name' => 'warehouse',
			'caption' => t('Ladu')
		));

		$t->define_field(array(
			'name' => 'address',
			'caption' => t('Aadress')
		));

		$warehouses = $arr['obj_inst']->get_warehouses();

		foreach ($warehouses as $wh_oid)
		{
			$wh = new object($wh_oid);

			$t->define_data(array(
				'warehouse' => $wh->name(),
				'address' => $wh->comment()
			));
		}

	}

	function _get_import_link($arr)
	{
		$links[] = html::href(array(
			'caption' => t('K&auml;ivita import'),
			'url' => $this->mk_my_orb('import_data', array(
				'id' => $arr['obj_inst']->id(),
				'return_url' => get_ru()
			))
		));
		$links[] = html::href(array(
			'caption' => t('Laoseisu import'),
			'url' => $this->mk_my_orb('import_amounts', array(
				'id' => $arr['obj_inst']->id(),
				'return_url' => get_ru()
			))
		));
		$links[] = html::href(array(
			'caption' => t('Hindade import'),
			'url' => $this->mk_my_orb('import_prices', array(
				'id' => $arr['obj_inst']->id(),
				'return_url' => get_ru()
			))
		));
	
		$arr['prop']['value'] = implode(' | ', $links);
	}

	/**
	@attrib name=import_data all_args=1
	**/
	function import_data($arr)
	{
		if($this->can("view", $arr["id"]))
		{
			obj($arr["id"])->get_data($arr);
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=import_amounts all_args=1
	**/
	function import_amounts($arr)
	{
		if($this->can("view", $arr["id"]))
		{
			obj($arr["id"])->import_amounts($arr);
		}
		return $arr["post_ru"];
	}

	/**
		@attrib name=import_prices all_args=1
	**/
	function import_prices($arr)
	{
		if($this->can("view", $arr["id"]))
		{
			obj($arr["id"])->import_prices($arr);
		}
		return $arr["post_ru"];
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function get_warehouse_list()
	{
		
	}
}

?>
