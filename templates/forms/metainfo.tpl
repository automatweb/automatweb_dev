<br>
<form action='reforb.{VAR:ext}' method=post name=ffrm>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_FORMS_NAME}:</td>
<td class="fform"><input type='text' NAME='name' VALUE='{VAR:form_name}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_COMMENT}:</td>
<td class="fform"><textarea NAME='comment' COLS=50 ROWS=5 wrap='soft'>{VAR:form_comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_CREATED}:</td>
<td class="fform">{VAR:created}, {VAR:created_by}</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_CHANGED}:</td>
<td class="fform">{VAR:modified}, {VAR:modified_by}</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_LOOKED}:</td>
<td class="fform">{VAR:views} korda</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_FILLED}:</td>
<td class="fform">{VAR:num_entries} korda</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_POSITION}:</td>
<td class="fform">{VAR:position}</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
