<?php

class k_test_entry_obj extends _int_object
{
	public function get_latest_test_question()
	{
		$ol = new object_list(array(
			"class_id" => CL_K_TEST_ANSWER,
			"test_entry" => $this->id(),
			new obj_predicate_sort(array(
				"created" => "desc",
				"oid" => "desc",
			)),
			new obj_predicate_limit(1),
		));
		return $ol->count() > 0 ? $ol->begin()->test_question() : false;
	}

	public function save_answer_to_question($test_question_id, $option_using_id)
	{
		$answer_points = obj($option_using_id)->value;

		$o = obj();
		$o->set_name(sprintf(
			t("Vastus testi %s k&uuml;simusele %s"), 
			obj($this->parent())->name(),
			obj($test_question_id)->name()
		));
		$o->set_parent($this->id());
		$o->set_class_id(CL_K_TEST_ANSWER);
		$o->test_entry = $this->id();
		$o->test_question = $test_question_id;
		$o->option_using = $option_using_id;
		$o->answer_value = $answer_points;
		$o->save();

		$this->set_prop("total_points", $this->prop("total_points") + $answer_points);
		$this->save();

		return $o;
	}

	public function get_answer_for($question)
	{
		$ol = new object_list(array(
			"class_id" => CL_K_TEST_ANSWER,
			"test_entry" => $this->id(),
			"test_question" => $question->id()
		));
		if (!$ol->count())
		{
			return null;
		}
		return $ol->begin()->prop("option_using");
	}

	public function get_all_answers()
	{
		$ol = new object_list(array(
			"class_id" => CL_K_TEST_ANSWER,
			"test_entry" => $this->id(),
			new obj_predicate_sort(array(
				"created" => "asc"
			))
		));
		return $ol->arr();
	}

	public function needs_analysis()
	{
		$test = obj($this->prop("test"));
		$retval = false;
		if($test->is_a(CL_K_TEST))
		{
			foreach($test->get_all_completion_rules() as $rule)
			{
				if ($rule->applies(obj($this->id())))
				{
					$retval = $rule->type == 0 ? true : false;
				}
			}
		}
		$this->set_prop("result", (int)$retval);
		$this->save();
		return $retval;
	}

	public function get_point_sum_by_block($block_id)
	{
		$ol = new object_list(array(
			"class_id" => CL_K_TEST_ANSWER,
			"test_question.block" => $block_id,
			"test_entry" => $this->id(),
		));
		$sum = 0;
		foreach($ol->arr() as $o)
		{
			$sum += $o->answer_value;
		}
		return $sum;
	}
}

?>
