<form enctype="multipart/form-data" method=POST action='images.{VAR:ext}'>
<input type="hidden" name="MAX_FILE_SIZE" value="100000">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td colspan="2"><img src="{VAR:url}"></td>
</tr>
<tr>
	<td class="fcaption">Vali uus pilt</td>
	<td class="fform"><input type="file" size="40" name="pilt"></td>
</tr>
<tr>
	<td class="fcaption">Pildiallkiri</td>
	<td class="fform"><input type="text" size="40" name="comment" value="{VAR:comment}"></td>
</tr>
<tr>
	<td class="fcaption">Link</td>
	<td class="fform"><input type="text" size="40" name="link" value="{VAR:link}"></td>
</tr>
<tr>
	<td class="fform" colspan="2" align="center">
	{VAR:reforb}
	<input type="submit" value="Salvesta">
	</td>
</tr>
</table>
<input type='hidden' name='parent' VALUE='{VAR:parent}'>
<input type='hidden' name='poid' VALUE='{VAR:poid}'>
<input type='hidden' name='idx' VALUE='{VAR:idx}'>
<input type='hidden' NAME='action' VALUE='replace'>
</form>
