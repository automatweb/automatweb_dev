<table border="0" cellspacing="1" bgcolor="#CCCCCC">
<form method="POST" action="reforb.{VAR:ext}">
<tr>
<td class="plain">Nimi:</td>
<td class="plain"><input type="text" name="name" value="{VAR:name}" size="40"></td>
</tr>
<tr>
<td class="plain">Login objekt:</td>
<td class="plain"><select name="aw_login">{VAR:aw_login}</select></td>
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
