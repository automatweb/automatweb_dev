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
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return doSubmit('change')">Muuda</a>
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return doSubmit('delete')">Kustuta</a>
 | <a href='#' onClick='window.location.reload()' class="fgtitle_link">V&auml;rskenda</a></b>
</b>
</td>
<td align=right class="fgtitle_new"><a href='orb.aw?action=list&class=bugtrack&filt=all' class='fgtitle_link'>BugTrack</a>&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" class="title">&nbsp;</td>
<td height="15" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=name&order={VAR:order1}&period={VAR:period}'>Nimi</a>{VAR:sortedimg1}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=jrk&order={VAR:order2}&period={VAR:period}'>Jrk</a>{VAR:sortedimg2}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=status&order={VAR:order6}&period={VAR:period}'>Aktiivne</a>{VAR:sortedimg6}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=modifiedby&order={VAR:order3}&period={VAR:period}'>Muutja</a>{VAR:sortedimg3}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=modified&order={VAR:order4}&period={VAR:period}'>Muudetud</a>{VAR:sortedimg4}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=class_id&order={VAR:order5}&period={VAR:period}'>T&uuml;&uuml;p</a>{VAR:sortedimg5}&nbsp;</td>
<td align="center" class="title">&nbsp;Lead?&nbsp;</td>
<td align="center" class="title">&nbsp;Foorum?&nbsp;</td>
<td align="center" class="title">&nbsp;Esilehel&nbsp;</td>
<td align="center" class="title">&nbsp;Paremal&nbsp;</td>
<td align="center" class="title">&nbsp;Text OK?&nbsp;</td>
<td align="center" class="title">&nbsp;Pildid OK?&nbsp;</td>
<td align="center" class="title">&nbsp;Link&nbsp;</td>
<td align="center" colspan="2" class="title">&nbsp;Vali&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td height="15" class="fgtext">&nbsp;<img src="{VAR:icon}">&nbsp;</td>
<td height="15" class="fgtext">&nbsp;<a href="{VAR:change}">{VAR:name}</a>&nbsp;</td>
<td class="fgtext" align=center>&nbsp;
<!-- SUB: NFIRST -->
<input class='small_button' type=text NAME='jrk[{VAR:oid}]' VALUE='{VAR:order}' SIZE=2 MAXLENGTH=3><input type='hidden' name='old_ord[{VAR:oid}]' value='{VAR:order}'>
<!-- END SUB: NFIRST -->
&nbsp;</td>
<td align="center" class="fgtext">&nbsp;
<!-- SUB: CAN_ACTIVE -->
<input type='checkbox' NAME='act[{VAR:oid}]' {VAR:active} value="1"><input type='hidden' NAME='old_act[{VAR:oid}]' VALUE='{VAR:active2}'>
<!-- END SUB: CAN_ACTIVE -->
&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:type}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;<input type="checkbox" name="showlead[{VAR:oid}]" value="1" {VAR:showlead}>&nbsp;</td>
<td align="center" class="fgtext">&nbsp;<input type="checkbox" name="is_forum[{VAR:oid}]" value="1" {VAR:is_forum}>&nbsp;</td>
<td align="center" class="fgtext">&nbsp;<input type="checkbox" name="esilehel[{VAR:oid}]" value="1" {VAR:esilehel}>&nbsp;<input type='text' name='jrk1[{VAR:oid}]' size=2 class="small_button" maxlength=2 value='{VAR:jrk1}'>&nbsp;</td>
<td align="center" class="fgtext">&nbsp;<input type="checkbox" name="esilehel_uudis[{VAR:oid}]" value="1" {VAR:esilehel_uudis}>&nbsp;<input type='text' name='jrk2[{VAR:oid}]' size=2 class="small_button" maxlength=2 value='{VAR:jrk2}'>&nbsp;</td>
<td align="center" class="fgtext">&nbsp;<input type="checkbox" name="text_ok[{VAR:oid}]" value="1" {VAR:text_ok}>&nbsp;</td>
<td align="center" class="fgtext">&nbsp;<input type="checkbox" name="pic_ok[{VAR:oid}]" value="1" {VAR:pic_ok}>&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;<a target='_blank' href='{VAR:link}'>Link</a>&nbsp;</td>

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
