<?php

classload("mrp/mrp_header");

class mrp_job_obj extends _int_object
{
	function prop($k)
	{
		if($k === "real_length" || $k === "length_deviation")
		{
			if(!is_numeric(parent::prop($k)) && $this->prop("state") == MRP_STATUS_DONE && is_oid($this->id()))
			{
				if($k === "length_deviation")
				{
					$this->set_prop($k, $this->get_deviation());
				}
				else
				{
					$this->set_prop($k, $this->get_real());
				}
				$this->save();
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
		return $this->prop("planned_length") - $this->prop("real_length");
	}

	function get_real($k)
	{
		$case = $this->prop("project");
		$res = $this->prop("resource");
		$job_id = $this->id();

		$v = $this->instance()->db_fetch_field("
			SELECT SUM(length) as length_sum FROM mrp_stats WHERE 
				case_oid = $case AND
				resource_oid = $res AND
				job_oid = $job_id",
			"length_sum");
		
		return $v ? (int)$v : 0;
	}
}