<?php

class crm_call_obj extends task_object
{
	const RESULT_CALL = 1;
	const RESULT_PRESENTATION = 2;

	protected static $read_only_when_done = array(
		"real_start", "start1", "end", "real_duration", "result", "deadline"
	);

	public function set_prop($name, $value)
	{
		if ($this->prop("real_duration") > 0 and in_array($name, self::$read_only_when_done))
		{
			return $this->prop($name);
		}
		else
		{
			return parent::set_prop($name, $value);
		}
	}

	/** Returns list of call result options
	@attrib api=1 params=pos
	@param result type=int
		Result constant value to get name for, one of crm_call_obj::RESULT_*
	@returns array
		Format option value => human readable name, if result parameter set, array with one element returned and empty array when that result not found.
	**/
	public static function result_names($result = null)
	{
		$result_names = array(
			self::RESULT_CALL => t("Uus k&otilde;ne"),
			self::RESULT_PRESENTATION => t("Esitlus")
		);

		if (isset($result))
		{
			if (isset($result_names[$result]))
			{
				$result_names = array($result => $result_names[$result]);
			}
			else
			{
				$result_names = array();
			}
		}

		return $result_names;
	}

	/** Makes a phone call.
	@attrib api=1 params=pos
	@param phone type=CL_CRM_PHONE default=null
		If not set, 'phone' property must be set
	@returns void
	@errors
		throws awex_crm_call_state when call already started
	**/
	public function make(object $phone = null)
	{
		if ($this->prop("real_duration") > 0)
		{
			throw new awex_crm_call_state("Call has already been made");
		}

		if ($phone instanceof object and $phone->is_a(CL_CRM_PHONE))
		{
			$this->set_prop("phone", $phone->id());
		}

		$this->set_prop("real_start", time());
		$crm_call = new crm_call();
		$crm_call->add_participant(new object($this->id()), get_current_person());

		if (is_oid($this->prop("sales_schedule_job")))
		{
			$job = new object($this->prop("sales_schedule_job"));

			if (!is_oid($job->prop("resource")))
			{
				$resource = mrp_workspace_obj::get_person_resource(get_current_person(), get_current_company());
				$job->set_prop("resource", $resource->id());
				$job->load_data();
				$job->plan();
			}
			else
			{
				$job->load_data();
			}

			if (!($info = $job->can_start(false, true)))
			{
				throw new awex_crm_call_state("Call job can't start");
			}

			$job->start();
		}

		$this->save();
	}

	/** Finishes phone call.
	@attrib api=1 params=pos
	@returns void
	@errors
		throws awex_crm_call_state when call not started or already ended
	**/
	public function end()
	{
		if ($this->prop("real_duration") > 0)
		{
			throw new awex_crm_call_state("Call was already ended");
		}

		if ($this->prop("real_start") < 2)
		{
			throw new awex_crm_call_state("Call not started");
		}

		$this->set_prop("real_duration", (time() - $this->prop("real_start")));

		if (is_oid($this->prop("sales_schedule_job")))
		{
			$job = new object($this->prop("sales_schedule_job"));
			$job->load_data();
			$job->done();
		}

		$this->save();
	}

	public function save($exclusive = false, $previous_state = null)
	{
		$r = parent::save($exclusive, $previous_state);

		if (is_oid($this->prop("sales_schedule_job")))
		{
			$job = new object($this->prop("sales_schedule_job"));
			$planned_length = $this->prop("end") > $this->prop("start1") ? $this->prop("end") - $this->prop("start1") : 0;
			if ($this->prop("start") != $job->prop("minstart") or $planned_length != $job->prop("planned_length"))
			{
				$job->set_prop("minstart", $this->prop("start1"));
				$job->set_prop("planned_length", $planned_length);
				$job->save();
			}
		}

		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES))
		{
			$application->process_call_result(new object($this->id()));// send call change message to sales application
		}
		return $r;
	}
}

/** Generic call error **/
class awex_crm_call extends awex_obj {}

/** Call status error **/
class awex_crm_call_state extends awex_crm_call {}

?>
