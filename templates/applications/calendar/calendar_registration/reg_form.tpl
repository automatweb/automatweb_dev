<form action="{VAR:baseurl}/reforb.{VAR:ext}" method="POST">
<table border="1">
	<tr>
		<td colspan="2">{VAR:person} {VAR:person_rank} {VAR:person_mail} {VAR:person_phone}</td>
	<tr>
	<tr>
		<td colspan="2">{VAR:date} {VAR:time_from} - {VAR:time_to}</td>
	</tr>
	<!-- SUB: FAIL_first_name -->
	<tr>
		<td colspan="2"><font color="red">J&auml;rgnev v&auml;li peab olema t&auml;idetud!</font></td>
	</tr>
	<!-- END SUB: FAIL_first_name -->
	<tr>
		<td>Eesnimi:</td>
		<td><input type="text" name="reg[first_name]" value="{VAR:first_name}"></td>
	</tr>
	<!-- SUB: FAIL_last_name -->
	<tr>
		<td colspan="2"><font color="red">J&auml;rgnev v&auml;li peab olema t&auml;idetud!</font></td>
	</tr>
	<!-- END SUB: FAIL_last_name -->
	<tr>
		<td>Perekonnanimi:</td>
		<td><input type="text" name="reg[last_name]" value="{VAR:last_name}"></td>
	</tr>

	<!-- SUB: FAIL_phone -->
	<tr>
		<td colspan="2"><font color="red">J&auml;rgnev v&auml;li peab olema t&auml;idetud!</font></td>
	</tr>
	<!-- END SUB: FAIL_phone -->
	<tr>
		<td>Telefon:</td>
		<td><input type="text" name="reg[phone]" value="{VAR:phone}"></td>
	</tr>
	<!-- SUB: FAIL_email -->
	<tr>
		<td colspan="2"><font color="red">J&auml;rgnev v&auml;li peab olema t&auml;idetud!</font></td>
	</tr>
	<!-- END SUB: FAIL_email -->
	<tr>
		<td>E-mail:</td>
		<td><input type="text" name="reg[email]" value="{VAR:email}"></td>
	</tr>

	<!-- SUB: FAIL_code -->
	<tr>
		<td colspan="2"><font color="red">J&auml;rgnev v&auml;li peab olema t&auml;idetud!</font></td>
	</tr>
	<!-- END SUB: FAIL_code -->
	<tr>
		<td>Isikukood:</td>
		<td><input type="text" name="reg[code]" value="{VAR:code}"></td>
	</tr>
	<tr>
		<td colspan="2">Sisu:</td>
	</tr>
	<tr>
		<td colspan="2"><textarea name="reg[content]" rows="10" cols="50">{VAR:content}</textarea></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="Registreeru"></td>
	</tr>
</table>
{VAR:reforb}
</form>