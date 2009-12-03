<?php

class k_test_block_obj extends _int_object
{
	public function get_test_questions()
	{
		$ol = new object_list(array(
			"class_id" => CL_K_TEST_QUESTION,
			"block" => $this->id(),
			new obj_predicate_sort(array(
				"jrk" => "asc"
			))
		));
		return $ol;
	}

	public function get_next_question($test_entry)
	{
		$questions = $this->get_test_questions();
		$latest_question = $test_entry->get_latest_test_question();
		if ($latest_question && $latest_question->prop("block") != $this->id())
		{
			$latest_question = false;
		}

		$previous_question_found = false;
		foreach($questions->ids() as $question_id)
		{
			if($previous_question_found || $latest_question === false)
			{
				$question = obj($question_id);
				if(obj($this->parent())->validate_question($test_entry, $question))
				{
					return $question;
				}
			}
			if($latest_question && $latest_question->id() == $question_id)
			{
				$previous_question_found = true;
			}
		}
		return false;
	}

	public function get_maximum_points()
	{
		$sum = 0;
		foreach($this->get_test_questions()->arr() as $question)
		{
			foreach($question->get_values() as $value)
			{
				$max = isset($max) ? max($max, $value->value) : $value->value;
			}
			$sum += $max;
			unset($max);
		}
		return $sum;
	}
}

?>
