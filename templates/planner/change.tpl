{VAR:menubar}
<form method="POST" action="reforb.{VAR:ext}">
<table border="0" cellspacing="0" cellpadding="1">
<tr>
<td bgcolor=#000000>
<table border="0" cellspacing="0" cellpadding="3">
<tr>
<td class="fgtitle">ID</td>
<td class="fgtitle"><b>{VAR:oid}</b></td>
</tr>
<tr>
<td class="fgtitle">{VAR:LC_PLANNER_NAME}</td>
<td class="fgtitle"><input type="text" name="name" size="30" value="{VAR:name}"></td>
</tr>
<tr>
<td class="fgtitle">Konfi objekt</td>
<td class="fgtitle"><select name="confobject">{VAR:confobjects}</select></td>
</tr>
<tr>
<td class="fgtitle" colspan="2" align="center">
<input type="submit" value="Salvesta">
</td>
</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
