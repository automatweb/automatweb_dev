<form action=reforb.{VAR:ext} method=post enctype='multipart/form-data'>
<table border="0" cellspacing="0" cellpadding="0"  width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2"  width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>EKOMAR:&nbsp;<a href='{VAR:list_files}'>Failide nimekiri</a> | <a href='{VAR:addfile}'>Lisa fail</a> | Uploadi firmade nimekiri</b></td>
</tr>
</table><td></tr></table>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr><td class="title" colspan=2>Impordi firmade nimekiri</td></tr>
<tr><td class="plain">Vali fail:</td><td class="plain"><input type='hidden' NAME='MAX_FILE_SIZE' VALUE='10000000'><input class='small_button' type='file' name='fail'></td></tr>
<tr><td class="plain" align=right colspan=2><input class='small_button' type='submit' VALUE='Uploadi'></td></tr></table>
{VAR:reforb}
</form>
