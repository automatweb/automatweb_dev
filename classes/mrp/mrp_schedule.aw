<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_schedule.aw,v 1.5 2005/02/01 19:48:44 voldemar Exp $
// mrp_schedule.aw - Ajaplaan?
/*

@classinfo syslog_type=ST_MRP_SCHEDULE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

*/

### resource types
define ("MRP_RESOURCE_MACHINE", 1);
define ("MRP_RESOURCE_OUTSOURCE", 2);

### states
define ("MRP_STATUS_NEW", 1);
define ("MRP_STATUS_PLANNED", 2);
define ("MRP_STATUS_INPROGRESS", 3);
define ("MRP_STATUS_ABORTED", 4);
define ("MRP_STATUS_DONE", 5);
define ("MRP_STATUS_LOCKED", 6);
define ("MRP_STATUS_OVERDUE", 7);
define ("MRP_STATUS_DELETED", 8);

### misc
define ("MRP_DATE_FORMAT", "j/m/Y H.i");

ini_set ("max_execution_time", "120");

class mrp_schedule extends class_base
{
	# how many seconds from time() to start schedule, should be at least scheduler's maximum execution time (int)
	var $schedule_start = 300;

	# day end for the time when scheduling takes place. (timestamp)
	var $scheduling_day_end;

	# years (float)
	var $schedule_length = 2;

	# for each resource. resource_id1 => array (start_range1 => array (start1 => length1, ...), ...), ....
	var $reserved_times = array ();

	# scheduled jobs : resource_id1 => array (id1 => array (starttime1, length1), ...), ...
	var $job_schedule = array ();

	var $project_schedule = array ();
	var $schedulable_resources = array ();
	var $workspace_id;
	var $jobs_table = "mrp_job";

	# array (res_id => array (), ...)
	var $resource_data = array ();

	var $parameter_due_date_overdue_slope = 0.5;
	var $parameter_due_date_overdue_intercept = 10;
	var $parameter_due_date_decay = 0.05;
	var $parameter_due_date_intercept = 0.1;
	var $parameter_priority_slope = 0.8;

	var $range_scale = array (//!!! tihedamalt, kogu planeeritava perioodi peale need piirkonnad teha vbl automaatselt.
		0,
		86400,
		172800,
		259200,
		345600,
		432000,
		518400,
		604800,
		864000,
		1209600,
		1814400,
		3456000,
		6048000,
		12096000,
	);

	var $timings = array ();

	function mrp_schedule ()
	{
		$this->init (array (
			"tpldir" => "mrp/mrp_schedule",
			"clid" => CL_MRP_SCHEDULE,
		));
	}

