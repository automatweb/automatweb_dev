{VAR:menu}
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
