<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">{VAR:LC_FORMS_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_COMMENT}:</td><td class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_HOW_MANY_COLUMNS}:</td><td class="fform"><input type='text' NAME='num_cols' VALUE='{VAR:num_cols}' size=3></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_TABLE_STYLE}:</td><td class="fform"><select name='tablestyle'>{VAR:tablestyles}</select></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_TABLE_STYLE_ORDINARY}:</td><td class="fform"><select name='header_normal'>{VAR:header_normal}</select></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_TABLE_STYLE_POSS_SORT}:</td><td class="fform"><select name='header_sortable'>{VAR:header_sortable}</select></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_TABLE_STYLE_SORTED}:</td><td class="fform"><select name='header_sorted'>{VAR:header_sorted}</select></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_CELL_STYLE_1}:</td><td class="fform"><select name='content_style1'>{VAR:content_style1}</select></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_CELL_STYLE_2}:</td><td class="fform"><select name='content_style2'>{VAR:content_style2}</select></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_SORTED_CELL_SYLE_1}:</td><td class="fform"><select name='content_sorted_style1'>{VAR:content_sorted_style1}</select></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_SORTED_CELL_SYLE_2}:</td><td class="fform"><select name='content_sorted_style2'>{VAR:content_sorted_style2}</select></td>
</tr>
<tr>
<td class="fform">Linkide stiil:</td><td class="fform"><select name='link_style'>{VAR:link_style}</select></td>
</tr>
<tr>
<td class="fform">Vaata link <i>popup</i>-s:</td>
<td class="fform">
	<input type="checkbox" name="view_new_win" value="1" {VAR:view_new_win}>
</td>
</tr>
<tr>
<td class="fform"><i>Popup</i> akna mõõtmed:</td><td class="fform"><input type="text" name="new_win_x" value="{VAR:new_win_x}" size="3">x<input type="text" name="new_win_y" value="{VAR:new_win_y}" size="3">
	Kerimisribad: <input type="checkbox" name="new_win_scroll" {VAR:new_win_scroll} value="1">
	Fikseeritud suurus: <input type="checkbox" name="new_win_fixedsize" {VAR:new_win_fixedsize} value="1">
</td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_CHOOSE_FORMS_WHERE_ELEMENTS_TAKEN}:</td><td class="fform"><select class='small_button' name='forms[]' multiple size=7>{VAR:forms}</select></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_CHOOSE_CATALOGUES_WHERE_ENTRIES}:</td><td class="fform"><select class='small_button' name='moveto[]' size=10 multiple>{VAR:moveto}</select></td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_SUBSMIT_BUTTON}</td><td class="fform">{VAR:LC_FORMS_TEXT}: <input type='text' name='submit_text' value='{VAR:submit_text}'> Jrk: <input type='text' class='small_button' size=3 value='{VAR:submit_jrk}'>{VAR:LC_FORMS_UP}  <input type='checkbox' name='submit_top' value='1' {VAR:top_checked}>{VAR:LC_FORMS_DOWN}  <input type='checkbox' name='submit_bottom' value='1' {VAR:bottom_checked}> </td>
</tr>
<tr>
<td class="fform">{VAR:LC_FORMS_ADD_BUTTON}:</td><td class="fform">{VAR:LC_FORMS_TEXT}: <input type='text' name='user_button_text' value='{VAR:user_button_text}'> Jrk: <input type='text' class='small_button' size=3 value='{VAR:but_jrk}'> {VAR:LC_FORMS_UP} <input type='checkbox' name='user_button_top' value='1' {VAR:user_button_top}>{VAR:LC_FORMS_DOWN} <input type='checkbox' name='user_button_bottom' value='1' {VAR:user_button_bottom}>  &nbsp;{VAR:LC_FORMS_ADDRESS}:<input type='text' name='user_button_url' value='{VAR:user_button_url}'> </td>
</tr>
<!-- SUB: CHANGE -->
<tr>
<td class="fform" colspan=2>{VAR:LC_FORMS_CHOOSE_WHICH_COUMN_ELEMENT}:</td>
</tr>
<tr>
<td class="fform" colspan=2>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">{VAR:LC_FORMS_COLUMN}</td>
<!-- SUB: TITLE -->
<td class="fform">{VAR:el_name}</td>
<!-- END SUB: TITLE -->
<td class="fform">{VAR:LC_FORMS_CHANGING}</td>
<td class="fform">{VAR:LC_FORMS_LOOKING}</td>
<td class="fform">Special</td>
<td class="fform">{VAR:LC_FORMS_DELETE}</td>
<td class="fform">{VAR:LC_FORMS_CREATED}</td>
<td class="fform">{VAR:LC_FORMS_CHANGED}</td>
<td class="fform">UID</td>
<td class="fform">{VAR:LC_FORMS_ACTIVITY}</td>
<td class="fform">{VAR:LC_FORMS_CHANGING_POSITION}</td>
<!-- SUB: LANG_H -->
<td class="fform">{VAR:LC_FORMS_COLUMN_TITLE} ({VAR:lang_name})</td>
<!-- END SUB: LANG_H -->
<td class="fform">{VAR:LC_FORMS_SORTABLE}?</td>
<td class="fform">{VAR:LC_FORMS_DEL_TABLE_COL}?</td>
<td class="fform">{VAR:LC_FORMS_TABLE_ADD_COL}?</td>
</tr>

<!-- SUB: ROW -->
<tr>
<td class="fform">{VAR:column}</td>

<!-- SUB: COL -->
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='{VAR:el_id}' {VAR:checked}></td>
<!-- END SUB: COL -->

<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='change' {VAR:change_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='view' {VAR:view_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='special' {VAR:special_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='delete' {VAR:delete_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='created' {VAR:created_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='modified' {VAR:modified_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='uid' {VAR:uid_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='active' {VAR:active_checked}></td>
<td align="center" class="fform"><input type='checkbox' name='columns[{VAR:column}][]' value='chpos' {VAR:chpos_checked}></td>
<!-- SUB: LANG -->
<td class="fform"><input type='text' class='small_button' name='names[{VAR:column}][{VAR:lang_id}]' VALUE='{VAR:c_name}'></td>
<!-- END SUB: LANG -->
<td class="fform" align="center"><input type='checkbox' name='sortable[{VAR:column}]' VALUE='1' {VAR:sortable}></td>
<td class="fform" align="center"><input type='checkbox' name='del[{VAR:column}]' VALUE='1'></td>
<td class="fform" align="center"><input type='checkbox' name='addaf[{VAR:column}]' VALUE='1'></td>
</tr>
<!-- END SUB: ROW -->
<tr>
<td class="fform">Vaatamine:</td>
<!-- SUB: VCOL -->
<td align="center" class="fform"><input type='radio' name='viewcol' value='{VAR:el_id}' {VAR:checked}></td>
<!-- END SUB: VCOL -->
<td class="fform">&nbsp;</td>
<td align="center" class="fform"><input type='radio' name='viewcol' value='view' {VAR:v_view_checked}></td>
<td class="fform" colspan="20">&nbsp;</td>
</tr>
<tr>
<td class="fform">Muutmine:</td>
<!-- SUB: CCOL -->
<td align="center" class="fform"><input type='radio' name='changecol' value='{VAR:el_id}' {VAR:checked}></td>
<!-- END SUB: CCOL -->
<td align="center" class="fform"><input type='radio' name='changecol' value='change' {VAR:v_change_checked}></td>
<td class="fform" colspan="20">&nbsp;</td>
</tr>
</table>
</td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_FORMS_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
