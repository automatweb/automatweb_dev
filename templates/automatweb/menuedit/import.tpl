<form method="POST" action="reforb.{VAR:ext}" enctype='multipart/form-data'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_MENUEDIT_IMPORT_MENUS}</td>
</tr>
<tr>
	<td class="fcaption2">{VAR:LC_MENUEDIT_CHOOSE_FILE}:</td>
	<td class="fcaption2"><input type='hidden' name='MAX_FILE_SIZE' VALUE='1000000'><input type='file' name='fail'></td>
</tr>
<tr>
	<td colspan="2" class="fcaption2">Vali faili t&uuml;&uuml;p:</td>
</tr>
<tr>
	<td class="fcaption2">Eksporditud AW'st:</td>
	<td class="fcaption2"><input type="radio" name="file_type" value="aw" checked></td>
</tr>
<tr>
	<td class="fcaption2">Tekstifail:</td>
	<td class="fcaption2"><input type="radio" name="file_type" value="text" ></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
		<input type="submit" value="{VAR:LC_MENUEDIT_IMPORT}">
		{VAR:reforb}
	</td>
</tr>
</table>
</form>
