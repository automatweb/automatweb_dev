<form enctype="multipart/form-data" method=POST action='reforb.{VAR:ext}'>
<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td colspan="2" class="fcaption">{VAR:img}</td>
</tr>
<tr>
	<td class="fcaption">Pilt:</td>
	<td class="fform"><input type="file" size="40" name="file" ></td>
</tr>
<tr>
	<td class="fcaption">Nimi:</td>
	<td class="fform"><input type="text" size="40" name="name" value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption">Link:</td>
	<td class="fform"><input type="text" size="40" name="link" value='{VAR:link}'></td>
</tr>
<tr>
	<td class="fcaption">Uues aknas?</td>
	<td class="fform"><input type="checkbox" name="newwindow" value="1" {VAR:newwindow}></td>
</tr>
<tr>
	<td class="fform" colspan="2" align="center">
	{VAR:reforb}
	<input type="submit" value="Salvesta">
	</td>
</tr>
</table>
</form>
