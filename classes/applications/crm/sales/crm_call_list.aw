<?php

/*
@classinfo maintainer=voldemar
*/

class crm_call_list extends object_list
{
	protected $user_role = crm_sales_obj::ROLE_GENERIC;

	public function __construct($param = array())
	{
		$param["class_id"] = CL_CRM_CALL;
		return parent::object_list($param);
	}

	public function filter($param)
	{
		$param["class_id"] = CL_CRM_CALL;
		$param["lang_id"] = array();
		$param["site_id"] = array();
		$application = automatweb::$request->get_application();

		if ($application->is_a(CL_CRM_SALES))
		{ // special properties only if in sales application
			$param["CL_CRM_CALL.RELTYPE_CUSTOMER_RELATION.seller"] = $application->prop("owner")->id();

			// role specific constraints
			$role = automatweb::$request->get_application()->get_current_user_role();
			switch ($role)
			{
				case crm_sales_obj::ROLE_GENERIC:
					if (empty($param["real_duration"]))
					{
						$param["real_duration"] = new obj_predicate_compare(OBJ_COMP_LESS, 1);//!!! tmp
					}
					break;

				case crm_sales_obj::ROLE_DATA_ENTRY_CLERK:
					break;

				case crm_sales_obj::ROLE_TELEMARKETING_SALESMAN:
					if (empty($param["real_duration"]))
					{
						$param["real_duration"] = new obj_predicate_compare(OBJ_COMP_LESS, 1);//!!! tmp
					}
					break;

				case crm_sales_obj::ROLE_TELEMARKETING_MANAGER:
					break;

				case crm_sales_obj::ROLE_SALESMAN:
					break;

				case crm_sales_obj::ROLE_MANAGER:
					break;
			}

			$param[] = new obj_predicate_sort(array("deadline" => "asc"));
		}

		return parent::filter($param);
	}

	protected function _int_add_to_list($oid_arr)
	{
		foreach($oid_arr as $oid)
		{
			$o = new object($oid);
			if ($o->is_a(CL_CRM_CALL))
			{
				$this->list[$oid] = $o;
				$this->list_names[$oid] = $this->list[$oid]->name();
				$this->list_objdata[$oid] = array(
					"brother_of" => $this->list[$oid]->brother_of()
				);
			}
		}
	}
}

?>
