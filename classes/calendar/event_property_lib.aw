<?php
// $Header: /home/cvs/automatweb_dev/classes/calendar/Attic/event_property_lib.aw,v 1.10 2004/10/06 15:24:55 duke Exp $
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

		global $XX5;
		if ($XX5)
		{
			arr($arr);
		};

		// I need a list of all brothers of this object!
		// so that I can show active ones

		$orig = $arr["obj_inst"]->get_original();

		$olist = new object_list(array(
			"brother_of" => $orig->id(),
		));

		$prjlist = array();
		for($o =& $olist->begin(); !$olist->end(); $o =& $olist->next())
		{
			$xlist[$o->parent()] = 1;
		};

		$all_props = array();
		$prop = $arr["prop"];

		// väga lahe - nüüd tuleb veel grupeerimine teha
		if (1 == $prop["all_projects"])
		{
			$olist = new object_list(array(
				"class_id" => CL_PROJECT,
			));

			$by_parent = array();
			$first = true;

			for($o =& $olist->begin(); !$olist->end(); $o =& $olist->next())
			{
				$pr = new object($o->parent());
				if ($first)
				{
					$first_project = $o->id();
					$first = false;
				};

				// aah, but that IS the bloody problem .. I can't enter events in that way

				// now how do I get that grouping shit to work?
				$all_props["prj_" . $o->id()] = array(
					"type" => "checkbox",
					"name" => "prj" . "[" .$o->id() . "]",
					"caption" => html::href(array(
						"url" => $this->mk_my_orb("change",array("id" => $o->id()),CL_PROJECT),
						"caption" => "<font color='black'>" . $o->name() . "</font>",
					)),
					"ch_value" => $xlist[$o->id()],
					"value" => 1,
				);

			};
			$pr = get_instance(CL_PROJECT);
			$pr->_recurse_projects(0,$first_project);


			global $XX5;
			if ($XX5)
			{
				arr($by_parent);
				arr($pr->prj_map);
			};
		}
		else
		{
			// ajaa .. aga see on nüüd see asi et näitab ainult neid projekte kus ma ise olen
			// mul aga on vaja et ta näitaks kõiki projekte

			$users = get_instance("users");
			$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
			$conns = $user->connections_to(array(
				"from.class_id" => CL_PROJECT,
				"sort_by" => "from.name",
			));


			foreach($conns as $conn)
			{
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

		// determine all projects that this event is part of,
		// compare that list to the selected items in the form
		// and put the event (create a brother) into all the projects
		// that it wasn't already a part of
		$xlist = array();
		for($o =& $olist->begin(); !$olist->end(); $o =& $olist->next())
		{
			if ($o->id() != $o->brother_of())
			{
				$xlist[$o->id()] = $o->parent();
			};
		};

		//arr($xlist);

		$awt->stop("retr-project-connections");

		$new_ones = array();
		if (is_array($arr["request"]["prj"]))
		{
			$new_ones = $arr["request"]["prj"];
		};

		$prj_inst = get_instance(CL_PROJECT);
		$awt->start("disconnect-from-project");


		foreach($xlist as $obj_id => $folder_id)
		{
			if (!$new_ones[$obj_id])
			{
				$bo = new object($obj_id);
				$bo->delete();
			};
			unset($new_ones[$obj_id]);
		};

		$awt->stop("disconnect-from-project");
		$awt->start("connect-to-project");

		$clones = $transx = array();
		$event_clones = $event_obj->connections_from(array(
			"type" => "RELTYPE_COPY",
		));

		foreach($event_clones as $event_clone)
		{
			$clones[] = $event_clone->prop("to");
		};

		obj_set_opt("no_auto_translation", 1);
		$translations = $event_obj->connections_from(array(
			"type" => RELTYPE_TRANSLATION,
		));

		foreach($translations as $translation)
		{
			$transx[] = $translation->prop("to");
		};
		obj_set_opt("no_auto_translation", 0);

		// uut venda ei looda kui ta juba olemas on

		foreach($new_ones as $new_id => $whatever)
		{
			$event_obj->create_brother($new_id);

			// aga vend tuleb luua iga keele alla kuhu sündmus pandud on!

			// ja tuleks ka kontrollida kas see seos on juba olemas või ei

			// seos projektist sündmusse
			/*
			$prj_inst->connect_event(array(
				"id" => $new_id,
				"event_id" => $event_obj->id(),
			));
			*/

			foreach($transx as $trans_item)
			{
				obj_set_opt("no_auto_translation", 1);
				$trans_obj = new object($trans_item);
				obj_set_opt("no_auto_translation", 0);
			
				dbg::p1("translation is " . $trans_obj->id() . "/" . $trans_obj->lang());
				
				$trans_brother = $trans_obj->create_brother($new_id);
				$trans_brother_obj = new object($trans_brother);

				// by default tehakse tõlge ju aktiivse keele koodiga
				$trans_brother_obj->set_lang($trans_obj->lang());
				$trans_brother_obj->save();

				// kloonil pole alguskuupäeva, vaat mis :(
				dbg::p1("created translation with id " . $trans_brother_obj->id());
			};

			foreach($clones as $clone_item)
			{
				/*
				$clone_obj = new object($clone_item);
				$clone_obj->create_brother($new_id);
				*/

				// ja tõlked on ju kaaa vaja kloonida! ooh fuck
				/*
				$prj_inst->connect_event(array(
					"id" => $new_id,
					"event_id" => $clone_item,
				));
				*/
			};

			// lisaks tuleb koopiad korralikult ära connectida
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
		$use_date = aw_global_get("date");
		if (empty($use_date))
		{
			$use_date = date("d-m-Y");
		};
		list($d,$m,$y) = explode("-",$use_date);

		$tm = mktime(0,0,0,$m,$d,$y);

		classload("date_calc");

		$range = get_date_range(array(
			"time" => $tm,
			"type" => "day",
		));


		$this->vars(array(
			"date" => date("d.m Y",$tm),
			"prevlink" => aw_url_change_var("date",$range["prev"]),
			"nextlink" => aw_url_change_var("date",$range["next"]),
		));

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
						$ev_obj = new object($event["id"]);
						$ev_obj = $ev_obj->get_original();
						if ($ev_obj->class_id() != CL_CALENDAR_VACANCY)
						{
							$evstr .= $ev_obj->name() . "<br>";
							$free = false;
						};
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
