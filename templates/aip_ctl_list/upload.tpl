<form enctype="multipart/form-data" method=POST action='reforb.{VAR:ext}'>
<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
<table border=0 cellspacing=1 cellpadding=2>
<tr>
	<td class="celltext">Vali fail:</td>
	<td class="celltext"><input type="file" class="formfile" size="40" name="file"></td>
</tr>
<tr>
	<td class="celltext" colspan="2" align="center">
	{VAR:reforb}
	<input type="submit" value="Salvesta">
	</td>
</tr>
</table>
</form>
