<?php

class k_question_obj extends _int_object
{
	public function get_options()
	{
		$ol = new object_list(array(
			"class_id" => CL_K_OPTION,
			"CL_K_OPTION.RELTYPE_OPTION(CL_K_QUESTION)" => $this->id(),
		));
		return $ol;
	}
}

?>
