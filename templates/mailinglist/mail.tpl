<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0 width=80%>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_SMALL_VARIABLES}:</td><td class="fform">
#{VAR:LC_MAILINGLIST_SMALL_NAME}#&nbsp; #email#&nbsp; #{VAR:LC_MAILINGLIST_DATE}#
<!-- SUB: LIST -->
&nbsp; {VAR:var_name} 
<!-- END SUB: LIST -->
</td>
</tr>
<tr>
<td class="fcaption" colspan=2>{VAR:LC_MAILINGLIST_SMALL_STAMPS}:</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_NAME}</td>
<td class="fcaption">{VAR:LC_MAILINGLIST_VALUE}</td>
</tr>
<!-- SUB: SLIST -->
<tr><td class="fcaption">{VAR:stamp_name}</td><td class="fcaption">{VAR:stamp_value}</td></tr>
<!-- END SUB: SLIST -->
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_LINKS}:</td><td class="fform">
<!-- SUB: L_LIST -->
&nbsp; {VAR:link_name} ({VAR:link_addr})
<!-- END SUB: L_LIST -->
</td>
</tr>
<tr>
<td class="fcaption">From:</td><td class="fform">e-mail:&nbsp;<input type='text' NAME='from' VALUE='{VAR:mail_from}'>&nbsp;{VAR:LC_MAILINGLIST_SMALL_NAME}:&nbsp;<input type='text' NAME='from_name' VALUE='{VAR:mail_from_name}'></td>
</tr>
<tr>
<td class="fcaption">Subject:</td><td class="fform"><input type='text' NAME='subject' VALUE='{VAR:mail_subj}'></td>
</tr>
<tr>
<td valign=top class="fcaption">{VAR:LC_MAILINGLIST_CONTENT}:</td><td class="fform"><textarea NAME='contents' rows=20 cols=70 wrap=soft>{VAR:mail_content}</textarea></td>
</tr>
<tr>
<td class="fcaption" colspan=2>{VAR:LC_MAILINGLIST_ADD_LINK}:</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_ADDRESS}:</td><td class="fform"><input type='text' NAME='link_addr' ></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_MAILINGLIST_SAVE}' NAME='save_mail'>&nbsp;<input class='small_button' type='submit' VALUE='{VAR:LC_MAILINGLIST_SEND}' NAME='send_mail'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='admin_mail'>
<input type='hidden' NAME='mail_id' VALUE='{VAR:mail_id}'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
</form>
