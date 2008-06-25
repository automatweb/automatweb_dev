<?php

class crm_area_obj extends _int_object
{
	/** Returns object list of personnel_management_job_offer objects that are connected to the area.

		@attrib name=get_job_offers params=name api=1

		@param parent optional type=oid,array(oid) acl=view

		@param status optional type=int
			The status of the personnel_management_job_offer objects.

		@param props optional type=array
			You can add here filters for the object list.

	**/
	function get_job_offers($arr)
	{
		$this->prms(&$arr);

		$ol_prms = array(
			"class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER,
			"CL_PERSONNEL_MANAGEMENT_JOB_OFFER.RELTYPE_AREA" => parent::id(),
			"status" => $arr["status"],
			"parent" => $arr["parent"],
			"lang_id" => array(),
			"site_id" => array(),
		);

		if(is_array($arr["props"]) && count($arr["props"]) > 0)
		{
			$ol_prms += $arr["props"];
		}

		$needed_acl = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->needed_acl_job_offer;

		$ol_tmp = new object_list($ol_prms);
		$ids = (is_array($needed_acl) && count($needed_acl) > 0) ? array() : $ol_tmp->ids();
		foreach($ol_tmp->ids() as $oid)
		{
			$acl_ok = true;
			foreach($needed_acl as $acl)
			{
				$acl_ok = $acl_ok && $this->can($acl, $oid);
			}
			if($acl_ok)
			{
				$ids[] = $oid;
			}
		}

		$ol = new object_list();
		$ol->add($ids);

		return $ol;
	}

	/**
		@attrib name=get_residents api=1 params=name

		@param parent optional type=oid,array(oid)
			The oid(s) of the parent(s) of the crm_person objects.

		@param status optional type=int
			The status of the crm_person objects.

		@param by_jobwish optional type=bool
			
	**/
	function get_residents($arr)
	{
		$this->prms(&$arr);

		$ol_prms = array(
			"class_id" => CL_CRM_PERSON,
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"parent" => $arr["parent"],
					"CL_CRM_PERSON.RELTYPE_PERSONNEL_MANAGEMENT" => $arr["personnel_management"],
				)
			)),
			"status" => $arr["status"],
		);
		if(!$arr["by_jobwish"])
		{
			$ol_prms[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"CL_CRM_PERSON.RELTYPE_ADDRESS.RELTYPE_PIIRKOND" => parent::id(),
					"CL_CRM_PERSON.RELTYPE_ADDRESS.RELTYPE_MAAKOND.parent" => parent::id(),
					"CL_CRM_PERSON.RELTYPE_ADDRESS.RELTYPE_LINN.parent" => parent::id(),
					// Do we really need 'em both?? Reltypes, I mean.
					"CL_CRM_PERSON.RELTYPE_CORRESPOND_ADDRESS.RELTYPE_PIIRKOND" => parent::id(),
					"CL_CRM_PERSON.RELTYPE_CORRESPOND_ADDRESS.RELTYPE_MAAKOND.parent" => parent::id(),
					"CL_CRM_PERSON.RELTYPE_CORRESPOND_ADDRESS.RELTYPE_LINN.parent" => parent::id(),
				),
			));
		}
		else
		{
			$ol_prms[] = new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					//"CL_CRM_PERSON.RELTYPE_WORK_WANTED.location_text" = "%".parent::name()."%",
					"CL_CRM_PERSON.RELTYPE_WORK_WANTED.RELTYPE_LOCATION" => parent::id(),
					"CL_CRM_PERSON.RELTYPE_WORK_WANTED.RELTYPE_LOCATION2" => parent::id(),
					"CL_CRM_PERSON.RELTYPE_WORK_WANTED.RELTYPE_LOCATION.parent" => parent::id(),
					"CL_CRM_PERSON.RELTYPE_WORK_WANTED.RELTYPE_LOCATION2.parent" => parent::id(),
				),
			));
		}

		if($arr["by_jobwish"])
		{
			$needed_acl = obj(get_instance(CL_PERSONNEL_MANAGEMENT)->get_sysdefault())->needed_acl_employees;

			$ol_tmp = new object_list($ol_prms);
			$ids = (is_array($needed_acl) && count($needed_acl) > 0) ? array() : $ol_tmp->ids();
			foreach($ol_tmp->ids() as $oid)
			{
				$acl_ok = true;
				foreach($needed_acl as $acl)
				{
					$acl_ok = $acl_ok && $this->can($acl, $oid);
				}
				if($acl_ok)
				{
					$ids[] = $oid;
				}
			}

			$ol = new object_list();
			$ol->add($ids);

			return $ol;
		}
		else
		{
			return new object_list($ol_prms);
		}
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
					"lang_id" => array(),
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
