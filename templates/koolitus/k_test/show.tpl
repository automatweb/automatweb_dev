<!-- SUB: QUESTION -->
<b>{VAR:block}</b><br />
{VAR:image}
K&uuml;simus: {VAR:question}<br />
Valikvastused:<br />
<!-- SUB: OPTIONS -->
<input type="radio" value="{VAR:option_using_id}" name="option_using" />{VAR:option}<br />
<!-- END SUB: OPTIONS -->
<input type="hidden" value="{VAR:test_question_id}" name="test_question" />
<input type="hidden" value="{VAR:test_entry_id}" name="test_entry" />
<!-- END SUB: QUESTION -->

<!-- SUB: RESULT -->
TULEMUS: {VAR:result}
<!-- END SUB: RESULT -->