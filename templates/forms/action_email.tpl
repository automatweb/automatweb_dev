 <form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_FORMS_EMAIL}:</td>
<td class="fform"><input type='text' NAME='email' VALUE='{VAR:email}'></td>
</tr>
<tr>
<td class="fcaption">Kirja subjekt:</td>
<td class="fform">
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<!-- SUB: T_LANG -->
<td class="fform">{VAR:lang_name}</td>
<!-- END SUB: T_LANG -->
</tr>
<tr>
<!-- SUB: LANG -->
<td class="fform"><input class='small_button' type='text' NAME='subj[{VAR:lang_id}]' VALUE='{VAR:subj}'></td>
<!-- END SUB: LANG -->
</tr>
</table>

</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_OUTPUT_STYLE}:</td>
<td class="fform"><select name='op_id'>{VAR:ops}</select></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_WH_MENU_LINK_IS}:</td>
<td class="fform"><select class='small_button' name='l_section'>{VAR:sec}</select></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' NAME='save_form_actions' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
