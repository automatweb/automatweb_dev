<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_call.aw,v 1.8 2004/02/03 12:03:09 duke Exp $
// crm_call.aw - phone call
/*

@classinfo syslog_type=ST_CRM_CALL relationmgr=yes no_status=1 

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

@property aliasmgr type=aliasmgr no_caption=1 store=no
@caption Aliastehaldur

@tableinfo planner index=id master_table=objects master_index=brother_of


*/

class crm_call extends class_base
{
	function crm_call()
	{
		$this->init(array(
			"tpldir" => "crm/call",
			"clid" => CL_CRM_CALL
		));
	}

	function request_execute($obj)
	{
		classload("icons");
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $obj->name(),
			"icon" => icons::get_icon_url($obj),
			"time" => date("d-M-y H:i",$obj->prop("start1")),
			"content" => nl2br($obj->prop("content")),
		));
		return $this->parse();
	}

	function parse_alias($arr)
	{
		// shows a phone call
		$obj = new object($arr["id"]);
		$done = $obj->prop("is_done");
		$done .= $obj->prop("name");
		return $done;
	}

};
?>
