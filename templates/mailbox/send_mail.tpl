<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">From:</td><td class="fform"><input type='text' NAME='from' VALUE='{VAR:from}'></td>
</tr>
<tr>
<td class="fcaption">To:</td><td class="fform"><input type='text' NAME='to' VALUE='{VAR:to}'></td>
</tr>
<tr>
<td class="fcaption">Subject:</td><td class="fform"><input type='text' NAME='subject' VALUE='{VAR:subject}'></td>
</tr>
<tr>
<td class="fcaption" valign=top>Text:</td><td class="fform"><textarea name='content' COLS=74 ROWS=20 WRAP=HARD>{VAR:content}</textarea></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' NAME='save' VALUE='Salvesta'>&nbsp;&nbsp;<input class='small_button' type='submit' NAME='send' VALUE='Saada'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_mail'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
<input type='hidden' NAME='mail_action' VALUE='{VAR:mail_action}'>
<input type='hidden' NAME='in_reply_to' VALUE='{VAR:in_reply_to}'>
</form>
