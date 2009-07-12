<?php

class crm_presentation_obj extends task_object
{
	const RESULT_CALL = 1;
	const RESULT_PRESENTATION = 2;
	const RESULT_SALE = 3;
	const RESULT_MISS = 4;

	public function save($exclusive = false, $previous_state = null)
	{
		$r = parent::save($exclusive, $previous_state);
		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES))
		{
			$application->process_presentation_result(new object($this->id()));
		}

		if (is_oid($this->prop("sales_schedule_job")))
		{
			// change job times
			$job = new object($this->prop("sales_schedule_job"));
			$planned_length = $this->prop("end") > $this->prop("start") ? $this->prop("end") - $this->prop("start") : 0;
			if ($this->prop("start") != $job->prop("minstart") or $planned_length != $job->prop("planned_length"))
			{
				$job->set_prop("minstart", $this->prop("start"));
				$job->set_prop("planned_length", $planned_length);
				$job->save();
			}

			// start and end job if presentation done
			if ($this->prop("real_duration") > 1 and $this->prop("real_start") > 1 and $this->prop("result"))
			{
				$job->load_data();
				$job->start("", $this->prop("real_start"));
				$job->done(null, "", $this->prop("real_duration"));
			}
			elseif ($this->prop("real_duration") > 1 or $this->prop("real_start") > 1 or $this->prop("result"))
			{
				throw new awex_crm_presentation_results("real_duration, real_start and result must all be defined when presentation done.");
			}
		}
		return $r;
	}
}

class awex_crm_presentation extends awex_crm {}
class awex_crm_presentation_results extends awex_crm_presentation {}

?>
