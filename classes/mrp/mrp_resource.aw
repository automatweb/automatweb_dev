<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_resource.aw,v 1.26 2005/03/22 09:06:57 kristo Exp $
// mrp_resource.aw - Ressurss
/*

EMIT_MESSAGE(MSG_MRP_RESCHEDULING_NEEDED)

@classinfo syslog_type=ST_MRP_RESOURCE relationmgr=yes no_status=1

@groupinfo grp_resource_schedule caption="Kalender"
@groupinfo grp_resource_joblist caption="Tööleht" submit=no
@groupinfo grp_resource_settings caption="Seaded"
@groupinfo grp_resource_unavailable caption="Tööajad"
	@groupinfo grp_resource_unavailable_work caption="T&ouml;&ouml;ajad" parent=grp_resource_unavailable
	@groupinfo grp_resource_unavailable_una caption="Kinnised ajad" parent=grp_resource_unavailable


@default table=objects
@default field=meta
@default method=serialize

@default group=general
	@property category type=text editonly=1
	@caption Kategooria

	@property type type=select
	@caption Tüüp

	@property state type=chooser editonly=1 multiple=0
	@caption Ressursi staatus

	@property concurrent_threads type=textbox default=1
	@comment Positiivne täisarv
	@caption Samaaegseid töid enim


@default group=grp_resource_schedule
	@property resource_calendar type=text store=no no_caption=1
	@caption Tööd


@default group=grp_resource_joblist
	@property job_list type=table store=no editonly=1 no_caption=1
	@caption Tööleht


@default group=grp_resource_settings
	@property default_pre_buffer type=textbox
	@caption Vaikimisi eelpuhveraeg (h)

	@property default_post_buffer type=textbox
	@caption Vaikimisi järelpuhveraeg (h)

	@property global_buffer type=textbox default=4
	@caption Päeva üldpuhver (h)


@default group=grp_resource_unavailable_work

	@property work_hrs_recur type=releditor reltype=RELTYPE_RECUR_WRK mode=manager props=name,start,end,time,length table_fields=name,start,end,time,length
	@caption T&ouml;&ouml;ajad

@default group=grp_resource_unavailable_una

	@property unavailable_recur type=releditor reltype=RELTYPE_RECUR use_form=emb mode=manager props=name,start,end,time,length,recur_type,interval_daily,interval_weekly,interval_yearly table_fields=name,start,end,time,length,recur_type
	@caption Kinnised ajad

	@property unavailable_weekends type=checkbox ch_value=1
	@caption Ei t&ouml;&ouml;ta n&auml;dalavahetustel

	@property unavailable_dates type=textarea rows=5 cols=50
	@comment Formaat: alguskuupäev.kuu, tund:minut - lõppkuupäev.kuu, tund:minut; alguskuupäev.kuu, ...
	@caption Kinnised päevad (Formaat: <span style="white-space: nowrap;">p.k, h:m - p.k, h:m;</span><br /><span style="white-space: nowrap;">p.k, h:m - p.k, h:m;</span><br /> ...)

// --------------- RELATION TYPES ---------------------

@reltype MRP_SCHEDULE value=2 clid=CL_PLANNER
@caption Ressursi kalender

@reltype MRP_OWNER value=3 clid=CL_MRP_WORKSPACE
@caption Ressursi omanik

@reltype RECUR value=4 clid=CL_RECURRENCE
@caption Kordus

@reltype RECUR_WRK value=5 clid=CL_RECURRENCE
@caption T&ouml;&ouml;aja kordus

*/

### resource types
define ("MRP_RESOURCE_SCHEDULABLE", 1);
define ("MRP_RESOURCE_NOT_SCHEDULABLE", 2);
define ("MRP_RESOURCE_SUBCONTRACTOR", 3);

### states
define ("MRP_STATUS_NEW", 1);
define ("MRP_STATUS_PLANNED", 2);
define ("MRP_STATUS_INPROGRESS", 3);
define ("MRP_STATUS_ABORTED", 4);
define ("MRP_STATUS_DONE", 5);
define ("MRP_STATUS_LOCKED", 6);
define ("MRP_STATUS_PAUSED", 7);
define ("MRP_STATUS_DELETED", 8);
define ("MRP_STATUS_ONHOLD", 9);
define ("MRP_STATUS_ARCHIVED", 10);

