<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_call.aw,v 1.3 2003/12/09 18:34:39 duke Exp $
// crm_call.aw - Kõne 
/*

@classinfo syslog_type=ST_CRM_CALL relationmgr=yes

@default table=objects
@default group=general

@property start1 type=datetime_select field=start table=planner 
@caption Algab 

@property duration type=time_select field=end table=planner
@caption Kestab

@property content type=textarea cols=60 rows=30 table=planner field=description
@caption Sisu

@tableinfo planner index=id master_table=objects master_index=brother_of


*/

class crm_call extends class_base
{
	function crm_call()
	{
		$this->init(array(
			"clid" => CL_CRM_CALL
		));
	}
};
?>
