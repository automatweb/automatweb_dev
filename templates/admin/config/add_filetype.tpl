<form action='refcheck.{VAR:ext}' method=post>
{VAR:error}
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Laiend:</td><td class="fform"><input type='text' NAME='extt' VALUE='{VAR:extt}'></td>
</tr>
<tr>
<td class="fcaption">T&uuml;&uuml;p:</td><td class="fform"><input type='text' NAME='type' VALUE='{VAR:type}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_file_icon'>
<input type='hidden' NAME='change' VALUE='{VAR:change}'>
</form>
