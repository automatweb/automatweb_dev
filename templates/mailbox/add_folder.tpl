<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b><a href='mail.{VAR:ext}?type=folders&parent={VAR:parent}'>KATALOOGID</a></b></td>
</tr>
</table>
<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_mail_folder'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
</form>
