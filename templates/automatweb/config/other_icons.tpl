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
<form name='boo' action='reforb.{VAR:ext}' method=post>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>MUUD IKOONID:</b></td>
</tr>
<tr>
<td align="center" class="title">&nbsp;T&uuml;&uuml;p&nbsp;</td>
<td align="center" class="title">&nbsp;Ikoon&nbsp;</td>
<td align="center" colspan="1" class="title">Tegevus</td>
<td align="center" class="title"><a href='#' onClick='selall()'>K&otilde;ik</a></td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:type}&nbsp;</td>
<td class="fgtext">&nbsp;<img src='{VAR:url}'>&nbsp;</td>
<td class="fgtext">&nbsp;<a href='{VAR:select}'>Vali ikoon</a>&nbsp;</td>
<td class="fgtext"><input type='checkbox' name='sel[]' value='{VAR:mtype}'></td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext" align="center"><a href='javascript:document.boo.submit()'>Ekspordi</a></td>
</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
<Br><br>