	function initialize ($arr)
	{
		if (is_oid ($arr["mrp_workspace"]))
		{
			$workspace = obj ($arr["mrp_workspace"]);
			$this->workspace_id = $workspace->id ();
		}
		else
		{
			exit (VIGA1);//!!!
		}

		$this->schedule_start = time () + $this->schedule_start;

		### get parameters
		$schedule_length = $workspace->prop ("parameter_schedule_length");
		$this->schedule_length = is_numeric ($schedule_length) ? $schedule_length : $this->schedule_length;
		$this->schedule_length = $this->schedule_length * 31536000;

		$schedule_start = $workspace->prop ("parameter_schedule_start");
		$this->schedule_start = is_numeric ($schedule_start) ? (time () + floor ($schedule_start)) : $this->schedule_start;

		$schedule_start_dateinfo = getdate ($this->schedule_start);
		$this->scheduling_day_end = strtotime ("today", $this->schedule_start) + 86399;

		### get combined_priority parameters
		$p1 = $workspace->prop ("parameter_due_date_overdue_slope");
		$p2 = $workspace->prop ("parameter_due_date_overdue_intercept");
		$p3 = $workspace->prop ("parameter_due_date_decay");
		$p4 = $workspace->prop ("parameter_due_date_intercept");
		$p5 = $workspace->prop ("parameter_priority_slope");

		$this->parameter_due_date_overdue_slope = is_numeric ($p1) ? $p1 : $this->parameter_due_date_overdue_slope;
		$this->parameter_due_date_overdue_intercept = is_numeric ($p2) ? $p2 : $this->parameter_due_date_overdue_intercept;
		$this->parameter_due_date_decay = is_numeric ($p3) ? $p3 : $this->parameter_due_date_decay;
		$this->parameter_due_date_intercept = is_numeric ($p4) ? $p4 : $this->parameter_due_date_intercept;
		$this->parameter_priority_slope = is_numeric ($p5) ? $p5 : $this->parameter_priority_slope;

		### define timerange scale
		$range_scale = $workspace->prop ("parameter_timescale");
		$range_scale_unit = $workspace->prop ("parameter_timescale_unit");

		if ($range_scale)
		{
			$range_scale = explode (",", $range_scale);

			foreach ($range_scale as $key => $value)
			{
				$range_scale[$key] = ceil ($value * $range_scale_unit);
			}
		}
		else
		{
			$range_scale = $this->range_scale;
		}

		if (reset ($this->range_scale))
		{
			array_unshift ($this->range_scale, 0);
		}

		sort ($range_scale, SORT_NUMERIC);
		$this->range_scale = $range_scale;

		### get schedulable resources
		$resources_folder = $workspace->prop ("resources_folder");
		$resource_tree = new object_tree (array (
			"parent" => $resources_folder,
			"class_id" => array (CL_MRP_RESOURCE,CL_MENU),
		));
		$resource_list = $resource_tree->to_list ();
		$resource_list->filter (array (
			"class_id" => CL_MRP_RESOURCE,
			"type" => MRP_RESOURCE_MACHINE,
		), true);

		for ($resource = $resource_list->begin (); !$resource_list->end (); $resource = $resource_list->next ())
		{
			$this->schedulable_resources[] = $resource->id ();
		}
	}

/**
    @attrib name=create
	@param mrp_workspace required type=int
**/
	function create ($arr)
	{

/* timing */ timing ("initialize", "start");


		$this->initialize ($arr);


/* timing */ timing ("initialize", "end");
/* timing */ timing ("get used resources", "start");


		### get used resources
		$resources = array ();
		$this->db_query("SELECT DISTINCT `resource` FROM `" . $this->jobs_table . "` WHERE state = " . MRP_STATUS_NEW . " OR state = " . MRP_STATUS_PLANNED . " ORDER BY `resource`");

		while ($job = $this->db_next())
		{
			if (in_array ($job["resource"], $this->schedulable_resources))
			{
				$resources[] = $job["resource"];
			}
		}


/* timing */ timing ("get used resources", "end");
/* timing */ timing ("init_resource_data", "start");


		$this->init_resource_data ($resources);


/* timing */ timing ("init_resource_data", "end");
/* timing */ timing ("initiate resource timetables", "start");
// $res = 6672;
// $arr = ($this->get_closest_unavailable_period($res, (14*3600)));
	// echo date (MRP_DATE_FORMAT, ($this->schedule_start+$arr[0]))."|".$arr[1];
// arr ($this->resource_data[$res]);
	// exit;

		### initiate resource reserved times index
		if ($resources)
		{
			foreach ($resources as $resource_id)
			{
				if (is_oid ($resource_id) and in_array ($resource_id, $this->schedulable_resources))
				{
					foreach ($this->range_scale as $key => $start)
					{
						$this->reserved_times[$resource_id][$key] = array ($start => 0);
					}
				}
			}

			### reserve locked projects, if any
			$this->db_query ("SELECT `starttime`,`length`,`resource` FROM `" . $this->jobs_table . "` where `state`=" . MRP_STATUS_LOCKED . " AND `starttime`> " . $this->schedule_start . " AND `length` > 0");

			while ($job = $this->db_next ())
			{
				if (is_oid ($resource_id) and in_array ($resource_id, $schedulable_resources))
				{
					$this->reserve_time ($job["resource"], $job["starttime"], $job["length"]);
				}
			}
		}


/* timing */ timing ("initiate resource timetables", "end");
/* timing */ timing ("get all projects from db & initiate project array", "start");


		### get all projects from db
		$this->db_query ("SELECT DISTINCT mrp_case.* FROM mrp_case,aliases,objects WHERE
		objects.oid = mrp_case.oid AND
		objects.status != 0 AND
		aliases.source = mrp_case.oid AND
		aliases.target = " . $this->workspace_id . " AND
		aliases.reltype = 5 AND
		(mrp_case.state = " . MRP_STATUS_NEW . " OR mrp_case.state = " . MRP_STATUS_PLANNED .")"
		);
		$projects = array ();

		### initiate project array
		while ($project = $this->db_next ())
		{
			$projects[$project["oid"]] = array (
				"jobs" => array (),
				"starttime" => $project["starttime"],
				"due_date" => $project["due_date"],
				"customer_priority" => $project["customer_priority"],
				"project_priority" => $project["project_priority"],
			);
		}


/* timing */ timing ("get all projects from db & initiate project array", "end");
/* timing */ timing ("get all jobs from db", "start");


		### get all jobs from db
		$this->db_query ("SELECT * FROM `" . $this->jobs_table . "` WHERE (`state`=" . MRP_STATUS_NEW . " OR `state`=" . MRP_STATUS_LOCKED . " OR `state`=" . MRP_STATUS_PLANNED . ") AND `length` > 0 AND `project` != 0 AND `resource` != 0 AND `project` IS NOT NULL AND `resource` IS NOT NULL");


/* timing */ timing ("get all jobs from db", "end");
/* timing */ timing ("distribute jobs to projects", "start");


		### distribute jobs to projects & initiate successor indices
		$starttime_index = array ();
		$successor_index = array ();

		while ($job = $this->db_next())
		{
			if (array_key_exists ($job["project"], $projects))
			{
				$projects[$job["project"]]["jobs"][$job["exec_order"]] = $job;
				$prerequisites = explode (",", $job["prerequisites"]);

				foreach ($prerequisites as $prerequisite)
				{
					$successor_index[$prerequisite][] = $job["oid"];
				}
			}
		}


/* timing */ timing ("distribute jobs to projects", "end");
/* timing */ timing ("sort jobs in projects", "start");

		### sort jobs in all projects
		foreach ($projects as $project_id => $project)
		{
			ksort ($projects[$project_id]["jobs"]);
		}


/* timing */ timing ("sort jobs in projects", "end");
/* timing */ timing ("sort projects", "start");


		### sort projects for scheduling by priority
		uasort ($projects, array ($this, "project_priority_comparison"));


/* timing */ timing ("sort projects", "end");
/* timing */ timing ("schedule jobs total", "start");


		### schedule jobs in all projects
		foreach ($projects as $project_id => $project)
		{
			if (!is_array ($project["jobs"]))
			{
				continue;
			}

/* timing */ timing ("one project total", "start");

			$next_job_earliest_starttime = $projects[$project_id]["starttime"];

			### schedule project jobs
			foreach ($project["jobs"] as $key => $job)
			{

/* timing */ timing ("one job total", "start");
/* timing */ timing ("reserve time & modify earliest start", "start");


				$job_length = $job["pre_buffer"] + $job["length"] + $job["post_buffer"];
				$minstart = max ($next_job_earliest_starttime, $starttime_index[$job["oid"]]);

				if ( (in_array ($job["resource"], $this->schedulable_resources)) or ($project["state"] == MRP_STATUS_NEW) )
				{
					### schedule job next in line
					list ($scheduled_start, $job_length) = $this->reserve_time ($job["resource"], $minstart, $job_length);
					$this->job_schedule[$job["oid"]] = $scheduled_start;

					### modify earliest starttime for next unscheduled job in array.
					$next_job_earliest_starttime = $scheduled_start + $job_length;
				}
				else
				{
					### modify earliest starttime for next unscheduled job in array.
					$scheduled_start = $job["starttime"];
					$next_job_earliest_starttime = max (($minstart + $job_length), ($job["starttime"] + $job_length));
				}
					///!!! vaadata mis saab erinevate workflow graafide korral -- mitu eeldust88d, 1 t88 mitmele eeldust88ks

/* timing */ timing ("reserve time & modify earliest start", "end");
/* timing */ timing ("modify starttimes for next jobsin wf", "start");

				### modify earliest starttime for unscheduled jobs next in workflow
				if (is_array ($successor_index[$job["oid"]]))
				{
					foreach ($successor_index[$job["oid"]] as $successor_id)
					{
						if ($starttime_index[$successor_id] <= ($scheduled_start + $job_length))
						{
							$starttime_index[$successor_id] = $scheduled_start + $job_length;
						}
					}
				}

/* timing */ timing ("modify starttimes for next jobsin wf", "end");

				### set planned finishing date for project
				if (!isset ($project["jobs"][$key + 1]))
				{
					$this->project_schedule[$project_id] = $scheduled_start + $job_length;
				}

/* timing */ timing ("one job total", "end");
				// echo $project_id . "-" . $job["exec_order"] . ":" . date (MRP_DATE_FORMAT, $scheduled_start) . "<br>";
			}

/* timing */ timing ("one project total", "end");

		}

/* timing */ timing ("schedule jobs total", "end");
/* timing */ timing ("save schedule data", "start");

		$this->save ();

/* timing */ timing ("save schedule data", "end");
/* timing */ timing (null, "show");

	}

