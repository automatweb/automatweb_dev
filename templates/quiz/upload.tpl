<form action=reforb.{VAR:ext} method=post enctype='multipart/form-data'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>TESTID:&nbsp;Uploadi faile | <a href='{VAR:list}'>Nimekiri failidest</a></b></td>
</tr>
</table></td></tr></table>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='1000000'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr><td class="title" colspan=2>Uploadi kokku zipitud fail </td></tr>
<tr><td class="plain">Vali fail:</td><td class="plain"><input class='small_button' type='file' name='fail'></td></tr>
<tr><td class="plain">V&otilde;i kirjuta kataloog, kus asuvad failid serveris:</td><td class="plain"><input class='small_button' type='text' size=70 name='dir'></td></tr>
<tr><td class="plain" align=right colspan=2><input class='small_button' type='submit' VALUE='Impordi'></td></tr></table>
{VAR:reforb}
</form>
