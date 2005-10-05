<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_schedule.aw,v 1.94 2005/10/05 13:06:14 voldemar Exp $
// mrp_schedule.aw - Ressursiplaneerija
/*

@classinfo syslog_type=ST_MRP_SCHEDULE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

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

### colours (CSS colour definition)
define ("MRP_COLOUR_NEW", "#05F123");
define ("MRP_COLOUR_PLANNED", "#5B9F44");
define ("MRP_COLOUR_INPROGRESS", "#FF9900");
define ("MRP_COLOUR_ABORTED", "#FF13F3");
define ("MRP_COLOUR_DONE", "#996600");
define ("MRP_COLOUR_PAUSED", "#999999");
define ("MRP_COLOUR_UNAVAILABLE", "#D0D0D0");
define ("MRP_COLOUR_ONHOLD", "#9900CC");
define ("MRP_COLOUR_ARCHIVED", "#0066CC");
define ("MRP_COLOUR_HILIGHTED", "#FFE706");
define ("MRP_COLOUR_PLANNED_OVERDUE", "#FBCEC1");
define ("MRP_COLOUR_OVERDUE", "#DF0D12");
define ("MRP_COLOUR_AVAILABLE", "#FCFCF4");
define ("MRP_COLOUR_PRJHILITE", "#FFE706");

ini_set ("max_execution_time", "60");

class mrp_schedule extends class_base
{
	# time() at the moment of starting scheduling (int)
	var $scheduling_time;

	# how many seconds from time() to start schedule, should be at least scheduler's maximum execution time (int)
	var $schedule_start = 300;

	# how many seconds from time() to start including unscheduled/not done/not started jobs in scheduling (int)
	var $min_planning_jobstart = 300;

	# day end for the time when scheduling takes place. (timestamp)
	var $scheduling_day_end;

	# years (float)
	var $schedule_length = 2;

	# shortest time to partially do a job. seconds (int)
	var $least_reasonable_joblength = 900;

	# for each resource and its threads. resource_tag1 => array (start_range1 => array (start1 => length1, ...), ...), ....
	var $reserved_times = array ();

	# scheduled jobs : resource_id1 => array (id1 => array (starttime1, length1), ...), ...
	var $job_schedule = array ();

	var $project_schedule = array ();
	var $schedulable_resources = array ();
	var $workspace_id;
	var $jobs_table = "mrp_job";

	# array (res_id => array (), ...)
	var $resource_data = array ();

	# scheduler parameters
	var $use_default_parameters = false;

	var $parameter_due_date_overdue_slope = 0.5;
	var $parameter_due_date_overdue_intercept = 10;
	var $parameter_due_date_decay = 0.05;
	var $parameter_due_date_intercept = 0.1;
	var $parameter_priority_slope = 0.8;

	# importance of job start/job length in weighing available times for parallel threads (float)
	var $parameter_start_priority = 1;
	var $parameter_length_priority = 1;
	# END scheduler parameters

//!!! vbl teha ainult yks planeeritud t88de array ja seega siin absoluutsed ajad, mitte rel.
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
	# farthest ends of currently allocated jobs in $reserved_times for all ranges of $range_scale
	var $range_lengths = array ();

	var $timings = array ();

	# resource unavailable times for range of time, output of get_unavailable_periods function (start => end)
	var $unavailable_times = array ();

	# indicates whether initialization has been done for get_unavailable_periods
	var $initialized = false;

	# method name to use for saving schedule data.
	// var $save_method = "save_direct";
	var $save_method = "save_fileload";

	var $state_names = array (
		MRP_STATUS_NEW => "Uus",
		MRP_STATUS_PLANNED => "Töösse planeeritud",
		MRP_STATUS_INPROGRESS => "Töös",
		MRP_STATUS_ABORTED => "Katkestatud",
		MRP_STATUS_DONE => "Valmis",
	);


	function mrp_schedule ()
	{
		$this->init (array (
			"tpldir" => "mrp/mrp_schedule",
			"clid" => CL_MRP_SCHEDULE,
		));
	}

	function initialize ($arr)
	{
		$workspace = obj ($arr["mrp_workspace"]);
		$this->workspace_id = $workspace->id ();
		$this->scheduling_time = time ();

		### get parameters
		$schedule_length = $workspace->prop ("parameter_schedule_length");
		$this->schedule_length = (int) empty ($schedule_length) ? $this->schedule_length : $schedule_length;
		$this->schedule_length = (int) $this->schedule_length * 31536000;
		define ("MRP_INF", $this->schedule_length * 10);

		$schedule_start = $workspace->prop ("parameter_schedule_start");
		$this->schedule_start = $this->scheduling_time + ((int) empty ($schedule_start) ? $this->schedule_start : $schedule_start);

		$this->scheduling_day_end = mktime (23, 59, 59, date ("m", $this->scheduling_time), date ("d", $this->scheduling_time), date("Y", $this->scheduling_time));

		$min_planning_jobstart = $workspace->prop ("parameter_min_planning_jobstart");
		$this->min_planning_jobstart = $this->scheduling_time + ((int) empty ($min_planning_jobstart) ? $this->min_planning_jobstart : $min_planning_jobstart);

		### define timerange scale
		$range_scale = $workspace->prop ("parameter_timescale");
		$range_scale_unit = (int) $workspace->prop ("parameter_timescale_unit");

		if ($range_scale)
		{
			$range_scale = explode (",", $range_scale);

			foreach ($range_scale as $key => $value)
			{
				$range_scale[$key] = ceil ($value * $range_scale_unit);
			}
		}

		if (reset ($range_scale))
		{
			### prepend range starting at 0
			array_unshift ($range_scale, 0);
		}

		sort ($range_scale, SORT_NUMERIC);

		### get parameters
		if (!$this->use_default_parameters)
		{
			### get combined_priority parameters
			$this->parameter_due_date_overdue_slope = (float) $workspace->prop ("parameter_due_date_overdue_slope");
			$this->parameter_due_date_overdue_intercept = (float) $workspace->prop ("parameter_due_date_overdue_intercept");
			$this->parameter_due_date_decay = (float) $workspace->prop ("parameter_due_date_decay");
			$this->parameter_due_date_intercept = (float) $workspace->prop ("parameter_due_date_intercept");
			$this->parameter_priority_slope = (float) $workspace->prop ("parameter_priority_slope");

			$this->parameter_start_priority = abs ((float) $workspace->prop ("parameter_start_priority"));
			$this->parameter_length_priority = abs ((float) $workspace->prop ("parameter_length_priority"));
			$this->range_scale = $range_scale;
		}

		$this->range_lengths = $this->range_scale;

		### get schedulable resources
		#### shcedulable resource types
		$applicable_types = array (
			MRP_RESOURCE_SCHEDULABLE,
			MRP_RESOURCE_SUBCONTRACTOR,
		);

		$resources_folder = $workspace->prop ("resources_folder");
		$resource_tree = new object_tree (array (
			"parent" => $resources_folder,
			"class_id" => array (CL_MRP_RESOURCE,CL_MENU),
			"type" => $applicable_types,
		));
		$resource_list = $resource_tree->to_list ();
		$resource_list->filter (array (
			"class_id" => CL_MRP_RESOURCE,
		));

		for ($resource = $resource_list->begin (); !$resource_list->end (); $resource = $resource_list->next ())
		{
			$this->schedulable_resources[] = $resource->id ();
		}
	}

/**
    @attrib name=create
	@param mrp_workspace required type=int
	@param mrp_force_replan optional type=int
**/
	function create ($arr)
	{
/* dbg */ list($micro,$sec) = split(" ",microtime());
/* dbg */ $ts_s = $sec + $micro;

		$workspace_id = (int) $arr["mrp_workspace"];

		if (is_oid($workspace_id) and $this->can ("view", $workspace_id))
		{
			$workspace = obj ($workspace_id);
		}
		else
		{
			error::raise(array(
				"msg" => t("Kasutatava ressursihalduskeskkonna id planeerijale edasi andmata või puuduvad õigused selle vaatamiseks."),
				"fatal" => true,
				"show" => true,
			));
		}

		### get and acquire semaphore for given workspace
		$sem_id = sem_get($workspace_id, 1, 0666, 1);

		if ($sem_id === false)
		{
			error::raise(array(
				"msg" => t("Planeerimisluku käivitamine ebaõnnestus! Planeerimist ei toimunud."),
				"fatal" => true,
				"show" => true,
			));
		}

		if (!sem_acquire($sem_id))
		{
			if (!sem_remove($sem_id))
			{
				// error::raise(array(
					// "msg" => t("Planeerimisluku lukustamiseta kustutamine ebaõnnestus!"),
					// "fatal" => false,
					// "show" => false,
				// ));
			}

			// error::raise(array(
				// "msg" => t("Planeerimiseks lukustamine ebaõnnestus!"),
				// "fatal" => true,
				// "show" => true,
			// ));//!!! vaadata uurida miks ikkagi aegajalt ei saada seda semafori k2tte.
			echo t("Planeerimiseks lukustamine ebaõnnestus! Planeerimist ei toimunud.");
			return;
		}

		### start scheduling only if input data has been altered
		if ( $workspace->prop("rescheduling_needed") or ($arr["mrp_force_replan"] == 1) )
		{
			### set scheduling not needed, and start scheduling
			$workspace->set_prop("rescheduling_needed", 0);
			aw_disable_acl();
			$workspace->save();
			aw_restore_acl();
		}
		else
		{
	  		### Release&remove semaphore. Stop, no rescheduling needed
			if (!sem_release($sem_id))
			{
				// error::raise(array(
					// "msg" => t("Planeerimisluku avamine ebaõnnestus!"),
					// "fatal" => false,
					// "show" => false,
				// ));
			}

			if (!sem_remove($sem_id))
			{
				// error::raise(array(
					// "msg" => t("Planeerimisluku kustutamine ebaõnnestus!"),
					// "fatal" => false,
					// "show" => false,
				// ));
			}

			return;
		}

/* timing */ timing ("initialize", "start");


		$this->initialize ($arr);


/* timing */ timing ("initialize", "end");
/* timing */ timing ("get used resources", "start");


		### get used resources
		$resources = array ();
		$resource_tree = new object_tree (array (
			"parent" => $workspace->prop ("resources_folder"),
			"class_id" => array (CL_MENU, CL_MRP_RESOURCE),
		));
		$list = $resource_tree->to_list ();
		$list->filter (array (
			"class_id" => CL_MRP_RESOURCE,
		));
		$resources = $list->ids ();


/* timing */ timing ("get used resources", "end");
/* timing */ timing ("init_resource_data", "start");


		$this->init_resource_data ($resources);


/* timing */ timing ("init_resource_data", "end");
/* timing */ timing ("initiate resource timetables", "start");
// /* dbg */  $res = 6672;
// /* dbg */  $arr = ($this->get_closest_unavailable_period($res, (14*3600)));
// /* dbg */  echo date (MRP_DATE_FORMAT, ($this->schedule_start+$arr[0]))."|".$arr[1];
// /* dbg */  arr ($this->resource_data[$res]);
// /* dbg */  exit;

		### initiate resource reserved times index
		if ($resources)
		{
			foreach ($resources as $resource_id)
			{
				$threads = $this->resource_data[$resource_id]["threads"];

				if (is_oid ($resource_id) and in_array ($resource_id, $this->schedulable_resources))
				{
					while ($threads--)
					{
						$resource_tag = $resource_id . "-" . $threads;

						foreach ($this->range_scale as $key => $start)
						{
							$this->reserved_times[$resource_tag][$key] = array ();
						}
					}
				}
			}
		}

/* timing */ timing ("initiate resource timetables", "end");
/* timing */ timing ("insert inprogress jobs", "start");

		### insert inprogress jobs' remaining lengths to resource reserved times
		$applicable_states = array (
			MRP_STATUS_PAUSED,
			MRP_STATUS_INPROGRESS,
		);

		$list = new object_list (array (
			"class_id" => CL_MRP_JOB,
			"state" => $applicable_states,
			"parent" => $workspace->prop ("jobs_folder"),
		));
		$inprogress_jobs = $list->arr ();

		foreach ($inprogress_jobs as $job)
		{
			$length = (int) ($job->prop("length") * ((($job->prop("started") + $job->prop("planned_length")) - $this->schedule_start) / $job->prop("planned_length")));

			if ($length > 0)
			{
				$this->reserve_time ($job->prop("resource"), $this->schedule_start, $length);
			}
		}

/* timing */ timing ("insert inprogress jobs", "end");
/* timing */ timing ("get all projects from db & initiate project array", "start");


		### get all projects from db
		### schedulable project states
		$applicable_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_INPROGRESS,
		);

		$this->db_query (
		"SELECT mrp_case.* " .
		"FROM " .
			"mrp_case " .
			"LEFT JOIN objects ON objects.oid = mrp_case.oid " .
		"WHERE " .
			"mrp_case.state IN (" . implode (",", $applicable_states) . ") AND " .
			"objects.status > 0 AND " .
			"objects.parent = " . $workspace->prop ("projects_folder") .
		"");
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
				"state" => $project["state"],
			);
		}

