<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/shop/shop_product_type.aw,v 1.3 2004/06/04 11:11:01 kristo Exp $
// shop_product_type.aw - Toote t&uuml;&uuml;p 
/*

@classinfo syslog_type=ST_SHOP_PRODUCT_TYPE relationmgr=yes no_status=1

@default table=objects
@default group=general

@property sp_cfgform type=relpicker reltype=RELTYPE_SP_CFGFORM field=meta method=serialize
@caption Vorm, millega toodet sisestatakse

@property packaging_cfgform type=relpicker reltype=RELTYPE_SP_CFGFORM field=meta method=serialize
@caption Vorm, millega toote pakendit sisestatakse

@property image_folder type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Toodete piltide kataloog

@property file_folder type=relpicker reltype=RELTYPE_FOLDER field=meta method=serialize
@caption Toodete failide kataloog


@reltype SP_CFGFORM value=1 clid=CL_CFGFORM
@caption seadete vorm

@reltype FOLDER value=2 clid=CL_MENU
@caption kataloog

*/

class shop_product_type extends class_base
{
	function shop_product_type()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_product_type",
			"clid" => CL_SHOP_PRODUCT_TYPE
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
