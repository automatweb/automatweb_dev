<form action="reforb.{VAR:ext}" method="post" name="q">
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0">
	<tr>
		<td height="15" colspan="11" class="fgtitle">&nbsp;<b>Gruppide login menüüd:&nbsp;<a href='javascript:document.q.submit()'>Salvesta</a></b></td>
	</tr>
	<tr>
		<td align="center" class="title">&nbsp;GID&nbsp;</td>
		<td align=center class="title">&nbsp;Grupp&nbsp;</td>
		<td align=center class="title">&nbsp;Menüü&nbsp;</td>
	</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext" align=center>&nbsp;{VAR:gid}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:group}&nbsp;</td>
<td class="fgtext">&nbsp;
<select name="menu[{VAR:gid}]">
{VAR:menus}
</select>
&nbsp;</td>
<!-- END SUB: LINE -->
</table>
</td></tr></table>
{VAR:reforb}
</form>
