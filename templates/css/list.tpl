<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<form method="POST" action="{VAR:baseurl}/automatweb/reforb.{VAR:ext}" name="csslist">
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>CSS Editor:&nbsp;
<b><a href="{VAR:link_sys_styles}">Süsteemsed stiilid</a> | <a href="{VAR:link_my_styles}">Minu stiilid</a> |
<a href="javascript:document.csslist.submit()"><font color="red">Salvesta</font></a></b>
</td>
</tr>
</table>
<br>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td align="center" class="title">&nbsp;#&nbsp;</td>
<td align="center" class="title">&nbsp;Süsteemi stiil&nbsp;</td>
<td align="center" class="title">&nbsp;Oma stiil&nbsp;</td>
</tr>
<!-- SUB: line -->
<tr>
<td class="fgtext">{VAR:cnt}</td>
<td class="fgtext">{VAR:sys_style}</td>
<td class="fgtext">
	<select name="style[{VAR:sys_style}]">
	{VAR:my_styles}
	</select>
</td>
</tr>
<!-- END SUB: line -->
</table>
</td>
</tr>
{VAR:reforb}
</form>
</table>