/* timing */ timing ("get all projects from db & initiate project array", "end");
/* timing */ timing ("get all jobs from db", "start");


		### get all jobs from db
		### job states
		$applicable_states = array (
			MRP_STATUS_PLANNED,
			MRP_STATUS_NEW,
			MRP_STATUS_ABORTED,
		);

		$this->db_query (
		"SELECT job.* " .
		"FROM " .
			$this->jobs_table . " as job " .
			"LEFT JOIN objects o ON o.oid = job.oid " .
		"WHERE " .
			"job.state IN (" . implode (",", $applicable_states) . ") AND " .
			"job.project > 0 AND " .
			"o.status > 0 AND " .
			"o.parent = " . $workspace->prop ("jobs_folder") . " AND " .
			"job.resource > 0 " .
		"");

/* timing */ timing ("get all jobs from db", "end");
/* timing */ timing ("distribute jobs to projects", "start");


		### distribute jobs to projects & initiate successor indices
		$starttime_index = array ();
		$successor_index = array ();

		while ($job = $this->db_next())
		{
			/* kiiruse huvides seda kontrolli praegu ei tehta. sellest v6ib mingitel juhtudel jama olla. !!!vaadata altpoolt kas kuskil vaja yldse midagi mis eeldab vaatamis6iguse olemasolu t88le.
			//!!! [15:16] <terryf> see onyks asi, mis seal fiksimist vajab jah
			if (!$this->can("view", $job["oid"]))
			{
				// echo t(sprintf ("Esines töö (id: %s), mis pole kasutajale nähtav. Planeerimine ei toimu adekvaatselt.", $job["oid"]));
				continue;
			}
			*/

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
// /* dbg */ error_reporting (E_ALL);

		### sort jobs in all projects
		foreach ($projects as $project_id => $project)
		{
			ksort ($projects[$project_id]["jobs"]);
		}


/* timing */ timing ("sort jobs in projects", "end");
/* timing */ timing ("sort projects", "start");


		### sort projects for scheduling by priority
		uasort ($projects, array ($this, "project_priority_comparison"));

// /* dbg */ arr($projects);exit;
/* timing */ timing ("sort projects", "end");
/* timing */ timing ("schedule jobs total", "start");

		### schedule jobs in all projects
		foreach ($projects as $project_id => $project)
		{
			if (is_array ($project["jobs"]))
			{
// /* dbg */ if ($project_id == 7700) {
// /* dbg */ $this->mrpdbg=1;
// /* dbg */ exit;
// /* dbg */ }
/* timing */ timing ("one project total", "start");

				$project_start = $projects[$project_id]["starttime"];
				$project_progress = $projects[$project_id]["progress"];

				### schedule project jobs
				foreach ($project["jobs"] as $key => $job)
				{

/* timing */ timing ("one job total", "start");
/* timing */ timing ("reserve time & modify earliest start", "start");
/* dbg */ if (($job["oid"] == $_GET["mrp_dbg_job"] and $_GET["mrp_dbg_job"]) or ($job["oid"] == $_GET["mrp_dbg_resource"] and $_GET["mrp_dbg_resource"])) {
/* dbg */ $this->mrpdbg=1;
/* dbg */ echo "<hr><h2>Job oid: {$job["oid"]}</h2><hr>";
// /* dbg */ exit;
/* dbg */ }


					$this->currently_processed_job = $job["oid"];
					$successor_starttime = $starttime_index[$job["oid"]];
					$minstart = max ($projects[$project_id]["starttime"], $projects[$project_id]["progress"], $this->schedule_start, $starttime_index[$job["oid"]], $job["minstart"]);
					// $minstart = $job["pre_buffer"] + $minstart;


/* dbg */ if ($this->mrpdbg) {
/* dbg */ echo "minstart-". date (MRP_DATE_FORMAT,$minstart )." | length - ". $job["length"]/3600 ."h <br>";
/* dbg */ arr ($job);
/* dbg */ echo "minplan jobstart: " . date (MRP_DATE_FORMAT,$this->min_planning_jobstart) . "<br>";
/* dbg */ echo "sched start: " . date (MRP_DATE_FORMAT,$this->schedule_start) . "<br>";
/* dbg */ }

					### states for planning jobs
					$applicable_planning_states = array (
						MRP_STATUS_PLANNED,
						MRP_STATUS_NEW,
					);

					### states for reserving job time and length w/o planning
					$applicable_timereserve_states = array (
						MRP_STATUS_ABORTED,
					);

					if ( in_array ($job["state"], $applicable_planning_states) and in_array ($job["resource"], $this->schedulable_resources) and (($job["starttime"] >= $this->min_planning_jobstart) or ($job["starttime"] < $this->schedule_start) or !$job["starttime"]) and ($job["length"] > 0))
					{
						### (re)schedule job next in line
						list ($scheduled_start, $scheduled_length) = $this->reserve_time ($job["resource"], $minstart, $job["length"]);
						$this->job_schedule[$job["oid"]] = array ($scheduled_start, $scheduled_length, $job["state"]);
					}
					elseif (in_array ($job["state"], $applicable_timereserve_states) and in_array ($job["resource"], $this->schedulable_resources))
					{
						### postpone next jobs by job length
						$remaining_length = ($job["remaining_length"] > 0) ? $job["remaining_length"] : $job["length"];
						list ($scheduled_start, $scheduled_length) = $this->reserve_time ($job["resource"], $minstart, $remaining_length);
					}
					elseif ( (!$job["length"]) and in_array ($job["state"], $applicable_planning_states) and in_array ($job["resource"], $this->schedulable_resources) )
					{
						### postpone next jobs by zero length job start
						$scheduled_start = $minstart;
						$scheduled_length = 0;
						$this->job_schedule[$job["oid"]] = array ($scheduled_start, $scheduled_length, $job["state"]);
					}
					else
					{
						continue;
					}

// /* dbg */ if ($this->mrpdbg) {
// /* dbg */ echo "rsrv time ret: ". date (MRP_DATE_FORMAT,$scheduled_start )." | length - ". $scheduled_length/3600 ."h <br>";
// /* dbg */ }
/* timing */ timing ("reserve time & modify earliest start", "end");
/* timing */ timing ("modify starttimes for next jobs in wf", "start");

					### modify earliest starttime for unscheduled jobs next in workflow
					if (is_array ($successor_index[$job["oid"]]))
					{
						foreach ($successor_index[$job["oid"]] as $successor_id)
						{
							$starttime_index[$successor_id] = max ($starttime_index[$successor_id], ($scheduled_start + $scheduled_length + $job["post_buffer"]));
						}
					}

/* timing */ timing ("modify starttimes for next jobs in wf", "end");

					### set planned finishing date for project
					if (!isset ($project["jobs"][$key + 1]))
					{
						$planned_date = $scheduled_start + $scheduled_length;
						$this->project_schedule[$project_id] = array ($planned_date, $project["state"]);
					}

/* timing */ timing ("one job total", "end");
/* dbg */ $this->mrpdbg=0;
// /* dbg */ echo "<small>proj: " . $project_id . " | job: " . $job["oid"] . " | res: " . $job["resource"] . " | start: " . date (MRP_DATE_FORMAT, $scheduled_start) . " | end: " . date (MRP_DATE_FORMAT, $scheduled_start+$scheduled_length) . "</small><br>";
				}

/* timing */ timing ("one project total", "end");
			}
		}

/* timing */ timing ("schedule jobs total", "end");
/* timing */ timing ("save schedule data", "start");
// /* dbg */ echo "<hr>";
// /* dbg */ error_reporting (0);

		$this->save ();

/* timing */ timing ("save schedule data", "end");
/* timing */ timing (null, "show");

  		### Release&remove semaphore
		if (!sem_release($sem_id))
		{
			// error::raise(array(
				// "msg" => t("Planeerimisluku avamine peale planeerimist ebaõnnestus!"),
				// "fatal" => false,
				// "show" => false,
			// ));
		}

		if (!sem_remove($sem_id))
		{
			// error::raise(array(
				// "msg" => t("Planeerimisluku kustutamine peale planeerimist ebaõnnestus!"),
				// "fatal" => false,
				// "show" => false,
			// ));
			//!!! esineb sageli millegip2rast.
		}

/* dbg */ list($micro,$sec) = split(" ",microtime());
/* dbg */ $ts_e = $sec + $micro;
/* dbg */ $GLOBALS["timings"]["planning_time"] = $ts_s - $ts_e;
		// return $this->mk_my_orb("change", array("id" => $arr["mrp_workspace"], "group" => "grp_schedule"), "mrp_workspace");
	}

	function compute_due_date ()
	{
		$this->create ();
	}

	function save ()
	{
		call_user_func (array (&$this, $this->save_method));
	}

