<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_call.aw,v 1.4 2003/12/11 14:21:16 duke Exp $
// crm_call.aw - phone call
/*

@classinfo syslog_type=ST_CRM_CALL relationmgr=yes no_status=1 no_comment=1

@default table=planner
@default group=general

@property is_done type=checkbox table=objects field=flags method=bitmask ch_value=8 // OBJ_IS_DONE
@caption Tehtud

@property start1 type=datetime_select field=start 
@caption Algus

@property duration type=time_select field=end 
@caption Kestus

@property content type=textarea cols=60 rows=30 field=description
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
