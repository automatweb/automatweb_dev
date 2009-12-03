<?php

/*
@classinfo  maintainer=voldemar
*/

class crm_sales_obj extends _int_object implements application_interface
{
	const ROLE_GENERIC = 10;
	const ROLE_DATA_ENTRY_CLERK = 20;
	const ROLE_TELEMARKETING_SALESMAN = 30;
	const ROLE_TELEMARKETING_MANAGER = 40;
	const ROLE_SALESMAN = 50;
	const ROLE_MANAGER = 60;

	private static $role_names = array();
	public static $role_ids = array(
		self::ROLE_GENERIC => "generic",
		self::ROLE_DATA_ENTRY_CLERK => "data_entry_clerk",
		self::ROLE_TELEMARKETING_SALESMAN => "telemarketing_salesman",
		self::ROLE_TELEMARKETING_MANAGER => "telemarketing_manager",
		self::ROLE_SALESMAN => "salesman",
		self::ROLE_MANAGER => "manager"
	);

	/** Returns object list of contacts visible to current user, in applicable order
	@attrib api=1
	@param start optional type=int
	@returns object_list
	**/
	public function get_contacts($start = 0, $end = -1)
	{
		// get current user role
		$role = $this->get_current_user_role();

		if (self::ROLE_TELEMARKETING_SALESMAN === $role)
		{
			$contacts;
		}
	}

	/** Returns list of role options
	@attrib api=1 params=pos
	@param role type=int
		Role id constant value to get name for, one of crm_sales_obj::ROLE_*
	@returns array
		Format option value => human readable name, if $role parameter set, array with one element returned and empty array when that role not found.
	**/
	public static function role_names($role = null)
	{
		if (0 === count($this->role_names))
		{
			self::$role_names = array(
				self::ROLE_GENERIC => t("&Uuml;ldine"),
				self::ROLE_DATA_ENTRY_CLERK => t("Andmesisestaja"),
				self::ROLE_TELEMARKETING_SALESMAN => t("Telemarketingit&ouml;&ouml;taja"),
				self::ROLE_TELEMARKETING_MANAGER => t("Telemarketingi juht"),
				self::ROLE_SALESMAN => t("M&uuml;&uuml;giesindaja"),
				self::ROLE_MANAGER => t("Juht")
			);
		}

		if (isset($role))
		{
			if (isset(self::$role_names[$role]))
			{
				$role_names = array($result => self::$role_names[$role]);
			}
			else
			{
				$role_names = array();
			}
			return $role_names;
		}
		else
		{
			return self::$role_names;
		}
	}

	public function save($exclusive = false, $previous_state = null)
	{
		if (!is_oid($this->prop("owner")))
		{
			throw new awex_crm_sales_owner("Owner not defined, can't save");
		}

		return parent::save($exclusive, $previous_state);
	}

	public function awobj_get_owner()
	{
		return new object(parent::prop("owner"));
	}

	public function awobj_set_owner(object $owner)
	{
		if (!$owner->is_a(CL_CRM_COMPANY))
		{
			throw new awex_crm_sales_owner("Owner must be CL_CRM_COMPANY object");
		}

		return parent::set_prop("owner", $owner->id());
	}

	/** Returns current user role id in this sales application
	@attrib api=1
	@returns int
		one of ROLE_... constants
	@comment
		role id constant integer values are in priority order. if someone fills many roles, the role with highest priority value will be returned
	**/
	public function get_current_user_role()
	{
		$role = self::ROLE_GENERIC;
		$current_person = get_current_person();
		$professions = $current_person->get_profession_selection($this->prop("owner"));
		if (count($professions))
		{
			reset($professions);
			$profession = key($professions);
			switch ($profession)
			{
				case $this->prop("role_profession_manager"):
					$role = self::ROLE_MANAGER;
					break;
				case $this->prop("role_profession_salesman"):
					$role = self::ROLE_SALESMAN;
					break;
				case $this->prop("role_profession_telemarketing_manager"):
					$role = self::ROLE_TELEMARKETING_MANAGER;
					break;
				case $this->prop("role_profession_telemarketing_salesman"):
					$role = self::ROLE_TELEMARKETING_SALESMAN;
					break;
				case $this->prop("role_profession_data_entry_clerk"):
					$role = self::ROLE_DATA_ENTRY_CLERK;
					break;
			}
		}
		return $role;
	}

