<form action='reforb.{VAR:ext}' METHOD=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Alias:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_COMMENT}:</td><td class="fform"><textarea cols=50 rows=5 NAME=comment>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_FORMS_CHOOSE_FORMS}:</td><td class="fform"><select name='forms[]' size="20" multiple>{VAR:forms}</select></td>
</tr>
<!-- SUB: admin -->
<tr>
<td colspan=2 class="fcaption"><a href="{VAR:adminurl}">{VAR:LC_FORMS_MAKE_OUTPUT}</a></td>
</tr>
<!-- END SUB: admin -->
<tr>
<td colspan=2 class="fcaption"><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
