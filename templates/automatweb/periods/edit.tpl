<form method=POST action="reforb.{VAR:ext}">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fcaption">ID</td>
	<td class="fcaption">{VAR:ID}</td>
</tr>
<tr>
	<td class="fcaption">Nimetus</td>
	<td class="fform"><input type="text" name="description" value="{VAR:description}"></td>
</tr>
<tr>
	<td class="fcaption">Arhiveeritud</td>
	<td class="fform"><select name="archived">
	{VAR:arc}
	</select>
	</td>
<tr>
	<td class="fform" colspan="2">
	<input type="submit" value="Salvesta periood">
	{VAR:reforb}
	</td>
</tr>
</table>
</form>