	/** Adds a contact to this sales application
	@attrib api=1 params=pos
	@param customer_relation type=CL_CRM_COMPANY_CUSTOMER_DATA
		Customer relation defining the contact to add and its relation to sales application owner
	@comment
		Saves customer_relation object
	@returns void
	**/
	public function add_contact(object $customer_relation)
	{
		$call = $this->create_call($customer_relation);
		$customer_relation->set_prop("sales_state", crm_company_customer_data_obj::SALESSTATE_NEW);
		$customer_relation->save();
		$case = $this->get_customer_case($customer_relation, true);
		$case->plan();
	}

	/** Creates a presentation task
	@attrib api=1 params=pos
	@param customer_relation type=CL_CRM_COMPANY,CL_CRM_PERSON
	@param time type=int
		Time when presentation is to be held/made. UNIX timestamp
	@param products type=object_list
		Products to be presented
	@returns CL_CRM_PRESENTATION
		Created presentation object
	**/
	public function create_presentation(object $customer_relation)
	{
		$folder = $this->prop("presentations_folder");
		if (!is_oid($folder))
		{
			throw new awex_crm_sales_folder("Presentations folder not defined");
		}
		$presentation = obj(null, array(), CL_CRM_PRESENTATION);
		$presentation->set_parent($folder);
		$presentation->set_name(sprintf(t("Esitlus kliendile %s"), $customer_relation->prop("buyer.name")));
		$case = $this->get_customer_case($customer_relation, true);
		$job = $case->add_job();// presentation job in sales schedule
		$job->set_prop("planned_length", $this->prop("avg_presentation_duration_est"));
		$presentation->set_prop("customer_relation", $customer_relation->id());
		$presentation->save();
		return $presentation;
	}

	/** Creates a phone call task
	@attrib api=1 params=pos
	@param to type=CL_CRM_COMPANY_CUSTOMER_DATA
	@param time type=int default=0
		UNIX timestamp. Default means an unscheduled call is created
	@returns CL_CRM_CALL
		Created call object
	**/
	public function create_call(object $customer_relation, $time = 0)
	{
		// uses only customer relation prop avoid additional connection objects
		// customer prop is left empty as redundant
		$calls_folder = $this->prop("calls_folder");
		$call = obj(null, array(), CL_CRM_CALL);
		$call->set_parent($calls_folder);
		$call->set_name(sprintf(t("K&otilde;ne %s kliendile %s"), $this->get_calls_count($customer_relation) + 1, $customer_relation->prop("buyer.name")));
		$call->set_prop("customer_relation", $customer_relation->id());
		$this->set_call_time($call, $time);
		$company = $this->awobj_get_owner();
		$profession = new object($this->prop("role_profession_telemarketing_salesman"), array(), CL_CRM_PROFESSION);
		$human_resources_manager = mrp_workspace_obj::get_hr_manager($company);
		$resource = $human_resources_manager->get_profession_resource($company, $profession);
		$call->schedule($resource);
		return $call;
	}

	/** Makes a phone call.
	@attrib api=1 params=pos
	@param call type=CL_CRM_CALL
	@returns void
	**/
	public function make_call(object $call)
	{
	}

	/** Ends a phone call made in this application
	@attrib api=1 params=pos
	@param call type=CL_CRM_CALL
	@returns void
	**/
	public function end_call(object $call)
	{
		$this->process_call_result($call);
	}

