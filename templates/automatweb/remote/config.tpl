<table border="0" cellspacing="1" bgcolor="#CCCCCC">
<form method="POST" action="reforb.{VAR:ext}">
<tr>
<td class="plain">Server:</td>
<td class="plain"><input type="textbox" name="server" value="{VAR:server}" size="40"></td>
</tr>
<tr>
<td class="plain">Kasutajanimi:</td>
<td class="plain"><input type="textbox" name="uid" value="" size="40"></td>
</tr>
<tr>
<td class="plain">Parool:</td>
<td class="plain"><input type="password" name="password" value="" size="40"></td>
</tr>
<!-- SUB: QUERY -->
<tr>
<td class="plain">Päring:</td>
<td class="plain"><input type="text" name="query[{VAR:id}]" value="{VAR:query}" size="80"></td>
</tr>
<!-- END SUB: QUERY -->
<tr>
<td class="plain" colspan="2" align="center">
<input type="submit" value="Salvesta">
<input type="submit" value="Saada päring" name="do_query">
{VAR:reforb}
</td>
</tr>
</table>
