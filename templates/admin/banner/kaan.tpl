<form enctype="multipart/form-data" method=POST action='banner.{VAR:ext}'>
<input type="hidden" name="MAX_FILE_SIZE" value="100000">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fcaption">Praegune Kaanepilt</td>
	<td class="fcaption"><img src="{VAR:imgref}"></td>
</tr>
<tr>
	<td class="fcaption">Vali uus pilt</td>
	<td class="fform"><input type="file" size="40" name="pilt"></td>
</tr>
<tr>
	<td class="fform" colspan="2" align="center">
	<input type="hidden" name="op" value="upload_kaas">
	<input type="hidden" name="type" value="{VAR:type}">
	<input type="hidden" name="period" value="{VAR:period}">
	<input type="submit" value="Salvesta uus pilt">
	</td>
</tr>
</table>
</form>
