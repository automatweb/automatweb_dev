<form enctype="multipart/form-data" method=POST action='refcheck.{VAR:ext}'>
<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fcaption">Vali fail</td>
	<td class="fform"><input type="file" size="40" name="file"></td>
</tr>
<tr>
	<td class="fcaption">Faili allkiri</td>
	<td class="fform"><input type="text" size="40" name="comment"></td>
</tr>
<tr>
	<td class="fcaption">Kas kuvatakse kohe?</td>
	<td class="fform"><input type="checkbox" name="show" value=1></td>
</tr>
<tr>
	<td class="fcaption">Uues aknas?</td>
	<td class="fform"><input type="checkbox" name="newwindow" value=1 {VAR:newwindow}></td>
</tr>
<tr>
	<td class="fform" colspan="2" align="center">
	{VAR:reforb}
	<input type="submit" value="Lisa">
	</td>
</tr>
</table>
</form>
