<form action='reforb.{VAR:ext}' method="POST">
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" class="fgtext">&nbsp;Admin language:&nbsp;</td>
<td height="15" class="fgtext">&nbsp;
<!-- SUB: LANG -->
{VAR:lang_name} <input type='radio' name='adminlang' VALUE='{VAR:lang_id}' {VAR:checked}>
<!-- END SUB: LANG -->
</td>
</tr>
<tr>
<td height="15" class="fgtext">&nbsp;Puu tüüp&nbsp;</td>
<td height="15" class="fgtext">&nbsp;
<select name="treetype">
{VAR:treetype}
</select>
</td>
</tr>
<tr>
<td height="15" class="fgtext">&nbsp;Objekti lisamise menüü tüüp&nbsp;</td>
<td height="15" class="fgtext">&nbsp;
<select name="addobject_type">
{VAR:addobject_type}
</select>
</td>
</tr>
<tr>
<td height="15" class="fgtext">&nbsp;Default currency:&nbsp;</td>
<td height="15" class="fgtext">&nbsp;
<!-- SUB: CUR -->
{VAR:cur_name} <input type='radio' name='currency' VALUE='{VAR:cur_id}' {VAR:checked}>
<!-- END SUB: CUR -->
</td>
</tr>
<tr>
<td height="15" class="fgtext" colspan=2>&nbsp;<input class='small_button' type='submit' value='Save'>&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
Kasutaja info:
{VAR:form}
