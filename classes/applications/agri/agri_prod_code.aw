<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/agri/Attic/agri_prod_code.aw,v 1.1 2004/03/28 21:45:14 kristo Exp $
// agri_prod_code.aw - Agri tootekood 
/*

@tableinfo agri_codes index=id master_table=objects master_index=brother_of

@classinfo syslog_type=ST_AGRI_PROD_CODE relationmgr=yes

@default table=objects
@default group=general

@property code type=textbox table=agri_codes
@caption Tootekood

@property period type=checkbox ch_value=1 table=agri_codes
@caption Perioodilised andmed

*/

class agri_prod_code extends class_base
{
	function agri_prod_code()
	{
		$this->init(array(
			"tpldir" => "applications/agri/agri_prod_code",
			"clid" => CL_AGRI_PROD_CODE
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
}
?>
