<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_meeting.aw,v 1.3 2004/01/09 13:56:52 duke Exp $
// kohtumine.aw - Kohtumine 
/*

@classinfo syslog_type=ST_CRM_MEETING relationmgr=yes

@default table=objects
@default group=general

@property start1 type=datetime_select field=start table=planner 
@caption Algab 

@property duration type=time_select field=end table=planner 
@caption Kestab

@property content type=textarea cols=60 rows=30 table=documents
@caption Sisu

@property summary type=textarea cols=60 rows=30 table=planner field=description
@caption Kokkuvõte

@property aliasmgr type=aliasmgr no_caption=1
@caption Aliastehaldur

@tableinfo documents index=docid master_table=objects master_index=brother_of
@tableinfo planner index=id master_table=objects master_index=brother_of

*/

class crm_meeting extends class_base
{
	function crm_meeting()
	{
		$this->init(array(
			"clid" => CL_CRM_MEETING,
		));
	}
};
?>
