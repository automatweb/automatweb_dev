<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/event_property_lib.aw,v 1.7 2004/08/02 10:48:12 duke Exp $
// Shared functionality for event classes
class event_property_lib extends aw_template
{
	function event_property_lib()
	{
		$this->init(array(
			"tpldir" => "calendar",
		));
	}

	function project_selector($arr)
	{
		// see annab connectionid kõigist projektidest, mis viitavad sellele sündmusele
		// which of course is bad.

		// I need a list of all brothers of this object!
		// so that I can show active ones
		$e_conns = $arr["obj_inst"]->connections_to(array(
			"from.class_id" => CL_PROJECT,
		));

		$orig = $arr["obj_inst"]->get_original();

		$olist = new object_list(array(
			"brother_of" => $orig->id(),
		));

		$prjlist = array();
		for($o =& $olist->begin(); !$olist->end(); $o =& $olist->next())
		{
			$xlist[$o->parent()] = 1;
		};

		//arr($xlist);
		//arr($olist);

		/*
		$prjlist = array();
		foreach($e_conns as $conn)
		{
			$prjlist[$conn->prop("from")] = 1;
		};
		*/

		$users = get_instance("users");
		$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
		$conns = $user->connections_to(array(
			"from.class_id" => CL_PROJECT,
			"sort_by" => "from.name",
		));

		$all_props = array();

		foreach($conns as $conn)
		{
			/*
			print $conn->prop("from");
			print "<br>";
			*/
			$all_props["prj_" . $conn->prop("from")] = array(
				"type" => "checkbox",
				"name" => "prj" . "[" .$conn->prop("from") . "]",
				"caption" => html::href(array(
					"url" => $this->mk_my_orb("change",array("id" => $conn->prop("from")),"project"),
					"caption" => "<font color='black'>" . $conn->prop("from.name") . "</font>",
				)),
				"ch_value" => $xlist[$conn->prop("from")],
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

		$orig = $arr["obj_inst"]->get_original();

		$olist = new object_list(array(
			"brother_of" => $orig->id(),
		));

		$xlist = array();
		for($o =& $olist->begin(); !$olist->end(); $o =& $olist->next())
		{
			$xlist[$o->id()] = $o->parent();
		};

		$awt->stop("retr-project-connections");

		$new_ones = array();
		if (is_array($arr["request"]["prj"]))
		{
			$new_ones = $arr["request"]["prj"];
		};

		$prj_inst = get_instance(CL_PROJECT);
		$awt->start("disconnect-from-project");

		//foreach($e_conns as $conn)
		foreach($xlist as $obj_id => $folder_id)
		{
			if (!$new_ones[$obj_id])
			{
				$prj_inst->disconnect_event(array(
					//"id" => $conn->prop("from"),
					"event_id" => $obj_id,
				));
			};
			unset($new_ones[$obj_id]);
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

	// your basic view, shows calendars from left to right
	function calendar_others($arr)
	{
		$event_obj  = $arr["obj_inst"];
		$brlist = new object_list(array(
			"brother_of" => $event_obj->id(),
		));

		$plrlist = array();

		$current_start = $event_obj->prop("start1");

		$pl = get_instance(CL_PLANNER);

		for($o =& $brlist->begin(); !$brlist->end(); $o =& $brlist->next())
		{
			//if ($o->id() != $event_obj->id())
			//{
				$menu_id = $o->parent();
				$menu_obj = new object($menu_id);

				$cal_conns = $menu_obj->connections_to(array(
					"type" => 6 // EVENT_FOLDER,
				));

				$first = reset($cal_conns);
				if (is_object($first))
				{
					$cal_obj = $first->from();
					$plrlist[$cal_obj->id()] = $cal_obj->id();
				};
			//};
		};

		if (sizeof($plrlist) == 0)
		{
			return false;
		};

		$this->read_template("others.tpl");

		$tt = $tc = "";

		// XXX: get date from url
		list($d,$m,$y) = explode("-",date("d-m-Y"));

		// XXX: arvestada kalendris määratud päeva alguse ja lõpu aegu
		$day_start = mktime(9,0,0,$m,$d,$y);
		$day_end = mktime(21,0,0,$m,$d,$y);

		$step = 60 * 60; // 1. tund

		$first = true;
		foreach($plrlist as $pl_id)
		{
			$pl_obj = new object($pl_id);
			$events = $pl->get_event_list(array(
				"id" => $pl_id,
				"start" => $day_start,
				"end" => $day_end,
			));

			$this->vars(array(
				"calendar_name" => $pl_obj->name(),
			));
			$tt .= $this->parse("one_calendar");

			$cells = "";
			for ($ts = $day_start; $ts <= $day_end; $ts = $ts + $step)
			{
				$evstr = "";
				$free = true;
				foreach($events as $event)
				{
					if (between($event["start"],$ts,$ts+$step-1))
					{
						$free = false;
						$ev_obj = new object($event["id"]);
						$ev_obj = $ev_obj->get_original();
						$evstr .= $ev_obj->name() . "<br>";
					};
				};

				$this->vars(array(
					"event" => $evstr,
					"time" => date("H:i",$ts),
					"selector_cell" => "",
					"bgcolor" => $free ? "#CCFFCC" : "#FFCCCC",
				));


				if ($first)
				{
					$this->vars(array(
						"time" => date("H:i",$ts),
						"checked" => checked(between($current_start,$ts,$ts+$step-1)),
						"event_sel_id" => $ts,
					));

					$this->vars(array(
						"selector_cell" => $this->parse("selector_cell"),
					));
				}

				$cells .= $this->parse("cell");

			};

			$this->vars(array(
				"cell" => $cells,
			));

			$tc .= $this->parse("one_calendar_content");

			$first = false;
		};
		
		// 

		$this->vars(array(
			"one_calendar" => $tt,
			"one_calendar_content" => $tc,
		));

		$all_props = array();
		$all_props[$arr["prop"]["name"]] = array(
			"type" => "text",
			"name" => $arr["prop"]["name"],
			"value" => $this->parse(),
			"caption" => $arr["prop"]["caption"],
			"no_caption" => $arr["prop"]["no_caption"],
		);

		return $all_props;

		
	}

	function process_other_selector($arr)
	{
		$event_obj = $arr["obj_inst"];
		$event_obj->set_prop("start1",$arr["request"]["start_time"]);

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

   function callb_human_name($arr)
   {
      return html::href(array(
         "url" => $this->mk_my_orb("change",array(
            "id" => $arr["id"],
            "return_url" => urlencode(aw_global_get("REQUEST_URI")),
         ),CL_CRM_PERSON),
         "caption" => $arr["name"],
      ));
   }

	
	function participant_selector($arr)
	{
		classload("vcl/table");
		$rtrn = array();
		$table = new vcl_table();
		
		$table->define_field(array(
							'name' => 'name',
							'caption' => 'Nimi',
							'sortable' => '1',
							'callback' => array(&$this,'callb_human_name'),
							'callb_pass_row' => true,
		));
		$table->define_field(array(
							'name' => 'phone',
							'caption' => 'Telefon',
							'sortable' => '1',
		));

		$table->define_field(array(
							'name' => 'email',
							'caption' => 'E-post',
							'sortable' => '1',
		));
		$table->define_field(array(
							'name' => 'rank',
							'caption' => 'Ametinimetus',
							'sortable' => '1',
		));
		$table->define_chooser(array(
						'name' => 'check',
						'field' => 'id',
						'caption' => 'X',
		));

		$conns = $arr['obj_inst']->connections_to(array());
		//arr($conns);
		foreach($conns as $conn)
		{
			$person = get_instance(CL_CRM_PERSON);
			if($conn->prop('from.class_id')==CL_CRM_PERSON)
			{
				$data = $person->fetch_person_by_id(array('id'=>$conn->prop('from')));
				$table->define_data(array(
					'id' => $conn->prop('from'),
					'name' => $data['name'],
					'phone' => $data['phone'],
					'email' => $data['email'],
					'rank' => $data['rank'],
				));
			}
		}
		return array('tabel'=> array('type'=>'table','vcl_inst'=>&$table,'no_caption' => 1));
	}

};
?>
