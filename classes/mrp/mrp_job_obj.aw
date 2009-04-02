<?php

classload("mrp/mrp_header");

class mrp_job_obj extends _int_object
{
	const PRSN_HNDL_S = 1;
	const PRSN_HNDL_F = 2;
	const PRSN_HNDL_S_OR_F = 3;
	const PRSN_HNDL_S_AND_F = 4;

	function save()
	{
		$retval = parent::save();
		$this->log_state_change();
		return $retval;
	}

	function log_state_change()
	{
		$i = $this->instance();
		$r = $i->db_fetch_row("SELECT * FROM mrp_job_rows WHERE aw_job_id = '".$this->id()."' ORDER BY aw_tm DESC, aw_row_id DESC LIMIT 1");
		// Log only if state is changed or new object
		if($r["aw_job_state"] != $this->prop("state") || !$r)
		{
			$last_duration = isset($r["aw_tm"]) ? time() - $r["aw_tm"] : 0;
			$prev_state = isset($r["aw_job_state"]) ? $r["aw_job_state"] : MRP_STATUS_NEW;
			$i->db_query("
				INSERT INTO mrp_job_rows
					(aw_job_id, aw_case_id, aw_resource_id, aw_uid, aw_uid_oid, aw_previous_pid, aw_pid, aw_job_previous_state, aw_job_state, aw_job_last_duration, aw_tm)
				VALUES
					('".$this->id()."', '".$this->prop("project")."', '".$this->prop("resource")."', '".aw_global_get("uid")."', '".aw_global_get("uid_oid")."', '".$r["aw_pid"]."', '".get_instance("user")->get_current_person()."', '".$prev_state."', '".$this->prop("state")."', '".$last_duration."', '".time()."')
			");
			$connectable_states = array(MRP_STATUS_INPROGRESS, MRP_STATUS_ABORTED, MRP_STATUS_DONE, MRP_STATUS_PAUSED, MRP_STATUS_SHIFT_CHANGE);
			if(in_array($this->prop("state"), $connectable_states))
			{
				$this->connect(array(
					"type" => "RELTYPE_PERSON",
					"to" => get_instance("user")->get_current_person(),
				));
			}
		}
	}

	function set_prop($k, $v)
	{
		if($k === "state" && $v === MRP_STATUS_DONE)
		{
			$this->set_prop("real_length", $this->get_real());
			$this->set_prop("length_deviation", $this->get_deviation());
		}

		return parent::set_prop($k, $v);
	}

	private function get_deviation()
	{
		return $this->prop("real_length") - $this->prop("length");
	}

	private function get_real($k)
	{
		$job_id = $this->id();

		$v = $this->instance()->db_fetch_field("
			SELECT 
				SUM(aw_job_last_duration) as length_sum
			FROM
				mrp_job_rows 
			WHERE
				aw_job_id = '".$job_id."' AND 
				aw_job_previous_state = '".MRP_STATUS_INPROGRESS."';",
			"length_sum");
		// If the work is still in progress we have to add the time from last state change until now.
		$i = $this->instance()->db_fetch_row("SELECT aw_job_state, UNIX_TIMESTAMP() - aw_tm as tm FROM mrp_job_rows WHERE aw_job_id = '$job_id' ORDER BY aw_tm DESC LIMIT 1");
		if(isset($i["aw_job_state"]) && $i["aw_job_state"] == MRP_STATUS_INPROGRESS)
		{
			return (int)$v + $i["tm"];
		}
		return (int)$v;
	}

	function get_resource()
	{
		return $this->get_first_obj_by_reltype("RELTYPE_MRP_RESOURCE");
	}

	/**
	@attrib name=save_materials
		
	@param amount required type=array
	@param unit required type=array
	@param movement optional type=array
	@param planning optional type=array

	@comment 
		Function to be called on a mrp_job object to save its materials
		Parameters are arrays of $product_id => "value" pairs
		eg amount => array( 123 => 12, 124 => 13 ), where 123 & 124 are product ids, 12 & 13 amounts
	**/
	function save_materials($arr)
	{
		$res = $this->get_first_obj_by_reltype("RELTYPE_MRP_RESOURCE");
		if($res)
		{
			$conn = $res->connections_to(array(
				"from.class_id" => CL_MATERIAL_EXPENSE_CONDITION,
			));
			$conn2 = $this->connections_to(array(
				"from.class_id" => CL_MATERIAL_EXPENSE,
			));
			foreach($conn2 as $c)
			{
				$o = $c->from();
				$prod = $o->prop("product");
				$prods[$prod] = $o->id();
			}

			foreach($conn as $c)
			{
				$prod = $c->from()->prop("product");
				$arg["product"] = $prod;
				$arg["amount"] = $arr["amount"][$prod];
				$arg["unit"] = $arr["unit"][$prod];
				if(isset($arr["movement"][$prod]))
				{
					$arg["movement"] = $arr["movement"][$prod];
				}
				if(isset($arr["planning"][$prod]))
				{
					$arg["planning"] = $arr["planning"][$prod];
				}
				$this->set_used_material($arg);
			}

			$conn = $this->connections_to(array(
				"from.class_id" => CL_MATERIAL_MOVEMENT_RELATION,
			));
			foreach($arr["unit"] as $prod => $unit)
			{
				if(!$arr["amount"][$prod])
				{
					continue;
				}
				$data[$prod] = array(
					"unit" => $unit,
					"amount" => $arr["amount"][$prod],
				);
			}
			if(!count($conn))
			{
				$o = obj();
				$o->set_class_id(CL_MATERIAL_MOVEMENT_RELATION);
				$o->set_parent($this->id());
				$o->set_name(sprintf(t("Materjali liikumisseos t&ouml;&ouml;ga %s"), $this->name()));
				$o->set_prop("job", $this->id());
				$o->save();
				$o->create_dn($o, $data);
			}
			else
			{
				foreach($conn as $c)
				{
					$c->from()->update_dn_rows($c->from(), $data);
				}
			}
		}
	}

	/** Adds, changes or removes source materials planned to be used by this job
	@attrib api=1 params=pos
	@param product required type=CL_SHOP_PRODUCT
	@param quantity required type=int,float
		Quantity how much or how many of $product is assessed to be required for completing this job.
	@param unit optional type=CL_UNIT
		If no unit given, product's default unit used.
	@returns void
	@comment
		If quantity is 0, the product is removed from this job's planned materials. If product already planned to be used and $quantity is different, it will overwrite previous value.
	**/
	public function set_used_material_assessment(object $product, $quantity, $unit = null)
	{
	}

	public function get_material_expense_list()
	{
		$ol = new object_list(array(
			"class_id" => CL_MATERIAL_EXPENSE,
			"lang_id" => array(),
			"site_id" => array(),
			"job" => $this->id()
		));
		$rv = array();
		foreach($ol->arr() as $entry)
		{
			$rv[$entry->prop("product")] = $entry;
		}
		return $rv;
	}

	/**
	@attrib name=set_used_material_assessment api=1

	@param product required type=oid
	@param amount required type=int
	@param unit optional type=oid
	@param movement optional type=int
	@param planning optional type=int
	
	@comment saves a product for a job, when called on a job object
	**/
	function set_used_material($arr)
	{
		$conn2 = $this->connections_to(array(
			"from.class_id" => CL_MATERIAL_EXPENSE,
		));
		foreach($conn2 as $c)
		{
			$o = $c->from();
			$prod = $o->prop("product");
			$prods[$prod] = $o->id();
		}
		$prod = $arr["product"];
		if(!$prods[$prod] && $arr["amount"])
		{
			$o = obj();
			$o->set_class_id(CL_MATERIAL_EXPENSE);
			$o->set_parent($this->id());
			$o->set_name(sprintf(t("%s kulu %s jaoks"), obj($prod)->name(), $this->name()));
			$o->set_prop("product", $prod);
			$o->set_prop("unit", $unit);
			$this->set_used_material_base_amount(array(
				"obj" => &$o,
				"product" => $prod,
				"amount" => $arr["amount"],
				"unit" => $arr["unit"],
			));
			$o->set_prop("amount", $arr["amount"]);
			$o->set_prop("job", $this->id());
			if(isset($arr["movement"]))
			{
				$o->set_prop("movement", $arr["movement"]);
			}
			if(isset($arr["planning"]))
			{
				$o->set_prop("planning", $arr["planning"]);
			}
			$this->set_used_material_base_amount(array(
				"obj" => &$eo,
				"product" => $prod,
				"amount" => $arr["amount"],
				"unit" => $arr["unit"],
			));
			$o->save();
		}
		else
		{
			if($prods[$prod] && !$arr["amount"])
			{
				$eo = obj($prods[$prod]);
				$eo->delete();
			}
			elseif($prods[$prod])
			{
				$eo = obj($prods[$prod]);
				$eo->set_prop("unit", $arr["unit"]);
				$eo->set_prop("amount", $arr["amount"]);
				if(isset($arr["movement"]))
				{
					$eo->set_prop("movement", $arr["movement"]);
				}
				if(isset($arr["planning"]))
				{
					$eo->set_prop("planning", $arr["planning"]);
				}
				$eo->save();
			}
		}
	}

	/**
		@attrib name=get_person_work_hours api=1 params=name

		@param from optional type=int
			UNIX timestamp
		@param to optional type=int
			UNIX timestamp
		@param state optional type=int/array
			The state(s) of job to return the hours for
		@param person optional type=int/array
			The OID(s) of crm_person to return the hours for
		@param person_handling optional type=int default=PRSN_HNDL_S
			How to use [person param]?
			PRSN_HNDL_S - [person param] was the one to change the job's state to [state param]
			PRSN_HNDL_F - [person param] was the one to change the job's state from [state param]
			PRSN_HNDL_S_OR_F - PRSN_HNDL_S or PRSN_HNDL_F
			PRSN_HNDL_S_AND_F - PRSN_HNDL_S and PRSN_HNDL_F
		@param job optional type=int/array
			The OID(s) of mrp_job to return the hours fo
		@param by_job optional type=boolean default=false
		
		@param average optional type=boolean

		@param count optional type=boolean

		@param convert_to_hours optional type=boolean default=true


		@returns Array of work hours by person

		@comment Output format:
			Array
			(
				[[job status]] => Array
				(
					[{person object OID}] => {time in seconds}
				)
				::optional::
				[average] => Array
				(
					[[job status]] => Array
					(
						[{person object OID}] => {time in seconds}
					)
				)
				[count] => Array
				(
					[[job status]] => Array
					(
						[{person object OID}] => {time in seconds}
					)
				)
				::optional::
			)
	**/
	public function get_person_hours($arr)
	{
		enter_function("mrp_job_obj::get_person_hours");
		$i = get_instance("class_base");

		$states = isset($arr["state"]) ? (array)$arr["state"] : array(MRP_STATUS_INPROGRESS, MRP_STATUS_PAUSED);

		// Initialize $data
		$data = array();
		foreach($states as $state)
		{
			$data[$state] = array();
			if(!empty($arr["average"]))
			{
				$data["average"][$state] = array();
			}
			if(!empty($arr["count"]))
			{
				$data["count"][$state] = array();
			}
		}

		$arr["person_handling"] = isset($arr["person_handling"]) ? $arr["person_handling"] : self::PRSN_HNDL_S;
		$arr["person"] = isset($arr["person"]) ? (is_oid($arr["person"]) ? (array)$arr["person"] : safe_array($arr["person"])) : array();

		if($arr["person_handling"] == self::PRSN_HNDL_S_OR_F)
		{
			// First, get the hours the person started
			$persons = count($arr["person"]) > 0 ? "aw_previous_pid IN (".implode(",", $arr["person"]).") AND" : "";
			$q = $i->db_fetch_array($this->something_hours_build_query($arr, "aw_previous_pid", "pid", $persons));
			$this->something_hours_insert_data($q, "pid", &$data, $arr);

			// Now, get the hours the person finished, but DIDN'T start
			$persons = count($arr["person"]) > 0 ? "aw_pid IN (".implode(",", $arr["person"]).") AND aw_pid != aw_previous_pid AND" : "";
			$q = $i->db_fetch_array($this->something_hours_build_query($arr, "aw_pid", "pid", $persons));
			$this->something_hours_insert_data($q, "pid", &$data, $arr);
		}
		else
		{
			switch($arr["person_handling"])
			{
				default:
				case self::PRSN_HNDL_S:
					$persons = count($arr["person"]) > 0 ? "aw_previous_pid IN (".implode(",", $arr["person"]).") AND" : "";
					$field = "aw_previous_pid";
					break;

				case self::PRSN_HNDL_F:
					$persons = count($arr["person"]) > 0 ? "aw_pid IN (".implode(",", $arr["person"]).") AND" : "";
					$field = "aw_pid";
					break;

				case self::PRSN_HNDL_S_AND_F:
					$persons = count($arr["person"]) > 0 ? "aw_pid IN (".implode(",", $arr["person"]).") AND aw_pid = aw_previous_pid AND" : "";
					$field = "aw_pid";
					break;
			}

			$q = $i->db_fetch_array($this->something_hours_build_query($arr, $field, "pid", $persons));
			$this->something_hours_insert_data($q, "pid", &$data, $arr);
		}

		exit_function("mrp_job_obj::get_person_hours");

		return $data;
	}

	/**
		@attrib name=get_resource_hours api=1 params=name

		@param from optional type=int
			UNIX timestamp
		@param to optional type=int
			UNIX timestamp
		@param state optional type=int/array
			The state(s) of job to return the hours for
		@param resource optional type=int/array
			The OID(s) of mrp_resource to return the hours for
		@param job optional type=int/array
			The OID(s) of mrp_job to return the hours for
		@param average optional type=boolean

		@param count optional type=boolean

		@param convert_to_hours optional type=boolean default=true
			

		@returns Array of work hours by person or FALSE on failure.

		@comment Output format:
			Array
			(
				[[job status]] => Array
				(
					[{resource object OID}] => {time in seconds}
				)
				::optional::
				[average] => Array
				(
					[[job status]] => Array
					(
						[{resource object OID}] => {time in seconds}
					)
				)
				[count] => Array
				(
					[[job status]] => Array
					(
						[{resource object OID}] => {count}
					)
				)
				::optional::
			)
	**/
	public function get_resource_hours($arr)
	{
		enter_function("mrp_job_obj::get_resource_hours");
		$i = get_instance("class_base");

		$states = isset($arr["state"]) ? (array)$arr["state"] : array(MRP_STATUS_INPROGRESS, MRP_STATUS_PAUSED);

		// Initialize $data
		$data = array();
		foreach($states as $state)
		{
			$data[$state] = array();
			if(!empty($arr["average"]))
			{
				$data["average"][$state] = array();
			}
			if(!empty($arr["count"]))
			{
				$data["count"][$state] = array();
			}
		}

		$arr["resource"] = isset($arr["resource"]) ? (is_oid($arr["resource"]) ? (array)$arr["resource"] : safe_array($arr["resource"])) : array();
		$resources = count($arr["resource"]) > 0 ? "aw_resource_id IN (".implode(",", $arr["resource"]).") AND" : "";

		$q = $i->db_fetch_array($this->something_hours_build_query($arr, "aw_resource_id", "resource_id", $persons));
		$this->something_hours_insert_data($q, "resource_id", &$data, $arr);

		exit_function("mrp_job_obj::get_resource_hours");

		return $data;
	}

	private function something_hours_build_query($arr, $field, $key, $additionnal)
	{
		$states = isset($arr["state"]) ? (array)$arr["state"] : array(MRP_STATUS_INPROGRESS, MRP_STATUS_PAUSED);
		$from = (int)(isset($arr["from"]) ? $arr["from"] : 0);
		$to = (int)(isset($arr["to"]) ? $arr["to"] : time());
		$c2h = !isset($arr["convert_to_hours"]) || !empty($arr["convert_to_hours"]) ? "/3600" : "";
		$count = !empty($arr["count"]) ? "COUNT(*) as cnt," : "";
		$average = !empty($arr["average"]) ? "AVG(aw_job_last_duration){$c2h} as avg," : "";

		$arr["job"] = isset($arr["job"]) ? (is_oid($arr["job"]) ? (array)$arr["job"] : safe_array($arr["job"])) : array();
		$jobs = count($arr["job"]) > 0 ? "aw_job_id IN (".implode(",", $arr["job"]).") AND" : "";

		$by_job = empty($arr["by_job"]) ? "" : "aw_job_id,";
		$select_job = empty($arr["by_job"]) ? "" : "aw_job_id as job_id,";

		$query = "
			SELECT 
				$select_job
				$count
				$average
				aw_job_previous_state as state,
				SUM(aw_job_last_duration){$c2h} as hours,
				$field as $key
			FROM
				mrp_job_rows 
			WHERE
				$additionnal
				$jobs
				aw_job_previous_state IN('".implode("','", $states)."') AND
				aw_tm BETWEEN $from AND $to
			GROUP BY aw_job_previous_state, {$by_job} $field
		";
		return $query;
	}

	private function something_hours_insert_data($q, $key, &$data, $arr)
	{
		$states = isset($arr["state"]) ? (array)$arr["state"] : array(MRP_STATUS_INPROGRESS, MRP_STATUS_PAUSED);
		$by_job = !empty($arr["by_job"]);

		foreach($q as $d)
		{
			// Hours
			if(isset($data[$d["state"]][$d[$key]]) && !$by_job || $by_job && isset($data[$d["state"]][$d[$key]][$d["job_id"]]))
			{
				$by_job ? $data[$d["state"]][$d[$key]][$d["job_id"]] += $d["hours"] : $data[$d["state"]][$d[$key]] += $d["hours"];
			}
			else
			{
				$by_job ? $data[$d["state"]][$d[$key]][$d["job_id"]] = $d["hours"] : $data[$d["state"]][$d[$key]] = $d["hours"];
			}

			// Average
			if(isset($d["avg"]) && (isset($data["average"][$d["state"]][$d[$key]]) && !$by_job || $by_job && isset($data["average"][$d["state"]][$d[$key]][$d["job_id"]])))
			{
				$by_job ? $data["average"][$d["state"]][$d[$key]][$d["job_id"]] += $d["avg"] : $data["average"][$d["state"]][$d[$key]] += $d["avg"];
			}
			elseif(isset($d["avg"]))
			{
				$by_job ? $data["average"][$d["state"]][$d[$key]][$d["job_id"]] = $d["avg"] : $data["average"][$d["state"]][$d[$key]] = $d["avg"];
			}

			// Count
			if(isset($d["cnt"]) && (isset($data["count"][$d["state"]][$d[$key]]) && !$by_job || $by_job && isset($data["count"][$d["state"]][$d[$key]][$d["job_id"]])))
			{
				$by_job ? $data["count"][$d["state"]][$d[$key]][$d["job_id"]] += $d["cnt"] : $data["count"][$d["state"]][$d[$key]] += $d["cnt"];
			}
			elseif(isset($d["cnt"]))
			{
				$by_job ? $data["count"][$d["state"]][$d[$key]][$d["job_id"]] = $d["cnt"] : $data["count"][$d["state"]][$d[$key]] = $d["cnt"];
			}

			// Initialize others
			foreach($states as $_state)
			{
				if($d["state"] !== $_state && (!isset($data[$_state][$d[$key]])) && !$by_job || $by_job && !isset($data[$_state][$d[$key]][$d["job_id"]]))
				{
					$by_job ? $data[$_state][$d[$key]][$d["job_id"]] = 0 : $data[$_state][$d[$key]] = 0;
					$by_job ? $data["average"][$_state][$d[$key]][$d["job_id"]] = 0 : $data["average"][$_state][$d[$key]] = 0;
					$by_job ? $data["count"][$_state][$d[$key]][$d["job_id"]] = 0 : $data["count"][$_state][$d[$key]] = 0;
				}
			}
		}
	}
}

?>
