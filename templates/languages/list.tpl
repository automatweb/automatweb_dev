<form action='languages.{VAR:ext}' method=get name='q'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
	<tr>
		<td height="15" colspan="11" class="fgtitle">&nbsp;<b>{VAR:LC_LANGUAGES_BIG_LANGUAGES}:&nbsp;<a href='languages.{VAR:ext}?type=add'>{VAR:LC_LANGUAGES_ADD}</a> | <a href='javascript:document.q.submit()'>{VAR:LC_LANGUAGES_SAVE}</a></b></td>
	</tr>
	<tr>
		<td align=center class="title">&nbsp;{VAR:LC_LANGUAGES_NAME}&nbsp;</td>
		<td align=center class="title">&nbsp;{VAR:LC_LANGUAGES_LANGUAGE_ID}&nbsp;</td>
		<td align=center class="title">&nbsp;{VAR:LC_LANGUAGES_CHARSET}&nbsp;</td>
		<td align=center class="title">&nbsp;{VAR:LC_LANGUAGES_CHOSEN}&nbsp;</td>
		<td align=center class="title">&nbsp;{VAR:LC_LANGUAGES_ADMIN}&nbsp;</td>
		<td align=center class="title">&nbsp;{VAR:LC_LANGUAGES_ACTIVE}?&nbsp;</td>
		<td align="center" colspan="2" class="title">&nbsp;{VAR:LC_LANGUAGES_ACTION}&nbsp;</td>
	</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext" align=center>&nbsp;{VAR:name}&nbsp;</td>
<td class="fgtext" align=center>&nbsp;{VAR:acceptlang}&nbsp;</td>
<td class="fgtext" align=center>&nbsp;{VAR:charset}&nbsp;</td>
<!-- SUB: SEL -->
<td class="fgtext" align="center">&nbsp;<font color=red>{VAR:LC_LANGUAGES_ACTIVE}</font>&nbsp;</td>
<!-- END SUB: SEL -->

<!-- SUB: NSEL -->
<td class="fgtext" align="center">&nbsp;<a href='languages.{VAR:ext}?type=set_sel&id={VAR:id}'>{VAR:LC_LANGUAGES_ACTIVATE}</a>&nbsp;</td>
<!-- END SUB: NSEL -->

<!-- SUB: CSEL -->
<td class="fgtext" align="center">&nbsp;{VAR:LC_LANGUAGES_ACTIVATE}&nbsp;</td>
<!-- END SUB: CSEL -->

<td class="fgtext" align="center">&nbsp;<input type='radio' name='adminlang' value='{VAR:id}' {VAR:check}>&nbsp;</td>

<!-- SUB: ACTIVE -->
<td class="fgtext" align="center">&nbsp;<a href='languages.{VAR:ext}?type=set_nactive&id={VAR:id}'>{VAR:LC_LANGUAGES_ACTIVE}</a>&nbsp;</td>
<!-- END SUB: ACTIVE -->
<!-- SUB: NACTIVE -->
<td class="fgtext" align="center">&nbsp;<a href='languages.{VAR:ext}?type=set_active&id={VAR:id}'>{VAR:LC_LANGUAGES_ACTIVATE}</a>&nbsp;</td>
<!-- END SUB: NACTIVE -->
<td class="fgtext2" align=center>&nbsp;<a href='languages.{VAR:ext}?type=change&id={VAR:id}'>{VAR:LC_LANGUAGES_CHANGE}</a>&nbsp;</td>
<td class="fgtext2" align=center>&nbsp;<a href='javascript:box2("Are You sure you wish to delete language?","languages.{VAR:ext}?type=delete&id={VAR:id}")'>{VAR:LC_LANGUAGES_DELETE}</a>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td></tr></table>
<input type='hidden' name='type' value='saveadmin'>
</form>