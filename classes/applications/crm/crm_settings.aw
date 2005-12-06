<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/crm/crm_settings.aw,v 1.1 2005/12/06 18:20:35 kristo Exp $
// crm_settings.aw - Kliendibaasi seaded 
/*

@classinfo syslog_type=ST_CRM_SETTINGS relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects


@default group=general

	@property cfgform type=relpicker reltype=RELTYPE_CFGFORM field=meta method=serialize
	@caption Kliendi seadete vorm 

@reltype USER value=1 clid=CL_USER
@caption Kasutaja

@reltype CFGFORM value=2 clid=CL_CFGFORM
@caption Seadete vorm

*/

class crm_settings extends class_base
{
	function crm_settings()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_settings",
			"clid" => CL_CRM_SETTINGS
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
		};
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

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
}
?>
