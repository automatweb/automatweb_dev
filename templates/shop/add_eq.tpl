<form method="POST" action="reforb.{VAR:ext}" name='q'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_NAME1}:</td>
	<td class="fcaption2" ><input type='text' name='name' value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_FORMULA}:</td>
	<td class="fcaption2" ><input type='text' size='130' name='eq' value='{VAR:eq}'></td>
</tr>
<tr>
	<td colspan=2 class="fcaption2">{VAR:LC_SHOP_WHEN_EMPTY}.</td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="{VAR:LC_SHOP_SAVE}">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
