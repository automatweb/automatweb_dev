{VAR:menu}

<br>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<form method="POST"  action='reforb.{VAR:ext}'>
<tr>
<td>
	<table border="0" cellspacing="1" cellpadding="2" width="100%">
	<tr>
	<td class="textsmallbold" align="center" bgcolor="#C3D0DC" align="center"> X </td>
	<td class="textsmallbold" align="center" bgcolor="#C3D0DC">Nimetus</td>
	<td class="textsmallbold" align="center" bgcolor="#C3D0DC">Kokku</td>
	<td class="textsmallbold" align="center" bgcolor="#C3D0DC">Lugemata</td>
	</tr>
	<!-- SUB: line -->
	<tr>
		<td class="text" align="center"><input type="radio" name="msg_defaultfolder" value="{VAR:id}" {VAR:checked}></td>
		<td class="text"><a href="{VAR:go}">{VAR:name}</a></td>
		<td class="text" align="center">&nbsp;{VAR:total}</td>
		<td class="text" align="center">&nbsp;<font color="red">{VAR:unread}</font></td>
	</tr>
	<!-- END SUB: line -->
	<tr>
		<td colspan="4" class="text" align="left">
			<input class="text" type="submit" value="Set default">
			{VAR:reforb}
		</td>
	</tr>
	</table>
</td>
</tr>
</form>
</table>
