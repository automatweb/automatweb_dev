<table width="100%">
	<tr>
		<td valign="top">
			{VAR:question}
		</td>
	</tr>
	<tr>
		<td valign="top">
			<table width="100%" cellpadding="5">
				<tr>
					<td valign="top">
						<!-- SUB: ANSWER_RADIO -->
							<input type="radio" value="{VAR:answer_oid}" name="answers[{VAR:question_id}]" {VAR:answer_checked}> {VAR:answer_caption}<br>
						<!-- END SUB: ANSWER_RADIO -->
						<!-- SUB: ANSWER_TEXTBOX -->
							<input type="textbox" name="answers[{VAR:question_id}]" value="{VAR:answer_value}"><br>
						<!-- END SUB: ANSWER_TEXTBOX -->
					</td>
					<td valign="top">
						<!-- SUB: PICTURE -->
						{VAR:picture}
						<!-- END SUB: PICTURE -->
					</td>
				</tr>
			</table>
		</td>		
	</tr>
	<tr>
		<td valign="top">
			<table width="100%">
				<tr>
					<td width="40%" valign="top">
						{VAR:qcomment}<br>
					</td>
				</tr>
			</table>
		</td>		
	</tr>
</table>