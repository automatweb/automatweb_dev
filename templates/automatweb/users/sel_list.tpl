<a name="#userlist">
<form action='refcheck.{VAR:ext}' METHOD=post>
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>KASUTAJAD:&nbsp;
<!-- SUB: CAN_EDIT -->
<a href='{VAR:urlgrp}'>Selle grupi</a>&nbsp;|&nbsp;<a href='{VAR:urlall}'>Lisa juurde</a>&nbsp;|&nbsp;<a href='{VAR:urlgrps}'>Grupid</a>
<!-- END SUB: CAN_EDIT -->
</b></td>
</tr>
</table>
{VAR:table}
<!-- SUB: CAN_EDIT_2 -->
<input type='submit' NAME='save' VALUE='Salvesta'>
<!-- END SUB: CAN_EDIT_2 -->
<input type='hidden' NAME='action' VALUE='update_grp_members'>
<input type='hidden' NAME='gid' VALUE='{VAR:gid}'>
<input type='hidden' NAME='all' VALUE='{VAR:all}'>
<input type='hidden' NAME='from' VALUE='{VAR:from}'>
</form>
