<form method=POST action='reforb.{VAR:ext}'>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fcaption">{VAR:LC_CURRENCY_NAME}:</td>
	<td class="fform"><input type="text" name="name" value="{VAR:name}"></td>
</tr>
<tr>
	<td class="fcaption">{VAR:LC_CURRENCY_DEM_RATE}:</td>
	<td class="fform"><input type="text" size="10" name="ratio" value='{VAR:ratio}'></td>
</tr>
<tr>
	<td class="fform" colspan="2" align="center">
	{VAR:reforb}
	<input type="submit" value="Salvesta">
	</td>
</tr>
</table>
</form>
