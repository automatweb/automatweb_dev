<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/vcl/multi_calendar.aw,v 1.2 2005/04/05 13:52:35 kristo Exp $
class multi_calendar extends aw_template
{
	function multi_calendar()
	{
		$this->init("calendar/vcl");
	}


	function init_vcl_property($arr)
	{
		$rv = array();
		$prop = $arr["prop"];

		// mul on vaja teada neid _teisi_ kalendreid, et saaks otsida asju, mida vaja
		// need on selle kalendri küljes, mille seest ma seda sündmust vaatan

		// mis loogiliselt võttes viib sellele, et see otsing peaks asuma kalendri
		// küljes ja mitte sündmuse klassis. Aga ma testin seda asja siin

		$search_calendars = aw_global_get($prop["name"] . "_calendars");
		$search_duration = aw_global_get($prop["name"] . "_duration");

		//aw_session_del($prop["name"] . "_calendars");
		//aw_session_del($prop["name"] . "_duration");

		$pl = get_instance(CL_PLANNER);
		$cal_id = $pl->get_calendar_for_user();

		if (!is_oid($cal_id))
		{
			// siin peaks mingi vea tagastama tegelikult
			return false;
		};

		$cal_obj = new object($cal_id);
		$other_conns = $cal_obj->connections_from(array(
			"type" => "RELTYPE_OTHER_CALENDAR",
		));

		$options = array();

		foreach($other_conns as $conn)
		{
			$options[$conn->prop("to")] = $conn->prop("to.name");
		};

		$name = $prop["name"] . "_calendars";
		$rv[$name] = array(
			"name" => $name,
			"caption" => t("Kalendrid, millest otsida"),
			"type" => "chooser",
			"orient" => "vertical",
			"multiple" => 1,
			"options" => $options,
			"value" => $search_calendars,
		);


		$name = $prop["name"] . "_duration";
		$rv[$name] = array(
			"name" => $name,
			"caption" => t("Otsitava aja pikkus (hh:mm)"),
			"type" => "textbox",
			"size" => 5,
			"value" => $search_duration,
		);

		// search only of there were any calendars chosen
		if (sizeof($search_calendars) > 0)
		{
			$ol = new object_list(array(
				"class_id" => CL_PLANNER,
				"lang_id" => array(),
				"oid" => $search_calendars,
				"site_id" => array(),
			));

			$min_day_start = 0;
			$max_day_end = (23*3600) + 59;

			$parents = array();
			foreach($ol->arr() as $o)
			{
				$event_folders = $pl->get_event_folders(array("id" => $o->id()));

				$parents = array_merge($parents,$event_folders);
				$day_start = $o->prop("day_start");
				$ds = $day_start["hour"] * 3600 + $day_start["minute"];
				$day_end = $o->prop("day_end");
				$de = $day_end["hour"] * 3600 + $day_end["minute"];
				if ($ds > $min_day_start)
				{
					$min_day_start = $ds;
				};
				if ($de < $max_day_end)
				{
					$max_day_end = $de;
				};
			};

			if (sizeof($parents) > 0)
			{
				// and of course only if we have any valid event folders
				$parents = array_merge($parents,$pl->get_event_folders(array("id" => $cal_obj->id())));

				// start from midnight today
				list($d,$m,$y) = explode("-",date("d-m-Y"));
				$start_tm = date("U",mktime(0,0,0,$m,$d,$y));
				// and look ahead for the next 7 days
				$end_tm = time() + (7 * 86400);

				list($hour,$minute) = explode(":",$search_duration);

				$diff = ($hour * 3600) + ($minute * 60);
				$slices = array();

				// create a list of all possible time slices
				// 3600 is the interval for finding vacancies
				for ($i = $start_tm; $i <= $end_tm; $i = $i + 3600)
				{
					// but exclude those outside of the day time range
					$lim = date("H",$i) * 3600 + date("i",$i);
					$slice_end = $i + ($diff);
					$lim_end = date("H",$slice_end) * 3600 + date("i",$slice_end);
					$slicen_end -= 1;
					// $lim_end can overflow to the next day, so deal with that too
					if ($lim >= $min_day_start && $lim_end <= $max_day_end && $lim_end > $lim)
					{
						$slices[$i] = $slice_end;
					};
				};


				$ol_args = array(
					"parent" => $parents,
					"sort_by" => "planner.start",
					"class_id" => array(CL_CRM_MEETING,CL_TASK,CL_CRM_CALL,CL_CALENDAR_EVENT),
					"CL_CALENDAR_EVENT.start1" => new obj_predicate_compare(OBJ_COMP_BETWEEN, $start_tm, $end_tm),
				);

				$ol = new object_list($ol_args);

				// delete slices that have events in them
				foreach($ol->arr() as $event)
				{
					$e_start = $event->prop("start1");
					$e_end = $event->prop("end");
					foreach($slices as $skey => $sval)
					{
						if (between($skey,$e_start,$e_end))
						{
							unset($slices[$skey]);
						};

						if (between($sval,$e_start,$e_end))
						{
							unset($slices[$sval]);
						};
					};

				};

				// now we should have a list of available time slices, which we return

				$opts = array();

				/*
				load_vcl("table");
				$this->t = new aw_table(array("layout" => "generic"));

				$this->t->define_field(array(
					"name" => "calendar",
					"caption" => t("Kalender"),
				));

				$this->t->define_field(array(
					"name" => "from",
					"caption" => t("Alates"),
				));

				$this->t->define_field(array(
					"name" => "to",
					"caption" => t("Kuni"),
				));

				$this->t->define_chooser(array(
					"field" => "from",
					"caption" => t("XX"),
				));
				*/

				foreach($slices as $skey => $sval)
				{
					$m = locale::get_lc_date($skey,5);
					$w = strtoupper(locale::get_lc_weekday(date("w",$skey),true));
					$opts[$skey] = "${w}, ${m}" . date(" H:i",$skey) . " - " . date("H:i",$sval);
					/*
					$this->t->define_data(array(
						"from" => date("d-m-Y H:i",$skey),
						"to" => date("d-m-Y H:i",$sval),
					));
					*/
				};

				$rname = $prop["name"] . "_select_date";

				$rv[$rname] = array(
					"name" => $rname,
					"type" => "chooser",
					"orient" => "vertical",
					"options" => $opts,
					"caption" => t("Leitud ajad"),
				);


				/*
				$rv["table"] = array(
					"name" => "table",
					"type" => "table",
					"vcl_inst" => &$this->t,
				);
				*/
			};

		};

		return $rv;
	}

