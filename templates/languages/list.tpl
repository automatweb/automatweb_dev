<form action='languages.{VAR:ext}' method=get name='q'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
	<tr>
		<td height="15" colspan="11" class="fgtitle">&nbsp;<b>LANGUAGES:&nbsp;<a href='languages.{VAR:ext}?type=add'>Add</a> | <a href='javascript:document.q.submit()'>Save</a></b></td>
	</tr>
	<tr>
		<td align=center class="title">&nbsp;Name&nbsp;</td>
		<td align=center class="title">&nbsp;Language id&nbsp;</td>
		<td align=center class="title">&nbsp;Charset&nbsp;</td>
		<td align=center class="title">&nbsp;Chosen&nbsp;</td>
		<td align=center class="title">&nbsp;Admin&nbsp;</td>
		<td align=center class="title">&nbsp;Active?&nbsp;</td>
		<td align="center" colspan="2" class="title">&nbsp;Action&nbsp;</td>
	</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext" align=center>&nbsp;{VAR:name}&nbsp;</td>
<td class="fgtext" align=center>&nbsp;{VAR:acceptlang}&nbsp;</td>
<td class="fgtext" align=center>&nbsp;{VAR:charset}&nbsp;</td>
<!-- SUB: SEL -->
<td class="fgtext" align="center">&nbsp;<font color=red>Active</font>&nbsp;</td>
<!-- END SUB: SEL -->

<!-- SUB: NSEL -->
<td class="fgtext" align="center">&nbsp;<a href='languages.{VAR:ext}?type=set_sel&id={VAR:id}'>Activate</a>&nbsp;</td>
<!-- END SUB: NSEL -->

<!-- SUB: CSEL -->
<td class="fgtext" align="center">&nbsp;Activate&nbsp;</td>
<!-- END SUB: CSEL -->

<td class="fgtext" align="center">&nbsp;<input type='radio' name='adminlang' value='{VAR:id}' {VAR:check}>&nbsp;</td>

<!-- SUB: ACTIVE -->
<td class="fgtext" align="center">&nbsp;<a href='languages.{VAR:ext}?type=set_nactive&id={VAR:id}'>Active</a>&nbsp;</td>
<!-- END SUB: ACTIVE -->
<!-- SUB: NACTIVE -->
<td class="fgtext" align="center">&nbsp;<a href='languages.{VAR:ext}?type=set_active&id={VAR:id}'>Activate</a>&nbsp;</td>
<!-- END SUB: NACTIVE -->
<td class="fgtext2" align=center>&nbsp;<a href='languages.{VAR:ext}?type=change&id={VAR:id}'>Change</a>&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;<a href='javascript:box2("Are You sure you wish to delete language?","languages.{VAR:ext}?type=delete&id={VAR:id}")'>Delete</a>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td></tr></table>
<input type='hidden' name='type' value='saveadmin'>
</form>