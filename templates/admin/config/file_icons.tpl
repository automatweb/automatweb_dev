<script language=javascript>
var st=1;
function selall()
{
	for (i=0; i < document.forms[0].elements.length; i++)
		document.forms[0].elements[i].checked=st;
	st = !st;
	return false;
}
</script>
<form name='boo' action='refcheck.{VAR:ext}' method=post>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>FAILIDE IKOONID:&nbsp;<a href='config.{VAR:ext}?type=add_filetype'>Lisa</a></b></td>
</tr>
<tr>
<td align="center" class="title">&nbsp;Laiend&nbsp;</td>
<td align="center" class="title">&nbsp;T&uuml;&uuml;p&nbsp;</td>
<td align="center" class="title">&nbsp;Ikoon&nbsp;</td>
<td align="center" colspan="3" class="title">Tegevus</td>
<td align="center" class="title"><a href='#' onClick='selall()'>K&otilde;ik</a></td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:extt}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:type}&nbsp;</td>
<td class="fgtext">&nbsp;<img src='{VAR:url}'>&nbsp;</td>
<td class="fgtext">&nbsp;<a href='config.{VAR:ext}?type=change_filetype&extt={VAR:extt}'>Muuda</a>&nbsp;</td>
<td class="fgtext">&nbsp;<a href='config.{VAR:ext}?type=sel_icon&rtype=file_icon&rid={VAR:extt}'>Vali ikoon</a>&nbsp;</td>
<td class="fgtext">&nbsp;<a href='config.{VAR:ext}?type=delete_filetype&extt={VAR:extt}'>Kustuta</a>&nbsp;</td>
<td class="fgtext"><input type='checkbox' name='sel[]' value='{VAR:extt}'></td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext" align="center"><a href='javascript:document.boo.submit()'>Ekspordi</a></td>
</tr>
</table>
</td>
</tr>
</table>
<input type='hidden' name='action' value='export_file_icons'>
</form>
<Br><br>