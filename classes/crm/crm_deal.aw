<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_deal.aw,v 1.3 2003/11/20 21:21:49 duke Exp $
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
		
		$kohtumine = get_instance('crm/crm_meeting');
		$kohtumine->_change($args);
	}	
}
?>
