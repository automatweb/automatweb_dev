<form method="POST" action="reforb.{VAR:ext}">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">{VAR:LC_GUESTBOOK_NAME}</td>
	<td class="fform"><input type="text" name="name" size="40" value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_GUESTBOOK_COMMENTARY}:</td>
</tr>
<tr>
	<td colspan=2 class="fform"><textarea name="comment" rows=5 cols=50>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
		<input type="submit" value="Save">
		{VAR:reforb}
	</td>
</tr>
</table>
</form>
