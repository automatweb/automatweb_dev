<table border="0" cellspacing="1" cellpadding="0" width=100%>
<form method="POST" action="{VAR:baseurl}/automatweb/reforb.{VAR:ext}" name="csslist">
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>{VAR:LC_CSS_CSS_EDITOR}:&nbsp;
<b><a href="{VAR:link_groups}">{VAR:LC_CSS_GROUPS}</a> |
<a href="{VAR:link_sys_styles}">{VAR:LC_CSS_GROUPS_STYLES}</a> |
<a href="{VAR:link_add_style}">{VAR:LC_CSS_ADD_NEW}</a> |
<a href="javascript:document.csslist.submit()"><font color="red">{VAR:LC_CSS_SAVE}</font></b>
</td>
</tr>
</table>
<br>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td align="center" class="title">&nbsp;Akt.&nbsp;</td>
<td align="center" class="title">&nbsp;#&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_CSS_NAME}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_CSS_CAHNGED}&nbsp;</td>
<td align="center" class="title">&nbsp;Muutja&nbsp;</td>
<td align="center" class="title" colspan="2">&nbsp;{VAR:LC_CSS_ACTION}&nbsp;</td>
</tr>
<!-- SUB: line -->
<tr>
<input type="hidden" name="check[]" value="{VAR:oid}">
<td class="fgtext" align="center"><input type="checkbox" name="act[]" value="{VAR:oid}" {VAR:checked}></td>
<td class="fgtext" align="center">{VAR:oid}</td>
<td class="fgtext"><strong>{VAR:name}</strong></td>
<td class="fgtext" align="center">{VAR:modified}</td>
<td class="fgtext" align="center">{VAR:modifiedby}</td>
<td class="fgtext" align="center"><a href="{VAR:link_edit}">{VAR:LC_CSS_CHANGE}</a></td>
<td class="fgtext" align="center"><a href="javascript:box2('Oled kindel?','{VAR:link_delete}')">{VAR:LC_CSS_DELETE}</a></td>
</tr>
<!-- END SUB: line -->
</table>
</td>
</tr>
{VAR:reforb}
</form>
</table>
