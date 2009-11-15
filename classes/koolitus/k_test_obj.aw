<?php

class k_test_obj extends _int_object
{
	public function get_test_questions()
	{
		$ol = new object_list(array(
			"class_id" => CL_K_TEST_QUESTION,
			"test" => $this->id(),
			new obj_predicate_sort(array(
				"jrk" => "asc",
			)),
		));
		return $ol;
	}

	public function get_next_question($test_entry)
	{
		$block = $this->get_current_block($test_entry);
		$question = $block !== false ? $block->get_next_question($test_entry) : false;

		while($question === false && $block !== false)
		{
			$block = $this->get_next_block($test_entry, $block->id());

			$question = $block !== false ? $block->get_next_question($test_entry) : false;
		}
		return $question;
	}

	public function validate_question($test_entry, $question)
	{
		foreach($question->get_ignore_rules() as $rule)
		{
			if ($rule->applies($test_entry, $question))
			{
				return false;
			}
		}
		return true;
	}

	public function get_current_block($test_entry)
	{
		$test_question = $test_entry->get_latest_test_question();
		if($test_question !== false)
		{
			return $test_question->block();
		}
		return $this->get_next_block($test_entry, false);
	}

	public function get_next_block($test_entry, $previous_block_id)
	{
		$blocks = $this->get_blocks();
		$previous_block_found = false;
		foreach($blocks->ids() as $block_id)
		{
			if($previous_block_found || $previous_block_id === false)
			{
				return obj($block_id);
			}
			if($block_id == $previous_block_id)
			{
				$previous_block_found = true;
			}
		}
		return false;
	}

	public function get_blocks()
	{
		$ol = new object_list(array(
			"class_id" => CL_K_TEST_BLOCK,
			"parent" => $this->id(),
			new obj_predicate_sort(array(
				"jrk" => "asc",
			))
		));
		return $ol;
	}

	public function get_new_test_entry()
	{
		$o = obj();
		$o->set_class_id(CL_K_TEST_ENTRY);
		$o->set_parent($this->id());
		$o->test = $this->id();
		$o->set_name(sprintf(t("Sisestus testile '%s'"), parse_obj_name($this->name())));
		$o->save();

		return $o;
	}

	public function get_all_test_entries()
	{
		$ol = new object_list(array(
			"class_id" => CL_K_TEST_ENTRY,
			"parent" => $this->id()
		));
		return $ol->arr();
	}

	public function get_all_completion_rules()
	{
		$ol = new object_list($this->connections_from(array(
			"type" => "RELTYPE_COMPLETION_RULE"
		)));
		return $ol->arr();
	}
}

?>
