<?php
// $Header: /home/cvs/automatweb_dev/classes/groupware/Attic/project.aw,v 1.3 2003/11/13 14:27:04 duke Exp $
// project.aw - Projekt 
/*

@classinfo syslog_type=ST_PROJECT relationmgr=yes

@default table=objects
@default group=general

@reltype SUBPROJECT clid=CL_PROJECT value=1
@caption alamprojekt

@reltype PARTICIPANT clid=CL_USER value=2
@caption kasutaja

@reltype PRJ_EVENT value=3 clid=CL_TASK,CL_CRM_CALL,CL_CRM_OFFER,CL_CRM_DEAL,CL_CRM_MEETING
@caption Sündmus

*/

// god, this has shrunken so much.
class project extends class_base
{
	function project()
	{
		$this->init(array(
			"clid" => CL_PROJECT
		));
	}

	////
	// !connects an event to a project
	// id - id of the project
	// event_id - id of the event 
	function connect_event($arr)
	{
		$prj_obj = new object($arr["id"]);
		$prj_obj->connect(array(
			"to" => $arr["event_id"],
			"reltype" => RELTYPE_PRJ_EVENT,
		));
	}

	////
	// !Disconnects and event from a project
	// id - id of the project
	// event_id - id of the event
	function disconnect_event($arr)
	{
		$prj_obj = new object($arr["id"]);
		$prj_obj->disconnect(array(
			"from" => $arr["event_id"],
		));
	}
};
?>
