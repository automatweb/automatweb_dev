<form enctype="multipart/form-data" method=POST action='reforb.{VAR:ext}'>
<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fcaption">{VAR:LC_GRAPH_CHOOSE_FILE}</td>
	<td class="fform"><input type="file" size="40" name="file"></td>
</tr>
<tr>
	<td class="fform" colspan="2" align="center">
	{VAR:upload}
	<input type="submit" value="Impordi">
	</td>
</tr>
</table>
</form>
