<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">Muudetud:</td><td class="fform">{VAR:modifiedby}, {VAR:modified}</td>
</tr>
<tr>
<td class="fcaption">Liikmeid:</td><td class="fform">{VAR:gcount}</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
{VAR:reforb}
</form>
