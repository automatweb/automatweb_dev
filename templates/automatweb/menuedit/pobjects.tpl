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

	function cut()
	{
		document.foo.action.value="cut";
		document.foo.subaction.value="";
		document.foo.submit();
		return true;
	}

	function copy()
	{
		document.foo.action.value="copy";
		document.foo.subaction.value="";
		document.foo.submit();
		return true;
	}

	function paste()
	{
		document.foo.action.value="paste";
		document.foo.subaction.value="";
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
<td height="15" colspan="9" class="fgtitle_new">&nbsp;<b>{VAR:LC_MENUEDIT_OBJECTS}: 
<!-- SUB: ADD_CAT -->
<select class="fgtitle_button" name="type">{VAR:types}</select>&nbsp;<a class="fgtitle_link" href='javascript:doSubmit2()'>{VAR:LC_MENUEDIT_ADD}</a>
<!-- END SUB: ADD_CAT -->
| <a href='javascript:document.foo.submit()' class="fgtitle_link">{VAR:LC_MENUEDIT_SAVE}</a>
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return doSubmit('change')">{VAR:LC_MENUEDIT_CHANGE}</a>
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return doSubmit('delete')">{VAR:LC_MENUEDIT_DELETE}</a>
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return cut()">Cut</a>
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return copy()">Copy</a>
<!-- SUB: PASTE -->
| <a href="javascript:document.foo.submit()"  class="fgtitle_link" onClick="return paste()">Paste</a>
<!-- END SUB: PASTE -->
 | <a href='#' onClick='window.location.reload()' class="fgtitle_link">{VAR:LC_MENUEDIT_REFRESH}</a></b>
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
<td height="15" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=name&order={VAR:order1}&period={VAR:period}'>{VAR:LC_MENUEDIT_NAME}</a>{VAR:sortedimg1}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=jrk&order={VAR:order2}&period={VAR:period}'>{VAR:LC_MENUEDIT_ORDER}</a>{VAR:sortedimg2}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=status&order={VAR:order6}&period={VAR:period}'>{VAR:LC_MENUEDIT_ACTIVE}</a>{VAR:sortedimg6}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=modifiedby&order={VAR:order3}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED_BY}</a>{VAR:sortedimg3}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=modified&order={VAR:order4}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED}</a>{VAR:sortedimg4}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=objects&sortby=class_id&order={VAR:order5}&period={VAR:period}'>{VAR:LC_MENUEDIT_TYPE}</a>{VAR:sortedimg5}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MENUEDIT_LEAD}?&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MENUEDIT_FORUM}?&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MENUEDIT_FRONTPAGE}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MENUEDIT_RIGHT}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MENUEDIT_TEXT} OK?&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MENUEDIT_PICTURES} OK?&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MENUEDIT_LINK}&nbsp;</td>
<td align="center" colspan="2" class="title">&nbsp;{VAR:LC_MENUEDIT_CHOOSE}&nbsp;</td>
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
<td class="fgtext2">&nbsp;<input type="checkbox" NAME="sel[{VAR:oid}]" VALUE=1>&nbsp;</td>
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
