<table hspace=0 vspace=0 cellpadding=3  bgcolor=#a0a0a0>
	<tr>
		<td bgcolor=#f0f0f0><a href='{VAR:change}'>Toimeta</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:styles}'>Stiilid</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:view}'>Eelvaade</a></td>
		<td bgcolor=#a0a0a0><a href='{VAR:import}'>Impordi</a></td>
	</tr>
</table>
<br>
<form action = 'reforb.{VAR:ext}' method=post enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='1000000'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Tabel: {VAR:name}</td>
</tr>
<tr>
<td class="fcaption">Uploaditav fail peab olema salvestatud Comma-Separated-Values (.csv) t&uuml;&uuml;pi faili.</td>
</tr>
<tr>
<td class="fform"><input type='file' NAME='fail'></td>
</tr>
<tr>
<td class="fcaption">Kas eemaldame t&uuml;hjad read l&otilde;pust? <input type='checkbox' name='trim' value="1" checked></td>
</tr>
<tr>
<td class="fcaption">Mis m&auml;rgiga on tulbad eraldatud? <input type='text' name='separator' value=";" size=1></td>
</tr>
<tr>
<td class="fform"><input type='submit' VALUE='Impordi'></td>
</tr>
</table>
{VAR:reforb}
</form>
