<table hspace=0 vspace=0 cellpadding=3  bgcolor=#a0a0a0>
	<tr>
		<td bgcolor=#f0f0f0><a href='{VAR:change}'>{VAR:LC_TABLE_EDIT}</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:styles}'>{VAR:LC_TABLE_STYLES}</a></td>
		<td bgcolor=#f0f0f0><a href='{VAR:view}'>{VAR:LC_TABLE_PREVIEW}</a></td>
		<td bgcolor=#a0a0a0><a href='{VAR:import}'>{VAR:LC_TABLE_IMPORT}</a></td>
	</tr>
</table>
<br>
<form action = 'reforb.{VAR:ext}' method=post enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='1000000'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_TABLE_TABLE}: {VAR:name}</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_TABLE_UPLOAD_FILE_CVS}.</td>
</tr>
<tr>
<td class="fform"><input type='file' NAME='fail'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_TABLE_REMOVE_EMPTY_ROWS}? <input type='checkbox' name='trim' value="1" checked></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_TABLE_WHAT_SIGN_COL}? <input type='text' name='separator' value=";" size=1></td>
</tr>
<tr>
<td class="fform"><input type='submit' VALUE='{VAR:LC_TABLE_IMPORT}'></td>
</tr>
</table>
{VAR:reforb}
</form>