	/**
	@attrib api=1 params=pos
	@param call type=CL_CRM_CALL
	@returns void
	**/
	public function process_call_result(object $call)
	{
		if (!is_oid($call->prop("customer_relation")))
		{
			throw new awex_crm_sales_call("Customer relation not defined");
		}

		// cache call data in cro
		$customer_relation = new object($call->prop("customer_relation"));
		$customer_relation->set_prop("sales_last_call_time", $call->prop("real_start"));
		$customer_relation->set_prop("sales_calls_made", $customer_relation->prop("sales_calls_made") + 1);
		$customer_relation->save();

		$result = (int) $call->prop("result");
		if ($result === crm_call_obj::RESULT_CALL)
		{
			$new_call_time =  $call->prop("new_call_date");
			$new_call = $this->result_call_for_ended_call($call, $new_call_time);
		}
		elseif ($result === crm_call_obj::RESULT_NOANSWER)
		{
			$new_call_time =  time() + $this->prop("call_result_noanswer_recall_time");
			$new_call = $this->result_call_for_ended_call($call, $new_call_time);
		}
		elseif ($result === crm_call_obj::RESULT_BUSY)
		{
			$new_call_time =  time() + $this->prop("call_result_busy_recall_time");
			$new_call = $this->result_call_for_ended_call($call, $new_call_time);
		}
		elseif ($result === crm_call_obj::RESULT_OUTOFSERVICE)
		{
			$new_call_time =  time() + $this->prop("call_result_outofservice_recall_time");
			$new_call = $this->result_call_for_ended_call($call, $new_call_time);
		}
		elseif ($result === crm_call_obj::RESULT_VOICEMAIL)
		{
			$new_call_time =  time() + $this->prop("call_result_busy_recall_time");
			$new_call = $this->result_call_for_ended_call($call, $new_call_time);
		}
		elseif ($result === crm_call_obj::RESULT_NEWNUMBER)
		{
			// replace old nr. and create immediate new call
			$new_call_time =  time();
			$new_call = $this->result_call_for_ended_call($call, $new_call_time);
		}
		elseif ($result === crm_call_obj::RESULT_PRESENTATION)
		{
			$presentation = $call->get_first_obj_by_reltype("RELTYPE_RESULT_PRESENTATION");
			if (!is_object($presentation))
			{ // create presentation
				$customer_relation->set_prop("sales_state", crm_company_customer_data_obj::SALESSTATE_PRESENTATION);
				$customer_relation->save();
			}
		}
		else
		{ // result not defined
		}
	}

	private function result_call_for_ended_call(object $ended_call, $new_call_time)
	{
		$new_call = $ended_call->get_first_obj_by_reltype("RELTYPE_RESULT_CALL");
		if (is_object($new_call))
		{ // call already created
			$this->set_call_time($new_call, $new_call_time);
			$new_call->save();
		}
		else
		{ // create call
			$customer_relation = new object($ended_call->prop("customer_relation"));
			$new_call = $this->create_call($customer_relation, $new_call_time);
			$customer_relation->set_prop("sales_state", crm_company_customer_data_obj::SALESSTATE_NEWCALL);
			$customer_relation->save();
			$ended_call->connect(array("to" => $new_call, "reltype" => "RELTYPE_RESULT_CALL"));
		}
		return $new_call;
	}

	/**
	@attrib api=1 params=pos
	@param customer_relation type=CL_CRM_COMPANY_CUSTOMER_DATA
	@param result type=int default=0
		One of crm_call::RESULT_... constants. If set, only calls with that result are counted
	@param result_count_mode type=string default="any"
		Has effect when $result parameter set. Applicable values:
		"any" counts any calls with $result.
		"last_consecutive" counts last consecutive calls with $result, if any
	@returns int
		Number of calls made in this sales application to customer considering given parameters
	**/
	public function get_calls_count(object $customer_relation, $result = 0, $result_count_mode = "any")
	{
		$calls_made = new object_list(array(
			"class_id" => CL_CRM_CALL,
			"lang_id" => array(),
			"site_id" => array(),
			"customer_relation" => $customer_relation->id(),
			"real_duration" => new obj_predicate_compare(OBJ_COMP_GREATER, 0)
		));
		return $calls_made->count();
	}

