<form action='reforb.{VAR:ext}' method=post>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
		<tr>
			<td class="fcaption">{VAR:LC_TABLE_NAME}:</td><td class="fform"><input type='text' NAME='name'></td>
		</tr>
	<tr>
		<td class="fcaption">{VAR:LC_TABLE_COMM}:</td>
		<td class="fform"><textarea name=comment cols=50 rows=5></textarea></td>
	</tr>
		<tr>
			<td class="fcaption" colspan=2><input type='submit' class='small_button' VALUE='Save'></td>
		</tr>
	</table>
	{VAR:reforb}
</form>
