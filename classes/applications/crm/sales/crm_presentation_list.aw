<?php

/*
@classinfo maintainer=voldemar
*/

class crm_presentation_list extends object_list
{
	protected $user_role = crm_sales_obj::ROLE_GENERIC;

	public function __construct($param = array())
	{
		$param["class_id"] = CL_CRM_PRESENTATION;
		return parent::object_list($param);
	}


	public function filter($param)
	{
		$param["class_id"] = CL_CRM_PRESENTATION;
		$param["lang_id"] = array();
		$param["site_id"] = array();

		$application = automatweb::$request->get_application();
		if ($application->is_a(CL_CRM_SALES))
		{ // special properties only if in sales application
			$param["CL_CRM_PRESENTATION.RELTYPE_CUSTOMER_RELATION.seller"] = $application->prop("owner")->id();

			// role specific constraints
			$role = automatweb::$request->get_application()->get_current_user_role();
			switch ($role)
			{
				case crm_sales_obj::ROLE_GENERIC:
					break;

				case crm_sales_obj::ROLE_DATA_ENTRY_CLERK:
					$param = array();
					break;

				case crm_sales_obj::ROLE_TELEMARKETING_SALESMAN:
					break;

				case crm_sales_obj::ROLE_TELEMARKETING_MANAGER:
					break;

				case crm_sales_obj::ROLE_SALESMAN:
					$param["CL_CRM_PRESENTATION.RELTYPE_ROW.RELTYPE_IMPL"] = get_current_person(); // only salesman's own presentations
					$param["CL_CRM_PRESENTATION.RELTYPE_ROW.primary"] = 1;
					break;

				case crm_sales_obj::ROLE_MANAGER:
					break;
			}

			$param[] = new obj_predicate_sort(array("start" => "asc"));
		}

		return parent::filter($param);
	}

	/* protected */ function _int_add_to_list($oid_arr) // reinstate access level after object_list method access level determined.
	{
		foreach($oid_arr as $oid)
		{
			$o = new object($oid);
			if ($o->is_a(CL_CRM_PRESENTATION))
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
