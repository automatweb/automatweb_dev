<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_schedule.aw,v 1.3 2005/01/14 10:34:35 voldemar Exp $
// mrp_schedule.aw - Ajaplaan?
/*

@classinfo syslog_type=ST_MRP_SCHEDULE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

*/

### resource types
define ("MRP_RESOURCE_PHYSICAL", 1);
define ("MRP_RESOURCE_OUTSOURCE", 2);
define ("MRP_RESOURCE_GLOBAL_BUFFER", 3);

### states
define ("MRP_STATUS_NEW", 1);
define ("MRP_STATUS_PLANNED", 2);
define ("MRP_STATUS_INPROGRESS", 3);
define ("MRP_STATUS_ABORTED", 4);
define ("MRP_STATUS_DONE", 5);
define ("MRP_STATUS_LOCKED", 6);
define ("MRP_STATUS_OVERDUE", 7);

### misc
define ("MRP_DATE_FORMAT", "j/m/Y H.i");

define ("MRP_AVAIL_TIME_FIXED", 1);
define ("MRP_AVAIL_TIME_MOVABLE", 2);

define ("MRP_GLOBAL_BUFFER_PERIOD", 86400);



class mrp_schedule extends class_base
{
	var $schedule_start = 300; // how many seconds from time() to start schedule, should be at least scheduler's maximum execution time (int)
	var $schedule_length = 2; // years (float)
	var $resource_data = array (); // resource_id => array (global_buffer)
	var $available_times = array (); // unscheduled "slots" for each resource.
	var $schedule = array (); // scheduled jobs : id => starttime

	var $parameter_due_date_overdue_slope = 0.5;
	var $parameter_due_date_overdue_intercept = 10;
	var $parameter_due_date_decay = 0.05;
	var $parameter_due_date_intercept = 0.1;
	var $parameter_priority_slope = 0.8;

	var $jobs_table = "mrp_job";

	var $timescale = array (
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
		12096000,// tihedamalt, kogu planeeritava perioodi peale need piirkonnad teha vbl automaatselt.!!!
	);

	function mrp_schedule()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_schedule",
			"clid" => CL_MRP_SCHEDULE,
		));

		$workspace = obj ($arr["mrp_workspace"]);
		$this->schedule_start = time () + $this->schedule_start;

		### get parameters
		$schedule_length = $workspace->prop ("parameter_schedule_length");
		$this->schedule_length = is_numeric ($schedule_length) ? $schedule_length : $this->schedule_length;
		$this->schedule_length = $this->schedule_length * 31536000;

		$schedule_start = $workspace->prop ("parameter_schedule_start");
		$this->schedule_start = is_numeric ($schedule_start) ? (time () + floor ($schedule_start)) : $this->schedule_start;

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

		### define timescale
		$timescale = $workspace->prop ("parameter_timescale");
		$timescale_unit = $workspace->prop ("parameter_timescale_unit");

		if ($timescale)
		{
			$timescale = explode (",", $timescale);

			foreach ($timescale as $key => $value)
			{
				$timescale[$key] = ceil ($value * $timescale_unit);
			}
		}
		else
		{
			$timescale = $this->timescale;
		}

		array_unshift ($timescale, 0);
		sort ($timescale, SORT_NUMERIC);
		$this->timescale = $timescale;
	}

	function compute_due_date ()
	{
		$this->create ();
	}

