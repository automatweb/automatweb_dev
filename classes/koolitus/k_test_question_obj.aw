<?php

class k_test_question_obj extends _int_object
{
	public function get_values()
	{
		$ol = new object_list(array(
			"class_id" => CL_K_OPTION_USING,
			"test_question" => $this->id(),
		));
		$values = array();
		foreach($ol->arr() as $o)
		{
			$values[$o->option] = $o;
		}
		return $values;
	}

	public function get_ignore_rules()
	{
		$ol = new object_list(array(
			"class_id" => CL_K_IGNORE_RULE,
			"question" => $this->id()
		));
		return $ol->arr();
	}
}

?>
