<form action = 'reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_COMMENTARY}:</td><td class="fform"><input type='text' NAME='comment' VALUE='{VAR:comment}'></td>
</tr>
<tr>
<td class="fcaption">Listiga liitumisel saadetav meil:</td><td class="fform"><select NAME='join_mail'>{VAR:join_mail}</select></td>
</tr>
<tr>
<td class="fcaption">Listist lahkumisel saadetav meil:</td><td class="fform"><select NAME='leave_mail'>{VAR:leave_mail}</select></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_MAILINGLIST_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
