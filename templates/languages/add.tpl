<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">Keele kood:</td><td class="fform"><input type='text' NAME='acceptlang' VALUE='{VAR:acceptlang}'></td>
</tr>
<tr>
<td class="fcaption">Charset:</td><td class="fform"><input type='text' NAME='charset' VALUE='{VAR:charset}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' VALUE='Salvesta' CLASS="small_button"></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='admin_languages'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
</form>
