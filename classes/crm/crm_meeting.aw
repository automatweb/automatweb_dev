<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_meeting.aw,v 1.5 2004/01/13 15:34:23 duke Exp $
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

@property aliasmgr type=aliasmgr no_caption=1 store=no
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

	function parse_alias($arr)
	{
		$target = new object($arr["alias"]["target"]);
		return html::href(array(
			//"url" => aw_ini_get("baseurl") . "/" . $target->id(),
			"url" => $this->mk_my_orb("change",array("id" => $target->id()),$target->class_id(),true,true),
			"caption" => $target->name(),
		));
	}

};
?>
