<form action='reforb.{VAR:ext}' METHOD={VAR:meth}>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fgtext">{VAR:LC_FORMS_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fgtext">{VAR:LC_FORMS_COMMENT}:</td><td class="fform"><textarea cols=50 rows=5 NAME=comment>{VAR:comment}</textarea></td>
</tr>
<tr>
<td valign="top" class="fgtext">{VAR:LC_FORMS_CHOOSE_FORMS}:</td>
<td class="fgtext"><select name='forms[]' multiple>{VAR:forms}</select></td>
</tr>
<tr>
<td class="fgtext">{VAR:LC_FORMS_CHOOSE_TABLE_STYLE}:</td><td class="fform"><select name='table_style'>{VAR:styles}</select></td>
</tr>
<!-- SUB: ADD -->
<tr>
<td class="fgtext">{VAR:LC_FORMS_CHOOSE_SUBFORM}:</td><td class="fform"><select multiple name='baseform[]'>{VAR:forms2}</select></td>
</tr>
<!-- END SUB: ADD -->

<!-- SUB: ADD_2_LINE -->
<tr>
<td class="fgtext">{VAR:form_name}</td><td class="fform"><input type='text' name='ord[{VAR:form_id}]' size=3></td>
</tr>
<!-- END SUB: ADD_2_LINE -->

<!-- SUB: ADD2 -->
<tr>
	<td class="fgtext">{VAR:LC_FORMS_CHOOSE_ELEMENTS}:</td>
	<td class="fgtext"><select name='elements[]' multiple class='small_button' size="20">{VAR:els}</select></td>
</tr>
<!-- END SUB: ADD2 -->
<tr>
	<td class="fgtext">{VAR:LC_FORMS_ALIASMGR}:</td>
	<td class="fgtext"><input type="checkbox" name="has_aliasmgr" value="1" {VAR:has_aliasmgr}></td>
</tr>
<!-- SUB: CHANGE -->
<tr>
<td class="fgtext" colspan=2><a href='{VAR:admin}'>{VAR:LC_FORMS_ADMIN}</a></td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
<td colspan=2 class="fgtext" align="center">
<input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_SAVE}'>
<input class='small_button' type='reset' VALUE='{VAR:LC_FORMS_RESET}'>
</td>
</tr>
</table>
{VAR:reforb}
</form>
