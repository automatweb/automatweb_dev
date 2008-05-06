<?php
/*
@classinfo syslog_type=ST_SHOP_WAREHOUSE_CONFIG relationmgr=yes maintainer=kristo

@default table=objects
@default group=general
@default field=meta
@default method=serialize

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


@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

@reltype SEARCH_FORM value=2 clid=CL_CB_SEARCH
@caption otsinguvorm

@reltype MANAGER_CO value=3 clid=CL_CRM_COMPANY
@caption haldaja firma

@reltype CFGFORM value=4 clid=CL_CFGFORM
@caption Seadete vorm

@reltype DEF_PRICELIST value=5 clid=CL_SHOP_PRICELIST
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
			
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{

		}
		return $retval;
	}	
}
?>