/* --------------------------  PRIVATE METHODS ----------------------------- */

	function save_direct ()
	{
		$log = get_instance(CL_MRP_WORKSPACE);

		### job & project states for which to change state to planned and log change
		$applicable_states = array (
			MRP_STATUS_NEW,
		);

		if (is_array ($this->project_schedule) and is_array ($this->job_schedule))
		{
			foreach ($this->project_schedule as $project_id => $date)
			{
				if (is_oid($project_id))
				{
					$project = obj ($project_id);
					$project->set_prop ("planned_date", $date);

					if (in_array ($project->prop("state"), $applicable_states))
					{
						$project->set_prop ("state", MRP_STATUS_PLANNED);
						$log->mrp_log(
							$project->id(),
							0,
							"Projekti staatus muutus ".
								$this->state_names[$project->prop("state")]." => ".
								$this->state_names[MRP_STATUS_PLANNED]
						);
					}

					aw_disable_acl();
					$project->save ();
					aw_restore_acl();

/* dbg */ if ($_GET["mrp_dbg"]) {
// /* dbg */ echo "proj-" . $project_id . ": [" . date (MRP_DATE_FORMAT, $date) . "]<br>";
/* dbg */ }

				}
			}

			foreach ($this->job_schedule as $job_id => $job_data)
			{
				if (is_oid($job_id))
				{
					$job = obj ($job_id);
					$job->set_prop ("starttime", $job_data[0]);
					$job->set_prop ("planned_length", $job_data[1]);

					if (in_array ($job->prop("state"), $applicable_states))
					{
						$job->set_prop ("state", MRP_STATUS_PLANNED);
						$log->mrp_log(
							$job->prop("project"),
							$job->id(),
							"T&ouml;&ouml; staatus muutus ".
								$this->state_names[$job->prop("state")]." => ".
								$this->state_names[MRP_STATUS_PLANNED]
						);
					}

					aw_disable_acl();
					$job->save ();
					aw_restore_acl();

/* dbg */ if ($_GET["mrp_dbg"]) {
// /* dbg */ echo "job-" . $job_id . ": [" . date (MRP_DATE_FORMAT, $job_data[0]) . "] - [" . date (MRP_DATE_FORMAT, $job_data[0]+$job_data[1]) . "]<br>";
/* dbg */ }

				}
			}
		}
	}

	function save_fileload ()
	{
		if (is_array ($this->project_schedule) and is_array ($this->job_schedule))
		{
			$log = get_instance(CL_MRP_WORKSPACE);

			### job & project states for which to change state to planned and log change
			$applicable_project_states = array (
				MRP_STATUS_NEW,
			);

			$applicable_job_states = array (
				MRP_STATUS_NEW,
			);

/* timing */ timing ("save schedule data - projects", "start");
			$tmpname = tempnam(aw_ini_get("server.tmpdir"), "mrpschedule");
			$tmp = fopen ($tmpname, "w");

			foreach ($this->project_schedule as $project_id => $project_data)
			{
				if (is_oid($project_id))
				{
/* timing */ timing ("save schedule data - projects - write datafile record", "start");

					$line = "{$project_id}\t{$project_data[0]}\n";
					fwrite ($tmp, $line);

/* timing */ timing ("save schedule data - projects - write datafile record", "end");
					if (in_array ($project_data[1], $applicable_job_states))
					{
/* timing */ timing ("save schedule data - projects - save new state", "start");

						$project = obj ($project_id);

						if (in_array ($project->prop("state"), $applicable_project_states))
						{
							$project->set_prop ("state", MRP_STATUS_PLANNED);
							$log->mrp_log(
								$project->id(),
								0,
								"Projekti staatus muutus ".
									$this->state_names[$project->prop("state")]." => ".
									$this->state_names[MRP_STATUS_PLANNED]
							);
						}

						aw_disable_acl();
						$project->save ();
						aw_restore_acl();

/* timing */ timing ("save schedule data - projects - save new state", "end");
					}
				}
				else
				{
					error::raise(array(
						"msg" => sprintf (t("Viga planeeritud aegade salvestamisel. Projekti id (%s) pole oid."), $project_id),
						"fatal" => false,
						"show" => false,
					));
				}

/* dbg */ if ($_GET["mrp_dbg"]) {
// /* dbg */ echo "proj-" . $project_id . ": [" . date (MRP_DATE_FORMAT, $project_data[0]) . "]<br>";
/* dbg */ }
			}

/* timing */ timing ("save schedule data - projects", "end");
/* timing */ timing ("save schedule data - load projectdata to DB", "start");

			### load local file into db. LOCAL is slower but used because dbserver might be on another machine. Subject to change if speed is primary concern.
			$query = "LOAD DATA LOCAL INFILE '{$tmpname}' REPLACE INTO TABLE mrp_case_schedule";
			// $query = "LOAD DATA LOCAL INFILE '{$tmpname}' REPLACE INTO TABLE mrp_case_schedule FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' (oid,planned_length,starttime)";
			// $query = "LOAD DATA INFILE '{$tmpname}' REPLACE INTO TABLE mrp_case_schedule";
			// $query = "LOAD DATA INFILE '{$tmpname}' REPLACE INTO TABLE mrp_case_schedule FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' (oid,planned_length,starttime)";
			$db_retval = $this->db_query ($query);

/* timing */ timing ("save schedule data - load projectdata to DB", "end");

			if (!$db_retval)
			{
				error::raise(array(
					"msg" => t("Viga projektide planeeritud aegade salvestamisel. ") . $this->db_last_error,
					"fatal" => false,
					"show" => true,
				));
			}

			fclose($tmp);
			unlink($tmpname);


/* timing */ timing ("save schedule data - jobs", "start");
			$tmpname = tempnam(aw_ini_get("server.tmpdir"), "mrpschedule");
			$tmp = fopen ($tmpname, "w");

			foreach ($this->job_schedule as $job_id => $job_data)
			{
				if (is_oid ($job_id))
				{
/* timing */ timing ("save schedule data - jobs - write datafile record", "start");

					$line = "{$job_id}\t{$job_data[1]}\t{$job_data[0]}\n";
					fwrite ($tmp, $line);

/* timing */ timing ("save schedule data - jobs - write datafile record", "end");

					if (in_array ($job_data[2], $applicable_job_states))
					{
/* timing */ timing ("save schedule data - jobs - save new state", "start");

						$job = obj ($job_id);
						$log->mrp_log(
							$job->prop("project"),
							$job->id(),
							"T&ouml;&ouml; staatus muutus ".
								$this->state_names[$job->prop("state")]." => ".
								$this->state_names[MRP_STATUS_PLANNED]
						);

						$job->set_prop ("state", MRP_STATUS_PLANNED);
						aw_disable_acl();
						$job->save ();
						aw_restore_acl();

/* timing */ timing ("save schedule data - jobs - save new state", "end");
					}
				}
				else
				{
					error::raise(array(
						"msg" => sprintf (t("Viga planeeritud aegade salvestamisel. Töö id (%s) pole oid. (starttime: %s, planned_len: %s, state: %s)"), $job_id, $job_data[0], $job_data[1], $job_data[2]),
						"fatal" => false,
						"show" => false,
					));
				}

/* dbg */ if ($_GET["mrp_dbg"]) {
/* dbg */ echo "job-" . $job_id . ": [" . date (MRP_DATE_FORMAT, $job_data[0]) . "] - [" . date (MRP_DATE_FORMAT, $job_data[0]+$job_data[1]) . "]<br>";
/* dbg */ }
			}

/* timing */ timing ("save schedule data - jobs", "end");
/* timing */ timing ("save schedule data - load jobdata to DB", "start");

			### load local file into db. LOCAL is slower but used because dbserver might be on another machine. Subject to change if speed is primary concern.
			$query = "LOAD DATA LOCAL INFILE '{$tmpname}' REPLACE INTO TABLE mrp_schedule";
			// $query = "LOAD DATA LOCAL INFILE '{$tmpname}' REPLACE INTO TABLE mrp_schedule FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' (oid,planned_length,starttime)";
			// $query = "LOAD DATA INFILE '{$tmpname}' REPLACE INTO TABLE mrp_schedule";
			// $query = "LOAD DATA INFILE '{$tmpname}' REPLACE INTO TABLE mrp_schedule FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' (oid,planned_length,starttime)";
			$db_retval = $this->db_query ($query);

/* timing */ timing ("save schedule data - load jobdata to DB", "end");

			if (!$db_retval)
			{
				error::raise(array(
					"msg" => t("Viga tööde planeeritud aegade salvestamisel. ") . $this->db_last_error,
					"fatal" => false,
					"show" => true,
				));
			}

			fclose($tmp);
			unlink($tmpname);
		}
	}

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
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "<h4>reserve_time</h4>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

		$threads = $this->resource_data[$resource_id]["threads"];
		$available_times = array ();
		#### convert to relative time (for $reserved_times, $range_scale, ...)
		$start = ($start > $this->schedule_start) ? ($start - $this->schedule_start) : 0;

		while ($threads--)
		{
			$resource_tag = $resource_id . "-" . $threads;
			$available_times[$resource_tag] = $this->get_available_time ($resource_tag, $start, $length);

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "<h4>reserve_time</h4>";
/* dbg */ echo "thread nr." . $threads. " restag:" . $resource_tag. " reservation (time,len,timerange): ";
/* dbg */ arr ($available_times[$resource_tag]);
/* dbg */ echo "reserved times this tag: ";
/* dbg */ arr ($this->reserved_times[$resource_tag]);
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
		}

		### select thread with minimal start&length
		$weight = NULL;
		$selected_resource_tag = NULL;

		foreach ($available_times as $resource_tag => $available_time)
		{
			if (is_array ($available_time))
			{
				list ($start, $length, $time_range) = $available_time;
				$new_weight = ($start * $this->parameter_start_priority + $length * $this->parameter_length_priority) / 2;

				if (!isset ($weight))
				{
					$selected_resource_tag = $resource_tag;

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "weight=na. selected_resource_tag: " . $selected_resource_tag . "<br>";
// /* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

				}
				elseif ($new_weight < $weight)
				{
					$selected_resource_tag = $resource_tag;

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "new_weight<weight. selected_resource_tag: " . $selected_resource_tag . "<br>";
// /* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

				}

				$weight = isset ($weight) ? min ($weight, $new_weight) : $new_weight;

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "new_weight: " . $new_weight . "<br>";
// /* dbg */ echo "weight: " . $weight . "<br><br>";
// /* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
			}
		}

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "available_times: ";
// /* dbg */ arr ($available_times);
/* dbg */ echo "selected_resource_tag: " . $selected_resource_tag;
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

		if (!isset ($weight) or !isset ($selected_resource_tag))
		{
			if ($_GET["show_errors"] == 1) {echo sprintf (t("error@%s. resource-tag: %s, job: %s<br>"), __LINE__, $resource_tag, $this->currently_processed_job); flush ();}
			error::raise(array(
				"msg" => sprintf (t("Sobivat aega ei leidunud tervest kalendrist! resource-tag: %s, job: %s"), $resource_tag, $this->currently_processed_job),
				"fatal" => false,
				"show" => false,
			));
			return false;
		}

		### reserve time
		$resource_tag = $selected_resource_tag;
		$reserved_time = $available_times[$resource_tag][0];
		$length = $available_times[$resource_tag][1];
		$this->reserved_times[$resource_tag][$time_range][$reserved_time] = $length;
		#### update max. reach of selected timerange.
		$this->range_lengths[$time_range] = max (($reserved_time + $length), $this->range_lengths[$time_range]);
		#### convert back to real time
		$reserved_time += $this->schedule_start;

/* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "reserved times after this reservation: ";
/* dbg */ arr ($this->reserved_times[$resource_tag]);
/* dbg */ echo "reserved time: " . $reserved_time . " [" . date (MRP_DATE_FORMAT, $reserved_time) . "]";
/* dbg */ }
/* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

		return array ($reserved_time, $length);
	}

	function get_snug_slot ($resource_tag, $start, $length, $time_range)
	{ ## find free space with right length/start
/* timing */ timing ("reserve_time - get_snug_slot", "start");

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "<h4>get_snug_slot</h4>";
/* dbg */ echo "timerange:" . $time_range ."<br>"; $time_range_dbg = 0;
// /* dbg */ while ($time_range_dbg < ($time_range + 1)) {
// /* dbg */ echo "<hr> timerangedbg:" .  $time_range_dbg . "<br>";
// /* dbg */ foreach ($this->reserved_times[$resource_tag][$time_range_dbg] as $time_range_dbg_start => $time_range_dbg_length) {
// /* dbg */ echo "start1:". date (MRP_DATE_FORMAT, $this->schedule_start + $time_range_dbg_start) . " len:" . $time_range_dbg_length . " end:" . date (MRP_DATE_FORMAT, $this->schedule_start + $time_range_dbg_start + $time_range_dbg_length) . "<br>";	}  $time_range_dbg++; }
// /* dbg */ arr ($this->reserved_times[$resource_tag]);
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

