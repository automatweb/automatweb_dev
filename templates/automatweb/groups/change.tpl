<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">Muudetud:</td><td class="fform">{VAR:modifiedby}, {VAR:modified}</td>
</tr>
<tr>
<td class="fcaption">Liikmeid:</td><td class="fform">{VAR:members}</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_group_change'>
<input type='hidden' NAME='gid' VALUE='{VAR:gid}'>
<input type='hidden' NAME='type' VALUE='{VAR:type}'>
</form>
