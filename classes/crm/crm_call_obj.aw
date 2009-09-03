<?php

class crm_call_obj extends task_object
{
	const RESULT_CALL = 1;
	const RESULT_PRESENTATION = 2;
	const RESULT_REFUSED = 3;
	const RESULT_NOANSWER = 4;
	const RESULT_BUSY = 5;
	const RESULT_HUNGUP = 6;
	const RESULT_OUTOFSERVICE = 7; // number is out of service at the moment
	const RESULT_INVALIDNR = 8; // number is not used or invalid
	const RESULT_VOICEMAIL = 9; // voicemail or answering machine
	const RESULT_NEWNUMBER = 10; // a redirect or comment from answerer giving a new number to call the contact
	const RESULT_DISCONNECTED = 11; // an error occurred during call, got disconnected, ...

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
			self::RESULT_PRESENTATION => t("Esitlus"),
			self::RESULT_NOANSWER => t("Ei vasta"),
			self::RESULT_BUSY => t("Kinni"),
			self::RESULT_OUTOFSERVICE => t("Teenindusest v&auml;ljas"),
			self::RESULT_VOICEMAIL => t("K&otilde;nepost/automaatvastaja"),
			self::RESULT_REFUSED => t("Keeldub kontaktist"),
			self::RESULT_HUNGUP => t("Katkestas k&otilde;ne"),
			self::RESULT_INVALIDNR => t("Vigane number/pole kasutusel"),
			self::RESULT_DISCONNECTED => t("K&otilde;ne katkes"),
			self::RESULT_NEWNUMBER => t("Number muutund")
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

	/** Tells if call can be started.
	@attrib api=1 params=pos
	@returns bool
	@errors
		none
	**/
	public function can_start()
	{
		$can_start = ($this->prop("real_start") < 2);
		if (is_oid($this->prop("hr_schedule_job")))
		{
			$job = new object($this->prop("hr_schedule_job"));
			$can_start = ($can_start and $job->can_start(false, true));
		}
		return $can_start;
	}

	/** Tells if call is in progress.
	@attrib api=1 params=pos
	@returns bool
	@errors
		none
	**/
	public function is_in_progress()
	{
		return (($this->prop("real_duration") < 1) and ($this->prop("real_start") > 1));
	}

	/** Makes a phone call.
	@attrib api=1 params=pos
	@param phone type=CL_CRM_PHONE default=null
		If not set, 'phone' property must be set
	@returns void
	@errors
		throws awex_crm_call_state when call already started
		throws awex_crm_call_job when call job is not defined
	**/
	public function make(object $phone = null)
	{
		if ($this->prop("real_duration") > 0)
		{
			throw new awex_crm_call_state("Call has already been made");
		}

		if (!is_oid($this->prop("hr_schedule_job")))
		{
			throw new awex_crm_call_job("Call job not defined");
		}

		if ($phone instanceof object and $phone->is_a(CL_CRM_PHONE))
		{
			$this->set_prop("phone", $phone->id());
		}

		$current_person_oid = get_current_person();
		$this->set_prop("real_start", time());
		$this->set_prop("real_maker", $current_person_oid);
		$crm_call = new crm_call();
		$crm_call->add_participant(new object($this->id()), $current_person_oid);

		$job = new object($this->prop("hr_schedule_job"));
		$job->load_data();

		if (!($info = $job->can_start(false, true)))
		{
			throw new awex_crm_call_state("Call job can't start");
		}

		$job->start();
		$this->save();

		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES))
		{
			// send call start message to sales application
			$application->end_call(new object($this->id()));
		}
	}

	/** Schedules the phone call.
	@attrib api=1 params=pos
	@param resource type=CL_MRP_RESOURCE
		resource to schedule the call to
	@returns void
	@errors
		throws awex_crm_call_state when call state doesn't allow scheduling
		throws awex_crm_call_cr when call state doesn't allow scheduling
	@comment
		Requires customer_relation to be set. Saves object (calls self::save())
	**/
	public function schedule(object $resource)
	{
		if ($this->prop("real_duration") > 0 or $this->prop("real_start") > 1)
		{
			throw new awex_crm_call_state("Call has already been made");
		}

		$customer_relation = new object($this->prop("customer_relation"));
		if (!$customer_relation->is_saved())
		{
			throw new awex_crm_call_cr("Customer relation must be defined");
		}

		$case = $customer_relation->get_sales_case(true);
		$job = $case->add_job();
		$job->set_prop("resource", $resource->id());
		$job->set_prop("planned_length", ($this->prop("end") - $this->prop("start1")));
		$this->set_prop("hr_schedule_job", $job->id());
		$time = $this->prop("start1");
		$job->load_data();

		if ($time < time())
		{ // an unscheduled call
			$job->set_on_hold();
		}
		else
		{
			$job->plan();
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

		if (is_oid($this->prop("hr_schedule_job")))
		{
			$job = new object($this->prop("hr_schedule_job"));
			$job->load_data();
			$job->done();
		}

		$this->save();

		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES))
		{
			// send call end message to sales application
			$application->end_call(new object($this->id()));
		}
	}

	public function save($exclusive = false, $previous_state = null)
	{
		$r = parent::save($exclusive, $previous_state);

		if (is_oid($this->prop("hr_schedule_job")))
		{
			$job = new object($this->prop("hr_schedule_job"));
			$planned_length = $this->prop("end") > $this->prop("start1") ? $this->prop("end") - $this->prop("start1") : 0;
			if ($this->prop("start") != $job->prop("minstart") or $planned_length != $job->prop("planned_length"))
			{
				$job->set_prop("minstart", $this->prop("start1"));
				$job->set_prop("planned_length", $planned_length);
				try
				{
					$job->plan();
				}
				catch (Exception $e)
				{
				}
			}
		}

		return $r;
	}
}

/** Generic call error **/
class awex_crm_call extends awex_obj {}

/** Call status error **/
class awex_crm_call_state extends awex_crm_call {}

/** Call customer relation error **/
class awex_crm_call_cr extends awex_crm_call {}

/** Call job error **/
class awex_crm_call_job extends awex_crm_call {}

?>
