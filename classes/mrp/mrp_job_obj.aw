<?php

classload("mrp/mrp_header");

class mrp_job_obj extends _int_object
{
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
					(aw_job_id, aw_case_id, aw_resource_id, aw_uid, aw_uid_oid, aw_pid, aw_job_previous_state, aw_job_state, aw_job_last_duration, aw_tm)
				VALUES
					('".$this->id()."', '".$this->prop("project")."', '".$this->prop("resource")."', '".aw_global_get("uid")."', '".aw_global_get("uid_oid")."', '".get_instance("user")->get_current_person()."', '".$prev_state."', '".$this->prop("state")."', '".$last_duration."', '".time()."')
			");
		}
	}

	function set_prop($k, $v)
	{
		if($k === "state" && $v === MRP_STATUS_DONE && !is_numeric(parent::prop($k)))
		{
			$this->set_prop("length_deviation", $this->get_deviation());
			$this->set_prop("real_length", $this->get_real());
		}

		return parent::set_prop($k, $v);
	}

	function prop($k)
	{
		if($k === "real_length" || $k === "length_deviation")
		{
			if(!is_numeric(parent::prop($k)) && $this->prop("state") == MRP_STATUS_DONE && is_oid($this->id()))
			{
				if($k === "length_deviation")
				{
					return $this->get_deviation();
				}
				else
				{
					return $this->get_real($k);
				}
			}
			else
			{
				return (int)parent::prop($k);
			}
		}

		return parent::prop($k);
	}

	function get_deviation()
	{
		return $this->prop("real_length") - $this->prop("planned_length");
	}

	function get_real($k)
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
		/*
		$v = $this->instance()->db_fetch_field("
			SELECT SUM(length) as length_sum FROM mrp_stats WHERE 
				case_oid = $case AND
				resource_oid = $res AND
				job_oid = $job_id",
			"length_sum");
		*/
		return $v ? (int)$v : 0;
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
			if($arr["unit"])
			{
				$unit = $arr["unit"];
			}
			else
			{
				$po = obj($prod);
				$units = $po->instance()->get_units($po);
				if(count($units))
				{
					$unit = reset($units);
				}
			}
			if($unit)
			{
				$o->set_prop("unit", $unit);
			}
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
			The state of job to return the hours for
		@param person optional type=int/array
			The OID of crm_person to return the hours for
		@param average optional type=boolean
			The OID of crm_person to return the hours for
		@param count optional type=boolean
			The OID of crm_person to return the hours for

		@returns Array of work hours by person

		@comment Output format:
			Array
			(
				[MRP_STATUS_INPROGRESS] => Array
				(
					[{person object OID}] => {time in seconds}
				)
				[MRP_STATUS_INPROGRESS] => Array
				(
					[{person object OID}] => {time in seconds}
				)
				[name] => Array
				(
					[{person object OID}] => {name string}
				)
			)
	**/
	public function get_person_hours($arr)
	{
		$i = get_instance("class_base");

		$states = isset($arr["state"]) ? (array)$arr["state"] : array(MRP_STATUS_INPROGRESS, MRP_STATUS_PAUSED);
		$from = (int)(isset($arr["from"]) ? $arr["from"] : 0);
		$to = (int)(isset($arr["to"]) ? $arr["to"] : time());

		$arr["person"] = isset($arr["person"]) ? (is_oid($arr["person"]) ? (array)$arr["person"] : safe_array($arr["person"])) : array();
		$persons = count($arr["person"]) > 0 ? "aw_pid IN (".implode(",", $arr["person"]).") AND" : "";
		$count = !empty($arr["count"]) ? "COUNT(*) as cnt," : "";
		$average = !empty($arr["average"]) ? "AVG(aw_job_last_duration)/3600 as avg," : "";

		foreach($states as $state)
		{
			$q[$state] = $i->db_fetch_array("
				SELECT 
					$count
					$average
					SUM(aw_job_last_duration)/3600 as hours,
					aw_pid as pid
				FROM
					mrp_job_rows 
				WHERE
					$persons
					aw_job_previous_state = '".$state."' AND
					aw_tm BETWEEN $from AND $to
				GROUP BY aw_pid
			");
		}
		foreach($states as $state)
		{
			foreach($q[$state] as $d)
			{
				$data[$state][$d["pid"]] = $d["hours"];
				if(isset($d["avg"]))
				{
					$data["average"][$state][$d["pid"]] = $d["avg"];
				}
				if(isset($d["cnt"]))
				{
					$data["count"][$state][$d["pid"]] = $d["cnt"];
				}
				foreach($states as $_state)
				{
					if($state !== $_state && !isset($data[$state][$d["pid"]]))
					{
						$data[$_state][$d["pid"]] = 0;
						$data["average"][$_state][$d["pid"]] = 0;
						$data["count"][$_state][$d["pid"]] = 0;
					}
				}
			}
		}

		return $data;
	}
}

?>
