<form action='refcheck.{VAR:ext}' method=post>
<table border=0 cellspacing=1 bgcolor=#cccccc cellpadding=2>
<tr>
<td class=title>Nimi</td>
<td class=title>T&uuml;&uuml;p</td>
<td class=title colspan=3 align=center>Tegevus</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class=plain>{VAR:name}</td>
<td class=plain>{VAR:type}</td>
<td class=plain><a href='{VAR:change}'>Muuda</a>&nbsp;</td>
<td class=plain><a href="javascript:box2('Kas oled kindel, et tahad stiili kustutada?','{VAR:delete}')">Kustuta</a>&nbsp;</td>
<td class=plain align=center><input class='chkbox' type='checkbox' NAME='style_{VAR:style_id}' VALUE='1'>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td class=plain colspan=4 align=center><a href='{VAR:add}'>Lisa</a>&nbsp;</td>
<td class=plain align=center><input class='small_button' type='submit' NAME='s' VALUE='Ekspordi'>&nbsp;</td>
</tr>
</table>
<br>
<input type='hidden' NAME='action' VALUE='export_styles'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
</form>

<form action='refcheck.{VAR:ext}' method=post enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE=1000000>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr><td class="title" colspan=2>Impordi stiile:</td></tr>
<tr><td class="plain">Fail:</td><td class="plain"><input class='small_button' type=file NAME=file></td></tr>
<tr><td class="plain" colspan=2 align=right><input class='small_button' type=submit name=upload value=Impordi></td></tr>
</table>
<input type='hidden' NAME='action' VALUE='import_styles'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
</form>