define ("MRP_STATUS_RESOURCE_AVAILABLE", 10);
define ("MRP_STATUS_RESOURCE_INUSE", 11);
define ("MRP_STATUS_RESOURCE_OUTOFSERVICE", 12);

### misc
define ("MRP_DATE_FORMAT", "j/m/Y H.i");

class mrp_resource extends class_base
{
	function mrp_resource()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_resource",
			"clid" => CL_MRP_RESOURCE
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		$this_object = &$arr["obj_inst"];

		if ($arr["new"])
		{
			$this->mrp_workspace = $arr["request"]["mrp_workspace"];
		}

		switch($prop["name"])
		{
			case "category":
				### get workspace object "owning" current object
				$workspace = $this_object->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

				if ($workspace)
				{
					$resources_folder_id = $workspace->prop ("resources_folder");
					$parent_folder_id = $this_object->parent ();
					$parents = "";

					while ($resources_folder_id and ($parent_folder_id != $resources_folder_id))
					{
						$parent = obj ($parent_folder_id);
						$parents = "/" . $parent->name () . $parents;
						$parent_folder_id = $parent->parent ();
					}

					$prop["value"] = t("/Ressursid") . $parents;
				}
				else
				{
					$prop["value"] = t("Ressurss ei kuulu ühessegi ressursihaldussüsteemi.");
				}
				break;

			case "resource_calendar":
				$prop["value"] = $this->create_resource_calendar ($arr);
				break;

			case "type":
				$prop["options"] = array (
					MRP_RESOURCE_SCHEDULABLE => t("Ressursi kasutust planeeritakse"),
					MRP_RESOURCE_NOT_SCHEDULABLE => t("Ressursi kasutust ei planeerita"),
					MRP_RESOURCE_SUBCONTRACTOR => t("Ressurss on allhange"),
				);
				break;

			case "state":
				$applicable_states = array (
					MRP_STATUS_RESOURCE_AVAILABLE,
					MRP_STATUS_RESOURCE_INUSE,
					MRP_STATUS_RESOURCE_OUTOFSERVICE,
				);
				$prop["value"] = (in_array ($prop["value"], $applicable_states)) ? $prop["value"] : 0;
				$prop["options"] = array (
					0 => "M&auml;&auml;ramata",
					MRP_STATUS_RESOURCE_AVAILABLE => t("Vaba"),
					MRP_STATUS_RESOURCE_INUSE => t("Kasutusel"),
					MRP_STATUS_RESOURCE_OUTOFSERVICE => t("Suletud"),
				);
				break;

			case "job_list":
				$this->create_job_list_table ($arr);
				break;

			case "default_pre_buffer":
			case "default_post_buffer":
			case "global_buffer":
				$prop["value"] = $prop["value"] / 3600;
				break;
		}

