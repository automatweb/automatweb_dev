<?php

class k_test_completion_rule_obj extends _int_object
{
	public function get_conditions_by_block()
	{
		$ol = new object_list(array(
			"class_id" => CL_K_TEST_COMPLETION_RULE_CONDITION,
			"completion_rule" => $this->id(),
		));
		$retval = array();
		foreach($ol->arr() as $o)
		{
			$retval[$o->block] = $o;
		}
		return $retval;
	}

	public function applies($entry)
	{
		if ($this->prop("rule") == 0)
		{
			return $this->applies_by_condition($entry);
		}
		else
		{
			return $this->applies_direct($entry);
		}
	}

	private function applies_by_condition($entry)
	{
		$condition_list = $this->get_conditions_by_block();
		if (!count($condition_list))
		{
			return false;
		}

		foreach($condition_list as $condition)
		{
			if (!$condition->applies($entry))
			{
				return false;
			}
		}
		return true;
	}

	private function applies_direct($entry)
	{
		$blocks = $entry->test()->get_blocks();
		$matching_sums_cnt = 0;
		foreach($blocks->arr() as $block)
		{
			$block_sum = $entry->get_point_sum_by_block($block->id(), $this->prop("rule_conf_4"));
			$compare_sum = $this->handle_rule_conf_4($block, $this->prop("rule_conf_4"));
			switch($this->prop("rule_conf_3"))
			{
				case k_test_completion_rule_condition::TYPE_GREATER:
					if($block_sum > $compare_sum)
					{
						$matching_sums_cnt++;
					}
					break;
				case k_test_completion_rule_condition::TYPE_LESS:
					if($block_sum < $compare_sum)
					{
						$matching_sums_cnt++;
					}
					break;
				case k_test_completion_rule_condition::TYPE_GREATER_OR_EQ:
					if($block_sum >= $compare_sum)
					{
						$matching_sums_cnt++;
					}
					break;
				case k_test_completion_rule_condition::TYPE_LESS_OR_EQ:
					if($block_sum <= $compare_sum)
					{
						$matching_sums_cnt++;
					}
					break;
			}
		}

		switch ($this->prop("rule_conf_1"))
		{
			case 0:
				return $matching_sums_cnt >= $this->prop("rule_conf_2");

			case 1:
				return $matching_sums_cnt <= $this->prop("rule_conf_2");
		}

		return false;
	}

	public static function handle_rule_conf_4($block, $value)
	{
		$val = aw_math_calc::string2float($value);
		if(substr(trim($value), -1) === "%")
		{
			return $block->get_maximum_points() * $val / 100;
		}
		else
		{
			return $val;
		}
	}
}

?>
