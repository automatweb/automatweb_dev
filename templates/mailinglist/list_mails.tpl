<table border="0" cellspacing="0" cellpadding="0"  width=100%>
<form action="{VAR:self}" method="POST" name="defform">
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2"  width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>{VAR:LC_MAILINGLIST_BIG_MAILS}:&nbsp;<a href='list.{VAR:ext}?type=new_mail&parent={VAR:parent}'>{VAR:LC_MAILINGLIST_ADD}</a> |
<a href="javascript:document.defform.submit()">{VAR:LC_MAILINGLIST_SAVE}</a></b></td>
</tr>
<tr>
<td align="center" class="title">&nbsp;From&nbsp;</td>
<td align="center" class="title">&nbsp;Subject&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MAILINGLIST_SENT}?&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MAILINGLIST_WHEN_SENT}&nbsp;</td>
<td align="center" colspan="5" class="title">{VAR:LC_MAILINGLIST_ACTION}</td>
<td align="center" class="title">&nbsp;Default&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:mail_from}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:mail_subj}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:mail_sent}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:mail_sent_when}&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: M_CHANGE -->
<a href='list.{VAR:ext}?type=change_mail&parent={VAR:parent}&id={VAR:mail_id}'>{VAR:LC_MAILINGLIST_CHANGE}</a>
<!-- END SUB: M_CHANGE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: M_DELETE -->
<a href="javascript:box2('{VAR:LC_MAILINGLIST_WANT_TO_DEL_MAIL}?','list.{VAR:ext}?type=delete_mail&id={VAR:mail_id}&parent={VAR:parent}')">{VAR:LC_MAILINGLIST_DELETE}</a>
<!-- END SUB: M_DELETE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: M_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:mail_id}&file=email.xml'>ACL</a>
<!-- END SUB: M_ACL -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: M_SEND -->
<a href='list.{VAR:ext}?type=send_mail&id={VAR:mail_id}&parent={VAR:parent}'>{VAR:LC_MAILINGLIST_SEND}</a>
<!-- END SUB: M_SEND -->
&nbsp;</td>
<td class="fgtext2">&nbsp;<a href='list.{VAR:ext}?type=mail_preview&id={VAR:mail_id}&parent={VAR:parent}'>{VAR:LC_MAILINGLIST_PREVIEW}</a>&nbsp;</td>
<td class="fgtext2" align="center">
<input type="radio" name="default" value="{VAR:mail_id}" {VAR:checked}>
</td>
<!-- END SUB: LINE -->
</tr>
</table>

</td></tr>
<input type="hidden" name="type" value="submit_default">
<input type="hidden" name="id" value="{VAR:id}">
</form>
</table>
<br><br>
