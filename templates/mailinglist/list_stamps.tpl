<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>STAMBID:&nbsp;<a href='list.{VAR:ext}?type=add_stamp'>Lisa</a></b></td>
</tr>
<tr>
<td align=center class="title">&nbsp;Number&nbsp;</td>
<td align=center class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" colspan="3" class="title">&nbsp;Tegevus&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:stamp_id}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:stamp_name}&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: V_CHANGE -->
<a href='list.{VAR:ext}?type=change_stamp&id={VAR:stamp_id}'>Muuda</a>
<!-- END SUB: V_CHANGE -->
&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: V_DELETE -->
<a href='javascript:box2("Oled kindel et tahad kustutada stampi {VAR:stamp_name}?","list.{VAR:ext}?type=delete_stamp&id={VAR:stamp_id}")'>Kustuta</a>
<!-- END SUB: V_DELETE -->
&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: V_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:stamp_id}&file=variable.xml'>ACL</a>
<!-- END SUB: V_ACL -->
&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