	function process_vcl_property($arr)
	{
		$name = $arr["prop"]["name"];
		//print "interesting data<br>";

		$calendars = $arr["request"]["${name}_calendars"];
		$duration = $arr["request"]["{$name}_duration"];
		$selected_date = $arr["request"]["{$name}_select_date"];

		

		

		//print "sd = $selected_date<br>";
		//print "kalendrid, millest otsida";
		//arr($calendars);


		if (sizeof($calendars) > 0)
		{
			if ($selected_date)
			{
				$event_obj = $arr["obj_inst"];
				foreach($calendars as $calendar)
				{
					$cal_obj = new object($calendar);
					$parent = $cal_obj->prop("event_folder");
					//print "pr = $parent<br>";
					$event_brother = $event_obj->create_brother($parent);
					$event_bo = new object($event_brother);
					$event_bo->set_prop("start1",$selected_date);
					$event_bo->save();
					//print "<br>";
				};
				//print "sd = $selected_date<br>";
				//arr($arr);
				$event_obj->set_prop("start1",$selected_date);
				$event_obj->save();


			};
			aw_session_set("${name}_calendars",$calendars);
			aw_session_set("${name}_duration",$duration);
			// nii, aga ma pean mingit datat edasi andma ju. 
			// kuidas ma seda teen?

		}
			

		//print "kestvus";
		//arr($duration);





		/*
		$event_obj  = $arr["obj_inst"];

		$parents = array();
		
		$planners = new object_list(array(
			"class_id" => CL_PLANNER,
			"status" => STAT_ACTIVE,
			"site_id" => array(),
		));

		foreach($planners->arr() as $planner_obj)
		{
			if (is_oid($planner_obj->prop("event_folder")))
			{
				$parents[] = $planner_obj->prop("event_folder");
			};
		};		
		*/

		//arr($arr);
	}


};
?>