/* timing */ timing ("reserve_time - sort reserved_times", "start");

		ksort ($this->reserved_times[$resource_tag][$time_range], SORT_NUMERIC);

/* timing */ timing ("reserve_time - sort reserved_times", "end");

		### get max reach of previous timerange
		$prev_range_end = $this->range_lengths[$time_range - 1];

		if (count ($this->reserved_times[$resource_tag][$time_range]))
		{ ### timerange has already reserved times
			### check if there's space for the job between prev. range end and this range first job
			$start2 = reset ($this->reserved_times[$resource_tag][$time_range]); # needed here to get $start2 and later for cycling through time_range reserved times with next() and key()
			$d = ($start < ($prev_range_end)) ? 0 : ($start - ($prev_range_end));

			if ( (($prev_range_end + $length + $d) <= $start2) and ($start2  >= ($start + $length)) )
			{
/* timing */ timing ("reserve_time - get_snug_slot", "end");
				return array ($prev_range_end, 0, $start2); # fabricated time -- no information available about prev range job ending latest. fortunately no info needed about it later on (!!!??).
			}

			### go through reserved times in current timerange to find place for job being scheduled
			foreach ($this->reserved_times[$resource_tag][$time_range] as $start1 => $length1)
			{
				### get next reserved time start -- start2
				if (false !== next ($this->reserved_times[$resource_tag][$time_range]))
				{ #### look for it in the same timerange, after current reserved time (start1)
					$start2 = key ($this->reserved_times[$resource_tag][$time_range]);
					prev ($this->reserved_times[$resource_tag][$time_range]);
				}
				else
				{ #### look for it in the sequent timeranges
					$start2 = $this->get_next_range_first_job ($resource_tag, $time_range);
				}

				$d = ($start < ($start1 + $length1)) ? 0 : ($start - ($start1 + $length1));

				### check if requested space is available between start1 & start2
				if ( (($start1 + $length1 + $length + $d) <= $start2) and ($start2  >= ($start + $length)) )
				{
/* timing */ timing ("reserve_time - get_snug_slot", "end");
					return array ($start1, $length1, $start2);
				}

				next ($this->reserved_times[$resource_tag][$time_range]);
			}
		}
		else
		{ ### no times reserved yet
			$start1 = max ($this->range_scale[$time_range], $prev_range_end);
			$length1 = 0;
			$start2 = $this->get_next_range_first_job ($resource_tag, $time_range_search);
			$d = ($start < ($start1 + $length1)) ? 0 : ($start - ($start1 + $length1));

			### check if requested space is available between start1 & start2
			if ( (($start1 + $length1 + $length + $d) <= $start2) and ($start2  >= ($start + $length)) )
			{
/* timing */ timing ("reserve_time - get_snug_slot", "end");
				return array ($start1, $length1, $start2);
			}
		}