		return $retval;
	}

	function callback_mod_reforb ($arr)
	{
		if ($this->mrp_workspace)
		{
			$arr["mrp_workspace"] = $this->mrp_workspace;
		}
	}

	function set_property ($arr = array ())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch ($prop["name"])
		{
			case "default_pre_buffer":
			case "default_post_buffer":
			case "global_buffer":
				$prop["value"] = round ($prop["value"] * 3600);
				break;

			case "work_hrs_recur":
				$prop["value"]["recur_type"] = 1;
            	$prop["value"]["interval_daily"] = 1;
				break;
		}

		return $retval;
	}

	function callback_post_save ($arr)
	{
		$this_object =& $arr["obj_inst"];

		### connect newly created obj. to workspace from which the req. was made
		if ($arr["new"] and is_oid ($arr["request"]["mrp_workspace"]))
		{
			$workspace = obj ($arr["request"]["mrp_workspace"]);
			$resources_folder = $workspace->prop ("resources_folder");
			$this_object->connect (array (
				"to" => $workspace,
				"reltype" => "RELTYPE_MRP_OWNER",
			));
			$this_object->set_parent ($resources_folder);
			$this_object->save ();
		}

		$applicable_types = array (
			MRP_RESOURCE_SCHEDULABLE,
			MRP_RESOURCE_SUBCONTRACTOR,
		);

		if (in_array ($this_object->prop ("type"), $applicable_types))
		{
			$workspace = $arr["obj_inst"]->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

			if ($workspace)
			{
				post_message (MSG_MRP_RESCHEDULING_NEEDED, array ("mrp_workspace" => $workspace->id ()));
			}
		}
	}

	function create_job_list_table ($arr)
	{
		$this_object =& $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];

		$table->define_field(array(
			"name" => "client",
			"caption" => t("Klient"),
			"sortable" => 1,
			"align" => "center"
		));
		$table->define_field(array(
			"name" => "project",
			"caption" => t("Projekt"),
			"sortable" => 1,
			"align" => "center"
		));
		$table->define_field(array(
			"name" => "name",
			"caption" => t("Töö"),
			"sortable" => 1,
			"align" => "center"
		));
		$table->define_field(array(
			"name" => "starttime",
			"caption" => t("Alustamisaeg"),
			"sortable" => 1,
			"align" => "center"
		));
		$table->define_field(array(
			"name" => "modify",
			"caption" => t("Ava"),
			"align" => "center"
		));

		$table->set_default_sortby ("starttime");
		$table->set_default_sorder ("asc");
		$table->draw_text_pageselector (array (
			"records_per_page" => 50,
		));

		$list = new object_list(array(
			"class_id" => CL_MRP_JOB,
			"resource" => $this_object->id (),
			// "starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, time (), mktime (23, 59, 59)),
		));
		$jobs = $list->arr ();

		foreach ($jobs as $job_id => $job)
		{
			$starttime = date (MRP_DATE_FORMAT, $job->prop ("starttime"));
			$project = $client = "";
			if (is_oid ($job->prop ("project") && $this->can("view", $job->prop("project"))))
			{
				$p = obj($job->prop("project"));
				$project = html::get_change_url($p->id(), array("return_url" => urlencode(aw_global_get("REQUEST_URI"))), $p->name());

				if (is_oid($p->prop("customer") && $this->can("view", $p->prop("customer"))))
				{
					$c = obj($p->prop("customer"));
					$client = html::get_change_url($c->id(), array("return_url" => urlencode(aw_global_get("REQUEST_URI"))), $c->name());
				}
			}

			$change_url = $this->mk_my_orb ("change", array (
				"id" => $job_id,
				"return_url" => urlencode (aw_global_get ('REQUEST_URI')),
				"group" => "",
			), "mrp_job");

			$table->define_data (array (
				"modify" => html::href (array (
					"caption" => t("Ava"),
					"url" => $change_url,
					)),
				"project" => $project,
				"name" => $job->name (),
				"starttime" => $starttime,
				"client" => $client
			));
		}
	}

	function create_resource_calendar ($arr)
	{
		$this_object =& $arr["obj_inst"];

		classload("vcl/calendar");
		$calendar = new vcalendar (array ("tpldir" => "mrp_calendar"));
		$calendar->init_calendar (array ());
		$calendar->configure (array (
			"overview_func" => array (&$this, "get_overview"),
		));
		$range = $calendar->get_range (array (
			"date" => $arr["request"]["date"],
			"viewtype" => $arr["request"]["viewtype"],
		));
		$start = $range["start"];
		$end = $range["end"];

		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"resource" => $this_object->id (),
			"starttime" => new obj_predicate_compare (OBJ_COMP_BETWEEN, $start, $end),
		));

		if ($list->count () > 0)
		{
			for ($job =& $list->begin(); !$list->end(); $job =& $list->next())
			{
				//$project = is_oid ($job->prop ("project")) ? obj ($job->prop ("project")) : NULL;
				$project = is_object ($project) ? $project->name () : "...";
				$calendar->add_item (array (
					"timestamp" => $job->prop ("starttime"),
					"data" => array(
						"name" => $job->prop ("name"),
						"link" => $this->mk_my_orb ("change",array ("id" => $job->id ()), "mrp_job"),
					),
				));
			}
		}

		return $calendar->get_html ();
	}

	function get_overview ($arr = array())
	{
		$start = time() - (24*3600*60);
		$end = time() + (24*3600*60);

		for($i = $start; $i < $end; $i += (24*3600))
		{
			$ret[$i] = aw_url_change_var("viewtype", "week", aw_url_change_var("date", date("d", $i)."-".date("m", $i)."-".date("Y", $i)));
		}
		return $ret;
	}

	function _get_unavailable_dates ($dates, $period_start, $period_end)
	{
		$unavailable_dates = array ();
		$dates = explode (";", $dates);
		$pattern = 		"/([1-9]|\d{2})\s*\.\s*([1-9]|\d{2})\s*\,\s*(\d{1,2})\s*(\:\s*(\d{1,2})\s*)*\-" .
								"\s*([1-9]|\d{2})\s*\.\s*([1-9]|\d{2})\s*\,\s*(\d{1,2})\s*(\:\s*(\d{1,2}))*/S";
		$period_start_year = date ("Y", $period_start);
		$period_start_mon = date ("n", $period_start);
		$period_start_day = date ("j", $period_start);
		$period_end_year = date ("Y", $period_end);
		$period_end_mon = date ("n", $period_end);
		$period_end_day = date ("j", $period_end);

		foreach ($dates as $date)
		{
			$match = preg_match (&$pattern, $date, $datedefinition);
			list ($NULL, $start_day, $start_mon, $start_hour, $NULL, $start_min, $end_day, $end_mon, $end_hour, $NULL, $end_min) = $datedefinition;

			if ($match and (mktime ($start_hour, $start_min, 0, $start_mon, $start_day, $period_start_year) < mktime ($end_hour, $end_min, 0, $end_mon, $end_day, ($period_start_year + 1))))
			{
				$year = $period_start_year;

				while ($year <= $period_end_year)
				{
					if (
						(($year != $period_start_year) and ($year != $period_end_year)) or
						(($year == $period_start_year) and ($start_mon >= $period_start_mon) and ($start_day >= $period_start_day))
						// (($year == $period_end_year) and ($end_mon <= $period_end_mon) and ($end_day <= $period_end_day))
					)
					{
						$start = mktime ($start_hour, $start_min, 0, $start_mon, $start_day, $year);
						$end = mktime ($end_hour, $end_min, 0, $end_mon, $end_day, $year);
						$end = ($end < $start) ? mktime ($end_hour, $end_min, 0, $end_mon, $end_day, ($year + 1)) : $end;



						foreach ($unavailable_dates as $start_defined => $end_defined)
						{
							if ( (($start >= $start_defined) and ($start <= $end_defined)) or (($end >= $start_defined) and ($end <= $end_defined)) )
							{
								unset ($unavailable_dates[$start_defined]);
								$start = min ($start, $start_defined);
								$unavailable_dates[$start] = max ($end, $end_defined);
							}
							else
							{
								$unavailable_dates[$start] = $end;
							}
						}
					}

					$year++;
				}
			}
		}

		ksort ($unavailable_dates);
		return $unavailable_dates;
	}

	function get_unavailable_periods ($resource, $start, $end)
	{
		$unavailable_periods = array ();
		$unavailable_periods = $this->_get_unavailable_dates ($resource->prop ("unavailable_dates"), $start, $end);
		return $unavailable_periods;
	}

	function get_recurrent_unavailable_periods ($resource, $start, $end)
	{
/* dbg */ if ($resource->id () == 6670  ) {
/* dbg */ $this->mrpdbg=1;
/* dbg */ }

		### unavailable recurrences
		$recurrent_unavailable_periods = array ();
		$start = mktime (0, 0, 0, date ("m", $start), date ("d", $start), date("Y", $start));
		$end = mktime (0, 0, 0, date ("m", $end), date ("d", $end), date("Y", $end));

		if ($resource->prop ("unavailable_weekends"))
		{
			$weekend_start = $this->get_week_start ($start) + (5 * 86400);
			$recurrent_unavailable_periods[] = array (
				"length" => 172800,
				"start" => $weekend_start,
				"end" => $end,
				"interval" => 604800,
			);
		}

		foreach ($resource->connections_from (array ("type" => "RELTYPE_RECUR")) as $connection)
		{
			$recurrence = $connection->to ();

			if ( !(($recurrence->prop ("start") > $end) or ($recurrence->prop ("end") < $start)) )
			{
				switch ($recurrence->prop ("recur_type"))
				{
					case "1": //day
						$interval = $recurrence->prop ("interval_daily");
						$interval = round (($interval ? $interval : 1) * 86400);
						break;

					case "2": //week
						$interval = $recurrence->prop ("interval_weekly");
						$interval = round (($interval ? $interval : 1) * 86400 * 7);
						break;

					case "3": //month
						continue;
						break;

					case "4": //year
						$interval = $recurrence->prop ("interval_yearly");
						$interval = round (($interval ? $interval : 1) * 86400 * 365);
						break;
				}

				$recurrence_starttime = $recurrence->prop ("time");
				$recurrence_starttime = explode (":", $recurrence_starttime);
				$recurrence_starttime_hours = $recurrence_starttime[0] ? (int) $recurrence_starttime[0] : 0;
				$recurrence_starttime_minutes = $recurrence_starttime[1] ? (int) $recurrence_starttime[1] : 0;
				$recurrence_starttime = $recurrence_starttime_hours * 3600 + $recurrence_starttime_minutes * 60;

				$recurrent_unavailable_periods[] = array (
					"length" => round ($this->safe_settype_float ($recurrence->prop ("length")) * 3600),
					"start" => $recurrence->prop ("start"),
					"time" => $recurrence_starttime,
					"end" => $recurrence->prop ("end"),
					"interval" => $interval,
				);
			}
		}


		### add workhours (available recurrences)
		$recurrent_available_periods = array ();

		foreach ($resource->connections_from (array ("type" => "RELTYPE_RECUR_WRK")) as $connection)
		{
			$recurrence = $connection->to ();

			if ( !(($recurrence->prop ("start") > $end) or ($recurrence->prop ("end") < $start)) )
			{
				$interval = 86400;
				list ($recurrence_time_hours, $recurrence_time_minutes) = explode (":", $recurrence->prop ("time"), 2);
				$recurrence_time = abs ((int) $recurrence_time_hours) * 3600 + abs ((int) $recurrence_time_minutes) * 60;
				$recurrence_length = round ($this->safe_settype_float ($recurrence->prop ("length")) * 3600);

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "recurrent_available_period time:" . $recurrence_time . "<br>";
// /* dbg */ }

				$recurrent_available_periods[] = array (
					"length" => $recurrence_length,
					"start" => $recurrence->prop ("start"),
					"time" => $recurrence_time,
					"end" => $recurrence->prop ("end"),
					"interval" => $interval,
				);
			}
		}

/* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "recurrent_available_periods:";
// /* dbg */ arr ($recurrent_available_periods);
/* dbg */ }

		### transmute recurrently available periods to unavailables
		### throw away erroneous definitions
		foreach ($recurrent_available_periods as $key => $available_period)
		{
			if ( ($available_period["start"] >= $available_period["end"]) or ($available_period["length"] > 86400) or ($available_period["length"] < 1) )
			{
				unset ($recurrent_available_periods[$key]);
			}
		}

/* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "recurrent_available_periods after errorcheck:";
// /* dbg */ arr ($recurrent_available_periods);
// /* dbg */ exit;
/* dbg */ }

		### find combinations of available periods
		$combination_breakpoints = array ($start, $end);

		foreach ($recurrent_available_periods as $available_period)
		{
			if (($available_period["start"] > $start) and ($available_period["start"] < $end))
			{
				$combination_breakpoints[] = $available_period["start"];
			}

			if (($available_period["end"] > $start) and ($available_period["end"] < $end))
			{
				$combination_breakpoints[] = $available_period["end"];
			}
		}

		### make unavailable recurrence definitions according to these combinations
		usort ($recurrent_available_periods, array ($this, "sort_recurrences_by_start"));
		sort ($combination_breakpoints, SORT_NUMERIC);
		$interval = 86400;

		foreach ($combination_breakpoints as $bp_key => $breakpoint)
		{
			if (isset ($combination_breakpoints[$bp_key + 1]))
			{
				$combination_start = $breakpoint;
				$combination_end = $combination_breakpoints[$bp_key + 1];
				$combination = array ();

				foreach ($recurrent_available_periods as $available_period)
				{
					if ( ($available_period["start"] <= $combination_start) and ($available_period["end"] >= $combination_end) )
					{
						$combination[] = $available_period;
					}
				}

				usort ($combination, array ($this, "sort_recurrences_by_time"));

				foreach ($combination as $key => $available_period)
				{
					$time = ($available_period["time"] + $available_period["length"]) % $interval;

					if (isset ($combination[$key + 1]))
					{
						$end_time = $combination[$key + 1]["time"];
					}
					else
					{
						$end_time = $combination[0]["time"];
					}

					if ($end_time > $time)
					{
						$length = $end_time - $time;
					}
					else
					{
						$length = $end_time + ($interval - $time);
					}

					$recurrent_unavailable_periods[] = array (
						"length" => $length,
						"start" => $combination_start,
						"end" => $combination_end,
						"time" => $time,
						"interval" => $interval,
					);
				}
			}
		}

// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "return recurrent_unavailable_periods:";
// /* dbg */ foreach ($recurrent_unavailable_periods as $key => $value){ echo "<hr>";
// /* dbg */ echo "length: " . ($value["length"]/3600).  "h<br>";
// /* dbg */ echo "start: " . date (MRP_DATE_FORMAT, $value["start"]).  "<br>";
// /* dbg */ echo "end: " . date (MRP_DATE_FORMAT, $value["end"]).  "<br>";
// /* dbg */ echo "time: " . date ("H.i", mktime(0,0,0,1,1,2005) + $value["time"]).  "<br>";
// /* dbg */ echo "interval: " . ($value["interval"]/3600).  "h<br>";}
// /* dbg */ }

		return $recurrent_unavailable_periods;
	}

	function get_week_start ($time) //!!! not really dst safe
	{
		$date = getdate ($time);
		$wday = $date["wday"] ? ($date["wday"] - 1) : 6;
		$week_start = $time - ($wday * 86400 + $date["hours"] * 3600 + $date["minutes"] * 60 + $date["seconds"]);
		$nodst_hour = (int) date ("H", $week_start);

		switch ($nodst_day_hour)
		{
			case 1:
				$week_start = $week_start - 3600;
				break;

			case 23:
				$week_start = $week_start + 3600;
				break;

			case 2:
				$week_start = $week_start - 2*3600;
				break;

			case 22:
				$week_start = $week_start + 2*3600;
				break;

			case 0:
			default:
				$week_start = $week_start;
				break;
		}

		return $week_start;
	}

	function safe_settype_float ($value)
	{
		$parts1 = explode (",", $value, 2);
		$parts2 = explode (".", $value, 2);
		$parts = (count ($parts2) == 1) ? $parts1 : $parts2;
		$value = (float) ((isset ($parts[0]) ? ((int) $parts[0]) : 0) . "." . (isset ($parts[1]) ? ((int) $parts[1]) : 0));
		return $value;
	}

	function sort_recurrences_by_start ($recurrence1, $recurrence2)
	{
		if ($recurrence1["start"] > $recurrence2["start"])
		{
			$result = 1;
		}
		elseif ($recurrence1["start"] < $recurrence2["start"])
		{
			$result = -1;
		}
		else
		{
			$result = 0;
		}

		return $result;
	}

	function sort_recurrences_by_time ($recurrence1, $recurrence2)
	{
		if ($recurrence1["time"] > $recurrence2["time"])
		{
			$result = 1;
		}
		elseif ($recurrence1["time"] < $recurrence2["time"])
		{
			$result = -1;
		}
		else
		{
			$result = 0;
		}

		return $result;
	}

