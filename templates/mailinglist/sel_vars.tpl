<form action='refcheck.{VAR:ext}' METHOD=POST NAME='fib'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle">&nbsp;<b>MUUTUJAD:&nbsp;<a href='javascript:fib.submit()'>Salvesta</a></b>
</td>
</tr>
<tr>
<td align=center class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;K&otilde;ik&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:var_name}&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;<input type='checkbox' NAME='ch_{VAR:var_id}' VALUE=1 {VAR:var_ch}>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
<input type='hidden' NAME='action' VALUE='sel_vars'>
<input type='hidden' NAME='list_id' VALUE='{VAR:list_id}'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
</form>