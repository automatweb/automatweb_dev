<table border="0" cellspacing="0" cellpadding="0" bgcolor="#CCCCCC">
<tr>
<td>
	<form action="reforb.{VAR:ext}" method="POST">
	<table border="1" cellspacing="2" cellpadding="2" bgcolor="#FFFFFF">
	<tr>
		<td class="line" align="center"><strong>IP</strong></center></td>
		<td class="line" align="center"><strong>Aktiivne</strong></center></td>
	</tr>
	<!-- SUB: line -->
	<tr>
		<td class="line">{VAR:ip}</td>
		<td class="line" align="center"><input type="checkbox" name="check[{VAR:id}]" value="1" {VAR:checked}></td>
	</tr>
	<!-- END SUB: line -->
	<tr>
		<td class="line" align="center">
		<input type="text" name="new" size="20"><input type="submit" value="Lisa/Salvesta">
		{VAR:reforb}
		</td>
	</tr>
	</table>
	</form>
</td>
</tr>
</table>
