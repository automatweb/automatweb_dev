<form method="POST" action="reforb.{VAR:ext}" enctype='multipart/form-data'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2" colspan=2>Impordi men&uuml;&uuml;sid</td>
</tr>
<tr>
	<td class="fcaption2">Vali fail:</td>
	<td class="fcaption2"><input type='hidden' name='MAX_FILE_SIZE' VALUE='1000000'><input type='file' name='fail'></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2">
		<input type="submit" value="Impordi">
		{VAR:reforb}
	</td>
</tr>
</table>
</form>
