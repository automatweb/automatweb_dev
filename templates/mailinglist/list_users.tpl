<script language = javascript>
var st=1;
function selall()
{
<!-- SUB: SELLINE -->

	document.forms[0].elements[{VAR:row}].checked=st;
	
<!-- END SUB: SELLINE -->
st = !st;
return false;
}
function submitForm(val)
{
	document.forms[0].elements[val].value=1;
	document.forms[0].submit();
}
</script>
<form action=refcheck.{VAR:ext} method=POST>
<table border="0" cellspacing="0" cellpadding="0"  width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0"  width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>LISTI {VAR:list_name} LIIKMED ({VAR:count}):&nbsp;
<!-- SUB: U_ADD -->
<a href='list.{VAR:ext}?type=add_user&id={VAR:list_id}'>Lisa</a>&nbsp;|&nbsp;
<!-- END SUB: U_ADD -->
<!-- SUB: PASTE -->
<a href='list.{VAR:ext}?type=paste_user&id={VAR:list_id}'>Paste</a>&nbsp;|&nbsp;
<!-- END SUB: PASTE -->
<a href="javascript:submitForm('delete')" >Kustuta</a>
&nbsp;|&nbsp;<a href="javascript:submitForm('copy')" >Copy</a>
&nbsp;|&nbsp;<a href="javascript:submitForm('cut')" >Cut</a>
<!-- SUB: U_IMPORT -->
&nbsp;|&nbsp;<a href="list.{VAR:ext}?type=import_file&id={VAR:list_id}" >Impordi</a>
<!-- END SUB: U_IMPORT -->
&nbsp;|&nbsp;<a href="list.{VAR:ext}?type=export_file&id={VAR:list_id}" >Ekspordi</a>
</b></td>
</tr>

<tr>
<td align="center" class="title">&nbsp;ID&nbsp;</td>
<td align="center" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;E-mail&nbsp;</td>
<td align="center" class="title" colspan=2>Tegevus</td>
<td align="center" class="title"><a href='#' onClick="selall();return false;">K&otilde;ik</a></td>
</tr>
<!-- SUB: LINE -->
<tr>
<td {VAR:cut}>&nbsp;{VAR:user_id}&nbsp;</td>
<td {VAR:cut}>&nbsp;{VAR:user_name}&nbsp;</td>
<td {VAR:cut}>&nbsp;{VAR:user_mail}&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: U_CHANGE -->
<a href='list.{VAR:ext}?type=change_user&id={VAR:list_id}&user_id={VAR:user_id}'>Muuda</a>
<!-- END SUB: U_CHANGE -->
&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;
<!-- SUB: U_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:user_id}&file=list_member.xml'>ACL</a>
<!-- END SUB: U_ACL -->
&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;<input type='checkbox' NAME='ch_{VAR:user_id}' VALUE=1>&nbsp;</td>
<!-- END SUB: LINE -->
</tr>
</table>
</td></tr></table>
<br><br>

<input type=hidden NAME=action VALUE=people_list>
<input type=hidden NAME=delete VALUE="">
<input type=hidden NAME=copy VALUE="">
<input type=hidden NAME=cut VALUE="">
<input type=hidden NAME=list_id VALUE={VAR:list_id}>
</form>
Legend:
<table border=0>
<tr><td>Copied on sellised:</td><td class="fgtext_copied">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
<tr><td>Cut on sellised:</td><td class="fgtext_cut">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
</table>