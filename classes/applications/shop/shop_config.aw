<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/Attic/shop_config.aw,v 1.1 2004/03/17 16:06:57 kristo Exp $
// shop_config.aw - Lao konfiguratsioon 
/*

@classinfo syslog_type=ST_SHOP_CONFIG relationmgr=yes

@default table=objects
@default group=general

@property prod_fld type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Toodete kataloog

@property pkt_fld type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Pakettide kataloog

@property reception_fld type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Lao sissetulekute kataloog

@property export_fld type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Lao v&auml;jaminekute kataloog

@property prod_type_fld type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Lao toodete t&uuml;&uuml;bid

@reltype FOLDER value=1 clid=CL_MENU
@caption kataloog

*/

class shop_config extends class_base
{
	function shop_config()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_config",
			"clid" => CL_SHOP_CONFIG
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
