<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">
<script language="javascript">
	function doSubmit(act)
	{
		document.foo.action.value="menuedit_redirect";
		document.foo.subaction.value=act;
		document.foo.submit();
		return true;
	}
	function doSubmit2()
	{
		document.foo.action.value="menuedit_newobj";
		document.foo.submit();
		return true;
	}
	function doClick()
	{
		url = "forms.{VAR:ext}?type=change_entry&id={VAR:form_id}&entry_id="+document.foo.oid.value;
		window.location.href=url;
	}
</script>
</head>
<body>
<form action='reforb.{VAR:ext}' METHOD=POST NAME='foo'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="0" cellpadding="1" width=100%>
<tr>
<td bgcolor=#000000>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="9" class="fgtitle_new">&nbsp;<b>OBJEKTID: 
<!-- SUB: ADD_CAT -->
<select class="fgtitle_button" name="type">{VAR:types}</select>&nbsp;<a class="fgtitle_link" href='javascript:doSubmit2()'>Lisa</a>
<!-- END SUB: ADD_CAT -->
| <a href='javascript:document.foo.submit()' class="fgtitle_link">Salvesta</a>
| <a href="javascript:doClick()"  class="fgtitle_link" onClick="return doClick('change')">Muuda</a>
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return doSubmit('delete')">Kustuta</a>
 | <a href='#' onClick='window.location.reload()' class="fgtitle_link">V&auml;rskenda</a></b>
</b>
</td>
<td align=right class="fgtitle_new"><a href='bug.{VAR:ext}?op=listall' class='fgtitle_link'>BugTrack</a>&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" class="title">&nbsp;</td>
<td height="15" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=name&order={VAR:order1}'>Nimi</a>{VAR:sortedimg1}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=jrk&order={VAR:order2}'>Jrk</a>{VAR:sortedimg2}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=modifiedby&order={VAR:order3}'>Muutja</a>{VAR:sortedimg3}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=modified&order={VAR:order4}'>Muudetud</a>{VAR:sortedimg4}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=class_id&order={VAR:order5}'>T&uuml;&uuml;p</a>{VAR:sortedimg5}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=status&order={VAR:order6}'>Aktiivne</a>{VAR:sortedimg6}&nbsp;</td>
<td align="center" colspan="2" class="title">&nbsp;Vali&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td height="15" class="fgtext">&nbsp;<img src="{VAR:icon}">&nbsp;</td>
<td height="15" class="fgtext">&nbsp;<a href="{VAR:change}">{VAR:name}</a>&nbsp;</td>
<td class="fgtext" align=center>&nbsp;
<!-- SUB: NFIRST -->
<input class='small_button' type=text NAME='ord[{VAR:oid}]' VALUE='{VAR:order}' SIZE=2 MAXLENGTH=3><input type='hidden' name='old_ord[{VAR:oid}]' value='{VAR:order}'>
<!-- END SUB: NFIRST -->
&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:type}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;
<!-- SUB: CAN_ACTIVE -->
<input type='checkbox' NAME='act[{VAR:oid}]' {VAR:active}><input type='hidden' NAME='old_act[{VAR:oid}]' VALUE='{VAR:active2}'>
<!-- END SUB: CAN_ACTIVE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;<input type="radio" name="oid" value="{VAR:oid}">&nbsp;</td>
<td class="fgtext2">&nbsp;<input type="checkbox" NAME="sel_{VAR:oid}" VALUE=1>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
</table>
<input type="hidden" name="period" value="{VAR:period}">
<input type='hidden' NAME='subaction' VALUE=''>
{VAR:reforb}
</form>
</body>
</html>
