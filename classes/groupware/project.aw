<?php
// $Header: /home/cvs/automatweb_dev/classes/groupware/Attic/project.aw,v 1.8 2004/01/28 16:58:17 duke Exp $
// project.aw - Projekt 
/*

@classinfo syslog_type=ST_PROJECT relationmgr=yes

@default table=objects
@default group=general

@property event_list type=calendar group=event_list no_caption=1
@caption Sündmused

@groupinfo event_list caption="Sündmused" submit=no

@reltype SUBPROJECT clid=CL_PROJECT value=1
@caption alamprojekt

@reltype PARTICIPANT clid=CL_USER,CL_CRM_COMPANY value=2
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
		$lds = $this->get_events_for_project(array(
			"project_id" => $arr["obj_inst"]->id(),
		));
		
		$t = &$arr["prop"]["vcl_inst"];

		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"],
		));

		$start = $range["start"];
		$end = $range["end"];
		classload("icons");

		if (sizeof($lds) > 0)
		{
			$ol = new object_list(array(
				"oid" => $lds,
				"sort_by" => "planner.start",
				new object_list_filter(array("non_filter_classes" => CL_CRM_MEETING)),
			));


			for($o =& $ol->begin(); !$ol->end(); $o =& $ol->next())
			{
				$clinf = $this->cfg["classes"][$o->class_id()];
				$t->add_item(array(
					"timestamp" => $o->prop("start1"),
					"data" => array(
						"name" => $o->prop("name"),
						"icon" => icons::get_icon_url($o),
						"url" => $this->mk_my_orb("change",array("id" => $o->id()),$clinf["file"]),
					),
				));
			};
		};
	}

	////
	// !returns a list of events from the projects the user participates in
	// project_id (optional) - id of the project, if specified we get events
	// from that project only

	// XXX: split this into separate methods
	function get_events_from_projects($arr = array())
	{
		$ev_ids = array();
		//if (empty($arr["project_id"]))
		if ($arr["type"] == "my_projects")
		{
			// this returns a list of events from "My projects"
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
		elseif (!empty($arr["project_id"]))
		{
			$ev_ids = $this->get_events_for_project(array("project_id" => $arr["project_id"]));
		};
		return $ev_ids;
	}

	////
	// !id - participant id
	function get_events_for_participant($arr = array())
	{
		$ev_ids = array();
		$projects = array();
		$obj = new object($arr["id"]);
		if ($obj->class_id() == CL_CRM_COMPANY)
		{
			$conns = $obj->connections_to(array(
				"reltype" => RELTYPE_PARTICIPANT,
			));
			foreach($conns as $conn)
			{
				$ev_ids = $ev_ids + $this->get_events_for_project(array(
					"project_id" => $conn->prop("from"),
					"class_id" => $arr["clid"],
				));
			};
		};

		return $ev_ids;
	}

	////
	// !Returns a list of event id-s for a given project
	function get_events_for_project($arr)
	{
		$pr_obj = new object($arr["project_id"]);
		$args = array(
			"type" => RELTYPE_PRJ_EVENT,
		);


		if (!empty($arr["class_id"]))
		{
			$args["to.class_id"] = $arr["class_id"];
		};

		$event_connections = $pr_obj->connections_from($args);

		$ev_id_list = array();
		foreach($event_connections as $conn)
		{
			$ev_id_list[$conn->prop("to")] = $conn->prop("to");
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
