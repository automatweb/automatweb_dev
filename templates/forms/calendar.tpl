<form action='reforb.{VAR:ext}' method=post name=ffrm>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_EV_ENTRY_FORM}:</td>
<td class="celltext"><input type='checkbox' name='ev_entry_form' value=1 {VAR:ev_entry_form}></td>
</tr>
<tr class="aste01">
<td class="celltext">{VAR:LC_FORMS_CALENDAR_OP}:</td>
<td class="celltext"><select name="event_display_table" class="formselect2">{VAR:event_display_tables}</select></td>
</tr>
<tr class="aste01">
<td class="celltext">Eventi alguse m‰‰rab element:</td>
<td class="celltext"><select name="event_start_el" class="formselect2">{VAR:event_start_els}</select></td>
</tr>
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_SAVE} form'></td>
</table>
{VAR:reforb}
</form>
  