	function compute_due_date ()
	{
		$this->create ();
	}

	function save ()
	{
		if (is_array ($this->project_schedule) and is_array ($this->job_schedule))
		{
			aw_disable_acl ();

			foreach ($this->project_schedule as $project_id => $date)
			{
				if (!is_oid($project_id))
				{
					continue;
				}

				$project = obj ($project_id);
				$project->set_prop ("planned_date", $date);
				$project->set_prop ("state", MRP_STATUS_PLANNED);
				$project->save ();
			}

			foreach ($this->job_schedule as $job_id => $starttime)
			{
				if (!is_oid($job_id) || !(is_integer ($starttime)))
				{
					continue;
				}

				$job = obj ($job_id);
				$job->set_prop ("starttime", $starttime);
				$job->set_prop ("state", MRP_STATUS_PLANNED);
				$job->save ();
				echo $job_id . ":" . date (MRP_DATE_FORMAT, $starttime) . "<br>";
			}

			aw_restore_acl ();
		}
	}

/* --------------------------  PRIVATE METHODS ----------------------------- */

	function project_priority_comparison ($project1, $project2)
	{
		$due_date1 = $project1["due_date"] - $this->schedule_start;
		$due_date2 = $project2["due_date"] - $this->schedule_start;
		$project_priority1 = $project1["project_priority"];
		$project_priority2 = $project2["project_priority"];
		// $length1 = $project1["project_length"];
		// $length2 = $project2["project_length"];

		### function
		$value1 = $this->combined_priority ($due_date1, $project_priority1);
		$value2 = $this->combined_priority ($due_date2, $project_priority2);

		### return result
		if ($value1 > $value2)
		{
			$result = -1;
		}
		elseif ($value1 < $value2)
		{
			$result = 1;
		}
		else
		{
			$result = 0;
		}

		return $result;
	}

