<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/event_property_lib.aw,v 1.2 2004/04/06 10:39:23 duke Exp $
// Shared functionality for event classes
class event_property_lib extends core
{
	function event_property_lib()
	{
		$this->init();
	}

	function project_selector($arr)
	{
		$e_conns = $arr["obj_inst"]->connections_to(array(
			"from.class_id" => CL_PROJECT,
		));

		$prjlist = array();
		foreach($e_conns as $conn)
		{
			$prjlist[$conn->prop("from")] = 1;
		};

		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
		$conns = $user->connections_to(array(
			"from.class_id" => CL_PROJECT,
			"sort_by" => "from.name",
		));

		$all_props = array();

		foreach($conns as $conn)
		{
			$all_props["prj_" . $conn->prop("from")] = array(
				"type" => "checkbox",
				"name" => "prj" . "[" .$conn->prop("from") . "]",
				"caption" => html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $conn->prop("from")),"project"),
					"caption" => "<font color='black'>" . $conn->prop("from.name") . "</font>",
				)),
				"ch_value" => $prjlist[$conn->prop("from")],
				"value" => 1,
			);
		};

		return $all_props;
	}

	function process_project_selector($arr)
	{
		$event_obj = $arr["obj_inst"];
		// 1) retreieve all connections that this event has to projects
		// 2) remove those that were not explicitly checked in the form
		// 3) create new connections which did not exist before
		global $awt;
		$awt->start("retr-project-connections");
		$e_conns = $event_obj->connections_to(array(
			"from.class_id" => CL_PROJECT,
		));
		$awt->stop("retr-project-connections");

		$new_ones = array();
		if (is_array($arr["request"]["prj"]))
		{
			$new_ones = $arr["request"]["prj"];
		};

		$prj_inst = get_instance("groupware/project");
		$awt->start("disconnect-from-project");

		foreach($e_conns as $conn)
		{
			if (!$new_ones[$conn->prop("from")])
			{
				$prj_inst->disconnect_event(array(
					"id" => $conn->prop("from"),
					"event_id" => $event_obj->id(),
				));
			};
			unset($new_ones[$conn->prop("from")]);
		};
		$awt->stop("disconnect-from-project");
		$awt->start("connect-to-project");

		foreach($new_ones as $new_id => $whatever)
		{
			$prj_inst->connect_event(array(
				"id" => $new_id,
				"event_id" => $event_obj->id(),
			));
		};

		$awt->stop("connect-to-project");
		if (aw_global_get("uid") == "duke")
		{
			print "<pre>";
			print_r($awt->summaries());
			print "</pre>";
		};

	}

	function calendar_selector($arr)
	{
		$brlist = new object_list(array(
			"brother_of" => $arr["obj_inst"]->id(),
			// ignore site id's for this list
			"site_id" => array(),
		));

		for($o =& $brlist->begin(); !$brlist->end(); $o =& $brlist->next())
		{
			$plrlist[$o->parent()] = $o->id();
		};

		$all_props = array();

		foreach($this->get_planners_with_folders() as $row)
		{
			//if ($row["event_folder"] != $arr["obj_inst"]->parent())
			//{
				$folderdat = $this->get_object($row["event_folder"]);

				$all_props["link_calendars_" . $row["oid"]] = array(
					"type" => "checkbox",
					"name" => "link_calendars" . "[" .$row["oid"] . "]",
					"caption" => html::href(array(
						"url" => $this->mk_my_orb("change",array("id" => $row["oid"]),"planner"),
						"caption" => "<font color='black'>" . $row["name"] . "</font>",
					)),
					"ch_value" => $row["oid"],
					"value" => isset($plrlist[$row["event_folder"]]) ? $row["oid"] : 0,
				);
			//};
		};

	
		return $all_props;
	}

	function process_calendar_selector($arr)
	{
		$event_obj  = $arr["obj_inst"];
		// 1) retrieve all connections that this event has to projects
		// 2) remove those that were not explicitly checked in the form
		// 3) create new connections which did not exist before

		// urk .. I need all brothers of the event object.

		$brlist = new object_list(array(
			"brother_of" => $event_obj->id(),
		));

		$plrlist = array();

		for($o =& $brlist->begin(); !$brlist->end(); $o =& $brlist->next())
		{
			if ($o->id() != $event_obj->id())
			{
				$plrlist[$o->parent()] = $o->id();
			};
		};

		$all_props = array();

		$new_ones = array();
		if (is_array($arr["request"]["link_calendars"]))
		{
			$new_ones = $arr["request"]["link_calendars"];
		};

		foreach($plrlist as $plid => $evid)
		{
			if (!$new_ones[$plid])
			{
				$ev_obj = new object($evid);
				$ev_obj->delete();
			};
			unset($new_ones[$plid]);
		};

		// now new_ones sisaldab nende kalendrite id-sid, millega ma pean seose looma
		foreach($new_ones as $plid)
		{
			$plr_obj = new object($plid);
                	$bro = $event_obj->create_brother($plr_obj->prop("event_folder"));
		};

	}

	////
	// !Returns a list of planners that have event folders ..
	function get_planners_with_folders($args = array())
	{
		$retval = array();

		$planners = new object_list(array(
			"class_id" => CL_PLANNER,
			"sort_by" => "name",
			"status" => STAT_ACTIVE,
			"site_id" => array(),
		));

                for($o = $planners->begin(); !$planners->end(); $o = $planners->next())
                {
                        if ($o->prop("event_folder") != 0)
                        {
                                $retval[] = array(
                                        "oid" => $o->id(),
                                        "name" => $o->name(),
                                        "event_folder" => $o->prop("event_folder"),
                                );

                        };
                };
                return $retval;
        }




};
?>
