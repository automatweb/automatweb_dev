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
Vali milliste formide sisestustest otsitakse selle formi t&auml;itmisel:<br>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr bgcolor="#C9EFEF">
<td class="title">ID</td>
<td class="title">Nimi</td>
<td class="title">Kommentaar</td>
<td class="title">Asukoht</td>
<td class="title"><a href='#' onClick="selall();return false;">K&otilde;ik</a></td>
<td class="title">Millist outputti kasutada</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td class="plain">{VAR:form_id}</td>
<td class="plain">{VAR:form_name}</td>
<td class="plain">{VAR:form_comment}</td>
<td class="plain">{VAR:form_location}</td>
<td class="chkbox"><input type='checkbox' NAME='ch_{VAR:form_id}' VALUE=1 {VAR:checked}></td>
<td class="chkbox"><SELECT class='small_button' NAME='sel_{VAR:form_id}'>{VAR:ops}</select>
</td>
</tr>
<!-- END SUB: LINE -->
</table>
<input type=submit NAME='save' VALUE='Salvesta'>
{VAR:reforb}
</form>
    
