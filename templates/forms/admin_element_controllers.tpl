<table border=0 cellpadding=2 bgcolor=#badbad>
<tr><td>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption">Elemendi tekst:</td>
		<td class="fcaption">{VAR:element_text}</td>
	</tr>
	<tr>
		<td class="fcaption">Elemendi tyyp:</td>
		<td class="fcaption">{VAR:element_type}</td>
	</tr>
<!-- SUB: T_TEXTBOX -->
	<tr>
		<td class="fcaption">Elemendi max pikkus:</td>
		<td class="fcaption"><input type='text' NAME='el_{VAR:el_id}_maxlength' VALUE='{VAR:el_maxlen}'></td>
	</tr>
	<tr>
		<td class="fcaption">Elemendi min pikkus:</td>
		<td class="fcaption"><input type='text' NAME='el_{VAR:el_id}_minlength' VALUE='{VAR:el_minlen}'></td>
	</tr>
	<tr>
		<td class="fcaption">V6ib sisestada ainult:</td>
		<td class="fcaption"><select NAME='el_{VAR:el_id}_ctype'>
			<option>
			<option VALUE='email' {VAR:t_email_sel}>E-maili aadressi
			<option VALUE='url' {VAR:t_url_sel}>URL'i
			<option VALUE='number' {VAR:t_number_sel}>Numbreid
			<option VALUE='letter' {VAR:t_letter_sel}>T&auml;hti
		</td>
	</tr>
	<tr>
		<td class="fcaption">Element on kohustuslik:</td>
		<td class="fcaption"><input type='checkbox' NAME='el_{VAR:el_id}_req' VALUE=1 {VAR:el_req_sel}></td>
	</tr>
<!-- END SUB: T_TEXTBOX -->
</table>
</td></tr></table>