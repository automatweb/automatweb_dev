<form action='reforb.{VAR:ext}' method=post name=ffrm>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform" colspan=2>Vali kataloogid, mille alamkatalooge valida saab:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select name='main_folders[]' multiple size="20" class='small_button'>{VAR:main_folders}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_CHOOSE_CATALOGUE_WHERE_SAVES_FORM_INFO}:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select name='ff_folder' class='small_button'>{VAR:ff_folder}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_CHOOSE_CATALOGUE_WHERE_ADD_TYPELEMENT}:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select name='newel_parent' class='small_button'>{VAR:ne_folder}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_CHOOSE_CATALOGUE_WHERE_SAVES_FORM_EL}:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select name='tear_folder' class='small_button'>{VAR:tear_folder}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_CHOOSE_CATALOGUE_WHERE_CAN_SAVE_NEW_EL}:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select class='small_button' NAME='el_menus[]' size=20 multiple>{VAR:el_menus}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_TABLE_ADD_COL}:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select class='small_button' NAME='el_menus2[]' size=20 multiple>{VAR:el_menus2}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_CHOOSE_MOVE_FOLDERS}:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select class='small_button' NAME='el_move_menus[]' size=20 multiple>{VAR:el_move_menus}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_CHOOSE_TIEELEMENTFORMS}:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select class='small_button' NAME='relation_forms[]' size=10 multiple>{VAR:relation_forms}</select></td>
</tr>
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' NAME='save_form_settings' VALUE='{VAR:LC_FORMS_SAVE} form'></td>
</table>
{VAR:reforb}
</form>
  
