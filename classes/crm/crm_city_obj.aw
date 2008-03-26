<?php

class crm_city_obj extends _int_object
{
	/** Returns object list of personnel_management_job_offer objects that are connected to the city.

		@attrib name=get_job_offers params=name api=1

		@param parent optional type=oid,array(oid) acl=view

		@param status optional type=int
			The status of the personnel_management_job_offer objects.

	**/
	function get_job_offers($arr)
	{
		$this->prms(&$arr);

		return new object_list(array(
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			"parent" => $arr["parent"],
			"CL_PERSONNEL_MANAGEMENT_JOB_OFFER.RELTYPE_CITY" => parent::id(),
			"status" => $arr["status"],
		));
	}

	/**
		@attrib name=get_residents api=1 params=name

		@param parent optional type=oid,array(oid)
			The oid(s) of the parent(s) of the crm_person objects.

		@param status optional type=int
			The status of the crm_person objects.
	**/
	function get_residents($arr)
	{
		$this->prms(&$arr);

		return new object_list(array(
			"class_id" => CL_CRM_PERSON,
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"parent" => $arr["parent"],
					"CL_CRM_PERSON.RELTYPE_PERSONNEL_MANAGEMENT" => $arr["personnel_management"],
				)
			)),
			"status" => $arr["status"],
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_ADDRESS.RELTYPE_LINN" => parent::id(),
					// Do we really need 'em both?? Reltypes, I mean.
					"CL_CRM_PERSON.RELTYPE_CORRESPOND_ADDRESS.RELTYPE_LINN" => parent::id(),
				),
			)),
		));
	}

	function prms($arr)
	{
		$arr["parent"] = !isset($arr["parent"]) ? array() : $arr["parent"];
		if(!is_array($arr["parent"]))
		{
			$arr["parent"] = array($arr["parent"]);
		}
		$arr["status"] = !isset($arr["status"]) ? array() : $arr["status"];
		$arr["childs"] = !isset($arr["childs"]) ? true : $arr["childs"];

		if($arr["childs"] && (!is_array($arr["parent"]) || count($arr["parent"]) > 0))
		{
			$pars = $arr["parent"];
			foreach($pars as $par)
			{
				$ot = new object_tree(array(
					"class_id" => CL_MENU,
					"status" => $arr["status"],
					"parent" => $par,
				));
				foreach($ot->ids() as $oid)
				{
					$arr["parent"][] = $oid;
				}
			}
		}
	}
}

?>