	function combined_priority ($x, $y)
	{
		if ($x <= 0)
		{
			$value = (((-1*$this->parameter_due_date_overdue_slope)*$x) + $this->parameter_due_date_overdue_intercept) + ($this->parameter_priority_slope*$y);
		}
		else
		{
			if ((($x*$this->parameter_due_date_decay) + $this->parameter_due_date_intercept) == 0)
			{
				echo $x . "-" . $this->parameter_due_date_decay . "-" . $this->parameter_due_date_intercept;
			}

			$value = (1/(($x*$this->parameter_due_date_decay) + $this->parameter_due_date_intercept)) + ($this->parameter_priority_slope*$y);
		}

		return $value;
	}

	function reserve_time ($resource_id, $start, $length)
	{
		### find range for given starttime
		$reserved_time = NULL;
		$start = ($start > $this->schedule_start) ? ($start - $this->schedule_start) : 0;
		$time_range = $this->find_range ($start);

		### find free space with right length/start
		while (isset ($this->reserved_times[$resource_id][$time_range]))
		{
/* timing */ timing ("reserve_time - sort reserved_times", "start");

			ksort ($this->reserved_times[$resource_id][$time_range], SORT_NUMERIC);

/* timing */ timing ("reserve_time - sort reserved_times", "end");

			reset ($this->reserved_times[$resource_id][$time_range]);

			### go through reserved times in current timerange to find place for job being scheduled
			foreach ($this->reserved_times[$resource_id][$time_range] as $start1 => $length1)
			{

/* timing */ timing ("reserve_time - get next reserved time start", "start");

				### get next reserved time start
				$start2 = false;
				$i = 0;

				while (isset ($this->reserved_times[$resource_id][$time_range + $i]))
				{
					if ($i > 0)
					{
						ksort ($this->reserved_times[$resource_id][$time_range + $i], SORT_NUMERIC);
						reset ($this->reserved_times[$resource_id][$time_range + $i]);

						if (current ($this->reserved_times[$resource_id][$time_range + $i]))
						{
							$start2 = key ($this->reserved_times[$resource_id][$time_range + $i]);
						}
						else
						{
							next ($this->reserved_times[$resource_id][$time_range + $i]);
							$start2 = key ($this->reserved_times[$resource_id][$time_range + $i]);
						}
					}
					else
					{
						if (next ($this->reserved_times[$resource_id][$time_range + $i]))
						{
							$start2 = key ($this->reserved_times[$resource_id][$time_range + $i]);
						}
						else
						{
							next ($this->reserved_times[$resource_id][$time_range + $i]);
							$start2 = key ($this->reserved_times[$resource_id][$time_range + $i]);
							prev ($this->reserved_times[$resource_id][$time_range + $i]);
						}

						prev ($this->reserved_times[$resource_id][$time_range + $i]);
					}

					if ($start2)
					{
						break;
					}
					else
					{
						$i++;
					}
				}

				if (!$start2)
				{
					$start2 = $this->schedule_length;
				}

/* timing */ timing ("reserve_time - get next reserved time start", "end");

				$d = ($start < ($start1 + $length1)) ? 0 : ($start - ($start1 + $length1));

				### check if requested space is available between start1 & start2
				if ( (($start1 + $length1 + $length + $d) <= $start2) and ($start2  >= ($start + $length)) )
				{
					$reserved_time = (($start1 + $length1) >= $start) ? ($start1 + $length1) : $start;
					list ($unavailable_start, $unavailable_length) = $this->get_closest_unavailable_period ($resource_id, $reserved_time);

					### check if reserved starttime is in an unavailable period & make starttime correction, shifting it to the end of that unavail. period
					if ($reserved_time < ($unavailable_start + $unavailable_length))
					{
						if (($unavailable_start + $unavailable_length + $length) <= $start2)
						{
							$reserved_time = ($unavailable_start + $unavailable_length);
							list ($unavailable_start, $unavailable_length) = $this->get_closest_unavailable_period ($resource_id, $reserved_time);
						}
						else
						{
							continue;
						}
					}

/* timing */ timing ("reserve_time - insert unavailable periods to job length", "start");

					### check if reserved time covers unavailable periods & make length correction if job fits in slices else start over
					if ($unavailable_length)
					{
						while (($reserved_time + $unavailable_length + $length) <= $start2)
						{
							$length += $unavailable_length;
							list ($unavailable_start2, $unavailable_length2) = $this->get_closest_unavailable_period ($resource_id, ($unavailable_start + $unavailable_length + 1));

							if ( (($reserved_time + $length) <= $unavailable_start2) or (!$unavailable_length2) or (!is_numeric ($unavailable_start2)) )
							{
								break;
							}
							else
							{
								$unavailable_length = $unavailable_length2;
								$unavailable_start = $unavailable_start2;
							}
						}
					}

/* timing */ timing ("reserve_time - insert unavailable periods to job length", "end");

					### insert reserved time into schedule
					if ($reserved_time)
					{
						$this->reserved_times[$resource_id][$time_range][$reserved_time] = $length;
					}
					else
					{
						continue;
					}

/* timing */ timing ("reserve_time - make corrections to timerange starting-times", "start");

					### make corrections to timerange starting-times
					$i = 1;

					while (isset ($this->reserved_times[$resource_id][$time_range + $i]))
					{
						$next_range_start = array_keys ($this->reserved_times[$resource_id][$time_range + $i], 0);
						$next_range_start = reset ($next_range_start);

						if ( (($reserved_time + $length) > $next_range_start) and $next_range_start )
						{
							unset ($this->reserved_times[$resource_id][$time_range + $i][$next_range_start]);
							$next_range_start = $this->range_scale[$time_range + $i + 1];

							if ( (($reserved_time + $length) < $next_range_start) and (($reserved_time + $length) < $start2) )
							{
								$this->reserved_times[$resource_id][$time_range + $i][($reserved_time + $length)] = 0;
							}
						}

						$i++;
					}

/* timing */ timing ("reserve_time - make corrections to timerange starting-times", "end");

					### return planned starttime
					$reserved_time = $this->schedule_start + $reserved_time;
					return array ($reserved_time, $length);
				}

				next ($this->reserved_times[$resource_id][$time_range]);
			}

			### if suitable slot not found in this start range, search next range
			$time_range++;
		}

		### ... slot not found
		return VIGA2;//!!! mis teha?

	}
		//!!! teha et kui konfigureeritakse ajaskaala laiemaks kui sched. length siis ....

