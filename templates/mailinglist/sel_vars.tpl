<form action='reforb.{VAR:ext}' METHOD=POST NAME='fib'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>{VAR:LC_MAILINGLIST_BIG_VARIABLES}:&nbsp;<a href='javascript:document.fib.submit()'>{VAR:LC_MAILINGLIST_SAVE}</a></b>
</td>
</tr>
<tr>
<td align=center class="title">&nbsp;{VAR:LC_MAILINGLIST_NAME}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MAILINGLIST_ALL}&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:var_name}&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;<input type='checkbox' NAME='ch_{VAR:var_id}' VALUE=1 {VAR:var_ch}>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
{VAR:reforb}
</form>
