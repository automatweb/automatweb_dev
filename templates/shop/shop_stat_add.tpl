<form method="POST" action="reforb.{VAR:ext}" name='b88'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">{VAR:LC_SHOP_NAME}:</td>
	<td class="fform"><input type="text" name="name" size="40" value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">{VAR:LC_SHOP_COMM}:</td>
	<td class="fform"><textarea name="comment" rows=5 cols=50>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_CHOOSE_TARGETS}:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select multiple name='shops[]'>{VAR:shop_list}</select></td>
</tr>
<!-- SUB: CHANGE -->
<tr>
	<td class="fcaption2" colspan=2><a href='{VAR:stat_by_turnover}'>{VAR:LC_SHOP_TO_STAT}</a></td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="{VAR:LC_SHOP_SAVE}">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
