<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_FORMS_EMAIL}:</td>
<td class="fform"><input type='text' NAME='email' VALUE='{VAR:email}'></td>
</tr>
<tr>
<td class="fcaption">V&auml;ljundi stiil:</td>
<td class="fform"><select name='op_id'>{VAR:ops}</select></td>
</tr>
<tr>
<td class="fcaption">Mis men&uuml;&uuml; alla link n2itab:</td>
<td class="fform"><select class='small_button' name='l_section'>{VAR:sec}</select></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' NAME='save_form_actions' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
