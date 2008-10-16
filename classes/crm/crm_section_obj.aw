<?php

class crm_section_obj extends _int_object
{
	function get_job_offers()
	{
		$r = new object_list;
		foreach($this->connections_to(array("from.class_id" => CL_PERSONNEL_MANAGEMENT_JOB_OFFER, "type" => "RELTYPE_SECTION")) as $conn)
		{
			$r->add($conn->prop("from"));
		}
		return $r;
	}

	function get_employees()
	{
		$ol = new object_list();
		//getting all the workers for the $obj
		$conns = $this->connections_from(array(
			"type" => "RELTYPE_WORKERS",
		));
		foreach($conns as $conn)
		{
			$ol->add($conn->prop('to'));
		}

		//getting all the sections
		$conns = $this->connections_from(array(
			"type" => "RELTYPE_SECTION",
		));
		foreach($conns as $conn)
		{
			$section = $conn->to();
			foreach($section->get_employees()->ids() as $oid)
			{
				$ol->add($oid);
			}
		}

		return $ol;
	}

	function get_workers_grp_profession()
	{
		$ret = array();
		$rel_list = new object_list(array(
			"class_id" => CL_CRM_PERSON_WORK_RELATION,
			"site_id" => array(),
			"lang_id" => array(),
			"CL_CRM_PERSON_WORK_RELATION.RELTYPE_SECTION" => $this->id(),
		));

		if(sizeof($rel_list->ids()))
		{
			foreach($rel_list->arr() as $rel)
			{
				$person_list = new object_list(array(
					"class_id" => CL_CRM_PERSON,
					"site_id" => array(),
					"class_id" => array(),
					"CL_CRM_PERSON.RELTYPE_CURRENT_JOB" => $rel->id(),
				));
				$person = reset($person_list->ids());
				if($rel->prop("profession"))
				{
					$ret[$rel->prop("profession")][] = $person;
				}
				else
				{
					$ret[0][] = $person;
				}
			}
			return $ret;
		}

		return $ret;
	}

	function get_workers($arr = array())
	{
		$ol = new object_list();

		$rel_list = new object_list(array(
			"class_id" => CL_CRM_PERSON_WORK_RELATION,
			"site_id" => array(),
			"lang_id" => array(),
			"CL_CRM_PERSON_WORK_RELATION.RELTYPE_SECTION" => $this->id(),
		));

		if(sizeof($rel_list->ids()))
		{
			$ol = new object_list(array(
				"class_id" => CL_CRM_PERSON,
				"site_id" => array(),
				"class_id" => array(),
				"CL_CRM_PERSON.RELTYPE_CURRENT_JOB" => $rel_list->ids(),
			));
		}

		//vana asja toimiseks
		$ol->add($this->get_employees());
		return $ol;
	}

//2kki optimeerib... seep2rast selline lisa funktsioon
	/** Returns company worker selection
		@attrib api=1 params=name
		@param active optional type=bool
			if set, returns only active workers
		@return object list
			person object list
	**/
	public function get_worker_selection($arr = array())
	{
		$workers = $this->get_workers($arr);

		return $workers->names();

	}

	function get_sections()
	{
		$ol = new object_list();
		$ol->add($this->id());
		foreach($this->connections_from(array(
			"type" => "RELTYPE_SECTION",
			"sort_by_num" => "to.jrk"
		)) as $conn)
		{
			$ol->add($conn->prop("to"));
			$parent = $conn->to();
			$ol->add($parent->get_sections());
		}
		return $ol;
	}

	function has_sections()
	{
		$sub = $this->get_first_obj_by_reltype("RELTYPE_SECTION");
		if(is_object($sub))
		{
			return 1;
		}
		return 0;
	}

	function has_workers()
	{
		$sub = $this->get_first_obj_by_reltype("RELTYPE_WORKERS");
		if(is_object($sub))
		{
			return 1;
		}
		
		$rels = $this->get_work_relations(array("limit" => 1));
		if(sizeof($rels->ids()))
		{
			return 1;
		}

		return 0;
	}

	/** Returns company work relations
		@attrib api=1 params=name
		@param limit optional type=int
		@return object list
			section work relations object list
	**/
	public function get_work_relations($arr = array())
	{
		$filter = array(
			"class_id" => CL_CRM_PERSON_WORK_RELATION,
			"lang_id" => array(),
			"site_id" => array(),
			"section" => $this->id(),
		);
		if($arr["limit"])
		{
			$filter["limit"] = 1;
		}
		$ol = new object_list($filter);
		return $ol;
	}

	function get_professions()
	{
		$ol = new object_list();
		foreach($this->connections_from(array(
			"type" => "RELTYPE_PROFESSIONS",
			"sort_by_num" => "to.jrk"
		)) as $conn)
		{
			$ol->add($conn->prop("to"));
		}
		return $ol;
	}
}

?>
