<br>
<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Kasutajanimi:</td><td class="fform"><input type='text' NAME='conf[username]' VALUE='{VAR:username}'></td>
</tr>
<tr>
<td class="fcaption">Password:</td><td class="fform"><input type='password' NAME='conf[password]' VALUE='{VAR:password}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2>Sissetulev server:</td>
</tr>
<tr>
<td class="fcaption" >Nimi:</td><td class="fform"><input type='text' NAME='conf[server_name]' VALUE='{VAR:server_name}'></td>
</tr>
<tr>
<td class="fcaption">T&uuml;&uuml;p:</td><td class="fform"><input type='radio' NAME='conf[server_type]' VALUE='pop3' {VAR:pop3_sel}> - pop3</td>
</tr>
<tr>
<td class="fcaption">&nbsp;</td><td class="fform"><input type='radio' NAME='conf[server_type]' VALUE='imap' {VAR:imap_sel}> - IMAP</td>
</tr>
<tr>
<td class="fcaption">&nbsp;</td><td class="fform"><input type="checkbox" NAME='conf[leave_messages]' VALUE=1 {VAR:leave_messages_sel}>&nbsp;kas j&auml;tta meilid ka serverisse alles?</td>
</tr>
<tr>
<td class="fcaption">V&auml;ljaminev server:</td><td class="fform"><input type='text' NAME='conf[out_server_name]' VALUE='{VAR:out_server_name}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_mailbox_conf'>
</form>

