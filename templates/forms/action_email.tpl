<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_EMAIL}:</td>
<td class="celltext"><input type='text' NAME='email' VALUE='{VAR:email}'></td>
</tr>
<tr class="aste01">
<td class="celltext">Kirja subjekt:</td>
<td class="celltext">
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr class="aste01">
<!-- SUB: T_LANG -->
<td class="celltext">{VAR:lang_name}</td>
<!-- END SUB: T_LANG -->
</tr>
<tr class="aste01">
<!-- SUB: LANG -->
<td class="celltext"><input class='small_button' type='text' NAME='subj[{VAR:lang_id}]' VALUE='{VAR:subj}'></td>
<!-- END SUB: LANG -->
</tr>
</table>

</td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_OUTPUT_STYLE}:</td>
<td class="celltext"><select name='op_id'>{VAR:ops}</select></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_WH_MENU_LINK_IS}:</td>
<td class="celltext"><select class='small_button' name='l_section'>{VAR:sec}</select></td>
</tr>
<tr class="aste01">
<td class="celltext" colspan=2><input type='submit' class='formbutton' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
