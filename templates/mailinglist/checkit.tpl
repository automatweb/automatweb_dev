<table border="0" cellspacing="0" cellpadding="0"  width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0"  width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>LISTI {VAR:list_name} LIIKMETE kontroll:&nbsp;</b></td>
</tr>

<tr>
<td align="center" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;E-mail&nbsp;</td>
<td align="center" class="title">&nbsp;Listid&nbsp;</td>
<td align="center" class="title">&nbsp;Tegevus&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:user_name}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:user_mail}&nbsp;</td>
<td class="fgtext" align=center>&nbsp;
<!-- SUB: HAS_LIST -->
<table border=0 cellpadding=0 cellspacing=1 bgcolor="#000000">
<tr><td bgcolor="#FFFFFF" class="fgtext">&nbsp;Nimi&nbsp;</td><td  bgcolor="#FFFFFF" class="fgtext">&nbsp;Kas meil saadetud?&nbsp;</td><td bgcolor="#FFFFFF" class="fgtext">&nbsp;Millal?&nbsp;</td><td bgcolor="#FFFFFF" class="fgtext">&nbsp;Kustuta&nbsp;</td></tr>
<!-- SUB: LIST -->
<tr>
<td bgcolor="#FFFFFF" class="fgtext">&nbsp;<a href='list.{VAR:ext}?type=list_inimesed&id={VAR:list_id}'>{VAR:list_name}</a>&nbsp;</td>
<td bgcolor="#FFFFFF" class="fgtext">&nbsp;{VAR:mail_sent}&nbsp;</td>
<td bgcolor="#FFFFFF" class="fgtext">&nbsp;{VAR:mail_when}&nbsp;</td>
<td bgcolor="#FFFFFF" class="fgtext">&nbsp;<a href='list.{VAR:ext}?type=del_comp&lid={VAR:blid}&usid={VAR:user_id}'>Kustuta listist</a>&nbsp;</td>
</tr>
<!-- END SUB: LIST -->
</table>
<!-- END SUB: HAS_LIST -->
&nbsp;</td>
<td class="fgtext2">&nbsp;<a href='list.{VAR:ext}?type=del_comp&lid={VAR:blid}&usid={VAR:user_id}'>Kustuta listist</a>&nbsp;</td>
<!-- END SUB: LINE -->
</tr>
</table>
</td></tr></table>
<br><br>

