<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fgtext">{VAR:LC_FORMS_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fgtext">{VAR:LC_FORMS_COMMENT}:</td><td class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fgtext" colspan=2>{VAR:LC_FORMS_TYPE}:</td>
</tr>
<tr>
<td class="fgtext"><input type='radio' NAME='type' VALUE='email' {VAR:email_selected}></td><td class="fform">{VAR:LC_FORMS_SEND_FORM_TO_EMAIL_AFTER_FILLING}</td>
</tr>
<tr>
<td class="fgtext"><input type='radio' NAME='type' VALUE='email_form' {VAR:email_form}></td><td class="fform">Saada vorm teises vormis olevatele aadressidele</td>
</tr>
<!--<tr>
<td class="fcaption"><input type='radio' NAME='type' VALUE='move_filled' {VAR:move_filled_selected}></td><td class="fform">{VAR:LC_FORMS_MOVE_FORM_ENTRIES_OTHER_CATEGORY}</td>
</tr>-->
<tr>
<td class="fgtext"><input type='radio' NAME='type' VALUE='join_list' {VAR:join_list_selected}></td><td class="fform">{VAR:LC_FORMS_ESPOUSE_MAILLIST}</td>
</tr>
<tr>
<td class="fgtext" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_FORWARD}'></td>
</tr>
</table>
{VAR:reforb}
</form>
