<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_schedule.aw,v 1.2 2004/12/08 12:23:32 voldemar Exp $
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


class mrp_schedule extends class_base
{
	//var $schedule_offset = 0; // how many seconds from time() to start scheduling
	var $resource_schedules = array (); // scheduled_job[]
	var $schedule = array (); // scheduled jobs : id => starttime
	// var $new_project = array (
		// "resources" => array (),
	// );
	var $conditions = array ( // DCP
		"222" => 0,
		"221" => 0,
		"220" => 0,
		"212" => 0,
		"211" => 0,
		"210" => 0,
		"202" => 0,
		"201" => 0,
		"200" => 0,
		"122" => 1,
		"121" => 1,
		"120" => 1,
		"112" => 1,
		"111" => 0,
		"110" => 0,
		"102" => 0,
		"101" => 0,
		"100" => 0,
		"022" => 1,
		"021" => 1,
		"020" => 1,
		"012" => 1,
		"011" => 1,
		"010" => 1,
		"002" => 1,
		"001" => 1,
		"000" => 1,
	);

	function mrp_schedule()
	{
		$this->init(array(
			"tpldir" => "mrp/mrp_schedule",
			"clid" => CL_MRP_SCHEDULE
		));
	}

	function compute_due_date ()
	{
		$this->create ();
	}

/**
    @attrib name=create
**/
	function create ($arr)
	{
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		$workspace = obj ($arr["mrp_workspace"]);

		### get used resources
		$resources = array ();
		$this->db_query("SELECT DISTINCT `resource` FROM `mrp_job` ORDER BY `resource`");
//!!! only machine resources
		while ($job = $this->db_next())
		{
			$resources[] = $job["resource"];
		}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["get used resources"] = gettime () - $time;                                                                                                             ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### initiate resource timetables
		if ($resources)
		{
			foreach ($resources as $resource)
			{
				$this->resource_schedules[$resource] = array ();
				// !!! igale ressursile vaba aja algus ka kirja
				// !!! siin lisada lukustatud t88d jm. ette reserveeritud ressursiajad
			}

			$this->db_query("SELECT `starttime`,`length`,`resource` FROM `mrp_job` where `state`=" . MRP_STATUS_LOCKED);

			while ($job = $this->db_next())
			{
				$this->resource_schedules[$job["resource"]][$job["starttime"]] = $job["length"];
			}
		}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["initiate resource timetables"] = gettime () - $time;                                                                                               ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### get all projects from db
		$this->db_query("SELECT * FROM `mrp_case` WHERE `state`=" . MRP_STATUS_NEW . " OR `state`=" . MRP_STATUS_PLANNED);
		$projects = array ();

		### initiate project array
		while ($project = $this->db_next())
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
		$this->db_query("SELECT * FROM `mrp_job` WHERE `state`=" . MRP_STATUS_NEW . " OR `state`=" . MRP_STATUS_PLANNED);

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$times["get all jobs from db"] = gettime () - $time;                                                                                                            ////////
$time = gettime ();                                                                                                                                                                 ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		### distribute jobs to projects & initiate minimal starttime and successsor indices
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
				$starttime = $this->find_place_in_timetable ($job, $minstart);
				$this->schedule[$job["oid"]] = $starttime;
///!!! vaadata mis saab erinevate workflow graafide korral -- mitu eeldust88d, 1 t88 mitmele eeldust88ks

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$st2 += (gettime () - $t2);                                                                                                                                                    ////////
$t2 = gettime ();                                                                                                                                                                     ////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

				### modify earliest starttime for next unscheduled job .///!!! vaja yldse?
				if ( !($next_job_earliest_starttime > ($starttime + $job["length"] + $job["buffer"])) )
				{
					$next_job_earliest_starttime = $starttime + $job["length"] + $job["buffer"];
				}

				### modify earliest starttime for unscheduled jobs next in workflow
				if (is_array ($successor_index[$job["oid"]]))
				{
					foreach ($successor_index[$job["oid"]] as $successor_id)
					{
						if ( !($starttime_index[$successor_id] > ($starttime + $job["length"] + $job["buffer"])) )
						{
							$starttime_index[$successor_id] = $starttime + $job["length"] + $job["buffer"];
						}
					}
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
	}

	function save ()
	{
		foreach ($this->schedule as $job_id => $starttime)
		{
			$job = obj ($job_id);
			$job->set_prop ("starttime", $starttime);
			$job->set_prop ("state", MRP_STATUS_PLANNED);
			$job->save ();
			echo $job_id . ":" . $starttime . "<br>";
		}
	}

/* --------------------------  PRIVATE METHODS ----------------------------- */

	function find_place_in_timetable ($job, $minstart)
	{
		ksort ($this->resource_schedules[$job["resource"]]);

		### check whether to put it to the end
		$last_job_length = end ($this->resource_schedules[$job["resource"]]);
		$last_starttime = key ($this->resource_schedules[$job["resource"]]);
		$starttime = $last_starttime + $last_job_length;

		if (($last_starttime + $last_job_length) < $minstart)
		{
			$starttime = $minstart;
		}
		else
		{
			### find frame in schedule where the job fits
			foreach ($this->resource_schedules[$job["resource"]] as $scheduled_starttime => $scheduled_job)
			{
				$starttime1 = $scheduled_job["starttime"];
				$length1 = $scheduled_job["length"];
				$length = $job["length"];

				if (isset ($this->resource_schedules[$job["resource"]][$scheduled_starttime + 1]))
				{
					$starttime2 = $this->resource_schedules[$job["resource"]][$scheduled_starttime + 1]["starttime"];

					if ( (($starttime1 + $length1) <= $minstart) and (($starttime2 - ($starttime1 + $length1)) <= $length) )
					{
						$starttime = $starttime1 + $length1 . "x";
						break;
					}
				}
			}
		}

		### update resource timetable
		$this->resource_schedules[$job["resource"]][$starttime] = $job["length"];

		return $starttime;
	}

	function project_priority_comparison ($project1, $project2)
	{
		$D1 = $project1["due_date"];
		$D2 = $project2["due_date"];
		$C1 = $project1["customer_priority"];
		$C2 = $project2["customer_priority"];
		$P1 = $project1["project_priority"];
		$P2 = $project2["project_priority"];
		// $L1 = $project1["project_length"];
		// $L2 = $project2["project_length"];

		$D = ($D1 < $D2) ? "0" : (($D1 == $D2) ? "1" : "2");
		$C = ($C1 < $C2) ? "0" : (($C1 == $C2) ? "1" : "2");
		$P = ($P1 < $P2) ? "0" : (($P1 == $P2) ? "1" : "2");
		// $L = ($L1 < $L2) ? "0" : (($L1 == $L2) ? "1" : "2");

		$condition = $this->conditions[$D.$C.$P];

		if ($condition)
		{
			return -1;
		}
		else
		{
			return 1;
		}
	}
}

function gettime ()
{
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

?>
