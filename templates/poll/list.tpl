<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0"  width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>{VAR:LC_POLL_BIG_POLLS}: <a href="poll.{VAR:ext}?type=add">{VAR:LC_POLL_ADD}</a></b></td>
</tr>

<tr>
<td align="center" class="title">&nbsp;{VAR:LC_POLL_QUESTION}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_POLL_MUUTJA}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_POLL_CHANGED}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_POLL_ACTIVITY}&nbsp;</td>
<td align="center" class="title" colspan=2>&nbsp;{VAR:LC_POLL_ACTION}&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td align="center" class="fgtext">&nbsp;{VAR:name}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modified}&nbsp;</td>
<!-- SUB: ACTIVE -->
<td align="center" class="fgtext">&nbsp;<font color="#ff0000">{VAR:LC_POLL_YES}</font>&nbsp;</td>
<!-- END SUB: ACTIVE -->
<!-- SUB: NACTIVE -->
<td align="center" class="fgtext">&nbsp;<a href="poll.{VAR:ext}?type=set_active&id={VAR:id}">{VAR:LC_POLL_NO}</a>&nbsp;</td>
<!-- END SUB: NACTIVE -->
<td align="center" class="fgtext2">&nbsp;<a href='poll.{VAR:ext}?type=change&id={VAR:id}'>{VAR:LC_POLL_CHANGE}</a>&nbsp;</td>
<td align="center" class="fgtext2">&nbsp;<a href='poll.{VAR:ext}?type=delete&id={VAR:id}'>{VAR:LC_POLL_DELETE}</a>&nbsp;</td>
</tr>

<!-- END SUB: LINE -->

</table>
</td></tr></table>
