<form action='refcheck.{VAR:ext}' method=post enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='2000000'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Fail:</td><td class="fform"><input type='file' NAME='fail'></td>
</tr>
<tr>
<td colspan=2 class="fcaption">Fail peab olema pakitud .zip formaati, faili sees asuvad .ico failid konverditakse automaatselt .gif failideks. Baasis juba olemas olevaid ikoone ei impordita (v&ouml;rreldakse pilte).</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_icon_zip'>
</form>
