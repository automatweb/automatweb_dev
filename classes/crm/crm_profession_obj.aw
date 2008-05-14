<?php

class crm_profession_obj extends _int_object
{
	function get_workers($section)
	{
		$ol = new object_list(array(
			"lang_id" => array(),
			 "site_id" => array(),
			 "CL_CRM_PERSON.RELTYPE_RANK" => $this->id(),
			"class_id" => CL_CRM_PERSON,
		));
		return $ol;
	}


	function get_workers_for_section($section)
	{
		$ol = new object_list();

		$rel_list = new object_list(array(
			"class_id" => CL_CRM_PERSON_WORK_RELATION,
			"site_id" => array(),
			"lang_id" => array(),
			"CL_CRM_PERSON_WORK_RELATION.RELTYPE_SECTION" => $section,
			"profession" => $this->id(),
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
	}
}

?>
