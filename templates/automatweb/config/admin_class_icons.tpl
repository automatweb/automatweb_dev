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
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>KLASSIDE IKOONID:&nbsp; <a href='orb.aw?class=icons&action=save_class_icons'>ekspordi</a></b></td>
</tr>
<tr>
<td align="center" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Ikoon&nbsp;</td>
<td align="center" class="title">Tegevus</td>
<td align="center" class="title"><a href='#' onClick='selall()'>Vali</a></td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:name}&nbsp;</td>
<td class="fgtext">&nbsp;<img src='{VAR:url}'>&nbsp;</td>
<td class="fgtext">&nbsp;<a href='{VAR:change}'>Muuda</a>&nbsp;</td>
<td class="fgtext" align="center"><input type='checkbox' name='sel[]' value={VAR:id}></td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext"><a href='javascript:document.boo.submit()'>Ekspordi</a></td>
</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
<Br><br>
