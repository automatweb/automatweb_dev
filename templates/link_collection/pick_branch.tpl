<form method="POST">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">
		{VAR:LC_LINKCOLLECTION_NAME}
	</td>
	<td class="fform">
		<input type="text" name="name" size="40">
	</td>
</tr>
<tr>
	<td class="fcaption2">
		{VAR:LC_LINKCOLLECTION_COMMENT}
	</td>
	<td class="fform">
		<input type="text" name="comment" size="40">
	</td>
</tr>
<tr>
	<td class="fcaption2">
		{VAR:LC_LINKCOLLECTION_CHOOSE_BRANCH}
	</td>
	<td class="fform">
		<select size="30" name="branch">
		{VAR:branches}
		</select>
	</td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
		<input type="submit" value="{VAR:LC_LINKCOLLECTION_ADD_ALIAS}">
		{VAR:reforb}
	</td>
</tr>
</table>
</form>
