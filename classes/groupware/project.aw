<?php
// $Header: /home/cvs/automatweb_dev/classes/groupware/Attic/project.aw,v 1.5 2003/11/19 17:57:04 duke Exp $
// project.aw - Projekt 
/*

@classinfo syslog_type=ST_PROJECT relationmgr=yes

@default table=objects
@default group=general

@property event_list type=table group=event_list no_caption=1
@caption Sündmused

@groupinfo event_list caption="Sündmused" submit=no

@reltype SUBPROJECT clid=CL_PROJECT value=1
@caption alamprojekt

@reltype PARTICIPANT clid=CL_USER value=2
@caption osaleja

@reltype PRJ_EVENT value=3 clid=CL_TASK,CL_CRM_CALL,CL_CRM_OFFER,CL_CRM_DEAL,CL_CRM_MEETING
@caption Sündmus

*/

class project extends class_base
{
	function project()
	{
		$this->init(array(
			"clid" => CL_PROJECT
		));
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "event_list":
				$this->gen_event_list($arr);
				break;
		}
		return $retval;
	}

	////
	// !Optionally this also needs to support date range ..
	function gen_event_list($arr)
	{
		$ol = new object_list(array(
			"oid" => $this->get_events_for_project(array("project_id" => $arr["obj_inst"]->id())),
			"sort_by" => "planner.start",
			new object_list_filter(array("non_filter_classes" => CL_CRM_MEETING)),
		));


		$rv = "";

		$t = &$arr["prop"]["vcl_inst"];
		$t->define_field(array(
			"name" => "time",
			"caption" => "Aeg",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "type",
			"caption" => "Tüüp",
			"sortable" => 1,
			"align" => "center"
		));
		$t->define_field(array(
			"name" => "name",
			"caption" => "Nimi",
			"sortable" => 1,
			"align" => "center"
		));


		for($o =& $ol->begin(); !$ol->end(); $o =& $ol->next())
		{
			$clinf = $this->cfg["classes"][$o->class_id()];
			$t->define_data(array(
				"time" => $this->time2date($o->prop("start1")),
				"type" => $clinf["name"],
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $o->id()),$clinf["file"]),
					"caption" => $o->prop("name"),
				)),
			));
		};
	}

	////
	// !returns a list of events from the projects the user participates in
	// project_id (optional) - id of the project, if specified we get events
	// from that project only

	function get_events_from_projects($arr = array())
	{
		$rv = array();
		if (empty($arr["project_id"]))
		{
			$users = get_instance("users");
			$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
			$conns = $user->connections_to(array(
				"from.class_id" => CL_PROJECT,
			));
			$ev_ids = array();
			foreach($conns as $conn)
			{
				$ev_ids = array_merge($ev_ids,$this->get_events_for_project(array("project_id" => $conn->prop("from"))));
			};
		}
		else
		{
			$ev_ids = $this->get_events_for_project(array("project_id" => $arr["project_id"]));
		};
		return $ev_ids;
	}

	////
	// !Returns a list of event id-s for a given project
	function get_events_for_project($arr)
	{
		$pr_obj = new object($arr["project_id"]);
		$event_connections = $pr_obj->connections_from(array(
			"type" => RELTYPE_PRJ_EVENT,
		));

		$ev_id_list = array();
		foreach($event_connections as $conn)
		{
			$ev_id_list[] = $conn->prop("to");
		};

		return $ev_id_list;
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
