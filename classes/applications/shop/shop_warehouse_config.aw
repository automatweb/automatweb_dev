<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_warehouse_config.aw,v 1.7 2005/03/30 12:18:28 ahti Exp $
// shop_warehouse_config.aw - Lao konfiguratsioon 
/*

@classinfo syslog_type=ST_SHOP_WAREHOUSE_CONFIG relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property prod_fld type=relpicker reltype=RELTYPE_FOLDER
@caption Toodete kataloog

@property pkt_fld type=relpicker reltype=RELTYPE_FOLDER
@caption Pakettide kataloog

@property reception_fld type=relpicker reltype=RELTYPE_FOLDER
@caption Lao sissetulekute kataloog

@property export_fld type=relpicker reltype=RELTYPE_FOLDER
@caption Lao v&auml;jaminekute kataloog

@property prod_type_fld type=relpicker reltype=RELTYPE_FOLDER
@caption Lao toodete t&uuml;&uuml;bid

@property prod_type_cfgform type=relpicker reltype=RELTYPE_CFGFORM
@caption Lao toodete t&uuml;&uuml;pide lisamise vormi seadete vorm

@property order_fld type=relpicker reltype=RELTYPE_FOLDER
@caption Lao tellimuste kataloog

@property buyers_fld type=relpicker reltype=RELTYPE_FOLDER
@caption Lao tellijate kataloog

@property search_form type=relpicker reltype=RELTYPE_SEARCH_FORM
@caption Lao otsinguvorm

@property manager_cos type=relpicker reltype=RELTYPE_MANAGER_CO multiple=1
@caption Haldurfirmad

@property sell_prods type=checkbox ch_value=1
@caption Ladu m&uuml;&uuml;b tooteid, mitte pakendeid

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

@reltype SEARCH_FORM value=2 clid=CL_CB_SEARCH
@caption otsinguvorm

@reltype MANAGER_CO value=3 clid=CL_CRM_COMPANY
@caption haldaja firma

@reltype CFGFORM value=4 clid=CL_CFGFORM
@caption Seadete vorm

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
