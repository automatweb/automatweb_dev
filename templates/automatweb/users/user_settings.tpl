<form action='reforb.{VAR:ext}' method="POST">
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" class="fgtext">&nbsp;Admin liidese keel:&nbsp;</td>
<td height="15" class="fgtext">&nbsp;
<!-- SUB: LANG -->
{VAR:lang_name} <input type='radio' name='adminlang' VALUE='{VAR:lang_id}' {VAR:checked}>
<!-- END SUB: LANG -->
</td>
</tr>
<tr>
<td height="15" class="fgtext">&nbsp;Aktiivne valuuta:&nbsp;</td>
<td height="15" class="fgtext">&nbsp;
<!-- SUB: CUR -->
{VAR:cur_name} <input type='radio' name='currency' VALUE='{VAR:cur_id}' {VAR:checked}>
<!-- END SUB: CUR -->
</td>
</tr>
<tr>
<td height="15" class="fgtext" colspan=2>&nbsp;<input class='small_button' type='submit' value='Salvesta'>&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
Kasutaja info:
{VAR:form}
