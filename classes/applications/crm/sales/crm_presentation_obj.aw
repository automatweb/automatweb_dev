<?php

class crm_presentation_obj extends task_object
{
	const RESULT_CALL = 1;
	const RESULT_PRESENTATION = 2;
	const RESULT_SALE = 3;
	const RESULT_MISS = 4;

	private static $result_names = array();

	/** Returns list of presentation result options
	@attrib api=1 params=pos
	@param result type=int
		Result constant value to get name for, one of crm_presentation_obj::RESULT_*
	@returns array
		Format option value => human readable name, if result parameter set, array with one element returned and empty array when that result not found.
	**/
	public static function result_names($result = null)
	{
		if (empty(self::$result_names))
		{
			self::$result_names = array(
				self::RESULT_CALL => t("Uus k&otilde;ne"),
				self::RESULT_PRESENTATION => t("Esitlus"),
				self::RESULT_SALE => t("Toote m&uuml;&uuml;k"),
				self::RESULT_MISS => t("J&auml;i &auml;ra")
			);
		}

		if (isset($result))
		{
			if (isset(self::$result_names[$result]))
			{
				$result_names = array($result => self::$result_names[$result]);
			}
			else
			{
				$result_names = array();
			}
		}
		else
		{
			$result_names = self::$result_names;
		}

		return $result_names;
	}

	public function awobj_set_start1($value)
	{
		if ($this->prop("real_start") < 2 and $value < time())
		{
			throw new awex_crm_presentation("Start cannot be in the past");
		}

		$this->set_prop("start1", $value);
	}

	public function save($exclusive = false, $previous_state = null)
	{
		$r = parent::save($exclusive, $previous_state);
		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES))
		{
			$application->process_presentation_result(new object($this->id()));
		}

		if (is_oid($this->prop("hr_schedule_job")))
		{
			// change job times
			$job = new object($this->prop("hr_schedule_job"));
			$planned_length = $this->prop("end") > $this->prop("start1") ? $this->prop("end") - $this->prop("start1") : 0;
			if ($this->prop("start1") != $job->prop("minstart") or $planned_length != $job->prop("planned_length"))
			{
				$job->set_prop("minstart", $this->prop("start1"));
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
