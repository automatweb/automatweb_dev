<table border="0" cellspacing="0" cellpadding="0"  width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2"  width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>MEILID:&nbsp;<a href='list.{VAR:ext}?type=new_mail&parent={VAR:parent}'>Lisa</a></b></td>
</tr>
<tr>
<td align="center" class="title">&nbsp;From&nbsp;</td>
<td align="center" class="title">&nbsp;Subject&nbsp;</td>
<td align="center" class="title">&nbsp;Saadetud?&nbsp;</td>
<td align="center" class="title">&nbsp;Millal saadetud&nbsp;</td>
<td align="center" colspan="5" class="title">Tegevus</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:mail_from}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:mail_subj}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:mail_sent}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:mail_sent_when}&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: M_CHANGE -->
<a href='list.{VAR:ext}?type=change_mail&parent={VAR:parent}&id={VAR:mail_id}'>Muuda</a>
<!-- END SUB: M_CHANGE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: M_DELETE -->
<a href="javascript:box2('Oled kindel, et soovid seda meili  kustutada?','list.{VAR:ext}?type=delete_mail&id={VAR:mail_id}&parent={VAR:parent}')">Kustuta</a>
<!-- END SUB: M_DELETE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: M_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:mail_id}&file=email.xml'>ACL</a>
<!-- END SUB: M_ACL -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: M_SEND -->
<a href='list.{VAR:ext}?type=send_mail&id={VAR:mail_id}&parent={VAR:parent}'>Saada</a>
<!-- END SUB: M_SEND -->
&nbsp;</td>
<td class="fgtext2">&nbsp;<a href='list.{VAR:ext}?type=mail_preview&id={VAR:mail_id}&parent={VAR:parent}'>Eelvaade</a>&nbsp;</td>
<!-- END SUB: LINE -->
</tr>
</table>

</td></tr>
</table>
<br><br>