<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_call.aw,v 1.1 2003/11/10 13:05:42 duke Exp $
// crm_call.aw - Kõne 
/*

@classinfo syslog_type=ST_CRM_CALL relationmgr=yes

@default table=objects
@default group=general

@property start1 type=datetime_select
// field=start table=planner group=calendar
@caption Algab 

@property duration type=time_select 
//field=end table=planner group=calendar
@caption Kestab

@property content type=textarea cols=60 rows=30
@caption Sisu


*/

class crm_call extends class_base
{
	function crm_call()
	{
		$this->init(array(
			"clid" => CL_CRM_CALL
		));
	}

	function change($args)
	{
		$args['strs'] = array(
			'type' => 'KONE',
			'typeStr' => 'Kõne',
			'typestr' => 'kõne',
			'typestrs' => 'kõne',
		);
		
		$kohtumine = get_instance('kliendibaas/kohtumine');
		$kohtumine->_change($args);
	}	
};
?>
