<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_POLL_QUESTION}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_POLL_COMMENTARY}:</td><td class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:comment}</textarea></td>
</tr>
<!-- SUB: QUESTION -->
<tr>
<td class="fcaption">{VAR:LC_POLL_ANSWER}:</td><td class="fform"><input type='text' NAME='an_{VAR:answer_id}' VALUE='{VAR:answer}'></td>
</tr>
<!-- END SUB: QUESTION -->
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_POLL_SAVE}'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_poll'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
</form>
