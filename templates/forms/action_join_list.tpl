<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_FORMS_AFTER_FILLING_FORM_IF} <select name='j_checkbox'>{VAR:checkbox}</select>{VAR:LC_FORMS_IS_MARK_ADD_TO_LIST} <select name='j_list'>{VAR:list}</select> {VAR:LC_FORMS_USER_WHOS_EMAIL} <select NAME='j_textbox'>{VAR:textbox}</select> {VAR:LC_FORMS_AND_NAME_IN_CHECKBOX} <select NAME='j_name_tb'>{VAR:name_tb}</select></td>
</tr>
<tr>
<td class="fcaption" ><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_FORWARD}'></td>
</tr>
</table>
{VAR:reforb}
</form>
