<table border="0" cellspacing="1" bgcolor="#CCCCCC">
<form method="POST" action="reforb.{VAR:ext}">
<tr>
<td class="plain">Objekti nimi:</td>
<td class="plain"><input type="textbox" name="name" value="{VAR:name}" size="40"></td>
</tr>
<tr>
<td class="plain">Server:</td>
<td class="plain"><input type="textbox" name="server" value="{VAR:server}" size="40"></td>
</tr>
<tr>
<td class="plain">Kasutajanimi:</td>
<td class="plain"><input type="textbox" name="uid"  size="40" value="{VAR:uid}"></td>
</tr>
<tr>
<td class="plain">Parool:</td>
<td class="plain"><input type="password" name="password"  size="40" value="{VAR:password}"></td>
</tr>
<tr>
<td class="plain" colspan="2" align="center">
<input type="submit" value="Salvesta">
{VAR:reforb}
</td>
</tr>
</table>
