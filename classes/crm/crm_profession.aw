<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_profession.aw,v 1.2 2003/11/20 21:21:49 duke Exp $
// crm_profession.aw - Ameti nimetus 
/*

@classinfo syslog_type=ST_CRM_PROFESSION
//relationmgr=yes

@default table=objects
@default group=general

@classinfo no_status=1

*/

class crm_profession extends class_base
{
	function crm_profession()
	{
		$this->init(array(
			"clid" => CL_CRM_PROFESSION
		));
	}
};
?>
