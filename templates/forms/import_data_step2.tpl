<br><br>
<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr bgcolor="#C9EFEF">
<td class="title" colspan=2>{VAR:LC_FORMS_ELEMENT}</td>
<td class="title" colspan={VAR:num_cols} align=center>{VAR:LC_FORMS_CHOOSE_WHICH_COLUMN_IS_EL}.</td>
</tr>
<!-- SUB: GRID_LINE -->
<tr>
<td colspan=2 class=plain><b>{VAR:field_name}</b></td>
<!-- SUB: GRID_COL -->
<td class=plain align=center><input type='radio' NAME='rb_{VAR:row}' VALUE='{VAR:col}' {VAR:checked}></td>
<!-- END SUB: GRID_COL -->
</tr>
<!-- END SUB: GRID_LINE -->
<!-- SUB: EX_LINE -->
<tr><td class=plain align=right>{VAR:line_no}.</td><td class=plain><input type='checkbox' name='ch_{VAR:line_no}' VALUE=1></td>
<!-- SUB: EX_COL -->
<td class=plain>{VAR:example}</td>
<!-- END SUB: EX_COL -->
</tr>
<!-- END SUB: EX_LINE -->
<tr><td colspan=2 class=plain>{VAR:LC_FORMS_CHOOSE_WHISH_ROWS_IGNORED}</td><td class=plain align=center colspan={VAR:num_cols}><input class='small_button' type=submit VALUE='Impordi'></td></tr>
</table>
<input type=hidden NAME='action' VALUE='import_data_step2'>
<input type=hidden NAME='step' VALUE='3'>
<input type=hidden NAME='id' VALUE='{VAR:form_id}'>
<input type=hidden NAME='fname' VALUE='{VAR:fname}'>
<input type=hidden NAME='ftype' VALUE='{VAR:ftype}'>
<input type=hidden NAME='numrows' VALUE='{VAR:numrows}'>
</form>
