<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">Nimi:</td><td class="fform"><input type='text' NAME='name' ></td>
</tr>
<tr>
<td class="fform" colspan=2>T&uuml;&uuml;p:</td>
</tr>
<tr>
<td class="fform" colspan=2><input type='radio' name='type' value=0>Tavaline grupp</td>
</tr>
<tr>
<td class="fform" colspan=2><input type='radio' name='type' value=2>Dyn. Grupp</td>
</tr>
<tr>
<td class="fform">Otsinguform dyngrupi jaoks:</td><td class="fform"><select name='search_form'>{VAR:search_forms}</select></td>
</tr>
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
{VAR:reforb}
</form>
