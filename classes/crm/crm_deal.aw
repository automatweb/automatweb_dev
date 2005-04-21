<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_deal.aw,v 1.5 2005/04/21 08:54:56 kristo Exp $
// crm_deal.aw - Tehing 
/*

@classinfo syslog_type=ST_CRM_DEAL relationmgr=yes

@default table=objects
@default group=general

*/

class crm_deal extends class_base
{
	function crm_deal()
	{
		$this->init(array(
			"clid" => CL_CRM_DEAL
		));
	}
}
?>
