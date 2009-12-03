<?php

class k_ignore_rule_obj extends _int_object
{
	public function applies($test_entry, $question)
	{
		$answered_option_using = $test_entry->get_answer_for(
			obj($this->prop("ignore_question"))
		);
	
		if (!is_oid($answered_option_using))
		{
			return false;
		}

		if (obj($answered_option_using)->prop("option") == $this->prop("ignore_option"))
		{
			return true;
		}
		return false;
	}
}

?>
