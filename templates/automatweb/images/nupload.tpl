<form enctype="multipart/form-data" method=POST action='reforb.{VAR:ext}'>
<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fcaption">{VAR:LC_DOCUMENT_PICT_CHOOSE}</td>
	<td class="fform"><input type="file" size="40" name="pilt"></td>
</tr>
<tr>
	<td class="fcaption">{VAR:LC_DOCUMENT_PICT_SIGN}</td>
	<td class="fform"><input type="text" size="40" name="comment"></td>
</tr>
<tr>
	<td class="fcaption">{VAR:LC_DOCUMENT_PICT_LINK}</td>
	<td class="fform"><input type="text" size="40" name="link"></td>
</tr>
<tr>
	<td class="fcaption">{VAR:LC_DOCUMENT_NEW_WINDOW}?</td>
	<td class="fform"><input type="checkbox" name="newwindow" value="1"></td>
</tr>
<tr>
	<td class="fcaption">{VAR:LC_DOCUMENT_PICT_PER}?</td>
	<td class="fform"><input type="checkbox" name="set_period" value=1></td>
</tr>
<tr>
	<td class="fform" colspan="2" align="center">
	{VAR:reforb}
	<input type="submit" value="{VAR:LC_DOCUMENT_PICT_ADD}">
	</td>
</tr>
</table>
</form>