/**
    @attrib name=start_job
	@param resource required type=int
**/
	function start_job ($arr)
	{
		if (is_oid ($arr["resource"]))
		{
			$resource = obj ($arr["resource"]);
		}
		else
		{
			return false;
		}

		switch ($resource->prop ("state"))
		{
			case MRP_STATUS_RESOURCE_AVAILABLE:
				$resource->set_prop ("state", MRP_STATUS_RESOURCE_INUSE);
				$resource->save ();
				return true;

			default:
				return false;
		}
	}

/**
    @attrib name=stop_job
	@param resource required type=int
**/
	function stop_job ($arr)
	{
		if (is_oid ($arr["resource"]))
		{
			$resource = obj ($arr["resource"]);
		}
		else
		{
			return false;
		}

		switch ($resource->prop ("state"))
		{
			case MRP_STATUS_RESOURCE_AVAILABLE:
				return MRP_STATUS_RESOURCE_AVAILABLE;

			case MRP_STATUS_RESOURCE_INUSE:
				$resource->set_prop ("state", MRP_STATUS_RESOURCE_AVAILABLE);
				$resource->save ();
				return MRP_STATUS_RESOURCE_INUSE;
				break;

			case MRP_STATUS_RESOURCE_OUTOFSERVICE:
				return MRP_STATUS_RESOURCE_OUTOFSERVICE;
				break;

			default:
				return false;
		}
	}
}

?>
