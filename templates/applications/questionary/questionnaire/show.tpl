<!-- SUB: QUESTIONNAIRE -->
<form method="post">
<table width="100%">
	<tr>
		<td>
			{VAR:question}
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" cellpadding="5" ja valign="top">
				<tr>
					<td>
						<!-- SUB: ANSWER_RADIO -->
							<input type="radio" value="{VAR:answer_oid}" name="answer" {VAR:answer_checked}> {VAR:answer_caption}<br>
						<!-- END SUB: ANSWER_RADIO -->
						<!-- SUB: ANSWER_TEXTBOX -->
							<input type="textbox" name="answer" value="{VAR:answer_value}"><br>
						<!-- END SUB: ANSWER_TEXTBOX -->
					</td>
					<td>
						<!-- SUB: PICTURE -->
						{VAR:picture}
						<!-- END SUB: PICTURE -->
					</td>
				</tr>
			</table>
		</td>		
	</tr>
	<tr>
		<td>
			<table width="100%">
				<tr>
					<td width="40%">
						{VAR:correct_vs_false}<br>
						<!-- SUB: CORRECT_ANSWERS -->
						{VAR:correct_answer_caption}<br>
						<!-- SUB: CORRECT_ANSWER -->
						{VAR:answer}<br>
						<!-- END SUB: CORRECT_ANSWER -->
						<!-- END SUB: CORRECT_ANSWERS -->
						{VAR:acomment}<br>
						{VAR:qcomment}<br><br>
					</td>
					<td width="20%">
						<!-- SUB: ANSWER_PICTURE -->
						{VAR:picture}
						<!-- END SUB: ANSWER_PICTURE -->
					</td>
					<td>
						{VAR:submit} <a href="{VAR:next_url}">{VAR:next_caption}</a>
					</td>
				</tr>
			</table>
		</td>		
	</tr>
</table>
<input type="hidden" value="{VAR:question_id}" name="qid">
</form>
<!-- END SUB: QUESTIONNAIRE -->
<!-- SUB: RESULTS -->
<table width="100%">
	<tr>
		<td align="center">
			{VAR:results_percent}<br>
			{VAR:results_text}<br>
			<!-- SUB: RESULTS_TEXT_BY_PERCENT -->
			{VAR:results_text_by_percent}<br>
			<!-- END SUB: RESULTS_TEXT_BY_PERCENT -->
		</td>
	</tr>
</table>
<!-- END SUB: RESULTS -->