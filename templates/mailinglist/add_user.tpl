<form action = 'refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">List:</td><td class="fcaption">{VAR:list_name}</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:user_name}'></td>
</tr>
<tr>
<td class="fcaption">E-mail:</td><td class="fform"><input type='text' NAME='email' VALUE='{VAR:user_mail}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2>{VAR:LC_MAILINGLIST_SMALL_VARIABLES}:</td>
</tr>
<!-- SUB: VARS -->
<tr>
<td class="fcaption" valign=top>{VAR:var_name}</td>
<td class="fform"><textarea ROWS=3 COLS=60 NAME='var_{VAR:var_id}'>{VAR:var_value}</textarea></td>
</tr>
<!-- END SUB: VARS -->
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_MAILINGLIST_SAVE}'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='new_user'>
<input type='hidden' NAME='id' VALUE='{VAR:list_id}'>
<input type='hidden' NAME='user_id' VALUE='{VAR:user_id}'>
</form>
