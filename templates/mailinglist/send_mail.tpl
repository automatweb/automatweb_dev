<form action='refcheck.{VAR:ext}' METHOD=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Vali, millisesse listi saadame:</td><td class="fform"><select NAME='list_id'>
<!-- SUB: LINE -->
<option VALUE={VAR:list_id}>{VAR:list_name}
<!-- END SUB: LINE -->
</select>
</td></tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Saada'></td>
</tr>
</table>
<input type='hidden' NAME='id' VALUE='{VAR:mail_id}'>
<input type='hidden' NAME='action' VALUE='send_mail'>
</form>
