<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_deal.aw,v 1.2 2003/11/11 10:23:54 duke Exp $
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

	function __change($args)
	{
		$args['strs'] = array(
			'type' => 'TEHING',
			'typeStr' => 'Tehing',
			'typestr' => 'tehing',
			'typestrs' => 'tehingu',
		);
		
		$kohtumine = get_instance('kliendibaas/kohtumine');
		$kohtumine->_change($args);
	}	
}
?>
