<?php
/*
@classinfo syslog_type=ST_SHOP_WAREHOUSE_CONFIG relationmgr=yes maintainer=kristo

@default table=objects
@default group=general

	@layout split type=hbox 

		@layout left type=vbox area_caption=Seaded closeable=1 parent=split

			@property name type=textbox parent=left
			@caption Nimi
			@comment Objekti nimi

			@property comment type=textbox parent=left
			@caption Kommentaar
			@comment Vabas vormis tekst objekti kohta

			@property status type=status trans=1 default=1 parent=left
			@caption Aktiivne
			@comment Kas objekt on aktiivne

@default field=meta
@default method=serialize

			@property search_form type=relpicker reltype=RELTYPE_SEARCH_FORM parent=left
			@caption Lao otsinguvorm

			@property has_alternative_units type=chooser parent=left
			@caption Alternatiiv&uuml;hikud

			@property alternative_unit_levels type=textbox size=5 parent=left
			@caption Alternatiiv&uuml;hikute tasemeid

			@property def_price_list type=relpicker reltype=RELTYPE_DEF_PRICELIST parent=left automatic=1
			@caption Vaikimisi hinnakiri

			@property def_currency type=relpicker reltype=RELTYPE_DEF_CURRENCY parent=left automatic=1
			@caption Vaikimisi valuuta

			@property manager_cos type=relpicker reltype=RELTYPE_MANAGER_CO multiple=1 parent=left
			@caption Haldurfirmad

			@property sell_prods type=checkbox ch_value=1 parent=left
			@caption Ladu m&uuml;&uuml;b tooteid, mitte pakendeid

			@property no_packets type=checkbox ch_value=1 parent=left
			@caption Ladu ei m&uuml;&uuml; pakette

			@property no_count type=checkbox ch_value=1 parent=left
			@caption Toodetel puudub laoseis

		@layout right type=vbox area_caption=Kaustad closeable=1 parent=split

			@property prod_fld type=relpicker reltype=RELTYPE_FOLDER parent=right
			@caption Toodete kataloog

			@property pkt_fld type=relpicker reltype=RELTYPE_FOLDER parent=right
			@caption Pakettide kataloog

			@property reception_fld type=relpicker reltype=RELTYPE_FOLDER parent=right
			@caption Lao sissetulekute kataloog

			@property export_fld type=relpicker reltype=RELTYPE_FOLDER parent=right
			@caption Lao v&auml;jaminekute kataloog

			@property prod_type_fld type=relpicker reltype=RELTYPE_FOLDER parent=right
			@caption Lao toodete t&uuml;&uuml;bid

			@property prod_type_cfgform type=relpicker reltype=RELTYPE_CFGFORM parent=right
			@caption Lao tootet&uuml;&uuml;pide lisamise vormi seadete vorm

			@property prod_conf_folder type=relpicker reltype=RELTYPE_FOLDER parent=right
			@caption Toodete lisaobjektide seadetevormide kataloog

			@property order_fld type=relpicker reltype=RELTYPE_FOLDER parent=right
			@caption Lao tellimuste kataloog

			@property buyers_fld type=relpicker reltype=RELTYPE_FOLDER parent=right
			@caption Lao tellijate kataloog

@default group=units

	@property units_table type=table store=no no_caption=1


@groupinfo units caption="&Uuml;hikud"

	@layout units_split type=hbox width=20%:80%

		@layout units_tree_box type=vbox area_caption=Artiklikategooriad closeable=1 parent=units_split

			@property prodg_tree type=treeview store=no no_caption=1 parent=units_tree_box

		@layout units_tbl_box type=vbox parent=units_split area_caption=&Uuml;hikud closeable=1

			@property units_tbl type=table store=no no_caption=1 parent=units_tbl_box

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

@reltype SEARCH_FORM value=2 clid=CL_CB_SEARCH
@caption otsinguvorm

@reltype MANAGER_CO value=3 clid=CL_CRM_COMPANY
@caption haldaja firma

@reltype CFGFORM value=4 clid=CL_CFGFORM
@caption Seadete vorm

@reltype DEF_PRICELIST value=5 clid=CL_SHOP_PRICE_LIST
@caption Hinnakiri

@reltype DEF_CURRENCY value=6 clid=CL_CURRENCY
@caption Valuuta

*/

class shop_warehouse_config extends class_base
{
	function shop_warehouse_config()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_warehouse_config",
			"clid" => CL_SHOP_WAREHOUSE_CONFIG
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "prodg_tree":
				$whi = get_instance(CL_SHOP_WAREHOUSE);
				$whi->prod_type_fld = $arr["obj_inst"]->prop("prod_type_fld");
				return $whi->mk_prodg_tree(&$arr);

			case "units_tbl":
				$this->_units_tbl(&$arr);
				break;

			case "has_alternative_units":
				$data["options"] = array(
					0 => t("Ei"),
					1 => t("Jah"),
				);
				if(!$data["value"])
				{
					$data["value"] = 0;
				}
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
			case "units_tbl":
				$this->_save_units(&$arr);
				break;
		}
		return $retval;
	}	

	function _units_tbl($arr)
	{
		if(!$this->can("view", $arr["request"]["pgtf"]))
		{
			return PROP_IGNORE;
		}
		$t = &$arr["prop"]["vcl_inst"];
		$units = $arr["obj_inst"]->prop("alternative_unit_levels");
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"align" => "center",
		));
		for($i=0;$i<=$units;$i++)
		{
			$t->define_field(array(
				"name" => "unit_".$i,
				"caption" => $i? sprintf(t("Alternatiiv&uuml;hik %s"), $i) : t("P&otilde;hi&uuml;hik"),
				"align" => "center",
			));
		}
		$ui = get_instance(CL_UNIT);
		$unitnames = $ui->get_unit_list(true);
		$catol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT_CATEGORY,
			"parent" => $arr["request"]["pgtf"],
			"site_id" => array(),
			"lang_id" => array(),
		));
		foreach($catol->arr() as $cat)
		{
			$unitdata = $cat->meta("units");
			if(!$unitdata)
			{
				$unitdata = array();
			}
			for($i=0;$i<=$units;$i++)
			{
				$data["unit_".$i] = html::select(array(
					"name" => "units[".$cat->id()."][".$i."]",
					"options" => $unitnames,
					"value" => $unitdata[$i],
				));
			}
			$data["name"] = $cat->name();
			$t->define_data($data);
		}
	}

	function _save_units($arr)
	{
		if($units = $arr["request"]["units"])
		{
			foreach($units as $cat=>$data)
			{
				$cato = obj($cat);
				$cato->set_meta("units", $data);
				$cato->save();
			}
		}
	}

	function callback_mod_tab($arr)
	{
		if($arr["id"] == "units" && !$arr["obj_inst"]->prop("has_alternative_units"))
		{
			return false;
		}
		return true;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = get_ru();
		$arr["pgtf"] = $_GET["pgtf"];
	}

	function callback_mod_retval($arr)
	{
		$arr["args"]["pgtf"] = $arr["request"]["pgtf"];
	}
}
?>