	function find_range ($starttime)
	{
/* timing */ timing ("find_range", "start");

		$low = 0;
		$high = count ($this->range_scale) - 1;

		while ($low <= $high)
		{
			$mid = floor (($low + $high) / 2);
			$next = isset ($this->range_scale[$mid + 1]) ? $this->range_scale[$mid + 1] : ($this->schedule_length + 1);

			if ( ($starttime >= $this->range_scale[$mid]) and ($starttime < $next) )
			{

/* timing */ timing ("find_range", "end");

				return $mid;
			}
			else
			{
				if ($starttime < $this->range_scale[$mid])
				{
					$high = $mid - 1;
				}
				else
				{
					$low = $mid + 1;
				}
			}
		}
	}

	## returns start and length of next unavailable period after $time. if $time is in an unavail. period, that period's data is returned.
	function get_closest_unavailable_period ($resource_id, $time)
	{
/* timing */ timing ("get_closest_unavailable_period", "start");

		///!!! allhankeressurssidega on mingi asi.
		$closest_periods = array ();

		### get dateinfo
		$time = $time + $this->schedule_start;
		$time_dateinfo = getdate ($time);
		$timeunit_start = $time - ($time_dateinfo["hours"] * 3600 + $time_dateinfo["minutes"] * 60 + $time_dateinfo["seconds"]);

		### get closest global buffer
		if ($time > $this->scheduling_day_end)
		{
			$global_buffer_start = $timeunit_start + (86400 - $global_buffer_length);
		}
		else
		{
			$global_buffer_start = $timeunit_start + 86400 + (86400 - $global_buffer_length);
		}

		$closest_periods[$global_buffer_start] = $global_buffer_start + $this->resource_data[$resource_id]["global_buffer"];

		### get recurrences
		$closest_recurrences = array ();

		foreach ($this->resource_data[$resource_id]["recurrence_definitions"] as $recurrence)
		{
			$recurrence_start = $recurrence["start"] + (floor (($time - $recurrence["start"]) / $recurrence["interval"])) * $recurrence["interval"];
			$recurrence_end = $recurrence_start + $recurrence["length"];

			if ($recurrence_end <= $time)
			{
				$recurrence_start += $recurrence["interval"];
				$recurrence_end += $recurrence["interval"];
			}

			if ($recurrence_end > $time)
			{
				$closest_recurrences[$recurrence_start] = $recurrence_end;
			}
		}

		list ($recurrence_start, $recurrence_end) = $this->find_combined_range ($closest_recurrences, $time);
		$closest_periods[$recurrence_start] = $recurrence_end;

		### get closest separate unavailable period
		$first = true;

		foreach ($this->resource_data[$resource_id]["unavailable_periods"] as $period_start => $period_end)
		{
			if ($period_end > $time)
			{
				$closest_periods[$period_start] = $period_end;
				break;
			}
		}

		### combine buffer, recurrence & period
		list ($period_start, $period_end) = $this->find_combined_range ($closest_periods, $time);
		$start = ($period_start > $this->schedule_start) ? ($period_start - $this->schedule_start) : 0;
		$length = ($period_start > $this->schedule_start) ? ($period_end - $period_start) : ($period_end - $this->schedule_start);

// echo $time."[".$resource_id."]:".$start. "-".$length."<br>";
/* timing */ timing ("get_closest_unavailable_period", "end");

		return array ($start, $length);
	}