/* timing */ timing ("reserve_time - get_snug_slot", "end");
		return false;
	}

	function get_next_range_first_job ($resource_tag, $time_range)
	{
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "<h4>get_next_range_first_job</h4>";
/* dbg */ }

		$i = 1;
		$start2 = MRP_INF;

		while (isset ($this->reserved_times[$resource_tag][$time_range + $i]) and ($start2 === MRP_INF))
		{
			ksort ($this->reserved_times[$resource_tag][$time_range + $i], SORT_NUMERIC);

			if (reset ($this->reserved_times[$resource_tag][$time_range + $i]))
			{
				$start2 = key ($this->reserved_times[$resource_tag][$time_range + $i]);
			}

			$i++;
		}

/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "found next_range_first_job  start2: ".$start2;
/* dbg */ }

		return $start2;
	}

	function add_unavailable_times ($resource_tag, $reserved_time, $length, $start2)
	{
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "<h4>add_unavailable_times</h4>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------


		list ($resource_id, $thread) = sscanf ($resource_tag, "%u-%u");
		list ($unavailable_start, $unavailable_length) = $this->get_closest_unavailable_period ($resource_id, $reserved_time, $length);


// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "reservedtime: " . date (MRP_DATE_FORMAT, $this->schedule_start + $reserved_time) . "<br>";
/* dbg */ echo "1st unavail: " . date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start) ."-". date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start + $unavailable_length)."<br>";
/* dbg */ $dbg_time = $unavailable_start + $unavailable_length;
/* dbg */ }
// /* dbg */ echo date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start) ."-". date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start + $unavailable_length)."<br>";
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------


		### check if unavailable time starts before reserved time ends
		if (($reserved_time + $length) > $unavailable_start)
		{
			### check if reserved starttime is in an unavailable period & make starttime correction, shifting it to the end of that unavail. period
			if ( ($reserved_time < ($unavailable_start + $unavailable_length)) and ($reserved_time >= $unavailable_start) )
			{
				### check whether with moved starttime it still fits before next already scheduled job's starting time
				if (($unavailable_start + $unavailable_length + $length) <= $start2)
				{
					$reserved_time = ($unavailable_start + $unavailable_length);
					list ($unavailable_start, $unavailable_length) = $this->get_closest_unavailable_period ($resource_id, $reserved_time, $length);


// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "2nd unavail: " . date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start) ."-". date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start + $unavailable_length)."<br>";
/* dbg */ $dbg_time = $unavailable_start + $unavailable_length;
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------


				}
				else
				{


// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "moved starttime doesn't fit before next job. " . date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start) ."-". date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start + $unavailable_length)."<br>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

					return false;
				}
			}

/* timing */ timing ("reserve_time - insert unavailable periods to job length", "start");

			### check if reserved time covers unavailable periods & make length correction if job fits in slices else start over
			$i_dbg1 = 0;

			while ( $unavailable_length and (($reserved_time + $length) > $unavailable_start) )
			{


// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "cycle start unavail: " . date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start) ."-". date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start + $unavailable_length)." | len: " . $unavailable_length/3600 . " | resp to time: " . date (MRP_DATE_FORMAT, $this->schedule_start + $dbg_time) . "<br>";
/* dbg */ $dbg_time = $unavailable_start + $unavailable_length;
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

				if ($i_dbg1++ == 1000)
				{
					if ($_GET["show_errors"] == 1) {echo sprintf (t("error@%s. res: %s, job: %s<br>"), __LINE__, $resource_id, $this->currently_processed_job); flush ();}
					error::raise(array(
						"msg" => sprintf (t("Unavailable times covered by reserved exceeded reasonable limit (%s). Resource %s, job %s"), $i_dbg1, $resource_id, $this->currently_processed_job),
						"fatal" => false,
						"show" => false,
					));
					return false;
				}


				### check if with added unavailable period length, job still fits before next job
				if (($reserved_time + $length + $unavailable_length) > $start2)
				{
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "cycle didnt fit: true<br>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

					return false;
				}
				else
				{
					$length += $unavailable_length;
					list ($unavailable_start, $unavailable_length) = $this->get_closest_unavailable_period ($resource_id, ($unavailable_start + $unavailable_length), $length);


// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "cycle end unavail: " . date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start) ."-". date (MRP_DATE_FORMAT, $this->schedule_start + $unavailable_start + $unavailable_length)." resp to time: " . date (MRP_DATE_FORMAT, $this->schedule_start + $dbg_time) . "<br>";
/* dbg */ echo "cycle end length: " . $length/3600 . "h<br>";
/* dbg */ echo "cycle end reserved_time+length: " . date (MRP_DATE_FORMAT, $this->schedule_start + $reserved_time + $length) . "<br>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
				}
			}

/* timing */ timing ("reserve_time - insert unavailable periods to job length", "end");
		}

		return array ((int) $reserved_time, (int) $length);
	}

	function get_available_time ($resource_tag, $start, $length)
	{
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "<h4>get_available_time</h4>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------


		### find range for given starttime
		$reserved_time = NULL;
		$time_range = $this->find_range ($start);

		### get place for job
		while (isset ($this->reserved_times[$resource_tag][$time_range]) and !isset ($reserved_time))
		{
			$snug_slot = $this->get_snug_slot ($resource_tag, $start, $length, $time_range);

			if (is_array ($snug_slot))
			{
				list ($start1, $length1, $start2) = $snug_slot;

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "start1:". date (MRP_DATE_FORMAT, $this->schedule_start + $start1)." - length1:".$length1." - start:". date (MRP_DATE_FORMAT, $this->schedule_start + $start) ."-start2:". date (MRP_DATE_FORMAT, $this->schedule_start + $start2) ."<br>";
/* dbg */ echo "start1:". $start1." - length1:".$length1." - start:". $start ."-start2:".$start2 ."<br>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------


				$reserved_time = (($start1 + $length1) >= $start) ? ($start1 + $length1) : $start;
				$reserved_time_data = $this->add_unavailable_times ($resource_tag, $reserved_time, $length, $start2);

				if (is_array ($reserved_time_data))
				{
					list ($reserved_time, $length) = $reserved_time_data;

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "suitable slot found in start range nr {$time_range}:";
// /* dbg */ arr ($this->reserved_times[$resource_tag][$time_range]);
// /* dbg */ echo "next range is:";
// /* dbg */ arr ($this->reserved_times[$resource_tag][$time_range]);
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

				}
				else
				{
					$reserved_time = NULL;
				}
			}

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "suitable slot not found in this start range (range nr: {$time_range}):";
// /* dbg */ arr ($this->reserved_times[$resource_tag][$time_range]);
// /* dbg */ echo "next range is:";
// /* dbg */ arr ($this->reserved_times[$resource_tag][$time_range]);
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

			$time_range++;
		}

		### return planned starttime
		if (isset ($reserved_time))
		{
			return array ($reserved_time, $length, --$time_range);
		}
		else
		{
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
// /* dbg */ if ($this->mrpdbg){
// /* dbg */ echo "reserved_time = NA<br>";
// /* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

			### ... slot not found in this thread
			return false;
		}
	}

	function find_range ($starttime)
	{
/* timing */ timing ("find_range", "start");
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "<h5>find_range</h5>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

		$low = 0;
		$high = count ($this->range_scale) - 1;
		$i_dbg = 0;

		while ($low <= $high)
		{
			$mid = floor (($low + $high) / 2);
			$next = isset ($this->range_scale[$mid + 1]) ? $this->range_scale[$mid + 1] : ($this->schedule_length + 1);

			if ( ($starttime >= $this->range_scale[$mid]) and ($starttime < $next) )
			{

/* timing */ timing ("find_range", "end");
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "found range [{$mid}] resp. to starttime [{$starttime}]";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

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

			if ($i_dbg++ == (count ($this->range_scale) * 3))
			{
				if ($_GET["show_errors"] == 1) {echo sprintf (t("error@%s. job: %s<br>"), __LINE__, $this->currently_processed_job); flush ();}
				error::raise(array(
					"msg" => sprintf (t("Timerange search exceeded reasonable limit (%s) of cycles. Job %s"), $i_dbg2, $this->currently_processed_job),
					"fatal" => false,
					"show" => false,
				));
				break;
			}
		}
	}

	function get_closest_unavailable_period ($resource_id, $time, $length)
	{
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "<h4>get_closest_unavailable_period</h4>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

		$time += $this->schedule_start;
		list ($start, $end) = $this->_get_closest_unavailable_period ($resource_id, $time, $length);

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "<br>_closestper1: ". date (MRP_DATE_FORMAT, $start). "-" .date (MRP_DATE_FORMAT, $end) . " | resp to: " .date (MRP_DATE_FORMAT, ($time)) . "<br>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

		### unavailable periods not defined past $time, stop and return 0?
		if ($start == $end)
		{
			// !!! ajutine lahendus sellele, mis siis saaab kui t88aegu ega midagi pole dfn, v6i peale kysitud hetke pole kinniseid aegu. tagastatakse 1sekundine kinnine aeg 10 aastat p2rast schedule-endi
			$quasi_start = ($start - $this->schedule_start) + MRP_INF;
			$quasi_length = 0;
			return array ($quasi_start, $quasi_length);
		}


		### find if period ends before another starts
		$i_dbg = 0;

		while (true)//!!! bad
		{
			list ($period_start, $period_end) = $this->_get_closest_unavailable_period ($resource_id, $end, $length);

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "_closestper cycle: ". date (MRP_DATE_FORMAT, $period_start). "-" .date (MRP_DATE_FORMAT, $period_end) . " | resp to: " .date (MRP_DATE_FORMAT, ($end)) . "<br>";
/* dbg */ }
// /* dbg */ $tmptime = time();
// /* dbg */ $tmpschtime = $this->scheduling_time;
// /* dbg */ if (($tmptime - $tmpschtime) > 15) {
// /* dbg */ var_dump ($this);
// /* dbg */ exit ("res:" . $resource_id . " time:" . date (MRP_DATE_FORMAT, $time) . " t" . $tmptime . " st" . $tmpschtime);
// /* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

			if (
				($period_start <= $end)
				and $period_end
				// and ($period_start != $period_end)//!!! kas on vaja siis katkestada kui 0-pikkusega periood?
			)
			{
				$end = $period_end;
			}
			else
			{
				break;
			}

			if ($i_dbg++ == 10000)
			{
				//!!! siia j6utakse t6en2oliselt siis kui kogu aeg on ressurss kinni, tykkide kaupa.
				if ($_GET["show_errors"] == 1) {echo sprintf (t("error@%s. res: %s, job %s<br>"), __LINE__, $resource_id, $this->currently_processed_job); flush ();}
				error::raise(array(
					"msg" => sprintf (t("Ressursil id-ga %s pole piirangu ulatuses vabu aegu. Võimalik on ka viga või ettenägematu seadistus ressursi tööaegades. Töö id: %s"), $resource_id, $this->currently_processed_job),
					"fatal" => false,
					"show" => false,
				));
				break;
			}
		}

		### convert back to relative time & return
		$period_start = ($start > $this->schedule_start) ? ($start - $this->schedule_start) : 0;
		$length = ($start > $this->schedule_start) ? ($end - $start) : ($end - $this->schedule_start);

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "_closestper ret: ". date (MRP_DATE_FORMAT, $start). "-" .date (MRP_DATE_FORMAT, ($start+$length)) . " | resp to: " .date (MRP_DATE_FORMAT, ($time)) . "<br>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

		return array ($period_start, $length);
	}

	## returns start and length of next unavailable period after $time. if $time is in an unavail. period, that period's data is returned.
	function _get_closest_unavailable_period ($resource_id, $time, $length)
	{
/* timing */ timing ("get_closest_unavailable_period", "start");
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "<h5>_get_closest_unavailable_period</h5>";
/* dbg */ echo "timeforclosestper:". date (MRP_DATE_FORMAT, $time)."<br>";
/* dbg */ }
// /* dbg */ if ((time() - $this->scheduling_time) > 25){
// /* dbg */ echo "<br>time:". date (MRP_DATE_FORMAT, $time) . " res:".$resource_id." t:".$this->scheduling_time."<br>";
// /* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

		$closest_periods = array ();

		### get dateinfo
		$day_start = mktime (0, 0, 0, date ("m", $time), date ("d", $time), date("Y", $time));

		### get closest global buffer
		if ($this->resource_data[$resource_id]["global_buffer"] > 0)
		{
			if ($time <= $this->scheduling_day_end)
			{
				$global_buffer_start = $day_start + 86400 + (86400 - $this->resource_data[$resource_id]["global_buffer"]);
			}
			else
			{
				$global_buffer_start = $day_start + (86400 - $this->resource_data[$resource_id]["global_buffer"]);
			}

			$closest_periods[$global_buffer_start] = $global_buffer_start + $this->resource_data[$resource_id]["global_buffer"];
		}

		### get recurrences
		foreach ($this->resource_data[$resource_id]["recurrence_definitions"] as $recurrence)
		{
			if (($recurrence["max_span"] > $time) and ($recurrence["start"] <= $time))
			{ #### only process recurrences that may be relevant to $time -- that may end after $time or start before

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg) {
/* dbg */ echo "recstart: " . date (MRP_DATE_FORMAT, $recurrence["start"]) . " | recinterval: " .  $recurrence["interval"] .  " | reclength: " .  $recurrence["length"] / 3600 . "h | rectime: " .  $recurrence["time"] / 3600 . "h<br>";
// /* dbg */ arr ($recurrence);
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

				### make dst corrections
				// $nodst_day_start = $recurrence["start"] + floor (($time - $recurrence["start"]) / $recurrence["interval"]) * $recurrence["interval"];
				$nodst_day_start = $recurrence["start"] + (($time - $recurrence["start"]) - (($time - $recurrence["start"]) % $recurrence["interval"]));
				$nodst_day_hour = (int) date ("H", $nodst_day_start);

				if ($nodst_day_hour === 0)
				{
					$dst_day_start = $nodst_day_start;
				}
				else
				{
					if ($nodst_day_hour < 13)
					{
						$dst_error = $nodst_day_hour;
						$dst_day_start = $nodst_day_start - $dst_error*3600;
					}
					else
					{
						$dst_error = 24 - $nodst_day_hour;
						$dst_day_start = $nodst_day_start + $dst_error*3600;
					}
				}

				### ...
				$recurrence_start = $dst_day_start + $recurrence["time"];
				$recurrence_end = $recurrence_start + $recurrence["length"];


	// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
	/* dbg */ if ($this->mrpdbg){
	/* dbg */ echo " rectime:". ($recurrence["time"] / 3600) . " h<br>";
	/* dbg */ echo " recdaystart: nodst - ". date (MRP_DATE_FORMAT, $nodst_day_start) . " | dst - ". date (MRP_DATE_FORMAT, $dst_day_start) . "<br>";
	/* dbg */ echo " recperiod:". date (MRP_DATE_FORMAT, $recurrence_start) ."-". date (MRP_DATE_FORMAT, $recurrence_end) . "<br>";
	// /* dbg */ echo " closestper rec: ". date (MRP_DATE_FORMAT, $recurrence_start). "-" .date (MRP_DATE_FORMAT, ($recurrence_end)) . " | resp to: " .date (MRP_DATE_FORMAT, ($time)) . "<br>";
	/* dbg */ }
	// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

				while ($recurrence_start > $time)
				{
					$recurrence_start -= $recurrence["interval"];
					$recurrence_end -= $recurrence["interval"];
				}

				while ($recurrence_end <= $time)
				{
					$recurrence_start += $recurrence["interval"];
					$recurrence_end += $recurrence["interval"];
				}

				if (($recurrence_end > $recurrence["start"]) and ($recurrence_start < $recurrence["end"]))
				{
					$closest_periods[$recurrence_start] = max ($recurrence_end, $closest_periods[$recurrence_start]);
				}
			}
		}

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "closest_periods before:";
/* dbg */ arr ($closest_periods);
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
		### add separate unavailable periods
		foreach ($this->resource_data[$resource_id]["unavailable_periods"] as $period_start => $period_end)
		{
			$closest_periods[$period_start] = max ($period_end, $closest_periods[$period_start]);
		}
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "closest_periods after:";
/* dbg */ arr ($closest_periods);
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

		### combine buffer, recurrence & period
		if (!empty ($closest_periods))
		{
			list ($start, $end) = $this->find_combined_range ($closest_periods, $time);
		}
		else
		{
			$start = $end = 0;
		}

		if ($start == $end)
		{
			$start = $end = 0;
		}

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "combined unavail: t - ".date (MRP_DATE_FORMAT, $time)." [".date (MRP_DATE_FORMAT, $start)." - ".date (MRP_DATE_FORMAT, $end) . "]<br>";
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* timing */ timing ("get_closest_unavailable_period", "end");


		return array ($start, $end);
	}

	### find and combine ranges closest to $value, exclude ranges that don't exceed $value directly or through overlapping ranges
	function find_combined_range ($ranges, $value)
	{
		$start = $end = $value;
		ksort ($ranges, SORT_NUMERIC);

		if (end ($ranges) > $value)
		{
			$ranges = $this->combine_ranges ($ranges);

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "ranges after combining:";
/* dbg */ arr ($ranges);
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------

			foreach ($ranges as $range_start => $range_end)
			{
				if ($range_end > $value)
				{
					$start = $range_start;
					$end = $range_end;
					break;
				}
			}
		}

// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
/* dbg */ if ($this->mrpdbg){
/* dbg */ echo "found range:";
/* dbg */ arr (array ($start, $end));
/* dbg */ }
// /* dbg */ //-------------------------------------------------------------------------------------------------------------------------------------------
		return array ($start, $end);
	}

	## input is an array sorted by key, pointer at start.
	function combine_ranges ($ranges)
	{
		$prev_end = NULL;
		$combined_ranges = array ();

		foreach ($ranges as $start => $end)
		{
			if (($start <= $prev_end) and isset ($prev_end))
			{
				$prev_end = max ($end, $prev_end);
				$combined_ranges[$prev_start] = $prev_end;
			}
			else
			{
				$combined_ranges[$start] = $end;
				$prev_start = $start;
				$prev_end = $end;
			}
		}

		return $combined_ranges;
	}

	function init_resource_data ($resources)
	{
		foreach ($resources as $resource_id)
		{
			if (is_oid ($resource_id))
			{
				$resource = obj ($resource_id);
				$thread_data = $resource->prop ("thread_data");//!!! thread datas midagi valesti?
				$threads = count ($thread_data) ? count ($thread_data) : 1;

				$this->resource_data[$resource_id]["global_buffer"] = $resource->prop ("global_buffer");
				$this->resource_data[$resource_id]["threads"] = $threads;
				$i = $resource->instance ();
				$this->resource_data[$resource_id]["unavailable_periods"] = $i->get_unavailable_periods ($resource, $this->schedule_start, ($this->schedule_start + $this->schedule_length));
				$this->resource_data[$resource_id]["recurrence_definitions"] = $i->get_recurrent_unavailable_periods ($resource, $this->schedule_start, ($this->schedule_start + $this->schedule_length));
			}
		}
	}

	// @param mrp_resource required type=int
	// @param mrp_start required type=int
	// @param mrp_length required type=int
	function get_unavailable_periods_for_range ($arr)
	{
		$resource_id = $arr["mrp_resource"];
		$resource = obj ($resource_id);
		$workspace = $resource->get_first_obj_by_reltype("RELTYPE_MRP_OWNER");

		if (!is_object($workspace))
		{
			return false;
		}

		if (!$this->initialized)
		{
			$this->scheduling_time = time ();
			$this->schedule_start = $arr["mrp_start"];
			$this->schedule_length = $arr["mrp_length"];
			$this->scheduling_day_end = mktime (23, 59, 59, date ("m", $this->scheduling_time), date ("d", $this->scheduling_time), date("Y", $this->scheduling_time));

			$resources_folder = $workspace->prop ("resources_folder");
			$resource_tree = new object_tree (array (
				"parent" => $resources_folder,
				"class_id" => array (CL_MRP_RESOURCE,CL_MENU),
			));

			$resource_list = $resource_tree->to_list ();
			$resources = array();
			foreach($resource_list->arr() as $resource)
			{
				if ($resource->class_id() == CL_MRP_RESOURCE && $resource->prop("type") != MRP_RESOURCE_NOT_SCHEDULABLE)
				{
					$resources[] = $resource->id();
				}
			}
			$this->init_resource_data ($resources);
			$this->initialized = true;
		}

		$this->unavailable_times = array();
		$pointer = 0;
		$i_dbg = 0;

		while ($pointer <= $this->schedule_length)
		{
			list ($unavailable_start, $unavailable_length) = $this->get_closest_unavailable_period ($resource_id, $pointer, $this->schedule_length);

			if ($unavailable_length <= 0)
			{
				return $this->unavailable_times;
			}

			$pointer = $unavailable_start + $unavailable_length + 1;
			$unavailable_start = $this->schedule_start + $unavailable_start;
			$unavailable_end = $unavailable_start + $unavailable_length;
			$this->unavailable_times[$unavailable_start] = max ($unavailable_end, $this->unavailable_times[$unavailable_start]);

			if (5000 == $i_dbg++)
			{
				error::raise(array(
					"msg" => sprintf (t("Search for unavailable times for range exceeded reasonable limit (%s) of cycles. Resource %s"), $i_dbg3, $resource_id),
					"fatal" => false,
					"show" => false,
				));
				break;
			}
		}

		return $this->unavailable_times;
	}
}

function timing ($name, $action = "time")
{
	if (isset ($_GET["show_timings"]) and $_GET["show_timings"] == 1)
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
					echo "[" . $name . "] => " . ($timing["sum"] / $timing["count"]) . " (count: " . $timing["count"] . ", total: " . $timing["sum"] . ")\n";
				}

				echo "</pre>";
				break;
		}
	}
}

?>
