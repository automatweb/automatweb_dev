<form action='refcheck.{VAR:ext}' method=post ENCTYPE="multipart/form-data"> 
<INPUT TYPE="HIDDEN" name="MAX_FILE_SIZE" value="1000000">
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Fail:</td><td class="fform"><input type='file' NAME='pilt'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input CLASS="small_button" type='submit' VALUE='Impordi'></td>
</td>
<input type='hidden' NAME='action' VALUE='import_mails'>
<input type='hidden' NAME='id' VALUE='{VAR:list_id}'>
</form>