<form action='reforb.{VAR:ext}' METHOD=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_FORMS_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_COMMENT}:</td><td class="fform"><textarea cols=50 rows=5 NAME=comment>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_CHOOSE_FORMS}:</td><td class="fform"><select name='forms[]' multiple>{VAR:forms}</select></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_CHOOSE_TABLE_STYLE}:</td><td class="fform"><select name='table_style'>{VAR:styles}</select></td>
</tr>
<!-- SUB: ADD -->
<tr>
<td class="fcaption">{VAR:LC_FORMS_CHOOS_SUBFORM}:</td><td class="fform"><select multiple name='baseform[]'>{VAR:forms}</select></td>
</tr>
<!-- END SUB: ADD -->

<!-- SUB: CHANGE -->
<tr>
<td class="fcaption" colspan=2><a href='{VAR:admin}'>Administreeri{VAR:LC_FORMS_ADMIN}</a></td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
<td colspan=2 class="fcaption"><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
