<?php
// $Header: /home/cvs/automatweb_dev/classes/mrp/mrp_schedule.aw,v 1.1 2004/11/15 16:03:39 voldemar Exp $
// mrp_schedule.aw - Ajaplaan?
/*

@classinfo syslog_type=ST_MRP_SCHEDULE relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

*/

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

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
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
		$workspace = obj ($arr["mrp_workspace"]);

		### get used resources
		$this->db_query("SELECT DISTINCT `resource` FROM `mrp_job` ORDER BY `resource`");
//!!! only this company's systems resources
		while ($array = $this->db_next())
		{
			$resources[] = $array["resource"];
		}

		### initiate resource timetables
		if ($resources)
		{
			foreach ($resources as $resource)
			{
				$this->resource_schedules[$resource] = array ();
				// !!! igale ressursile vaba aja algus ka kirja
				// !!! siin lisada lukustatud t88d jm. ette reserveeritud ressursiajad
			}
		}

		### get all projects from db
		$this->db_query("SELECT * FROM `mrp_case`");

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

		### get all jobs from db
		$this->db_query("SELECT * FROM `mrp_job`");

		### distribute jobs to projects
		while ($job = $this->db_next())
		{
			if (array_key_exists ($job["project"], $projects))
			{
				$projects[$job["project"]]["jobs"][$job["exec_order"]] = $job;
			}
		}

		### sort jobs in all projects
		foreach ($projects as $project_id => $project)
		{
			ksort ($projects[$project_id]["jobs"]);
		}

		### sort projects for scheduling by priority
		uasort ($projects, array ($this, "project_priority_comparison"));

		### schedule jobs
		foreach ($projects as $project_id => $project)
		{
			$next_job_starttime = $projects[$project_id]["starttime"];

			foreach ($project["jobs"] as $job)
			{
				### schedule next job in line
				$starttime = $this->find_place_in_timetable ($job, $next_job_starttime);
				$this->schedule[$job["oid"]] = $starttime;

				### modify earliest starttime for next unscheduled job
				if ( !($next_job_starttime > ($starttime + $job["length"])) )
				{
					$next_job_starttime = $starttime + $job["length"];
				}
			}
		}

		$this->save ();
	}

	function save ()
	{
		foreach ($this->schedule as $job_id => $starttime)
		{
			$job = obj ($job_id);
			$job->set_prop ("starttime", $starttime);
			$job->save ("starttime", $starttime);
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
			foreach ($this->resource_schedules[$job["resource"]] as $key => $scheduled_job)
			{
				$starttime1 = $scheduled_job["starttime"];
				$length1 = $scheduled_job["length"];
				$length = $job["length"];

				if (isset ($this->resource_schedules[$job["resource"]][$key + 1]))
				{
					$starttime2 = $this->resource_schedules[$job["resource"]][$key + 1]["starttime"];

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

?>
