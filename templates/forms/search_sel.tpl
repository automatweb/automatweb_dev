<script language=javascript>
var st=1;
function selall()
{
<!-- SUB: SELLINE -->
	document.forms[0].elements[{VAR:row}].checked=st;
<!-- END SUB: SELLINE -->
st = !st;
return false;
}
</script>
<form action='reforb.{VAR:ext}' METHOD=post>
Lehek&uuml;lg: 
<!-- SUB: PAGE -->
<a href='{VAR:pageurl}'>{VAR:from} - {VAR:to}</a> |
<!-- END SUB: PAGE -->

<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to} |
<!-- END SUB: SEL_PAGE -->

<br>{VAR:LC_FORMS_CHOOSE_WHTA_INPUT_FORM_FILL}:<br>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr bgcolor="#C9EFEF">
<td class="title">ID</td>
<td class="title">{VAR:LC_FORMS_NAME}</td>
<td class="title">{VAR:LC_FORMS_COMMENT}</td>
<td class="title">{VAR:LC_FORMS_POSITION}</td>
<td class="title"><a href='#' onClick="selall();return false;">{VAR:LC_FORMS_ALL}</a></td>
<td class="title">{VAR:LC_FORMS_WHAT_OUTPUT_TO_USE}</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td class="plain">{VAR:form_id}</td>
<td class="plain">{VAR:form_name}</td>
<td class="plain">{VAR:form_comment}</td>
<td class="plain">{VAR:form_location}</td>
<td class="chkbox"><input type='checkbox' NAME='ch_{VAR:form_id}' VALUE=1 {VAR:checked}><input type='hidden' name='inpage[{VAR:form_id}]' value='1'><input type='hidden' name='prev[{VAR:form_id}]' value='{VAR:prev}'></td>
<td class="chkbox"><SELECT class='small_button' NAME='sel_{VAR:form_id}'>{VAR:ops}</select>
</td>
</tr>
<!-- END SUB: LINE -->
</table>
Otsi ainult formist, mitte p&auml;rgadest: <input type='checkbox' name='formsonly' value=1 {VAR:formsonly}><br><br>
otsi p&auml;rjast: <select name='se_chain'>{VAR:chains}</select><Br><br>
<input type=submit NAME='save' VALUE='{VAR:LC_FORMS_SAVE}'>
{VAR:reforb}
</form>
    
