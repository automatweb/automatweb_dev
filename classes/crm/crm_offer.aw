<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_offer.aw,v 1.2 2003/11/11 10:23:54 duke Exp $
// pakkumine.aw - Pakkumine 
/*

@classinfo syslog_type=ST_CRM_OFFER relationmgr=yes

@default table=objects
@default group=general
@default field=meta

default group=calendar
@property start1 type=datetime_select field=start table=planner
@caption Algab 

@property duration type=time_select field=end table=planner 
@caption Kestab

@property content type=textarea cols=60 rows=30 table=planner field=description
@caption Sisu

default group=other_calendars
@tableinfo planner index=id master_table=objects master_index=brother_of

*/

class crm_offer extends class_base
{
	function crm_offer()
	{
		$this->init(array(
			"clid" => CL_CRM_OFFER
		));
	}

	
	
	function __change($args)
	{
		$args['strs'] = array(
			'type' => 'PAKKUMINE',
			'typeStr' => 'Pakkumine',
			'typestr' => 'pakkumine',
			'typestrs' => 'pakkumise',
		);
		
		$kohtumine = get_instance('kliendibaas/kohtumine');
		$kohtumine->_change($args);
	}

	

}
?>
