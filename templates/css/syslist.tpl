{VAR:menu}
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<form name="syslist" method="POST" action="reforb.{VAR:ext}">
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td colspan="8" class="title">&nbsp;<b>{VAR:LC_CSS_SYSTEM_STYLEGROUPS}</b>
|
<a href="{VAR:link_addgroup}">{VAR:LC_CSS_ADD_NEW}</a>
|
<a href="javascript:document.syslist.submit()"><font color="red">{VAR:LC_CSS_SAVE}</font></a>
</td>
</tr>
<tr>
<td align="center" class="title">&nbsp;#&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_CSS_GROUP_NAME}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_CSS_COMMENT}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_CSS_AUTHOR}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_CSS_ACTIVE}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_CSS_IN_USE}&nbsp;</td>
<td align="center" class="title" colspan="2">&nbsp;{VAR:LC_CSS_ACTION}&nbsp;</td>
</tr>
<!-- SUB: line -->
<tr>
<td class="fgtext">{VAR:cnt}</td>
<td class="fgtext">{VAR:name}</td>
<td class="fgtext">&nbsp;{VAR:comment}</td>
<td class="fgtext">&nbsp;{VAR:modifiedby}</td>
<td class="fgtext" align="center">
	<input type="checkbox" name="active[]" value="{VAR:oid}" {VAR:active}>
</td>
<td class="fgtext" align="center">
	<input type="radio" name="use" value="{VAR:oid}" {VAR:use}>
</td>
<td class="fgtext" align="center">
	<a href="{VAR:link_edgroup}">{VAR:LC_CSS_CHANGE}</a>
</td>
<td class="fgtext" align="center">
	<a href="{VAR:link_prevgroup}">{VAR:LC_CSS_PREVIEW}</a>
</td>
</tr>
<!-- END SUB: line -->
</td>
{VAR:reforb}
</form>
</tr>
</table>