	### find and combine ranges closest to $value, exclude ranges that don't exceed $value directly or through overlapping ranges
	function find_combined_range ($ranges, $value)
	{
		ksort ($ranges, SORT_NUMERIC);
		reset ($ranges);
		$first = true;
		$start = false;
		$end = false;

		foreach ($ranges as $range_start => $range_end)
		{
			if ($range_start == $range_end)
			{
				continue;
			}

			if ($first)
			{
				$start = $range_start;
				$end = $range_end;
				$first = false;
			}

			$nextend = next ($ranges);

			if ($nextend)
			{
				$nextstart = key ($ranges);

				if ($range_end < $nextstart)
				{
					if ($range_end > $value)
					{
						break;
					}
					else
					{
						$start = $nextstart;
						$end = $nextend;
					}
				}
				else
				{
					$end = max ($nextend, $range_end, $end);
				}
			}
		}

		if ((!$start) or (!$end) or ($end <= $value))
		{
			$start = $value;
			$end = $value;
		}

		return array ($start, $end);
	}

	function init_resource_data ($resources)
	{
		foreach ($resources as $resource_id)
		{
			if (is_oid ($resource_id))
			{
				$resource = obj ($resource_id);
				$this->resource_data[$resource_id]["global_buffer"] = $resource->prop ("global_buffer");
				$i = $resource->instance ();
				$this->resource_data[$resource_id]["unavailable_periods"] = $i->get_unavailable_periods ($resource, $this->schedule_start, ($this->schedule_start + $this->schedule_length));
				$this->resource_data[$resource_id]["recurrence_definitions"] = $i->get_recurrent_unavailable_periods ($resource, $this->schedule_start, ($this->schedule_start + $this->schedule_length));
			}
		}
	}
}

function timing ($name, $action = "time")
{
	if ($_GET["showtimings"])
	{
		static $timings = array ();

		switch ($action)
		{
			case "time":
			case "start":
			case "end":
				list ($msec, $sec) = explode (" ", microtime ());
				$time = ((float) $msec + (float) $sec);

				if ($timings[$name]["start"])
				{
					$timings[$name]["sum"] += ($time - $timings[$name]["start"]);
					$timings[$name]["count"]++;
					$timings[$name]["start"] = 0;
				}
				else
				{
					$timings[$name]["start"] = $time;
				}
				break;

			case "show":
				echo "<pre>";

				foreach ($timings as $name => $timing)
				{
					echo "[" . $name . "] => " . ($timing["sum"] / $timing["count"]) . " (count = " . $timing["count"] . ")\n";
				}

				echo "</pre>";
				break;
		}
	}
}

?>
