<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/project.aw,v 1.2 2004/06/10 11:16:21 duke Exp $
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
		/*
		$lds = $this->get_events_for_project(array(
			"project_id" => $arr["obj_inst"]->id(),
		));
		*/
		
		$t = &$arr["prop"]["vcl_inst"];

		$arr["prop"]["vcl_inst"]->configure(array(
                        "overview_func" => array(&$this,"get_overview"),
                ));

		$range = $arr["prop"]["vcl_inst"]->get_range(array(
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"],
		));

		$start = $range["start"];
		$end = $range["end"];
		classload("icons");

		$this->overview = array();

		//if (sizeof($lds) > 0)
		//{
			$ol = new object_list(array(
				//"oid" => $lds,
				"parent" => $arr["obj_inst"]->id(),
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
						"link" => $this->mk_my_orb("change",array("id" => $o->id()),$clinf["file"]),
					),
				));

				if ($o->prop("start1") > $range["overview_start"])
				{
					$this->overview[$o->prop("start1")] = 1;
				};


			};
		//};
	}

	function get_overview($arr = array())
        {
                return $this->overview;
        }


	////
	// !returns a list of events from the projects the user participates in
	// project_id (optional) - id of the project, if specified we get events
	// from that project only

	// XXX: split this into separate methods
	function get_events_from_projects($arr = array())
	{
		$ev_ids = array();
		if (!empty($arr["project_id"]))
		{
			global $awt;
			$awt->start("project-event-loader");
			#$ev_ids = $this->get_events_for_project(array("project_id" => $arr["project_id"]));
			$ev_ids = $arr["project_id"];
			$awt->stop("project-event-loader");
		}
		else
		if ($arr["type"] == "my_projects")
		{
			// this returns a list of events from "My projects"
			$users = get_instance("users");
			if (aw_global_get("uid"))
			{
				// see asi peab nüüd hakkama tagastame foldereid!
				$user_obj = new object($arr["user_ids"][0]);
				// this is wrong, I need to figure out the users, that this calendar belongs to
				//$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
				$conns = $user_obj->connections_to(array(
					"from.class_id" => CL_PROJECT,
				));
				// ei mingit bloody cyclet, see hakkab lihtsalt tagastame projektide id-sid, onjä!
				$ev_ids = array();
				foreach($conns as $conn)
				{
					$ev_ids[] = $conn->prop("from");
					//$ev_ids = array_merge($ev_ids,$this->get_events_for_project(array("project_id" => $conn->prop("from"))));
				};
			};
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
		//$prj_obj = new object($arr["id"]);
		$evt_obj = new object($arr["event_id"]);
		// create a brother under the project object
		$evt_obj->create_brother($arr["id"]);
		/*
		$prj_obj->connect(array(
			"to" => $arr["event_id"],
			"reltype" => RELTYPE_PRJ_EVENT,
		));
		*/
	}

	////
	// !Disconnects and event from a project
	// id - id of the project
	// event_id - id of the event
	function disconnect_event($arr)
	{
		#$evt_obj = new object($arr["event_id"]);
		#$evt_obj->delete();
		// deleting is broken now until I can figure out something
		//$evt_obj 
		/*
		$prj_obj = new object($arr["id"]);
		$prj_obj->disconnect(array(
			"from" => $arr["event_id"],
		));
		*/
	}

	/**
		@attrib name=test_it_out all_args="1"

	**/
	function test_it_out($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_PROJECT,
		));
		set_time_limit(0);
		for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			/*
			if ($o->id() != 87738)
			{
				continue;
			};
			*/
			$subs = new object_list(array(
				"parent" => $o->id(),
				"site_id" => array(),
			));
			for ($sub_o = $subs->begin(); !$subs->end(); $sub_o = $subs->next())
			{
				$orig = $sub_o->get_original();
				#print_r($sub_o);
				$sub2parent[$orig->id()] = $sub_o->id();
			};
			#arr($sub2parent);
			#arr($subs);
			$brother_parent = $o->id();
			// now I have to create brothers for each object
			print "projekt " . $o->name(). "<br>";
			print "id = " . $o->id() . "<br>";
			print "connections = ";
			$conns = $o->connections_from(array(
				"type" => "RELTYPE_PRJ_EVENT",
			));
			// create_brother
			print sizeof($conns);
			print "<br><br>";
			foreach($conns as $conn)
			{
				$to_obj = $conn->to();
				$tmp = $to_obj->get_original();
				$to_oid = $tmp->id();
				print "# ";
				print $conn->prop("reltype") . " ";
				print $to_obj->name() . " ";
				$p_obj = new object($to_obj->prop("parent"));
				print $p_obj->name() . "<bR>";
				// but first check, whether I already have an object with that parent!

				print_r($to_obj);
				if ($sub2parent[$to_oid])
				{
					print "brother already exists under $brother_parent<br>";
				}
				else
				{
					print "creating a brother under $brother_parent<br>";
				};
				print "<hr>";
				$to_obj->create_brother($brother_parent);
			};
			// I have to clone those, you know
			print "<br><br>";
		};
		print "oh, man, this is SO cool!";


	}
};
?>
