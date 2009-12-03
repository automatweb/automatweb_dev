<?php

class k_test_completion_rule_condition_obj extends _int_object
{
	public function applies($entry)
	{
		$point_count = $entry->get_point_sum_by_block($this->prop("block"));
		$value = k_test_completion_rule_obj::handle_rule_conf_4(obj($this->prop("block")), $this->prop("value"));
		switch($this->prop("type"))
		{
				case k_test_completion_rule_condition::TYPE_GREATER:
					$retval = $point_count > $value;
					break;

				case k_test_completion_rule_condition::TYPE_LESS:
					$retval = $point_count < $value;
					break;

				case k_test_completion_rule_condition::TYPE_GREATER_OR_EQ:
					$retval = $point_count >= $value;
					break;

				case k_test_completion_rule_condition::TYPE_LESS_OR_EQ:
					$retval = $point_count <= $value;
					break;
		}
		return $retval;
	}
}

?>
