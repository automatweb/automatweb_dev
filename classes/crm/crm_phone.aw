<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_phone.aw,v 1.1 2003/11/11 10:23:54 duke Exp $
// phone.aw - Telefon 
/*

@classinfo syslog_type=ST_CRM_PHONE relationmgr=yes

@default table=objects
@default group=general

@property name type=textbox
@caption Number

@property comment type=textbox
@caption Kommentaar
*/

/*
@reltype BELONGTO value=1 clid=CL_CRM_ADDRESS,CL_CRM_COMPANY,CL_CRM_PERSON
@caption Numbriga seotud objekt
*/

class crm_phone extends class_base
{
	function crm_phone()
	{
		$this->init(array(
			"clid" => CL_CRM_PHONE
		));
	}
	
	function get_property($args)
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case 'status':
				$retval = PROP_IGNORE;
				break;
			
		};
		return $retval;
	}
}
?>
