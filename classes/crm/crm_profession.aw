<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_profession.aw,v 1.1 2003/11/11 10:23:54 duke Exp $
// amet.aw - Ameti nimetus 
/*

@classinfo syslog_type=ST_CRM_PROFESSION
//relationmgr=yes

@default table=objects
@default group=general

*/

class crm_profession extends class_base
{
	function crm_profession()
	{
		$this->init(array(
			"clid" => CL_CRM_PROFESSION
		));
	}

	
	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case 'status':
				$retval=PROP_IGNORE;
			break;

		};
		return $retval;
	}
	

	/*
	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
                {

		}
		return $retval;
	}	
	*/

};
?>
