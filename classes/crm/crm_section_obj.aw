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

	function get_workers()
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
			$person_list = new object_list(array(
				"class_id" => CL_CRM_PERSON,
				"site_id" => array(),
				"class_id" => array(),
				"CL_CRM_PERSON.RELTYPE_CURRENT_JOB" => $rel_list->ids(),
			));
			return $person_list;
		}
		else
		{
			return $ol;
		}
//		foreach($rel_list->arr() as $rel)
//		{
//			arr($rel->prop(""))
//		}
//arr($rel_list);
/*		foreach($rel_list->arr() as $rel)
		{arr($rel);
			$person = $rel->get_first_obj_by_reltype(array("type" => "RELTYPE_PERSON"));
			if($person)
			{
				$ol->add($person);
			}
		}
*/

//		foreach($this->connections_from(array(
//			"type" => "RELTYPE_WORKERS",
//			"sort_by_num" => "to.jrk"
//		)) as $conn)
//		{
//			$ol->add($conn->prop("to"));
//		}
		return $ol;
	}

	function get_sections()
	{
		$ol = new object_list();
		foreach($this->connections_from(array(
			"type" => "RELTYPE_SECTION",
			"sort_by_num" => "to.jrk"
		)) as $conn)
		{
			$ol->add($conn->prop("to"));
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
		return 0;
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
