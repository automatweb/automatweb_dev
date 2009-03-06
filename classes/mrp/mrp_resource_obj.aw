<?php

class mrp_resource_obj extends _int_object
{
	function get_possible_materials()
	{
		$ol = new object_list(array(
			"class_id" => CL_MATERIAL_EXPENSE_CONDITION,
			"lang_id" => array(),
			"site_id" => array(),
			"resource" => $this->id()
		));
		return $ol->arr();
	}
}