<script language=javascript>
var st=1;
function selall()
{
	for (i=0; i < document.forms[0].elements.length; i++)
		document.forms[0].elements[i].checked=st;
	st = !st;
	return false;
}

function del_ic()
{
	document.boo.action.value='del_icons';document.boo.submit();
}

function grp_ic()
{
	document.boo.action.value='grp_icons';
	document.boo.submit();
}
function sel_grp()
{
	window.location.href = "{VAR:baseurl}/automatweb/orb.{VAR:ext}?class=icons&action=icon_db&grp="+document.boo.grp.options[document.boo.grp.selectedIndex].value;
}
function del_grp()
{
	document.boo.action.value='del_grp';
	document.boo.submit();
}
</script>
<form name='boo' action='reforb.{VAR:ext}' method=post>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="5" class="fgtitle">&nbsp;<b>IKOONID:&nbsp;<a href='{VAR:add_icon}'>Lisa</a> | <a href='{VAR:add_zip}'>Uploadi zip</a>
<br>&nbsp;Lehek&uuml;lg: 
<!-- SUB: PAGE -->
<a href='{VAR:pg_url}'>{VAR:from} - {VAR:to}</a> | 
<!-- END SUB: PAGE -->

<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to} |
<!-- END SUB: SEL_PAGE -->
<!-- SUB: ALL -->
<a href='{VAR:all_url}'>K&otilde;ik</a>
<!-- END SUB: ALL -->
<!-- SUB: ALL_SEL -->
K&otilde;ik
<!-- END SUB: ALL_SEL -->
</b></td>
<td height="15" colspan="2" class="fgtitle">
<select name='grp'>{VAR:grps}</select><input type='submit' onClick='sel_grp();return false;' value='Vali grupp'><br>
<a href='javascript:grp_ic()'>Grupeeri</a>
</td>
</tr>
<tr>
<td align="center" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Kommentaar&nbsp;</td>
<td align="center" class="title">&nbsp;Programm&nbsp;</td>
<td align="center" class="title">&nbsp;Ikoon&nbsp;</td>
<td align="center" colspan="2" class="title">Tegevus</td>
<td align="center" colspan="1" class="title"><a href='#' onClick='selall()'>K&otilde;ik</a></td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:name}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:comment}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:programm}&nbsp;</td>
<td class="fgtext">&nbsp;<img src='{VAR:url}'>&nbsp;</td>
<td class="fgtext">&nbsp;<a href='{VAR:change}'>Muuda</a>&nbsp;</td>
<td class="fgtext">&nbsp;<a href='{VAR:delete}'>Kustuta</a>&nbsp;</td>
<td class="fgtext"><input type='checkbox' name='sel[]' value={VAR:id}></td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td class="fgtext"><a href='javascript:del_grp()'>Kustuta valitud grupp</a></td>
<td class="fgtext"><a href='javascript:del_ic()'>Kustuta valitud ikoonid</a></td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext"><a href='javascript:document.boo.submit()'>Ekspordi</a><br><a href='javascript:grp_ic()'>Grupeeri</a></td>
</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
<Br><br>