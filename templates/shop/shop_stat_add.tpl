<form method="POST" action="reforb.{VAR:ext}" name='b88'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Name:</td>
	<td class="fform"><input type="text" name="name" size="40" value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Comment:</td>
	<td class="fform"><textarea name="comment" rows=5 cols=50>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Choose targets:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select multiple name='shops[]'>{VAR:shop_list}</select></td>
</tr>
<!-- SUB: CHANGE -->
<tr>
	<td class="fcaption2" colspan=2><a href='{VAR:stat_by_turnover}'>Turnover statistics</a></td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Save">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