	/**
	@attrib api=1 params=pos
	@param customer_relation type=CL_CRM_COMPANY_CUSTOMER_DATA
	@returns CL_CRM_CALL
		Last call made to customer
	**/
	public function get_last_call(object $customer_relation)
	{
		$calls_made = new object_list(array(
			"class_id" => CL_CRM_CALL,
			"lang_id" => array(),
			"site_id" => array(),
			"customer_relation" => $customer_relation->id(),
			"real_duration" => new obj_predicate_compare(OBJ_COMP_GREATER, 0),
			new obj_predicate_limit(1),
			new obj_predicate_sort(array("real_start" => "desc"))
		));
		return $calls_made->begin();
	}

	public function process_presentation_result(object $presentation)
	{
		if (crm_presentation_obj::RESULT_MISS == $presentation->prop("result"))
		{
			// immediately call back to check why presentation didn't take place
			$customer_relation = new object($call->prop("customer_relation"));
			$new_call = $this->create_call(new object($presentation->prop("customer_relation")), time());
			$customer_relation->set_prop("sales_state", crm_company_customer_data_obj::SALESSTATE_NEWCALL);
			$customer_relation->save();
		}
		elseif (crm_presentation_obj::RESULT_SALE == $presentation->prop("result"))
		{
			$customer_relation = new object($call->prop("customer_relation"));
			$customer_relation->set_prop("sales_state", crm_company_customer_data_obj::SALESSTATE_SALE);
			$customer_relation->save();
		}
		elseif (crm_presentation_obj::RESULT_CALL == $presentation->prop("result"))
		{
		}
	}

	public function get_cfgform_for_object(object $object)
	{
		$clid = $object->class_id();
		$role = $this->get_current_user_role();
		$cfgform = null;
		if (CL_CRM_SALES == $clid)
		{
			try
			{
				$cfgform = new object($this->prop("cfgf_main_" . self::$role_ids[$role]));
			}
			catch (Exception $e)
			{
			}
		}
		elseif (CL_CRM_CALL == $clid)
		{
			try
			{
				$cfgform = new object($this->prop("cfgf_call_" . self::$role_ids[$role]));
			}
			catch (Exception $e)
			{
			}
		}
		elseif (CL_CRM_PRESENTATION == $clid)
		{
			try
			{
				$cfgform = new object($this->prop("cfgf_presentation_" . self::$role_ids[$role]));
			}
			catch (Exception $e)
			{
			}
		}
		return $cfgform;
	}

	private function set_call_time(object $call, $time)
	{
		$call->set_prop("start1", $time);

		if ($time > 1)
		{
			$call->set_prop("deadline", $time + 3600); //!!! normaalseks
		}

		$call->set_prop("end", $time + $this->prop("avg_call_duration_est"));
	}

	private function set_presentation_time(object $presentation, $time)
	{//!!! saab muuta ainult kuni mingi tingimuseni -- myygimehe plaani koostamiseni vms.
		$presentation->set_prop("start", $time);
		$presentation->set_prop("end", $time + $this->prop("avg_presentation_duration_est"));
	}

	private function get_customer_case(object $customer_relation, $create = false)
	{
		return $customer_relation->get_sales_case($create);
	}
}

/** Generic sales application exception **/
class awex_crm_sales extends awex_crm {}

/** Application owner company error **/
class awex_crm_sales_owner extends awex_crm_sales {}

/** Error with handling sales resource manager **/
class awex_crm_sales_resmgr extends awex_crm_sales {}

/** Error with handling sales case **/
class awex_crm_sales_case extends awex_crm_sales {}

/** Expected folder not found or invalid **/
class awex_crm_sales_folder extends awex_crm_sales {}

/** Call error **/
class awex_crm_sales_call extends awex_crm_sales {}

?>
