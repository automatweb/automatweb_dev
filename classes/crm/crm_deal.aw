<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_deal.aw,v 1.4 2003/12/09 18:34:39 duke Exp $
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
