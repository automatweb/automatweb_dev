<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/building_management/crm_building_management_purchase.aw,v 1.3 2007/12/06 14:33:21 kristo Exp $
// crm_building_management_purchase.aw - Hange 
/*

@classinfo syslog_type=ST_CRM_BUILDING_MANAGEMENT_PURCHASE relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=dragut

@default table=objects
@default group=general

*/

class crm_building_management_purchase extends class_base
{
	function crm_building_management_purchase()
	{
		$this->init(array(
			"tpldir" => "applications/crm/building_management/crm_building_management_purchase",
			"clid" => CL_CRM_BUILDING_MANAGEMENT_PURCHASE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

//-- methods --//
}
?>
