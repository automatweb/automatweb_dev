<?php

class person_skill_manager_obj extends _int_object
{

	function get_all_skills()
	{
		$filter = array(
			"lang_id" => array(),
			"site_id" => array(),
			"class_id" => CL_PERSON_SKILL,
		);
		$ol = new object_list($filter);
		return $ol;
	}


}

?>
