<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="hele_hall_taust">Nimi:</td><td class="fform"><input type='text' NAME='name' size='50' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td colspan="2" class="hele_hall_taust">
Hinne peaks olema number ja võib olla ka negatiivne
</td>
</tr>
<tr>
<td class="hele_hall_taust">Hinne:</td><td class="fform"><input type='text' NAME='rate' size='4' VALUE='{VAR:rate}'></td>
</tr>
<tr>
<td align="center" class="hele_hall_taust" colspan="2">
<input type="submit" value="Salvesta">
</td>
</tr>
</table>
{VAR:reforb}
</form>
