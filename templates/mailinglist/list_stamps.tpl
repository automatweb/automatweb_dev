<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>{VAR:LC_MAILINGLIST_BIG_STAMPS}:&nbsp;<a href='list.{VAR:ext}?type=add_stamp'>{VAR:LC_MAILINGLIST_ADD}</a></b></td>
</tr>
<tr>
<td align=center class="title">&nbsp;Number&nbsp;</td>
<td align=center class="title">&nbsp;{VAR:LC_MAILINGLIST_NAME}&nbsp;</td>
<td align="center" colspan="3" class="title">&nbsp;{VAR:LC_MAILINGLIST_ACTION}&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:stamp_id}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:stamp_name}&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: V_CHANGE -->
<a href='list.{VAR:ext}?type=change_stamp&id={VAR:stamp_id}'>{VAR:LC_MAILINGLIST_CHANGE}</a>
<!-- END SUB: V_CHANGE -->
&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: V_DELETE -->
<a href='javascript:box2("{VAR:LC_MAILINGLIST_WANT_TO_DEL_STAMP} {VAR:stamp_name}?","list.{VAR:ext}?type=delete_stamp&id={VAR:stamp_id}")'>{VAR:LC_MAILINGLIST_DELETE}</a>
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