/**
    @attrib name=create
	@param mrp_workspace required type=int
**/
	function create ($arr)
	{
		$workspace = obj ($arr["workspace"]);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### get used resources
		$resources = array ();
		$this->db_query("SELECT DISTINCT `resource` FROM `" . $this->jobs_table . "` WHERE state = " . MRP_STATUS_NEW . " OR state = " . MRP_STATUS_PLANNED . " ORDER BY `resource`");

		while ($job = $this->db_next())
		{
			$resources[] = $job["resource"];
		}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["get used resources"] = gettime () - $time;                                                                                                             ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### initiate resource free space index
		if ($resources)
		{
			foreach ($resources as $resource_id)
			{
				$index = array ();

				foreach ($this->timescale as $key => $start)
				{
					if ($this->timescale[$key + 1])
					{
						$length = $this->timescale[$key + 1] - $start - 1;
					}
					else
					{
						$length = $this->schedule_length - $this->timescale[$key];
			}

					$resource = obj ($resource_id);
					$this->resource_data[$resource_id]["global_buffer"] = $resource->prop ("global_buffer") * 3600;
					$this->resource_data[$resource_id]["day_counter"] = 0;
					$this->available_times[$resource_id][$key] = array ($start => array ($length, MRP_AVAIL_TIME_FIXED));
				}
			}

			$this->init_resource_schedules($resources);

			### add locked jobs, if any
			$this->db_query ("SELECT `starttime`,`length`,`resource` FROM `" . $this->jobs_table . "` where `state`=" . MRP_STATUS_LOCKED);

			while ($job = $this->db_next ())
			{
				$this->reserve_time ($job["resource"], $job["starttime"], $job["length"]);//!!! mida teha lukustatud t88de puhul Gpuhvriga???
			}
		}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["initiate resource timetables"] = gettime () - $time;                                                                                               ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### get all projects from db
		$this->db_query ("SELECT DISTINCT mrp_case.* FROM mrp_case,aliases WHERE
		aliases.source = mrp_case.oid AND
		aliases.target = " . $workspace->id () . " AND
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["get all projects from db & initiate project array"] = gettime () - $time;                                                               ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### get all jobs from db
		$this->db_query ("SELECT * FROM `" . $this->jobs_table . "` WHERE `state`=" . MRP_STATUS_NEW . " OR `state`=" . MRP_STATUS_PLANNED);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["get all jobs from db"] = gettime () - $time;                                                                                                            ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### distribute jobs to projects & initiate minimal starttime and successor indices
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["distribute jobs to proj."] = gettime () - $time;                                                                                                       ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### sort jobs in all projects
		foreach ($projects as $project_id => $project)
		{
			ksort ($projects[$project_id]["jobs"]);
		}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["sort jobs in projects"] = gettime () - $time;                                                                                                           ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### sort projects for scheduling by priority
		uasort ($projects, array ($this, "project_priority_comparison"));

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["sort projects"] = gettime () - $time;                                                                                                                       ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### schedule jobs
		foreach ($projects as $project_id => $project)
		{
			if (!is_array ($project["jobs"]))
			{
				break;
			}

			$next_job_earliest_starttime = $projects[$project_id]["starttime"];

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$t1 = gettime ();                                                                                                                                                                     ////////
$sc1 = 0;                                                                                                                                                                                  ////////
$sc2 = 0;                                                                                                                                                                                  ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			foreach ($project["jobs"] as $job)
			{

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$t2 = gettime ();                                                                                                                                                                     ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

				### schedule next job in line
				// $minstart = $starttime_index[$job["oid"]] ? $starttime_index[$job["oid"]] : $next_job_earliest_starttime;
				$minstart = $next_job_earliest_starttime;
				$relative_minstart = $minstart - $this->schedule_start;
				$starttime = $this->reserve_time ($job["resource"], $relative_minstart, $job["length"]);
				$this->schedule[$job["oid"]] = $starttime;
///!!! vaadata mis saab erinevate workflow graafide korral -- mitu eeldust88d, 1 t88 mitmele eeldust88ks

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$st2 += (gettime () - $t2);                                                                                                                                                    ////////
$t2 = gettime ();                                                                                                                                                                     ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

				### modify earliest starttime for next unscheduled job .///!!! vaja yldse?
				if ( !($next_job_earliest_starttime > ($starttime + $job["pre_buffer"] + $job["length"] + $job["post_buffer"])) )
				{
					$next_job_earliest_starttime = $starttime + $job["pre_buffer"] + $job["length"] + $job["post_buffer"];
				}

				### modify earliest starttime for unscheduled jobs next in workflow
				if (is_array ($successor_index[$job["oid"]]))
				{
					foreach ($successor_index[$job["oid"]] as $successor_id)
					{
						if ( !($starttime_index[$successor_id] > ($starttime + $job["pre_buffer"] + $job["length"] + $job["post_buffer"])) )
						{
							$starttime_index[$successor_id] = $starttime + $job["pre_buffer"] + $job["length"] + $job["post_buffer"];
						}
					}
				}

				### set planned finishing date for project
				if (!next($project["jobs"]))
				{
					$this->project_schedule[$project_id] = $starttime + $job["pre_buffer"] + $job["length"] + $job["post_buffer"];
				}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$st3 += (gettime () - $t2);                                                                                                                                                    ////////
$t2 = gettime ();                                                                                                                                                                     ////////
$sc2++;                                                                                                                                                                                   ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$st1 += (gettime () - $t1);                                                                                                                                                    ////////
$sc1++;                                                                                                                                                                                   ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["schedule jobs - avg one project total"] = $sc1 ? ($st1 / $sc1) : 0;                                                                      ////////
$times["schedule jobs - avg schedule job - finplaceintimetable"] = $sc2 ? ($st2 / $sc2) : 0;                                         ////////
$times["schedule jobs - avg modify starttimes for next jobs"] = $sc2 ? ($st3 / $sc2) : 0;                                              ////////
$times["schedule jobs total"] = gettime () - $time;                                                                                                             ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$this->save ();

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// $times["save schedule data"] = gettime () - $time;                                                                                                             ////////
// $time = gettime ();                                                                                                                                                                  ////////
// arr ($times);                                                                                                                                                                            ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		echo "Valmis.";
	}

	function save ()
	{
		foreach ($this->project_schedule as $project_id => $date)
		{
			if (!is_oid($project_id) || !$this->can("view", $project_id))
			{
				continue;
			}

			$project = obj ($project_id);
			$project->set_prop ("planned_date", $date);
			$project->set_prop ("state", MRP_STATUS_PLANNED);
			$project->save ();
		}

		foreach ($this->schedule as $job_id => $starttime)
		{
			if (!is_oid($job_id) || !$this->can("view", $job_id))
			{
				continue;
			}

			$job = obj ($job_id);
			$job->set_prop ("starttime", $starttime);
			$job->set_prop ("state", MRP_STATUS_PLANNED);
			$job->save ();
			// echo $job_id . ":" . $starttime . "<br>";
		}
	}

/* --------------------------  PRIVATE METHODS ----------------------------- */

	function project_priority_comparison ($project1, $project2)
	{
		$due_date1 = $project1["due_date"] - $this->schedule_start;
		$due_date2 = $project2["due_date"] - $this->schedule_start;
		$customer_priority1 = $project1["customer_priority"];
		$customer_priority2 = $project2["customer_priority"];
		$project_priority1 = $project1["project_priority"];
		$project_priority2 = $project2["project_priority"];
		// $L1 = $project1["project_length"];
		// $L2 = $project2["project_length"];

		### function
		$value1 = $this->combined_priority ($due_date1, $customer_priority1, $project_priority1);
		$value2 = $this->combined_priority ($due_date2, $customer_priority2, $project_priority2);

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

	function combined_priority ($x, $y, $z)
			{
		$y = $y + $z;

		if ($x <= 0)
				{
			$value = (((-1*$this->parameter_due_date_overdue_slope)*$x) + $this->parameter_due_date_overdue_intercept) + ($this->parameter_priority_slope*$y);
		}
		else
		{
			if ((($x*$this->parameter_due_date_decay)+$this->parameter_due_date_intercept) == 0)
			{
				echo $x . "-" . $this->parameter_due_date_decay . "-" . $this->parameter_due_date_intercept;
			}

			$value = (1/(($x*$this->parameter_due_date_decay)+$this->parameter_due_date_intercept)) + ($this->parameter_priority_slope*$y);
		}

		return $value;
	}

	function init_resource_schedules ($resources)
					{
		foreach ($resources as $resource_id)
		{
			$resource = obj ($resource_id);
			$i = $resource->instance ();
			$unavailable_times = $i->get_unavailable_times ($resource);

			foreach ($unavailable_times as $start => $length)
			{
				$this->reserve_time ($resource_id, $start, $length, true);
					}
				}
			}

	function reserve_time ($resource, $min_starttime, $length, $buffer = false)
	{
		$reserved_time = NULL;
		$time_range = $this->find_range ($min_starttime);
		ksort ($this->available_times[$resource][$time_range], SORT_NUMERIC);

		### find free space with right length/start
		while ($this->available_times[$resource][$time_range])
		{
			foreach ($this->available_times[$resource][$time_range] as $slot_start => $slot_properties)
			{
				if ( ($slot_start <= $min_starttime) and (($slot_start + $slot_properties[0]) <= ($min_starttime + $length)) )
				{
					### reserve time
					if ($space_start < $starttime)
					{
						$reserved_time = $starttime;
					}
					else
					{
						$reserved_time = $space_start;
		}

					### delete or reduce free space in array
					$slot_size = $slot_properties[0];
					$slot_type = $slot_properties[1];
					$new_preceding_slot_start = $slot_start;
					$new_preceding_slot_length = $reserved_time - $slot_start;
					$new_succeeding_slot_start = $reserved_time + $length;
					$new_succeeding_slot_length = ($slot_start + $slot_size) - $new_succeeding_slot_start;
					unset ($this->available_times[$resource][$time_range][$slot_start]);

					### insert new preceding & succeeding space to available_times array
					if ($new_preceding_slot_length > 0)
					{
						$this->available_times[$resource][$time_range][$new_preceding_slot_start] = array ($new_preceding_slot_length, $slot_type);
	}

					if ($new_succeeding_slot_length > 0)
	{
						$this->available_times[$resource][$time_range][$new_succeeding_slot_start] = array ($new_succeeding_slot_length, $slot_type);
					}

					if (!$buffer)
					{
						### reserve time for global buffer if last job in a day
						$time = $this->schedule_start + $reserved_time;
						$date = getdate ($time);
						$day_start = $time - ($date["hours"] * 3600 + $date["minutes"] * 60 + $date["seconds"]);

						if ( ($reserved_time + $length) >= ($day_start + $global_buffer) )
						{
//!!! vbl v6iks nii teha : global_buffer_length = (length/86400) * global_buffer ???// see hajutaks Gpuhvri laiali -- hea? halb ???
							// $global_buffer_start = $day_start + (86400 - $global_buffer);
							// $overflow = ($length + 1) - ($global_buffer_start - $reserved_time);
							// $buffer_factor = ceil ($overflow / 86400);
							// $global_buffer_length = $buffer_factor * $global_buffer;
							$global_buffer_length = ceil ((($length + 1) - (($day_start + (86400 - $global_buffer)) - $reserved_time)) / 86400) * $global_buffer;

							$this->reserve_time ($resource, $global_buffer_start, $global_buffer_length, true);
						}
					}

//!!! kas on vaja neid relatiivseid aegu?

					### return planned starttime
					$reserved_time = $this->schedule_start + $reserved_time;
					return $reserved_time;
				}
			}

			### if suitable space found in this start range, move to search next range
			$time_range++;
		}

		### ... slot not found
		return NULL;//!!! mis teha?
		//!!! teha et kui konfigureeritakse ajaskaala laiemaks kui sched. length siis ....
	}

	function find_range ($starttime)
	{
		$low = 0;
		$high = count ($this->timescale) - 1;

		while ($low <= $high)
		{
			$mid = floor (($low + $high) / 2);
			$upper = ($this->timescale[$mid + 1]) ? $this->timescale[$mid + 1] : ($this->schedule_length + 1);

			if ( ($starttime >= $this->timescale[$mid]) and ($starttime < $upper) )
		{
				return $mid;
		}
		else
		{
				if ($starttime < $this->timescale[$mid])
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
}

function gettime ()
{
    list ($msec, $sec) = explode (" ", microtime ());
    return ((float) $msec + (float) $sec);
}

?>
