<form name='boo' action='reforb.{VAR:ext}' method=post>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>BANNERID:&nbsp;<a href='{VAR:add}'>Lisa</a></b></td>
</tr>
<!-- SUB: LINE -->
<tr>
<td colspan=5 class="fgtext">{VAR:img}</td>
</tr>
<tr>
<td class="fgtext">&nbsp;{VAR:id}&nbsp;</td>
<td class="fgtext">&nbsp;<a href='{VAR:url}' target=_blank>{VAR:url}</a>&nbsp;</td>
<!-- SUB: ACTIVE -->
<td class="fgtext">&nbsp;<a href='{VAR:deactivate}'>Tee mitteaktiivseks</a>&nbsp;</td>
<!-- END SUB: ACTIVE -->
<!-- SUB: DEACTIVE -->
<td class="fgtext">&nbsp;<a href='{VAR:activate}'>Tee aktiivseks</a>&nbsp;</td>
<!-- END SUB: DEACTIVE -->
<td class="fgtext">&nbsp;<a href='{VAR:change}'>Muuda</a>&nbsp;</td>
<td class="fgtext">&nbsp;<a href='{VAR:delete}'>Kustuta</a>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
<Br><br>
