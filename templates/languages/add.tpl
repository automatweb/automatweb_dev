<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_LANGUAGES_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_LANGUAGES_LANGUAGE_ID}:</td><td class="fform"><input type='text' NAME='acceptlang' VALUE='{VAR:acceptlang}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_LANGUAGES_CHARSET}:</td><td class="fform"><input type='text' NAME='charset' VALUE='{VAR:charset}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' VALUE='Save' CLASS="small_button"></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='admin_languages'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
</form>
