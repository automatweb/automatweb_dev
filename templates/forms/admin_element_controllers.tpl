<table border=0 cellpadding=2 bgcolor=#badbad>
<tr><td>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption">{VAR:LC_FORMS_ELEMENT_TEXT}:</td>
		<td class="fcaption">{VAR:element_text}</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_FORMS_ELEMENT_TYPE}:</td>
		<td class="fcaption">{VAR:element_type}</td>
	</tr>
<!-- SUB: T_TEXTBOX -->
	<tr>
		<td class="fcaption">{VAR:LC_FORMS_ELEMENT_MAX_LENGTH}:</td>
		<td class="fcaption"><input type='text' NAME='el_{VAR:el_id}_maxlength' VALUE='{VAR:el_maxlen}'></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_FORMS_ELEMENT_MIN_LENGTH}:</td>
		<td class="fcaption"><input type='text' NAME='el_{VAR:el_id}_minlength' VALUE='{VAR:el_minlen}'></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_FORMS_CAN_INSERT_ONLY}:</td>
		<td class="fcaption"><select NAME='el_{VAR:el_id}_ctype'>
			<option>
			<option VALUE='email' {VAR:t_email_sel}>{VAR:LC_FORMS_EMAIL_ADDRESS}
			<option VALUE='url' {VAR:t_url_sel}>URL'i
			<option VALUE='number' {VAR:t_number_sel}>{VAR:LC_FORMS_NUMBERS}
			<option VALUE='letter' {VAR:t_letter_sel}>{VAR:LC_FORMS_LETTERS}
		</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_FORMS_ELEMENT_IS_REQUIRED}:</td>
		<td class="fcaption"><input type='checkbox' NAME='el_{VAR:el_id}_req' VALUE=1 {VAR:el_req_sel}></td>
	</tr>
<!-- END SUB: T_TEXTBOX -->
</table>
</td></tr></table>