<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
	<td class="fcaption">Men&uuml;&uuml;</td><td class="fform">Tekst otsikastis</td><td class="fform">J&auml;rjekord</td>
</tr>
<!-- SUB: RUBR -->
<tr>
	<td class="fcaption">{VAR:section}</td><td class="fform"><input type='text' NAME='se_{VAR:section_id}' VALUE='{VAR:section_name}'></td><td class="fform"><input type='text' NAME='so_{VAR:section_id}' VALUE='{VAR:order}' size=3></td>
</tr>
<!-- END SUB: RUBR -->
<tr>
<td class="fcaption" colspan=3><input type='submit' VALUE='Salvesta' CLASS="small_button"></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='admin_search_conf'>
<input type='hidden' NAME='level' VALUE='1'>
</form>
