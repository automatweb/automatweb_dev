<br>
<form action='refcheck.{VAR:ext}' method=post>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0">
<tr>
<td align="center" class="title">&nbsp;ID&nbsp;</td>
<td align="center" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Kirjeldus&nbsp;</td>
<td align="center" colspan="1" class="title">K&otilde;ik</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td align="center" class="fgtext">&nbsp;{VAR:cat_id}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:cat_name}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:cat_comment}&nbsp;</td>
<td class="fgtext">&nbsp;<input type='checkbox' NAME='ch_{VAR:cat_id}' VALUE=1 {VAR:cat_checked}>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
<tr>
<td class="fgtext" colspan=4><input type='submit' CLASS='small_button' VALUE='Salvesta'></td>
</tr>
</table>
</td></tr></table>
<input type='hidden' NAME='action' VALUE='submit_action'>
<input type='hidden' NAME='level' VALUE='1'>
<input type='hidden' NAME='id' VALUE='{VAR:form_id}'>
<input type='hidden' NAME='action_id' VALUE='{VAR:action_id}'>
</form>